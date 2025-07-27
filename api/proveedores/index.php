<?php

class Sql2JSONApi {
	var $idRuta;
    var $idCliente;
    var $Ruta;
    var $ip_server;
    var $user;
    var $password;
    var $db;
    var $connectinfo;
    var $Vendedor;
    var $IdEmpresa;
	
	function getProveedores() {
		$conn = mysqli_connect($this->ip_server, $this->user, $this->password, $this->db);

		if( $conn === false ) {
			return mysqli_error();
		}
		
		$logged = false;
		
        $sql = "SELECT * FROM c_proveedores;";
        $result2 = mysqli_query($conn, $sql);
		
		$_arr = array();
		
		while ($row2 = mysqli_fetch_array($result2)) {
			$_arr[] = array("ID_Proveedor" => $row2["ID_Proveedor"],
							"Empresa" => utf8_encode($row2["Empresa"]));
		}
		
        return $_arr;
	}
	
}

$json = file_get_contents('php://input');
$obj = json_decode($json);

/*$obj->{'function'} = "gotoLogin";
$obj->{'login'} = "admin";
$obj->{'pass'} = "admin";*/

if (!empty($obj)) {
    //error_reporting(0);

    $t = new Sql2JSONApi();
    $t->ip_server = "localhost";
    $t->db = "samenli1_wms";
    $t->user = "samenli1_wms";
    $t->password = 'upxG}^{-+XSz';

    $function = $obj->{'function'};
    $ret = $t->$function();

    header('Content-type: application/json');
    echo json_encode($ret);	
}