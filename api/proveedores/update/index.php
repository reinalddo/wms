<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Proveedores\Proveedores();

if( $_POST['action'] == 'add' ) {
    $data = $ga->save($_POST);
	
	$arr = array(
		"success"=>true,
        "data"=>$data,
        "post"=>$_POST
	);

    echo json_encode($arr);
	
} 

if( $_POST['action'] == 'edit' ) {
    $ga->actualizarProveedor($_POST);
 	$arr = array(
		"success"=>true
	);

    echo json_encode($arr);
}
if( $_POST['action'] == 'delete' ) {
    $ga->borrarProveedor($_POST);
    $ga->cve_proveedor = $_POST["cve_proveedor"];
    $ga->__get("cve_proveedor");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->ID_Proveedor = $_POST["ID_Proveedor"];
    $ga->__get("ID_Proveedor");

    $codDaneSql = \db()->prepare("SELECT departamento,des_municipio  FROM c_dane WHERE cod_municipio='".$ga->data->cve_dane."'");
    $codDaneSql->execute();
    $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);

    $arr = array(
        "success" => true,
		"ID_Proveedor" => $ga->data->ID_Proveedor,
		"cve_proveedor" => $ga->data->cve_proveedor,
        "nombre_proveedor" => $ga->data->Nombre,
		"direccion" => $ga->data->direccion,		
		"colonia" => $ga->data->colonia,
		"cve_dane" => $ga->data->cve_dane,		
		"ciudad" => $ga->data->ciudad,
		"estado" => $ga->data->estado,
		"pais" => $ga->data->pais,		
        "RUT" => $ga->data->RUT,        
		"telefono1" => $ga->data->telefono1,
        "telefono2" => $ga->data->telefono2,        
        "cliente_proveedor" => $ga->data->es_cliente,        
        "es_transportista" => $ga->data->es_transportista,
        "departamento" =>$codDane[0]["departamento"],
        "municipio" =>$codDane[0]["des_municipio"]
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'recovery' ) {
    $ga->recoveryProveedor($_POST);
    $ga->ID_Proveedor = $_POST["ID_Proveedor"];
    $ga->__get("ID_Proveedor");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'exists' ) {
	$cve_proveedor=$ga->exist($_POST["cve_proveedor"]);
	
    if($cve_proveedor==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
	);

    echo json_encode($arr);
}

if( $_POST['action'] == 'tieneOrden' ) {
	$ga->ID_Proveedor = $_POST["ID_Proveedor"];
	
    $ga->tieneOrden("ID_Proveedor");

    $success = false;

    if (!empty($ga->data->ID_Proveedor)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
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