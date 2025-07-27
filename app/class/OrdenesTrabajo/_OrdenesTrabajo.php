<?php

namespace OrdenesTrabajo;

class OrdenesTrabajo {

    function save( $_post ) {
        try {
			$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $sql = "Call SPWS_AgregaOrdenProd('"
				. $_post['Almacen'] . "','"
				. $_post['NumeroOT'] . "','"
				. $_post['Articulo'] . "','"
				. $_post['Lote'] . "','"
				. $_post['FCaducidad'] . "',"
				. $_post['Cantidad'] . ");";
var_dump($sql);
			if(!$result = $conn->query($sql))
			{
				$conn->close();
				return 'ERROR: ' . 'NO SE EJECUTO EL SP SPWS_AgregaOrdenProd :(';
			}
			else
			{
				while ($row = $result->fetch_array(MYSQLI_NUM)) {
					$_arr = array("ERROR" => $row[0],
									"MSG" => $row[1]);
				}
				$conn->close();
				$a=(array)$_arr;
				if($a['ERROR']==-1)
				{
					return 'ERROR: '.$a['MSG'];
				}
				if (!empty($_post["arrDetalle"])) {
					foreach ($_post["arrDetalle"] as $item) {
						$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
						$sql = "Call SPWS_AgregaDetOrdenProd( '";
						$sql .= $_post["NumeroOT"] . "','"
							. $item['codigo'] . "',"
							. $item['CantPiezas'] . ");";
						if(!$res = $conn->query($sql))
						{
							$conn->close();
							return 'ERROR: ' . 'NO SE EJECUTO EL SP SPWS_AgregaDetOrdenProd :(';
						}
						else
						{
							$conn->close();
						}
					}
				}
            }
			return 'Guardado';
        }
		catch(Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

}
