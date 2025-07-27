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
    $_fecha = $_POST['_fecha'];
    $_fechaFin = $_POST['_fechaFin'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_fecha)) $_fecha = date("Y-m-d", strtotime($_fecha));
    if (!empty($_fechaFin)) $_fechaFin = date("Y-m-d", strtotime($_fechaFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sql = "Select * from th_inventario;";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	
	var_dump($_POST["ID_Inventario"]); exit;

    $sql = "select 
			i.ID_Inventario as ID_Inventario,
			a.cve_articulo as clave,
			a.des_articulo as descripcion,
			i.cve_lote as lote,
			a.Caduca as caducidad,
			i.Cantidad as stockTeorico,
			i.NConteo as conteo
			from t_invcajas i, c_articulo a
			where i.cve_articulo=a.cve_articulo
			and i.ID_Inventario=".$_POST['ID_Inventario'].";";

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
        $responce->rows[$i]['id']=$row['ID_Inventario'];
        $responce->rows[$i]['cell']=array($row['ID_Inventario'], $row['clave'], $row['descripcion'], $row['lote'], $row['caducidad'], $row['stockTeorico'], $row['conteo']);
        $i++;
    }
    echo json_encode($responce);
}