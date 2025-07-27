<?php

namespace GrupoServicios;

class GrupoServicios {

    const TABLE = 'c_gposervicios';
    var $identifier;
    var $Cve_GpoServicio;

    public function __construct( $Cve_GpoServicio = false, $key = false ) {

        if( $Cve_GpoServicio ) {
            $this->Cve_GpoServicio = (int) $Cve_GpoServicio;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            Cve_GpoServicio
          FROM
            %s
          WHERE
            Cve_GpoServicio = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\GrupoServicios\GrupoServicios');
            $sth->execute(array($key));

            $gruposervicios = $sth->fetch();

            $this->Cve_GpoServicio = $gruposervicios->Cve_GpoServicio;

        }

    }

    public function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          Cve_GpoServicio = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\GrupoServicios\GrupoServicios' );
        $sth->execute( array( $this->Cve_GpoServicio ) );

        $this->data = $sth->fetch();

    }

    function getAll($id_almacen = "") {

      $sql_almacen = "";
      //if($id_almacen)
      //  $sql_almacen = " AND id_almacen = '{$id_almacen}' ";

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
		  WHERE Activo=1
      '. $sql_almacen;

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\GrupoServicios\GrupoServicios' );
        $sth->execute( array( $Cve_GpoServicio ) );

        return $sth->fetchAll();

    }

    function __get( $key ) {

        switch($key) {
            case 'Cve_GpoServicio':
            case 'Des_GpoServicio':
            case 'por_depcont':
            case 'por_depfical':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function save( $data ) {

        if( !$data['codigo'] ) { throw new \ErrorException( 'Des_GpoServicio is required.' ); }
        if( !$data['descripcion'] ) { throw new \ErrorException( 'Des_GpoServicio is required.' ); }
        //if( !$data['por_depcont'] ) { throw new \ErrorException( 'por_depcont is required.' ); }
        //if( !$data['por_depfical'] ) { throw new \ErrorException( 'por_depfical is required.' ); }

        $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          Cve_GpoServicio = :Cve_GpoServicio, 
          Des_GpoServicio = :Des_GpoServicio
        ');

        $this->save = \db()->prepare($sql);

        /*$password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );*/
        $identifier = bin2hex(openssl_random_pseudo_bytes(10));

        $this->identifier = $identifier;

        //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
        $this->save->bindValue( ':Cve_GpoServicio', $data['codigo'], \PDO::PARAM_STR );
        $this->save->bindValue( ':Des_GpoServicio', $data['descripcion'], \PDO::PARAM_STR );
        //$this->save->bindValue( ':almacen', $data['almacen'], \PDO::PARAM_STR );
        //$this->save->bindValue( ':por_depcont', $data['por_depcont'], \PDO::PARAM_STR );
        //$this->save->bindValue( ':por_depfical', $data['por_depfical'], \PDO::PARAM_STR );

        $this->save->execute();

    }

    function actualizarGrupoServicios( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Des_GpoServicio = ?
        WHERE
          Cve_GpoServicio = ? 
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['descripcion']
        , $data['codigo']
        ) );
    }

    function borrarGrupoServicios( $data ) {
        $sql = '
        DELETE FROM 
          ' . self::TABLE . '
        WHERE
          Cve_GpoServicio = ? 
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['Cve_GpoServicio']
        ) );
    }

	function exist($clave) {

      $sql = sprintf('
        SELECT
          *
        FROM
			 ' . self::TABLE . '
        WHERE
          Cve_GpoServicio = ? 
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
	
	function recovery( $data ) {

          $sql = "UPDATE " . self::TABLE . "
		  SET Activo = 1 WHERE  id='".$data['id']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['id']
          ) );
    }

 public function inUse( $data ) {
/*
      $sql = "SELECT Cve_GpoServicio FROM `c_gposervicios` WHERE Cve_GpoServicio = '".$data['Cve_GpoServicio']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['Cve_GpoServicio']) 
        return true;
    else
  */
        return false;
  }  
   
}
