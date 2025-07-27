<?php 
include '../../../config.php'; 
 
error_reporting(0); 
 
if(isset($_GET) && !empty($_GET)){ 
    $page = $_GET['start']; 
    $limit = $_GET['length'];  
    $search = $_GET['search']['value']; 
  
  
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
 
    $sqlCount = 'SELECT  
COUNT(hi.FECHA_APLICA) AS total
            FROM det_planifica_inventario hi 
            LEFT JOIN t_conteoinventariocicl ci ON ci.ID_PLAN = hi.ID_PLAN
            LEFT JOIN c_usuario us ON us.cve_usuario = ci.cve_usuario
            LEFT JOIN t_invpiezasciclico ip ON ip.ID_PLAN = hi.ID_PLAN AND ip.NConteo = ci.NConteo
            LEFT JOIN c_articulo ar ON ar.cve_articulo = ip.cve_articulo
            LEFT JOIN c_ubicacion ub ON ub.idy_ubica = ip.idy_ubica
	    LEFT JOIN c_almacen alp ON alp.cve_almac = ub.cve_almac
            LEFT JOIN c_almacenp al ON al.id = alp.cve_almacenp            
            LEFT JOIN c_lotes l ON l.cve_articulo = ip.cve_articulo AND l.LOTE = ip.cve_lote
            LEFT JOIN c_serie s ON s.clave_articulo = ip.cve_articulo
            WHERE ci.NConteo > 0
            GROUP BY hi.ID_PLAN/*,ip.`idy_ubica`, ip.NConteo*/
            ORDER BY hi.ID_PLAN DESC, ip.NConteo ASC
            '; 
    $query = mysqli_query($conn, $sqlCount); 
    $count = 0; 
    if($query){ 
        $count = mysqli_fetch_array($query)['total']; 
    } 
 
         
 
    $sql = "SELECT  
al.nombre AS almacen,   
                    us.nombre_completo AS usuario,
                    hi.ID_PLAN AS inventario,
                    DATE_FORMAT(hi.FECHA_APLICA, '%d-%m-%Y %H:%i:%s') AS fecha, 
                    ip.cve_articulo AS clave_articulo,
                    ar.des_articulo AS descripcion_articulo,
                    ub.CodigoCSD AS ubicacion,
                    l.LOTE AS lote,
                    l.CADUCIDAD AS caducidad,
                    s.numero_serie AS numero_serie,
                    ip.ExistenciaTeorica AS existencia,
                    ip.NConteo AS conteo,
                    (ip.Cantidad - ip.ExistenciaTeorica) AS diferencia
            FROM det_planifica_inventario hi 
            LEFT JOIN t_conteoinventariocicl ci ON ci.ID_PLAN = hi.ID_PLAN
            LEFT JOIN c_usuario us ON us.cve_usuario = ci.cve_usuario
            LEFT JOIN t_invpiezasciclico ip ON ip.ID_PLAN = hi.ID_PLAN AND ip.NConteo = ci.NConteo
            LEFT JOIN c_articulo ar ON ar.cve_articulo = ip.cve_articulo
            LEFT JOIN c_ubicacion ub ON ub.idy_ubica = ip.idy_ubica
	    LEFT JOIN c_almacen alp ON alp.cve_almac = ub.cve_almac
            LEFT JOIN c_almacenp al ON al.id = alp.cve_almacenp            
            LEFT JOIN c_lotes l ON l.cve_articulo = ip.cve_articulo AND l.LOTE = ip.cve_lote
            LEFT JOIN c_serie s ON s.clave_articulo = ip.cve_articulo
            WHERE ci.NConteo > 0
            GROUP BY hi.ID_PLAN/*,ip.`idy_ubica`, ip.NConteo*/
            ORDER BY hi.ID_PLAN DESC, ip.NConteo ASC"  ; 
 
 
    $sql .= " LIMIT $page,$limit; "; 
 
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

if(isset($_POST) && !empty($_POST) && !isset($_POST['action'])){ 
    $start = $_POST['start']; 
    $limit = $_POST['length'];  
    $search = $_POST['search']['value']; 
    $inventario = $_POST['inventario'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');

    $sqlCount = "SELECT  
COUNT(hi.FECHA_APLICA) AS total
            FROM det_planifica_inventario hi 
            LEFT JOIN t_conteoinventariocicl ci ON ci.ID_PLAN = hi.ID_PLAN
            LEFT JOIN c_usuario us ON us.cve_usuario = ci.cve_usuario
            LEFT JOIN t_invpiezasciclico ip ON ip.ID_PLAN = hi.ID_PLAN AND ip.NConteo = ci.NConteo
            LEFT JOIN c_articulo ar ON ar.cve_articulo = ip.cve_articulo
            LEFT JOIN c_ubicacion ub ON ub.idy_ubica = ip.idy_ubica
	    LEFT JOIN c_almacen alp ON alp.cve_almac = ub.cve_almac
            LEFT JOIN c_almacenp al ON al.id = alp.cve_almacenp            
            LEFT JOIN c_lotes l ON l.cve_articulo = ip.cve_articulo AND l.LOTE = ip.cve_lote
            LEFT JOIN c_serie s ON s.clave_articulo = ip.cve_articulo
            WHERE ci.NConteo > 0
            and hi.ID_PLAN={$inventario}
            GROUP BY hi.ID_PLAN,ip.`idy_ubica`, ip.NConteo
            ORDER BY hi.ID_PLAN DESC, ip.NConteo ASC";
    $query = mysqli_query($conn, $sqlCount); 
    if($query){ 
        $count = mysqli_fetch_all($query, MYSQLI_ASSOC); 
        $c_total = 0;
        foreach($count as $c){
            $c_total += $c['total'];            
        }
        $count = $c_total;
    } 

    $sql = "SELECT  
al.nombre AS almacen,   
                    us.nombre_completo AS usuario,
                    hi.ID_PLAN AS inventario,
                    DATE_FORMAT(hi.FECHA_APLICA, '%d-%m-%Y %H:%i:%s') AS fecha, 
                    ip.cve_articulo AS clave_articulo,
                    ar.des_articulo AS descripcion_articulo,
                    ip.`ExistenciaTeorica` AS stockTeorico,
                    ip.NConteo AS conteo,
                    (SELECT Cantidad FROM t_invpiezasciclico WHERE ID_PLAN = {$inventario} AND cve_articulo = ip.cve_articulo AND idy_ubica =ip.idy_ubica AND NConteo = ip.NConteo) AS stockFisico,
                    ub.CodigoCSD AS ubicacion,
                    IFNULL(l.LOTE,'0') AS lote,
                    IFNULL(l.CADUCIDAD,'0') AS caducidad,
                    IFNULL(s.numero_serie,'0') AS numero_serie,
                    IFNULL(ip.ExistenciaTeorica,'0') AS existencia,
                    (ip.Cantidad - ip.ExistenciaTeorica) AS diferencia
            FROM det_planifica_inventario hi 
            LEFT JOIN t_conteoinventariocicl ci ON ci.ID_PLAN = hi.ID_PLAN
            LEFT JOIN c_usuario us ON us.cve_usuario = ci.cve_usuario
            LEFT JOIN t_invpiezasciclico ip ON ip.ID_PLAN = hi.ID_PLAN AND ip.NConteo = ci.NConteo
            LEFT JOIN c_articulo ar ON ar.cve_articulo = ip.cve_articulo
            LEFT JOIN c_ubicacion ub ON ub.idy_ubica = ip.idy_ubica
	    LEFT JOIN c_almacen alp ON alp.cve_almac = ub.cve_almac
            LEFT JOIN c_almacenp al ON al.id = alp.cve_almacenp            
            LEFT JOIN c_lotes l ON l.cve_articulo = ip.cve_articulo AND l.LOTE = ip.cve_lote
            LEFT JOIN c_serie s ON s.clave_articulo = ip.cve_articulo
            WHERE ci.NConteo > 0
            AND hi.ID_PLAN={$inventario}
            GROUP BY hi.ID_PLAN,ip.`idy_ubica`, ip.NConteo
            ORDER BY hi.ID_PLAN DESC, ip.`idy_ubica` ASC, ip.NConteo ASC
            LIMIT {$start},{$limit};
        ";
    if (!($res = mysqli_query($conn, $sql))) { 
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") ")); 
    } 

    $data = array(); 
    $i = 0; 
    while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) { 
        $data[] = $row; 
        $i++; 
    }  

    mysqli_close($conn); 
    header('Content-type: application/json'); 
    $output = array( 
        "draw" => $_POST["draw"], 
        "recordsTotal" => $count, 
        "recordsFiltered" => $count, 
        "data" => $data 
    );  
    echo json_encode($output); 
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'exportExcelinvfidet'){
    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = "Reporte Detallado de Inventario Ciclico.xlsx";
    $id = $_POST['id'];

    $sql = "SELECT  
al.nombre AS almacen,   
                    us.nombre_completo AS usuario,
                    hi.ID_PLAN AS inventario,
                    DATE_FORMAT(hi.FECHA_APLICA, '%d-%m-%Y %H:%i:%s') AS fecha, 
                    ip.cve_articulo AS clave_articulo,
                    ar.des_articulo AS descripcion_articulo,
                    ip.`ExistenciaTeorica` AS stockTeorico,
                    ip.NConteo AS conteo,
                    (SELECT Cantidad FROM t_invpiezasciclico WHERE ID_PLAN = {$id} AND cve_articulo = ip.cve_articulo AND idy_ubica =ip.idy_ubica AND NConteo = ip.NConteo) AS stockFisico,
                    ub.CodigoCSD AS ubicacion,
                    IFNULL(l.LOTE,'0') AS lote,
                    IFNULL(l.CADUCIDAD,'0') AS caducidad,
                    IFNULL(s.numero_serie,'0') AS numero_serie,
                    IFNULL(ip.ExistenciaTeorica,'0') AS existencia,
                    (ip.Cantidad - ip.ExistenciaTeorica) AS diferencia
            FROM det_planifica_inventario hi 
            LEFT JOIN t_conteoinventariocicl ci ON ci.ID_PLAN = hi.ID_PLAN
            LEFT JOIN c_usuario us ON us.cve_usuario = ci.cve_usuario
            LEFT JOIN t_invpiezasciclico ip ON ip.ID_PLAN = hi.ID_PLAN AND ip.NConteo = ci.NConteo
            LEFT JOIN c_articulo ar ON ar.cve_articulo = ip.cve_articulo
            LEFT JOIN c_ubicacion ub ON ub.idy_ubica = ip.idy_ubica
	    LEFT JOIN c_almacen alp ON alp.cve_almac = ub.cve_almac
            LEFT JOIN c_almacenp al ON al.id = alp.cve_almacenp            
            LEFT JOIN c_lotes l ON l.cve_articulo = ip.cve_articulo AND l.LOTE = ip.cve_lote
            LEFT JOIN c_serie s ON s.clave_articulo = ip.cve_articulo
            WHERE ci.NConteo > 0
            AND hi.ID_PLAN={$id}
            GROUP BY hi.ID_PLAN,ip.`idy_ubica`, ip.NConteo
            ORDER BY hi.ID_PLAN DESC, ip.`idy_ubica` ASC, ip.NConteo ASC
        ";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($query, MYSQLI_ASSOC);


    $header = array(
        'Conteo'                     => 'string',
        'Clave'                      => 'string',
        'Descripción'                => 'string',
        'Ubicación'                  => 'string',
        'Lote'                      => 'string',
        'Caducidad'                 => 'string',
        'Numero de Serie'               => 'string',
        'Stock Teórico'             => 'string',
        'Stock Físico'              => 'string',
        'Existencia'                => 'string',
        'Diferencia'                 => 'string',
        'Usuario'                   => 'string'
    );
    $excel = new XLSXWriter();
    $excel->writeSheetHeader('Sheet1', $header );

    foreach($data as $d){
        $row = array(
            $d['conteo'],
            $d['clave_articulo'],
            $d['descripcion_articulo'],
            $d['ubicacion'],
            $d['lote'],
            $d['caducidad'],
            $d['numero_serie'],
            $d['stockTeorico'],
            $d['stockFisico'],
            $d['existencia'],
            $d['diferencia'],
            $d['usuario']
        );
        $excel->writeSheetRow('Sheet1', $row );
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
}