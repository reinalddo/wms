<?php
include '../../../config.php';

error_reporting(0);

if(isset($_GET) && !empty($_GET)){
    $page = $_GET['page'];
    $rows = $_GET['rows'];
    $search = $_GET['search'];

    $conn = mysqli_connect('samenlinea.com', 'assistpr_de_asl', 'pDlq7-s0as}3', 'assistpr_deliveryasl');
    
    $sql = "SELECT id_operador as clave, nombre as descripcion FROM catoperadores WHERE activo = 1";

    if(!empty($search) && $search != '%20'){
        $sql.= " AND nombre '%".$search."%'";
    }

    if($page != '0' && $rows != '0'){
        $sql.= " LIMIT ".$page.", ".$rows.";";
    }

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    while($row = mysqli_fetch_array($res)){
        extract($row);
        $result[] = array(
            'clave'         =>  $clave,
            'descripcion'   =>  $descripcion
        );
    };
    mysqli_close();
    header('Content-type: application/json');
    echo json_encode( $result);
}
