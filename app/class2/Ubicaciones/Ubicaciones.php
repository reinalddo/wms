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
        WHERE (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = c.idy_ubica) IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '{$almacen}')
          AND c.Activo = 1 AND c.idy_ubica IN (SELECT cve_ubicacion FROM V_ExistenciaGralProduccion {$excludeSql}) AND (c.AcomodoMixto = 'N' OR c.AcomodoMixto = '')
          AND c.idy_ubica = ve.cve_ubicacion {$sqlTraslado}

        UNION

        SELECT  
          c.idy_ubica,
          c.CodigoCSD as ubicacion, 
          c.AcomodoMixto
        FROM 
          c_ubicacion c #, V_ExistenciaGralProduccion ve
        WHERE (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = c.idy_ubica) IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = '{$almacen}')
          AND c.Activo = 1 
          #AND c.idy_ubica IN (SELECT Cve_Ubicacion FROM V_EspacioUbicXProd) 
          AND c.AcomodoMixto = 'S'
          #AND c.idy_ubica = ve.cve_ubicacion 
          AND ((c.idy_ubica IN (SELECT idy_ubica FROM ts_existenciapiezas))
              OR  (c.idy_ubica IN (SELECT idy_ubica FROM ts_existenciatarima))
              OR  (c.idy_ubica IN (SELECT idy_ubica FROM ts_existenciacajas))) {$sqlTraslado}
        ";

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

    function getUbicacionesNoLlenasxZona($zona, $excludeInventario = 0, $ubicacion_origen, $tipo_pca, $descripcion_pca) 
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

        $sql = "
SELECT  
  c.idy_ubica,
  c.CodigoCSD AS ubicacion, 
  c.AcomodoMixto
FROM 
  c_ubicacion c
WHERE c.cve_almac = '{$zona}' 
  AND c.Activo = 1 AND c.AcomodoMixto = 'S' AND c.Tipo = 'L' AND c.Tipo <> 'R' AND c.Tipo <> 'Q'
  AND IF(c.PesoMaximo = 0, 1, ((SELECT SUM(V_ExistenciaGral.Existencia * c_articulo.peso) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = c.idy_ubica) * 100 / c.PesoMaximo)) < 100
  AND IF(((c.num_ancho/1000) * (c.num_largo/1000) * (c.num_alto/1000)) = 0, 1, (SELECT SUM(V_ExistenciaGral.Existencia * ((c_articulo.alto/1000) * (c_articulo.fondo/1000) * (c_articulo.ancho/1000))) FROM V_ExistenciaGral LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo WHERE V_ExistenciaGral.cve_ubicacion = c.idy_ubica) * 100 / ((c.num_ancho/1000) * (c.num_largo/1000) * (c.num_alto/1000))) < 100

UNION

SELECT  
  c.idy_ubica,
  c.CodigoCSD AS ubicacion, 
  c.AcomodoMixto
FROM 
  c_ubicacion c
WHERE c.cve_almac = '{$zona}' 
  AND c.Activo = 1 AND c.Tipo = 'L' AND c.Tipo <> 'R' AND c.Tipo <> 'Q'
  AND c.idy_ubica NOT IN (SELECT cve_ubicacion FROM V_ExistenciaGral WHERE V_ExistenciaGral.tipo='area' AND IFNULL(cve_ubicacion, '') != '')
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
            COALESCE(if(a.control_lotes = 'S',if(l.Caducidad = DATE_FORMAT('0000-00-00', '%Y-%m-%d'),'',date_format(l.Caducidad,'%d-%m-%Y')),'')) as caducidad,
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
              SELECT cve_ubicacion, cve_articulo, cve_lote, Existencia, Cve_Contenedor, Cuarentena FROM V_ExistenciaGral WHERE cve_ubicacion = '{$idy_ubica}'
              UNION SELECT cve_ubicacion, cve_articulo, cve_lote, Existencia, Cve_Contenedor, Cuarentena FROM V_ExistenciaProduccion WHERE cve_ubicacion = '{$idy_ubica}'
          ) AS v
          LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
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
        $sqlZonas = "
            SELECT 
                cve_almac AS clave,
                des_almac AS descripcion
            FROM c_almacen
            WHERE cve_almacenp = {$cve_almac} 
              AND cve_almac IN (
                SELECT DISTINCT cve_almac 
                FROM c_ubicacion 
                WHERE idy_ubica IN (
                    SELECT DISTINCT cve_ubicacion 
                    FROM V_ExistenciaGralProduccion 
                    WHERE Existencia > 0
                )
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
            WHERE e.Existencia >0 {$sql_proveedor}
            GROUP BY proveedor
            ";

        $sqlGrupos = "SELECT id, cve_gpoart AS cve_grupo, des_gpoart AS des_grupo 
                      FROM c_gpoarticulo WHERE Activo = 1 ORDER BY des_grupo ASC";

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
        return array(
            "zonas"     => $zonas,
            "articulos" => $articulos,
            "contenedores" => $contenedores,
            "proveedores" => $proveedores,
            "grupos" => $grupos
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
              IFNULL(IF(l.CADUCIDAD = '0000-00-00', '', DATE_FORMAT(l.CADUCIDAD, '%d-%m-%Y')), '') as caducidad,
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

    function getArticulosRecepcion($cve_ubicacion) 
    { //PR acomodo
      $sql = "
      SELECT DISTINCT
          V_EntradasContenedores.Fol_Folio AS folio,
          #IF(V_EntradasContenedores.Clave_Contenedor != '', 'Contenedor','Articulo') AS tipo,
          IFNULL(V_EntradasContenedores.Clave_Contenedor, 'Articulo') AS tipo,
          IFNULL(c_articulo.control_abc, '') AS control_abc,
          IFNULL(IF(c_articulo.control_lotes = 'S', tde.cve_lote,''), '') AS lote,
          IF(c_articulo.Caduca = 'S', IFNULL(IF(DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y') = '00-00-0000', '', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y')), ''), '') AS caducidad,
          IF(c_articulo.control_numero_series = 'S', V_EntradasContenedores.Cve_lote,'') AS numero_serie,
          #IFNULL(CAST(((c_articulo.alto / 1000) * (c_articulo.ancho / 1000) * (c_articulo.fondo / 1000) * IFNULL(t_pendienteacomodo.Cantidad, 0)) AS DECIMAL(10,6)), 0) AS volumen_ocupado,
          IFNULL(CAST(((c_articulo.alto / 1000) * (c_articulo.ancho / 1000) * (c_articulo.fondo / 1000)) AS DECIMAL(10,6)), 0) AS volumen_ocupado,
          tde.cve_articulo AS clave,
          a.des_articulo AS descripcion,
          a.control_peso,
          IFNULL(tde.CantidadRecibida - IFNULL(tde.CantidadUbicada, 0), 0) AS num_existencia,
          #IFNULL(t_pendienteacomodo.Cantidad, 0) AS num_existencia,
          /*c_articulo.des_articulo AS descripcion,*/
          /*IFNULL(V_EntradasContenedores.Clave_Contenedor,V_EntradasContenedores.Cve_Articulo) AS pallet_contenedor,*/
          V_EntradasContenedores.Clave_Contenedor AS pallet_contenedor,
          '' AS LP,
          #IFNULL(CAST((c_articulo.peso * IFNULL(t_pendienteacomodo.Cantidad, 0)) AS DECIMAL(10,2)), 0) AS peso_total,
          IFNULL(CAST((c_articulo.peso) AS DECIMAL(10,2)), 0) AS peso_total,
          a.id AS id_articulo,
          c_proveedores.Nombre AS proveedor,
          V_EntradasContenedores.Cve_Proveedor AS  id_proveedor
          FROM V_EntradasContenedores
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_EntradasContenedores.Cve_articulo
          LEFT JOIN t_pendienteacomodo ON t_pendienteacomodo.cve_articulo = V_EntradasContenedores.Cve_articulo AND t_pendienteacomodo.cve_lote = V_EntradasContenedores.Cve_Lote AND t_pendienteacomodo.cve_ubicacion = '{$cve_ubicacion}' AND t_pendienteacomodo.ID_Proveedor = V_EntradasContenedores.Cve_Proveedor
          LEFT JOIN c_lotes ON c_lotes.LOTE = V_EntradasContenedores.Cve_Lote AND c_lotes.cve_articulo = V_EntradasContenedores.Cve_articulo 
          LEFT JOIN c_serie ON c_serie.numero_serie = V_EntradasContenedores.Cve_Lote AND c_serie.cve_articulo = V_EntradasContenedores.Cve_articulo 
          LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = V_EntradasContenedores.Cve_Proveedor
          LEFT JOIN td_entalmacen tde ON tde.fol_folio = V_EntradasContenedores.Fol_Folio AND tde.cve_articulo = V_EntradasContenedores.Cve_articulo AND tde.cve_lote = V_EntradasContenedores.Cve_Lote
          LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo AND t_pendienteacomodo.cve_articulo = a.cve_articulo
          WHERE 0 = 0 
          AND V_EntradasContenedores.Cve_Ubicacion = '{$cve_ubicacion}'
          AND a.id <> ''
          AND V_EntradasContenedores.Cantidad_C = 0
          AND V_EntradasContenedores.Cve_Lote IN (SELECT cve_lote FROM V_ExistenciaGralProduccion WHERE tipo = 'area')
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
              c_charolas.clave_contenedor AS pallet_contenedor,
              c_charolas.CveLP AS LP,
              SUM(IFNULL(CAST((a.peso*V_EntradasContenedores.Cantidad_C) AS DECIMAL(10,2)),0)) AS peso_total,
              V_EntradasContenedores.Clave_Contenedor AS id_articulo,
              c_proveedores.Nombre AS proveedor,
              V_EntradasContenedores.Cve_Proveedor AS id_proveedor
              FROM V_EntradasContenedores JOIN c_articulo a ON a.cve_articulo = V_EntradasContenedores.Cve_articulo
              LEFT JOIN c_lotes ON c_lotes.LOTE = V_EntradasContenedores.Cve_Lote AND c_lotes.cve_articulo = V_EntradasContenedores.Cve_articulo
              LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = V_EntradasContenedores.Cve_Proveedor
              #LEFT JOIN c_charolas ON c_charolas.clave_contenedor = V_EntradasContenedores.Clave_Contenedor
              LEFT JOIN c_charolas ON c_charolas.CveLP = V_EntradasContenedores.Clave_Contenedor
              LEFT JOIN ts_existenciatarima tr ON tr.Fol_Folio = V_EntradasContenedores.Fol_Folio AND tr.cve_articulo = V_EntradasContenedores.Cve_Articulo AND tr.lote = V_EntradasContenedores.Cve_Lote
              LEFT JOIN td_entalmacenxtarima tt ON tt.fol_folio = V_EntradasContenedores.Fol_Folio AND tt.cve_articulo = V_EntradasContenedores.Cve_Articulo AND tt.cve_lote = V_EntradasContenedores.Cve_Lote AND tt.ClaveEtiqueta = V_EntradasContenedores.Clave_Contenedor
              WHERE V_EntradasContenedores.Cve_Ubicacion = '{$cve_ubicacion}' 
              #AND tr.ntarima NOT IN (SELECT ntarima FROM ts_existenciatarima WHERE Fol_Folio = tr.Fol_Folio)
              AND V_EntradasContenedores.Clave_Contenedor != ''
              AND V_EntradasContenedores.Cantidad_C > 0
              AND IFNULL(tt.Ubicada, 'N') != 'S'
              #AND V_EntradasContenedores.Cantidad_C != V_EntradasContenedores.CantidadUbicada
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

      $sql = "";
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

          $sql .= 
            "INSERT INTO `t_cardex` (`cve_articulo`, `cve_lote`, `fecha`, `origen`, `destino`, `cantidad`, `id_TipoMovimiento`, `cve_usuario`, `Cve_Almac`, `Activo`) 
             VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$idiorigen}', '{$ididestino}','{$cantidadTotal}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1);";

        //if ($tipo =="Por Pieza")
        if ($tipo == '1')
        {//tipo1
          $sql.=
            "INSERT INTO ts_existenciapiezas (cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) 
               VALUES ('{$almacen}', '$ididestino', '$cve_articulo', '$cve_lote', '$cantidad', '$ID_Proveedor') 
               ON DUPLICATE KEY UPDATE
               Existencia=Existencia+$cantidad,ID_Proveedor= $ID_Proveedor;";
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
           "SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '{$pallet_contenedor}' OR CveLP ='{$pallet_contenedor}' LIMIT 1"
           );
          $query->execute();
          $IDContenedor = $query->fetch()['IDContenedor'];



          $sql2 = "";

          $sql2 = \db()->prepare
          (
            "SELECT * FROM td_entalmacenxtarima WHERE ClaveEtiqueta = (SELECT CveLP FROM c_charolas WHERE clave_contenedor = '{$pallet_contenedor}');"
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

             $sql5 =\db()->prepare(
              "INSERT INTO `t_cardex` (`cve_articulo`, `cve_lote`, `fecha`, `origen`, `destino`, `cantidad`, `id_TipoMovimiento`, `cve_usuario`, `Cve_Almac`, `Activo`) 
             VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '$Fol_Folio', '{$ididestino}','{$existencia}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1);");
              //echo var_dump($sql3);
              $sql5->execute();

             $sql4 =\db()->prepare(
              "INSERT IGNORE INTO t_MovCharolas (Cve_Almac, id_kardex, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) 
            VALUES ('$cve_almac', (SELECT MAX(id) FROM t_cardex), '$ntarima', NOW(), '$cve_almaci', '{$ididestino}', 2, '{$cve_usuario}', 'I');");
              //echo var_dump($sql3);
              $sql4->execute();


          }

           $sql6 =\db()->prepare(
            "UPDATE td_entalmacenxtarima SET Ubicada='S' WHERE ClaveEtiqueta=(SELECT CveLP FROM c_charolas WHERE clave_contenedor = '{$pallet_contenedor}');");
            //echo var_dump($sql3);
            $sql6->execute();

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
            sum(Existencia) as Existencia FROM ts_existenciacajas where  cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."' and Existencia*PiezasXCaja>".$data['cantidadTotal']." and Existencia>1;";
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

                $sql2="DELETE FROM ts_existenciacajas where cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."' and PiezasXCaja='".$registro["PiezasXCaja"]."' and Existencia='".$registro["Existencia"]."';";
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

            $sql.= "UPDATE ts_existenciacajas SET Existencia=Existencia-".$data["cantidad"]." WHERE cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."' and Existencia>=".$data['cantidadTotal']." and ID_Proveedor='".$ID_Proveedor."';";

            //$sql.= "UPDATE ts_existenciacajas SET Existencia=Existencia-".$data["cantidad"]." WHERE id=$id_cajas;";

            if(!$traslado_almacen)
            $sql.=" INSERT INTO ts_existenciacajas (cve_almac, idy_ubica, cve_articulo, cve_lote,PiezasXCaja, Existencia, ID_Proveedor) 
            VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."','".$data["piezaxcaja"]."', '".$data["cantidad"]."', '".$ID_Proveedor."') 
            ON DUPLICATE KEY UPDATE Existencia=Existencia+".$data["cantidad"].",ID_Proveedor='".$ID_Proveedor."';";
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
            delete from ts_existenciacajas where Existencia<=0;
            delete from ts_existenciatarima where Existencia<=0;
        ";
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

        $id_piezas = 0; $id_cajas = 0; $id_tarima = 0;

        $valor_lp         = $data["lp_val"];
        $valor_pallet     = $data["pallet_val"];
        $valor_origen     = $data["idiorigen"];
        $valor_destino    = $data["ididestino"];
        $cantidad         = $data["cantidad"];
        $cantidad_max     = $data["cantidad_max"];

        $query3 = mysqli_query(\db2(), "SELECT IFNULL(cve_nivel, 0) as nivel FROM c_ubicacion WHERE idy_ubica = '$ididestino'");
        $nivel_ubicacion = mysqli_fetch_assoc($query3)['nivel'];

        //******************************************************************************************************
        //                                            PALLETIZAR TRASLADOS
        //******************************************************************************************************
        $valor_lp_palletizar     = "";
        $valor_pallet_palletizar = "";
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

            $sql = "INSERT INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) 
                    VALUES ({$id_almacen}, '{$label_lp}', '{$descripcion}', 0, 'Pallet', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
            $rs = mysqli_query($conn, $sql);

            $valor_lp_palletizar     = $label_lp;
            $valor_pallet_palletizar = $label_lp;

            $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - {$cantidad} WHERE cve_articulo = '".$data["cve_articulo"]."' AND cve_lote = '".$data["cve_lote"]."' AND idy_ubica='".$data["idiorigen"]."'";
            $rs = mysqli_query($conn, $sql);   

            //$sql = "SELECT idy_ubica, ID_Proveedor FROM ts_existenciapiezas WHERE cve_articulo = '".$data["cve_articulo"]."' AND cve_lote = '".$data["cve_lote"]."' AND idy_ubica='".$data["idiorigen"]."'";
            //$rs = mysqli_query($conn, $sql);
            //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            //$idy_ubica    = $resul['idy_ubica'];
            //$ID_Proveedor = $resul['ID_Proveedor'];

            //$sql = "INSERT INTO ts_existenciatarima(cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) VALUES({$id_almacen}, {$idy_ubica}, '{$cve_articulo}', '{$lote}', 0, {$id_contenedor}, 0, {$cantidad}, 1, {$ID_Proveedor}, 0) ON DUPLICATE KEY UPDATE existencia = existencia + {$cantidad}";
            $sql=" INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Existencia, ntarima, ID_Proveedor) 
              VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$cantidad."', (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp_palletizar}' LIMIT 1), '".$ID_Proveedor."') 
              ON DUPLICATE KEY UPDATE existencia=existencia+".$cantidad.";";

            $rs = mysqli_query($conn, $sql);

        }
        //******************************************************************************************************
      $sql = "";
      if($tipo_pallet_contenedor_articulo == 'Artículo' /*&& !$traslado_almacen*/  && !$valor_lp && !$palletizar_traslado)
      {
        $sql2="UPDATE ts_existenciapiezas set Existencia=Existencia-{$cantidad} where cve_articulo='".$data["cve_articulo"]."' and cve_lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."';";
        $sth = \db()->prepare( $sql2 );
        $sth->execute();

        $sql=" INSERT INTO ts_existenciapiezas (cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) 
        VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$cantidad."', '".$ID_Proveedor."') ON DUPLICATE KEY UPDATE Existencia=Existencia+".$cantidad.";";

        if($fusionar && ($nivel_ubicacion+0) > 0)
            $sql=" INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Existencia, ntarima, ID_Proveedor) 
              VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$cantidad."', (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '{$fusionar}' LIMIT 1), '".$ID_Proveedor."') ON DUPLICATE KEY UPDATE existencia=existencia+".$cantidad.";";

        $sth = \db()->prepare( $sql );
        $sth->execute();

              //$sql_tr = "INSERT INTO t_MovCharolas(Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
              //        VALUES('{$clave_almacen}', {$id_contenedor}, NOW(), '{$Folio}', '{$cve_ubicacion}', 1, '{$usuario}', 'I')";
              //$rs = mysqli_query($conn, $sql_tr);

            $sql = "INSERT INTO `t_cardex` (`cve_articulo`, `cve_lote`, `fecha`, `origen`, `destino`, `cantidad`, `id_TipoMovimiento`, `cve_usuario`, `Cve_Almac`, `Activo`, `stockinicial`, `ajuste`) 
            VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$idiorigen}', '{$ididestino}','{$cantidadTotal}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1, '{$cantidad_max}', {$cantidad_max}-{$cantidadTotal});";

            $sth = \db()->prepare( $sql );
            $sth->execute();

      }
      else if(/*!$traslado_almacen &&*/ !$palletizar_traslado)
      {

        if($valor_pallet || (($tipo_pallet_contenedor_articulo == 'Contenedor' || $tipo_pallet_contenedor_articulo == 'Pallet') && !$valor_pallet))
        {
            //$query_rev = mysqli_query(\db2(), "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE cve_articulo='".$data["cve_articulo"]."' and lote='".$data["cve_lote"]."' and idy_ubica='".$data["idiorigen"]."'");
            $query_rev = mysqli_query(\db2(), "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE idy_ubica='".$data["idiorigen"]."' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '".$data["lp_val"]."')");
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
             $sql2="UPDATE ts_existenciatarima set idy_ubica='".$data["ididestino"]."', cve_almac = '{$almacen}' where idy_ubica='".$data["idiorigen"]."' and ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);";
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
        $sth = \db()->prepare( $sql2 );
        $sth->execute();
        if($fusionar && ($nivel_ubicacion+0) > 0)
        {
            $sql2="UPDATE ts_existenciatarima SET ntarima=(SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '{$fusionar}' LIMIT 1) where ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1);";
            $sth = \db()->prepare( $sql2 );
            $sth->execute();
        }

            //$sql .= "INSERT INTO `t_cardex` (`cve_articulo`, `cve_lote`, `fecha`, `origen`, `destino`, `cantidad`, `id_TipoMovimiento`, `cve_usuario`, `Cve_Almac`, `Activo`, `stockinicial`, `ajuste`) VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$idiorigen}', '{$ididestino}','{$cantidadTotal}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1, '{$cantidad_max}', {$cantidad_max}-{$cantidadTotal});";
            //$sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, stockinicial, ajuste) (SELECT cve_articulo, lote, NOW(), '{$idiorigen}', '{$ididestino}', existencia, '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1, existencia, 0 FROM ts_existenciatarima WHERE ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1))";


            $sql_kardex = "SELECT cve_articulo, lote, existencia FROM ts_existenciatarima WHERE ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1)";
            $rs_kardex = mysqli_query($conn, $sql_kardex);

            while($row_kardex = mysqli_fetch_array($rs_kardex, MYSQLI_ASSOC))
            {
              $cve_articulo_kdx = $row_kardex["cve_articulo"];
              $lote_kdx         = $row_kardex["lote"];
              $existencia_kdx   = $row_kardex["existencia"];

              $sql = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, stockinicial, ajuste) VALUES ('{$cve_articulo_kdx}', '{$lote_kdx}', NOW(), '{$idiorigen}', '{$ididestino}','{$existencia_kdx}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1, '{$existencia_kdx}', 0);";

              $sth = \db()->prepare( $sql );
              $sth->execute();


              $sql_tr = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                      VALUES((SELECT MAX(id) FROM t_cardex),'{$clave_almacen}', (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1), NOW(), '{$idiorigen}', '{$ididestino}', 1, '{$cve_usuario}', 'I')";
              $sth = \db()->prepare( $sql_tr );
              $sth->execute();

            }


/*
            $sql_tr = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                    VALUES((SELECT MAX(id) FROM t_cardex),'{$clave_almacen}', (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1), NOW(), '{$idiorigen}', '{$ididestino}', 1, '{$cve_usuario}', 'I')";
            $sth = \db()->prepare( $sql_tr );
            $sth->execute();
*/


/*
        if($valor_lp_palletizar)
        {
          $valor_lp = $valor_lp_palletizar;
          $valor_pallet = $valor_pallet_palletizar;
        }
*/
          if($cantidad == $cantidad_max && $tipo_pallet_contenedor_articulo != 'Artículo')
          {
              /*
              $sql=" INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Existencia, ntarima, ID_Proveedor) 
              VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$cantidad."', (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$valor_lp}' LIMIT 1), '".$ID_Proveedor."') 
              ON DUPLICATE KEY UPDATE existencia=existencia+".$cantidad.";";
              */
          }
          else 
          {
            
              $sql=" INSERT INTO ts_existenciapiezas (cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) 
              VALUES ('{$almacen}', '".$data["ididestino"]."', '".$data["cve_articulo"]."', '".$data["cve_lote"]."', '".$cantidad."', '".$ID_Proveedor."') 
              ON DUPLICATE KEY UPDATE existencia=existencia+".$cantidad.";";
              $sth = \db()->prepare( $sql );
              $sth->execute();
              
          }
      }
        $sql ="DELETE from ts_existenciapiezas where Existencia<=0;
            DELETE from ts_existenciacajas where Existencia<=0;
            DELETE from ts_existenciatarima where Existencia<=0;
        ";
        $sth = \db()->prepare( $sql );
        $sth->execute();
/*
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
            $pendienteacomodo = true;
            if($valor_lp)
            {
                $sql_tr = "UPDATE c_charolas SET cve_almac = {$ididestino} WHERE CveLP = '{$valor_lp}'";
                $rs = mysqli_query($conn, $sql_tr);

                #$sql_tr = "INSERT INTO td_entalmacenxtarima(fol_folio, cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, Ubicada, PzsXCaja) VALUES ({$Folio}, '{$cve_articulo}', '{$cve_lote}', '{$valor_lp}', {$cantidadTotal}, 'N', 1)";
                $sql_tr = "INSERT INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadRecibida, CantidadUbicada, cve_usuario, cve_ubicacion, fecha_inicio, fecha_fin) 
                (SELECT {$Folio}, cve_articulo, lote, existencia, 0, '{$cve_usuario}', '{$zonarecepcioni_alm}', NOW(), NOW() FROM ts_existenciatarima WHERE ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '$valor_lp' LIMIT 1))";
                $rs = mysqli_query($conn, $sql_tr);

                $sql_tr = "INSERT INTO td_entalmacenxtarima(fol_folio, cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, Ubicada, PzsXCaja) 
                        (SELECT {$Folio}, cve_articulo, lote, '{$valor_lp}',existencia, 'N', 1 FROM ts_existenciatarima WHERE ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '$valor_lp' LIMIT 1))";
                $rs = mysqli_query($conn, $sql_tr);

                //if($valor_pallet || ($tipo_pallet_contenedor_articulo == 'Contenedor' && !$valor_pallet))
                if($valor_pallet || (($tipo_pallet_contenedor_articulo == 'Contenedor' || $tipo_pallet_contenedor_articulo == 'Pallet') && !$valor_pallet))

                {
                    //$sql_tr = "UPDATE ts_existenciatarima SET existencia = existencia-{$cantidadTotal} WHERE idy_ubica = '{$idy_ubica}' AND cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '$valor_lp' LIMIT 1)";
                  $pendienteacomodo = false;
                  $sql_acomodo = "SELECT cve_almac, idy_ubica, cve_articulo, lote, Fol_folio, ntarima, capcidad, existencia, ID_Proveedor, Cuarentena FROM ts_existenciatarima WHERE ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '$valor_lp' LIMIT 1)";
                  $rs_acomodo = mysqli_query($conn, $sql_acomodo);
                  //********************************************************************************************************
                  //                                    t_pendienteacomodo
                  //********************************************************************************************************
                  while($row_acomodo = mysqli_fetch_array($rs_acomodo, MYSQLI_ASSOC))
                  {
                      $cve_articulo2 = $row_acomodo["cve_articulo"];
                      $cve_lote2 = $row_acomodo["lote"];
                      $cantidadTotal2 = $row_acomodo["existencia"];

                      $sql_tr = "SELECT COUNT(*) AS existe FROM t_pendienteacomodo WHERE cve_articulo = '{$cve_articulo2}' AND cve_lote = '{$cve_lote2}' AND cve_ubicacion = '{$zonarecepcioni_alm}' AND ID_Proveedor = {$id_proveedor}";
                      $rs = mysqli_query($conn, $sql_tr);
                      $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                      $existe = $resul['existe'];

                      if(!$existe)
                      {
                          $sql_tr = "INSERT INTO t_pendienteacomodo(cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) 
                                  VALUES ('{$cve_articulo2}', '{$cve_lote2}', {$cantidadTotal2}, '{$zonarecepcioni_alm}', {$id_proveedor})";
                          $rs = mysqli_query($conn, $sql_tr);
                      }
                      else
                      {
                          $sql_tr = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad + {$cantidadTotal2} WHERE cve_articulo = '{$cve_articulo2}' AND cve_lote = '{$cve_lote2}' AND cve_ubicacion = '{$zonarecepcioni_alm}' AND ID_Proveedor = {$id_proveedor}";
                          $rs = mysqli_query($conn, $sql_tr);
                      }
                      //********************************************************************************************************
                        //echo "SQL2 = ".$sql2." SQL = ".$sql;
                      $sql .= "INSERT INTO `t_cardex` (`cve_articulo`, `cve_lote`, `fecha`, `origen`, `destino`, `cantidad`, `id_TipoMovimiento`, `cve_usuario`, `Cve_Almac`, `Activo`) 
                      VALUES ('{$cve_articulo2}', '{$cve_lote2}', NOW(), '{$idiorigen}', '{$ididestino}','{$cantidadTotal2}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1);";

                      $sth = \db()->prepare( $sql );
                      $sth->execute();
                  }

                  $sql_tr = "DELETE FROM ts_existenciatarima WHERE idy_ubica = '{$idy_ubica}' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '$valor_lp' LIMIT 1)";
                }
                else 
                {

                    $sql_tr = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                            VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$idiorigen}', '{$zonarecepcioni_alm}', {$cantidadTotal}, 1, '{$cve_usuario}', {$almacen}, 1, NOW())";
                    $rs = mysqli_query($conn, $sql_tr);

                    $sql_tr = "UPDATE ts_existenciapiezas SET Existencia = Existencia-{$cantidadTotal} WHERE idy_ubica = '{$idy_ubica}' AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}'";
                }

                $rs = mysqli_query($conn, $sql_tr);

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


          if($pendienteacomodo == true)
          {
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

            //echo "SQL2 = ".$sql2." SQL = ".$sql;
            $sql .= "INSERT INTO `t_cardex` (`cve_articulo`, `cve_lote`, `fecha`, `origen`, `destino`, `cantidad`, `id_TipoMovimiento`, `cve_usuario`, `Cve_Almac`, `Activo`) 
            VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$idiorigen}', '{$ididestino}','{$cantidadTotal}', '{$movimiento}', '{$cve_usuario}', '{$almacen}', 1);";

            $sth = \db()->prepare( $sql );
            $sth->execute();

          }

        }$traslado_almacen
*/
        //******************************************************************************************************************
        //                                                   PROCESAR QA
        //******************************************************************************************************************
        $QA               = ($data["QA"]=='Si')?1:0;

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
                    $sql_qa = "UPDATE ts_existenciatarima SET Cuarentena = '{$QA}' WHERE cve_crticulo = '{$cve_articulo}' AND lote = '{$cve_lote}' AND idy_ubica = '{$valor_destino}' AND ntarima = '{$valor_pallet}'";
                    $rs = mysqli_query($conn, $sql_qa);
                  }
                  else
                  {
                    $sql_qa = "UPDATE ts_existenciapiezas SET Cuarentena = '{$QA}' WHERE cve_crticulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND idy_ubica = '{$valor_destino}'";
                    $rs = mysqli_query($conn, $sql_qa);
                  }

              }
        }

        //******************************************************************************************************************

     }
  
    function importeTotal($data)
    {
      extract($data);
      $articulo = $data["articulo"];
      $zona = $data["zona"];
      $almacen = $data["almacen"];
      $proveedor = $data["proveedor"];
      $bl = $data["bl"];
      $sqlZona = !empty($zona) ? 
           "AND e.cve_ubicacion IN(
            SELECT DISTINCT idy_ubica 
            FROM c_ubicacion 
            WHERE cve_almac = '{$zona}')" : "";
      $sqlArticulo = !empty($articulo) ? 
           "AND e.cve_articulo = '{$articulo}'" : ""; 
      $sqlProveedor = !empty($proveedor) ? 
           "AND (IFNULL(
                    (SELECT nombre from c_proveedores where ID_Proveedor = (
                        IFNULL(
                            (SELECT ID_Proveedor from ts_existenciapiezas where ts_existenciapiezas.idy_ubica = e.cve_ubicacion and ts_existenciapiezas.cve_articulo = e.cve_articulo limit 1),
                            IFNULL(
                                (SELECT ID_Proveedor from ts_existenciacajas where ts_existenciacajas.idy_ubica = e.cve_ubicacion and ts_existenciacajas.cve_articulo = e.cve_articulo limit 1),
                                IFNULL(
                                    (SELECT ID_Proveedor from ts_existenciatarima where ts_existenciatarima.idy_ubica = e.cve_ubicacion and ts_existenciatarima.cve_articulo = e.cve_articulo limit 1),
                                    0
                                )
                            )
                        )
                    )),0
                ))  = '{$proveedor}'" : "";
      $sqlbl = !empty($bl) ? "AND u.CodigoCSD like '%{$bl}%'" : ""; 

      $sql = 
        "SELECT (SELECT SUM(a.costoPromedio*e.Existencia)) as importeTotalPromedio 
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
      
      $sth = \db()->prepare($sql);
      $sth->execute();
      return $sth->fetch();
    }
}
