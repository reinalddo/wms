<?php

namespace AjustesExistencias;

class AjustesExistencias {

    const TABLE = 'c_ubicacion';
    var $identifier;
    var $resultado;
    var $dataPasillos;
    var $dataRack;
    var $dataNivel;
    var $dataSeccion;
    var $dataUbicacion;

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

    private function loadSelects() {
        /*********************** PASILLO **************************/
        $sqlp = "SELECT c_ubicacion.cve_pasillo, c_ubicacion.cve_almac FROM " . self::TABLE . " WHERE c_ubicacion.cve_almac = '".$this->cve_almac."' GROUP BY c_ubicacion.cve_pasillo;";
        $rsp = mysqli_query(\db2(), $sqlp) or die("Error description: " . mysqli_error(\db2()));
        $arrp = array();
        while ($rowp = mysqli_fetch_array($rsp)) {
            $arrp[] = $rowp;
        }
        $this->dataPasillos = $arrp;
        /**********************************************************/

        /*********************** RACK **************************/
        $sqlr = "SELECT c_ubicacion.cve_rack, c_ubicacion.cve_almac FROM " . self::TABLE . " WHERE c_ubicacion.cve_almac = '".$this->cve_almac."' GROUP BY c_ubicacion.cve_rack;";
        $rsr = mysqli_query(\db2(), $sqlr) or die("Error description: " . mysqli_error(\db2()));
        $arrr = array();
        while ($rowr = mysqli_fetch_array($rsr)) {
            $arrr[] = $rowr;
        }
        $this->dataRack = $arrr;
        /**********************************************************/

        /*********************** NIVEL **************************/
        $sqln = "SELECT c_ubicacion.cve_nivel, c_ubicacion.cve_almac FROM " . self::TABLE . " WHERE c_ubicacion.cve_almac = '".$this->cve_almac."' GROUP BY c_ubicacion.cve_nivel;";
        $rsn = mysqli_query(\db2(), $sqln) or die("Error description: " . mysqli_error(\db2()));
        $arrn = array();
        while ($rown = mysqli_fetch_array($rsn)) {
            $arrn[] = $rown;
        }
        $this->dataNivel = $arrn;
        /**********************************************************/

        /*********************** SECCION **************************/
        $sqls = "SELECT c_ubicacion.Seccion, c_ubicacion.cve_almac FROM " . self::TABLE . " WHERE c_ubicacion.cve_almac = '".$this->cve_almac."' GROUP BY c_ubicacion.Seccion;";
        $rss = mysqli_query(\db2(), $sqls) or die("Error description: " . mysqli_error(\db2()));
        $arrs = array();
        while ($rows = mysqli_fetch_array($rss)) {
            $arrs[] = $rows;
        }
        $this->dataSeccion = $arrs;
        /**********************************************************/

        /*********************** UBICACION **************************/
        $sqlu = "SELECT c_ubicacion.idy_ubica, c_ubicacion.Ubicacion, c_ubicacion.cve_almac FROM " . self::TABLE . " WHERE c_ubicacion.cve_almac = '".$this->cve_almac."' GROUP BY c_ubicacion.Ubicacion;";
        $rsu = mysqli_query(\db2(), $sqlu) or die("Error description: " . mysqli_error(\db2()));
        $arru = array();
        while ($rowu = mysqli_fetch_array($rsu)) {
            $arru[] = $rowu;
        }
        $this->dataUbicacion = $arru;
        /**********************************************************/

    }

    function __getSelectList( $key ) {

        switch($key) {
            case 'cve_almac':
                $this->loadSelects();
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

    function saveDetalle( $data ) {
        try {
            $sql = mysqli_query(\db2(), "SELECT SPAD_AgregaArticulo(
              '".$data['idy_ubica']."'
            , '".$data['cve_articulo']."'              
		    , '".$data['cve_lote']."'
		    , '".$data['existencia']."'
		    , ''
		    , '".$data['cve_usuario']."'		        
		    );") or die('saveDetalle: '.mysqli_error(\db2()));
        } catch(PDOException $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    function LoadGrid( $data ) {
        $sql = "CALL SPAD_DameExistenciasDeUbicacion(
              '".$data['almacen']."'
            , '".$data['ubicacion']."'            
		    );";
        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
        $arr = array();

        while ($row = mysqli_fetch_array($rs)) {
            $arr[] = $row;
        }

        $this->LoadDetalleGrid = $arr;

    }

    function saveLoad( $data ) {
        try {
            $sql = mysqli_query(\db2(), "SELECT SPAD_ActualizaExistenciaPiezas(
              '".$data['idy_ubica']."'
            , '".$data['cve_articulo']."'              
		    , '".$data['cve_lote']."'
		    , '".$data['existencia']."'
		    , ''
		    , '".$data['cve_usuario']."'		        
		    );") or die(mysqli_error(\db2()));
        } catch(PDOException $e) {
            return 'ERROR: ' . $e->getMessage();
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
