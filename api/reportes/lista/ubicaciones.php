<?php 
include '../../../config.php'; 
 
error_reporting(0); 
 
if(isset($_GET) && !empty($_GET)){ 
    $page = $_GET['start']; 
    $limit = $_GET['length'];  
    $search = $_GET['search']['value']; 
 $id_ubicacion= $_GET["id_ubicacion"]; 
 $zonas=$_GET["zonas"];
 $rack=$_GET["rack"];
 $area=$_GET["area"];
 $almacen=$_GET["almacen"];


    if($rack!="") $split.=" and u.cve_rack=".$rack."";        
        
 
  
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
 
    $sqlCount = "SELECT 
            count(u.cve_rack) as total
            from c_ubicacion u, c_almacen a
            where u.cve_almac = a.cve_almac 
            and a.cve_almac = '".$zonas."'
            and u.activo = 1 and a.activo =1 $split "; 
    $query = mysqli_query($conn, $sqlCount); 
    $count = 0; 
    if($query){ 
        $count = mysqli_fetch_array($query)['total']; 
    } 
 
         
 
    $sql = "SELECT CONCAT(u.cve_rack,'rack-',u.Seccion,'sección-',u.cve_nivel,'pasillo-',u.Ubicacion) as ubicacion,
            u.cve_rack as rack,
            u.Seccion as seccion,
            u.cve_nivel as nivel,
            u.Ubicacion as ubic,
            u.cve_almac as zona_almacen, 
            u.idy_ubica as id_ubicacion
            from c_ubicacion u, c_almacen a
            where u.cve_almac = a.cve_almac 
            and a.cve_almac = '".$zonas."'
            and u.activo = 1 and a.activo =1 $split "; 
 
 
    $sql .= " LIMIT $page,$limit; "; 
    //echo $sql; exit;
 
    if (!($res = mysqli_query($conn, $sql))) { 
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") ")); 
    } 
 
    $data = array(); 
    $i = 0; 
    while ($row = mysqli_fetch_array($res)) { 
        $row = array_map('utf8_encode', $row); 
        $data[] = $row; 
        $i++; 
    }  
         
    mysqli_close(); 
    header('Content-type: application/json'); 
    $output = array( 
        "draw" => $_GET["draw"], 
        "recordsTotal" => $count, 
        "recordsFiltered" => $count, 
        "data" => $data 
    );  
    echo json_encode($output); 
}