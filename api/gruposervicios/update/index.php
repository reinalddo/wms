<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \GrupoServicios\GrupoServicios();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarGrupoServicios($_POST);
}
if( $_POST['action'] == 'exists' ) {
	
    $clave=$ga->exist($_POST["clave"]);

   if($clave==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
 );

    echo json_encode($arr);

} if( $_POST['action'] == 'delete' ) {
    $ga->borrarGrupoServicios($_POST);
    $ga->Cve_GpoServicio = $_POST["Cve_GpoServicio"];
    $ga->__get("Cve_GpoServicio");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->Cve_GpoServicio = $_POST["codigo"];
    $ga->__get("Cve_GpoServicio");
    $arr = array(
        "success" => true,
        "codigo" => $ga->data->Cve_GpoServicio,
        "descripcion" => $ga->data->Des_GpoServicio
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

if( $_POST['action'] == 'inUse' ) {
    $use = $ga->inUse($_POST);
    $arr = array(
        "success" => $use,
    );

    echo json_encode($arr);

}

