<?php

namespace Protocolos;

class Protocolos {

    const TABLE = 't_protocolo';
    var $identifier;

    public function __construct( $ID_Protocolo = false, $key = false ) {

        if( $ID_Protocolo ) {
            $this->$ID_Protocolo = (int) $ID_Protocolo;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            ID_Protocolo
          FROM
            %s
          WHERE
            ID_Protocolo = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Protocolos\Protocolos');
            $sth->execute(array($key));

            $protocolo = $sth->fetch();

            $this->ID_Protocolo = $protocolo->ID_Protocolo;

        }

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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Protocolos\Protocolos' );
        @$sth->execute( array( ID_Protocolo ) );

        return $sth->fetchAll();

    }

    private function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          ID_Protocolo = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Protocolos\Protocolos' );
        $sth->execute( array( $this->ID_Protocolo ) );

        $this->data = $sth->fetch();

    }

    function __get( $key ) {

        switch($key) {
            case 'ID_Protocolo':
            case 'descripcion':
            case 'FOLIO':
            case 'Activo':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function save( $_post ) {

        $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
		ID_Protocolo = :ID_Protocolo
         ,descripcion = :descripcion
        , FOLIO = :FOLIO
      ');

        $this->save = \db()->prepare($sql);

        /*$password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );*/
        //$identifier = bin2hex(openssl_random_pseudo_bytes(10));

        $this->identifier = $identifier;

        //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
        $this->save->bindValue( ':descripcion', $_post['descripcion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':FOLIO', $_post['FOLIO'], \PDO::PARAM_STR );
		$this->save->bindValue( ':ID_Protocolo', $_post['ID_Protocolo'], \PDO::PARAM_STR );

        $this->save->execute();

    }

    function actualizarProtocolos( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          descripcion = ?
        , FOLIO = ?
        WHERE
          ID_Protocolo = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
          $data['descripcion']
        , $data['FOLIO']
        , $data['ID_Protocolo']
        ) );
    }
	
    function borrarProtocolo( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          ID_Protocolo = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
          $data['ID_Protocolo']
        ) );
    }	

function exist($clave) {
      $sql = sprintf('
        SELECT
          *
        FROM
          t_protocolo
        WHERE
          ID_Protocolo = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
    
      $sth->execute( array( $clave ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }
	
	 function tieneOrden() {
      $sql = sprintf('
        SELECT
          r.*
        FROM
          t_protocolo r, th_aduana cr
        WHERE
          cr.ID_Protocolo = r.ID_Protocolo 
		  and r.ID_Protocolo = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
      $sth->execute( array( $this->ID_Protocolo ) );

      $this->data = $sth->fetch();

    }
	
	function recovery( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 1
        WHERE
          ID_Protocolo = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
          $data['ID_Protocolo']
        ) );
    }
}
