<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \UnidadesMedida\UnidadesMedida();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
	$arr = array(
		"success"=>$success
	);

    echo json_encode($arr);
} 

if( $_POST['action'] == 'edit' ) {
    $ga->actualizarUnidadesMedida($_POST);
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
    $ga->borrarUnidMed($_POST);
    $ga->cve_umed = $_POST["cve_umed"];
    $ga->__get("cve_umed");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} 


if( $_POST['action'] == 'load' ) {
    $ga->cve_umed = $_POST["cve_umed"];
    $ga->__get("cve_umed");
    $arr = array(
        "success" => true,
        "cve_umed" => $ga->data->cve_umed,
        "des_umed" => $ga->data->des_umed
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