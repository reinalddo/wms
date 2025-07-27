<?php

namespace Caracteristicas;

class Caracteristicas {

    const TABLE = 'c_tipo_car';
    const TABLE2 = 'c_caracteristicas';
    
    var $identifier;

    public function __construct( $Id_Tipo_car = false, $key = false ) {

        if( $Id_Tipo_car ) {
            $this->Id_Tipo_car = (int) $Id_Tipo_car;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            Id_Tipo_car
          FROM
            %s
          WHERE
            Id_Tipo_car = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Caracteristicas\Caracteristicas');
            $sth->execute(array($key));

            $Caracteristicas = $sth->fetch();

            $this->Id_Tipo_car = $Caracteristicas->Id_Tipo_car;

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
	  
       $sth->setFetchMode( \PDO::FETCH_CLASS, '\Caracteristicas\Caracteristicas' );
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
          Id_Tipo_car = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Caracteristicas\Caracteristicas' );
        $sth->execute( array( $this->Id_Tipo_car ) );

        $this->data = $sth->fetch();

    }

    private function load_caract() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          Id_Carac = ?
      ',
            self::TABLE2
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Caracteristicas\Caracteristicas' );
        $sth->execute( array( $this->Id_Carac ) );

        $this->data = $sth->fetch();

    }

    function __get( $key ) {

        switch($key) {
            case 'Id_Tipo_car':
                $this->load();
                return @$this->data->$key;
            case 'Id_Carac':
                $this->load_caract();
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
          TipoCar_Desc = :descripcion
      ');

        $this->save = \db()->prepare($sql);

		    $this->save->bindValue( ':descripcion', $_post['descripcion'], \PDO::PARAM_STR );

        $this->save->execute();
    }

    function save_caracteristica( $_post ) {

        $sql = sprintf('
        INSERT INTO
          ' . self::TABLE2 . '
        SET
          Cve_Carac = :clave,
          Des_Carac = :descripcion,
          Id_Tipo_car = :tipo
      ');

        $this->save = \db()->prepare($sql);

        $this->save->bindValue( ':clave', $_post['clave'], \PDO::PARAM_STR );
        $this->save->bindValue( ':descripcion', $_post['descripcion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':tipo', $_post['tipo'], \PDO::PARAM_STR );

        $this->save->execute();
    }

    function borrarTipoCaracteristica( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          Id_Tipo_car = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['id']
        ) );
    }

    function borrarCaracteristica( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE2 . '
        SET
          Activo = 0
        WHERE
          Id_Carac = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['id']
        ) );
    }

    function actualizarCaracteristica( $_post ) {
    
        $sql = sprintf('
        UPDATE
          ' . self::TABLE2 . '
        SET
          Cve_Carac = :clave,
          Id_Tipo_car = :tipo,
          Des_Carac = :descripcion
        where Id_Carac = :id
      ');

        $this->save = \db()->prepare($sql);

        $this->save->bindValue( ':clave', $_post['clave'], \PDO::PARAM_STR );
        $this->save->bindValue( ':tipo', $_post['tipo'], \PDO::PARAM_STR );
        $this->save->bindValue( ':descripcion', $_post['descripcion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':id', $_post['id'], \PDO::PARAM_STR );

         $this->save->execute();
    }

    function actualizarTipoCaracteristica( $_post ) {
		
        $sql = sprintf('
        UPDATE
          ' . self::TABLE . '
        SET
          TipoCar_Desc = :descripcion
		    where Id_Tipo_car = :id
      ');

        $this->save = \db()->prepare($sql);

        $this->save->bindValue( ':descripcion', $_post['descripcion'], \PDO::PARAM_STR );
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
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Caracteristicas\Caracteristicas' );
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

      $sql = "SELECT Id_Carac FROM c_caracteristicas WHERE Id_Tipo_car ='".$data['id']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['Id_Carac']) 
        return true;
    else
        return false;
  }  

}
