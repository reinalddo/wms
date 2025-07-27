<?php

  namespace Ruta;

  class Ruta {
    const TABLE = 't_ruta';
    const TABLE_AGENTES     = 'Rel_Ruta_Agentes';
    const TABLE_TRANSPORTES = 'Rel_Ruta_Transporte';
    var $identifier;

    public function __construct( $ID_Ruta = false, $key = false ) {

      if( $ID_Ruta ) {
        $this->ID_Ruta = (int) $ID_Ruta;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            ID_Ruta
          FROM
            %s
          WHERE
            ID_Ruta = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Ruta\Ruta');
        $sth->execute(array($key));

        $ruta = $sth->fetch();

        $this->ID_Ruta = $ruta->ID_Ruta;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          ID_Ruta = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
      $sth->execute( array( $this->ID_Ruta ) );

      $this->data = $sth->fetch();

    }

    function getAll($almacen = "", $venta = "") {

        $sqlAlmacen = "";
        if($almacen)
          $sqlAlmacen = " AND cve_almacenp = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') ";

        $sqlVenta = "";
        if($venta)
          $sqlVenta = " AND (ID_Ruta IN (SELECT RutaId FROM Venta WHERE IdEmpresa = '{$almacen}') OR ID_Ruta IN (SELECT Ruta FROM V_Cabecera_Pedido WHERE Ruta IS NOT NULL AND IdEmpresa = '{$almacen}')) ";

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . ' where Activo = "1" '.$sqlAlmacen.$sqlVenta.'
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
        @$sth->execute( array( ID_Ruta ) );

        return $sth->fetchAll();

    }
    
    function getAllInventario($almacen = "") 
    {
        //$sql = "SELECT DISTINCT * FROM t_ruta WHERE ID_Ruta IN (SELECT ruta FROM th_pedido WHERE IFNULL(ruta, '') != '' AND cve_almac = '{$almacen}' UNION SELECT RutaId FROM Venta WHERE IdEmpresa = (SELECT clave FROM c_almacenp WHERE id = '{$almacen}'))";

        $sql = "SELECT DISTINCT * FROM t_ruta 
                WHERE ID_Ruta IN (

                SELECT t_ruta.ID_Ruta AS ID_Ruta 
                FROM th_pedido th
                LEFT JOIN t_ruta ON t_ruta.cve_ruta = IFNULL(th.cve_ubicacion, '') OR IFNULL(th.ruta, '') = t_ruta.ID_Ruta
                WHERE 
                th.cve_almac = '{$almacen}' 

                UNION 

                SELECT DISTINCT r.ID_Ruta 
                FROM th_pedido p 
                LEFT JOIN c_cliente c ON c.Cve_Clte = p.Cve_clte
                LEFT JOIN c_destinatarios d ON d.Cve_Clte = c.Cve_Clte
                LEFT JOIN t_clientexruta cr ON cr.clave_cliente = d.id_destinatario
                LEFT JOIN t_ruta r ON r.ID_Ruta = IFNULL(cr.clave_ruta, p.ruta) 
                WHERE r.ID_Ruta IS NOT NULL AND p.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')

                UNION

                SELECT DISTINCT r.ID_Ruta 
                FROM th_pedido p 
                LEFT JOIN t_ruta r ON r.ID_Ruta IN (SELECT id_ruta_entrega FROM rel_RutasEntregas)
                LEFT JOIN rel_RutasEntregas re ON re.id_ruta_entrega = r.ID_Ruta 
                WHERE r.ID_Ruta IS NOT NULL AND p.ruta = re.id_ruta_venta_preventa

                UNION 

                SELECT RutaId AS ID_Ruta FROM Venta WHERE IdEmpresa = (SELECT clave FROM c_almacenp WHERE id = '{$almacen}')

                )";

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
        $sth->execute();

        return $sth->fetchAll();
    }

	function getLastInsertId(){
          $sql = '
        SELECT MAX(ID_Ruta) as ID        
        FROM
          ' . self::TABLE . '
      ';

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
          @$sth->execute(array( ID_Ruta ) );
          return $sth->fetch();
      }
	
    function __get( $key ) {

      switch($key) {
        case 'ID_Ruta':
        case 'cve_ruta':
        case 'descripcion':
        case 'status':
        case 'cve_almacenp':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

	
	function asignarRutaClientes ( $cliente, $ruta, $cve_ruta){
		
		
		$sql = sprintf('
        INSERT INTO
          t_clientexruta
        SET
			clave_cliente = '.$cliente.',
			clave_ruta = '.$ruta.'			
		');
		
		$this->save = \db()->prepare($sql);
        $this->save->bindValue( ':clave_cliente', $cliente, \PDO::PARAM_STR );
		$this->save->bindValue( ':clave_ruta', $ruta, \PDO::PARAM_STR );
        
        $this->save->execute();	

    $sql = "UPDATE c_cliente
    SET
      cve_ruta = '".$cve_ruta."'
    WHERE Cve_Clte = ".$cliente."";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));

	}


function getRealIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];
       
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
   
    return $_SERVER['REMOTE_ADDR'];
}

      function save( $data ) {
        //if( !$_post['cve_ruta'] ) { throw new \ErrorException( 'Full name is required.' ); }
		
        $sql = sprintf("
        INSERT INTO
          " . self::TABLE . "
        SET
			cve_ruta = :cve_ruta,
			cve_almacenp = :cve_almacenp,
			descripcion = :descripcion,
      venta_preventa = :venta_preventa,
      control_pallets_cont = :envases_ruta,
      ID_Proveedor = :id_proveedor,
			status = :status
		");

        $this->save = \db()->prepare($sql);
        
		$this->save->bindValue( ':cve_ruta', $data['cve_ruta'], \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_almacenp', $data['cve_almacenp'], \PDO::PARAM_STR );
    $this->save->bindValue( ':descripcion', $data['descripcion'], \PDO::PARAM_STR );
    $this->save->bindValue( ':venta_preventa', $data['venta_preventa'], \PDO::PARAM_STR );
    $this->save->bindValue( ':envases_ruta', $data['envases_ruta'], \PDO::PARAM_STR );
    $this->save->bindValue( ':id_proveedor', $data['id_proveedor'], \PDO::PARAM_STR );
		$this->save->bindValue( ':status', $data['status'], \PDO::PARAM_STR );
        
        $this->save->execute();

        if($data["venta_preventa"] == 2)
        {
          //$sql = sprintf("DELETE FROM rel_RutasEntregas WHERE id_ruta_entrega = (SELECT MAX(ID_Ruta) FROM t_ruta)");
          //$this->save = \db()->prepare($sql);
          //$this->save->execute();

          $rel_rutas = $data["rels_rutas"][0];
          foreach ($rel_rutas as $ruta_entrega) 
          {
            $sql = sprintf("INSERT INTO rel_RutasEntregas(id_ruta_entrega, id_ruta_venta_preventa) VALUES ((SELECT MAX(ID_Ruta) FROM t_ruta), $ruta_entrega)");
            $this->save = \db()->prepare($sql);
            $this->save->execute();
          }
        }



        $IP = $this->getRealIP();
        $sql = sprintf("
        INSERT INTO
          ConfigRutasP
        SET
          RutaId = (SELECT MAX(ID_Ruta) FROM t_ruta)
          ,VelCom = :VelCom
          ,Server = :Server
          ,Puerto = :Puerto
          ,CteNvo = :CteNvo
          ,CveCteNvo = :CveCteNvo
          ,IdEmpresa = :IdEmpresa
          ,SugerirCant = :SugerirCant
          ,PromoEq = :PromoEq
    ");

        $this->save = \db()->prepare($sql);

    $this->save->bindValue( ':VelCom', '19200', \PDO::PARAM_STR );
    $this->save->bindValue( ':Server', $IP, \PDO::PARAM_STR );
    $this->save->bindValue( ':Puerto', '21', \PDO::PARAM_STR );
    $this->save->bindValue( ':CteNvo', '0', \PDO::PARAM_STR );
    $this->save->bindValue( ':CveCteNvo', '0', \PDO::PARAM_STR );
    $this->save->bindValue( ':IdEmpresa', $data['cve_almac'], \PDO::PARAM_STR );
    $this->save->bindValue( ':SugerirCant', '0', \PDO::PARAM_STR );
    $this->save->bindValue( ':PromoEq', '0', \PDO::PARAM_STR );

        $this->save->execute();

      $id_almacen = $data['cve_almac'];
        $sql = sprintf("
        INSERT INTO
          Continuidad
        SET
          RutaID = (SELECT MAX(ID_Ruta) FROM t_ruta)
          ,DiaO = 1 
          ,FolVta = 10000000
          ,FolPed = 10000000
          ,FolDevol = 10000000
          ,FolCob = 10000000
          ,UDiaO = 1 
          ,CteNvo = 1 
          ,IdEmpresa = '{$id_almacen}'
    ");

        $this->save = \db()->prepare($sql);

/*
    $this->save->bindValue( ':cve_ruta', $data['cve_ruta'], \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_almacenp', $data['cve_almacenp'], \PDO::PARAM_STR );
    $this->save->bindValue( ':descripcion', $data['descripcion'], \PDO::PARAM_STR );
    $this->save->bindValue( ':venta_preventa', $data['venta_preventa'], \PDO::PARAM_STR );
    $this->save->bindValue( ':status', $data['status'], \PDO::PARAM_STR );
*/
        $this->save->execute();

        $sql = sprintf("
        INSERT IGNORE INTO Stock (Articulo, Stock, Ruta, IdEmpresa) (SELECT cve_articulo, 0, (SELECT MAX(ID_Ruta) FROM t_ruta), '{$id_almacen}' FROM c_articulo WHERE tipo_producto = 'Producto de Consumo' AND cve_articulo NOT IN (SELECT Articulo FROM Stock WHERE IdEmpresa = '{$id_almacen}' AND Ruta = (SELECT MAX(ID_Ruta) FROM t_ruta)))
        ");

        $this->save = \db()->prepare($sql);

        $this->save->execute();

      }


	function actualizarRuta( $data ) {
		$sql = "UPDATE " . self::TABLE . " 
		SET
			cve_almacenp = '".$data['cve_almacenp']."', 
			cve_ruta = '".$data['cve_ruta']."',
      descripcion = '".$data['descripcion']."',
      Activo = '".$data['Activo']."',
      venta_preventa = '".$data['venta_preventa']."',
      control_pallets_cont = '".$data['envases_ruta']."',
			status = '".$data['status']."'			       
		WHERE ID_Ruta = '".$data['ID_Ruta']."'";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));


  }


  function existeTransporteRuta($ruta, $id_transporte)
  {
      $sql = "SELECT COUNT(*) AS existe FROM Rel_Ruta_Transporte WHERE cve_ruta = '$ruta' AND id_transporte = '{$id_transporte}'";

      //$sth = \db()->prepare( $sql );
      //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
      //@$sth->execute(array( ID_Ruta ) );
      //$ruta = $sth->fetch();

      $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
      $ruta = mysqli_fetch_array($rs);
      //$existe = $ruta->existe;
      $existe = $ruta['existe'];

      return $existe;
  }

  function AsignarTransporteRuta( $ruta, $transporte ) {
/*
    if($this->existeTransporteRuta($ruta, $transporte))
    {
      $sql = "UPDATE Rel_Ruta_Transporte 
      SET
        id_transporte = '".$transporte."'
      WHERE cve_ruta = '".$ruta."'";
          $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
    }
    else
    {
        $sql = "INSERT Rel_Ruta_Transporte 
        SET
          cve_ruta = '".$ruta."',
          id_transporte = '".$transporte."'";
         $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
    }
*/
        $sql = "DELETE FROM Rel_Ruta_Transporte WHERE cve_ruta = '".$ruta."' OR id_transporte = '".$transporte."'";
         $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));

        $sql = "INSERT Rel_Ruta_Transporte 
        SET
          cve_ruta = '".$ruta."',
          id_transporte = '".$transporte."'";
         $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));

  }

  function EliminarTransporteRuta( $ruta, $transporte ) 
  {
    $noexiste = true;
    if($this->existeTransporteRuta($ruta, $transporte))
    {
        $sql = "DELETE FROM Rel_Ruta_Transporte WHERE cve_ruta = '".$ruta."' AND id_transporte = '".$transporte."'";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
    }
    else
    {
        $noexiste = false;
    }
    return $noexiste;
  }


  function existeAgenteRuta($ruta, $agente)
  {
      $sql = "SELECT COUNT(*) AS existe FROM Rel_Ruta_Agentes WHERE cve_ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '".$ruta."' LIMIT 1) AND cve_vendedor = (SELECT Id_Vendedor FROM t_vendedores WHERE Cve_Vendedor = '".$agente."' LIMIT 1)";

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
      @$sth->execute(array( ID_Ruta ) );
      $ruta = $sth->fetch();

      //$rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
      //$ruta = mysqli_fetch($rs);
      $existe = $ruta->existe;

      return $existe;
  }

  function EliminarClienteRuta( $ruta, $cliente ) 
  {
    $noexiste = true;
    //if($this->existeAgenteRuta($ruta, $agente)), 
    {
        $sql = "DELETE FROM t_clientexruta WHERE clave_ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '".$ruta."' LIMIT 1) AND clave_cliente IN (SELECT id_destinatario FROM c_destinatarios WHERE Cve_Clte = (SELECT Cve_Clte FROM c_cliente WHERE id_cliente = '".$cliente."'))";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));

        $sql = "DELETE FROM RelDayCli WHERE Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '".$ruta."' LIMIT 1) AND Id_Destinatario IN (SELECT id_destinatario FROM c_destinatarios WHERE Cve_Clte = (SELECT Cve_Clte FROM c_cliente WHERE id_cliente = '".$cliente."'))";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));

        $sql = "DELETE FROM RelClirutas WHERE IdRuta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '".$ruta."' LIMIT 1) AND IdCliente IN (SELECT id_destinatario FROM c_destinatarios WHERE Cve_Clte = (SELECT Cve_Clte FROM c_cliente WHERE id_cliente = '".$cliente."'))";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));

/*
        $sql = "DELETE FROM t_clientexruta WHERE clave_ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '".$ruta."' LIMIT 1) AND clave_cliente = '".$cliente."'";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
*/
    }
    /*
    else
    {
        $noexiste = false;
    }
    */
    return $noexiste;
  }

  function EliminarChoferRuta( $ruta, $agente ) 
  {
    $noexiste = true;
    if($this->existeAgenteRuta($ruta, $agente))
    {
        $sql = "DELETE FROM Rel_Ruta_Agentes WHERE cve_ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '".$ruta."' LIMIT 1) AND cve_vendedor = (SELECT Id_Vendedor FROM t_vendedores WHERE Cve_Vendedor = '".$agente."' LIMIT 1)";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));

        $sql = "DELETE FROM RelDayCli WHERE Cve_Vendedor = '".$agente."'";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
    }
    else
    {
        $noexiste = false;
    }
    return $noexiste;
  }

  function AsignarChoferRuta( $ruta, $agente ) 
  {
    $existe = true;

    /*
    if(!$this->existeAgenteRuta($ruta, $agente))
    {
        $sql = "INSERT Rel_Ruta_Agentes 
        SET
          cve_ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '".$ruta."' LIMIT 1),
          cve_vendedor = (SELECT Id_Vendedor FROM t_vendedores WHERE Cve_Vendedor = '".$agente."' LIMIT 1)";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
    }
    else
      */
    //if($this->existeAgenteRuta($ruta, $agente))
    //{
        //$sql = "UPDATE Rel_Ruta_Agentes SET cve_vendedor = '' WHERE cve_vendedor = (SELECT Id_Vendedor FROM t_vendedores WHERE Cve_Vendedor = '".$agente."' LIMIT 1)";
        //$rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));

        $sql = "UPDATE RelDayCli SET Cve_Vendedor = '' WHERE Cve_Vendedor = (SELECT Id_Vendedor FROM t_vendedores WHERE Cve_Vendedor = '".$agente."' LIMIT 1)";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));

        //$sql = "UPDATE Rel_Ruta_Agentes SET cve_vendedor = (SELECT Id_Vendedor FROM t_vendedores WHERE Cve_Vendedor = '".$agente."' LIMIT 1) WHERE cve_ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '".$ruta."' LIMIT 1)";
        //$rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));

        $sql = "UPDATE RelDayCli SET Cve_Vendedor = (SELECT Id_Vendedor FROM t_vendedores WHERE Cve_Vendedor = '".$agente."' LIMIT 1) WHERE Cve_Ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '".$ruta."' LIMIT 1)";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
    //}

    $sql = "DELETE FROM Rel_Ruta_Agentes WHERE cve_ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '".$ruta."' LIMIT 1) OR cve_vendedor = (SELECT Id_Vendedor FROM t_vendedores WHERE Cve_Vendedor = '".$agente."' LIMIT 1)";
    $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));

    $sql = "INSERT Rel_Ruta_Agentes 
    SET
      cve_ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '".$ruta."' LIMIT 1),
      cve_vendedor = (SELECT Id_Vendedor FROM t_vendedores WHERE Cve_Vendedor = '".$agente."' LIMIT 1)";
    $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));

    return $existe;
  }


  function actualizarDatosRuta($idRuta, $descripcion, $cve_almacenp ,$activo, $status, $venta_preventa, $envases_ruta, $clave_almacen, $id_proveedor)
  {
    $sql = "UPDATE " . self::TABLE . " 
    SET
      cve_almacenp = ".$cve_almacenp.", 
      descripcion = '".$descripcion."',
      Activo = ".$activo.",
      venta_preventa = ".$venta_preventa.",
      control_pallets_cont = '".$envases_ruta."',
      ID_Proveedor = ".$id_proveedor.",
      status = '".$status."'
    WHERE ID_Ruta = ".$idRuta."";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));


      $sql = "SELECT COUNT(*) AS existe FROM ConfigRutasP WHERE RutaId = '$idRuta'";
      $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
      $ruta = mysqli_fetch_array($rs);
      $existe = $ruta['existe'];
      if(!$existe)
      {
          $IP = $this->getRealIP();
          $sql = sprintf("
          INSERT IGNORE INTO
            ConfigRutasP
          SET
            RutaId = :IDRuta
            ,VelCom = :VelCom
            ,Server = :Server
            ,Puerto = :Puerto
            ,CteNvo = :CteNvo
            ,CveCteNvo = :CveCteNvo
            ,IdEmpresa = :IdEmpresa
            ,SugerirCant = :SugerirCant
            ,PromoEq = :PromoEq
      ");

        $this->save = \db()->prepare($sql);

        $this->save->bindValue( ':IDRuta', $idRuta, \PDO::PARAM_STR );
        $this->save->bindValue( ':VelCom', '19200', \PDO::PARAM_STR );
        $this->save->bindValue( ':Server', $IP, \PDO::PARAM_STR );
        $this->save->bindValue( ':Puerto', '21', \PDO::PARAM_STR );
        $this->save->bindValue( ':CteNvo', '0', \PDO::PARAM_STR );
        $this->save->bindValue( ':CveCteNvo', '0', \PDO::PARAM_STR );
        $this->save->bindValue( ':IdEmpresa', $clave_almacen, \PDO::PARAM_STR );
        $this->save->bindValue( ':SugerirCant', '0', \PDO::PARAM_STR );
        $this->save->bindValue( ':PromoEq', '0', \PDO::PARAM_STR );

        $this->save->execute();
      }
        $sql = sprintf("
        INSERT IGNORE INTO
          Continuidad
        SET
          RutaID = '{$idRuta}'
          ,DiaO = 1 
          ,FolVta = 10000000
          ,FolPed = 10000000
          ,FolDevol = 10000000
          ,FolCob = 10000000
          ,UDiaO = 1 
          ,CteNvo = 1 
          ,IdEmpresa = '{$clave_almacen}'
    ");

        $this->save = \db()->prepare($sql);
/*
    $this->save->bindValue( ':cve_ruta', $data['cve_ruta'], \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_almacenp', $data['cve_almacenp'], \PDO::PARAM_STR );
    $this->save->bindValue( ':descripcion', $data['descripcion'], \PDO::PARAM_STR );
    $this->save->bindValue( ':venta_preventa', $data['venta_preventa'], \PDO::PARAM_STR );
    $this->save->bindValue( ':status', $data['status'], \PDO::PARAM_STR );
*/
        $this->save->execute();

        $sql = sprintf("
        INSERT IGNORE INTO Stock (Articulo, Stock, Ruta, IdEmpresa) (SELECT cve_articulo, 0, '{$idRuta}', '{$clave_almacen}' FROM c_articulo WHERE tipo_producto = 'Producto de Consumo' AND cve_articulo NOT IN (SELECT Articulo FROM Stock WHERE IdEmpresa = '{$clave_almacen}' AND Ruta = '{$idRuta}'))
        ");

        $this->save = \db()->prepare($sql);

        $this->save->execute();

  }

      function borrarRuta( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          ID_Ruta = ?
      ;';
	  
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['ID_Ruta']
          ) );
      }

	function recoveryRuta( $data ) {

          $sql = "UPDATE " . self::TABLE . "
		  SET Activo = 1 WHERE  ID_Ruta='".$data['ID_Ruta']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['ID_Ruta']
          ) );
    }

	private function loadClave() {
	

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          cve_ruta = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
      $sth->execute( array( $this->cve_ruta ) );

      $this->data = $sth->fetch();

    }
	
	private function loadTieneCliente() {
      $sql = sprintf('
        SELECT
          r.*
        FROM
          t_ruta r, t_clientexruta cr
        WHERE
          cr.clave_ruta = r.id_ruta 
		  and r.id_ruta = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
      $sth->execute( array( $this->ID_Ruta ) );

      $this->data = $sth->fetch();

    }
	
	function validaClave( $key ) {
      switch($key) {
        case 'ID_Ruta':
        case 'cve_ruta':
        case 'descripcion':
        case 'status':
        case 'cve_almacenp':
          $this->loadClave();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }
	
	function tieneCliente( $key ) {
      switch($key) {
        case 'ID_Ruta':
        case 'cve_ruta':
        case 'descripcion':
        case 'status':
        case 'cve_almacenp':
          $this->loadTieneCliente();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }
	
	function borrarClientes( $data ) {          
	    $sql= '
        DELETE FROM
		t_clientexruta
        WHERE
          clave_ruta = ?
      ;';
	        $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['ID_Ruta']
          ) );
	}
	
	function getRutas(){
		   $sql = "SELECT
            t_ruta.ID_Ruta,
			c_almacenp.nombre as almacenp,
            t_ruta.cve_ruta,
            t_ruta.descripcion,
            t_ruta.status,
            t_ruta.cve_almacenp,
            t_ruta.Activo
            FROM
            t_ruta
			left join c_almacenp on c_almacenp.id=t_ruta.cve_almacenp
			WHERE 
			 t_ruta.Activo = 1";
		 $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
        $sth->execute();

        return $sth->fetchAll();
		
		
	}
	
	function traerClientesxRuta($id_ruta) 
  {          
    $sql= "
      SELECT
        c.id_cliente as id, 
        c.Cve_Clte as clave, 
        c.RazonSocial as razon, 
        GROUP_CONCAT(r.descripcion SEPARATOR ', ') as rutas
      FROM t_ruta r, t_clientexruta cr, c_cliente c
      WHERE cr.clave_ruta = r.ID_Ruta 
        and cr.clave_cliente = c.id_cliente 
        and cr.clave_ruta='".$id_ruta."'
        and r.activo= 1
        and c.Activo= 1
      GROUP BY c.id_cliente;
    ";
    $sth = \db()->prepare( $sql );
    //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
    $sth->execute();
    return $sth->fetchAll();
	}
	
	 function OperadoresRuta($id_ruta, $asignados_si_no, $cve_almac) 
  {
    $sql = "";
/*
    if($asignados_si_no == 1)
        $sql= "
        SELECT DISTINCT 
          u.cve_usuario, u.nombre_completo
        FROM
          c_usuario u
        INNER JOIN t_ruta r ON r.cve_ruta = '{$id_ruta}'
        WHERE u.Activo=1 AND u.es_cliente = 3 AND 
        CONCAT(IFNULL(u.id_user, ''), IFNULL(r.ID_Ruta, '')) NOT IN (SELECT CONCAT(IFNULL(cve_vendedor, ''), IFNULL(cve_ruta, '')) FROM Rel_Ruta_Agentes)";
    else 
        $sql= "
          SELECT DISTINCT 
            u.cve_usuario, u.nombre_completo
          FROM
            c_usuario u
          INNER JOIN t_ruta r ON r.cve_ruta = '{$id_ruta}'
          INNER JOIN Rel_Ruta_Agentes ra ON ra.cve_ruta = r.ID_Ruta AND u.id_user = ra.cve_vendedor
          WHERE u.Activo=1 AND u.es_cliente = 3 
        ";
*/
    $sql= "
      SELECT DISTINCT 
      u.cve_usuario, u.nombre_completo
      FROM
      c_usuario u
      INNER JOIN trel_us_alm a ON a.cve_usuario = u.cve_usuario
      WHERE u.Activo=1 AND u.es_cliente = 3 AND a.cve_almac = '$cve_almac'
    ";

    $sth = \db()->prepare( $sql );
    //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
    $sth->execute();
    return $sth->fetchAll();
  }

         
  }
