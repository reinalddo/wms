<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \AdminEntrada\AdminEntrada();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
	    $arr = array(
        "success" => true,
        "err" => $resp
    );
    /*$idAlmacen  = $ga->getLastInsertId()->ID_Almacen;

    if(!empty($_POST['usuarios']))
    {
        for($i=0;$i<count($_POST['usuarios'][0]);$i++)
        {                       
            $ga->saveUserAl($_POST['usuarios'][0][$i],$idAlmacen);
        }
    }*/
	
	echo json_encode($arr);

}

if( $_POST['action'] == 'edit' ) {
    $ga->actualizarAlmacen($_POST);
   
    $arr = array(
        "success" => $success,
        "err" => $resp
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'exists' ) {
	
    $clave=$ga->exist($_POST["clave_almacen"]);
	
 

   if($clave==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
 );

    echo json_encode($arr);

}

if( $_POST['action'] == 'delete' ) {
    $ga->borrarAlmacen($_POST);
    $ga->cve_almac = $_POST["cve_almac"];
    $ga->__get("cve_almac");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'load' ) {
    
    $data=$ga->loadDetalle($_POST["codigo"]);

    //var_dump($data); exit;
    
    $arr = array(
        "success" => true,
        "detalle" => $data
    );

    

    echo json_encode($arr);

}


if( $_POST['action'] == 'load2' ) {
    
    $data=$ga->load($_POST["codigo"]);

    //var_dump($data); exit;
    
    $arr = array(
        "success" => true,
        "detalle" => $data
    );

    

    echo json_encode($arr);

}

if( $_POST['action'] == 'guardarUsuario' ) {	
	$ga->borrarUsuarioAlmacen($_POST["cve_almac"]);
	
	$usuarios = $_POST["usuarios"][0];
	
    if(!empty($_POST["usuarios"]))
    {
        foreach($usuarios as $usuarioAlmacen)
        {
            $ga->saveUserAl($usuarioAlmacen,$_POST["cve_almac"]);
        }
    }

    $arr = array(
        "success" => $success,
        "err" => $resp
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'loadAlmacenes' ) {
    $almacenUser = $ga->loadAlmacenUser($_POST["cve_usuario"]);


    $current_almacen = array(
        "Current" =>array()
    );

    foreach ($almacenUser as $currentAlmacen)
    {
        $current_almacen['Current'][] = array (
            'id' => $currentAlmacen->cve_almac,
            'desc' =>$currentAlmacen->des_almac,
        );
    }

    $almacen_data = array();
    foreach($almacenUser as $almacen)
    {
        $almacen_data[]  = $almacen->cve_almac;
    }


    $model_almacen = new \Almacen\Almacen();
    $almacenes = $model_almacen->getAll();

    $store_data = array(
        "Almacenes" =>array()
    );

    foreach ($almacenes as $almacen)
    {
        if(!in_array($almacen->cve_almac,$almacen_data))
        {
            $store_data['Almacenes'][] = array (
                'id' => $almacen->cve_almac,
                'desc' =>$almacen->des_almac
            );
        }

    }

    $finalArray = array_merge($store_data,$current_almacen);

    echo json_encode($finalArray);

}

if( $_POST['action'] == 'guardarAlmacen' ) {

    $ga->borrarAlmacenUsuario($_POST["cve_usuario"]);
	
	$almacenes = $_POST["almacenes"][0];
	
    if(!empty($_POST["almacenes"]))
    {
        foreach($almacenes as $almacenUsuario)
        {
            $ga->saveUserAl($_POST["cve_usuario"],$almacenUsuario);
        }
    }

    $arr = array(
        "success" => $success,
        "err" => $resp
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'traerUsuariosDeAlmacen' ) {	
    $userAlmacen = $ga->loadUserAlmacen($_POST["cve_almac"]);
	$users = $ga->loadUsers($_POST["cve_almac"]);
		
	$arr = array(
        "success" => true,        
		"usuariosAlmacen" => $userAlmacen,
		"todosUsuarios" => $users		
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'traerAlmacenesDeUsuario' ) {	
    $almacenUsuario = $ga->loadAlmacenUser($_POST["cve_usuario"]);
	$almacenes = $ga->loadAlmacenes($_POST["cve_usuario"]);
		
	$arr = array(
        "success" => true,        
		"almacenesUsuario" => $almacenUsuario,
		"todosAlmacenes" => $almacenes		
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'reporte' ) {	
    $almacenUsuario = $ga->reporte();
	
		
	$arr = array(
            
		"data" => $almacenUsuario,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

