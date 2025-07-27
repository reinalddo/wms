<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
	$cve_almacenp= $_POST['cve_almacenp'];
	$fecha_inicio= $_POST['fechaInicio'];
	$fecha_fin =$_POST['fechaFin'];
	$cve_almacenp= $_POST['almacen'];
    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];
	
	
	$_page = 0;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($fecha_fin)) $fecha_inicio = date("d/m/Y", strtotime($fecha_inicio));
    if (!empty($fecha_fin)) $fecha_fin = date("d/m/Y", strtotime($fecha_fin));

	if ($fecha_inicio && $fecha_fin){
		
		$split= " and th_entalmacen.Fec_Entrada >= '$fecha_inicio' and th_entalmacen.Fec_Entrada <='$fecha_fin' ";
	}
	
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT
	count(th_entalmacen.Fol_Folio) as cuenta,
	c_usuario.nombre_completo AS usuario_activo,
	th_entalmacen.Fec_Entrada AS fecha_recepcion,
	th_entalmacen.`STATUS` AS estado,
	c_proveedores.Nombre AS proveedor,
	td_entalmacen.CantidadRecibida AS cantidad_recibida
	FROM
	th_entalmacen
	LEFT JOIN c_usuario ON c_usuario.cve_usuario = th_entalmacen.Cve_Usuario
	LEFT JOIN c_proveedores ON th_entalmacen.Cve_Proveedor= c_proveedores.ID_Proveedor
	LEFT JOIN td_entalmacen ON th_entalmacen.Fol_Folio = td_entalmacen.fol_folio
	where (th_entalmacen.Fol_Folio like '%".$_criterio."%' or th_entalmacen.fol_folio like '%".$_criterio."%')  $split GROUP BY th_entalmacen.Fol_Folio LIMIT $_page, $limit;";
	
	//echo $sqlCount; exit;
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
	th_entalmacen.Fol_Folio AS numero_oc,
	c_usuario.nombre_completo AS usuario_activo,
	th_entalmacen.Fec_Entrada AS fecha_recepcion,
	th_entalmacen.`STATUS` AS estado,
	c_proveedores.Nombre AS proveedor,
	td_entalmacen.CantidadRecibida AS cantidad_recibida
	FROM
	th_entalmacen
	LEFT JOIN c_usuario ON c_usuario.cve_usuario = th_entalmacen.Cve_Usuario
	LEFT JOIN c_proveedores ON th_entalmacen.Cve_Proveedor= c_proveedores.ID_Proveedor
	LEFT JOIN td_entalmacen ON th_entalmacen.Fol_Folio = td_entalmacen.fol_folio
		where (th_entalmacen.Fol_Folio like '%".$_criterio."%' or th_entalmacen.fol_folio like '%".$_criterio."%')  $split GROUP BY th_entalmacen.Fol_Folio LIMIT $_page, $limit;";
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

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
		if ($row['estado']=="k") $row['estado']="Cancelado";
		if ($row['estado']=="p") $row['estado']="En Proceso";
		if ($row['estado']=="e") $row['estado']="Cerrada";
		if ($row['estado']=="c") $row['estado']="Completada";
        $responce->rows[$i]['id']=$row['numero_oc'];
        $responce->rows[$i]['cell']=array($row['numero_oc'],$row['proveedor'],$row['numero_oc'],$row['cantidad_recibida'],$row['fecha_recepcion'],$row['estado'],$row['usuario_activo']);
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