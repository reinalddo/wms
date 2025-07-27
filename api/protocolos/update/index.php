<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Protocolos\Protocolos();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarProtocolos($_POST);
}
if( $_POST['action'] == 'exists' ) {
	$clave=$ga->exist($_POST["ID_Protocolo"]);
	
    if($clave==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
	);

    echo json_encode($arr);
} if( $_POST['action'] == 'delete' ) {
    $ga->borrarProtocolo($_POST);
    $ga->ID_Protocolo = $_POST["ID_Protocolo"];
    $ga->__get("ID_Protocolo");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->ID_Protocolo = $_POST["ID_Protocolo"];
    $ga->__get("ID_Protocolo");
    $arr = array(
        "success" => true,
        "descripcion" => $ga->data->descripcion,
        "FOLIO" => $ga->data->FOLIO,
		"ID_Protocolo" => $ga->data->ID_Protocolo
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'tieneOrden' ) {
	$ga->ID_Protocolo = $_POST["ID_Protocolo"];
	
    $ga->tieneOrden("ID_Protocolo");

    $success = false;

    if (!empty($ga->data->ID_Protocolo)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'recovery' ) {
    $ga->recovery($_POST);
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}