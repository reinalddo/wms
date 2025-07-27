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

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "Select count(*) as cuenta 
			FROM
            c_charolas c, c_almacenp a
			where a.clave=c.cve_almac and c.Activo = '0';";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row["cuenta"];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;
	
    $sql = "SELECT
            c.IDContenedor,
            c.cve_almac,
			c.descripcion,
            c.peso,
			c.pesomax,
			c.capavol,
			c.alto,
			c.ancho,
			c.fondo,
            c.tipo,
			c.clave_contenedor,
			a.nombre as des_almac
            FROM
            c_charolas c
            left join c_almacenp a on a.id = c.cve_almac
           where a.id=c.cve_almac and c.cve_almac = $almacen and c.Activo = 0
           LIMIT $_page, $limit;";

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
        $arr[] = $row;
		$row['peso']=str_replace('.',',',$row['peso']);
		$row['pesomax']=str_replace('.',',',$row['pesomax']);
        $responce->rows[$i]['id']=$row['IDContenedor'];
        $responce->rows[$i]['cell']=array($row['IDContenedor'], $row['clave_contenedor'], $row['descripcion'], $row['des_almac'], $row['tipo'], $row['alto'], $row['ancho'], $row['fondo'],  number_format($row['peso'], 2, '.', ','), number_format($row['pesomax'], 2, '.', ',') , number_format($row['capavol'], 2, '.', ','));
        $i++;
    }
    echo json_encode($responce);
}