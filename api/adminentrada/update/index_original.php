<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Almacen\Almacen();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
    $idAlmacen  = $ga->getLastInsertId()->ID_Almacen;

    if(!empty($_POST['usuarios']))
    {
        for($i=0;$i<count($_POST['usuarios'][0]);$i++)
        {                       
            $ga->saveUserAl($_POST['usuarios'][0][$i],$idAlmacen);
        }
    }

} if( $_POST['action'] == 'edit' ) {
    /*$ga->actualizarAlmacen($_POST);*/
    $userAlmacen = $ga->loadUserAlmacen($_POST["cve_almac"]);

    // Arreglo de usuarios actuales en el almacen
    $usuario_data = array();
    foreach($userAlmacen as $user)
    {
        $usuario_data[]  = $user->cve_usuario;
    }

    $success = true;
    // Si el arreglo usuarios a agregar está vacío inactiva todos los usuarios
    if(!empty($_POST["usuarios"]))
    {
        $diferencia = array_diff($_POST["usuarios"][0],$usuario_data);

        $array_dif = array();
        foreach($diferencia as $diff)
        {
            $array_dif[] = $diff;
        }

        foreach($array_dif as $usuarioAlmacen)
        {
            $ga->saveUserAl($usuarioAlmacen,$_POST["cve_almac"]);
        }

        $diferencia_inv = array_diff($usuario_data,$_POST["usuarios"][0]);

        $array_difinv = array();
        foreach($diferencia_inv as $diffinv)
        {
            $array_difinv[] = $diffinv;
        }

        foreach($array_difinv as $arraydiffinv)
        {
            $ga->borrarUsuarioAlmacen($_POST["cve_almac"],$arraydiffinv);
        }

    }
    else
    {
        foreach($usuario_data as $user)
        {
            $ga->borrarUsuarioAlmacen($_POST["cve_almac"],$user);
        }
    }

    $arr = array(
        "success" => $success,
        "err" => $resp
    );

    echo json_encode($arr);
}
if( $_POST['action'] == 'exists' ) {
    $ga->cve_almac = $_POST["cve_almac"];
    $ga->__get("cve_almac");

    $success = false;

    if (!empty($ga->data->cve_almac)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
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
    $ga->cve_almac = $_POST["cve_almac"];
    $ga->__get("cve_almac");

    $userAlmacen = $ga->loadUserAlmacen($_POST["cve_almac"]);


    $current_user = array(
        "Current" =>array()
    );

    foreach ($userAlmacen as $currentUser)
    {
        $current_user['Current'][] = array (
            'id' => $currentUser->cve_usuario
        );
    }

    $usuario_data = array();
    foreach($userAlmacen as $user)
    {
        $usuario_data[]  = $user->cve_usuario;
    }


    $model_usuario = new \Usuarios\Usuarios();
    $users = $model_usuario->getAll();

    $store_data = array(
        "Usuarios" =>array()
    );

    foreach ($users as $usuario)
    {
        if(!in_array($usuario->cve_usuario,$usuario_data))
        {
            $store_data['Usuarios'][] = array (
                'id' => $usuario->cve_usuario
            );
        }

    }

    $arr = array(
        "success" => true,
        "NAlmacen" => $ga->data->des_almac,
        "txtNomCompa" => $ga->data->cve_cia,
        "Direccion" => $ga->data->des_direcc
    );

    $arr = array_merge($arr,$store_data);
    $finalArray = array_merge($arr,$current_user);

    echo json_encode($finalArray);

}

if( $_POST['action'] == 'guardarUsuario' ) {
    $userAlmacen = $ga->loadUserAlmacen($_POST["cve_almac"]);

    // Arreglo de usuarios actuales en el almacen
    $usuario_data = array();
    foreach($userAlmacen as $user)
    {
        $usuario_data[]  = $user->cve_usuario;
    }

    $success = true;
    // Si el arreglo usuarios a agregar está vacío inactiva todos los usuarios
    if(!empty($_POST["usuarios"]))
    {
        $diferencia = array_diff($_POST["usuarios"][0],$usuario_data);

        $array_dif = array();
        foreach($diferencia as $diff)
        {
            $array_dif[] = $diff;
        }

        foreach($array_dif as $usuarioAlmacen)
        {
            $ga->saveUserAl($usuarioAlmacen,$_POST["cve_almac"]);
        }

        $diferencia_inv = array_diff($usuario_data,$_POST["usuarios"][0]);

        $array_difinv = array();
        foreach($diferencia_inv as $diffinv)
        {
            $array_difinv[] = $diffinv;
        }

        foreach($array_difinv as $arraydiffinv)
        {
            $ga->borrarUsuarioAlmacen($_POST["cve_almac"],$arraydiffinv);
        }

    }
    else
    {
        foreach($usuario_data as $user)
        {
            $ga->borrarUsuarioAlmacen($_POST["cve_almac"],$user);
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

    $almacenUser = $ga->loadAlmacenUser($_POST["cve_usuario"]);

    // Arreglo de usuarios actuales en el almacen
    $usuario_data = array();
    foreach($almacenUser as $almacen)
    {
        $almacen_data[]  = $almacen->cve_almac;
    }

    $success = true;
    // Si el arreglo usuarios a agregar está vacío inactiva todos los usuarios
    if(!empty($_POST["almacenes"]))
    {
        $diferencia = array_diff($_POST["almacenes"][0],$almacen_data);

        $array_dif = array();
        foreach($diferencia as $diff)
        {
            $array_dif[] = $diff;
        }

        foreach($array_dif as $almacenUsuario)
        {
            $ga->saveUserAl($_POST["cve_usuario"],$almacenUsuario);
        }

        $diferencia_inv = array_diff($almacen_data,$_POST["almacenes"][0]);

        $array_difinv = array();
        foreach($diferencia_inv as $diffinv)
        {
            $array_difinv[] = $diffinv;
        }

        foreach($array_difinv as $arraydiffinv)
        {
            $ga->borrarUsuarioAlmacen($arraydiffinv,$_POST["cve_usuario"]);
        }

    }
    else
    {
        foreach($almacen_data as $almacen)
        {
            $ga->borrarUsuarioAlmacen($almacen,$_POST["cve_usuario"]);
        }
    }

    $arr = array(
        "success" => $success,
        "err" => $resp
    );

    echo json_encode($arr);
}

