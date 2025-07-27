<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST) and $_POST['action'] == '') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
/*
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "Select * from c_articulo Where Activo = '1';";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close($conn);
*/
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;
	
    $sql = "SELECT
                c_articulo.cve_articulo,
                c_articulo.des_articulo,
                c_articulo.num_multiplo,
                c_articulo.cve_umed,
                c_lotes.LOTE,
                DATE_FORMAT(c_lotes.CADUCIDAD,'%d-%m-%Y') as CADUCIDAD,
				(Select COUNT(*) FROM c_articulo) as cuenta
                FROM
                c_articulo
                LEFT JOIN c_lotes ON c_articulo.cve_articulo = c_lotes.cve_articulo
            GROUP BY c_articulo.cve_articulo ";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);

    $sql .= " LIMIT $_page, $limit;";

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
    $responce->sql = $sql;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $arr[] = $row;

        $lote = (empty($row['LOTE'])) ? "" : $row['LOTE'];
        $CADUCIDAD = (empty($row['CADUCIDAD'])) ? "" : $row['CADUCIDAD'];

        $responce->rows[$i]['id']=$row['cve_articulo'];
        $responce->rows[$i]['cell']=array($row['cve_articulo'], utf8_encode($row['des_articulo']), $lote, $CADUCIDAD, $row['num_multiplo'], $row['cve_umed'], $row['cuenta']);
        $i++;
    }
    echo json_encode($responce); exit;
}




if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $almacen = "";
    if(isset($_POST['almacen']))
        $almacen = $_POST['almacen'];

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit * $page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx = 1;

  
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;
    



    $sql = "SELECT
                COUNT(a.cve_articulo) totalRegistros
            FROM ts_existenciapiezas e
                INNER JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                LEFT JOIN c_almacenp alm ON a.cve_almac = alm.id 
            WHERE 
                {$articulo} 
                a.Activo = '1' AND 
                (SELECT existencia > 0) AND 
                a.cve_articulo NOT IN (
                    (SELECT 
                        ar.cve_articulo
                    FROM cab_planifica_inventario ca, det_planifica_inventario p, c_articulo ar 
                    WHERE 
                        p.`status` = 'A' AND 
                        ca.cve_articulo = a.cve_articulo AND 
                        p.cve_articulo = ar.cve_articulo AND 
                        p.cve_articulo = a.cve_articulo
                    )
                )
                AND alm.clave = '{$almacen}'
                GROUP BY a.cve_articulo 
                ";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $cantidad = mysqli_fetch_array($res);
    $count = $cantidad['totalRegistros'];



    $sql = "SELECT 
                #SUM(e.Existencia) existencia 
    SUM( DISTINCT
    IFNULL((SELECT SUM(ex.Existencia) FROM (
    SELECT DISTINCT ve.cve_almac, ve.cve_ubicacion, ve.cve_articulo, ve.cve_lote, ve.Existencia, a.des_articulo 
    FROM V_ExistenciaGral ve
    LEFT JOIN c_articulo a ON a.cve_articulo = ve.cve_articulo
    LEFT JOIN c_ubicacion u ON u.idy_ubica = ve.cve_ubicacion
    LEFT JOIN c_lotes l ON l.LOTE = ve.cve_lote AND l.cve_articulo = ve.cve_articulo
    LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = ve.cve_ubicacion)
    LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
    LEFT JOIN c_charolas ch ON ch.clave_contenedor = ve.Cve_Contenedor
    WHERE ve.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND ve.Existencia > 0 AND ve.cve_articulo = l.cve_articulo AND ve.cve_lote = l.LOTE AND l.cve_articulo = a.cve_articulo AND ve.tipo = 'ubicacion' AND ve.cve_lote != ''
    ) AS ex WHERE ex.cve_articulo = e.cve_articulo), 
    IFNULL((SELECT IFNULL(SUM(ve.Existencia), 0) FROM V_ExistenciaGral ve 
    WHERE ve.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  
    AND ve.Existencia > 0  AND ve.cve_articulo = e.cve_articulo AND ve.tipo = 'ubicacion' ), 0
    )) +
    IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion = 'S' AND vp.cve_articulo = e.cve_articulo AND vp.tipo = 'ubicacion'), 0)
    ) AS existencia

            FROM ts_existenciapiezas e
                INNER JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                LEFT JOIN c_almacenp alm ON a.cve_almac = alm.id 
            WHERE
                a.Activo = '1' AND 
                e.Existencia > 0 AND 
                e.cve_articulo NOT IN (
                    (SELECT 
                        ar.cve_articulo
                    FROM cab_planifica_inventario ca, det_planifica_inventario p, c_articulo ar 
                    WHERE 
                        p.`status` = 'A' AND 
                        ca.cve_articulo = e.cve_articulo AND 
                        p.cve_articulo = ar.cve_articulo AND 
                        p.cve_articulo = e.cve_articulo
                    )
                )
                AND alm.clave = '{$almacen}'
            ";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $totalExistencias = mysqli_fetch_array($res);
    $totalExistencias = $totalExistencias['existencia'];



    $articulo = $_POST['producto'];

    if( !empty($articulo)) {
        $articulo = "(a.cve_articulo LIKE '%{$articulo}%' OR a.des_articulo LIKE '%{$articulo}%') AND ";
    } else $articulo = '';
/*
    $sql = "SELECT
                a.cve_articulo,
                a.des_articulo,
                #(SELECT SUM(Existencia) FROM ts_existenciapiezas WHERE cve_articulo = a.cve_articulo) existencia
        IFNULL((SELECT SUM(ex.Existencia) FROM (
        SELECT DISTINCT ve.cve_almac, ve.cve_ubicacion, ve.cve_articulo, ve.cve_lote, ve.Existencia, a.des_articulo 
        FROM V_ExistenciaGral ve
        LEFT JOIN c_articulo a ON a.cve_articulo = ve.cve_articulo
        LEFT JOIN c_ubicacion u ON u.idy_ubica = ve.cve_ubicacion
        LEFT JOIN c_lotes l ON l.LOTE = ve.cve_lote AND l.cve_articulo = ve.cve_articulo
        LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = ve.cve_ubicacion)
        LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
        LEFT JOIN c_charolas ch ON ch.clave_contenedor = ve.Cve_Contenedor
        WHERE ve.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND ve.Existencia > 0 AND ve.cve_articulo = l.cve_articulo AND ve.cve_lote = l.LOTE AND l.cve_articulo = a.cve_articulo AND ve.tipo = 'ubicacion' AND ve.cve_lote != ''
        ) AS ex WHERE ex.cve_articulo = e.cve_articulo), 
        IFNULL((SELECT IFNULL(SUM(ve.Existencia), 0) FROM V_ExistenciaGral ve 
        WHERE ve.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  
        AND ve.Existencia > 0  AND ve.cve_articulo = e.cve_articulo AND ve.tipo = 'ubicacion' ), 0
        )) +
        IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion = 'S' AND vp.cve_articulo = e.cve_articulo AND vp.tipo = 'ubicacion'), 0) AS existencia

            FROM ts_existenciapiezas e
                INNER JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                LEFT JOIN c_almacenp alm ON a.cve_almac = alm.id 
            WHERE 
                {$articulo} 
                a.Activo = '1' AND 
                (SELECT existencia > 0) AND 
                a.cve_articulo NOT IN (
                    (SELECT 
                        ar.cve_articulo
                    FROM cab_planifica_inventario ca, det_planifica_inventario p, c_articulo ar 
                    WHERE 
                        p.`status` = 'A' AND 
                        ca.cve_articulo = a.cve_articulo AND 
                        p.cve_articulo = ar.cve_articulo AND 
                        p.cve_articulo = a.cve_articulo
                    )
                )
                AND alm.clave = '{$almacen}'
                GROUP BY a.cve_articulo 
            ORDER BY a.des_articulo ASC LIMIT {$start}, {$limit}";
*/
    $sql = "SELECT
                u.CodigoCSD,
                u.idy_ubica,
                a.cve_articulo,
                a.des_articulo,
                IFNULL(ch.CveLP, '') AS lp,
                IFNULL(e.cve_lote, '') AS lote_serie,
                IF(a.Caduca = 'S', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS caducidad,
                (SUM(e.Existencia)) AS existencia,
                z.des_almac AS zona_almacenaje
            FROM V_ExistenciaGralProduccion e
                INNER JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                LEFT JOIN c_almacenp alm ON e.cve_almac = alm.id 
                LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion 
                LEFT JOIN c_charolas ch ON IFNULL(ch.clave_contenedor, '') = IFNULL(e.Cve_Contenedor, '') AND ch.cve_almac = e.cve_almac
                LEFT JOIN c_lotes l ON l.cve_articulo = e.cve_articulo AND l.Lote = e.cve_lote
                LEFT JOIN c_almacen z ON u.cve_almac = z.cve_almac
            WHERE 
                {$articulo} 
                a.Activo = '1'  
                AND e.tipo = 'ubicacion'
                AND alm.clave = '{$almacen}'
                GROUP BY a.cve_articulo, u.CodigoCSD 
            ORDER BY a.des_articulo ASC ";
    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
/*
  */
    $count = mysqli_num_rows($res);

    $sql .= " LIMIT {$start}, {$limit}";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }


    $i = 0;
    //$porc_sum = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        $abc = number_format(($row['existencia'] * 100 / $totalExistencias), 3);
        //$porc_sum += $abc;
        $responce->rows[$i]['id'] = $row['cve_articulo'];
        $responce->rows[$i]['cell'] = [
            '',
            $row['CodigoCSD'], 
            $row['cve_articulo'], 
            utf8_encode($row['des_articulo']), 
            $row['lp'], 
            $row['lote_serie'], 
            $row['caducidad'], 
            $row['existencia'], 
            $abc, 
            $row['zona_almacenaje'], 
            $row['idy_ubica'], 
            ''//$porc_sum
        ];
        $i++;
    }

    //$count = $i;

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;
    $responce->records = $count;

    $responce->page = $page;
    $responce->total = $total_pages;
    

    echo json_encode($responce); exit;
}