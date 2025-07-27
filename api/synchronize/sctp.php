<?php 
include '../../config.php';

if(isset($_POST) && !empty($_POST)){

	if(!extension_loaded('sqlsrv')){
		echo json_encode(array(
			"success" 	=> false,
			"error"		=> "La extensión de SQL Server no está cargada en tu sistema, contacta con el administrador de sistema para solucionar el inconveniente"
		));
	}else{
		$connmssql = sqlsrv_connect(
							'vps157135.vps.ovh.ca', 
							array(
								"Database"	=> "SCTP",
								"UID"		=> "SA",
								"PWD"		=> "KG4t+|1tB8"
							)
						);
		$connmysql = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		switch ($_POST['action']) {
			case 'sucursalesSCTP':
				$query = sqlsrv_query($connmssql, "SELECT * FROM Empresas");

				if( $query === false ) {
					echo json_encode(array(
						"success" 	=> false,
						"error"		=> sqlsrv_errors()
					));
				}else{
					$insert = "";
					while($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
					    extract($row);
					    $insert .= "INSERT IGNORE INTO c_sucursal (cve_cia, clave_sucursal, des_direcc, des_cia, des_telef, des_contacto, des_email, des_observ, des_cp) VALUES ('$empresamadre_id', '$IdEmpresa', '$Direccion', '$Sucursal', '$Telefono', '$Contacto', '$Email', '$NombreComercial', '$CP'); ";
					}
					$query = mysqli_multi_query($connmysql, $insert);
					echo json_encode(array(
						"success"	=> $query,
						"error"		=> "Ocurrió un error al guardar los datos en nuestra base de datos, intenta más tarde ".mysqli_error($connmysql)
					));
				}

				break;
			case 'clientesSCTP':
				$query = sqlsrv_query($connmssql, "SELECT * FROM Clientes");

				if( $query === false ) {
					echo json_encode(array(
						"success" 	=> false,
						"error"		=> sqlsrv_errors()
					));
				}else{
					$insert = "";
					while($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
					    extract($row);
					    $insert .= "INSERT IGNORE INTO c_cliente(Cve_Clte, RazonSocial, RazonComercial, CalleNumero, Contacto, Telefono1, CodigoPostal, CondicionPago, Colonia, Telefono2, Ciudad, Estado, RFC, Cve_Almacenp, ID_Destinatario) VALUES('$IdCli', '$Nombre', '$NombreCorto', '$Direccion', '$Referencia', '$Telefono', '$CP', '$DiasCreedito Dias', '$Colonia', '$Tel2', '$Ciudad', '$Estado', '$RFC', '1', '0'); ";
					}
					$query = mysqli_multi_query($connmysql, $insert);
					echo json_encode(array(
						"success"	=> $query,
						"error"		=> "Ocurrió un error al guardar los datos en nuestra base de datos, intenta más tarde ".mysqli_error($connmysql)
					));
				}

				break;
			case 'productosSCTP':
				$queryServer = sqlsrv_query($connmssql, "SELECT * FROM Productos");

				if( $queryServer === false ) {
					echo json_encode(array(
						"success" 	=> false,
						"error"		=> sqlsrv_errors()
					));
				}else{
					$insert = "";
					while($row = sqlsrv_fetch_array($queryServer, SQLSRV_FETCH_ASSOC)) {
					    extract($row);
					    $sqlAlmacen = "SELECT id FROM c_almacenp WHERE clave = '$IdEmpresa'";
					    $insert = "INSERT IGNORE INTO c_articulo(cve_articulo, des_articulo, cve_codprov, cve_almac) VALUES ('$Clave', '$Producto', '$CodBarras', ({$sqlAlmacen}));";
					    $query = mysqli_query($connmysql, $insert);
					}
				}

				$queryServer2 = sqlsrv_query($connmssql, "SELECT * FROM Stock WHERE Stock > 0");

				if( $queryServer2 === false ) {
					echo json_encode(array(
						"success" 	=> false,
						"error"		=> sqlsrv_errors()
					));
				}else{
					$insert = "";
					while($row = sqlsrv_fetch_array($queryServer2, SQLSRV_FETCH_ASSOC)) {
					    extract($row);
					    $sqlAlmacen = "SELECT id FROM c_almacenp WHERE clave = '$IdEmpresa'";
					    $sqlZona = "SELECT cve_almac FROM c_almacen WHERE cve_almacenp = ({$sqlAlmacen})";
					    $sqlUbica = "SELECT idy_ubica FROM c_ubicacion WHERE cve_almac = ({$sqlZona})";
					    $insert = "INSERT IGNORE INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia) VALUES (({$sqlAlmacen}), ({$sqlUbica}), '$Articulo', '', '$Stock'); ";
					    $query = mysqli_query($connmysql, $insert);
					}
					echo json_encode(array(
						"success"	=> $query,
						"error"		=> "Ocurrió un error al guardar los datos en nuestra base de datos, intenta más tarde ".mysqli_error($connmysql)
					));
				}

				break;
			case 'almacenesSCTP':
				$query = sqlsrv_query($connmssql, "SELECT IdEmpresa, Direccion, Telefono, Contacto, Email, CP, Sucursal FROM Empresas");

				if( $query === false ) {
					echo json_encode(array(
						"success" 	=> false,
						"error"		=> sqlsrv_errors()
					));
				}else{
					$insert = "";
					while($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
					    extract($row);
					    $tipoAlmacen = "SELECT id FROM tipo_almacen ORDER BY id ASC LIMIT 1";
					    $insert .= "INSERT IGNORE INTO c_almacenp(clave, nombre, codigopostal, direccion, telefono, contacto, correo, Activo, cve_talmacen, cve_cia) VALUES ('$IdEmpresa', '$Sucursal', '$CP', '$Direccion', '$Telefono', '$Contacto', '$Email', '1', ({$tipoAlmacen}), '1'); ";
					}
					$query = mysqli_multi_query($connmysql, $insert);
					echo json_encode(array(
						"success"	=> $query,
						"error"		=> "Ocurrió un error al guardar los datos en nuestra base de datos, intenta más tarde ".mysqli_error($connmysql)
					));
				}

				break;
		}
		mysqli_close($connmysql);
		sqlsrv_close($connmssql);
	}

}

?>