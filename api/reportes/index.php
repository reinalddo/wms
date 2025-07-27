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
	
	function getAlmacen() {
		$conn = mysqli_connect($this->ip_server, $this->user, $this->password, $this->db);

		if( $conn === false ) {
			return mysqli_error();
		}
		
		$logged = false;
		
        $sql = "SELECT
            c_almacen.cve_almac,
            c_almacen.cve_cia,
            c_almacen.des_almac,
            c_almacen.des_direcc,
            c_almacen.ManejaCajas,
            c_almacen.ManejaPiezas,
            c_almacen.MaxXPedido,
            c_almacen.Maneja_Maximos,
            c_almacen.MANCC,
            c_almacen.Compromiso,
            c_almacen.Activo,
            c_compania.cve_cia,
            c_compania.des_cia
            FROM
            c_almacen INNER JOIN c_compania ON c_almacen.cve_cia = c_compania.cve_cia;";
        $result2 = mysqli_query($conn, $sql);
		
		$_arr = array();
		
		while ($row2 = mysqli_fetch_array($result2)) {
			$_arr[] = $row2;
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