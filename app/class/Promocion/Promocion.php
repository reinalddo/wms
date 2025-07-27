<?php

namespace Promocion;

class Promocion {

    const TABLE = 't_artcompuesto';
    var $identifier;
    var $resultado;

    public function __construct( $IDpromo = false, $key = false ) {

        if( $IDpromo ) {
            $this->IDpromo = (int) $IDpromo;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            IDpromo
          FROM
            %s
          WHERE
            IDpromo = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Promocion\Promocion');
            $sth->execute(array($key));

            $IDpromo = $sth->fetch();

            $this->IDpromo = $IDpromo->IDpromo;

        }

    }

    function __get( $key ) {

        switch($key) {
            case 'IDpromo':
                $this->save();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    private function loadDetalleArt() {

        $sql = "SELECT * FROM " . self::TABLE . " WHERE Cve_Articulo = '".$this->IDpromo."'";
        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
        $arr = array();

        while ($row = mysqli_fetch_array($rs)) {
            $arr[] = $row;
        }

        $this->dataDetalleArt = $arr;

    }

    function __getDetalleArt( $key ) {

        switch($key) {
            case 'IDpromo':
                $this->loadDetalleArt();
                return @$this->dataDetalleArt->$key;
            default:
                return $this->key;
        }

    }

    function save( $data ) {
		try {
            $sql = mysqli_query(\db2(), "SELECT SPAD_AgregaArticuloPromocion(
		      '".$data['Cve_Articulo']."'
		    , '".$data['Cve_ArtComponente']."'
		    , '".$data['Cantidad']."'   
		    );") or die(mysqli_error(\db2()));
            $row = mysqli_fetch_array(\db2(),$sql);
            $this->resultado = $row[0];
        } catch(PDOException $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }
}
