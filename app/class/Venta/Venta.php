<?php

  namespace Venta;

  class Venta {
    const TABLE = 'Venta';
    //const TABLE_AGENTES     = 'Rel_Ruta_Agentes';

    var $identifier;

    public function __construct( $Id = false, $key = false ) {

      if( $Id ) {
        $this->Id = (int) $Id;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            Id
          FROM
            %s
          WHERE
            Id = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Venta\Venta');
        $sth->execute(array($key));

        $venta = $sth->fetch();

        $this->Id = $venta->Id;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          Id = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Venta\Venta' );
      $sth->execute( array( $this->Id ) );

      $this->data = $sth->fetch();

    }

    function getAll($almacen = "") {

        $sqlAlmacen = "";
        if($almacen)
          $sqlAlmacen = " AND cve_almacenp = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')";

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . ' where Activo = "1" '.$sqlAlmacen.'
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Venta\Venta' );
        @$sth->execute( array( ID_Ruta ) );

        return $sth->fetchAll();

    }

    function getAllDiaO($almacen = "") {

        //$sql = 'SELECT DISTINCT DiaO FROM Venta WHERE IdEmpresa = "'.$almacen.'"';
        $sql = 'SELECT DISTINCT * FROM (
                SELECT DISTINCT DiaO FROM Venta WHERE IdEmpresa = "'.$almacen.'"
                UNION 
                SELECT DISTINCT DiaO FROM th_pedido WHERE cve_almac = (SELECT id FROM c_almacenp WHERE clave = "'.$almacen.'")
                ) diao 
                ORDER BY diao.DiaO DESC';
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Venta\Venta' );
        @$sth->execute( array( Id ) );

        return $sth->fetchAll();

    }

	function getLastInsertId(){
          $sql = '
        SELECT MAX(Id) as ID        
        FROM
          ' . self::TABLE . '
      ';

          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Venta\Venta' );
          @$sth->execute(array( ID_Ruta ) );
          return $sth->fetch();
      }
	
    function __get( $key ) {

      switch($key) {
        case 'Id':
        case 'cve_ruta':
        case 'descripcion':
        case 'status':
        case 'cve_almacenp':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

}
