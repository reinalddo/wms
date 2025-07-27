<?php
include '../../config.php';

error_reporting(0);


if (isset($_GET) && ! empty($_GET) and $_GET['action'] == 'diasEnPatio') {
    $page = $_GET['page']; // get the requested page
    $limit = $_GET['rows']; // get how many rows we want to have into the grid
    $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    $sord = $_GET['sord']; // get the direction
    $almacen = $_GET['almacen'];
    $zona = $_GET['zona'];

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_GET['criterio'];
		
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //mysqli_set_charset($conn, 'utf8');
    // prepara la llamada al procedimiento almacenado Lis_Facturas
    /*$sql = "SELECT 
                e.cve_articulo AS clave_producto,
                p.des_articulo AS nombre_producto,
                DATEDIFF(NOW(), e.fecha) dias
            FROM t_invpiezas e
                JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
                JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
                JOIN c_almacenp a ON z.cve_almacenp = a.id
                JOIN c_ubicacion u ON u.idy_ubica = e.idy_ubica
            WHERE z.cve_almac = {$zona} AND a.id = {$almacen}
                AND e.Cantidad > 0 AND e.NConteo = (
                    SELECT 
                        MAX(e2.NConteo) conteo FROM t_invpiezas e2 
                    WHERE  e2.cve_articulo = e.cve_articulo AND e2.idy_ubica = e.idy_ubica AND e2.cve_lote = e.cve_lote AND e2.ID_Inventario = e.ID_Inventario
                )

                en el catalogo articulos
            ";*/

	$sql = "SELECT 
                e.cve_articulo AS clave_producto,
                p.des_articulo AS nombre_producto,
                DATEDIFF(NOW(), e.fecha) dias
            FROM t_invpiezas e
                JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
                JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
                JOIN c_almacenp a ON z.cve_almacenp = a.id
                JOIN c_ubicacion u ON u.idy_ubica = e.idy_ubica
            WHERE z.cve_almac = {$zona} AND a.id = {$almacen}
                AND e.Cantidad > 0 AND e.NConteo = (
                    SELECT 
                        MAX(e2.NConteo) conteo FROM t_invpiezas e2 
                    WHERE  e2.cve_articulo = e.cve_articulo AND e2.idy_ubica = e.idy_ubica AND e2.cve_lote = e.cve_lote AND e2.ID_Inventario = e.ID_Inventario
                )
            ";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $response = [
        'page' => $page,
        'total' => $total_pages,
        'records' => $count,
        'rows' => $rows
    ];

    $arr = array();
    $i = 0;
    
    
    
    while ($row = mysqli_fetch_array($res)) {
		$row = array_map('utf8_encode',$row);
        extract($row);
        //$response['row'][$i]['id'] = $id;
        $response['rows'][$i]['cell'] = array($clave_producto, $nombre_producto, $dias);
        $i++;
    }
    echo json_encode($response);
}





if (isset($_GET) && ! empty($_GET) and $_GET['action'] == 'costoInventario') {
    $page = $_GET['page']; // get the requested page
    $limit = $_GET['rows']; // get how many rows we want to have into the grid
    $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    $sord = $_GET['sord']; // get the direction
    $almacen = $_GET['almacen'];
    $zona = $_GET['zona'];

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_GET['criterio'];
		
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //mysqli_set_charset($conn, 'utf8');
    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sql = "SELECT 
                e.ID_Inventario inventario,
                i.Fecha,
                e.cve_articulo clave,
                p.des_articulo articulo,
                p.costo costo_uni,
                SUM(p.costo) costo_total
            FROM t_invpiezas e
                JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
                JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
                JOIN c_almacenp a ON z.cve_almacenp = a.id
                JOIN c_ubicacion u ON u.idy_ubica = e.idy_ubica
                JOIN th_inventario i ON e.ID_Inventario = i.ID_Inventario
            WHERE z.cve_almac = {$zona} AND a.id = {$almacen} 
                e.Cantidad > 0 AND 
                e.NConteo = (
                    SELECT 
                        MAX(e2.NConteo) conteo FROM t_invpiezas e2 
                    WHERE  e2.cve_articulo = e.cve_articulo AND e2.idy_ubica = e.idy_ubica AND e2.cve_lote = e.cve_lote AND e2.ID_Inventario = e.ID_Inventario
                )
            GROUP BY articulo, inventario
            ORDER BY inventario
        ";

	
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $response = [
        'page' => $page,
        'total' => $total_pages,
        'records' => $count,
        'rows' => $rows
    ];

    $arr = array();
    $i = 0;
    
    
    
    while ($row = mysqli_fetch_array($res)) {
		$row = array_map('utf8_encode',$row);
        extract($row);
        //$response['row'][$i]['id'] = $id;
        $response['rows'][$i]['cell'] = array($clave_producto, $nombre_producto, $dias);
        $i++;
    }
    echo json_encode($response);
}