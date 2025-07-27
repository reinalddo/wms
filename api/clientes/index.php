<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Clientes\Clientes();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarClientes($_POST);
} if( $_POST['action'] == 'exists' ) {
    $ga->Cve_Clte = $_POST["codigo"];
    $ga->__get("Cve_Clte");

    $success = false;

    if (!empty($ga->data->Cve_Clte)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);
} if( $_POST['action'] == 'delete' ) {
    $ga->borrarCliente($_POST);
    $ga->Cve_Clte = $_POST["Cve_Clte"];
    $ga->__get("Cve_Clte");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->Cve_Clte = $_POST["codigo"];
    $ga->__get("Cve_Clte");
    $arr = array(
        "success" => true,
    );

    foreach ($ga->data as $nombre => $valor) $arr2[$nombre] = $valor;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);

} 
if( $_POST['action'] == 'loadClientes' ) {   
    $clientes = $ga->getAll();
    $arr = array(
        "success" => true
    );
    $associativeArray = array();
    foreach ($clientes as $Cliente)
    {
        if($Cliente->cve_ruta == "")
        {
            $store_data[] = array(
                'id' => $Cliente->Cve_Clte,
                'razon_social' => $Cliente->RazonSocial            
            ); 
        }
    }   

    $arr = array_merge($arr,$store_data);

    echo json_encode($arr);

} if($_POST['action'] == 'loadClientsRuta' ){
    /*$model_ruta = new \Ruta\Ruta();
    $model_ruta->ID_Ruta = $_POST["ID_Ruta"];
    $model_ruta->__get("ID_Ruta"); 


     $arr = array(
        "success" => true,
        "ID" => $model_ruta->data->cve_ruta  
    );

    echo json_encode($arr);*/

    $clientes = $ga->getAll();
    $arr = array(
        "success" => true
    );
    $associativeArray = array();
    foreach ($clientes as $Cliente)
    {
            $store_data[] = array(
                'id' => $Cliente->Cve_Clte,
                'razon_social' => $Cliente->RazonSocial,
                'cve_ruta' => $Cliente -> cve_ruta
            ); 
        
    }   

    $arr = array_merge($arr,$store_data);

    echo json_encode($arr); 

}

