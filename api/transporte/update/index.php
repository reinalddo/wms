<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Transporte\Transporte();

if( $_POST['action'] == 'add' ) 
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    /*Pedidos listos para embarcar*/
    $transporte = $_POST['ID_Transporte'];
    $Placa = $_POST['Placas'];

    $sql = "SELECT * FROM t_transporte WHERE ID_Transporte = '{$transporte}'";
    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }
    $countClave = mysqli_num_rows($res);

    $sql = "SELECT * FROM t_transporte WHERE Placas = '{$Placa}'";
    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }
    $countPlaca = mysqli_num_rows($res);

    if(!$countPlaca && !$countClave)
        $ga->save($_POST);

    $arr = array(
        "countPlaca" => $countPlaca,
        "countClave" => $countClave
    );
    echo json_encode($arr);

} 

if( $_POST['action'] == 'edit' ) {
    $ga->actualizarTransporte($_POST);
    $success = true;

    $arr = array(
        "success" => $success,
        "err" => $resp
    );
    echo json_encode($arr);
} 

if( $_POST['action'] == 'exists' ) {

    $clave=$ga->exist($_POST["ID_Transporte"]);
	$Cve_transporte_otro_almacen = $ga->existe_en_otro_almacen($_POST["ID_Transporte"], $_POST["id_almacen"]);

  $success_otro_almacen = false;
  if($clave == true)
  {
    $success = true;
    if($Cve_transporte_otro_almacen == true)
        $success_otro_almacen = true;
  }
  else
  {
    $success= false;
  }
  $arr = array(
    "success"=>$success,
    "success_otro_almacen"=>$success_otro_almacen
  );

    echo json_encode($arr);

} 

if( $_POST['action'] == 'CopiarTransporteA_Almacen' ) 
{
    $clave = $_POST['ID_Transporte'];
    $id_almacen = $_POST['id_almacen'];
    $sql = "INSERT INTO t_transporte(ID_Transporte, Nombre, Placas, cve_cia, tipo_transporte, id_almac, num_ec, transporte_externo)(SELECT ID_Transporte, Nombre, Placas, cve_cia, tipo_transporte, '{$id_almacen}', num_ec, transporte_externo FROM t_transporte WHERE ID_Transporte = '{$clave}' LIMIT 1)";
    $Sql = \db()->prepare($sql);
    $Sql->execute();

  $arr = array(
    "success"=>true
  );
  echo json_encode($arr);
}


if( $_POST['action'] == 'existsNombre' ) {
    $nombre=$ga->existNombre($_POST["Nombre"]);
	
 

   if($nombre==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
 );

    echo json_encode($arr);

}

 if( $_POST['action'] == 'delete' ) {
    $ga->borrarTransporte($_POST);
    $ga->ID_Transporte = $_POST["ID_Transporte"];
    $ga->__get("ID_Transporte");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->ID_Transporte = $_POST["ID_Transporte"];
    $ga->__get("ID_Transporte");
    $arr = array(
        "success" => true,
        "ID_Transporte" => $ga->data->ID_Transporte,
        "almacen" => $ga->data->id_almac,
        "Nombre" => $ga->data->Nombre,
        "Placas" => $ga->data->Placas,
        "cve_cia" => $ga->data->cve_cia,
        "num_ec" => $ga->data->num_ec,
        "tipo_transporte" => $ga->data->tipo_transporte,
        "transporte_externo" => $ga->data->transporte_externo
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'recovery' ) {
    $ga->recoveryTransporte($_POST);
    $ga->ID_Transporte = $_POST["ID_Transporte"];
    $ga->__get("ID_Transporte");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}