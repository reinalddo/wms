<?php

  namespace GrupoArticulos;

  class GrupoArticulos {

    const TABLE = 'c_gpoarticulo';
    var $identifier;

    public function __construct( $cve_gpoart = false, $key = false ) {

      if( $cve_gpoart ) {
        $this->cve_gpoart = (int) $cve_gpoart;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            cve_gpoart
          FROM
            %s
          WHERE
            cve_gpoart = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Articulos\Articulos');
        $sth->execute(array($key));

        $grupoarticulos = $sth->fetch();

        $this->cve_gpoart = $grupoarticulos->cve_gpoart;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          cve_gpoart = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
      $sth->execute( array( $this->cve_gpoart ) );

      $this->data = $sth->fetch();

    }

    function __get( $key ) {

      switch($key) {
        case 'cve_gpoart':
        case 'des_gpoart':
        case 'por_depcont':
        case 'por_depfical':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function save( $data ) {

      if( !$data['des_gpoart'] ) { throw new \ErrorException( 'des_gpoart is required.' ); }
      if( !$data['por_depcont'] ) { throw new \ErrorException( 'por_depcont is required.' ); }
      if( !$data['por_depfical'] ) { throw new \ErrorException( 'por_depfical is required.' ); }

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          des_gpoart = :des_gpoart
        , por_depcont = :por_depcont
        , por_depfical = :por_depfical
      ');

      $this->save = \db()->prepare($sql);

      /*$password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );*/
      $identifier = bin2hex(openssl_random_pseudo_bytes(10));

      $this->identifier = $identifier;

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':des_gpoart', $data['des_gpoart'], \PDO::PARAM_STR );
      $this->save->bindValue( ':por_depcont', $data['por_depcont'], \PDO::PARAM_STR );
      $this->save->bindValue( ':por_depfical', $data['por_depfical'], \PDO::PARAM_STR );

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

	function actualizarGrupoArticulos( $data ) {
      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          des_gpoart = ?
        , por_depcont = ?
        , por_depfical = ?
        WHERE
          cve_gpoart = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['des_gpoart']
      , $data['por_depcont']
      , $data['por_depfical']
	  , $data['cve_gpoart']
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
