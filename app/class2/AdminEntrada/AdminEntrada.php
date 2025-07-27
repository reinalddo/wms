<?php

namespace AdminEntrada;

$tools = new \Tools\Tools();

class AdminEntrada
{

  const TABLE   = 'th_entalmacen';
  const TABLE2  = 'td_entalmacen';
  var $identifier;

  public function __construct( $Fol_Folio = false, $key = false )
  {
    $this->tools = new \Tools\Tools();
    if($Fol_Folio)
    {
      $this->Fol_Folio = (int) $Fol_Folio;
    }

      if($key) {

        $sql = sprintf('
          SELECT
            Fol_Folio
          FROM
            %s
          WHERE
            Fol_Folio = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\AdminEntrada\AdminEntrada');
        $sth->execute(array($key));

        $AdminEntrada = $sth->fetch();

        $this->Fol_Folio = $AdminEntrada->Fol_Folio;

      }

    }

    function load($codigo) 
    {
      $sql = sprintf('SELECT * FROM %s WHERE id_ocompra = "'.$codigo.'"',
      self::TABLE
      );
      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\AdminEntrada\AdminEntrada' );
      $sth->execute( array( $codigo ) );
      $this->data = $sth->fetch();
    }


    function loadDetalle($codigo) 
    {

      $sqlprevio='SET @row_number = 0;';
	    $sql=
            '
            select 
            (@row_number:=@row_number + 1) as linea,
            A.* from(
            SELECT
              td_aduana.num_orden AS numero_orden,
              c_usuario.nombre_completo AS usuario_activo,
              c_articulo.des_articulo AS descripcion,
              sum(td_aduana.cantidad) AS cantidad_pedida,
              c_articulo.cve_articulo AS clave,
              sum(td_entalmacen.CantidadRecibida) AS cantidad_recibida,
              sum(td_aduana.cantidad) - sum(CantidadRecibida) AS cantidad_faltante,
              sum(CantidadRecibida - CantidadDisponible) AS cantidad_danada,
              td_entalmacen. STATUS AS STATUS,
              th_aduana.fech_pedimento AS fecha_compromiso,
              MIN(DATE_FORMAT(th_entalmacen_log.fecha_inicio,"%d-%m-%Y")) AS fecha_recepcion,
              MIN(DATE_FORMAT(th_entalmacen_log.fecha_inicio,"%d-%m-%Y  %I:%i:%s %p"))  AS hora_inicio,
              MAX(DATE_FORMAT(th_entalmacen_log.fecha_fin,"%d-%m-%Y  %I:%i:%s %p"))  as hora_fin
            FROM td_aduana
              LEFT JOIN td_entalmacen on td_aduana.num_orden=td_entalmacen.fol_folio
              LEFT JOIN th_entalmacen_log on td_aduana.num_orden=th_entalmacen_log.Fol_Folio
              LEFT JOIN th_entalmacen ON th_entalmacen.Fol_Folio =  td_aduana.num_orden
              LEFT JOIN c_usuario ON c_usuario.cve_usuario = td_entalmacen.cve_usuario
              LEFT JOIN th_aduana ON th_aduana.num_pedimento =  td_aduana.num_orden
              LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_aduana.cve_articulo
            GROUP BY td_entalmacen.fol_folio, c_articulo.cve_articulo
            ) A 
            where numero_orden="'.$codigo.'"
            GROUP BY clave
            ';

      $sth = \db()->prepare( $sqlprevio );

      $sth->execute();

      $sth = \db()->prepare( $sql );

      $sth->execute();
      //$this->data = $sth->fetch();
      return $sth->fetchAll();
    }

	  function loadDetalle2($codigo)
    {
      $sql=
      '
      select
        c_articulo.des_articulo,
        td_entalmacen.*,
        c_lotes.CADUCIDAD as caducidad,
        tubicacionesretencion.desc_ubicacion as almacen
      FROM th_entalmacen
        LEFT JOIN td_entalmacen on th_entalmacen.Fol_Folio = td_entalmacen.fol_folio
        LEFT JOIN c_articulo on c_articulo.cve_articulo=td_entalmacen.cve_articulo
        LEFT JOIN c_lotes on c_lotes.LOTE=td_entalmacen.cve_lote and c_lotes.cve_articulo=td_entalmacen.cve_articulo
        LEFT JOIN tubicacionesretencion on tubicacionesretencion.cve_ubicacion=td_entalmacen.cve_ubicacion
      where th_entalmacen.id_ocompra="'.$codigo.'"
      ';

      /*
      '
      select 
        c_articulo.des_articulo, 
        td_entalmacen.*, 
        c_lotes.CADUCIDAD as caducidad, 
        tubicacionesretencion.desc_ubicacion as almacen 
      FROM td_entalmacen 
        LEFT JOIN c_articulo on c_articulo.cve_articulo=td_entalmacen.cve_articulo 
        LEFT JOIN th_entalmacen on td_entalmacen.fol_folio = th_entalmacen.Fol_Folio 
        LEFT JOIN c_lotes on c_lotes.LOTE=td_entalmacen.cve_lote and c_lotes.cve_articulo=td_entalmacen.cve_articulo 
        LEFT JOIN tubicacionesretencion on tubicacionesretencion.cve_ubicacion=td_entalmacen.cve_ubicacion 
      where th_entalmacen.id_ocompra="'.$codigo.'"
      ';
      */

      $sth = \db()->prepare( $sql );
      $sth->execute();
      return $sth->fetchAll();
    }

    function exist($Fol_Folio) 
    {
      $sql = sprintf('SELECT * FROM %s WHERE Fol_Folio = ?',
      self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\AdminEntrada\AdminEntrada' );
      $sth->execute( array( $Fol_Folio ) );
      $this->data = $sth->fetch();

      if(!$this->data)
      {
        return false;
      }
      else
      {
        return true;
      }
    }



    function getAll() 
    {
      $sql = 
        '
        SELECT 
          Cve_Almac,
          Cve_Proveedor,
          Cve_Usuario,
          Fec_Entrada,
          Fol_Folio,
          HoraFin
        FROM
        ' . self::TABLE . '

        ';
      $sth = \db()->prepare( $sql );
      // $sth->setFetchMode( \PDO::FETCH_CLASS, '\AdminEntrada\AdminEntrada' );
      $sth->execute( array( Fol_Folio ) );
      return $sth->fetchAll();
    }

    function __get( $key ) 
    {
      switch($key) 
      {
        case 'Fol_Folio':
        case 'cve_cia':
        case 'clave_AdminEntrada':
        case 'des_almac':
        case 'des_direcc':
        case 'cve_AdminEntradap':
        $this->load();
        return @$this->data->$key;
        default:
        return $this->key;
      }
    }

    function actualizarAdminEntrada( $data ) 
    {
      $sql = 
        '
        UPDATE
        ' . self::TABLE . '
        SET
          cve_AdminEntradap = ?,
          des_almac = ?,
          clave_AdminEntrada= ?
        WHERE
        Fol_Folio = ?
        ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['cve_AdminEntradap'],
        $data['des_almac'],
        $data['clave_AdminEntrada'],
        $data['Fol_Folio']
      ));
    }

    function borrarAdminEntrada( $data ) 
    {
      $sql = 
        '
        UPDATE
        ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
        Fol_Folio = ?
        ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['Fol_Folio']
      ));
    }

    /*function settings_design( $data ) {

      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Empresa = ?
        , VendId = ?
        , ID_Externo = ?
        WHERE
          ID_Proveedor = ?
      ';

      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['Empresa']
      , $data['VendId']
      , $data['ID_Externo']
      ) );

    }*/

    function getLastInsertId()
    {
      $sql = 
        '
        SELECT MAX(Fol_Folio) as ID_AdminEntrada
        FROM
        ' . self::TABLE . '
        ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\AdminEntrada\AdminEntrada');
      $sth->execute(array( ID_Ruta ) );
      return $sth->fetch();
    }

    function saveUserAl( $usuario, $ID_AdminEntrada ) 
    {
      try 
      {
        $sql = mysqli_query(\db2(), "CALL SPAD_AgregaRelUsA (
        '".$usuario."'
        , '".$ID_AdminEntrada."'
        );") or die(mysqli_error(\db2()));
      } 
      catch(PDOException $e) 
      {
        return 'ERROR: ' . $e->getMessage();
      }
    }

    function loadUserAdminEntrada($AdminEntrada) 
    {
      $sql = 
        '
        SELECT
          c.id_user as id_usuario,
          c.cve_usuario as clave_usuario,
          c.nombre_completo as nombre_usuario
        FROM trel_us_alm t
          INNER JOIN c_usuario c ON c.cve_usuario = t.cve_usuario
        WHERE t.Fol_Folio = '.$AdminEntrada.'
          AND t.Activo = 1 and c.Activo =1
        ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\AdminEntrada\AdminEntrada' );
      $sth->execute( array( $user ) );
      return $sth->fetchAll();
    }

    function loadAdminEntradaUser($user) 
    {
      $sql =
        '
        SELECT
          a.Fol_Folio as clave_AdminEntrada,
          a.des_almac as descripcion_AdminEntrada
        FROM trel_us_alm t
          INNER JOIN c_AdminEntrada a ON a.Fol_Folio = t.Fol_Folio
        WHERE t.cve_usuario = '.$user.'
          AND t.Activo = 1
          and a.Activo =1
        ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\AdminEntrada\AdminEntrada' );
      $sth->execute( array( $user ) );
      return $sth->fetchAll();
    }


	  function loadUsers($Fol_Folio) 
    {
      $sql = 
        '
        SELECT
          c.id_user as id_usuario,
          c.cve_usuario as clave_usuario,
          c.nombre_completo as nombre_usuario
        FROM c_usuario c
        WHERE c.Activo =1
          and c.id_user not in (
            SELECT 
              c.id_user 
            FROM c_usuario c, trel_us_alm t
            where c.cve_usuario = t.cve_usuario
              and t.Fol_Folio = '.$Fol_Folio.'
              AND t.Activo = 1 and c.Activo =1
          );
        ';

      $sth = \db()->prepare( $sql );
      //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
      $sth->execute();
      return $sth->fetchAll();
    }

    function loadAdminEntradaes($usuario) 
    {
      $sql =
        '
        SELECT
          a.Fol_Folio as clave_AdminEntrada,
          a.des_almac as descripcion_AdminEntrada
        FROM c_AdminEntrada a
        WHERE a.Activo =1
          and a.Fol_Folio not in (
            SELECT 
              a.Fol_Folio
            FROM c_AdminEntrada a, trel_us_alm t
            where a.Fol_Folio = t.Fol_Folio
              and t.cve_usuario = '.$usuario.'
              AND t.Activo = 1 and a.Activo =1
          );
        ';

      $sth = \db()->prepare( $sql );
      //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Ruta\Ruta' );
      $sth->execute();
      return $sth->fetchAll();
    }

    function borrarUsuarioAdminEntrada($cve_AdminEntrada) 
    {
      $sql = 
      '
        DELETE FROM trel_us_alm WHERE Fol_Folio = '.$cve_AdminEntrada.';
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute(array($cve_AdminEntrada));
    }

    function borrarAdminEntradaUsuario($cve_usuario) 
    {
      $sql = 
      '
        DELETE FROM trel_us_alm WHERE cve_usuario = '.$cve_usuario.';
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute(array($cve_AdminEntrada));
    }

    function reporte()
    {
      $sql='
      SELECT
        td_entalmacen.fol_folio as entrada,
        c_almacen.des_almac as almacen,
        th_entalmacen.fol_oep as orden_entrada,
        c_proveedores.Nombre as proveedor,
        th_entalmacen.Fec_Entrada as fecha_recepcion,
        c_usuario.nombre_completo as usuario_activo,
        c_usuario2.nombre_completo as autorizado,
        c_articulo.des_articulo as descripcion,
        td_entalmacen.CantidadRecibida as cantidad_recibida
      FROM td_entalmacen
        INNER JOIN th_entalmacen on th_entalmacen.Fol_Folio=td_entalmacen.fol_folio
        INNER JOIN c_usuario on c_usuario.cve_usuario= th_entalmacen.Cve_Usuario
        INNER JOIN c_usuario c_usuario2 on c_usuario2.cve_usuario= th_entalmacen.Cve_Autorizado
        INNER JOIN c_articulo on c_articulo.cve_articulo= td_entalmacen.cve_articulo
        INNER JOIN th_aduana on th_aduana.num_pedimento= th_entalmacen.Fol_Folio
        INNER JOIN c_almacen on c_almacen.clave_almacen= th_entalmacen.Cve_Almac
        INNER JOIN c_proveedores on c_proveedores.ID_Proveedor= th_entalmacen.Cve_Proveedor
      ';
      $sth = \db()->prepare( $sql );
      $sth->execute();
      return $sth->fetchAll();
    }
  
    function terminada($num_pedimento)
    {
      $to_update = array(
        "status" => "T"
      );
      $where = array(
        "num_pedimento" => $num_pedimento
      );
      $this->tools->dbUpdate("th_aduana",$to_update,$where);
    }

    function save( $_post ) 
    { 
      extract($_post);
      $clave_ubicacion = $_post["arrDetalle"][0]["cve_ubicacion"];

      $sql1 = 'Select if(max(Fol_Folio) is null,1,max(Fol_Folio)+1) as Fol_Folio from th_entalmacen';
      $sth = \db()->prepare( $sql1 );
      $sth->execute();
      $folio_next = $sth->fetch();
      $folio_final = $folio_next["Fol_Folio"];

      $query = mysqli_query(\db2(), "SELECT id FROM c_almacenp WHERE clave = '{$Cve_Almac}';");
      $almacen = mysqli_fetch_assoc($query)['id'];
      $query2 = mysqli_query(\db2(), "INSERT IGNORE INTO `t_tipomovimiento` (`nombre`) VALUES ('Entrada');");
      $query3 = mysqli_query(\db2(), "SELECT id_TipoMovimiento AS id FROM `t_tipomovimiento` WHERE nombre = 'Entrada';");
      $movimiento = mysqli_fetch_assoc($query3)['id'];

      if (${tipo}=="OC")
      {
        ${id_ocompra}=${Fol_Folio};
      } 
      try
      {
        $to_insert_th_entalmacen = array(
            "Fol_Folio"          => "$folio_final",
            "Cve_Almac"          => "${Cve_Almac}",
            "Fec_Entrada"        => "${Fec_Entrada}",
            "fol_oep"            => "${fol_oep}",
            "Cve_Usuario"        => "${Cve_Usuario}",
            "Cve_Proveedor"      => "${Cve_Proveedor}",
            "STATUS"             => "${STATUS}",
            "Cve_Autorizado"     => "${Cve_Autorizado}",
            "tipo"               => "${tipo}",
            "statusaurora"       => "${statusaurora}",
            "id_ocompra"         => "${id_ocompra}",
            "placas"             => "${placas}",
            "entarimado"         => "${entarimado}",
            "bufer"              => "${bufer}",
            "HoraInicio"         => "${HoraInicio}",
            "ID_Protocolo"       => "${ID_Protocolo}",
            "Consec_protocolo"   => "${Consec_protocolo}",
            "cve_ubicacion"      => "$clave_ubicacion",
            "HoraFin"            => "${HoraFin}",
            "Fact_Prov"          => "${Fact_Prov}"
        );
         
        $table_th = "th_entalmacen";
        $comprobar = mysqli_query(\db2(),"select id_ocompra from $table_th where id_ocompra = ${id_ocompra}");
        //echo var_dump($comprobar);
        //die();
          if(!(mysql_num_rows($comprobar)>0))
          {
             $insert_th = $this->tools->dbInsert($table_th,$to_insert_th_entalmacen);
          }
       
        
       // $insert_th = $this->tools->dbInsert($table_th,$to_insert_th_entalmacen);
        /*
        $sql = "
          INSERT IGNORE INTO `th_entalmacen` 
            (
            `Fol_Folio`,
            `Cve_Almac`,
            `Fec_Entrada`,
            `fol_oep`,
            `Cve_Usuario`,
            `Cve_Proveedor`,
            `STATUS`,
            `Cve_Autorizado`,
            `tipo`,
            `statusaurora`,
            `id_ocompra`,
            `placas`,
            `entarimado`,
            `bufer`,
            `HoraInicio`,
            `ID_Protocolo`,
            `Consec_protocolo`,
            `cve_ubicacion`,
            `HoraFin`,
            `Fact_Prov`
            )
          VALUES 
            (
            $folio_final,
            '${Cve_Almac}',
            '${Fec_Entrada}',
            '${fol_oep}',
            '${Cve_Usuario}',
            '${Cve_Proveedor}',
            '${STATUS}',
            '${Cve_Autorizado}',
            '${tipo}',
            '${statusaurora}',
            '${id_ocompra}',
            '${placas}',
            '${entarimado}',
            '${bufer}',
            '${HoraInicio}',
            '${ID_Protocolo}',
            '${Consec_protocolo}',
            '$clave_ubicacion',
            '${HoraFin}',
            '${Fact_Prov}'
            );
        ";
        */
        foreach ( ${arrDetalle} as $dat)
        { 
          $ubicada = 0;
          $to_insert_td_entalmacen = array(
                "fol_folio"               => $folio_final,
                "cve_articulo"            => $dat["cve_articulo"],
                "cve_lote"                => $dat["lote"],
                "CantidadPedida"          => $dat["pedida"],
                "CantidadRecibida"        => $dat["recibida"],
                "CantidadDisponible"      => $dat["recibida"],
                "CantidadUbicada"         => $ubicada,
                "status"                  => "P",
                "numero_serie"            => $dat["serie"],
                "cve_usuario"             => $dat["cve_usuario"],
                "cve_ubicacion"           => $dat["cve_ubicacion"],
                "fecha_inicio"            => "$fechainicio",
                "fecha_fin"               => "{$fechafin}",
                "costoUnitario"           => $dat["costo"],
                "tipo_entrada"            => "1",
                "num_orden"               => "${id_ocompra}"
          );
          $table_td = "td_entalmacen";
          $insert_td = $this->tools->dbInsert($table_td,$to_insert_td_entalmacen);

          
          $sql="INSERT INTO `t_pendienteacomodo` (`cve_articulo`, `cve_lote`, `Cantidad`, `cve_ubicacion`,`ID_Proveedor`) VALUES ('".$dat["cve_articulo"]."', '".$dat["lote"]."', '".$dat["recibida"]."', '".$dat["cve_ubicacion"]."', '".$dat["Cve_Proveedor"]."') ON DUPLICATE KEY UPDATE `Cantidad` = `Cantidad` + ".$dat["recibida"]."; ";
          $insert_t_pendienteacomodo = $this->tools->dbQuery($sql);

          $to_insert_t_cardex = array(
                "cve_articulo"       => $dat["cve_articulo"],
                "cve_lote"           => $dat["lote"],
                "fecha"              => $fechafin,
                "origen"             => $Cve_Proveedor,
                "destino"            => $Fol_Folio,
                "cantidad"           => $dat["recibida"],
                "id_TipoMovimiento"  => $movimiento,
                "cve_usuario"        => $Cve_Usuario,
                "Cve_Almac"          => $almacen,
                "Activo"             => "1"
          );
          $table_cardex = "t_cardex";
          $insert_cardex = $this->tools->dbInsert($table_cardex,$to_insert_t_cardex);

          /*
          $sql.= "INSERT INTO `td_entalmacen` 
                  (
                  `fol_folio`,
                  `cve_articulo`,
                  `cve_lote`,
                  `CantidadPedida`,
                  `CantidadRecibida`,
                  `CantidadDisponible`,
                  `CantidadUbicada`,
                  `status`,
                  `numero_serie`,
                  `cve_usuario`,
                  `cve_ubicacion`,
                  `fecha_inicio`, 
                  `fecha_fin`,
                  `costoUnitario`,
                  `tipo_entrada`,
                  `num_orden`)
                VALUES 
                ($folio_final,
                '".$dat["cve_articulo"]."',
                '".$dat["lote"]."',
                '".$dat["pedida"]."',
                '".$dat["recibida"]."',
                '".$dat["recibida"]."',
                '".$ubicada."',
                'P',
                '".$dat["serie"]."',
                '".$dat["cve_usuario"]."',
                '".$dat["cve_ubicacion"]."',
                '$fechainicio',
                '{$fechafin}',
                '".$dat["costo"]."',
                '1',
                '${id_ocompra}');
                ";
          */
          /*
          $sql .= "INSERT INTO `t_pendienteacomodo` (`cve_articulo`, `cve_lote`, `Cantidad`, `cve_ubicacion`) VALUES ('".$dat["cve_articulo"]."', '".$dat["lote"]."', '".$dat["recibida"]."', '".$dat["cve_ubicacion"]."') ON DUPLICATE KEY UPDATE `Cantidad` = `Cantidad` + ".$dat["recibida"]."; ";
          */
          /*
          $sql .= "INSERT INTO `t_cardex` (`cve_articulo`, `cve_lote`, `fecha`, `origen`, `destino`, `cantidad`, `id_TipoMovimiento`, `cve_usuario`, `Cve_Almac`, `Activo`) VALUES ('".$dat["cve_articulo"]."', '".$dat["lote"]."', '{$fechafin}', '{$Cve_Proveedor}', '{$Fol_Folio}', ".$dat["recibida"].", '{$movimiento}', '{$Cve_Usuario}', '{$almacen}', 1);";
          */
        }

        if(!empty($quehizo))
        {
          $to_insert_th_entalmacen_log = array(
                "Fol_Folio"             => $folio_final,
                "fecha_inicio"          => ${fechainicio},
                "fecha_fin"             => $fechafin,
                "cve_usuario"           => ${Cve_Usuario},
                "quehizo"               => $quehizo
          );
          $table_th_log = "th_entalmacen_log";
          $insert_th_log = $this->tools->dbInsert($table_th_log,$to_insert_th_entalmacen_log);
          /*
          $sql.="INSERT INTO `th_entalmacen_log` (`Fol_Folio`, `fecha_inicio`, `fecha_fin`, `cve_usuario`, `quehizo`) VALUES ($folio_final, '${fechainicio}', '{$fechafin}', '${Cve_Usuario}', '$quehizo');";
          */
        }
        else
        {
          $to_insert_th_entalmacen_log = array(
                "Fol_Folio"             => $folio_final,
                "fecha_inicio"          => ${fechainicio},
                "fecha_fin"             => $fechafin,
                "cve_usuario"           => ${Cve_Usuario}
          );
          $table_th_log = "th_entalmacen_log";
          $insert_th_log = $this->tools->dbInsert($table_th_log,$to_insert_th_entalmacen_log);
          /*
          $sql.="INSERT INTO `th_entalmacen_log` (`Fol_Folio`, `fecha_inicio`, `fecha_fin`, `cve_usuario`) VALUES ($folio_final, '${fechainicio}', '{$fechafin}', '${Cve_Usuario}');";
          */
        }

        if (${tipo}=="OC")
        {
          $status="I";
          if (${STATUS}=="E")
          {
            $status="T";
          }
          if (${STATUS}=="P")
          {
            $status="I";
          }

          $to_update_th_aduana = array(
            "status"   => $status,
          );
          $where_update = array(
            "num_pedimento"    => ${Fol_Folio}
          );
          $table_th_aduana = "th_aduana";
          $insert_orden = $this->tools->dbUpdate($table_th_aduana,$to_update_th_aduana,$where_update);
          /*
          $sql.='UPDATE th_aduana set status="'.$status.'" where num_pedimento="'.${Fol_Folio}.'";';
          */
        }
        //mysqli_multi_query(\db2(), $sql);
      }
      catch(Exception $e)
      {
        $res = 'ERROR: ' . $e->getMessage();
      }
      $res["debug"] = $insert_td;
      return $res;
    }

  	    function actualizar( $_post ) {

        extract($_post);
          
        $sql = 'Select if(max(Fol_Folio) is null,1,max(Fol_Folio)+1) as Fol_Folio from th_entalmacen';
        $sth = \db()->prepare( $sql );
        $sth->execute();
        $folio_next = $sth->fetch();
        $folio_final = $folio_next["Fol_Folio"];
          
        $query = mysqli_query(\db2(), "SELECT id FROM c_almacenp WHERE clave = '{$Cve_Almac}';");
        $almacen = mysqli_fetch_assoc($query)['id'];
        $query2 = mysqli_query(\db2(), "INSERT IGNORE INTO `t_tipomovimiento` (`nombre`) VALUES ('Entrada');");
        $query3 = mysqli_query(\db2(), "SELECT id_TipoMovimiento AS id FROM `t_tipomovimiento` WHERE nombre = 'Entrada';");
        $movimiento = mysqli_fetch_assoc($query3)['id'];

        $sql = "
         UPDATE `th_entalmacen` set HoraFin='${HoraFin}', status='${STATUS}' where id_ocompra='${Fol_Folio}' ;
        ";

		foreach ( ${arrDetalle} as $dat){
      
      
      extract($dat);
      if(!empty($serie) && empty($lote)){
        $sqlc = "SELECT cve_articulo FROM td_entalmacen LEFT JOIN th_entalmacen on td_entalmacen.fol_folio = th_entalmacen.Fol_Folio  WHERE th_entalmacen.id_ocompra= '$Fol_Folio' AND cve_articulo = '$cve_articulo' AND numero_serie = '$serie' AND cve_usuario = '$cve_usuario'  AND cve_ubicacion = '$cve_ubicacion' AND CantidadRecibida = '$recibida' AND fecha_inicio = '$fechainicio';";
      }else{
          $sqlc = "SELECT cve_articulo FROM td_entalmacen LEFT JOIN th_entalmacen on td_entalmacen.fol_folio = th_entalmacen.Fol_Folio  WHERE th_entalmacen.id_ocompra= '$Fol_Folio' AND cve_articulo = '$cve_articulo' AND cve_lote = '$lote' AND cve_usuario = '$cve_usuario'  AND cve_ubicacion = '$cve_ubicacion' AND CantidadRecibida = '$recibida' AND fecha_inicio = '$fechainicio';";
      }

      $query = mysqli_query(\db2(),$sqlc);
      if($query->num_rows < 1){
        if(!empty($serie) && empty($lote)){
          $sqld = "SELECT id, CantidadRecibida, CantidadDisponible from td_entalmacen LEFT JOIN th_entalmacen on td_entalmacen.fol_folio = th_entalmacen.Fol_Folio  WHERE th_entalmacen.id_ocompra= '$Fol_Folio' AND cve_articulo = '$cve_articulo' AND numero_serie = '$serie';";
        }else{
          $sqld = "SELECT id, CantidadRecibida, CantidadDisponible from td_entalmacen LEFT JOIN th_entalmacen on td_entalmacen.fol_folio = th_entalmacen.Fol_Folio  WHERE th_entalmacen.id_ocompra= '$Fol_Folio' AND cve_articulo = '$cve_articulo' AND cve_lote = '$lote';";
        }
        $query = mysqli_query(\db2(),$sqld);
        if($query->num_rows > 0){
          $res = mysqli_fetch_array($query);
          $id = $res['id'];
          $cantidad = $res['CantidadRecibida'] + $dat['recibida'];
          $cantidad_disponible = $res['CantidadDisponible'] + $dat['recibida'];
          $sql .= "UPDATE td_entalmacen SET CantidadRecibida = '$cantidad', CantidadDisponible = '$cantidad_disponible', cve_usuario = '".$dat["cve_usuario"]."', fecha_fin = '{$fechafin}', cve_ubicacion = '".$dat["cve_ubicacion"]."' WHERE id = '$id';";
        }else{
          $sql.= "INSERT INTO `td_entalmacen` (`fol_folio`, `cve_articulo`, `cve_lote`, `CantidadPedida`, `CantidadRecibida`,`CantidadDisponible`, `CantidadUbicada`,
          `status`, `numero_serie`,`cve_usuario`,`cve_ubicacion`, `fecha_inicio`, `fecha_fin`, `costoUnitario`,`tipo_entrada`, `num_orden` )
          VALUES ('$folio_final', '".$dat["cve_articulo"]."', '".$dat["lote"]."', '".$dat["pedida"]."', '".$dat["recibida"]."', '".$dat["recibida"]."', '0', 'P', '".$dat["serie"]."', '".$dat["cve_usuario"]."', '".$dat["cve_ubicacion"]."', '$fechainicio', '{$fechafin}', '".$dat["costo"]."','1', '".$_post["Fol_Folio"]."' );";
        }
        $sql .= "INSERT INTO `t_pendienteacomodo` (`cve_articulo`, `cve_lote`, `Cantidad`, `cve_ubicacion`) VALUES ('".$dat["cve_articulo"]."', '".$dat["lote"]."', '".$dat["recibida"]."', '".$dat["cve_ubicacion"]."') ON DUPLICATE KEY UPDATE `Cantidad` = `Cantidad` + ".$dat["recibida"]."; ";

        $sql .= "INSERT INTO `t_cardex` (`cve_articulo`, `cve_lote`, `fecha`, `origen`, `destino`, `cantidad`, `id_TipoMovimiento`, `cve_usuario`, `Cve_Almac`, `Activo`) VALUES ('".$dat["cve_articulo"]."', '".$dat["lote"]."', '{$fechafin}', '{$Cve_Proveedor}', $folio_final, ".$dat["recibida"].", '{$movimiento}', '{$Cve_Usuario}', '{$almacen}', 1);";
      }
  }

		if (${tipo}=="OC"){
			 $status="I";
			if (${STATUS}=="E") $status="T";
			if (${STATUS}=="P") $status="I";

		$sql.='UPDATE th_aduana set status="'.$status.'" where num_pedimento="'.${Fol_Folio}.'";';

    if(!empty($quehizo)){
      $sql .="INSERT INTO `th_entalmacen_log` (`Fol_Folio`, `fecha_inicio`, `fecha_fin`, `cve_usuario`, `quehizo`) VALUES ($folio_final, '${fechainicio}', '{$fechafin}', '${Cve_Usuario}', '$quehizo');";
    }
    else{
      $sql .="INSERT INTO `th_entalmacen_log` (`Fol_Folio`, `fecha_inicio`, `fecha_fin`, `cve_usuario`) VALUES ($folio_final, '${fechainicio}', '{$fechafin}', '${Cve_Usuario}');";
    }

		}
          
        //echo var_dump($sql);
        //die();
        mysqli_multi_query(\db2(), $sql);

    }

	function isTerminado($codigo)
  {
    $termino=false;
    $sql = "select cve_articulo as articulo ,sum(CantidadDisponible) as cantidad from td_entalmacen where num_orden='$codigo' GROUP BY cve_articulo";
    $sth = \db()->prepare( $sql );
    $sth->execute();
    $llegados=$sth->fetchAll();
    $sql = "select cve_articulo as articulo ,sum(cantidad) as cantidad from td_aduana where num_orden='$codigo' GROUP BY cve_articulo;";
    $sth = \db()->prepare( $sql );
    $sth->execute();
    $pedidos=$sth->fetchAll();
    foreach  ($llegados as $llegado)
    {
      foreach  ($pedidos as $pedido)
      {
        if ($llegado["articulo"]==$pedido["articulo"] && $llegado["cantidad"]==$pedido["cantidad"])
        {
          $termino=true;
        }
      }
    }
    return $termino;
	}

		function getTotalPedido($_post){

			extract($_post);

			if (${fechainicio} && ${fechafin}) $split=' and (th_aduana.fech_pedimento>="'.${fechainicio}.'" and th_aduana.fech_pedimento<="'.${fechafin}.'") ';

			/*if (${almacen}!=""){
			$split=" and th_aduana.Cve_Almac='${almacen}' ";
			}*/
      
      $pres="";
      
      if (${presupuesto}!=""){
			$pres=" and th_aduana.presupuesto='${presupuesto}' ";
			}

			    $sql = /*"select th_aduana.num_pedimento as numero_oc,
                  'OC' AS tipo,
                  th_aduana.fech_pedimento AS fecha_entrega,
                  sum(td_aduana.cantidad) AS total_pedido,
                  th_aduana.status as estado
                  from th_aduana
                  LEFT JOIN td_aduana ON td_aduana.num_orden = th_aduana.num_pedimento
                  where th_aduana.Activo='1'  $split $pres ";*/
            "SELECT * FROM (
            SELECT th_entalmacen.Fol_Folio AS numero_oc,
                th_entalmacen.tipo AS tipo,
                td_entalmacen.tipo_entrada as tipo_entrada,
                c_usuario.nombre_completo AS usuario_activo,
                DATE_FORMAT(th_aduana.fech_pedimento,'%d-%m-%Y %I:%i:%s %p') AS fecha_entrega,
                (SELECT MIN(DATE_FORMAT(td_entalmacen.fecha_inicio,'%d-%m-%Y %I:%i:%s %p')) FROM td_entalmacen WHERE fol_folio = th_aduana.num_pedimento) as fecha_recepcion,
                (SELECT DATE_FORMAT(MAX(td_entalmacen.fecha_fin),'%d-%m-%Y %I:%i:%s %p') FROM td_entalmacen WHERE fol_folio = th_aduana.num_pedimento) as fecha_fin_recepcion,
                (SELECT sum(td_aduana.cantidad) FROM td_aduana WHERE td_aduana.num_orden = th_entalmacen.Fol_Folio) as total_pedido,
                (SELECT sum(c_articulo.peso * td_aduana.cantidad) FROM td_aduana, c_articulo where td_aduana.num_orden=th_entalmacen.Fol_Folio and td_aduana.cve_articulo=c_articulo.cve_articulo) as peso_estimado,
                th_entalmacen.status  as estado,
                
                c_proveedores.Nombre as proveedor,
                (SELECT sum(td_entalmacen.CantidadRecibida) FROM td_entalmacen WHERE td_entalmacen.fol_folio=th_entalmacen.Fol_Folio) as cantidad_recibida,
                th_entalmacen.Fact_Prov as facprov,
                th_aduana.Cve_Almac as almacen,
                th_aduana.factura as erp,
                th_aduana.recurso as recurso,
                th_aduana.procedimiento as procedimiento,
                th_aduana.dictamen as dictamen,
                c_presupuestos.nombreDePresupuesto,
                th_aduana.presupuesto as presupuesto,
                th_aduana.condicionesDePago as condicionesDePago,
                th_aduana.lugarDeEntrega as lugarDeEntrega,
                DATE_FORMAT(th_aduana.fechaDeFallo,'%d-%m-%Y') AS fechaDeFallo,
                th_aduana.plazoDeEntrega as plazoDeEntrega,
                th_aduana.numeroDeExpediente as numeroDeExpediente,
                th_aduana.areaSolicitante as areaSolicitante,
                th_aduana.numSuficiencia as numSuficiencia,
                th_aduana.fechaSuficiencia as fechaSuficiencia,
                th_aduana.fechaContrato as fechaContrato,
                th_aduana.montoSuficiencia as montoSuficiencia,
                th_aduana.numeroContrato as numeroContrato,
                
                (SELECT (SUM(td_aduana.cantidad*td_aduana.costo)) FROM td_aduana WHERE num_pedimento = td_aduana.num_orden) as importe
            FROM th_entalmacen
                LEFT JOIN c_usuario ON c_usuario.cve_usuario= th_entalmacen.Cve_Usuario
                LEFT JOIN th_aduana ON th_aduana.num_pedimento = th_entalmacen.id_ocompra 
                LEFT JOIN td_aduana ON td_aduana.num_orden = th_entalmacen.id_ocompra 
                LEFT JOIN c_proveedores ON th_entalmacen.Cve_Proveedor= c_proveedores.ID_Proveedor
                LEFT JOIN td_entalmacen on th_entalmacen.Fol_Folio= td_entalmacen.fol_folio
                LEFT JOIN c_articulo ON td_aduana.cve_articulo = c_articulo.cve_articulo
                LEFT JOIN th_entalmacen_log ON th_entalmacen_log.Fol_Folio= th_entalmacen.Fol_Folio
                LEFT JOIN c_presupuestos ON th_aduana.presupuesto = c_presupuestos.id
                
                GROUP BY numero_oc,td_entalmacen.CantidadRecibida
        
            
        ) A
                    WHERE almacen = '${almacen}'
                    $split $pres
                    GROUP BY numero_oc
                    ORDER BY numero_oc DESC
                   ";
      
        //echo var_dump($sql);
        //die();

				$sth = \db()->prepare( $sql );
				$sth->execute();
				return $sth->fetch();
        

	}

	function cambiarEstatus($fol_folio,$status, $statusAduana){
		$sql ="UPDATE th_entalmacen SET STATUS='{$status}' where Fol_Folio='{$fol_folio}';
           UPDATE td_entalmacen SET status='{$status}' where fol_folio='{$fol_folio}';
            ";
    if(!empty($statusAduana)){
      $sql .= "UPDATE th_aduana SET status = '{$statusAduana}' WHERE num_pedimento = '{$fol_folio}';";
    }
		return mysqli_multi_query(\db2(),$sql);
	}
    
  function getPresupuestos()
    {
    $sql = 'SELECT nombreDePresupuesto, id,claveDePartida
            FROM c_presupuestos
            GROUP BY nombreDePresupuesto';

    $sth = \db()->prepare($sql);
    //$sth->setFetchMode( \PDO::FETCH_CLASS, '\AdminEntrada\AdminEntrada' );
    @$sth->execute(array( nombreDePresupuesto ));

    return $sth->fetchAll();
    }
    
    function presupuestoAsignado($presupuesto)
    {
    extract($_post);
    $sql = 'SELECT 
            th_aduana.presupuesto, 
            c_presupuestos.monto 
            FROM `th_aduana` 
            LEFT JOIN c_presupuestos ON th_aduana.presupuesto = c_presupuestos.id
            WHERE th_aduana.presupuesto="'.$presupuesto.'"
            GROUP BY presupuesto';

    $sth = \db()->prepare($sql);
    $sth->execute();
    return $sth->fetchAll();
    }
    
    function importeTotalDeOrden($presupuesto)
    {
    extract($_post);
    $sql = 'SELECT presupuesto,SUM(importeOrden) AS importeTotalDePresupuesto 
            FROM `th_aduana` 
            WHERE presupuesto = "'.$presupuesto.'"
            GROUP BY presupuesto';

    $sth = \db()->prepare($sql);
    $sth->execute();
    return $sth->fetchAll();
    }
    
    
    function datosResumen($codigo)
    {
    extract($_post);
    $sql = "select	th_entalmacen.Cve_Proveedor,
                    if(th_entalmacen.id_ocompra != '', th_entalmacen.id_ocompra, th_entalmacen.Fol_Folio) as id_ocompra,
                    c_proveedores.nombre as nombre_proveedor
            from th_entalmacen 
            left join c_proveedores on th_entalmacen.Cve_Proveedor = c_proveedores.ID_Proveedor
            where th_entalmacen.Fol_Folio = '$codigo'";
    $sth = \db()->prepare($sql);
    $sth->execute();
     
    return $sth->fetch();
    }
    
    //EDG
    function calcularCostoPromedio($data)
    {
      
      extract($data);
    
      foreach($data["arrDetalle"] as $item)
      {
        
          //query para traer los datos
          $sql= "SELECT 
                V_ExistenciaGral.cve_almac, 
                c_articulo.cve_articulo, 
                SUM(Existencia) Existencia_Total, 
                c_articulo.costoPromedio
                FROM `V_ExistenciaGral`
                LEFT JOIN c_articulo ON V_ExistenciaGral.cve_articulo = c_articulo.cve_articulo
                WHERE V_ExistenciaGral.cve_articulo = '".$item["cve_articulo"]."'
                GROUP BY V_ExistenciaGral.cve_articulo";
          $sth = \db()->prepare($sql);
          $sth->execute();
          $cosulta = $sth->fetch();
        
          $Existencias_Piezas = $cosulta["Existencia_Total"];
          $cantidad_entrada =  $item["recibida"];
          $Precio_Promedio = $cosulta["costoPromedio"];
          $costo_entrada = $item["costo"];

           //$costoPromedio = ($Existencias_Piezas + $cantidad_entrada / (($Existencias_Piezas * $Precio_Promedio)+($cantidad_entrada * $costo_entrada)) 

          //Simplex
          $cantidad_total = $Existencias_Piezas + $cantidad_entrada;
          $costo_almacen = $Existencias_Piezas * $Precio_Promedio;
          $costo_entrada = $cantidad_entrada * $costo_entrada;

          $costoPromedio = ($costo_almacen + $costo_entrada) / $cantidad_total;
        
        $sqlup ="UPDATE `c_articulo` SET costoPromedio = '".$costoPromedio."' WHERE cve_articulo = '".$item["cve_articulo"]."'";
        $sth = \db()->prepare($sqlup);
        $sth->execute();
        //echo var_dump($costoPromedio);
      }
      //echo var_dump($costoPromedio);
      //die();
      
    }
}


   

