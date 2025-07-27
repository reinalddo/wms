<?php

  namespace Proyectos;

  class Proyectos {

    const TABLE = 'c_proyecto';
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

        WHERE Id = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proyectos\Proyectos' );
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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proyectos\Proyectos' );
        $sth->execute( array( $cve_umed ) );

        return $sth->fetchAll();

    }	

    function getAllProyectos($id_almacen) {

        $sql = "
        SELECT
          *
        FROM
          " . self::TABLE . " WHERE id_almacen = $id_almacen #AND IFNULL(Cve_Proyecto, '') NOT IN (SELECT IFNULL(Proyecto, '') FROM th_entalmacen)
    #where Activo=1
      ";

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proyectos\Proyectos' );
        $sth->execute( array( $cve_umed ) );

        return $sth->fetchAll();

    } 

    function getAllProyectosOcupados($id_almacen) {

        $sql = "
        SELECT
          *
        FROM
          " . self::TABLE . " WHERE id_almacen = $id_almacen AND IFNULL(Cve_Proyecto, '') IN (SELECT IFNULL(Proyecto, '') FROM th_entalmacen) 
          #AND IFNULL(Cve_Proyecto, '') NOT IN (SELECT IFNULL(Proyecto, '') FROM td_pedido)
    #where Activo=1
      ";

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proyectos\Proyectos' );
        $sth->execute( array( $cve_umed ) );

        return $sth->fetchAll();

    } 

    function getAllProyectosExistencias($id_almacen) {

        $sql = "
        SELECT
          *
        FROM
          " . self::TABLE . " WHERE id_almacen = $id_almacen AND IFNULL(Cve_Proyecto, '') IN (SELECT proyecto FROM t_trazabilidad_existencias WHERE IFNULL(proyecto, '') != '' AND cve_almac = $id_almacen AND idy_ubica IS NOT NULL)  
          #AND IFNULL(Cve_Proyecto, '') NOT IN (SELECT IFNULL(Proyecto, '') FROM td_pedido)
    #where Activo=1
      ";

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proyectos\Proyectos' );
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
          Cve_Proyecto = :Clave,
          Des_Proyecto = :descripcion,
          id_almacen = :id_almacen
      ');

      $this->save = \db()->prepare($sql);

      $this->save->bindValue( ':Clave', $_post['Clave'], \PDO::PARAM_STR );
      $this->save->bindValue( ':descripcion', $_post['descripcion'], \PDO::PARAM_STR );
      $this->save->bindValue( ':id_almacen', $_post['id_almacen'], \PDO::PARAM_STR );

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

      function borrarProyecto( $data ) {
          $sql = '
        DELETE FROM 
          ' . self::TABLE . '
        WHERE
          Id = ?
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

	function actualizarProyectos( $data ) {
      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Cve_Proyecto = ?
          ,Des_Proyecto = ?
        WHERE
          id = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['Clave']
    , $data['descripcion']
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
          Cve_Proyecto = ?
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
