<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \ClasificacionClientes\ClasificacionClientes();

if( $_POST['action'] == 'add' ) {
    if(isset($_POST['tipo_cliente']))
        $ga->save2($_POST);
    else 
        $ga->save($_POST);
} if( $_POST['action'] == 'edit' ) {

    if(isset($_POST['tipo_cliente']))
        $ga->actualizarClasificacionClientes2($_POST);
    else 
        $ga->actualizarClasificacionClientes($_POST);
} if( $_POST['action'] == 'exists' ) {
	
    $clave=$ga->exist($_POST["clave"]);
	
 

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
    $ga->Cve_TipoCte = $_POST["Cve_TipoCte"];
    $ga->__get("Cve_TipoCte");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} 
if( $_POST['action'] == 'delete2' ) {
    $ga->borrarSubgrupoArt2($_POST);
    $ga->Cve_TipoCte = $_POST["Cve_TipoCte"];
    $ga->__get("Cve_TipoCte");
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
        $str .= "<option value='" . $p->Cve_TipoCte . "'>" . $p->Des_TipoCte . "</option>";
    }
    $arr = array(
        "success" => true,
        "response" => $str
    );

    echo json_encode($arr);
}
if( $_POST['action'] == 'load' ) {
    $ga->Cve_TipoCte = $_POST["codigo"];
    if(isset($_POST['clasif2']))
    {
        $ga->__get2("Cve_TipoCte");
        $arr = array(
            "success" => true,
            "Cve_TipoCte" => $ga->data->Cve_TipoCte,
            "cve_grupo" => $ga->data->cve_grupo,
    		"id_tipocliente" => $ga->data->id_tipocliente,		
            "Descripcion" => $ga->data->Des_TipoCte
        );
    }
    else
    {
        $ga->__get("Cve_TipoCte");
        $arr = array(
            "success" => true,
            "Cve_TipoCte" => $ga->data->Cve_TipoCte,
            "id_grupo" => $ga->data->id_grupo,      
            "Descripcion" => $ga->data->Des_TipoCte
        );
    }

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

if( $_POST['action'] == 'inUse2' ) {
    $use = $ga->inUse2($_POST);
    $arr = array(
        "success" => $use,
    );

    echo json_encode($arr);
}