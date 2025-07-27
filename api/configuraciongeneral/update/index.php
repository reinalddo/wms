<?php
include '../../../config.php';

error_reporting(0);

if(isset($_POST) && $_POST['action'] === 'save'){

	$campos = $_POST['campos'];
	$valores = $_POST['valores'];

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	for($i = 0; $i < count($campos); $i++)
	{
		$valor = $valores[$i];
		$clave = $campos[$i];
		$query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$valor}' WHERE cve_conf = '{$clave}';");
	}

	echo json_encode(array(
		"success" => true
	));
}

if(isset($_POST) && $_POST['action'] === 'actualizar_licencia'){

	$tipo = $_POST['tipo'];
	$cantidadweb = $_POST['cantidadweb'];
	$cantidadapk = $_POST['cantidadapk'];
	$clave_almacen = $_POST['almacen'];

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$query = mysqli_query($conn, "SELECT * FROM t_license");

	if($query->num_rows == 0)
	{
		$query = mysqli_query($conn, "INSERT INTO t_license(L_Web, L_Mobile, Activo) VALUES(TO_BASE64(0), TO_BASE64(0), 1);");
	}

		if($tipo == 'web')
		   $query = mysqli_query($conn, "UPDATE t_license SET L_Web = TO_BASE64($cantidadweb);");
		else
			$query = mysqli_query($conn, "UPDATE c_almacenp SET No_Licencias = $cantidadapk WHERE clave = '$clave_almacen';");
		   //$query = mysqli_query($conn, "UPDATE t_license SET L_Mobile = TO_BASE64($cantidadapk);");


	echo json_encode(array(
		"success" => true
	));
}

if(isset($_POST) && $_POST['action'] === 'saveHabilitarSFA'){

	$valor = $_POST['valor'];

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'SFA'; ");

	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$valor}' WHERE cve_conf = 'SFA';");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor) VALUES('SFA','{$valor}');");

	echo json_encode(array(
		"success" => true
	));
}

if(isset($_POST) && $_POST['action'] === 'saveDatosConfig'/*'saveSurtidoCompleto'*/){

	$valor  = $_POST['valor'];
	$valor2 = $_POST['valor2'];
	$valor3 = $_POST['valor3'];
	$valor4 = $_POST['valor4'];
	$valor5 = $_POST['valor5'];
	$valor6 = $_POST['valor6'];


	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'SurtidoCompleto'; ");
	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$valor}' WHERE cve_conf = 'SurtidoCompleto';");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor) VALUES('SurtidoCompleto','{$valor}');");


	$query2 = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'OTSinSurtir'; ");
	if($query2->num_rows > 0)
       $query2 = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$valor2}' WHERE cve_conf = 'OTSinSurtir';");
    else
       $query2 = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor) VALUES('OTSinSurtir','{$valor2}');");

	$query3 = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'recepcion_por_cajas'; ");
	if($query3->num_rows > 0)
       $query3 = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$valor3}' WHERE cve_conf = 'recepcion_por_cajas';");
    else
       $query3 = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor) VALUES('recepcion_por_cajas','{$valor3}');");


	$query4 = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_cantidad'; ");
	if($query4->num_rows > 0)
       $query4 = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$valor4}' WHERE cve_conf = 'decimales_cantidad';");
    else
       $query4 = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor) VALUES('decimales_cantidad','{$valor4}');");

	$query5 = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_costo'; ");
	if($query5->num_rows > 0)
       $query5 = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$valor5}' WHERE cve_conf = 'decimales_costo';");
    else
       $query5 = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor) VALUES('decimales_costo','{$valor5}');");

	$query6 = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'existencias_con_formato'; ");
	if($query6->num_rows > 0)
       $query6 = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$valor6}' WHERE cve_conf = 'existencias_con_formato';");
    else
       $query6 = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor) VALUES('existencias_con_formato','{$valor6}');");

	echo json_encode(array(
		"success" => true
	));
}

if(isset($_POST) && $_POST['action'] === 'saveDatosConfigAlmacen'/*'saveSurtidoCompleto'*/){

	$id_almacen = $_POST['id_almacen'];
	$lista_empaque = $_POST['lista_empaque'];
	$entrega_programada = $_POST['entrega_programada'];
	$salida_inventario = $_POST['salida_inventario'];
	$lista_embarque = $_POST['lista_embarque'];
	$auditoria_embarque = $_POST['auditoria_embarque'];
	$discrepancia = $_POST['discrepancia'];
	$imprimir_asn = $_POST['imprimir_asn'];
	$imp_archivo_despacho = $_POST['imp_archivo_despacho'];
	$des_aviso_despacho = $_POST['des_aviso_despacho'];
	$des_archivo_despacho = $_POST['des_archivo_despacho'];
	$kardex_consolidado = $_POST['kardex_consolidado'];

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'lista_empaque' AND id_almacen = {$id_almacen}; ");
	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$lista_empaque}' WHERE cve_conf = 'lista_empaque' AND id_almacen = {$id_almacen};");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor, id_almacen) VALUES('lista_empaque','{$lista_empaque}', {$id_almacen});");


	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'entrega_programada' AND id_almacen = {$id_almacen}; ");
	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$entrega_programada}' WHERE cve_conf = 'entrega_programada' AND id_almacen = {$id_almacen};");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor, id_almacen) VALUES('entrega_programada','{$entrega_programada}', {$id_almacen});");


	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'salida_inventario' AND id_almacen = {$id_almacen}; ");
	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$salida_inventario}' WHERE cve_conf = 'salida_inventario' AND id_almacen = {$id_almacen};");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor, id_almacen) VALUES('salida_inventario','{$salida_inventario}', {$id_almacen});");


	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'lista_embarque' AND id_almacen = {$id_almacen}; ");
	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$lista_embarque}' WHERE cve_conf = 'lista_embarque' AND id_almacen = {$id_almacen};");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor, id_almacen) VALUES('lista_embarque','{$lista_embarque}', {$id_almacen});");


	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'auditoria_embarque' AND id_almacen = {$id_almacen}; ");
	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$auditoria_embarque}' WHERE cve_conf = 'auditoria_embarque' AND id_almacen = {$id_almacen};");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor, id_almacen) VALUES('auditoria_embarque','{$auditoria_embarque}', {$id_almacen});");


	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'discrepancia' AND id_almacen = {$id_almacen}; ");
	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$discrepancia}' WHERE cve_conf = 'discrepancia' AND id_almacen = {$id_almacen};");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor, id_almacen) VALUES('discrepancia','{$discrepancia}', {$id_almacen});");


	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'imprimir_asn' AND id_almacen = {$id_almacen}; ");
	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$imprimir_asn}' WHERE cve_conf = 'imprimir_asn' AND id_almacen = {$id_almacen};");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor, id_almacen) VALUES('imprimir_asn','{$imprimir_asn}', {$id_almacen});");


	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'imp_archivo_despacho' AND id_almacen = {$id_almacen}; ");
	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$imp_archivo_despacho}' WHERE cve_conf = 'imp_archivo_despacho' AND id_almacen = {$id_almacen};");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor, id_almacen) VALUES('imp_archivo_despacho','{$imp_archivo_despacho}', {$id_almacen});");


	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'des_aviso_despacho' AND id_almacen = {$id_almacen}; ");
	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$des_aviso_despacho}' WHERE cve_conf = 'des_aviso_despacho' AND id_almacen = {$id_almacen};");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor, id_almacen) VALUES('des_aviso_despacho','{$des_aviso_despacho}', {$id_almacen});");


	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'des_archivo_despacho' AND id_almacen = {$id_almacen}; ");
	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$des_archivo_despacho}' WHERE cve_conf = 'des_archivo_despacho' AND id_almacen = {$id_almacen};");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor, id_almacen) VALUES('des_archivo_despacho','{$des_archivo_despacho}', {$id_almacen});");

	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'kardex_consolidado' AND id_almacen = {$id_almacen}; ");
	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$kardex_consolidado}' WHERE cve_conf = 'kardex_consolidado' AND id_almacen = {$id_almacen};");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor, id_almacen) VALUES('kardex_consolidado','{$kardex_consolidado}', {$id_almacen});");

	echo json_encode(array(
		"success" => true
	));
}

if(isset($_POST) && $_POST['action'] === 'kardex_consolidado_chk'){

	$id_almacen = $_POST['id_almacen'];
	$kardex_consolidado = $_POST['kardex_consolidado'];

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$query = mysqli_query($conn, "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'kardex_consolidado' AND id_almacen = {$id_almacen}; ");
	if($query->num_rows > 0)
       $query = mysqli_query($conn, "UPDATE t_configuraciongeneral SET Valor = '{$kardex_consolidado}' WHERE cve_conf = 'kardex_consolidado' AND id_almacen = {$id_almacen};");
    else
       $query = mysqli_query($conn, "INSERT INTO t_configuraciongeneral(cve_conf, Valor, id_almacen) VALUES('kardex_consolidado','{$kardex_consolidado}', {$id_almacen});");

	echo json_encode(array(
		"success" => true
	));
}

if(isset($_POST) && $_POST['action'] === 'begin_commit_rollback'){

	$orden = $_POST['orden'];

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$query = mysqli_query($conn, "$orden");

	echo json_encode(array(
		"success" => "ORDEN EJECUTADA: $orden"
	));
}


?>