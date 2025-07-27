<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Usuarios\Usuarios();

if( $_POST['action'] == 'add' ) {
    $ga->saveUser($_POST);
    $arr = array(
        "success" => true
    );
    echo json_encode($arr);

} if( $_POST['action'] == 'addRolUser' ) {
    $ga->addRolUser($_POST);
    $arr = array(
        "success" => true
    );
    echo json_encode($arr);

} if( $_POST['action'] == 'BorrarRolUser' ) {
    $ga->BorrarRolUser($_POST);
    $arr = array(
        "success" => true
    );
    echo json_encode($arr);

} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarUser($_POST);
    $arr = array(
        "success" => true
    );
    echo json_encode($arr);
}

if( $_POST['action'] == 'editUser' ) {
    $ga->actualizarUser($_POST);
    $arr = array(
        "success" => true
    );
    echo json_encode($arr);
}
if( $_POST['action'] == 'loadPerRoles' ) {
    $ga->cve_usuario = $_POST["cve_usuario"];
    $ga->__getPerRoles("cve_usuario");
    $success = true;
    $Origen = '';
    $Destino = '';
    /******************************** ROLES DEL SISTEMA **************************************/
    foreach ($ga->dataOrigen as $origen) {
        $id_perfil = $origen["ID_PERFIL"];
        $name = $origen["PER_NOMBRE"];
        $Origen .= '<option value="'.$id_perfil.'">'.$name.'</option>';
    }
    /******************************** ROLES DEL USUARIO **************************************/
    foreach ($ga->dataDestino as $destino) {
        $id_data = $destino["ID_PERFIL"];
        $name = $destino["PER_NOMBRE"];
        $Destino .= '<option value="'.$id_data.'">'.$name.'</option>';
    }

    $arr = array(
        "Origen" => $Origen,
        "Destino" => $Destino,
        "success" => $success
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'delete' ) {
    $ga->borrarUser($_POST);
    $ga->id_user = $_POST["id_user"];
    $ga->__get("id_user");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}
if( $_POST['action'] == 'recovery' ) {
    $ga->recoveryUser($_POST);
    $ga->id_user = $_POST["id_user"];
    $ga->__get("id_user");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}
if( $_POST['action'] == 'load' ) {
    $ga->id_user = $_POST["id_user"];
    $ga->__get("id_user");
    $arr = array(
        "success" => true,
        "cve_usuario" => $ga->data->cve_usuario,
        "cve_cia" => $ga->data->cve_cia,
        "des_usuario" => $ga->data->des_usuario,
        "pwd_usuario" => $ga->data->pwd_usuario,
        "nombre_completo" => $ga->data->nombre_completo,
        "email" => $ga->data->email,
        "perfil" => $ga->data->perfil,
        "image_url" => $ga->data->image_url,
        "id_user" => $ga->data->id_user,
        "es_cliente" => $ga->data->es_cliente,
        "web_apk" => $ga->data->web_apk,
        "cve_almacen" => $ga->data->cve_almacen,
        "cve_cliente" => $ga->data->cve_cliente,
        "cve_proveedor" => $ga->data->cve_proveedor,
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'loadAllUsers' ) {
    $model_usuario = $ga->getAll();

    $store_data = array(
        "Usuarios" =>array()
    );

    foreach ($model_usuario as $usuario)
    {
        $store_data['Usuarios'][] = array (
            'id' => $usuario->cve_usuario
        );
    }

    $arr = array(
        "success" => true
    );

    $arr = array_merge($arr,$store_data);

    echo json_encode($arr);

}

if( $_POST['action'] == 'exists' ) {
	
    $clave=$ga->exist($_POST["cve_usuario"]);
	
 

   if($clave==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
 );

    echo json_encode($arr);

}

if( $_POST['action'] == 'tieneAlmacen' ) {
    $ga->id_user = $_POST["id_user"];
    
    $ga->tieneAlmacen("id_user");
    $success = false;

    if (!empty($ga->data->id_user)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'fecha_cierre_sesion' ) {

    if($_SESSION['cve_usuario'] != 'wmsmaster')
    {
        $sql = 'SELECT id FROM users_bitacora WHERE cve_usuario = "'.$_SESSION['cve_usuario'].'" ORDER BY id DESC LIMIT 1';
        $data = mysqli_query(\db2(), $sql);
        $id = mysqli_fetch_assoc($data);
        $id = $id['id'];

        $sql = 'UPDATE users_bitacora SET fecha_cierre = NOW() WHERE cve_usuario = "'.$_SESSION['cve_usuario'].'" AND id = '.$id.';';
        $data = mysqli_query(\db2(), $sql);
    }

    //$res = $ga->fecha_cierre_sesion($_POST);
    $arr = array(
        //"res" => $res,
        "success" => true
    );
    echo json_encode($arr);
}

if( $_GET['action'] == 'ExisteUsuario' ) {

    $sql = 'SELECT COUNT(*) as existe FROM c_usuario WHERE cve_usuario = "'.$_GET['usuario'].'"';
    $data = mysqli_query(\db2(), $sql);
    $usuario = mysqli_fetch_assoc($data);
    $existe = $usuario['existe'];

    //$res = $ga->fecha_cierre_sesion($_POST);
    $arr = array(
        //"res" => $res,
        "success" => $existe
    );
    echo json_encode($arr);
}
