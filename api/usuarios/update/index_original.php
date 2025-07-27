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
} if( $_POST['action'] == 'loadPerRoles' ) {
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

} if( $_POST['action'] == 'load' ) {
    $ga->id_user = $_POST["id_user"];
    $ga->__get("id_user");
    $arr = array(
        "success" => true,
        "cve_usuario" => $ga->data->cve_usuario,
        "cve_cia" => $ga->data->cve_cia,
        "des_usuario" => $ga->data->des_usuario,
        "pwd_usuario" => $ga->data->pwd_usuario
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