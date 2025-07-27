<?php
include '../../app/load.php';

$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}
else{

    $ga = new \AlmacenP\AlmacenP();

    if($_POST['action'] == 'search_almacen_pre'){

        $res = $ga->getAlmaPre($_POST);
        
        $arr = array(
            "success" => true,
            "codigo" => $res[0],
            "clave" => $res[1]
        );
        echo json_encode($arr);
    }

    if($_POST['action'] == 'search_usuario'){

        $res = $ga->getUser($_POST);
        
        $arr = array(
            "success" => true,
            "data" => $res[0]
        );
        echo json_encode($arr);
    }
}


