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
	$folio = $_POST['pedido'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT count(*)			
				from td_pedido, th_pedido, c_articulo, c_lotes
				where td_pedido.Fol_folio=th_pedido.Fol_folio and
				c_articulo.cve_articulo=td_pedido.Cve_articulo and c_lotes.cve_articulo=c_articulo.cve_articulo			
				and th_pedido.Fol_folio like '".$folio."';";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "select 
			td_pedido.Cve_articulo as clave,
			c_articulo.des_articulo as nombre,
			c_lotes.LOTE as lote,
			c_articulo.tipo as tipo,
			td_pedido.Num_cantidad as pedidas
			from td_pedido, th_pedido, c_articulo, c_lotes
			where td_pedido.Fol_folio=th_pedido.Fol_folio and
			c_articulo.cve_articulo=td_pedido.Cve_articulo and c_lotes.cve_articulo=c_articulo.cve_articulo			
			and th_pedido.Fol_folio like '".$folio."';";

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
        $responce->rows[$i]['id']=$row['clave'];
        $responce->rows[$i]['cell']=array($row['clave'],$row['nombre'], $row['lote'], $row['tipo'], $row['pedidas'],'0');
        $i++;
    }
    echo json_encode($responce);
}