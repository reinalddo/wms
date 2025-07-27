<?php

  namespace Transporte;

  class Transporte {

    const TABLE = 't_transporte';
    var $identifier;

    public function __construct( $ID_Transporte = false, $key = false ) {

      if( $ID_Transporte ) {
        $this->ID_Transporte = (int) $ID_Transporte;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            ID_Transporte
          FROM
            %s
          WHERE
            ID_Transporte = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Transporte\Transporte');
        $sth->execute(array($key));

        $transporte = $sth->fetch();

        $this->ID_Transporte = $transporte->ID_Transporte;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          ID_Transporte = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Transporte\Transporte' );
      $sth->execute( array( $this->ID_Transporte ) );

      $this->data = $sth->fetch();

    }

    function getAll($id_almacen = "") {

      $sql_almacen = "";
      if($id_almacen)
        $sql_almacen = " WHERE id_almac = {$id_almacen} ";
        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . ' $sql_almacen
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Transporte\Transporte' );
        @$sth->execute( array( ID_Transporte ) );

        return $sth->fetchAll();

    }
	
    function __get( $key ) {

      switch($key) {
        case 'ID_Transporte':
          $this->load();
          return @$this->data->$key;
        case 'Nombre':
        case 'Placas':
        case 'cve_cia':
        case 'tipo_transporte':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

      function save( $data ) {
          try {
        $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          ID_Transporte = :ID_Transporte
        , Nombre = :des_tr
        , Placas = :Placas
        , cve_cia = :cve_cia										
        , id_almac = :almacen
        , tipo_transporte = :tipo_transporte
        , num_ec = :num_ec
        , transporte_externo = :transporte_externo
      ');
	  
	        $this->save = \db()->prepare($sql);

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':ID_Transporte', $data['ID_Transporte'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_tr', $data['des_tr'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Placas', $data['Placas'], \PDO::PARAM_STR );
      $this->save->bindValue( ':cve_cia', $data['cve_cia'], \PDO::PARAM_STR );
      $this->save->bindValue( ':tipo_transporte', $data['tipo_transporte'], \PDO::PARAM_STR );
      $this->save->bindValue( ':almacen', $data['almacen'], \PDO::PARAM_STR );
      $this->save->bindValue( ':num_ec', $data['num_ec'], \PDO::PARAM_STR );
      $this->save->bindValue( ':transporte_externo', $data['transporte_externo'], \PDO::PARAM_STR );

      $this->save->execute();


	  
	  
	  
          } catch(PDOException $e) {
              return 'ERROR: ' . $e->getMessage();
          }

      }

    /*function password( $data ) {

      if( !$data['password'] ) { throw new \ErrorException( 'Unfortuantly you wont get far without a password.' ); }

      $sql = sprintf('
        UPDATE
          ' . self::TABLE . '
        SET
          password = :password
        WHERE
          id_user = ' . $this->id_user . '
      ');

      $this->save = \db()->prepare($sql);

      $password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );

      $this->save->bindValue( ':password', $password, \PDO::PARAM_STR );
      $this->save->execute();

    }*/

	function actualizarTransporte( $data ) {
        $sql = sprintf('
        UPDATE
          ' . self::TABLE . '
        SET
          Nombre = :des_tr
        , Placas = :Placas
        , cve_cia = :cve_cia
        , id_almac = :almacen
        , tipo_transporte = :tipo_transporte
        , num_ec = :num_ec
        , transporte_externo = :transporte_externo
		where
		ID_Transporte = :ID_Transporte
      ');
	  
	        $this->save = \db()->prepare($sql);

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':ID_Transporte', $data['ID_Transporte'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_tr', $data['des_tr'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Placas', $data['Placas'], \PDO::PARAM_STR );
      $this->save->bindValue( ':cve_cia', $data['cve_cia'], \PDO::PARAM_STR );
      $this->save->bindValue( ':tipo_transporte', $data['tipo_transporte'], \PDO::PARAM_STR );
      $this->save->bindValue( ':almacen', $data['almacen'], \PDO::PARAM_STR );
      $this->save->bindValue( ':num_ec', $data['num_ec'], \PDO::PARAM_STR );
      $this->save->bindValue( ':transporte_externo', $data['transporte_externo'], \PDO::PARAM_STR );

      $this->save->execute();


	  

    }

      function borrarTransporte( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          ID_Transporte = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['ID_Transporte']
          ) );
      }
	  
	  function exist($clave_transporte) {

      $sql = sprintf('
        SELECT
          *
        FROM
          t_transporte
        WHERE
          ID_Transporte = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
       $sth->setFetchMode( \PDO::FETCH_CLASS, '\Transporte\Transporte' );
      $sth->execute( array( $clave_transporte ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }


  function existe_en_otro_almacen($clave_transporte, $id_almacen) 
  {

    $sql = "SELECT * FROM t_transporte WHERE ID_Transporte = '{$clave_transporte}' AND id_almac != '{$id_almacen}'";
    
    $sth = \db()->prepare( $sql );
    //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
    $sth->execute( array( $Cve_Clte, $id_almacen ) );
    $this->data = $sth->fetch();

    $sql = "SELECT * FROM t_transporte WHERE ID_Transporte = '{$clave_transporte}' AND id_almac = '{$id_almacen}'";
  
    $sth2 = \db()->prepare( $sql );
    //$sth2->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
    $sth2->execute( array( $clave_transporte, $id_almacen ) );
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

	
	function existNombre($Nombre) {

      $sql = sprintf('
        SELECT
          *
        FROM
          t_transporte
        WHERE
          Nombre =  ?
      ',
	   self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
       $sth->setFetchMode( \PDO::FETCH_CLASS, '\Transporte\Transporte' );
      $sth->execute( array( $Nombre ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }

	function recoveryTransporte( $data ) {

          $sql = "UPDATE " . self::TABLE . "
		  SET Activo = 1 WHERE  ID_Transporte='".$data['ID_Transporte']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['ID_Transporte']
          ) );
    }

  }
