<?php
include '../../../config.php';

error_reporting(0);

if(isset($_GET) && !empty($_GET)){
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $orden = $_GET['orden'];
  $sql = "SELECT IFNULL(Usr_Armo, '') AS usuario, IFNULL(Tipo, '') as Tipo, Cve_Articulo, IFNULL(id_zona_almac, '') AS id_zona_almac, IFNULL(idy_ubica, '') AS idy_ubica, IFNULL(idy_ubica_dest, '') AS idy_ubica_dest, cve_almac FROM t_ordenprod WHERE Folio_Pro = '$orden'";
  $query = mysqli_query($conn, $sql);
  $usuario = '';
  $Tipo = '';
  $Cve_Articulo = '';
  $compuesto = '';
  $id_zona_almac = '';
  $idy_ubica_dest = '';
  $id_almacen = '';
  if($query->num_rows > 0){
    $row = mysqli_fetch_assoc($query);
    $usuario        = $row['usuario'];
    $Tipo           = $row['Tipo'];
    $Cve_Articulo   = $row['Cve_Articulo'];
    $id_zona_almac  = $row['id_zona_almac'];
    $idy_ubica      = $row['idy_ubica'];
    $idy_ubica_dest = $row['idy_ubica_dest'];
    $id_almacen     = $row['cve_almac'];
  }

  $options_bl = ""; 

  $sql = "SELECT idy_ubica, CodigoCSD FROM c_ubicacion WHERE cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp IN (SELECT Cve_Almac FROM t_ordenprod WHERE Folio_Pro = '$orden')) AND AreaProduccion = 'S'";

  if($id_zona_almac)
  {
      $sql = "SELECT idy_ubica, CodigoCSD FROM c_ubicacion WHERE cve_almac = {$id_zona_almac} AND AreaProduccion = 'S'";
  }
  $query = mysqli_query($conn, $sql);
  $num_bls = mysqli_num_rows($query);
  if($num_bls > 1)
    $options_bl .= "<option value=''>Seleccione BL</option>";
  else if($num_bls == 0)
    $options_bl .= "<option value=''>No Hay BLs asignados a esta Zona</option>";


  while($bls_zona = array_map('utf8_encode', mysqli_fetch_assoc($query)))
  {
      $selected = "";
      if($idy_ubica == $bls_zona["idy_ubica"]) $selected = "selected";
      $options_bl .= "<option $selected value='".$bls_zona["idy_ubica"]."'>".$bls_zona["CodigoCSD"]."</option>";

      if($num_bls == 1)
      {
          $sql_bl_ot = "UPDATE t_ordenprod SET idy_ubica = '".$bls_zona["idy_ubica"]."' WHERE Folio_Pro = '$orden' ";
          $query_bl_ot = mysqli_query($conn, $sql_bl_ot);
      }
  }
//************************************************************************************************
  $options_bl_dest = ""; 

$sql = "SELECT  DISTINCT a.des_almac AS nombre_zona, a.cve_almac AS clave_zona, u.CodigoCSD, u.idy_ubica
FROM c_almacen a
LEFT JOIN c_ubicacion u ON u.cve_almac = a.cve_almac 
WHERE a.Activo = 1 AND u.Activo = 1 #AND u.picking = 'S'
AND a.cve_almacenp = {$id_almacen}";

  $query = mysqli_query($conn, $sql);
  $options_bl_dest .= "<option value=''>Seleccione BL</option>";

  while($row_dest = array_map('utf8_encode', mysqli_fetch_assoc($query)))
  {
      $selected = "";
      if($idy_ubica_dest == $row_dest["idy_ubica"]) $selected = "selected";
      $options_bl_dest .= "<option $selected value='".$row_dest["idy_ubica"]."'>".$row_dest["CodigoCSD"]."</option>";
  }

//************************************************************************************************
  $sql = "SELECT
                  o.Cve_Articulo AS clave,
                  a.des_articulo AS descripcion,
                  o.Cantidad AS cantidad, 
                  #IF(a.control_peso = 'S', CONCAT(TRUNCATE((SELECT SUM(Cantidad) FROM td_ordenprod WHERE Folio_Pro = o.Folio_Pro), 3), ''), (SELECT SUM(Cantidad) FROM td_ordenprod WHERE Folio_Pro = o.Folio_Pro)) AS cantidad,
                  a.control_peso,
                  a.control_lotes,
                  a.Caduca,
                  o.Cant_Prod,
                  IFNULL(l.Lote,'') AS Lote,
                  DATE_FORMAT(l.Caducidad, '%d-%m-%Y') AS Caducidad,
                  ai.url as imagen
          FROM t_ordenprod o
          LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
          LEFT JOIN c_articulo_imagen ai ON ai.cve_articulo = a.cve_articulo
          LEFT JOIN c_lotes l ON l.cve_articulo = a.cve_articulo AND o.Cve_Lote = l.Lote
          WHERE o.Folio_Pro = '$orden'
          LIMIT 1
          ";
  $query = mysqli_query($conn, $sql);
  if($query->num_rows > 0){
    $compuesto = array_map('utf8_encode', mysqli_fetch_assoc($query));
  }
  mysqli_close($conn);
  echo json_encode(array(
    "usuario" => utf8_encode($usuario),
    "Tipo" => $Tipo,
    "Cve_Articulo" => $Cve_Articulo,
    "compuesto" => $compuesto,
    "options_bl" => $options_bl,
    "options_bl_dest" => $options_bl_dest,
    "sql" => $sql
  ));
}
if(isset($_POST) && !empty($_POST)){
  $sql = '';
  $orden = $_POST['folio'];
  $usuario = $_POST['usuario'];
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $articulos = $_POST['articulos'];
  $articulosTotal = count($articulos) - 1;
  $claveProduccion = $articulos[$articulosTotal]['clave'];
  $loteProduccion = $articulos[$articulosTotal]['lote'];
  $caducidadProduccion = $articulos[$articulosTotal]['caducidad'];

  foreach($articulos as $key => $value){
    if($key === $articulosTotal){
      break;
    }
    extract($value);
    $sql .= "UPDATE td_ordenprod SET Cve_Lote = '$lote' WHERE Folio_Pro = '$folio' AND Cve_Articulo = '$clave'; ";
    if(!empty($lote)){
        $sql .= "INSERT INTO c_lotes (cve_articulo, LOTE, CADUCIDAD, Activo) VALUES ('$clave', '$lote', '$caducidad', '1') ON DUPLICATE KEY UPDATE CADUCIDAD = '$caducidad'; ";
    }
  }
  $sql .= "INSERT INTO c_lotes (cve_articulo, LOTE, CADUCIDAD, Activo) VALUES ('$claveProduccion', '$loteProduccion', '$caducidadProduccion', '1') ON DUPLICATE KEY UPDATE CADUCIDAD = '$caducidadProduccion'; ";
  $sql .= "UPDATE t_ordenprod SET Usr_Armo = '$usuario', Cve_Lote = '$loteProduccion', Status = 'T', Hora_Ini = NOW() WHERE Folio_Pro = '$orden'; ";

  $query = mysqli_multi_query($conn, $sql);

  
  echo json_encode(array(
    "success" => $query
  ));
}
?>
