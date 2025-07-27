<?php

namespace MotivoNoVentas;

class MotivoNoVentas {

    const TABLE = 'MotivosNoVenta';
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
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\MotivoNoVentas\MotivoNoVentas');
            $sth->execute(array($key));

            $MotivoNoVentas = $sth->fetch();

            $this->MOT_ID = $MotivoNoVentas->MOT_ID;

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
	  
       $sth->setFetchMode( \PDO::FETCH_CLASS, '\MotivoNoVentas\MotivoNoVentas' );
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
          IdMot = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\MotivoNoVentas\MotivoNoVentas' );
        $sth->execute( array( $this->IdMot ) );

        $this->data = $sth->fetch();

    }

    function __get( $key ) {

        switch($key) {
            case 'IdMot':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function save( $_post ) {

        if( !$_post['MOT_DESC'] ) { throw new \ErrorException( 'Full name is required.' ); }

        $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          Motivo = :MOT_DESC
		 , Clave = :Clave_motivo
     , Status = 1
      ');

        $this->save = \db()->prepare($sql);

        $this->save->bindValue( ':MOT_DESC', $_post['MOT_DESC'], \PDO::PARAM_STR );
    $this->save->bindValue( ':Clave_motivo', $_post['Clave_motivo'], \PDO::PARAM_STR );

        $this->save->execute();
    }

    function borrarMotivoNoVentas( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Status = 0
        WHERE
          IdMot = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['MOT_ID']
        ) );
    }

    function actualizarMotivoNoVentas( $_post ) {
		
        $sql = sprintf('
        UPDATE
          ' . self::TABLE . '
        SET
          Motivo = :MOT_DESC
		 where IdMot = :MOT_ID
      ');

        $this->save = \db()->prepare($sql);

        $this->save->bindValue( ':MOT_DESC', $_post['MOT_DESC'], \PDO::PARAM_STR );
    $this->save->bindValue( ':MOT_ID', $_post['MOT_ID'], \PDO::PARAM_STR );

         $this->save->execute();
    }
	
	function getMotivos($id_almacen = "") {

    $sql_almacen = "";
    if($id_almacen != "")
      $sql_almacen = " AND id_almacen = '{$id_almacen}' ";
          $sql = "
        SELECT
          *
        FROM
          motivos_devolucion
		where Activo = 1 {$sql_almacen}
      ";

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\MotivoNoVentas\MotivoNoVentas' );
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

  return false;
/*
      $sql = "SELECT clave_almacen FROM `c_almacen` WHERE clave_almacen ='".$data['MOT_ID']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['clave_almacen']) 
        return true;
    else
        return false;
*/
  }  

}
