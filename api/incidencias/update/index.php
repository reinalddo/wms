<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Incidencias\Incidencias();

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
    $fecha = strtotime($ga->data->Fecha);
    $fecha = date('d/m/Y', $fecha);
    $fecha_accion = strtotime($ga->data->Fecha_accion);
    $fecha_accion = date('d/m/Y', $fecha_accion);
    $arr = array(
        "success" => true,
        "ID_Incidencia" => $ga->data->ID_Incidencia,
    		"cliente" => $ga->data->cliente,
    		"Fol_folio" => $ga->data->Fol_folio,
    		"centro_distribucion" => $ga->data->centro_distribucion,
    		"tipo_reporte" => $ga->data->tipo_reporte,
    		"reportador" => $ga->data->reportador,
    		"cargo_reportador" => $ga->data->cargo_reportador,
    		"Fecha" => $fecha,
    		"Descripcion" => $ga->data->Descripcion,
    		"responsable_recibo" => $ga->data->responsable_recibo,
    		"responsable_caso" => $ga->data->responsable_caso,
    		"plan_accion" => $ga->data->plan_accion,
    		"responsable_plan" => $ga->data->responsable_plan,
    		"Fecha_accion" => $fecha_accion,
            "responsable_verificacion" => $ga->data->responsable_verificacion,
            "id_motivo_registro" => $ga->data->id_motivo_registro,
            "desc_motivo_registro" => $ga->data->desc_motivo_registro,
            "id_motivo_cierre" => $ga->data->id_motivo_cierre,
            "desc_motivo_cierre" => $ga->data->desc_motivo_cierre,
    		"status" => $ga->data->status
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
