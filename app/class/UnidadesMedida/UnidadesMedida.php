<?php

  namespace UnidadesMedida;

  class UnidadesMedida {

    const TABLE = 'c_unimed';
    var $identifier;

    public function __construct( $cve_umed = false, $key = false ) {

      if( $cve_umed ) {
        $this->cve_umed = (int) $cve_umed;
      }

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

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          cve_umed = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\UnidadesMedida\UnidadesMedida' );
      $sth->execute( array( $this->cve_umed ) );

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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\UnidadesMedida\UnidadesMedida' );
        $sth->execute( array( $cve_umed ) );

        return $sth->fetchAll();

    }	

    function __get( $key ) {

      switch($key) {
        case 'cve_umed':
        case 'des_umed':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function save( $_post ) {

      if( !$_post['des_umed'] ) { throw new \ErrorException( 'des_umed is required.' ); }

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET       
          des_umed = :des_umed,
		  cve_umed = :cve_umed
      ');

      $this->save = \db()->prepare($sql);

      $this->save->bindValue( ':des_umed', $_post['des_umed'], \PDO::PARAM_STR );
	    $this->save->bindValue( ':cve_umed', $_post['cve_umed'], \PDO::PARAM_STR );

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

      function borrarUnidMed( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          cve_umed = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['cve_umed']
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

	function actualizarUnidadesMedida( $data ) {
      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          des_umed = ?
        WHERE
          cve_umed = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['des_umed']
	  , $data['cve_umed']
      ) );
    }
	
		   function exist($cve_umed) {

      $sql = sprintf('
        SELECT
          *
        FROM
			 ' . self::TABLE . '
        WHERE
          cve_umed = ?
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
