<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Pedidos\Pedidos();

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
} if( $_POST['action'] == 'update_status' ) {
    $ga->actualizarStatus($_POST);
    $success = true;
    $arr = array(
        "success" => $success
    );
    echo json_encode($arr);
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

} if( $_POST['action'] == 'change_status' ) {
    $ga->ID_Pedido = $_POST["ID_Pedido"];
    $ga->__getChangeStatus("ID_Pedido");
    $arr = array(
        "success" => true,
        "status" => $ga->data->status
    );

    echo json_encode($arr);

}
if( $_POST['action'] == 'load' ) {
    $ga->Fol_folio = $_POST["codigo"];
    $ga->__get("Fol_folio");
    $arr = array(
        "success" => true,
    );

    $ga->__getDetalle("Fol_folio");

    foreach ($ga->data as $nombre => $valor) $arr2[$nombre] = $valor;

    $arr2["detalle"] = $ga->dataDetalle;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);

}