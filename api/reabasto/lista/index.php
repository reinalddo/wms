<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST) && $_POST['action'] = 'load') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $search = $_POST['buscar'];
    $almacen = $_POST['almacen'];
    $tipo = $_POST["type"];
    $tipo_reabasto = $_POST["tipo_reabasto"];
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)
  
    //if($search[0] == " "){$search = substr($search, 1);}

    if(!$sidx) $sidx =1;
    $and="";
    if($tipo=="PTL")
    {
      $and=" and rb.picking = 'PTL'";
    }
    else if($tipo=="Picking")
    {
      $and=" and rb.picking = 'picking'";
    }

    if($tipo_reabasto != "")
        $and .= " AND IFNULL(rb.caja_pieza, 'P') = '{$tipo_reabasto}' ";


    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);
/*
    $sqlCount = "
        SELECT 
            COUNT(ve.cve_articulo) AS total
        FROM    V_ExistenciaGral ve
        LEFT JOIN c_articulo a ON a.cve_articulo = ve.cve_articulo
        WHERE   cve_ubicacion IN (
            SELECT idy_ubica 
            FROM c_ubicacion u 
            WHERE u.picking = 'S' 
            AND u.cve_almac IN (
                SELECT cve_almac 
                FROM c_almacen 
                WHERE cve_almacenp = '{$almacen}'
                )
                {$and}
            )
    ";
    if(!empty($search)){
        $sqlCount .= " AND CONCAT_WS(' ', a.cve_articulo, a.des_articulo) like '%$search%'";
    }
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $row = mysqli_fetch_array($res);
    $count = $row['total'];
*/    
     $query = mysqli_query($conn, "SELECT codigo FROM t_codigocsd;");
	if($query->num_rows > 0){
		$codigo = mysqli_fetch_row($query)[0];
	}
/*
	if(!empty($codigo)){
		$data = explode('-', $codigo);
		$sqlPre = "CONCAT(";
		$totalData = sizeof($data) - 1;
		for($i = 0; $i <= $totalData; $i++){
            if($data[$i]=='cve_rack'){
                $g="'rack-'";
            }
            
            if($data[$i]=='cve_nivel'){
                $g="'secci&oacute;n-'";
            }
            
            if($data[$i]=='Ubicacion'){
                $g="'pasillo-'";
            }

			$sqlPre .= "u.{$data[$i]}";
            //$sqlPre .=$g.",";
			$sqlPre .= ($i < $totalData) ? ", '-', ": ")";
		}
	}
    $ff=$sqlPre."AS ubicacion,";
*/
    if(!empty($search)){
        //$sql .= " AND (CONCAT_WS(' ', a.cve_articulo, a.des_articulo) like '%$search%' or $sqlPre like '%{$search}%')";
        $and .= " AND (CONCAT_WS(' ', rb.clave, rb.descripcion) like '%".$search."%' )";
    }

  $sql = "  SELECT * FROM (
            SELECT DISTINCT
                ve.cve_articulo AS clave,
                a.des_articulo AS descripcion,
                IF(tu.caja_pieza = 'P', IFNULL(tu.CapacidadMinima, 0), ROUND((IFNULL(tu.CapacidadMinima, 0)/IFNULL(a.num_multiplo, 1)), 0)) AS minimo,
                IF(tu.caja_pieza = 'P', IFNULL(tu.CapacidadMaxima, 0), ROUND((IFNULL(tu.CapacidadMaxima, 0)/IFNULL(a.num_multiplo, 1)), 0)) AS maximo,
                IF(tu.caja_pieza = 'P', IF((SUM(ve.Existencia) <= ROUND(tu.CapacidadMinima, 0)), CONCAT('<span style=\'color:red;\'>',((IFNULL(tu.CapacidadMaxima, 0))-SUM(ve.Existencia)), '</span>'), 0), 0) AS reabastop,
                IF(tu.caja_pieza = 'C', IF((TRUNCATE((SUM(ve.Existencia)/IFNULL(a.num_multiplo, 1)), 0) <= ROUND((IFNULL(tu.CapacidadMinima, 0)/IFNULL(a.num_multiplo, 1)), 0)), CONCAT('<span style=\'color:red;\'>',(ROUND((IFNULL(tu.CapacidadMaxima, 0)/IFNULL(a.num_multiplo, 1)), 0)-TRUNCATE((SUM(ve.Existencia)/IFNULL(a.num_multiplo, 1)), 0)), '</span>'), 0), 0) AS reabasto,
                a.num_multiplo,
                IF(tu.caja_pieza = 'C', ROUND(SUM(ve.Existencia)/IFNULL(a.num_multiplo, 1), 0), SUM(ve.Existencia)) AS existencia,
                z.des_almac AS zona,
                tu.caja_pieza,
                u.CodigoCSD AS ubicacion,
                IF(IFNULL(tu.caja_pieza, 'P') = 'C', 'Cajas', 'Piezas') AS tipo_reabasto,
                u.idy_ubica AS id,
                ze.nombre AS almacen,
                IF(u.TECNOLOGIA='PTL','PTL',IF(u.picking='S','Picking','')) AS picking
            FROM    V_ExistenciaGral ve
            LEFT JOIN ts_ubicxart tu ON tu.cve_articulo = ve.cve_articulo AND tu.idy_ubica = ve.cve_ubicacion
            LEFT JOIN c_articulo a ON a.cve_articulo = ve.cve_articulo 
            INNER JOIN c_ubicacion u ON u.idy_ubica = ve.cve_ubicacion AND u.picking = 'S' 
            INNER JOIN c_almacen z ON z.cve_almac = u.cve_almac
            INNER JOIN c_almacenp ze ON ze.id = z.cve_almacenp AND ze.id = '$almacen'

            GROUP BY u.CodigoCSD, ve.cve_articulo 
            
            UNION
            
            SELECT DISTINCT
                vp.Cve_Articulo AS clave,
                ap.des_articulo AS descripcion,
                IF(tup.caja_pieza = 'P', IFNULL(tup.CapacidadMinima, 0), ROUND((IFNULL(tup.CapacidadMinima, 0)/IFNULL(ap.num_multiplo, 1)), 0)) AS minimo,
                IF(tup.caja_pieza = 'P', IFNULL(tup.CapacidadMaxima, 0), ROUND((IFNULL(tup.CapacidadMaxima, 0)/IFNULL(ap.num_multiplo, 1)), 0)) AS maximo,
                IF(tup.caja_pieza = 'P', IF((SUM(vp.Existencia) <= ROUND(tup.CapacidadMinima, 0)), CONCAT('<span style=\'color:red;\'>',((IFNULL(tup.CapacidadMaxima, 0))-SUM(vp.Existencia)), '</span>'), 0), 0) AS reabastop,
                IF(tup.caja_pieza = 'C', IF((TRUNCATE((SUM(vp.Existencia)/IFNULL(ap.num_multiplo, 1)), 0) <= ROUND((IFNULL(tup.CapacidadMinima, 0)/IFNULL(ap.num_multiplo, 1)), 0)), CONCAT('<span style=\'color:red;\'>',(ROUND((IFNULL(tup.CapacidadMaxima, 0)/IFNULL(ap.num_multiplo, 1)), 0)-TRUNCATE((SUM(vp.Existencia)/IFNULL(ap.num_multiplo, 1)), 0)), '</span>'), 0), 0) AS reabasto,
                ap.num_multiplo,
                IF(tup.caja_pieza = 'C', ROUND(SUM(vp.Existencia)/IFNULL(ap.num_multiplo, 1), 0), SUM(vp.Existencia)) AS existencia,
                zp.des_almac AS zona,
                tup.caja_pieza,
                up.CodigoCSD AS ubicacion,
                IF(IFNULL(tup.caja_pieza, 'P') = 'C', 'Cajas', 'Piezas') AS tipo_reabasto,
                up.idy_ubica AS id,
                zep.nombre AS almacen,
                IF(up.TECNOLOGIA='PTL','PTL',IF(up.picking='S','Picking','')) AS picking
            FROM    V_ProductosAReabastecer vp
            LEFT JOIN ts_ubicxart tup ON tup.cve_articulo = vp.cve_articulo AND tup.idy_ubica = vp.idy_ubica
            LEFT JOIN c_articulo ap ON ap.cve_articulo = vp.cve_articulo 
            INNER JOIN c_ubicacion up ON up.idy_ubica = vp.idy_ubica AND up.picking = 'S' 
            INNER JOIN c_almacen zp ON zp.cve_almac = up.cve_almac
            INNER JOIN c_almacenp zep ON zep.id = zp.cve_almacenp AND zep.id = '$almacen'
            WHERE vp.Existencia = 0
            GROUP BY up.CodigoCSD, vp.cve_articulo 
            ) AS rb
            WHERE rb.minimo >= 0 AND rb.maximo > 0 {$and}
            ORDER BY IFNULL(rb.maximo, 0) DESC
";
    //if(!empty($search)){
    //    //$sql .= " AND (CONCAT_WS(' ', a.cve_articulo, a.des_articulo) like '%$search%' or $sqlPre like '%{$search}%')";
    //    $sql .= " AND (CONCAT_WS(' ', a.cve_articulo, a.des_articulo) like '%".$search."%' )";
    //}
    
    //$sql .= " order by a.des_articulo";
    //$sql .= " GROUP BY u.CodigoCSD, ve.cve_articulo order by IFNULL(tu.CapacidadMaxima, 0) DESC";
    
  
    $responce->query1 = $sql;
  
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_num_rows($res);

    $sql .= " LIMIT $start, $limit";

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
    $responce->j1 = $sql;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map("utf8_encode", $row);
        $arr[] = $row;
        extract($row);        
        $responce->rows[$i]['id']=$id;
        $responce->rows[$i]['cell']=array(
                                          "",'',
                                          $clave, 
                                          $descripcion,
                                          $ubicacion,
                                          $picking, 
                                          $tipo_reabasto, 
                                          $maximo, 
                                          $minimo, 
                                          $existencia,
                                          $reabastop,
                                          $reabasto,
                                          $id,
                                          $almacen,
                                          $zona
/*
                                          $clave, 
                                          $descripcion,
                                          $ubicacion,
                                          $maximo, 
                                          $minimo, 
                                          $existencia,
                                          $reabastop,
                                          $reabasto,
                                          $id,
                                          $almacen,
                                          $picking, 
                                          $zona,
                                          $reabastop_val,
                                          $reabastoc_val
*/
                                        );
        $i++;
    }
    //echo $sql;
    echo json_encode($responce);
}