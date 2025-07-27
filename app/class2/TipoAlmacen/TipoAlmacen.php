<?php

namespace TipoAlmacen;

class TipoAlmacen {

    const TABLE = 'tipo_almacen';
    var $identifier;

    public function __construct( $id = false, $key = false ) {

        if( $id ) {
            $this->id = (int) $id;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            id
          FROM
            %s
          WHERE
            id = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\TipoAlmacen\TipoAlmacen');
            $sth->execute(array($key));

            $TipoAlmacen = $sth->fetch();

            $this->id = $TipoAlmacen->id;

        }

    }

    private function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          id = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoAlmacen\TipoAlmacen' );
        $sth->execute( array( $this->id ) );

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
      //$sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoAlmacen\TipoAlmacen' );
      //$sth->execute( array( @clave_talmacen ) );
      $sth->execute();
      return $sth->fetchAll();
    }

    function __get( $key ) {

        switch($key) {
            case 'id':
            case 'desc_tipo_almacen':
			case 'clave_talmacen':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function save( $_post ) {

        if( !$_post['desc_tipo_almacen'] ) { throw new \ErrorException( 'desc_tipo_almacen is required.' ); }

        $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET       
          desc_tipo_almacen = :desc_tipo_almacen
		  ,clave_talmacen= :clave_talmacen
      ');

        $this->save = \db()->prepare($sql);

        $this->save->bindValue( ':desc_tipo_almacen', $_post['desc_tipo_almacen'], \PDO::PARAM_STR );
		$this->save->bindValue( ':clave_talmacen', $_post['clave_talmacen'], \PDO::PARAM_STR );

        $this->save->execute();

    }

    function borrarTipoAlmacen( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          id = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['id']
        ) );
    }

    function actualizarTipoAlmacen( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          desc_tipo_almacen = ?
        WHERE
          id = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['desc_tipo_almacen']
		   , $data['id']
		
		
        ) );
    }
	
	 function exist($clave_talmacen) {

      $sql = sprintf('
        SELECT
          *
        FROM
          ' . self::TABLE . '
		  WHERE
          clave_talmacen = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoAlmacen\TipoAlmacen' );
      $sth->execute( array( $clave_talmacen ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }

    function tieneAlmacen() {
      $sql = sprintf('
        SELECT
          cr.*
        FROM
          tipo_almacen r, c_almacenp cr
        WHERE
          cr.cve_talmacen = r.id 
          and cr.Activo = 1
          and r.id = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoAlmacen\TipoAlmacen' );
      $sth->execute( array( $this->id ) );

      $this->data = $sth->fetch();

    }

      function recovery( $data ) {

          $sql = "UPDATE tipo_almacen SET Activo = 1 WHERE  id='".$data['id']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['id']
          ) );
      }
}
