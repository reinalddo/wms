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
	
	function gotoLogin($login, $pass) {
		$conn = mysqli_connect($this->ip_server, $this->user, $this->password, $this->db);
		mysqli_set_charset($conn, 'utf8');
		if( $conn === false ) {
			return mysqli_error();
		}
		
		$logged = false;
		
        $sql2 = "SELECT * FROM c_usuario Where cve_usuario='" . $login . "' And pwd_usuario='".$pass."';";
        $result2 = mysqli_query($conn, $sql2);
		
		$_arr = array();
		
		if (mysqli_num_rows($result2)>0) {
			$logged = true;
			$row2 = mysqli_fetch_array($result2);
			$_arr[] = array("Clave" => $row2["Clave"],
				"des_usuario" => $row2["des_usuario"],
				"cve_cia" => $row2["cve_cia"],
				"fec_ingreso" => $row2["fec_ingreso"],
				"ban_usuario" => $row2["ban_usuario"],
				"status" => $row2["status"],
				"Activo" => $row2["Activo"],
				"id_user" => $row2["id_user"],
				"logged" => $logged);
		} else {
			$_arr[] = array("Clave" => "",
				"des_usuario" => "",
				"cve_cia" => "",
				"fec_ingreso" => "",
				"ban_usuario" => "",
				"status" => "",
				"Activo" => "",
				"id_user" => "",
				"logged" => $logged);
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
    $ret = $t->$function($obj->{'login'}, $obj->{'pass'});

    header('Content-type: application/json');
    echo json_encode($ret);	
}