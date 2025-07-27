    <?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Sucursal\Sucursal();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
} 

if( $_POST['action'] == 'edit' ) {
$ga->actualizarCompa($_POST);
} 

   
if( $_POST['action'] == 'delete' ) {
$ga->borrarSucursal($_POST);
$arr = array(
    "success" => true,
    //"nombre_proveedor" => $ga->data->Empresa,
    //"contacto" => $ga->data->VendId
);

echo json_encode($arr);

} 

if( $_POST['action'] == 'loadcomp' ) {
    $ga->clavecomp = $_POST["option"];
    $ga->getNCompania();
    $arr = array(
        "success" => true,
        "cve_pobla" => $ga->data->cve_pobla,
        "cve_cia" => $ga->data->cve_cia,
        "des_cia" => $ga->data->des_cia,
        "des_rfc" => $ga->data->des_rfc,
        "des_direcc" => $ga->data->des_direcc,
        "des_cp" => $ga->data->des_cp,
        "des_telef" => $ga->data->des_telef,
        "des_contacto" => $ga->data->des_contacto,
        "des_email" => $ga->data->des_email,
        "des_observ" => $ga->data->des_observ,
		"imagen" => $ga->data->imagen
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}
if( $_POST['action'] == 'load' ) {

$ga->load($_POST["codigo"]);

$codDaneSql = \db()->prepare("SELECT departamento,des_municipio  FROM c_dane WHERE cod_municipio='".$ga->data->des_cp."'");
$codDaneSql->execute();
$codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);

$arr = array(
    "success" => true,
    "departamento" =>$codDane[0]["departamento"],
    "municipio" =>$codDane[0]["des_municipio"]
);

foreach ($ga->data as $nombre => $valor) $arr2[$nombre] = $valor;

$arr = array_merge($arr, $arr2);

echo json_encode($arr);

}

if( $_POST['action'] == 'exists' ) {
	
    $clave=$ga->exist($_POST["sucursal"]);
	
 

   if($clave==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
 );

    echo json_encode($arr);
}



if( $_POST['action'] == 'recovery' ) {
    $ga->recovery($_POST);
    
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}