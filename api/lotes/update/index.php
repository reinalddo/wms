<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Lotes\Lotes();

if( $_POST['action'] == 'add' ) 
{
  $ga->save($_POST);
  $arr = array(
    "success" => $success
  );
  echo json_encode($arr);
} 

if( $_POST['action'] == 'load2' ) 
{
  $data=$ga->load2($_POST["LOTE"],$_POST["cve_articulo"]);
  $arr = array(
    "success" => true,
    "cve_articulo" => $data["cve_articulo"],
    "LOTE" => $data["LOTE"],
    "CADUCIDAD" => $data["Caducidad"]
  );

  echo json_encode($arr);
}

if( $_POST['action'] == 'edit' ) 
{
  $ga->actualizarLotes($_POST);
  $arr = array(
    "success" => $success
  );
  echo json_encode($arr);
} 

if( $_POST['action'] == 'delete' ) {
    $ga->borrarLote($_POST);
    $ga->LOTE = $_POST["LOTE"];
    $ga->__get("LOTE");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} 

if( $_POST['action'] == 'maneja_caducidad' ) 
{
  $respuesta = $ga->maneja_caducidad($_POST);
  $arr = array(
    "success" => true,
    "respuesta" => $respuesta
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'load' ) 
{
  $ga->LOTE = $_POST["id_lote"];
  $ga->ARTICULO = $_POST["cve_articulo"];
  $ga->buscarLote($ga->LOTE, $ga->ARTICULO);
  $arr = array(
    "success" => true,
    "cve_articulo" => $ga->cve_articulo,
    "LOTE" => $ga->LOTE,
    "CADUCIDAD" => $ga->CADUCIDAD
  );
  echo json_encode($arr);
}



if( $_POST['action'] == 'exists' ) {
    $ga->LOTE = $_POST["LOTE"];
    $ga->validaClave("LOTE");

    $success = false;

    if (!empty($ga->data->LOTE)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

}


if( $_POST['action'] == 'geLotesxArticulo' ) {
    
    $ga->getLotes($_POST['cve_articulo']);

    $success = false;

    if (!empty($ga->data)) {
        $success = true;
    }

    $arr = array(
        "success" => $success,
		"lotes" => $ga->data
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'geLotesxArticuloActivos' ) {
    
    $ga->getLotesActivos($_POST['cve_articulo']);

    $success = false;

    if (!empty($ga->data)) {
        $success = true;
    }

    $arr = array(
        "success" => $success,
        "lotes" => $ga->data
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'recovery' ) {
    $ga->recovery($_POST);
    $ga->ID_Proveedor = $_POST["id"];
    $ga->__get("id");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'inUse' ) {
    $use = $ga->inUse($_POST);
    $arr = array(
        "success" => $use,
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'ExisteLote' )
{
  $lote = $_POST['lote'];

  $sql = "
    SELECT  Lote, Caducidad
    FROM c_lotes
    WHERE Lote = '$lote';
    ";

  if($_POST['lote_serie'] == 'S')
  $sql = "
    SELECT  numero_serie
    FROM c_serie
    WHERE numero_serie = '$lote';
    ";
  $res = getArraySQL($sql);
  $array = [
    "res"=>$res
  ];
  echo json_encode($array);
}

function getArraySQL($sql){
  $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  mysqli_set_charset($conexion, "utf8");
  if(!$result = mysqli_query($conexion, $sql))
  {
    echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";;
  }
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
