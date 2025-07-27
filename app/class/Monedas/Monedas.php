<?php

namespace Monedas;

class Monedas {

    const TABLE = 'c_monedas';
    var $identifier;
    var $Cve_Moneda;

    public function __construct( $Cve_Moneda = false, $key = false ) {

        if( $Cve_Moneda ) {
            $this->Cve_Moneda = $Cve_Moneda;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            Cve_Moneda
          FROM
            %s
          WHERE
            Cve_Moneda = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Monedas\Monedas');
            $sth->execute(array($key));

            $Monedas = $sth->fetch();

            $this->Cve_Moneda = $Monedas->Cve_Moneda;

        }

    }

    public function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          Cve_Moneda = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Monedas\Monedas' );
        $sth->execute( array( $this->Cve_Moneda ) );

        $this->data = $sth->fetch();

    }

    function getAll() {
/*
      $sql_almacen = "";
      if($id_almacen)
        $sql_almacen = " AND id_almacen = '{$id_almacen}' ";
*/
        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
		  WHERE Activo=1
      '. $sql_almacen;

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Monedas\Monedas' );
        $sth->execute( array( $Cve_Moneda ) );

        return $sth->fetchAll();

    }

    function __get( $key ) {

        switch($key) {
            case 'Cve_Moneda':
            case 'des_gpoart':
            case 'por_depcont':
            case 'Cve_Moneda':
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
          Clave = :Cve_Moneda, 
          Forma = :des_gpoart,
          Status = 1,
          IdEmpresa = :almacen
        ');

        $this->save = \db()->prepare($sql);

        /*$password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );*/
        $identifier = bin2hex(openssl_random_pseudo_bytes(10));

        $this->identifier = $identifier;

        //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
        $this->save->bindValue( ':Cve_Moneda', $data['codigo'], \PDO::PARAM_STR );
        $this->save->bindValue( ':des_gpoart', $data['descripcion'], \PDO::PARAM_STR );
        $this->save->bindValue( ':almacen', $data['cve_almacen'], \PDO::PARAM_STR );
        //$this->save->bindValue( ':por_depcont', $data['por_depcont'], \PDO::PARAM_STR );
        //$this->save->bindValue( ':por_depfical', $data['por_depfical'], \PDO::PARAM_STR );

        $this->save->execute();

    }

    function actualizarMonedas( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Forma = ?
        WHERE
          Clave = ? AND IdEmpresa = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['descripcion']
        , $data['codigo']
        , $data['cve_almacen']
        ) );
    }

    function borrarMonedas( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Status = 0
        WHERE
          Clave = ? AND IdEmpresa = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['id'],
            $data['cve_almacen']
        ) );
    }

	function exist($clave, $id_almacen) {

      $sql = sprintf('
        SELECT
          *
        FROM
			 ' . self::TABLE . '
        WHERE
          Cve_Moneda = ? AND id_almacen = ?
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


  return false;
/*
      $sql = "SELECT grupo FROM `c_articulo` WHERE grupo = '".$data['Cve_Moneda']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['grupo']) 
        return true;
    else
        return false;
*/
  }  
   
}
