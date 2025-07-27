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

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT 
	                COUNT(*) as total
	            FROM c_tipocaja 
	            WHERE (clave LIKE '%{$_criterio}%' OR
                    descripcion LIKE '%{$_criterio}%' Or 
                    largo LIKE '%{$_criterio}%' OR 
                    ancho LIKE '%{$_criterio}%' OR 
                    alto LIKE '%{$_criterio}%') AND Activo = '1';";
	
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];

    mysqli_close($conn);

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;//

    $sql = "SELECT *
            FROM c_tipocaja 
            WHERE (clave LIKE '%{$_criterio}%' or
			descripcion like '%{$_criterio}%' Or 
			largo LIKE '%{$_criterio}%' Or 
			ancho LIKE '%{$_criterio}%' Or 
			alto LIKE '%{$_criterio}%') and Activo = '1'
			ORDER BY c_tipocaja.clave {$sord}
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

    $responce["page"] = $page;
    $responce["total"] = $total_pages;
    $responce["records"] = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
		//$row=array_map($charset,$row);
        $arr[] = $row;
        $empaque = $row['Packing'] === 'S' ? '<i style="color:green" class="fa fa-check"></i>': '<i style="color:red" class="fa fa-times"></i>';
        $responce["rows"][$i]['id']=$row['id_tipocaja'];
        $responce["rows"][$i]['cell']= [
            $row['id_tipocaja'],
            $row['clave'],
            utf8_encode($row['descripcion']),
            intval($row['peso']),
            intval($row['alto']),
            intval($row['ancho']),
            intval($row['largo']),
            $empaque
        ];
        $i++;
    }
    echo json_encode($responce);
}