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
      $conteo = intval($conteo);
      $sql = "INSERT INTO t_conteoinventario (ID_Inventario,NConteo,Status)
      VALUES ((select MAX(ID_Inventario) from th_inventario),{$conteo},'A');";
      $this->save = \db()->prepare($sql);
      $this->save->execute(); 
    }

    function saveExistencia($id = false, $usuario = false, $ubicacion)
    {
/*
      $gral_ubica = str_replace(" ","",explode('|',$ubicacion));
      $ubicacion = $gral_ubica[0];
      $area = $gral_ubica[1]; //si es true es area si es false es ubicacion
*/

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
      if($conteo_maximo >= 1)
      {
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

        $sql .= 
        "SELECT 
            idy_ubica AS cve_ubicacion,
            cve_articulo, 
            cve_lote,
            ExistenciaTeorica
          FROM t_invpiezas
          WHERE ID_Inventario = $idSQL
          AND Art_Cerrado = 0 
          AND Cantidad <> ExistenciaTeorica
          AND NConteo = '0'
          ";
      }
      else
      {
        $sql .=
            "SELECT 
              cve_articulo, 
              cve_ubicacion, 
              cve_lote, 
              Existencia as cantidad 
            FROM V_ExistenciaGralProduccion 
            WHERE Existencia > 0 
            AND cve_ubicacion IN (SELECT COALESCE(cve_ubicacion, idy_ubica) FROM t_ubicacioninventario WHERE t_ubicacioninventario.idy_ubica = '$ubicacion')
            ";//WHERE ID_Inventario = {$idSQL}
      }
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
          if($conteo_maximo > 1 || $conteo_maximo == 1)
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
                Activo) 
              VALUES
                ({$idSQL}, 
                (SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = $idSQL), 
                '$cve_articulo',  
                '$cve_lote',
                '$cve_ubicacion', 
                0, 
                '$ExistenciaTeorica', 
                '".$usuario[0]["cve_usuario"]."', 
                NOW(), 
                NOW(), 
                0,
                1); 
            ";
          }
          else
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
                Activo) 
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
                1); 
            ";
           }
           //return "SQL = ".$sql;

           /*
            $sql_cab = "INSERT INTO cab_planifica_inventario(ID_PLAN, cve_articulo, ID_PERIODO, DESCRIPCION, FECHA_INI, FECHA_FIN, INTERVALO, ID_EXCALAR, DIA_MES, MES_YEAR, DIAS_LABORABLES, Activo) VALUES ($idSQL, '$cve_articulo', 1, '', NOW(), NOW(), 1, 0, 0, 0, 'N', 1);";
            //return "SQL = ".$sql." ------ SQL CAB = ".$sql_cab;
            $result["sql_cab"][]=$sql_cab;
            $sth_cab = \db()->prepare($sql_cab);
            $sth_cab->execute();
            */
        }

        $result["sql2"][]=$sql;
        $sth = \db()->prepare($sql);
        $sth->execute();
      }
      return $result;
    }

    function saveUbicacion( $ubicacion ) 
    {

      $ubicaciones = explode("|", $ubicacion[0]);
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

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , array(152.4, 101.6), true, 'UTF-8', false);
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
/*
      $content = $contentHeader . $contentBody;
      echo $content;
      $pdf = new \ReportePDF\PDF($_SESSION['cve_cia'], "Formato de Llenado para Inventario #{$folio}", "L");
      $pdf->setContent($content);
      $pdf->stream();
*/
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
