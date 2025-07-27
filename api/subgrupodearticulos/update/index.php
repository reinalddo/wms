<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \SubGrupoArticulos\SubGrupoArticulos();

if( $_POST['action'] == 'add' ) {
    $clave=$ga->exist($_POST["cve_sgpoart"], $_POST["almacen"]);

    $success = 1;
    if($clave == "")
        $ga->save($_POST);
    else
        $success = 0;

    echo $success;
} 
if( $_POST['action'] == 'edit' ) {
    $ga->actualizarSubgrupoarticulos($_POST);
}
if( $_POST['action'] == 'exists' ) {
	
    $clave=$ga->exist($_POST["clave"], $_POST["id_almacen"]);

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
    $ga->borrarSubgrupoArt($_POST);
    $ga->cve_sgpoart = $_POST["cve_sgpoart"];
    $ga->__get("cve_sgpoart");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'inputSubSelect' ) {
    $arr = $ga->actualizarInputSubgrupoA($_POST);
    $str = "<option value=''>Seleccione</option>";
    foreach( $arr AS $p ) {
        $str .= "<option value='" . $p->cve_sgpoart . "'>" . $p->des_sgpoart . "</option>";
    }
    $arr = array(
        "success" => true,
        "response" => $str
    );

    echo json_encode($arr);
}
if( $_POST['action'] == 'load' ) {
    $ga->cve_sgpoart = $_POST["codigo"];
    $ga->__get("cve_sgpoart");
    $arr = array(
        "success" => true,
        "id" => $_POST["codigo"],
        "cve_sgpoart" => $ga->data->cve_sgpoart,
        "descripcion" => $ga->data->des_sgpoart,
		"cve_gpoart" => $ga->data->cve_gpoart,
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