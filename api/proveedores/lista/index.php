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
	$ands ="";
    if(!empty($_criterio)){
        $ands = " AND (p.cve_proveedor like '%$_criterio%' OR p.Nombre  like '%$_criterio%')";
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "Select count(ID_Proveedor) as cuenta from c_proveedores p
	left join c_dane d on d.cod_municipio=p.cve_dane
		Where Activo = '1'  $ands;";
	
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row["cuenta"];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;
	
    $sql = "SELECT p.*, 
    IF(IFNULL(p.es_transportista, 0) = 1, 'Si', 'No') as es_transportadora,
    d.departamento, d.des_municipio 
    from c_proveedores p
	left join c_dane d on d.cod_municipio=p.cve_dane
		Where p.Activo = '1' $ands LIMIT $_page, $limit;";

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
		$row=array_map('utf8_encode',$row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['ID_Proveedor'];
        $responce->rows[$i]['cell']=array('', $row['ID_Proveedor'],$row['cve_proveedor'], $row['Nombre'], $row['RUT'], $row['direccion'], $row['cve_dane'], $row['estado'], $row['ciudad'], $row['es_transportadora']);
        $i++;
    }
    echo json_encode($responce);
}
