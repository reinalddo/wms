<?php

namespace Pedidos;

class Pedidos {

    const TABLE = 'th_pedido';
    const TABLE_D = 'td_pedido';
    const TABLE_C = 'c_cliente';
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

    private function loadChangeStatus() {

        $sql = sprintf('
        SELECT
          status
        FROM
          %s
        WHERE
          ID_Pedido = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Pedidos\Pedidos' );
        $sth->execute( array( $this->ID_Pedido ) );

        $this->data = $sth->fetch();

    }

    function getStatus() {

        $sql = '
        SELECT
          *
        FROM
          cat_estados 
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Pedidos\Pedidos' );
        $sth->execute( array( ESTADO ) );

        return $sth->fetchAll();

    }

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Pedidos\Pedidos' );
        $sth->execute( array( Fol_folio ) );

        return $sth->fetchAll();

    }
    //Pedidos disponibles para embarque
    function getAllForShipment($page = 0, $limit = 0) {

        $sql = "SELECT th_pedido.Fol_folio AS folio, IFNULL(c_cliente.RazonSocial, '') as cliente, IFNULL(td_pedido.SurtidoXCajas, '0') as cajas, IFNULL(td_pedido.SurtidoXPiezas, '0') as piezas FROM th_pedido LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte LEFT JOIN td_pedido ON td_pedido.Fol_folio = th_pedido.Fol_folio WHERE th_pedido.status = 'C'";

        if($limit > 0 || $page > 0){
            $sql .= " LIMIT $page, $limit";
        }

        $sth = \db()->prepare( $sql );
        $sth->execute();

        return $sth->fetchAll(\PDO::FETCH_CLASS);

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

    function __getChangeStatus( $key ) {

        switch($key) {
            case 'ID_Pedido':
                $this->loadChangeStatus();
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

            $Fol_folio        = " ";                    if($_post['Fol_folio']) $Fol_folio = $_post['Fol_folio'];

            $Fec_Pedido       = "0000-00-00 00:00:00";  if($_post['Fec_Pedido']) 
                                                        {
                                                            $date=date_create($_post['Fec_Pedido']);
                                                            $Fec_Pedido = date_format($date,"Y-m-d");
                                                            //$Fec_Pedido = "CONCAT(DATE_FORMAT($Fec_Pedido, '%Y-%m-%d'),' ', DATE_FORMAT(NOW(), '%H:%i:%s'))";
                                                            //$fecha_datetime = explode("-", $Fec_Pedido);
                                                            //$Fec_Pedido = $fecha_datetime[2]."-".$fecha_datetime[1]."-".$fecha_datetime[0];
                                                        }

            $Fec_Entrega      = "0000-00-00 00:00:00";  if($_post["Fec_Entrega"]) 
                                                        {
                                                            $date=date_create($_post['Fec_Entrega']);
                                                            $Fec_Entrega = date_format($date,"Y-m-d");
                                                            //$Fec_Entrega = "CONCAT(DATE_FORMAT($Fec_Entrega, '%Y-%m-%d'),' ', DATE_FORMAT(NOW(), '%H:%i:%s'))";
                                                            //$fecha_datetime = explode("-", $Fec_Entrega);
                                                            //$Fec_Entrega = $fecha_datetime[2]."-".$fecha_datetime[1]."-".$fecha_datetime[0];
                                                        }

            $Fec_Entrada      = "0000-00-00 00:00:00";  if($_post["Fec_Entrada"]) 
                                                        {
                                                            $date=date_create($_post['Fec_Entrada']);
                                                            $Fec_Entrada = date_format($date,"Y-m-d");
                                                            //$Fec_Entrada = "CONCAT(DATE_FORMAT($Fec_Entrada, '%Y-%m-%d'),' ', DATE_FORMAT(NOW(), '%H:%i:%s'))";
                                                            //$fecha_datetime = explode("-", $Fec_Entrada);
                                                            //$Fec_Entrada = $fecha_datetime[2]."-".$fecha_datetime[1]."-".$fecha_datetime[0];
                                                        }

            $Cve_clte         = " ";                    if($_post['Cve_clte']) $Cve_clte = $_post['Cve_clte'];
            $cve_Vendedor     = " ";                    if($_post['cve_Vendedor']) $cve_Vendedor = $_post['cve_Vendedor'];
            $Pick_Num         = " ";                    if($_post['Pick_Num']) $Pick_Num = $_post['Pick_Num'];
            $user             = " ";                    if($_post['user']) $user = $_post['user'];
            $Observaciones    = " ";                    if($_post['Observaciones']) $Observaciones = $_post['Observaciones'];
            $ID_Tipoprioridad = 1;                      if($_post['ID_Tipoprioridad']) $ID_Tipoprioridad = $_post['ID_Tipoprioridad'];
            $cve_almac        = " ";                    if($_post['cve_almac']) $cve_almac = $_post['cve_almac'];
            $Sku        = " ";                          if($_post['Sku']) $Sku = $_post['Sku'];


            $sql = "INSERT IGNORE INTO " . self::TABLE . " (Fol_folio, 
            Fec_Pedido, 
            Fec_Entrega, 
            Fec_Entrada, 
            Cve_clte, status, cve_Vendedor, Pick_Num, Cve_Usuario, Observaciones, ID_Tipoprioridad, cve_almac, Activo, Cve_CteProv) ";
            $sql .= "Values (";
            $sql .= "'".$Fol_folio."',";
            $sql .= "'".$Fec_Pedido."',";
            $sql .= "'".$Fec_Entrega."',";
            $sql .= "'".$Fec_Entrada."',";
            $sql .= "'".$Cve_clte."',";
            $sql .= "'A',";
            $sql .= "'".$cve_Vendedor."',";            
            $sql .= "'".$Pick_Num."',";
            $sql .= "'".$user."',";
            $sql .= "'".$Observaciones."',";
            $sql .= "'".$ID_Tipoprioridad."',";
            $sql .= " ( SELECT id FROM c_almacenp WHERE clave = '".$cve_almac."') ,";
            $sql .= "1,";
            $sql .= "'".$Cve_clte."');";

            mysqli_set_charset(\db2(), 'utf8');
            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
            $i = 1;

            if (!empty($_post["arrDetalle"])) {
                $sql = "DELETE FROM td_pedido WHERE Fol_folio = '".$_post['Fol_folio']."'";
                $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

                $detalles = [];

                foreach ($_post["arrDetalle"] as $item)
                {
                    $codigo = $item['codigo'];
                    if( ! isset($detalles[$codigo]) )
                    {
                        $detalles[$codigo] = [
                            'codigo' => $codigo, 
                            'cant' => $item['CantPiezas'], 
                            'folio' => $_post['Fol_folio']
                        ];
                    }
                    else {
                        $detalles[$codigo]['cant'] += (int) $item['CantPiezas'];
                    }
                }

                $sql_td;
                foreach ($detalles as $item)
                {
                    $sql = "SELECT COUNT(cve_articulo) e FROM c_articulo WHERE cve_articulo = '".$item['codigo']."'";
                    $res = mysqli_query(\db2(), $sql);
                    $row = mysqli_fetch_array($res);
                    if( is_array($row) and (int) $row[0] > 0)
                    {
                        $folio = $item['folio'];
                        //$num_menos = $folio[strlen($folio)-1];
                        //$num_menos--;
                        //$folio[strlen($folio)-1] = $num_menos;

                        $sql_td = "INSERT INTO td_pedido (Cve_articulo, Num_cantidad, Fol_folio, Activo, status, itemPos) Values ";
                        $sql_td .= "('".$item['codigo']."', '".$item['cant']."', '".$folio."', '1', 'A', '{$i}');";
                        $rs = mysqli_query(\db2(), $sql_td) or die("Error description: " . mysqli_error(\db2()));

                    }
                }
            }

            // Por la API
            if (!empty($_post["destinatarios"])) {
                foreach ($_post["destinatarios"] as $key => $item) 
                {
                    $item = (array) $item;

                    

                    $sql = "SELECT * FROM c_cliente WHERE Cve_Clte='".trim($item['Cve_Clte'])."'";
                    $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

                    if (mysqli_num_rows($rs)==0) {
                        $sql = "INSERT INTO `c_cliente`(`Cve_Clte`, Cve_CteProv, `RazonSocial`, `CalleNumero`, `Colonia`, `CodigoPostal`, `Ciudad`, `Estado`, `Contacto`, `Telefono1`, Cve_Almacenp, `Activo`) VALUES ";
                        $sql .= "('".$item['Cve_Clte']."', ";
                        $sql .= "'".$item['Cve_Clte']."', ";
                        $sql .= "'".$item['razonsocial']."', ";
                        $sql .= "'".$item['direccion']."', ";
                        //$sql .= "'".$item['Pais']."', ";
                        $sql .= "'".$item['colonia']."', ";
                        $sql .= "'".$item['postal']."', ";
                        $sql .= "'".$item['ciudad']."', ";
                        $sql .= "'".$item['estado']."', ";
                        $sql .= "'".$item['contacto']."', ";
                        $sql .= "'".$item['telefono']."', ";
                        $sql .= " ( SELECT id FROM c_almacenp WHERE clave = '".$_post['cve_almac']."') , ";
                        $sql .= "'1'";
                        $sql .= ");";
                        mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                    }

                    $sql = "INSERT INTO `c_destinatarios`(
                        Cve_Clte, razonsocial, colonia, direccion, postal, ciudad, estado, contacto, telefono, Activo) VALUES ";
                    $sql .= "('".$item['Cve_Clte']."', ";
                    $sql .= "'".$item['razonsocial']."', ";
                    $sql .= "'".$item['colonia']."', ";
                    $sql .= "'".$item['direccion']."', ";
                    $sql .= "'".$item['postal']."', ";
                    $sql .= "'".$item['ciudad']."', ";
                    $sql .= "'".$item['estado']."', ";
                    $sql .= "'".$item['contacto']."', ";
                    $sql .= "'".$item['telefono']."', ";
                    $sql .= "'".$item['Activo']."'";
                    $sql .= ");";   
                    $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

                    $Id_Destinatario = mysqli_insert_id(\db2());

                    $sql = "INSERT INTO `Rel_PedidoDest`(`Fol_Folio`, `Cve_Almac`, `Id_Destinatario`) VALUES ";
                    $sql .= "('".$_post['Fol_folio']."', '".$_post['cve_almac']."', '".$Id_Destinatario."');";
                    $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                }

                return true;
            }

             // Por la WEB
            if (!empty($_post["destinatario"])) {
               
                $destinatario = $_post["destinatario"];

                $sql = "INSERT INTO `Rel_PedidoDest`(
                            `Fol_Folio`, `Cve_Almac`, `Id_Destinatario`
                         ) VALUES (
                             '".$_post['Fol_folio']."', 
                             '".$_post['cve_almac']."', 
                             '".$destinatario."'
                        );";
                $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                
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

    function actualizarStatus( $_post ) {
        try {
            $sql = "UPDATE " . self::TABLE . " SET status='".$_post['status']."' WHERE ID_Pedido='".$_post['ID_Pedido']."'";
            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

        } catch(Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }
    
    function actualizarPedido( $_post ) {
        try {
            $sql = "Delete From " . self::TABLE . " WHERE Fol_folio = '".$_post['Fol_folio']."'";
            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

            $sql = "INSERT IGNORE INTO " . self::TABLE . " (Fol_folio, Cve_clte, cve_Vendedor, Fec_Entrada, Pick_Num, Observaciones, cve_almac, Activo)";
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
}