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

$ga = new \Roles\Roles();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
    $arr = array(
        "success" => true
    );
    echo json_encode($arr);

} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarRole($_POST);
    $arr = array(
        "success" => true
    );
    echo json_encode($arr);
}
if( $_POST['action'] == 'exists' ) {
    $ga->ID_Ruta = $_POST["ID_Ruta"];
    $ga->__get("ID_Ruta");

    $success = false;

    if (!empty($ga->data->ID_Ruta)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'delete' ) {
    $ga->borrarRole($_POST);
    $ga->id_role = $_POST["id_role"];
    $ga->__get("id_role");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'addPerUser' ) {
    $ga->addPerUser($_POST);
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

} if( $_POST['action'] == 'BorrarPerUser' ) {
    $ga->BorrarPerUser($_POST);
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

} if( $_POST['action'] == 'load' ) {
    $ga->id_role = $_POST["id_role"];
    $ga->__get("id_role");
    $arr = array(
        "success" => true,
        "id_role" => $ga->data->id_role,
        "rol" => $ga->data->rol
    );

    echo json_encode($arr);

}