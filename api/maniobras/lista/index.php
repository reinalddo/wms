<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

if(isset($_POST['action']) && $_POST['action'] === 'getClientes'){
	$almacen = $_POST['almacen'];
//	$combo = "";

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	//mysqli_set_charset($conn, 'utf8');
	$clientes = [];
	$seleccionado = '';
	$combo = '<option value="">Seleccione</option>';
	$direccion = '';

/*
	$sql = "SELECT id FROM c_almacenp WHERE clave = '".$almacen."'";
	$query = mysqli_query($conn, $sql);
	$almacen = mysqli_fetch_all($query, MYSQLI_ASSOC)['id'];

	$sql = "SELECT Cve_Clte, RazonSocial FROM c_cliente WHERE Cve_Almacenp = '".$almacen."'";
	$query = mysqli_query($conn, $sql);
	$datos = mysqli_fetch_all($query, MYSQLI_ASSOC);
*/

	$sql = "SELECT Cve_Clte AS value, RazonSocial AS texto FROM c_cliente WHERE Cve_Almacenp = (SELECT id FROM c_almacenp WHERE clave = '$almacen')";
	$query = mysqli_query($conn, $sql);
	$datos = mysqli_fetch_all($query, MYSQLI_ASSOC);

	foreach($datos as $cliente){
		$cliente = array_map("utf8_encode", $cliente);
		extract($cliente);
		ob_start();
		?><option value="<?php echo $value; ?>" <?php if($value === $seleccionado): echo 'selected'; endif; ?>> <?php echo  utf8_encode($value." - ".$texto); ?> </option><?php
		$combo .= ob_get_clean();
		//$combo .= "<option value='".$value."'>".$texto."</option>";
		//$combo .= $Cve_Clte." - ".$RazonSocial." *** ";
	}

	mysqli_close($conn);

	echo json_encode(array(
		"val_almacen" => $almacen,
		"sql" => $sql,
		"combo" => $combo
	));
}

if(isset($_GET['action']) && $_GET['action'] === 'getClientesSelect'){
	$cliente = $_GET['cliente'];
	$id_almacen = $_GET['id_almacen'];
	
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	//mysqli_set_charset($conn, 'utf8');
	$seleccionado = '';
	//$combo = '<option value="">Seleccione</option>';
	$combo = '';
	$direccion = '';

	$lista_select = "";
	if(isset($_GET['listaD']))
		$lista_select = "AND c_cliente.Cve_Clte IN (SELECT DISTINCT Cve_Clte FROM c_destinatarios WHERE id_destinatario NOT IN (SELECT Id_Destinatario FROM RelCliLis WHERE ListaD IN (SELECT id FROM listad WHERE STR_TO_DATE(FechaFin, '%Y-%m-%d') >= STR_TO_DATE(CURDATE(), '%Y-%m-%d'))))";

	if(isset($_GET['listaP']))
		$lista_select = "AND c_cliente.Cve_Clte IN (SELECT DISTINCT Cve_Clte FROM c_destinatarios WHERE id_destinatario NOT IN (SELECT Id_Destinatario FROM RelCliLis WHERE ListaP IN (SELECT id FROM listap WHERE STR_TO_DATE(FechaFin, '%Y-%m-%d') >= STR_TO_DATE(CURDATE(), '%Y-%m-%d'))))";

	$sql = "SELECT DISTINCT c_cliente.Cve_Clte AS value, c_cliente.RazonSocial AS texto 
			FROM c_cliente 
			LEFT JOIN rel_cliente_almacen r ON r.Cve_Clte = c_cliente.Cve_Clte
			WHERE (c_cliente.Cve_Clte LIKE '%$cliente%' OR c_cliente.RazonSocial LIKE '%$cliente%') {$lista_select} AND (c_cliente.Cve_Almacenp = {$id_almacen} OR r.Cve_Almac = {$id_almacen}) AND c_cliente.Activo = 1";
			//{$tipo_cliente_traslado}

	if(isset($_GET['tipo_cliente_traslado']))
	{
		if($_GET['tipo_cliente_traslado'] == 1)
		{
			//$tipo_cliente_traslado = "AND ClienteTipo='TRASLADO'";
			$sql = "SELECT cve_proveedor AS value, Nombre AS texto 
					FROM c_proveedores 
					WHERE es_cliente=1";
		}
	}

	$query = mysqli_query($conn, $sql);
	$datos = mysqli_fetch_all($query, MYSQLI_ASSOC);

	$find = false;
	$firsTValue = 0;
	foreach($datos as $cliente){
		//$cliente = array_map("utf8_encode", $cliente);
		extract($cliente);
		//$error .= " * ".$value." - ".$texto." - ".$seleccionado." ->";
		ob_start();
		?><option value="<?php echo $value; ?>"> <?php echo '['.$value.'] - ' . utf8_encode($texto); ?> </option><?php
		$combo .= ob_get_clean();
		if(!$find) $firsTValue = $value;
		$find = true;
	}

	mysqli_close($conn);
	echo json_encode(array(
		"find" => $find,
		"firsTValue" => $firsTValue,
		"sql" => $sql,
		"combo" => $combo
	));

}

if(isset($_GET['action']) && $_GET['action'] === 'getPedimentoYReferencias'){

	$clave_cliente = $_GET['cliente'];

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$sql = "
		SELECT DISTINCT Factura AS Pedimento FROM th_aduana WHERE IFNULL(Factura, '') != '' AND areaSolicitante = '$clave_cliente'
	";
	$query = mysqli_query($conn, $sql);
	$datos = mysqli_fetch_all($query, MYSQLI_ASSOC);
	$options_pedimentos = "<option value=''>Seleccione</option>";
	foreach($datos as $PedimentoRes){
		//$destinatario = array_map("utf8_encode", $destinatario);
		extract($PedimentoRes);
		//$error .= " * ".$value." - ".$texto." - ".$seleccionado." ->";
		$options_pedimentos .= "<option value='$Pedimento'>$Pedimento</option>";
	}
	//Rutas

	$sql = "SELECT DISTINCT recurso AS Referencia FROM th_aduana WHERE IFNULL(recurso, '') != '' AND areaSolicitante = '$clave_cliente';";
	$query = mysqli_query($conn, $sql);
	$datos_ref = mysqli_fetch_all($query, MYSQLI_ASSOC);
	$options_referencias = "<option value=''>Seleccione</option>";

	//mysqli_close($conn);
	foreach($datos_ref as $ReferenciaRes){
		//$destinatario = array_map("utf8_encode", $destinatario);
		extract($ReferenciaRes);
		//$error .= " * ".$value." - ".$texto." - ".$seleccionado." ->";
		$options_referencias .= "<option value='$Referencia'>$Referencia</option>";
	}

	mysqli_close($conn);
	//echo $combo;

	echo json_encode(array(
		"options_pedimentos" => $options_pedimentos,
		"options_referencias" => $options_referencias
	));

}


if(isset($_GET['action']) && $_GET['action'] === 'getDestinatario'){

	$clave_cliente = $_GET['cliente'];

	$pertenece_a_lista_precio = "";$pertenece_a_lista_precio_rutas = "";
	if(isset($_GET['lista']))
	{
		$pertenece_a_lista_precio = "AND id_destinatario NOT IN (SELECT Id_Destinatario FROM RelCliLis WHERE ListaP IN (SELECT id FROM listap WHERE STR_TO_DATE(FechaFin, '%Y-%m-%d') >= STR_TO_DATE(CURDATE(), '%Y-%m-%d')))";
		$pertenece_a_lista_precio_rutas = "AND d.id_destinatario NOT IN (SELECT Id_Destinatario FROM RelCliLis WHERE ListaD IN (SELECT id FROM listad WHERE STR_TO_DATE(FechaFin, '%Y-%m-%d') >= STR_TO_DATE(CURDATE(), '%Y-%m-%d')))";
	}

	if(isset($_GET['descuento']))
	{
		$pertenece_a_lista_precio = "AND id_destinatario NOT IN (SELECT Id_Destinatario FROM RelCliLis WHERE ListaD IN (SELECT id FROM listad WHERE STR_TO_DATE(FechaFin, '%Y-%m-%d') >= STR_TO_DATE(CURDATE(), '%Y-%m-%d')))";
		$pertenece_a_lista_precio_rutas = "AND d.id_destinatario NOT IN (SELECT Id_Destinatario FROM RelCliLis WHERE ListaD IN (SELECT id FROM listad WHERE STR_TO_DATE(FechaFin, '%Y-%m-%d') >= STR_TO_DATE(CURDATE(), '%Y-%m-%d')))";

	}

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	//mysqli_set_charset($conn, 'utf8');
	$seleccionado = '';
/*
	$sql = "SELECT CONCAT(id_destinatario, ' - ', direccion, ', ', colonia, ', ', estado) AS texto, id_destinatario AS value, (SELECT ID_Destinatario FROM c_cliente WHERE Cve_Clte = '$clave_cliente') AS seleccionado FROM c_destinatarios WHERE Cve_Clte = '$clave_cliente' {$pertenece_a_lista_precio};";//V_DestinatariosTodos
*/
	//Destinatarios
	$sql = "
		SELECT DISTINCT d.*, IFNULL(GROUP_CONCAT(r.cve_ruta SEPARATOR ','), '') AS rutas 
		FROM (
			SELECT CONCAT(id_destinatario, ' - ', direccion, ', ', colonia, ', ', estado) AS texto, 
		       id_destinatario AS value, 
		       (SELECT DISTINCT ID_Destinatario FROM c_cliente WHERE Cve_Clte = '$clave_cliente') AS seleccionado
		       FROM c_destinatarios 
		       WHERE Cve_Clte = '$clave_cliente' {$pertenece_a_lista_precio}
		) AS d 
		LEFT JOIN t_clientexruta c ON c.clave_cliente = d.value
		LEFT JOIN t_ruta r ON r.ID_Ruta = c.clave_ruta
		GROUP BY value
	";
	$query = mysqli_query($conn, $sql);
	$datos = mysqli_fetch_all($query, MYSQLI_ASSOC);

	$sql = "SELECT IF(IFNULL(limite_credito, 0) > 0, 'S', 'N') AS tiene_credito FROM c_cliente WHERE Cve_Clte = '$clave_cliente'";
	$query = mysqli_query($conn, $sql);
	$tiene_credito = mysqli_fetch_array($query)["tiene_credito"];

	//mysqli_close($conn);
	$find = false;
	$combo = ""; $combo_rutas = "";
	foreach($datos as $destinatario){
		//$destinatario = array_map("utf8_encode", $destinatario);
		extract($destinatario);
		//$error .= " * ".$value." - ".$texto." - ".$seleccionado." ->";
		ob_start();
		?><option value="<?php echo $value; ?>" data-ruta="<?php echo $rutas; ?>" <?php if($value === $seleccionado): echo 'selected'; endif; ?>> <?php echo  utf8_encode($texto); ?> </option><?php
		$combo .= ob_get_clean();
		$find = true;
	}
	//Rutas

	$sql = "
		SELECT DISTINCT IFNULL((r.cve_ruta ), '') AS cve_ruta, r.descripcion
		FROM t_clientexruta c 
		LEFT JOIN t_ruta r ON r.ID_Ruta = c.clave_ruta
		LEFT JOIN c_destinatarios d ON d.id_destinatario = c.clave_cliente
		WHERE d.Cve_Clte = '$clave_cliente' {$pertenece_a_lista_precio_rutas} 
	";
	$query = mysqli_query($conn, $sql);
	$datos_rutas_cliente = mysqli_fetch_all($query, MYSQLI_ASSOC);

	//mysqli_close($conn);
	foreach($datos_rutas_cliente as $ruta){
		//$destinatario = array_map("utf8_encode", $destinatario);
		extract($ruta);
		//$error .= " * ".$value." - ".$texto." - ".$seleccionado." ->";
		ob_start();
		?><option value="<?php echo $cve_ruta; ?>"> <?php echo  utf8_encode($descripcion); ?> </option><?php
		$combo_rutas .= ob_get_clean();
	}

	$ListaServicio = ""; $comboArticulos = "";$moneda = "";
	$sql_ls = ""; $options_art = "";
	if(isset($_GET['es_maniobras']))
	{
		if($_GET['sfa'] == 1)
		{
			$sql_ls = "SELECT IFNULL(ListaP, '') ListaP FROM RelCliLis WHERE Id_Destinatario IN (SELECT id_destinatario FROM c_destinatarios WHERE Cve_Clte = '$clave_cliente') AND ListaP IN (SELECT id FROM listap WHERE TipoServ = 'S')";
			$query = mysqli_query($conn, $sql_ls);
			$ListaServicio = mysqli_fetch_array($query)['ListaP'];


			if($ListaServicio)
			{
				$sql_art = "SELECT  a.Cve_Servicio AS cve_articulo, a.Des_Servicio AS des_articulo, a.Id_Servicio AS id, a.UniMedida
							FROM c_servicios a 
							INNER JOIN detallelp lp ON lp.ListaId = $ListaServicio AND a.Cve_Servicio = lp.Cve_Articulo;";
				$query = mysqli_query($conn, $sql_art);
				$num_art = mysqli_num_rows($query);
				$options_art = "<option value=''>Seleccione Servicio ($num_art)</option>";
				while($row_articulos = mysqli_fetch_array($query))
				{
					extract($row_articulos);
					$options_art .= "<option value='$cve_articulo' data-id='$id' data-um='$UniMedida'>$cve_articulo - $des_articulo</option>";
				}

				$sql_moneda = "SELECT IFNULL(id_moneda, 0) as moneda FROM listap WHERE id = $ListaServicio";
				$query = mysqli_query($conn, $sql_moneda);
				$moneda = mysqli_fetch_array($query)['moneda'];

			}
		}
	}


	mysqli_close($conn);
	//echo $combo;

	echo json_encode(array(
		"find" => $find,
		//"sql_ls" => $sql_ls,
		"combo_rutas" => $combo_rutas,
		"combo" => $combo,
		"combo_articulos" => $options_art,
		"ListaServicio" => $ListaServicio,
		"moneda" => $moneda,
		"tiene_credito" => $tiene_credito
	));

}

if(isset($_POST) && $_POST['action'] === 'articulos_lp')
{
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$lp = $_POST['lp'];
	$pry = "";

	$sql = "SELECT a.cve_articulo, IFNULL(a.des_articulo, '') as des_articulo, IFNULL(a.des_detallada, '') as des_detallada, IFNULL(tr.lote, '') as lote, tr.existencia, um.id_umed, um.des_umed, IF(a.Caduca = 'S', DATE_FORMAT(lt.Caducidad, '%d-%m-%Y'), '') AS Caducidad, a.peso
			FROM ts_existenciatarima tr 
			LEFT JOIN c_articulo a ON tr.cve_articulo = a.cve_articulo
			LEFT JOIN c_charolas ch ON ch.IDContenedor = tr.ntarima
			LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
			LEFT JOIN c_lotes lt ON lt.cve_articulo = tr.cve_articulo AND lt.Lote = tr.lote
			WHERE ch.CveLP = '$lp'
			";

	if($_POST['pry'] != '')
	{
		$pry = $_POST['pry'];
		$art_selected = $_POST['art_selected'];
		$lote_selected = $_POST['lote_selected'];

		$sqlLP = " LEFT JOIN td_entalmacenxtarima tdt ON IFNULL(vg.Cve_Contenedor, '') = IFNULL(tdt.ClaveEtiqueta, '') ";
		if($lp != '')
			$sqlLP = " INNER JOIN td_entalmacenxtarima tdt ON IFNULL(tdt.ClaveEtiqueta, '') = '$lp' AND IFNULL(vg.Cve_Contenedor, '') = IFNULL(tdt.ClaveEtiqueta, '') ";

		$sqlArt = "";
		if($art_selected != '')
			$sqlArt = " AND pr.cve_articulo = '$art_selected' ";

		$sqlLote = "";
		if($lote_selected != '')
			$sqlLote = " AND pr.lote = '$lote_selected' ";

		$sql = "
				SELECT DISTINCT pr.cve_articulo, pr.des_articulo, pr.des_detallada, pr.lote, SUM(pr.existencia) AS existencia, 
								pr.id_umed, pr.des_umed, pr.Caducidad, pr.peso #pr.lp,
				FROM (
					SELECT  a.cve_articulo, IFNULL(a.des_articulo, '') AS des_articulo, IFNULL(a.des_detallada, '') AS des_detallada, 
       							IFNULL(vg.cve_lote, '') AS lote, IFNULL(td.CantidadUbicada, 0) AS existencia, um.id_umed, um.des_umed, 
       							IF(a.Caduca = 'S', DATE_FORMAT(lt.Caducidad, '%d-%m-%Y'), '') AS Caducidad, a.peso #, IFNULL(tdt.ClaveEtiqueta, '') AS lp
				FROM V_ExistenciaGral vg
				LEFT JOIN c_articulo a ON vg.cve_articulo = a.cve_articulo
				LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
				LEFT JOIN c_lotes lt ON lt.cve_articulo = vg.cve_articulo AND IFNULL(lt.Lote, '') = IFNULL(vg.cve_lote, '')
				INNER JOIN td_entalmacen td ON td.cve_articulo = vg.cve_articulo AND IFNULL(vg.cve_lote, '') = IFNULL(td.cve_lote, '') AND td.fol_folio IN (SELECT Fol_Folio FROM th_entalmacen WHERE Proyecto = '$pry')
				#{$sqlLP} 
				) AS pr
				WHERE 1 {$sqlArt} {$sqlLote}
				GROUP BY cve_articulo, lote #lp";
	}



	$sth = \db()->prepare($sql);
	$sth->execute();
	$articulos_lp = $sth->fetchAll();// [0]["fecha_actual"]; 

	echo json_encode(array(
		"articulos_lp" => $articulos_lp
	));
}

if(isset($_POST) && $_POST['action'] === 'exportPDF'){
	$content = $_POST['content'];
	$cia = $_POST['cia'];
	$title = $_POST['title'];
	$pdf = new \ReportePDF\PDF($cia, $title, 'L');
	$pdf->setContent($content);
	$pdf->stream();
}

if(isset($_POST) && $_POST['action'] === 'fecha_actual')
{
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$sql = "SELECT DATE_FORMAT(CURDATE(), '%d-%m-%Y') fecha_actual FROM DUAL";
	$sth = \db()->prepare($sql);
	$sth->execute();
	$fecha_actual = $sth->fetchAll() [0]["fecha_actual"]; 

	echo json_encode(array(
		"fecha_actual" => $fecha_actual
	));
}

if(isset($_POST) && $_POST['action'] === 'exportExcel'){
	include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
	$content = $_POST['content'];
	$title = "Nuevo pedido.xlsx";
	$header = array(
        'Clave'          			=> 'string',
        'Artículo'       			=> 'string',
        'Cantidad de Piezas'        => 'string',
        'Caducidad Mínima (meses)'  => 'string',
        'Fecha Registro'       		=> 'string',
        'Cliente'       			=> 'string',
        'Destinatario'       		=> 'string',
        'Fecha Compromiso Entrega'  => 'string',
        'Prioridad'       			=> 'string',
    );
    $excel = new XLSXWriter();
    $excel->writeSheetHeader('Sheet1', $header );
	foreach($content as $c){
		$data = explode(',', $c);
		$row = array(
            $data[0],
            $data[1],
            $data[2],
            $data[3],
            $data[4],
            $data[5],
            $data[6],
            $data[7],
            $data[8],
        );
        $excel->writeSheetRow('Sheet1', $row );
	}
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
}

if(isset($_POST) && $_POST['action'] === 'buscar_existencias')
{
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);
*/
	$cve_articulo = $_POST['cve_articulo'];
	$lote_serie   = $_POST['lote_serie'];
	$id_almacen   = $_POST['id_almacen'];
	//$lp_selected  = $_POST['LP'];

	//$sql = "SELECT IFNULL(SUM(Existencia), 0) AS existencia FROM V_ExistenciaGral WHERE cve_articulo = '$cve_articulo' AND cve_almac = {$id_almacen} AND tipo = 'ubicacion' AND IFNULL(Cuarentena, 0) = 0;";
	$sql = "SELECT IFNULL(SUM(Existencia), 0) AS existencia FROM VS_ExistenciaParaSurtido WHERE cve_articulo = '$cve_articulo' AND Cve_Almac = {$id_almacen}";

	if($lote_serie)
		$sql = "SELECT IFNULL(SUM(Existencia), 0) AS existencia FROM VS_ExistenciaParaSurtido WHERE cve_articulo = '$cve_articulo' AND Cve_Almac = {$id_almacen} AND cve_lote = '$lote_serie'";
		//$sql = "SELECT IFNULL(SUM(Existencia), 0) AS existencia FROM V_ExistenciaGral WHERE cve_articulo = '$cve_articulo' AND cve_almac = {$id_almacen} AND cve_lote = '$lote_serie' AND tipo = 'ubicacion' AND IFNULL(Cuarentena, 0) = 0;";


	//echo $sql;
	$query = mysqli_query($conn, $sql);
	$existencia = mysqli_fetch_array($query)["existencia"];

	echo json_encode(array(
		"existencia" => $existencia
	));
}

if(isset($_POST) && $_POST['action'] === 'load_lotes_series')
{
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

	$cve_articulo = $_POST['cve_articulo'];
	$tiene_lote   = $_POST['tiene_lote'];
	$tiene_serie  = $_POST['tiene_serie'];
	$id_almacen  = $_POST['id_almacen'];
	$LP 		 = $_POST['LP'];

	$SQL_LP = "";

	$res = "<option value=''>Desconocido</option>"; $sql = "";
	if($tiene_lote)
	{
		if($LP)
		$SQL_LP = " AND CONCAT(cve_articulo, Lote) IN (SELECT CONCAT(cve_articulo,lote) FROM ts_existenciatarima WHERE cve_almac = {$id_almacen} AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '$LP')) ";

		//$sql = "SELECT DISTINCT Lote FROM c_lotes WHERE cve_articulo = '{$cve_articulo}' AND Activo = 1 AND CONCAT(cve_articulo, Lote) IN (SELECT CONCAT(cve_articulo,cve_lote) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_almac = {$id_almacen})";
		$sql = "SELECT DISTINCT Lote FROM c_lotes WHERE cve_articulo = '{$cve_articulo}' AND Activo = 1 AND CONCAT(cve_articulo, Lote) IN (SELECT CONCAT(cve_articulo,cve_lote) FROM VS_ExistenciaParaSurtido WHERE Cve_Almac = {$id_almacen}) {$SQL_LP}";
		$query = mysqli_query($conn, $sql);
		$datos = mysqli_fetch_all($query, MYSQLI_ASSOC);
		foreach($datos as $lote){
			$res.="<option value='".$lote['Lote']."'>".$lote['Lote']."</option>";
		}
	}
	else if($tiene_serie)
	{
		if($LP)
			$SQL_LP = " AND CONCAT(cve_articulo, numero_serie) IN (SELECT CONCAT(cve_articulo,lote) FROM ts_existenciatarima WHERE cve_almac = {$id_almacen} AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '$LP')) ";

		//$sql = "SELECT DISTINCT numero_serie FROM c_serie WHERE cve_articulo = '{$cve_articulo}' AND numero_serie IN (SELECT cve_lote FROM V_ExistenciaGral WHERE tipo = 'ubicacion')";
		$sql = "SELECT DISTINCT numero_serie FROM c_serie WHERE cve_articulo = '{$cve_articulo}' AND numero_serie IN (SELECT cve_lote FROM VS_ExistenciaParaSurtido WHERE Cve_Almac = {$id_almacen}) {$SQL_LP}";
		$query = mysqli_query($conn, $sql);
		$datos = mysqli_fetch_all($query, MYSQLI_ASSOC);
		foreach($datos as $serie){
			$res.="<option value=".$serie['numero_serie'].">".$serie['numero_serie']."</option>";
		}
	}

	echo json_encode(array(
		"res" => $res
	));
}

if(isset($_POST) && $_POST['action'] === 'buscar_lista_relacionada')
{
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$cve_articulo  = $_POST['cve_articulo'];
	$id_destinatario = $_POST['id_destinatario'];
	$ruta = $_POST['ruta'];

	//$sql_destinatario = "";
	//if($ruta == "")
		$sql_destinatario = " WHERE rcl.Id_Destinatario = '{$id_destinatario}' ";

	$sql = "
		SELECT  lista.ListaPrecios, lista.TipoListaPrecios, lista.PrecioMinSiva, lista.PrecioMaxSiva, lista.PrecioMinCiva, lista.PrecioMaxCiva, lista.ComisionMon, lista.ComisionPor, 
			lista.ListaDescuentos, lista.TipoListaDescuentos, lista.Minimo, lista.Maximo, 
			IF(lista.Maximo > 0, TRUNCATE((lista.Maximo/lista.PrecioMaxCiva)*100, 3), 0) AS FactorMaximo, lista.ivaServicio,
			lista.Factor, lista.FactorMax, lista.existe_lista_precios, lista.existe_lista_descuentos 
		FROM (
			SELECT  dp.ListaId AS ListaPrecios, 
					p.Tipo AS TipoListaPrecios, 
					dp.PrecioMin AS PrecioMinSiva, 
					dp.PrecioMax AS PrecioMaxSiva,  
					dp.PrecioMin AS PrecioMinCiva,
					dp.PrecioMax AS PrecioMaxCiva, 
					dp.ComisionMon, 
					dp.ComisionPor,
					dd.ListaId AS ListaDescuentos, 
					d.Tipo AS TipoListaDescuentos,
					dd.Minimo, 
					IF(dd.Maximo = 0, dd.Minimo, dd.Maximo) AS Maximo, 
					dd.Factor, 
					IFNULL(s.IVA, 0) as ivaServicio,
					IF(dd.FactorMax = 0, dd.Factor, dd.FactorMax) AS FactorMax, 
					IFNULL(dp.ListaId, 0) AS existe_lista_precios,
					IFNULL(dd.ListaId, 0) AS existe_lista_descuentos
			FROM RelCliLis rcl 
			LEFT JOIN c_servicios s ON s.Cve_Servicio = '{$cve_articulo}'
			LEFT JOIN listap p ON p.id = rcl.ListaP 
			LEFT JOIN detallelp dp ON dp.ListaId = p.id AND dp.Cve_Articulo = '{$cve_articulo}'
			LEFT JOIN listad d ON d.id = rcl.ListaD
			LEFT JOIN detalleld dd ON dd.ListaId = d.id AND dd.Articulo = '{$cve_articulo}'
			{$sql_destinatario}
			LIMIT 1
		) AS lista";
	$query = mysqli_query($conn, $sql);
	$datos = mysqli_fetch_assoc($query);

	$PrecioMin = "";
	$PrecioMax = "";
	$ComisionMon = "";
	$ComisionPor = "";
	$Minimo = "";
	$Maximo = "";
	$Factor = "";
	$FactorMax = "";
	$FactorMaximo = "";
	$TipoListaPrecios = "";
	$TipoListaDescuentos = "";
	$ivaServicio = "";

	$existe_lista_precios = 0;
	$existe_lista_descuentos = 0;

	if($datos['existe_lista_precios'] != '0' && mysqli_num_rows($query) > 0)
	{
		$PrecioMinSiva = $datos['PrecioMinSiva'];
		$PrecioMaxSiva = $datos['PrecioMaxSiva'];
		$PrecioMinCiva = $datos['PrecioMinCiva'];
		$PrecioMaxCiva = $datos['PrecioMaxCiva'];
		$ComisionMon = $datos['ComisionMon'];
		$ComisionPor = $datos['ComisionPor'];
		$TipoListaPrecios = $datos['TipoListaPrecios'];
		$ivaServicio = $datos['ivaServicio'];
		//$existe_lista_precios = 1;
		$existe_lista_precios = $datos['existe_lista_precios'];
	}

	if($datos['existe_lista_descuentos'] != '0' && mysqli_num_rows($query) > 0)
	{
		$Minimo = $datos['Minimo'];
		$Maximo = $datos['Maximo'];
		$FactorMaximo = $datos['FactorMaximo'];
		$Factor = $datos['Factor'];
		$FactorMax = $datos['FactorMax'];
		$TipoListaDescuentos = $datos['TipoListaDescuentos'];
		$ivaServicio = $datos['ivaServicio'];
		$existe_lista_descuentos = 1;
	}

	echo json_encode(array(
		"sql" => $sql,
		"existe_lista_precios" => $existe_lista_precios,
		"PrecioMinSiva" => $PrecioMinSiva,
		"PrecioMaxSiva" => $PrecioMaxSiva,
		"PrecioMinCiva" => $PrecioMinCiva,
		"PrecioMaxCiva" => $PrecioMaxCiva,
		"ComisionMon" => $ComisionMon,
		"ComisionPor" => $ComisionPor,
		"TipoListaPrecios" => $TipoListaPrecios,
		"TipoListaDescuentos" => $TipoListaDescuentos,
		"existe_lista_descuentos" => $existe_lista_descuentos,
		"Minimo" => $Minimo,
		"Maximo" => $Maximo,
		"Factor" => $Factor,
		"ivaServicio" => $ivaServicio,
		"FactorMaximo" => $FactorMaximo,
		"FactorMax" => $FactorMax
	));
}

/*
if(isset($_POST) && $_POST['action'] === 'load_lotes_series')
{
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$cve_articulo = $_POST['cve_articulo'];
	$tiene_lote   = $_POST['tiene_lote'];
	$tiene_serie  = $_POST['tiene_serie'];

	$res = "<option value=''>Desconocido</option>"; $sql = "";
	if($tiene_lote)
	{
		$sql = "SELECT DISTINCT Lote FROM c_lotes WHERE cve_articulo = '{$cve_articulo}' AND Activo = 1 AND Lote IN (SELECT cve_lote FROM V_ExistenciaGral WHERE tipo = 'ubicacion')";
		$query = mysqli_query($conn, $sql);
		$datos = mysqli_fetch_all($query, MYSQLI_ASSOC);
		foreach($datos as $lote){
			$res.="<option value=".$lote['Lote'].">".$lote['Lote']."</option>";
		}
	}
	else if($tiene_serie)
	{
		$sql = "SELECT DISTINCT numero_serie FROM c_serie WHERE cve_articulo = '{$cve_articulo}' AND numero_serie IN (SELECT cve_lote FROM V_ExistenciaGral WHERE tipo = 'ubicacion')";
		$query = mysqli_query($conn, $sql);
		$datos = mysqli_fetch_all($query, MYSQLI_ASSOC);
		foreach($datos as $serie){
			$res.="<option value=".$serie['numero_serie'].">".$serie['numero_serie']."</option>";
		}
	}

	echo json_encode(array(
		"res" => $res
	));
}
*/

if (isset($_GET) && !empty($_GET) && $_GET['action'] == 'mostrar_lp_almacen'){

    $almacen = $_GET['almacen'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

/*
    $sql = "SELECT ch.CveLP 
            FROM ts_existenciatarima ts
            LEFT JOIN c_charolas ch ON ts.ntarima = ch.IDContenedor
            WHERE ts.cve_almac = {$almacen} ";
*/
     $sql = "SELECT DISTINCT CveLP FROM c_charolas WHERE clave_contenedor IN (SELECT DISTINCT v.Cve_Contenedor FROM V_ExistenciaGral v LEFT JOIN c_ubicacion u ON u.idy_ubica = v.cve_ubicacion WHERE v.cve_almac = {$almacen} AND u.picking = 'S' AND v.tipo = 'ubicacion') AND IDContenedor NOT IN (SELECT DISTINCT nTarima FROM td_pedidoxtarima) AND IFNULL(CveLP, '') != '' 
		UNION 

		    SELECT DISTINCT c.CveLP 
		    FROM c_charolas c
		    INNER JOIN ts_existenciatarima t ON t.ntarima = c.IDContenedor 
		    INNER JOIN td_pedidoxtarima p ON p.nTarima = t.ntarima 
		    INNER JOIN th_pedido th ON th.fol_folio = p.fol_folio AND th.status NOT IN ('A', 'S') 
		    #INNER JOIN t_recorrido_surtido r ON r.claveEtiqueta = p.nTarima
		    WHERE IFNULL(c.CveLP, '') != '' AND t.cve_almac = {$almacen} AND t.existencia > 0
		    AND c.IDContenedor NOT IN (SELECT nTarima FROM td_pedidoxtarima WHERE fol_folio IN (SELECT fol_folio FROM th_pedido WHERE STATUS IN ('A', 'S'))) 
     ORDER BY CveLP";
    // hace una llamada previa al procedimiento
  
   // echo var_dump($sql);
    //die();
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación 5: (" . mysqli_error($conn) . ") ".$sql;
    }
/*
    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
*/
    $i = 0;
    $arr = array();

    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['CveLP'];
        $responce->rows[$i]['cell']=array($row['CveLP']);
        $i++;
    }
    echo json_encode($arr);
}

?>