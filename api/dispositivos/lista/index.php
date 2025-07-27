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
  
/*
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
  
    $sqlCount = "SELECT
                COUNT(*) as total
                FROM
				c_sgpoarticulo s
				inner join c_gpoarticulo g on s.cve_gpoart = g.cve_gpoart
                Where s.des_sgpoart like '%".$_criterio."%' and s.Activo = '1' AND s.id_almacen = {$almacen};";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];

    mysqli_close();
*/
    $sqlCriterio = "";
    if($_criterio != '')
        $sqlCriterio = " AND (IP LIKE '%$_criterio%' OR NOMBRE LIKE '%$_criterio%' OR Marca LIKE '%$_criterio%' OR Modelo LIKE '%$_criterio%' OR TIPO_IMPRESORA LIKE '%$_criterio%' OR TIPO_CONEXION LIKE '%$_criterio%' OR PUERTO LIKE '%$_criterio%') ";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;//
	
    /*$sql = "Select
        c_sgpoarticulo.cve_sgpoart,
        c_sgpoarticulo.cve_gpoart,
        c_sgpoarticulo.des_sgpoart,
        c_articulo.cve_articulo,
        c_articulo.des_articulo
    from c_sgpoarticulo INNER JOIN c_articulo ON c_sgpoarticulo.cve_gpoart = c_articulo.cve_articulo;";*/
		$sql = "SELECT IP, NOMBRE, Marca, Modelo, TIPO_IMPRESORA, TIPO_CONEXION, PUERTO FROM s_impresoras WHERE cve_almac = $almacen AND Activo = 1 {$sqlCriterio}";
            //s.cve_sgpoart,

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_num_rows($res);

    $sql .= " LIMIT $_page, $limit;";
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

    $responce["page"] = $page;
    $responce["total"] = $total_pages;
    $responce["records"] = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $arr[] = $row;
        //IP, NOMBRE, Marca, Modelo, TIPO_IMPRESORA, TIPO_CONEXION, PUERTO
        //$responce["rows"][$i]['id']=$row['cve_sgpoart'];
        $responce["rows"][$i]['cell']=array('', $row['IP'],  utf8_encode($row['NOMBRE']), utf8_encode($row['Marca']), utf8_encode($row['Modelo']), $row['TIPO_IMPRESORA'], $row['TIPO_CONEXION'], $row['PUERTO']);
        $i++;
    }
    echo json_encode($responce);
}