<?php
include '../../../config.php';

error_reporting(0);

if(isset($_GET) && !empty($_GET)){
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $orden = $_GET['orden'];
  $sql = "SELECT IFNULL(Usr_Armo, '') AS usuario FROM t_ordenprod WHERE Folio_Pro = '$orden'";
  $query = mysqli_query($conn, $sql);
  $usuario = '';
  $compuesto = '';
  if($query->num_rows > 0){
    $usuario = mysqli_fetch_assoc($query)['usuario'];
  }
  $sql = "SELECT
                  o.Cve_Articulo AS clave,
                  a.des_articulo AS descripcion,
                  o.Cantidad AS cantidad, 
                  ai.url as imagen
          FROM t_ordenprod o
          LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
          LEFT JOIN c_articulo_imagen ai ON ai.cve_articulo = a.cve_articulo
          WHERE Folio_Pro = '$orden'
          LIMIT 1
          ";
  $query = mysqli_query($conn, $sql);
  if($query->num_rows > 0){
    $compuesto = array_map('utf8_encode', mysqli_fetch_assoc($query));
  }
  mysqli_close($conn);
  echo json_encode(array(
    "usuario" => utf8_encode($usuario),
    "compuesto" => $compuesto,
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
