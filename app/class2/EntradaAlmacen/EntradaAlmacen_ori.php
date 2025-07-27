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

            $sql = sprintf('
          SELECT
            fol_folio
          FROM
            %s
          WHERE
            fol_folio = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\EntradaAlmacen\EntradaAlmacen');
            $sth->execute(array($key));

            $fol_folio = $sth->fetch();

            $this->fol_folio = $fol_folio->fol_folio;

        }

    }

    private function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          td_entalmacen.fol_folio = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\EntradaAlmacen\EntradaAlmacen' );
        $sth->execute( array( $this->fol_folio ) );

        $this->data = $sth->fetch();

    }

    private function loadDetalle() {

        $sql = "SELECT
				td_entalmacen.fol_folio,
				td_entalmacen.cve_articulo,
				td_entalmacen.cve_lote,
				td_entalmacen.CantidadPedida,
				td_entalmacen.CantidadRecivida,
				td_entalmacen.CantidadDisponible,
				td_entalmacen.CantidadUbicada,
				c_articulo.des_articulo,
				c_lotes.LOTE,
				c_lotes.CADUCIDAD
				FROM
				td_entalmacen
				INNER JOIN c_articulo ON td_entalmacen.cve_articulo = c_articulo.cve_articulo
				INNER JOIN c_lotes ON c_articulo.cve_articulo = c_lotes.cve_articulo WHERE td_entalmacen.id_ocompra = '".$this->fol_folio."'";

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

    function save( $_post ) {
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
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          num_pedimento = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['num_pedimento']
        ) );
    }

    function actualizarOrden( $_post ) {
        try {
            $sql = "Delete From " . self::TABLE . " WHERE num_pedimento = '".$_post['num_pedimento']."'";
            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

            $sql = "INSERT INTO " . self::TABLE . " (num_pedimento, fech_pedimento, ID_Proveedor, ID_Protocolo, Consec_protocolo, factura, Cve_Almac, Activo)";
            $sql .= "Values (";
            $sql .= "'".$_post['num_pedimento']."',";
            $sql .= "now(),";
            $sql .= "'".$_post['ID_Proveedor']."',";
            $sql .= "'".$_post['ID_Protocolo']."',";
            $sql .= "'".$_post['Consec_protocolo']."',";
            $sql .= "'".$_post['factura']."',";
            $sql .= "'".$_post['Cve_Almac']."', '1');";

            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

            if (!empty($_post["arrDetalle"])) {
                $sql = "Delete From td_aduana WHERE num_orden = '".$_post['num_pedimento']."'";
                $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

                foreach ($_post["arrDetalle"] as $item) {
                    $sql = "INSERT INTO td_aduana (cve_articulo, cantidad, num_orden, cve_lote, Activo) Values ";
                    $sql .= "('".$item['codigo']."', '".$item['CantPiezas']."', '".$_post['num_pedimento']."', '1', '1');";
                    $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                }
            }
        } catch(Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }
}
