<?php
/*
  namespace Ruta;

  class Ruta {

    const TABLE = 't_ruta';
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

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
        $sth->execute( array( ID_Ruta ) );

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
          $sth->execute(array( ID_Ruta ) );
          return $sth->fetch();
      }
	
    function __get( $key ) {

      switch($key) {
        case 'ID_Ruta':
        case 'cve_ruta':
        case 'descripcion':
        case 'status':
        case 'cve_cia':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

      function save( $data ) {
          try {
              $sql = mysqli_query(\db2(), "CALL Ruta_AddUpdate(
		      '".$data['ID_Ruta']."'
		    , '".$data['cve_ruta']."'
		    , '".$data['descripcion']."'
		    , '".$data['status']."'
		    , '".$data['cve_cia']."'		    
		    );") or die(mysqli_error(\db2()));
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
/*
	function actualizarRuta( $data ) {
        $sql = mysqli_query(\db2(), "CALL Ruta_AddUpdate(
		      '".$data['ID_Ruta']."'
		    , '".$data['cve_ruta']."'
		    , '".$data['descripcion']."'
		    , '".$data['status']."'
		    , '".$data['cve_cia']."'
		    );") or die(mysqli_error(\db()));
    }

      function borrarRuta( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          ID_Ruta = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['ID_Ruta']
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
