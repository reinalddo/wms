<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) 
{
  $page = $_POST['page']; // get the requested page
  $limit = $_POST['rows']; // get how many rows we want to have into the grid
  $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
  $sord = $_POST['sord']; // get the direction
  $fecha_inicio= $_POST['fechaInicio'];
  $fecha_fin =$_POST['fechaFin'];

  //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
  $_criterio = $_POST['criterio'];
  $almacen = $_POST['almacen'];

  ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  if (!empty($fecha_fin)) $fecha_inicio = date("d/m/Y", strtotime($fecha_inicio));
  if (!empty($fecha_fin)) $fecha_fin = date("d/m/Y", strtotime($fecha_fin));

  if ($fecha_inicio && $fecha_fin)
  {
    $split= " and str_to_date(l.CADUCIDAD, '%d-%m-%Y') >=  str_to_date('$fecha_inicio', '%d/%m/%Y') and str_to_date(l.CADUCIDAD, '%d-%m-%Y') <= str_to_date('$fecha_fin', '%d/%m/%Y') ";
  }

  $start = $limit*$page - $limit; // do not put $limit*($page - 1)

  if(!$sidx) $sidx =1;

  // se conecta a la base de datos
/*
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  // prepara la llamada al procedimiento almacenado Lis_Facturas
  $sqlCount = "
    Select 
      COUNT(*) as total 
    FROM c_lotes l, c_articulo a 
    WHERE a.cve_articulo=l.cve_articulo
    AND (l.LOTE like '%".$_criterio."%' or a.cve_articulo like '%".$_criterio."%' or a.des_articulo like '%".$_criterio."%')
    AND a.cve_almac='$almacen'
    AND l.Activo = '1' $split
  ";
  if (!($res = mysqli_query($conn, $sqlCount))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

  $row = mysqli_fetch_array($res);
  $count = $row['total'];

  mysqli_close();
*/
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $_page = 0;

  if(intval($page)>0) {$_page = ($page-1)*$limit;}

  $sql = "
    SELECT 
      l.LOTE,
      a.cve_articulo,
      a.des_articulo,
      DATE_FORMAT(l.CADUCIDAD, '%d-%m-%Y') AS CADUCIDAD,
      a.control_lotes,
      a.Caduca
    FROM c_lotes l, c_articulo a 
    LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = a.cve_articulo
    WHERE a.cve_articulo = l.cve_articulo
    AND a.control_lotes = 'S'
    AND (l.LOTE like '%".$_criterio."%' or a.cve_articulo like '%".$_criterio."%' or a.des_articulo like '%".$_criterio."%')
    AND ra.Cve_Almac='$almacen'
    AND l.Activo = '1' $split
    ORDER BY a.cve_articulo 
  ";

  // hace una llamada previa al procedimiento almacenado Lis_Facturas
  if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
  $count = mysqli_num_rows($res);

  $sql .= " LIMIT $_page, $limit; ";
  if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

  if( $count >0 ) 
  {
    $total_pages = ceil($count/$limit);
  } 
  else 
  {
    $total_pages = 0;
  } 
  if ($page > $total_pages)
  {
    $page=$total_pages;
  }

  $responce["page"] = $page;
  $responce["total"] = $total_pages;
  $responce["records"] = $count;
  $responce["sql"] = $sql;

  
  $articulo = "";
  $caduca = "";
  $arr = array();
  $i = 0;
  while ($row = mysqli_fetch_array($res)) 
  {
    if($row["Caduca"] == 'N')
    {
      $row['CADUCIDAD'] = 'No Aplica';
    }
    else
    {
      $row['CADUCIDAD'] =  date('d-m-Y', strtotime($row["CADUCIDAD"]));
    }
    
    
    $arr[] = $row;
    $responce["rows"][$i]['id']=$row['cve_articulo'];
    $responce["rows"][$i]['cell']=array(
                                        $row['LOTE'],
                                        '',
                                        $row['cve_articulo'], 
                                        utf8_encode($row['des_articulo']), 
                                        $row['LOTE'],
                                        utf8_encode($row['CADUCIDAD'])
                                      );
    $i++;
  }
  echo json_encode($responce);
}

if(isset($_GET) && !empty($_GET) && !empty($_GET['cve_articulo']))
{
  $clave = $_GET['cve_articulo'];
  $sql = "SELECT LOTE FROM c_lotes WHERE cve_articulo = '$clave'";
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $query = mysqli_query($conn, $sql) or die (mysqli_error(\db2()));
  if($query->num_rows >0)
  {
    $data= [];
    while($row = mysqli_fetch_array($query))
    {
      $data [] = $row['LOTE'];
    }
  }
  mysqli_close();
  echo json_encode($data);
}