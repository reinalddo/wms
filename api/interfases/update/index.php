<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Interfases\Interfases();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
	$arr = array(
		"success"=>$success
	);

    echo json_encode($arr);
} 

if( $_POST['action'] == 'edit' ) {
    $ga->actualizarProyectos($_POST);
	$arr = array(
		"success"=>$success
	);

    echo json_encode($arr);
} 

if( $_POST['action'] == 'enviar_cadena' ) {
    $success = $ga->EnviarCadena($_POST);

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
    $ga->borrarProyecto($_POST);
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
        "clave" => $ga->data->Cve_Proyecto,
        "descripcion" => $ga->data->Des_Proyecto
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