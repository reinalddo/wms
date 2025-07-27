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

   
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "Select count(c_articulo.cve_articulo) as cuenta, 
			ts_ubicxart.CapacidadMinima as stock_minimo,
			ts_ubicxart.CapacidadMaxima as stock_maximo,
			c_gpoarticulo.des_gpoart as grupoa,
			c_sgpoarticulo.des_sgpoart as clasificaciona,
			c_ssgpoarticulo.des_ssgpoart as tipoa
            from c_articulo 
			left join ts_ubicxart
			on c_articulo.cve_articulo = ts_ubicxart.cve_articulo			
		    left join c_gpoarticulo
			on c_articulo.grupo = c_gpoarticulo.cve_gpoart	
			left join c_sgpoarticulo
			on c_articulo.clasificacion = c_sgpoarticulo.cve_sgpoart				
			left join c_ssgpoarticulo
			on c_articulo.tipo = c_ssgpoarticulo.cve_ssgpoart	
            Where (c_articulo.des_articulo like '%".$_criterio."%' or c_articulo.cve_codprov like '%".$_criterio."%' or c_articulo.cve_articulo like '%".$_criterio."%') 
			and c_articulo.Activo = '0'";

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close($conn);

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;
	
    $sql = "Select c_articulo.*, 
			ts_ubicxart.CapacidadMinima as stock_minimo,
			ts_ubicxart.CapacidadMaxima as stock_maximo,
			c_gpoarticulo.des_gpoart as grupoa,
			c_sgpoarticulo.des_sgpoart as clasificaciona,
			c_ssgpoarticulo.des_ssgpoart as tipoa
            from c_articulo 
			left join ts_ubicxart
			on c_articulo.cve_articulo = ts_ubicxart.cve_articulo			
		    left join c_gpoarticulo
			on c_articulo.grupo = c_gpoarticulo.cve_gpoart	
			left join c_sgpoarticulo
			on c_articulo.clasificacion = c_sgpoarticulo.cve_sgpoart				
			left join c_ssgpoarticulo
			on c_articulo.tipo = c_ssgpoarticulo.cve_ssgpoart	
            Where (c_articulo.des_articulo like '%".$_criterio."%' or c_articulo.cve_codprov like '%".$_criterio."%' or c_articulo.cve_articulo like '%".$_criterio."%')
			and c_articulo.Activo = '0' LIMIT $_page, $limit;";
			
    // hace una llamada previa al procedimiento 
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
        $row=array_map('utf8_encode', $row);
		$arr[] = $row;
        $responce->rows[$i]['id']=$row['cve_tipcaja'];
        $responce->rows[$i]['cell']=array($row["id"],$row["cve_articulo"],$row["cve_codprov"],$row["des_articulo"],$row["peso"],$row["barras2"],$row["num_multiplo"],$row["barras3"],$row["cajas_palet"],$row["grupoa"],$row["clasificaciona"],$row["tipoa"]);
        $i++;
    }
    echo json_encode($responce);
}