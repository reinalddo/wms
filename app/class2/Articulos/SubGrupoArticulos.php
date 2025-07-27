<?php

  namespace SubGrupoArticulos;

  class SubGrupoArticulos {

    const TABLE = 'c_sgpoarticulo';
    var $identifier;

    public function __construct( $cve_sgpoart = false, $key = false ) {

      if( $cve_sgpoart ) {
        $this->cve_sgpoart = (int) $cve_sgpoart;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            cve_sgpoart
          FROM
            %s
          WHERE
            cve_sgpoart = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Articulos\Articulos');
        $sth->execute(array($key));

        $subgrupoarticulo = $sth->fetch();

        $this->cve_sgpoart = $subgrupoarticulo->cve_sgpoart;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          cve_sgpoart = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
      $sth->execute( array( $this->cve_sgpoart ) );

      $this->data = $sth->fetch();

    }

    function __get( $key ) {

      switch($key) {
        case 'cve_sgpoart':
        case 'cve_gpoart':
        case 'des_sgpoart':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function save( $data ) {

      if( !$data['cve_gpoart'] ) { throw new \ErrorException( 'cve_gpoart is required.' ); }
      if( !$data['des_sgpoart'] ) { throw new \ErrorException( 'des_sgpoart is required.' ); }	  

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          cve_gpoart = :cve_gpoart
        , des_sgpoart = :des_sgpoart
      ');

      $this->save = \db()->prepare($sql);

      /*$password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );*/
      $identifier = bin2hex(openssl_random_pseudo_bytes(10));

      $this->identifier = $identifier;

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':cve_gpoart', $data['cve_gpoart'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_sgpoart', $data['des_sgpoart'], \PDO::PARAM_STR );

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

	function actualizarProveedor( $data ) {
      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          cve_gpoart = ?
        , des_sgpoart = ?
        WHERE
          cve_sgpoart = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['cve_sgpoart']
      , $data['cve_gpoart']
      , $data['des_sgpoart']
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
