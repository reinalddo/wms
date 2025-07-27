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

if($_POST['action'] === 'EliminarUbicacionesVaciasConteo0')
{
    $ga::EliminarUbicacionesVaciasConteo0($_POST['id_plan']);
}

if($_POST['action'] === 'printReport_fisico_ParaLlenado')
{
    $ga::printReport_fisico_ParaLlenado($_POST['id'], $_POST['status']);
}

if($_POST['action'] === 'ReporteConsolidadoFisico')
{
    $ga::ReporteConsolidadoFisico($_POST['id'], $_POST['status'], $_POST['comp'], $_POST['fecha_inv'], $_POST['diferencia_inv'], $_POST['rack'], $_POST['tipo_inv']);
}

if($_POST['action'] === 'ReporteIndividualFisico')
{
    $ga::ReporteIndividualFisico($_POST['id'], $_POST['status'], $_POST['comp'], $_POST['usuario_conteo'], $_POST['conteo_usuario'], $_POST['fecha_inv'], $_POST['ubicacion_inv'], $_POST['ubicacion_text_inv'], $_POST['ubicacion_rack'], $_POST['tipo_inv']);
}

if($_POST['action'] === 'ReporteConsolidadoItemFisico')
{
    $ga::ReporteConsolidadoItemFisico($_POST['id'], $_POST['status'], $_POST['comp'], $_POST['fecha_inv'], $_POST['tipo_inv']);
}

if($_POST['action'] === 'ReporteTeoricos')
{
    $ga::ReporteTeoricos($_POST['id'], $_POST['status'], $_POST['comp'], $_POST['fecha_inv'], $_POST['tipo_inv']);
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
    echo json_encode(array("success"   =>  $query, 'status' => 200));
    exit;
}

if($_POST['action'] === 'ActualizarConteoFisico')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $inventario = $_POST['id_plan'];
    $conteo = $_POST['Conteo'];
    $ubicacion = $_POST['cve_ubicacion'];
    $articulo = $_POST['clave'];
    $lote = $_POST['lote'];
    $usuario = $_POST['usuario'];
    $cantidad = $_POST['cantidad'];

    $conteo++;
/*
    $sql = "SELECT COUNT(*) as existe FROM t_invpiezas iv WHERE iv.cve_articulo = '{$articulo}' AND iv.idy_ubica = '{$ubicacion}' AND iv.cve_lote = '{$lote}' AND iv.ID_Inventario = {$inventario} AND iv.NConteo = {$conteo}";
    $query = mysqli_query($conn, $sql);
    $row_query = mysqli_fetch_assoc($query);
    $existe = $row_query['existe'];
*/
/*
    $sql = "SELECT cve_usuario FROM t_invpiezas WHERE ID_Inventario = {$inventario} AND NConteo = 0 AND idy_ubica = '{$ubicacion}' AND cve_articulo = '{$articulo}' AND cve_lote = '{$lote}'";
    $query = mysqli_query($conn, $sql);
    $row_query = mysqli_fetch_assoc($query);
    $usuario = $row_query['cve_usuario'];
*/
    $sql = "SELECT IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote), 0, 1) AS Cerrar FROM t_invpiezas inv WHERE inv.cve_articulo = '{$articulo}' AND inv.idy_ubica = '{$ubicacion}' AND inv.cve_lote = '{$lote}' AND inv.ID_Inventario = {$inventario}";
    $query = mysqli_query($conn, $sql);

    $row_query = mysqli_fetch_assoc($query);
    $cerrar = $row_query['Cerrar'];

  //  if(!$existe)
  //  {
        if($conteo <= 6 && $cerrar == 0)
        {
            $sql = "INSERT INTO t_invpiezas(ID_Inventario, NConteo, idy_ubica, cve_articulo, cve_lote, Cantidad, ExistenciaTeorica, cve_usuario, fecha, Art_Cerrado, fecha_fin) VALUES({$inventario}, {$conteo}, '{$ubicacion}', '{$articulo}', '{$lote}', {$cantidad}, (SELECT t.ExistenciaTeorica FROM t_invpiezas t WHERE t.ID_Inventario = {$inventario} AND t.NConteo = 0 AND t.cve_articulo = '{$articulo}' AND t.cve_lote = '{$lote}' AND t.idy_ubica = '{$ubicacion}'), '{$usuario}', NOW(), 0, NOW());";
            $query = mysqli_query($conn, $sql);
        }
/*
    }
    else
    {
        $sql = "
            UPDATE  t_invpiezas 
                SET Cantidad = {$cantidad}, cve_usuario = '{$usuario}'
                WHERE ID_Inventario = {$inventario}
                AND NConteo = {$conteo}
                AND cve_articulo = '{$articulo}'
                AND cve_lote = '{$lote}'
                AND idy_ubica = '{$ubicacion}'; 
        ";
        $query = mysqli_query($conn, $sql);
    }
*/
    /*
    if($cerrar == 1)
    {
        $sql = "SELECT DISTINCT c.cve_usuario, c.nombre_completo, t.NConteo FROM c_usuario c, t_invpiezas t WHERE t.ID_Inventario = 5 AND t.cve_usuario = c.cve_usuario AND c.cve_usuario IN (SELECT cve_usuario FROM t_invpiezas iv WHERE iv.ID_Inventario = {$inventario} AND iv.cve_articulo = '{$articulo}' AND iv.idy_ubica = '{$ubicacion}' AND iv.cve_lote = '{$lote}')";
        $query = mysqli_query($conn, $sql);

        $usuarios = "";
        while($row = mysqli_fetch_assoc($query))
        {
            $usuarios.= "<br>Conteo ".$row["NConteo"].": ".$row["nombre_completo"]."</b>\n";
        }
    }
    */
    echo json_encode(array("success"   =>  true, "sql" => $sql, "id_plan" => $inventario, 'status' => 200));
    exit;
}

if($_POST['action'] === 'ActualizarUsuariosFisico')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $inventario = $_POST['id_plan'];
    $ubicacion = $_POST['cve_ubicacion'];
    $articulo = $_POST['clave'];
    $lote = $_POST['lote'];
    $usuario = $_POST['usuario'];

        $sql = "
            UPDATE  t_invpiezas 
                SET cve_usuario = '{$usuario}'
                WHERE ID_Inventario = {$inventario}
                AND cve_articulo = '{$articulo}'
                AND cve_lote = '{$lote}'
                AND idy_ubica = '{$ubicacion}'; 
        ";
        $query = mysqli_query($conn, $sql);

    echo json_encode(array("success"   =>  true, "id_plan" => $inventario, 'status' => 200));
    exit;
}


if( $_POST['action'] == 'loadDetalle' )
{
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $id = $_POST["ID_Inventario"];
    //$criterio = $_POST["criterio"];
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
    //$count = $ga->loadDetalleCount($id, $conteo);
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
            $art["Cantidad_reg"], 
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
            LEFT JOIN c_almacenp al ON al.id = e.cve_almac
            LEFT JOIN th_inventario th on th.cve_almacen = al.clave AND th.Status = 'A'
            JOIN t_invpiezas tp ON tp.idy_ubica != e.cve_ubicacion and th.ID_Inventario = tp.ID_Inventario
            JOIN t_invtarima tt ON tt.idy_ubica != e.cve_ubicacion and th.ID_Inventario = tt.ID_Inventario
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

function ubicacion_en_inventario($conn, $ubicacion)
{
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

/*
    $sql = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res)['charset'];
    mysqli_set_charset($conn , $charset);
*/
    $sql_charset = "SET NAMES 'utf8mb4';";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";



    $ubicaciones = $_POST["ubicaciones"][0];

    $sql = "";$max_id_inv = 0;
    if($_POST['cuadro_bl_vacio'] == "")
    {
        $pasillo_inv   = $_POST['pasillo_inv'];
        $rackk_inv     = $_POST['rackk_inv'];
        $nivel_inv     = $_POST['nivel_inv'];
        $seccion_inv   = $_POST['seccion_inv'];
        $ubicacion_inv = $_POST['ubicacion_inv'];
        $productos_inv = $_POST['productos_inv'];
        $zona_inv      = $_POST['zona'];

        $and = "";$and2 = "";
        if($zona_inv)      {$and .= " AND cve_almac = '$zona_inv' ";$and2 .= " AND u.cve_almac = '$zona_inv' ";}
        if($pasillo_inv)   {$and .= " AND cve_pasillo = '$pasillo_inv' ";$and2 .= " AND u.cve_pasillo = '$pasillo_inv' ";}
        if($rackk_inv)     {$and .= " AND cve_rack = '$rackk_inv' ";$and2 .= " AND u.cve_rack = '$rackk_inv' ";}
        if($nivel_inv)     {$and .= " AND cve_nivel = '$nivel_inv' ";$and2 .= " AND u.cve_nivel = '$nivel_inv' ";}
        if($seccion_inv)   {$and .= " AND Seccion = '$seccion_inv' ";$and2 .= " AND u.Seccion = '$seccion_inv' ";}
        if($ubicacion_inv) {$and .= " AND Ubicacion = '$ubicacion_inv' ";$and2 .= " AND u.Ubicacion = '$ubicacion_inv' ";}
        if($productos_inv) {$and .= "";$and2 = "";}



        $sql = "SELECT CONCAT(idy_ubica, ' | ', 'false') as ubicaciones FROM c_ubicacion WHERE 1 {$and}";
        $query = mysqli_query($conn, $sql);
      
        $ubicaciones = array();
        while($row_ubicaciones = mysqli_fetch_array($query))
        {
            $ubicaciones[] = $row_ubicaciones['ubicaciones'];
        }

    }


    //$ubicaciones2 = $_POST["ubicaciones"];//[0];
    //if(!empty($_POST["ubicaciones"]))
    //if($ubicaciones)
    //{

        //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
        $se_puede_inventariar = true;
        foreach($ubicaciones as $ubicacion)
        {
            if(ubicacion_en_inventario($conn, $ubicacion) == true)
            {
                $se_puede_inventariar = false;
                break;
            }
        }


/*
    $arr = array(
        "success" => true,
        "sql" => $sql,
        "se_puede_inventariar" => $se_puede_inventariar,
        "ubicaciones" => $ubicaciones
    );
    echo json_encode($arr);

*/

        if($se_puede_inventariar)
        {

            $ga->saveInventario($fecha,'A',$_POST["almacen"],$_POST["zona"]);
            $ga->saveConteo(0);
            //$txt = "";
            $sql = "";

            if($_POST['cuadro_bl_vacio'] == "")
            {
                $sql = "SELECT MAX(ID_Inventario) as max_id_inv from th_inventario";
                if (!($res = mysqli_query($conn, $sql)))
                    echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
                $max_id_inv = mysqli_fetch_array($res)['max_id_inv'];
                
                //echo $max_id_inv."---".$and."---".$and2;
                $ga->saveUbicacionFiltros($max_id_inv, $and);
                $ga->saveExistenciaFiltros($max_id_inv, $and2);

                /*
                    $arr = array(
                        "success" => true,
                        "sql" => $sql,
                        "se_puede_inventariar" => $se_puede_inventariar,
                        "max_id_inv" => $max_id_inv,
                        "and" => $and,
                        "and2" => $and2
                    );
                    echo json_encode($arr);
                    */
            }
            else
            {
                foreach($ubicaciones as $ubicacion)
                {
                    //$txt .= " - ".$ubicacion;
                    //echo "0HERE0";
                    $ga->saveUbicacion($ubicacion);
                    //echo "2HERE2";
                      //$ubicaciones_arr = explode("|", $ubicacion);
                      //$ubicacion_val = trim($ubicaciones_arr[0]);
                      //echo $ubicacion." - ";
                      //$area = trim($ubicaciones_arr[1]);
                     $sql = $ga->saveExistencia(false, false, $ubicacion);
                }
            }


            $sql = "SELECT MAX(ID_Inventario) as ID_Inventario FROM th_inventario";
            $query2 = mysqli_query($conn, $sql);
            $idSQL = mysqli_fetch_array($query2)['ID_Inventario'];

            $max_id_inv = $idSQL;
            //$idSQL = 123;
            $success = "success";
            $title = "Inventario #$max_id_inv planificado";
            $txt = "El inventario ha sido enviado al administrador de inventario";

        }
        else
        {
            $txt = "No se pudo planificar el inventario, debido a que existe una planificación abierta en una de las ubicaciones";
        }

    //}

    $arr = array(
        "success" => $success,
        "title" => $title,
        "SQL" => $sql,
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

if($_POST['action'] === 'TraerConteos')
{
    $id_plan = $_POST['id_inventario'];
    $tipo = $_POST['tipo'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "";
    if($tipo == 'Físico')
    $sql = "SELECT DISTINCT * FROM (
            SELECT DISTINCT NConteo FROM t_invpiezas WHERE ID_Inventario = {$id_plan} AND NConteo > 0 AND Cantidad >= 0
            UNION 
            SELECT DISTINCT NConteo FROM t_invtarima WHERE ID_Inventario = {$id_plan} AND NConteo > 0 AND existencia >= 0
            ) t
            ORDER BY NConteo";
    else
    $sql = "SELECT DISTINCT * FROM (
            SELECT DISTINCT NConteo FROM t_invpiezasciclico WHERE ID_PLAN = {$id_plan} AND NConteo > 0 AND Cantidad >= 0
            UNION 
            SELECT DISTINCT NConteo FROM t_invtarimaciclico WHERE ID_PLAN = {$id_plan} AND NConteo > 0 AND existencia >= 0
            ) t
            ORDER BY NConteo";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $option = "";
    while($row = mysqli_fetch_array($res))
    {
        $option .= "<option value='".$row['NConteo']."'>".$row['NConteo']."</option>";
    }

  //mysqli_close($conn);

    echo json_encode(array(
        "conteos_option" => $option
    ));
} 

if($_POST['action'] === 'TraerRacks')
{
    $id_plan = $_POST['id_inventario'];
    $tipo    = $_POST['tipo'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //**********************************************************************************
    //                                      RACKS
    //**********************************************************************************
    $sql = "";
    if($tipo == 'Físico')
    $sql = "SELECT DISTINCT c.cve_rack AS Rack FROM c_ubicacion c, t_invpiezas t WHERE c.idy_ubica = t.idy_ubica AND t.ID_Inventario = {$id_plan} ORDER BY CAST(Rack AS DECIMAL)";
    else
    $sql = "SELECT DISTINCT c.cve_rack AS Rack FROM c_ubicacion c, t_invpiezasciclico t WHERE c.idy_ubica = t.idy_ubica AND t.ID_PLAN = {$id_plan} ORDER BY CAST(Rack AS DECIMAL)";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $option_racks = "";
    while($row = mysqli_fetch_array($res))
    {
        $option_racks .= "<option value='".$row['Rack']."'>".$row['Rack']."</option>";
    }
    //**********************************************************************************
  //mysqli_close($conn);

    echo json_encode(array(
        "option_racks" => $option_racks
    ));
} 

if($_POST['action'] === 'cambiarStatus')
{
    $id_plan = $_POST['inv'];
    $status = $_POST['status'];

    $status_nuevo = "A";
    if($status == "Abierto") $status_nuevo = "T";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $tipo    = $_POST['tipo'];

    $sql = "UPDATE det_planifica_inventario SET status = '{$status_nuevo}' WHERE ID_PLAN = {$id_plan}";
    if($tipo == 'Físico')
       $sql = "UPDATE th_inventario SET Status = '{$status_nuevo}' WHERE ID_Inventario = {$id_plan}";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    echo json_encode(array(
        "success" => true
    ));
} 

if($_POST['action'] === 'TraerUsuariosYUbicacionesConteo')
{
    $id_plan = $_POST['id_inventario'];
    $conteo = $_POST['conteo'];
    $tipo = $_POST['tipo'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //**********************************************************************************
    //                                      USUARIOS
    //**********************************************************************************
    //$sql = "SELECT DISTINCT c.cve_usuario, c.nombre_completo FROM c_usuario c, t_invpiezas t WHERE c.cve_usuario = t.cve_usuario AND t.ID_Inventario = {$id_plan} AND t.NConteo = {$conteo}";
    $sql = "";
    if($tipo == "Físico")
        $sql = "SELECT DISTINCT c.cve_usuario, c.nombre_completo FROM c_usuario c WHERE c.cve_usuario IN (
                SELECT DISTINCT cve_usuario FROM t_invpiezas WHERE ID_Inventario = {$id_plan} AND NConteo = {$conteo}
                UNION
                SELECT DISTINCT cve_usuario FROM t_invtarima WHERE ID_Inventario = {$id_plan} AND NConteo = {$conteo}
                )";
    else
        $sql = "SELECT DISTINCT c.cve_usuario, c.nombre_completo FROM c_usuario c WHERE c.cve_usuario IN (
                SELECT cve_usuario FROM t_invpiezasciclico WHERE ID_PLAN = {$id_plan} AND NConteo = {$conteo}
                UNION
                SELECT cve_usuario FROM t_invtarimaciclico WHERE ID_PLAN = {$id_plan} AND NConteo = {$conteo}
                )";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $options_usuarios = "";
    while($row = mysqli_fetch_array($res))
    {
        $options_usuarios .= "<option value='".$row['cve_usuario']."'> ( ".$row['cve_usuario']." ) - ".utf8_decode($row['nombre_completo'])."</option>";
    }
    //**********************************************************************************
    //                                      UBICACIONES
    //**********************************************************************************
    //$sql = "SELECT DISTINCT t.idy_ubica, c.CodigoCSD AS ubicacion FROM c_ubicacion c, t_invpiezas t WHERE c.idy_ubica = t.idy_ubica AND t.ID_Inventario = {$id_plan} AND t.NConteo = {$conteo} ORDER BY ubicacion";
/*
    $sql = "";
    if($tipo == "Físico")
        $sql = "SELECT DISTINCT c.idy_ubica, c.CodigoCSD AS ubicacion FROM c_ubicacion c WHERE c.idy_ubica IN (
                SELECT idy_ubica FROM t_invpiezas WHERE ID_Inventario = {$id_plan} AND NConteo = {$conteo}
                UNION
                SELECT idy_ubica FROM t_invtarima WHERE ID_Inventario = {$id_plan} AND NConteo = {$conteo}
                ) AND IFNULL(c.cve_rack, '') != '' AND c.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp IN (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almacen FROM th_inventario WHERE ID_Inventario = {$id_plan})))";
    else 
        $sql = "SELECT DISTINCT c.idy_ubica, c.CodigoCSD AS ubicacion FROM c_ubicacion c WHERE c.idy_ubica IN (
                SELECT idy_ubica FROM t_invpiezasciclico WHERE ID_PLAN = {$id_plan} AND NConteo = {$conteo}
                UNION
                SELECT idy_ubica FROM t_invtarimaciclico WHERE ID_PLAN = {$id_plan} AND NConteo = {$conteo}
                ) AND IFNULL(c.cve_rack, '') != '' AND c.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp IN (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almacen FROM th_inventario WHERE ID_Inventario = {$id_plan})))";
*/
    $sql = "";
    if($tipo == "Físico")
        $sql = "SELECT DISTINCT * FROM (
                SELECT DISTINCT c.idy_ubica, c.CodigoCSD AS ubicacion 
                FROM c_ubicacion c 
                INNER JOIN t_invpiezas tp ON tp.ID_Inventario = {$id_plan} AND tp.NConteo = {$conteo}
                WHERE c.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp IN (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almacen FROM th_inventario WHERE ID_Inventario = {$id_plan})))
                UNION 
                SELECT DISTINCT c.idy_ubica, c.CodigoCSD AS ubicacion 
                FROM c_ubicacion c 
                INNER JOIN t_invtarima tt ON tt.ID_Inventario = {$id_plan} AND tt.NConteo = {$conteo}
                WHERE c.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp IN (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almacen FROM th_inventario WHERE ID_Inventario = {$id_plan})))
                ) AS i
                ";
    else 
        $sql = "SELECT DISTINCT * FROM (
                SELECT DISTINCT c.idy_ubica, c.CodigoCSD AS ubicacion 
                FROM c_ubicacion c 
                INNER JOIN t_invpiezasciclico tp ON tp.ID_PLAN = {$id_plan} AND tp.NConteo = {$conteo}
                WHERE c.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp IN (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almacen FROM th_inventario WHERE ID_Inventario = {$id_plan})))
                UNION 
                SELECT DISTINCT c.idy_ubica, c.CodigoCSD AS ubicacion 
                FROM c_ubicacion c 
                INNER JOIN t_invtarimaciclico tt ON tt.ID_PLAN = {$id_plan} AND tt.NConteo = {$conteo}
                WHERE c.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp IN (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almacen FROM th_inventario WHERE ID_Inventario = {$id_plan})))
                ) AS i
                ";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $option_ubicaciones = "";
    
    while($row = mysqli_fetch_array($res))
    {
        $option_ubicaciones .= "<option value='".$row['idy_ubica']."'>".$row['ubicacion']."</option>";
    }
    
    //**********************************************************************************

    //**********************************************************************************
    //                                      RACKS
    //**********************************************************************************
    //$sql = "SELECT DISTINCT c.cve_rack AS Rack FROM c_ubicacion c, t_invpiezas t WHERE c.idy_ubica = t.idy_ubica AND t.ID_Inventario = {$id_plan} AND t.NConteo = {$conteo} ORDER BY ubicacion";
/*
    $sql = "";
    if($tipo == "Físico")
        $sql = "SELECT DISTINCT c.cve_rack AS Rack FROM c_ubicacion c WHERE c.idy_ubica IN (
                SELECT idy_ubica FROM t_invpiezas WHERE ID_Inventario = {$id_plan} AND NConteo = {$conteo}
                UNION
                SELECT idy_ubica FROM t_invtarima WHERE ID_Inventario = {$id_plan} AND NConteo = {$conteo}
                ) AND IFNULL(c.cve_rack, '') != '' AND c.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp IN (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almacen FROM th_inventario WHERE ID_Inventario = {$id_plan})))";
    else
        $sql = "SELECT DISTINCT c.cve_rack AS Rack FROM c_ubicacion c WHERE c.idy_ubica IN (
                SELECT idy_ubica FROM t_invpiezasciclico WHERE ID_PLAN = {$id_plan} AND NConteo = {$conteo}
                UNION
                SELECT idy_ubica FROM t_invtarimaciclico WHERE ID_PLAN = {$id_plan} AND NConteo = {$conteo}
                ) AND IFNULL(c.cve_rack, '') != '' AND c.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp IN (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almacen FROM th_inventario WHERE ID_Inventario = {$id_plan})))";
*/
    $sql = "";
    if($tipo == "Físico")
        $sql = "SELECT DISTINCT * FROM (
                SELECT DISTINCT c.cve_rack AS Rack 
                FROM c_ubicacion c 
                INNER JOIN t_invpiezas tp ON tp.ID_Inventario = {$id_plan} AND tp.NConteo = {$conteo}
                WHERE c.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp IN (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almacen FROM th_inventario WHERE ID_Inventario = {$id_plan})))
                UNION 
                SELECT DISTINCT c.cve_rack AS Rack 
                FROM c_ubicacion c 
                INNER JOIN t_invtarima tt ON tt.ID_Inventario = {$id_plan} AND tt.NConteo = {$conteo}
                WHERE c.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp IN (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almacen FROM th_inventario WHERE ID_Inventario = {$id_plan})))
                ) AS i
                ";
    else 
        $sql = "SELECT DISTINCT * FROM (
                SELECT DISTINCT c.cve_rack AS Rack 
                FROM c_ubicacion c 
                INNER JOIN t_invpiezasciclico tp ON tp.ID_PLAN = {$id_plan} AND tp.NConteo = {$conteo}
                WHERE c.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp IN (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almacen FROM th_inventario WHERE ID_Inventario = {$id_plan})))
                UNION 
                SELECT DISTINCT c.idy_ubica, c.CodigoCSD AS ubicacion 
                FROM c_ubicacion c 
                INNER JOIN t_invtarimaciclico tt ON tt.ID_PLAN = {$id_plan} AND tt.NConteo = {$conteo}
                WHERE c.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp IN (SELECT id FROM c_almacenp WHERE clave = (SELECT cve_almacen FROM th_inventario WHERE ID_Inventario = {$id_plan})))
                ) AS i
                ";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $option_racks = "";
    while($row = mysqli_fetch_array($res))
    {
        $option_racks .= "<option value='".$row['Rack']."'>".$row['Rack']."</option>";
    }
    //**********************************************************************************

    echo json_encode(array(
        "conteos_option" => $options_usuarios,
        "option_racks"   => $option_racks,
        "option_ubicaciones" => $option_ubicaciones
    ));
} 

if($_POST['action'] === 'FechaInventario')
{
    $id_plan = $_POST['id_inventario'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT DISTINCT IFNULL(DATE_FORMAT(Fecha, '%d-%m-%Y'), '') AS fecha FROM th_inventario WHERE ID_Inventario = {$id_plan}";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $fecha = $row['fecha'];

    echo json_encode(array(
        "fecha" => $fecha
    ));
} 