<?php
include '../../../app/load.php';
include '../../../config.php';
// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \MaximosMinimos\MaximosMinimos();

if( $_POST['action'] == 'save' ) {
    $ga->save($_POST);
    $success = true;

    $err = ($ga->resultado=="No existe el Artículo") ? $ga->resultado : "";

    $arr = array(
        "success" => $success,
        "des_articulo" => $ga->des_articulo,
        "detalle" => $ga->resultado
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'UpdateArt' ) {
    $ga->UpdateArt($_POST);
    $success = true;

    $arr = array(
        "success" => $success,
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'delete' ) {
    $ga->borrarPromocion($_POST);
    $ga->IDpromo = $_POST["IDpromo"];
    $ga->__get("IDpromo");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}
if($_POST['action'] === 'guardar'){
    //extract($_POST);
    $cve_articulo = $_POST['cve_articulo'];
    $idy_ubica = $_POST['idy_ubica'];
    $minimo = $_POST['minimo'];
    $maximo = $_POST['maximo'];
    $tipo_reabasto = $_POST['tipo_reabasto'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT num_multiplo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($query);
    $num_multiplo = $row['num_multiplo'];
    if($num_multiplo == 0)
        $num_multiplo = 1;

    if($tipo_reabasto == 'C')
    {
        $maximo = $maximo*$num_multiplo;
        $minimo = $minimo*$num_multiplo;
    }


    $sql = "SELECT cve_articulo FROM ts_ubicxart WHERE cve_articulo = '$cve_articulo' AND idy_ubica = '$idy_ubica' AND Activo = 1;";
    $query = mysqli_query($conn, $sql);
    if(mysqli_num_rows($query) > 0){
        $sql = "UPDATE ts_ubicxart SET CapacidadMaxima = '$maximo', CapacidadMinima = '$minimo', caja_pieza = '$tipo_reabasto' WHERE cve_articulo = '$cve_articulo' AND idy_ubica = '$idy_ubica' AND Activo = 1;";
    }else{
        $sql = "INSERT INTO ts_ubicxart (cve_articulo, idy_ubica, CapacidadMinima, CapacidadMaxima, Activo, caja_pieza) VALUES ('$cve_articulo', '$idy_ubica', '$minimo', '$maximo', 1, '$tipo_reabasto');";
    }
    $query = mysqli_query($conn, $sql);

    echo json_encode(array("success" => true));
}