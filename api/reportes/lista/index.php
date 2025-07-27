<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
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
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "Select count(cve_almac) as cuenta from c_almacen Where Activo = '1';";
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
		th_entalmacen.Fol_Folio as numero_oc,
	c_usuario.nombre_completo as usuario_activo,
	th_aduana.fech_pedimento as fecha_entrega,
	th_entalmacen.Fec_Entrada as fecha_recepcion,
	sum(td_aduana.cantidad) as total_pedido,
(select sum(c_articulo.peso*td_aduana.cantidad) from c_articulo where c_articulo.cve_articulo=td_aduana.cve_articulo) as peso_estimado,
th_entalmacen.`STATUS` as estado,
c_proveedores.Nombre as proveedor,
sum(td_entalmacen.CantidadDisponible)*100/sum(td_aduana.cantidad) as porcentaje_recibido
		FROM
	th_entalmacen
	INNER JOIN c_usuario on c_usuario.cve_usuario= th_entalmacen.Cve_Usuario
	INNER JOIN th_aduana on th_aduana.num_pedimento= th_entalmacen.Fol_Folio
	INNER JOIN td_aduana  on td_aduana.num_orden =th_entalmacen.Fol_Folio
	INNER JOIN c_proveedores on th_aduana.ID_Proveedor=c_proveedores.ID_Proveedor
	INNER JOIN td_entalmacen on th_entalmacen.Fol_Folio= td_entalmacen.fol_folio
	where th_entalmacen.Fol_Folio like '%".$_criterio."%' GROUP BY th_entalmacen.Fol_Folio LIMIT $_page, $limit;";
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
        $responce->rows[$i]['id']=$row['numero_oc'];
        $responce->rows[$i]['cell']=array($row['numero_oc'],$row['proveedor'],$row['numero_oc'],  $row['total_pedido'], $row['peso_estimado'],$row['fecha_entrega'],$row['fecha_recepcion'],$row['estado'],$row['usuario_activo'],$row['porcentaje_recibido']);
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