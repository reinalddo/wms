<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$articulos = new \Articulos\Articulos();

 if( $_POST['action'] == 'load' ) {
    $almacen = isset($_POST['almacen']) && !empty($_POST['almacen']) ? $_POST['almacen'] : '';
    $cve_proveedor = isset($_POST['cve_proveedor']) && !empty($_POST['cve_proveedor']) ? $_POST['cve_proveedor'] : '';
    $articulos = $articulos->getAllForDashboard($almacen, $cve_proveedor);

    $arr = array(
        "success" => true,
        "articulo" => "Inventario:",
        "piezas" => "Piezas",
        "texto" => $articulos->texto,
        "activo" => $articulos->activo,
        "inactivo" => $articulos->inactivo
    );

    echo json_encode($arr);
}