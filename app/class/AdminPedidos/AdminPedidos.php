<?php

namespace AdminPedidos;

class AdminPedidos {

    const TABLE = 'th_pedido';
    var $identifier;

    public function __construct( $Fol_folio = false, $key = false ) {

        if( $Fol_folio ) {
            $this->Fol_folio = (int) $Fol_folio;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            Fol_folio
          FROM
            %s
          WHERE
            Fol_folio = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Pedidos\Pedidos');
            $sth->execute(array($key));

            $Fol_folio = $sth->fetch();

            $this->Fol_folio = $Fol_folio->Fol_folio;

        }

    }

    private function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          Fol_folio = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Pedidos\Pedidos' );
        $sth->execute( array( $this->Fol_folio ) );

        $this->data = $sth->fetch();

    }

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
		  where Activo=1
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Pedidos\Pedidos' );
        $sth->execute( array( Fol_folio ) );

        return $sth->fetchAll();

    }

    private function loadDetalle() {

        $sql = "SELECT
                td_pedido.Fol_folio,
                td_pedido.Cve_articulo,
                td_pedido.Num_cantidad,
                c_articulo.des_articulo
                FROM
                td_pedido
                INNER JOIN c_articulo ON td_pedido.Cve_articulo = c_articulo.cve_articulo WHERE td_pedido.Fol_folio = '".$this->Fol_folio."'";

        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

        $arr = array();

        while ($row = mysqli_fetch_array($rs)) {
            $arr[] = $row;
        }

        $this->dataDetalle = $arr;

    }

    function __get( $key ) {

        switch($key) {
            case 'Fol_folio':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function __getDetalle( $key ) {

        switch($key) {
            case 'Fol_folio':
                $this->loadDetalle();
                return @$this->dataDetalle->$key;
            default:
                return $this->key;
        }

    }

    function save( $_post ) {
		try {
			$sql = "INSERT INTO " . self::TABLE . " (Fol_folio, Cve_clte, status, cve_Vendedor, Fec_Entrada, Pick_Num, Cve_Usuario, Observaciones, cve_almac, Activo)";
            $sql .= "Values (";
            $sql .= "'".$_post['Fol_folio']."',";
            $sql .= "'".$_post['Cve_clte']."',";
            $sql .= "'B',";
            $sql .= "'".$_post['cve_Vendedor']."',";
            $sql .= "now(),";
            $sql .= "'".$_post['Pick_Num']."',";
            $sql .= "'".$_post['user']."',";
            $sql .= "'".$_post['Observaciones']."',";
            $sql .= "'".$_post['cve_almac']."', '1');";

            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

            if (!empty($_post["arrDetalle"])) {
                $sql = "Delete From td_pedido WHERE Fol_folio = '".$_post['Fol_folio']."'";
                $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

                foreach ($_post["arrDetalle"] as $item) {
                    $sql = "INSERT INTO td_pedido (Cve_articulo, Num_cantidad, Fol_folio, Activo) Values ";
                    $sql .= "('".$item['codigo']."', '".$item['CantPiezas']."', '".$_post['Fol_folio']."', '1');";
                    $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                }
            }
		} catch(Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    function borrarPedido( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          Fol_folio = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['Fol_folio']
        ) );
    }

    function actualizarPedido( $_post ) {
        try {
            $sql = "Delete From " . self::TABLE . " WHERE Fol_folio = '".$_post['Fol_folio']."'";
            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

            $sql = "INSERT INTO " . self::TABLE . " (Fol_folio, Cve_clte, cve_Vendedor, Fec_Entrada, Pick_Num, Observaciones, cve_almac, Activo)";
            $sql .= "Values (";
            $sql .= "'".$_post['Fol_folio']."',";
            $sql .= "'".$_post['Cve_clte']."',";
            $sql .= "'".$_post['cve_Vendedor']."',";
            $sql .= "now(),";
            $sql .= "'".$_post['Pick_Num']."',";
            $sql .= "'".$_post['Observaciones']."',";
            $sql .= "'".$_post['cve_almac']."', '1');";

            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

            if (!empty($_post["arrDetalle"])) {
                $sql = "Delete From td_pedido WHERE Fol_folio = '".$_post['Fol_folio']."'";
                $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

                foreach ($_post["arrDetalle"] as $item) {
                    $sql = "INSERT INTO td_pedido (Cve_articulo, Num_cantidad, Fol_folio, Activo) Values ";
                    $sql .= "('".$item['codigo']."', '".$item['CantPiezas']."', '".$_post['Fol_folio']."', '1');";
                    $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                }
            }
        } catch(Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    function planificar($pedidos) {
		foreach ($pedidos as $folio) {
			$sql = mysqli_query(\db2(), "CALL SPRF_PreparaPedidoSurtido ('".$folio."');") or die(mysqli_error(\db2()));
		}	
    }

    function asignar($usuarios,$pedidos) {

		foreach ($pedidos as $folio) {
			$sql = mysqli_query(\db2(), "CALL SPAD_CreaSubPedido ('".$folio."','".$usuario[0]."');") or die(mysqli_error(\db2()));
		}	
    }


}
