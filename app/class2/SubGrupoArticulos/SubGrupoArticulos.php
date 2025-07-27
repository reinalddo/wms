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
            id = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\SubGrupoArticulos\SubGrupoArticulos');
        $sth->execute(array($key));

        $subgrupoarticulo = $sth->fetch();

        $this->cve_sgpoart = $subgrupoarticulo->cve_sgpoart;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          s.id,
		      s.cve_sgpoart,
            s.cve_gpoart,
            s.des_sgpoart,
            g.des_gpoart,
            s.id_almacen
            FROM
            %s s
            left join c_gpoarticulo g on s.cve_gpoart= g.id
        WHERE
          s.id = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\SubGrupoArticulos\SubGrupoArticulos' );
      $sth->execute( array( $this->cve_sgpoart ) );

      $this->data = $sth->fetch();

    }

    function actualizarInputSubgrupoA( $data ) {
        $val = $data["get_option"];
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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\SubGrupoArticulos\SubGrupoArticulos' );
        $sth->execute( array( $val ) );

        return $sth->fetchAll();

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
	
    function getAll($id_almacen = "") {

      $sql_almacen = "";
      if($id_almacen)
        $sql_almacen = " AND id_almacen = '{$id_almacen}' ";

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
		  where Activo=1
      '.$sql_almacen;

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\SubGrupoArticulos\SubGrupoArticulos' );
        $sth->execute( array( $cve_sgpoart ) );

        return $sth->fetchAll();

    }	

    function save( $_post ) {

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          cve_sgpoart = :cve_sgpoart
        , des_sgpoart = :des_sgpoart
    , cve_gpoart = :cve_gpoart
    , id_almacen = :almacen
		
      ');

      $this->save = \db()->prepare($sql);

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':cve_sgpoart', $_post['cve_sgpoart'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_sgpoart', $_post['des_sgpoart'], \PDO::PARAM_STR );
    $this->save->bindValue( ':cve_gpoart', $_post['cve_gpoart'], \PDO::PARAM_STR );
    $this->save->bindValue( ':almacen', $_post['almacen'], \PDO::PARAM_STR );
      $this->save->execute();

    }

	function actualizarSubgrupoarticulos( $data ) {
      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          cve_sgpoart = ?
        , des_sgpoart = ?
		, cve_gpoart = ?
        WHERE
          cve_sgpoart = ? AND id_almacen = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['cve_sgpoart']
      , $data['des_sgpoart']
	  , $data['cve_gpoart']
      , $data['cve_sgpoart']
      , $data['almacen']
      ) );
    }

      function borrarSubgrupoArt( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          id = ? AND id_almacen = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['cve_sgpoart']
              ,$data['almacen']
          ) );
      }

	   function exist($clave, $id_almacen) {

      $sql = sprintf('
        SELECT
          *
        FROM
			 ' . self::TABLE . '
        WHERE
          cve_sgpoart = ? AND id_almacen = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );

      $sth->execute( array( $clave, $id_almacen ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }
	
		function recovery( $data ) {

          $sql = "UPDATE " . self::TABLE . "
		  SET Activo = 1 WHERE  id='".$data['id']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['id']
          ) );
    }
	
  public function inUse( $data ) {


      $sql = "SELECT clasificacion FROM `c_articulo` WHERE clasificacion = '".$data['id']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['clasificacion']) 
        return true;
    else
        return false;
  }  
	
  }
