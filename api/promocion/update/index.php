<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Promocion\Promocion();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
    $success = true;

    $err = ($ga->resultado=="No existe el Artículo") ? $ga->resultado : "";

    $arr = array(
        "success" => $success,
        "err" => $ga->resultado
    );

    echo json_encode($arr);
} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarPromocion($_POST);

    $success = true;

    $arr = array(
        "success" => $success
        //"err" => "El Número del Folio ya se Ha Introducido"
    );

    echo json_encode($arr);
   /* if (!$success) {
        exit();
    }*/
} if( $_POST['action'] == 'exists' ) {

} if( $_POST['action'] == 'delete' ) {
    $ga->borrarPromocion($_POST);
    $ga->IDpromo = $_POST["IDpromo"];
    $ga->__get("IDpromo");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'loadArt' ) {
    $ga->IDpromo = $_POST["codigo"];
    $ga->__getDetalleArt("IDpromo");
    $arr = array(
        "success" => true,
    );

    $ga->__getDetalleArt("IDpromo");
    //$ga->__getDetalle("IDpromo");

    foreach ($ga->dataDetalleArt as $nombre => $valor) $arr2[$nombre] = $valor;

    //$arr2["detalle"] = $ga->dataDetalle;
    $arr2["detalle"] = $ga->dataDetalleArt;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->IDpromo = $_POST["codigo"];
    $ga->__get("IDpromo");
    $arr = array(
        "success" => true,
    );

    $ga->__getDetalle("IDpromo");

    foreach ($ga->data as $nombre => $valor) $arr2[$nombre] = $valor;

    $arr2["detalle"] = $ga->data;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);

}