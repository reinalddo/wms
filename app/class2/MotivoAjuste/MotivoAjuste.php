<?php

namespace MotivoAjuste;

class MotivoAjuste {

    const TABLE = 'c_motivo';
    var $identifier;

    public function __construct( $MOT_ID = false, $key = false ) {

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
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\MotivoDevolucion\MotivoDevolucion');
            $sth->execute(array($key));

            $MotivoDevolucion = $sth->fetch();

            $this->id = $MotivoDevolucion->id;

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
        $sth->execute( array( $this->id ) );

        $this->data = $sth->fetch();

    }

    function __get( $key ) {

        switch($key) {
            case 'id':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function save( $_post ) {

        if( !$_post['Des_Motivo'] ) { throw new \ErrorException( 'Full name is required.' ); }

        $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          Tipo_Cat = :cierre_incidencia, 
          Des_Motivo = :Des_Motivo,
          Activo = 1
          
      ');

        $this->save = \db()->prepare($sql);

        $this->save->bindValue( ':cierre_incidencia', $_post['cierre_incidencia'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Des_Motivo', $_post['Des_Motivo'], \PDO::PARAM_STR );

        $this->save->execute();
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
            $data['id']
        ) );
    }

    function actualizarMotivoDevolucion( $_post ) {
		
        $sql = sprintf('
        UPDATE
          ' . self::TABLE . '
        SET
           Des_Motivo = :Des_Motivo
          ,Tipo_Cat = :cierre_incidencia
          ,dev_proveedor = :check_devolucion
		 where id = :id
      ');

        $this->save = \db()->prepare($sql);

        $this->save->bindValue( ':cierre_incidencia', $_post['cierre_incidencia'], \PDO::PARAM_STR );
        $this->save->bindValue( ':check_devolucion', $_post['check_devolucion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Des_Motivo', $_post['Des_Motivo'], \PDO::PARAM_STR );
		$this->save->bindValue( ':id', $_post['id'], \PDO::PARAM_STR );

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
          $sth->execute( array( id ) );

          return $sth->fetchAll();

      }
	  
	      function recovery( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 1
        WHERE
          id = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['id']
        ) );
    }

public function inUse( $data ) {

      $sql = "SELECT clave_almacen FROM `c_almacen` WHERE clave_almacen ='".$data['id']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['clave_almacen']) 
        return true;
    else
        return false;
  }  

}
