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
    $search = isset($_POST['search']) ? $_POST['search'] : '';

    if (!$sidx) {
        $sidx =1;
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = "SELECT COUNT(Fol_Folio) as cuenta FROM th_entalmacen WHERE cve_almac = '$almacen'";
    
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $_page = 0;
    
    if (intval($page)>0) {
        $_page = ($page-1)*$limit;
    }
    
    $sql = "SELECT
                items.cve_ubicacion area_clave,
                embarq.descripcion AS area_descripcion,
                IFNULL((SELECT COUNT(DISTINCT(fol_folio)) FROM td_entalmacen WHERE cve_ubicacion = items.cve_ubicacion), 0) AS total_pedidos,
                IFNULL((SELECT SUM(CantidadDisponible) FROM td_entalmacen WHERE cve_ubicacion = items.cve_ubicacion), 0) AS total_productos,
                (SELECT COUNT(*) FROM td_pedido WHERE Fol_folio = items.fol_folio) pedido_total_items
            FROM td_entalmacen items
                LEFT JOIN t_ubicacionembarque embarq ON embarq.cve_ubicacion = items.cve_ubicacion AND embarq.Activo = 1
                LEFT JOIN th_entalmacen header ON header.Fol_Folio = items.fol_folio
            WHERE header.Cve_Almac ='{$almacen}' ";

    if (!empty($search)) {
        $sql .= " AND (items.cve_ubicacion LIKE '%$search%' OR header.Fol_Folio LIKE '%$search%') ";
    }
   
    $sql .= " GROUP BY items.cve_ubicacion LIMIT {$_page}, {$limit};";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if ($count >0) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages) {
        $page=$total_pages;
    }

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id'] = $row['area_clave'];
        $responce->rows[$i]['cell'] = [
            $row['area_clave'],
            $row['area_descripcion'],
            $row['total_pedidos'],
            $row['total_productos']
        ];
        $i++;
    }
    echo json_encode($responce);
}






if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'loadDetails') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows'] ? $_POST['rows'] : 20; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
        
    $_page = 0;
    
    $embarque = $_POST['embarque'];
    $almacen = $_POST['almacen'];

    if (!$sidx) {
        $sidx =1;
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = "SELECT 
                    COUNT(cve_articulo) as cuenta 
                FROM td_entalmacen WHERE fol_folio IN (SELECT Fol_Folio FROM th_entalmacen WHERE Cve_Almac = '$almacen' AND cve_ubicacion = '$embarque')";
    
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $_page = 0;
    
    if (intval($page)>0) {
        $_page = ($page-1) * $limit;
    }
    
        $sql = "SELECT 
                    embarq.descripcion AS area_descripcion,
                    items.fol_folio pedido_folio,
                    DATE_FORMAT(ped.Fec_Pedido, '%d/%m/%Y %l:%i %p') pedido_fecha,
                    DATE_FORMAT(ped.Fec_Entrega, '%d/%m/%Y %l:%i %p') pedido_entrega,
                    (SELECT COUNT(Fol_folio) FROM td_pedido WHERE Fol_folio = items.fol_folio) pedido_total_items
                FROM td_entalmacen items
                    INNER JOIN t_ubicacionembarque embarq ON embarq.cve_ubicacion = items.cve_ubicacion
                    LEFT JOIN th_pedido ped ON items.fol_folio = items.Fol_folio
                WHERE items.cve_ubicacion = '{$embarque}' AND ped.Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')
                    GROUP BY items.fol_folio 
            LIMIT {$_page}, {$limit};
    ";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if ($count >0) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages) {
        $page=$total_pages;
    }

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id'] = $row['idy_ubica'];
        $responce->rows[$i]['cell'] = [
            $row['area_descripcion'],
            $row['pedido_folio'],
            $row['pedido_fecha'],
            $row['pedido_entrega'],
            $row['pedido_total_items']
        ];
        $i++;
    }
    echo json_encode($responce);
}





if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'cargarDetallePedido') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows'] ? $_POST['rows'] : 20; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
        
    $_page = 0;
    
    $folio = $_POST['folio'];
    $almacen = $_POST['almacen'];

    if (!$sidx) {
        $sidx =1;
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = "SELECT 
                    count(ped.Fol_folio) cuenta
                FROM 
                    td_pedido ped
                WHERE Fol_folio = '{$folio}'";
    
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $_page = 0;
    
    if (intval($page)>0) {
        $_page = ($page-1) * $limit;
    }
    
    $sql = "SELECT 
                ped.Fol_folio folio,
                ped.Cve_articulo articulo_clave,
                (SELECT des_articulo FROM c_articulo WHERE cve_articulo = ped.Cve_articulo) articulo_descripcion,
                ped.Num_cantidad cantidad
            FROM 
                td_pedido ped
            WHERE ped.Fol_folio LIKE '{$folio}' 
            ORDER BY ped.itemPos DESC;
    ";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if ($count >0) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages) {
        $page=$total_pages;
    }

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id'] = $row['clave'];
        $responce->rows[$i]['cell'] = [
            $row['folio'],
            $row['articulo_clave'],
            $row['articulo_descripcion'],
            $row['cantidad'],
        ];
        $i++;
    }
    echo json_encode($responce);
}
