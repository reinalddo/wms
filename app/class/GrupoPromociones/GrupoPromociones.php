<?php

  namespace GrupoPromociones;

  class GrupoPromociones {

    const TABLE = 'ListaPromoMaster';
    var $identifier;

    public function __construct( $cve_gpoart = false, $key = false ) {

      if( $cve_gpoart ) {
        $this->cve_gpoart = (int) $cve_gpoart;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            ListaMaster
          FROM
            %s
          WHERE
            Id = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\GrupoPromociones\GrupoPromociones');
        $sth->execute(array($key));

        $GrupoPromociones = $sth->fetch();

        $this->cve_gpoart = $GrupoPromociones->cve_gpoart;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          Id = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\GrupoPromociones\GrupoPromociones' );
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

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          ListaMaster = :descripcion
          ,Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = :cve_almacen)
      ');

      $this->save = \db()->prepare($sql);

      /*$password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );*/
      $identifier = bin2hex(openssl_random_pseudo_bytes(10));

      $this->identifier = $identifier;

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':descripcion', $data['descripcion'], \PDO::PARAM_STR );
      $this->save->bindValue( ':cve_almacen', $data['cve_almacen'], \PDO::PARAM_STR );

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

  function borrarGrupoPromociones($data) {
    $sql = '
        DELETE
        FROM
          ListaPromoMaster       
        WHERE
          Id = "'.$data['id'].'";';
    $this->save = \db()->prepare($sql);
    $this->save->execute(array($cve_almacen));
  }

	function actualizarGrupoPromociones( $data ) {
      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          ListaMaster = ?
        WHERE
          Id = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['descripcion']
      , $data['codigo']
	     ) );
    }

  public function inUse( $data ) {

    $sql = "SELECT Id FROM DetalleLProMaster WHERE IdLm = '".$data['id']."'";

    $sth = \db()->prepare($sql);
    $sth->execute();
    $data = $sth->fetch();

    if ($data['Id']) 
      return true;
    else
      return false;
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
