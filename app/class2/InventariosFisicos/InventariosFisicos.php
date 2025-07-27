<?php 

namespace InventariosFisicos;

require dirname(dirname(dirname(__DIR__))).'/TCPDF/tcpdf.php';

class InventariosFisicos {

    const TABLE = 'th_inventario';
    const TABLE_D = 'th_inventario';
    var $identifier;

    public function __construct( $ID_Inventario = false, $key = false ) 
    {
        if( $ID_Inventario ) 
        {
          $this->ID_Inventario = (int) $ID_Inventario;
        }

        if($key) 
        {
          $sql = sprintf('
            SELECT
            ID_Inventario
            FROM
            %s
            WHERE
            ID_Inventario = ?
            ', self::TABLE );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\InventariosFisicos\InventariosFisicos');
            $sth->execute(array($key));
            $ID_Inventario = $sth->fetch();
            $this->ID_Inventario = $ID_Inventario->ID_Inventario;
        }
    }

    private function load() 
    {
      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          ID_Inventario = ?
        ', self::TABLE );
      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\InventariosFisicos\InventariosFisicos' );
      $sth->execute( array( $this->ID_Inventario ) );
      $this->data = $sth->fetch();
    }

    function getAll() 
    {
      $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
        ';
      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\InventariosFisicos\InventariosFisicos' );
      $sth->execute( array( ID_Inventario ) );
      return $sth->fetchAll();
    }

    function __get( $key ) 
    {
      switch($key) 
      {
        case 'ID_Inventario':
            $this->load();
            return @$this->data->$key;
        default:
            return $this->key;
      }
    }


    function saveInventario($fecha, $status, $almacen, $zona) 
    {		
      if($zona == "")
      {
        /* Se deberia poder buscar la zona por ubicaciones */
      }
      
      $sql = sprintf("
        INSERT INTO " . self::TABLE . "
        SET
          Fecha = :Fecha
          ,Status=:Status
          ,cve_almacen=:cve_almacen
          ,cve_zona=:cve_zona
      ");

      $this->save = \db()->prepare($sql);
      $this->save->bindValue( ':Fecha', substr($fecha, 0, 10), \PDO::PARAM_STR );
      $this->save->bindValue( ':Status', $status, \PDO::PARAM_STR );
      $this->save->bindValue( ':cve_almacen', $almacen, \PDO::PARAM_STR );
      $this->save->bindValue( ':cve_zona', ($zona=='')?0:$zona, \PDO::PARAM_STR );
      $this->save->execute(); 
    }

    function saveConteo($conteo) 
    {		
      //(select MAX(ID_Inventario) from th_inventario)
      $conteo = intval($conteo);
      $sql = "INSERT INTO t_conteoinventario (ID_Inventario,NConteo,Status)
      VALUES ((select MAX(ID_Inventario) from th_inventario),{$conteo},'A');";
      $this->save = \db()->prepare($sql);
      $this->save->execute(); 
    }


    function EliminarUbicacionesVaciasConteo0($id_inventario) 
    {   
/*
      $sql = sprintf("
        DELETE FROM t_invpiezas WHERE id IN (SELECT DISTINCT e.id FROM t_invpiezas e
        WHERE e.ID_Inventario = {$id_inventario} 
        AND CONCAT(e.cve_articulo, e.cve_lote) IN (SELECT CONCAT(i.cve_articulo, i.cve_lote) FROM (SELECT DISTINCT t.cve_articulo, t.cve_lote, (GROUP_CONCAT(t.NConteo SEPARATOR '')+0) AS Conteos FROM t_invpiezas t WHERE t.ID_Inventario = {$id_inventario} AND cve_articulo != '' GROUP BY cve_articulo, cve_lote) AS i WHERE i.Conteos = 0))
      ");
*/
      $sql = "CALL SPRP_ReporteInvFisico({$id_inventario})";
      //$this->save = \db()->prepare($sql);
      //$this->save->execute(); 
      $query = \db()->prepare($sql);
      $query->execute(); 
      $res = $query->fetchAll(\PDO::FETCH_ASSOC);

      foreach($res as $row)
      {
         $Cerrado = $row['Cerrado'];

         if($Cerrado == 0)
         {
            $lp           = $row["Lp"];
            $cve_articulo = $row["Clave"];
            $idy_ubica    = $row["Cve_Ubicacion"];
            $lote         = $row["LoteSerie"];

            $sql = "";
            if($lp)
               $sql = "DELETE FROM t_invtarima WHERE ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$lp}' LIMIT 1) AND idy_ubica = '{$idy_ubica}' AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$lote}' AND ID_Inventario = {$id_inventario} ;";
            else
               $sql = "DELETE FROM t_invpiezas WHERE idy_ubica = '{$idy_ubica}' AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$lote}' AND ID_Inventario = {$id_inventario};";
            $query = \db()->prepare($sql);
            $query->execute(); 
         }
      }


    }


    function saveExistencia($id = false, $usuario = false, $ubicacion)
    {
/*
      $gral_ubica = str_replace(" ","",explode('|',$ubicacion));
      $ubicacion = $gral_ubica[0];
      $area = $gral_ubica[1]; //si es true es area si es false es ubicacion
*/
      $ubicaciones = explode("|", $ubicacion);
      $ubicacion = trim($ubicaciones[0]);
      $area = trim($ubicaciones[1]);

      $result=array();
      //$idSQL = $id === false  ? "(SELECT MAX(ID_Inventario) FROM th_inventario)" : $id;
      $idSQL = $id;
      if($id == false)
      {
        $sql = "SELECT MAX(ID_Inventario) FROM th_inventario";
        $sth = \db()->prepare($sql);
        $sth->execute();
        $idSQL = $sth->fetch()[0];
      }

      $usuario = !$usuario ? '' : $usuario;

      $sql_conteo = "SELECT IFNULL(MAX(NConteo), 0) FROM t_conteoinventario WHERE ID_Inventario = {$idSQL}";
      $sth = \db()->prepare($sql_conteo);
      $sth->execute();
      $conteo_maximo = $sth->fetch()[0];
//      $conteo_maximo = 0;
      $sql="";
      //if($conteo_maximo >= 1)
      //{
              /*"SELECT 
                idy_ubica,
                cve_articulo, 
                cve_lote,
                ExistenciaTeorica
              FROM t_invpiezas
              WHERE ID_Inventario = '{$idSQL}'
              AND Art_Cerrado IS NULL
              AND NConteo = '{$conteo_maximo}'
              ";*/

      //  $sql .= 
      //  "SELECT 
      //      idy_ubica AS cve_ubicacion,
      //      cve_articulo, 
      //      cve_lote,
      //      ExistenciaTeorica
      //    FROM t_invpiezas
      //    WHERE ID_Inventario = $idSQL
      //    AND Art_Cerrado = 0 
      //    AND Cantidad <> ExistenciaTeorica
      //    AND NConteo = '0'
      //    ";
      //}
      //else
      //{
        $sql .=
            "SELECT 
              cve_articulo, 
              cve_ubicacion, 
              cve_lote, 
              Existencia as cantidad,
              IFNULL(Cve_Contenedor, '') as Cve_Contenedor, 
              ID_Proveedor
            FROM V_ExistenciaGralProduccion 
            WHERE 1 #Existencia > 0 
            AND cve_ubicacion IN (SELECT COALESCE(cve_ubicacion, idy_ubica) FROM t_ubicacioninventario WHERE t_ubicacioninventario.idy_ubica = '$ubicacion')

        UNION 

        SELECT DISTINCT
          ve.cve_articulo AS cve_articulo,
          u.idy_ubica AS cve_ubicacion,
          IFNULL(ve.cve_lote, '') AS cve_lote,
          IFNULL(ve.Existencia, 0) AS cantidad,
          IFNULL(ve.Cve_Contenedor, '') as Cve_Contenedor,
          ve.ID_Proveedor as ID_Proveedor
        FROM c_almacenp p, c_ubicacion u
        LEFT JOIN V_ExistenciaGralProduccion ve ON ve.cve_ubicacion = u.idy_ubica
        LEFT JOIN c_charolas ch ON ch.clave_contenedor = ve.Cve_Contenedor
        , c_almacen a
        WHERE 1 
            AND u.cve_almac=a.cve_almac
            AND a.cve_almacenp=p.id
            AND u.cve_almac = a.cve_almac
            AND u.Activo = '1' 
            AND u.idy_ubica = '$ubicacion'
            ";//WHERE ID_Inventario = {$idSQL}
      //}
      $result["sql1"] = $sql;
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetchAll();

      $sql = "";
      if(!empty($data))
      {
        foreach ($data as $e)
        {
          extract($e);
          //if($conteo_maximo > 1 || $conteo_maximo == 1)
          //{
          //  $sql .=
          //  "
          //    INSERT INTO t_invpiezas
          //      (ID_Inventario, 
          //      NConteo, 
          //      cve_articulo, 
          //      cve_lote, 
          //      idy_ubica, 
          //      Cantidad, 
          //      ExistenciaTeorica, 
          //      cve_usuario, 
          //      fecha, 
          //      fecha_fin,
          //      Art_Cerrado,
          //      Activo) 
          //    VALUES
          //      ({$idSQL}, 
          //      (SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = $idSQL), 
          //      '$cve_articulo',  
          //      '$cve_lote',
          //      '$cve_ubicacion', 
          //      0, 
          //      '$ExistenciaTeorica', 
          //      '".$usuario[0]["cve_usuario"]."', 
          //      NOW(), 
          //      NOW(), 
          //      0,
          //      1); 
          //  ";
          //}
          //else
          //{
          $sql = "";
          if($Cve_Contenedor == '')
          {

            $sql .=
            "
              INSERT INTO t_invpiezas
                (ID_Inventario, 
                NConteo, 
                cve_articulo, 
                cve_lote, 
                idy_ubica, 
                Cantidad, 
                ExistenciaTeorica, 
                cve_usuario, 
                fecha, 
                fecha_fin,
                Art_Cerrado,
                Activo, 
                ID_Proveedor) 
              VALUES
                ({$idSQL}, 
                (SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = $idSQL), 
                '$cve_articulo',  
                '$cve_lote',
                '$cve_ubicacion', 
                0, 
                $cantidad, 
                '".$usuario[0]["cve_usuario"]."', 
                NOW(), 
                NOW(), 
                0,
                1, 
                '$ID_Proveedor'); 
            ";
          }
          else
          {
            $sql .=
            "
              INSERT INTO t_invtarima
                (ID_Inventario, 
                NConteo, 
                cve_articulo, 
                Cve_Lote, 
                idy_ubica, 
                existencia, 
                Teorico, 
                cve_usuario, 
                fecha, 
                ntarima,
                Abierto,
                Activo, 
                ID_Proveedor) 
              VALUES
                ({$idSQL}, 
                (SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = $idSQL), 
                '$cve_articulo',  
                '$cve_lote',
                '$cve_ubicacion', 
                0, 
                $cantidad, 
                '".$usuario[0]["cve_usuario"]."', 
                NOW(), 
                (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor'), 
                'N',
                1, 
                '$ID_Proveedor'); 
            ";
          }
           //}
           //return "SQL = ".$sql;
        $result["sql2"][]=$sql;
        $sth = \db()->prepare($sql);
        $sth->execute();

           /*
            $sql_cab = "INSERT INTO cab_planifica_inventario(ID_PLAN, cve_articulo, ID_PERIODO, DESCRIPCION, FECHA_INI, FECHA_FIN, INTERVALO, ID_EXCALAR, DIA_MES, MES_YEAR, DIAS_LABORABLES, Activo) VALUES ($idSQL, '$cve_articulo', 1, '', NOW(), NOW(), 1, 0, 0, 0, 'N', 1);";
            //return "SQL = ".$sql." ------ SQL CAB = ".$sql_cab;
            $result["sql_cab"][]=$sql_cab;
            $sth_cab = \db()->prepare($sql_cab);
            $sth_cab->execute();
            */
        }

        //$result["sql2"][]=$sql;
        //$sth = \db()->prepare($sql);
        //$sth->execute();
      }
      return $result;
    }

    function saveUbicacion( $ubicacion ) 
    {

      $ubicaciones = explode("|", $ubicacion);
      $ubicacion = trim($ubicaciones[0]);
      $area = trim($ubicaciones[1]);

/*
      $gral_ubica = str_replace(" ","",explode('|',$ubicacion));
      $ubicacion = $gral_ubica[0];
      $area = $gral_ubica[1]; //si es true es area si es false es ubicacion
*/
      if($area == "true") 
      {
        //INSERT INTO t_ubicacioninventario (ID_Inventario,NConteo,cve_ubicacion,status)
        $sql = sprintf("
          INSERT INTO t_ubicacioninventario (ID_Inventario,NConteo,idy_ubica,status)
          VALUES ((select MAX(ID_Inventario) from th_inventario),1,'".$ubicacion."','A');"
        );
      }
      else
      {
        $sql = sprintf("
          INSERT INTO t_ubicacioninventario (ID_Inventario,NConteo,idy_ubica,status)
          VALUES ((select MAX(ID_Inventario) from th_inventario),1,'".$ubicacion."','A');"
        );
      }		
      $this->save = \db()->prepare($sql);
      $this->save->execute(); 
      
      $sql_ubicaciones_por_inventariar = sprintf("
          INSERT INTO `t_ubicacionesainventariar`(`ID_Inventario`, `idy_ubica`, `status`) 
          VALUES ((select MAX(ID_Inventario) from th_inventario),'{$ubicacion}','A');"
        );
      $this->save = \db()->prepare($sql_ubicaciones_por_inventariar);
      $this->save->execute();

    }
  
    function saveInvCajas($ubicacion,$articulo,$lote,$cantidad) 
    {		
      $sql = sprintf("
        INSERT INTO t_invcajas (ID_Inventario,NConteo,idy_ubica,cve_articulo,cve_lote,Cantidad,PiezasxCaja,fecha)
        VALUES ((select MAX(ID_Inventario) from th_inventario),1,".$ubicacion.",'".$articulo."','".$lote."',".$cantidad.",".$cantidad.",now())");
      $this->save = \db()->prepare($sql);
      $this->save->execute(); 
    }


    function traerArticulosUbicacion($ubicacion) 
    {
      $sql = '
        select distinct (c.cve_articulo) as cve_articulo, 
        l.LOTE as cve_lote, 
        e.PiezasXCaja as Cantidad
        from c_ubicacion u, ts_existenciacajas e, c_articulo c, c_lotes l
        where
        u.idy_ubica='.$ubicacion.' and
        e.idy_ubica = u.idy_ubica and
        c.cve_articulo = e.cve_articulo and
        l.LOTE = e.cve_lote;
      ';
      $sth = \db()->prepare( $sql );
      $sth->execute();
      return $sth->fetchAll();
    }


    function actualizarStatus( $_post ) 
    {
      try 
      {
        $sql = "UPDATE " . self::TABLE . " SET status='".$_post['status']."' WHERE ID_Pedido='".$_post['ID_Pedido']."'";
        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
      } 
      catch(Exception $e) 
      {
        return 'ERROR: ' . $e->getMessage();
      }
    }

    function loadDetalleCount($codigo, $conteo)
    {
      $sql=
        "SELECT COUNT(cve_articulo) FROM `t_invpiezas` WHERE `ID_Inventario` = ".$codigo." And NConteo = ".$conteo."";
      /*"
        SELECT 
          COUNT(cve_articulo) AS total
        FROM V_ExistenciaGralProduccion
        WHERE Existencia > 0 AND cve_ubicacion IN (select COALESCE(idy_ubica, cve_ubicacion) from t_ubicacioninventario where ID_Inventario = {$codigo})
      "*/
      $sth = \db()->prepare($sql);
      $sth->execute();
      return $sth->fetch()['total'];
    }

    function saveUser($id, $usuario)
    {
      $result=array();
    
      $sqlConteo = "SELECT MAX(NConteo) AS conteo FROM t_conteoinventario WHERE ID_Inventario = {$id};";
      $sth = \db()->prepare($sqlConteo);
      $sth->execute();
      $conteo = intval($sth->fetch()['conteo']) + 1;
      //foreach($usuario as $user)
      //{
        $sql = 
          "
          INSERT  INTO t_conteoinventario (ID_Inventario, NConteo, cve_usuario, Status, Activo)
          VALUES ({$id}, {$conteo}, '{$usuario}', 'A', 1);
          ";
        $sth = \db()->prepare($sql);
        $result["status"] = $sth->execute();
      //}
      $result["saveExistencias"] = $this->saveExistencia($id, $usuario, "");
      return  $result;     
    }
  
    function terminar_usuarios($id_inventario)
    {
      $sqlConteo = "SELECT MAX(NConteo) AS conteo FROM t_conteoinventario WHERE ID_Inventario = {$id};";
      $sth = \db()->prepare($sqlConteo);
      $sth->execute();
      $conteo = intval($sth->fetch()['conteo']) + 1;
      $sql = "
        INSERT  INTO t_conteoinventario (ID_Inventario, NConteo, cve_usuario, Status, Activo)
        VALUES ({$id}, {$conteo}, '{$usuario}', 'A', 1);
      ";
      $sth = \db()->prepare($sql);
      $result=array();
      $result["status"]= $sth->execute();
      $result["saveExistencias"] = $this->saveExistencia($id, $usuario);
      return  $result;     
    }
  
    function saveUsers($id, $usuario)
    {
      $sql = "UPDATE t_conteoinventario SET cve_usuario='".$usuario."' WHERE id='".$id."'";
      $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));   
      return  $rs;     
    }
    
    function saveUserss($id, $usuario)
    {
      $sql = "UPDATE t_conteoinventariocicl SET cve_usuario='".$usuario."' WHERE id='".$id."'";
      $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));   
      return  $rs;     
    }
  
    function loadDetalle($id_inventario, $start, $limit, $nconteo = false, $criterio = '')
    {
      $chop=false;
      $producto="";
      if($criterio !='')
      {
        if(is_numeric($criterio))
        {
          $producto = "AND t_invpiezas.cve_articulo LIKE '%".$criterio."%'";
          $chop=true;
        }
        if(is_string($criterio) && $chop==false)
        {
          $producto = "AND c_articulo.des_articulo LIKE "."'%".$criterio."%'";
        }
      }
      /*
      $sql = "
          Select
              Coalesce(c_ubicacion.CodigoCSD, tubicacionesretencion.desc_ubicacion) as ubicacion,
              t_invpiezas.cve_articulo as clave,
              c_articulo.des_articulo as descripcion,
              if(c_articulo.control_lotes = 'S',t_invpiezas.cve_lote,'') as lote,
              Ifnull(Date_format(if(c_lotes.caducidad = '0000-00-00','',c_lotes.Caducidad), '%d-%m-%Y'), '') as caducidad,
              if(c_articulo.control_numero_series = 'S',t_invpiezas.cve_lote,'') as serie,
              t_invpiezas.NConteo as conteo,
              (t_invpiezas.existenciateorica) as stockTeorico,
              (t_invpiezas.cantidad) as stockFisico,
              (t_invpiezas.cantidad - t_invpiezas.existenciateorica) as diferencia,
              c_usuario.nombre_completo as usuario,
              'Piezas' as unidad_medida
          FROM t_invpiezas
              LEFT JOIN t_ubicacioninventario on t_ubicacioninventario.id_inventario = t_invpiezas.id_inventario and t_ubicacioninventario.idy_ubica = t_invpiezas.idy_ubica 
              LEFT JOIN c_articulo on c_articulo.cve_articulo = t_invpiezas.cve_articulo
              LEFT JOIN c_lotes on c_lotes.lote = t_invpiezas.cve_lote AND t_invpiezas.cve_articulo = c_lotes.cve_articulo
              #LEFT JOIN c_serie on c_serie.cve_articulo = t_invpiezas.cve_articulo 
              LEFT JOIN c_usuario on c_usuario.cve_usuario = t_invpiezas.cve_usuario
              LEFT JOIN tubicacionesretencion ON tubicacionesretencion.cve_ubicacion = t_ubicacioninventario.cve_ubicacion
              LEFT JOIN c_ubicacion ON t_ubicacioninventario.idy_ubica = c_ubicacion.idy_ubica
          WHERE  t_invpiezas.id_inventario = ".$id_inventario."
              AND t_invpiezas.NConteo = ".$nconteo." AND c_ubicacion.CodigoCSD <> ''
              ".$producto."
          order by c_articulo.des_articulo;
      ";
      //Coalesce(t_ubicacioninventario.idy_ubica, t_ubicacioninventario.cve_ubicacion) as ubicacion,
      //GROUP BY clave
      
      //echo var_dump($sql);
      //die();
      */

      $sql = "
SELECT  tinv.zona, tinv.cve_ubicacion, tinv.ubicacion, tinv.clave, tinv.descripcion, tinv.lote, tinv.caducidad, 
  tinv.conteo, '' as serie,
  GROUP_CONCAT(DISTINCT tinv.NConteo_Cantidad_reg SEPARATOR ',') AS NConteo_Cantidad_reg, 
  GROUP_CONCAT(DISTINCT tinv.Nconteo SEPARATOR ',') AS Nconteo, '' as conteo,
  tinv.stockTeorico, 
  GROUP_CONCAT(DISTINCT tinv.Cantidad_reg SEPARATOR ',') AS Cantidad_reg, '' as diferencia,
  tinv.Cantidad, tinv.Cerrar, tinv.LP, tinv.usuario, tinv.unidad_medida, tinv.Status 
FROM (

        SELECT DISTINCT 
                c.des_almac zona,
                v.cve_ubicacion, 
                (CASE 
                    WHEN v.tipo = 'area' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion) 
                    WHEN v.tipo = 'ubicacion' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)
                    ELSE '--'
                END) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(v.cve_lote = '', '', v.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = v.cve_articulo AND ext.idy_ubica = v.cve_ubicacion AND ext.cve_lote = v.cve_lote AND v.tipo = 'ubicacion'), 0) AS stockTeorico,
                IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = v.cve_ubicacion AND cve_lote = v.cve_lote AND cve_articulo = v.cve_articulo), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion  AND iv.NConteo = MAX(inv.NConteo) AND iv.cve_lote = v.cve_lote) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo > 0 ), 0, 1) AS Cerrar,
                '' AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                (SELECT Status FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS Status
            FROM V_ExistenciaGralProduccion v
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                LEFT JOIN t_invpiezas inv ON inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote 
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
            WHERE v.Existencia > 0 AND 
            v.tipo='ubicacion' AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_articulo = v.cve_articulo AND inv.cve_lote = v.cve_lote 
            #AND inv.NConteo > 0 
            AND CONCAT(v.cve_articulo,v.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invtarima WHERE ID_Inventario = {$id_inventario})
            GROUP BY LP,clave,cve_ubicacion,lote

        UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = 168 AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                (SELECT Status FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezas inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #LEFT JOIN t_invtarima invt ON invt.ID_Inventario = inv.ID_Inventario AND invt.cve_articulo = c_articulo.cve_articulo AND invt.idy_ubica = ub.idy_ubica
                #LEFT JOIN c_charolas ch ON ch.IDContenedor = invt.ntarima
                #####
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario}             AND NConteo = 0)
            ) 
            #AND inv.NConteo > 0 
            GROUP BY LP,clave,ubicacion,lote

UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.existencia) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #(SELECT ext.Teorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = ch.clave_contenedor), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.existencia ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                (SELECT STATUS FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS STATUS
            FROM t_invtarima inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo)
            #AND inv.NConteo > 0 
            GROUP BY LP,clave,ubicacion,lote

  UNION 

        SELECT DISTINCT 
                '' AS zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                '' AS clave,
                '' AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                '' AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                (SELECT STATUS FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS STATUS
            FROM t_invpiezas inv
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = ''
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario}             AND NConteo = 0)
            ) 
            #AND inv.NConteo > 0 
            GROUP BY LP,clave,ubicacion,lote

            ORDER BY ubicacion
            ) AS tinv
            GROUP BY clave, ubicacion, lote, LP
            ";

      $sth = \db()->prepare($sql);
      $sth->execute();
      $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
      return $result;
    }

    /*function loadDetalle($codigo, $start, $limit, $conteo = false, $criterio = '') 
    {
      $result=array();
      $conteo = ($conteo !== false) ? intval($conteo) : "(SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = {$codigo} )";

      // Revisar si es un inventario fisico o planeacion 
      $and='';
      if($criterio != '')
      {
        $and = " and (c_articulo.cve_articulo = '{$criterio}' or c_articulo.des_articulo like '%{$criterio}%')";
      }
      $sql = 
        "
          SELECT 
            IF(LENGTH(fecha_final) > 2, 'f', 'p') AS tipo
          FROM V_AdministracionInventario
          WHERE consecutivo = {$codigo}
        ";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $tipo = $sth->fetch(\PDO::FETCH_ASSOC)['tipo'];

      if($tipo === 'f' && gettype($conteo) !== "integer")
      {
        $sql = 
        "
          SELECT
            V_ExistenciaFisica.ubicacion,
            IFNULL(c_articulo.cve_articulo, '--') as clave,
            IFNULL(c_articulo.des_articulo, '--') as descripcion,   
            if(V_ExistenciaFisica.cve_lote = '', '--', V_ExistenciaFisica.cve_lote) as lote,
            IFNULL(c_lotes.CADUCIDAD, '--') as caducidad,
            IFNULL(c_serie.numero_serie, '--') as serie,
            (SELECT Cantidad FROM t_invpiezas WHERE ID_Inventario = {$codigo} AND cve_articulo = V_ExistenciaFisica.cve_articulo AND idy_ubica = V_ExistenciaFisica.idy_ubica AND NConteo =(SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = {$codigo})) as stockTeorico,
            IFNULL(V_ExistenciaFisica.Existencia, '--') as stockFisico,
            IFNULL(((SELECT stockTeorico) - (SELECT stockFisico)), '--') as diferencia,
            V_ExistenciaFisica.NConteo as conteo,
            V_ExistenciaFisica.usuario,
            'Piezas' as unidad_medida
          FROM V_ExistenciaFisica 
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaFisica.cve_articulo
            LEFT JOIN c_lotes ON c_lotes.LOTE = V_ExistenciaFisica.cve_lote
            LEFT JOIN c_serie ON c_serie.cve_articulo = c_articulo.cve_articulo
            LEFT JOIN V_ExistenciaGral ON V_ExistenciaGral.cve_ubicacion = V_ExistenciaFisica.idy_ubica and V_ExistenciaGral.cve_articulo = V_ExistenciaFisica.cve_articulo
          WHERE 
            V_ExistenciaFisica.idy_ubica IN (SELECT DISTINCT cve_ubicacion from V_Ubicacion_Inventario where ID_Inventario = {$codigo} AND tipo is not null) AND
            V_ExistenciaFisica.ID_Inventario = {$codigo} AND 
            V_ExistenciaFisica.NConteo = {$conteo}
        ";
      }
      else if($tipo === 'p' || gettype($conteo) === "integer")
      {
        //Para planeación 
        $sql = 
        "
          SELECT
            (CASE
            WHEN v.tipo = 'area' and v.cve_ubicacion IS NOT NULL 
            THEN (SELECT desc_ubicacion as ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion  )
            WHEN v.tipo = 'ubicacion' and v.cve_ubicacion IS NOT NULL 
            THEN (SELECT u.CodigoCSD as ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)
            ELSE '--'
            END) as ubicacion,
            IFNULL(c_articulo.cve_articulo, '--') as clave,
            IFNULL(c_articulo.des_articulo, '--') as descripcion,
            if(v.cve_lote = '', '--', v.cve_lote) as lote,
            IFNULL(DATE_FORMAT(c_lotes.CADUCIDAD, '%d-%m-%Y'), '--') as caducidad,
            IFNULL(c_serie.numero_serie, '--') as serie,
            {$conteo} as conteo,
            (SELECT ExistenciaTeorica FROM t_invpiezas WHERE ID_Inventario = {$codigo} AND NConteo = (SELECT MIN(NConteo) FROM t_conteoinventario WHERE ID_Inventario = {$codigo}) AND cve_articulo = v.cve_articulo AND idy_ubica = v.cve_ubicacion) as stockTeorico,
            (SELECT Cantidad FROM t_invpiezas WHERE ID_Inventario = {$codigo} AND NConteo = (SELECT conteo) AND cve_articulo = v.cve_articulo AND idy_ubica = v.cve_ubicacion) as stockFisico,
            (SELECT stockFisico) - (SELECT stockTeorico) as diferencia,                        
            (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT cve_usuario FROM t_conteoinventario WHERE ID_Inventario = {$codigo} AND NConteo = (SELECT conteo))) as usuario,
            'Piezas' as unidad_medida,
            CONCAT(v.tipo, '|', v.cve_ubicacion, '|', v.cve_articulo, '|', {$codigo}, '|', (SELECT conteo), '|', (SELECT cve_usuario FROM t_conteoinventario WHERE NConteo = (SELECT conteo) AND ID_Inventario = {$codigo})) AS id
          FROM V_ExistenciaGralProduccion v
            LEFT JOIN c_articulo on c_articulo.cve_articulo = v.cve_articulo
            LEFT JOIN c_lotes on c_lotes.LOTE = v.cve_lote
            LEFT JOIN c_serie on c_serie.cve_articulo = c_articulo.cve_articulo
            LEFT JOIN V_ExistenciaFisica i on i.cve_articulo = v.cve_articulo and i.ID_Inventario = {$codigo}
          WHERE v.cve_ubicacion IN (select COALESCE(idy_ubica, cve_ubicacion) from t_ubicacioninventario where ID_Inventario = {$codigo}) AND v.Existencia > 0 AND v.tipo='ubicacion'
            {$and}
          GROUP BY v.cve_articulo, v.cve_ubicacion
        ";
      }
       //echo var_dump($sql);
      //die();
      $sth = \db()->prepare($sql);
      $sth->execute();
      $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
     
      return $result;
    }
    */

    function loadDetalleI($codigo) 
    {
      $sqlprevio='SET @row_number = 0;';
      $sql=
        "
          select
            i.ID_Inventario as ID_Inventario,
            i.Fecha as fecha,
            c_almacenp.nombre as almacen,
            c_almacen.des_almac as zona,
            c.NConteo as conteo, 
            IF(i.Status = 'A', 'Abierto', IF(i.Status = 'C', 'Cerrado', IF(i.Status = 'P', 'Planeado',IF(i.Status = 'E', 'En Curso','')))) as status
          from th_inventario i, t_conteoinventario c, c_almacenp, c_almacen
          where c.ID_Inventario=i.ID_Inventario
            and i.cve_almacen=c_almacenp.clave
            and i.cve_zona=c_almacen.cve_almac;
        ";
      // $sth = \db()->prepare( $sqlprevio );
      //$sth->execute();
      $sth = \db()->prepare( $sql );
      $sth->execute();
      //$this->data = $sth->fetch();
      return $sth->fetchAll();
    }
  
        public function ReporteTeoricos($id_inventario ,$status, $cia, $fecha, $tipo_inv) //, $diferencia, $rack
    {
      $diferencia = "";
      $rack = "";
    //set_time_limit(0);
    //ini_set('memory_limit', '-1');
    //error_reporting(E_ALL);
    //ini_set('display_errors', '1');

      $sql_rack = "";
      if($rack)
      $sql_rack = "AND ub.cve_rack = '{$rack}'";

    $sqlBody = "";

    if($tipo_inv == 'Físico')
      $sqlBody = "
SELECT  tinv.zona, tinv.cve_ubicacion, tinv.ubicacion, tinv.clave, tinv.descripcion, tinv.lote, tinv.caducidad, 
  tinv.conteo, 
  GROUP_CONCAT(DISTINCT tinv.NConteo_Cantidad_reg SEPARATOR ',') AS NConteo_Cantidad_reg, 
  GROUP_CONCAT(DISTINCT tinv.Nconteo SEPARATOR ',') AS Nconteo, 
  tinv.stockTeorico, 
  GROUP_CONCAT(DISTINCT tinv.Cantidad_reg SEPARATOR ',') AS Cantidad_reg, 
  tinv.Cantidad, tinv.Cerrar, tinv.LP, tinv.usuario, tinv.unidad_medida, tinv.Nombre_Empresa , tinv.Status
FROM (

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = 168 AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                p.Nombre as Nombre_Empresa,
                (SELECT Status FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezas inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #LEFT JOIN t_invtarima invt ON invt.ID_Inventario = inv.ID_Inventario AND invt.cve_articulo = c_articulo.cve_articulo AND invt.idy_ubica = ub.idy_ubica
                #LEFT JOIN c_charolas ch ON ch.IDContenedor = invt.ntarima
                #####
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario}             AND NConteo = 0)
            ) 
            AND inv.NConteo = 0 
            GROUP BY LP,clave,ubicacion,lote

UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.existencia) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #(SELECT ext.Teorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                (SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = ch.clave_contenedor), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.existencia ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                p.Nombre as Nombre_Empresa,
                (SELECT STATUS FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS STATUS
            FROM t_invtarima inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo)
            AND inv.NConteo = 0 
            GROUP BY LP,clave,ubicacion,lote


  UNION 

        SELECT DISTINCT 
                '' AS zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                '' AS clave,
                '' AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                '' AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                p.Nombre as Nombre_Empresa,
                (SELECT STATUS FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS STATUS
            FROM t_invpiezas inv
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = ''
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario}             AND NConteo = 0)
            ) 
            AND inv.NConteo = 0 
            GROUP BY LP,clave,ubicacion,lote

            ORDER BY ubicacion
            ) AS tinv #WHERE IFNULL(tinv.stockTeorico, 0) != 0
            GROUP BY clave, ubicacion, lote, LP
            ORDER BY descripcion
            ";
        else
          $sqlBody = "SELECT  tinv.zona, tinv.cve_ubicacion, tinv.ubicacion, tinv.clave, tinv.descripcion, tinv.lote, tinv.caducidad, 
  tinv.conteo, 
  GROUP_CONCAT(DISTINCT tinv.NConteo_Cantidad_reg SEPARATOR ',') AS NConteo_Cantidad_reg, 
  GROUP_CONCAT(DISTINCT tinv.Nconteo SEPARATOR ',') AS Nconteo, 
  tinv.stockTeorico, 
  GROUP_CONCAT(DISTINCT tinv.Cantidad_reg SEPARATOR ',') AS Cantidad_reg, 
  tinv.Cantidad, tinv.Cerrar, tinv.LP, tinv.usuario, tinv.unidad_medida, tinv.Nombre_Empresa , tinv.Status, tinv.ID_Proveedor
FROM (

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.ID_Proveedor = inv.Id_Proveedor AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                p.Nombre AS Nombre_Empresa,
                p.ID_Proveedor,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezasciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
            ) 
            AND inv.NConteo = 0 
            GROUP BY LP,clave,ubicacion,lote, ID_Proveedor

UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.existencia) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                (SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.ID_Proveedor = inv.Id_Proveedor AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.existencia ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(existencia) FROM t_invtarimaciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarimaciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                p.Nombre AS Nombre_Empresa,
                p.ID_Proveedor,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM t_invtarimaciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo)
            AND inv.NConteo = 0 
            GROUP BY LP,clave,ubicacion,lote, ID_Proveedor


  UNION 

        SELECT DISTINCT 
                '' AS zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                '' AS clave,
                '' AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                '' AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.ID_Proveedor = inv.Id_Proveedor AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                p.Nombre AS Nombre_Empresa,
                p.ID_Proveedor,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezasciclico inv
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = ''
            ) 
            AND inv.NConteo = 0 
            GROUP BY LP,clave,ubicacion,lote, ID_Proveedor

            ORDER BY ubicacion
            ) AS tinv 
            GROUP BY clave, ubicacion, lote, LP, ID_Proveedor
            ORDER BY descripcion";

            #GROUP BY clave,ubicacion, lote
#AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo = 0)) 

      
      $queryBody = \db()->prepare($sqlBody);
      $queryBody->execute();
      $dataBody = [];
      $resbody = "";
      if($queryBody)
      {
        $resbody = $queryBody->fetchAll(\PDO::FETCH_ASSOC);
      }
    //$cia = $_SESSION['cve_cia']; 

    $sql = 'SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = '.$cia;
    $query = \db()->prepare($sql);
    $query->execute();

    $reslogo = $query->fetchAll(\PDO::FETCH_ASSOC);

    foreach($reslogo as $rowlogo)
      $logo = $rowlogo['logo'];

      //echo $logo." - ";
/*
    if($cia != ''){
      $logo = str_replace('/img', 'img', $logo);
      //$url = "".$_SERVER['DOCUMENT_ROOT']."/";
      $url = "";

      //$query->free_result();
      //$db->close();
    }
*/

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $titulo = "REPORTE TEÓRICO DE INVENTARIO";

        $pdf = new \TCPDF(L, PDF_UNIT , LETTER, true, '$charset', false);
        $pdf->SetCreator('wms');
        $pdf->SetAuthor('wms');
        $pdf->SetTitle('Reporte de Inventario Teorico');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
       $pdf->SetMargins(30, 30, 30);
       $pdf->SetFooterMargin(30);
        $filename = "Inventario Teorico #{$folio}.pdf";
        ob_start();
?>
        <br><br>
          <table style="width: 980px;" border="0">
            <tr>
              <td style="width: 5px"></td>
              <td style="width: 850px">


                <table border="0">
                  <tr>
                    <td style="width: 200px;"><img src="<?php echo '../../..'.$logo; ?>" alt="" height="100"></td>
                    <td align="center" style="font-size: 14px;width: 650px; text-align: center; vertical-align: middle;"><br><br><br><br><b><?php echo $titulo; ?>
                    <br><br>
                    Inventario #<?php echo $id_inventario; ?></b>
                    </td>
                  </tr>
                </table>

                <br><br><br>

                <?php 
                $date=date_create($fecha);
                $fecha = date_format($date,"d/m/Y");
                ?>
                <table border="1">
                  <tr>
                    <td style="font-size: 15px;width: 100px;"> <b>FECHA</b></td>
                    <td style="font-size: 15px;width: 150px;" align="center"><?php echo $fecha; ?></td>
                  </tr>
                </table>

                <br><br><br>

                <table border="1">
                    <tr>
                      <td style="width: 80px;">  <b>Ubicación</b></td>
                      <td>  <b>Artículo</b></td>
                      <td style="width: 200px;">  <b>Descripción</b></td>
                      <td style="width: 110px;"> <b>LP</b></td>
                      <td>  <b>Lote</b></td>
                      <td>  <b>Teorico</b></td>
                      <td>  <b>Empresa</b></td>
<?php 
/*
?>
                      <td style="width: 50px;">  <b>Conteo 1</b></td>
                      <td style="width: 50px;">  <b>Conteo 2</b></td>
                      <td style="width: 50px;">  <b>Conteo 3</b></td>
                      <td style="width: 50px;">  <b>Conteo 4</b></td>
                      <td style="width: 50px;">  <b>Conteo 5</b></td>
                      <td>  <b>Valor Final</b></td>
                      <td>  <b>Ajuste</b></td>
<?php 
*/
?>
                    </tr>

                    <?php 
                    foreach($resbody as $row)
                    {
                      
                      if($diferencia == 1 && $row["Cerrar"] == 1) continue;

                      $cantidad_conteoN = explode(",", $row["Cantidad_reg"]);
                      $conteosN         = explode(",", $row["Nconteo"]);
                      $NConteo_Cantidad_reg = explode(",", $row["NConteo_Cantidad_reg"]);

                      $conteo = array("", "", "", "", "", "");//$conteo2 = 0;$conteo3 = 0;$conteo4 = 0;$conteo5 = 0;

                      $n_cantidades = count($cantidad_conteoN);
                      //$n_conteos    = count($conteosN);

                      $val_in_i = 1;
                      $n_conteos = $conteosN[count($conteosN)-1];
                      $n_conteos++;

                      //array_splice($NConteo_Cantidad_reg, 0, 1); 
                      //array_splice($conteosN, 0, 1); 
                      //if($NConteo_Cantidad_reg[0] != '0-0') {$val_in_i = 1; $n_conteos++;}
                      for($i = 1; $i < $n_conteos; $i++)
                      {
                          if($i < $n_conteos)
                          {
                            $conteo_cantidad = explode("-", $NConteo_Cantidad_reg[$i-$val_in_i]);
                            if($i == $conteo_cantidad[0])
                              $conteo[$i] = $conteo_cantidad[1];
                            else 
                              $conteo[$i] = '';
                            //$conteo[$i] = $cantidad_conteoN[$i];
                          }
                      }

                      if($n_cantidades < $n_conteos)
                         $conteo[$n_conteos-1] = $row["Cantidad"];

                       
                    ?>
                    <tr style="font-size: 12px;">
                      <td style="width: 80px;">  <?php echo utf8_encode($row["ubicacion"]); ?></td>
                      <td>  <?php echo utf8_encode($row["clave"]); ?></td>
                      <td style="width: 200px;">  <?php echo utf8_encode($row["descripcion"]); ?></td>
                      <td style="width: 110px;">  <?php echo utf8_encode($row["LP"]); ?></td>
                      <td>  <?php echo utf8_encode($row["lote"]); ?></td>
                      <td align="right"><?php echo utf8_encode($row["stockTeorico"])."&nbsp;&nbsp;"; ?></td>
                      <td> <?php echo utf8_encode($row["Nombre_Empresa"]); ?></td>
<?php 
/*
?>

                      <td align="right"  style="width: 50px;"><?php echo $conteo[1]."&nbsp;&nbsp;"; ?>  </td>
                      <td align="right"  style="width: 50px;"><?php echo $conteo[2]."&nbsp;&nbsp;"; ?>  </td>
                      <td align="right"  style="width: 50px;"><?php echo $conteo[3]."&nbsp;&nbsp;"; ?>  </td>
                      <td align="right"  style="width: 50px;"><?php echo $conteo[4]."&nbsp;&nbsp;"; ?>  </td>
                      <td align="right"  style="width: 50px;"><?php echo $conteo[5]."&nbsp;&nbsp;"; ?>  </td>
                      <td align="right" >
                        <?php 
                            $valor = false;
                            for($n = 1; $n < count($conteo); $n++)
                              if($conteo[$n] != "")
                                $valor = true;

                            if($valor)
                            {
                              $found = 0;
                              for($n = 1; $n < count($conteo); $n++)
                              {
                                  if($row["Cantidad"] == $conteo[$n])
                                     $found++;
                              }
                                if($found >= 2)
                                    echo $row["Cantidad"]."&nbsp;&nbsp;";
                                else 
                                    echo "&nbsp;&nbsp;"; 
                            }
                            else 
                                echo "&nbsp;&nbsp;"; 
                              
                        ?>  
                      </td>
                      <td align="right" ><?php if($found >= 2)echo ($row["Cantidad"]-$row["stockTeorico"])."&nbsp;&nbsp;"; ?>  </td>
<?php 
*/
?>

                    </tr>
                    <?php 
                    }
                    ?>
                </table>

              </td>
              <td style="width: 65px"></td>
            </tr>
          </table>

          <br><br><br><br><br>
                <table border='0'>
                  <tr>
                    <td align="center">_________________________________<br>Responsable</td>
                    <td align="center">_________________________________<br>Firma</td>
                  </tr>
                </table>

<?php 
        $contentBody = ob_get_clean();
        //ob_flush();
            //$desProducto = ob_get_clean(); 
          $pdf->AddPage();
          $pdf->SetAutoPageBreak(TRUE, 5);
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
          $pdf->setMargins(0, 5, 0, 0);
          $pdf->SetXY(5, 5);
          $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
          $pdf->WriteHTML($contentBody, true, false, true, '');
          ob_end_clean();
      //}

      $pdf->Output($filename, 'I');
/*
      $content = $contentHeader . $contentBody;
      echo $content;
      $pdf = new \ReportePDF\PDF($_SESSION['cve_cia'], "Formato de Llenado para Inventario #{$folio}", "L");
      $pdf->setContent($content);
      $pdf->stream();
*/
    }

      public function ReporteConsolidadoFisico($id_inventario ,$status, $cia, $fecha, $diferencia, $rack, $tipo)
    {
    //set_time_limit(0);
    //ini_set('memory_limit', '-1');
    //error_reporting(E_ALL);
    //ini_set('display_errors', '1');

      $sql_rack = "";
      if($rack)
      $sql_rack = "AND ub.cve_rack = '{$rack}'";

      $sqlBody = "";

    if($tipo == 'Físico')
    {

$sql_inicial = "";
/*
    $sql = "SELECT IFNULL(Inv_Inicial, 0) as Inv_Inicial FROM th_inventario WHERE ID_Inventario = {$id_inventario}";
    $query = \db()->prepare($sql);
    $query->execute();
    $resinv = $query->fetchAll(\PDO::FETCH_ASSOC);

    $tipo_inicial = 1;
    foreach($resinv as $rowinv)
      $tipo_inicial = $rowinv['Inv_Inicial'];

    $sql_inicial = "";
    if($tipo_inicial == 0)
      $sql_inicial = " WHERE RIGHT(tinv.Nconteo, 1) != '1' AND tinv.TeoricoPiezas != 0 ";
*/
      $sqlBody = "
SELECT  tinv.zona, tinv.cve_ubicacion, tinv.ubicacion, tinv.clave, tinv.descripcion, tinv.lote, tinv.caducidad, 
  tinv.conteo, 
  GROUP_CONCAT(DISTINCT tinv.NConteo_Cantidad_reg SEPARATOR ',') AS NConteo_Cantidad_reg, 
  GROUP_CONCAT(DISTINCT tinv.Nconteo SEPARATOR ',') AS Nconteo, 
  tinv.stockTeorico, 
  GROUP_CONCAT(DISTINCT tinv.Cantidad_reg SEPARATOR ',') AS Cantidad_reg, 
  tinv.Cantidad, tinv.Cerrar, tinv.LP, tinv.Nombre_Empresa, tinv.usuario, tinv.unidad_medida, tinv.Max_Conteo AS Max_Conteo, tinv.Status 
FROM (

        SELECT DISTINCT 
                c.des_almac zona,
                v.cve_ubicacion, 
                (CASE 
                    WHEN v.tipo = 'area' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion) 
                    WHEN v.tipo = 'ubicacion' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)
                    ELSE '--'
                END) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(v.cve_lote = '', '', v.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = v.cve_articulo AND ext.idy_ubica = v.cve_ubicacion AND ext.cve_lote = v.cve_lote AND v.tipo = 'ubicacion'), 0) AS stockTeorico,
                IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = v.cve_ubicacion AND cve_lote = v.cve_lote AND cve_articulo = v.cve_articulo), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion  AND iv.NConteo = MAX(inv.NConteo) AND iv.cve_lote = v.cve_lote) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo > 0 ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                inv.ExistenciaTeorica AS TeoricoPiezas,
                (SELECT Status FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS Status
            FROM V_ExistenciaGralProduccion v
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                LEFT JOIN t_invpiezas inv ON inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote 
                LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_Inventario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE v.Existencia > 0 AND 
            v.tipo='ubicacion' AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_articulo = v.cve_articulo AND inv.cve_lote = v.cve_lote 
            #AND inv.NConteo > 0 
            {$sql_rack} 
            AND inv.Cantidad >= 0
            AND CONCAT(v.cve_articulo,v.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invtarima WHERE ID_Inventario = {$id_inventario})
            GROUP BY LP,clave,cve_ubicacion,lote

        UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = 168 AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                inv.ExistenciaTeorica AS TeoricoPiezas,
                (SELECT Status FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezas inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_Inventario
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #LEFT JOIN t_invtarima invt ON invt.ID_Inventario = inv.ID_Inventario AND invt.cve_articulo = c_articulo.cve_articulo AND invt.idy_ubica = ub.idy_ubica
                #LEFT JOIN c_charolas ch ON ch.IDContenedor = invt.ntarima
                #####
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario}             AND NConteo = 0)
            ) 
            #AND inv.NConteo > 0 
            AND inv.Cantidad >= 0
            {$sql_rack}
            GROUP BY LP,clave,cve_ubicacion,lote

UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.existencia) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #(SELECT ext.Teorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = ch.clave_contenedor), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.existencia ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                IFNULL(ch.CveLP, '') AS LP,
                p.Nombre as Nombre_Empresa,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                '1' AS TeoricoPiezas,
                (SELECT STATUS FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS STATUS
            FROM t_invtarima inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_Inventario
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo)
            #AND inv.NConteo > 0 
            AND inv.existencia >= 0
            {$sql_rack}
            GROUP BY LP,clave,cve_ubicacion,lote

  UNION 

        SELECT DISTINCT 
                '' AS zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                '' AS clave,
                '' AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                '' AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                inv.ExistenciaTeorica AS TeoricoPiezas,
                (SELECT STATUS FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS STATUS
            FROM t_invpiezas inv
            LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_Inventario
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = ''
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario}             AND NConteo = 0)
            ) 
            #AND inv.NConteo > 0 
            AND inv.Cantidad >= 0
            {$sql_rack}
            GROUP BY LP,clave,ubicacion,lote

            ORDER BY ubicacion
            ) AS tinv 
            {$sql_inicial} 
            GROUP BY clave, cve_ubicacion, lote, LP
            ORDER BY ubicacion
            ";
    }
    else 
      $sqlBody = "
SELECT  tinv.zona, tinv.cve_ubicacion, tinv.ubicacion, tinv.clave, tinv.descripcion, tinv.lote, tinv.caducidad, 
  tinv.conteo, 
  GROUP_CONCAT(DISTINCT tinv.NConteo_Cantidad_reg SEPARATOR ',') AS NConteo_Cantidad_reg, 
  GROUP_CONCAT(DISTINCT tinv.Nconteo SEPARATOR ',') AS Nconteo, 
  tinv.stockTeorico, 
  GROUP_CONCAT(DISTINCT tinv.Cantidad_reg SEPARATOR ',') AS Cantidad_reg, 
  tinv.Cantidad, tinv.Cerrar, tinv.LP, tinv.Nombre_Empresa, tinv.usuario, tinv.unidad_medida, tinv.Max_Conteo AS Max_Conteo, tinv.Status 
FROM (

        SELECT DISTINCT 
                c.des_almac zona,
                v.cve_ubicacion, 
                (CASE 
                    WHEN v.tipo = 'area' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion) 
                    WHEN v.tipo = 'ubicacion' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)
                    ELSE '--'
                END) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(v.cve_lote = '', '', v.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = v.cve_ubicacion AND cve_lote = v.cve_lote AND cve_articulo = v.cve_articulo), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion  AND iv.NConteo = MAX(inv.NConteo) AND iv.cve_lote = v.cve_lote) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo > 0 ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM V_ExistenciaGralProduccion v
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                LEFT JOIN t_invpiezasciclico inv ON inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote 
                LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_PLAN
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE v.Existencia > 0 AND 
            v.tipo='ubicacion' AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_articulo = v.cve_articulo AND inv.cve_lote = v.cve_lote 
            {$sql_rack} 
            AND CONCAT(v.cve_articulo,v.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario})
            AND inv.Cantidad >= 0 
            GROUP BY LP,clave,cve_ubicacion,lote

        UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                TRUNCATE(IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0), 3) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.cve_lote = inv.cve_lote AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezasciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_PLAN
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
            ) 
            AND inv.Cantidad >= 0 
            {$sql_rack}
            GROUP BY LP,clave,cve_ubicacion,lote

UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.existencia) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.existencia ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(existencia) FROM t_invtarimaciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarimaciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                IFNULL(ch.CveLP, '') AS LP,
                p.Nombre as Nombre_Empresa,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM t_invtarimaciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_PLAN
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo)
            AND inv.existencia >= 0 
            {$sql_rack}
            GROUP BY LP,clave,cve_ubicacion,lote

  UNION 

        SELECT DISTINCT 
                '' AS zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                '' AS clave,
                '' AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                '' AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezasciclico inv
            LEFT JOIN t_conteoinventario cinv ON cinv.ID_Inventario = inv.ID_PLAN
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = ''
            ) AND inv.Cantidad >= 0 
            {$sql_rack}
            GROUP BY LP,clave,ubicacion,lote

            ORDER BY ubicacion
            ) AS tinv
            GROUP BY clave, cve_ubicacion, lote, LP
            ORDER BY ubicacion
            ";


      
      $queryBody = \db()->prepare($sqlBody);
      $queryBody->execute();
      $dataBody = [];
      $resbody = "";
      if($queryBody)
      {
        $resbody = $queryBody->fetchAll(\PDO::FETCH_ASSOC);
      }
    //$cia = $_SESSION['cve_cia']; 

    $sql = 'SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = '.$cia;
    $query = \db()->prepare($sql);
    $query->execute();

    $reslogo = $query->fetchAll(\PDO::FETCH_ASSOC);

    foreach($reslogo as $rowlogo)
      $logo = $rowlogo['logo'];

      //echo $logo." - ";
/*
    if($cia != ''){
      $logo = str_replace('/img', 'img', $logo);
      //$url = "".$_SERVER['DOCUMENT_ROOT']."/";
      $url = "";

      //$query->free_result();
      //$db->close();
    }
*/
        $titulo = "REPORTE DE INVENTARIO CONSOLIDADO DE CONTEOS";
        if($diferencia == 1)
          $titulo = "REPORTE DE UBICACIONES CON DIFERENCIA";

        $pdf = new \TCPDF(L, PDF_UNIT , LETTER, true, 'UTF-8', false);
        $pdf->SetCreator('wms');
        $pdf->SetAuthor('wms');
        $pdf->SetTitle('Reporte de Inventario');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
       $pdf->SetMargins(30, 30, 30);
       $pdf->SetFooterMargin(30);
        $filename = "Inventario Consolidado #{$folio}.pdf";
        ob_start();
?>
        <br><br>
          <table style="width: 980px;" border="0">
            <tr>
              <td style="width: 5px"></td>
              <td style="width: 850px">


                <table border="0">
                  <tr>
                    <td style="width: 200px;"><img src="<?php echo '../../..'.$logo; ?>" alt="" height="100"></td>
                    <td align="center" style="font-size: 14px;width: 650px; text-align: center; vertical-align: middle;"><br><br><br><br><b><?php echo $titulo; ?>
                    <br><br>
                    Inventario #<?php echo $id_inventario; ?></b>
                    </td>
                  </tr>
                </table>

                <br><br><br>

                <?php 
                $date=date_create($fecha);
                $fecha = date_format($date,"d/m/Y");
                ?>
                <table border="1">
                  <tr>
                    <td style="font-size: 15px;width: 100px;"> <b>FECHA</b></td>
                    <td style="font-size: 15px;width: 150px;" align="center"><?php echo $fecha; ?></td>
                  </tr>
                </table>

                <br><br><br>

                <table border="1">
                    <tr>
                      <td style="width: 80px;">  <b>Ubicación</b></td>
                      <td>  <b>Artículo</b></td>
                      <td style="width: 200px;">  <b>Descripción</b></td>
                      <td style="width: 110px;"> <b>LP</b></td>
                      <td>  <b>Lote</b></td>
                      <td>  <b>Teorico</b></td>
                      <td style="width: 50px;">  <b>Conteo 1</b></td>
                      <td style="width: 50px;">  <b>Conteo 2</b></td>
                      <td style="width: 50px;">  <b>Conteo 3</b></td>
                      <td style="width: 50px;">  <b>Conteo 4</b></td>
                      <td style="width: 50px;">  <b>Conteo 5</b></td>
                      <td>  <b>Valor Final</b></td>
                      <td>  <b>Ajuste</b></td>
                      <?php /* ?><td>  <b>Empresa</b></td><?php */ ?>
                    </tr>

                    <?php 
                    foreach($resbody as $row)
                    {
                      // || ($row["Max_Conteo"] == 1 && $row["stockTeorico"] == 0)
                    if(($diferencia == 1 && $row["Cerrar"] == 1))  continue;

                      $cantidad_conteoN = explode(",", $row["Cantidad_reg"]);
                      $conteosN         = explode(",", $row["Nconteo"]);
                      $NConteo_Cantidad_reg = explode(",", $row["NConteo_Cantidad_reg"]);

                      $conteo = array("BB", "BB", "BB", "BB", "BB", "BB");//$conteo2 = 0;$conteo3 = 0;$conteo4 = 0;$conteo5 = 0;

                      $n_cantidades = count($cantidad_conteoN);
                      //$n_conteos    = count($conteosN);

                      $val_in_i = 1;
                      //$n_conteos = $conteosN[count($conteosN)-1];
                      //$n_conteos = count($conteosN)-1;
                      //$n_conteos++;
                      $n_conteos = $row["conteo"];
                      //$n_conteos++;

                      //if($NConteo_Cantidad_reg[0] != '0-0') {$val_in_i = 1; $n_conteos++;}
                      if($NConteo_Cantidad_reg[0] == '0-0')
                      {
                      array_splice($NConteo_Cantidad_reg, 0, 1); 
                      array_splice($conteosN, 0, 1); 
                      }
                      for($i = 1; $i <= count($NConteo_Cantidad_reg); $i++)
                      {
                          //if($i < $n_conteos)
                          {
                            $conteo_cantidad = explode("-", $NConteo_Cantidad_reg[$i-$val_in_i]);
                            //if($i == $conteo_cantidad[0] && $conteo_cantidad)
                            //{
                              //$conteo[$i] = $conteo_cantidad[1];
                              $conteo[$conteo_cantidad[0]] = $conteo_cantidad[1];
                            //}
                            //else 
                            //  $conteo[$i] = '0';
                          }
                      }

                      //if($n_cantidades < $n_conteos)
                      //   $conteo[$n_conteos] = $row["Cantidad"];

                       if($diferencia == 1 && ($row["Cantidad"]-$row["stockTeorico"]) == 0) continue;
                    ?>
                    <tr style="font-size: 12px;">
                      <td style="width: 80px;">  <?php echo utf8_encode($row["ubicacion"]); ?></td>
                      <td>  <?php echo utf8_encode($row["clave"]); ?></td>
                      <td style="width: 200px;">  <?php echo utf8_encode($row["descripcion"]); ?></td>
                      <td style="width: 110px;">  <?php echo utf8_encode($row["LP"]); ?></td>
                      <td>  <?php echo utf8_encode($row["lote"]); ?></td>
                      <td align="right"><?php echo $row["stockTeorico"]."&nbsp;&nbsp;"; ?></td>
                      <td align="right"  style="width: 50px;"><?php if($conteo[1] >= 0) {if($conteo[1] == 'BB') echo ''; else echo $conteo[1]."&nbsp;&nbsp;";}//else if(1 <= $row['Max_Conteo']) echo "0"."&nbsp;&nbsp;"; ?>  </td>
                      <td align="right"  style="width: 50px;"><?php if($conteo[2] >= 0) {if($conteo[2] == 'BB') echo ''; else echo $conteo[2]."&nbsp;&nbsp;";}//else if(2 <= $row['Max_Conteo']) echo "0"."&nbsp;&nbsp;"; ?>  </td>
                      <td align="right"  style="width: 50px;"><?php if($conteo[3] >= 0) {if($conteo[3] == 'BB') echo ''; else echo $conteo[3]."&nbsp;&nbsp;";}//else if(3 <= $row['Max_Conteo']) echo "0"."&nbsp;&nbsp;"; ?>  </td>
                      <td align="right"  style="width: 50px;"><?php if($conteo[4] >= 0) {if($conteo[4] == 'BB') echo ''; else echo $conteo[4]."&nbsp;&nbsp;";}//else if(4 <= $row['Max_Conteo']) echo "0"."&nbsp;&nbsp;"; ?>  </td>
                      <td align="right"  style="width: 50px;"><?php if($conteo[5] >= 0) {if($conteo[5] == 'BB') echo ''; else echo $conteo[5]."&nbsp;&nbsp;";}//else if(5 <= $row['Max_Conteo']) echo "0"."&nbsp;&nbsp;"; ?>  </td>
                      <td align="right" >
                        <?php 
                            $valor = false;
                            $found = 0;
                            for($n = 1; $n < count($conteo); $n++)
                              if($conteo[$n] != "")
                                $valor = true;

                            if($valor)
                            {
                              $found = 0;

                              for($n = 1; $n < count($conteo); $n++)
                              {
                                  if($row["Cantidad"] == $conteo[$n])// || $row["stockTeorico"] == $conteo[$n]
                                     $found++;
                              }

                                if($found >= 2)
                                    echo $row["Cantidad"]."&nbsp;&nbsp;";
                                else 
                                    echo "&nbsp;&nbsp;"; 
                            }
                            else 
                                echo "&nbsp;&nbsp;"; 
                              
                        ?>  
                      </td>
                      <td align="right" ><?php if($found >= 2) echo ($row["Cantidad"]-$row["stockTeorico"])."&nbsp;&nbsp;"; ?>  </td>
                      <?php /* ?><td style="width: 110px;">  <?php echo utf8_encode($row["Nombre_Empresa"]); ?></td><?php */ ?>
                    </tr>
                    <?php 
                    }
                    ?>
                </table>

              </td>
              <td style="width: 65px"></td>
            </tr>
          </table>
          <br><br><br><br><br>
                <table border='0'>
                  <tr>
                    <td align="center">_________________________________<br>Responsable</td>
                    <td align="center">_________________________________<br>Firma</td>
                  </tr>
                </table>

<?php 
        $contentBody = ob_get_clean();
        //ob_flush();
            //$desProducto = ob_get_clean(); 
          $pdf->AddPage();
          $pdf->SetAutoPageBreak(TRUE, 5);
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
          $pdf->setMargins(0, 5, 0, 0);
          $pdf->SetXY(5, 5);
          $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
          $pdf->WriteHTML($contentBody, true, false, true, '');
          ob_end_clean();
      //}

      $pdf->Output($filename, 'I');
/*
      $content = $contentHeader . $contentBody;
      echo $content;
      $pdf = new \ReportePDF\PDF($_SESSION['cve_cia'], "Formato de Llenado para Inventario #{$folio}", "L");
      $pdf->setContent($content);
      $pdf->stream();
*/
    }

    public function ReporteIndividualFisico($id_inventario ,$status, $cia, $usuario, $conteo, $fecha, $ubicacion, $codigo_csd, $codigo_rack, $tipo)
    {
      $sql_usuario = "";
      if($usuario) $sql_usuario = "AND inv.cve_usuario = '{$usuario}'";

      $sql_rack = "";
      if($codigo_rack) $sql_rack = "AND ub.cve_rack = '{$codigo_rack}'";

      $sql_ubicacion = "AND inv.idy_ubica = v.cve_ubicacion";
      if($ubicacion) $sql_ubicacion = "AND inv.idy_ubica = '{$ubicacion}'";

      $sql_ubicacion2 = "";
      if($ubicacion) $sql_ubicacion2 = "AND inv.idy_ubica = '{$ubicacion}'";

      $sqlBody = "";

      if($tipo == 'Físico')
      {
      $sqlBody = "
        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica ), 0, 1) = 1, (SELECT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.Cantidad AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            '' AS LP,
            p.Nombre as Nombre_Empresa,
            u.nombre_completo AS usuario
            FROM t_invpiezas inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo = {$conteo})) {$sql_rack} 
            AND inv.Cantidad >= 0 
            GROUP BY LP,clave,ubicacion,lote

  UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica ), 0, 1) = 1, (SELECT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.Cantidad AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            '' AS LP,
            p.Nombre as Nombre_Empresa,
            u.nombre_completo AS usuario
            FROM t_invpiezas inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} 
            AND inv.NConteo = 0
            AND (CONCAT(inv.idy_ubica, inv.cve_articulo, inv.cve_lote) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo > 0))
            ) {$sql_rack}
            AND inv.Cantidad >= 0 
            GROUP BY LP,clave,idy_ubica,lote

        UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            #(SELECT ext.Teorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
            IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = ch.CveLP), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica), 0, 1) = 1, (SELECT SUM(existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.existencia AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            IFNULL(ch.CveLP, '') AS LP,
            p.Nombre as Nombre_Empresa,
            u.nombre_completo AS usuario
            FROM t_invtarima inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} ) {$sql_rack}
            AND inv.existencia >= 0 
            GROUP BY LP,clave,ubicacion,lote

UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            #(SELECT ext.Teorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
            IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = ch.CveLP), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica), 0, 1) = 1, (SELECT SUM(existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.existencia AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            IFNULL(ch.CveLP, '') AS LP,
            p.Nombre as Nombre_Empresa,
            u.nombre_completo AS usuario
            FROM t_invtarima inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} 
            AND inv.NConteo = 0
            AND (CONCAT(inv.idy_ubica, inv.cve_articulo, inv.cve_lote, inv.ntarima) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, cve_lote, ntarima) FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo > 0))
            #AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} 
            ) 
            {$sql_rack}
            AND inv.existencia >= 0 
            GROUP BY LP,clave,ubicacion,lote
            ORDER BY descripcion
";
    }
    else
    {
            $sqlBody = "
        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
            inv.Cantidad AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            '' AS LP,
            p.Nombre as Nombre_Empresa,
            u.nombre_completo AS usuario
            FROM t_invpiezasciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario} AND NConteo = {$conteo})) {$sql_rack}
            AND inv.Cantidad >= 0 
            GROUP BY LP,clave,ubicacion,lote

  UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
            inv.Cantidad AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            '' AS LP,
            p.Nombre as Nombre_Empresa,
            u.nombre_completo AS usuario
            FROM t_invpiezasciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} 
            AND inv.NConteo = 0
            AND (CONCAT(inv.idy_ubica, inv.cve_articulo, inv.cve_lote) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, cve_lote) FROM t_invpiezasciclico WHERE ID_PLAN = {$id_inventario} AND NConteo > 0))
            ) {$sql_rack}
            AND inv.Cantidad >= 0 
            GROUP BY LP,clave,idy_ubica,lote

        UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
            inv.existencia AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            IFNULL(ch.CveLP, '') AS LP,
            p.Nombre as Nombre_Empresa,
            u.nombre_completo AS usuario
            FROM t_invtarimaciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} ) {$sql_rack}
            AND inv.existencia >= 0 
            GROUP BY LP,clave,ubicacion,lote

UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
            inv.existencia AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            IFNULL(ch.CveLP, '') AS LP,
            p.Nombre as Nombre_Empresa,
            u.nombre_completo AS usuario
            FROM t_invtarimaciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} 
            AND inv.NConteo = 0
            AND (CONCAT(inv.idy_ubica, inv.cve_articulo, inv.cve_lote, inv.ntarima) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, cve_lote, ntarima) FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario} AND NConteo > 0))
            ) 
            {$sql_rack}
            AND inv.existencia >= 0 
            GROUP BY LP,clave,ubicacion,lote
            ORDER BY descripcion
";
      }

#AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invtarimaciclico WHERE ID_Inventario = {$id_inventario} AND NConteo = 0)

      $queryBody = \db()->prepare($sqlBody);
      $queryBody->execute();
      $dataBody = [];
      $resbody = "";
      if($queryBody)
      {
        $resbody = $queryBody->fetchAll(\PDO::FETCH_ASSOC);
      }
    //$cia = $_SESSION['cve_cia']; 

    $sql = 'SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = '.$cia;
    $query = \db()->prepare($sql);
    $query->execute();

    $reslogo = $query->fetchAll(\PDO::FETCH_ASSOC);

    foreach($reslogo as $rowlogo)
      $logo = $rowlogo['logo'];

//*************************************************************************************************

    $usuarios = "";
    if($usuario)
    {
      $sql = "SELECT DISTINCT cve_usuario, nombre_completo FROM c_usuario WHERE cve_usuario = '$usuario'";
      $query = \db()->prepare($sql);
      $query->execute();
      $resusuario = $query->fetchAll(\PDO::FETCH_ASSOC);

      foreach($resusuario as $rowusuario)
        $usuarios = "(".$rowusuario['cve_usuario'].") - ".$rowusuario['nombre_completo'];
    
    }
    else 
    {
      $sql = "SELECT DISTINCT c.cve_usuario, c.nombre_completo FROM c_usuario c WHERE c.cve_usuario IN (
              SELECT cve_usuario FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo = {$conteo}
              UNION
              SELECT cve_usuario FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo = {$conteo}
              )
              ";
      $query = \db()->prepare($sql);
      $query->execute();
      $resusuario = $query->fetchAll(\PDO::FETCH_ASSOC);
      foreach($resusuario as $rowusuario)
        $usuarios .= "(".$rowusuario['cve_usuario'].") - ".$rowusuario['nombre_completo']."<br>";
    }
//*************************************************************************************************
      //echo $logo." - ";
/*
    if($cia != ''){
      $logo = str_replace('/img', 'img', $logo);
      //$url = "".$_SERVER['DOCUMENT_ROOT']."/";
      $url = "";

      //$query->free_result();
      //$db->close();
    }
*/
        $pdf = new \TCPDF(L, PDF_UNIT , LETTER, true, 'UTF-8', false);
        $pdf->SetCreator('wms');
        $pdf->SetAuthor('wms');
        $pdf->SetTitle('Reporte de Inventario');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
       $pdf->SetMargins(30, 30, 30);
       $pdf->SetFooterMargin(30);
        $filename = "Inventario Individual #{$folio}.pdf";
        ob_start();
?>
        <br><br>
          <table style="width: 980px;" border="0">
            <tr>
              <td style="width: 65px"></td>
              <td style="width: 850px">


                <table border="0">
                  <tr>
                    <td style="width: 200px;"><img src="<?php echo '../../..'.$logo; ?>" alt="" height="100"></td>
                    <td style="font-size: 14px;width: 650px; vertical-align: middle;"><br><br><br><br><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;REPORTE DE INVENTARIO POR CONTEO</b>
                      <br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Inventario # <?php echo $id_inventario; ?>
                    </td>
                  </tr>
                  <tr align="center" style="font-size: 14px;"><td style="width: 400px;"><br><br><b>Conteo <?php echo $conteo; ?></b></td>
                    <td align="left" style="width: 500px;"><br><b>Usuario(s): <br><?php echo $usuarios; ?></b></td></tr>
                  
                  <?php 
                  if($ubicacion)
                  {
                  ?>
                  <tr colspan="2" align="center" style="font-size: 14px;"><br><br><b>Ubicación <?php echo $codigo_csd; ?></b></tr>
                  <?php  
                  }
                  ?>
                </table>

                <br><br><br>

                <?php 
                $date=date_create($fecha);
                $fecha = date_format($date,"d/m/Y");
                ?>
                <table border="1">
                  <tr>
                    <td style="font-size: 15px;width: 100px;"> <b>FECHA</b></td>
                    <td style="font-size: 15px;width: 150px;" align="center"><?php echo $fecha; ?></td>
                  </tr>
                </table>

                <br><br><br>

                <table border="1">
                  <thead>
                    <tr>
                      <?php 
                      if(!$ubicacion)
                      {
                      ?>
                      <th  style="width: 100px;">  <b>Ubicación</b></th>
                      <?php 
                      }
                      ?>
                      <th>  <b>Artículo</b></th>
                      <th style="width: 200px;">  <b>Descripción</b></th>
                      <th  style="width: 110px;">  <b>LP</b></th>
                      <th>  <b>Lote</b></th>
                      <th style="width: 50px;">  <b>Teorico</b></th>
                      <th>  <b>Inventariado</b></th>
                      <th style="width: 60px;">  <b>Diferencia</b></th>
                      <th style="width: 100px;">  <b>Empresa</b></th>
                      <?php  
                      /*
                      if(!$usuario)
                      {
                      ?>
                      <th style="width: 100px;">  <b>Usuario</b></th>
                      <?php 
                      }
                      */
                      ?>
                    </tr>
                    </thead>
                    <tbody>

                    <?php 
                    foreach($resbody as $row)
                    {

                    ?>
                    <tr style="font-size: 12px;">
                      <?php 
                      if(!$ubicacion)
                      {
                      ?>
                      <td style="width: 100px;">  <?php echo $row["ubicacion"]; ?></td>
                      <?php 
                      }
                      ?>
                      <td>  <?php echo $row["clave"]; ?></td>
                      <td style="width: 200px;">  <?php echo $row["descripcion"]; ?></td>
                      <td style="width: 110px;">  <?php echo $row["LP"]; ?></td>
                      <td>  <?php echo $row["lote"]; ?></td>
                      <td style="width: 50px;" align="right"><?php echo $row["stockTeorico"]."&nbsp;&nbsp;"; ?></td>
                      <td align="right">  <?php echo $row["Inventariado"]."&nbsp;&nbsp;"; ?></td>
                      <td style="width: 60px;" align="right" ><?php echo ($row["Inventariado"]-$row["stockTeorico"])."&nbsp;&nbsp;"; ?></td>
                      <td style="width: 100px;">  <?php echo $row["Nombre_Empresa"]; ?></td>
                      <?php  
                      /*
                      if(!$usuario)
                      {
                      ?>
                      <td style="width: 100px;">  <?php echo $row["usuario"]; ?></td>
                      <?php 
                      }
                      */
                      ?>
                    </tr>
                    <?php 
                    }
                    ?>

                    </tbody>
                </table>
                <br><br><br>

                <table border='0'>
                  <tr>
                    <td align="center">_________________________________<br>Responsable</td>
                    <td align="center">_________________________________<br>Firma</td>
                      <?php  
                      if($usuario)
                      {
                      ?>
                    <td align="center">_________________________________<br><?php echo $row['usuario']; ?></td>
                      <?php 
                      }
                      ?>
                  </tr>
                </table>

              </td>
              <td style="width: 65px"></td>
            </tr>
          </table>

<?php 
        $contentBody = ob_get_clean();
        //ob_flush();
            //$desProducto = ob_get_clean(); 
          $pdf->AddPage();
          $pdf->SetAutoPageBreak(TRUE, 5);
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
          $pdf->setMargins(0, 5, 0, 0);
          $pdf->SetXY(5, 5);
          $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
          $pdf->WriteHTML($contentBody, true, false, true, '');
          ob_end_clean();
      //}

      $pdf->Output($filename, 'I');
/*
      $content = $contentHeader . $contentBody;
      echo $content;
      $pdf = new \ReportePDF\PDF($_SESSION['cve_cia'], "Formato de Llenado para Inventario #{$folio}", "L");
      $pdf->setContent($content);
      $pdf->stream();
*/
    }

    public function ReporteConsolidadoItemFisico($id_inventario ,$status, $cia, $fecha, $tipo)
    {
/*
      $sqlBody = "
      SELECT clave, descripcion, SUM(Existencia_Total) AS Existencia_Total FROM (
      SELECT DISTINCT 
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(v.cve_lote = '', '', v.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                SUM(DISTINCT inv.Cantidad) AS Existencia_Total
            FROM V_ExistenciaGralProduccion v
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                LEFT JOIN t_invpiezas inv ON inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.NConteo = (SELECT MAX(iv.NConteo) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND inv.idy_ubica = iv.idy_ubica AND inv.cve_lote = iv.cve_lote )
                LEFT JOIN ts_existenciapiezas ex ON ex.cve_articulo = inv.cve_articulo AND inv.cve_lote = ex.cve_lote
            WHERE v.Existencia > 0 AND v.tipo='ubicacion' AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_articulo = v.cve_articulo AND inv.cve_lote = v.cve_lote #and inv.NConteo > 0
            GROUP BY clave

  UNION

        SELECT DISTINCT 
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                SUM(DISTINCT inv.Cantidad) AS Existencia_Total
            FROM t_invpiezas inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo = 0)) 
            GROUP BY clave,lote
      ORDER BY clave
    ) AS t GROUP BY clave

        ";
*/
      $sqlBody = "";

      if($tipo == 'Físico')
      $sqlBody = "
        SELECT clave, descripcion, SUM(Cantidad) AS Existencia_Total, Cerrar FROM (
                SELECT  
                        v.cve_ubicacion AS ubicacion,
                        IFNULL(c_articulo.cve_articulo, '--') AS clave,
                        IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                        IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo > 0 ), 0, 1) AS Cerrar,
                        (SELECT SUM(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote) AS Cantidad
                    FROM V_ExistenciaGralProduccion v
                        LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                        LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                        LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                        LEFT JOIN t_invpiezas inv ON inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote 
                    WHERE v.Existencia > 0 AND v.tipo='ubicacion' AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_articulo = v.cve_articulo 
                    #AND inv.cve_lote = v.cve_lote #and inv.NConteo > 0
                    AND inv.NConteo > 0
                    GROUP BY clave, ubicacion

          UNION

                SELECT  
                        inv.idy_ubica AS ubicacion,
                        IFNULL(c_articulo.cve_articulo, '--') AS clave,
                        IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                        IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                        (SELECT SUM(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote) AS Cantidad
                    FROM t_invpiezas inv
                        LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                        LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                        LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                        LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                    WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
                    #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo = 0)
                    ) 
                    AND inv.NConteo > 0
                    GROUP BY clave

          UNION

                SELECT  
            inv.idy_ubica AS ubicacion,
                        IFNULL(c_articulo.cve_articulo, '--') AS clave,
                        IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                        IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                        (SELECT SUM(DISTINCT existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote) AS Cantidad
                    FROM t_invtarima inv
                        LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                        LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                        LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                        LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                    WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
                    #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo = 0)
                    ) 
                    AND inv.NConteo > 0
                    GROUP BY clave
          ORDER BY clave

         ) AS t WHERE Cerrar = 1 GROUP BY clave
         ORDER BY descripcion
      ";
    else
      $sqlBody = "
        SELECT clave, descripcion, SUM(Cantidad) AS Existencia_Total, Cerrar FROM (
                SELECT  
                        v.cve_ubicacion AS ubicacion,
                        IFNULL(c_articulo.cve_articulo, '--') AS clave,
                        IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                        IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo > 0 ), 0, 1) AS Cerrar,
                        (SELECT SUM(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote) AS Cantidad
                    FROM V_ExistenciaGralProduccion v
                        LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                        LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                        LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                        LEFT JOIN t_invpiezasciclico inv ON inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote 
                    WHERE v.Existencia > 0 AND v.tipo='ubicacion' AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_articulo = v.cve_articulo 
                    #AND inv.cve_lote = v.cve_lote #and inv.NConteo > 0
                    AND inv.NConteo > 0
                    GROUP BY clave, ubicacion

          UNION

                SELECT  
                        inv.idy_ubica AS ubicacion,
                        IFNULL(c_articulo.cve_articulo, '--') AS clave,
                        IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                        IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                        (SELECT SUM(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote) AS Cantidad
                    FROM t_invpiezasciclico inv
                        LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                        LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                        LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                        LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                    WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
                    #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezasciclico WHERE ID_PLAN = {$id_inventario} AND NConteo = 0)
                    ) 
                    AND inv.NConteo > 0
                    GROUP BY clave

          UNION

                SELECT  
            inv.idy_ubica AS ubicacion,
                        IFNULL(c_articulo.cve_articulo, '--') AS clave,
                        IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                        IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                        (SELECT SUM(DISTINCT existencia) FROM t_invtarimaciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote) AS Cantidad
                    FROM t_invtarimaciclico inv
                        LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                        LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                        LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                        LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                    WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
                    #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezasciclico WHERE ID_PLAN = {$id_inventario} AND NConteo = 0)
                    ) 
                    AND inv.NConteo > 0
                    GROUP BY clave
          ORDER BY clave

         ) AS t WHERE Cerrar = 1 GROUP BY clave
         ORDER BY descripcion
      ";



      $queryBody = \db()->prepare($sqlBody);
      $queryBody->execute();
      $dataBody = [];
      $resbody = "";
      if($queryBody)
      {
        $resbody = $queryBody->fetchAll(\PDO::FETCH_ASSOC);
      }
    //$cia = $_SESSION['cve_cia']; 

    $sql = 'SELECT des_cia AS nombre, des_direcc AS direccion, imagen AS logo FROM c_compania WHERE cve_cia = '.$cia;
    $query = \db()->prepare($sql);
    $query->execute();

    $reslogo = $query->fetchAll(\PDO::FETCH_ASSOC);

    foreach($reslogo as $rowlogo)
      $logo = $rowlogo['logo'];

      //echo $logo." - ";
/*
    if($cia != ''){
      $logo = str_replace('/img', 'img', $logo);
      //$url = "".$_SERVER['DOCUMENT_ROOT']."/";
      $url = "";

      //$query->free_result();
      //$db->close();
    }
*/
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , LETTER, true, 'UTF-8', false);
        $pdf->SetCreator('wms');
        $pdf->SetAuthor('wms');
        $pdf->SetTitle('Reporte de Inventario');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
       $pdf->SetMargins(30, 30, 30);
       $pdf->SetFooterMargin(30);
        $filename = "Inventario Consolidado por Item #{$folio}.pdf";
        ob_start();
?>
        <br><br>
          <table style="width: 780px;" border="0">
            <tr>
              <td style="width: 65px"></td>
              <td style="width: 650px">


                <table border="0">
                  <tr>
                    <td style="width: 100px;"><img src="<?php echo '../../..'.$logo; ?>" alt="" height="100"></td>
                    <td align="center" style="font-size: 14px;width: 450px; text-align: center; vertical-align: middle;"><br><br><br><br><b>REPORTE DE INVENTARIO CONSOLIDADO POR ITEM</b>
                      <br><br>Inventario # <?php echo $id_inventario; ?></td>
                  </tr>
                </table>

                <br><br><br>

                <?php 
                $date=date_create($fecha);
                $fecha = date_format($date,"d/m/Y");
                ?>
                <table border="1">
                  <tr>
                    <td style="font-size: 15px;width: 100px;"> <b>FECHA</b></td>
                    <td style="font-size: 15px;width: 150px;" align="center"><?php echo $fecha; ?></td>
                  </tr>
                </table>

                <br><br><br>

                <table border="1">
                  <thead>
                    <tr>
                      <th style="width: 100px;">  <b>Artículo</b></th>
                      <th style="width: 300px;">  <b>Descripción</b></th>
                      <th style="width: 100px;">  <b>Cantidad Total</b></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php 
                    foreach($resbody as $row)
                    {
                      if($row["Cerrar"] == 1)
                      {
                    ?>
                    <tr style="font-size: 12px;">
                      <td style="width: 100px;">  <?php echo $row["clave"]; ?></td>
                      <td style="width: 300px;">  <?php echo $row["descripcion"]; ?></td>
                      <td style="width: 100px;" align="right">  <?php echo $row["Existencia_Total"]."&nbsp;&nbsp;"; ?></td>
                    </tr>
                    <?php 
                      }
                    }
                    ?>

                    </tbody>
                </table>

              </td>
              <td style="width: 65px"></td>
            </tr>
          </table>

<?php 
        $contentBody = ob_get_clean();
        //ob_flush();
            //$desProducto = ob_get_clean(); 
          $pdf->AddPage();
          $pdf->SetAutoPageBreak(TRUE, 5);
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
          $pdf->setMargins(0, 5, 0, 0);
          $pdf->SetXY(5, 5);
          $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
          $pdf->WriteHTML($contentBody, true, false, true, '');
          ob_end_clean();
      //}

      $pdf->Output($filename, 'I');
/*
      $content = $contentHeader . $contentBody;
      echo $content;
      $pdf = new \ReportePDF\PDF($_SESSION['cve_cia'], "Formato de Llenado para Inventario #{$folio}", "L");
      $pdf->setContent($content);
      $pdf->stream();
*/
    }

    public function printReport_fisico_ParaLlenado($folio ,$status)
    {
      //$estatus = "T";

      if($status == "Abierto"){$estatus = "A";}
      if($status == "Cerrado"){$estatus = "T";}
      
      $sqlHeader = "SELECT * FROM(SELECT 
                    inv.ID_Inventario AS consecutivo,
                    DATE_FORMAT(COALESCE(MIN(v_inv.fecha), inv.Fecha),'%d-%m-%Y') AS fecha_inicio,
                    (CASE 
                        WHEN inv.`Status` = 'T' OR inv.`Status` = 'O' THEN DATE_FORMAT(MAX(v_inv.fecha),'%d-%m-%Y') 
                        WHEN inv.`Status` = 'A' THEN '--' 
                    END) AS fecha_final,
                    almac.nombre AS almacen,
                    IFNULL(c_almacen.des_almac, 'Inventario Total') AS zona,
                    IFNULL(
                        (SELECT 
                            u.nombre_completo 
                        FROM c_usuario u
                        WHERE 
                            (CONVERT(u.cve_usuario USING UTF8) = 
                                (SELECT 
                                    conteo.cve_usuario
                                FROM t_conteoinventario conteo 
                                WHERE 
                                    conteo.ID_Inventario = inv.ID_Inventario AND 
                                    conteo.NConteo = 
                                        (SELECT 
                                            MAX(conteo2.NConteo) 
                                        FROM t_conteoinventario conteo2 
                                        WHERE conteo2.ID_Inventario = inv.ID_Inventario
                                        
                                        ) 					
                                LIMIT 1) 
                            ) 
                        ) 
                        ,'--'
                     ) AS usuario,
                
                    (CASE 
                        WHEN inv.`Status` = 'T' OR inv.`Status` = 'O' THEN 'Cerrado' 
                        WHEN inv.`Status` = 'A' THEN 'Abierto' 
                    END) AS `status`,
                
                    IFNULL((
                        SELECT 
                        SUM(ABS(ExistenciaTeorica - Cantidad))
                        FROM t_invpiezas
                        WHERE ID_Inventario = inv.ID_Inventario
                        AND t_invpiezas.NConteo = (Select max(t_invpiezas.NConteo) from t_invpiezas where t_invpiezas.ID_Inventario = inv.ID_Inventario)
                            GROUP BY ID_Inventario
                    
                    ), '--') AS diferencia,
                    
                  ROUND(
                    IFNULL((
                        SELECT 
                        ((sum(ExistenciaTeorica) - (if(ExistenciaTeorica-Cantidad < 0,((ExistenciaTeorica-Cantidad)*-1),(ExistenciaTeorica-Cantidad))))/sum(ExistenciaTeorica))*100 as Porcentaje
                        FROM t_invpiezas
                        WHERE ID_Inventario = inv.ID_Inventario
                        AND t_invpiezas.NConteo = (Select max(t_invpiezas.NConteo) from t_invpiezas where t_invpiezas.ID_Inventario = inv.ID_Inventario)
                            GROUP BY ID_Inventario
                    ), '--'), 2) AS porcentaje,
                    
                    
                    'Físico' AS tipo, 

                    IFNULL((SELECT 
                        nombre_completo 
                    FROM c_usuario 
                    WHERE id_user = 
                        (SELECT cve_supervisor FROM t_conteoinventario WHERE ID_Inventario = inv.ID_Inventario LIMIT 1)
                    ), '--') AS supervisor,

                    IFNULL((
                        SELECT 
                        IF(MAX(NConteo) < 1, 0, (MAX(NConteo))) conteo
                        FROM t_invpiezas
                        WHERE ID_Inventario = consecutivo
                            GROUP BY ID_Inventario
                        
                    ), '--') AS n_inventario  
                FROM th_inventario inv
                    LEFT JOIN c_almacenp almac ON inv.cve_almacen = CONVERT(almac.clave USING UTF8)
                    LEFT JOIN c_almacen ON inv.cve_zona = c_almacen.cve_almac
                    LEFT JOIN V_Inventario v_inv ON v_inv.ID_Inventario = inv.ID_Inventario
                WHERE inv.Activo = 1  AND inv.`Status` = '{$estatus}'
                GROUP BY inv.ID_Inventario
                
                UNION 
                
                SELECT
                    DISTINCT cab.ID_PLAN AS consecutivo, 	
                    DATE_FORMAT(cab.FECHA_INI, '%d-%m-%Y') AS fecha_inicio, 	
                    DATE_FORMAT(cab.FECHA_FIN, '%d-%m-%Y') AS fecha_final,	
                    ap.nombre AS almacen, 	
                    '--' AS zona,	
                    (SELECT u.cve_usuario FROM c_usuario u, t_conteoinventariocicl cic WHERE cic.cve_usuario = u.cve_usuario AND cic.ID_PLAN=cab.ID_PLAN LIMIT 1) AS usuario,
                    (CASE 
                    WHEN d.status = 'A' THEN 'Abierto'
                    WHEN d.status = 'T' THEN 'Cerrado'
                    ELSE 'Sin Definir'
                    END) AS status,	
                    
                    IFNULL((
                        SELECT 
                        SUM(ABS(ExistenciaTeorica - Cantidad))
                        FROM t_invpiezas
                        WHERE ID_Inventario = cab.ID_PLAN
                            GROUP BY ID_Inventario
                        
                    ), '--') AS diferencia,
                    
                  ROUND(
                    IFNULL((
                        SELECT 
                        ((sum(ExistenciaTeorica) - (if(ExistenciaTeorica-Cantidad < 0,((ExistenciaTeorica-Cantidad)*-1),(ExistenciaTeorica-Cantidad))))/sum(ExistenciaTeorica))*100 as Porcentaje
                        FROM t_invpiezas
                        WHERE ID_Inventario = cab.ID_PLAN
                            GROUP BY ID_Inventario
                    ), '--'), 2) AS porcentaje,
                    
                    'Cíclico' AS tipo,	

                    IFNULL((SELECT 
                        nombre_completo 
                    FROM c_usuario 
                    WHERE id_user = 
                        (SELECT cve_supervisor FROM t_conteoinventariocicl WHERE ID_PLAN = cab.ID_PLAN LIMIT 1)
                    ), '--') AS supervisor,

                    IFNULL((
                        SELECT 
                        IF(MAX(NConteo) < 1, 0, (MAX(NConteo))) conteo
                        FROM t_invpiezasciclico
                        WHERE ID_PLAN = cab.ID_PLAN
                            GROUP BY ID_PLAN
                        
                    ), '--') AS n_inventario
                FROM det_planifica_inventario d
                    LEFT JOIN c_articulo a ON d.cve_articulo = a.cve_articulo 
                    LEFT JOIN c_almacenp ap ON a.cve_almac = ap.id 
                    LEFT JOIN cab_planifica_inventario cab ON cab.ID_PLAN = d.ID_PLAN
                WHERE  d.`Status` = '{$estatus}'
                ) W
              WHERE consecutivo = '{$folio}'
              ORDER BY consecutivo DESC";
      
      //$sqlHeader = "SELECT * FROM V_AdministracionInventario WHERE consecutivo = '{$folio}';";
      $queryHeader = \db()->prepare($sqlHeader);
      $queryHeader->execute();
      $dataHeader = [];
      $contentHeader = "";
      if($queryHeader)
      {
        $dataHeader = $queryHeader->fetch(\PDO::FETCH_ASSOC);
      }

      $sqlBody = "SELECT * FROM(Select
        Coalesce(c_ubicacion.CodigoCSD, tubicacionesretencion.desc_ubicacion) as ubicacion,
        t_invpiezas.cve_articulo as clave,
        c_articulo.des_articulo as descripcion,
        IF(t_invpiezas.cve_lote = '','--',t_invpiezas.cve_lote) as lote,
        Ifnull(Date_format(c_lotes.caducidad, '%d-%m-%Y'), '--') as caducidad,
        '--' as serie,
        t_invpiezas.NConteo as conteo,
        (t_invpiezas.existenciateorica) as stockTeorico,
        (t_invpiezas.cantidad) as stockFisico,
        (t_invpiezas.cantidad - t_invpiezas.existenciateorica) as diferencia,
        c_usuario.nombre_completo as usuario,
        'Piezas' as unidad_medida
        FROM t_invpiezas
        LEFT JOIN t_ubicacioninventario on t_ubicacioninventario.id_inventario = t_invpiezas.id_inventario and t_ubicacioninventario.idy_ubica = t_invpiezas.idy_ubica 
        LEFT JOIN c_articulo on c_articulo.cve_articulo = t_invpiezas.cve_articulo
        LEFT JOIN c_lotes on c_lotes.lote = t_invpiezas.cve_lote AND t_invpiezas.cve_articulo = c_lotes.cve_articulo
        LEFT JOIN c_usuario on c_usuario.cve_usuario = t_invpiezas.cve_usuario
        LEFT JOIN tubicacionesretencion ON tubicacionesretencion.cve_ubicacion = t_ubicacioninventario.cve_ubicacion
        LEFT JOIN c_ubicacion ON t_ubicacioninventario.idy_ubica = c_ubicacion.idy_ubica
        WHERE  
          t_invpiezas.id_inventario = '{$folio}') x
        ORDER BY conteo, descripcion";

      
      $queryBody = \db()->prepare($sqlBody);
      $queryBody->execute();
      $dataBody = [];
      $contentBody = "";
      if($queryBody)
      {
        $contentBody = $queryBody->fetchAll(\PDO::FETCH_ASSOC);
      }
      
      
      //if($estatus != "T")
      if($estatus == "T")
      {
        $sqlMaxConteo = "SELECT Max(NConteo) as MaxConteo  
        FROM `t_invpiezas` 
        INNER JOIN th_inventario 
          ON th_inventario.ID_Inventario = t_invpiezas.ID_Inventario 
          AND th_inventario.Status = 'T' 
        WHERE t_invpiezas.ID_Inventario = '{$folio}'";
      
        $queryConteo = \db()->prepare($sqlMaxConteo);
        $queryConteo->execute();
        if($queryConteo)
        {
          $conteo_max = $queryConteo->fetch(\PDO::FETCH_ASSOC);
        }
      }

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , LETTER, true, 'UTF-8', false);
        $pdf->SetCreator('wms');
        $pdf->SetAuthor('wms');
        $pdf->SetTitle('Reporte de Inventario');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $filename = "Formato de Llenado para Inventario #{$folio}.pdf";

      
      if(!empty($queryHeader))
      {
        
        $fecha_inicio = date("d-m-Y h:i:s A", strtotime($dataHeader['fecha_inicio']));
        if($dataHeader['fecha_final'] != "--" && $dataHeader['fecha_final'] != null && $dataHeader['fecha_final'] != "")
        {
          $fecha_final = date("d-m-Y h:i:s A", strtotime($dataHeader['fecha_final']));

        }
        else
        {
          $fecha_final = "--";
        }

        ob_start();
        ?>
        <h3 align="center">Datos del Inventario #<?php echo $folio; ?></h3>
        <table style="width: 100%;" border="1">
          <thead>
            <tr>
              <th style="background-color: #f5f5f5; font-size: 5px;">Consecutivo</th>
              <th style="background-color: #f5f5f5; font-size: 5px;">Almacén</th>
              <th style="background-color: #f5f5f5; font-size: 5px;">Zona</th>
              <th style="background-color: #f5f5f5; font-size: 5px;">Usuario</th>
              <th style="background-color: #f5f5f5; font-size: 5px;">Fecha Inicio</th>
              <th style="background-color: #f5f5f5; font-size: 5px;">Fecha Fin</th>
              <th style="background-color: #f5f5f5; font-size: 5px;">Diferencia</th>
              <th style="background-color: #f5f5f5; font-size: 5px;">Fiabilidad %</th>
              <th style="background-color: #f5f5f5; font-size: 5px;">Status</th>
              <th style="background-color: #f5f5f5; font-size: 5px;">Supervisor</th>
              <th style="background-color: #f5f5f5; font-size: 5px;">Firma</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="font-size: 5px;"><?php echo utf8_encode($dataHeader['consecutivo']); ?></td>
              <td style="font-size: 5px;"><?php echo utf8_encode($dataHeader['almacen']); ?></td>
              <td style="font-size: 5px;"><?php echo utf8_encode($dataHeader['zona']); ?></td>
              <td style="font-size: 5px;"><?php echo " "; ?></td>
              <td style="font-size: 5px;"><?php echo " "; ?></td>
              <td style="font-size: 5px;"><?php echo " "; ?></td>
              <td style="font-size: 5px;"><?php echo " "; ?></td>
              <td style="font-size: 5px;"><?php echo " "; ?></td>
              <td style="font-size: 5px;"><?php echo utf8_encode($dataHeader['status']); ?></td>
              <td style="font-size: 5px;"><?php echo " "; ?></td>
              <td style="font-size: 5px;"><?php echo "__________________"; ?></td>
            </tr>
          </tbody>
        </table>
        <?php 
        //$contentHeader = ob_get_clean();
        //ob_flush();
      }

      if(!empty($queryBody))
      {
        //ob_start();
        ?>
        <h3 align="center">Llenado del Stock Fisico</h3>  
        <table style="width: 100%; border-collapse: collapse; font-size: 10px" border="1">
          <thead>
            <tr>
              <th style="padding: 5px; background-color: #f5f5f5; font-size: 5px;">Ubicación</th>
              <th style="padding: 5px; background-color: #f5f5f5; font-size: 5px;">Clave</th>
              <th style="padding: 5px; background-color: #f5f5f5; font-size: 5px;">Descripción</th>
              <th style="padding: 5px; background-color: #f5f5f5; font-size: 5px;">Lote</th>
              <th style="padding: 5px; background-color: #f5f5f5; font-size: 5px;">Caducidad</th>
              <th style="padding: 5px; background-color: #f5f5f5; font-size: 5px;">Serie</th>
              <th style="padding: 5px; background-color: #f5f5f5; font-size: 5px;">Stock Fisico</th>
              <th style="padding: 5px; background-color: #f5f5f5; font-size: 5px;">Diferencia</th> 
              <th style="padding: 5px; background-color: #f5f5f5; font-size: 5px;">Conteo</th>
              <th style="padding: 5px; background-color: #f5f5f5; font-size: 5px;">Unidad</th>
              <th style="padding: 5px; background-color: #f5f5f5; font-size: 5px;">Usuario</th>
              <th style="padding: 5px; background-color: #f5f5f5; font-size: 5px;">Firma</th>
            </tr>
          </thead>
          <tbody>
          <?php
          
            foreach($contentBody AS $body)
            {
              $color = "";//$index === (count($contentBody) - 1) && $dataHeader['status'] === 'Cerrado' ? //'background-color: yellow;' : '';
              
              if($body['conteo'] == $conteo_max["MaxConteo"])
              {
                $conteo_body = "Conteo de Cierre"."(".$body['conteo'].")";
              }
              else
              {
                $conteo_body = $body['conteo'];
                if($conteo_body == 0)
                {
                  $conteo_body = "Teorico";
                }
                else
                {
                  $conteo_body = $body['conteo'];
                }
              }
              if($conteo_body == "Teorico")
              {
              ?>
              <tr>
                <td style="padding: 5px;font-size: 5px; <?php echo $color; ?>"><?php echo utf8_encode($body['ubicacion']); ?></td>
                <td style="padding: 5px;font-size: 5px; <?php echo $color; ?>"><?php echo utf8_encode($body['clave']); ?></td>
                <td style="padding: 5px;font-size: 5px; <?php echo $color; ?>"><?php echo utf8_encode($body['descripcion']); ?></td>
                <td style="padding: 5px;font-size: 5px; <?php echo $color; ?>"><?php echo utf8_encode($body['lote']); ?></td>
                <td style="padding: 5px;font-size: 5px; <?php echo $color; ?>"><?php echo utf8_encode($body['caducidad']); ?></td>
                <td style="padding: 5px;font-size: 5px; <?php echo $color; ?>"><?php echo utf8_encode($body['serie']); ?></td>
                <td style="padding: 5px;font-size: 5px; <?php echo $color; ?>"><?php echo " "; ?></td>
                <td style="padding: 5px;font-size: 5px; <?php echo $color; ?>"><?php echo " "; ?></td>
                <td style="padding: 5px;font-size: 5px; <?php echo $color; ?>"><?php echo " "; ?></td>
                <td style="padding: 5px;font-size: 5px; <?php echo $color; ?>"><?php echo utf8_encode($body['unidad_medida']); ?></td>
                <td style="padding: 5px;font-size: 5px; <?php echo $color; ?>"><?php echo utf8_encode($body['usuario']); ?></td>
                <td style="padding: 5px;font-size: 5px; <?php echo $color; ?>"><?php echo "__________________"; ?></td>
              </tr>
              <?php 
              }
            }
            
          ?>
          </tbody>
        </table>
        <?php
        $contentBody = ob_get_clean();
        //ob_flush();
            //$desProducto = ob_get_clean(); 
          $pdf->AddPage();
          $pdf->SetAutoPageBreak(TRUE, 5);
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
          $pdf->setMargins(0, 5, 0, 0);
          $pdf->SetXY(5, 5);
          $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
          $pdf->WriteHTML($contentBody, true, false, true, '');
          ob_end_clean();
      }

      $pdf->Output($filename, 'I');

      $content = $contentHeader . $contentBody;
      echo $content;
      $pdf = new \ReportePDF\PDF($_SESSION['cve_cia'], "Formato de Llenado para Inventario #{$folio}", "L");
      $pdf->setContent($content);
      $pdf->stream();

    }
  
    public static function printReport_teorico($folio ,$status)
    {
      if($status == "Abierto"){$estatus = "A";}
      if($status == "Cerrado"){$estatus = "T";}
      
      $sqlHeader = "SELECT * FROM(SELECT 
                    inv.ID_Inventario AS consecutivo,
                    DATE_FORMAT(COALESCE(MIN(v_inv.fecha), inv.Fecha),'%d-%m-%Y') AS fecha_inicio,
                    (CASE 
                        WHEN inv.`Status` = 'T' OR inv.`Status` = 'O' THEN DATE_FORMAT(MAX(v_inv.fecha),'%d-%m-%Y') 
                        WHEN inv.`Status` = 'A' THEN '--' 
                    END) AS fecha_final,
                    almac.nombre AS almacen,
                    IFNULL(c_almacen.des_almac, 'Inventario Total') AS zona,
                    IFNULL(
                        (SELECT 
                            u.nombre_completo 
                        FROM c_usuario u
                        WHERE 
                            (CONVERT(u.cve_usuario USING UTF8) = 
                                (SELECT 
                                    conteo.cve_usuario
                                FROM t_conteoinventario conteo 
                                WHERE 
                                    conteo.ID_Inventario = inv.ID_Inventario AND 
                                    conteo.NConteo = 
                                        (SELECT 
                                            MAX(conteo2.NConteo) 
                                        FROM t_conteoinventario conteo2 
                                        WHERE conteo2.ID_Inventario = inv.ID_Inventario
                                        
                                        ) 					
                                LIMIT 1) 
                            ) 
                        ) 
                        ,'--'
                     ) AS usuario,
                
                    (CASE 
                        WHEN inv.`Status` = 'T' OR inv.`Status` = 'O' THEN 'Cerrado' 
                        WHEN inv.`Status` = 'A' THEN 'Abierto' 
                    END) AS `status`,
                
                    IFNULL((
                        SELECT 
                        SUM(ABS(ExistenciaTeorica - Cantidad))
                        FROM t_invpiezas
                        WHERE ID_Inventario = inv.ID_Inventario
                        AND t_invpiezas.NConteo = (Select max(t_invpiezas.NConteo) from t_invpiezas where t_invpiezas.ID_Inventario = inv.ID_Inventario)
                            GROUP BY ID_Inventario
                    
                    ), '--') AS diferencia,
                    
                  ROUND(
                    IFNULL((
                        SELECT 
                        ((sum(ExistenciaTeorica) - (if(ExistenciaTeorica-Cantidad < 0,((ExistenciaTeorica-Cantidad)*-1),(ExistenciaTeorica-Cantidad))))/sum(ExistenciaTeorica))*100 as Porcentaje
                        FROM t_invpiezas
                        WHERE ID_Inventario = inv.ID_Inventario
                        AND t_invpiezas.NConteo = (Select max(t_invpiezas.NConteo) from t_invpiezas where t_invpiezas.ID_Inventario = inv.ID_Inventario)
                            GROUP BY ID_Inventario
                    ), '--'), 2) AS porcentaje,
                    
                    
                    'Físico' AS tipo, 

                    IFNULL((SELECT 
                        nombre_completo 
                    FROM c_usuario 
                    WHERE id_user = 
                        (SELECT cve_supervisor FROM t_conteoinventario WHERE ID_Inventario = inv.ID_Inventario LIMIT 1)
                    ), '--') AS supervisor,

                    IFNULL((
                        SELECT 
                        IF(MAX(NConteo) < 1, 0, (MAX(NConteo))) conteo
                        FROM t_invpiezas
                        WHERE ID_Inventario = consecutivo
                            GROUP BY ID_Inventario
                        
                    ), '--') AS n_inventario  
                FROM th_inventario inv
                    LEFT JOIN c_almacenp almac ON inv.cve_almacen = CONVERT(almac.clave USING UTF8)
                    LEFT JOIN c_almacen ON inv.cve_zona = c_almacen.cve_almac
                    LEFT JOIN V_Inventario v_inv ON v_inv.ID_Inventario = inv.ID_Inventario
                WHERE inv.Activo = 1  AND inv.`Status` = '{$estatus}'
                GROUP BY inv.ID_Inventario
                
                UNION 
                
                SELECT
                    DISTINCT cab.ID_PLAN AS consecutivo, 	
                    DATE_FORMAT(cab.FECHA_INI, '%d-%m-%Y') AS fecha_inicio, 	
                    DATE_FORMAT(cab.FECHA_FIN, '%d-%m-%Y') AS fecha_final,	
                    ap.nombre AS almacen, 	
                    '--' AS zona,	
                    (SELECT u.cve_usuario FROM c_usuario u, t_conteoinventariocicl cic WHERE cic.cve_usuario = u.cve_usuario AND cic.ID_PLAN=cab.ID_PLAN LIMIT 1) AS usuario,
                    (CASE 
                    WHEN d.status = 'A' THEN 'Abierto'
                    WHEN d.status = 'T' THEN 'Cerrado'
                    ELSE 'Sin Definir'
                    END) AS status,	
                    
                    IFNULL((
                        SELECT 
                        SUM(ABS(ExistenciaTeorica - Cantidad))
                        FROM t_invpiezas
                        WHERE ID_Inventario = cab.ID_PLAN
                            GROUP BY ID_Inventario
                        
                    ), '--') AS diferencia,
                    
                  ROUND(
                    IFNULL((
                        SELECT 
                        ((sum(ExistenciaTeorica) - (if(ExistenciaTeorica-Cantidad < 0,((ExistenciaTeorica-Cantidad)*-1),(ExistenciaTeorica-Cantidad))))/sum(ExistenciaTeorica))*100 as Porcentaje
                        FROM t_invpiezas
                        WHERE ID_Inventario = cab.ID_PLAN
                            GROUP BY ID_Inventario
                    ), '--'), 2) AS porcentaje,
                    
                    'Cíclico' AS tipo,	

                    IFNULL((SELECT 
                        nombre_completo 
                    FROM c_usuario 
                    WHERE id_user = 
                        (SELECT cve_supervisor FROM t_conteoinventariocicl WHERE ID_PLAN = cab.ID_PLAN LIMIT 1)
                    ), '--') AS supervisor,

                    IFNULL((
                        SELECT 
                        IF(MAX(NConteo) < 1, 0, (MAX(NConteo))) conteo
                        FROM t_invpiezasciclico
                        WHERE ID_PLAN = cab.ID_PLAN
                            GROUP BY ID_PLAN
                        
                    ), '--') AS n_inventario
                FROM det_planifica_inventario d
                    LEFT JOIN c_articulo a ON d.cve_articulo = a.cve_articulo 
                    LEFT JOIN c_almacenp ap ON a.cve_almac = ap.id 
                    LEFT JOIN cab_planifica_inventario cab ON cab.ID_PLAN = d.ID_PLAN
                WHERE  d.`Status` = '{$estatus}'
                ) W
              WHERE consecutivo = '{$folio}'
              ORDER BY consecutivo DESC";
      
      //$sqlHeader = "SELECT * FROM V_AdministracionInventario WHERE consecutivo = '{$folio}';";
      $queryHeader = \db()->prepare($sqlHeader);
      $queryHeader->execute();
      $dataHeader = [];
      $contentHeader = "";
      if($queryHeader)
      {
        $dataHeader = $queryHeader->fetch(\PDO::FETCH_ASSOC);
      }

      $sqlBody = "SELECT * FROM(Select
        Coalesce(c_ubicacion.CodigoCSD, tubicacionesretencion.desc_ubicacion) as ubicacion,
        t_invpiezas.cve_articulo as clave,
        c_articulo.des_articulo as descripcion,
        IF(t_invpiezas.cve_lote = '','--',t_invpiezas.cve_lote) as lote,
        Ifnull(Date_format(c_lotes.caducidad, '%d-%m-%Y'), '--') as caducidad,
        '--' as serie,
        t_invpiezas.NConteo as conteo,
        (t_invpiezas.existenciateorica) as stockTeorico,
        (t_invpiezas.cantidad) as stockFisico,
        (t_invpiezas.cantidad - t_invpiezas.existenciateorica) as diferencia,
        c_usuario.nombre_completo as usuario,
        'Piezas' as unidad_medida
        FROM t_invpiezas
        LEFT JOIN t_ubicacioninventario on t_ubicacioninventario.id_inventario = t_invpiezas.id_inventario and t_ubicacioninventario.idy_ubica = t_invpiezas.idy_ubica 
        LEFT JOIN c_articulo on c_articulo.cve_articulo = t_invpiezas.cve_articulo
        LEFT JOIN c_lotes on c_lotes.lote = t_invpiezas.cve_lote AND t_invpiezas.cve_articulo = c_lotes.cve_articulo
        LEFT JOIN c_usuario on c_usuario.cve_usuario = t_invpiezas.cve_usuario
        LEFT JOIN tubicacionesretencion ON tubicacionesretencion.cve_ubicacion = t_ubicacioninventario.cve_ubicacion
        LEFT JOIN c_ubicacion ON t_ubicacioninventario.idy_ubica = c_ubicacion.idy_ubica
        WHERE  
          t_invpiezas.id_inventario = '{$folio}') x
        ORDER BY conteo, descripcion";
      
      $queryBody = \db()->prepare($sqlBody);
      $queryBody->execute();
      $dataBody = [];
      $contentBody = "";
      if($queryBody)
      {
        $contentBody = $queryBody->fetchAll(\PDO::FETCH_ASSOC);
      }
      
      if($estatus == "T")
      {
        $sqlMaxConteo = "SELECT Max(NConteo) as MaxConteo  
        FROM `t_invpiezas` 
        INNER JOIN th_inventario 
          ON th_inventario.ID_Inventario = t_invpiezas.ID_Inventario 
          AND th_inventario.Status = 'T' 
        WHERE t_invpiezas.ID_Inventario = '{$folio}'";
      
        $queryConteo = \db()->prepare($sqlMaxConteo);
        $queryConteo->execute();
        if($queryConteo)
        {
          $conteo_max = $queryConteo->fetch(\PDO::FETCH_ASSOC);
        }
      }
      
      if(!empty($queryHeader))
      {
        
        $fecha_inicio = date("d-m-Y h:i:s A", strtotime($dataHeader['fecha_inicio']));
        if($dataHeader['fecha_final'] != "--" && $dataHeader['fecha_final'] != null && $dataHeader['fecha_final'] != "")
        {
          $fecha_final = date("d-m-Y h:i:s A", strtotime($dataHeader['fecha_final']));
        }
        else
        {
          $fecha_final = "--";
        }
        
        ob_start();
        ?>
        <h3 align="center">Datos del Inventario #<?php echo $folio ?></h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;" border="1">
          <thead>
            <tr>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Consecutivo</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Almacén</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Zona</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Fecha Inicio</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Fecha Fin</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="padding: 5px"><?php echo $dataHeader['consecutivo'] ?></td>
              <td style="padding: 5px"><?php echo $dataHeader['almacen'] ?></td>
              <td style="padding: 5px"><?php echo $dataHeader['zona'] ?></td>
              <td style="padding: 5px"><?php echo $fecha_inicio ?></td>
              <td style="padding: 5px"><?php echo $fecha_final ?></td>
              <td style="padding: 5px"><?php echo $dataHeader['status'] ?></td>
            </tr>
          </tbody>
        </table>
        <?php 
        $contentHeader = ob_get_clean();
        ob_flush();
      }
      if(!empty($queryBody))
      {
        ob_start();
        ?>
        <h3 align="center">Detalles del Stock Teorico</h3>  
        <table style="width: 100%; border-collapse: collapse; font-size: 10px" border="1">
          <thead>
           <tr>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Ubicación</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Clave</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Descripción</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Lote</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Caducidad</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Serie</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Stock Teórico</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Unidad</th>
            </tr>
          </thead>
          <tbody>
          <?php
            foreach($contentBody AS $index => $body)
            {
              $color = /*$index === (count($contentBody) - 1) && $dataHeader['status'] === 'Cerrado' ? */""; //'background-color: yellow;' : '';

              if($body['conteo'] == $conteo_max["MaxConteo"])
              {
                $conteo_body = "Conteo de Cierre"."(".$body['conteo'].")";
              }
              else
              {
                $conteo_body = $body['conteo'];
                if($conteo_body == 0)
                {
                  $conteo_body = "Teorico";
                }
                else
                {
                  $conteo_body = $body['conteo'];
                }
              }
              if($conteo_body == "Teorico")
              {
              ?>
               <tr>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['ubicacion'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['clave'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['descripcion'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['lote'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['caducidad'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['serie'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['stockTeorico'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['unidad_medida'] ?></td>
              </tr>
              <?php 
              }
            };
          ?>
          </tbody>
        </table>
        <?php
        $contentBody = ob_get_clean();
        ob_flush();
      }
      $content = $contentHeader . $contentBody;
      $pdf = new \ReportePDF\PDF($_SESSION['cve_cia'], "Detalles del Stock Teorico en Inventario #{$folio}", "L");
      $pdf->setContent($content);
      $pdf->stream();
    }

    public static function printReport($folio ,$status)
    {
     
      if($status == "Abierto"){$estatus = "A";}
      if($status == "Cerrado"){$estatus = "T";}
      
      $sqlHeader = "SELECT * FROM(SELECT 
                    inv.ID_Inventario AS consecutivo,
                    DATE_FORMAT(COALESCE(MIN(v_inv.fecha), inv.Fecha),'%d-%m-%Y') AS fecha_inicio,
                    (CASE 
                        WHEN inv.`Status` = 'T' OR inv.`Status` = 'O' THEN DATE_FORMAT(MAX(v_inv.fecha),'%d-%m-%Y') 
                        WHEN inv.`Status` = 'A' THEN '--' 
                    END) AS fecha_final,
                    almac.nombre AS almacen,
                    IFNULL(c_almacen.des_almac, 'Inventario Total') AS zona,
                    IFNULL((SELECT 
                        `u`.`nombre_completo`
                    FROM
                        `c_usuario` `u`
                    WHERE
                        (CONVERT( `u`.`cve_usuario` USING UTF8) = (SELECT 
                                `conteo`.`cve_supervisor`
                            FROM
                                `t_conteoinventario` `conteo`
                            WHERE
                                ((`conteo`.`ID_Inventario` = `inv`.`ID_Inventario`)
                                    AND (`conteo`.`NConteo` = (SELECT 
                                        (MAX(`conteo2`.`NConteo`) - 1)
                                    FROM
                                        `t_conteoinventario` `conteo2`
                                    WHERE
                                        (`conteo2`.`ID_Inventario` = `inv`.`ID_Inventario`))))
                            GROUP BY `conteo`.`cve_supervisor`))),
                    '--') AS `usuario`,
                
                    (CASE 
                        WHEN inv.`Status` = 'T' OR inv.`Status` = 'O' THEN 'Cerrado' 
                        WHEN inv.`Status` = 'A' THEN 'Abierto' 
                    END) AS `status`,
                
                    IFNULL((
                        SELECT 
                        SUM(ABS(ExistenciaTeorica - Cantidad))
                        FROM t_invpiezas
                        WHERE ID_Inventario = inv.ID_Inventario
                        AND t_invpiezas.NConteo = (Select max(t_invpiezas.NConteo) from t_invpiezas where t_invpiezas.ID_Inventario = inv.ID_Inventario)
                            GROUP BY ID_Inventario
                    
                    ), '--') AS diferencia,
                    
                  ROUND(
                    IFNULL((
                        SELECT 
                        ((sum(ExistenciaTeorica) - (if(ExistenciaTeorica-Cantidad < 0,((ExistenciaTeorica-Cantidad)*-1),(ExistenciaTeorica-Cantidad))))/sum(ExistenciaTeorica))*100 as Porcentaje
                        FROM t_invpiezas
                        WHERE ID_Inventario = inv.ID_Inventario
                        AND t_invpiezas.NConteo = (Select max(t_invpiezas.NConteo) from t_invpiezas where t_invpiezas.ID_Inventario = inv.ID_Inventario)
                            GROUP BY ID_Inventario
                    ), '--'), 2) AS porcentaje,
                    
                    
                    'Físico' AS tipo, 

                    IFNULL((SELECT 
                        nombre_completo 
                    FROM c_usuario 
                    WHERE cve_usuario = 
                        (SELECT cve_supervisor FROM t_conteoinventario WHERE ID_Inventario = inv.ID_Inventario LIMIT 1)
                    ), '--') AS supervisor,

                    IFNULL((
                        SELECT 
                        IF(MAX(NConteo) < 1, 0, (MAX(NConteo))) conteo
                        FROM t_invpiezas
                        WHERE ID_Inventario = consecutivo
                            GROUP BY ID_Inventario
                        
                    ), '--') AS n_inventario  
                FROM th_inventario inv
                    LEFT JOIN c_almacenp almac ON inv.cve_almacen = CONVERT(almac.clave USING UTF8)
                    LEFT JOIN c_almacen ON inv.cve_zona = c_almacen.cve_almac
                    LEFT JOIN V_Inventario v_inv ON v_inv.ID_Inventario = inv.ID_Inventario
                WHERE inv.Activo = 1  AND inv.`Status` = '{$estatus}'
                GROUP BY inv.ID_Inventario
                ) W
              WHERE consecutivo = '{$folio}'
              ORDER BY consecutivo DESC";
      
      //$sqlHeader = "SELECT * FROM V_AdministracionInventario WHERE consecutivo = '{$folio}';";
      $queryHeader = \db()->prepare($sqlHeader);
      $queryHeader->execute();
      $dataHeader = [];
      $contentHeader = "";
      if($queryHeader)
      {
        $dataHeader = $queryHeader->fetch(\PDO::FETCH_ASSOC);
      }

      $sqlBody = "SELECT * FROM(Select
        Coalesce(c_ubicacion.CodigoCSD, tubicacionesretencion.desc_ubicacion) as ubicacion,
        t_invpiezas.cve_articulo as clave,
        c_articulo.des_articulo as descripcion,
        IF(t_invpiezas.cve_lote = '','--',t_invpiezas.cve_lote) as lote,
        Ifnull(Date_format(c_lotes.caducidad, '%d-%m-%Y'), '--') as caducidad,
        '--' as serie,
        t_invpiezas.NConteo as conteo,
        (t_invpiezas.existenciateorica) as stockTeorico,
        (t_invpiezas.cantidad) as stockFisico,
        (t_invpiezas.cantidad - t_invpiezas.existenciateorica) as diferencia,
        c_usuario.nombre_completo as usuario,
        'Piezas' as unidad_medida
        FROM t_invpiezas
        LEFT JOIN t_ubicacioninventario on t_ubicacioninventario.id_inventario = t_invpiezas.id_inventario and t_ubicacioninventario.idy_ubica = t_invpiezas.idy_ubica 
        LEFT JOIN c_articulo on c_articulo.cve_articulo = t_invpiezas.cve_articulo
        LEFT JOIN c_lotes on c_lotes.lote = t_invpiezas.cve_lote AND t_invpiezas.cve_articulo = c_lotes.cve_articulo
        LEFT JOIN c_usuario on c_usuario.cve_usuario = t_invpiezas.cve_usuario
        LEFT JOIN tubicacionesretencion ON tubicacionesretencion.cve_ubicacion = t_ubicacioninventario.cve_ubicacion
        LEFT JOIN c_ubicacion ON t_ubicacioninventario.idy_ubica = c_ubicacion.idy_ubica
        WHERE  
          t_invpiezas.id_inventario = '{$folio}') x
        ORDER BY conteo, descripcion";
      
      $queryBody = \db()->prepare($sqlBody);
      $queryBody->execute();
      $dataBody = [];
      $contentBody = "";
      if($queryBody)
      {
        $contentBody = $queryBody->fetchAll(\PDO::FETCH_ASSOC);
      }
      
      if($estatus == "T")
      {
        $sqlMaxConteo = "SELECT Max(NConteo) as MaxConteo  
        FROM `t_invpiezas` 
        INNER JOIN th_inventario 
          ON th_inventario.ID_Inventario = t_invpiezas.ID_Inventario 
          AND th_inventario.Status = 'T' 
        WHERE t_invpiezas.ID_Inventario = '{$folio}'";
      
        $queryConteo = \db()->prepare($sqlMaxConteo);
        $queryConteo->execute();
        if($queryConteo)
        {
          $conteo_max = $queryConteo->fetch(\PDO::FETCH_ASSOC);
        }
      }
      
      if(!empty($queryHeader))
      {
        
        $fecha_inicio = date("d-m-Y h:i:s A", strtotime($dataHeader['fecha_inicio']));
        if($dataHeader['fecha_final'] != "--" && $dataHeader['fecha_final'] != null && $dataHeader['fecha_final'] != "")
        {
          $fecha_final = date("d-m-Y h:i:s A", strtotime($dataHeader['fecha_final']));
        }
        else
        {
          $fecha_final = "--";
        }
        
        ob_start();
        ?>
        <h3 align="center">Datos del Inventario #<?php echo $folio ?></h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;" border="1">
          <thead>
            <tr>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Consecutivo</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Almacén</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Zona</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Usuario</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Fecha Inicio</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Fecha Fin</th>
              
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Diferencia</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Fiabilidad %</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Status</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Supervisor</th>
              <th style="padding: 5px; background-color: #f5f5f5; width: 12.5%">Firma</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="padding: 5px"><?php echo $dataHeader['consecutivo'] ?></td>
              <td style="padding: 5px"><?php echo $dataHeader['almacen'] ?></td>
              <td style="padding: 5px"><?php echo $dataHeader['zona'] ?></td>
              <td style="padding: 5px"><?php echo $dataHeader['usuario'] ?></td>
              <td style="padding: 5px"><?php echo $fecha_inicio ?></td>
              <td style="padding: 5px"><?php echo $fecha_final ?></td>
              
              <td style="padding: 5px"><?php echo $dataHeader['diferencia'] ?></td>
              <td style="padding: 5px"><?php echo $dataHeader['porcentaje'] ?></td>
              <td style="padding: 5px"><?php echo $dataHeader['status'] ?></td>
              <td style="padding: 5px"><?php echo $dataHeader['supervisor'] ?></td>
              <td style="padding: 5px"><?php echo "__________________" ?></td>
            </tr>
          </tbody>
        </table>
        <?php 
        $contentHeader = ob_get_clean();
        ob_flush();
      }
      if(!empty($queryBody))
      {
        ob_start();
        ?>
        <h3 align="center">Detalles del Conteo</h3>  
        <table style="width: 100%; border-collapse: collapse; font-size: 10px" border="1">
          <thead>
            <tr>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Ubicación</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Clave</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Descripción</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Lote</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Caducidad</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Serie</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Stock Teórico</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Stock Físico</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Diferencia</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Conteo</th>
              
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Unidad</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Usuario</th>
              <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Firma</th>
            </tr>
          </thead>
          <tbody>
          <?php
            $last_conteo = "";
            $articulos_totales = (count($contentBody));
            $i = 1;
            foreach($contentBody AS $index => $body)
            {
              $color = /*$index === (count($contentBody) - 1) && $dataHeader['status'] === 'Cerrado' ? */""; //'background-color: yellow;' : '';

              if($body['conteo'] == $conteo_max["MaxConteo"])
              {
                $conteo_body = "Conteo de Cierre"."(".$body['conteo'].")";
              }
              else
              {
                $conteo_body = $body['conteo'];
                if($conteo_body == 0)
                {
                  $conteo_body = "Teorico";
                }
                else
                {
                  $conteo_body = $body['conteo'];
                }
              }

              if($last_conteo==""){$last_conteo=$conteo_body;}

              if($last_conteo != $conteo_body)
              {
            ?>
                  <tr>
                    <td style="padding: 5px; background:#777" colspan="13"><br></td>
                  </tr>
              <?php
              }
              ?>
              <tr>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['ubicacion'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['clave'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['descripcion'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['lote'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['caducidad'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['serie'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['stockTeorico'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['stockFisico'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['diferencia'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $conteo_body ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['unidad_medida'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['usuario'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo "__________________" ?></td>
              </tr>
              <?php 
              if($last_conteo != $conteo_body)
              {
                $last_conteo = $conteo_body;
              }
            };
          ?>
          </tbody>
        </table>
        <?php
        $contentBody = ob_get_clean();
        ob_flush();
      }
      $content = $contentHeader . $contentBody;
      $pdf = new \ReportePDF\PDF($_SESSION['cve_cia'], "Inventario Físico #{$folio}", "L");
      $pdf->setContent($content);
      $pdf->stream();
    }
  
    public static function printDiferenciasExcel()
    {
        $sql = "SELECT 
                    (CASE
                        WHEN t_ubicacioninventario.cve_ubicacion IS NOT NULL THEN 
                            (SELECT desc_ubicacion FROM tubicacionesretencion WHERE t_ubicacioninventario.cve_ubicacion = tubicacionesretencion.cve_ubicacion)
                        WHEN t_ubicacioninventario.idy_ubica IS NOT NULL THEN
                            (SELECT c_ubicacion.CodigoCSD FROM c_ubicacion WHERE c_ubicacion.idy_ubica = t_ubicacioninventario.idy_ubica)
                        ELSE '--'
                    END) as ubicacion,
                    t_invpiezas.cve_articulo AS clave_articulo,
                    c_articulo.des_articulo AS descrt_invpiezascion_articulo,
                    IFNULL(c_lotes.LOTE, '--') AS lote,
                    IFNULL(c_lotes.CADUCIDAD, '--') AS caducidad,
                    '--' AS numero_serie,
                    t_invpiezas.ExistenciaTeorica AS stock_teorico,
                    t_invpiezas.Cantidad AS stock_fisico,
                    (t_invpiezas.Cantidad - t_invpiezas.ExistenciaTeorica) AS diferencia,
                    t_invpiezas.NConteo AS conteo,
                    'Piezas' AS unidad_medida,
                    c_usuario.nombre_completo AS usuario
              FROM t_invpiezas 
                  LEFT JOIN th_inventario ON th_inventario.ID_Inventario = t_invpiezas.ID_Inventario
                  LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = t_invpiezas.idy_ubica
                  LEFT JOIN c_articulo ON c_articulo.cve_articulo = t_invpiezas.cve_articulo
                  LEFT JOIN c_lotes ON c_lotes.LOTE = t_invpiezas.cve_lote AND c_lotes.cve_articulo = t_invpiezas.cve_articulo
                  LEFT JOIN c_usuario ON c_usuario.cve_usuario = t_invpiezas.cve_usuario
                  LEFT JOIN t_ubicacioninventario on t_ubicacioninventario.id_inventario = t_invpiezas.id_inventario and t_ubicacioninventario.idy_ubica = t_invpiezas.idy_ubica 
              WHERE t_invpiezas.NConteo > 0
                  AND t_invpiezas.Cantidad <> t_invpiezas.ExistenciaTeorica
                  AND t_invpiezas.NConteo = (SELECT MAX(NConteo) FROM t_invpiezas WHERE ID_Inventario = th_inventario.ID_Inventario);
        ";
      
        $queryBody = \db()->prepare($sqlBody);
        $queryBody->execute();
        $dataBody = [];
        $contentBody = "";
        if($queryBody)
        {
          $contentBody = $queryBody->fetchAll(\PDO::FETCH_ASSOC);
        }

        if($query->num_rows > 0){
            $delimiter = ",";
            $filename = "Diferencia entre conteos.csv";

            //create a file pointer
            $f = fopen('php://memory', 'w');

            //set column headers
            $fields = array('Ubicación', 'Clave', 'Descripcion', 'Lote', 'Caducidad', 'Serie', 'Stock Teórico', 'Stock Físico', 'Diferencia', 'Conteo', 'Unidad', 'Usuario');
            fputcsv($f, $fields, $delimiter);

            //output each row of the data, format line as csv and write to file pointer
            while($row = $query->fetch_assoc()){
                $lineData = $row;
                fputcsv($f, $lineData, $delimiter);
            }

            //move back to beginning of file
            fseek($f, 0);

            //set headers to download file rather than displayed
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '";');

            //output all remaining data on a file pointer
            fpassthru($f);
        }
        exit;
    }
  
    public static function printDiferencias($folio)
    {
        $and = "";
        if($folio != 0)
        {
          $and = "AND th_inventario.ID_Inventario = '{$folio}'";
        }      
      
        $sqlBody ="
            SELECT 
                th_inventario.ID_Inventario AS inventario,
                DATE_FORMAT(th_inventario.Fecha, '%d-%m-%Y') AS fecha,
                (CASE
                    WHEN t_ubicacioninventario.cve_ubicacion IS NOT NULL THEN 
                      (SELECT desc_ubicacion FROM tubicacionesretencion WHERE t_ubicacioninventario.cve_ubicacion = tubicacionesretencion.cve_ubicacion)
                    WHEN t_ubicacioninventario.idy_ubica IS NOT NULL THEN
                      (SELECT c_ubicacion.CodigoCSD FROM c_ubicacion WHERE c_ubicacion.idy_ubica = t_ubicacioninventario.idy_ubica)
                    ELSE '--'
                END) as ubicacion,
                #c_ubicacion.CodigoCSD AS ubicacion,
                t_invpiezas.cve_articulo AS clave_articulo,
                c_articulo.des_articulo AS descrt_invpiezascion_articulo,
                IFNULL(c_lotes.LOTE, '--') AS lote,
                IFNULL(c_lotes.CADUCIDAD, '--') AS caducidad,
                '--' AS numero_serie,
                ifnull(t_invpiezas.ExistenciaTeorica,0) AS stock_teorico,
                t_invpiezas.Cantidad AS stock_fisico,
                (ifnull(t_invpiezas.Cantidad,0) - ifnull(t_invpiezas.ExistenciaTeorica,0)) AS diferencia,
                t_invpiezas.NConteo AS conteo,
                c_usuario.nombre_completo AS usuario,
                (select c_usuario.nombre_completo from c_usuario where c_usuario.cve_usuario = (select t_conteoinventario.cve_supervisor from t_conteoinventario where t_conteoinventario.ID_Inventario = t_invpiezas.ID_Inventario limit 1)) as supervisor,
                'Piezas' AS unidad_medida,
                (SELECT (MAX(NConteo) - 1) FROM t_invpiezas WHERE ID_Inventario = th_inventario.ID_Inventario) AS total_conteo,
                (SELECT MAX(NConteo) FROM t_invpiezas WHERE ID_Inventario = th_inventario.ID_Inventario) as conteoMax,
                th_inventario.Status As status_inventario,
                c_almacenp.nombre as almacen
            FROM t_invpiezas 
                LEFT JOIN th_inventario ON th_inventario.ID_Inventario = t_invpiezas.ID_Inventario
                LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = t_invpiezas.idy_ubica
                inner join c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
                inner join c_almacenp on c_almacenp.id = c_almacen.cve_almacenp
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = t_invpiezas.cve_articulo
                LEFT JOIN c_lotes ON c_lotes.LOTE = t_invpiezas.cve_lote AND c_lotes.cve_articulo = t_invpiezas.cve_articulo
                LEFT JOIN c_usuario ON c_usuario.cve_usuario = t_invpiezas.cve_usuario
                LEFT JOIN t_ubicacioninventario on t_ubicacioninventario.id_inventario = t_invpiezas.id_inventario and t_ubicacioninventario.idy_ubica = t_invpiezas.idy_ubica 
           WHERE th_inventario.ID_Inventario = '{$folio}' 
                And t_invpiezas.cve_articulo in (
                    SELECT t_invpiezas.cve_articulo from t_invpiezas 
                    WHERE t_invpiezas.Cantidad <> t_invpiezas.ExistenciaTeorica
                    AND t_invpiezas.NConteo = (SELECT MAX(NConteo) FROM t_invpiezas WHERE ID_Inventario = th_inventario.ID_Inventario)
                    AND t_invpiezas.ID_Inventario = th_inventario.ID_Inventario
                )
                {$and}
            order by t_invpiezas.NConteo asc;
        ";
        $queryBody = \db()->prepare($sqlBody);
        $queryBody->execute();
        $dataBody = [];
        $contentBody = "";
        if($queryBody)
        {
          $contentBody = $queryBody->fetchAll(\PDO::FETCH_ASSOC);
        }

        if(!empty($queryBody))
        {
          ob_start();
          ?>
          <h3 align="center">Articulos Con Diferencias</h3>  

          <table style="width: 100%; border-collapse: collapse; font-size: 10px" border="1">
            <thead>
              <tr>              
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Inventario fisico</th>
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Almacén</th>
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Supervisor</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="padding: 5px;"><?php echo $contentBody[0]['inventario'] ?></td>
                <td style="padding: 5px;"><?php echo $contentBody[0]['almacen'] ?></td>
                <td style="padding: 5px;"><?php echo $contentBody[0]['supervisor'] ?></td>
              </tr>
            </tbody>
          </table>
          <br><br>
          <table style="width: 100%; border-collapse: collapse; font-size: 10px" border="1">
            <thead>
              <tr>
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Conteo</th>
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Clave</th>
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Descripción</th>
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Lote</th>
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Caducidad</th>
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Serie</th>
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Ubicación</th>
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Stock Teórico</th>
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Stock Físico</th>
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Diferencia</th>
                <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Usuario</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($contentBody AS $index => $body): ?>
              <?php 
                  $color = /*$index === (count($contentBody) - 1) && $dataHeader['status'] === 'Cerrado' ? */""; //'background-color: yellow;' : '';

              ?>
              <tr>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['conteo'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['clave_articulo'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['descrt_invpiezascion_articulo'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['lote'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['caducidad'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['numero_serie'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['ubicacion'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['stock_teorico'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['stock_fisico'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['diferencia'] ?></td>
                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['usuario'] ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php
          $contentBody = ob_get_clean();
          ob_flush();
        }
        $content = $contentBody;

        if($folio != 0)
        {
          $pdf = new \ReportePDF\PDF($_SESSION['cve_cia'], "Diferencias de inventario Físico #{$folio}", "L");
        }
        else
        {
            $pdf = new \ReportePDF\PDF($_SESSION['cve_cia'], "Diferencias de inventario Físico", "L");  
        }

        $pdf->setContent($content);
        $pdf->stream();
    }
}
