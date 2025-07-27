<?php
include '../../../app/load.php';

error_reporting(0);

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$i = new \QaGuia\QaGuia();


if( $_POST['action'] == 'loadDetalle' ) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $id = $_POST["ID_PLAN"];

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)
    $count = $i->loadDetalleCount($id);

    if(!$sidx) $sidx =1;

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;


    $data=$i->loadDetalleCi($id, $start, $limit);
    $i = 0;
    foreach($data as $art){
        extract($art);
        $responce->rows[$i]['id'] = $art['id'];
        $responce->rows[$i]['cell']= array($Cve_CajaMix, $TipoCaja, $Guia,$abierta,$Peso);
        $i++;
    }

    echo json_encode($responce);

} 



if($_POST['oper'] === 'edit'){

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "UPDATE  th_cajamixta 
            SET Guia = {$_POST['guia']}
            WHERE Cve_CajaMix = {$_POST['id']}; ";
    $query = mysqli_query($conn, $sql);


    mysqli_close($conn);
    echo json_encode(array(
        "success"   =>  $query
    ));
}