<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $cve_almac = $_POST['cve_almac'];
    $sqlCount = "SELECT * FROM c_almacen WHERE c_almacen.cve_almac = ".$cve_almac;
    
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $responce = utf8_encode($row['des_almac']);
    echo json_encode($responce);
}