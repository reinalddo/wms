<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \TipoCompania\TipoCompania();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarTipoCompania($_POST);
} 
if( $_POST['action'] == 'exists' ) {
	
    $clave=$ga->exist($_POST["clave_tcia"]);
	
 

   if($clave==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
 );

    echo json_encode($arr);

}
if( $_POST['action'] == 'validar' ) {
    
    $clave=$ga->exist($_POST["clave_tcia"]);
    
 

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
    $ga->borrarTipoCompania($_POST);
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->cve_tipcia = $_POST["cve_tipcia"];
    $ga->__get("cve_tipcia");
    $arr = array(
        "success" => true,
        "cve_tipcia" => $ga->data->cve_tipcia,
        "des_tipcia" => $ga->data->des_tipcia,
        //"es_transportista" => $ga->data->es_transportista,
		"clave_tcia" => $ga->data->clave_tcia
		);

    echo json_encode($arr);

}

if( $_POST['action'] == 'tieneEmpresa' ) {
    $ga->cve_tipcia = $_POST["cve_tipcia"];
    
    $ga->tieneEmpresa("cve_tipcia");
    $success = false;

    if (!empty($ga->data->cve_tipcia)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);
}
if( $_POST['action'] == 'recovery' ) {
    $ga->recovery($_POST);
    $ga->cve_tipcia = $_POST["cve_tipcia"];
    $ga->__get("cve_tipcia");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}