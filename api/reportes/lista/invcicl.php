<?php 
include '../../../config.php'; 
 
error_reporting(0); 
 
if(isset($_GET) && !empty($_GET)){ 
    $page = $_GET['start']; 
    $limit = $_GET['length'];  
    $search = $_GET['search']['value']; 
  
  
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
 
    $sqlCount = 'SELECT 
  COUNT(t_invpiezasciclico.cve_articulo) AS total 
FROM
  t_invpiezasciclico 
  LEFT JOIN det_planifica_inventario 
    ON t_invpiezasciclico.ID_PLAN = det_planifica_inventario.ID_PLAN 
  LEFT JOIN t_conteoinventariocicl 
    ON t_conteoinventariocicl.ID_PLAN = det_planifica_inventario.ID_PLAN 
  LEFT JOIN c_usuario 
    ON c_usuario.cve_usuario = t_invpiezasciclico.cve_usuario 
  LEFT JOIN c_articulo 
    ON c_articulo.cve_articulo = t_invpiezasciclico.cve_articulo 
  LEFT JOIN ts_existenciapiezas 
    ON ts_existenciapiezas.cve_almac = t_invpiezasciclico.cve_articulo 
    AND ts_existenciapiezas.idy_ubica = t_invpiezasciclico.idy_ubica 
WHERE t_conteoinventariocicl.NConteo > 0 
GROUP BY t_conteoinventariocicl.ID_PLAN, t_conteoinventariocicl.NConteo, t_conteoinventariocicl.cve_usuario'; 
    $query = mysqli_query($conn, $sqlCount); 
    $count = 0; 
    if($query){ 
        $count = mysqli_fetch_array($query)['total']; 
    } 
 
         
 
    $sql = 'SELECT 
  det_planifica_inventario.ID_PLAN AS inventario, 
            t_conteoinventariocicl.NConteo AS conteo, 
            DATE_FORMAT(det_planifica_inventario.FECHA_APLICA, "%d-%m-%Y %H:%i:%s") AS fecha, 
            det_planifica_inventario.cve_articulo AS clave, 
            c_articulo.des_articulo AS descripcion, 
            t_invpiezasciclico.Cantidad AS cantidad,
            "nose" as serie
FROM
  t_invpiezasciclico 
  LEFT JOIN det_planifica_inventario 
    ON t_invpiezasciclico.ID_PLAN = det_planifica_inventario.ID_PLAN 
  LEFT JOIN t_conteoinventariocicl 
    ON t_conteoinventariocicl.ID_PLAN = det_planifica_inventario.ID_PLAN 
  LEFT JOIN c_usuario 
    ON c_usuario.cve_usuario = t_invpiezasciclico.cve_usuario 
  LEFT JOIN c_articulo 
    ON c_articulo.cve_articulo = t_invpiezasciclico.cve_articulo 
  LEFT JOIN ts_existenciapiezas 
    ON ts_existenciapiezas.cve_almac = t_invpiezasciclico.cve_articulo 
    AND ts_existenciapiezas.idy_ubica = t_invpiezasciclico.idy_ubica 
WHERE t_conteoinventariocicl.NConteo > 0 
GROUP BY t_conteoinventariocicl.ID_PLAN, t_conteoinventariocicl.NConteo, t_conteoinventariocicl.cve_usuario'  ; 
 
 
    $sql .= " LIMIT $page,$limit; "; 
 
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