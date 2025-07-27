<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST))
{
    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $responce = array();
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $split = "";

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];
    $_pallet_contenedor = $_POST['pallet_contenedor'];
    $_lp = $_POST['lp'];
    $id_proveedor = $_POST['id_proveedor'];
    //$BL_selected = $_POST['BL'];

    $_almacen = $_POST['almacen'];
    $sql = 'SELECT * FROM c_almacenp WHERE clave = "'.$_almacen.'"';
    $result = getArraySQL($sql);
    $responce["bl"] = $result[0]["BL"];
    $id_almacen = $result[0]["id"];

    if(isset($_POST['zone']))
    {
        if(!empty($_POST['zone']))
        {
            $split.= " and c_almacen.cve_almac='".$_POST['zone']."'";
        }
    }
  $condicion_ubicados_t = "";
    if(isset($_POST['BL']))
    {
        if(!empty($_POST['BL']))
        {
            //$split.= "and c_ubicacion.CodigoCSD='".$_POST['BL']."'";
            $condicion_ubicados_t .= " and t.CodigoCSD='".$_POST['BL']."' ";
        }
    }
  
    if(isset($_POST['RACK']))
    {
        if(!empty($_POST['RACK']))
        {
            $split.= "and c_ubicacion.cve_rack='".$_POST['RACK']."'";
        }
    }

    $ok_empresa = false;
    if(isset($_POST['vacio']))
    {
        if($_POST['vacio'] != "0")
        {
          if($_POST['vacio'] == "1") //Con Existencia
          {
             $split.= " AND ((c_ubicacion.idy_ubica IN (SELECT idy_ubica FROM ts_existenciapiezas))
                        OR  (c_ubicacion.idy_ubica IN (SELECT idy_ubica FROM ts_existenciatarima))
                        OR  (c_ubicacion.idy_ubica IN (SELECT idy_ubica FROM ts_existenciacajas))) 
                        AND ve.cve_almac = '$id_almacen' ";
             $ok_empresa = true;
          }
          else //Sin Existencia
          {
             $split.= " AND (c_ubicacion.idy_ubica NOT IN (SELECT idy_ubica FROM ts_existenciapiezas WHERE cve_almac = '$id_almacen' ))
                        AND (c_ubicacion.idy_ubica NOT IN (SELECT idy_ubica FROM ts_existenciatarima WHERE cve_almac = '$id_almacen' ))
                        AND (c_ubicacion.idy_ubica NOT IN (SELECT idy_ubica FROM ts_existenciacajas WHERE Cve_Almac = '$id_almacen' )) ";
            $ok_empresa = false;
          }

        }
    }

    $orden_bl = " ORDER BY ISNULL(sec_surtido), sec_surtido ASC ";
    if(isset($_POST['orden_bl']))
    {
        if($_POST['orden_bl'] == "1")
           $orden_bl = " ORDER BY t.CodigoCSD ASC ";
    }

    if(isset($_POST['tipo']))
    {
        if(!empty($_POST['tipo']))
        {
            if($_POST['tipo'] == "L" || $_POST['tipo'] == "R" || $_POST['tipo'] == "Q")
              $split.= " and c_ubicacion.Tipo='".$_POST['tipo']."'";
            if($_POST['tipo'] == "Picking")
              $split.= " and c_ubicacion.picking = 'S'";
            if($_POST['tipo'] == "PTL")
              $split.= " and c_ubicacion.TECNOLOGIA = '".$_POST['tipo']."'";
            if($_POST['tipo'] == "Mixto")
              $split.= " and c_ubicacion.AcomodoMixto = 'S'";
            if($_POST['tipo'] == "Produccion")
              $split.= " and c_ubicacion.AreaProduccion = 'S'";
            if($_POST['tipo'] == "Reabasto")
              $split.= " and c_ubicacion.Reabasto = 'S'";
        }
    }

    $sql_proveedor = "";
    if(isset($_POST['cve_proveedor']) && $ok_empresa == true)
    {
        $cve_proveedor = $_POST['cve_proveedor'];
        if($cve_proveedor != '')
            $sql_proveedor = " AND (t.Id_Proveedor = '{$cve_proveedor}') ";// OR t.Id_Proveedor IS NULL OR t.Id_Proveedor = ''
    }

  if(!empty($id_proveedor) && $ok_empresa == true){
        $sql_proveedor = " AND (t.Id_Proveedor = '{$id_proveedor}') ";// OR t.Id_Proveedor IS NULL OR t.Id_Proveedor = ''
    }

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)
    if(!$sidx) $sidx =1;
  
    $condicion="";
    if($_criterio != "")
    {
      $condicion.=" and (c_ubicacion.CodigoCSD like '%".$_criterio."%' or c_almacen.des_almac like '%".$_criterio."%' OR ch.clave_contenedor LIKE '%".$_criterio."%' OR ch.CveLP LIKE '%".$_criterio."%') ";
    }

    $condicion_ubicados_0 = "";
    if($_pallet_contenedor != "")
    {
      $condicion_ubicados_0.=" and (ch.clave_contenedor like '%".$_pallet_contenedor."%')";
      $condicion_ubicados_t.=" and (t.clave_contenedor like '%".$_pallet_contenedor."%') ";
    }

    if($_lp != "")
    {
      $condicion_ubicados_0.=" and (ch.CveLP like '%".$_lp."%')";
      $condicion_ubicados_t.=" and (t.CveLP like '%".$_lp."%') ";
    }

    // prepara la llamada al procedimiento almacenado Lis_Facturas
/*
    $sqlCount = "
        SELECT
            COUNT(*) as total
        FROM c_almacenp, c_ubicacion, c_almacen, V_ExistenciaGralProduccion ex
        LEFT JOIN c_charolas ch ON ch.clave_contenedor = ex.Cve_Contenedor
        where
        (
            c_ubicacion.Ubicacion like '%".$_criterio."%'
            or c_almacen.des_almac like '%".$_criterio."%'
            or c_ubicacion.cve_pasillo like '%".$_criterio."%'
            or c_ubicacion.cve_rack like '%".$_criterio."%'
        )
            ".$condicion.$condicion_ubicados_0."
            and c_ubicacion.cve_almac=c_almacen.cve_almac
            and c_almacen.cve_almacenp=c_almacenp.id
            and c_almacenp.clave='".$_almacen."'
            and c_ubicacion.cve_almac = c_almacen.cve_almac
            and c_ubicacion.Activo = '1' $split
            AND ex.cve_ubicacion = c_ubicacion.idy_ubica
            AND ((IFNULL(ch.clave_contenedor, '') != '' AND IFNULL(ch.CveLP, '') != '') OR (IFNULL(ch.clave_contenedor, '') = '' AND IFNULL(ch.CveLP, '') = ''))
        order by c_ubicacion.CodigoCSD
    ";
*/
/*
    $sqlCount = "
        SELECT * FROM (
        SELECT DISTINCT
    COUNT(*) as total
    FROM c_almacenp, c_ubicacion, c_almacen
        WHERE 1
            ".$condicion.$condicion_ubicados_0."
            AND c_ubicacion.cve_almac=c_almacen.cve_almac
            AND c_almacen.cve_almacenp=c_almacenp.id
            AND c_almacenp.clave='100'
            AND c_ubicacion.cve_almac = c_almacen.cve_almac
            AND c_ubicacion.Activo = '1' 
        ) AS t 
    ";

    if (!($res = mysqli_query($conn, $sqlCount))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];
    mysqli_close();
*/
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res)['charset'];
    mysqli_set_charset($conn , $charset);


  	$_page = 0;

	  if (intval($page)>0) $_page = ($page-1)*$limit;//

    $sql = "SELECT * FROM (
        SELECT DISTINCT
            c_ubicacion.idy_ubica,
            c_ubicacion.cve_almac,
            GROUP_CONCAT(DISTINCT ch.clave_contenedor SEPARATOR ',') AS clave_contenedor,
            GROUP_CONCAT(DISTINCT ch.CveLP SEPARATOR ',') AS CveLP,
            c_ubicacion.cve_pasillo,
            c_ubicacion.cve_rack,
            c_ubicacion.cve_nivel,
            c_ubicacion.num_ancho,
            c_ubicacion.num_largo,
            c_ubicacion.num_alto,
            c_ubicacion.AcomodoMixto,
            c_ubicacion.AreaProduccion,
            IFNULL(c_ubicacion.AreaStagging, 'N') AS AreaStagging,
            c_ubicacion.picking,
            if(c_ubicacion.TECNOLOGIA='PTL','S','N') as Ptl,
            c_ubicacion.Maximo,
            c_ubicacion.Minimo,
            c_ubicacion.Seccion,
            c_ubicacion.Ubicacion,
            c_ubicacion.PesoMaximo,

            #TRUNCATE((
            #    SELECT 
            #        COUNT(DISTINCT ts_existenciapiezas.cve_articulo) 
            #    FROM c_ubicacion u
            #    INNER JOIN ts_existenciapiezas ON ts_existenciapiezas.idy_ubica = u.idy_ubica AND ts_existenciapiezas.Existencia > 0
            #    WHERE u.idy_ubica = c_ubicacion.idy_ubica) + 
            #    (
            #    SELECT 
            #        COUNT(DISTINCT ts_existenciacajas.cve_articulo) 
            #    FROM c_ubicacion u
            #    INNER JOIN ts_existenciacajas ON ts_existenciacajas.idy_ubica = u.idy_ubica AND ts_existenciacajas.Existencia > 0
            #    WHERE u.idy_ubica = c_ubicacion.idy_ubica) + 
            #    (
            #    SELECT 
            #        COUNT(DISTINCT ts_existenciatarima.cve_articulo) 
            #    FROM c_ubicacion u
            #    INNER JOIN ts_existenciatarima ON ts_existenciatarima.idy_ubica = u.idy_ubica AND ts_existenciatarima.Existencia > 0
            #    WHERE u.idy_ubica = c_ubicacion.idy_ubica),0
            #)AS ubicados,

            #(SELECT COUNT(DISTINCT cve_articulo) FROM V_ExistenciaGral WHERE cve_ubicacion = c_ubicacion.idy_ubica AND Existencia > 0) AS ubicados,

            #TRUNCATE(IFNULL((
                #SELECT 
                    #((SUM(c_articulo.peso * ts_existenciapiezas.Existencia )*100)/u.PesoMaximo) AS porcentaje_peso 
                #FROM ts_existenciapiezas 
                #INNER JOIN c_articulo ON c_articulo.cve_articulo = ts_existenciapiezas.cve_articulo
                #INNER JOIN c_ubicacion u ON u.idy_ubica = ts_existenciapiezas.idy_ubica
                #WHERE ts_existenciapiezas.idy_ubica = c_ubicacion.idy_ubica
                #GROUP BY ts_existenciapiezas.idy_ubica), 0) + 
                #IFNULL((
                #SELECT 
                    #((SUM(c_articulo.peso * ts_existenciacajas.Existencia )*100)/u.PesoMaximo) AS porcentaje_peso 
                #FROM ts_existenciacajas 
                #INNER JOIN c_articulo ON c_articulo.cve_articulo = ts_existenciacajas.cve_articulo
                #INNER JOIN c_ubicacion u ON u.idy_ubica = ts_existenciacajas.idy_ubica
                #WHERE ts_existenciacajas.idy_ubica = c_ubicacion.idy_ubica
                #GROUP BY ts_existenciacajas.idy_ubica), 0) + 
                #IFNULL((
                #SELECT 
                    #((SUM(c_articulo.peso * ts_existenciatarima.Existencia )*100)/u.PesoMaximo) AS porcentaje_peso 
                #FROM ts_existenciatarima 
                #INNER JOIN c_articulo ON c_articulo.cve_articulo = ts_existenciatarima.cve_articulo
                #INNER JOIN c_ubicacion u ON u.idy_ubica = ts_existenciatarima.idy_ubica
                #WHERE ts_existenciatarima.idy_ubica = c_ubicacion.idy_ubica
                #GROUP BY ts_existenciatarima.idy_ubica), 0) ,4
            #) AS pes_porcentaje,
            #TRUNCATE(
            #IFNULL((
                #SELECT 
#                #((SUM(ts_existenciapiezas.Existencia*((c_articulo.alto/1000)*(c_articulo.fondo/1000)*(c_articulo.ancho/1000)))*100)/((u.num_ancho/1000)*(u.num_alto/1000)*(u.num_largo/1000))) AS volumen_porcentaje 
                #FROM ts_existenciapiezas
                #INNER JOIN c_articulo ON c_articulo.cve_articulo = ts_existenciapiezas.cve_articulo
                #INNER JOIN c_ubicacion u ON u.idy_ubica = ts_existenciapiezas.idy_ubica
                #WHERE ts_existenciapiezas.idy_ubica = c_ubicacion.idy_ubica
                #GROUP BY ts_existenciapiezas.idy_ubica), 0) + 
                #IFNULL((
                #SELECT 
#                #((SUM(ts_existenciacajas.Existencia*((c_articulo.alto/1000)*(c_articulo.fondo/1000)*(c_articulo.ancho/1000)))*100)/((u.num_ancho/1000)*(u.num_alto/1000)*(u.num_largo/1000))) AS volumen_porcentaje 
                #FROM ts_existenciacajas
                #INNER JOIN c_articulo ON c_articulo.cve_articulo = ts_existenciacajas.cve_articulo
                #INNER JOIN c_ubicacion u ON u.idy_ubica = ts_existenciacajas.idy_ubica
                #WHERE ts_existenciacajas.idy_ubica = c_ubicacion.idy_ubica
                #GROUP BY ts_existenciacajas.idy_ubica), 0) + 
                #IFNULL((
                #SELECT 
#                #((SUM(ts_existenciatarima.Existencia*((c_articulo.alto/1000)*(c_articulo.fondo/1000)*(c_articulo.ancho/1000)))*100)/((u.num_ancho/1000)*(u.num_alto/1000)*(u.num_largo/1000))) AS volumen_porcentaje 
                #FROM ts_existenciatarima
                #INNER JOIN c_articulo ON c_articulo.cve_articulo = ts_existenciatarima.cve_articulo
                #INNER JOIN c_ubicacion u ON u.idy_ubica = ts_existenciatarima.idy_ubica
                #WHERE ts_existenciatarima.idy_ubica = c_ubicacion.idy_ubica
                #GROUP BY ts_existenciatarima.idy_ubica), 0),4
            #)AS vol_porcentaje,
            IFNULL(c_ubicacion.Status, 'N') AS Status,
            IFNULL(TRUNCATE((c_ubicacion.num_ancho / 1000) * (c_ubicacion.num_alto / 1000) * (c_ubicacion.num_largo / 1000), 8), 0) as volumen,
            c_ubicacion.CodigoCSD,
            c_ubicacion.TECNOLOGIA,
            c_ubicacion.Activo,
            IFNULL(c_almacen.clasif_abc, '') as clasif_abc,
            c_almacen.des_almac,
            ve.Id_Proveedor,
            IFNULL(thrs.nombre, '') AS ruta_surtido,
            IFNULL(rs.orden_secuencia, '') AS sec_surtido,
            if(c_ubicacion.Tipo='L','S','N') as li,
            if(c_ubicacion.Tipo='R','S','N') as re,
            if(c_ubicacion.Tipo='Q','S','N') as cu,
            IF(IFNULL(ve.cve_ubicacion, '') = '', 0, 1) AS tieneexistencia
        FROM c_ubicacion
        LEFT JOIN V_ExistenciaGralProduccion ve ON ve.cve_ubicacion = c_ubicacion.idy_ubica
        LEFT JOIN c_charolas ch ON ch.clave_contenedor = ve.Cve_Contenedor
        LEFT JOIN c_almacen ON c_ubicacion.cve_almac = c_almacen.cve_almac
        LEFT JOIN c_almacenp ON c_almacen.cve_almacenp=c_almacenp.id
        LEFT JOIN td_ruta_surtido rs ON rs.idy_ubica = c_ubicacion.idy_ubica AND rs.Activo = 1
        LEFT JOIN th_ruta_surtido thrs ON thrs.idr = rs.idr AND thrs.Activo = 1
        where 1
            ".$condicion."
            ".$condicion_ubicados_0."
            and c_ubicacion.cve_almac=c_almacen.cve_almac
            and c_almacen.cve_almacenp=c_almacenp.id
            and c_almacenp.clave='".$_almacen."'
            and c_ubicacion.cve_almac = c_almacen.cve_almac
            and c_ubicacion.Activo = '1' ".$split."
            GROUP BY CodigoCSD, cve_almac
            order by c_ubicacion.CodigoCSD DESC
        ) AS t WHERE 1 {$sql_proveedor} {$condicion_ubicados_t} 
        
        {$orden_bl}";
        //#WHERE t.ubicados = 0 

    $sql_cuenta = $sql;
    //if(!$condicion_ubicados_t)
    $sql .= " LIMIT $_page, $limit;";
    //c_almacen.cve_almac,

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if (!($res_cuenta = mysqli_query($conn, $sql_cuenta))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res_cuenta);

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce["page"] = $page;
    $responce["total"] = $total_pages;
    $responce["records"] = $count;
    $responce["query"]=$sql;

    $arr = array();
    $i = 0;
    $idy_ubica_arr = array();
    while ($row = mysqli_fetch_array($res)) {
        $arr[] = $row;


    if(!in_array($row['idy_ubica'], $idy_ubica_arr))
    {
            array_push($idy_ubica_arr, $row['idy_ubica']);

            $row['cve_rack']='0'.$row['cve_rack'];
            //$row["dim"] = number_format($row['num_alto'], 2, '.', '').'  X  '.number_format($row['num_ancho'], 2, '.', '').'  X  '.number_format($row['num_largo'], 2, '.', '');

            $responce["rows"][$i]['id']=$row['idy_ubica'];
            $responce["rows"][$i]['cell']=array(
                $row['cve_almac'],
                $row['idy_ubica'],
                '',
                $row['sec_surtido'],
                utf8_encode($row['ruta_surtido']),
                $row['CodigoCSD'],
                $row['clasif_abc'],
                utf8_encode($row['des_almac']),
                $row['clave_contenedor'],
                $row['CveLP'],
                //$row['ubicados'],
                utf8_encode($row['cve_pasillo']),
                $row['cve_rack'],
                $row['cve_nivel'],
                $row['Seccion'],
                $row['Ubicacion'],
                $row['PesoMaximo'],
                $row['volumen'],
                //$row['pes_porcentaje'],
                //$row['vol_porcentaje'],
                //$row["dim"],
                $row['Tipo'],
                utf8_encode($row['picking']),
                utf8_encode($row['Ptl']),
                utf8_encode($row['li']),
                utf8_encode($row['re']),
                utf8_encode($row['cu']),
                $row['AcomodoMixto'],
                $row['AreaProduccion'],
                $row['AreaStagging'],
                $row['Status'],
                $row['Maximo'],
                $row['Minimo'], 
                $row['tieneexistencia']
                
              );
              $i++;
        }
    }
    echo json_encode($responce);
}


function getArraySQL($sql){

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    if(!$result = mysqli_query($conexion, $sql)) 
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";;

    $rawdata = array();

    $i = 0;

    while($row = mysqli_fetch_assoc($result))
    {
        $rawdata[$i] = $row;
        $i++;
    }

    mysqli_close($conexion);

    return $rawdata;
}

if (isset($_GET) && !empty($_GET) && $_GET['action'] === "getDetallesFolio") 
{
  $page = $_GET['page']; // get the requested page
  $limit = $_GET['rows']; // get how many rows we want to have into the grid
  $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
  $sord = $_GET['sord']; // get the direction
  $cve_ubicacion = $_GET['id'];
  //$CveLP = $_GET['CveLP'];
  $cve_almacen = $_GET['almacen'];
  $start = $limit*$page - $limit; // do not put $limit*($page - 1)
  
  $_page = 0;
  if (intval($page)>0) $_page = ($page-1)*$limit;
 

  if(!$sidx) $sidx =1;

  // se conecta a la base de datos
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  
  /*
  $sql_almacen = "SELECT id FROM c_almacenp WHERE clave = '{$cve_almacen}'";
  if (!($res = mysqli_query($conn, $sql_almacen))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
  $almacen = mysqli_fetch_array($res)['id'];

  $sqlCount = "
    SELECT 
        v.cve_articulo  
      FROM V_ExistenciaGral v
      LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
      LEFT JOIN c_lotes l ON l.cve_articulo = v.cve_articulo AND l.LOTE = v.cve_lote
      LEFT JOIN c_proveedores cp ON cp.ID_Proveedor = v.Id_Proveedor
      LEFT JOIN c_charolas ch ON ch.clave_contenedor = v.Cve_Contenedor
      WHERE v.cve_ubicacion = '{$cve_ubicacion}'
  ";
  if (!($res = mysqli_query($conn, $sqlCount))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
  $dt = mysqli_fetch_all($res);
  $count = (count($dt));
*/

  $sql_area = "SELECT AreaProduccion FROM c_ubicacion WHERE idy_ubica = '{$cve_ubicacion}'";
  if (!($res = mysqli_query($conn, $sql_area))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
  $AreaProduccion = mysqli_fetch_array($res)['AreaProduccion'];

  $sql_count = "SELECT  DISTINCT
        v.cve_almac,
        v.cve_ubicacion,
        v.cve_articulo,
        ch.clave_contenedor AS clave_contenedor,
        ch.CveLP AS LP,
        IF(a.control_lotes = 'S',v.cve_lote,'') AS lote,
        COALESCE(IF(a.control_lotes = 'S',IF(l.Caducidad = DATE_FORMAT('0000-00-00','%d-%m-%Y'),'',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
        IF(a.control_numero_series = 'S',v.cve_lote,'') AS serie,
    IFNULL(v.Existencia, 0) AS Existencia_Total,
        a.des_articulo AS descripcion,
        IFNULL(TRUNCATE(a.peso, 4), 0) AS peso_unitario,
    IFNULL(TRUNCATE(((a.alto*a.ancho*a.fondo)/1000000000), 8), 0) AS volumen_unitario,
        IFNULL(CAST((a.peso * v.Existencia) AS DECIMAL(10,2)), 0) AS peso_total,
        IFNULL(CAST(((a.alto / 1000) * (a.ancho / 1000) * (a.fondo / 1000) * v.Existencia) AS DECIMAL(10,6)), 0) AS volumen_total,
        v.ID_Proveedor,
        cp.Nombre AS proveedor,
        a.id AS id_articulo
      FROM V_ExistenciaGral v
      LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
      LEFT JOIN c_lotes l ON l.cve_articulo = v.cve_articulo AND l.LOTE = v.cve_lote
      LEFT JOIN c_proveedores cp ON cp.ID_Proveedor = v.Id_Proveedor
      LEFT JOIN c_charolas ch ON ch.clave_contenedor = v.Cve_Contenedor
      WHERE v.cve_ubicacion = '{$cve_ubicacion}'";

  if($AreaProduccion == "S")
  {
      $sql_count = "SELECT  DISTINCT
            v.cve_almac,
            v.cve_ubicacion,
            v.cve_articulo,
            ch.clave_contenedor AS clave_contenedor,
            ch.CveLP AS LP,
            IF(a.control_lotes = 'S',v.cve_lote,'') AS lote,
            COALESCE(IF(a.control_lotes = 'S',IF(l.Caducidad = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
            IF(a.control_numero_series = 'S',v.cve_lote,'') AS serie,
        IFNULL(v.Existencia, 0) AS Existencia_Total,
            a.des_articulo AS descripcion,
            IFNULL(TRUNCATE(a.peso, 4), 0) AS peso_unitario,
        IFNULL(TRUNCATE(((a.alto*a.ancho*a.fondo)/1000000000), 8), 0) AS volumen_unitario,
            IFNULL(CAST((a.peso * v.Existencia) AS DECIMAL(10,2)), 0) AS peso_total,
            IFNULL(CAST(((a.alto / 1000) * (a.ancho / 1000) * (a.fondo / 1000) * v.Existencia) AS DECIMAL(10,6)), 0) AS volumen_total,
            '' AS ID_Proveedor,
            '' AS proveedor,
            a.id AS id_articulo
          FROM V_ExistenciaGralProduccion v
          LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
          LEFT JOIN c_lotes l ON l.cve_articulo = v.cve_articulo AND l.LOTE = v.cve_lote
          LEFT JOIN c_charolas ch ON ch.clave_contenedor = v.Cve_Contenedor
          WHERE v.cve_ubicacion = '{$cve_ubicacion}'";
  }

  $sql = $sql_count." LIMIT $_page, $limit";

/* en FROM (
          SELECT cve_almac, cve_ubicacion, cve_articulo, cve_lote, Existencia FROM V_ExistenciaGral WHERE cve_ubicacion = '{$cve_ubicacion}'
          UNION SELECT cve_almac, cve_ubicacion, cve_articulo, cve_lote, Existencia FROM V_ExistenciaProduccion WHERE cve_ubicacion = '{$cve_ubicacion}'
*/

  //$dt = mysqli_fetch_all($res);
  //$count = (count($dt));
    $res_count = "";
  if (!($res_count = mysqli_query($conn, $sql_count))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

  $count = mysqli_num_rows($res_count);
  //$count = 45;
  //echo var_dump($page,$limit);
  //die();
  if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

  if($count >0 && $limit > 0) 
  {
    $total_pages = ceil($count/$limit);
    //$total_pages = ceil($count/1);
  } 
  else 
  {
    $total_pages = 0;
  } 
  if($page > $total_pages)
  {
    $page=$total_pages;
  }

  $responce->page = $page;
  $responce->total = $total_pages;
  $responce->records = $count;
  $responce->sql = $sql;

  $i = 0;
  while ($row = mysqli_fetch_array($res)) 
  {
    $row = array_map('utf8_encode', $row);
    $responce->rows[$i]['id']=$row['cve_articulo'];

    $responce->rows[$i]['cell']=array('',$row['cve_articulo'], $row['descripcion'], $row['clave_contenedor'], $row['LP'], $row['lote'], $row['caducidad'],$row['serie'], $row['Existencia_Total'],$row['peso_unitario'],$row['volumen_unitario'], $row['peso_total'], $row['volumen_total']);
    $i++;
  }
  echo json_encode($responce);
}

if (isset($_GET) && !empty($_GET) && $_GET['action'] === "traer_totales") 
{
  $page = $_GET['page']; // get the requested page
  $limit = $_GET['rows']; // get how many rows we want to have into the grid
  $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
  $sord = $_GET['sord']; // get the direction
  $cve_ubicacion = $_GET['id'];
  $cve_almacen = $_GET['almacen'];
  $start = $limit*$page - $limit; // do not put $limit*($page - 1)
  
 

  if(!$sidx) $sidx =1;

  // se conecta a la base de datos
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  
  $sql_almacen = "SELECT id FROM c_almacenp WHERE clave = '{$cve_almacen}'";
  if (!($res = mysqli_query($conn, $sql_almacen))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
  $almacen = mysqli_fetch_array($res)['id'];
  
  $sqlCount = "
    SELECT cve_articulo  
    FROM V_ExistenciaGral 
    WHERE cve_ubicacion = '{$cve_ubicacion}' 
    AND cve_almac = '{$almacen}' 
    AND Existencia IS NOT NULL 
    GROUP BY cve_articulo
  ";
  if (!($res = mysqli_query($conn, $sqlCount))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
  $dt = mysqli_fetch_all($res);
  $count = (count($dt));
 

  $sql = 
  "
    SELECT 
      V_ExistenciaGral.cve_almac, 
      V_ExistenciaGral.cve_ubicacion, 
      V_ExistenciaGral.cve_articulo, 
      SUM(V_ExistenciaGral.Existencia) as Existencia_Total,
      c_articulo.des_articulo as descripcion,
      c_articulo.peso as peso_unitario,
      ((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000) as volumen_unitario,
      (c_articulo.peso*SUM(V_ExistenciaGral.Existencia)) as peso_total,
      (((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000)*SUM(V_ExistenciaGral.Existencia)) as volumen_total
    FROM V_ExistenciaGral 
    LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo
    WHERE cve_ubicacion = '{$cve_ubicacion}' 
    AND V_ExistenciaGral.cve_almac = '{$almacen}' 
    AND V_ExistenciaGral.Existencia IS NOT NULL 
    GROUP BY cve_articulo
  ";
  if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

  //echo var_dump($start, $page, $limit);
  //die();
  if($count >0) 
  {
    $total_pages = ceil($count/$limit);
    //$total_pages = ceil($count/1);
  } 
  else 
  {
    $total_pages = 0;
  } 
  if($page > $total_pages)
  {
    $page=$total_pages;
  }

  $responce->page = $page;
  $responce->total = $total_pages;
  $responce->records = $count;

  $i = 0;
  while ($row = mysqli_fetch_array($res)) 
  {
    $row = array_map('utf8_encode', $row);
    $responce->rows[$i]['id']=$row['cve_articulo'];
    $responce->rows[$i]['cell']=array('',$row['cve_articulo'], $row['descripcion'], $row['Existencia_Total'],$row['peso_unitario'],$row['volumen_unitario'], $row['peso_total'], $row['volumen_total']);
    $i++;
  }
  echo json_encode($responce);
}

