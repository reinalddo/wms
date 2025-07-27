<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$tc = new \TipoCaja\TipoCaja();

if( $_POST['action'] == 'add' ) {
    $res = $tc->save($_POST);
	$arr = array(
		"success"=>true,
    "insert"=>$res
	);
    echo json_encode($arr);
} 

if( $_POST['action'] == 'edit' ) {
    $tc->actualizarTipCaja($_POST);
	$success = true;

    $arr = array(
        "success" => $success,
        "err" => $resp
    );
    echo json_encode($arr);
} 


if( $_POST['action'] == 'exists' ) {
	
    $clave=$tc->exist($_POST["clave"]);
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
    $tc->borrarTipoCaja($_POST);
    $tc->id_tipocaja = $_POST["id_tipocaja"];
    $tc->__get("id_tipocaja");
    $arr = array(
        "success" => true
    );

    echo json_encode($arr);
} 

if( $_POST['action'] == 'load' ) {
    $tc->id_tipocaja = $_POST["id_tipocaja"];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $tc->__get("id_tipocaja");
    $arr = array(
        "success" => true,
        "id_tipocaja" => $tc->data->id_tipocaja,
        "clave" => $tc->data->clave,
        "descripcion" => ($tc->data->descripcion),
        "largo" => intval($tc->data->largo),
		"alto" => intval($tc->data->alto),
        "ancho" => intval($tc->data->ancho),
        "peso" => intval($tc->data->peso),
        "Packing"   => $tc->data->Packing
    );

    echo json_encode($arr);

	}
	
	
	if( $_POST['action'] == 'recovery' ) {
    $tc->recovery($_POST);
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'inUse' ) {
    $use = $tc->inUse($_POST);
    $arr = array(
        "success" => $use,
    );

    echo json_encode($arr);

}