<?php
class Consultas
{
	var $ip_server;
	var $uid;
	var $pswd;
	var $db;
	var $arrTbl;

	function Productos()
	{
        $conn =  new mysqli($this->ip_server, $this->uid, $this->pswd, $this->db);
        $i=0;
        foreach((array)$this->arrTbl as $detalle)
        {
            $MiSql[] = $detalle;
            $sql = "Call SPWS_DameProducto ('"
                . $MiSql[$i]->{'Almacen'} . "','"
                . $MiSql[$i]->{'Clave1'} . "');";
            $result = $conn->query($sql);
			$_arr = array();
			if(!$result)
			{
				$_arr[] = array("ERROR" => -1,
								"MSG" => utf8_encode($conn->error));
				$conn->close();
				return $_arr;
			}
			while ($row = $result->fetch_array(MYSQLI_NUM)) {
				$_arr[] = array("Id" => $row[0],
								"Cve_Articulo" => $row[1],
								"Des_Articulo" => $row[2],
								"CodBarras" => $row[3],
								"Fecha" => $row[4],
								"UnidadMedida" => $row[5],
								"Peso" => $row[6],
								"PzsXCaja" => $row[7],
								"ManejaSerie" => $row[8],
								"ManejaLote" => $row[9],
								"ManejaCaducidad" => $row[10],
								"Grupo" => $row[11],
								"clasificacion" => $row[12],
								"tipo" => $row[13],
								"Alto" => $row[14],
								"Ancho" => $row[15],
								"Fondo" => $row[16],
								"Compuesto" => $row[17],
								"Activo" => $row[18]);
			}
            $i++;
        }
		$conn->close();
        return $_arr;
	}
}

include '../config.php';
header("Content-Type:application/json; Charset=UTF-8'");
// Clase para las funciones de las consultas
$t = new Consultas();
$t->ip_server = DB_HOST;
$t->db = DB_NAME;
$t->uid = DB_USER;
$t->pswd = DB_PASSWORD;
 //recojemos variables
$function = $_GET['Catalogo'];
$Almacen = $_GET['Almacen'];
$Clave1 = $_GET['Clave1'];
$Clave2 = $_GET['Clave2'];
$arr = array();
if(empty($Almacen))
{
	$arr[] = array("Error" => -1,
					"Msg" => "Debe capturar la clave del almacén");
	echo json_encode($arr);
	exit();
}
if(empty($Clave1))
{
	$arr[] = array("Error" => -1,
					"Msg" => "Debe capturar la clave a consultar");
	echo json_encode($arr);
	exit();
}
if(empty($Clave2))
{
	$arr[] = array("Almacen" => $Almacen,
				"Clave1" => $Clave1);
}
else
{
	$arr[] = array("Almacen" => $Almacen,
				"Clave1" => $Clave1,
				"Clave2" => $Clave2);
}
$Jsn = json_encode($arr);
$Obj = json_decode($Jsn);
$t->arrTbl = array();
$t->arrTbl = (array)$Obj;
$ret = $t->$function();
echo json_encode($ret);