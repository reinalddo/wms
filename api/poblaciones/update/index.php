<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Poblaciones\Poblaciones();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);

    $success = true;

    $arr = array(
        "success" => $success
        //"err" => "El Número del Folio ya se Ha Introducido"
    );

    echo json_encode($arr);
    /* if (!$success) {
         exit();
     }*/
} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarPoblaciones($_POST);

    $success = true;

    $arr = array(
        "success" => $success
        //"err" => "El Número del Folio ya se Ha Introducido"
    );

    echo json_encode($arr);
} 
if( $_POST['action'] == 'exists' ) {
    $ga->ID_Tipoprioridad = $_POST["ID_Tipoprioridad"];
    $ga->__get("ID_Tipoprioridad");

    $success = false;

    if (!empty($ga->data->ID_Tipoprioridad)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);
} if( $_POST['action'] == 'delete' ) {
    $ga->borrarPoblaciones($_POST);
    $ga->cve_pobla = $_POST["cve_pobla"];
    $ga->__get("cve_pobla");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->cve_pobla = $_POST["cve_pobla"];
    $ga->__get("cve_pobla");
    $arr = array(
        "success" => true,
        "cve_estado" => $ga->data->cve_estado,
        "des_pobla" => $ga->data->des_pobla
    );

    echo json_encode($arr);

}