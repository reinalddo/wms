<?php 
include '../../../config.php'; 

error_reporting(0); 

if(isset($_GET) && !empty($_GET) && $_GET['action'] != 'entrada'){ 
    $page = $_GET['start']; 
    $limit = $_GET['length'];  
    $search = $_GET['search']['value']; 
    $fecha = $_GET['fecha'];
    $ands = "";

    if(isset($fecha) && !empty($fecha)){
        $ands = " and ce.Fec_Entrada like '%$fecha%' ";
    }


    if($_GET['almacen']!=''){
        $almacen = " and al.id = '".$_GET['almacen']."'";
    }

    if($_GET['entrada']!=''){
        $inventario = " and de.fol_folio = ".$_GET['entrada']."";
    }

    if(isset($_GET['cve_proveedor']))
    {
      if($_GET['cve_proveedor'])
      {
          $cve_proveedor = $_GET['cve_proveedor'];
          $ands .= "AND p.ID_Proveedor = {$cve_proveedor}";
      }
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

        $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
        if (!($res = mysqli_query($conn, $sql_charset)))
            echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
        $charset = mysqli_fetch_array($res)['charset'];
        mysqli_set_charset($conn , $charset);
/*
    $sqlCount = "SELECT count(de.fol_folio) as total FROM  td_entalmacen de LEFT JOIN th_entalmacen ce ON ce.Fol_Folio = de.fol_folio {$ands}"; 
    $query = mysqli_query($conn, $sqlCount); 
    $count = 0; 
    if($query){ 
        $count = mysqli_fetch_array($query)['total']; 
    }
*/
    $sql = "SELECT  de.fol_folio AS entrada, 
                    al.nombre AS almacen, 
                    IFNULL(ad.factura, '') AS orden_entrada,
                    p.Nombre AS proveedor, 
                    DATE_FORMAT(ce.Fec_Entrada, '%d-%m-%Y %H:%i:%s') AS fecha_entrada, 
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = ce.Cve_Usuario) AS dio_entrada,
                    IFNULL(u.nombre_completo, '--') AS autorizo,
                    a.cve_articulo AS clave_articulo, 
                    a.des_articulo AS descripcion_articulo, 
                    de.CantidadRecibida AS cantidad_recibida 
            FROM td_entalmacen de
            LEFT JOIN th_entalmacen ce ON ce.Fol_Folio = de.fol_folio 
            LEFT JOIN th_aduana ad ON ad.num_pedimento = de.fol_folio 
            LEFT JOIN c_articulo a ON a.cve_articulo = de.cve_articulo 
            LEFT JOIN c_almacenp al ON al.clave = ce.Cve_Almac 
            LEFT JOIN c_usuario u ON u.cve_usuario = ce.Cve_Autorizado
            #LEFT JOIN c_proveedores p ON p.ID_Proveedor = ce.Cve_Proveedor
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = ce.Cve_Proveedor
            WHERE 1 #and ce.STATUS IN ('E','T')
            {$ands}
            {$almacen}
            {$inventario}
            group by de.fol_folio
            ORDER BY de.fol_folio DESC
            ";  
    if (!($res = mysqli_query($conn, $sql))) { 
        echo json_encode(array( "error" => "01 - Error al procesar la petición: (" . mysqli_error($conn) . ") ")); 
    } 
    $count = mysqli_num_rows($res);

    $sql .= " LIMIT {$page}, {$limit}; ";
    if (!($res = mysqli_query($conn, $sql))) { 
        echo json_encode(array( "error" => "01 - Error al procesar la petición: (" . mysqli_error($conn) . ") ")); 
    } 
    $data = array(); 
    $i = 0; 
    while ($row = mysqli_fetch_array($res)) { 
        $row = array_map('utf8_encode', $row); 
        $row['acciones'] = "<button class='btn btn-sm btn-default no' title='Ver Detalle' onclick='see(".$row["entrada"].")'> <i class='fa fa-search'></i> </button>";
        $row['acciones'] .= "&nbsp; <button class='btn btn-sm btn-danger no' title='Reporte PDF' onclick='printPDF(".$row["entrada"].")'> <i class='fa fa-print'></i><sup> PDF</sup></button>";
        $row['acciones'] .= "&nbsp; <button class='btn btn-sm btn-primary no' title='Reporte Excel' onclick='printExcel(".$row["entrada"].")'> <i class='fa fa-print'></i><sup> Excel</sup></button>";
        $data[] = $row; 
        $i++; 
    }  

    mysqli_close(); 
    header('Content-type: application/json'); 
    $output = array( 
        "draw" => $_GET["draw"], 
        "recordsTotal" => $count, 
        "recordsFiltered" => $count, 
        "data" => $data,
        "code" => "export_1",
        "sql" => $sql
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

    $sqlCount = "SELECT  COUNT(de.fol_folio) AS total
            FROM td_entalmacen de
            LEFT JOIN th_entalmacen ce ON ce.Fol_Folio = de.fol_folio 
            LEFT JOIN th_aduana ad ON ad.num_pedimento = de.fol_folio 
            LEFT JOIN c_articulo a ON a.cve_articulo = de.cve_articulo 
            LEFT JOIN c_almacenp al ON al.clave = ce.Cve_Almac 
            LEFT JOIN c_usuario u ON u.cve_usuario = ce.Cve_Autorizado
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = ce.Cve_Proveedor
            LEFT JOIN c_lotes ON c_lotes.LOTE = de.cve_lote
            LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo
            WHERE de.fol_folio='{$inventario}'
            GROUP BY de.`cve_articulo`,de.`cve_ubicacion`
            ";
    $query = mysqli_query($conn, $sqlCount); 
    if($query){ 
        $count = mysqli_fetch_all($query, MYSQLI_ASSOC); 
        $c_total = 0;
        foreach($count as $c){
            $c_total += $c['total'];            
        }
        $count = $c_total;
    } 

    $sql = "SELECT  de.fol_folio AS entrada, 
                    al.nombre AS almacen, 
                    IFNULL(ad.factura, '--') AS orden_entrada,
                    p.Nombre AS proveedor, 
                    DATE_FORMAT(ce.Fec_Entrada, '%d-%m-%Y') AS fecha_entrada, 
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = ce.Cve_Usuario) AS dio_entrada,
                    IFNULL(u.nombre_completo, '--') AS autorizo,
                    a.cve_articulo AS clave_articulo, 
                    a.des_articulo AS articulo, 
                    de.CantidadRecibida AS cantidad_recibida,
                    IF(a.control_lotes = 'S', IFNULL(c_lotes.`LOTE`,''), '') as lote,
                    IF(a.control_numero_series = 'S', IFNULL(c_serie.`numero_serie`,''), '') AS serie,
                    IF(a.Caduca = 'S', IFNULL(DATE_FORMAT(c_lotes.`CADUCIDAD`, '%d-%m-%Y'),''), '') AS caducidad,
                    (de.cantidadPedida) as total_pedido,
                    (de.cantidadPedida-de.CantidadRecibida) as faltante
            FROM td_entalmacen de
            LEFT JOIN th_entalmacen ce ON ce.Fol_Folio = de.fol_folio 
            LEFT JOIN th_aduana ad ON ad.num_pedimento = de.fol_folio 
            LEFT JOIN td_aduana  adu on adu.num_orden =ce.Fol_Folio
            LEFT JOIN c_articulo a ON a.cve_articulo = de.cve_articulo 
            LEFT JOIN c_almacenp al ON al.clave = ce.Cve_Almac 
            LEFT JOIN c_usuario u ON u.cve_usuario = ce.Cve_Autorizado
            #LEFT JOIN c_proveedores p ON p.ID_Proveedor = ce.Cve_Proveedor
            LEFT JOIN c_proveedores p ON p.cve_proveedor = ce.Proveedor
            LEFT JOIN c_lotes ON c_lotes.LOTE = de.cve_lote
            LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo
            WHERE de.fol_folio='{$inventario}'
            GROUP BY de.`cve_articulo`,de.`cve_ubicacion`
            LIMIT {$start},{$limit};
        ";
    if (!($res = mysqli_query($conn, $sql))) { 
        echo json_encode(array( "error" => "02 - Error al procesar la petición: (" . mysqli_error($conn) . ") ")); 
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
        "data" => $data,
        "code" => "export_2"
    );  
    echo json_encode($output); 
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'exportExcelinvfidet'){
    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $id = $_POST['id'];
    $title = "Reporte Detallado de Entradas_{$id}.xlsx";

    $sql = "SELECT  de.fol_folio AS entrada, 
                    al.nombre AS almacen, 
                    IFNULL(ad.factura, '--') AS orden_entrada,
                    p.Nombre AS proveedor, 
                    DATE_FORMAT(ce.Fec_Entrada, '%d-%m-%Y') AS fecha_entrada, 
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = ce.Cve_Usuario) AS dio_entrada,
                    IFNULL(u.nombre_completo, '--') AS autorizo,
                    a.cve_articulo AS clave_articulo, 
                    a.des_articulo AS articulo, 
                    de.CantidadRecibida AS cantidad_recibida,
                    #IFNULL(c_lotes.`LOTE`,'0') as lote,
                    #IFNULL(c_serie.`numero_serie`,'0') AS serie,
                    #IFNULL(c_lotes.`CADUCIDAD`,'0') AS caducidad,
                    #IF(a.control_lotes = 'S', c_lotes.Lote, IF(a.control_numero_series = 'S', c_serie.numero_serie, '')) AS lote_serie,
                    IFNULL(de.cve_lote, '') AS lote_serie,
                    IF(a.control_lotes = 'S' AND a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), '') AS caducidad,
                  IFNULL(de.cantidadPedida, de.CantidadRecibida) AS total_pedido,
                  IFNULL((de.cantidadPedida-de.CantidadRecibida), 0) AS faltante
            FROM td_entalmacen de
            LEFT JOIN th_entalmacen ce ON ce.Fol_Folio = de.fol_folio 
            LEFT JOIN th_aduana ad ON ad.num_pedimento = ce.id_ocompra
            LEFT JOIN td_aduana  adu on adu.num_orden =ce.Fol_Folio
            LEFT JOIN c_articulo a ON a.cve_articulo = de.cve_articulo 
            LEFT JOIN c_almacenp al ON al.clave = ce.Cve_Almac 
            LEFT JOIN c_usuario u ON u.cve_usuario = ce.Cve_Autorizado
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = ce.Cve_Proveedor
            LEFT JOIN c_lotes ON c_lotes.Lote = de.cve_lote AND c_lotes.cve_articulo = de.cve_articulo
            LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo AND c_serie.numero_serie = de.cve_lote
            WHERE de.fol_folio='{$id}'
          GROUP BY de.cve_articulo,de.cve_ubicacion, de.cve_lote
          ORDER BY clave_articulo, faltante DESC
        ";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($query, MYSQLI_ASSOC);


    $header = array(
        'Clave Artículo'    => 'string',
        'Articulo'          => 'string',
        'Lote|Serie'        => 'string',
        'Caducidad'         => 'string',
        'Total Pedido'      => 'string',
        'Cantidad Recibida' => 'string',
        'Cantidad Faltante' => 'string'
    );
    $excel = new XLSXWriter();
    $excel->writeSheetHeader('Sheet1', $header );


    foreach($data as $d){
        $row = array(
            $d['clave_articulo'],
            $d['articulo'],
            $d['lote_serie'],
            $d['caducidad'],
            $d['total_pedido'],
            $d['cantidad_recibida'],
            $d['faltante']
        );
        $excel->writeSheetRow('Sheet1', $row );
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
}

if($_GET['action'] === 'entrada'){
    $almacen = $_GET['almacen'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn, 'utf8');
    $inventories = ["code" => "export_4"];

    $query = mysqli_query($conn, "SELECT Fol_Folio AS n FROM th_entalmacen WHERE Cve_Almac = (SELECT clave FROM c_almacenp WHERE id = {$almacen})  #and  STATUS='E' 
        order by Fol_Folio DESC");
    if($query->num_rows > 0){
        $inventories = mysqli_fetch_all($query, MYSQLI_ASSOC);
    }
    echo json_encode($inventories);
}
