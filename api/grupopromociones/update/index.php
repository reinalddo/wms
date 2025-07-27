<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \GrupoPromociones\GrupoPromociones();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarGrupoPromociones($_POST);
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
    $ga->borrarGrupoPromociones($_POST);
    $ga->cve_gpoart = $_POST["id"];
    $ga->__get("cve_gpoart");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->cve_gpoart = $_POST["codigo"];
    $ga->__get("cve_gpoart");
    $arr = array(
        "success" => true,
        "codigo" => $ga->data->Id,
        "descripcion" => $ga->data->ListaMaster
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

if( $_POST['action'] == 'DeleteListasDeGrupos' )
{
    $listas_eliminar = $_POST['listas'];
    $id_grupo = $_POST['id_grupo'];

    //$listas_eliminar = explode(",",$listas_eliminar);

    foreach($listas_eliminar as $d)
    {
        $Sql = \db()->prepare("DELETE FROM DetalleLProMaster WHERE IdLm = {$id_grupo} AND IdPromo = {$d}");
        $Sql->execute();
    }

    $arr = array(
        "success" => true,
        "listas_eliminar" =>$listas_eliminar
    );

    echo json_encode($arr);


}