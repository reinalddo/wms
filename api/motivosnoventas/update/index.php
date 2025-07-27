<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$md = new \MotivoNoVentas\MotivoNoVentas();

if( $_POST['action'] == 'add' ) {
    $md->save($_POST);
} if( $_POST['action'] == 'edit' ) {
    $md->actualizarMotivoNoVentas($_POST);
} 
if( $_POST['action'] == 'exists' ) {
    $clave=$md->exist($_POST["Clave_motivo"]);
	
 

   if($clave==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
 );

    echo json_encode($arr);

}

if( $_POST['action'] == 'delete' ) {
    $md->borrarMotivoNoVentas($_POST);
    $md->IdMot = $_POST["MOT_ID"];
    $md->__get("IdMot");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $md->IdMot = $_POST["MOT_ID"];
    $md->__get("IdMot");
    $arr = array(
        "success" => true,
        "MOT_DESC" => $md->data->Motivo,
		"Clave_motivo" => $md->data->Clave
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'recovery' ) {
    $md->recovery($_POST);
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'inUse' ) {
    $use = $md->inUse($_POST);
    $arr = array(
        "success" => $use,
    );

    echo json_encode($arr);

}