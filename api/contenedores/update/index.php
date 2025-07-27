<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Contenedores\Contenedores();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
    $arr = array(
		"success"=>true
	);
    echo json_encode($arr);
} 
	
	
if( $_POST['action'] == 'edit' ) {
    $ga->actualizarContenedor($_POST);
    $success = true;

    $arr = array(
        "success" => $success,
        "err" => $resp
    );
    echo json_encode($arr);
}

if( $_POST['action'] == 'exists' ) {
	
    $clave=$ga->exist($_POST["clave_contenedor"]);
	
 

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
    $ga->borrarContenedor($_POST);
    $ga->clave_contenedor = $_POST["clave_contenedor"];
    $ga->__get("clave_contenedor");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->IDContenedor = $_POST["IDContenedor"];
    $ga->__get("IDContenedor");
    $arr = array(
        "success" => true,
        "cve_almac" => $ga->data->cve_almac,
		"descripcion" => $ga->data->descripcion,
        "clave_contenedor" => $ga->data->Clave_Contenedor,
		"ancho" => $ga->data->ancho,
		"alto" => $ga->data->alto,
		"fondo" => $ga->data->fondo,		
		"peso" => $ga->data->peso,	
		"pesomax" => $ga->data->pesomax,	
		"capavol" => $ga->data->capavol,		
        "tipo" => $ga->data->tipo,
        "TipoGenVal" => $ga->data->TipoGen
    );

    echo json_encode($arr);

}


if( $_POST['action'] == 'recovery' ) {
    $ga->recovery($_POST);
    $ga->IDContenedor = $_POST["IDContenedor"];
    $ga->__get("IDContenedor");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'inUse' ) {
    $data = $ga->inUse($_POST);
    $arr = array(
        "success" => $use,
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'loadcon' ) {
    $data = $ga->loadcon($_POST);
     echo json_encode(array(
    "success" => true,
    "data"  => $data[0],
  ));
}