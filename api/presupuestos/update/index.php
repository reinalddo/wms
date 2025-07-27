<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$presupuestos = new \Presupuestos\Presupuestos();

if( $_POST['action'] == 'add' )
{
    $resp = $presupuestos->save($_POST);
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
    $presupuestos->id = $_POST["id"];
    $presupuestos->__get("id");
    $arr = array(
        "success" => true,
    );
  
    //echo var_dump($presupuestos->data);
  
    foreach ($presupuestos->data as $nombre => $valor) $arr2[$nombre] = $valor;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);
}

if( $_POST['action'] == 'edit' ) {
    $resp = $presupuestos->actualizarPresupuestos($_POST);
    $presupuestos->id = $_POST["id"];
    $success = true;

    $arr = array(
        "success" => $success,
        "err" => $resp
    );
    echo json_encode($arr);
  //echo var_dump($resp);
}

if( $_POST['action'] == 'existeCodigoArticulo' ) {
    $clave=$presupuestos->existeCodigoArticulo($_POST);

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
	$clave=$presupuestos->exist($_POST);

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
	$clave=$presupuestos->existeEnUbicacion($_POST);

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
    $presupuestos->borrarPresupuesto($_POST);
    $presupuestos->id = $_POST["id"];
    //$presupuestos->__get("id");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} 

if( $_POST['action'] == 'loadVer' ) {
    $presupuestos->cve_articulo = $_POST["cve_articulo"];
    $presupuestos->__getVer("cve_articulo");
    $arr = array(
        "success" => true,
    );

    foreach ($presupuestos->data as $nombre => $valor) $arr2[$nombre] = $valor;

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
    $presupuestos->cve_articulo = $_POST["cve_articulo"];
    $presupuestos->__get("cve_articulo");
    $arr = array(
        "success" => true,
    );

    foreach ($presupuestos->data as $nombre => $valor) $arr2[$nombre] = $valor;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);
}

if( $_POST['action'] == 'traerClasificacionDeGrupo' ) {
    $clasificaciones = $presupuestos->loadClasificaciones($_POST["grupo"]);

	$arr = array(
        "success" => true,
		"clasificaciones" => $clasificaciones
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'traerTipoDeClasificacion' ) {
    $tipos = $presupuestos->loadTipos($_POST["clasificacion"]);

	$arr = array(
        "success" => true,
		"tipos" => $tipos
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'recovery' ) {
    $presupuestos->recoveryArticulo($_POST);
    $presupuestos->cve_articulo = $_POST["id"];
    $presupuestos->__get("id");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'loadAll' ) {
    $data=$presupuestos->getAll();
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
    $data=$presupuestos->getArtCompuestos($_GET["almacen"], $start, $length, $search);
    $total=$presupuestos->getArtCompuestosTotalCount($_GET["almacen"], $search);

    $output = array(
        "draw" => $_GET["draw"],
        "recordsTotal" => $total,
        "recordsFiltered" => $total,
        "data" => $data
    );

    echo json_encode($output);

}
