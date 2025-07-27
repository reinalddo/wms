<?php

  namespace Almacen;

  class Almacen {

    const TABLE = 'c_almacen';
    var $identifier;

    public function __construct( $cve_almac = false, $key = false ) {

      if( $cve_almac ) {
        $this->cve_almac = (int) $cve_almac;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            cve_almac
          FROM
            %s
          WHERE
            cve_almac = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Almacen\Almacen');
        $sth->execute(array($key));

        $almacen = $sth->fetch();

        $this->cve_almac = $almacen->cve_almac;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          cve_almac = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen' );
      $sth->execute( array( $this->cve_almac ) );

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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen' );
        $sth->execute( array( cve_almac ) );

        return $sth->fetchAll();

    }
	
    function __get( $key ) {

      switch($key) {
        case 'cve_almac':
        case 'cve_cia':
        case 'des_almac':
        case 'des_direcc':
        case 'ManejaCajas':
        case 'ManejaPiezas':
        case 'MaxXPedido':
        case 'Maneja_Maximos':
        case 'MANCC':										
        case 'Compromiso':												
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function save( $_post ) {

      if( !$_post['cve_cia'] ) { throw new \ErrorException( 'cve_cia is required.' ); }
      if( !$_post['des_almac'] ) { throw new \ErrorException( 'des_almac is required.' ); }
      if( !$_post['des_direcc'] ) { throw new \ErrorException( 'des_direcc is required.' ); }

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          cve_cia = :cve_cia
        , des_almac = :des_almac
        , des_direcc = :des_direcc	        	
      ');

        $this->save = \db()->prepare($sql);

        //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_cia', $_post['cve_cia'], \PDO::PARAM_STR );
        $this->save->bindValue( ':des_almac', $_post['des_almac'], \PDO::PARAM_STR );
        $this->save->bindValue( ':des_direcc', $_post['des_direcc'], \PDO::PARAM_STR );

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

	function actualizarAlmacen( $data ) {
      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          cve_cia = ?
        , des_almac = ?
        , des_direcc = ?        	        										
        WHERE
          cve_almac = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['cve_cia']
      , $data['des_almac']
      , $data['des_direcc']
      , $data['cve_almac']
      ) );
    }

      function borrarAlmacen( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          cve_almac = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['cve_almac']
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

    function getLastInsertId(){
      $sql = '
        SELECT MAX(cve_almac) as ID_Almacen        
        FROM
          ' . self::TABLE . '
      ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen');
      $sth->execute(array( ID_Ruta ) );
      return $sth->fetch();
    }

      function saveUserAl( $usuario, $ID_Almacen ) {
          try {
              $sql = mysqli_query(\db2(), "CALL SPAD_AgregaRelUsA (
		      '".$usuario."'
		    , '".$ID_Almacen."'   
		    );") or die(mysqli_error(\db2()));
          } catch(PDOException $e) {
              return 'ERROR: ' . $e->getMessage();
          }

      }

    function loadUserAlmacen($almacen) {

      $sql = '
        SELECT
          *
        FROM
          trel_us_alm         
        WHERE
          cve_almac = ?
        AND 
          Activo = 1
          
      ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen' );
      $sth->execute( array( $almacen ) );

      return $sth->fetchAll();

    }

    function loadAlmacenUser($user) {

       $sql = '
        SELECT 
          c.cve_almac, c.des_almac 
        FROM 
          trel_us_alm t 
        INNER JOIN 
          c_almacen c ON c.cve_almac = t.cve_almac 
        WHERE 
          cve_usuario = ?
        AND 
          t.Activo = 1 
          
      ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen' );
      $sth->execute( array( $user ) );

      return $sth->fetchAll();

    }

    function borrarUsuarioAlmacen( $cve_almacen, $cve_usuario ) {
      $sql = '
        DELETE
        FROM
          trel_us_alm       
        WHERE
          cve_almac = ?
        AND 
          cve_usuario = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
          $cve_almacen,
          $cve_usuario
      ) );
    }

  }
