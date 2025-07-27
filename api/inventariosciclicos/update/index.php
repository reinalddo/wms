<?php
include '../../../app/load.php';

error_reporting(0);

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$i = new \InventariosCiclicos\InventariosCiclicos();




if($_POST['action'] === 'printReport'){
    $i::printReport($_POST['id']);
}




//debug
if($_POST['action'] === 'guardarUsuario'){
    $result = $i->saveUser(intval($_POST['id']), $_POST['usuario']);
    echo json_encode(
        array(
            "success" => $result
        )
    );exit;
}


if( $_POST['action'] == 'add' ) {
    $i->save($_POST);
    $i->saveConteo(0);
    $ID_PLAN = "(SELECT MAX(ID_PLAN) FROM det_planifica_inventario)";
    $NConteo = "(SELECT MAX(NConteo) FROM t_conteoinventariocicl WHERE ID_PLAN = {$ID_PLAN})";

    $articulos_produccion = $_POST['articulos_produccion'];
    $articulos_cuarentena = $_POST['articulos_cuarentena'];
    $articulos_obsoletos = $_POST['articulos_obsoletos'];

    $tabla = 'V_ExistenciaGral';
    $sqlCuarentena = " AND IFNULL(ve.Cuarentena, 0) = 0 ";
    $sqlObsoletos  = " AND IF(ar.Caduca = 'S', l.Caducidad, CURDATE()+1) > CURDATE() ";

    if($articulos_produccion == 1) $tabla = 'V_ExistenciaGralProduccion';
    if($articulos_cuarentena == 1) $sqlCuarentena = "";
    if($articulos_obsoletos == 1) $sqlObsoletos = "";

  
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    foreach ($_POST["arrInv"] as $item) 
    {
        $cve_articulo = $item["articulo"];
        $almacen = $_POST["almacen"];

        $sql = "SELECT id FROM c_almacenp WHERE clave = '{$almacen}'";
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $almacen_id = mysqli_fetch_array($res);
        $almacen_id = $almacen_id['id'];

/*
        $sql ="SELECT
                u.cve_almac id,
                u.CodigoCSD ubicacion,
                u.idy_ubica idy_ubica,
                e.cve_articulo cve_articulo,
                e.cve_lote lote,
                '' serie,
                e.Existencia cantidad
            FROM V_ExistenciaGralProduccion e
                LEFT JOIN c_ubicacion u ON e.cve_ubicacion = u.idy_ubica
                LEFT JOIN c_almacen a ON e.cve_ubicacion = a.cve_almac 
            WHERE e.cve_almac = '{$almacen_id}' AND e.cve_articulo = '{$cve_articulo}'AND e.Existencia > 0 AND e.tipo = 'ubicacion'" 
            ;*/

        $sql = "SELECT DISTINCT
          ve.cve_articulo AS cve_articulo,
          u.idy_ubica AS idy_ubica,
          IFNULL(ve.cve_lote, '') AS cve_lote,
          IFNULL(ve.Existencia, 0) AS cantidad,
          IFNULL(ve.Cve_Contenedor, '') AS Cve_Contenedor,
          IFNULL(ch.IDContenedor, '') AS ntarima,
          ve.ID_Proveedor AS ID_Proveedor
        FROM c_almacenp p, c_ubicacion u
        LEFT JOIN {$tabla} ve ON ve.cve_ubicacion = u.idy_ubica
        LEFT JOIN c_lotes l ON l.cve_articulo = ve.cve_articulo AND l.lote = ve.cve_lote
        LEFT JOIN c_articulo ar ON ar.cve_articulo = ve.cve_articulo
        LEFT JOIN c_charolas ch ON ch.clave_contenedor = ve.Cve_Contenedor
        , c_almacen a
        WHERE 1 
            AND u.cve_almac=a.cve_almac
            AND a.cve_almacenp=p.id
            AND u.cve_almac = a.cve_almac
            AND u.Activo = '1' 
            AND ve.cve_almac = '{$almacen_id}'
            AND ve.cve_articulo = '{$cve_articulo}'
            {$sqlCuarentena} 
            {$sqlObsoletos} 
        GROUP BY cve_articulo, idy_ubica, cve_lote, Cve_Contenedor, ID_Proveedor";

        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $sql_track = "";
        while ($row = mysqli_fetch_array($res))
        {
            $idy_ubica = $row["idy_ubica"];
            $cve_lote = $row["cve_lote"];
            $Cantidad = 0;
            $ExistenciaTeorica = $row["cantidad"];
            $Id_Proveedor = $row["ID_Proveedor"];
            $ntarima = $row["ntarima"];
            $NConteo = 0;
            $cve_usuario = '';
            $Cve_Contenedor = $row["Cve_Contenedor"];
            
            $sql = "";
            if($Cve_Contenedor == '')
            {
            $sql = "INSERT INTO t_invpiezasciclico ( 
                    ID_PLAN, 
                    NConteo, 
                    cve_articulo, 
                    cve_lote,
                    idy_ubica,
                    Cantidad, 
                    ExistenciaTeorica,
                    cve_usuario, 
                    fecha, 
                    Activo,
                    Id_Proveedor
                ) VALUES(
                    {$ID_PLAN},
                    {$NConteo}, 
                    '{$cve_articulo}',  
                    '{$cve_lote}',
                    '{$idy_ubica}',
                    '{$Cantidad}', 
                    '{$ExistenciaTeorica}',
                    '{$cve_usuario}', 
                    NOW(), 1, '{$Id_Proveedor}'); ";
            }
            else
            {
            $sql = "INSERT INTO t_invtarimaciclico ( 
                    ID_PLAN, 
                    NConteo, 
                    cve_articulo, 
                    Cve_Lote,
                    ntarima,
                    idy_ubica,
                    existencia, 
                    Teorico,
                    cve_usuario, 
                    fecha, 
                    Activo,
                    Id_Proveedor
                ) VALUES(
                    {$ID_PLAN},
                    {$NConteo}, 
                    '{$cve_articulo}',  
                    '{$cve_lote}',
                    '{$ntarima}',
                    '{$idy_ubica}',
                    '{$Cantidad}', 
                    '{$ExistenciaTeorica}',
                    '{$cve_usuario}', 
                    NOW(), 1, '{$Id_Proveedor}'); ";

            }
            //$sql_track .= $sql."**********";
            mysqli_query($conn, $sql);
        }

            $sql = "INSERT INTO t_conteoinventariocicl ( 
                    ID_PLAN, 
                    NConteo, 
                    Status,
                    Activo
                ) VALUES(
                    {$ID_PLAN},
                    0, 
                    'A', 
                    1); ";
            
            mysqli_multi_query($conn, $sql);


    }

    echo json_encode(["success" => true, "sql_track" => $sql_track]);exit;
}



if( $_POST['action'] == 'edit' ) {
    $i->actualizarClientes($_POST);
}

if( $_POST['action'] == 'exists' ) {
    $i->Cve_Clte = $_POST["codigo"];
    $i->__get("Cve_Clte");

    $success = false;

    if (!empty($i->data->Cve_Clte)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);
} 



/**
 * Obtiene los datos del Grid principal de la vista
 * @author Brayan Rincon <brayan262@gmail.com>
 */
if(isset($_POST) AND ! empty($_POST) AND $_POST['action'] == 'cargarDeatllesInventarioCiclico')
{
    $id_plan = $_POST["id_plan"];
    $data = $i->loadDetalleCid($id_plan, 0, 100000);

    foreach($data as $value){
        extract($value);
        $response[] = [
            'id' => $id,
            'clave' => $clave, 
            'ubicacion' => $ubicacion,
            'zona' => $zona,
            'descripcion' => $descripcion, 
            'lote' => $lote, 
            'caducidad' => $caducidad, 
            'serie' => $serie, 
            'stockTeorico' => $stockTeorico, 
            'stockFisico' => $stockFisico, 
            'diferencia' => $diferencia, 
            'conteo' => $conteo, 
            'usuario' => $usuario, 
            'unidad_medida' => $unidad_medida
        ];
    }

    echo json_encode(['status'=>200, 'data'=>$response]);exit;

}



if(isset($_POST) AND ! empty($_POST) AND $_POST['action'] == 'guardarStockCiclico')
{
    $stocks = $_POST['stocks'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $fech = date('Y-m-d');

    foreach ($stocks as $key => $value) {        
    
        list($tipo, $ubicacion, $articulo, $inventario, $conteo, $user) = explode('|',$value['id']);
        $stock_fisico = $value['value'];

        $sql22 = "SELECT * FROM t_invpiezasciclico  WHERE ID_PLAN = {$inventario} AND NConteo = {$conteo}; ";
        $query22 = mysqli_query($conn, $sql22);        
        $cua = mysqli_num_rows($query22);

        if($cua>0){
            $conteo_mas = $conteo + 1;
            $cantidad = $value['value'];
            $sql = "UPDATE t_invpiezasciclico SET 
                        Cantidad = {$cantidad}
                    WHERE 
                        ID_PLAN = {$inventario} AND 
                        NConteo = {$conteo} AND 
                        cve_articulo = '{$articulo}' AND 
                        idy_ubica = '{$ubicacion}'; ";
            $query = mysqli_query($conn, $sql);
        }
        else {
            $sql = "INSERT INTO t_invpiezasciclico (Cantidad,ID_PLAN,NConteo,cve_articulo,idy_ubica,cve_usuario,fecha,Activo) VALUES ({$stock_fisico},{$inventario},{$conteo},'{$articulo}',{$ubicacion},'{$user}',{$fech},'1');";
            $query = mysqli_query($conn, $sql);
        }

        //Chequear status del inventario
        $sql2 = "SELECT SUM(ABS(ExistenciaTeorica - Cantidad)) AS diferencia, NConteo, ID_PLAN FROM t_invpiezasciclico WHERE ID_PLAN = {$inventario} AND NConteo > 0  GROUP BY ID_PLAN, NConteo;";

        $query2 = mysqli_query($conn, $sql2);
        if($query2->num_rows > 0){
            $data = mysqli_fetch_all($query2, MYSQLI_ASSOC);
            $sql3 = "UPDATE det_planifica_inventario SET Status = 'T' WHERE ID_PLAN = {$inventario};
            UPDATE t_invpiezasciclico SET ExistenciaTeorica = Cantidad WHERE ID_PLAN = {$inventario} AND NConteo = (SELECT MAX(NConteo) FROM t_conteoinventariocicl WHERE ID_PLAN = {$inventario});";
            $s3 = false;
            if(count($data) === 1){
                if(intval($data[0]['diferencia']) === 0){
                    $s3 = true;
                }
            } 
            else {
                $lastIndex = count($data) -1;
                if(intval($data[$lastIndex]['diferencia']) === 0){
                    $s3 = true;
                }
                else{
                    $diferencias = array();
                    foreach($data as $diff){
                        $diferencias [] = intval($diff['diferencia']);
                    }
                    $diferencias = array_count_values($diferencias);
                    arsort($diferencias);
                    if(array_shift($diferencias) > 1){
                        $s3 = true;
                    }
                }
            } 
            if($s3){
                mysqli_multi_query($conn, $sql3);
            }
        }
    }


    echo json_encode(array(
        "success"   =>  $query,
        'status' => 200
    ));exit;
}




if( $_POST['action'] == 'loadDetalle' ) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $id = $_POST["ID_PLAN"];

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)
    $count = $i->loadDetalleCount($id);

    if(!$sidx) $sidx =1;

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;


    $data=$i->loadDetalleCid($id, $start, $limit);
    $i = 0;
    foreach($data as $art){
        extract($art);
        $responce->rows[$i]['id'] = $art['id'];
        $responce->rows[$i]['cell']= array($clave, $clave, $descripcion, $zona, $ubicacion, $serie, $lote, $caducidad, $stockTeorico, $stockFisico, $diferencia, '', '', $conteo, $usuario);
        $i++;
    }

    echo json_encode($responce);

} 


if( $_POST['action'] == 'existe' ) {

    $existe = $i->existe($_POST);

    if($existe === false){
        $articulo = NULL; $success = false;
    } else {
        $articulo = $existe; $success = true;
    }

    $arr = array(
        "success"=>$success,
        "articulo"=> $articulo
    );

    echo json_encode($arr);exit;

}


if($_POST['oper'] === 'edit'){
    $stock_fisico = $_POST['stockFisico'];
    list($tipo, $ubicacion, $articulo, $inventario, $conteo, $user) = explode('|',$_POST['id']);
    $fech=date('Y-m-d');
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql22 = "SELECT * from t_invpiezasciclico 
            WHERE ID_PLAN = {$inventario}
            AND NConteo = {$conteo}; ";
    $query22 = mysqli_query($conn, $sql22);
    
    $cua=mysqli_num_rows($query22);

    if($cua>0){
        $sql = "UPDATE  t_invpiezasciclico 
            SET Cantidad = {$stock_fisico}
            WHERE ID_PLAN = {$inventario}
            AND NConteo = {$conteo}
            AND cve_articulo = '{$articulo}'
            AND idy_ubica = '{$ubicacion}'; ";
        $query = mysqli_query($conn, $sql);
    }
    else
    {
        $sql = "INSERT INTO t_invpiezasciclico (Cantidad,ID_PLAN,NConteo,cve_articulo,idy_ubica,cve_usuario,fecha,Activo) VALUES ({$stock_fisico},{$inventario},{$conteo},'{$articulo}',{$ubicacion},'{$user}',{$fech},'1');";
        $query = mysqli_query($conn, $sql);
    }


    //Chequear status del inventario
    $sql2 = "SELECT SUM(ABS(ExistenciaTeorica - Cantidad)) AS diferencia, NConteo, ID_PLAN FROM t_invpiezasciclico WHERE ID_PLAN = {$inventario} AND NConteo > 0  GROUP BY ID_PLAN, NConteo;";

    $query2 = mysqli_query($conn, $sql2);
    if($query2->num_rows > 0){
        $data = mysqli_fetch_all($query2, MYSQLI_ASSOC);
        $sql3 = "UPDATE det_planifica_inventario SET Status = 'T' WHERE ID_PLAN = {$inventario}; UPDATE t_invpiezasciclico SET ExistenciaTeorica = Cantidad WHERE ID_PLAN = {$inventario} AND NConteo = (SELECT MAX(NConteo) FROM t_conteoinventariocicl WHERE ID_PLAN = {$inventario});";
        $s3 = false;
        if(count($data) === 1){
            if(intval($data[0]['diferencia']) === 0){
                $s3 = true;
            }
        }else{
            $lastIndex = count($data) -1;
            if(intval($data[$lastIndex]['diferencia']) === 0){
                $s3 = true;
            }
            else{
                $diferencias = array();
                foreach($data as $diff){
                    $diferencias [] = intval($diff['diferencia']);
                }
                $diferencias = array_count_values($diferencias);
                arsort($diferencias);
                if(array_shift($diferencias) > 1){
                    $s3 = true;
                }
            }
        } 
        if($s3){
            mysqli_multi_query($conn, $sql3);
        }
    }

    mysqli_close($conn);
    echo json_encode(array(
        "success"   =>  $query
    ));
}

if($_POST['action'] === 'existe_conteo_0')
{
    $id_plan = $_POST['id'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT COUNT(*) cuenta FROM t_invpiezasciclico WHERE ID_PLAN = '{$id_plan}' AND NConteo = 0";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);

    $cuenta = $row['cuenta'];


    $sql = "SELECT NConteo FROM t_conteoinventariocicl WHERE ID_PLAN = '{$id_plan}'";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $option = "";
    $max_conteo = "";
    while($row = mysqli_fetch_array($res))
    {
        $option .= "<option value='".$id_plan."-".$row['NConteo']."'>".$row['NConteo']."</option>";
        $max_conteo = $row["NConteo"];
    }

  mysqli_close($conn);

    echo json_encode(array(
        "cuenta"   =>  $cuenta,
        "conteos_option" => $option,
        "max_conteo" => $id_plan."-".$max_conteo
    ));
}