<?php
include '../../../config.php';

error_reporting(0);

if(isset($_GET) && $_GET['action'] === 'get'){
	$cia = $_GET['cia'];
	$id_almacen = $_GET['id_almacen'];
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$codigo = "";

	//$query = mysqli_query($conn, "SELECT codigo FROM t_codigocsd WHERE cve_cia = {$cia}; ");
	$query = mysqli_query($conn, "SELECT codigo FROM t_codigocsd WHERE cve_almac = {$id_almacen}; ");
	if($query->num_rows > 0){
		$codigo = mysqli_fetch_assoc($query)['codigo'];
	}

	//Seccion-cve_pasillo-Ubicacion-cve_nivel

	$codigo_csd = $codigo;

	$codigo = str_replace("Seccion", "Sección", $codigo);
	$codigo = str_replace("cve_rack", "Rack", $codigo);
	$codigo = str_replace("cve_nivel", "Nivel", $codigo);
	$codigo = str_replace("Ubicacion", "Posición", $codigo);
	$codigo = str_replace("cve_pasillo", "Pasillo", $codigo);

	echo json_encode(array(
		"codigo"	=> $codigo_csd,
		"codigo_init" => $codigo
	));
}

?>