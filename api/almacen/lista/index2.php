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
	 $almacen = $_POST['almacen'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ($almacen!=""){
        $split=" and c_almacenp.clave='$almacen' ";
    }

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT
            count(c_almacen.cve_almac) as cuenta,
			 c_almacen.clave_almacen,
            c_almacen.des_almac,
            c_almacen.des_direcc,
            c_almacen.Activo,
			c_almacenp.nombre
            FROM
            c_almacen ,c_almacenp
			where c_almacen.cve_almacenp= c_almacenp.id and
			(c_almacen.des_almac like '%".$_criterio."%' or c_almacen.clave_almacen like '%".$_criterio."%') and c_almacen.Activo = '1' $split;";
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
            c_almacen.cve_almac,
			 c_almacen.clave_almacen,
            c_almacen.des_almac,
            c_almacen.des_direcc,
            c_almacen.Activo,
			c_almacenp.nombre
            FROM
            c_almacen ,c_almacenp
			where c_almacen.cve_almacenp= c_almacenp.id and
			(c_almacen.des_almac like '%".$_criterio."%' or c_almacen.clave_almacen like '%".$_criterio."%') and c_almacen.Activo = '1' $split LIMIT $_page, $limit;";
			
			
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
        $responce->rows[$i]['id']=$row['cve_almac'];
        $responce->rows[$i]['cell']=array($row['cve_almac'],$row['clave_almacen'],  $row['nombre'], $row['des_almac']);
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
    $sql = "SELECT clave, nombre FROM c_almacenp WHERE Activo = 1";

    if(!empty($search) && $search != '%20'){
        $sql.= " AND nombre like '%".$search."%'";
    }

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    while($row = mysqli_fetch_array($res)){
        extract($row);
        $result [] = array(
            'clave' => $clave,
            'descripcion' => $nombre
        );
    };
    mysqli_close();
    header('Content-type: application/json');
    echo json_encode( $result);
}