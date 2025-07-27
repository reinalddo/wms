<?php

namespace TipoCaja;

class TipoCaja {

    const TABLE = 'c_tipocaja';
    var $identifier;

    public function __construct( $id_tipocaja = false, $key = false ) {

        if( $id_tipocaja ) {
            $this->id_tipocaja = (int) $id_tipocaja;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            id_tipocaja
          FROM
            %s
          WHERE
            id_tipocaja = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\TipoCaja\TipoCaja');
            $sth->execute(array($key));

            $tipcaja = $sth->fetch();

            $this->id_tipocaja = $tipcaja->id_tipocaja;

        }

    }

    private function load() {

        $sql = sprintf('
        SELECT 
			*
        FROM
			%s
        WHERE
			id_tipocaja = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoCaja\TipoCaja' );
        $sth->execute( array( $this->id_tipocaja ) );

        $this->data = $sth->fetch();

    }
    
    function __get( $key ) {

      switch($key) {
        case 'id_tipocaja':
        case 'clave':
        case 'descripcion':
        case 'peso':
        case 'largo':
        case 'alto':
        case 'ancho':
        case 'Packing':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoCaja\TipoCaja' );
        $sth->execute( array( $cve_tipcaja ) );

        return $sth->fetchAll();

    }	

    function save( $_post ) {

        $sql = "INSERT INTO c_tipocaja (clave, descripcion, largo, alto, ancho, peso, packing, Activo) 
        VALUES ('".strip_tags($_post['clave'])."','".strip_tags($_post['descripcion'])."','".strip_tags($_post['largo'])."','".strip_tags($_post['alto'])."', '".strip_tags($_post['ancho'])."', '".strip_tags($_post['peso'])."', '".strip_tags($_post['empaque'])."', '1')";

        $sth = \db()->prepare($sql);
        return $sth->execute();

    }

    function borrarTipoCaja( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          id_tipocaja = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['id_tipocaja']
        ) );
    }

    function actualizarTipCaja( $data ) {
        $sql = "
        UPDATE
          " . self::TABLE . "
          SET
            descripcion = '".$data['descripcion']."', 
            largo = '".$data['largo']."', 
            alto = '".$data['alto']."',
            ancho = '".$data['ancho']."',
            peso = '".$data['peso']."',
            Packing = '".$data['empaque']."'
          WHERE
          clave = '".$data['clave']."'";		  
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
    }
	
	function exist($clave) {

      $sql = sprintf("
        SELECT
          *
        FROM
          " . self::TABLE . "
		  WHERE
          clave = ?
      ",
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoCaja\TipoCaja' );
      $sth->execute( array( $clave ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }
	
		function recovery( $data ) {

          $sql = "UPDATE " . self::TABLE . " SET Activo = 1 WHERE  id_tipocaja='".$data['id_tipocaja']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['id_tipocaja']
          ) );
    }

  public function inUse( $data ) {

      $sql = "SELECT tipo_caja FROM `c_articulo` WHERE tipo_caja ='".$data['clave']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['tipo_caja']) 
        return true;
    else
        return false;
  }  

}

