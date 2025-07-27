<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \PedidosUrgentes\PedidosUrgentes();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarPedidosUrgentes($_POST);
} 
if( $_POST['action'] == 'exists' ) {
    $ga->Clave = $_POST["Clave"];
    $ga->__get("Clave");

    $success = false;

    if (!empty($ga->data->Clave)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);
} if( $_POST['action'] == 'delete' ) {
    $ga->borrarPedidosUrgentes($_POST);
    $ga->Clave = $_POST["Clave"];
    $ga->__get("Clave");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->Clave = $_POST["Clave"];
    $ga->__get("Clave");
    $arr = array(
        "success" => true,
        "fol_folio" => $ga->data->fol_folio,
        "descripcion" => $ga->data->descripcion,
        "Fecha" => $ga->data->Fecha
    );

    echo json_encode($arr);

}