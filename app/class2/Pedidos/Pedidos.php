<?php

namespace Pedidos;

class Pedidos {

    const TABLE = 'th_pedido';
    const TABLE_D = 'td_pedido';
    const TABLE_C = 'c_cliente';
    // var $identifier;

    public function __construct( $Fol_folio = false, $key = false )
	{
        if( $Fol_folio )
		{
            $this->Fol_folio = (int) $Fol_folio;
        }
        if($key)
		{
            $sql = sprintf(' SELECT Fol_folio FROM %s WHERE Fol_folio = ?',self::TABLE);
            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Pedidos\Pedidos');
            $sth->execute(array($key));
            $Fol_folio = $sth->fetch();
            $this->Fol_folio = $Fol_folio->Fol_folio;
        }
    }

    private function load() {
        $sql = sprintf('SELECT * FROM %s WHERE Fol_folio = ?',self::TABLE);
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Pedidos\Pedidos' );
        $sth->execute( array( $this->Fol_folio ) );
        $this->data = $sth->fetch();
    }

    private function loadStatus() {
        $sql = sprintf("SELECT '".$this->cve_almac."' Cve_Almac,Fol_folio,Observaciones FROM %s WHERE Status In ('C','E','F','T') And Fol_folio = ?",self::TABLE);
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Pedidos\Pedidos' );
        $sth->execute( array( $this->Fol_folio ) );
        $this->data = $sth->fetch();
    }

    private function loadStatusMov() {
		if($this->TipoMov==1)
		{
			$sql = "Select 'true' success,IfNull(A.Factura,A.Pedimento) Referencia,'E' TipoMov From t_cardex X Join th_entalmacen E On X.Origen=E.Fol_Folio And X.Id_TipoMovimiento=1 Join th_aduana A On E.Id_OCompra=A.Num_Pedimento Where A.Factura='".$this->Referencia."' And A.Cve_Almac='".$this->cve_almac."' Limit 1;";
		}
		else
		{
			$sql = "Select 'true' success,E.Fol_Folio Referencia,'S' TipoMov From t_cardex X Join th_pedido E On X.Destino=E.Fol_Folio And X.Id_TipoMovimiento=8 Join c_almacenp A On A.Id=E.Cve_Almac Where E.Fol_Folio='".$this->Referencia."' And A.Clave='".$this->cve_almac."' Limit 1;";
		}
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Pedidos\Pedidos' );
        $sth->execute( array( $this->Referencia ) );
        $this->data = $sth->fetch();
    }

    private function loadChangeStatus()
	{
        $sql = sprintf('SELECT status FROM %s WHERE ID_Pedido = ?',self::TABLE);
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Pedidos\Pedidos' );
        $sth->execute( array( $this->ID_Pedido ) );
        $this->data = $sth->fetch();
    }

    function getStatus()
	{
        $sql = 'SELECT * FROM cat_estados';
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Pedidos\Pedidos' );
        $sth->execute( array( ESTADO ) );
        return $sth->fetchAll();
    }

    function getAll()
	{
        $sql = 'SELECT * FROM ' . self::TABLE . ';';
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Pedidos\Pedidos' );
        $sth->execute( array( Fol_folio ) );
        return $sth->fetchAll();
    }
	
    //Pedidos disponibles para embarque
    function getAllForShipment($page = 0, $limit = 0)
	{
        $sql = "SELECT th_pedido.Fol_folio AS folio, IFNULL(c_cliente.RazonSocial, '') as cliente, IFNULL(td_pedido.SurtidoXCajas, '0') as cajas, IFNULL(td_pedido.SurtidoXPiezas, '0') as piezas FROM th_pedido LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte LEFT JOIN td_pedido ON td_pedido.Fol_folio = th_pedido.Fol_folio WHERE th_pedido.status = 'C'";
        if($limit > 0 || $page > 0){
            $sql .= " LIMIT $page, $limit";
        }
        $sth = \db()->prepare( $sql );
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_CLASS);
    }

    private function loadDetalle()
	{
        $sql = "SELECT td_pedido.Fol_folio,td_pedido.Cve_articulo,td_pedido.Num_cantidad,c_articulo.des_articulo " .
				"FROM td_pedido INNER JOIN c_articulo ON td_pedido.Cve_articulo = c_articulo.cve_articulo " .
				"WHERE td_pedido.Fol_folio = '".$this->Fol_folio."'";
        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
        $arr = array();
        while ($row = mysqli_fetch_array($rs))
		{
            $arr[] = array( "Fol_folio" => $row["Fol_folio"],
							"Cve_articulo" => $row["Cve_articulo"],
							"Num_cantidad" => $row["Num_cantidad"],
							"des_articulo" => $row["des_articulo"]);
        }
        $this->dataDetalle = $arr;
    }

    private function loadDetalleMov()
	{
		if($this->TipoMov==1)
		{
			$sql = "Select IfNull(A.Factura,A.Pedimento) Referencia,X.Cve_articulo,X.Cve_Lote Lote,SUM(X.Cantidad) Num_Cantidad From t_cardex X Join th_entalmacen E On X.Origen=E.Fol_Folio And X.Id_TipoMovimiento=1 Join th_aduana A On E.Id_OCompra=A.Num_Pedimento Where A.Factura='".$this->Referencia."' And A.Cve_Almac='".$this->cve_almac."' Group By A.Factura,A.Pedimento,A.Cve_Almac,X.Cve_Articulo,X.Cve_Lote;";
		}
		else
		{
			$sql = "Select E.Fol_Folio as Referencia,X.Cve_articulo,X.Cve_Lote Lote,SUM(X.Cantidad) Num_Cantidad From t_cardex X Join th_pedido E On X.Destino=E.Fol_Folio And X.Id_TipoMovimiento=8 Join c_almacenp A On A.Id=E.Cve_Almac Where E.Fol_Folio='".$this->Referencia."' And A.Clave='".$this->cve_almac."' Group By E.Fol_Folio,A.Clave,X.Cve_Articulo,X.Cve_Lote;";
		}
        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
        $arr = array();
        while ($row = mysqli_fetch_array($rs))
		{
            $arr[] = array( "Referencia" => $row["Referencia"],
							"Cve_articulo" => $row["Cve_articulo"],
							"Lote" => $row["Lote"],
							"Num_Cantidad" => $row["Num_Cantidad"]);
        }
        $this->dataDetalle = $arr;
    }

    function __get( $key ) {
        switch($key)
		{
            case 'Fol_folio':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }
    }

    function __getStatus( $key ) {
        switch($key)
		{
            case 'Fol_folio':
                $this->loadStatus();
                return @$this->data->$key;
            case 'Referencia':
                $this->loadStatusMov();
                return @$this->data->$key;
            default:
                return $this->key;
        }
    }

    function __getChangeStatus( $key )
	{
        switch($key)
		{
            case 'ID_Pedido':
                $this->loadChangeStatus();
                return @$this->data->$key;
            default:
                return $this->key;
        }
    }

    function __getDetalle( $key )
	{
        switch($key)
		{
            case 'Fol_folio':
                $this->loadDetalle();
                return @$this->dataDetalle->$key;
            default:
                return $this->key;
        }
    }

    function __getDetalleMov( $key )
	{
        switch($key)
		{
            case 'Referencia':
                $this->loadDetalleMov();
                return @$this->dataDetalle->$key;
            default:
                return $this->key;
        }
    }

    function save( $_post )
	{
		try
		{
            $Fol_folio=" ";
			if($_post['Fol_folio']) $Fol_folio = $_post['Fol_folio'];
            $Fec_Pedido="0000-00-00 00:00:00";
			if($_post['Fec_Pedido']) $Fec_Pedido = $_post['Fec_Pedido'];
            $Fec_Entrega="0000-00-00 00:00:00";
			if($_post["Fec_Entrega"]) $Fec_Entrega = $_post["Fec_Entrega"];
            $Fec_Entrada="0000-00-00 00:00:00";
			if($_post["Fec_Entrada"]) $Fec_Entrada = $_post["Fec_Entrada"];
            $Cve_clte=" ";
			if($_post['Cve_clte']) $Cve_clte = $_post['Cve_clte'];
			if(!isset($_post['Cve_cteProv']) || $_post['Cve_cteProv']=='' || is_null($_post['Cve_cteProv']))
			{
				$Cve_CteProv=$Cve_clte;
			}
			else
			{
				$Cve_CteProv==$_post['Cve_cteProv'];
			}
			if(!isset($_post['Bloqueado']) || is_null($_post['Bloqueado']))
			{
				$Bloqueado=0;
			}
			else
			{
				$Bloqueado==$_post['Bloqueado'];
			}
            $Almac_Ori=" ";
			if($_post['Almac_Ori']) $Almac_Ori = $_post['Almac_Ori'];
            $Docto_Ref=" ";
			if($_post['Docto_Ref']) $Docto_Ref = $_post['Docto_Ref'];
            $cve_Vendedor=" ";
			if($_post['cve_Vendedor']) $cve_Vendedor = $_post['cve_Vendedor'];
            $Pick_Num=" ";
			if($_post['Pick_Num']) $Pick_Num = $_post['Pick_Num'];
            $user=" ";
			if($_post['user']) $user = $_post['user'];
            $Observaciones=" ";
			if($_post['Observaciones']) $Observaciones = $_post['Observaciones'];
            $ID_Tipoprioridad=1;
			if($_post['ID_Tipoprioridad']) $ID_Tipoprioridad = $_post['ID_Tipoprioridad'];
            $cve_almac=" ";
			if($_post['cve_almac']) $cve_almac = $_post['cve_almac'];
            $Sku=" ";
			if($_post['Sku']) $Sku = $_post['Sku'];
            $sql = "INSERT IGNORE INTO " . self::TABLE . "(Fol_folio,Fec_Pedido,Fec_Entrega,Fec_Entrada,bloqueado,
            Cve_clte,status,TipoPedido,cve_Vendedor,Pick_Num,Cve_Usuario,Observaciones,ID_Tipoprioridad,cve_almac,Activo,Cve_CteProv,Almac_Ori,Docto_Ref) ";
            $sql .= "Values (";
            $sql .= "'".$Fol_folio."',";
            $sql .= "'".$Fec_Pedido."',";
            $sql .= "'".$Fec_Entrega."',";
            $sql .= "'".$Bloqueado."',";
            $sql .= "'".$Fec_Entrada."',";
            $sql .= "'".$Cve_clte."',";
            $sql .= "'A','P',";
            $sql .= "'".$cve_Vendedor."',";
            $sql .= "'".$Pick_Num."',";
            $sql .= "'".$user."',";
            $sql .= "'".$Observaciones."',";
            $sql .= "'".$ID_Tipoprioridad."',";
            $sql .= " ( SELECT id FROM c_almacenp WHERE clave = '".$cve_almac."') ,";
            $sql .= "1,";
            $sql .= "'".$Cve_CteProv."',";
            $sql .= "'".$Almac_Ori."',";
            $sql .= "'".$Docto_Ref."');";
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
					$Item_Num = "1";
					if($item['Item']) $Item_Num = $item['Item'];
                    if( ! isset($detalles[$codigo]) )
                    {
                        $detalles[$codigo] = [
                            'codigo' => $codigo, 
                            'cant' => $item['CantPiezas'], 
                            'folio' => $_post['Fol_folio'], 
                            'cve_lote' => $item['cve_lote'],
							'Item' => $Item_Num
                        ];
                    }
                    else {
                        $detalles[$codigo]['cant'] += (int) $item['CantPiezas'];
                    }
                }
                foreach ($detalles as $item)
                {
                    $sql = "SELECT COUNT(cve_articulo) e FROM c_articulo WHERE cve_articulo = '".$item['codigo']."'";
                    $res = mysqli_query(\db2(), $sql);
                    $row = mysqli_fetch_array($res);
                    if( is_array($row) and (int) $row[0] > 0)
                    {
                        $folio = $item['folio'];
                        $sql = "Call SPWS_InsertaDetallePedido ";
                        $sql .= "('".$folio."','".$item['codigo']."',".$item['cant'].",".$item['Item'].",'".$item['cve_lote']."');";
                        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
						$i++;
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
                        $sql = "INSERT INTO `c_cliente`(`Cve_Clte`,Cve_CteProv,`RazonSocial`,`CalleNumero`,`Colonia`,`CodigoPostal`,`Ciudad`,`Estado`,`Contacto`,`Telefono1`,Cve_Almacenp,`Activo`) VALUES ";
                        $sql .= "('".$item['Cve_Clte']."', ";
                        $sql .= "'".$item['Cve_Clte']."', ";
                        $sql .= "'".$item['razonsocial']."', ";
                        $sql .= "'".$item['direccion']."', ";
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
					$Id_Destinatario = "";
                    $sql = "SELECT * FROM c_destinatarios WHERE Cve_Clte='".trim($item['Cve_Clte'])."' And ((razonsocial='".trim($item['razonsocial'])."'".
							" And direccion='".trim($item['direccion'])."') Or clave_destinatario='".trim($item['clave_destinatario'])."')";
                    $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                    if (mysqli_num_rows($rs)==0) {
						$sql = "INSERT INTO `c_destinatarios`(
							Cve_Clte, razonsocial, colonia, direccion, postal, ciudad, estado, contacto, telefono, Activo, clave_destinatario) VALUES ";
						$sql .= "('".$item['Cve_Clte']."', ";
						$sql .= "'".$item['razonsocial']."', ";
						$sql .= "'".$item['colonia']."', ";
						$sql .= "'".$item['direccion']."', ";
						$sql .= "'".$item['postal']."', ";
						$sql .= "'".$item['ciudad']."', ";
						$sql .= "'".$item['estado']."', ";
						$sql .= "'".$item['contacto']."', ";
						$sql .= "'".$item['telefono']."', ";
						$sql .= "'".$item['Activo']."', ";
						$sql .= "'".$item['clave_destinatario']."'";
						$sql .= ");";
						$rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
						$Id_Destinatario = mysqli_insert_id(\db2());
                    }
					else {
						while ($row = mysqli_fetch_array($rs))
						{
							$Id_Destinatario = $row["id_destinatario"];
						}
					}
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