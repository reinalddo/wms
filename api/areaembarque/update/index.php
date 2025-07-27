<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) 
{
    exit();
}

$ga = new \AreaEmbarque\AreaEmbarque();

if( $_POST['action'] == 'add' ) 
{
    $ga->save($_POST);
    $arr = array("success"=>true);
    echo json_encode($arr);
} 
	
if( $_POST['action'] == 'edit' ) 
{
    $ga->editar($_POST);
    $success = true;
    $arr = array(
        "success" => $success,
        "err" => $resp
    );
    echo json_encode($arr);
}

if( $_POST['action'] == 'exists' ) 
{
  	$ga->cve_ubicacion = $_POST["cve_ubicacion"];
    $exist = $ga->exist($_POST["cve_ubicacion"]);
    $success = false;

    if ($exist==true) 
    {
        $success = true;
    }

    $arr = array("success" => $success);
    echo json_encode($arr);
} 

if( $_POST['action'] == 'delete' ) 
{
    $ga->borrar($_POST);
    $ga->id = $_POST["ID_Embarque"];
    $ga->__get("ID_Embarque");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );
    echo json_encode($arr);
} 

if( $_POST['action'] == 'load' ) 
{
    $ga->cve_ubicacion = $_POST["cve_ubicacion"];
    $ga->__get("cve_ubicacion");
    $arr = array(
        "success" => true,
    		"ID_Embarque" => $ga->data->ID_Embarque,
        "cve_ubicacion" => $ga->data->cve_ubicacion,
        "cve_almac" => $ga->data->cve_almac,
		    "descripcion" => $ga->data->descripcion,
        "AreaStagging" => $ga->data->AreaStagging,
		    "idAlmacen" => $ga->data->id		
    );
    echo json_encode($arr);
}

if( $_POST['action'] == 'recovery' ) 
{
    $ga->recovery($_POST);
    $ga->ID_Embarque = $_POST["ID_Embarque"];
    $ga->__get("ID_Embarque");
    $arr = array("success" => true,);
    echo json_encode($arr);
}

if( $_POST['action'] == 'inUse' ) 
{
    $use = $ga->inUse($_POST);
    $arr = array("success" => $use,);
    echo json_encode($arr);
}

if( $_POST['action'] == 'pedidos_ws' ) 
{
    $pedidos = $ga->pedidos_ws($_POST);
    $arr = array("html" => $pedidos);
    echo json_encode($arr);
}