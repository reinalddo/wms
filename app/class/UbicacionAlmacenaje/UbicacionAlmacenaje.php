<?php

namespace UbicacionAlmacenaje;

class UbicacionAlmacenaje {

    const TABLE = 'c_ubicacion';
    var $identifier;
    var $dataNiveles;
    var $dataSecciones;
    var $dataUbicaciones;
    var $dataSection;


    public function __construct( $idy_ubica = false, $key = false ) {

        if( $idy_ubica ) {
            $this->idy_ubica = (int) $idy_ubica;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            idy_ubica
          FROM
            %s
          WHERE
            idy_ubica = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\UbicacionAlmacenaje\UbicacionAlmacenaje');
            $sth->execute(array($key));

            $idy_ubica = $sth->fetch();

            $this->idy_ubica = $idy_ubica->idy_ubica;

        }

    }

    function Ubicaciones( $_post ) {

        /*********************** RACK / PASILLO **************************/
        $sqlS = "SELECT c_ubicacion.idy_ubica, c_ubicacion.cve_almac, c_ubicacion.cve_nivel, c_ubicacion.Seccion, c_ubicacion.Ubicacion, c_ubicacion.Activo FROM c_ubicacion WHERE c_ubicacion.cve_almac = '".$_post['cve_almac']."' AND c_ubicacion.Seccion = '".$_post['Seccion']."' AND c_ubicacion.cve_nivel = '".$_post['cve_nivel']."' AND c_ubicacion.Activo = '1';";
        $rsS = mysqli_query(\db2(), $sqlS) or die("Error description: " . mysqli_error(\db2()));
        $row_cntS = mysqli_num_rows($rsS);
        $arrS = array();
        while ($rowS = mysqli_fetch_array($rsS)) {
            $arrS[] = $rowS;
        }
        $this->dataSection = $arrS;
    }

    function loadUbica() {

	    $sql = sprintf('
        SELECT
          u.*,
          ap.id as id_ap,
          a.des_almac as zona
        FROM
          %s u
        LEFT JOIN  c_almacen a ON u.cve_almac = a.cve_almac
        LEFT JOIN  c_almacenp ap ON a.cve_almacenp = ap.id
        WHERE
          idy_ubica = ?
      ',
        self::TABLE
      );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\UbicacionAlmacenaje\UbicacionAlmacenaje' );
        $sth->execute( array( $this->idy_ubica ) );

        $this->data = $sth->fetch();
    }

    private function load() {

        /*********************** RACK / PASILLO **************************/
        $sql = "SELECT c_ubicacion.cve_rack, c_ubicacion.cve_pasillo FROM c_ubicacion WHERE c_ubicacion.cve_almac = '".$this->cve_almac."' AND c_ubicacion.Activo = '1' GROUP BY c_ubicacion.cve_almac;";
        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
        $row = mysqli_fetch_array($rs);
        $arr = $row;
        $this->data = $arr;

            /*********************** NIVELES **************************/
            $sql1 = "SELECT c_ubicacion.cve_almac, c_ubicacion.cve_nivel, c_ubicacion.cve_rack, c_ubicacion.Ubicacion FROM c_ubicacion WHERE c_ubicacion.cve_almac = '".$this->cve_almac."' GROUP BY c_ubicacion.cve_nivel;";
            $rs1 = mysqli_query(\db2(), $sql1) or die("Error description: " . mysqli_error(\db2()));
            $row_cnt = mysqli_num_rows($rs1);
            $arr1 = array();
            while ($row1 = mysqli_fetch_array($rs1)) {
                $arr1[] = $row1;
            }
            $this->dataNiveles = $arr1;
            $this->NumNiveles = $row_cnt;
            /**********************************************************/

                /*********************** SECCIONES **************************/
                $sql2 = "SELECT c_ubicacion.cve_almac, c_ubicacion.cve_nivel, c_ubicacion.Seccion, c_ubicacion.Activo FROM c_ubicacion WHERE c_ubicacion.cve_almac = '".$this->cve_almac."' AND c_ubicacion.Activo = '1' GROUP BY c_ubicacion.Seccion;";
                $rs2 = mysqli_query(\db2(), $sql2) or die("Error description: " . mysqli_error(\db2()));
                $row_cnt2 = mysqli_num_rows($rs2);
                $arr2 = array();
                while ($row2 = mysqli_fetch_array($rs2)) {
                    $arr2[] = $row2;
                }
                $this->dataSecciones = $arr2;
                $this->NumSecciones = $row_cnt2;
                /**********************************************************/

                /*********************** UBICACIONES **************************/
                $arr3 = array();
                foreach ($this->dataSecciones as $seccion) {
                    $valor = $seccion["Seccion"];
                    $valor2 = $seccion["cve_nivel"];
                    $sql3 = "SELECT c_ubicacion.cve_almac, c_ubicacion.Ubicacion, c_ubicacion.Seccion, c_ubicacion.cve_nivel, c_ubicacion.Activo FROM c_ubicacion WHERE c_ubicacion.cve_almac = '" . $this->cve_almac . "' AND c_ubicacion.Seccion = '" . $valor . "' AND c_ubicacion.cve_nivel = '" . $valor2 . "' AND c_ubicacion.Activo = '1';";
                    $rs3 = mysqli_query(\db2(), $sql3) or die("Error description: " . mysqli_error(\db2()));
                    while ($row3 = mysqli_fetch_array($rs3)) {
                        $arr3[] = $row3;
                    }
                    $this->dataUbicaciones = $arr3;
                }
                /**********************************************************/
    }

    function __getLoadUbica( $key ) {

        switch($key) {
            case 'idy_ubica':
                $this->loadUbica();
				return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function __get( $key ) {

        switch($key) {
            case 'cve_almac':
                $this->load();
            default:
                return $this->key;
        }

    }

    function __getUbicacion( $key ) {

        switch($key) {
            case 'cve_almac':
                $this->Ubicaciones();
            default:
                return $this->key;
        }

    }

    public static function detectPad($str){
        return substr_count($str, "0") === 0 ? strlen($str) : substr_count($str, "0");
    }

    public static function QuitarAcentosYCaracteresEspeciales($texto)
    {
        $texto = str_replace("´", "", $texto);$texto = str_replace("`", "", $texto);

        $texto = str_replace("á", "a", $texto);$texto = str_replace("é", "e", $texto);
        $texto = str_replace("í", "i", $texto);$texto = str_replace("ó", "o", $texto);
        $texto = str_replace("ú", "u", $texto);

        $texto = str_replace("à", "a", $texto);$texto = str_replace("è", "e", $texto);
        $texto = str_replace("ì", "i", $texto);$texto = str_replace("ò", "o", $texto);
        $texto = str_replace("ù", "u", $texto);

        $texto = str_replace("Á", "A", $texto);$texto = str_replace("É", "E", $texto);
        $texto = str_replace("Í", "I", $texto);$texto = str_replace("Ó", "O", $texto);
        $texto = str_replace("Ú", "U", $texto);

        $texto = str_replace("À", "A", $texto);$texto = str_replace("È", "E", $texto);
        $texto = str_replace("Ì", "I", $texto);$texto = str_replace("Ò", "O", $texto);
        $texto = str_replace("Ù", "U", $texto);

        $texto = preg_replace('([^A-Za-z0-9])', '', $texto);

        return $texto;
    }

    function save( $data ) 
    {
      $error = "no existe";
      try 
      {
        if (!empty($data)) 
        {
          extract($data[0]);
          $ubicaciones = [];

        //$cve_pasillo = self::QuitarAcentosYCaracteresEspeciales($cve_pasillo);
        //$cve_rack = self::QuitarAcentosYCaracteresEspeciales($cve_rack);

          for($nv = $nivini; $nv <= $nivfin; $nv++)
          {
            for($se = $seccini; $se <= $seccfin; $se++)
            {
              for($ub = 1; $ub <= $ubifin; $ub++)
              {
                $codigoCSD = "";
                $patronCodigo = [
                  "cve_almac" => $cve_almac,
                  "cve_pasillo" => self::QuitarAcentosYCaracteresEspeciales($cve_pasillo),
                  "cve_rack" => str_pad(self::QuitarAcentosYCaracteresEspeciales($cve_rack), self::detectPad(self::QuitarAcentosYCaracteresEspeciales($cve_rack)), '0',STR_PAD_LEFT),
                  "cve_nivel" => str_pad($nv, self::detectPad($nv), '0',STR_PAD_LEFT),
                  "Seccion" => str_pad($se, self::detectPad($se), '0',STR_PAD_LEFT),
                  "Ubicacion" => str_pad($ub, self::detectPad($ubifin), '0',STR_PAD_LEFT),
                ];
                $codigos = explode('-', $CodigoCSD);
                $total_codes = count($codigos);
                for($s = 0; $s < $total_codes; $s++)
                {
                  $codigoCSD .= $patronCodigo[$codigos[$s]];
                  if($s < ($total_codes - 1))
                  {
                    $codigoCSD .= "-";
                  }
                }

                $ubicaciones [] = [
                  "cve_almac" => $cve_almac,
                  "cve_pasillo" => self::QuitarAcentosYCaracteresEspeciales($cve_pasillo),
                  "cve_rack" => str_pad(self::QuitarAcentosYCaracteresEspeciales($cve_rack), self::detectPad(self::QuitarAcentosYCaracteresEspeciales($cve_rack)), '0',STR_PAD_LEFT),
                  "cve_nivel" => str_pad($nv, self::detectPad($nv), '0',STR_PAD_LEFT),
                  "Seccion" => str_pad($se, self::detectPad($se), '0',STR_PAD_LEFT),
                  "Ubicacion" => str_pad($ub, self::detectPad($ub), '0',STR_PAD_LEFT),
                  "num_alto" => $num_alto,
                  "num_ancho" => $num_ancho,
                  "num_largo" => $num_largo,
                  "PesoMaximo" => $PesoMaximo,
                  "tipo" => $tipo,
                  "picking" => $picking,
                  "Tecnologia" => $Tecnologia,
                  "AcomodoMixto"=>$AcomodoMixto,
                  "Maximo"=>$maximo,
                  "Minimo"=>$minimo,
                  "AreaProduccion"=>$AreaProduccion,
                  "CodigoCSD"=>$codigoCSD
                ];
              }
            }
          }
          foreach($ubicaciones as $ubicacion)
          {
            $query = "CALL SPAD_AgregarUbicacion('" . $ubicacion['cve_almac'] . "', '" . $ubicacion['cve_pasillo'] . "', '" . $ubicacion['cve_rack'] . "', '" . $ubicacion['cve_nivel'] . "', '" . $ubicacion['num_ancho'] . "', '" . $ubicacion['num_largo'] . "', '" . $ubicacion['num_alto'] . "', '" . $ubicacion['picking'] . "', '" . $ubicacion['Seccion'] . "', '" . $ubicacion['Ubicacion'] . "','" . $ubicacion['Maximo'] . "','" . $ubicacion['Minimo'] . "', '" . $ubicacion['PesoMaximo'] . "', '" . $ubicacion['Tecnologia'] . "', '" . $ubicacion['tipo'] ."','" . $ubicacion['AcomodoMixto'] ."', '" . $ubicacion['AreaProduccion'] ."', '".$ubicacion['CodigoCSD']."');";
            $rs = mysqli_query(\db2(), $query) or die(mysqli_error(\db2()));
          }
        }
      } 
      catch(Exception $e) 
      {
        return 'ERROR: ' . $error;
      }
      $this->data = $error;
    }

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\UbicacionAlmacenaje\UbicacionAlmacenaje' );
        $sth->execute( array( idy_ubica ) );

        return $sth->fetchAll();

    }
    /************************************* ALMACEN ***************************************/
    function getAll_Almacen() {
        $sql = 'SELECT c_ubicacion.cve_almac, c_almacen.des_almac, c_almacen.cve_almac FROM ' . self::TABLE . ' INNER JOIN c_almacen ON c_almacen.cve_almac = c_ubicacion.cve_almac GROUP BY c_ubicacion.cve_almac';
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\UbicacionAlmacenaje\UbicacionAlmacenaje' );
        $sth->execute( array( cve_almac ) );
        return $sth->fetchAll();
    }

    function borrarPasillo( $data ) {
        $sql = 'DELETE FROM ' . self::TABLE . ' WHERE cve_almac = ? ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['cve_almac']
        ) );
    }

    function borrarUbicacion( $data ) {
    $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          idy_ubica = ?
      ;';

          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['idy_ubica']
          ) );

    }

	/*
    function actualizarUbicacion( $data ) {
        $sql = mysqli_query(\db2(), "CALL cubicacionAddUpdate(
		      '".$data['idy_ubica']."'
		    , '".$data['cve_almac']."'
		    , '".$data['cve_pasillo']."'
		    , '".$data['cve_rack']."'
		    , '".$data['cve_nivel']."'
		    , '".$data['num_ancho']."'
		    , '".$data['num_largo']."'
		    , '".$data['num_alto']."'
		    , '".$data['Seccion']."'
		    , '".$data['Ubicacion']."'
		    , '".$data['orden_secuencia']."'
		    , '".$data['CodigoCSD']."'
		    , '".$data['Reabasto']."'
		    , '".$data['PesoMaximo']."'
		    );") or die(mysqli_error(\db2()));
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['cve_almac'],
            $data['cve_pasillo'],
            $data['cve_rack'],
            $data['cve_nivel'],
            $data['num_ancho'],
            $data['num_largo'],
            $data['num_alto'],
            $data['picking'],
            $data['Seccion'],
            $data['Ubicacion'],
            $data['orden_secuencia'],
            $data['CodigoCSD'],
            $data['Reabasto'],
            $data['PesoMaximo']
        ) );
    }*/


	function actualizarUbicacion( $data ) {

			$sql = "UPDATE " . self::TABLE . "
		SET
			num_alto = '".$data['num_alto']."',
			num_ancho = '".$data['num_ancho']."',
			num_largo = '".$data['num_largo']."',
			PesoMaximo = '".$data['PesoMaximo']."',
			Tipo = '".$data['tipo']."',
			picking = '".$data['picking']."',
			TECNOLOGIA = '".$data['Tecnologia']."',
            AcomodoMixto = '".$data['AcomodoMixto']."',
            Maximo = '".$data['maximo']."',
            Minimo = '".$data['minimo']."',
            AreaProduccion = '".$data['AreaProduccion']."'
		WHERE idy_ubica = '".$data['idy_ubica']."'";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
}


function recovery( $data ) {

			$sql = "UPDATE " . self::TABLE . "
		SET Activo=1
		WHERE idy_ubica = '".$data['idy_ubica']."'";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
	}


 public function inUse( $data ) {

      $sql = "SELECT cve_articulo FROM V_ExistenciaGral WHERE cve_ubicacion IN (SELECT idy_ubica FROM c_ubicacion WHERE cve_almac = '".$data['clave_almacen']."')";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();

    if ($data['cve_articulo'])
        return true;
    else
        return false;
  }


}
