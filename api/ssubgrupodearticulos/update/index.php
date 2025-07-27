<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \SSubGrupoArticulos\SSubGrupoArticulos();

if( $_POST['action'] == 'add' ) {
    $clave=$ga->exist($_POST["cve_ssgpoart"], $_POST["almacen"]);

    $success = true;
    if($clave == "")
        $ga->save($_POST);
    else
        $success = false;
    $arr = array(
        "success"=>$success
    );

} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarSSbArticulos($_POST);
} if( $_POST['action'] == 'exists' ) {
	
    $clave=$ga->exist($_POST["clave"], $_POST["almacen"]);
	
 

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
    $ga->borrarSSubgrupoArt($_POST);
    $ga->cve_ssgpoart = $_POST["cve_ssgpoart"];
    $ga->__get("cve_ssgpoart");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}  if( $_POST['action'] == 'inputSubSubSelect' ) {
    $arr = $ga->actualizarInputSSubgrupoA($_POST);
    $str = "<option value=''>Seleccione</option>";
    foreach( $arr AS $p ) {
        $str .= "<option value='" . $p->cve_ssgpoart . "'>" . $p->des_ssgpoart . "</option>";
    }
    $arr = array(
        "success" => true,
        "response" => $str
    );

    echo json_encode($arr);
}
if( $_POST['action'] == 'load' ) {
    $ga->cve_ssgpoart = $_POST["id"];
    $ga->__get("cve_ssgpoart");
    $arr = array(
        "success" => true,
        "codigo" => $ga->data,
        "cve_ssgpoart" => $ga->cve_ssgpoart,
		"cve_sgpoart" => $ga->data->cve_sgpoart,		
        "Descripcion" => $ga->des_ssgpoart
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