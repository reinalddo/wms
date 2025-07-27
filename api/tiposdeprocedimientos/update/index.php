<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$procedimientos = new \TipoDeProcedimientos\TipoDeProcedimientos();

if( $_POST['action'] == 'add' )
{
    $resp = $procedimientos->save($_POST);
    $success = true;
    $arr = array(
        "success" => $success,
        "err" => $resp[0][0]
    );
    echo json_encode($arr);
}

if( $_POST['action'] == 'load' )
{
    //echo var_dump($_POST["id"]);
    $procedimientos->id = $_POST["id"];
    $procedimientos->__get("id");
    $arr = array(
        "success" => true,
    );
  
    //echo var_dump($procedimientos->data);
  
    foreach ($procedimientos->data as $nombre => $valor) $arr2[$nombre] = $valor;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);
}

if( $_POST['action'] == 'edit' ) {
    $resp = $procedimientos->actualizarProcedimientos($_POST);
    $procedimientos->id = $_POST["id"];
    $success = true;

    $arr = array(
        "success" => $success,
        "err" => $resp
    );
    echo json_encode($arr);
  //echo var_dump($resp);
}

if( $_POST['action'] == 'existeCodigoArticulo' ) {
    $clave=$procedimientos->existeCodigoArticulo($_POST);

    if($clave==true)
        $success = true;
    else
        $success= false;

    $arr = array(
        "success"=>$success
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'exists' ) {
	$clave=$procedimientos->exist($_POST);

    if($clave==true)
        $success = true;
    else
		$success= false;

    $arr = array(
		"success"=>$success
	);

    echo json_encode($arr);
}

if( $_POST['action'] == 'existeEnUbicacion' ) {
	$clave=$procedimientos->existeEnUbicacion($_POST);

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
    $procedimientos->borrarPresupuesto($_POST);
    $procedimientos->id = $_POST["id"];
    //$procedimientos->__get("id");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} 

if( $_POST['action'] == 'loadVer' ) {
    $procedimientos->cve_articulo = $_POST["cve_articulo"];
    $procedimientos->__getVer("cve_articulo");
    $arr = array(
        "success" => true,
    );

    foreach ($procedimientos->data as $nombre => $valor) $arr2[$nombre] = $valor;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);
}

if( $_POST['action'] == 'loadEmpty' ) {
    $_arr[] = array("a" => "1", "b" => "2", "c" => "3", "d" => "4", "e" => "5");

    $arr = array(
        "data" => $_arr
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'load2' ) {
    $procedimientos->cve_articulo = $_POST["cve_articulo"];
    $procedimientos->__get("cve_articulo");
    $arr = array(
        "success" => true,
    );

    foreach ($procedimientos->data as $nombre => $valor) $arr2[$nombre] = $valor;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);
}

if( $_POST['action'] == 'traerClasificacionDeGrupo' ) {
    $clasificaciones = $procedimientos->loadClasificaciones($_POST["grupo"]);

	$arr = array(
        "success" => true,
		"clasificaciones" => $clasificaciones
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'traerTipoDeClasificacion' ) {
    $tipos = $procedimientos->loadTipos($_POST["clasificacion"]);

	$arr = array(
        "success" => true,
		"tipos" => $tipos
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'recovery' ) {
    $procedimientos->recoveryArticulo($_POST);
    $procedimientos->cve_articulo = $_POST["id"];
    $procedimientos->__get("id");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'loadAll' ) {
    $data=$procedimientos->getAll();
    $arr = array(
        "success" => true,
		"detalle" => $data,
    );

    echo json_encode($arr);

}

if(isset($_GET) && $_GET['action'] == 'getArtCompuestos' ) {
    $search = $_GET['search']['value'];
    $start = $_GET['start'];
    $length = $_GET['length'];
    $data=$procedimientos->getArtCompuestos($_GET["almacen"], $start, $length, $search);
    $total=$procedimientos->getArtCompuestosTotalCount($_GET["almacen"], $search);

    $output = array(
        "draw" => $_GET["draw"],
        "recordsTotal" => $total,
        "recordsFiltered" => $total,
        "data" => $data
    );

    echo json_encode($output);

}
