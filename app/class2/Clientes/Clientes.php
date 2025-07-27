<?php

namespace Clientes;

class Clientes {

    const TABLE = 'c_cliente';
    var $identifier;

    public function __construct( $Cve_Clte = false, $key = false ) {

        if( $Cve_Clte ) {
            $this->Cve_Clte = (int) $Cve_Clte;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            Cve_Clte
          FROM
            %s
          WHERE
            Cve_Clte = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Clientes\Clientes');
            $sth->execute(array($key));

            $Cve_Clte = $sth->fetch();

            $this->Cve_Clte = $Cve_Clte->Cve_Clte;

        }

    }

    private function load() {
        $cve = $this->Cve_Clte;

        $sql = 
        /*
        "SELECT
        c.*, 
        d.departamento,
        d.des_municipio,
        COALESCE(de.id_destinatario, c.ID_Destinatario) AS id_destinatario
        FROM
        c_cliente c
        LEFT JOIN c_dane d ON d.cod_municipio = c.CodigoPostal 
        LEFT JOIN c_destinatarios de ON de.id_destinatario = c.ID_Destinatario
        WHERE c.Cve_Clte = '$cve';";
        */
        "
        SELECT 
          c.*, 
          d.departamento,
          d.des_municipio,
          de.dir_principal as dir_principal,
          de.id_destinatario as id_destinatario
        FROM c_cliente c 
          LEFT JOIN c_dane d ON d.cod_municipio = c.CodigoPostal 
          LEFT JOIN c_destinatarios de ON de.Cve_Clte = c.Cve_Clte 
        WHERE c.Cve_Clte = '$cve';
        ";
      
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
        $sth->execute();
      
        

        $this->data = $sth->fetch();
        $cliente = $this->data->Cve_Clte;
        $sql = "SELECT id_destinatario, CONCAT(id_destinatario, '-', direccion,', ', colonia, ', ', postal) AS texto, CONCAT(razonsocial, '|', direccion, '|', colonia, '|', postal, '|', ciudad, '|', estado, '|', contacto, '|', telefono) AS value, dir_principal FROM c_destinatarios WHERE Cve_Clte = '$cliente';";
        $sth = \db()->prepare($sql);
        $sth->execute();
        $destinatarios = $sth->fetchAll(\PDO::FETCH_ASSOC);
        $this->data->destinatarios = $destinatarios;
    }

    function __get( $key ) {

        switch($key) {
            case 'Cve_Clte':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function save($data) 
    {
      try 
      {
        $almacen = 0;

        if(isset($data['almacenp']))
        {
          $almacen = $data['almacenp'];
        }
        else
        {
          $sql = 
            "
            SELECT 
              almac.id 
            FROM t_usu_alm_pre prede, c_almacenp almac 
            WHERE prede.cve_almac = almac.clave 
              AND id_user = ".$_SESSION['id_user']
            ;
          $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
          if (!($res = mysqli_query($conn, $sql))) 
          {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
          $row = mysqli_fetch_array($res);
          $almacen = $row['id'];
        }

        //$cliente_tipo = "";
        //if($data['cliente_tipo_traslado'] == 1) $cliente_tipo = "TRASLADO";

        $sql = sprintf('
          INSERT INTO 
            ' . self::TABLE . '
          SET
            Cve_Clte = :Cve_Clte,
            Cve_CteProv =:Cve_CteProv,
            RazonSocial = :RazonSocial,
            RazonComercial = :RazonComercial,
            Encargado = :Encargado,
            Referencia = :Referencia,
            CalleNumero = :CalleNumero,
            Colonia = :Colonia,
            CodigoPostal = :CodigoPostal,
            Ciudad = :Ciudad,
            Estado = :Estado,
            Pais = :Pais,
            RFC = :RFC,
            Telefono1 = :Telefono1,
            Telefono2 = :Telefono2,
            email_cliente = :email_cliente,
            latitud = :txtLatitud,
            longitud = :txtLongitud,
            ID_Proveedor = :ID_Proveedor,
            Cve_Almacenp = :Cve_Almacenp,
            credito = :credito,
            limite_credito = :limite_credito,
            dias_credito = :dias_credito,
            credito_actual = :credito_actual,
            saldo_inicial = :saldo_inicial,
            saldo_actual = :saldo_actual,
            ClienteGrupo = :grupocliente,
            ClienteTipo = :tipocliente,
            ClienteTipo2 = :tipocliente2,
            validar_gps = :validar_gps
        ');
/*
            CondicionPago = :CondicionPago,
            ZonaVenta = :ZonaVenta,
            ClienteTipo = :ClienteTipo,
*/


        $this->save = \db()->prepare($sql);
        $this->save->bindValue( ':Cve_Clte', $data['Cve_Clte'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Cve_CteProv', $data['Cve_CteProv'], \PDO::PARAM_STR );
        $this->save->bindValue( ':RazonSocial', strtoupper($data['RazonSocial']), \PDO::PARAM_STR );
        $this->save->bindValue( ':RazonComercial', strtoupper($data['NombreCorto']), \PDO::PARAM_STR );
        $this->save->bindValue( ':Encargado', strtoupper($data['Encargado']), \PDO::PARAM_STR );
        $this->save->bindValue( ':Referencia', strtoupper($data['Referencia']), \PDO::PARAM_STR );

        $this->save->bindValue( ':credito', strtoupper($data['credito']), \PDO::PARAM_STR );
        $this->save->bindValue( ':limite_credito', strtoupper($data['limite_credito']), \PDO::PARAM_STR );
        $this->save->bindValue( ':dias_credito', strtoupper($data['dias_credito']), \PDO::PARAM_STR );
        $this->save->bindValue( ':credito_actual', strtoupper($data['credito_actual']), \PDO::PARAM_STR );
        $this->save->bindValue( ':saldo_inicial', strtoupper($data['saldo_inicial']), \PDO::PARAM_STR );
        $this->save->bindValue( ':saldo_actual', strtoupper($data['saldo_actual']), \PDO::PARAM_STR );
        $this->save->bindValue( ':validar_gps', strtoupper($data['validar_gps']), \PDO::PARAM_STR );

        $this->save->bindValue( ':CalleNumero', strtoupper($data['CalleNumero']), \PDO::PARAM_STR );
        $this->save->bindValue( ':Colonia', strtoupper($data['Colonia']), \PDO::PARAM_STR );
        $this->save->bindValue( ':CodigoPostal', $data['CodigoPostal'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Ciudad', $data['Ciudad'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Estado', $data['Estado'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Pais', $data['Pais'], \PDO::PARAM_STR );
        $this->save->bindValue( ':RFC', $data['RFC'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Telefono1', $data['Telefono1'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Telefono2', $data['Telefono2'], \PDO::PARAM_STR );
        $this->save->bindValue( ':email_cliente', $data['email_cliente'], \PDO::PARAM_STR );
        $this->save->bindValue( ':txtLatitud', $data['txtLatitud'], \PDO::PARAM_STR );
        $this->save->bindValue( ':txtLongitud', $data['txtLongitud'], \PDO::PARAM_STR );
        $this->save->bindValue( ':ID_Proveedor', $data['ID_Proveedor'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Cve_Almacenp', $almacen, \PDO::PARAM_STR );
        $this->save->bindValue( ':grupocliente', $data['grupocliente'], \PDO::PARAM_STR );
        $this->save->bindValue( ':tipocliente', $data['tipocliente'], \PDO::PARAM_STR );
        $this->save->bindValue( ':tipocliente2', $data['tipocliente2'], \PDO::PARAM_STR );
        //$this->save->bindValue( ':ClienteTipo', $cliente_tipo, \PDO::PARAM_STR );
        $this->save->execute();

/*
        $this->save->bindValue( ':CondicionPago', $data['CondicionPago'], \PDO::PARAM_STR );
        $this->save->bindValue( ':ZonaVenta', $data['ZonaVenta'], \PDO::PARAM_STR );
        $this->save->bindValue( ':ClienteTipo', $data['ClienteTipo'], \PDO::PARAM_STR );
*/

        $id_cliente = \db()->lastInsertId();

        if (isset($data["fromAPI"]))
        {
          return "Guardado";
        }

        $clave_cliente = $data['Cve_Clte'];
        $destinatarios = $data['destinatarios'];
        if(!empty($destinatarios))
        {
          foreach($destinatarios as $destinatario)
          {
            list($razon, $direccion, $colonia, $codigop, $ciudad, $estado, $contacto, $telefono, $principal, $emailDest, $latitudDest, $longitudDest) = explode("|", $destinatario);
            $sql = 
              "
              INSERT INTO c_destinatarios 
                (Cve_Clte, 
                razonsocial,
                direccion, 
                colonia,
                postal, 
                ciudad, 
                estado, 
                contacto, 
                telefono,
                dir_principal,
                email_destinatario,
                latitud,
                longitud) 
              VALUES 
                ('{$clave_cliente}',
                '{$razon}', 
                '{$direccion}',
                '{$colonia}',
                '{$codigop}', 
                '{$ciudad}', 
                '{$estado}', 
                '{$contacto}',
                '{$telefono}',
                '{$principal}',
                '{$emailDest}',
                '{$latitudDest}',
                '{$longitudDest}');
              ";
            mysqli_query(\db2(), $sql);
            if(intval($principal) === 1)
            {
              $sql = 
                "
                UPDATE c_cliente 
                SET 
                ID_Destinatario = (SELECT MAX(id_destinatario) FROM c_destinatarios) 
                WHERE id_cliente = {$id_cliente};
                ";
              mysqli_query(\db2(), $sql);
            }
          }
        }
        /*
        if(intval($data['usar_direccion']) === 1)
        {
          $sql = "UPDATE c_cliente SET ID_Destinatario = 0 WHERE id_cliente = {$id_cliente};";
          mysqli_query(\db2(), $sql);
        }
        */
      } 
      catch(PDOException $e) 
      {
        return 'ERROR: ' . $e->getMessage();
      }
    }
  
    
	
    function getAll($filtro = "") 
    {
        $sql = '
        SELECT DISTINCT
          *
        FROM
          ' . self::TABLE . '
      WHERE Activo = 1 '.$filtro.' 
      ORDER BY RazonSocial
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
        @$sth->execute( array( Cve_Clte ) );

        return $sth->fetchAll();

    } 
/*
    function getColonias() 
    {
        $sql = '
        SELECT DISTINCT
          Colonia
        FROM
          ' . self::TABLE . '
      WHERE Activo = 1
      ORDER BY Colonia
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
        @$sth->execute( array( Cve_Clte ) );

        return $sth->fetchAll();

    } 

    function getCPostales() 
    {
        $sql = '
        SELECT DISTINCT
          CodigoPostal
        FROM
          ' . self::TABLE . '
      WHERE Activo = 1
      ORDER BY CodigoPostal
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
        @$sth->execute( array( Cve_Clte ) );

        return $sth->fetchAll();

    } 
	*/
	function traerRutas($id_cliente) {
        $sql = '
        SELECT
          r.*,
					c_dane.des_municipio
        FROM
          t_clientexruta cr, t_ruta r, c_cliente, c_dane
		WHERE cr.clave_cliente = "'.$id_cliente.'"
		and cr.clave_ruta = r.ID_Ruta 
		and cr.clave_cliente= c_cliente.id_cliente
		and c_dane.cod_municipio=c_cliente.CodigoPostal
		and r.activo= 1;
      ';

        $sth = \db()->prepare( $sql );
        
        $sth->execute();

        return $sth->fetchAll();

    }	

  function traerClientesDeRutaClave ($cve_ruta){
    
          $sql = '
      SELECT DISTINCT
          c.*
        FROM
          c_cliente c 
          LEFT JOIN c_destinatarios d ON d.Cve_Clte = c.Cve_Clte
          LEFT JOIN t_clientexruta cr ON cr.clave_cliente = d.id_destinatario
          LEFT JOIN t_ruta r ON r.ID_Ruta = cr.clave_ruta
      WHERE r.cve_ruta = "'.$cve_ruta.'"
    AND c.activo= 1
    ';

        $sth = \db()->prepare( $sql );
        
        $sth->execute();

        return $sth->fetchAll();
  
  
  }

	function traerClientesDeRuta ($id_ruta){
		
		      $sql = '
        SELECT
          c.*
        FROM
          t_clientexruta cr, c_cliente c
		  WHERE cr.clave_ruta = '.$id_ruta.'
		and cr.clave_cliente = c.id_cliente 
		and c.activo= 1;
      ';

        $sth = \db()->prepare( $sql );
        
        $sth->execute();

        return $sth->fetchAll();
	
	
	}
	
	
    function borrarCliente( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          Cve_Clte = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['Cve_Clte']
        ) );


        $sql = "UPDATE c_destinatarios SET Activo = 0 WHERE Cve_Clte = '".$data['Cve_Clte']."'";
        $sth = \db()->prepare( $sql );
        $sth->execute();

        $sql = "DELETE FROM RelCliLis WHERE Id_Destinatario IN (SELECT id_destinatario FROM c_destinatarios WHERE Cve_Clte = '".$data['Cve_Clte']."')";
        $sth = \db()->prepare( $sql );
        $sth->execute();

        $sql = "DELETE FROM RelClirutas WHERE IdCliente IN (SELECT id_destinatario FROM c_destinatarios WHERE Cve_Clte = '".$data['Cve_Clte']."')";
        $sth = \db()->prepare( $sql );
        $sth->execute();

        $sql = "DELETE FROM RelDayCli WHERE Id_Destinatario IN (SELECT id_destinatario FROM c_destinatarios WHERE Cve_Clte = '".$data['Cve_Clte']."')";
        $sth = \db()->prepare( $sql );
        $sth->execute();


    }

    function actualizarClientes( $data ) {
		try{

        //$cliente_tipo = "";
        //if($data['cliente_tipo_traslado'] == 1) $cliente_tipo = "TRASLADO";

        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
			Cve_CteProv =:Cve_CteProv,
			RazonSocial = :RazonSocial,
      RazonComercial = :RazonComercial,
      Encargado   = :Encargado,
      Referencia   = :Referencia,
			CalleNumero = :CalleNumero,
			Colonia = :Colonia,
			CodigoPostal = :CodigoPostal,
			Ciudad = :Ciudad,
			Estado = :Estado,
			Pais = :Pais,
			RFC = :RFC,
			Telefono1 = :Telefono1,
			Telefono2 = :Telefono2,
      email_cliente = :email_cliente,
      latitud = :txtLatitud,
      longitud = :txtLongitud,
			ID_Proveedor = :ID_Proveedor,
      Cve_Almacenp = :Cve_Almacenp,
			Contacto = :Contacto,
      credito = :credito,
      limite_credito = :limite_credito,
      dias_credito = :dias_credito,
      credito_actual = :credito_actual,
      saldo_inicial = :saldo_inicial,
      saldo_actual = :saldo_actual,
      ClienteGrupo = :grupocliente,
      ClienteTipo = :tipocliente,
      ClienteTipo2 = :tipocliente2,
      validar_gps = :validar_gps
        WHERE
          Cve_Clte = :Cve_Clte;';
/*
      CondicionPago = :CondicionPago,
      ClienteTipo = :ClienteTipo,
      ZonaVenta = :ZonaVenta,
*/		  

      $this->save = \db()->prepare($sql);
			$this->save->bindValue( ':Cve_Clte', $data['Cve_Clte'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Cve_CteProv', $data['Cve_CteProv'], \PDO::PARAM_STR );
      $this->save->bindValue( ':RazonSocial', $data['RazonSocial'], \PDO::PARAM_STR );
      $this->save->bindValue( ':RazonComercial', $data['NombreCorto'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Encargado', $data['Encargado'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Referencia', $data['Referencia'], \PDO::PARAM_STR );

      $this->save->bindValue( ':credito', $data['credito'], \PDO::PARAM_STR );
      $this->save->bindValue( ':limite_credito', $data['limite_credito'], \PDO::PARAM_STR );
      $this->save->bindValue( ':dias_credito', $data['dias_credito'], \PDO::PARAM_STR );
      $this->save->bindValue( ':credito_actual', $data['credito_actual'], \PDO::PARAM_STR );
      $this->save->bindValue( ':saldo_inicial', $data['saldo_inicial'], \PDO::PARAM_STR );
      $this->save->bindValue( ':saldo_actual', $data['saldo_actual'], \PDO::PARAM_STR );
      $this->save->bindValue( ':validar_gps', $data['validar_gps'], \PDO::PARAM_STR );

			$this->save->bindValue( ':CalleNumero', $data['CalleNumero'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Colonia', $data['Colonia'], \PDO::PARAM_STR );
			$this->save->bindValue( ':CodigoPostal', $data['CodigoPostal'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Ciudad', $data['Ciudad'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Estado', $data['Estado'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Pais', $data['Pais'], \PDO::PARAM_STR );
			$this->save->bindValue( ':RFC', $data['RFC'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Telefono1', $data['Telefono1'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Telefono2', $data['Telefono2'], \PDO::PARAM_STR );
      $this->save->bindValue( ':email_cliente', $data['email_cliente'], \PDO::PARAM_STR );
      $this->save->bindValue( ':txtLatitud', $data['txtLatitud'], \PDO::PARAM_STR );
      $this->save->bindValue( ':txtLongitud', $data['txtLongitud'], \PDO::PARAM_STR );
			$this->save->bindValue( ':ID_Proveedor', $data['ID_Proveedor'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Cve_Almacenp', $data['almacenp'], \PDO::PARAM_STR );
			$this->save->bindValue( ':Contacto', $data['Contacto'], \PDO::PARAM_STR );
      $this->save->bindValue( ':grupocliente', $data['grupocliente'], \PDO::PARAM_STR );
      $this->save->bindValue( ':tipocliente', $data['tipocliente'], \PDO::PARAM_STR );
      $this->save->bindValue( ':tipocliente2', $data['tipocliente2'], \PDO::PARAM_STR );
      //$this->save->bindValue( ':ClienteTipo', $cliente_tipo, \PDO::PARAM_STR );
/*
      $this->save->bindValue( ':CondicionPago', $data['CondicionPago'], \PDO::PARAM_STR );
      $this->save->bindValue( ':ClienteTipo', $data['ClienteTipo'], \PDO::PARAM_STR );
      $this->save->bindValue( ':ZonaVenta', $data['ZonaVenta'], \PDO::PARAM_STR );
*/				
			$this->save->execute();

      $clave_cliente = $data['Cve_Clte'];
      $destinatarios = $data['destinatarios'];
      $RazonSocial   = $data['RazonSocial'];
      //mysqli_query(\db2(), "DELETE FROM c_destinatarios WHERE Cve_Clte = '{$clave_cliente}';");
            
      /*if(intval($data['usar_direccion']) === 1){
        $sql = "UPDATE c_cliente SET ID_Destinatario = 0 WHERE Cve_Clte = '{$clave_cliente}';";
        mysqli_query(\db2(), $sql);
      }else*/ if(!empty($destinatarios)){
        foreach($destinatarios as $destinatario){
          //list($razon, $direccion, $colonia, $codigop, $ciudad, $estado, $contacto, $telefono, $principal) = explode("|", $destinatario);
          list($razon, $direccion, $colonia, $codigop, $ciudad, $estado, $contacto, $telefono, $principal, $emailDest, $latitudDest, $longitudDest) = explode("|", $destinatario);
          if($direccion)
          {
          $sql = "INSERT INTO c_destinatarios (Cve_Clte, razonsocial, direccion, colonia, postal, ciudad, estado, contacto, telefono, dir_principal, email_destinatario, latitud, longitud) VALUES ('{$clave_cliente}', '{$razon}', '{$direccion}', '{$colonia}', '{$codigop}', '{$ciudad}', '{$estado}', '{$contacto}', '{$telefono}', '{$principal}', '{$emailDest}', '{$latitudDest}', '{$longitudDest}');";
            mysqli_query(\db2(), $sql);
          }
          /*if(intval($principal) === 1){
            $sql = "UPDATE c_cliente SET ID_Destinatario = (SELECT MAX(id_destinatario) FROM c_destinatarios) WHERE Cve_Clte = {$clave_cliente};";
            mysqli_query(\db2(), $sql);
          }*/
        }
      }/*else{
        $sql = "UPDATE c_cliente SET ID_Destinatario = NULL WHERE Cve_Clte = '{$clave_cliente}';";
        mysqli_query(\db2(), $sql);
      }*/

        $sql = "UPDATE c_destinatarios SET razonsocial = '{$RazonSocial}' WHERE Cve_Clte = '{$clave_cliente}';";
        mysqli_query(\db2(), $sql);
			
			
        } catch(PDOException $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    function asignarRutaCliente( $cliente, $idRuta ) {

        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET            
            cve_ruta = ?
        WHERE
          Cve_Clte = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $idRuta,
            $cliente
        ) );



    }

     function loadClienteRuta($ID_Ruta) {

        $sql = '
        SELECT
          c.*
        FROM
          c_cliente c, t_clientexruta cr      
        WHERE
          cr.clave_ruta = ? and c.id_cliente = cr.clave_cliente          
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
        $sth->execute( array( id_cliente ) );

        return $sth->fetchAll();

    }
	
	function recoveryCliente( $data ) {

          $sql = "UPDATE c_cliente SET Activo = 1 WHERE  id_cliente='".$data['id_cliente']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['id_cliente']
          ) );
    }
	
  function exist($Cve_Clte) 
  {
   /* $sql = sprintf('
      SELECT
        *
      FROM
        c_cliente
      WHERE
        Cve_Clte = ?
    ',
      self::TABLE
    );*/
    
    $sql = "SELECT * FROM c_cliente WHERE Cve_Clte = '{$Cve_Clte}'";
    
    $sth = \db()->prepare( $sql );
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
    $sth->execute( array( $Cve_Clte ) );
    $this->data = $sth->fetch();

    //if(!$this->data)
    if(!$sth->rowCount())
    {
      return false; 
    }
    else 
    {
      return true;
    }
  }


  function existe_en_otro_almacen($Cve_Clte, $id_almacen) 
  {

    $sql = "SELECT * FROM c_cliente WHERE Cve_Clte = '{$Cve_Clte}' AND Cve_Almacenp != '{$id_almacen}'";
    
    $sth = \db()->prepare( $sql );
    $sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
    $sth->execute( array( $Cve_Clte, $id_almacen ) );
    $this->data = $sth->fetch();

    $sql = "SELECT * FROM c_cliente WHERE Cve_Clte = '{$Cve_Clte}' AND Cve_Almacenp = '{$id_almacen}'";
  
    $sth2 = \db()->prepare( $sql );
    $sth2->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
    $sth2->execute( array( $Cve_Clte, $id_almacen ) );
    $this->data2 = $sth2->fetch();

    //if(!$this->data)
    if($sth->rowCount() && !$sth2->rowCount())
    {
      return true; 
    }
    else 
    {
      return false;
    }
  }

	function getCliente() {

          $sql = '
        SELECT
          *
        FROM
          c_cliente
      ';

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
          @$sth->execute( array( id_cliente ) );

          return $sth->fetchAll();

      }
	  
  function getClientesVentas($cve_almacen) {

          $sql = "
        SELECT DISTINCT * FROM (
            SELECT DISTINCT d.id_destinatario as CodCliente, d.Cve_Clte as Cve_Clte, d.razonsocial as Nombre
            FROM Venta v 
            INNER JOIN c_destinatarios d ON d.id_destinatario = v.CodCliente
            INNER JOIN Cobranza c ON c.Cliente = v.CodCliente
            WHERE v.IdEmpresa = '{$cve_almacen}' AND v.Cancelada = 0 AND c.Status = 1

            UNION 

            SELECT DISTINCT d.id_destinatario as CodCliente, d.Cve_Clte as Cve_Clte, d.razonsocial as Nombre
            FROM V_Cabecera_Pedido p
            INNER JOIN c_destinatarios d ON d.id_destinatario = p.Cod_Cliente
            INNER JOIN Cobranza c ON c.Cliente = p.Cod_Cliente 
            WHERE p.IdEmpresa = '{$cve_almacen}' AND p.Cancelada = 0 AND c.Status = 1
          ) as clientes_ventas
            ";

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
          @$sth->execute( array( id_cliente ) );

          return $sth->fetchAll();

      }

  function getClientesAnalisisVentas($cve_almacen) {

          $sql = "
        SELECT DISTINCT * FROM (
            SELECT DISTINCT d.id_destinatario as CodCliente, d.Cve_Clte as Cve_Clte, d.razonsocial as Nombre
            FROM Venta v 
            INNER JOIN c_destinatarios d ON d.id_destinatario = v.CodCliente
            WHERE v.IdEmpresa = '{$cve_almacen}' AND v.Cancelada = 0 

            UNION 

            SELECT DISTINCT d.id_destinatario as CodCliente, d.Cve_Clte as Cve_Clte, d.razonsocial as Nombre
            FROM V_Cabecera_Pedido p
            INNER JOIN c_destinatarios d ON d.id_destinatario = p.Cod_Cliente
            WHERE p.IdEmpresa = '{$cve_almacen}' AND p.Cancelada = 0 
          ) as clientes_ventas
            ";

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
          @$sth->execute( array( id_cliente ) );

          return $sth->fetchAll();

      }

	function getCliente2(){
		$sql= 'Select c.*, d.departamento, d.des_municipio, a.nombre as almacenp from c_cliente c  left join c_almacenp a on a.id=c.Cve_Almacenp
		left join c_dane d on  d.cod_municipio=c.CodigoPostal where c.Activo=1';
		
		  $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
          $sth->execute( array( id_cliente ) );

          return $sth->fetchAll();
		
	}
  
    function asignarRutaACliente($data) 
    {
      $clientes = $data["clientes"];
      $ruta = $data["ruta"];
      foreach($clientes as $cliente)
      {
        $sql = "
          INSERT INTO `t_clientexruta`
            (`clave_cliente`, `clave_ruta`)
          VALUES (
            (SELECT id_cliente FROM c_cliente WHERE Cve_Clte = '{$cliente}'),
            (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$ruta}')
          )
        ";
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
        $sth->execute();
      }
    }	
  
  
    function getConsecutivo()
    {
      //$sql = "SELECT COUNT(id_cliente)+1 as id_actual FROM `c_cliente`";
      $sql = "SELECT IFNULL(MAX(id_cliente), 0)+1 as id_actual FROM `c_cliente`";

      $sth = \db()->prepare($sql);
      $sth->execute();
      $consecutivo = $sth->fetch();
      return($consecutivo["id_actual"]);
    }
	  
	  
	}
