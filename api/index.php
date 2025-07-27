<?php
//mi commit
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
            $_arr[] = array("cve_almac" => $row2["cve_almac"],
                "cve_cia" => $row2["cve_cia"],
                "des_almac" => $row2["des_almac"],
                "des_direcc" => $row2["des_direcc"],
                "ManejaCajas" => $row2["ManejaCajas"],
                "ManejaPiezas" => $row2["ManejaPiezas"],
                "MaxXPedido" => $row2["MaxXPedido"],
                "Maneja_Maximos" => $row2["Maneja_Maximos"],
                "MANCC" => $row2["MANCC"],
                "Compromiso" => $row2["Compromiso"],
                "Activo" => $row2["Activo"],
                "cve_cia" => $row2["cve_cia"],
                "des_cia" => $row2["des_cia"]);
        }
        return $_arr;
    }

    function getStockAlmacen($POST) {
		ini_set('mbstring.substitute_character', "none");
        $conn = mysqli_connect($this->ip_server, $this->user, $this->password, $this->db);
        if( $conn === false ) {
            return mysqli_error();
        }
		mysqli_set_charset($conn,'utf8');
        $logged = false;
        $sql = "Select	A.clave Almacen,V.cve_articulo Articulo,SUM(V.Existencia) Cantidad
				From	V_ExistenciaGral V Join c_almacenp A On V.Cve_Almac=A.Id
				Where	V.Cuarentena=0 And V.Tipo!='area' And A.clave='".$POST['cve_almac']."'
				Group By V.cve_almac,V.cve_articulo;";
        $result2 = mysqli_query($conn, $sql);
        $_arr = array();
        while ($row2 = mysqli_fetch_array($result2)) {
            $_arr["StockAlmac"][] = array("Almacen" => $row2["Almacen"],
                "Articulo" => utf8_encode($row2["Articulo"]),
                "Cantidad" => utf8_encode($row2["Cantidad"]));
        }
        return $_arr;
    }

    function getEntradasAlmacen() {
		ini_set('mbstring.substitute_character', "none");
        $conn = mysqli_connect($this->ip_server, $this->user, $this->password, $this->db);
        if( $conn === false ) {
            return mysqli_error();
        }
		mysqli_set_charset($conn,'utf8');
        $logged = false;
        $sql = "SELECT fol_oep, id_ocompra, Empresa
            FROM
            vw_th_entalmacen;";
        $result2 = mysqli_query($conn, $sql);
        $_arr = array();
        while ($row2 = mysqli_fetch_array($result2)) {
            $_arr[] = array("fol_oep" => $row2["fol_oep"],
                "id_ocompra" => utf8_encode($row2["id_ocompra"]),
                "Empresa" => utf8_encode($row2["Empresa"]));
        }
        return $_arr;
    }

    function getCostosEntrada($POST) {
		ini_set('mbstring.substitute_character', "none");
        $conn = mysqli_connect($this->ip_server, $this->user, $this->password, $this->db);
        if( $conn === false ) {
            return mysqli_error();
        }
		mysqli_set_charset($conn,'utf8');
        $logged = false;
        $sql = "SELECT Cve_Articulo,SUM(CantidadRecibida),costoUnitario
            FROM td_entalmacen
			WHERE Fol_Folio=".$POST['Folio']."
			Group By Fol_FOlio,Cve_Articulo,costoUnitario;";
        $result2 = mysqli_query($conn, $sql);
        $_arr = array();
        while ($row2 = mysqli_fetch_array($result2)) {
			$conn2 = sqlsrv_connect($this->ip_server_remote,$this->connectinfo_remote);
			$sqlU = "Update Productos SET VBase=" . $row2["costoUnitario"] . " Where Clave='" . $row2["Cve_Articulo"] . "'";
			$reslt = sqlsrv_query($conn2, $sqlU);
			sqlsrv_close($conn2);
        }
		$arr = array(
			"success" => true
		);
        return $_arr;
    }
}

function valAlmacen($POST) {
    include '../config.php';
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if (isset($_POST['cve_almac'])){
		$Almac = $POST['cve_almac'];
	}
	else if(isset($_POST['Cve_Almacenp'])){
		$Almac = $POST['Cve_Almacenp'];
	}
	else if(isset($_POST['Almacen'])){
		$Almac = $POST['Almacen'];
	}
    $sql = "SELECT * FROM c_almacenp Where clave='" . $Almac . "';";
    $result = mysqli_query($conn, $sql);
    $_arr = array();
    if (mysqli_num_rows($result)>0) {
        $row = mysqli_fetch_array($result);
		return true;
    }
    return false;
}

function getUser($POST) {
    include '../config.php';
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT * FROM c_usuario Where cve_usuario='" . $POST['user'] . "' And pwd_usuario='".$POST['pwd']."';";
    $result = mysqli_query($conn, $sql);
    $_arr = array();
    if (mysqli_num_rows($result)>0) {
        $row = mysqli_fetch_array($result);
        if( $_POST['pwd'] == $row["pwd_usuario"] ) {
            session_start();
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['identifier'] = $row['identifier'];
            $_SESSION['subdomain'] = $row['subdomain'];
            $_SESSION['cve_cia'] = $row['cve_cia'];
            return true;
        }
    }
    return false;
}

set_time_limit(0);
$json = file_get_contents('php://input');
$_POST = (empty($HTTP_POST_FILES)) ? (array) json_decode($json) : $HTTP_POST_FILES;

if (isset($_POST) && !empty($_POST)) {
    if (isset($_POST['user']) && isset($_POST['pwd'])) {
        if (!getUser($_POST)) {
            $arr = array(   
                "success" => false,
                "err" => "usuario no existe"
            );
            echo json_encode($arr);
        }
    } else {
        $arr = array(
            "success" => false,
            "err" => "usuario no existe"
        );
        echo json_encode($arr);
        exit();
    }
	if (isset($_POST['cve_almac'])||isset($_POST['Cve_Almacenp'])||isset($_POST['Almacen'])){
		if (!valAlmacen($_POST)) {
            $arr = array(   
                "success" => false,
                "err" => "Almacen no existe"
            );
            echo json_encode($arr);
			exit();
        }
	}
    switch ($_POST['func']) {
        case "setProds":
            include '../app/load.php';
            $app = new \Slim\Slim();
            if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
                exit();
            }
            $a = new \Articulos\Articulos();
            if( $_POST['action'] == 'add' ) {
                $ret = $a->saveFromAPI($_POST);
                if ( $ret != FALSE) {
                    $arr = array(
                        "success" => true
                    );
                    echo json_encode($arr);
                    exit();
                } else {
					$ret = $a->actualizarArticulos($_POST);
					if ( $ret != FALSE) {
						$arr = array(
							"success" => true
						);
						echo json_encode($arr);
						exit();
					} else {
						$arr = array(
							"success" => false,
							"err" => $ret
						);
					}
                    echo json_encode($arr);
                    exit();
                }
            }
            break;
        case "setProveedores":
            include '../app/load.php';
            $app = new \Slim\Slim();
            if( !isset( $_SESSION['id_user'] ) AND ! $_SESSION['id_user'] ) {
                exit();
            }
            $c = new \Proveedores\Proveedores();
            if( $_POST['action'] == 'add' ) {
                $_POST['fromAPI'] = true;
                $ret = $c->save($_POST);
                if ($ret == "Guardado") {
                    $arr = array(
                        "success" => true
                    );
                    echo json_encode($arr);
                    exit();
                }
                else {
					$arr = array(
						"success" => false,
						"err" => "Error"
					);
					echo json_encode($arr);
					exit();
                }
            }
            break;
        case "setClientes":
            include '../app/load.php';
            $app = new \Slim\Slim();
            if( !isset( $_SESSION['id_user'] ) AND ! $_SESSION['id_user'] ) {
                exit();
            }
            $c = new \Clientes\Clientes();
            if( $_POST['action'] == 'add' ) {
                $_POST['fromAPI'] = true;
                $ret = $c->save($_POST);
                if ($ret == "Guardado") {
                    $arr = array(
                        "success" => true
                    );
                    echo json_encode($arr);
                    exit();
                }
                else {
					$ret = $c->actualizarClientes($_POST);
					if ($ret == "Actualizado") {
						$arr = array(
							"success" => true
						);
						echo json_encode($arr);
						exit();
					}
					else {
						$arr = array(
							"success" => false,
							"err" => "Error"
						);
						echo json_encode($arr);
						exit();
					}
                }
            }
            break;
        case "updateClientes":
            include '../app/load.php';
            $app = new \Slim\Slim();
            if( !isset( $_SESSION['id_user'] ) AND ! $_SESSION['id_user'] ) {
                exit();
            }
            $c = new \Clientes\Clientes();
            if( $_POST['action'] == 'add' ) {
                $_POST['fromAPI'] = true;
                $ret = $c->actualizarClientes($_POST);
                if ($ret == "Actualizado") {
                    $arr = array(
                        "success" => true
                    );
                    echo json_encode($arr);
                    exit();
                }
                else {
                    $arr = array(
                        "success" => false,
                        "err" => "Error"
                    );
                    echo json_encode($arr);
                    exit();
                }
            }
            break;
        case "setPedidos":
            if( $_POST['action'] == 'add' ) {
                include '../app/load.php';
                $app = new \Slim\Slim();
                if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
                    exit();
                }
                $p = new \Pedidos\Pedidos();
                $p->Fol_folio = $_POST["Fol_folio"];
                $p->__get("Fol_folio");
                $success = true;
                if (!empty($p->data->Fol_folio)) {
                    $success = false;
                }
                if (!$success) {
                    $arr = array(
                        "success" => $success,
                        "err" => "El Numero del Folio ya se ha Introducido"
                    );
                    echo json_encode($arr);
                    exit();
                }
                $arrd = array();
                $arrdestinatarios = array();
                foreach ($_POST["arrDetalle"] as $det) {
                    $a = (array) $det;
                    $arrd[] = array("codigo" => $a["codigo"], "descripcion" => $a["descripcion"], "CantPiezas" => $a["CantPiezas"], "cve_lote" => $a["cve_lote"]);
                }
                if( ! isset($_POST["destinatarios"]) or count($_POST["destinatarios"]) < 1 ) {
                    $_POST["destinatarios"] = [];
                }
                $_POST["arrDetalle"] = $arrd;
                $p->save($_POST);
                $arr = array(
                    "success" => true
                );
                echo json_encode($arr);exit();
            }
            break;
        case "setCross":
            if( $_POST['action'] == 'add' ) {
                include '../app/load.php';
                $app = new \Slim\Slim();
                if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
                    exit();
                }
                $p = new \PedidosCrossDocking\PedidosCrossDocking();
                $arrd = array();
                foreach ($_POST["crossDetalle"] as $det) {
                    $a = (array) $det;
                    $_POST["Fol_PedidoCon"] = $a["Fact_Madre"];
                    $arrd[] = array("Fol_PedidoCon" => $_POST["Fol_PedidoCon"],
                        "No_OrdComp" => $a["No_OrdComp"],
                        "Fec_OrdCom" => $a["Fec_OrdCom"],
                        "Cve_Articulo" => $a["Cve_Articulo"],
                        "Cant_Pedida" => $a["Cant_Pedida"],
                        "Unid_Empaque" => $a["Unid_Empaque"],
                        "Tot_Cajas" => $a["Tot_Cajas"],
                        "Fact_Madre" => $a["Fact_Madre"],
                        "Cve_Clte" => $a["Cve_Clte"],
                        "Cve_CteProv" => $a["Cve_CteProv"],
                        "Fol_Folio" => $a["Fol_Folio"],
                        "CodB_Cte" => $a["CodB_Cte"],
                        "Cod_PV" => $a["Cod_PV"]);
                }
                $_POST["crossDetalle"] = $arrd;
                $p->Fol_PedidoCon = $_POST["Fol_PedidoCon"];
                $p->__get("Fol_PedidoCon");
                $success = true;
                if (!empty($p->data->Fol_PedidoCon)) {
                    $success = false;
                }
                if (!$success) {
                    $arr = array(
                        "success" => $success,
                        "err" => "El Número del Folio ya se Ha Introducido"
                    );
                    echo json_encode($arr);
                    exit();
                }
                $p->save($_POST);
                $arr = array(
                    "success" => true
                );
                echo json_encode($arr);
                exit();
            }
            break;
        case "getPedidos":
            if ($_POST['action'] == 'load') {
                include '../app/load.php';
                $app = new \Slim\Slim();
                if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
                    exit();
                }
                $p = new \Pedidos\Pedidos();
                $p->Fol_folio = $_POST["Fol_folio"];
				$p->cve_almac = $_POST["cve_almac"];
                $p->__getStatus("Fol_folio");
                $success = true;
                if (empty($p->data->Fol_folio)) {
                    $success = false;
                }
                if (!$success) {
                    $arr = array(
                        "success" => $success,
                        "err" => "El pedido no esta listo para ser enviado"
                    );
                    echo json_encode($arr);
                    exit();
                }
                $arr = array();
                $p->__getDetalle("Fol_folio");
                foreach ($p->data as $nombre => $valor) $arr2[$nombre] = $valor;
                $arr2["detalle"] = $p->dataDetalle;
                $arr["getPedidos"][] = array_merge($arr, $arr2);
                echo json_encode($arr);
            }
            break;
        case "getMovsAlmac":
            if ($_POST['action'] == 'load') {
                include '../app/load.php';
                $app = new \Slim\Slim();
                if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
                    exit();
                }
                $p = new \Pedidos\Pedidos();
                $p->Referencia = $_POST["Referencia"];
				$p->cve_almac = $_POST["cve_almac"];
				if($_POST["TipoMov"]=='E'){$p->TipoMov = 1;}
				if($_POST["TipoMov"]=='S'){$p->TipoMov = 8;}
                $p->__getStatus("Referencia");
                $success = true;
                if (empty($p->data->Referencia)) {
                    $success = false;
                }
                if (!$success) {
                    $arr = array(
                        "success" => $success,
                        "err" => "Aun no esta listo el movimiento"
                    );
                    echo json_encode($arr);
                    exit();
                }
                $arr = array();
                $p->__getDetalleMov("Referencia");
                foreach ($p->data as $nombre => $valor) $arr2[$nombre] = $valor;
                $arr2["detalle"] = $p->dataDetalle;
                $arr = array_merge($arr, $arr2);
                echo json_encode($arr);
            }
            break;
        case "setUbicacion":
            if ($_POST['action'] == 'add') {
                include '../app/load.php';
                $app = new \Slim\Slim();
                if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
                    exit();
                }
                $p = new \UbicacionAlmacenaje\UbicacionAlmacenaje();
                $p->save($_POST);
                $arr = array(
                    "success" => true
                );
                echo json_encode($arr);
                exit();
            }
            break;
        case "getUbicacion":
            if ($_POST['action'] == 'load') {
                error_reporting(0);
                include '../config.php';
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid
                $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
                $sord = $_POST['sord']; // get the direction
                //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
                $_criterio = $_POST['criterio'];
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
                if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));
                $start = $limit*$page - $limit; // do not put $limit*($page - 1)
                if(!$sidx) $sidx =1;
                // se conecta a la base de datos
                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                //mysqli_set_charset($conn, 'utf8');
                // prepara la llamada al procedimiento almacenado Lis_Facturas
                $sqlCount = "Select * from c_ubicacion Where Activo = '1';";
                if (!($res = mysqli_query($conn, $sqlCount))) {
                    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                }
                $row = mysqli_fetch_array($res);
                $count = $row[0];
                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                $_page = 0;
                if (intval($page)>0) $_page = ($page-1)*$limit;
                $sql = "SELECT
						c_ubicacion.idy_ubica,
						c_ubicacion.cve_almac,
						c_ubicacion.cve_pasillo,
						c_ubicacion.cve_rack,
						c_ubicacion.cve_nivel,
						c_ubicacion.num_ancho,
						c_ubicacion.num_largo,
						c_ubicacion.num_alto,
						c_ubicacion.num_volumenDisp,
						c_ubicacion.`Status`,
						c_ubicacion.picking,
						c_ubicacion.Seccion,
						c_ubicacion.Ubicacion,
						c_ubicacion.orden_secuencia,
						c_ubicacion.PesoMaximo,
						c_ubicacion.PesoOcupado,
						c_ubicacion.claverp,
						c_ubicacion.CodigoCSD,
						c_ubicacion.TECNOLOGIA,
						c_ubicacion.Maneja_Cajas,
						c_ubicacion.Maneja_Piezas,
						c_ubicacion.Reabasto,
						c_ubicacion.Activo,
						c_almacen.cve_almac,
						c_almacen.des_almac
						FROM
						c_ubicacion
						INNER JOIN c_almacen ON c_ubicacion.cve_almac = c_almacen.cve_almac Where c_ubicacion.Ubicacion like '%".$_criterio."%' and c_ubicacion.Activo = '1' GROUP BY c_ubicacion.cve_almac LIMIT $_page, $limit;";
                // hace una llamada previa al procedimiento almacenado Lis_Facturas
                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                }
                if( $count >0 ) {
                    $total_pages = ceil($count/$limit);
                } else {
                    $total_pages = 0;
                } if ($page > $total_pages)
                    $page=$total_pages;
                $responce["page"] = $page;
                $responce["total"] = $total_pages;
                $responce["records"] = $count;
                $arr = array();
                $i = 0;
                while ($row = mysqli_fetch_array($res)) {
                    $arr[] = $row;
                    $responce["rows"][$i]['id']=$row['idy_ubica'];
                    $responce["rows"][$i]['cell']=array($row['cve_almac'], $row['idy_ubica'], $row['des_almac'], $row['Ubicacion']);
                    $i++;
                }
                echo json_encode($responce);
                exit();
            }
            break;
        case "getAlmacen":
            if( $_POST['action'] == 'load' ) {
                include '../config.php';
                $t = new Sql2JSONApi();
                $t->ip_server = DB_HOST;
                $t->db = DB_NAME;
                $t->user = DB_USER;
                $t->password = DB_PASSWORD;
                $function = "getAlmacen";
                $ret = $t->$function();
                echo json_encode($ret);
            }
            break;
        case "getStockAlmacen":
            if( $_POST['action'] == 'load' ) {
                include '../config.php';
                $t = new Sql2JSONApi();
                $t->ip_server = DB_HOST;
                $t->db = DB_NAME;
                $t->user = DB_USER;
                $t->password = DB_PASSWORD;
                $function = "getStockAlmacen";
                $ret = $t->$function($_POST);
                echo json_encode($ret);
            }
            break;
        case "getEntradasAlmacen":
            if( $_POST['action'] == 'load' ) {
                include '../config.php';
                $t = new Sql2JSONApi();
                $t->ip_server = DB_HOST;
                $t->db = DB_NAME;
                $t->user = DB_USER;
                $t->password = DB_PASSWORD;
                $function = "getEntradasAlmacen";
                $ret = $t->$function();
                echo json_encode($ret);
            }
            break;
        case "getCostosEntrada":
            if( $_POST['action'] == 'load' ) {
                include '../config.php';
                $t = new Sql2JSONApi();
                $t->ip_server = DB_HOST;
                $t->db = DB_NAME;
                $t->user = DB_USER;
                $t->password = DB_PASSWORD;
				// Datos remotos
				$t->ip_remote_server = DB_REMOTE_HOST;
				$t->db_remote = DB_REMOTE_NAME;
				$t->user_remote = DB_REMOTE_USER;
				$t->password_remote = DB_REMOTE_PASSWORD;
				$t->connectinfo_remote = array("Database"=>DB_REMOTE_NAME, "UID"=>DB_REMOTE_USER, "PWD"=>DB_REMOTE_PASSWORD, "CharacterSet"=>"UTF-8");
                $function = "getCostosEntrada";
                $ret = $t->$function();
                echo json_encode($ret);
            }
            break;
		case "setEntradasAlmacen":
            if( $_POST['action'] == 'add' ) {
                include '../app/load.php';
                $app = new \Slim\Slim();
                if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
                    exit();
                }
                $p = new \EntradaAlmacen\EntradaAlmacen();
				$arrd = array();
                foreach ($_POST["arrDetalle"] as $det) {
                    $a = (array) $det;
                    $arrd[] = array("codigo" => $a["codigo"], "CantPiezas" => $a["CantPiezas"], "Lote" => $a["Lote"], "Caducidad" => $a["Caducidad"], "Temperatura" => $a["Temperatura"], "ItemNum" => $a["ItemNum"]);
                }
                $_POST["arrDetalle"] = $arrd;
                $ret=$p->save($_POST);
				if ($ret == "Guardado") {
                    $arr = array(
                        "success" => true
                    );
                    echo json_encode($arr);
                    exit();
                }
                else {
                    $arr = array(
                        "success" => false,
                        "err" => "Error"
                    );
                    echo json_encode($arr);
                    exit();
                }
            }
            break;
		case "setOrdenesTrabajo":
            if( $_POST['action'] == 'add' ) {
                include '../app/load.php';
                $app = new \Slim\Slim();
                if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
                    exit();
                }
                $p = new \OrdenesTrabajo\OrdenesTrabajo();
				$arrd = array();
                foreach ($_POST["arrDetalle"] as $det) {
                    $a = (array) $det;
                    $arrd[] = array("codigo" => $a["codigo"], "loteA" => $a["loteA"], "CantPiezas" => $a["CantPiezas"]);
                }
                $_POST["arrDetalle"] = $arrd;
                $ret=$p->save($_POST);
				if ($ret == "Guardado") {
                    $arr = array(
                        "success" => true
                    );
                    echo json_encode($arr);
                    exit();
                }
                else {
                    $arr = array(
                        "success" => false,
                        "err" => "Error"
                    );
                    echo json_encode($arr);
                    exit();
                }
            }
            break;
    }
}