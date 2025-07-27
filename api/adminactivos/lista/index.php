<?php
include '../../../config.php';
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

$nuevo_pedido = new \NuevosPedidos\NuevosPedidos();

error_reporting(0);
if (isset($_POST) && !empty($_POST) && !isset($_POST['action'])) 
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
        where t_activo_fijo.id_pedido != 0
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
        DATE_FORMAT(t_activo_fijo.fecha_entrada ,'%d-%m-%Y %H:%i:%s') as fecha_ingreso,
        c_almacenp.nombre
      from t_activo_fijo
      inner join c_articulo on c_articulo.id = t_activo_fijo.id_articulo
      left join c_almacenp on c_almacenp.id = c_articulo.cve_almac
      LEFT JOIN c_serie on c_serie.id = t_activo_fijo.id_serie
      where t_activo_fijo.id_pedido != 0
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
      
        $sql2 = "
            select 
            c_cliente.RazonSocial,
            DATE_FORMAT(th_pedido.Fec_Pedido,'%d-%m-%Y %H:%i:%s') as Fec_Pedido,
            th_pedido.Cve_Usuario
            from t_activo_fijo
            INNER JOIN th_pedido on th_pedido.id_pedido = t_activo_fijo.id_pedido
            INNER join c_cliente on c_cliente.Cve_Clte = th_pedido.Cve_clte
            where t_activo_fijo.id = {$id}
        ";
  
        if (!($res2 = mysqli_query($conn, $sql2))) 
        {
          echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        
        $row2 = mysqli_fetch_array($res2);
        $row2 = array_map("utf8_encode", $row2);
        
        $responce->rows[$i]['id']= $idy_ubica;
        $responce->rows[$i]['cell']=array('','',$cve_articulo,$des_articulo,$unidadMedida,$id,$numero_serie,$clave_activo,$row2["RazonSocial"],$fecha_ingreso,'Si',$row2["Fec_Pedido"],$row2["Cve_Usuario"]);
        $i++;
    }
    echo json_encode($responce);
} 
