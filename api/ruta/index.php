<?php


include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Ruta\Ruta();


if( $_POST['action'] == 'add' ) {
    $model_cliente = new \Clientes\Clientes();
    $ga->save($_POST);
    $idRuta = $ga->getLastInsertId()->ID;

    $ga->ID_Ruta = $idRuta;
    $ga->__get("ID_Ruta");
    if(!empty($_POST['clientes'])) {

        for($i=0;$i<count($_POST['clientes'][0]);$i++)
        {
            for($j=0;$j<count($_POST['clientes'][0]);$j++)
            {
                $model_cliente->asignarRutaCliente($_POST['clientes'][$i][$j],$ga->data->cve_ruta);
            }
        }
    }

} if( $_POST['action'] == 'edit' ) {

    $success = true;
    $ga->ID_Ruta = $_POST["ID_Ruta"];
    $ga->__get("ID_Ruta");
    $ruta = $ga->data->cve_ruta;
    $cve_rutaActual = $ga->data->cve_ruta;

    $model_cliente = new \Clientes\Clientes();
    $clientes = $model_cliente->loadClienteRuta($ruta);

    foreach($clientes as $cliente_clte)
    {
        $clientes_actuales[] = $cliente_clte->Cve_Clte;
    }
    //Si un cliente con ruta actual no está en el nuevo arreglo de rutas lo elimina
    if(!empty($_POST['clientes'][0]))
    {
        if(!empty($clientes_actuales))
        {
            foreach ($clientes_actuales as $idCliente)
            {
                if (!in_array($idCliente, $_POST['clientes'][0]))
                {
                    $model_cliente->asignarRutaCliente($idCliente,"");
                }
            }
        }
    }else
    {
    //Borra todas las rutas asignada a los clientes
        if(!empty($clientes_actuales)) {
            foreach ($clientes_actuales as $idCliente) {
                $model_cliente->asignarRutaCliente($idCliente, "");
            }
        }
    }
    //Guardar los nuevos registros
    if(!empty($_POST['clientes'])) {

        for($i=0;$i<count($_POST['clientes'][0]);$i++)
        {
            for($j=0;$j<count($_POST['clientes'][0]);$j++)
            {
                if($cve_rutaActual === $_POST["cve_ruta"])
                {
                    $model_cliente->asignarRutaCliente($_POST['clientes'][$i][$j],$_POST["cve_ruta"]);
                }
            }
        }
    }
    if($cve_rutaActual === $_POST["cve_ruta"])
    {
        $ga->actualizarRuta($_POST);
    }
    else
    {
        if(!empty($clientes_actuales))
        {
            foreach ($clientes_actuales as $idCliente)
            {
                $model_cliente->asignarRutaCliente($idCliente,$_POST["cve_ruta"]);
                $ga->actualizarRuta($_POST);
            }
        }
    }

    $arr = array(
        "success" => $success,
        "err" => $resp
    );
    echo json_encode($arr);

}
if( $_POST['action'] == 'exists' ) {
    $ga->ID_Ruta = $_POST["ID_Ruta"];
    $ga->__get("ID_Ruta");

    $success = false;

    if (!empty($ga->data->ID_Ruta)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'delete' ) {
    $ga->borrarRuta($_POST);
    $ga->ID_Ruta = $_POST["ID_Ruta"];
    $ga->__get("ID_Ruta");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->ID_Ruta = $_POST["ID_Ruta"];
    $model_cliente = new \Clientes\Clientes();
    $clientes = $model_cliente->getAll();

    // $store_data["Clientes"]="";
    $store_data = array(
        "Clientes" =>array()
    );

    foreach ($clientes as $Cliente)
    {
        $store_data['Clientes'][] = array (
            'id' => $Cliente->Cve_Clte,
            'razon_social' => $Cliente->RazonSocial,
            'cve_ruta' => $Cliente->cve_ruta
        );
    }

    //array_push($store_data,$store_data1);

    $ga->__get("ID_Ruta");

    $arr = array(
        "success" => true,
        "ID_Ruta" => $ga->data->ID_Ruta,
        "cve_ruta" => $ga->data->cve_ruta,
        "descripcion" => $ga->data->descripcion,
        "status" => $ga->data->status,
        "cve_cia" => $ga->data->cve_cia
    );


    $arr = array_merge($arr,$store_data);

    echo json_encode($arr);

}