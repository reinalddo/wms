<?php

namespace GrupoArticulos;

class GrupoArticulos {

    const TABLE = 'c_gpoarticulo';
    var $identifier;
    var $cve_gpoart;

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
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\GrupoArticulos\GrupoArticulos');
            $sth->execute(array($key));

            $grupoarticulos = $sth->fetch();

            $this->cve_gpoart = $grupoarticulos->cve_gpoart;

        }

    }

    public function load() {

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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\GrupoArticulos\GrupoArticulos' );
        $sth->execute( array( $this->cve_gpoart ) );

        $this->data = $sth->fetch();

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
		  WHERE Activo=1
      '. $sql_almacen;

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\GrupoArticulos\GrupoArticulos' );
        $sth->execute( array( $cve_gpoart ) );

        return $sth->fetchAll();

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

        if( !$data['codigo'] ) { throw new \ErrorException( 'des_gpoart is required.' ); }
        if( !$data['descripcion'] ) { throw new \ErrorException( 'des_gpoart is required.' ); }
        //if( !$data['por_depcont'] ) { throw new \ErrorException( 'por_depcont is required.' ); }
        //if( !$data['por_depfical'] ) { throw new \ErrorException( 'por_depfical is required.' ); }

        $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          cve_gpoart = :cve_gpoart, 
          des_gpoart = :des_gpoart,
          id_almacen = :almacen
        ');

        $this->save = \db()->prepare($sql);

        /*$password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );*/
        $identifier = bin2hex(openssl_random_pseudo_bytes(10));

        $this->identifier = $identifier;

        //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
        $this->save->bindValue( ':cve_gpoart', $data['codigo'], \PDO::PARAM_STR );
        $this->save->bindValue( ':des_gpoart', $data['descripcion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':almacen', $data['almacen'], \PDO::PARAM_STR );
        //$this->save->bindValue( ':por_depcont', $data['por_depcont'], \PDO::PARAM_STR );
        //$this->save->bindValue( ':por_depfical', $data['por_depfical'], \PDO::PARAM_STR );

        $this->save->execute();

    }

    function actualizarGrupoArticulos( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          des_gpoart = ?
        WHERE
          cve_gpoart = ? AND id_almacen = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['descripcion']
        , $data['codigo']
        , $data['almacen']
        ) );
    }

    function borrarGrupoArticulos( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          cve_gpoart = ? AND id_almacen = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['cve_gpoart'],
            $data['almacen']
        ) );
    }

	function exist($clave, $id_almacen) {

      $sql = sprintf('
        SELECT
          *
        FROM
			 ' . self::TABLE . '
        WHERE
          cve_gpoart = ? AND id_almacen = ?
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

      $sql = "SELECT grupo FROM `c_articulo` WHERE grupo = '".$data['cve_gpoart']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['grupo']) 
        return true;
    else
        return false;
  }  
   
}
