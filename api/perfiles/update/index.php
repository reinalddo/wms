<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

function Sustituto_Cadena($rb){
    ## Sustituyo caracteres en la cadena final
    $rb = str_replace("Ã¡", "&aacute;", $rb);
    $rb = str_replace("Ã©", "&eacute;", $rb);
    $rb = str_replace("Â®", "&reg;", $rb);
    $rb = str_replace("Ã­", "&iacute;", $rb);
    $rb = str_replace("ÃƒÂ­", "&iacute;", $rb);
    $rb = str_replace("ï¿½", "&iacute;", $rb);
    $rb = str_replace("ÃƒÂ", "&Iacute;", $rb);
    $rb = str_replace("ÃƒÂ", "&Aacute;", $rb);
    $rb = str_replace("Ã³", "&oacute;", $rb);
    $rb = str_replace("ÃƒÂ©", "&eacute;", $rb);
    $rb = str_replace("ÃƒÂ³", "&oacute;", $rb);
    $rb = str_replace("Ãº", "&uacute;", $rb);
    $rb = str_replace("n~", "&ntilde;", $rb);
    $rb = str_replace("Âº", "&ordm;", $rb);
    $rb = str_replace("Âª", "&ordf;", $rb);
    $rb = str_replace("ÃƒÂ¡", "&aacute;", $rb);
    $rb = str_replace("Ã±", "&ntilde;", $rb);
    $rb = str_replace("Ã‘", "&Ntilde;", $rb);
    $rb = str_replace("ÃƒÂ±", "&ntilde;", $rb);
    $rb = str_replace("n~", "&ntilde;", $rb);
    $rb = str_replace("Ãš", "&Uacute;", $rb);
    return $rb;
}

$prof = new \Perfiles\Perfiles();

if( $_POST['action'] == 'add' ) {
    $prof->save($_POST);
    $arr = array(
        "success" => true
    );
    echo json_encode($arr);

} if( $_POST['action'] == 'edit' ) {
    $prof->actualizarRole($_POST);
    $arr = array(
        "success" => true
    );
    echo json_encode($arr);
}
if( $_POST['action'] == 'exists' ) {
    $prof->ID_Ruta = $_POST["ID_Ruta"];
    $prof->__get("ID_Ruta");

    $success = false;

    if (!empty($prof->data->ID_Ruta)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'delete' ) {
    $prof->borrarRole($_POST);
    $prof->ID_PERFIL = $_POST["ID_PERFIL"];
    $prof->__get("ID_PERFIL");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'addPerUser' ) {
    $prof->addPerUser($_POST);
    $arr = array(
        "success" => true
    );
    echo json_encode($arr);

} if( $_POST['action'] == 'addRolUser' ) {
    $prof->addRolUser($_POST);
    $arr = array(
        "success" => true
    );
    echo json_encode($arr);

} if( $_POST['action'] == 'BorrarPerUser' ) {
    $prof->BorrarPerUser($_POST);
    $arr = array(
        "success" => true
    );
    echo json_encode($arr);

} if( $_POST['action'] == 'BorrarRolUser' ) {
    $prof->BorrarRolUser($_POST);
    $arr = array(
        "success" => true
    );
    echo json_encode($arr);

} if( $_POST['action'] == 'loadPerUser' ) {
    $prof->ID_PERFIL = $_POST["ID_PERFIL"];
    $prof->__getPerUser("ID_PERFIL");
    $success = true;
    $Origen = '';
    $Destino = '';
    /******************************** ROLES DEL SISTEMA **************************************/
    foreach ($prof->dataOrigen as $origen) {
        $id_perfil = $origen["id_user"];
        $name = $origen["cve_usuario"];
        $Origen .= '<option value="'.$id_perfil.'">'.$name.'</option>';
    }
    /******************************** ROLES DEL USUARIO **************************************/
    foreach ($prof->dataDestino as $destino) {
        $id_data = $destino["id_user"];
        $name = $destino["cve_usuario"];
        $Destino .= '<option value="'.$id_data.'">'.$name.'</option>';
    }

    $arr = array(
        "Origen" => $Origen,
        "Destino" => $Destino,
        "success" => $success
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'loadPerRoles' ) {
    $prof->ID_PERFIL = $_POST["ID_PERFIL"];
    $prof->__getPerRoles("ID_PERFIL");
    $success = true;
    $Origen = '';
    $Destino = '';
    /******************************** ROLES DEL SISTEMA **************************************/
    foreach ($prof->dataOrigen as $origen) {
        $id_perfil = $origen["ID_PERMISO"];
        $name = $origen["DESCRIPCION"];
        $Origen .= '<option value="'.$id_perfil.'">'.Sustituto_Cadena($name).'</option>';
    }
    /******************************** ROLES DEL USUARIO **************************************/
    foreach ($prof->dataDestino as $destino) {
        $id_data = $destino["ID_PERMISO"];
        $name = $destino["DESCRIPCION"];
        $Destino .= '<option value="'.$id_data.'">'.Sustituto_Cadena($name).'</option>';
    }

    $arr = array(
        "Origen" => $Origen,
        "Destino" => $Destino,
        "success" => $success
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $prof->id_role = $_POST["id_role"];
    $prof->__get("id_role");
    $arr = array(
        "success" => true,
        "id_role" => $prof->data->id_role,
        "rol" => $prof->data->rol
    );

    echo json_encode($arr);

}