<?php
include '../../../app/load.php';

error_reporting(0);

/*if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}*/

$page = $_POST['items']; // get the requested page

$qac = new \QaCuarentena\QaCuarentena();
$qac->saveItemsInCuarentena( $page );

echo json_encode(['response'=>true]);
