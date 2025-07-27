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
    $split = "";
  
  if(isset($_POST['tipo'])){
    if(!empty($_POST['tipo']))
    {
      //echo var_dump($_POST);
      if($_POST['tipo'] == "L" || $_POST['tipo'] == "R" || $_POST['tipo'] == "Q")
        $split.= " and u.Tipo='".$_POST['tipo']."'";
      if($_POST['tipo'] == "Picking")
        $split.= " and u.picking = 'S'";
      if($_POST['tipo'] == "PTL")
        $split.= " and u.TECNOLOGIA = '".$_POST['tipo']."'";
      if($_POST['tipo'] == "Mixto")
        $split.= " and u.AcomodoMixto = 'S'";
      if($_POST['tipo'] == "Produccion")
        $split.= " and c_ubicacion.AreaProduccion = 'S'";
    }
  }

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
                u.PesoMaximo as PesoMax,
                a.des_almac as zona_almacenaje,
                u.cve_pasillo AS pasillo,
                u.cve_rack AS rack,
                u.cve_nivel AS nivel,
                u.Seccion AS seccion,
                u.Ubicacion AS posicion,
                u.codigoCSD AS BL,
                u.num_alto,
                u.num_ancho,
                u.num_largo,
                u.AcomodoMixto,
                u.picking,
                u.TECNOLOGIA,
                (
                    CASE
                        WHEN u.Tipo = 'L' THEN 'Libre'
                        WHEN u.Tipo = 'R' THEN 'Restringida'
                        WHEN u.Tipo = 'Q' THEN 'Cuarentena'
                        ELSE '--'
                    END
                ) AS tipo_ubicacion,
                (SELECT COUNT(distinct(cve_articulo)) FROM V_ExistenciaGral WHERE cve_ubicacion = u.idy_ubica) as total_ubicados,
                (SELECT CONCAT(IFNULL(SUM(Existencia), '0'),' ') FROM V_ExistenciaGral WHERE cve_ubicacion = u.idy_ubica) as existencia_total,
                IFNULL(TRUNCATE((u.num_ancho / 1000) * (u.num_alto / 1000) * (u.num_largo / 1000), 2), 0) as volumen_m3,
                IFNULL(CONCAT(TRUNCATE(((SELECT SUM(V_ExistenciaGral.Existencia * c_articulo.peso) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = u.idy_ubica) * 100 / u.PesoMaximo), 4), ''), '0.00') as peso,
                IFNULL(CONCAT(TRUNCATE((SELECT SUM(V_ExistenciaGral.Existencia * ((c_articulo.alto/1000) * (c_articulo.fondo/1000) * (c_articulo.ancho/1000))) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = u.idy_ubica) * 100 / ((u.num_ancho/1000) * (u.num_largo/1000) * (u.num_alto/1000)), 4), ''), '0.00') as volumen,
                IFNULL(u.orden_secuencia, '--') AS surtido,
                if(u.TECNOLOGIA='PTL','S','N') as Ptl,
                if(u.Tipo='L','S','N') as li,
                if(u.Tipo='R','S','N') as re,
                if(u.Tipo='Q','S','N') as cu
            FROM c_ubicacion u
            LEFT JOIN c_almacen a ON a.cve_almac = u.cve_almac
    ";

    if(empty($almacenaje) && !empty($almacen)){
        $sql .= " WHERE u.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '$almacen')";
    }else{
        $sql .= " WHERE u.cve_almac = '$almacenaje'";
    }

    if(!empty($search)){
       // $search = explode('-', $search);
        $BL = $search;
        //$rack = $search[0];
        //$seccion = $search[1];
        //$nivel = $search[2];
        //$posicion = $search[3];
        $sql .= "AND (u.codigoCSD like '%".$BL."%')";  //" AND (u.cve_rack like '%$rack%'";
        /*if($seccion){
            $sql .= " AND u.Seccion like '%$seccion%'";
        }
        if($nivel){
            $sql .= " AND u.cve_nivel like '%$nivel%'";
        }
        if($posicion){
            $sql .= " AND u.Ubicacion like '%$posicion%'";
        }*/
//        $sql .= ")";
    }
    $sql.= $split;

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
    $responce->query = $sql;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $row["dim"] = number_format($row['num_alto'], 2, '.', '').'  X  '.number_format($row['num_ancho'], 2, '.', '').'  X  '.number_format($row['num_largo'], 2, '.', '');
        $arr[] = $row;
        extract($row);

        $responce->rows[$i]['id']=$row['idy_ubica'];
        $responce->rows[$i]['cell']=array(
                                          "",
                                          $BL,
                                          $zona_almacenaje, 
                                          $total_ubicados,
                                          $existencia_total,
                                          $PesoMax,
                                          $volumen_m3,
                                          $peso,
                                          $volumen,
                                          $row['dim'],
                                          utf8_encode($row['picking']),
                                          utf8_encode($row['Ptl']),
                                          utf8_encode($row['li']),
                                          utf8_encode($row['re']),
                                          utf8_encode($row['cu']),
                                          $row['AcomodoMixto'],
                                          $idy_ubica
                                        );
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
    if (intval($page)>0) $_page = ($page-1)*$limit;

    $ubicacion = $_POST['ubicacion'];
    $almacenaje = $_POST['almacenaje'];

    if(!$sidx) $sidx =1;
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  
    $sql_almacen = "SELECT id FROM c_almacenp WHERE clave = '{$almacenaje}';";
    if (!($res = mysqli_query($conn, $sql_almacen))){echo "Falló la preparación(1): (" . $sql_almacen . ") ";}
    $almacen = mysqli_fetch_array($res)['id'];

    $sqlCount = "
        SELECT 
            V_ExistenciaGral.cve_articulo  
        FROM V_ExistenciaGral 
        LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo
        LEFT JOIN c_lotes ON c_lotes.LOTE = V_ExistenciaGral.cve_lote and c_lotes.cve_articulo = c_articulo.cve_articulo
        WHERE cve_ubicacion = '{$ubicacion}' 
            AND V_ExistenciaGral.cve_almac = '{$almacen}' 
            AND V_ExistenciaGral.Existencia IS NOT NULL 
        GROUP BY V_ExistenciaGral.cve_articulo,V_ExistenciaGral.cve_lote
    ";
    if (!($res = mysqli_query($conn, $sqlCount))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $dt = mysqli_fetch_all($res);
    $count = (count($dt));

    $sql = "
        SELECT 
          V_ExistenciaGral.cve_almac, 
          V_ExistenciaGral.cve_ubicacion, 
          V_ExistenciaGral.cve_articulo, 
          if(c_articulo.control_lotes = 'S',c_lotes.LOTE,'') as lote,
          ifnull(if(c_articulo.control_lotes = 'S', date_format(if(c_lotes.Caducidad='0000-00-00','',c_lotes.Caducidad),'%d-%m-%Y'),''),'') as caducidad,
          if(c_articulo.control_numero_series = 'S',c_lotes.LOTE,'') as serie,
          SUM(V_ExistenciaGral.Existencia) as Existencia_Total,
          c_articulo.des_articulo as descripcion,
          if(c_articulo.control_peso = 'S',SUM(V_ExistenciaGral.Existencia),TRUNCATE(ifnull(c_articulo.peso,0),4)) as peso_unitario,
          TRUNCATE(((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000),4) as volumen_unitario,
          if (c_articulo.control_peso = 'S',SUM(V_ExistenciaGral.Existencia),TRUNCATE((ifnull(c_articulo.peso,0)*SUM(V_ExistenciaGral.Existencia)),4)) as peso_total,
          TRUNCATE((((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000)*SUM(V_ExistenciaGral.Existencia)),4)as volumen_total
        FROM V_ExistenciaGral 
        LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo
        LEFT JOIN c_lotes ON c_lotes.LOTE = V_ExistenciaGral.cve_lote and c_lotes.cve_articulo = c_articulo.cve_articulo
        WHERE cve_ubicacion = '{$ubicacion}' 
        AND V_ExistenciaGral.cve_almac = '{$almacen}' 
        AND V_ExistenciaGral.Existencia IS NOT NULL 
        GROUP BY V_ExistenciaGral.cve_articulo,V_ExistenciaGral.cve_lote
        LIMIT {$_page},{$limit};
    ";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ";
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
        $responce->rows[$i]['cell']=array('',$row['cve_articulo'], $row['descripcion'], $row['lote'], $row['caducidad'],$row['serie'],$row['Existencia_Total'],$row['peso_unitario'],$row['volumen_unitario'], $row['peso_total'], $row['volumen_total']);
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
        $sqlOcupadas = "SELECT COUNT(DISTINCT cve_ubicacion) AS ocupadas FROM V_ExistenciaGral WHERE cve_ubicacion IN ($ids)";
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
