<?php

namespace Ubicaciones;

class Ubicaciones {

    const TABLE = 'c_ubicacion';
    var $identifier;

    public function __construct( $idy_ubica = false, $key = false ) {
        if( $idy_ubica )
        {
            $this->idy_ubica = (int) $idy_ubica;
        }

        if($key) 
        {
            $sql = sprintf('SELECT idy_ubica FROM %s WHERE idy_ubica = ?',self::TABLE);

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Ubicaciones\Ubicaciones');
            $sth->execute(array($key));

            $idy_ubica = $sth->fetch();
            $this->idy_ubica = $idy_ubica->idy_ubica;
        }
    }

    private function load() {
        $sql = sprintf('SELECT * FROM %s WHERE idy_ubica = ? ORDER BY CodigoCSD AND Activo = 1',self::TABLE);

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Ubicaciones\Ubicaciones' );
        $sth->execute( array( $this->idy_ubica ) );

        $this->data = $sth->fetch();
    }

    function __get( $key ) {
        switch($key) 
        {
            case 'idy_ubica':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }
    }

    function save( $data ) {
        try 
        {
            $cve_pasillo = "";
            $cve_rack = "";
            $cve_nivel = "";
            if($data['cve_pasillo']) $cve_pasillo = $data['cve_pasillo'];
            if($data['cve_rack']) $cve_rack = $data['cve_rack'];
            if($data['cve_nivel']) $cve_nivel = $data['cve_nivel'];

            $sql = mysqli_query(\db2(), "CALL cubicacionAddUpdate(
              '".$data['idy_ubica']."'
            , '".$data['cve_almac']."'
            , '".$cve_pasillo."'
            , '".$cve_rack."'
            , '".$cve_nivel."'
            , '".$data['num_ancho']."'
            , '".$data['num_largo']."'
            , '".$data['num_alto']."'
            , '".$data['Seccion']."'
            , '".$data['Ubicacion']."'
            , '".$data['orden_secuencia']."'
            , '".$data['CodigoCSD']."'
            , '".$data['Reabasto']."'
            , '".$data['PesoMaximo']."'
            , '".$data['tipo']."'
            );") or die(mysqli_error(\db2()));
        } catch(PDOException $e) {
            return 'ERROR: ' . $e->getMessage();
        }

    }

    function getAll() {
        $sql = "SELECT c_ubicacion.*, CONCAT(cve_rack,'-rack-',Seccion,'-sección-',cve_nivel,'-pasillo-',Ubicacion) as ubicacion FROM c_ubicacion";

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Ubicaciones\Ubicaciones' );
        $sth->execute( array( idy_ubica ) );

        return $sth->fetchAll();
    }

    function borrarUbicacion( $data ) {
        $sql = 'UPDATE ' . self::TABLE . ' SET Activo = 0 WHERE idy_ubica = ?';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['idy_ubica']
        ) );
    }

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
    }

    function getUbicacionesNoVaciasxZona($almacen, $excludeInventario = 0, $traslado) 
    {
      $excludeSql = $excludeInventario > 0 ? "WHERE cve_ubicacion NOT IN (SELECT COALESCE(cve_ubicacion, idy_ubica) AS cve_ubicacion FROM t_ubicacioninventario WHERE ID_Inventario IN (SELECT ID_Inventario FROM th_inventario WHERE Status = 'A')) " : "";
/*
      $sql = 
      "
        SELECT
          idy_ubica,
          codigoCSD as ubicacion
        FROM c_ubicacion 
        WHERE cve_almac= '{$zona}'
        AND idy_ubica IN (SELECT cve_ubicacion FROM V_ExistenciaGralProduccion {$excludeSql}) AND Activo = 1;
      ";
*/
//       echo var_dump($sql);
//       die();
      // WHERE 1 = WHERE c.cve_almac = '{$zona}' 
/*
      $sql = "
        SELECT  
          c.idy_ubica,
          c.CodigoCSD as ubicacion, 
          c.AcomodoMixto
        FROM 
          c_ubicacion c, V_ExistenciaGralProduccion ve, c_charolas ch
        WHERE (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = c.idy_ubica) IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '{$almacen}')
          AND c.Activo = 1 AND c.idy_ubica IN (SELECT cve_ubicacion FROM V_ExistenciaGralProduccion {$excludeSql}) AND (c.AcomodoMixto = 'N' OR c.AcomodoMixto = '')
          AND ch.clave_contenedor = ve.Cve_Contenedor AND c.idy_ubica = ve.cve_ubicacion AND ch.CveLP != ''

        UNION

        SELECT  
          c.idy_ubica,
          c.CodigoCSD as ubicacion, 
          c.AcomodoMixto
        FROM 
          c_ubicacion c, V_ExistenciaGralProduccion ve, c_charolas ch
        WHERE (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = c.idy_ubica) IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '{$almacen}')
          AND c.Activo = 1 AND c.idy_ubica IN (SELECT Cve_Ubicacion FROM V_EspacioUbicXProd) AND c.AcomodoMixto = 'S';
          AND ch.clave_contenedor = ve.Cve_Contenedor AND c.idy_ubica = ve.cve_ubicacion AND ch.CveLP != ''
        ";
*/
        $sqlTraslado = "";
        if($traslado)
           $sqlTraslado = " AND IFNULL(c.AreaProduccion, 'N') = 'N' ";
      $sql = "
        SELECT  
          c.idy_ubica,
          c.CodigoCSD as ubicacion, 
          c.AcomodoMixto
        FROM 
          c_ubicacion c, V_ExistenciaGralProduccion ve
        WHERE #(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = c.idy_ubica) IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '{$almacen}')
          ve.cve_almac = '{$almacen}' 
          AND c.Activo = 1 AND c.idy_ubica IN (SELECT cve_ubicacion FROM V_ExistenciaGralProduccion {$excludeSql}) AND (c.AcomodoMixto = 'N' OR c.AcomodoMixto = '')
          AND c.idy_ubica = ve.cve_ubicacion {$sqlTraslado}

        UNION

        SELECT  
          c.idy_ubica,
          c.CodigoCSD as ubicacion, 
          c.AcomodoMixto
        FROM 
          c_ubicacion c #, V_ExistenciaGralProduccion ve
          INNER JOIN c_almacen a ON a.cve_almac = c.cve_almac AND a.cve_almacenp = '{$almacen}'
        WHERE 
          #(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = c.idy_ubica) IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '{$almacen}') AND 
          c.Activo = 1 
          #AND c.idy_ubica IN (SELECT Cve_Ubicacion FROM V_EspacioUbicXProd) 
          AND c.AcomodoMixto = 'S'
          #AND c.idy_ubica = ve.cve_ubicacion 
          AND ((c.idy_ubica IN (SELECT idy_ubica FROM ts_existenciapiezas WHERE cve_almac = '{$almacen}'))
              OR  (c.idy_ubica IN (SELECT idy_ubica FROM ts_existenciatarima WHERE cve_almac = '{$almacen}'))
              OR  (c.idy_ubica IN (SELECT idy_ubica FROM ts_existenciacajas WHERE cve_almac = '{$almacen}'))) {$sqlTraslado}
        ";
        //echo $sql; exit;
      $sth = \db()->prepare($sql);
      $sth->execute();
      return $sth->fetchAll();
    }

    function getUbicacionesNoLlenasxAlmacen($almacen, $excludeInventario = 0, $ubicacion_origen, $tipo_pca, $descripcion_pca) 
    {
      $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      $excludeSql = $excludeInventario > 0 ? 
          "WHERE cve_ubicacion 
              IN (SELECT COALESCE(cve_ubicacion, idy_ubica) AS cve_ubicacion 
          FROM t_ubicacioninventario 
              WHERE ID_Inventario 
              IN (SELECT ID_Inventario 
          FROM th_inventario 
              WHERE Status = 'A')) " : "";
      /*
      $sql = "
        SELECT  
          c.idy_ubica,
          c.CodigoCSD as ubicacion, 
          c.AcomodoMixto
        FROM 
          c_ubicacion c
        WHERE c.cve_almac = '{$zona}' 
          AND c.Activo = 1 AND c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGralProduccion {$excludeSql}) AND (c.AcomodoMixto = 'S') AND c.idy_ubica <> '$ubicacion_origen'

        UNION

        SELECT  
          c.idy_ubica,
          c.CodigoCSD as ubicacion, 
          c.AcomodoMixto
        FROM 
          c_ubicacion c
        WHERE c.cve_almac = '{$zona}' 
          AND c.Activo = 1 AND c.idy_ubica IN (SELECT Cve_Ubicacion FROM V_EspacioUbicXProd) AND c.AcomodoMixto = 'S' AND c.idy_ubica <> '$ubicacion_origen';
        ";// OR c.AcomodoMixto = ''
        // AND c.AcomodoMixto = 'S'
        //OR c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGralProduccion {$excludeSql});
     // AND (c.AcomodoMixto = 'S' 
     // OR c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGralProduccion {$excludeSql}));
     // echo var_dump($sql);
     // die();
        */
/*
        $sql = "
SELECT  
  c.idy_ubica,
  c.CodigoCSD AS ubicacion, 
  c.AcomodoMixto
FROM 
  c_ubicacion c
WHERE c.cve_almac = '{$zona}' 
  AND c.Activo = 1 AND c.AcomodoMixto = 'S' AND c.Tipo = 'L' AND c.Tipo <> 'R' AND c.Tipo <> 'Q'
  AND ((SELECT SUM(V_ExistenciaGral.Existencia * c_articulo.peso) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = c.idy_ubica) * 100 / c.PesoMaximo) < 100
  AND (SELECT SUM(V_ExistenciaGral.Existencia * ((c_articulo.alto/1000) * (c_articulo.fondo/1000) * (c_articulo.ancho/1000))) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = c.idy_ubica) * 100 / ((c.num_ancho/1000) * (c.num_largo/1000) * (c.num_alto/1000)) < 100

UNION

SELECT  
  c.idy_ubica,
  c.CodigoCSD AS ubicacion, 
  c.AcomodoMixto
FROM 
  c_ubicacion c
WHERE c.cve_almac = '{$zona}' 
  AND c.Activo = 1 AND c.Tipo = 'L' AND c.Tipo <> 'R' AND c.Tipo <> 'Q'
  AND c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGral);
";
*/

$sql = "";

        $sql = "
SELECT  
  c.idy_ubica,
  c.CodigoCSD AS ubicacion, 
  c.AcomodoMixto
FROM 
  c_ubicacion c
WHERE (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = c.idy_ubica) IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '{$almacen}')
  AND c.Activo = 1 AND c.AcomodoMixto = 'S' AND c.Tipo = 'L'
  AND ((SELECT SUM(V_ExistenciaGral.Existencia * c_articulo.peso) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = c.idy_ubica) * 100 / c.PesoMaximo) < 100
  AND (SELECT SUM(V_ExistenciaGral.Existencia * ((c_articulo.alto/1000) * (c_articulo.fondo/1000) * (c_articulo.ancho/1000))) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = c.idy_ubica) * 100 / ((c.num_ancho/1000) * (c.num_largo/1000) * (c.num_alto/1000)) < 100

UNION

SELECT  
  c.idy_ubica,
  c.CodigoCSD AS ubicacion, 
  c.AcomodoMixto
FROM 
  c_ubicacion c
WHERE (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = c.idy_ubica) IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '{$almacen}')
  AND c.Activo = 1 AND c.Tipo = 'L' AND c.Tipo <> 'R' AND c.Tipo <> 'Q'
  AND c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGral);
";

      $sth = \db()->prepare($sql);
      $sth->execute();
      return $sth->fetchAll();
    }

    //$zona, $excludeInventario = 0, $ubicacion_origen, $tipo_pca, $descripcion_pca
    function getUbicacionesNoLlenasxZona($_post) 
    {
      $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $zona = $_post["zona"];

      $excludeInventario = 0;
      if($_post['excludeInventario'])
        $excludeInventario = $_post['excludeInventario'];

      $ubicacion_origen = $_post['ubicacion_origen'];
      $tipo_pca = $_post['tipo_pca'];
      $descripcion_pca = $_post['descripcion_pca'];
      $cve_articulo    = $_post['cve_articulo'];
      $pallet          = $_post['pallet'];
      $lp              = $_post['lp'];

      $excludeSql = $excludeInventario > 0 ? 
          "WHERE cve_ubicacion 
              IN (SELECT COALESCE(cve_ubicacion, idy_ubica) AS cve_ubicacion 
          FROM t_ubicacioninventario 
              WHERE ID_Inventario 
              IN (SELECT ID_Inventario 
          FROM th_inventario 
              WHERE Status = 'A')) " : "";
      /*
      $sql = "
        SELECT  
          c.idy_ubica,
          c.CodigoCSD as ubicacion, 
          c.AcomodoMixto
        FROM 
          c_ubicacion c
        WHERE c.cve_almac = '{$zona}' 
          AND c.Activo = 1 AND c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGralProduccion {$excludeSql}) AND (c.AcomodoMixto = 'S') AND c.idy_ubica <> '$ubicacion_origen'

        UNION

        SELECT  
          c.idy_ubica,
          c.CodigoCSD as ubicacion, 
          c.AcomodoMixto
        FROM 
          c_ubicacion c
        WHERE c.cve_almac = '{$zona}' 
          AND c.Activo = 1 AND c.idy_ubica IN (SELECT Cve_Ubicacion FROM V_EspacioUbicXProd) AND c.AcomodoMixto = 'S' AND c.idy_ubica <> '$ubicacion_origen';
        ";// OR c.AcomodoMixto = ''
        // AND c.AcomodoMixto = 'S'
        //OR c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGralProduccion {$excludeSql});
     // AND (c.AcomodoMixto = 'S' 
     // OR c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGralProduccion {$excludeSql}));
     // echo var_dump($sql);
     // die();
        */


  //********************************************************************************************************
  // Esto es para las ubicaciones de Acomodo Mixto y No mixto, 
  // Si la Ubicación es AcomodoMixto = 'S': 
  //                                  .- Puede Tener Diferentes Claves de piezas y Un solo LP
  //                                  .- No Puede Tener LP + Pieza
  //                                  .- No Puede Tener LP + Otro LP
  //
  // Si la Ubicación es AcomodoMixto = 'N': 
  //                                  .- Puede Tener Una sola Clave de piezas y Un solo LP
  //                                  .- No Puede Tener LP + Pieza
  //                                  .- No Puede Tener LP + Otro LP
  //********************************************************************************************************
  $sql_acomodar_lp_pieza = "";

  if($lp != '') 
    $sql_acomodar_lp_pieza = " AND (c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGral WHERE V_ExistenciaGral.tipo='ubicacion' AND IFNULL(cve_ubicacion, '') != '' AND V_ExistenciaGral.cve_almac = a.cve_almacenp)) ";
  else if($cve_articulo != '') 
    $sql_acomodar_lp_pieza = " AND IF(IFNULL(c.AcomodoMixto, 'N') = 'N', IF(c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGral WHERE V_ExistenciaGral.tipo = 'ubicacion' AND IFNULL(cve_ubicacion, '') != '' AND V_ExistenciaGral.cve_almac = a.cve_almacenp), 1, 0), 1) ";

    //$sql_acomodar_lp_pieza = " AND IF(IFNULL(c.AcomodoMixto, 'N') = 'N', IF('$cve_articulo' IN (SELECT cve_articulo FROM V_ExistenciaGral WHERE V_ExistenciaGral.cve_ubicacion = c.idy_ubica AND V_ExistenciaGral.tipo = 'ubicacion' AND V_ExistenciaGral.Cve_Contenedor = '' AND V_ExistenciaGral.cve_almac = a.cve_almacenp), 1, IF(c.idy_ubica NOT IN (SELECT V_ExistenciaGral.cve_ubicacion FROM V_ExistenciaGral WHERE V_ExistenciaGral.tipo = 'ubicacion' AND V_ExistenciaGral.Cve_Contenedor = '' AND V_ExistenciaGral.cve_almac = a.cve_almacenp), 1, 0)), 1) ";
  //********************************************************************************************************

      $sql = "
SELECT  
  c.idy_ubica,
  c.CodigoCSD AS ubicacion, 
  c.AcomodoMixto
FROM 
  c_ubicacion c
LEFT JOIN c_almacen a ON a.cve_almac = c.cve_almac AND c.cve_almac = '{$zona}' 
WHERE c.cve_almac = '{$zona}' 
  AND c.Activo = 1 AND c.AcomodoMixto = 'S' AND c.Tipo = 'L' AND c.Tipo <> 'R' AND c.Tipo <> 'Q'
  AND IF(c.PesoMaximo = 0, 1, ((SELECT SUM(V_ExistenciaGral.Existencia * c_articulo.peso) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = c.idy_ubica) * 100 / c.PesoMaximo)) < 100
  AND IF(((c.num_ancho/1000) * (c.num_largo/1000) * (c.num_alto/1000)) = 0, 1, (SELECT SUM(V_ExistenciaGral.Existencia * ((c_articulo.alto/1000) * (c_articulo.fondo/1000) * (c_articulo.ancho/1000))) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = c.idy_ubica) * 100 / ((c.num_ancho/1000) * (c.num_largo/1000) * (c.num_alto/1000))) < 100  AND IFNULL(c.AreaProduccion, 'N') = 'N'
  {$sql_acomodar_lp_pieza}

UNION

SELECT  
  c.idy_ubica,
  c.CodigoCSD AS ubicacion, 
  c.AcomodoMixto
FROM 
  c_ubicacion c
LEFT JOIN c_almacen a ON a.cve_almac = c.cve_almac AND c.cve_almac = '{$zona}' 
WHERE c.cve_almac = '{$zona}' 
  AND c.Activo = 1 AND c.Tipo = 'L' AND c.Tipo <> 'R' AND c.Tipo <> 'Q'
  AND c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGral WHERE V_ExistenciaGral.tipo='area' AND IFNULL(cve_ubicacion, '') != '')
  {$sql_acomodar_lp_pieza}  AND IFNULL(c.AreaProduccion, 'N') = 'N'

UNION

SELECT  
  c.idy_ubica,
  c.CodigoCSD AS ubicacion, 
  c.AcomodoMixto
FROM 
  c_ubicacion c
LEFT JOIN c_almacen a ON a.cve_almac = c.cve_almac AND c.cve_almac = '{$zona}'
WHERE c.cve_almac = '{$zona}' 
  AND c.Activo = 1 AND c.Tipo = 'L' AND c.Tipo <> 'R' AND c.Tipo <> 'Q'
  AND c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGral WHERE V_ExistenciaGral.tipo='area' AND IFNULL(cve_ubicacion, '') != '')
AND c.cve_nivel = 0  AND IFNULL(c.AreaProduccion, 'N') = 'N'
  ORDER BY ubicacion;
";

/*
$sql = "";

        $sql = "
SELECT  
  c.idy_ubica,
  c.CodigoCSD AS ubicacion, 
  c.AcomodoMixto
FROM 
  c_ubicacion c
WHERE (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = c.idy_ubica) IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '{$almacen}')
  AND c.Activo = 1 AND c.AcomodoMixto = 'S' AND c.Tipo = 'L'
  AND ((SELECT SUM(V_ExistenciaGral.Existencia * c_articulo.peso) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = c.idy_ubica) * 100 / c.PesoMaximo) < 100
  AND (SELECT SUM(V_ExistenciaGral.Existencia * ((c_articulo.alto/1000) * (c_articulo.fondo/1000) * (c_articulo.ancho/1000))) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = c.idy_ubica) * 100 / ((c.num_ancho/1000) * (c.num_largo/1000) * (c.num_alto/1000)) < 100

UNION

SELECT  
  c.idy_ubica,
  c.CodigoCSD AS ubicacion, 
  c.AcomodoMixto
FROM 
  c_ubicacion c
WHERE (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = c.idy_ubica) IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '{$almacen}')
  AND c.Activo = 1 AND c.Tipo = 'L' AND c.Tipo <> 'R' AND c.Tipo <> 'Q'
  AND c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGral);
";
*/

      $sth = \db()->prepare($sql);
      $sth->execute();
      return $sth->fetchAll();
    }

    function getUbicacionesVaciasxAlmacen($almacen, $excludeInventario = 0, $ubicacion_origen) 
    {
      $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      $excludeSql = $excludeInventario > 0 ? 
          "WHERE cve_ubicacion 
              IN (SELECT COALESCE(cve_ubicacion, idy_ubica) AS cve_ubicacion 
          FROM t_ubicacioninventario 
              WHERE ID_Inventario 
              IN (SELECT ID_Inventario 
          FROM th_inventario 
              WHERE Status = 'A')) " : "";

        //c.cve_almac = '{$zona}' 
        $sql = "
            SELECT  
              c.idy_ubica,
              c.CodigoCSD AS ubicacion, 
              c.AcomodoMixto
            FROM 
              c_ubicacion c
            WHERE (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = c.idy_ubica) IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '{$almacen}')
              AND c.Activo = 1 AND c.Tipo = 'L' AND c.Tipo <> 'R' AND c.Tipo <> 'Q'
              AND c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGral);
        ";

      $sth = \db()->prepare($sql);
      $sth->execute();
      return $sth->fetchAll();
    }

    function getUbicacionesVaciasxZona($almacen, $excludeInventario = 0, $ubicacion_origen) 
    {
      $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      $excludeSql = $excludeInventario > 0 ? 
          "WHERE cve_ubicacion 
              IN (SELECT COALESCE(cve_ubicacion, idy_ubica) AS cve_ubicacion 
          FROM t_ubicacioninventario 
              WHERE ID_Inventario 
              IN (SELECT ID_Inventario 
          FROM th_inventario 
              WHERE Status = 'A')) " : "";

        //c.cve_almac = '{$zona}' 
        $sql = "
            SELECT  
              c.idy_ubica,
              c.CodigoCSD AS ubicacion, 
              c.AcomodoMixto
            FROM 
              c_ubicacion c
            WHERE (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = c.idy_ubica) IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '{$almacen}')
              AND c.Activo = 1 AND c.Tipo = 'L' AND c.Tipo <> 'R' AND c.Tipo <> 'Q'
              AND c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGral);
        ";

      $sth = \db()->prepare($sql);
      $sth->execute();
      return $sth->fetchAll();
    }

    function getArticulosdeUbicacion($idy_ubica) 
    {
      //PR tabla traslado
      
        $sql = " 
          SELECT  DISTINCT
            v.cve_ubicacion AS idy_ubica,
            a.id as id_articulo,
            if(a.control_lotes = 'S',v.cve_lote, if(a.control_numero_series = 'S',v.cve_lote,'')) AS lote,
            #IFNULL(ch.tipo, 'Artículo') AS tipo,
            IF(IFNULL(ch.clave_contenedor, '') = '', 'Artículo', ch.tipo) AS tipo,
            ch.clave_contenedor AS pallet,
            IF(IFNULL(v.Cve_Contenedor,'') != '', ch.CveLP, '') AS LP,
            COALESCE(if(a.control_lotes = 'S',if(DATE_FORMAT(l.Caducidad, '%Y-%m-%d') = DATE_FORMAT('0000-00-00', '%Y-%m-%d'),'',date_format(l.Caducidad,'%d-%m-%Y')),'')) as caducidad,
            #if(a.control_numero_series = 'S',v.cve_lote,'') as serie,
            IFNULL(CAST(((a.alto / 1000) * (a.ancho / 1000) * (a.fondo / 1000)) AS DECIMAL(10,6)), 0) AS volumen_ocupado,# * v.Existencia
            IFNULL(v.Existencia, 0) AS num_existencia,
            v.cve_articulo,
            a.des_articulo AS articulo,
            IFNULL(CAST((a.peso) as DECIMAL(10,2)), 0) AS peso_total,# * v.Existencia
            #ts_existencia_proveedor.ID_Proveedor,
            #cp.Nombre as proveedor,
            IF(ts_existencia_proveedor.ID_Proveedor = 0, (SELECT DISTINCT ID_Proveedor FROM t_ordenprod WHERE Cve_Articulo = v.cve_articulo AND Cve_Lote = v.cve_lote AND ID_Proveedor IS NOT NULL LIMIT 1), ts_existencia_proveedor.ID_Proveedor) AS ID_Proveedor,
            IF(ts_existencia_proveedor.ID_Proveedor = 0, (SELECT DISTINCT Nombre FROM c_proveedores WHERE ID_Proveedor = (SELECT DISTINCT ID_Proveedor FROM t_ordenprod WHERE Cve_Articulo = v.cve_articulo AND Cve_Lote = v.cve_lote AND ID_Proveedor IS NOT NULL LIMIT 1)), cp.Nombre) AS proveedor,
            (SELECT des_almac FROM c_almacen WHERE cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = '{$idy_ubica}')) AS Zona_Almacenaje,
            (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = '{$idy_ubica}') AS Clave_Zona,
            IF(v.Cuarentena = 1, 'Si', 'No') as Cuarentena
          FROM (
              SELECT cve_almac, cve_ubicacion, cve_articulo, cve_lote, Existencia, Cve_Contenedor, Cuarentena FROM V_ExistenciaGral WHERE cve_ubicacion = '{$idy_ubica}'
              UNION SELECT cve_almac, cve_ubicacion, cve_articulo, cve_lote, Existencia, Cve_Contenedor, Cuarentena FROM V_ExistenciaProduccion WHERE cve_ubicacion = '{$idy_ubica}'
          ) AS v
          LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
          LEFT JOIN c_lotes l ON l.cve_articulo = v.cve_articulo AND l.LOTE = v.cve_lote
          LEFT JOIN (
            SELECT * FROM ( 
              SELECT idy_ubica,cve_articulo,cve_lote,ID_Proveedor FROM ts_existenciapiezas 
              #UNION 
              #SELECT idy_ubica,cve_articulo,cve_lote,ID_Proveedor FROM ts_existenciacajas 
              UNION 
              SELECT idy_ubica,cve_articulo,lote as cve_lote,ID_Proveedor FROM ts_existenciatarima 
            ) as ts_existe GROUP BY idy_ubica, cve_articulo, cve_lote ORDER BY ID_Proveedor DESC
          ) as ts_existencia_proveedor  ON ts_existencia_proveedor.idy_ubica = '{$idy_ubica}' AND ts_existencia_proveedor.cve_articulo = v.cve_articulo AND ts_existencia_proveedor.cve_lote = v.cve_lote 
          LEFT JOIN c_proveedores cp ON cp.ID_Proveedor = ts_existencia_proveedor.ID_Proveedor 
          LEFT JOIN c_charolas ch ON ch.clave_contenedor = v.Cve_Contenedor
          WHERE v.cve_ubicacion = '{$idy_ubica}' and v.cve_almac = (SELECT cve_almacenp from c_almacen where cve_almac in (select cve_almac from c_ubicacion where idy_ubica = v.cve_ubicacion) )
          ORDER BY articulo ASC
          ";
          //WHERE ch.CveLP != ''
//         echo var_dump($sql);
//         die();
/*
        $sql = " 
          SELECT  DISTINCT
            v.cve_ubicacion AS idy_ubica,
            a.id as id_articulo,
            if(a.control_lotes = 'S',v.cve_lote, if(a.control_numero_series = 'S',v.cve_lote,'')) AS lote,
            #IFNULL(ch.tipo, 'Artículo') AS tipo,
            IF(IFNULL(ch.clave_contenedor, '') = '', 'Artículo', ch.tipo) AS tipo,
            ch.clave_contenedor AS pallet,
            IF(IFNULL(v.Cve_Contenedor,'') != '', ch.CveLP, '') AS LP,
            COALESCE(if(a.control_lotes = 'S',if(l.Caducidad = '0000-00-00','',date_format(l.Caducidad,'%d-%m-%Y')),'')) as caducidad,
            #if(a.control_numero_series = 'S',v.cve_lote,'') as serie,
            IFNULL(CAST(((a.alto / 1000) * (a.ancho / 1000) * (a.fondo / 1000)) AS DECIMAL(10,6)), 0) AS volumen_ocupado,# * v.Existencia
            IFNULL(v.Existencia, 0) AS num_existencia,
            v.cve_articulo,
            a.des_articulo AS articulo,
            IFNULL(CAST((a.peso) as DECIMAL(10,2)), 0) AS peso_total,# * v.Existencia
            #ts_existencia_proveedor.ID_Proveedor,
            #cp.Nombre as proveedor,
            IF(ts_existencia_proveedor.ID_Proveedor = 0, (SELECT DISTINCT ID_Proveedor FROM t_ordenprod WHERE Cve_Articulo = v.cve_articulo AND Cve_Lote = v.cve_lote AND ID_Proveedor IS NOT NULL LIMIT 1), ts_existencia_proveedor.ID_Proveedor) AS ID_Proveedor,
            IF(ts_existencia_proveedor.ID_Proveedor = 0, (SELECT DISTINCT Nombre FROM c_proveedores WHERE ID_Proveedor = (SELECT DISTINCT ID_Proveedor FROM t_ordenprod WHERE Cve_Articulo = v.cve_articulo AND Cve_Lote = v.cve_lote AND ID_Proveedor IS NOT NULL LIMIT 1)), cp.Nombre) AS proveedor,
            (SELECT des_almac FROM c_almacen WHERE cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = '{$idy_ubica}')) AS Zona_Almacenaje,
            (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = '{$idy_ubica}') AS Clave_Zona
          FROM c_articulo a
          LEFT JOIN V_ExistenciaGralProduccion v ON a.cve_articulo = v.cve_articulo AND v.cve_ubicacion = '{$idy_ubica}'
          LEFT JOIN c_lotes l ON l.cve_articulo = v.cve_articulo AND l.LOTE = v.cve_lote
          LEFT JOIN (
            SELECT * FROM ( 
              SELECT idy_ubica,cve_articulo,cve_lote,ID_Proveedor FROM ts_existenciapiezas 
              UNION 
              SELECT idy_ubica,cve_articulo,cve_lote,ID_Proveedor FROM ts_existenciacajas 
              UNION 
              SELECT idy_ubica,cve_articulo,lote as cve_lote,ID_Proveedor FROM ts_existenciatarima 
            ) as ts_existe GROUP BY idy_ubica, cve_articulo, cve_lote ORDER BY ID_Proveedor DESC
          ) as ts_existencia_proveedor  ON ts_existencia_proveedor.idy_ubica = '{$idy_ubica}' AND ts_existencia_proveedor.cve_articulo = v.cve_articulo AND ts_existencia_proveedor.cve_lote = v.cve_lote 
          LEFT JOIN c_proveedores cp ON cp.ID_Proveedor = ts_existencia_proveedor.ID_Proveedor 
          LEFT JOIN c_charolas ch ON ch.clave_contenedor = v.Cve_Contenedor
          ORDER BY articulo ASC
          ";
*/
        $sth = \db()->prepare( $sql );
        $sth->execute( array( $this->idy_ubica ) );
        return $sth->fetchAll();
    }

    function getZonaUbicacion($idy_ubica) 
    {
      //PR tabla traslado
        $sql = " 
            SELECT DISTINCT cve_almac FROM c_ubicacion WHERE idy_ubica = '{$idy_ubica}'
          ";
          //WHERE ch.CveLP != ''
//         echo var_dump($sql);
//         die();
        $sth = \db()->prepare( $sql );
        $sth->execute();
        return $sth->fetchAll();
    }

    function getArticulosdeAlmacen($cve_almac) {
        $sql = "
            SELECT
              a.cve_articulo as id_articulo,
              a.des_articulo as articulo
            FROM c_articulo a
            WHERE a.cve_almac = $cve_almac
            ORDER BY a.des_articulo ASC";
        $sth = \db()->prepare( $sql );
        $sth->execute( array( $this->idy_ubica ) );
        return $sth->fetchAll();
    }
  
    function getArticulosYZonasAlmacenConExistencia($cve_almac) {

$utf8Sql = \db()->prepare("SET NAMES 'utf8mb4';");
$utf8Sql->execute();

        $sqlZonas = "
            SELECT 
                cve_almac AS clave,
                des_almac AS descripcion
            FROM c_almacen
            WHERE cve_almacenp = {$cve_almac} 
              AND cve_almac IN (
                SELECT DISTINCT cve_almac 
                FROM c_ubicacion 
                WHERE Activo = 1
                #idy_ubica IN (
                 #   SELECT DISTINCT cve_ubicacion 
                 #   FROM V_ExistenciaGralProduccion 
                 #   WHERE Existencia > 0
                #)
            )";
      
        $sqlArticulos = "
            SELECT  
                DISTINCT e.cve_articulo,
                a.cve_articulo as id_articulo,
                a.des_articulo as articulo
            FROM V_ExistenciaGralProduccion e
              LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            WHERE e.cve_almac = $cve_almac
              AND e.Existencia >  0 
              AND e.tipo  = 'ubicacion'
            ORDER BY a.des_articulo ASC";
      
      $sqlContenedor = "
           SELECT
	            DISTINCT V_ExistenciaGralProduccion.Cve_Contenedor,
              c_charolas.clave_contenedor as Cve_Contenedor
           FROM V_ExistenciaGralProduccion
              LEFT JOIN c_charolas ON c_charolas.clave_contenedor = V_ExistenciaGralProduccion.Cve_Contenedor
           WHERE V_ExistenciaGralProduccion.cve_almac = $cve_almac
             AND V_ExistenciaGralProduccion.Existencia > 0
             AND V_ExistenciaGralProduccion.tipo  = 'ubicacion'
           ORDER BY c_charolas.clave_contenedor ASC";
         
        /*
        $sqlProveedores = "
            select 
                ifnull(
                    (select concat(ID_Proveedor,'-',Nombre) from c_proveedores where ID_Proveedor = (
                        ifnull(
                            (select ID_Proveedor from ts_existenciapiezas where ts_existenciapiezas.idy_ubica = e.cve_ubicacion and ts_existenciapiezas.cve_articulo = e.cve_articulo limit 1),
                            ifnull(
                                (select ID_Proveedor from ts_existenciacajas where ts_existenciacajas.idy_ubica = e.cve_ubicacion and ts_existenciacajas.cve_articulo = e.cve_articulo limit 1),
                                ifnull(
                                    (select ID_Proveedor from ts_existenciatarima where ts_existenciatarima.idy_ubica = e.cve_ubicacion and ts_existenciatarima.cve_articulo = e.cve_articulo limit 1),
                                    0
                                )
                            )
                        )
                    )),'--'
                )as proveedor
            from V_ExistenciaGralProduccion e
            where e.Existencia >0
            GROUP by proveedor;
        ";
        */

        $sql_proveedor = "";
        if(isset($_POST['cve_proveedor']))
        {
          $cve_proveedor = $_POST['cve_proveedor'];
          if($cve_proveedor)
          $sql_proveedor = " AND p.ID_Proveedor = '{$cve_proveedor}' ";
        }

        $sqlProveedores = "
        SELECT 
    CONCAT(p.ID_Proveedor, '-', p.Nombre) AS proveedor

            FROM V_ExistenciaGralProduccion e
            INNER JOIN c_proveedores p ON p.ID_Proveedor = e.Id_Proveedor
            WHERE e.Existencia >0 {$sql_proveedor} AND p.es_cliente = 1 
            AND e.cve_almac = $cve_almac
            GROUP BY proveedor
            ";

        $sqlGrupos = "SELECT id, cve_gpoart AS cve_grupo, des_gpoart AS des_grupo FROM c_gpoarticulo WHERE Activo = 1 AND cve_gpoart IN (SELECT IFNULL(grupo, '') FROM c_articulo WHERE IFNULL(grupo, '') != '' AND cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion WHERE cve_almac = $cve_almac)) ORDER BY des_grupo ASC";

        $sqlClasficacion = "SELECT id, cve_sgpoart AS cve_clasif, des_sgpoart AS des_clasif FROM c_sgpoarticulo WHERE Activo = 1 AND cve_sgpoart IN (SELECT IFNULL(clasificacion, '') FROM c_articulo WHERE IFNULL(clasificacion, '') != '' AND cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion WHERE cve_almac = $cve_almac)) ORDER BY des_clasif ASC";

        $sthZonas = \db()->prepare( $sqlZonas );
        $sthZonas->execute();
        $zonas = $sthZonas->fetchAll();
        $sthArticulos = \db()->prepare( $sqlArticulos );
        $sthArticulos->execute();
        $articulos = $sthArticulos->fetchAll();
        $sthContenedor = \db()->prepare( $sqlContenedor );
        $sthContenedor->execute();
        $contenedores = $sthContenedor->fetchAll();
        $sthProveedores = \db()->prepare( $sqlProveedores );
        $sthProveedores->execute();
        $proveedores = $sthProveedores->fetchAll();
        $sthGrupos = \db()->prepare( $sqlGrupos );
        $sthGrupos->execute();
        $grupos = $sthGrupos->fetchAll();
        $sthClasif = \db()->prepare( $sqlClasficacion );
        $sthClasif->execute();
        $clasificacion = $sthClasif->fetchAll();


        return array(
            "zonas"     => $zonas,
            "articulos" => $articulos,
            "contenedores" => $contenedores,
            "proveedores" => $proveedores,
            "grupos" => $grupos,
            "clasificacion" => $clasificacion
        );
    }

    function getArticulosYZonasAlmacenConSinExistencia($almacen, $clave_almacen, $check_almacen) {

$utf8Sql = \db()->prepare("SET NAMES 'utf8mb4';");
$utf8Sql->execute();

    
    /*
        $sqlArticulos = "
            SELECT  
                DISTINCT e.cve_articulo,
                a.cve_articulo as id_articulo,
                a.des_articulo as articulo
            FROM V_ExistenciaGralProduccion e
              LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            WHERE e.cve_almac = $cve_almac
              AND e.Existencia >  0 
              AND e.tipo  = 'ubicacion'
            ORDER BY a.des_articulo ASC";
      */
            /*
    $sql_in = " AND ar.cve_articulo NOT IN (SELECT  
            z.cve_articulo
        FROM td_aduana z
            LEFT JOIN th_aduana adz ON z.num_orden = adz.num_pedimento
        WHERE adz.Cve_Almac = '{$clave_almacen}') AND ar.cve_articulo NOT IN (SELECT 
        cve_articulo FROM V_ExistenciaGralProduccion)";

    if($check_almacen == 1)
    {
        $sql_in = " AND ar.cve_articulo NOT IN (SELECT  
                z.cve_articulo
            FROM td_aduana z
                LEFT JOIN th_aduana adz ON z.num_orden = adz.num_pedimento
            ) AND ar.cve_articulo NOT IN (SELECT 
            cve_articulo FROM V_ExistenciaGralProduccion)";    
    }

    $sqlArticulos = "
    SELECT * FROM (
    SELECT  DISTINCT
            #ad.num_orden AS folio,
            a.id as id_articulo,
            alm.clave AS clave_alm,
            alm.nombre AS Nombre_Almacen,
            IFNULL(g.des_gpoart, '') AS grupo,
            p.ID_Proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            a.cve_articulo AS cve_articulo,
            IFNULL(a.des_articulo, '--') as articulo,
            IFNULL(a.cajas_palet, 0) AS cajasxpallets,
            IFNULL(a.num_multiplo, 0) AS piezasxcajas,
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,

        (SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGral e 
        WHERE e.cve_almac = '{$almacen}' 
        AND e.Existencia > 0 AND e.cve_articulo = a.cve_articulo AND e.tipo = 'ubicacion' AND e.Id_Proveedor = p.ID_Proveedor
        ) AS existencia_conc

        FROM c_articulo a
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
            #LEFT JOIN th_aduana v  ON v.Cve_Almac = '{$almacen}'
            #LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden AND ad.cve_articulo != ''
            LEFT JOIN V_ExistenciaGral vg ON a.cve_articulo = vg.cve_articulo AND vg.tipo = 'ubicacion' 
            LEFT JOIN c_almacenp alm ON alm.id = '{$almacen}' 
            #LEFT JOIN t_match tm ON tm.Cve_Almac = alm.clave AND tm.Cve_Articulo = a.cve_articulo 
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vg.ID_Proveedor
        WHERE a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion) AND vg.Id_Proveedor = p.ID_Proveedor
        AND alm.id = '{$almacen}' AND vg.Existencia > 0 AND vg.cve_almac = '{$almacen}'
        GROUP BY a.cve_articulo, p.ID_Proveedor
#v.Cve_Almac = '{$almacen}' AND
        UNION

        SELECT  
            #'' AS folio,
            ar.id as id_articulo,
            c_almacenp.clave AS clave_alm,
            c_almacenp.nombre AS Nombre_Almacen,
            IFNULL(g.des_gpoart, '') AS grupo,
            '' as id_proveedor,
            '' AS proveedor,
            ar.cve_articulo AS cve_articulo,
            IFNULL(ar.des_articulo, '--') AS articulo,
            IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
            IFNULL(ar.num_multiplo, 0) AS piezasxcajas, 
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            0 AS existencia_conc
        FROM c_articulo ar
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = ar.grupo
            LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = ar.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = ar.ID_Proveedor
            LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = ar.cve_articulo
            LEFT JOIN c_almacenp ON c_almacenp.id = ra.cve_almac
        WHERE c_almacenp.id = '{$almacen}' $sql_in 
        GROUP BY ar.cve_articulo
        ORDER BY Nombre_Almacen
        ) AS concentrado 
        GROUP BY concentrado.articulo";
//{$filtro_where_concentrado} {$filtro_clientes} 
        if($check_almacen == 1)
            $sqlArticulos = "
            SELECT * FROM (
            SELECT  DISTINCT
                    #ad.num_orden AS folio,
                    a.id as id_articulo,
                    alm.clave AS clave_alm,
                    alm.nombre AS Nombre_Almacen,
                    IFNULL(g.des_gpoart, '') AS grupo,
                    p.ID_Proveedor AS id_proveedor,
                    p.Nombre AS proveedor,
                    a.cve_articulo AS cve_articulo,
                    IFNULL(a.des_articulo, '--') as articulo,
                    IFNULL(a.cajas_palet, 0) AS cajasxpallets,
                    IFNULL(a.num_multiplo, 0) AS piezasxcajas,
                    0 AS Pallet,
                    0 AS Caja, 
                    0 AS Piezas,

                (SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGral e 
                WHERE e.Existencia > 0 AND e.cve_articulo = a.cve_articulo AND e.tipo = 'ubicacion' AND e.Id_Proveedor = p.ID_Proveedor
                ) AS existencia_conc

                FROM c_articulo a
                    LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
                    #LEFT JOIN th_aduana v  ON v.Cve_Almac = '{$almacen}'
                    #LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden AND ad.cve_articulo != ''
                    LEFT JOIN V_ExistenciaGral vg ON a.cve_articulo = vg.cve_articulo AND vg.tipo = 'ubicacion' 
                    LEFT JOIN c_almacenp alm ON alm.id = vg.cve_almac 
                    #LEFT JOIN t_match tm ON tm.Cve_Almac = alm.clave AND tm.Cve_Articulo = a.cve_articulo 
                    LEFT JOIN c_proveedores p ON p.ID_Proveedor = vg.ID_Proveedor
                WHERE a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion) AND vg.Id_Proveedor = p.ID_Proveedor
                AND vg.Existencia > 0 
                GROUP BY a.cve_articulo, p.ID_Proveedor
        #v.Cve_Almac = '{$almacen}' AND
                UNION

                SELECT  
                    #'' AS folio,
                    ar.id as id_articulo,
                    c_almacenp.clave AS clave_alm,
                    c_almacenp.nombre AS Nombre_Almacen,
                    IFNULL(g.des_gpoart, '') AS grupo,
                    '' as id_proveedor,
                    '' AS proveedor,
                    ar.cve_articulo AS cve_articulo,
                    IFNULL(ar.des_articulo, '--') AS articulo,
                    IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
                    IFNULL(ar.num_multiplo, 0) AS piezasxcajas, 
                    0 AS Pallet,
                    0 AS Caja, 
                    0 AS Piezas,
                    0 AS existencia_conc
                FROM c_articulo ar
                    LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = ar.grupo
                    LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = ar.cve_articulo
                    LEFT JOIN c_proveedores p ON p.ID_Proveedor = ar.ID_Proveedor
                    LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = ar.cve_articulo
                    LEFT JOIN c_almacenp ON c_almacenp.id = ra.cve_almac
                WHERE 1 $sql_in 
                GROUP BY ar.cve_articulo
                ORDER BY Nombre_Almacen
                ) AS concentrado 
                GROUP BY concentrado.articulo";
//{$filtro_where_concentrado} {$filtro_clientes} 
*/
/*
        $sqlAlmacen = " AND alm.id = '{$almacen}' ";
        if($check_almacen == 1)
          $sqlAlmacen = "";
*/
        $sqlArticulos = "
        SELECT DISTINCT
            a.id AS id_articulo,
            '' AS clave_alm,
            '' AS Nombre_Almacen,
            '' AS grupo,
            '' AS id_proveedor,
            '' AS proveedor,
            a.cve_articulo AS cve_articulo,
            IFNULL(a.des_articulo, '--') AS articulo,
            '' AS cajasxpallets,
            '' AS piezasxcajas,
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas

        FROM c_articulo a
        LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = a.cve_articulo
        WHERE ra.Cve_Almac = '{$almacen}'";

        $sqlArticulos2 = "
        SELECT DISTINCT
            a.id AS id_articulo,
            '' AS clave_alm,
            '' AS Nombre_Almacen,
            '' AS grupo,
            '' AS id_proveedor,
            '' AS proveedor,
            a.cve_articulo AS cve_articulo,
            IFNULL(a.des_articulo, '--') AS articulo,
            '' AS cajasxpallets,
            '' AS piezasxcajas,
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas

        FROM c_articulo a
        LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = a.cve_articulo";

        $sql_proveedor = "";
        if(isset($_POST['cve_proveedor']))
        {
          $cve_proveedor = $_POST['cve_proveedor'];
          if($cve_proveedor)
          $sql_proveedor = " AND p.ID_Proveedor = '{$cve_proveedor}' ";
        }

        $sqlProveedores = "
        SELECT 
    CONCAT(p.ID_Proveedor, '-', p.Nombre) AS proveedor

            FROM V_ExistenciaGralProduccion e
            INNER JOIN c_proveedores p ON p.ID_Proveedor = e.Id_Proveedor
            WHERE e.Existencia >0 {$sql_proveedor} AND p.es_cliente = 1 
            AND e.cve_almac = $almacen
            GROUP BY proveedor
            ";

        $sqlGrupos = "SELECT id, cve_gpoart AS cve_grupo, des_gpoart AS des_grupo FROM c_gpoarticulo WHERE Activo = 1 AND cve_gpoart IN (SELECT IFNULL(grupo, '') FROM c_articulo WHERE IFNULL(grupo, '') != '' AND cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion WHERE cve_almac = $almacen)) ORDER BY des_grupo ASC";

        $sthArticulos = \db()->prepare( $sqlArticulos );
        $sthArticulos->execute();
        $articulos = $sthArticulos->fetchAll();

        $sthArticulos2 = \db()->prepare( $sqlArticulos2 );
        $sthArticulos2->execute();
        $articulos2 = $sthArticulos2->fetchAll();

        $sthProveedores = \db()->prepare( $sqlProveedores );
        $sthProveedores->execute();
        $proveedores = $sthProveedores->fetchAll();

        $sthGrupos = \db()->prepare( $sqlGrupos );
        $sthGrupos->execute();
        $grupos = $sthGrupos->fetchAll();


        return array(
            "sql_articulos" => $sqlArticulos,
            "sql_articulos2" => $sqlArticulos2,
            "articulos" => $articulos,
            "articulos2" => $articulos2,
            "proveedores" => $proveedores,
            "grupos" => $grupos,
        );
    }

    function getArticulosPendientesAcomodo($cve_ubicacion) 
    {


        $sql = 
          "
            SELECT
              p.id,
              p.cve_articulo,
              a.des_articulo AS articulo,
              p.Cantidad AS existencia,
              p.cve_lote as lote,
              IFNULL(IF(DATE_FORMAT(l.CADUCIDAD, '%Y-%m-%d') = DATE_FORMAT('0000-00-00', '%Y-%m-%d'), '', DATE_FORMAT(l.CADUCIDAD, '%d-%m-%Y')), '') AS caducidad,
              CAST(a.peso*p.cantidad as DECIMAL(10,2)) as peso_total,
              CAST((a.alto * a.ancho * a.fondo * p.Cantidad/1000000000) as DECIMAL(10,6)) AS volumen_ocupado,
              a.id as id_articulo
            FROM t_pendienteacomodo p
            LEFT JOIN c_articulo a ON a.cve_articulo = p.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = p.cve_articulo AND l.LOTE = p.cve_lote
            where    0 = 0 and  p.cve_ubicacion='".$cve_ubicacion."'  order by p.cve_articulo
          ";
        //echo var_dump($sql); //EDG
        //die();
        $sth = \db()->prepare( $sql );
        $sth->execute();
        return $sth->fetchAll();
    }

    function getArticulosRecepcion($cve_ubicacion, $filtro_buscador = "", $filtro_lp = "", $filtro_clave = "", $filtro_lote_serie = "") 
    { //PR acomodo

      $sql_buscador = "";
      if($filtro_buscador)
        $sql_buscador = " AND (a.cve_articulo LIKE '%{$filtro_buscador}%' OR a.des_articulo LIKE '%{$filtro_buscador}%' OR V_EntradasContenedores.Fol_Folio LIKE '%{$filtro_buscador}%' OR V_EntradasContenedores.Clave_Contenedor LIKE '%{$filtro_buscador}%' OR c_proveedores.Nombre LIKE '%{$filtro_buscador}%' OR V_EntradasContenedores.Cve_Proveedor LIKE '%{$filtro_buscador}%' OR V_EntradasContenedores.Cve_Lote LIKE '%{$filtro_buscador}%') ";

      $sql_lp = ""; $sql_lp_1 = "";
      if($filtro_lp) {$sql_lp = " AND c_charolas.CveLP = '{$filtro_lp}' "; $sql_lp_1 = " AND 0 ";}

      $sql_clave = "";
      if($filtro_clave) $sql_clave = " AND a.cve_articulo = '{$filtro_clave}' ";

      $sql_serie = "";
      if($filtro_lote_serie) $sql_serie = " AND V_EntradasContenedores.Cve_Lote = '{$filtro_lote_serie}' ";

      $sql = "
      SELECT DISTINCT
          V_EntradasContenedores.Fol_Folio AS folio,
          #IF(V_EntradasContenedores.Clave_Contenedor != '', 'Contenedor','Articulo') AS tipo,
          IFNULL(V_EntradasContenedores.Clave_Contenedor, 'Articulo') AS tipo,
          IFNULL(c_articulo.control_abc, '') AS control_abc,
          IFNULL(IF(c_articulo.control_lotes = 'S' AND IFNULL(tde.cve_lote, '') != '', tde.cve_lote,''), '') AS lote,
          IF(c_articulo.control_lotes = 'S' AND c_articulo.Caduca = 'S' AND IFNULL(tde.cve_lote, '') != '', IFNULL(IF(DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y') = '00-00-0000', '', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y')), ''), '') AS caducidad,          IF(c_articulo.control_numero_series = 'S', V_EntradasContenedores.Cve_lote,'') AS numero_serie,
          #IFNULL(CAST(((c_articulo.alto / 1000) * (c_articulo.ancho / 1000) * (c_articulo.fondo / 1000) * IFNULL(t_pendienteacomodo.Cantidad, 0)) AS DECIMAL(10,6)), 0) AS volumen_ocupado,
          IFNULL(CAST(((c_articulo.alto / 1000) * (c_articulo.ancho / 1000) * (c_articulo.fondo / 1000)) AS DECIMAL(10,6)), 0) AS volumen_ocupado,
          tde.cve_articulo AS clave,
          a.des_articulo AS descripcion,
          a.control_peso,
          IFNULL(tde.CantidadRecibida - IFNULL(tde.CantidadUbicada, 0), 0) AS num_existencia,
          #IFNULL(t_pendienteacomodo.Cantidad, 0) AS num_existencia,
          /*c_articulo.des_articulo AS descripcion,*/
          /*IFNULL(V_EntradasContenedores.Clave_Contenedor,V_EntradasContenedores.Cve_Articulo) AS pallet_contenedor,*/
          IFNULL(V_EntradasContenedores.Clave_Contenedor, '') AS pallet_contenedor,
          '' AS LP,
          #IFNULL(CAST((c_articulo.peso * IFNULL(t_pendienteacomodo.Cantidad, 0)) AS DECIMAL(10,2)), 0) AS peso_total,
          IFNULL(CAST((c_articulo.peso) AS DECIMAL(10,2)), 0) AS peso_total,
          a.id AS id_articulo,
          IFNULL(th.Proyecto, '') as proyecto,
          c_proveedores.Nombre AS proveedor,
          V_EntradasContenedores.Cve_Proveedor AS  id_proveedor
          FROM V_EntradasContenedores
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_EntradasContenedores.Cve_articulo
          LEFT JOIN t_pendienteacomodo ON t_pendienteacomodo.cve_articulo = V_EntradasContenedores.Cve_articulo AND t_pendienteacomodo.cve_lote = V_EntradasContenedores.Cve_Lote AND t_pendienteacomodo.cve_ubicacion = '{$cve_ubicacion}' AND t_pendienteacomodo.ID_Proveedor = V_EntradasContenedores.Cve_Proveedor
          LEFT JOIN c_lotes ON c_lotes.LOTE = V_EntradasContenedores.Cve_Lote AND c_lotes.cve_articulo = V_EntradasContenedores.Cve_articulo 
          LEFT JOIN c_serie ON c_serie.numero_serie = V_EntradasContenedores.Cve_Lote AND c_serie.cve_articulo = V_EntradasContenedores.Cve_articulo 
          LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = V_EntradasContenedores.Cve_Proveedor
          LEFT JOIN td_entalmacen tde ON tde.fol_folio = V_EntradasContenedores.Fol_Folio AND tde.cve_articulo = V_EntradasContenedores.Cve_articulo AND tde.cve_lote = V_EntradasContenedores.Cve_Lote AND tde.status != 'Q' AND tde.status != 'M'
          LEFT JOIN th_entalmacen th ON th.Fol_Folio = V_EntradasContenedores.Fol_Folio
          LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo AND t_pendienteacomodo.cve_articulo = a.cve_articulo
          WHERE 0 = 0 
          AND V_EntradasContenedores.Cve_Ubicacion = '{$cve_ubicacion}'
          AND a.id <> ''
          AND V_EntradasContenedores.Cantidad_C = 0
          AND V_EntradasContenedores.Cve_Lote IN (SELECT cve_lote FROM V_ExistenciaGralProduccion WHERE tipo = 'area')

          {$sql_buscador}
          {$sql_lp_1}
          {$sql_clave}
          {$sql_serie}
          #AND V_EntradasContenedores.CantidadUbicada >= 0 

          #AND (((SELECT SUM(cantidad) FROM td_aduana WHERE num_orden = (SELECT id_ocompra FROM th_entalmacen WHERE fol_folio = V_EntradasContenedores.Fol_Folio)) != (SELECT SUM(IFNULL(CantidadUbicada, 0)) FROM td_entalmacen WHERE fol_folio = V_EntradasContenedores.Fol_Folio)) OR 
          #((SELECT SUM(IFNULL(CantidadUbicada, 0)) FROM td_entalmacen WHERE fol_folio = V_EntradasContenedores.Fol_Folio)!=(SELECT SUM(IFNULL(CantidadPedida, 0)) FROM td_entalmacen WHERE fol_folio = V_EntradasContenedores.Fol_Folio) AND V_EntradasContenedores.tipo != 'OC')
          #OR CONCAT(V_EntradasContenedores.Cve_Articulo,V_EntradasContenedores.Cve_Lote) IN (SELECT CONCAT(cve_articulo,cve_lote) FROM V_ExistenciaGral WHERE tipo = 'area'))

          #AND IFNULL(t_pendienteacomodo.Cantidad, 0) > 0 
          AND IFNULL(tde.CantidadRecibida - IFNULL(tde.CantidadUbicada, 0), 0) > 0
          AND IFNULL(V_EntradasContenedores.Clave_Contenedor, '') = ''
          GROUP BY folio, clave, lote, numero_serie
                UNION             
            
              SELECT DISTINCT V_EntradasContenedores.Fol_Folio AS folio,
              IFNULL(c_charolas.tipo, 'Articulo') AS tipo,
              IFNULL(a.control_abc, '') AS control_abc,
              IF(a.control_lotes = 'S', c_lotes.Lote,'') AS lote,
              IF(a.Caduca = 'S', IFNULL(IF(DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y') = '00-00-0000', '', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y')), ''), '') AS caducidad,
              IF(a.control_numero_series = 'S', V_EntradasContenedores.Cve_lote,'') AS numero_serie,
              SUM(IFNULL(CAST(((a.alto/1000)*(a.ancho/1000)*(a.fondo/1000)*V_EntradasContenedores.Cantidad_C) AS DECIMAL(10,6)),0)) AS volumen_ocupado,
              V_EntradasContenedores.Cve_Articulo AS clave,
              a.des_articulo AS descripcion,
              a.control_peso,
              V_EntradasContenedores.Cantidad_C AS num_existencia,
              #V_EntradasContenedores.CantidadRecibida AS num_existencia,
              #V_EntradasContenedores.Clave_Contenedor AS pallet_contenedor,
              #c_charolas.CveLP AS LP,
              IFNULL(c_charolas.clave_contenedor, '') AS pallet_contenedor,
              IFNULL(c_charolas.CveLP, '') AS LP,
              SUM(IFNULL(CAST((a.peso*V_EntradasContenedores.Cantidad_C) AS DECIMAL(10,2)),0)) AS peso_total,
              V_EntradasContenedores.Clave_Contenedor AS id_articulo,
              IFNULL(th.Proyecto, '') as proyecto,
              c_proveedores.Nombre AS proveedor,
              V_EntradasContenedores.Cve_Proveedor AS id_proveedor
              FROM V_EntradasContenedores JOIN c_articulo a ON a.cve_articulo = V_EntradasContenedores.Cve_articulo
              LEFT JOIN c_lotes ON c_lotes.LOTE = V_EntradasContenedores.Cve_Lote AND c_lotes.cve_articulo = V_EntradasContenedores.Cve_articulo
              LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = V_EntradasContenedores.Cve_Proveedor
              #LEFT JOIN c_charolas ON c_charolas.clave_contenedor = V_EntradasContenedores.Clave_Contenedor
              #LEFT JOIN c_charolas ON c_charolas.CveLP = V_EntradasContenedores.Clave_Contenedor
              LEFT JOIN c_charolas ON V_EntradasContenedores.Clave_Contenedor = c_charolas.clave_contenedor #IFNULL(c_charolas.CveLP, c_charolas.clave_contenedor)
              LEFT JOIN ts_existenciatarima tr ON tr.Fol_Folio = V_EntradasContenedores.Fol_Folio AND tr.cve_articulo = V_EntradasContenedores.Cve_Articulo AND tr.lote = V_EntradasContenedores.Cve_Lote
              LEFT JOIN th_entalmacen th ON th.Fol_Folio = V_EntradasContenedores.Fol_Folio
              LEFT JOIN td_entalmacenxtarima tt ON tt.fol_folio = V_EntradasContenedores.Fol_Folio AND tt.cve_articulo = V_EntradasContenedores.Cve_Articulo AND tt.cve_lote = V_EntradasContenedores.Cve_Lote AND tt.ClaveEtiqueta = V_EntradasContenedores.Clave_Contenedor
              WHERE V_EntradasContenedores.Cve_Ubicacion = '{$cve_ubicacion}' 
              #AND tr.ntarima NOT IN (SELECT ntarima FROM ts_existenciatarima WHERE Fol_Folio = tr.Fol_Folio)
              AND V_EntradasContenedores.Clave_Contenedor != ''
              AND V_EntradasContenedores.Cantidad_C > 0
              {$sql_buscador}
              {$sql_lp}
              {$sql_clave}
              {$sql_serie}
              AND IFNULL(tt.Ubicada, 'N') != 'S'
              #AND V_EntradasContenedores.Cantidad_C != V_EntradasContenedores.CantidadUbicada
              AND V_EntradasContenedores.CantidadRecibida > V_EntradasContenedores.CantidadUbicada
              GROUP BY V_EntradasContenedores.Fol_Folio,V_EntradasContenedores.Cve_Articulo,a.des_articulo, V_EntradasContenedores.Cve_Lote, V_EntradasContenedores.Cantidad_C,V_EntradasContenedores.Clave_Contenedor,c_proveedores.Nombre,V_EntradasContenedores.Cve_Proveedor
              ORDER BY folio DESC";

      $sth = \db()->prepare( $sql );
      $sth->execute();
      return $sth->fetchAll();
    }

    function getCapacidadDeUbicacion($idy_ubica) 
    {
      $sql = '
          SELECT
            idy_ubica,
          CAST((num_alto*num_largo*num_ancho)/1000000000 as DECIMAL(10,6)) as capacidad_volumetrica,
          CAST(PesoMaximo as DECIMAL(10,2)) as peso,
          cve_nivel AS ubicacion_piso,
          AcomodoMixto as amix
          FROM c_ubicacion
          where idy_ubica="'.$idy_ubica.'"
      ';
//       echo var_dump($sql);
//       die();
      $sth = \db()->prepare( $sql );
//         $sth->execute();
        $sth->execute( array( $this->idy_ubica ) );
      return $sth->fetchAll();
    }


    function moverAcomodo($data) 
    {
      extract($data);
      //$query = mysqli_query(\db2(), "SELECT e.fol_folio AS folio FROM td_entalmacen e WHERE id = {$idiorigen}");
      //$folio = mysqli_fetch_assoc($query)['folio'];
      $folio = '{$idiorigen}';
      $proyecto_acomodo = $proyecto;

      //$query = mysqli_query(\db2(), "INSERT IGNORE INTO `t_tipomovimiento` (`nombre`) VALUES ('Acomodo');");
      $query = mysqli_query(\db2(), "INSERT IGNORE INTO t_tipomovimiento (nombre) VALUES ('Acomodo');");
      //$query2 = mysqli_query(\db2(), "SELECT id_TipoMovimiento AS id FROM `t_tipomovimiento` WHERE nombre = 'Acomodo';");
      $query2 = mysqli_query(\db2(), "SELECT id_TipoMovimiento AS id FROM t_tipomovimiento WHERE nombre = 'Acomodo';");
      $movimiento = mysqli_fetch_assoc($query2)['id'];
      //$and = ($tipo != 'Por Pallet')?" and cve_articulo = '".$cve_articulo."' ":" and Contenedor_Ubicado = '".$cve_articulo."' ";
      $sql = "";
      if($tipo_pallet_contenedor_articulo == 'Articulo')
      {//tipo_pallet_contenedor_articulo
        $and = ($tipo != 3)?" and cve_articulo = '".$cve_articulo."' ":" and Contenedor_Ubicado = '".$cve_articulo."' ";
        //if($tipo != 'Por Pallet')
        $maximo = $cantidad_max;
        $query = \db()->prepare("SELECT cve_almacenp as almacen FROM c_almacen WHERE cve_almac = '$cve_almacf'");
        $query->execute();
        $almacen = $query->fetch()['almacen'];

      $sql = ""; $sql_trazabilidad = "";
      if(floatval($cantidadTotal) <= floatval($maximo))
      {//cantidadTotal
        //if($tipo != 'Por Pallet')
        if($tipo != '3')
        {//tipo3
          //CantidadDisponible = CantidadRecibida - IFNULL(CantidadUbicada, 0) 
          $sql = 
            "UPDATE td_entalmacen 
                SET CantidadUbicada = IFNULL(CantidadUbicada, 0) + {$cantidadTotal}, CantidadDisponible = CantidadRecibida - {$cantidadTotal} 
                WHERE fol_folio = {$idiorigen}
                 and cve_articulo = '{$cve_articulo}'
                 and cve_lote = '{$cve_lote}';";
        }//tipo3
          $sql .= 
            "UPDATE t_pendienteacomodo 
                  SET Cantidad = Cantidad - {$cantidadTotal} 
                WHERE cve_articulo = '{$cve_articulo}'
                  AND cve_ubicacion = '{$cve_almaci}'
                  AND cve_lote = '{$cve_lote}'
                  AND ID_Proveedor = {$ID_Proveedor};";

          $query = \db()->prepare("SELECT Existencia FROM ts_existenciapiezas WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND idy_ubica = '{$ididestino}' AND ID_Proveedor = '$ID_Proveedor'");
          $query->execute();
          $Existencia_piezas_inicial = $query->fetch()['Existencia'];
          $stockinicial = $Existencia_piezas_inicial;
          $ajuste = $stockinicial + $cantidadTotal;

          $sql .= 
            "INSERT INTO `t_cardex` (`cve_articulo`, `cve_lote`, `fecha`, `origen`, `destino`, `cantidad`, `stockinicial`, `ajuste`, `id_TipoMovimiento`, `cve_usuario`, `Cve_Almac`, `Activo`) 
             VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$cve_almaci}', '{$ididestino}','{$cantidadTotal}','{$stockinicial}','{$ajuste}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1);";

        //if ($tipo =="Por Pieza")
        if ($tipo == '1')
        {//tipo1
          $sql.=
            "INSERT INTO ts_existenciapiezas (cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) 
               VALUES ('{$almacen}', '$ididestino', '$cve_articulo', '$cve_lote', '$cantidad', '$ID_Proveedor') 
               ON DUPLICATE KEY UPDATE
               Existencia=Existencia+$cantidad,ID_Proveedor= $ID_Proveedor;";

          $query_traz = \db()->prepare("
            SELECT COUNT(*) as existe 
            FROM t_trazabilidad_existencias t 
            LEFT JOIN th_entalmacen e ON e.Fol_Folio = t.folio_entrada
            LEFT JOIN th_aduana oc ON oc.num_pedimento = IF(e.tipo != 'RL' AND e.id_ocompra IS NOT NULL, e.id_ocompra, NULL)            
            WHERE t.cve_almac = '{$almacen}' AND t.idy_ubica = '$ididestino' AND CONVERT(t.cve_articulo, CHAR) = '$cve_articulo' AND CONVERT(t.cve_lote, CHAR) = '$cve_lote' AND t.id_proveedor = '$ID_Proveedor' AND t.id_tipo_movimiento = 2 AND CONVERT(IFNULL(e.Fact_Prov, ''), CHAR) = CONVERT(IFNULL(t.factura_ent, ''), CHAR) AND CONVERT(IFNULL(e.Proyecto, ''), CHAR) = CONVERT(IFNULL(t.proyecto, ''), CHAR) AND CONVERT(IFNULL(oc.Factura, ''), CHAR) = CONVERT(IFNULL(t.factura_oc, ''), CHAR) AND IFNULL(t.ntarima, '') = '' AND CONVERT(IFNULL(t.proyecto, ''), CHAR) = '$proyecto_acomodo'");
          $query_traz->execute();
          $existe_traz = $query_traz->fetch()['existe'];


          $sql_traz = \db()->prepare("
          SELECT e.Fol_Folio AS folio_entrada, oc.num_pedimento as folio_oc, IFNULL(e.Fact_Prov, '') as factura_ent, IFNULL(oc.Factura, '') as factura_oc, IFNULL(e.Proyecto, '') as proyecto
          FROM t_trazabilidad_existencias t 
          LEFT JOIN th_entalmacen e ON e.Fol_Folio = t.folio_entrada
          LEFT JOIN th_aduana oc ON oc.num_pedimento = IF(e.tipo != 'RL' AND e.id_ocompra IS NOT NULL, e.id_ocompra, NULL)            
          WHERE t.cve_almac = '{$almacen}' AND t.idy_ubica IS NULL AND CONVERT(t.cve_articulo, CHAR) = '$cve_articulo' AND CONVERT(t.cve_lote, CHAR) = '$cve_lote' AND t.id_proveedor = '$ID_Proveedor' AND t.id_tipo_movimiento = 1 AND CONVERT(IFNULL(e.Fact_Prov, ''), CHAR) = CONVERT(IFNULL(t.factura_ent, ''), CHAR) AND CONVERT(IFNULL(e.Proyecto, ''), CHAR) = CONVERT(IFNULL(t.proyecto, ''), CHAR) AND CONVERT(IFNULL(oc.Factura, ''), CHAR) = CONVERT(IFNULL(t.factura_oc, ''), CHAR) AND IFNULL(t.ntarima, '') = '' AND CONVERT(IFNULL(t.proyecto, ''), CHAR) = '$proyecto_acomodo'");
          $sql_traz->execute();
          $valores_traz = $sql_traz->fetch();

          if($valores_traz['folio_entrada'])
          {
          extract($valores_traz);


          if(!$existe_traz)
          {
            $query_traz = \db()->prepare("INSERT INTO t_trazabilidad_existencias(cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ('{$almacen}', '$ididestino', '$cve_articulo', '$cve_lote', '$cantidad', '$ID_Proveedor', '{$idiorigen}', '$folio_oc', '$factura_ent', '$factura_oc', '$proyecto_acomodo', 2)");
            $query_traz->execute();
          }
          else
          {
            $query_traz = \db()->prepare("UPDATE t_trazabilidad_existencias SET cantidad = cantidad+$cantidad WHERE idy_ubica = '$ididestino' AND cve_articulo = '$cve_articulo' AND cve_lote = '$cve_lote' AND id_proveedor = '$ID_Proveedor' AND id_tipo_movimiento = 2 AND IFNULL(factura_ent, '') = '$factura_ent' AND IFNULL(proyecto, '') = '$proyecto_acomodo' AND IFNULL(factura_oc, '') = '$factura_oc'");
            $query_traz->execute();
          }
          }

        }//tipo1
        //else if ($tipo == "Por Caja")
        else if ($tipo == '2')
        {//tipo2

          $query = \db()->prepare("SELECT num_multiplo FROM c_articulo WHERE cve_articulo = '{$cve_articulo}'");
          $query->execute();
          $num_multiplo = $query->fetch()['num_multiplo'];

          $cantidad *= $num_multiplo;
          $sql.=
            "INSERT INTO ts_existenciacajas (cve_almac, idy_ubica, cve_articulo, cve_lote,PiezasXCaja, Existencia, ID_Proveedor)
             VALUES ('$almacen', '$ididestino', '$cve_articulo', '$cve_lote','$piezaxcaja', '$cantidad', '$ID_Proveedor') 
               ON DUPLICATE KEY UPDATE
               Existencia=Existencia+$cantidad,ID_Proveedor= $ID_Proveedor;";
        }//tipo2
        //else if ($tipo == "Por Pallet")
        else if ($tipo == '3')
        {//tipo3else
					$query = \db()->prepare
					 (
					 "SELECT
								c_almacenp.clave AS almacen
								FROM c_almacenp
								WHERE c_almacenp.id = '{$almacen}'");
     			$query->execute();
     		 	$alma = $query->fetch()['almacen'];
					//echo var_dump($alma);
					//die();
          $sql2 = \db()->prepare
          (
            "SELECT
                V_EntradasContenedores.Cve_Articulo as cve_articulo,
                V_EntradasContenedores.Cve_Lote as lote,
                IF(PzsXCaja_C = 1, V_EntradasContenedores.CantidadRecibida, V_EntradasContenedores.Cantidad_C) as Existencia
              FROM V_EntradasContenedores
                left join c_charolas on c_charolas.clave_contenedor = V_EntradasContenedores.Clave_Contenedor
              WHERE V_EntradasContenedores.Fol_Folio = '{$idiorigen}'
                and c_charolas.IDContenedor = '{$cve_articulo}'
                ;"
          );
           $sql2->execute();
           $articulos = $sql2-> fetchAll();;
         
          foreach ($articulos as $articulo)
          {
              $cve_arti = $articulo["cve_articulo"];
              $lote = $articulo["lote"];
              $existencia = $articulo["Existencia"];
              $sql1=\db()->prepare(" 
                INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) 
                VALUES ('{$almacen}','$ididestino', '$cve_arti', '$lote', $idiorigen, $cve_articulo, '', '$existencia', 1, '$ID_Proveedor', '');" );
             $sql1->execute();
            
             $sql3 =\db()->prepare(
              "UPDATE td_entalmacen 
                 SET CantidadUbicada = '$existencia', CantidadDisponible = CantidadRecibida - IFNULL(CantidadUbicada, 0) 
               WHERE fol_folio = $idiorigen
                 and cve_articulo = '$cve_arti'
                 and cve_lote = '$lote';");
              echo var_dump($sql3);
              $sql3->execute();
          }
          $sql .="
            INSERT INTO t_MovCharolas (Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) 
            VALUES ('{$alma}', '{$cve_articulo}', NOW(), '$cve_almaci', '{$ididestino}', 2, '{$cve_usuario}', 'I');";
        }//tipo3else

      }//cantidadTotal
      else
      {
        return false;
      }
    }//tipo_pallet_contenedor_articulo
    else
    {
        $query = \db()->prepare("SELECT cve_almacenp as almacen FROM c_almacen WHERE cve_almac = '$cve_almacf'");
        $query->execute();
        $almacen = $query->fetch()['almacen'];

      $sql = "";

//*****************************

          $query_result = "SELECT
                c_almacenp.clave AS almacen
                FROM c_almacenp
                WHERE c_almacenp.id = '{$almacen}'";

          $query = \db()->prepare
           (
           "SELECT
                c_almacenp.clave AS almacen
                FROM c_almacenp
                WHERE c_almacenp.id = '{$almacen}'");
          $query->execute();
          $alma = $query->fetch()['almacen'];
          //echo var_dump($alma);
          //die();
          $query = \db()->prepare
           (
           "SELECT DISTINCT IDContenedor,CveLP FROM c_charolas WHERE clave_contenedor = '{$pallet_contenedor}' OR CveLP ='{$pallet_contenedor}' LIMIT 1"
           );
          $query->execute();
          $rowIDContenedor = $query->fetch();
          $IDContenedor = $rowIDContenedor['IDContenedor'];
          $CveLPContenedor = $rowIDContenedor['CveLP'];



          $sql2 = "";
//"SELECT * FROM td_entalmacenxtarima WHERE ClaveEtiqueta = (SELECT CveLP FROM c_charolas WHERE clave_contenedor = '{$pallet_contenedor}');"
          $sql2 = \db()->prepare
          (
            "SELECT DISTINCT * FROM td_entalmacenxtarima WHERE ClaveEtiqueta IN ('{$pallet_contenedor}', '{$CveLPContenedor}');"
          );
           $sql2->execute();
           $articulos = $sql2-> fetchAll();

          $cve_almac = $alma;
          $idy_ubica = $ididestino;
          $ntarima = $IDContenedor;
          foreach ($articulos as $articulo)
          {

              $cve_articulo = $articulo["cve_articulo"];
              $lote = $articulo["cve_lote"];
              $Fol_Folio = $articulo["fol_folio"];
              $capcidad = 0;
              $existencia = $articulo["Cantidad"];
              $Activo = 1;
              //$ID_Proveedor;
              $Cuarentena = 0;

              $sql1=\db()->prepare(" 
                INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) 
                VALUES ('{$almacen}', $idy_ubica, '$cve_articulo', '$lote', $Fol_Folio, $ntarima, $capcidad, $existencia, $Activo, $ID_Proveedor, $Cuarentena)
                ON DUPLICATE KEY UPDATE
               Existencia=$existencia,ID_Proveedor= $ID_Proveedor;" );
             $sql1->execute();
/*
             $sql2 =\db()->prepare(
              "UPDATE td_entalmacen 
                 SET CantidadUbicada = IFNULL(CantidadUbicada, 0)+$existencia, CantidadDisponible = CantidadRecibida - IFNULL(CantidadUbicada, 0) 
               WHERE fol_folio = $Fol_Folio
                 and cve_articulo = '$cve_articulo'
                 and cve_lote = '$lote';");
*/
             $sql2 =\db()->prepare(
              "UPDATE td_entalmacen 
                 SET CantidadUbicada = $existencia, CantidadDisponible = 0 
               WHERE fol_folio = $Fol_Folio
                 and cve_articulo = '$cve_articulo'
                 and cve_lote = '$lote';");
              //echo var_dump($sql3);
              $sql2->execute();

             $sql3 =\db()->prepare(
              "UPDATE t_pendienteacomodo 
                    SET Cantidad = Cantidad - {$existencia} 
                  WHERE cve_articulo = '$cve_articulo'
                    AND cve_ubicacion = '$cve_almaci'
                    AND cve_lote = '$lote'
                    AND ID_Proveedor = $ID_Proveedor;");
              //echo var_dump($sql3);
              $sql3->execute();


          $query_traz = \db()->prepare("
            SELECT COUNT(*) as existe 
            FROM t_trazabilidad_existencias t 
            LEFT JOIN th_entalmacen e ON e.Fol_Folio = t.folio_entrada
            LEFT JOIN th_aduana oc ON oc.num_pedimento = IF(e.tipo != 'RL' AND e.id_ocompra IS NOT NULL, e.id_ocompra, NULL)            
            WHERE t.cve_almac = '{$almacen}' AND t.idy_ubica = '$ididestino' AND CONVERT(t.cve_articulo, CHAR) = '$cve_articulo' AND CONVERT(t.cve_lote, CHAR) = '$cve_lote' AND t.id_proveedor = '$ID_Proveedor' AND t.id_tipo_movimiento = 2 AND CONVERT(IFNULL(e.Fact_Prov, ''), CHAR) = CONVERT(IFNULL(t.factura_ent, ''), CHAR) AND CONVERT(IFNULL(e.Proyecto, ''), CHAR) = CONVERT(IFNULL(t.proyecto, ''), CHAR) AND CONVERT(IFNULL(oc.Factura, ''), CHAR) = CONVERT(IFNULL(t.factura_oc, ''), CHAR) AND IFNULL(t.ntarima, '') = '$ntarima' AND CONVERT(IFNULL(t.proyecto, ''), CHAR) = '$proyecto_acomodo'");
          $query_traz->execute();
          $existe_traz = $query_traz->fetch()['existe'];


          $sql_traz = \db()->prepare("
          SELECT e.Fol_Folio AS folio_entrada, oc.num_pedimento as folio_oc, IFNULL(e.Fact_Prov, '') as factura_ent, IFNULL(oc.Factura, '') as factura_oc, IFNULL(e.Proyecto, '') as proyecto
          FROM t_trazabilidad_existencias t 
          LEFT JOIN th_entalmacen e ON e.Fol_Folio = t.folio_entrada
          LEFT JOIN th_aduana oc ON oc.num_pedimento = IF(e.tipo != 'RL' AND e.id_ocompra IS NOT NULL, e.id_ocompra, NULL)            
          WHERE t.cve_almac = '{$almacen}' AND t.idy_ubica IS NULL AND CONVERT(t.cve_articulo, CHAR) = '$cve_articulo' AND CONVERT(t.cve_lote, CHAR) = '$cve_lote' AND t.id_proveedor = '$ID_Proveedor' AND t.id_tipo_movimiento = 1 AND CONVERT(IFNULL(e.Fact_Prov, ''), CHAR) = CONVERT(IFNULL(t.factura_ent, ''), CHAR) AND CONVERT(IFNULL(e.Proyecto, ''), CHAR) = CONVERT(IFNULL(t.proyecto, ''), CHAR) AND CONVERT(IFNULL(oc.Factura, ''), CHAR) = CONVERT(IFNULL(t.factura_oc, ''), CHAR) AND IFNULL(t.ntarima, '') = '$ntarima' AND CONVERT(IFNULL(t.proyecto, '') ,CHAR) = '$proyecto_acomodo'");
          $sql_traz->execute();
          $valores_traz = $sql_traz->fetch();

          if($valores_traz['folio_entrada'])
          {
          extract($valores_traz);


          if(!$existe_traz)
          {
            $query_traz = \db()->prepare("INSERT INTO t_trazabilidad_existencias(cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ('{$almacen}', '$ididestino', '$cve_articulo', '$cve_lote', '$cantidad', '$ntarima', '$ID_Proveedor', '{$idiorigen}', '$folio_oc', '$factura_ent', '$factura_oc', '$proyecto_acomodo', 2)");
            $query_traz->execute();
          }
          else
          {
            $query_traz = \db()->prepare("UPDATE t_trazabilidad_existencias SET cantidad = cantidad+$cantidad WHERE idy_ubica = '$ididestino' AND cve_articulo = '$cve_articulo' AND cve_lote = '$cve_lote' AND id_proveedor = '$ID_Proveedor' AND id_tipo_movimiento = 2 AND IFNULL(factura_ent, '') = '$factura_ent' AND IFNULL(proyecto, '') = '$proyecto_acomodo' AND IFNULL(factura_oc, '') = '$factura_oc' AND ntarima = '$ntarima'");
            $query_traz->execute();
          }
          }





          $query = \db()->prepare("SELECT existencia FROM ts_existenciatarima WHERE cve_articulo = '{$cve_articulo}' AND lote = '{$lote}' AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = '$ID_Proveedor' AND ntarima = '$ntarima'");
          $query->execute();
          $Existencia_tarima_inicial = $query->fetch()['existencia'];
          $stockinicial = $Existencia_tarima_inicial-$existencia;
          $ajuste = $stockinicial + $cantidadTotal;

             $sql5 =\db()->prepare(
              "INSERT INTO `t_cardex` (`cve_articulo`, `cve_lote`, `fecha`, `origen`, `destino`, `cantidad`, `stockinicial`, `ajuste`, `id_TipoMovimiento`, `cve_usuario`, `Cve_Almac`, `Activo`) 
             VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '$cve_almaci', '{$ididestino}','{$existencia}','{$stockinicial}','{$ajuste}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1);");
              //echo var_dump($sql3);
              $sql5->execute();

             $sql4 =\db()->prepare(
              "INSERT IGNORE INTO t_MovCharolas (Cve_Almac, id_kardex, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) 
            VALUES ('$cve_almac', (SELECT MAX(id) FROM t_cardex), '$ntarima', NOW(), '$cve_almaci', '{$ididestino}', 2, '{$cve_usuario}', 'I');");
              //echo var_dump($sql3);
              $sql4->execute();


          }
//"UPDATE td_entalmacenxtarima SET Ubicada='S' WHERE ClaveEtiqueta=(SELECT CveLP FROM c_charolas WHERE clave_contenedor = '{$pallet_contenedor}');");

           $sql6 =\db()->prepare(
            "UPDATE td_entalmacenxtarima SET Ubicada='S' WHERE ClaveEtiqueta IN ('{$pallet_contenedor}', '{$CveLPContenedor}');");
            //echo var_dump($sql3);
            $sql6->execute();


      //*******************************************************************************
      //                          EJECUTAR EN INFINITY
      //*******************************************************************************
      $query = mysqli_query(\db2(), "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'");
      $ejecutar_infinity = mysqli_fetch_assoc($query)['existe'];


      $query = mysqli_query(\db2(), "SELECT tipo FROM th_entalmacen WHERE Fol_Folio = '$Fol_Folio'");
      $tipo_entrada = mysqli_fetch_assoc($query)['tipo'];


      if($ejecutar_infinity && $tipo_entrada == 'OC')
      {

        $query = mysqli_query(\db2(), "SELECT Url, Servicio, User, IFNULL(Puerto, '8080') as Puerto, Pswd, Empresa, to_base64(CONCAT(USER,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl FROM c_datos_ws WHERE Servicio = 'wms_entr' AND Activo = 1");
        $row_infinity = mysqli_fetch_assoc($query);
        $Url_inf = $row_infinity['Url'];
        $url_curl = $row_infinity['url_curl'];
        $Servicio_inf = $row_infinity['Servicio'];
        $User_inf = $row_infinity['User'];
        $Puerto = $row_infinity['Puerto'];
        $Pswd_inf = $row_infinity['Pswd'];
        $Empresa_inf = $row_infinity['Empresa'];
        $Codificado = $row_infinity['Codificado'];

        $sql = "SELECT DISTINCT 
                  IFNULL(adt.Ref_Docto, '') AS Ref_Docto,
                  IFNULL(adt.Item, '') AS Item,
                  IFNULL(ed.cve_articulo, '') AS cve_articulo,
                  IFNULL(ed.cve_lote, '') AS cve_lote,
                  IFNULL(ed.CantidadRecibida, '') AS CantidadRecibida,
                  IFNULL(um.cve_umed, '') AS cve_umed,
                  IFNULL(ad.Cve_Almac, '') AS Cve_Almac,
                  IFNULL(DATE_FORMAT(e.HoraInicio, '%Y-%m-%d %H:%i:%s'), '') AS HoraInicio,
                  IFNULL(DATE_FORMAT(e.Fec_Factura_Prov, '%Y-%m-%d'), '') AS Fec_Factura_Prov,
                  IFNULL(e.Fact_Prov, '') AS Fact_Prov
                FROM th_entalmacen e
                LEFT JOIN td_entalmacen ed ON ed.fol_folio = e.Fol_Folio 
                LEFT JOIN c_articulo a ON a.cve_articulo = ed.cve_articulo
                LEFT JOIN th_aduana ad ON ad.num_pedimento = e.id_ocompra
                LEFT JOIN td_aduana adt ON ad.num_pedimento = adt.num_orden
                LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
                WHERE e.Fol_Folio = '$Fol_Folio'";

      $query = mysqli_query(\db2(), $sql);
      $json = '[';
      
      while($row = mysqli_fetch_assoc($query))
      {
        extract($row);
        $json .= '{"serdoc":"'.$Ref_Docto.'","rowdoc":"'.$Item.'","item":"'.$cve_articulo.'", "batch": "'.$cve_lote.'","qty":'.$CantidadRecibida.',"um":"'.$cve_umed.'","typeMov":"E","warehouse":"'.$Cve_Almac.'","dataOpe":"'.$HoraInicio.'","dataDoc":"'.$Fec_Factura_Prov.'","numDoc":"'.$Fact_Prov.'"},';
      }
      $json[strlen($json)-1] = ' ';
      $json .= ']';


          $curl = curl_init();

          curl_setopt_array($curl, array(
            // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
            CURLOPT_URL => "$url_curl",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>
            // Aquí cambia la cadena JSON
            $json,
            CURLOPT_HTTPHEADER => array(
              'Content-Type: application/json',
              'Authorization: Basic {$Codificado}'
            ),
          ));

          $response = curl_exec($curl);

          curl_close($curl);      
          //echo $response;
          $query = mysqli_query(\db2(), "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '{$json}', 'Traspaso entre Almacenes', 'WEB')");

      }
      //*******************************************************************************
      //*******************************************************************************



        return 1;
    }

    $query = \db()->prepare
     (
     "DELETE FROM t_pendienteacomodo WHERE Cantidad <= 0"
     );
    $query->execute();


      $sth = \db()->prepare($sql);
      $sth->execute();
      return $sql;
    }

    function XmoverTrasladoX($data) {
        extract($data);

        $query = mysqli_query(\db2(), "INSERT IGNORE INTO `t_tipomovimiento` (`nombre`) VALUES ('Traslado');");
        $query2 = mysqli_query(\db2(), "SELECT id_TipoMovimiento AS id FROM `t_tipomovimiento` WHERE nombre = 'Traslado';");
        $movimiento = mysqli_fetch_assoc($query2)['id'];


        $traslado_almacen = $data["traslado_almacen"];

        $almacen = '';
        $clave_almacen = '';
        if($traslado_almacen)
        {
          $almacen = $data['almacen_destino'];
          $query3 = mysqli_query(\db2(), "SELECT clave FROM c_almacenp WHERE id = {$almacen}");
          $clave_almacen = mysqli_fetch_assoc($query3)['clave'];
        }
        else 
        {
          $query3 = mysqli_query(\db2(), "SELECT cve_almacenp AS id FROM c_almacen WHERE cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = '$ididestino')");
          $almacen = mysqli_fetch_assoc($query3)['id'];
        }

        $id_piezas = 0; $id_cajas = 0; $id_tarima = 0;

        $valor_lp         = $data["lp_val"];
        $valor_pallet     = $data["pallet_val"];
        $valor_origen     = $data["idiorigen"];
        $valor_destino    = $data["ididestino"];

      $sql = "";
      if($tipo_pallet_contenedor_articulo == 'Artículo')
      {
        // primero busco quien tiene esa cantidad
        $sql2= "SELECT * FROM ts_existenciapiezas where  cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."' and existencia>0 LIMIT 1;";// and Existencia>=".$data['cantidadTotal']."
        //echo $sql2;


        $sth = \db()->prepare( $sql2 );
        $sth->execute();

        if (!$sth->rowCount()){
            //si no hay nada x pieza, busco x caja
            $sql2= "SELECT  sum(PiezasXCaja) as PiezasXCaja,
            sum(Existencia) as Existencia FROM ts_existenciacajas where  cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."' and PiezasXCaja >".$data['cantidadTotal'].";";
             //echo $sql2;
            $sth = \db()->prepare( $sql2 );
            $sth->execute();

            $registro= $sth->fetch();
            $PiezasXCaja = $registro["PiezasXCaja"];
            $Existencia = $registro["Existencia"];

            //echo " - PIEZASXCAJA = ".$PiezasXCaja." - EXISTENCIA = ".$Existencia." - ";

            if ( !$sth->rowCount() || ($PiezasXCaja=="" && $Existencia=="") ){
                //si no hay nada x caja, busco x pallet
                $sql2= "SELECT sum(existencia) as existencia, sum(capcidad) as capcidad FROM ts_existenciatarima where  cve_articulo='".$data["cve_articulo"]."' and lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."' and existencia*capcidad>".$data['cantidadTotal']." and existencia>1;";
                 //echo $sql2;
                $sth = \db()->prepare( $sql2 );
                $sth->execute();

                $registro= $sth->fetch();
                $existencia = $registro["existencia"];
                $capcidad = $registro["capcidad"];

                if (!$sth->rowCount() || ($existencia=="" && $capcidad=="") ){

                        //echo "CAI EN PALIDA";exit;
                }else{
                    //consegui x tarima

                    $piezasAire=(int)$registro["existencia"]*(int)$registro["capcidad"];

                    $id_tarima = $registro["id"];

                    $sql2= "DELETE from  ts_existenciatarima where  cve_articulo='".$data["cve_articulo"]."' and lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."' and existencia=".$registro["existencia"]." and capcidad=".$registro["capcidad"]." ;";

                    //echo $sql2;
                    $sth = \db()->prepare( $sql2 );
                    $sth->execute();
                }
            } else {
                //consegui x CAJA

                $registro= $sth->fetch();

                if($registro["Existencia"] === '')
                    $registro["Existencia"] = 0;

                $id_cajas = $registro["id"];

                $piezasAire=(int)$registro["Existencia"]*(int)$registro["PiezasXCaja"];

                $sql2="DELETE FROM ts_existenciacajas where cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."' and PiezasXCaja='".$registro["PiezasXCaja"]."' ;";//and Existencia='".$registro["Existencia"]."'
                //echo $sql2;
                $sth = \db()->prepare( $sql2 );
                $sth->execute(); //pendiente
              
            }
        } else {

            //si consegui x pieza

            //$cantidad_piezas = $sth->rowCount();

            $registro= $sth->fetch();
            $piezasAire=(int)$registro["Existencia"];

            if($registro["Existencia"] === '')
                $registro["Existencia"] = 0;

            $id_piezas = $registro["id"];

            $sql2="UPDATE ts_existenciapiezas set Existencia=0 where cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."' and Existencia='".$registro["Existencia"]."';";
            //$sql2="UPDATE ts_existenciapiezas set Existencia=0 where id=$id_piezas;";

                //echo $sql2;
            $sth = \db()->prepare( $sql2 );
            $sth->execute();
        }
        //guardo lo que quedo volando

            $piezasSobran=$piezasAire-$data["cantidadTotal"];

        // echo "PIEZAS QUE SOBRANS SON ".$piezasAire;


        $sql.=" INSERT INTO ts_existenciapiezas (cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) 
        VALUES ('{$almacen}', '".$data["idiorigen"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$piezasSobran."', '".$ID_Proveedor."') 
        ON DUPLICATE KEY UPDATE Existencia=Existencia+".$piezasSobran.",ID_Proveedor='".$ID_Proveedor."';";

        if ($data["tipo"]==1 && !$traslado_almacen){//"Por Pieza"

            //$sql.= "UPDATE ts_existenciapiezas SET Existencia=Existencia-".$data["cantidad"]." WHERE cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."' and ID_Proveedor='".$ID_Proveedor."';";
            //$sql.= "UPDATE ts_existenciapiezas SET Existencia=Existencia-".$data["cantidad"]." WHERE id=$id_piezas;";
              $sql.=" INSERT INTO ts_existenciapiezas (cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) 
              VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$data["cantidad"]."', '".$ID_Proveedor."') 
              ON DUPLICATE KEY UPDATE Existencia=Existencia+".$data["cantidad"].",ID_Proveedor='".$ID_Proveedor."';";
        }

        if ($data["tipo"]==2){//"Por Caja"

            //$sql.= "UPDATE ts_existenciacajas SET Existencia=Existencia-".$data["cantidad"]." WHERE cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."' and Existencia>=".$data['cantidadTotal']." and ID_Proveedor='".$ID_Proveedor."';";

            //$sql.= "UPDATE ts_existenciacajas SET Existencia=Existencia-".$data["cantidad"]." WHERE id=$id_cajas;";
          /*
            if(!$traslado_almacen)
            $sql.=" INSERT INTO ts_existenciacajas (cve_almac, idy_ubica, cve_articulo, cve_lote,PiezasXCaja, Existencia, ID_Proveedor) 
            VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."','".$data["piezaxcaja"]."', '".$data["cantidad"]."', '".$ID_Proveedor."') 
            ON DUPLICATE KEY UPDATE Existencia=Existencia+".$data["cantidad"].",ID_Proveedor='".$ID_Proveedor."';";
            */
        }

        if ($data["tipo"]==3){//"Por Pallet"

          $sql.= "UPDATE ts_existenciatarima SET Existencia=Existencia-".$data["cantidad"]." WHERE cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."' and Existencia>=".$data['cantidadTotal']." and ID_Proveedor='".$ID_Proveedor."';";

          //$sql.= "UPDATE ts_existenciatarima SET Existencia=Existencia-".$data["cantidad"]." WHERE id=$id_tarima;";

          if(!$traslado_almacen)
           $sql.=" INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, existencia, ID_Proveedor) 
           VALUES ('{$almacen}','".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$data["cantidad"]."', '".$ID_Proveedor."') 
           ON DUPLICATE KEY UPDATE Existencia=Existencia+".$data["cantidad"].",ID_Proveedor='".$ID_Proveedor."';";
        }
    }
    else if(!$traslado_almacen)
    {

        $sql.= "UPDATE ts_existenciatarima SET idy_ubica = '".$valor_destino."' WHERE idy_ubica = '".$valor_origen."';";
    }
        $sql.=" delete from ts_existenciapiezas where Existencia<=0;
            
            delete from ts_existenciatarima where Existencia<=0;
        ";
        //delete from ts_existenciacajas where Existencia<=0;
/*
        $sql .= "UPDATE t_pendienteacomodo 
                  SET Cantidad = Cantidad - {$cantidadTotal} 
                  WHERE cve_articulo = '{$cve_articulo}'
                  AND cve_ubicacion = '{$cve_almaci}'
                  AND cve_lote = '{$cve_lote}';";
*/

//        $sql .= "UPDATE c_charolas SET CveLP = '' WHERE clave_contenedor = '$valor_origen'";
//        $sql .= "UPDATE c_charolas SET CveLP = '$valor_lp' WHERE clave_contenedor = '$valor_origen'";

        if($traslado_almacen)
        {
            $cve_articulo = $data["cve_articulo"];

            $id_proveedor = $data["ID_Proveedor"];
            $cve_lote     = $data["cve_lote"];
            $idiorigen = $data["idiorigen"];
            $idy_ubica = $data["idiorigen"];
            $cve_usuario = $data["cve_usuario"];
            $zonarecepcioni_alm = $data["zonarecepcioni_alm"];

            //$query2 = mysqli_query(\db2(), "SELECT id_TipoMovimiento AS id FROM `t_tipomovimiento` WHERE nombre = 'Traslado';");
            //$movimiento = mysqli_fetch_assoc($query2)['id'];

            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            $sql_tr = "SELECT t.ID_Protocolo, t.Consec_protocolo FROM t_cardex o 
                    LEFT JOIN th_entalmacen t ON t.Fol_Folio = o.origen 
                    WHERE o.Id_TipoMovimiento = 1 AND t.STATUS = 'E'
                    AND o.destino = (
                             SELECT d.origen 
                             FROM t_cardex d 
                             WHERE d.destino = '$idiorigen'
                             AND d.Id_TipoMovimiento = 2 AND d.cve_articulo = '$cve_articulo' AND d.cve_lote = '$cve_lote'
                        )
                    AND o.cve_articulo = '$cve_articulo' AND o.cve_lote = '$cve_lote'
                    LIMIT 1";
            $rs = mysqli_query($conn, $sql_tr);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $ID_Protocolo = $resul['ID_Protocolo'];
            //$Consec_protocolo = $resul['Consec_protocolo'];
            //para que extraiga el ID_Protocolo y el Consec_protocolo, el kardex debe estar perfecto, así que por si acaso
            //si no encuentra el dato voy a consultar el primer registro que generalmente se cumple más
            if(!$ID_Protocolo) 
            {
              $sql_tr = "SELECT  ID_Protocolo, FOLIO as Consec_protocolo FROM t_protocolo WHERE descripcion LIKE 'Nacional%' ORDER BY id LIMIT 1";
              $rs = mysqli_query($conn, $sql_tr);
              $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
              $ID_Protocolo = $resul['ID_Protocolo'];
              $Consec_protocolo = $resul['Consec_protocolo'];
            }
            else
            {
              $sql_tr = "SELECT FOLIO as Consec_protocolo FROM t_protocolo WHERE ID_Protocolo = '{$ID_Protocolo}'";
              $rs = mysqli_query($conn, $sql_tr);
              $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
              $Consec_protocolo = $resul['Consec_protocolo'];
            }

            $Consec_protocolo++;

            $sql_tr = "UPDATE t_protocolo SET FOLIO = {$Consec_protocolo} WHERE id = {$ID_Protocolo}";
            $rs = mysqli_query($conn, $sql_tr);


            $idiorigen    = $data["almacen_origen"];
            $ididestino   = $data["almacen_destino"];

            $sql_tr = "INSERT INTO th_entalmacen(Cve_Almac, Fec_Entrada, Cve_Proveedor, STATUS, Cve_Usuario, Cve_Autorizado, tipo, HoraInicio, ID_Protocolo, Consec_protocolo, cve_ubicacion, HoraFin) VALUES({$clave_almacen}, NOW(), {$id_proveedor}, 'E', '{$cve_usuario}', '{$cve_usuario}', 'TR', NOW(), '{$ID_Protocolo}', {$Consec_protocolo}, '{$zonarecepcioni_alm}', NOW());";
            //echo $sql_tr;
            $rs = mysqli_query($conn, $sql_tr);

            $sql_tr = "SELECT MAX(Fol_Folio) as Folio FROM th_entalmacen";
            $rs = mysqli_query($conn, $sql_tr);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $Folio = $resul['Folio'];


            $valor_lp         = $data["lp_val"];
            $valor_pallet     = $data["pallet_val"];

            if($valor_lp)
            {
                $sql_tr = "UPDATE c_charolas SET cve_almac = {$ididestino} WHERE CveLP = '{$valor_lp}'";
                $rs = mysqli_query($conn, $sql_tr);

                $sql_tr = "INSERT INTO td_entalmacenxtarima(fol_folio, cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, Ubicada, PzsXCaja) 
                        VALUES ({$Folio}, '{$cve_articulo}', '{$cve_lote}', '{$valor_lp}', {$cantidadTotal}, 'N', 1)";
                $rs = mysqli_query($conn, $sql_tr);

                $sql_tr = "UPDATE ts_existenciatarima SET existencia = existencia-{$cantidadTotal} WHERE idy_ubica = '{$idy_ubica}' AND cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '$valor_lp' LIMIT 1)";
                $rs = mysqli_query($conn, $sql_tr);

                $sql_tr = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                        VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$idiorigen}', '{$zonarecepcioni_alm}', {$cantidadTotal}, 1, '{$cve_usuario}', {$almacen}, 1, NOW())";
                $rs = mysqli_query($conn, $sql_tr);

                 $sql4 =\db()->prepare(
                  "INSERT INTO t_MovCharolas (Cve_Almac, id_kardex, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) 
                VALUES ('$almacen', (SELECT MAX(id) FROM t_cardex), (SELECT IDContenedor FROM c_charolas WHERE CveLP = '$valor_lp' LIMIT 1), NOW(), '$idiorigen', '{$zonarecepcioni_alm}', 1, '{$cve_usuario}', 'I');");
                  //echo var_dump($sql3);
                  $sql4->execute();

                //*****************************************************************************************************************
                //HAY QUE ESTUDIAR BIEN QUE MOVIMIENTOS HACER O DONDE SE CONSULTA LOS MOVIMIENTOS DE CHAROLAS ANTES DE IMPLEMENTAR
                //*****************************************************************************************************************
                //$sql_tr = "INSERT INTO t_MovCharolas(Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                //        VALUES('{$clave_almacen}', {$id_contenedor}, NOW(), '{$Folio}', '{$cve_ubicacion}', 1, '{$usuario}', 'I')";
                //$rs = mysqli_query($conn, $sql_tr);
                //*****************************************************************************************************************
            }
            else
            {
                $sql_tr = "UPDATE ts_existenciapiezas SET Existencia = Existencia-{$cantidadTotal} WHERE idy_ubica = '{$idy_ubica}' AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}'";
                $rs = mysqli_query($conn, $sql_tr);
            }

            $sql_tr = "INSERT INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadRecibida, CantidadUbicada, cve_usuario, cve_ubicacion, fecha_inicio, fecha_fin) 
                    VALUES ({$Folio}, '{$cve_articulo}', '{$cve_lote}', {$cantidadTotal}, 0, '{$cve_usuario}', '{$zonarecepcioni_alm}', NOW(), NOW())";
            $rs = mysqli_query($conn, $sql_tr);


            //********************************************************************************************************
            //                                    t_pendienteacomodo
            //********************************************************************************************************
            $sql_tr = "SELECT COUNT(*) AS existe FROM t_pendienteacomodo WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$zonarecepcioni_alm}' AND ID_Proveedor = {$id_proveedor}";
            $rs = mysqli_query($conn, $sql_tr);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe = $resul['existe'];

            if(!$existe)
            {
                $sql_tr = "INSERT INTO t_pendienteacomodo(cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) 
                        VALUES ('{$cve_articulo}', '{$cve_lote}', {$cantidadTotal}, '{$zonarecepcioni_alm}', {$id_proveedor})";
                $rs = mysqli_query($conn, $sql_tr);
            }
            else
            {
                $sql_tr = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad + {$cantidadTotal} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$zonarecepcioni_alm}' AND ID_Proveedor = {$id_proveedor}";
                $rs = mysqli_query($conn, $sql_tr);
            }
            //********************************************************************************************************

        }

        //echo "SQL2 = ".$sql2." SQL = ".$sql;
        $sql .= "INSERT INTO `t_cardex` (`cve_articulo`, `cve_lote`, `fecha`, `origen`, `destino`, `cantidad`, `id_TipoMovimiento`, `cve_usuario`, `Cve_Almac`, `Activo`) 
        VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$idiorigen}', '{$ididestino}','{$cantidadTotal}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1);";

        $sth = \db()->prepare( $sql );
        $sth->execute();

     }

    function moverTraslado($data) {
        extract($data);

        $query = mysqli_query(\db2(), "INSERT IGNORE INTO `t_tipomovimiento` (`nombre`) VALUES ('Traslado');");
        $query2 = mysqli_query(\db2(), "SELECT id_TipoMovimiento AS id FROM `t_tipomovimiento` WHERE nombre = 'Traslado';");
        $movimiento = mysqli_fetch_assoc($query2)['id'];

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $traslado_almacen = $data["traslado_almacen"];

        $fusionar_cajas = $data["fusionar_cajas"];
        $pallets_por_piezas_aux = 0;
        //if(isset($data["pallets_por_piezas"]))
          if($data["pallets_por_piezas"] == 'S')
             $pallets_por_piezas_aux = 1;

        $pallets_por_piezas = $pallets_por_piezas_aux;

        $almacen = '';
        $clave_almacen = '';
/*
        if($traslado_almacen)
        {
          $almacen = $data['almacen_destino'];
          $query3 = mysqli_query(\db2(), "SELECT clave FROM c_almacenp WHERE id = {$almacen}");
          $clave_almacen = mysqli_fetch_assoc($query3)['clave'];
        }
        else 
*/
        //{
          $query3 = mysqli_query(\db2(), "SELECT cve_almacenp AS id FROM c_almacen WHERE cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = '$ididestino')");
          $almacen = mysqli_fetch_assoc($query3)['id'];

          //$almacen = $data['almacen_destino'];
          $query3 = mysqli_query(\db2(), "SELECT clave FROM c_almacenp WHERE id = {$almacen}");
          $clave_almacen = mysqli_fetch_assoc($query3)['clave'];
        //}

        $id_piezas = 0; $id_cajas = 0; $id_tarima = 0; $id_caja = 0;
        $cajas_iguales = 0;
        $valor_lp_caja_origen         = $data["lp_val"];
        $valor_lp_caja_destino        = $data["lp_dest"];
        if($valor_lp_caja_origen == $valor_lp_caja_destino)
          $cajas_iguales = 1;

        $valor_lp         = $data["lp_val"];
        $valor_pallet     = $data["pallet_val"];
        $valor_origen     = $data["idiorigen"];
        $valor_destino    = $data["ididestino"];
        $cantidad         = $data["cantidad"];
        $cantidad_max     = $data["cantidad_max"];
        $QA               = ($data["QA"]=='Si')?1:0;


        $query3 = mysqli_query(\db2(), "SELECT IFNULL(cve_nivel, 0) as nivel FROM c_ubicacion WHERE idy_ubica = '$ididestino'");
        $nivel_ubicacion = mysqli_fetch_assoc($query3)['nivel'];

        //******************************************************************************************************
        //                                            PALLETIZAR TRASLADOS
        //******************************************************************************************************
        $valor_lp_palletizar     = "";
        $valor_pallet_palletizar = "";

if($trasladar_ubicacion == 0)
{
        if($palletizar_traslado && !$traslado_almacen)
        {
            $id_contenedor = $data['pallet_palletizar'];
            //$Folio = $data['ordenp'];
            //$cantidad = $data['cantidad'];
            //$cve_articulo = $data['clave'];
            //$clave_articulo = $cve_articulo;
            //$lote = $data['lote'];

            if($data["importacion"] == 0)
            {
            $sql = "SELECT (SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, (SELECT MAX(ch.IDContenedor)+2 FROM c_charolas ch) AS id_contenedor2, cve_almac, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND IDContenedor = {$id_contenedor}  ORDER BY IDContenedor ASC LIMIT 1";#AND tipo = 'Pallet'

            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

            //if(!$resul['id_contenedor']) break;

            $id_contenedor = $resul['id_contenedor'];
            $id_contenedor2 = $resul['id_contenedor2'];
            $descripcion   = $resul['descripcion'];
            $tipo          = $resul['tipo'];
            $alto          = $resul['alto'];
            $ancho         = $resul['ancho'];
            $fondo         = $resul['fondo'];
            $peso          = $resul['peso'];
            $pesomax       = $resul['pesomax'];
            $capavol       = $resul['capavol'];
            $id_almacen    = $resul['cve_almac'];

            //id_contenedor + id_contenedor2, asegura que el LP no se repita

            $label_lp = "LP".str_pad($id_contenedor.$id_contenedor2, 9, "0", STR_PAD_LEFT);

            $sql = "INSERT INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) 
                    VALUES ({$id_almacen}, '{$label_lp}', '{$descripcion}', 0, 'Pallet', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
            $rs = mysqli_query($conn, $sql);
            }
            else 
              $label_lp = $data['pallet_palletizar'];

            $valor_lp_palletizar     = $label_lp;
            $valor_pallet_palletizar = $label_lp;

            
              $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - {$cantidad} WHERE cve_articulo = '".$data["cve_articulo"]."' AND cve_lote = '".$data["cve_lote"]."' AND idy_ubica='".$data["idiorigen"]."'";
              $rs = mysqli_query($conn, $sql);   

            //if($pallets_por_piezas == 0)
            //{
              $sql=" INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Existencia, ntarima, ID_Proveedor, Cuarentena) 
                VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$cantidad."', (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp_palletizar}' LIMIT 1), '".$ID_Proveedor."', $QA) 
                ON DUPLICATE KEY UPDATE existencia=existencia+".$cantidad.";";

              $rs = mysqli_query($conn, $sql);
            //}
            
        }
        //******************************************************************************************************
      $sql = "";
      if($tipo_pallet_contenedor_articulo == 'Artículo' /*&& !$traslado_almacen*/  && !$valor_lp && !$palletizar_traslado)
      {
        $sql2="UPDATE ts_existenciapiezas set Existencia=Existencia-{$cantidad} where cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."';";
        $sth = \db()->prepare( $sql2 );
        $sth->execute();

             if($fusionar_cajas)
             {
                $valor_lp = $fusionar_cajas;

//*************************************************************************************************************************************************
//                                                            CREAR CAJA
//*************************************************************************************************************************************************
                  $sql = "SELECT (MAX(IFNULL(IDContenedor, 0))+1) as nextid FROM c_charolas";
                  if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  $row_autoid = mysqli_fetch_assoc($res_id);
                  $nextid = $row_autoid['nextid'];

                  $sql ="SELECT IFNULL(tipo_caja, '') as tipo_caja FROM c_articulo WHERE cve_articulo = '".$data["cve_articulo"]."'";

                  if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  $row_caja = mysqli_fetch_assoc($res_id);
                  $tipo_caja = $row_caja['tipo_caja'];

                  if($tipo_caja != "")
                      $sql ="SELECT * FROM c_tipocaja WHERE id_tipocaja = $tipo_caja";
                  else
                      $sql ="SELECT * FROM c_tipocaja WHERE clave = '1'"; //caja generica

                  if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  $row_caja = mysqli_fetch_assoc($res_id);

                  $clave_caja = $row_caja['clave'];
                  $descripcion = $row_caja['descripcion'];
                  $alto = $row_caja['alto'];
                  $ancho = $row_caja['ancho'];
                  $largo = $row_caja['largo'];
                  $peso = $row_caja['peso'];
                  if($alto == '') $alto = 0;if($ancho == '') $ancho = 0; if($largo == '') $largo = 0; if($peso == '') $peso = 0;
                  $volumen = $alto*$ancho*$largo;

                     $label_caja = "CJ".str_pad($nextid, 6, "0", STR_PAD_LEFT);
                  $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES($almacen, CONCAT('$clave_caja','-', '$nextid'), '$descripcion', 0, 'Caja', 1, '$alto', '$ancho', '$largo', '$peso', 0, $volumen, '$label_caja', 0)";

                  if(!$res_id = mysqli_query($conn, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  //*************************************************************************************
                  $clave_caja = $clave_caja.'-'.$nextid;
                  $sql ="SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$clave_caja'";
                  if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  $row_caja = mysqli_fetch_assoc($res_id);
                  $id_caja = $row_caja['IDContenedor'];
                  $ultimo_folio_caja = $clave_caja;
                  //*************************************************************************************
//*************************************************************************************************************************************************
                    $sql_caja = "INSERT IGNORE INTO ts_existenciacajas (idy_ubica, cve_articulo, cve_lote, PiezasXCaja, Id_Caja, Cve_Almac, Id_Pzs, nTarima) VALUES ('".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '$cantidad', '$id_caja', '{$almacen}', 1, (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1))";
                    //ON DUPLICATE KEY UPDATE PiezasXCaja = PiezasXCaja + $existencia
                    $sth = \db()->prepare( $sql_caja );
                    $sth->execute();

                    $sql_caja = "INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', 0, (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1), 0, '$cantidad', 1, '$ID_Proveedor',  0) ON DUPLICATE KEY UPDATE existencia = existencia+{$cantidad}";
                    $sth = \db()->prepare( $sql_caja );
                    $sth->execute();

                $data["lp_val"] = $fusionar_cajas;
             }


        //******************************************************************************************************************************************
        //                                                              TRAZABILIDAD
        //******************************************************************************************************************************************
          $query_traz = \db()->prepare("
            SELECT (t.cantidad -  {$cantidad}) as mayor_a_cero 
            FROM t_trazabilidad_existencias t 
            LEFT JOIN th_entalmacen e ON e.Fol_Folio = t.folio_entrada
            LEFT JOIN th_aduana oc ON oc.num_pedimento = IF(e.tipo != 'RL' AND e.id_ocompra IS NOT NULL, e.id_ocompra, NULL) 
            WHERE t.cve_almac = '{$almacen}' AND t.idy_ubica = '".$data["idiorigen"]."' AND CONVERT(t.cve_articulo, CHAR) = '".$data["cve_articulo"]."' AND CONVERT(t.cve_lote, CHAR) = '".$data["cve_lote"]."' AND t.id_proveedor = '$ID_Proveedor' AND t.id_tipo_movimiento = 2 AND CONVERT(IFNULL(e.Fact_Prov, ''), CHAR) = CONVERT(IFNULL(t.factura_ent, ''), CHAR) AND CONVERT(IFNULL(e.Proyecto, ''), CHAR) = CONVERT(IFNULL(t.proyecto, ''), CHAR) AND CONVERT(IFNULL(oc.Factura, ''), CHAR) = CONVERT(IFNULL(t.factura_oc, ''), CHAR) AND IFNULL(t.ntarima, '') = ''");
          $query_traz->execute();
          $mayor_a_cero = $query_traz->fetch()['mayor_a_cero'];


          $sql_traz = \db()->prepare("
          SELECT e.Fol_Folio AS folio_entrada, oc.num_pedimento as folio_oc, IFNULL(e.Fact_Prov, '') as factura_ent, IFNULL(oc.Factura, '') as factura_oc, IFNULL(e.Proyecto, '') as proyecto
          FROM t_trazabilidad_existencias t 
          LEFT JOIN th_entalmacen e ON e.Fol_Folio = t.folio_entrada
          LEFT JOIN th_aduana oc ON oc.num_pedimento = IF(e.tipo != 'RL' AND e.id_ocompra IS NOT NULL, e.id_ocompra, NULL)            
          WHERE t.cve_almac = '{$almacen}' AND t.idy_ubica = '".$data["idiorigen"]."' AND CONVERT(t.cve_articulo, CHAR) = '".$data["cve_articulo"]."' AND CONVERT(t.cve_lote, CHAR) = '".$data["cve_lote"]."' AND t.id_proveedor = '$ID_Proveedor' AND t.id_tipo_movimiento = 2 AND CONVERT(IFNULL(e.Fact_Prov, ''), CHAR) = CONVERT(IFNULL(t.factura_ent, ''), CHAR) AND CONVERT(IFNULL(e.Proyecto, ''), CHAR) = CONVERT(IFNULL(t.proyecto, ''), CHAR) AND CONVERT(IFNULL(oc.Factura, ''), CHAR) = CONVERT(IFNULL(t.factura_oc, ''), CHAR) AND IFNULL(t.ntarima, '') = ''");
          $sql_traz->execute();
          $valores_traz = $sql_traz->fetch();

          if($valores_traz['folio_entrada'])
             extract($valores_traz);


          if($mayor_a_cero > 0 && $valores_traz['folio_entrada'])
          {
            $query_traz = \db()->prepare("UPDATE t_trazabilidad_existencias SET cantidad = cantidad-$cantidad WHERE idy_ubica = '".$data["idiorigen"]."' AND cve_articulo = '".$data["cve_articulo"]."' AND cve_lote = '".$data["cve_lote"]."' AND id_proveedor = '$ID_Proveedor' AND id_tipo_movimiento = 2 AND IFNULL(factura_ent, '') = '$factura_ent' AND IFNULL(proyecto, '') = '$proyecto' AND IFNULL(factura_oc, '') = '$factura_oc'");
            $query_traz->execute();
          }
          else if($valores_traz['folio_entrada'])
          {
            $query_traz = \db()->prepare("DELETE FROM t_trazabilidad_existencias WHERE idy_ubica = '".$data["idiorigen"]."' AND cve_articulo = '".$data["cve_articulo"]."' AND cve_lote = '".$data["cve_lote"]."' AND id_proveedor = '$ID_Proveedor' AND id_tipo_movimiento = 2 AND IFNULL(factura_ent, '') = '$factura_ent' AND IFNULL(proyecto, '') = '$proyecto' AND IFNULL(factura_oc, '') = '$factura_oc'");
            $query_traz->execute();
          }

          if($valores_traz['folio_entrada'])
          $query_traz = \db()->prepare("INSERT INTO t_trazabilidad_existencias(cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '$cantidad', '$ID_Proveedor', '{$folio_entrada}', '$folio_oc', '$factura_ent', '$factura_oc', '$proyecto', 2)");
          $query_traz->execute();
        //******************************************************************************************************************************************
        //                                                              FIN TRAZABILIDAD
        //******************************************************************************************************************************************
//, (SELECT IFNULL(Cuarentena, 0) FROM ts_existenciapiezas where cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."')

          if(!$fusionar_cajas)
          {
        $sql=" INSERT INTO ts_existenciapiezas (cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor, Cuarentena) 
        VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$cantidad."', '".$ID_Proveedor."', $QA) ON DUPLICATE KEY UPDATE Existencia=Existencia+".$cantidad.";";
          }

        if($fusionar /*&& ($nivel_ubicacion+0) > 0*/)
        {
            $sql=" INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, existencia, ntarima, ID_Proveedor, Cuarentena) 
              VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$cantidad."', (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '{$fusionar}' LIMIT 1), '".$ID_Proveedor."', $QA) ON DUPLICATE KEY UPDATE existencia=existencia+".$cantidad.";";
        }

        if(!$fusionar_cajas)
        {
        $sth = \db()->prepare( $sql );
        $sth->execute();
        }

              //$sql_tr = "INSERT INTO t_MovCharolas(Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
              //        VALUES('{$clave_almacen}', {$id_contenedor}, NOW(), '{$Folio}', '{$cve_ubicacion}', 1, '{$usuario}', 'I')";
              //$rs = mysqli_query($conn, $sql_tr);

            $sql = "INSERT INTO `t_cardex` (`cve_articulo`, `cve_lote`, `fecha`, `origen`, `destino`, `cantidad`, `id_TipoMovimiento`, `cve_usuario`, `Cve_Almac`, `Activo`, `stockinicial`, `ajuste`) 
            VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$idiorigen}', '{$ididestino}','{$cantidadTotal}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1, '{$cantidad_max}', {$cantidad_max}-{$cantidadTotal});";

            $sth = \db()->prepare( $sql );
            $sth->execute();

            if($fusionar_cajas)
            {
              $sql_tr = "INSERT INTO t_MovCharolas(Cve_Almac, id_kardex, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status, EsCaja)
                      VALUES('{$clave_almacen}', (SELECT MAX(id) FROM t_cardex), {$id_caja}, NOW(), '{$idiorigen}', '{$ididestino}', '{$movimiento}', '{$cve_usuario}', 'I', 'S')";
              $rs = mysqli_query($conn, $sql_tr);
            }

      }//if($tipo_pallet_contenedor_articulo == 'Artículo' /*&& !$traslado_almacen*/  && !$valor_lp && !$palletizar_traslado)
      else if(/*!$traslado_almacen &&*/ !$palletizar_traslado)
      {

        if($valor_pallet || (($tipo_pallet_contenedor_articulo == 'Contenedor' || $tipo_pallet_contenedor_articulo == 'Pallet' || $tipo_pallet_contenedor_articulo == 'Caja') && !$valor_pallet))
        {
            //$query_rev = mysqli_query(\db2(), "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE cve_articulo='".$data["cve_articulo"]."' and lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."'");
            $query_rev = mysqli_query(\db2(), "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE idy_ubica='".$data["idiorigen"]."' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '".$data["lp_val"]."')");
            if($tipo_pallet_contenedor_articulo == 'Caja')
              $query_rev = mysqli_query(\db2(), "SELECT COUNT(*) as existe FROM ts_existenciacajas WHERE idy_ubica='".$data["idiorigen"]."' AND Id_Caja = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '".$data["lp_val"]."')");
            $existe_tarima = mysqli_fetch_assoc($query_rev)['existe'];

          if($existe_tarima)
          {
            /*
            if($tipo_pallet_contenedor_articulo == 'Artículo')
               $sql2="UPDATE ts_existenciatarima set existencia=existencia-{$cantidad} where cve_articulo='".$data["cve_articulo"]."' and lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."' and ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);";
             else
              */
             //if($tipo == '1')
                //$sql2="UPDATE ts_existenciatarima set existencia=existencia-{$cantidad} where idy_ubica='".$data["idiorigen"]."' and ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);";

             //SPWS_ConvierteContenedorEnPzs(
                                              //Almacen   VARCHAR(50), origen
                                              //ClaveLP   VARCHAR(50), origen
                                              //Articulo  VARCHAR(50),
                                              //ILote   VARCHAR(50),
                                              //Ubica   INT, 
                                              //Usuario   VARCHAR(50),
                                              //NewTarima INT 
                                            //);
             if($convertir_pzs == 1)
             {
                $ub_origen = $data["idiorigen"];
                $ub_destino = $data["ididestino"];
                $valor_lp_origen = $data["lp_val"];

                $sql = "SELECT (SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, (SELECT MAX(ch.IDContenedor)+2 FROM c_charolas ch) AS id_contenedor2, cve_almac, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND IDContenedor = {$pallet_palletizar} AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1";

                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

                //if(!$resul['id_contenedor']) break;

                $id_contenedor = $resul['id_contenedor'];
                $id_contenedor2 = $resul['id_contenedor2'];
                $descripcion   = $resul['descripcion'];
                $tipo          = $resul['tipo'];
                $alto          = $resul['alto'];
                $ancho         = $resul['ancho'];
                $fondo         = $resul['fondo'];
                $peso          = $resul['peso'];
                $pesomax       = $resul['pesomax'];
                $capavol       = $resul['capavol'];
                $id_almacen    = $resul['cve_almac'];

                //id_contenedor + id_contenedor2, asegura que el LP no se repita

                $label_lp = "LP".str_pad($id_contenedor, 9, "0", STR_PAD_LEFT);

                $sql = "INSERT INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) 
                        VALUES ({$almacen}, '{$label_lp}', '{$descripcion}', 0, 'Pallet', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
                $rs = mysqli_query($conn, $sql);

                $valor_lp_palletizar     = $label_lp;
                $valor_pallet_palletizar = $label_lp;
                //$valor_lp                = $label_lp;

              $query3 = mysqli_query(\db2(), "SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$label_lp}'");
              $pallet_palletizar = mysqli_fetch_assoc($query3)['IDContenedor'];

              $query4 = mysqli_query(\db2(), "SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp_origen}'");
              $pallet_palletizar_origen = mysqli_fetch_assoc($query4)['IDContenedor'];

                $query_rev_pzs = mysqli_query(\db2(), "SELECT * FROM ts_existenciatarima WHERE idy_ubica='".$data["idiorigen"]."' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '".$data["lp_val"]."')");

                while($row_pzs = mysqli_fetch_assoc($query_rev_pzs))
                {
                    $cve_articulo_pzs = $row_pzs['cve_articulo'];
                    $lote_pzs = $row_pzs['lote'];
                    $sql_convertir_pzs = "CALL SPWS_ConvierteContenedorEnPzs('{$id_almacen}', '{$valor_lp_origen}', '{$cve_articulo_pzs}', '{$lote_pzs}', '{$ub_origen}', '{$cve_usuario}', {$pallet_palletizar})";

                    $sth = \db()->prepare( $sql_convertir_pzs );
                    $sth->execute();
                }
/*
              $query_new_lp = mysqli_query(\db2(), "SELECT CveLP FROM c_charolas WHERE IDContenedor = '{$pallet_palletizar}' ");
              $valor_lp   = mysqli_fetch_assoc($query_new_lp)["CveLP"];
              $data["lp_val"] = $valor_lp;
*/

                $sql_convertir_pzs = "UPDATE ts_existenciacajas SET Cve_Almac = '$almacen', idy_ubica = '$ub_destino' WHERE idy_ubica = '$ub_origen' AND Cve_Almac = '$id_almacen' AND nTarima = {$pallet_palletizar} AND Id_Caja = '$pallet_palletizar_origen'";
                $sth = \db()->prepare( $sql_convertir_pzs );
                $sth->execute();

                $sql_convertir_pzs = "UPDATE c_charolas SET cve_almac = '$almacen' WHERE IDContenedor = '$pallet_palletizar_origen'";
                $sth = \db()->prepare( $sql_convertir_pzs );
                $sth->execute();

             }
              if($pallets_por_piezas == 0)
              {
             $sql2="UPDATE ts_existenciatarima set idy_ubica='".$data["ididestino"]."', cve_almac = '{$almacen}' where idy_ubica='".$data["idiorigen"]."' and ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);";
             if($tipo_pallet_contenedor_articulo == 'Caja')
             {
                $sql2="UPDATE ts_existenciacajas set idy_ubica='".$data["ididestino"]."', cve_almac = '{$almacen}' where idy_ubica='".$data["idiorigen"]."' and Id_Caja = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);";
                //ESTA CONSULTA SERÁ MODIFICADA PARA CUANDO SE PUEDA MONTAR LA CAJA EN EL PALLET
                if($cajas_iguales == 1)
                  $sql2="UPDATE ts_existenciacajas set idy_ubica='".$data["ididestino"]."', cve_almac = '{$almacen}', nTarima = NULL, Id_Pzs = 1 where idy_ubica='".$data["idiorigen"]."' and Id_Caja = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);";
             }
             if($fusionar_cajas)//Si ando fusionando cajas, resto la cantidad del pallet que se va a transformar en cajas
             $sql2="UPDATE ts_existenciatarima set existencia = existencia-{$cantidad}, idy_ubica='".$data["ididestino"]."', cve_almac = '{$almacen}' where idy_ubica='".$data["idiorigen"]."' and ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);";
              }

             if($fusionar_cajas)
             {
                sleep(1);
                $sql_cajas = "UPDATE c_charolas SET Tipo = 'Caja', cve_almac = '{$almacen}' WHERE CveLP = '{$valor_lp}'";
                $sth = \db()->prepare( $sql_cajas );
                //$sth->execute();
                while(!$sth->execute())
                {
                  sleep(1);
                  $sth = \db()->prepare( $sql_cajas );
                }



//****************************************************************************************************************************
//****************************************************************************************************************************

                $valor_lp = $fusionar_cajas;//LP_Destino
                $lp_orig = $data["lp_val"];

//*************************************************************************************************************************************************
//                                                            CREAR CAJA
//*************************************************************************************************************************************************
                /*
                  $sql = "SELECT (MAX(IFNULL(IDContenedor, 0))+1) as nextid FROM c_charolas";
                  if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  $row_autoid = mysqli_fetch_assoc($res_id);
                  $nextid = $row_autoid['nextid'];

                  $sql ="SELECT IFNULL(tipo_caja, '') as tipo_caja FROM c_articulo WHERE cve_articulo = '".$data["cve_articulo"]."'";

                  if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  $row_caja = mysqli_fetch_assoc($res_id);
                  $tipo_caja = $row_caja['tipo_caja'];

                  if($tipo_caja != "")
                      $sql ="SELECT * FROM c_tipocaja WHERE id_tipocaja = $tipo_caja";
                  else
                      $sql ="SELECT * FROM c_tipocaja WHERE clave = '1'"; //caja generica

                  if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  $row_caja = mysqli_fetch_assoc($res_id);

                  $clave_caja = $row_caja['clave'];
                  $descripcion = $row_caja['descripcion'];
                  $alto = $row_caja['alto'];
                  $ancho = $row_caja['ancho'];
                  $largo = $row_caja['largo'];
                  $peso = $row_caja['peso'];
                  if($alto == '') $alto = 0;if($ancho == '') $ancho = 0; if($largo == '') $largo = 0; if($peso == '') $peso = 0;
                  $volumen = $alto*$ancho*$largo;

                     $label_caja = "CJ".str_pad($nextid, 6, "0", STR_PAD_LEFT);
                  $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES($almacen, CONCAT('$clave_caja','-', '$nextid'), '$descripcion', 0, 'Caja', 1, '$alto', '$ancho', '$largo', '$peso', 0, $volumen, '$label_caja', 0)";

                  if(!$res_id = mysqli_query($conn, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  //*************************************************************************************
                  $clave_caja = $clave_caja.'-'.$nextid;
                  */
                  $sql ="SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$lp_orig'";
                  if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  $row_caja = mysqli_fetch_assoc($res_id);
                  $id_caja = $row_caja['IDContenedor'];
                  //$ultimo_folio_caja = $clave_caja;
                  //*************************************************************************************
                  //*************************************************************************************
                    $sql_caja = "INSERT IGNORE INTO ts_existenciacajas (idy_ubica, cve_articulo, cve_lote, PiezasXCaja, Id_Caja, nTarima, Cve_Almac, Id_Pzs) VALUES ('".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '$cantidad', '$id_caja', (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1),'{$almacen}', 1)";
                    //ON DUPLICATE KEY UPDATE PiezasXCaja = PiezasXCaja + $existencia
                    $sth = \db()->prepare( $sql_caja );
                    $sth->execute();

                    $sql = "UPDATE ts_existenciatarima SET Existencia = Existencia - {$cantidad} WHERE cve_articulo = '".$data["cve_articulo"]."' AND lote = '".$data["cve_lote"]."' AND idy_ubica='".$data["idiorigen"]."' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '".$data["lp_val"]."' LIMIT 1)";
                    $rs = mysqli_query($conn, $sql);   

                    //TR000
                    $sql_caja = "INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', 0, (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1), 0, '$cantidad', 1, '$ID_Proveedor',  0) ON DUPLICATE KEY UPDATE existencia = existencia+{$cantidad}";
                    $sth = \db()->prepare( $sql_caja );
                    $sth->execute();

                $data["lp_val"] = $fusionar_cajas;

//****************************************************************************************************************************
//****************************************************************************************************************************

                //$query_existe_tarima = mysqli_query(\db2(), "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE idy_ubica='".$data["idiorigen"]."' and ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);");
                //$existe_tarima = mysqli_fetch_assoc($query_existe_tarima)['existe'];

                //$sql2="UPDATE IGNORE ts_existenciatarima set idy_ubica='".$data["ididestino"]."', cve_almac = '{$almacen}', ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$fusionar_cajas}' LIMIT 1) where idy_ubica='".$data["idiorigen"]."' and ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);";
/*
                if($existe_tarima)
                {
                    $sql3="UPDATE IGNORE ts_existenciatarima SET idy_ubica='".$data["ididestino"]."', cve_almac = '{$almacen}', ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$fusionar_cajas}' LIMIT 1), existencia = existencia + $cantidad WHERE idy_ubica='".$data["idiorigen"]."' and ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);";
                    $sth = \db()->prepare( $sql3 );
                    $sth->execute();
                }
*/
                //$query_cajas = mysqli_query(\db2(), "SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}'");
                //$id_caja = mysqli_fetch_assoc($query_cajas)['IDContenedor'];

                $query_rev_pzs = mysqli_query(\db2(), "SELECT *, (SELECT IDContenedor FROM c_charolas WHERE CveLP = '$fusionar_cajas') as id_tarima  FROM ts_existenciatarima WHERE idy_ubica='".$data["idiorigen"]."' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '".$data["lp_val"]."')");

                while($row_pzs = mysqli_fetch_assoc($query_rev_pzs))
                {
                    $cve_art_cajas = $row_pzs['cve_articulo'];
                    $cve_lote_cajas = $row_pzs['lote'];
                    $ntarima_cajas = $row_pzs['id_tarima'];
                    $existencia = $row_pzs['existencia'];
                    $sql_caja = "INSERT INTO ts_existenciacajas (idy_ubica, cve_articulo, cve_lote, PiezasXCaja, Id_Caja, Cve_Almac, nTarima, Id_Pzs) VALUES ('".$data["ididestino"]."', '$cve_art_cajas', '$cve_lote_cajas', '$existencia', '$id_caja','{$almacen}', $ntarima_cajas, 0) ON DUPLICATE KEY UPDATE PiezasXCaja = PiezasXCaja + $existencia";
                    $sth = \db()->prepare( $sql_caja );
                    $sth->execute();
                }
                $valor_lp = $fusionar_cajas;
                $data["lp_val"] = $fusionar_cajas;


                //$sth = \db()->prepare( $sql2 );
                //$sth->execute();


             }


             if($pallets_por_piezas == 1)
             {
                //if(isset($data['lp_dest']))
                //{
                  $query3 = mysqli_query(\db2(), "SELECT cve_almacenp AS id FROM c_almacen WHERE cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = '$idiorigen')");
                  $almacen_orig = mysqli_fetch_assoc($query3)['id'];


                    $sql = "UPDATE ts_existenciatarima SET Existencia = Existencia - {$cantidad} WHERE cve_articulo = '".$data["cve_articulo"]."' AND lote = '".$data["cve_lote"]."' AND idy_ubica='".$data["idiorigen"]."' AND cve_almac = '$almacen_orig' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1)";
                    $rs = mysqli_query($conn, $sql);   

                  if($data['lp_dest'] != '')
                  {
                    $lp_dest = $data['lp_dest'];

                    $sql = "SELECT COUNT(*) as existe, cve_almac FROM c_charolas WHERE clave_contenedor = '$lp_dest' OR CveLP = '$lp_dest'";
                    $rs = mysqli_query($conn, $sql);
                    $row_exist = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $existe_dest = $row_exist['existe'];
                    $id_almacen = $row_exist['cve_almac'];
                    $label_lp = $lp_dest;
                    if($existe_dest == 0)
                    {
                        $sql = "SELECT (SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, (SELECT MAX(ch.IDContenedor)+2 FROM c_charolas ch) AS id_contenedor2, cve_almac, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND cve_almac = '".$data['almacen_destino']."' AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1";

                        $rs = mysqli_query($conn, $sql);
                        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

                        //if(!$resul['id_contenedor']) break;

                        $id_contenedor = $resul['id_contenedor'];
                        $id_contenedor2 = $resul['id_contenedor2'];
                        $descripcion   = $resul['descripcion'];
                        $tipo          = $resul['tipo'];
                        $alto          = $resul['alto'];
                        $ancho         = $resul['ancho'];
                        $fondo         = $resul['fondo'];
                        $peso          = $resul['peso'];
                        $pesomax       = $resul['pesomax'];
                        $capavol       = $resul['capavol'];
                        $id_almacen    = $resul['cve_almac'];

                        //id_contenedor + id_contenedor2, asegura que el LP no se repita

                        $label_lp = $lp_dest;//"LP".str_pad($id_contenedor, 9, "0", STR_PAD_LEFT);

                        $sql = "INSERT INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) 
                                VALUES ({$id_almacen}, '{$label_lp}', '{$descripcion}', 0, 'Pallet', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
                        $rs = mysqli_query($conn, $sql);
                    }

                    //$sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - {$cantidad} WHERE cve_articulo = '".$data["cve_articulo"]."' AND cve_lote = '".$data["cve_lote"]."' AND idy_ubica='".$data["idiorigen"]."'";

                    //TR000
                    $sql=" INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Existencia, ntarima, ID_Proveedor, Cuarentena) 
                      VALUES ('{$id_almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$cantidad."', (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$label_lp}' LIMIT 1), '".$ID_Proveedor."', $QA) ON DUPLICATE KEY UPDATE existencia=existencia+".$cantidad.";";
                    $rs = mysqli_query($conn, $sql);
                  }
                //}

                $query_traz = \db()->prepare("UPDATE t_trazabilidad_existencias SET idy_ubica='".$data["ididestino"]."', cve_almac = '{$id_almacen}' WHERE idy_ubica = '".$data["idiorigen"]."' AND cve_articulo = '".$data["cve_articulo"]."' AND cve_lote = '".$data["cve_lote"]."' AND id_proveedor = '$ID_Proveedor' AND id_tipo_movimiento = 2 AND IFNULL(factura_ent, '') = '$factura_ent' AND IFNULL(proyecto, '') = '$proyecto' AND IFNULL(factura_oc, '') = '$factura_oc' AND IFNULL(ntarima, '') = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1)");
                $query_traz->execute();
             }
             else
             {
              //******************************************************************************************************************************************
              //                                                              TRAZABILIDAD
              //******************************************************************************************************************************************
             /*
                $sql_traz = \db()->prepare("
                SELECT e.Fol_Folio AS folio_entrada, oc.num_pedimento as folio_oc, IFNULL(e.Fact_Prov, '') as factura_ent, IFNULL(oc.Factura, '') as factura_oc, IFNULL(e.Proyecto, '') as proyecto
                FROM t_trazabilidad_existencias t 
                LEFT JOIN th_entalmacen e ON e.Fol_Folio = t.folio_entrada
                LEFT JOIN th_aduana oc ON oc.num_pedimento = IF(e.tipo != 'RL' AND e.id_ocompra IS NOT NULL, e.id_ocompra, NULL)            
                WHERE t.cve_almac = '{$almacen}' AND t.idy_ubica = '".$data["idiorigen"]."' AND CONVERT(t.cve_articulo, CHAR) = '".$data["cve_articulo"]."' AND CONVERT(t.cve_lote, CHAR) = '".$data["cve_lote"]."' AND t.id_proveedor = '$ID_Proveedor' AND t.id_tipo_movimiento = 2 AND CONVERT(IFNULL(e.Fact_Prov, ''), CHAR) = CONVERT(IFNULL(t.factura_ent, ''), CHAR) AND CONVERT(IFNULL(e.Proyecto, ''), CHAR) = CONVERT(IFNULL(t.proyecto, ''), CHAR) AND CONVERT(IFNULL(oc.Factura, ''), CHAR) = CONVERT(IFNULL(t.factura_oc, ''), CHAR) AND IFNULL(t.ntarima, '') = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1)");
                $sql_traz->execute();
                $valores_traz = $sql_traz->fetch();

                if($valores_traz['folio_entrada'])
                {
                extract($valores_traz);

                $query_traz = \db()->prepare("UPDATE t_trazabilidad_existencias SET idy_ubica='".$data["ididestino"]."', cve_almac = '{$almacen}' WHERE idy_ubica = '".$data["idiorigen"]."' AND cve_articulo = '".$data["cve_articulo"]."' AND cve_lote = '".$data["cve_lote"]."' AND id_proveedor = '$ID_Proveedor' AND id_tipo_movimiento = 2 AND IFNULL(factura_ent, '') = '$factura_ent' AND IFNULL(proyecto, '') = '$proyecto' AND IFNULL(factura_oc, '') = '$factura_oc' AND IFNULL(ntarima, '') = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1)");
                $query_traz->execute();
                }
                */
                if($tipo_pallet_contenedor_articulo != 'Caja')
                {
                $query_traz = \db()->prepare("UPDATE t_trazabilidad_existencias SET idy_ubica='".$data["ididestino"]."', cve_almac = '{$almacen}' WHERE idy_ubica = '".$data["idiorigen"]."' AND id_tipo_movimiento = 2 AND IFNULL(factura_ent, '') = '$factura_ent' AND IFNULL(ntarima, '') = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1)");
                $query_traz->execute();
                }

              //******************************************************************************************************************************************
              //                                                              FIN TRAZABILIDAD
              //******************************************************************************************************************************************
             }

            if($convertir_pzs == 1)
             $sql2="UPDATE ts_existenciatarima set idy_ubica='".$data["ididestino"]."', cve_almac = '{$almacen}', ntarima = '$pallet_palletizar' where idy_ubica='".$data["idiorigen"]."' and ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);";
             //else
             //   $sql2="UPDATE ts_existenciatarima set idy_ubica='".$data["ididestino"]."' where idy_ubica='".$data["idiorigen"]."' and ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);";
          }
          else 
          {
            /*
            if($tipo_pallet_contenedor_articulo == 'Artículo')
               $sql2="UPDATE ts_existenciapiezas set Existencia=Existencia-{$cantidad} where cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."';";
             else
              */
              //$sql2="UPDATE ts_existenciapiezas set Existencia=Existencia-{$cantidad} where idy_ubica='".$data["idiorigen"]."';";
             //$sql2="UPDATE ts_existenciapiezas set idy_ubica='".$data["ididestino"]."' where idy_ubica='".$data["idiorigen"]."';";
          }
        }
        else //contenedor
        {
            $sql2="UPDATE ts_existenciapiezas set Existencia=Existencia-{$cantidad} where cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."';";
        }
        if($pallets_por_piezas == 0 && $sql2 /* && !$fusionar_cajas*/)
        {
          sleep(1);
          $sth = \db()->prepare( $sql2 );
          //$sth->execute();
          while(!$sth->execute())
          {
            sleep(1);
            $sth = \db()->prepare( $sql2 );
          }


        }
        //echo $sql2;exit;
        if($fusionar /*&& ($nivel_ubicacion+0) > 0*/)
        {
            $sql2="UPDATE ts_existenciatarima SET ntarima=(SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '{$fusionar}' LIMIT 1) where ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);";
            $sth = \db()->prepare( $sql2 );
            $sth->execute();
        }

            //$sql .= "INSERT INTO `t_cardex` (`cve_articulo`, `cve_lote`, `fecha`, `origen`, `destino`, `cantidad`, `id_TipoMovimiento`, `cve_usuario`, `Cve_Almac`, `Activo`, `stockinicial`, `ajuste`) VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$idiorigen}', '{$ididestino}','{$cantidadTotal}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1, '{$cantidad_max}', {$cantidad_max}-{$cantidadTotal});";
            //$sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, stockinicial, ajuste) (SELECT cve_articulo, lote, NOW(), '{$idiorigen}', '{$ididestino}', existencia, '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1, existencia, 0 FROM ts_existenciatarima WHERE ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1))";


          if($fusionar_cajas)
          {
            $cve_articulo_kdx = $data["cve_articulo"];
            $lote_kdx = $data["cve_lote"];

            $sql = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, stockinicial, ajuste) VALUES ('{$cve_articulo_kdx}', '{$lote_kdx}', NOW(), '{$idiorigen}', '{$ididestino}','{$cantidad}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1, '{$cantidad}', 0);";

            $sth = \db()->prepare( $sql );
            $sth->execute();

            $sql_tr = "INSERT INTO t_MovCharolas(Cve_Almac, id_kardex, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                    VALUES('{$clave_almacen}', (SELECT MAX(id) FROM t_cardex), (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$fusionar_cajas}' LIMIT 1), NOW(), '{$idiorigen}', '{$ididestino}', '{$movimiento}', '{$cve_usuario}', 'I')";
            $rs = mysqli_query($conn, $sql_tr);

            $sql = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, stockinicial, ajuste) VALUES ('{$cve_articulo_kdx}', '{$lote_kdx}', NOW(), '{$idiorigen}', '{$ididestino}','{$cantidad}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1, '{$cantidad}', 0);";

            $sth = \db()->prepare( $sql );
            $sth->execute();

            $sql_tr = "INSERT INTO t_MovCharolas(Cve_Almac, id_kardex, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status, EsCaja)
                    VALUES('{$clave_almacen}', (SELECT MAX(id) FROM t_cardex), {$id_caja}, NOW(), '{$idiorigen}', '{$ididestino}', '{$movimiento}', '{$cve_usuario}', 'I', 'S')";
            $rs = mysqli_query($conn, $sql_tr);
          }

        if(!$fusionar_cajas)
        {
            $sql_kardex = "SELECT cve_articulo, lote, existencia FROM ts_existenciatarima WHERE ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1)";
            if($tipo_pallet_contenedor_articulo == 'Caja')
              $sql_kardex = "SELECT cve_articulo, cve_lote as lote, PiezasXCaja as existencia FROM ts_existenciacajas WHERE Id_Caja = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1)";
            if($pallets_por_piezas == 1)
              $sql_kardex = "SELECT cve_articulo, lote, $cantidad as existencia FROM ts_existenciatarima WHERE ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1) AND cve_articulo='".$data["cve_articulo"]."' and lote='".$data["cve_lote"]."'";
            $rs_kardex = mysqli_query($conn, $sql_kardex);
            //echo $sql_kardex;exit;
            while($row_kardex = mysqli_fetch_array($rs_kardex, MYSQLI_ASSOC))
            {
              $cve_articulo_kdx = $row_kardex["cve_articulo"];
              $lote_kdx         = $row_kardex["lote"];
              $existencia_kdx   = $row_kardex["existencia"];

              $sql = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, stockinicial, ajuste) VALUES ('{$cve_articulo_kdx}', '{$lote_kdx}', NOW(), '{$idiorigen}', '{$ididestino}','{$existencia_kdx}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1, '{$existencia_kdx}', 0);";

              $sth = \db()->prepare( $sql );
              $sth->execute();

              $EsCaja = 'N';
              if($tipo_pallet_contenedor_articulo == 'Caja')
                $EsCaja = 'S';

              $sql_tr = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status, EsCaja)
                      VALUES((SELECT MAX(id) FROM t_cardex),'{$clave_almacen}', (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1), NOW(), '{$idiorigen}', '{$ididestino}', 1, '{$cve_usuario}', 'I', '$EsCaja')";
              $sth = \db()->prepare( $sql_tr );
              $sth->execute();

/*
              if($fusionar_cajas)
              {
                $sql_tr = "INSERT INTO t_MovCharolas(Cve_Almac, id_kardex, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status, EsCaja)
                        VALUES('{$clave_almacen}', (SELECT MAX(id) FROM t_cardex), {$id_caja}, NOW(), '{$idiorigen}', '{$ididestino}', '{$movimiento}', '{$cve_usuario}', 'I', 'S')";
                $rs = mysqli_query($conn, $sql_tr);
              }
*/
            }
          }


          if($cantidad == $cantidad_max && $tipo_pallet_contenedor_articulo != 'Artículo')
          {
              /*
              $sql=" INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Existencia, ntarima, ID_Proveedor) 
              VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$cantidad."', (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1), '".$ID_Proveedor."') 
              ON DUPLICATE KEY UPDATE existencia=existencia+".$cantidad.";";
              */
          }
          else if($lp_dest == '' && !$fusionar_cajas)
          {
            
              $sql=" INSERT INTO ts_existenciapiezas (cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor, Cuarentena) 
              VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$cantidad."', '".$ID_Proveedor."', $QA) 
              ON DUPLICATE KEY UPDATE existencia=existencia+".$cantidad.";";
              @$sth = \db()->prepare( $sql );
              @$sth->execute();
              //echo $sql;exit;
              
          }
      }
        $sql ="DELETE from ts_existenciapiezas where Existencia<=0;
            
            DELETE from ts_existenciatarima where Existencia<=0;
        ";
        //DELETE from ts_existenciacajas where Existencia<=0;
        $sth = \db()->prepare( $sql );
        $sth->execute();

        //******************************************************************************************************************
        //                                                   PROCESAR QA
        //******************************************************************************************************************

        if($QA == 1)
        {
              $cve_articulo     = $data["cve_articulo"];
              $cve_lote         = $data["cve_lote"];
              $valor_lp         = $data["lp_val"];
              $valor_pallet     = $data["pallet_val"];
              $valor_origen     = $data["idiorigen"];
              $valor_destino    = $data["ididestino"];
              $cantidad         = $data["cantidad"];
              $cantidad_max     = $data["cantidad_max"];
              $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

              $sql_qa = "SELECT * FROM t_movcuarentena 
                         WHERE Cve_Articulo = '{$cve_articulo}' AND Cve_Lote = '{$cve_lote}' AND Idy_Ubica = '{$valor_origen}'";//AND IdContenedor = '{$valor_pallet}'
              $rs = mysqli_query($conn, $sql_qa);
              $existe = mysqli_num_rows($rs);
              if($existe)
              {
                  $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                  extract($resul);

                  $sql_qa = "UPDATE t_movcuarentena SET Idy_Ubica = '{$valor_destino}' WHERE Fol_Folio = '{$Fol_Folio}'";
                  $rs = mysqli_query($conn, $sql_qa);


                  if($valor_pallet)
                  {
                    $sql_qa = "UPDATE ts_existenciatarima SET Cuarentena = '{$QA}' WHERE cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}' AND idy_ubica = '{$valor_destino}' AND ntarima = '{$valor_pallet}'";
                    $rs = mysqli_query($conn, $sql_qa);
                  }
                  else
                  {
                    $sql_qa = "UPDATE ts_existenciapiezas SET Cuarentena = '{$QA}' WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND idy_ubica = '{$valor_destino}'";
                    $rs = mysqli_query($conn, $sql_qa);
                  }

              }
        }
}
else //if($trasladar_ubicacion == 1)
{

        if($palletizar_traslado && !$traslado_almacen)
        {
            $id_contenedor = $data['pallet_palletizar'];
            //$Folio = $data['ordenp'];
            //$cantidad = $data['cantidad'];
            //$cve_articulo = $data['clave'];
            //$clave_articulo = $cve_articulo;
            //$lote = $data['lote'];

            $sql = "SELECT (SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, (SELECT MAX(ch.IDContenedor)+2 FROM c_charolas ch) AS id_contenedor2, cve_almac, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND IDContenedor = {$id_contenedor} AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1";

            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

            //if(!$resul['id_contenedor']) break;

            $id_contenedor = $resul['id_contenedor'];
            $id_contenedor2 = $resul['id_contenedor2'];
            $descripcion   = $resul['descripcion'];
            $tipo          = $resul['tipo'];
            $alto          = $resul['alto'];
            $ancho         = $resul['ancho'];
            $fondo         = $resul['fondo'];
            $peso          = $resul['peso'];
            $pesomax       = $resul['pesomax'];
            $capavol       = $resul['capavol'];
            $id_almacen    = $resul['cve_almac'];

            //id_contenedor + id_contenedor2, asegura que el LP no se repita

            $label_lp = "LP".str_pad($id_contenedor.$id_contenedor2, 9, "0", STR_PAD_LEFT);

            $sql = "INSERT INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES ({$id_almacen}, '{$label_lp}', '{$descripcion}', 0, 'Pallet', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
            $rs = mysqli_query($conn, $sql);

            $valor_lp_palletizar     = $label_lp;
            $valor_pallet_palletizar = $label_lp;

            $sql=" INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Existencia, ntarima, ID_Proveedor, Cuarentena) (SELECT cve_almac, ".$data["ididestino"].", cve_articulo, cve_lote, Existencia, (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$label_lp}' LIMIT 1),ID_Proveedor, Cuarentena FROM ts_existenciapiezas WHERE idy_ubica = '".$data["idiorigen"]."') ON DUPLICATE KEY UPDATE existencia = existencia+{$Existencia}";

            $rs = mysqli_query($conn, $sql);

            $sql = "UPDATE ts_existenciapiezas SET Existencia = 0 WHERE idy_ubica='".$data["idiorigen"]."'";
            $rs = mysqli_query($conn, $sql);


          $sql = "SELECT cve_articulo, lote, NOW(), '{$idiorigen}', '{$ididestino}', Existencia, 0, Existencia, '{$movimiento}', '{$cve_usuario}', cve_almac, 1 FROM ts_existenciatarima WHERE idy_ubica = '{$ididestino}' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$label_lp}' LIMIT 1)";
          $rs = mysqli_query($conn, $sql);
          while($result = mysqli_fetch_array($rs, MYSQLI_ASSOC))
          {
            extract($result);
            $sql = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, cantidad, stockinicial, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo) VALUES ('$cve_articulo', '$lote', NOW(), '{$idiorigen}', '{$ididestino}', $Existencia, 0, $Existencia, '{$movimiento}', '{$cve_usuario}', $cve_almac, 1);";
            $rs1 = mysqli_query($conn, $sql);

            $sql ="INSERT INTO t_MovCharolas (Cve_Almac, id_kardex, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES ('{$alma}', (SELECT MAX(id) FROM t_cardex), (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$label_lp}' LIMIT 1), NOW(), '{$idiorigen}', '{$ididestino}', '{$movimiento}', '{$cve_usuario}', 'I');";
            $rs2 = mysqli_query($conn, $sql);

            if($fusionar_cajas)
            {
              $sql_tr = "INSERT INTO t_MovCharolas(Cve_Almac, id_kardex, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status, EsCaja)
                      VALUES('{$alma}', (SELECT MAX(id) FROM t_cardex), {$id_caja}, NOW(), '{$idiorigen}', '{$ididestino}', '{$movimiento}', '{$cve_usuario}', 'I', 'S')";
              $rs = mysqli_query($conn, $sql_tr);
            }

          }


        }
        else
        {
            $sql = "UPDATE ts_existenciapiezas SET idy_ubica = '".$data["ididestino"]."' WHERE idy_ubica='".$data["idiorigen"]."'";
            $rs = mysqli_query($conn, $sql);

            $sql = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, cantidad, stockinicial, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo) (SELECT cve_articulo, cve_lote, NOW(), '{$idiorigen}', '{$ididestino}', Existencia, 0, Existencia, '{$movimiento}', '{$cve_usuario}', cve_almac, 1 FROM ts_existenciapiezas WHERE idy_ubica = '{$ididestino}');";
            $rs = mysqli_query($conn, $sql);
        }
}
      $valor_lp = $data["lp_val"];

      if($traslado_almacen && $valor_lp)
      {
        $ub_destino = $data["ididestino"];
        $query_destino = mysqli_query(\db2(), "SELECT DISTINCT cve_almac FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = '$ub_destino' AND tipo = 'ubicacion' LIMIT 1");
        $row_destino = mysqli_fetch_assoc($query_destino);
        $alm_destino = $row_destino['cve_almac'];

        $query_destino = mysqli_query(\db2(), "UPDATE c_charolas SET cve_almac = $alm_destino WHERE clave_contenedor = '{$valor_lp}'");
      }

      //*******************************************************************************
      //                          EJECUTAR EN INFINITY
      //*******************************************************************************
      $query = mysqli_query(\db2(), "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'");
      $ejecutar_infinity = mysqli_fetch_assoc($query)['existe'];

      if($ejecutar_infinity && $traslado_almacen)
      {
        $query = mysqli_query(\db2(), "SELECT Url, Servicio, User, Pswd, IFNULL(Puerto, '8080') as Puerto, Empresa, to_base64(CONCAT(USER,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1");
        $row_infinity = mysqli_fetch_assoc($query);
        $Url_inf = $row_infinity['Url'];
        $url_curl = $row_infinity['url_curl'];
        $Servicio_inf = $row_infinity['Servicio'];
        $Puerto = $row_infinity['Puerto'];
        $User_inf = $row_infinity['User'];
        $Pswd_inf = $row_infinity['Pswd'];
        $Empresa_inf = $row_infinity['Empresa'];
        $Codificado = $row_infinity['Codificado'];

        $ub_origen =  $data["idiorigen"];
        $ub_destino = $data["ididestino"];


        //**************************************************************************************************************
        //                                              DATOS ORIGEN 
        //**************************************************************************************************************
        $query_origen = mysqli_query(\db2(), "SELECT DISTINCT cve_almac FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = '$ub_origen' AND tipo = 'ubicacion' LIMIT 1");
        $row_origen = mysqli_fetch_assoc($query_origen);
        $alm_origen = $row_origen['cve_almac'];

        $query_destino = mysqli_query(\db2(), "SELECT DISTINCT cve_almac FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = '$ub_destino' AND tipo = 'ubicacion' LIMIT 1");
        $row_destino = mysqli_fetch_assoc($query_destino);
        $alm_destino = $row_destino['cve_almac'];

        $sql = "SELECT e.cve_articulo, e.cve_lote, SUM(e.Existencia) AS Cantidad, u.cve_umed, al.clave, NOW() as HoraTraslado
                FROM V_ExistenciaGralProduccion e
                LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                LEFT JOIN c_almacenp al ON al.id = e.cve_almac
                WHERE e.cve_articulo = '{$cve_articulo}' AND  e.cve_almac = '$alm_origen' AND e.tipo = 'ubicacion' #AND e.cve_lote = '{$cve_lote}'
                GROUP BY e.cve_almac, e.cve_articulo #, e.cve_lote
                ";

      $query_ori = mysqli_query(\db2(), $sql);
      $json = '[';
      
      $num_rows = mysqli_num_rows($query_ori);

      while($row_ori = mysqli_fetch_assoc($query_ori))
      {
        extract($row_ori);
         $json .= '{"item":"'.$cve_articulo.'", "batch": "'.$cve_lote.'","qty":'.$Cantidad.',"um":"'.$cve_umed.'","typeMov":"T","warehouse":"'.$clave.'","dataOpe":"'.$HoraTraslado.'"},';//NOW()
      }
      if($num_rows == 0)
      {

        $query_cero = mysqli_query(\db2(), "SELECT NOW() as hora_traslado, u.cve_umed
                                            FROM c_articulo a 
                                            LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                                            WHERE a.cve_articulo = '{$cve_articulo}'
                                            LIMIT 1");
        $row_cero = mysqli_fetch_assoc($query_cero);
        $hora_traslado = $row_cero['hora_traslado'];
        $cve_umed = $row_cero['cve_umed'];

        $query_cero = mysqli_query(\db2(), "SELECT clave FROM c_almacenp WHERE id = '$alm_origen' LIMIT 1");
        $row_cero = mysqli_fetch_assoc($query_cero);
        $clave = $row_cero['clave'];


        $json .= '{"item":"'.$cve_articulo.'", "batch": "'.$cve_lote.'","qty":0,"um":"'.$cve_umed.'","typeMov":"T","warehouse":"'.$clave.'","dataOpe":"'.$hora_traslado.'"},';//NOW()
      }

      $json[strlen($json)-1] = ' ';
      $json .= ']';


          $curl = curl_init();
          //$url_curl = "$Url_inf.':'.$Puerto.'/'.$Servicio_inf";

          curl_setopt_array($curl, array(
            // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
            //CURLOPT_URL => '{$Url_inf:$Puerto/$Servicio_inf}',
            CURLOPT_URL => "$url_curl",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>
            // Aquí cambia la cadena JSON
            $json,
            CURLOPT_HTTPHEADER => array(
              'Content-Type: application/json',
              'Authorization: Basic '.$Codificado.''
            ),
          ));
          /*
//$curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://156.54.166.4:8081/wms_trasp',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'[{"item":"40003-100-03", "batch": "","qty":5964,"um":"PIEZA","typeMov":"T","warehouse":"WH1","dataOpe":"2025-01-18 00:02:16"} ]',
      CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json',
      'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
      ),
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_SSL_VERIFYPEER => false
    ));
*/
   // $response = curl_exec($curl);
   // curl_close($curl);
          $response = curl_exec($curl);

          curl_close($curl);      
          //echo $response;
          $query = mysqli_query(\db2(), "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', 'Traspaso entre Almacenes', 'WEB')");

        //**************************************************************************************************************
        //                                              DATOS DESTINO 
        //**************************************************************************************************************

        $sql = "SELECT e.cve_articulo, e.cve_lote, SUM(e.Existencia) AS Cantidad, u.cve_umed, al.clave, NOW() as HoraTraslado
                FROM V_ExistenciaGralProduccion e
                LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                LEFT JOIN c_almacenp al ON al.id = e.cve_almac
                WHERE e.cve_articulo = '{$cve_articulo}'  AND  e.cve_almac = '$alm_destino' AND e.tipo = 'ubicacion' #AND e.cve_lote = '{$cve_lote}'
                GROUP BY e.cve_almac, e.cve_articulo #, e.cve_lote
                ";

      $query_dest = mysqli_query(\db2(), $sql);
      $json = '[';
      
      while($row_dest = mysqli_fetch_assoc($query_dest))
      {
        extract($row_dest);
        $json .= '{"item":"'.$cve_articulo.'", "batch": "'.$cve_lote.'","qty":'.$Cantidad.',"um":"'.$cve_umed.'","typeMov":"T","warehouse":"'.$clave.'","dataOpe":"'.$HoraTraslado.'"},';//NOW()
      }
      $json[strlen($json)-1] = ' ';
      $json .= ']';


          $curl = curl_init();

          curl_setopt_array($curl, array(
            // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
            //CURLOPT_URL => '{$Url_inf:$Puerto/$Servicio_inf}',
            CURLOPT_URL => "$url_curl",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>
            // Aquí cambia la cadena JSON
            $json,
            CURLOPT_HTTPHEADER => array(
              'Content-Type: application/json',
              'Authorization: Basic '.$Codificado.''
            ),
          ));
          $response = curl_exec($curl);

          curl_close($curl);      
          //echo $response;
          $query = mysqli_query(\db2(), "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', 'Traspaso entre Almacenes', 'WEB')");


      }
      //*******************************************************************************
      //*******************************************************************************
    
        //******************************************************************************************************************

     }
  
    function importeTotal($data)
    {
      extract($data);
      $articulo = $data["articulo"];
      $zona = $data["zona"];
      $almacen = $data["almacen"];
      $lotes = $data["lotes"];
      $proveedor = $data["proveedor"];
      $bl = $data["bl"];
      $sqlZona = !empty($zona) ? 
           "AND e.cve_ubicacion IN(
            SELECT DISTINCT idy_ubica 
            FROM c_ubicacion 
            WHERE cve_almac = '{$zona}')" : "";
      //$sqlArticulo = !empty($articulo) ? 
      //     "AND e.cve_articulo = '{$articulo}'" : ""; 
      $sqlArticulo = !empty($articulo) ? 
           "AND td.cve_articulo = '{$articulo}'" : ""; 
      $sqlLotes = !empty($lotes) ? 
           "AND IFNULL(td.Cve_Lote, '') = '{$lotes}'" : ""; 
      $sqlProveedor = !empty($proveedor) ? 
           "AND (IFNULL(
                    (SELECT nombre from c_proveedores where ID_Proveedor = (
                        IFNULL(
                            (SELECT ID_Proveedor from ts_existenciapiezas where ts_existenciapiezas.idy_ubica = e.cve_ubicacion and ts_existenciapiezas.cve_articulo = e.cve_articulo limit 1),
                            IFNULL(
                                (SELECT NULL from ts_existenciacajas where ts_existenciacajas.idy_ubica = e.cve_ubicacion and ts_existenciacajas.cve_articulo = e.cve_articulo limit 1),
                                IFNULL(
                                    (SELECT ID_Proveedor from ts_existenciatarima where ts_existenciatarima.idy_ubica = e.cve_ubicacion and ts_existenciatarima.cve_articulo = e.cve_articulo limit 1),
                                    0
                                )
                            )
                        )
                    )),0
                ))  = '{$proveedor}'" : "";//ID_Proveedor
      $sqlbl = !empty($bl) ? "AND u.CodigoCSD like '%{$bl}%'" : ""; 

/*
      $sql = 
        "SELECT (SELECT SUM(IFNULL(a.costoPromedio, a.costo)*e.Existencia)) as importeTotalPromedio 
        FROM V_ExistenciaGralProduccion e 
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo 
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion 
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo 
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion) 
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp 
            LEFT JOIN c_serie s ON s.cve_articulo = e.cve_articulo 
        WHERE e.cve_almac = '{$almacen}' 
          AND e.tipo = 'ubicacion' 
          AND e.Existencia > 0 
          {$sqlArticulo} 
          {$sqlZona}
          {$sqlProveedor}
          {$sqlbl}
        ORDER BY l.CADUCIDAD ASC";
*/
        $sql = "SELECT SUM(IFNULL(td.costo, 0)*IFNULL(td.cantidad, 0)) AS importeTotalPromedio
  FROM td_aduana td
  LEFT JOIN th_aduana th ON th.num_pedimento = td.num_orden
  LEFT JOIN c_almacenp a ON a.clave = th.Cve_Almac
  WHERE  a.id = '{$almacen}' {$sqlArticulo} {$sqlLotes}";

      $sth = \db()->prepare($sql);
      $sth->execute();
      return $sth->fetch();
    }
}
