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
    $motivoqa = isset($_POST['motivoqa']) ? $_POST['motivoqa'] : '';
    $and = "";

    if(!$sidx) $sidx =1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    if(!empty($search)){
        $BL = $search;
        $and .= " AND u.codigoCSD like '%".$BL."%' "; 
    }
    /*
    if(!empty($almacen)){
        $almacen = $almacen;
        $and .= " AND a.cve_almacenp = $almacen ";
    }*/
    if(!empty($almacenaje)){
        $almacenaje = $almacenaje;
        $and .= " AND a.cve_almac = $almacenaje ";
    }
  
    if(!empty($motivoqa)){
        $motivoqa = $motivoqa;
        $and .= " AND m.id = $motivoqa ";
    }
  
    $sqlCount = "
        SELECT 
            COUNT(u.idy_ubica) as cuenta 
        FROM c_ubicacion u
            LEFT JOIN c_almacen a ON a.cve_almac = u.cve_almac
            LEFT JOIN ts_existenciapiezas on ts_existenciapiezas.idy_ubica = u.idy_ubica
            LEFT JOIN t_movcuarentena c ON c.Cve_Articulo = ts_existenciapiezas.cve_articulo AND c.Idy_Ubica = u.idy_ubica AND c.Cve_Lote = ts_existenciapiezas.cve_lote
            LEFT JOIN c_motivo m ON m.id = c.Id_MotivoIng
        where( u.Tipo = 'Q'  OR ts_existenciapiezas.Cuarentena = '1')
            and u.picking = 'N'
            AND a.cve_almacenp = $almacen 
            {$and}
     ";

    

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";
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
                m.Des_Motivo,
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
                (SELECT COUNT(cve_articulo) FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = u.idy_ubica AND Cuarentena = '1') as total_ubicados,
                (SELECT CONCAT(IFNULL(SUM(Cantidad), '0'),' ') FROM t_movcuarentena WHERE Idy_Ubica = u.idy_ubica) AS existencia_total,
                IFNULL(TRUNCATE((u.num_ancho / 1000) * (u.num_alto / 1000) * (u.num_largo / 1000), 2), 0) as volumen_m3,
                IFNULL(CONCAT(TRUNCATE(((SELECT SUM(V_ExistenciaGralProduccion.Existencia * c_articulo.peso) FROM V_ExistenciaGralProduccion LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGralProduccion.cve_articulo WHERE V_ExistenciaGralProduccion.cve_ubicacion = u.idy_ubica AND Cuarentena = '1') * 100 / u.PesoMaximo), 4), ''), '0.00') as peso,
                IFNULL(CONCAT(TRUNCATE((SELECT SUM(V_ExistenciaGralProduccion.Existencia * ((c_articulo.alto/1000) * (c_articulo.fondo/1000) * (c_articulo.ancho/1000))) FROM V_ExistenciaGralProduccion LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGralProduccion.cve_articulo WHERE V_ExistenciaGralProduccion.cve_ubicacion = u.idy_ubica AND Cuarentena = '1') * 100 / ((u.num_ancho/1000) * (u.num_largo/1000) * (u.num_alto/1000)), 4), ''), '0.00') as volumen,
                IFNULL(u.orden_secuencia, '--') AS surtido,
                if(u.TECNOLOGIA='PTL','S','N') as Ptl,
                if(u.Tipo='L','S','N') as li,
                if(u.Tipo='R','S','N') as re,
                if(u.Tipo='Q','S','N') as cu
            FROM c_ubicacion u
		    LEFT JOIN c_almacen a ON a.cve_almac = u.cve_almac
		    INNER JOIN V_ExistenciaGralProduccion on V_ExistenciaGralProduccion.cve_ubicacion = u.idy_ubica
            LEFT JOIN t_movcuarentena c ON c.Cve_Articulo = V_ExistenciaGralProduccion.cve_articulo AND c.Idy_Ubica = u.idy_ubica AND c.Cve_Lote = V_ExistenciaGralProduccion.cve_lote
            LEFT JOIN c_motivo m ON m.id = c.Id_MotivoIng
            where( u.Tipo = 'Q'  OR V_ExistenciaGralProduccion.Cuarentena = '1')
            AND V_ExistenciaGralProduccion.cve_almac = $almacen 
            {$and}
            GROUP BY u.idy_ubica

            UNION 

            SELECT
               td.fol_folio AS idy_ubica,
               'Cuarentena' AS Des_Motivo,
               '' AS PesoMax,
               tu.desc_ubicacion AS zona_almacenaje,
               '' AS pasillo,
               '' AS rack,
               '' AS nivel,
               '' AS seccion,
               '' AS posicion,
               th.cve_ubicacion AS BL,
               '' AS num_alto,
               '' AS num_ancho,
               '' AS num_largo,
               '' AS AcomodoMixto,
               '' AS picking,
               '' AS TECNOLOGIA,
               'RTM' AS tipo_ubicacion,
               SUM(d.Cantidad) AS total_ubicados,
               '' AS existencia_total,
               '' AS volumen_m3,
               '' AS peso,
               '' AS volumen,
               '' AS surtido,
               '' AS Ptl,
               '' AS li,
               '' AS re,
               '' AS cu
            FROM td_entalmacen td
            LEFT JOIN th_entalmacen th ON th.Fol_Folio = td.fol_folio
            LEFT JOIN Descarga d ON d.Folio = th.Fact_Prov AND d.Articulo = td.cve_articulo AND 0 #REVISAR AND = 0 MIENTRAS SE OPTIMIZA
            LEFT JOIN c_almacenp a ON a.clave = th.Cve_Almac
            LEFT JOIN tubicacionesretencion tu ON tu.cve_ubicacion = td.cve_ubicacion
            WHERE a.id = $almacen AND a.clave = th.Cve_Almac AND th.Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE STATUS = 'Q') AND td.status = 'Q' AND d.Cantidad > 0
            GROUP BY th.cve_ubicacion 
            ORDER BY BL
    
    ";


    $sql.= $split;

    $sql .= " LIMIT {$_page},{$limit};";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
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
        if($row['tipo_ubicacion'] != 'RTM')
        $row["dim"] = number_format($row['num_alto'], 2, '.', '').'  X  '.number_format($row['num_ancho'], 2, '.', '').'  X  '.number_format($row['num_largo'], 2, '.', '');
        $arr[] = $row;
        extract($row);

        $responce->rows[$i]['id']=$row['idy_ubica'];
        $responce->rows[$i]['cell']=array(
                                          "",
                                          $BL,
                                          $zona_almacenaje, 
                                          $total_ubicados,
                                          $Des_Motivo,
                                          $existencia_total,
                                          $PesoMax,
                                          $volumen_m3,
                                          $peso,
                                          $volumen,
                                          $row['dim'],
                                          $tipo_ubicacion,
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

/*
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);
  */
    $sql_almacen = "SELECT id FROM c_almacenp WHERE clave = '{$almacenaje}';";
    if (!($res = mysqli_query($conn, $sql_almacen))){echo "Falló la preparación(1): (" . $sql_almacen . ") ";}
    $almacen = mysqli_fetch_array($res)['id'];

    $sql_ubicacion = "SELECT idy_ubica FROM c_ubicacion WHERE CodigoCSD = '{$ubicacion}';";
    if (!($res = mysqli_query($conn, $sql_ubicacion))){echo "Falló la preparación(1): (" . $sql_ubicacion . ") ";}
    $idy_ubica = mysqli_fetch_array($res)['idy_ubica'];

/*
    $sqlCount = "
        SELECT 
            V_ExistenciaGral.cve_articulo  
        FROM V_ExistenciaGralProduccion V_ExistenciaGral 
        LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo
        LEFT JOIN c_lotes ON c_lotes.LOTE = V_ExistenciaGral.cve_lote and c_lotes.cve_articulo = c_articulo.cve_articulo
        #, t_movcuarentena c
        WHERE cve_ubicacion = '{$idy_ubica}' #AND c.Idy_Ubica = V_ExistenciaGral.cve_ubicacion
            AND V_ExistenciaGral.cve_almac = '{$almacen}' 
            AND V_ExistenciaGral.Existencia IS NOT NULL 
        GROUP BY V_ExistenciaGral.cve_articulo,V_ExistenciaGral.cve_lote
    ";
    if (!($res = mysqli_query($conn, $sqlCount))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $dt = mysqli_fetch_all($res);
    $count = (count($dt));
*/
    $diaoperativo = $_POST['diaoperativo'];
    $SQLDiaO = "";
    if($diaoperativo)
    {
        $SQLDiaO = " AND d.Diao = $diaoperativo ";
    }

    $sql = "
        SELECT 
          CONVERT(V_ExistenciaGral.cve_almac USING utf8) as cve_almac, 
          CONVERT(V_ExistenciaGral.cve_ubicacion USING utf8) as cve_ubicacion, 
          CONVERT(V_ExistenciaGral.cve_articulo USING utf8) as cve_articulo,
          CONVERT(V_ExistenciaGral.Cve_Contenedor USING utf8) as Cve_Contenedor,
          CONVERT('' USING utf8mb4) as val,
          CONVERT('' USING utf8mb4) as diao,
          CONVERT(if(c_articulo.control_lotes = 'S',c_lotes.LOTE,'') USING utf8mb4) as lote,
          CONVERT(if(c_articulo.control_lotes = 'S',if(DATE_FORMAT(c_lotes.Caducidad, '%Y-%m-%d') = '0000-00-00','',date_format(c_lotes.Caducidad,'%d-%m-%Y')),'')  USING utf8mb4) as caducidad,
          CONVERT(if(c_articulo.control_lotes = 'S',if(DATE_FORMAT(c_lotes.Caducidad, '%Y-%m-%d') = '0000-00-00','',c_lotes.Caducidad),'') USING utf8mb4) as caducidad_input,
		  CONVERT(if(c_articulo.control_numero_series = 'S', V_ExistenciaGral.cve_lote,'') USING utf8mb4) as serie,
          #SUM(c.Cantidad) AS Existencia_Total,
          CONVERT(IFNULL(c_articulo.control_lotes, 'N') USING utf8mb4) as control_lotes, 
          CONVERT(IFNULL(c_articulo.Caduca, 'N') USING utf8mb4) as Caduca, 
          CONVERT(IFNULL(c_articulo.control_numero_series, 'N') USING utf8mb4) as control_numero_series, 
          CONVERT(SUM(V_ExistenciaGral.Existencia) USING utf8mb4) AS Existencia_Total,
          CONVERT(c_articulo.des_articulo USING utf8mb4) as descripcion,
          CONVERT(IFNULL(IF(c_articulo.control_peso = 'S',SUM(V_ExistenciaGral.Existencia),TRUNCATE(IFNULL(c_articulo.peso,0),4)), 0) USING utf8mb4) AS peso_unitario,
          CONVERT(IFNULL(TRUNCATE(((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000),4), 0) USING utf8mb4) AS volumen_unitario,
          CONVERT(IFNULL(IF(c_articulo.control_peso = 'S',SUM(V_ExistenciaGral.Existencia),TRUNCATE((IFNULL(c_articulo.peso,0)*SUM(V_ExistenciaGral.Existencia)),4)), 0) USING utf8mb4) AS peso_total,
          CONVERT(IFNULL(TRUNCATE((((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000)*SUM(V_ExistenciaGral.Existencia)),4), 0) USING utf8mb4) AS volumen_total
        FROM V_ExistenciaGralProduccion V_ExistenciaGral 
        LEFT JOIN c_articulo ON CONVERT(c_articulo.cve_articulo, CHAR) = CONVERT(V_ExistenciaGral.cve_articulo, CHAR)
        LEFT JOIN c_lotes ON CONVERT(c_lotes.LOTE, CHAR) = CONVERT(V_ExistenciaGral.cve_lote, CHAR) and CONVERT(c_lotes.cve_articulo, CHAR) = CONVERT(c_articulo.cve_articulo, CHAR)
        #, t_movcuarentena c
        WHERE cve_ubicacion = '{$idy_ubica}' #AND c.Idy_Ubica = V_ExistenciaGral.cve_ubicacion
        AND V_ExistenciaGral.Existencia IS NOT NULL 
        AND V_ExistenciaGral.Cuarentena = 1
        GROUP BY V_ExistenciaGral.cve_articulo,V_ExistenciaGral.cve_lote,V_ExistenciaGral.Cve_Contenedor

        UNION 

        SELECT 
          CONVERT(a.id USING utf8mb4) AS cve_almac, 
          CONVERT(th.cve_ubicacion USING utf8mb4) AS cve_ubicacion, 
          CONVERT(td.cve_articulo USING utf8mb4) AS cve_articulo,
          CONVERT('' USING utf8mb4) AS Cve_Contenedor,
          CONVERT('RTM' USING utf8mb4) as val,
          CONVERT(d.Diao USING utf8mb4) as diao,
          CONVERT(IF(c_articulo.control_lotes = 'S',IFNULL(c_lotes.LOTE, ''),'') USING utf8mb4) AS lote,
          CONVERT(IF(c_articulo.control_lotes = 'S' AND IFNULL(c_lotes.LOTE, '') != '',IF(DATE_FORMAT(c_lotes.Caducidad, '%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(c_lotes.Caducidad,'%d-%m-%Y')),'') USING utf8mb4) AS caducidad,
          CONVERT(IF(c_articulo.control_lotes = 'S' AND IFNULL(c_lotes.LOTE, '') != '',IF(DATE_FORMAT(c_lotes.Caducidad, '%Y-%m-%d') = '0000-00-00','',c_lotes.Caducidad),'') USING utf8mb4) AS caducidad_input,
          CONVERT(IF(c_articulo.control_numero_series = 'S', IFNULL(td.cve_lote, ''),'') USING utf8mb4) AS serie,
          CONVERT(IFNULL(c_articulo.control_lotes, 'N') USING utf8mb4) AS control_lotes, 
          CONVERT(IFNULL(c_articulo.Caduca, 'N') USING utf8mb4) AS Caduca, 
          CONVERT(IFNULL(c_articulo.control_numero_series, 'N') USING utf8mb4) AS control_numero_series, 
          CONVERT(d.Cantidad USING utf8mb4) AS Existencia_Total,
          CONVERT(c_articulo.des_articulo USING utf8mb4) AS descripcion,
          CONVERT(IFNULL(IF(c_articulo.control_peso = 'S',SUM(td.CantidadRecibida),TRUNCATE(IFNULL(c_articulo.peso,0),4)), 0) USING utf8mb4) AS peso_unitario,
          CONVERT(IFNULL(TRUNCATE(((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000),4), 0) USING utf8mb4) AS volumen_unitario,
          CONVERT(IFNULL(IF(c_articulo.control_peso = 'S',SUM(td.CantidadRecibida),TRUNCATE((IFNULL(c_articulo.peso,0)*SUM(td.CantidadRecibida)),4)), 0) USING utf8mb4) AS peso_total,
          CONVERT(IFNULL(TRUNCATE((((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000)*SUM(td.CantidadRecibida)),4), 0) USING utf8mb4) AS volumen_total
        FROM td_entalmacen td
        LEFT JOIN th_entalmacen th ON CONVERT(th.Fol_Folio, CHAR) = CONVERT(td.fol_folio, CHAR)
        LEFT JOIN Descarga d ON CONVERT(d.Folio, CHAR) = CONVERT(th.Fact_Prov, CHAR) AND CONVERT(d.Articulo, CHAR) = CONVERT(td.cve_articulo, CHAR)
        LEFT JOIN c_almacenp a ON CONVERT(a.clave, CHAR) = CONVERT(th.Cve_Almac, CHAR)
        LEFT JOIN c_lotes ON CONVERT(c_lotes.LOTE, CHAR) = CONVERT(td.cve_lote, CHAR) AND CONVERT(c_lotes.cve_articulo, CHAR) = CONVERT(td.cve_articulo, CHAR)
        LEFT JOIN c_articulo ON CONVERT(c_articulo.cve_articulo, CHAR) = CONVERT(td.cve_articulo, CHAR)
        WHERE th.cve_ubicacion = '{$ubicacion}'
        AND td.CantidadRecibida IS NOT NULL 
        AND td.status = 'Q' AND d.Cantidad > 0
        {$SQLDiaO}
        GROUP BY td.cve_articulo,td.cve_lote, Cve_Contenedor, d.ID
        ORDER BY IF(diao = '', cve_articulo, diao) DESC
    ";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);

    $sql .= " LIMIT {$_page},{$limit}; ";
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
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        extract($row);


        $responce->rows[$i]['id']=$row['idy_ubica'];
        $responce->rows[$i]['cell']=array($i,'',$row['diao'],$row['cve_articulo'],$row['descripcion'], $row['Cve_Contenedor'], $row['lote'], $row['caducidad'],$row['serie'],$row['Existencia_Total'],$row['peso_unitario'],$row['volumen_unitario'], $row['peso_total'], $row['volumen_total'], $row['control_lotes'], $row['Caduca'], $row['control_numero_series'], $row['caducidad_input'], $row['val']);
        $i++;
    }


//****************************************************************************************
    $sql = "
        SELECT  DISTINCT
          d.Diao as diao
        FROM td_entalmacen td
        LEFT JOIN th_entalmacen th ON th.Fol_Folio = td.fol_folio
        LEFT JOIN Descarga d ON d.Folio = th.Fact_Prov AND d.Articulo = td.cve_articulo
        LEFT JOIN c_almacenp a ON a.clave = th.Cve_Almac
        LEFT JOIN c_lotes ON c_lotes.LOTE = td.cve_lote AND c_lotes.cve_articulo = td.cve_articulo
        LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.cve_articulo
        WHERE th.cve_ubicacion = '{$ubicacion}'
        AND td.CantidadRecibida IS NOT NULL 
        AND td.status = 'Q' AND d.Cantidad > 0
        ORDER BY diao DESC
    ";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ";
    }

    $options = "<option value=''>Seleccione Dia Operativo</option>";

    while ($row = mysqli_fetch_array($res)) 
    {
        $diao = $row['diao'];
        $selected = "";
        if($diao == $diaoperativo) $selected = "selected";
        $options .= "<option $selected value='$diao'>$diao</option>";
    }
    $responce->options_diao = $options;
//****************************************************************************************



    echo json_encode($responce);
}
if(isset($_POST) && !empty($_POST['action']) && $_POST['action'] === 'loadStatistics'){
    $almacen = $_POST['almacen'];
    $almacenaje = $_POST['almacenaje'];
    $total_ubicaciones = 0;
    $ocupadas = 0;
    $porcentaje_ocupadas = 0;

    if(empty($almacenaje) && !empty($almacen)){
        $sql = "SELECT idy_ubica AS id FROM c_ubicacion WHERE c_ubicacion.Tipo = 'Q' and c_ubicacion.picking = 'N' and cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '$almacen');";
    }else{
        $sql = "SELECT idy_ubica AS id FROM c_ubicacion WHERE c_ubicacion.Tipo = 'Q' and c_ubicacion.picking = 'N' and cve_almac = '$almacenaje'";
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

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'mover'){
    $ubicacion = $_POST['ubicacion'];
    $id_motivo = $_POST['id_motivo'];
    $usuario = $_POST['usuario'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    $sqlUsuario ="
        SELECT
            cve_usuario
        FROM c_usuario
        where id_user = '".$usuario."'
    ";
    if (!($res = mysqli_query($conn, $sqlUsuario))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $cve_usuario =  mysqli_fetch_array($res)['cve_usuario'];
  
    $sqlUsuario ="
        SELECT
            idy_ubica,
            Tipo
        FROM c_ubicacion
        where CodigoCSD = '".$ubicacion."'
    ";
    if (!($res = mysqli_query($conn, $sqlUsuario))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $res_ubicacion =  mysqli_fetch_array($res);
    $id_ubicacion = $res_ubicacion['idy_ubica'];
    $tipo = $res_ubicacion['Tipo'];
    
    if($tipo == 'Q'){
        $sql="
              UPDATE c_ubicacion
              SET Tipo = 'L'
              WHERE idy_ubica = '".$id_ubicacion."'
        ";
        if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
    }
  
    $sqlExistencias="
          UPDATE ts_existenciapiezas
          SET Cuarentena = 0
          WHERE idy_ubica = '".$id_ubicacion."'
    ";
    if (!($res = mysqli_query($conn, $sqlExistencias))){echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}
  
    $sqlExistencias="
          UPDATE ts_existenciatarima
          SET Cuarentena = 0
          WHERE idy_ubica = '".$id_ubicacion."'
    ";
    if (!($res = mysqli_query($conn, $sqlExistencias))){echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}
  
    $sqlLiberar ="
        update t_movcuarentena
        set Fec_Libera = now(), Id_MotivoLib = ".$id_motivo.", Tipo_Cat_Lib = 'S', Usuario_Lib = '".$cve_usuario."'
        where idy_ubica = '".$id_ubicacion."'
    ";
    if (!($res = mysqli_query($conn, $sqlLiberar))){echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ";}
    
    $responce->success = true;
    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'traer_motivos'){
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    $sql="
        SELECT id,
        CAST(BINARY(Des_Motivo) AS CHAR CHARACTER SET utf8) AS descripcion
        FROM c_motivo
        WHERE Tipo_Cat = 'S'
        AND Activo = 1
    ";
    
    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    echo mysqli_error($conn);
    $responce->success = true;
    $responce->motivos = mysqli_fetch_all($res);
    echo json_encode($responce);
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'liberar_existencias'){
    $existencia = $_POST['existencia'];
    $idUser = $_POST['idUser'];
    $ubicacion = $_POST['ubicacion'];
    $id_motivo = $_POST['id_motivo'];
    $cve_almacen = $_POST['almacen'];
    $id_almacen  = $_POST['almacen_id'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  
    $sql ="
        SELECT
            cve_usuario
        FROM c_usuario
        WHERE id_user = '".$idUser."';
    ";
    if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
    $cve_usuario = mysqli_fetch_array($res)['cve_usuario'];
  
    $sqlUsuario ="
        SELECT
            idy_ubica
        FROM c_ubicacion
        where CodigoCSD = '".$ubicacion."'
    ";
    if (!($res = mysqli_query($conn, $sqlUsuario))){echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}
    $id_ubicacion =  mysqli_fetch_array($res)['idy_ubica'];
  
    $sqlCambiarLotePiezas = "";$sqlCambiarLoteTarima = "";
    $lote_serie = "";
    if($_POST['cambiar_lote'] == 1)
    {
        //$lote_serie = $_POST['lote_serie'];
        //if($_POST['nuevo_lote_serie'] != '')
            $lote_serie = $_POST['nuevo_lote_serie'];

        $sqlCambiarLotePiezas = "cve_lote='{$lote_serie}' ";
        $sqlCambiarLoteTarima = "lote='{$lote_serie}' ";
    }
    else 
    {
        $sqlCambiarLotePiezas = "Cuarentena = 0 ";
        $sqlCambiarLoteTarima = "Cuarentena = 0 ";
    }

    foreach($existencia as $key=>$valor)
    {
        if($valor['lote']==""){
          $valor['lote'] = $valor["serie"];
        }

        if($_POST['cambiar_lote'] == 1 && $_POST['nuevo_lote_serie'] != '')
        {
            if($_POST['tiene_lote'] == 'S' && $_POST['tiene_caducidad'] == 'S')
            {
               $caducidad = $_POST['cambiar_caducidad'];
                $sql ="INSERT IGNORE INTO c_lotes (cve_articulo, Lote, Caducidad) VALUES ('".$valor['clave']."', '{$lote_serie}', '{$caducidad}')";
                if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
            }
            else if($_POST['tiene_lote'] == 'S')
            {
                $sql ="INSERT IGNORE INTO c_lotes (cve_articulo, Lote, Caducidad) VALUES ('".$valor['clave']."', '{$lote_serie}', '0000-00-00')";
                if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
            }
            else if($_POST['tiene_serie'] == 'S')
            {
                $sql ="INSERT IGNORE INTO c_serie (cve_articulo, numero_serie, fecha_ingreso) VALUES ('".$valor['clave']."', '{$lote_serie}', CURDATE())";
                if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
            }

        }
        else if($_POST['cambiar_lote'] == 1 && $_POST['nuevo_lote_serie'] == '')
        {
            if($_POST['tiene_caducidad'] == 'S')
            {
               $caducidad = $_POST['cambiar_caducidad'];
                $sql ="UPDATE c_lotes SET Caducidad = '{$caducidad}' WHERE Lote = '{$lote_serie}' AND cve_articulo='".$valor['clave']."'";
                if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
            }

            $lote_ant = $_POST['lote_serie'];
            $sql ="UPDATE c_lotes SET Lote = '{$lote_serie}' WHERE Lote = '{$lote_ant}' AND cve_articulo='".$valor['clave']."'";
            if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}

            //$sql ="INSERT IGNORE INTO c_lotes (cve_articulo, Lote, Caducidad) VALUES ('".$valor['clave']."', '{$lote_serie}', '0000-00-00')";
            //if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}

        }

        $cve_articulo = $valor['clave'];
        $lote_serie_origen  = $_POST['lote_serie'];
        $lote_serie_destino = $_POST['nuevo_lote_serie'];
        $existencia_kardex  = $valor['existencia'];

        if($valor["contenedor"] == "")
        {
            $SQLMovimiento ="INSERT IGNORE INTO t_tipomovimiento (id_TipoMovimiento, nombre) VALUES (20, 'Cambio de Lote | Serie')";
            if (!($res = mysqli_query($conn, $SQLMovimiento))){echo "Falló la preparación(Movimiento): (" . mysqli_error($conn) . ") ".$SQLMovimiento;}

            $SQLKardex = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo) VALUES ('$cve_articulo', '$lote_serie_destino', NOW(), '$lote_serie_origen', '$lote_serie_destino', $existencia_kardex, $existencia_kardex, 0, 20, '$cve_usuario', '$id_almacen', 1)";
            $res = mysqli_query($conn, $SQLKardex);
            //if (!($res = mysqli_query($conn, $SQLKardex)))
            //    {echo "Falló la preparación(Kardex1): (" . mysqli_error($conn) . ") ".$SQLKardex;}

            $sqlExistencias="
                UPDATE ts_existenciapiezas
                    SET {$sqlCambiarLotePiezas}
                WHERE idy_ubica = '".$id_ubicacion."'
                    AND cve_articulo = '".$valor['clave']."'
                    AND cve_lote = '".$valor['lote']."'
                    AND Existencia = '".$valor['existencia']."'
            ";
        }
        else
        {
            $sqlcontenedor ="
                SELECT
                    IDContenedor
                FROM c_charolas
                where clave_contenedor = '".$valor["contenedor"]."'
            ";
            if (!($res = mysqli_query($conn, $sqlcontenedor))){echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}
            $id_contenedor =  mysqli_fetch_array($res)['IDContenedor'];
          
            $SQLKardex = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo) VALUES ('$cve_articulo', '$lote_serie_destino', NOW(), '$lote_serie_origen', '$lote_serie_destino', $existencia_kardex, $existencia_kardex, 0, 20, '$cve_usuario', '$id_almacen', 1)";
            $res = mysqli_query($conn, $SQLKardex);
            //if (!($res = mysqli_query($conn, $SQLKardex))){echo "Falló la preparación(Kardex2): (" . mysqli_error($conn) . ") ";}

            $SQLKardexCharolas = "INSERT INTO t_MovCharolas (id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES ((SELECT MAX(id) FROM t_cardex), '$cve_almacen', $id_contenedor, NOW(), '$lote_serie_origen', '$lote_serie_destino', 20, '$cve_usuario', 'I')";
            $res = mysqli_query($conn, $SQLKardexCharolas);
            //if (!($res = mysqli_query($conn, $SQLKardexCharolas))){echo "Falló la preparación(KardexCharolas): (" . mysqli_error($conn) . ") ";}

            $sqlExistencias="
                UPDATE ts_existenciatarima
                    SET {$sqlCambiarLoteTarima}
                WHERE idy_ubica = '".$id_ubicacion."'
                    AND cve_articulo = '".$valor['clave']."'
                    AND lote = '".$valor['lote']."'
                    AND existencia = '".$valor['existencia']."'
                    AND ntarima = '".$id_contenedor."';
            ";
        }
        if (!($res = mysqli_query($conn, $sqlExistencias))){echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ";}
      
        if($_POST['cambiar_lote'] == 0)
        {
            $sqlLiberar ="
                update t_movcuarentena
                set Fec_Libera = now(), Id_MotivoLib = '".$id_motivo."', Tipo_Cat_Lib = 'S', Usuario_Lib = '".$cve_usuario."'
                where idy_ubica = '".$id_ubicacion."'
                and Cve_Articulo ='".$valor['clave']."'
                and Cve_Lote = '".$valor['lote']."'
            ";
            if (!($res = mysqli_query($conn, $sqlLiberar))){echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";}
        }
    }
  
    $responce->success = true;
    echo json_encode($responce);  
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'lote_serie_existente'){
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $cve_articulo = $_POST['cve_articulo'];
    $lote_serie = $_POST['lote_serie'];
    $sql="
        SELECT COUNT(*) as existe FROM V_Lotes_Series_Articulo WHERE Cve_Articulo = '{$cve_articulo}' AND LoteSerie = '{$lote_serie}'
    ";
    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $row = mysqli_fetch_array($res);
    $existe = $row["existe"];
    echo mysqli_error($conn);

    $success = false;
    if($existe)
       $success = true;

    echo json_encode($success);
}
