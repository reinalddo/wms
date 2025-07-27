<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \QaAuditoria\QaAuditoria();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
	$arr = array(
		"success"=>true
	);
    echo json_encode($arr);
} 

if( $_POST['action'] == 'lastid' ) {
    $ga->traerProxId();
    $arr = array(
        "success" => true,
        "id" => $ga->data["Auto_increment"]
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}


if( $_POST['action'] == 'edit' ) {
    $ga->actualizarIncidencia($_POST);
	$arr = array(
		"success"=>true
	);
    echo json_encode($arr);
} 

 if( $_POST['action'] == 'delete' ) {
    $ga->borrar($_POST);
    $ga->ID_Incidencia = $_POST["ID_Incidencia"];
    $ga->__get("ID_Incidencia");
    $arr = array(
        "success" => true,
    );
    echo json_encode($arr);

} 

if( $_POST['action'] == 'load' ) {
    $ga->ID_Incidencia = $_POST["ID_Incidencia"];
    $ga->__get("ID_Incidencia");
    $arr = array(
        "success" => true,
        "ID_Incidencia" => $ga->data->ID_Incidencia,
        "clave" => $ga->data->clave,
        "Fol_folio" => $ga->data->Fol_folio,
        "ReportadoCas" => $ga->data->ReportadoCas,
        "Descripcion" => $ga->data->Descripcion,
		"Respuesta" => $ga->data->Respuesta,
		"status" => $ga->data->status,
		"Fecha" => $ga->data->Fecha		
    );


    $arr = array_merge($arr);

    echo json_encode($arr);

}

if( $_POST['action'] == 'exists' ) {
    $ga->clave = $_POST["clave"];
    $ga->validaClave("clave");

    $success = false;

    if (!empty($ga->data->clave)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'traerMesasdeAlmacen' ) {	
    $mesas = $ga->loadMesas($_POST["clave"]);
		
	$arr = array(
        "success" => true,        
		"mesas" => $mesas	
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'cargarAuditoria' ) {	
    $auditoria = $ga->loadAuditoria($_POST["area"],$_POST["pedido"]);
		
	$arr = array(
        "success" => true,        
		"auditoria" => $auditoria	
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}