<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Servicios\Servicios();

if( $_POST['action'] == 'add' ) {
    $success = $ga->save($_POST);
	$arr = array(
		"success"=>$success
	);

    echo json_encode($arr);
} 

if( $_POST['action'] == 'edit' ) {
    $ga->actualizarServicios($_POST);
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
    $ga->borrarServicio($_POST);
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
        "clave" => $ga->data->Cve_Servicio,
        "descripcion" => $ga->data->Des_Servicio,
        "iva" => $ga->data->IVA,
        "unimed" => $ga->data->UniMedida
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