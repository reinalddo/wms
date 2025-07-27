<?php
include '../../../app/load.php';

error_reporting(0);
// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) 
{
    exit();
}

$ga = new \InventariosFisicos\InventariosFisicos();

if($_POST['action'] === 'printReport_fisico_ParaLlenado')
{
    $ga::printReport_fisico_ParaLlenado($_POST['id'], $_POST['status']);
}

if($_POST['action'] === 'printReport_teorico')
{
    $ga::printReport_teorico($_POST['id'], $_POST['status']);
}

if($_POST['action'] === 'printReport')
{
    $ga::printReport($_POST['id'], $_POST['status']);
}

if($_POST['action'] === 'printDiferencias')
{
    $ga::printDiferencias($_POST['id']);
}

if($_POST['action'] === 'printDiferenciasExcel')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "
        SELECT 
            (CASE
                WHEN t_ubicacioninventario.cve_ubicacion IS NOT NULL THEN 
                    (SELECT desc_ubicacion FROM tubicacionesretencion WHERE t_ubicacioninventario.cve_ubicacion = tubicacionesretencion.cve_ubicacion)
                WHEN t_ubicacioninventario.idy_ubica IS NOT NULL THEN
                    (SELECT c_ubicacion.CodigoCSD FROM c_ubicacion WHERE c_ubicacion.idy_ubica = t_ubicacioninventario.idy_ubica)
                ELSE '--'
            END) as ubicacion,
            t_invpiezas.cve_articulo AS clave,
            c_articulo.des_articulo AS descripcion,
            IFNULL(c_lotes.LOTE, '--') AS lote,
            IFNULL(c_lotes.CADUCIDAD, '--') AS caducidad,
            '--' AS serie,
            t_invpiezas.ExistenciaTeorica AS stock_teorico,
            t_invpiezas.Cantidad AS stock_fisico,
            (t_invpiezas.Cantidad - t_invpiezas.ExistenciaTeorica) AS diferencia,
            t_invpiezas.NConteo AS conteo,
            'Piezas' AS unidad,
            c_usuario.nombre_completo AS usuario
        FROM t_invpiezas 
            LEFT JOIN th_inventario ON th_inventario.ID_Inventario = t_invpiezas.ID_Inventario
            LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = t_invpiezas.idy_ubica
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = t_invpiezas.cve_articulo
            LEFT JOIN c_lotes ON c_lotes.LOTE = t_invpiezas.cve_lote AND c_lotes.cve_articulo = t_invpiezas.cve_articulo
            LEFT JOIN c_usuario ON c_usuario.cve_usuario = t_invpiezas.cve_usuario
            LEFT JOIN t_ubicacioninventario on t_ubicacioninventario.id_inventario = t_invpiezas.id_inventario and t_ubicacioninventario.idy_ubica = t_invpiezas.idy_ubica 
        WHERE t_invpiezas.NConteo > 0
            AND t_invpiezas.Cantidad <> t_invpiezas.ExistenciaTeorica
            AND t_invpiezas.NConteo = (SELECT MAX(NConteo) FROM t_invpiezas WHERE ID_Inventario = th_inventario.ID_Inventario);
    ";
    $query = mysqli_query($conn, $sql);
  
    $delimiter = ",";
    $filename = "Diferencia entre conteos.csv";
    $f = fopen('php://memory', 'w');

    $fields = array(utf8_decode('Ubicación'), 'Clave', utf8_decode('Descripción'), 'Lote', 'Caducidad', 'Serie', utf8_decode('Stock Teórico'), utf8_decode('Stock Físico'), 'Diferencia', 'Conteo', 'Unidad', 'Usuario');
    fputcsv($f,$fields, $delimiter);

    while ($row = mysqli_fetch_array($query)) 
    {
        $lineData = array($row["ubicacion"],$row["clave"],$row["descripcion"],$row["lote"],$row["caducidad"],$row["serie"],$row["stock_teorico"],$row["stock_fisico"],$row["diferencia"],$row["conteo"],$row["unidad"],$row["usuario"]);
        fputcsv($f, $lineData, $delimiter);
    }

    fseek($f, 0);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    fpassthru($f);
}

if($_POST['action'] === 'guardarUsuario')
{
    echo json_encode(
        array(
            "success" => $ga->saveUser(intval($_POST['id']), $_POST['usuario'])
        )
    );
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'cargar_usuario')
{
    $id_inventario = $_POST['id_inventario'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
    $usuarios = '';
    $sql = "
        SELECT t_conteoinventario.cve_usuario, c_usuario.nombre_completo 
        FROM t_conteoinventario 
        LEFT JOIN c_usuario ON t_conteoinventario.cve_usuario = c_usuario.cve_usuario
        WHERE ID_Inventario = {$id_inventario} 
    ";
    $query = mysqli_query($conn, $sql);

    if($query->num_rows > 0)
    {
        $success = true;
        $usuarios = mysqli_fetch_all($query, MYSQLI_ASSOC);
    }
    echo json_encode(array(
        "success"  => $success,
        "usuarios" => $usuarios
    ));
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'VerificarInventariosAbiertos')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
    $sql = "SELECT COUNT(*) AS Abierto FROM th_inventario WHERE Status = 'A'";
    $query = mysqli_query($conn, $sql);
    $fisicos = mysqli_fetch_assoc($query)['Abierto'];

    $sql = "SELECT COUNT(*) AS Abierto FROM det_planifica_inventario WHERE status = 'A'";
    $query = mysqli_query($conn, $sql);
    $ciclicos = mysqli_fetch_assoc($query)['Abierto'];

    $abiertos = $fisicos + $ciclicos;

    echo json_encode(array(
        "abiertos"  => $abiertos
    ));
}

if($_POST['action'] === 'guardarUsuarioSup')
{
    echo json_encode(
        array(
            "success" => $ga->saveUsers(intval($_POST['id']), $_POST['usuario'])
        )
    );
}

if($_POST['action'] === 'guardarUsuarioSup1')
{
    echo json_encode(array("success" => $ga->saveUserss(intval($_POST['id']), $_POST['usuario'])));
}

if( $_POST['action'] == 'add') 
{
    $ga->saveInventario($_POST);
    $ga->saveConteo($_POST);
    $arr = array("success"=>true);
    echo json_encode($arr);
}

if( $_POST['action'] == 'edit' ) 
{
    $ga->actualizarAlmacen($_POST);
    $arr = array("success" => $success);
    echo json_encode($arr);
}

if( $_POST['action'] == 'exists' ) 
{
    $clave=$ga->exist($_POST["clave_almacen"]);
    if($clave==true)
        $success = true;
    else 
        $success= false;

    $arr = array("success"=>$success);
    echo json_encode($arr);
}

if( $_POST['action'] == 'delete' ) 
{
    $ga->borrarAlmacen($_POST);
    $ga->cve_almac = $_POST["cve_almac"];
    $ga->__get("cve_almac");	
    $arr = array("success" => true,);
    echo json_encode($arr);
}

if( $_POST['action'] == 'load' ) 
{
    $ga->__get("ID_Ruta");
    $arr = array(
        "success" => true,
        "ID_Ruta" => $ga->data->ID_Ruta,
        "cve_ruta" => $ga->data->cve_ruta,
        "descripcion" => $ga->data->descripcion,
        "status" => $ga->data->status,
        "cve_cia" => $ga->data->cve_cia
    );
    $arr = array_merge($arr);
    echo json_encode($arr);
}

/**
 * Obtiene los datos del Grid principal de la vista
 * @author Brayan Rincon <brayan262@gmail.com>
 */
if(isset($_POST) AND ! empty($_POST) AND $_POST['action'] == 'cargarDeatllesInventarioFisico')
{
    $id_inventario = $_POST["id_inventario"];
    $conteo = isset($_POST['conteo']) ? $_POST['conteo'] : false;

    $data = $ga->loadDetalle($id_inventario, 0, 100000, $conteo);

    foreach($data as $value)
    {
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

/**
 * Obtiene los datos del Grid principal de la vista
 * @author Brayan Rincon <brayan262@gmail.com>
 */
if($_POST['action'] === 'guardarStockFisico')
{
    $stocks = $_POST['stocks'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    foreach ($stocks as $key => $value) 
    {
        list($tipo, $ubicacion, $articulo, $inventario, $conteo) = explode('|',$value['id']);
        $conteo_mas = $conteo + 1;
        $cantidad = $value['value'];
        $sql = "
            UPDATE  t_invpiezas 
                SET Cantidad = {$cantidad}
                WHERE ID_Inventario = {$inventario}
                AND NConteo = {$conteo}
                AND cve_articulo = '{$articulo}'
                AND idy_ubica = '{$ubicacion}'; 
        ";
        $query = mysqli_query($conn, $sql);

        $sql2 = "SELECT SUM(ABS(ExistenciaTeorica - Cantidad)) AS diferencia, NConteo, ID_Inventario FROM t_invpiezas WHERE ID_Inventario = {$inventario} AND NConteo > 0  GROUP BY ID_Inventario, NConteo;";
        $query2 = mysqli_query($conn, $sql2);
        if($query2->num_rows > 0)
        {
            $data = mysqli_fetch_all($query2, MYSQLI_ASSOC);
            $sql3 = "UPDATE th_inventario SET Status = 'T' WHERE ID_Inventario = {$inventario};
            UPDATE t_invpiezas SET ExistenciaTeorica = Cantidad WHERE ID_Inventario = {$inventario} AND NConteo = (SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = {$inventario});";
            $s3 = false;
            if(count($data) === 1)
            {
                if(intval($data[0]['diferencia']) === 0)
                {
                    $s3 = true;
                }
            }
            else
            {
                $lastIndex = count($data) -1;
                if(intval($data[$lastIndex]['diferencia']) === 0)
                {
                    $s3 = true;
                }
                else
                {
                    $diferencias = array();
                    foreach($data as $diff)
                    {
                        $diferencias [] = intval($diff['diferencia']);
                    }
                    $diferencias = array_count_values($diferencias);
                    arsort($diferencias);
                    if(array_shift($diferencias) > 1)
                    {
                        $s3 = true;
                    }
                }
            } 
            if($s3)
            {
                mysqli_multi_query($conn, $sql3);
            }
        }
    }
    echo json_encode(array("success"   =>  $query,'status' => 200));
    exit;
}

if( $_POST['action'] == 'loadDetalle' )
{
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $id = $_POST["ID_Inventario"];
    $criterio = $_POST["criterio"];
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)
    $conteo = isset($_POST['conteo']) ? $_POST['conteo'] : false;
    {
        $sidx =1;
    }
    if( $count >0 ) 
    {
        $total_pages = ceil($count/$limit);
    } 
    else 
    {
        $total_pages = 0;
    } 
    if ($page > $total_pages)
    {
        $page=$total_pages;
    }
    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $data = $ga->loadDetalle($id, $start, $limit, $conteo, $criterio);
    $count = $ga->loadDetalleCount($id, $conteo);
    $i = 0;
    foreach($data as $art)
    {
        $responce->rows[$i]['id'] = $art['id'];
        $responce->rows[$i]['cell']= array(
            ($i+1), 
            $art["ubicacion"],
            $art["clave"], 
            $art["descripcion"], 
            $art["lote"], 
            $art["caducidad"], 
            $art["serie"], 
            $art["stockTeorico"], 
            $art["stockFisico"], 
            $art["diferencia"], 
            $art["conteo"], 
            $art["usuario"], 
            $art["unidad_medida"]
        );
        $i++;
    }
    echo json_encode($responce);
}

if( $_POST['action'] == 'traerProductos' )
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $almacen = explode("|",$_POST["almacen"]);
    $id_almacenP = $almacen[1];
    $clave_almacenP = $almacen[0];
    $rack = $_POST["rack"];

    $pasillo    = $_POST["pasillo"];
    $rackk      = $_POST["rackk"];
    $nivel      = $_POST["nivel"];
    $seccion    = $_POST["seccion"];
    $ubicacion  = $_POST["ubicacion"];

    $and = '';
    $i=0;
  
    if($almacen != '')
    {
        $and .= ' and e.cve_almac = '.$id_almacenP;
    }
    if($rack != ''){ $and .= ' and u.idy_ubica = "'.$rack.'"'; }

    if($pasillo != ''){ $and .= ' and u.cve_pasillo = "'.$pasillo.'" '; }
    if($rackk != ''){ $and .= ' and u.cve_rack = "'.$rackk.'" '; }
    if($nivel != ''){ $and .= ' and u.cve_nivel = "'.$nivel.'" '; }
    if($seccion != ''){ $and .= ' and u.Seccion = "'.$seccion.'" '; }
    if($ubicacion != ''){ $and .= ' and u.Ubicacion = "'.$ubicacion.'" '; }

    $sql = "
        SELECT e.cve_articulo, a.des_articulo 
        FROM V_ExistenciaGral e
            LEFT JOIN c_ubicacion u on u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_articulo a on e.cve_articulo = a.cve_articulo
        where 1
            ".$and." 
        GROUP BY cve_articulo";
    $query = mysqli_query($conn, $sql);
  
    while ($row = mysqli_fetch_array($query)) 
    {
        $row=array_map('utf8_encode',$row);
        $responce->productos[$i]=array($row['cve_articulo'], $row['des_articulo']);
        $i++;
    }
    $responce->success = true;
    $responce->query = $sql;
    echo json_encode($responce);
}

function ubicacion_en_inventario($ubicacion)
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    $gral_ubica = str_replace(" ","",explode('|',$ubicacion));
    $codigo = $gral_ubica[0];
    $es_area = $gral_ubica[1]; //si es true es area si es false es ubicacion
    $sql = "";
    if($es_area == "false")
    {
        $sql = "
            SELECT * FROM th_inventario
            inner join t_ubicacioninventario on t_ubicacioninventario.ID_Inventario = th_inventario.ID_Inventario
            WHERE th_inventario.`Status`='A'
            and t_ubicacioninventario.idy_ubica = '{$codigo}';
        ";
    }
    if($es_area == "true")
    {
        $sql = "
            SELECT * FROM th_inventario
            inner join t_ubicacioninventario on t_ubicacioninventario.ID_Inventario = th_inventario.ID_Inventario
            WHERE th_inventario.`Status`='A'
            and t_ubicacioninventario.cve_ubicacion = '{$codigo}';
        ";
    }
    $query = mysqli_query($conn, $sql);
  
    if($query->num_rows > 0)
    {
      return true;// si existe un igual
    }
    else
    {
      return false;// no existe un igual
    }
}


if( $_POST['action'] == 'guardarInventario' )
{
    $success = "error";
    $title = "Error";
    $txt = "No se han seleccionado ubicaciones";
    $fecha = date('Y-m-d',strtotime($_POST['fecha']));
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $ubicaciones = $_POST["ubicaciones"];//[0];
    //if(!empty($_POST["ubicaciones"]))
    //if($ubicaciones)
    //{
        $se_puede_inventariar = true;
        foreach($ubicaciones as $ubicacion)
        {
            if(ubicacion_en_inventario($ubicacion) == true)
            {
                $se_puede_inventariar = false;
                break;
            }
        }

        if($se_puede_inventariar)
        {
            $success = "success";
            $title = "Inventario planificado";
            $txt = "El inventario ha sido enviado al administrador de inventario";

            $ga->saveInventario($fecha,'A',$_POST["almacen"],$_POST["zona"]);
            $ga->saveConteo(0);
            foreach($ubicaciones as $ubicacion)
            {
                $ga->saveUbicacion($ubicacion);
                  $ubicaciones = explode("|", $ubicacion[0]);
                  $ubicacion = trim($ubicaciones[0]);
                  $area = trim($ubicaciones[1]);
                $ga->saveExistencia(false, false, $ubicacion);
            }
            

        }
        else
        {
            $txt = "No se pudo planificar el inventario, debido a que existe una planificación abierta en una de las ubicaciones";
        }

    //}

    $arr = array(
        "success" => $success,
        "title" => $title,
        "txt" => $txt
    );
    echo json_encode($arr);
}
/*
if($_POST['action'] === 'guardarStockFisico')
{
    $stocks = $_POST['stocks'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    foreach ($stocks as $key => $value) 
    {
        list($tipo, $ubicacion, $articulo, $inventario, $conteo) = explode('|',$value['id']);
        $conteo_mas = $conteo + 1;

        $sql = "UPDATE  t_invpiezas 
                SET Cantidad = {$stock_fisico}
                WHERE ID_Inventario = {$inventario}
                AND NConteo = {$conteo_mas}
                AND cve_articulo = '{$articulo}'
                AND idy_ubica = '{$ubicacion}';";
        $query = mysqli_query($conn, $sql);

        $sql2 = "SELECT SUM(ABS(ExistenciaTeorica - Cantidad)) AS diferencia, NConteo, ID_Inventario FROM t_invpiezas WHERE ID_Inventario = {$inventario} AND NConteo > 0  GROUP BY ID_Inventario, NConteo;";
        $query2 = mysqli_query($conn, $sql2);
        
        if($query2->num_rows > 0)
        {
            $data = mysqli_fetch_all($query2, MYSQLI_ASSOC);
            $sql3 = "UPDATE th_inventario SET Status = 'T' WHERE ID_Inventario = {$inventario}; UPDATE t_invpiezas SET ExistenciaTeorica = Cantidad WHERE ID_Inventario = {$inventario} AND NConteo = (SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = {$inventario});";
            $s3 = false;
            if(count($data) === 1)
            {
                if(intval($data[0]['diferencia']) === 0)
                {
                    $s3 = true;
                }
            }
            else
            {
                $lastIndex = count($data) -1;
                if(intval($data[$lastIndex]['diferencia']) === 0)
                {
                    $s3 = true;
                }
                else
                {
                    $diferencias = array();
                    foreach($data as $diff)
                    {
                        $diferencias [] = intval($diff['diferencia']);
                    }
                    $diferencias = array_count_values($diferencias);
                    arsort($diferencias);
                    if(array_shift($diferencias) > 1)
                    {
                        $s3 = true;
                    }
                }
            } 
            if($s3)
            {
                mysqli_multi_query($conn, $sql3);
            }
        }
    }
    echo json_encode(array("success"   =>  $query,'status' => 200));
    exit;
}
*/
if($_POST['action'] === 'existe_conteo_0')
{
    $id_plan = $_POST['id'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT COUNT(*) cuenta FROM t_invpiezas WHERE ID_Inventario = '{$id_plan}' AND NConteo = 0";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);

    $cuenta = $row['cuenta'];


    $sql = "SELECT NConteo FROM t_conteoinventario WHERE ID_Inventario = '{$id_plan}'";

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