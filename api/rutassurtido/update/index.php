<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \RutasSurtido\RutasSurtido();

if( $_POST['action'] == 'edit' ) 
{
  $ga->actualizarRutasSurtido($_POST);
} 


if( $_POST['action'] == 'CambiarTipoRecorridoSurtido' ) 
{
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $valor = $_POST['valor'];
  $id_almacen = $_POST['id_almacen'];

  $sql = "UPDATE Rel_ModuloTipo SET Id_Tipo = 3 WHERE ID_Permiso = 2 AND Id_Tipo = 2 AND Cve_Almac = '$id_almacen'";
  if($valor)
    $sql = "UPDATE Rel_ModuloTipo SET Id_Tipo = 2 WHERE ID_Permiso = 2 AND Id_Tipo = 3 AND Cve_Almac = '$id_almacen'";

  if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}

  $arr = array("success"=>true,"text"=>"");
  echo json_encode($arr);
}

if( $_POST['action'] == 'guardarRuta' ) 
{
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $sql="
    SELECT 
      nombre_completo 
    FROM rel_usuario_ruta 
    INNER JOIN c_usuario on c_usuario.id_user = rel_usuario_ruta.id_usuario 
    WHERE id_usuario in ('".join("','",$_POST["id"])."')
  ";
  
  if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
  $responce="";
  while ($row = mysqli_fetch_array($res)) 
  {
    $responce.=$row["nombre_completo"].", ";
    $i++;
  }
  if($responce == "")
  {
    $ga->guardarRuta($_POST);
    if($ga==true)
    {
      //Seleciona la ruta que se acaba de crear
      $sql="SELECT idr FROM th_ruta_surtido WHERE nombre = '{$_POST["nombre"]}'";
      if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(2): (" . mysqli_error($conn) . ")";}
      while ($row = mysqli_fetch_array($res)) 
      {
        $responce=$row[0];
        $i++;
      }
      
      foreach ($_POST["id"] as $key => $val) 
      {
        $sql="INSERT INTO rel_usuario_ruta (id_usuario,id_ruta) VALUES(".$val.",".$responce.")";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(3): (" . mysqli_error($conn) . ")";}
        $arr = array("success"=>true,"text"=>"");
      }
      $success = true;
    }
    else
    {
      $success= false;
    }
  }
  else
  {
    $arr = array("success"=>false,"text"=>"Este usuario ya cuenta con una ruta asignada, 
    para liberar al usuario ir al Administrador de Rutas de Surtido.
    (".$responce.")");
  }
  echo json_encode($arr);
}

if( $_POST['action'] == 'guardarOrdenSec' ) 
{
  $ga->guardarOrdenSec($_POST);
}
if( $_POST['action'] == 'editarOrdenSec' ) 
{
  if($_POST["orden_secuencia"] != "")
  {
    $ga->editarOrdenSec($_POST);
  }
}
if( $_POST['action'] == 'organizarOrdenSec' ) 
{
  $ga->organizarOrdenSec($_POST);
}

if( $_POST['action'] == 'importar_rutas' ) 
{
  $arr = $ga->importar_rutas($_POST);
  echo json_encode($arr);
} 
