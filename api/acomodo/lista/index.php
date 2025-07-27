<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
	$cve_almacen= $_POST['almacen'];
	$fecha_inicio= $_POST['fechaInicio'];
	$fecha_fin =$_POST['fechaFin'];
	$zona =$_POST['zona'];
	$cadui= $_POST['cadui'];
	$caduf =$_POST['caduf'];
    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];
	
	
	$_page = 0;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($fecha_fin)) $fecha_inicio = date("d/m/Y", strtotime($fecha_inicio));
    if (!empty($fecha_fin)) $fecha_fin = date("d/m/Y", strtotime($fecha_fin));

	if ($fecha_inicio && $fecha_fin){
		
		$split= " and x.fecha >= '$fecha_inicio' and x.fecha <='$fecha_fin' ";
	}
	
	if ($cadui && $caduf){
		
		$split= $split." and str_to_date(l.CADUCIDAD, '%d-%m-%Y') >=  str_to_date('$cadui', '%d/%m/%Y') and str_to_date(l.CADUCIDAD, '%d-%m-%Y') <= str_to_date('$caduf', '%d/%m/%Y') ";
	}
	
	if ($zona){
		$split= $split." and c_almacen.clave_almacen = '".$zona."' ";
	}
	
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT
                    count(a.nombre) as cuenta,
                    s.des_usuario AS usuario,
                    x.fecha AS fecha,
                    x.cve_articulo + ' - ' + p.des_articulo AS producto,
                    x.Cantidad AS cantidad,
                    x.cve_lote AS lote,
                    l.Caducidad AS caducidad,
                    e.cve_ubicacion AS zona_recepcion,
                    c_almacen.des_almac AS zona_almacenaje,
                    u.CodigoCSD AS ubicacion,
                    u.Tipo AS tipo_ubicacion,
                    x.fecha AS hora_fin
                FROM t_cardex x
                    LEFT JOIN th_entalmacen e ON x.origen = e.Fol_Folio
                    LEFT JOIN c_almacenp a ON x.Cve_Almac = a.clave
                    LEFT JOIN c_articulo p ON x.cve_articulo = p.cve_articulo AND x.Cve_Almac = p.cve_almac
                    LEFT JOIN c_lotes l ON x.cve_articulo = l.cve_articulo AND x.cve_lote = l.LOTE
                    LEFT JOIN c_usuario s ON x.cve_usuario = s.cve_usuario
                    LEFT JOIN c_ubicacion u ON x.destino = u.idy_ubica
                    LEFT JOIN c_almacen ON c_almacen.clave_almacen= x.Cve_Almac AND x.Cve_Almac = u.cve_almac 
                WHERE x.id_TipoMovimiento = 2 AND x.Cve_Almac='{$cve_almacen}'
                AND (p.des_articulo LIKE '%".$_criterio."%' or x.cve_articulo like '%".$_criterio."%' or x.cve_lote like '%".$_criterio."%') $split";
                
//	echo $sqlCount; exit;
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;
	
    $sql = "SELECT
                a.nombre AS almacen,
                s.nombre_completo AS usuario,
                x.fecha AS fecha,
                x.cve_articulo as clave_producto,
                p.des_articulo AS producto,
                x.Cantidad as cantidad,
                x.cve_lote AS lote,
                l.Caducidad AS caducidad,
                e.cve_ubicacion AS zona_recepcion,
                c_almacen.des_almac as zona_almacenaje,
                u.CodigoCSD AS ubicacion,
                u.Tipo as tipo_ubicacion,
                x.fecha as hora_fin
            FROM t_cardex x
                LEFT JOIN th_entalmacen e ON x.origen = e.Fol_Folio
                JOIN c_almacenp a ON x.Cve_Almac = a.clave
                JOIN c_articulo p ON x.cve_articulo = p.cve_articulo AND x.Cve_Almac = p.cve_almac
                LEFT JOIN c_lotes l ON x.cve_articulo = l.cve_articulo AND x.cve_lote = l.LOTE
                LEFT JOIN c_usuario s ON x.cve_usuario = s.cve_usuario
                LEFT JOIN c_ubicacion u ON x.destino = u.idy_ubica
                LEFT JOIN c_almacen ON c_almacen.clave_almacen= x.Cve_Almac AND x.Cve_Almac = u.cve_almac 
            WHERE x.id_TipoMovimiento = 2 AND x.Cve_Almac='{$cve_almacen}' AND (p.des_articulo LIKE '%".$_criterio."%' or x.cve_articulo like '%".$_criterio."%' or x.cve_lote like '%".$_criterio."%')  $split
	LIMIT $_page, $limit;";
	//echo $sql; exit;
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

    $i = 0;

    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
		if ($row['tipo_ubicacion']=="L") $row['tipo_ubicacion']="Libre";
		if ($row['tipo_ubicacion']=="R") $row['tipo_ubicacion']="Restringida";
		if ($row['tipo_ubicacion']=="Q") $row['tipo_ubicacion']="Cuarentena";
		
        $responce->rows[$i]['id'] = "";
        $responce->rows[$i]['cell'] = [
            $row['almacen'],
            $row['usuario'],
            $row['fecha'],
            $row['clave_producto'],
            $row['producto'],
            $row['cantidad'],
            $row['lote'], 
            $row['caducidad'],
            $row['serie'],
            $row['zona_recepcion'],
            $row['zona_almacenaje'],
            $row['ubicacion'],
            $row['tipo_ubicacion'],
            $row['hora_fin']
        ];
        $i++;
    }
    
    echo json_encode($responce);
}



if(isset($_GET) && !empty($_GET)){
    $page = $_GET['page'];
    $rows = $_GET['rows'];
    $search = $_GET['search'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    /*Pedidos listos para embarcar*/
    $sql = "SELECT cve_almac, des_almac FROM c_almacen WHERE Activo = 1";

    if(!empty($search) && $search != '%20'){
        $sql.= " AND des_almac like '%".$search."%'";
    }

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    while($row = mysqli_fetch_array($res)){
        extract($row);
        $result [] = array(
            'clave' => $cve_almac,
            'descripcion' => $des_almac
        );
    };
    mysqli_close();
    header('Content-type: application/json');
    echo json_encode( $result);
}