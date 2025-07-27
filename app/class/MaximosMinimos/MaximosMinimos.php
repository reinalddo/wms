<?php

namespace MaximosMinimos;

class MaximosMinimos {

    const TABLE = 'c_articulo';
    var $identifier;
    var $resultado;

    public function __construct( $cve_articulo = false, $key = false ) {

        if( $cve_articulo ) {
            $this->cve_articulo = (int) $cve_articulo;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            cve_articulo
          FROM
            %s
          WHERE
            cve_articulo = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\MaximosMinimos\MaximosMinimos');
            $sth->execute(array($key));

            $cve_articulo = $sth->fetch();

            $this->cve_articulo = $cve_articulo->cve_articulo;

        }

    }

    function __get( $key ) {

        switch($key) {
            case 'cve_articulo':
                $this->save();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function __getArt( $key ) {

        switch($key) {
            case 'cve_articulo':
                $this->loadArt();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function save( $data ) {
        try {

            $sqlArt = "SELECT des_articulo FROM " . self::TABLE . " WHERE cve_articulo = '".$data['Cve_Articulo']."'";
            $rs = mysqli_query(\db2(), $sqlArt) or die("Error description: " . mysqli_error(\db2()));
            $row = mysqli_fetch_array($rs);

            $this->des_articulo = $row["des_articulo"];

            $sql = mysqli_query(\db2(), "CALL SPAD_DameMaxMinAlmacen(
              '".$data['Cve_Almac']."'
            , '".$data['Cve_Articulo']."'      
            );") or die(mysqli_error(\db2()));
            $arr = array();
            while ($row = mysqli_fetch_array($sql)) {
                $arr[] = $row;
            }
            $this->resultado = $arr;

        } catch(PDOException $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    function UpdateArt( $data ) {
        try {

            $sql = mysqli_query(\db2(), "Update	
              ts_ubicxart SET CapacidadMinima='".$data["min"]."',
              CapacidadMaxima='".$data["max"]."'
	          Where	
	          cve_articulo='".$data["cve_articulo"]."' And 
	          idy_ubica='".$data["idubica"]."'")
            or die(mysqli_error(\db2()));
            mysqli_fetch_array($sql);
        } catch(PDOException $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }
}
