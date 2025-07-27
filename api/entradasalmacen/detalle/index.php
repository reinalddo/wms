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

	$sql = "SELECT * FROM th_entalmacen	WHERE id_ocompra = '".$id."'";

	$rs = mysqli_query($conn, $sql) or die("Error description: " . mysqli_error(\db2()));

	$arr = array();

	while ($row = mysqli_fetch_array($rs)) {
		$arr = array(		
			"Fol_Folio" => $row["Fol_Folio"],
			"Cve_Almac" => $row["Cve_Almac"],
			"Fec_Entrada" => $row["Fec_Entrada"],
			"fol_oep" => $row["fol_oep"],
			"Cve_Usuario" => $row["Cve_Usuario"],
			"Cve_Proveedor" => $row["Cve_Proveedor"],
			"STATUS" => $row["STATUS"],
			"Cve_Autorizado" => $row["Cve_Autorizado"],
			"TieneOE" => $row["TieneOE"],
			"statusaurora" => $row["statusaurora"],
			"id_ocompra" => $row["id_ocompra"],
			"placas" => $row["placas"],
			"entarimado" => $row["entarimado"],
			"bufer" => $row["bufer"],
			"HoraInicio" => $row["HoraInicio"],
			"ID_Protocolo" => $row["ID_Protocolo"],
			"Consec_protocolo" => $row["Consec_protocolo"],
			"cve_ubicacion" => $row["cve_ubicacion"]);
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

		$o = new \EntradaAlmacen\EntradaAlmacen();
		$o->Fol_Folio = $_POST["codigo"];
		
		$encabezado = load(\db2(), $_POST["codigo"]);
		
		$arr = array(
			"success" => true,
			"encabezado" => $encabezado
		);

		$sql = "SELECT
				td_aduana.ID_Aduana,
				td_aduana.cve_articulo,
				td_aduana.cantidad,
				td_aduana.cve_lote,
				td_aduana.caducidad,
				td_aduana.temperatura,
				td_aduana.num_orden,
				td_aduana.Ingresado,
				td_aduana.Activo,
				c_articulo.des_articulo,
				c_articulo.cve_umed,
				c_articulo.num_multiplo
				FROM
				td_aduana
				INNER JOIN c_articulo ON td_aduana.cve_articulo = c_articulo.cve_articulo
				WHERE td_aduana.ID_Aduana = '".$_POST["codigo"]."'";

        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

        $arr = array();

        while ($row = mysqli_fetch_array($rs)) {
            $arr2[] = $row;
        }
		
		//foreach ($o->data as $nombre => $valor) $arr2[$nombre] = $valor;

		$arr2["detalle"] = $arr2;

		$arr = array_merge($arr, $arr2);

		echo json_encode($arr);

	}
}