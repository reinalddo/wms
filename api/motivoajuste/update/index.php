<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$md = new \MotivoAjuste\MotivoAjuste();

if( $_POST['action'] == 'add' ) {
    $md->save($_POST);
} if( $_POST['action'] == 'edit' ) {
    $md->actualizarMotivoDevolucion($_POST);
} 
if( $_POST['action'] == 'exists' ) {
    $clave=$md->exist($_POST["Des_Motivo"]);
	
 

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
    $md->borrarMotivoDevol($_POST);
    $md->id = $_POST["id"];
    $md->__get("id");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $md->id = $_POST["MOT_ID"];
    $md->__get("id");
    $arr = array(
        "success" => true,
        "MOT_DESC" => $md->data->Des_Motivo,
        "dev_proveedor" => $md->data->dev_proveedor,
		"Clave_motivo" => $md->data->Tipo_Cat
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