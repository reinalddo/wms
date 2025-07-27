<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \CortinaEntrada\CortinaEntrada();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
    } if( $_POST['action'] == 'edit' ) {
    $ga->actualizarCEntrada($_POST);
    $success = true;

    $arr = array(
        "success" => $success,
        "err" => $resp
    );
    echo json_encode($arr);
}
if( $_POST['action'] == 'exists' ) {
	
    $clave=$ga->exist($_POST["cve_ubicacion"]);
	
 

   if($clave==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
 );

    echo json_encode($arr);

} if( $_POST['action'] == 'delete' ) {
    $ga->borrarCEntrada($_POST);
    $ga->IDUbicacion = $_POST["IDUbicacion"];
    $ga->__get("IDUbicacion");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'recovery' ) {
    $ga->recovery($_POST);
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->cve_ubicacion = $_POST["cve_ubicacion"];
    $ga->__get("cve_ubicacion");
    $arr = array(
        "success" => true,
        "cve_almacp" => $ga->data->cve_almacp,
        "cve_ubicacion" => $ga->data->cve_ubicacion,
        "AreaStagging" => $ga->data->AreaStagging,
        "desc_ubicacion" => $ga->data->desc_ubicacion
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'loadPorAlmacen' ) {
   $data= $ga->loadPorAlmacen($_POST["cve_almacenp"], $_POST['excludeInventario']);
    $arr = array(
        "success" => true,
        "zonas" => $data
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'loadPorAlmacen2' ) 
{
  $data= $ga->loadPorAlmacen2($_POST["id_almacen"], $_POST['excludeInventario']);
  $arr = array(
    "success" => true,
    "zonas" => $data
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