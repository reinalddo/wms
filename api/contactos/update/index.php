<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Contactos\Contactos();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
	$arr = array(
		"success"=>$success
	);

    echo json_encode($arr);
} 

if( $_POST['action'] == 'edit' ) {
    $ga->actualizarContactos($_POST);
	$arr = array(
		"success"=>$success
	);

    echo json_encode($arr);
} 


if( $_POST['action'] == 'exists' ) {
    $clave=$ga->exist($_POST["cve_umed"]);
	
 

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
    $ga->borrarContacto($_POST);
    $ga->clave = $_POST["clave"];
    $ga->__get("clave");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} 


if( $_POST['action'] == 'load' ) {
    $ga->clave = $_POST["clave"];
    $ga->__get("clave");
    $arr = array(
        "success" => true,
        "clave" => $ga->data->clave,
        "nombre" => $ga->data->nombre,
        "apellido" => $ga->data->apellido,
        "correo" => $ga->data->correo,
        "telefono1" => $ga->data->telefono1,
        "telefono2" => $ga->data->telefono2,
        "pais" => $ga->data->pais,
        "estado" => $ga->data->estado,
        "ciudad" => $ga->data->ciudad,
        "direccion" => $ga->data->direccion
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

if( $_POST['action'] == 'inUse' ) {
    $use = $ga->inUse($_POST);
    $arr = array(
        "success" => $use,
    );

    echo json_encode($arr);

}