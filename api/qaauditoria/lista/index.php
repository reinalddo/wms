<?php
include '../../../config.php';

error_reporting(0);


if (isset($_POST) && ! empty($_POST) && $_POST['acction'] == 'ver') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $criterio = $_POST['criterio'];
    $status = $_POST['status'];
    $tipo = $_POST['tipo'];

    if( ! empty($criterio) ){
        $sqlWhere = " (pe.Fol_Folio LIKE '%{$criterio}%' OR pe.id_pedido = '{$criterio}') AND ";
    }

    if( ! empty($status) ){
        $sqlWhereStatus = " AND pe.status = '{$status}'";
    }
    else {
        $sqlWhereStatus = " AND pe.status IN ('L','R') ";
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT 
                    count(pe.`id_pedido`) as total
                FROM c_almacenp AS p
                    INNER JOIN t_ubicaciones_revision AS u
                    INNER JOIN th_pedido AS pe 
                    INNER  JOIN cat_estados e ON e.ESTADO = pe.status
                WHERE u.cve_almac = p.clave and pe.status in ('L','R') AND p.activo = 1 AND u.activo =1
		";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT 
                pe.`id_pedido` AS consecutivo,
                pe.`id_pedido` AS pedido,
                pe.`Fol_folio` AS folio,
                IFNULL(c.RazonSocial, '--') AS cliente,
                IFNULL(e.DESCRIPCION, '--') AS STATUS,
                IFNULL(DATE_FORMAT(pe.Fec_Entrada, '%d-%m-%Y'), '--') AS fecha_pedido,
                IFNULL(DATE_FORMAT(pe.Fec_Entrega, '%d-%m-%Y'), '--') AS fecha_entrega,
                u.descripcion AS mesa, 
                IFNULL(us.nombre_completo, '--') AS usuario
            FROM c_almacenp AS p
                INNER JOIN t_ubicaciones_revision AS u
                INNER JOIN th_pedido AS pe
                LEFT JOIN c_cliente c On c.Cve_Clte = pe.Cve_clte
                INNER  JOIN cat_estados e ON e.ESTADO = pe.status
                LEFT JOIN c_usuario us ON us.cve_usuario = pe.Cve_Usuario
            WHERE {$sqlWhere} (u.cve_almac = p.clave AND p.activo = 1 AND u.activo = 1 {$sqlWhereStatus})
                GROUP BY pe.`id_pedido`";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
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
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['pedido'];
        $responce->rows[$i]['cell']=array(
                        
                                          $row['consecutivo'],
                                          $row['pedido'], 
                                          $row['folio'],
                                          $row['cliente'],
                                          $row['STATUS'],
                                          $row['fecha_pedido'],
                                          $row['fecha_entrega'],
                                          $row['mesa'], 
                                          $row['usuario']
                                        );
        $i++;
    }
    echo json_encode($responce);exit;
}


if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];
	$mesa = $_POST['mesa'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT 
			count(pe.`id_pedido`) as total
		FROM c_almacenp AS p
		INNER JOIN t_ubicaciones_revision AS u
		INNER JOIN th_pedido AS pe 
		INNER  JOIN cat_estados e ON e.ESTADO = pe.status
		WHERE u.cve_almac = p.clave and pe.status in ('L','R')
		AND p.activo = 1 AND u.activo =1
		";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT 
			pe.`id_pedido` AS consecutivo,
            pe.`id_pedido` AS pedido,
			pe.`Fol_folio` AS folio,
			IFNULL(c.RazonSocial, '--') AS cliente,
			IFNULL(e.DESCRIPCION, '--') AS STATUS,
			IFNULL(DATE_FORMAT(pe.Fec_Entrada, '%d-%m-%Y'), '--') AS fecha_pedido,
			IFNULL(DATE_FORMAT(pe.Fec_Entrega, '%d-%m-%Y'), '--') AS fecha_entrega,
			u.descripcion AS mesa, 
			IFNULL(us.nombre_completo, '--') AS usuario
		FROM c_almacenp AS p
		INNER JOIN t_ubicaciones_revision AS u
		INNER JOIN th_pedido AS pe
        LEFT JOIN c_cliente c On c.Cve_Clte = pe.Cve_clte
		INNER  JOIN cat_estados e ON e.ESTADO = pe.status
		LEFT JOIN c_usuario us ON us.cve_usuario = pe.Cve_Usuario
		WHERE u.cve_almac = p.clave
		AND p.activo = 1 AND u.activo =1 and pe.status in ('L','R')
		GROUP BY pe.`id_pedido`";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
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
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['pedido'];
        $responce->rows[$i]['cell']=array($row['consecutivo'],$row['pedido'], $row['folio'],$row['cliente'],$row['STATUS'],$row['fecha_pedido'],$row['fecha_entrega'],$row['mesa'], $row['usuario']);
        $i++;
    }
    echo json_encode($responce);
}