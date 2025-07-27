<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $idy_ubica = $_POST['idy_ubica'];


    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT COUNT(cve_articulo) as total
                FROM V_ExistenciaGralProduccion v
                WHERE v.cve_ubicacion = '$idy_ubica' AND v.Existencia > 0;";

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];

	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;//
	
    $sql = "SELECT  v.cve_articulo AS clave,
                    a.des_articulo AS articulo,
                    u.CodigoCSD AS ubicacion,
                    v.cve_lote AS lote,
                    l.CADUCIDAD AS caducidad,
                    v.Existencia AS existencia
            FROM V_ExistenciaGralProduccion v
            LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = v.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = v.cve_lote AND l.cve_articulo = v.cve_articulo
            WHERE v.cve_ubicacion = '$idy_ubica'; ";
			
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
        $row = array_map("utf8_encode", $row);
        extract($row);
        $responce["rows"][$i]['id']=$i;
        $responce["rows"][$i]['cell']=array(
            $clave,
            $articulo,
            $ubicacion,
            $lote,
            $caducidad,
            $existencia
        );
        $i++;
    }
    echo json_encode($responce);
}