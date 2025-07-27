<?php
namespace EntradaAlmacen;
class EntradaAlmacen {
    const TABLE = 'th_entalmacen';
    const TABLE_D = 'td_entalmacen';
    var $identifier;
    public function __construct( $fol_folio = false, $key = false ) {
        if( $fol_folio ) {
            $this->fol_folio = (int) $fol_folio;
        }
        if($key) {
            $sql = sprintf('SELECT fol_folio FROM %s WHERE fol_folio=?',self::TABLE);
            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\EntradaAlmacen\EntradaAlmacen');
            $sth->execute(array($key));
            $fol_folio = $sth->fetch();
            $this->fol_folio = $fol_folio->fol_folio;
        }
    }

    private function load() {
        $sql = sprintf('SELECT * FROM %s WHERE td_entalmacen.fol_folio=?',self::TABLE);
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\EntradaAlmacen\EntradaAlmacen' );
        $sth->execute( array( $this->fol_folio ) );
        $this->data = $sth->fetch();
    }

    private function loadDetalle() {
        $sql = "SELECT td_entalmacen.fol_folio,td_entalmacen.cve_articulo,td_entalmacen.cve_lote,td_entalmacen.CantidadPedida,td_entalmacen.CantidadRecivida,
					td_entalmacen.CantidadDisponible,td_entalmacen.CantidadUbicada,c_articulo.des_articulo,c_lotes.LOTE,c_lotes.CADUCIDAD
				FROM td_entalmacen INNER JOIN c_articulo ON td_entalmacen.cve_articulo=c_articulo.cve_articulo
					INNER JOIN c_lotes ON c_articulo.cve_articulo=c_lotes.cve_articulo
				WHERE td_entalmacen.id_ocompra='".$this->fol_folio."'";
        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
        $arr = array();
        while ($row = mysqli_fetch_array($rs)) {
            $arr[] = $row;
        }
        $this->dataDetalle = $arr;
    }

    function __get( $key ) {
        switch($key) {
            case 'fol_folio':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }
    }

    function __getDetalle( $key ) {
        switch($key) {
            case 'fol_folio':
                $this->loadDetalle();
                return @$this->dataDetalle->$key;
            default:
                return $this->key;
        }
    }

    function actualizarOrden( $_post ) {
		try {
            if (!empty($_post["arreglo"])) {
                foreach ($_post["arreglo"] as $item) {
                    $a = (array) $item;
                    $sql = "Select SPAD_AgregaArticuloEntrada(
                            '" . $a["Folio"] . "',
                            '" . $a["Producto"] . "',
                            '" . $a["Lote"] . "',
                            '" . $a["CantidadRecivida"] . "');";
                    $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                    $row = mysqli_fetch_array($rs);
                }
            }
		} catch(Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    function borrarCliente( $data ) {
        $sql = 'UPDATE ' . self::TABLE . ' SET Activo = 0 WHERE num_pedimento = ?';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['num_pedimento']
        ) );
    }

    function save( $_post ) {
        try {
			$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $sql = "Call SPWS_CreaAduana('"
				. $_post['NPedimento'] . "','"
				. $_post['FechaPedimento'] . "','"
				. $_post['Aduana'] . "','"
				. $_post['OrdCompra'] . "','"
				. $_post['Proveedor'] . "','"
				. $_post['Protocolo'] . "','"
				. $_post['Almacen'] . "');";
			if(!$result = $conn->query($sql))
			{
				$conn->close();
				return 'ERROR: ' . 'NO SE EJECUTO EL SP SPWS_CreaAduana :(';
			}
			else
			{
				while ($row = $result->fetch_array(MYSQLI_NUM)) {
					$_arr = array("ERROR" => $row[0],
									"MSG" => $row[1],
									"ID_Aduana" => $row[2]);
				}
				$conn->close();
				// foreach($_arr as $MiItem){
					$a=(array)$_arr;
					if($a['ERROR']==-1)
					{
						return 'ERROR: '.$a['MSG'];
					}
					if (!empty($_post["arrDetalle"])) {
						foreach ($_post["arrDetalle"] as $item) {
							$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
							$sql = "Call SPWS_AgregaDetalleAduana( ";
							$sql .= $a["ID_Aduana"] . ",'"
								. $item['codigo'] . "',"
								. $item['CantPiezas'] . ",'"
								. $item['Lote'] . "','"
								. $item['Caducidad'] . "','"
								. $item['Temperatura'] . "','"
								. $item['ItemNum'] . "',1);";
							if(!$res = $conn->query($sql))
							{
								$conn->close();
								return 'ERROR: ' . 'NO SE EJECUTO EL SP SPWS_AgregaDetalleAduana :(';
							}
							else
							{
								// while ($row1 = $res->fetch_array(MYSQLI_NUM)) {
								// 	$_narr = array("ERROR" => $row1[0],
								// 					"MSG" => $row1[1]);
								// }
								$conn->close();
							}
						}
					}
				// }
            }
			return 'Guardado';
        }
		catch(Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }
}
