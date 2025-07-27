<?php

  namespace SSubGrupoArticulos;

  class SSubGrupoArticulos {

    const TABLE = 'c_ssgpoarticulo';
    var $identifier;

    public function __construct( $c_ssgpoarticulo = false, $key = false ) {

      if( $c_ssgpoarticulo ) {
        $this->c_ssgpoarticulo = (int) $c_ssgpoarticulo;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            cve_ssgpoart
          FROM
            %s
          WHERE
            cve_ssgpoart = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Articulos\Articulos');
        $sth->execute(array($key));

        $ssubgrupoarticulo = $sth->fetch();

        $this->cve_ssgpoart = $ssubgrupoarticulo->cve_ssgpoart;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          cve_ssgpoart = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
      $sth->execute( array( $this->cve_ssgpoart ) );

      $this->data = $sth->fetch();

    }

    function __get( $key ) {

      switch($key) {
        case 'cve_ssgpoart':
        case 'cve_sgpoart':
        case 'des_ssgpoart':
        case 'Opcinal':		
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function save( $data ) {

      if( !$data['cve_sgpoart'] ) { throw new \ErrorException( 'cve_sgpoart is required.' ); }
      if( !$data['des_ssgpoart'] ) { throw new \ErrorException( 'des_ssgpoart is required.' ); }	  
      if( !$data['Opcinal'] ) { throw new \ErrorException( 'Opcinal is required.' ); }	  	  

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          cve_sgpoart = :cve_sgpoart
        , des_ssgpoart = :des_ssgpoart
        , Opcinal = :Opcinal		
      ');

      $this->save = \db()->prepare($sql);

      /*$password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );*/
      $identifier = bin2hex(openssl_random_pseudo_bytes(10));

      $this->identifier = $identifier;

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':cve_sgpoart', $data['cve_sgpoart'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_ssgpoart', $data['des_ssgpoart'], \PDO::PARAM_STR );
      $this->save->bindValue( ':Opcinal', $data['Opcinal'], \PDO::PARAM_STR );	  

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

	function actualizarSSbArticulos( $data ) {
      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          cve_sgpoart = ?
        , des_ssgpoart = ?
        , Opcinal = ?		
        WHERE
          cve_ssgpoart = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['cve_ssgpoart']
      , $data['cve_sgpoart']		
      , $data['des_ssgpoart']
      , $data['Opcinal']
      ) );
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

  }
