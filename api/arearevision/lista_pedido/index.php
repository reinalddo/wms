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
    $Fol_folio = $_POST['Fol_folio'];
    $cve_ubicacion = $_POST['cve_ubicacion'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT COUNT(*) FROM th_pedido Where th_pedido.Fol_folio = '".$Fol_folio."' AND th_pedido.cve_ubicacion = '".$cve_ubicacion."'";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    /*$sql = "SELECT th_pedido.*
            FROM th_pedido
            WHERE th_pedido.Fol_folio like '%".$_criterio."%' AND th_pedido.Fol_folio = ".$Fol_folio." AND th_pedido.cve_ubicacion = ".$cve_ubicacion."
            ORDER BY th_pedido.Fol_folio ASC LIMIT $_page, $limit";

*/
    $sql = "SELECT th_pedido.*
            FROM th_pedido
            WHERE th_pedido.Fol_folio = '".$Fol_folio."' AND th_pedido.cve_ubicacion = '".$cve_ubicacion."'
            ORDER BY th_pedido.Fol_folio ASC";

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
        $responce["rows"][$i]['id']=$row['Fol_folio'];
        $responce["rows"][$i]['cell']=array(
            $row['Fol_folio'],
            $row['cve_ubicacion']
        );
        $i++;
    }
    echo json_encode($responce);
}