<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \AdminPedidos\AdminPedidos();

if( $_POST['action'] == 'add' ) {
    $ga->Fol_folio = $_POST["Fol_folio"];
    $ga->__get("Fol_folio");

    $success = true;

    if (!empty($ga->data->Fol_folio)) {
        $success = false;
    }

    $arr = array(
        "success" => $success,
        "err" => "El Número del Folio ya se Ha Introducido"
    );

    if (!$success) {
        echo json_encode($arr);
        exit();
    }

    $ga->save($_POST);
    echo json_encode($arr);
    exit();
} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarPedido($_POST);

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
    $ga->borrarPedido($_POST);
    $ga->Fol_folio = $_POST["Fol_folio"];
    $ga->__get("Fol_folio");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->Fol_folio = $_POST["codigo"];
    $ga->__get("Fol_folio");
    $arr = array(
        "success" => true,
    );


    echo json_encode($arr);

}