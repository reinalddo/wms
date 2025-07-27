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

      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    if(empty($almacenaje) && !empty($almacen)){
        $sqlCount = "SELECT COUNT(idy_ubica) as cuenta FROM c_ubicacion u WHERE u.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '$almacen') $split;";
    }else{
        $sqlCount = "SELECT COUNT(idy_ubica) as cuenta FROM c_ubicacion u WHERE u.cve_almac = '$almacenaje' $split";
    }

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset2'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

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
                u.CodigoCSD AS BL,
                GROUP_CONCAT(DISTINCT ch.clave_contenedor SEPARATOR ',') AS clave_contenedor,
                GROUP_CONCAT(DISTINCT ch.CveLP SEPARATOR ',') AS CveLP,
                #'' AS clave_contenedor,
                #'' AS CveLP,
                u.num_alto,
                u.num_ancho,
                u.num_largo,
                u.AcomodoMixto,
                u.AreaProduccion,
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

                #IF(u.AreaProduccion = 'N', (SELECT COUNT(cve_articulo) FROM V_ExistenciaGral WHERE cve_ubicacion = u.idy_ubica), (SELECT COUNT(cve_articulo) FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = u.idy_ubica)) AS total_ubicados,
                #IF(u.AreaProduccion = 'N', (SELECT CONCAT(IFNULL(SUM(Existencia), '0'),' ') FROM V_ExistenciaGral WHERE cve_ubicacion = u.idy_ubica), (SELECT CONCAT(IFNULL(SUM(Existencia), '0'),' ') FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = u.idy_ubica)) AS existencia_total,
                IFNULL(TRUNCATE((u.num_ancho / 1000) * (u.num_alto / 1000) * (u.num_largo / 1000), 2), 0) AS volumen_m3,
                #IF(u.AreaProduccion = 'N', IFNULL(CONCAT(TRUNCATE(((SELECT SUM(V_ExistenciaGral.Existencia * c_articulo.peso) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = u.idy_ubica) * 100 / u.PesoMaximo), 4), ''), '0.00'), IFNULL(CONCAT(TRUNCATE(((SELECT SUM(V_ExistenciaGralProduccion.Existencia * c_articulo.peso) FROM V_ExistenciaGralProduccion LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGralProduccion.cve_articulo WHERE V_ExistenciaGralProduccion.cve_ubicacion = u.idy_ubica) * 100 / u.PesoMaximo), 4), ''), '0.00')) AS peso,
                #IF(u.AreaProduccion = 'N', IFNULL(CONCAT(TRUNCATE((SELECT SUM(V_ExistenciaGral.Existencia * ((c_articulo.alto/1000) * (c_articulo.fondo/1000) * (c_articulo.ancho/1000))) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = u.idy_ubica) * 100 / ((u.num_ancho/1000) * (u.num_largo/1000) * (u.num_alto/1000)), 4), ''), '0.00'), IFNULL(CONCAT(TRUNCATE((SELECT SUM(V_ExistenciaGralProduccion.Existencia * ((c_articulo.alto/1000) * (c_articulo.fondo/1000) * (c_articulo.ancho/1000))) FROM V_ExistenciaGralProduccion LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGralProduccion.cve_articulo WHERE V_ExistenciaGralProduccion.cve_ubicacion = u.idy_ubica) * 100 / ((u.num_ancho/1000) * (u.num_largo/1000) * (u.num_alto/1000)), 4), ''), '0.00')) AS volumen,

                IFNULL(u.orden_secuencia, '--') AS surtido,
                if(u.TECNOLOGIA='PTL','S','N') as Ptl,
                if(u.Tipo='L','S','N') as li,
                if(u.Tipo='R','S','N') as re,
                if(u.Tipo='Q','S','N') as cu
            FROM c_ubicacion u
            LEFT JOIN c_almacen a ON a.cve_almac = u.cve_almac
            LEFT JOIN V_ExistenciaGralProduccion ve ON ve.cve_ubicacion = u.idy_ubica AND ve.cve_almac = a.cve_almacenp
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = ve.Cve_Contenedor
    ";

    if(empty($almacenaje) && !empty($almacen)){
        //$sql .= " WHERE ve.Existencia > 0 AND u.cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '$almacen') AND  u.Activo = 1 ";
        $sql .= " WHERE ve.Existencia > 0 AND ve.cve_almac = '$almacen' AND a.cve_almacenp = '$almacen' AND  u.Activo = 1 ";
    }else{
        $sql .= " WHERE ve.Existencia > 0 AND u.cve_almac = '$almacenaje' AND u.Activo = 1 ";
    }

    if(!empty($search)){
       // $search = explode('-', $search);
        $BL = $search;
        //$rack = $search[0];
        //$seccion = $search[1];
        //$nivel = $search[2];
        //$posicion = $search[3];
        $sql .= " AND (u.CodigoCSD like '%".$BL."%') ";  //" AND (u.cve_rack like '%$rack%'";
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

    $sql .= " GROUP BY u.CodigoCSD LIMIT {$_page},{$limit};";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
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
        //$row=array_map('utf8_encode', $row);
        $row["dim"] = number_format($row['num_alto'], 2, '.', '').'  X  '.number_format($row['num_ancho'], 2, '.', '').'  X  '.number_format($row['num_largo'], 2, '.', '');
        $arr[] = $row;
        extract($row);
        if($clave_contenedor[0] == ',') {$clave_contenedor[0] = ' '; $clave_contenedor=str_replace(" ", "", $clave_contenedor);}
        if($CveLP[0] == ',') {$CveLP[0] = ' '; $CveLP=str_replace(" ", "", $CveLP);}

        $responce->rows[$i]['id']=$row['idy_ubica'];
        $responce->rows[$i]['cell']=array(
                                          "",
                                          $BL,
                                          //utf8_decode($zona_almacenaje), 
                                          $zona_almacenaje, 
                                          $clave_contenedor,
                                          $CveLP,
                                          '', //$total_ubicados,
                                          '', //$existencia_total,
                                          $PesoMax,
                                          $volumen_m3,
                                          '', //$peso,
                                          '', //$volumen,
                                          $row['dim'],
                                          $row['AreaProduccion'],
                                          $idy_ubica,
                                          //utf8_decode($row['picking']),
                                          $row['picking'],
                                          //utf8_decode($row['Ptl']),
                                          $row['Ptl'],
                                          //utf8_decode($row['li']),
                                          $row['li'],
                                          //utf8_decode($row['re']),
                                          $row['re'],
                                          //utf8_decode($row['cu']),
                                          $row['cu'],
                                          $row['AcomodoMixto']
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
  
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'utf8') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset2'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql_almacen = "SELECT id FROM c_almacenp WHERE clave = '{$almacenaje}';";
    if (!($res = mysqli_query($conn, $sql_almacen))){echo "Falló la preparación(1): (" . $sql_almacen . ") ";}
    $almacen = mysqli_fetch_array($res)['id'];

/*
    $sqlCount = "";

    if($_POST['areaProduccion'] == 'S')
    $sqlCount = "
        SELECT 
            V_ExistenciaGralProduccion.cve_articulo  
        FROM V_ExistenciaGralProduccion 
        LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGralProduccion.cve_articulo
        LEFT JOIN c_lotes ON c_lotes.LOTE = V_ExistenciaGralProduccion.cve_lote and c_lotes.cve_articulo = c_articulo.cve_articulo
        WHERE cve_ubicacion = '{$ubicacion}' 
            AND V_ExistenciaGralProduccion.cve_almac = '{$almacen}' 
            AND V_ExistenciaGralProduccion.Existencia IS NOT NULL 
        GROUP BY V_ExistenciaGralProduccion.cve_articulo,V_ExistenciaGralProduccion.cve_lote
    ";
    else 
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
*/
    //if(c_articulo.control_lotes = 'S',c_lotes.LOTE,'') as lote,
    //u.AreaProduccion = 'N'

    $sql = "";
    if($_POST['areaProduccion'] == 'S')
    $sql = "
        SELECT 
          IFNULL(c_proveedores.ID_Proveedor, '') as id_proveedor,
          V_ExistenciaGralProduccion.cve_almac, 
          V_ExistenciaGralProduccion.cve_ubicacion, 
          V_ExistenciaGralProduccion.cve_articulo, 
          V_ExistenciaGralProduccion.cve_lote as lote, 
          ifnull(if(c_articulo.control_lotes = 'S', date_format(if(DATE_FORMAT(c_lotes.Caducidad, '%Y-%m-%d')='0000-00-00','',c_lotes.Caducidad),'%d-%m-%Y'),''),'') as caducidad,
          if(c_articulo.control_numero_series = 'S',V_ExistenciaGralProduccion.cve_lote,'') as serie,
          ROUND(SUM(V_ExistenciaGralProduccion.Existencia)/COUNT(V_ExistenciaGralProduccion.Existencia), 2) as Existencia_Total,
          c_articulo.des_articulo as descripcion,
          V_ExistenciaGralProduccion.Cve_Contenedor as contenedor,
          IFNULL(c_proveedores.Nombre, '') as proveedor,
          IF(c_articulo.control_peso = 'S',ROUND(SUM(c_articulo.peso), 2),TRUNCATE(IFNULL(ROUND(c_articulo.peso, 2),0),2)) AS peso_unitario,
          TRUNCATE(((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000),2) as volumen_unitario,
          IF (c_articulo.control_peso = 'S',TRUNCATE((IFNULL(c_articulo.peso,0)*SUM(V_ExistenciaGralProduccion.Existencia)),2),TRUNCATE((IFNULL(c_articulo.peso,0)*SUM(V_ExistenciaGralProduccion.Existencia)),2)) AS peso_total,
          TRUNCATE((((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000)*SUM(V_ExistenciaGralProduccion.Existencia)),2)as volumen_total
        FROM V_ExistenciaGralProduccion 
        LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGralProduccion.cve_articulo
        LEFT JOIN c_lotes ON c_lotes.LOTE = V_ExistenciaGralProduccion.cve_lote and c_lotes.cve_articulo = c_articulo.cve_articulo
        LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = V_ExistenciaGralProduccion.Id_Proveedor
        WHERE cve_ubicacion = '{$ubicacion}' 
        AND V_ExistenciaGralProduccion.cve_almac = '{$almacen}' 
        AND V_ExistenciaGralProduccion.Existencia IS NOT NULL 
        AND V_ExistenciaGralProduccion.cve_articulo = c_articulo.cve_articulo AND IFNULL(V_ExistenciaGralProduccion.cve_lote, '') = IFNULL(c_lotes.Lote, '')
        GROUP BY V_ExistenciaGralProduccion.cve_articulo,V_ExistenciaGralProduccion.cve_lote, V_ExistenciaGralProduccion.Cve_Contenedor, id_proveedor
        ORDER BY descripcion
        #LIMIT {$_page},{$limit};
    ";
    else
    $sql = "
        SELECT 
        IFNULL(c_proveedores.ID_Proveedor, '') as id_proveedor,
          V_ExistenciaGral.cve_almac, 
          V_ExistenciaGral.cve_ubicacion, 
          V_ExistenciaGral.cve_articulo, 
          IFNULL(V_ExistenciaGral.cve_lote, '') AS lote, 
          ifnull(if(c_articulo.control_lotes = 'S', date_format(if(DATE_FORMAT(c_lotes.Caducidad, '%Y-%m-%d')='0000-00-00','',c_lotes.Caducidad),'%d-%m-%Y'),''),'') as caducidad,
          if(c_articulo.control_numero_series = 'S',V_ExistenciaGral.cve_lote,'') as serie,
          ROUND(SUM(V_ExistenciaGral.Existencia)/COUNT(V_ExistenciaGral.Existencia), 2) as Existencia_Total,
          c_articulo.des_articulo as descripcion,
 		  V_ExistenciaGral.Cve_Contenedor as contenedor,
          IFNULL(c_proveedores.Nombre, '') as proveedor,
          IF(c_articulo.control_peso = 'S',ROUND(SUM(c_articulo.peso), 2),TRUNCATE(IFNULL(ROUND(c_articulo.peso, 2),0),2)) AS peso_unitario,
          TRUNCATE(((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000),2) as volumen_unitario,
          IF (c_articulo.control_peso = 'S',TRUNCATE((IFNULL(c_articulo.peso,0)*SUM(V_ExistenciaGral.Existencia)),2),TRUNCATE((IFNULL(c_articulo.peso,0)*SUM(V_ExistenciaGral.Existencia)),2)) AS peso_total,
          TRUNCATE((((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000)*SUM(V_ExistenciaGral.Existencia)),2)as volumen_total
        FROM V_ExistenciaGral 
        LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo
        LEFT JOIN c_lotes ON c_lotes.LOTE = V_ExistenciaGral.cve_lote and c_lotes.cve_articulo = c_articulo.cve_articulo
        LEFT JOIN c_serie ON c_serie.numero_serie = V_ExistenciaGral.cve_lote AND c_serie.cve_articulo = c_articulo.cve_articulo
        LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = V_ExistenciaGral.Id_Proveedor
        WHERE cve_ubicacion = '{$ubicacion}' 
        AND V_ExistenciaGral.cve_almac = '{$almacen}' 
        AND V_ExistenciaGral.Existencia IS NOT NULL 
        #AND V_ExistenciaGral.cve_articulo = c_articulo.cve_articulo AND (IFNULL(V_ExistenciaGral.cve_lote, '') = IFNULL(c_lotes.Lote, '') OR IFNULL(V_ExistenciaGral.cve_lote, '') = IFNULL(c_serie.numero_serie, ''))
        GROUP BY V_ExistenciaGral.cve_articulo,V_ExistenciaGral.cve_lote, V_ExistenciaGral.Cve_Contenedor, id_proveedor
        ORDER BY descripcion
    ";
//if(c_articulo.control_peso = 'S',ROUND(SUM(V_ExistenciaGral.Existencia), 2),TRUNCATE(ifnull(ROUND(c_articulo.peso, 2),0),2)) as peso_unitario,
//if (c_articulo.control_peso = 'S',ROUND(SUM(V_ExistenciaGral.Existencia), 2),TRUNCATE((ifnull(c_articulo.peso,0)*SUM(V_ExistenciaGral.Existencia)),2)) as peso_total,

    if (!($res_count = mysqli_query($conn, $sql))) {
        echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res_count);


    $sql .= "LIMIT {$_page},{$limit}";
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
    $responce->sql = $sql;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        //$row=array_map('utf8_encode', $row);
        $arr[] = $row;
        extract($row);
        $responce->rows[$i]['id']=$row['idy_ubica'];
        $responce->rows[$i]['cell']=array('',$row['cve_articulo'], $row['descripcion'], $row['contenedor'], $row['lote'], $row['caducidad'],$row['serie'],$row['Existencia_Total'],$row['peso_unitario'],$row['volumen_unitario'], $row['peso_total'], $row['volumen_total'], $row['proveedor'],$row['id_proveedor'],$row['Existencia_Total']);
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

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'actualizar_existencias'){
    $almacen = $_POST['almacen'];
    $ubicacion = $_POST['ubicacion'];
    $existencia = $_POST['articulos'];
    $idUser = $_POST['idUser'];
	$fecha = $_POST['fecha'];
	$motivos = $_POST['motivos'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql ="
                SELECT
                        id
                FROM c_almacenp
                WHERE clave = '".$almacen."';
        ";
        //echo $sql;
        if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ";}
        $id_almacen = mysqli_fetch_array($res)['id'];

		$sql ="
				SELECT
						idy_ubica
				FROM c_ubicacion
				WHERE CodigoCSD = '".$ubicacion."' AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = {$id_almacen});
		";
        //echo $sql;
		if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
		$id_ubica =  mysqli_fetch_array($res)['idy_ubica'];

		$sql ="
				SELECT
						cve_usuario
				FROM c_usuario
				WHERE id_user = '".$idUser."';
		";
        //echo $sql;
		if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}
		$cve_usuario = mysqli_fetch_array($res)['cve_usuario'];

		$ajuste = false;
	  //echo var_dump($sql);
	  //die();

		foreach($existencia as $key=>$valor)
		{
            $articulo_ajuste = $valor["clave"];
            $cajas = false;
				$sql2 = "
						SELECT 
								IFNULL(costo, 0) as costo,
								IFNULL(costoPromedio, 0) as costoPromedio
						FROM c_articulo
						WHERE cve_articulo = '$articulo_ajuste'
						";
                        //echo $sql2;
						if (!($res2 = mysqli_query($conn, $sql2))){echo "Falló la preparación(costo): (" . mysqli_error($conn) . ") ";}
						$costoN = mysqli_fetch_array($res2)["costo"];
						$costoPromedioA = mysqli_fetch_array($res2)["costoPromedio"];

				if($valor["existencia"] > $costoPromedio)
				{
						$cpf = ($costoPromedioA + $costoN)/2;
				}

				if($valor["contenedor"] != "")
				{
                    $articulo_tarima = $valor["clave"];
                    $lote_tarima = $valor["lote"];
                    $id_proveedor = $valor["id_proveedor"];
						$sql2 = "
								SELECT 
										existencia
								FROM ts_existenciatarima
								WHERE cve_almac = '{$id_almacen}'
										and idy_ubica = '{$id_ubica}'
										and cve_articulo = '$articulo_tarima'
                                        and lote = '$lote_tarima'
                                        and ID_Proveedor = '$id_proveedor'
                                LIMIT 1;
						";
                        //echo $sql2;

						if (!($res2 = mysqli_query($conn, $sql2))){echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";}
						$existenciaactual = mysqli_fetch_array($res2)["existencia"];
				}
				else
				{
						$sql2 = "
								SELECT 
										existencia
								FROM ts_existenciapiezas
								WHERE cve_almac = '".$id_almacen."'
										and idy_ubica = '".$id_ubica."'
										and cve_articulo = '".$valor["clave"]."'
                                        and cve_lote = '".$valor["lote"]."'
                                        and ID_Proveedor = '".$valor["id_proveedor"]."'
                                LIMIT 1;
						";
                        //echo $sql2;
						if (!($res2 = mysqli_query($conn, $sql2))){echo "Falló la preparación(5): (" . mysqli_error($conn) . ") ";}
						$existenciaactual = mysqli_fetch_array($res2)["existencia"];

                        if(!$existenciaactual)
                        {
                            $cajas = true;
                            $sql2 = "
                                    SELECT 
                                            Existencia
                                    FROM ts_existenciacajas
                                    WHERE cve_almac = '".$id_almacen."'
                                            and idy_ubica = '".$id_ubica."'
                                            and cve_articulo = '".$valor["clave"]."'
                                            and cve_lote = '".$valor["lote"]."'
                                    LIMIT 1;
                            ";
                            //echo $sql2;
                            if (!($res2 = mysqli_query($conn, $sql2))){echo "Falló la preparación(5): (" . mysqli_error($conn) . ") ";}
                            $existenciaactual = mysqli_fetch_array($res2)["existencia"];
                        }
				}

				//if($existenciaactual != $valor["existencia"])
				{
                        $sql_kardex = "";
						if($valor["contenedor"] != "")
						{
								$ajuste = true;

								$sql = "
										UPDATE ts_existenciatarima
										SET existencia = '".$valor["existencia"]."'
										WHERE cve_almac = '".$id_almacen."'
												and idy_ubica = '".$id_ubica."'
												and cve_articulo = '".$valor["clave"]."'
                                                and lote = '".$valor["lote"]."' 
                                                and ID_Proveedor = '".$valor["id_proveedor"]."'
                                                and ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '".$valor["contenedor"]."');
								";

                                //echo $sql;

								if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(6): (" . mysqli_error($conn) . ") ".$sql;}

                                $sql_kardex =" 
                                        INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso, Id_Motivo)
                                        VALUES(
                                                '".$valor["clave"]."',
                                                '".$valor["lote"]."',
                                                now(),
                                                '".$id_ubica."',
                                                (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '".$valor["contenedor"]."'),
                                                '".abs($existenciaactual - $valor["existencia"])."',
                                                '".(($existenciaactual > $valor["existencia"])?10:9)."',
                                                '".$cve_usuario."',
                                                '".$id_almacen."',
                                                '1',
                                                now(),
                                                '".$motivos."'
                                        );
                                ";
                                if (!($res = mysqli_query($conn, $sql_kardex))){echo "Falló la preparación(8): (" . mysqli_error($conn) . ") ";}

                                $sql_kardex =" 
                                        INSERT INTO t_MovCharolas (id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, id_TipoMovimiento, Cve_Usuario, Status)
                                        VALUES(
                                                (SELECT MAX(id) FROM t_cardex),
                                                (SELECT clave FROM c_almacenp WHERE id = '".$id_almacen."'),
                                                (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '".$valor["contenedor"]."'),
                                                now(),
                                                '".$id_ubica."',
                                                (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '".$valor["contenedor"]."'),
                                                '".(($existenciaactual > $valor["existencia"])?10:9)."',
                                                '".$cve_usuario."',
                                                'I'
                                        );
                                ";
                                if (!($res = mysqli_query($conn, $sql_kardex))){echo "Falló la preparación(10): (" . mysqli_error($conn) . ") ";}


						}
						else
						{
								$ajuste = true;

                                if($cajas)
                                    $sql = "
                                        UPDATE ts_existenciacajas
                                        SET Existencia = '".$valor["existencia"]."'
                                        WHERE cve_almac = '".$id_almacen."'
                                                and idy_ubica = '".$id_ubica."'
                                                and cve_articulo = '".$valor["clave"]."'
                                                and cve_lote = '".$valor["lote"]."';";
                                else
    								$sql = "
    										UPDATE ts_existenciapiezas
    										SET Existencia = '".$valor["existencia"]."'
    										WHERE cve_almac = '".$id_almacen."'
    												and idy_ubica = '".$id_ubica."'
    												and cve_articulo = '".$valor["clave"]."'
    												and cve_lote = '".$valor["lote"]."'
                                                    and ID_Proveedor = '".$valor["id_proveedor"]."'
                                                    ;
								    ";

                                $sql_kardex =" 
                                        INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso, Id_Motivo)
                                        VALUES(
                                                '".$valor["clave"]."',
                                                '".$valor["lote"]."',
                                                now(),
                                                '".$id_ubica."',
                                                '".$id_ubica."',
                                                '".abs($existenciaactual - $valor["existencia"])."',
                                                '".(($existenciaactual > $valor["existencia"])?10:9)."',
                                                '".$cve_usuario."',
                                                '".$id_almacen."',
                                                '1',
                                                now(),
                                                '".$motivos."'
                                        );
                                ";
                                if (!($res = mysqli_query($conn, $sql_kardex))){echo "Falló la preparación(8): (" . mysqli_error($conn) . ") ";}

                                //echo $sql;
                                /*
                                ?>
                                <script type="text/javascript">
                                     $(function($){
                                        console.log('<?php echo $sql; ?>');
                                    });
                                </script>
                                <?php 
                                */
								if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(7): (" . mysqli_error($conn) . ") ";}
						}

                //echo $sql;
				$existencia[$key]["ajuste"] = 1;
				$existencia[$key]["actual"] = $existenciaactual;
				}
		}

		if($ajuste)
		{
				$sql1 ="
						SELECT
								count(fol_folio) as conteo
						FROM th_ajusteexist
						WHERE MONTH(fec_ajuste) = MONTH(NOW())
				";
                //echo $sql1;
				if (!($res = mysqli_query($conn, $sql1))){echo "Falló la preparación(folio): (" . mysqli_error($conn) . ") ";}
				$nu = mysqli_fetch_array($res)["conteo"]++;
				$num = str_pad($nu,3,0,STR_PAD_LEFT);
				$nume = $fecha.$num;
				$folio =  'AD'.$nume;
				
				$sql = "
						INSERT INTO th_ajusteexist 
						VALUES(
								'$folio',
								'".$id_almacen."',
								now(),
								'$idUser',
								'',
								'1'
						);
				";
                //echo $sql;
				if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(9): (" . mysqli_error($conn) . ") ";}

				foreach($existencia as $key=>$valor)
				{
						if($valor["ajuste"] == 1)
						{
								$sql=" 
										INSERT INTO td_ajusteexist 
										VALUES(
												'$folio',
												'".$id_almacen."',
												'$id_ubica',
												'".$valor["clave"]."',
												'".$valor["lote"]."',
												'".$valor["actual"]."',
												'".$valor["existencia"]."',
												'$cpf',
												'$motivos',
												'A',
												'".$valor["contenedor"]."'
										) ON DUPLICATE KEY UPDATE num_cantant='".$valor["existencia"]."';
								";
                                //echo $sql;
								if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(13): (" . mysqli_error($conn) . ") ";}
						}
				}
  	}

                $sql="DELETE FROM ts_existenciapiezas WHERE existencia <= 0";
                if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(8): (" . mysqli_error($conn) . ") ";}

                $sql="DELETE FROM ts_existenciatarima WHERE existencia <= 0";
                if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(8): (" . mysqli_error($conn) . ") ";}
	$responce->success = true;
	echo json_encode($responce);
}

if($_POST['action'] == "traermotivos")
{
    $sql = "
     SELECT
          c_motivo.id,
          c_motivo.Tipo_Cat,
          c_motivo.Des_Motivo as descri
     FROM c_motivo
     WHERE c_motivo.Tipo_Cat = 'A'
   ";
   $res = getArraySQL($sql);
   $result = array(
     "sql" => $res,
   );
  
   echo json_encode($result);exit;
}
