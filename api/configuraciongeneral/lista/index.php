<?php
include '../../../config.php';

error_reporting(0);

if(isset($_GET) && $_GET['action'] === 'get')
{
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$conf = array();

	$query = mysqli_query($conn, "SELECT cve_conf, Valor FROM t_configuraciongeneral; ");

	$i = 0;
	if($query->num_rows > 0){
		while($row = mysqli_fetch_assoc($query))
		{
			$conf[$i] = $row;
			$i++;
		}
	}
/*
	echo json_encode(array(
		"conf"	=> $query
	));
*/
	echo json_encode($conf);
}

if(isset($_GET) && $_GET['action'] === 'getHabilitarSFA')
{
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$conf = array();

	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'SFA'; ");

	$valorSFA = 0;
	if($query->num_rows > 0){
		$row = mysqli_fetch_assoc($query);
		$valorSFA = $row['Valor'];
	}

	echo json_encode(array(
		"valorSFA"	=> $valorSFA
	));

	//echo json_encode($conf);
}

if(isset($_GET) && $_GET['action'] === 'getDatosConfig'/*'getSurtidoCompleto'*/)
{
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$conf = array();

	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'SurtidoCompleto'; ");
	$valorSC = 0;
	if($query->num_rows > 0){
		$row = mysqli_fetch_assoc($query);
		$valorSC = $row['Valor'];
	}
	else 
		$query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral (cve_conf, Valor) VALUES ('SurtidoCompleto', '0');");

	$query2 = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'OTSinSurtir'; ");
	$valorOT = 0;
	if($query2->num_rows > 0){
		$row = mysqli_fetch_assoc($query2);
		$valorOT = $row['Valor'];
	}
	else 
		$query2 = mysqli_query($conn, "INSERT INTO t_configuraciongeneral (cve_conf, Valor) VALUES ('OTSinSurtir', '0');");


	$query3 = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'recepcion_por_cajas'; ");
	$valorCajas = 0;
	if($query3->num_rows > 0){
		$row = mysqli_fetch_assoc($query3);
		$valorCajas = $row['Valor'];
	}
	else 
		$query3 = mysqli_query($conn, "INSERT INTO t_configuraciongeneral (cve_conf, Valor) VALUES ('recepcion_por_cajas', '0');");

	$query4 = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'existencias_con_formato'; ");
	$existencias_con_formato = 0;
	if($query4->num_rows > 0){
		$row = mysqli_fetch_assoc($query4);
		$existencias_con_formato = $row['Valor'];
	}
	else 
		$query4 = mysqli_query($conn, "INSERT INTO t_configuraciongeneral (cve_conf, Valor) VALUES ('existencias_con_formato', '0');");

	echo json_encode(array(
		"valorSC" => $valorSC,
		"valorOT" => $valorOT,
		"existencias_con_formato" => $existencias_con_formato,
		"valorCajas" => $valorCajas
	));

	//echo json_encode($conf);
}


?>