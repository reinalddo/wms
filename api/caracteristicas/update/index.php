<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$md = new \Caracteristicas\Caracteristicas();

if( $_POST['action'] == 'add' ) {
    $md->save($_POST);
} 

if( $_POST['action'] == 'edit' ) {
    $md->actualizarTipoCaracteristica($_POST);
} 
if( $_POST['action'] == 'add_caracteristica' ) {

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $clave = $_POST['clave'];
    $tipo = $_POST['tipo'];

    $sql = "SELECT COUNT(*) existe FROM c_caracteristicas WHERE Cve_Carac = '$clave' AND Id_Tipo_car = $tipo";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $row = mysqli_fetch_array($res);
    $existe = $row['existe'];

    $arr = "";
    if(!$existe)
    {
        $md->save_caracteristica($_POST);
        $arr = array(
            "success"=>true
        );
    }
    else
        $arr = array(
            "success"=>false
        );
    echo json_encode($arr);
}

if( $_POST['action'] == 'edit_caracteristica' ) {
    $md->actualizarCaracteristica($_POST);
} 
if( $_POST['action'] == 'exists' ) {
    $clave=$md->exist($_POST["Clave_motivo"]);
	
 

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
    $md->borrarTipoCaracteristica($_POST);
    $md->Id_Tipo_car = $_POST["Id_Tipo_car"];
    $md->__get("Id_Tipo_car");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'delete_caracteristica' ) {
    $md->borrarCaracteristica($_POST);
    $md->Id_Carac = $_POST["Id_Carac"];
    $md->__get("Id_Carac");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}
 if( $_POST['action'] == 'load' ) {
    $md->Id_Tipo_car = $_POST["id"];
    $md->__get("Id_Tipo_car");
    $arr = array(
        "success" => true,
        "Id_Tipo_car" => $md->data->Id_Tipo_car,
        "descripcion" => $md->data->TipoCar_Desc
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'load_caracteristica' ) {
    $md->Id_Carac = $_POST["id"];
    $md->__get("Id_Carac");
    $arr = array(
        "success" => true,
        "Cve_Carac" => $md->data->Cve_Carac,
        "Id_Tipo_car" => $md->data->Id_Tipo_car,
        "descripcion" => $md->data->Des_Carac
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'recovery' ) {
    $md->recovery($_POST);
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'inUse' ) {
    $use = $md->inUse($_POST);
    $arr = array(
        "success" => $use,
    );

    echo json_encode($arr);

}