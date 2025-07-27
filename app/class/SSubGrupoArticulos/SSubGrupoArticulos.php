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
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\SSubGrupoArticulos\SSubGrupoArticulos');
        $sth->execute(array($key));

        $ssubgrupoarticulo = $sth->fetch();

        $this->cve_ssgpoart = $ssubgrupoarticulo->cve_ssgpoart;

      }

    }

    private function load() {

      $sql = sprintf('	  
		SELECT
            ss.id,
            ss.cve_ssgpoart,
            ss.cve_sgpoart,
            ss.des_ssgpoart,
            ss.Opcinal,
            ss.Activo,
            s.des_sgpoart
            FROM
            %s ss
            LEFT JOIN c_sgpoarticulo s ON ss.cve_sgpoart = s.id
        WHERE
          ss.id = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\SSubGrupoArticulos\SSubGrupoArticulos' );
      $sth->execute( array( $this->cve_ssgpoart ) );

      $this->data = $sth->fetch();

    }

    function __get( $key ) {

      switch($key) {
        case 'cve_ssgpoart':
        case 'cve_sgpoart':
        case 'des_ssgpoart':
        case 'Activo':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }
	
    function getAll($id_almacen = "") {

      $sql_almacen = "";
      //if($id_almacen)
      //  $sql_almacen = " AND id_almacen = '{$id_almacen}' ";
        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE. '
		  where Activo=1
        '.$sql_almacen;

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\SSubGrupoArticulos\SSubGrupoArticulos' );
        @$sth->execute( array( cve_ssgpoart ) );

        return $sth->fetchAll();

    }

      function actualizarInputSSubgrupoA( $data ) {
          $val = $data["get_option"];
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
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\SSubGrupoArticulos\SSubGrupoArticulos' );
          $sth->execute( array( $val ) );

          return $sth->fetchAll();

      }

    function save( $data ) {

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          cve_ssgpoart = :cve_ssgpoart
        , des_ssgpoart = :des_ssgpoart
		    , cve_sgpoart  = :cve_sgpoart
        , id_almacen = :almacen
      ');

      $this->save = \db()->prepare($sql);

      /*$password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );*/
      //$identifier = bin2hex(openssl_random_pseudo_bytes(10));

      //$this->identifier = $identifier;

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':cve_ssgpoart', $data['cve_ssgpoart'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_ssgpoart', $data['des_ssgpoart'], \PDO::PARAM_STR );
  	  $this->save->bindValue( ':cve_sgpoart', $data['cve_sgpoart'], \PDO::PARAM_STR );
      $this->save->bindValue( ':almacen', $data['almacen'], \PDO::PARAM_STR );

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
        WHERE
          cve_ssgpoart = ? AND id_almacen = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['cve_sgpoart']
      , $data['des_ssgpoart']
      , $data['cve_ssgpoart']
      , $data['almacen']
      ) );
    }

      function borrarSSubgrupoArt( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          cve_ssgpoart = ? AND id_almacen = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['cve_ssgpoart']
              , $data['almacen']
          ) );
      }



	  	   function exist($clave, $id_almacen) {

      $sql = sprintf('
        SELECT
          *
        FROM
			 ' . self::TABLE . '
        WHERE
          cve_ssgpoart = ? AND id_almacen = ?
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

    function inUse( $data ) {

 
      $sql = "SELECT cve_ssgpo FROM `c_articulo` WHERE cve_ssgpo = '".$data['cve_ssgpoart']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['cve_ssgpo']) 
        return true;
    else
        return false;
  }

  }

