<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \AreaRevision\AreaRevision();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
	
	$arr = array(
        "success" => $success,
        "err" => $resp
    );
    echo json_encode($arr);
}

if( $_POST['action'] == 'edit' ) {
    $ga->actualizarARevision($_POST);
    $success = true;

    $arr = array(
        "success" => $success,
        "err" => $resp
    );
    echo json_encode($arr);
}

if( $_POST['action'] == 'exists' ) {
	$ga->cve_ubicacion = $_POST["cve_ubicacion"];
    $ga->validaClave("cve_ubicacion");

    $success = false;

    if (!empty($ga->data->cve_ubicacion)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

} 

if( $_POST['action'] == 'delete' ) {
    $ga->borrarARevision($_POST);
    $ga->ID_URevision = $_POST["ID_URevision"];
    $ga->__get("ID_URevision");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'recovery' ) {
    $ga->recovery($_POST);
   
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} 
 


if( $_POST['action'] == 'load' ) {
    $ga->ID_URevision = $_POST["ID_URevision"];
    $ga->__get("ID_URevision");
    $arr = array(
        "success" => true,
        "cve_ubicacion" => $ga->data->cve_ubicacion,
        "cve_almac" => $ga->data->cve_almac,
        "AreaStagging" => $ga->data->AreaStagging,
        "descripcion" => $ga->data->descripcion
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'inUse' ) {
    $use = $ga->inUse($_POST);
    $arr = array(
        "success" => $use,
    );

    echo json_encode($arr);

}