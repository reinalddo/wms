<?php

  namespace CortinaEntrada;

  class CortinaEntrada {

    const TABLE = 'tubicacionesretencion';
    var $identifier;

    public function __construct( $cve_ubicacion = false, $key = false ) {

      if( $cve_ubicacion ) {
        $this->cve_ubicacion = (int) $cve_ubicacion;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            cve_ubicacion
          FROM
            %s
          WHERE
            cve_ubicacion = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\CortinaEntrada\CortinaEntrada');
        $sth->execute(array($key));

        $c_entrada = $sth->fetch();

        $this->cve_ubicacion = $cve_ubicacion->cve_ubicacion;

      }

    }

    private function load() {

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
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\CortinaEntrada\CortinaEntrada' );
      $sth->execute( array( $this->cve_ubicacion ) );

      $this->data = $sth->fetch();

    }
	
			   function exist($cve_ubicacion) {

      $sql = sprintf('
        SELECT
          *
        FROM
            ' . self::TABLE . '
        WHERE
          cve_ubicacion = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\CortinaEntrada\CortinaEntrada' );
      $sth->execute( array( $cve_ubicacion ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\CortinaEntrada\CortinaEntrada' );
        $sth->execute( array( cve_ubicacion ) );

        return $sth->fetchAll();

    }
	
    function __get( $key ) {

      switch($key) {
        case 'cve_ubicacion':
        case 'cve_almacp':
        case 'desc_ubicacion':
        case 'Activo':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function save( $_post ) {

      if( !$_post['cve_ubicacion'] ) { throw new \ErrorException( 'cve_ubicacion is required.' ); }
    
      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          cve_ubicacion = :cve_ubicacion
        , cve_almacp = :cve_almacp
        , desc_ubicacion = :desc_ubicacion                	
        , AreaStagging = :stagging

      ');

        $this->save = \db()->prepare($sql);

        //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_ubicacion', $_post['cve_ubicacion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_almacp', $_post['cve_almacp'], \PDO::PARAM_STR );
        $this->save->bindValue( ':desc_ubicacion', $_post['desc_ubicacion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':stagging', $_post['stagging'], \PDO::PARAM_STR );

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

	function actualizarCEntrada( $data ) {
      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
		  cve_almacp = ?       	        										
        , desc_ubicacion = ?        
        , AreaStagging = ?

        WHERE
          cve_ubicacion = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['cve_almacp']
      , $data['desc_ubicacion']
      , $data['stagging']
      , $data['cve_ubicacion']
      ) );
    }

      function borrarCEntrada( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          cve_ubicacion = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['cve_ubicacion']
          ) );
      }
	  
	    function recovery( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 1
        WHERE
          cve_ubicacion = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['cve_ubicacion']
          ) );
      }
	  
	   function loadPorAlmacen( $cve_almacenp, $excludeInventario = 0) {
          $excludeSql = $excludeInventario > 0 ? "AND z.cve_ubicacion NOT IN(SELECT COALESCE(cve_ubicacion, idy_ubica) AS cve_ubicacion FROM t_ubicacioninventario WHERE ID_Inventario IN (SELECT ID_Inventario FROM th_inventario WHERE Status = 'A')) " : "";
          $sql = "SELECT  z.* 
                  FROM tubicacionesretencion z 
                  LEFT JOIN c_almacenp ON c_almacenp.id=z.cve_almacp 
			            WHERE c_almacenp.clave = '{$cve_almacenp}' {$excludeSql}";
        
  				$sth = \db()->prepare( $sql );
  				$sth->execute();

          return $sth->fetchAll();
      }

      function loadPorAlmacen2( $id_almacen, $excludeInventario = 0 ) 
      {
        $and_activo = $excludeInventario == 0 ? " AND z.Activo ='1'" : "";
        $excludeSql = $excludeInventario > 0 ? "AND z.cve_ubicacion NOT IN(SELECT COALESCE(cve_ubicacion, idy_ubica) AS cve_ubicacion FROM t_ubicacioninventario WHERE ID_Inventario IN (SELECT ID_Inventario FROM th_inventario WHERE Status = 'A')) " : "";
        $sql = 
          "
            SELECT  z.* 
            FROM tubicacionesretencion z 
            LEFT JOIN c_almacenp ON c_almacenp.id = z.cve_almacp 
            WHERE c_almacenp.id = '{$id_almacen}' {$excludeSql} {$and_activo}
          ";
     
        //echo var_dump($sql);
        //die();
        
        $sth = \db()->prepare( $sql );
        $sth->execute();
        return $sth->fetchAll();
      }

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
