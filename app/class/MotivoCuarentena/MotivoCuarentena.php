<?php

namespace MotivoCuarentena;

class MotivoCuarentena {

    const TABLE = 'c_motivo';
    var $identifier;

    public function __construct( $MOT_ID = false, $key = false ) {

        if( $MOT_ID ) {
            $this->MOT_ID = (int) $MOT_ID;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            MOT_ID
          FROM
            %s
          WHERE
            MOT_ID = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\MotivoDevolucion\MotivoDevolucion');
            $sth->execute(array($key));

            $MotivoDevolucion = $sth->fetch();

            $this->MOT_ID = $MotivoDevolucion->MOT_ID;

        }

    }
	
	function exist($Clave_motivo) {

      $sql = sprintf('
        SELECT
          *
        FROM
          motivos_devolucion

		  WHERE
          Clave_motivo = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
       $sth->setFetchMode( \PDO::FETCH_CLASS, '\MotivoDevolucion\MotivoDevolucion' );
      $sth->execute( array( $Clave_motivo ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\MotivoDevolucion\MotivoDevolucion' );
        $sth->execute( array( $this->MOT_ID ) );

        $this->data = $sth->fetch();

    }

    function __get( $key ) {

        switch($key) {
            case 'MOT_ID':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function save( $_post ) {

        if( !$_post['Des_Motivo'] ) { throw new \ErrorException( 'Description is required.' ); }
        if( !$_post['tipo'] ) { throw new \ErrorException( 'Type is required.' ); }

        $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          Tipo_Cat = :tipo,    
          Des_Motivo = :Des_Motivo,
          Activo = 1
          
      ');

        $this->save = \db()->prepare($sql);

		    $this->save->bindValue( ':Des_Motivo', $_post['Des_Motivo'], \PDO::PARAM_STR );
        $this->save->bindValue( ':tipo', $_post['tipo'], \PDO::PARAM_STR );

        $this->save->execute();
    }

    function getAll() {

        $sql = '

        SELECT

          *

        FROM

          ' . self::TABLE . '
      WHERE Activo=1 AND Tipo_Cat = "Q"';



        $sth = \db()->prepare( $sql );

        $sth->setFetchMode( \PDO::FETCH_CLASS, '\AlmacenP\AlmacenP' );

        $sth->execute();



        return $sth->fetchAll();



    }

    function borrarMotivoDevol( $data ) {
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
            $data['MOT_ID']
        ) );
    }

    function actualizarMotivoDevolucion( $_post ) {
      $id = $_post['id'];
      $Des_Motivo = $_post['Des_Motivo'];
      $Tipo_Cat = $_post['tipo'];
        $sql = sprintf('
        UPDATE
          ' . self::TABLE . '
        SET
          Des_Motivo = "'.$Des_Motivo.'",
          Tipo_Cat = "'.$Tipo_Cat.'"
		      where id = '.$id.'
      ');

        $this->save = \db()->prepare($sql);

        //$this->save->bindValue( ':Des_Motivo', $_post['Des_Motivo'], \PDO::PARAM_STR );
        //$this->save->bindValue( ':Tipo_Cat', $_post['tipo'], \PDO::PARAM_STR );
		    //$this->save->bindValue( ':id', $_post['id'], \PDO::PARAM_INT );

         $this->save->execute();
    }
	
	function getMotivos() {

          $sql = '
        SELECT
          *
        FROM
          motivos_devolucion
		where Activo = 1
      ';

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\MotivoDevolucion\MotivoDevolucion' );
          $sth->execute( array( MOT_ID ) );

          return $sth->fetchAll();

      }
	  
	      function recovery( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 1
        WHERE
          MOT_ID = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['MOT_ID']
        ) );
    }

public function inUse( $data ) {

      $sql = "SELECT clave_almacen FROM `c_almacen` WHERE clave_almacen ='".$data['MOT_ID']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['clave_almacen']) 
        return true;
    else
        return false;
  }  

}
