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
    $sqlCount = "Select count(*) as cuenta from c_poblacion Where Activo = '1'";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row["cuenta"];

    mysqli_close($conn);

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;
	
    $sql = "Select * from c_poblacion Where cve_estado like '%".$_criterio."%' and Activo = '1' Order by des_pobla ASC  LIMIT $_page, $limit;";

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
    function Sustituto_Cadena($rb){
        ## Sustituyo caracteres en la cadena final
        $rb = str_replace("ÃƒÂ¡", "&aacute;", $rb); // a
        $rb = str_replace("ÃƒÂ©", "&eacute;", $rb); // e
        $rb = str_replace("ÃƒÂ", "&iacute;", $rb); // i
        $rb = str_replace("iacute;³", "oacute;", $rb); // o
        $rb = str_replace("º", "&uacute;", $rb); // u
        $rb = str_replace("iacute;±", "ntilde;", $rb); //ñ
        return $rb;
    }
    while ($row = mysqli_fetch_array($res)) {
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['cve_pobla'];
        $responce->rows[$i]['cell']=array($row['cve_pobla'], $row['cve_estado'], utf8_encode(Sustituto_Cadena($row['des_pobla'])));
        $i++;
    }
    echo json_encode($responce);
}