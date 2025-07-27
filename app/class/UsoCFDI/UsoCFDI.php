<?php

  namespace UsoCFDI;

  class UsoCFDI {

    const TABLE = 'c_usocfdi';
    var $identifier;

    public function __construct( $clave = false, $key = false ) {

      if( $clave ) {
        $this->id = (int) $clave;
      }
/*
      if($key) {

        $sql = sprintf('
          SELECT
            cve_umed
          FROM
            %s
          WHERE
            cve_umed = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\UnidadesMedida\UnidadesMedida');
        $sth->execute(array($key));

        $unidadesmedida = $sth->fetch();

        $this->cve_umed = $unidadesmedida->cve_umed;

      }
*/
    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s

        WHERE id = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\UsoCFDI\UsoCFDI' );
      $sth->execute( array( $this->clave ) );

      $this->data = $sth->fetch();

    }

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
		#where Activo=1
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\UsoCFDI\UsoCFDI' );
        $sth->execute( array( $cve_umed ) );

        return $sth->fetchAll();

    }	

    function __get( $key ) {

      switch($key) {
          case 'clave':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function save( $_post ) {

      //if( !$_post['des_umed'] ) { throw new \ErrorException( 'des_umed is required.' ); }

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET       
          clave = :Clave,
          nombre = :Nombre,
          apellido = :Apellido,
          correo = :Email,
          telefono1 = :Telefono1,
          telefono2 = :Telefono2,
          pais = :Pais,
          estado = :Estado,
          ciudad = :Ciudad,
          direccion = :Direccion
      ');

      $this->save = \db()->prepare($sql);

      $this->save->bindValue( ':Clave', $_post['Clave'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Nombre', $_post['Nombre'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Apellido', $_post['Apellido'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Email', $_post['Email'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Telefono1', $_post['Telefono1'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Telefono2', $_post['Telefono2'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Pais', $_post['Pais'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Estado', $_post['Estado'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Ciudad', $_post['Ciudad'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Direccion', $_post['Direccion'], \PDO::PARAM_STR );

      $this->save->execute();

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

      function borrarContacto( $data ) {
          $sql = '
        DELETE FROM 
          ' . self::TABLE . '
        WHERE
          id = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['clave']
          ) );
      }
	  
	  	function recovery( $data ) {

             $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 1
        WHERE
          cve_umed = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['cve_umed']
          ) );
    }

	function actualizarUsoCFDI( $data ) {
      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          clave = ?
          ,nombre = ?
          ,apellido = ?
          ,correo = ?
          ,telefono1 = ?
          ,telefono2 = ?
          ,pais = ?
          ,estado = ?
          ,ciudad = ?
          ,direccion = ?
        WHERE
          id = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['Clave']
    , $data['Nombre']
    , $data['Apellido']
    , $data['Email']
    , $data['Telefono1']
    , $data['Telefono2']
    , $data['Pais']
    , $data['Estado']
    , $data['Ciudad']
    , $data['Direccion']
    , $data['id']
      ) );
    }
	
		   function exist($cve_umed) {

      $sql = sprintf('
        SELECT
          *
        FROM
			 ' . self::TABLE . '
        WHERE
          clave = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );

      $sth->execute( array( $cve_umed ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }

    /*function settings_design( $data ) {

      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Empresa = ?
        , VendId = ?
        , ID_Externo = ?
        WHERE
          ID_Proveedor = ?
      ';

      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['Empresa']
      , $data['VendId']
      , $data['ID_Externo']
      ) );

    }*/
    public function inUse( $data ) {


      $sql = "SELECT cve_umed FROM `c_articulo` WHERE cve_umed = '".$data['cve_umed']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['cve_umed']) 
        return true;
    else
        return false;
  }

  }
