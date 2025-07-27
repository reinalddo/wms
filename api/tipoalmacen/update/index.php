<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \TipoAlmacen\TipoAlmacen();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarTipoAlmacen($_POST);
} 
if( $_POST['action'] == 'exists' ) {
	
    $clave=$ga->exist($_POST["clave_talmacen"]);
	
 

   if($clave==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
 );

    echo json_encode($arr);

}  if( $_POST['action'] == 'delete' ) {
    $ga->borrarTipoAlmacen($_POST);
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->id = $_POST["id"];
    $ga->__get("id");
    $arr = array(
        "success" => true,
        "clave_talmacen" => $ga->data->clave_talmacen,
        "desc_tipo_almacen" => $ga->data->desc_tipo_almacen,
		"id" => $ga->data->id
		);

    echo json_encode($arr);

}

if( $_POST['action'] == 'tieneAlmacen' ) {
    $ga->id = $_POST["id"];
    
    $ga->tieneAlmacen("id");
    $success = false;

    if (!empty($ga->data->id)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

}
if( $_POST['action'] == 'recovery' ) {
    $ga->recovery($_POST);
    $ga->id = $_POST["id"];
    $ga->__get("id");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}