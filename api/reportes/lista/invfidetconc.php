<?php 
include '../../../config.php'; 
 
error_reporting(0); 
 
if(isset($_GET) && !empty($_GET)){ 
    $page = $_GET['start']; 
    $limit = $_GET['length'];  
    $search = $_GET['search']['value']; 
  
  
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
 
    $sqlCount = "SELECT COUNT(th_inventario.ID_Inventario) AS total
                FROM th_inventario 
                LEFT JOIN t_invpiezas ON t_invpiezas.ID_Inventario = th_inventario.ID_Inventario 
                LEFT JOIN t_conteoinventario ON t_conteoinventario.ID_Inventario = th_inventario.ID_Inventario 
                WHERE t_conteoinventario.NConteo > 0
                GROUP BY th_inventario.ID_Inventario"; 
    $query = mysqli_query($conn, $sqlCount); 
    $count = 0; 
    if($query){ 
        $count = mysqli_fetch_array($query)['total']; 
    }          
 
    $sql = "SELECT 
                    th_inventario.ID_Inventario AS inventario, 
                    (SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = th_inventario.ID_Inventario) AS conteo, 
                    DATE_FORMAT(th_inventario.Fecha, '%d-%m-%Y') AS fecha, 
                    t_invpiezas.Cantidad AS diferencia 
            FROM 
                th_inventario 
            LEFT JOIN t_invpiezas ON t_invpiezas.ID_Inventario = th_inventario.ID_Inventario 
            LEFT JOIN t_conteoinventario ON t_conteoinventario.ID_Inventario = th_inventario.ID_Inventario 
            WHERE t_conteoinventario.NConteo > 0
            GROUP BY th_inventario.ID_Inventario
            LIMIT $page,$limit;"; 
 
    if (!($res = mysqli_query($conn, $sql))) { 
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") ")); 
    } 
 
    $data = array(); 
    $i = 0; 
    while ($row = mysqli_fetch_array($res)) { 
        $row = array_map('utf8_encode', $row); 
        $row['acciones'] = "<button class='btn btn-sm btn-default no' title='Ver Detalle' onclick='see(".$row["inventario"].")'> <i class='fa fa-search'></i> </button>";
        $row['acciones'] .= "&nbsp; <button class='btn btn-sm btn-danger no' title='Reporte PDF' onclick='printPDF(".$row["inventario"].")'> <i class='fa fa-print'></i><sup> PDF</sup></button>";
        $row['acciones'] .= "&nbsp; <button class='btn btn-sm btn-primary no' title='Reporte Excel' onclick='printExcel(".$row["inventario"].")'> <i class='fa fa-print'></i><sup> Excel</sup></button>";
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