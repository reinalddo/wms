<?php

  namespace AreaRevision;

  class AreaRevision {

    const TABLE = 't_ubicaciones_revision';
    var $identifier;

    public function __construct( $ID_URevision = false, $key = false ) {

      if( $ID_URevision ) {
        $this->ID_URevision = (int) $ID_URevision;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            ID_URevision
          FROM
            %s
          WHERE
            ID_URevision = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\AreaRevision\AreaRevision');
        $sth->execute(array($key));

        $area_revision = $sth->fetch();

        $this->ID_URevision = $area_revision->ID_URevision;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          ID_URevision = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\AreaRevision\AreaRevision' );
      $sth->execute( array( $this->ID_URevision ) );

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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\AreaRevision\AreaRevision' );
        $sth->execute( array( ID_URevision ) );

        return $sth->fetchAll();

    }
	
    function __get( $key ) {

      switch($key) {
        case 'ID_URevision':
        case 'cve_almac':
        case 'cve_ubicacion':
        case 'descripcion':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function save( $data ) {

      //if( !$_post['cve_almac'] ) { throw new \ErrorException( 'cve_almac is required.' ); }
      //if( !$_post['cve_ubicacion'] ) { throw new \ErrorException( 'cve_ubicacion is required.' ); }

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          cve_almac = :cve_almac,
          cve_ubicacion = :cve_ubicacion,
		      descripcion = :descripcion,
          AreaStagging = :stagging
      ');

        $this->save = \db()->prepare($sql);

        //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_almac', $data['cve_almac'], \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_ubicacion', $data['cve_ubicacion'], \PDO::PARAM_STR );
		    $this->save->bindValue( ':descripcion', $data['descripcion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':stagging', $data['stagging'], \PDO::PARAM_STR );

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

	function actualizarARevision( $data ) {
    //if($data['AreaStagging'] == '') $data['AreaStagging'] = 'N';
      $sql = "
        UPDATE
          " . self::TABLE . "
        SET
          cve_almac = '".$data['cve_almac']."', 
      descripcion = '".$data['descripcion']."',    
      AreaStagging = '".$data['stagging']."'    
        WHERE
          ID_URevision = '".$data['ID_URevision']."'";

	$rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
    }

      function borrarARevision( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          ID_URevision = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['ID_URevision']
          ) );
      }

	        function recovery( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 1
        WHERE
          ID_URevision = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['ID_URevision']
          ) );
      }
	  
	  
	  
	  
	private function loadClave() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          cve_ubicacion = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\AreaRevision\AreaRevision' );
      $sth->execute( array( $this->cve_ubicacion ) );

      $this->data = $sth->fetch();

    }
	  
	function validaClave( $key ) {
      switch($key) {
        case 'ID_URevision':
        case 'cve_almac':
        case 'cve_ubicacion':
          $this->loadClave();
          return @$this->data->$key;
        default:
          return $this->key;
      }

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
         
      $sql = "SELECT cve_ubicacion FROM V_ExistenciaGral WHERE cve_ubicacion = '".$data['cve_ubicacion']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['cve_ubicacion']) 
        return true;
    else
        return false;
  }
  
  }
