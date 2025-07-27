<?php


if (isset($_SERVER['HTTP_ORIGIN'])) {
	header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
		header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
}

set_time_limit(0);
//error_reporting(0);

function load($conn, $id) {

	$sql = "SELECT * FROM th_aduana	WHERE num_pedimento = '".$id."'";

	$rs = mysqli_query($conn, $sql) or die("Error description: " . mysqli_error(\db2()));

	$arr = array();

	while ($row = mysqli_fetch_array($rs)) {
		$arr = array(		
			"ID_Aduana" => $row["ID_Aduana"],
			"num_pedimento" => $row["num_pedimento"],
			"fech_pedimento" => $row["fech_pedimento"],
			"aduana" => $row["aduana"],
			"factura" => $row["factura"],
			"fech_llegPed" => $row["fech_llegPed"],
			"status" => $row["status"],
			"ID_Proveedor" => $row["ID_Proveedor"],
			"ID_Protocolo" => $row["ID_Protocolo"],
			"Consec_protocolo" => $row["Consec_protocolo"],
			"cve_usuario" => $row["cve_usuario"],
			"Cve_Almac" => $row["Cve_Almac"],
			"Activo" => $row["Activo"]);
	}

	return $arr;
}

$_POST = (empty($HTTP_POST_FILES)) ? (array) json_decode(file_get_contents("php://input")) : $HTTP_POST_FILES;

if (isset($_POST) && !empty($_POST)) {
	if (isset($_POST['id_user'])) {
		session_start();
		$_SESSION['id_user'] = $_POST['id_user'];
	}
	if( $_POST['action'] == 'load' ) {
		include '../../../app/load.php';

		$app = new \Slim\Slim();

		error_reporting(0);

		$o = new \OrdenCompra\OrdenCompra();
		$o->ID_Aduana = $_POST["codigo"];
		
		$encabezado = load(\db2(), $_POST["codigo"]);
		
		$arr = array(
			"success" => true,
			"encabezado" => $encabezado
		);

		$o->__getDetalle("ID_Aduana");

		foreach ($o->data as $nombre => $valor) $arr2[$nombre] = $valor;

		$arr2["detalle"] = $o->dataDetalle;

		$arr = array_merge($arr, $arr2);

		echo json_encode($arr);

	}
}