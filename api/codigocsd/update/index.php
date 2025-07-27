<?php
include '../../../config.php';

error_reporting(0);

if(isset($_POST) && $_POST['action'] === 'save'){
	$codigo = $_POST['codigo'];
	$cia = $_POST['cia'];
	$id_almacen = $_POST['id_almacen'];

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$sql = "SELECT COUNT(*) as existe FROM t_codigocsd WHERE cve_almac = $id_almacen";
	$query = mysqli_query($conn, $sql);
	$existe = mysqli_fetch_assoc($query)["existe"];


	$sql = "INSERT INTO t_codigocsd (cve_almac, cve_cia, codigo) VALUES ({$id_almacen}, {$cia}, '$codigo') ;";//ON DUPLICATE KEY UPDATE codigo = '$codigo'

	if($existe)
		$sql = "UPDATE t_codigocsd SET codigo = '$codigo' WHERE cve_almac = {$id_almacen}";

	$query = mysqli_query($conn, $sql);

	echo json_encode(array(
		"success" => $query,
		"sql" => $sql
	));
}

if(isset($_POST) && $_POST['action'] === 'applyCSD'){
	$cia = $_POST['cia'];

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$codigo = '';
	$result = false;
	$query = mysqli_query($conn, "SELECT codigo FROM t_codigocsd WHERE cve_cia = '$cia';");
	if($query->num_rows > 0){
		$codigo = mysqli_fetch_row($query)[0];
	}
	if(!empty($codigo)){
		$data = explode('-', $codigo);
		$sqlPre = "CONCAT(";
		$totalData = sizeof($data) - 1;
		for($i = 0; $i <= $totalData; $i++){
			$sqlPre .= "u1.{$data[$i]}";
			$sqlPre .= ($i < $totalData) ? ", '-', ": ")";
		}
		$sql = "UPDATE	c_ubicacion u1 
				JOIN 	c_ubicacion u2  ON u1.idy_ubica = u2.idy_ubica
				SET u1.CodigoCSD = ${sqlPre};";
	}
	$error = '';
	$result = mysqli_query($conn, $sql);
	//$error = mysqli_error($conn);

	echo json_encode(array(
		"success" 	=> $result,
		"error"		=>$error
	));
}

?>