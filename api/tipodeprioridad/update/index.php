<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \TipoPrioridad\TipoPrioridad();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
	$arr = array(
        "success" => $success,
    );
    echo json_encode($arr);
} 

if( $_POST['action'] == 'edit' ) {
    $ga->actualizarPrioridad($_POST);
	$arr = array(
        "success" => $success,
    );
    echo json_encode($arr);
} 
if( $_POST['action'] == 'exists' ) {
    $ga->Clave = $_POST["Clave"];
    $ga->validaClave("Clave");

    $success = false;

    if (!empty($ga->data->Clave)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'exists2' ) {
   $clave=$ga->exist($_POST["prioridad"]);
   

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
    $ga->borrarTipoPrioridad($_POST);
    $ga->ID_Tipoprioridad = $_POST["ID_Tipoprioridad"];
    $ga->__get("ID_Tipoprioridad");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} 

if( $_POST['action'] == 'load' ) {
    $ga->ID_Tipoprioridad = $_POST["ID_Tipoprioridad"];
    $ga->__get("ID_Tipoprioridad");
    $arr = array(
        "success" => true,
		"Clave" => $ga->data->Clave,
        "Descripcion" => $ga->data->Descripcion,
        "Prioridad" => $ga->data->Prioridad,
    
    );
    echo json_encode($arr);

}

if( $_POST['action'] == 'recovery' ) {
    $ga->recovery($_POST);
    $ga->ID_Tipoprioridad = $_POST["ID_Tipoprioridad"];
    $ga->__get("ID_Tipoprioridad");
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