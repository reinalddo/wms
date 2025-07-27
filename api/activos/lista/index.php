<?php
include '../../../config.php';
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

$nuevo_pedido = new \NuevosPedidos\NuevosPedidos();

error_reporting(0);
if($_POST['action'] === 'getClientes')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = " 
        select RazonSocial, Cve_CteProv from c_cliente;
    ";
    
    if (!$res = mysqli_query($conn, $sqlCount)) 
    {
      echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    //echo var_dump($res);
    $responce =array();
    
    
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        $responce["cliente"][$i]["nombre"] = utf8_decode($row["RazonSocial"]);
        $responce["cliente"][$i]["clave_prov"] = utf8_decode($row["Cve_CteProv"]);
        $i++;
    }
    echo json_encode($responce);
}
else if($_POST['action'] === 'asignarActivo')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  
    $sql = "
      SELECT 
        c_articulo.cve_almac, 
        c_articulo.cve_articulo 
      FROM c_articulo 
      inner join t_activo_fijo on t_activo_fijo.id_articulo = c_articulo.id
      where t_activo_fijo.id = '".$_POST["id"]."'
      )
    ";
    $query = mysqli_query(\db2(), $sql);
    if($query->num_rows > 0){
        $articulo = mysqli_fetch_all($query, MYSQLI_ASSOC)[0];
        $cve_almacen = $articulo["cve_almac"];
        $cve_articulo = $articulo["cve_articulo"];
    }
    
    $sql = "SELECT id_folio FROM t_activo_fijo where id = '".$_POST["id"]."'";
    $query = mysqli_query(\db2(), $sql);
    $asignado = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["id_folio"];
    if($asignado != 0)
    {
        $responce =array("success"=>false, "folio" => mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["fol_folio"]);
    }
    else
    {
        // Creación de pedido
        $sql = "SELECT COUNT(Fol_folio) as Consecutivo FROM th_pedido";
        $query = mysqli_query(\db2(), $sql);
        if($query->num_rows > 0){
            $consecutivo = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["Consecutivo"];
        }

        $sql = "SELECT Cve_Clte FROM c_cliente where Cve_CteProv = '".$_POST["cve_cliente"]."';";
        $query = mysqli_query(\db2(), $sql);
        if($query->num_rows > 0){
            $cve_cliente = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["Cve_Clte"];
        }

        $sql = "SELECT clave FROM c_almacenp where id = '".$cve_almacen."';";
        $query = mysqli_query(\db2(), $sql);
        if($query->num_rows > 0){
            $id_almacen = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["clave"];
        }

        $dt = date('Ym');

        $folio = 'S'.$dt.$consecutivo;

        $data = array(
            'Fol_folio' => $folio,
            'Fec_Pedido' => date('Y-m-d H:m:s'),
            'Cve_clte' => $cve_cliente,
            'status' => 'A',
            'Fec_Entrega' => date('Y-m-d H:m:s'),
            'cve_Vendedor' => "",
            'Fec_Entrada' => date('Y-m-d H:m:s'),
            'Pick_Num' => "",
            'destinatario' => 0,
            'Cve_Usuario' => $_SESSION['id_user'],
            'Observaciones' => "",
            'ID_Tipoprioridad' => 0,
            'cve_almac' => $id_almacen,
            'arrDetalle' => array(
                array(
                    "Cve_articulo" => $cve_articulo,
                    "Num_cantidad" => 1,
                    "Num_Meses" => ""
                )
            )
        );
        $nuevo_pedido->save($data);

        //Surtido del pedido
      
        $sql = "SELECT *  FROM `ts_existenciapiezas` WHERE Existencia = (select min(Existencia) from ts_existenciapiezas WHERE `cve_articulo` LIKE '$cve_articulo') and `cve_articulo` LIKE '$cve_articulo'";
        $query = mysqli_query(\db2(), $sql);
        if($query->num_rows > 0){
            $existencia = mysqli_fetch_all($query, MYSQLI_ASSOC)[0];
            if($existencia["Existencia"] >= 2)
            {
                $sql = "update ts_existenciapiezas set Existencia = Existencia -1 where id = ".$existencia["id"].";";
                $query = mysqli_query(\db2(), $sql);
            }
            else
            {
                $sql = "delete from ts_existenciapiezas where id = ".$existencia["id"].";";
                $query = mysqli_query(\db2(), $sql);
            }
        }
      
        $sql = "SELECT id_pedido  FROM th_pedido where fol_folio = '".$folio."';";
        $query = mysqli_query(\db2(), $sql);
        if($query->num_rows > 0){
            $id_folio = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["id_pedido"];
        }

        $sqlCount = "insert into td_surtidopiezas(fol_folio,cve_almac,Sufijo,Cve_articulo,Cantidad,revisadas,status) 
                            values('$folio', '$cve_almacen', 1, '$cve_articulo',1,1,'A');";
        if (!$res = mysqli_query($conn, $sqlCount)){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}

        $sqlCount = "update th_pedido set status = 'T' where Fol_folio = '$folio'";
        if (!$res = mysqli_query($conn, $sqlCount)) {echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}

        $sqlCount = "update td_pedido set status = 'T' where Fol_folio = '$folio';";
        if (!$res = mysqli_query($conn, $sqlCount)) {echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ";}

        //creacion de clave de activo
        $sqlCount = " 
            update t_activo_fijo 
            set clave_activo = concat(id,'-','".$_POST["cve_cliente"]."','-',year(now())),
            id_pedido = ".$id_folio.",
            nombre_empleado = '".$_POST["nombre"]."',
            clave_empleado = '".$_POST["clave"]."',
            rfc_empleado = '".$_POST["rfc"]."'
            where id = ".$_POST["id"].";
        ";
        if (!$res = mysqli_query($conn, $sqlCount)) {echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";}
      
        $responce =array("success"=>true);
    }
    
    echo json_encode($responce);
}
else if (isset($_POST) && !empty($_POST) && !isset($_POST['action'])) 
{
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $criterio = $_POST['criterio'];
    $almacen = $_POST['almacen'];
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if (!$sidx) {$sidx =1;}
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  
    if($criterio != "" && isset($criterio))
    {
      $and = "and (c_articulo.cve_articulo like '%".$criterio."%' or t_activo_fijo.clave_activo like '%".$criterio."%')";
    }

    $sqlCount = " 
        select 
          count(*)
        from t_activo_fijo 
        inner join c_articulo on c_articulo.id = t_activo_fijo.id_articulo
        where t_activo_fijo.id_pedido = 0
        ".$and."
    ";

    if ((!$res = mysqli_query($conn, $sqlCount))) 
    {
      echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];
        
    $sql = "
      select 
        t_activo_fijo.id,
        c_articulo.cve_articulo,
        c_articulo.des_articulo,
        c_serie.numero_serie,
        ifnull(t_activo_fijo.clave_activo,'') as clave_activo,
        DATE_FORMAT(t_activo_fijo.fecha_entrada ,'%d-%m-%Y') as fecha_ingreso,
        c_almacenp.nombre
      from t_activo_fijo
      inner join c_articulo on c_articulo.id = t_activo_fijo.id_articulo
      left join c_almacenp on c_almacenp.id = c_articulo.cve_almac
      LEFT JOIN c_serie on c_serie.id = t_activo_fijo.id_serie
      where t_activo_fijo.id_pedido = 0
      ".$and."
      order by id desc
    ";
  
    if (!($res = mysqli_query($conn, $sql))) 
    {
      echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
  
    if( $count >0 ) 
    {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } 
    else 
    {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;
    
    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        $row = array_map("utf8_encode", $row);
        $arr[] = $row;
        extract($row);
        $responce->rows[$i]['id']= $idy_ubica;
        $responce->rows[$i]['cell']=array('','',$id,$cve_articulo,$des_articulo,$numero_serie,$clave_activo,$fecha_ingreso);
        $i++;
    }
    echo json_encode($responce);
} 
