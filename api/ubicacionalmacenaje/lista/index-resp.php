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
    $_almacen = $_POST['almacen'];
    $sql = 'SELECT * FROM c_almacenp WHERE clave = "'.$_almacen.'"';
    $result = getArraySQL($sql);
    $responce["bl"] = $result[0]["BL"];

    if(isset($_POST['zone']))
    {
        if(!empty($_POST['zone']))
        {
            $split.= " and c_almacen.cve_almac='".$_POST['zone']."'";
        }
    }
  
    if(isset($_POST['BL']))
    {
        if(!empty($_POST['BL']))
        {
            $split.= "and c_ubicacion.CodigoCSD='".$_POST['BL']."'";
        }
    }
  
    if(isset($_POST['vacio']))
    {
        if($_POST['vacio'] == "true")
        {
            $split.= "and (SELECT 
                    count(ts_existenciapiezas.cve_articulo) 
                FROM c_ubicacion u
                INNER JOIN ts_existenciapiezas on ts_existenciapiezas.idy_ubica = u.idy_ubica
                where u.idy_ubica = c_ubicacion.idy_ubica) = 0";
        }
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
        }
    }

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)
    if(!$sidx) $sidx =1;
  
    $condicion="";
    if($_criterio != "")
    {
      $condicion=" and (c_ubicacion.CodigoCSD like '%".$_criterio."%' or c_almacen.des_almac like '%".$_criterio."%')";
    }

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "
        SELECT
            COUNT(*) as total
        FROM c_almacenp, c_ubicacion, c_almacen
        where
        (
            c_ubicacion.Ubicacion like '%".$_criterio."%'
            or c_almacen.des_almac like '%".$_criterio."%'
            or c_ubicacion.cve_pasillo like '%".$_criterio."%'
            or c_ubicacion.cve_rack like '%".$_criterio."%'
        )
            ".$condicion."
            and c_ubicacion.cve_almac=c_almacen.cve_almac
            and c_almacen.cve_almacenp=c_almacenp.id
            and c_almacenp.clave='".$_almacen."'
            and c_ubicacion.cve_almac = c_almacen.cve_almac
            and c_ubicacion.Activo = '1' $split
        order by c_ubicacion.CodigoCSD
    ";

    if (!($res = mysqli_query($conn, $sqlCount))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];
    mysqli_close();
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  	$_page = 0;

	  if (intval($page)>0) $_page = ($page-1)*$limit;//
  
	  $sql = "
        SELECT
            c_ubicacion.idy_ubica,
            c_ubicacion.cve_almac,
            c_ubicacion.cve_pasillo,
            c_ubicacion.cve_rack,
            c_ubicacion.cve_nivel,
            c_ubicacion.num_ancho,
            c_ubicacion.num_largo,
            c_ubicacion.num_alto,
            c_ubicacion.AcomodoMixto,
            c_ubicacion.picking,
            if(c_ubicacion.TECNOLOGIA='PTL','S','N') as Ptl,
            c_ubicacion.Maximo,
            c_ubicacion.Minimo,
            c_ubicacion.Seccion,
            c_ubicacion.Ubicacion,
            c_ubicacion.PesoMaximo,
            TRUNCATE((
                SELECT 
                    count(ts_existenciapiezas.cve_articulo) 
                FROM c_ubicacion u
                INNER JOIN ts_existenciapiezas on ts_existenciapiezas.idy_ubica = u.idy_ubica
                where u.idy_ubica = c_ubicacion.idy_ubica),4
            )as ubicados,
            TRUNCATE((
                SELECT 
                    ((SUM(c_articulo.peso * ts_existenciapiezas.Existencia )*100)/u.PesoMaximo) as porcentaje_peso 
                FROM ts_existenciapiezas 
                INNER JOIN c_articulo on c_articulo.cve_articulo = ts_existenciapiezas.cve_articulo
                inner join c_ubicacion u on u.idy_ubica = ts_existenciapiezas.idy_ubica
                WHERE ts_existenciapiezas.idy_ubica = c_ubicacion.idy_ubica
                group by ts_existenciapiezas.idy_ubica),4
            ) AS pes_porcentaje,
            TRUNCATE((
                SELECT 
                ((sum(ts_existenciapiezas.Existencia*((c_articulo.alto/1000)*(c_articulo.fondo/1000)*(c_articulo.ancho/1000)))*100)/((u.num_ancho/1000)*(u.num_alto/1000)*(u.num_largo/1000))) as volumen_porcentaje 
                from ts_existenciapiezas
                inner join c_articulo on c_articulo.cve_articulo = ts_existenciapiezas.cve_articulo
                inner join c_ubicacion u on u.idy_ubica = ts_existenciapiezas.idy_ubica
                where ts_existenciapiezas.idy_ubica = c_ubicacion.idy_ubica
                group by ts_existenciapiezas.idy_ubica),4
            )as vol_porcentaje,
            IFNULL(TRUNCATE((c_ubicacion.num_ancho / 1000) * (c_ubicacion.num_alto / 1000) * (c_ubicacion.num_largo / 1000), 2), 0) as volumen,
            c_ubicacion.CodigoCSD,
            c_ubicacion.TECNOLOGIA,
            c_ubicacion.Activo,
            c_almacen.cve_almac,
            c_almacen.des_almac,
            if(c_ubicacion.Tipo='L','S','N') as li,
            if(c_ubicacion.Tipo='R','S','N') as re,
            if(c_ubicacion.Tipo='Q','S','N') as cu
        FROM c_almacenp, c_ubicacion, c_almacen
        where 1
            ".$condicion."
            and c_ubicacion.cve_almac=c_almacen.cve_almac
            and c_almacen.cve_almacenp=c_almacenp.id
            and c_almacenp.clave='".$_almacen."'
            and c_ubicacion.cve_almac = c_almacen.cve_almac
            and c_ubicacion.Activo = '1' ".$split."
        order by c_ubicacion.CodigoCSD DESC
        LIMIT $_page, $limit;
    ";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

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
    //$responce["query"]=$sql;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $arr[] = $row;
    $row['cve_rack']='0'.$row['cve_rack'];
    $row["dim"] = number_format($row['num_alto'], 2, '.', '').'  X  '.number_format($row['num_ancho'], 2, '.', '').'  X  '.number_format($row['num_largo'], 2, '.', '');

    $responce["rows"][$i]['id']=$row['idy_ubica'];
    $responce["rows"][$i]['cell']=array(
        $row['cve_almac'],
        $row['idy_ubica'],
        '',
        $row['CodigoCSD'],//noob
        utf8_encode($row['des_almac']),
        $row['ubicados'],
        utf8_encode($row['cve_pasillo']),
        $row['cve_rack'],
        $row['cve_nivel'],
        $row['Seccion'],
        $row['Ubicacion'],
        $row['PesoMaximo'],
        $row['volumen'],
        $row['pes_porcentaje'],
        $row['vol_porcentaje'],
        $row["dim"],
        utf8_encode($row['picking']),
        utf8_encode($row['Ptl']),
        utf8_encode($row['li']),
        utf8_encode($row['re']),
        utf8_encode($row['cu']),
        $row['AcomodoMixto'],
        $row['Maximo'],
        $row['Minimo']
      );
      $i++;
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
  $cve_almacen = $_GET['almacen'];
  $start = $limit*$page - $limit; // do not put $limit*($page - 1)
  
  $_page = 0;
  if (intval($page)>0) $_page = ($page-1)*$limit;
 

  if(!$sidx) $sidx =1;

  // se conecta a la base de datos
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  
  $sql_almacen = "SELECT id FROM c_almacenp WHERE clave = '{$cve_almacen}'";
  if (!($res = mysqli_query($conn, $sql_almacen))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
  $almacen = mysqli_fetch_array($res)['id'];
  
  $sqlCount = "
    SELECT 
        V_ExistenciaGral.cve_articulo  
    FROM V_ExistenciaGral 
    LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo
    LEFT JOIN c_lotes ON c_lotes.LOTE = V_ExistenciaGral.cve_lote and c_lotes.cve_articulo = c_articulo.cve_articulo
    WHERE cve_ubicacion = '{$cve_ubicacion}' 
        AND V_ExistenciaGral.cve_almac = '{$almacen}' 
        AND V_ExistenciaGral.Existencia IS NOT NULL 
    GROUP BY V_ExistenciaGral.cve_articulo,V_ExistenciaGral.cve_lote
  ";
  if (!($res = mysqli_query($conn, $sqlCount))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
  $dt = mysqli_fetch_all($res);
  $count = (count($dt));
 
//EDG
  $sql = 
  "
    SELECT 
      V_ExistenciaGral.cve_almac, 
      V_ExistenciaGral.cve_ubicacion, 
      V_ExistenciaGral.cve_articulo, 
      if(c_articulo.control_lotes = 'S',c_lotes.LOTE,'') as lote,
      if(c_articulo.control_lotes = 'S',c_lotes.Caducidad,'') as caducidad,
      if(c_articulo.control_numero_series = 'S',c_lotes.LOTE,'') as serie,
      SUM(V_ExistenciaGral.Existencia) as Existencia_Total,
      c_articulo.des_articulo as descripcion,
      TRUNCATE((c_articulo.peso),4) as peso_unitario,
      TRUNCATE(((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000),4) as volumen_unitario,
      TRUNCATE((c_articulo.peso*SUM(V_ExistenciaGral.Existencia)),4) as peso_total,
      TRUNCATE((((c_articulo.alto*c_articulo.ancho*c_articulo.fondo)/1000000000)*SUM(V_ExistenciaGral.Existencia)),4)as volumen_total
    FROM V_ExistenciaGral 
    LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaGral.cve_articulo
    LEFT JOIN c_lotes ON c_lotes.LOTE = V_ExistenciaGral.cve_lote and c_lotes.cve_articulo = c_articulo.cve_articulo
    WHERE cve_ubicacion = '{$cve_ubicacion}' 
    AND V_ExistenciaGral.cve_almac = '{$almacen}' 
    AND V_ExistenciaGral.Existencia IS NOT NULL 
    GROUP BY V_ExistenciaGral.cve_articulo,V_ExistenciaGral.cve_lote
    LIMIT $_page, $limit;
  ";
  if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

  //echo var_dump($page,$limit);
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
    $responce->rows[$i]['cell']=array('',$row['cve_articulo'], $row['descripcion'], $row['lote'], $row['caducidad'],$row['serie'], $row['Existencia_Total'],$row['peso_unitario'],$row['volumen_unitario'], $row['peso_total'], $row['volumen_total']);
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

