<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$partidas = new \Partidas\Partidas();

if( $_POST['action'] == 'get' )
{
  $partidas->get($_POST);
  echo json_encode($partidas->result);
}

if( $_POST['action'] == 'save' )
{
  $partidas->save($_POST);
  echo json_encode($partidas->result);
}

if( $_POST['action'] == 'getPresupuestos' )
{
  $partidas->getPresupuestos($_POST);
  echo json_encode($partidas->result);
}


if( $_POST['action'] == 'delete' )
{
  $partidas->delete($_POST);
  echo json_encode($partidas->result);
} 