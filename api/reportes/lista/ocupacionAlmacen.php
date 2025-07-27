<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'loadGrid') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    $_page = 0;

    $almacen = $_POST['almacen'];
    $almacenaje = $_POST['almacenaje'];
    $search = isset($_POST['search']) ? $_POST['search'] : '';

    if(!$sidx) $sidx =1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if(empty($almacenaje) && !empty($almacen)){
        $sqlCount = "SELECT COUNT(idy_ubica) as cuenta FROM c_ubicacion WHERE cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '$almacen');";
    }else{
        $sqlCount = "SELECT COUNT(idy_ubica) as cuenta FROM c_ubicacion WHERE cve_almac = '$almacenaje'";
    }

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$_page = 0;

	if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql = "
            SELECT
                u.idy_ubica,
                a.des_almac as zona_almacenaje,
                u.cve_pasillo AS pasillo,
                u.cve_rack AS rack,
                u.cve_nivel AS nivel,
                u.Seccion AS seccion,
                u.Ubicacion AS posicion,
                u.CodigoCSD as BL,
                (
                    CASE
                        WHEN u.Tipo = 'L' THEN 'Libre'
                        WHEN u.Tipo = 'R' THEN 'Restringida'
                        WHEN u.Tipo = 'Q' THEN 'Cuarentena'
                        ELSE '--'
                    END
                ) AS tipo_ubicacion,
                (SELECT COUNT(cve_articulo) FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = u.idy_ubica) as total_ubicados,
                (SELECT CONCAT(IFNULL(SUM(Existencia), '0'),' Piezas') FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = u.idy_ubica) as existencia_total,
                IFNULL(CONCAT(TRUNCATE(((SELECT SUM(V_ExistenciaGralProduccion.Existencia * c_articulo.peso) FROM V_ExistenciaGralProduccion LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGralProduccion.cve_articulo WHERE V_ExistenciaGralProduccion.cve_ubicacion = u.idy_ubica) * 100 / u.PesoMaximo), 2), '%'), '0.00%') as peso,
                IFNULL(CONCAT(TRUNCATE((SELECT SUM(V_ExistenciaGralProduccion.Existencia * ((c_articulo.alto/1000) * (c_articulo.fondo/1000) * (c_articulo.ancho/1000))) FROM V_ExistenciaGralProduccion LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGralProduccion.cve_articulo WHERE V_ExistenciaGralProduccion.cve_ubicacion = u.idy_ubica) * 100 / ((u.num_ancho/1000) * (u.num_largo/1000) * (u.num_alto/1000)), 2), '%'), '0.00%') as volumen,
                IFNULL(u.orden_secuencia, '--') AS surtido
            FROM c_ubicacion u
            LEFT JOIN c_almacen a ON a.cve_almac = u.cve_almac
    ";

    if(empty($almacenaje) && !empty($almacen)){
        $sql .= " WHERE u.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '$almacen')";
    }else{
        $sql .= " WHERE u.cve_almac = '$almacenaje'";
    }

    $sql .="and ((SELECT COUNT(cve_articulo) FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = u.idy_ubica)>0)";
    if(!empty($search)){
        $search = explode('-', $search);
        $rack = $search[0];
        $seccion = $search[1];
        $nivel = $search[2];
        $posicion = $search[3];
        $sql .= " AND (u.cve_rack like '%$rack%'";
        if($seccion){
            $sql .= " AND u.Seccion like '%$seccion%'";
        }
        if($nivel){
            $sql .= " AND u.cve_nivel like '%$nivel%'";
        }
        if($posicion){
            $sql .= " AND u.Ubicacion like '%$posicion%'";
        }
        $sql .= ")";
    }

    $sql .= " LIMIT {$_page},{$limit};";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        extract($row);

        $responce->rows[$i]['id']=$row['idy_ubica'];
        $responce->rows[$i]['cell']=array('',$pasillo,$rack,$nivel,$seccion,$posicion, $BL,$tipo_ubicacion,$total_ubicados,$existencia_total,$peso,$volumen,$surtido, $zona_almacenaje, $idy_ubica);
        $i++;
    }
    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'loadDetails') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    $_page = 0;

    $ubicacion = $_POST['ubicacion'];
    $almacenaje = $_POST['almacenaje'];

    if(!$sidx) $sidx =1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = "SELECT COUNT(cve_articulo) as cuenta FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = '$ubicacion'";

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$_page = 0;

	if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql = "
            SELECT
                    z.des_almac AS zona_almacenaje,
                    CONCAT(u.cve_rack,'rack-',u.Seccion,'seccion-',u.cve_nivel,'pasillo-',u.Ubicacion) as ubicacion,
                    e.cve_articulo AS clave,
                    a.des_articulo AS descripcion,
                    CONCAT(e.Existencia, ' Piezas') AS cantidad,
                    IFNULL(l.LOTE, '--') AS lote,
                    IFNULL(l.CADUCIDAD, '--') AS caducidad
            FROM V_ExistenciaGralProduccion e
            LEFT JOIN c_ubicacion u ON u.idy_ubica = '$ubicacion'
            LEFT JOIN c_almacen z ON z.cve_almac = '$almacenaje'
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = e.cve_articulo AND l.LOTE = e.cve_lote
            WHERE e.cve_ubicacion = '$ubicacion'
            LIMIT {$_page},{$limit};
    ";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        extract($row);
        $responce->rows[$i]['id']=$row['idy_ubica'];
        $responce->rows[$i]['cell']=array($zona_almacenaje,$ubicacion,$clave,$descripcion,$cantidad,$lote,$caducidad);
        $i++;
    }
    echo json_encode($responce);
}
if(isset($_POST) && !empty($_POST['action']) && $_POST['action'] === 'loadStatistics'){
    $almacen = $_POST['almacen'];
    $almacenaje = $_POST['almacenaje'];
    $total_ubicaciones = 0;
    $ocupadas = 0;
    $porcentaje_ocupadas = 0;

    if(empty($almacenaje) && !empty($almacen)){
        $sql = "SELECT idy_ubica AS id FROM c_ubicacion WHERE cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '$almacen');";
    }else{
        $sql = "SELECT idy_ubica AS id FROM c_ubicacion WHERE cve_almac = '$almacenaje'";
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $query = mysqli_query($conn,$sql);
    if($query->num_rows > 0){
        $all_ids = mysqli_fetch_all($query, MYSQLI_ASSOC);
        $total_ubicaciones = intval(count($all_ids));
        $ids = '';
        foreach($all_ids as $key => $value){
            extract($value);
            $ids .= "{$id}";
            if($key !== ($total_ubicaciones - 1) ){
                $ids .= ',';
            }
        }
        $sqlOcupadas = "SELECT COUNT(DISTINCT cve_ubicacion) AS ocupadas FROM V_ExistenciaGralProduccion WHERE cve_ubicacion IN ($ids)";
        $query = mysqli_query($conn, $sqlOcupadas);
        if($query->num_rows > 0){
            $ocupadas = intval(mysqli_fetch_row($query)[0]);
        }
    }
    if($total_ubicaciones > 0){
        $porcentaje_ocupadas = ($ocupadas * 100) / $total_ubicaciones;
    }
    $vacias = $total_ubicaciones - $ocupadas;
    echo json_encode(array(
        'total'                 => $total_ubicaciones,
        'porcentajeocupadas'    => number_format($porcentaje_ocupadas, 2, ',', '.'),
        'vacias'                => $vacias
    ));
}
