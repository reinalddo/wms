<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \TipoTransporte\TipoTransporte();

if( $_POST['action'] == 'add' ) {

    $ga->save($_POST);
    } if( $_POST['action'] == 'edit' ) {
    $ga->actualizarTipoTransporte($_POST);
    $success = true;

    $arr = array(
        "success" => $success,
        "err" => $resp
    );
    echo json_encode($arr);
}
if( $_POST['action'] == 'exists' ) {
    $clave=$ga->exist($_POST["clave_ttransporte"]);
	
 

   if($clave==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
 );

    echo json_encode($arr);

} if( $_POST['action'] == 'delete' ) {
    $ga->borrarTipoTransporte($_POST);
    $ga->clave_ttransporte = $_POST["clave_ttransporte"];
    $ga->__get("clave_ttransporte");

    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->clave_ttransporte = $_POST["clave_ttransporte"];
    $ga->__get("clave_ttransporte");
    $arr = array(
        "success" => true,
        "clave_ttransporte" => $ga->data->clave_ttransporte,
        "alto" => $ga->data->alto,
        "fondo" => $ga->data->fondo,
        "ancho" => $ga->data->ancho,
        "capacidad_carga" => $ga->data->capacidad_carga,
        "desc_ttransporte" => $ga->data->desc_ttransporte,
        "imagen" => $ga->data->imagen,
        "Activo" => $ga->data->Activo,
		"capacidad_volumetrica" => $ga->data->capacidad_volumetrica
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'recovery' ) {
    $ga->recoveryTipoTransporte($_POST);
    $ga->id = $_POST["id"];
    $ga->__get("id");
    $arr = array(
        "success" => true,
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