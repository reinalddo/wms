<?php
include '../../../app/load.php';
// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Reportes\Reportes();

if( $_POST['action'] == 'productosmasubicacion' ) {   
    $data = $ga->productosmasubicacion();
    echo json_encode($data);
}

if( $_POST['action'] == 'concentradoexistencia' ) {   
    $data = $ga->concentradoexistencia();
    echo json_encode($data);
}

if( $_POST['action'] == 'entrada' ) {	
    
    $id=$_POST['idss'];
    $data = $ga->entrada($id);
	
		
	$arr = array(
            
		"data" => $data,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'setRangoGuiasEmbarqueShowReport' ) {
    $_SESSION["FolPedidoCon"] = $_POST["FolPedidoCon"];
    $_SESSION["FacturaMadre"] = $_POST["FacturaMadre"];

    $data = (array) $ga->guiasembarque($_POST["FolPedidoCon"], $_POST["FacturaMadre"]);

    $total = 0;

    foreach ($data as $d) {
        $total = $total + $d["cajas"];
    }

    $arr = array(
        "success"=>true,
        "total" => $total,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'setRangoUbicacionesShowReport' ) {
    $_SESSION["rack"] = $_POST["rack"];
	$_SESSION["zona"] = $_POST["zona"];

        $arr = array(
        "success"=>true,
        
    );

    

    echo json_encode($arr);
}

if( $_POST['action'] == 'printRangeGuiasEmbarqueShowReport' ) {
    $_SESSION["desde"] = $_POST["desde"];
    $_SESSION["hasta"] = $_POST["hasta"];
    $_SESSION["FolPedidoCon"] = $_POST["FolPedidoCon"];
    //$_SESSION["FacturaMadre"] = $_POST["FacturaMadre"];

    $arr = array(
        "success"=>true,
        "data" => true,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'printRangeUbicacionesShowReport' ) {
    $_SESSION["desde"] = $_POST["desde"];
    $_SESSION["hasta"] = $_POST["hasta"];
    $_SESSION["id_ubicacion"] = $_POST["id_ubicacion"];

    $arr = array(
        "success"=>true,
        "data" => true,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'guiasembarqueshowreport' ) {
    $_SESSION["FolPedidoCon"] = $_POST["FolPedidoCon"];
    $_SESSION["FacturaMadre"] = $_POST["FacturaMadre"];

    $arr = array(
        "data" => true,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}


if( $_POST['action'] == 'productosShowReport' ) {
    $_SESSION["cve_articulo"] = $_POST["cve_articulo"];

    $arr = array(
        "data" => true,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}


if( $_POST['action'] == 'ubicacioneshowreport' ) {
    $_SESSION["id_ubicacion"] = $_POST["id_ubicacion"];
	
    $arr = array(
        "data" => true,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'guiasembarque' ) {
    $data = $ga->guiasembarque($_POST["FolPedidoCon"], $_POST["FacturaMadre"]);

    $arr = array(
        "data" => $data,
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'comprobantei' ) {	
    $data = $ga->comprobantei();
	
		
	$arr = array(
            
		"data" => $data,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'invfidet' ) {	
    $data = $ga->invfidet($_POST['id']);
		
	$arr = array(            
		"data" => $data,	
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'maxmin1' ) {	
    $data = $ga->maxmin1();
	
		
	$arr = array(
            
		"data" => $data,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'maxmin2' ) {   
    $data = $ga->maxmin2();
    
        
    $arr = array(
            
        "data" => $data,    
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'invfidetconc' ) {	
    $data = $ga->invfidetconc();
	
		
	$arr = array(
            
		"data" => $data,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'difconteos' ) {	
    $data = $ga->difconteos();
	
		
	$arr = array(
            
		"data" => $data,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'invcicl' ) {	
    $data = $ga->invcicl();
	
		
	$arr = array(
            
		"data" => $data,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'invcicldet' ) {	
    $data = $ga->invcicldet($_POST['id']);
	
		
	$arr = array(
            
		"data" => $data,	
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}


if( $_POST['action'] == 'lotesporvencer' ) {    
    $data = $ga->lotesPorVencer();
    
        
    $arr = array(
            
        "data" => $data,    
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'lotesvencidos' ) {    
    $data = $ga->lotesVencidos();
    
        
    $arr = array(
            
        "data" => $data,    
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}


if( $_POST['action'] == 'existenciaUbicacion' ) {    
    $data = $ga->existenciaubica($_POST["almacen"],$_POST["articulo"], $_POST["zona"]);
    
        
    $arr = array(
            
        "data" => $data,    
    );


    $arr = array_merge($arr);

    echo json_encode($arr);
}


