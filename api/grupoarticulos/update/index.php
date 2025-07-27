<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \GrupoArticulos\GrupoArticulos();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarGrupoArticulos($_POST);
}
if( $_POST['action'] == 'exists' ) {
	
    $clave=$ga->exist($_POST["clave"], $_POST["almacen"]);

   if($clave==true)
        $success = true;
    else 
		$success= false;

    $arr = array(
		"success"=>$success
 );

    echo json_encode($arr);

} if( $_POST['action'] == 'delete' ) {
    $ga->borrarGrupoArticulos($_POST);
    $ga->cve_gpoart = $_POST["cve_gpoart"];
    $ga->__get("cve_gpoart");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->cve_gpoart = $_POST["codigo"];
    $ga->__get("cve_gpoart");
    $arr = array(
        "success" => true,
        "codigo" => $ga->data->cve_gpoart,
        "descripcion" => $ga->data->des_gpoart
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'recovery' ) {
    $ga->recovery($_POST);
    $ga->id = $_POST["id"];
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

if( $_POST['action'] == 'copiar' ) {
/*
    $ga->CopiarGrupoArticulos($_POST);
    $ga->cve_gpoart = $_POST["cve_gpoart"];
    $ga->__get("cve_gpoart");
*/

    $id_almacen_actual = $_POST['id_almacen_actual'];
    $cve_grupo_actual  = $_POST['cve_grupo_actual'];
    $id_grupo_destino  = $_POST['id_grupo_destino'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //$sql = "DELETE FROM Rel_Articulo_Almacen WHERE Grupo_ID = $id_grupo_destino";
    //$query = mysqli_query($conn, $sql);


    $sql = "INSERT INTO Rel_Articulo_Almacen (Cve_Almac, Cve_Articulo, Grupo_ID) 
    (SELECT (SELECT id_almacen FROM c_gpoarticulo WHERE id = $id_grupo_destino), r.Cve_Articulo, $id_grupo_destino 
    FROM Rel_Articulo_Almacen r 
    WHERE r.Cve_Almac = $id_almacen_actual AND r.Grupo_ID = (SELECT id FROM c_gpoarticulo WHERE cve_gpoart = '$cve_grupo_actual') 
     AND r.Cve_Articulo IN (SELECT Cve_Articulo FROM Rel_Articulo_Almacen WHERE Cve_Almac = (SELECT id_almacen FROM c_gpoarticulo WHERE id = $id_grupo_destino))) ON DUPLICATE KEY UPDATE Grupo_ID = $id_grupo_destino";
    $query = mysqli_query($conn, $sql);


    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}