<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \OrdenCompra\OrdenCompra();

if( $_POST['action'] == 'add' ) {
    $ga->ID_Aduana = $_POST["num_pedimento"];
    $ga->__get("ID_Aduana");

    $success = true;

    if (!empty($ga->data->ID_Aduana)) {
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
    $ga->actualizarOrden($_POST);
} if( $_POST['action'] == 'exists' ) {

} if( $_POST['action'] == 'delete' ) {
    $ga->borrarCliente($_POST);
    $ga->Cve_Clte = $_POST["Cve_Clte"];
    $ga->__get("Cve_Clte");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->ID_Aduana = $_POST["codigo"];
    $ga->__get("ID_Aduana");
    $arr = array(
        "success" => true,
    );

    $ga->__getDetalle("ID_Aduana");

    foreach ($ga->data as $nombre => $valor) $arr2[$nombre] = $valor;

    $arr2["detalle"] = $ga->dataDetalle;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);

}