<?php 
include '../../../config.php'; 

error_reporting(0);

if(isset($_GET) && !empty($_GET)){ 
    $page = $_GET['start']; 
    $limit = $_GET['length'];  
    $search = $_GET['search']['value']; 

    if($_GET['fecha']!=''){
        $fecha = " and STR_TO_DATE(i.fecha_final, '%d-%m-%Y') = '".$_GET['fecha']."' ";
    }

    if($_GET['almacen']!=''){
        $almacen = " and i.almacen = (SELECT nombre FROM c_almacenp WHERE id = ".$_GET['almacen'].")";
    }

    if($_GET['inventario']!=''){
        $inventario = " and i.consecutivo = ".$_GET['inventario']."";
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');

    $sqlCount = "SELECT  COUNT(*) AS total
            FROM V_AdministracionInventario i 
            INNER JOIN V_Inventario AS b
            WHERE i.`diferencia`!=0
            AND b.`ID_Inventario`=i.`consecutivo`
            AND b.`NConteo`>0
            GROUP BY i.`consecutivo`
            ORDER BY i.consecutivo DESC";
    $query = mysqli_query($conn, $sqlCount); 
    $count = 0; 
    if($query){ 
        $count = mysqli_fetch_array($query)['total']; 
    } 

    $sql = "SELECT  i.almacen,
                    i.usuario,
                    i.consecutivo AS inventario,
                    i.consecutivo AS ajuste,
                    ABS(i.diferencia) as diferencia,
                    b.`fecha` AS fecha
            FROM V_AdministracionInventario i 
            INNER JOIN V_Inventario AS b
            WHERE i.`diferencia`!=0
            AND b.`ID_Inventario`=i.`consecutivo`
            AND b.`NConteo`>0
            GROUP BY i.`consecutivo`
            ORDER BY i.consecutivo DESC
            LIMIT {$page}, {$limit};";

    if (!($res = mysqli_query($conn, $sql))) { 
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") ")); 
    } 

    $data = array(); 
    $i = 0; 
    $count =1;
    while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) { 
        $row['acciones'] = "<button class='btn btn-sm btn-default no' title='Ver Detalle' onclick='see(".$row["inventario"].")'> <i class='fa fa-search'></i> </button>";
        $row['acciones'] .= "&nbsp; <button class='btn btn-sm btn-danger no' title='Reporte PDF' onclick='printPDF(".$row["inventario"].")'> <i class='fa fa-print'></i><sup> PDF</sup></button>";
        $row['acciones'] .= "&nbsp; <button class='btn btn-sm btn-primary no' title='Reporte Excel' onclick='printExcel(".$row["inventario"].")'> <i class='fa fa-print'></i><sup> Excel</sup></button>";
        $i++;
        $row['ajuste'] = $i;
        $data[] = $row; 
        
        
    }  

    mysqli_close($conn); 
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

    $sqlCount = "SELECT  COUNT(*) AS total
                FROM V_ExistenciaFisica 
                where V_ExistenciaFisica.idy_ubica 
            in (select distinct cve_ubicacion from V_Ubicacion_Inventario where ID_Inventario = {$inventario} AND tipo is not null)
            and V_ExistenciaFisica.ID_Inventario = {$inventario} AND V_ExistenciaFisica.NConteo > 0
            GROUP BY V_ExistenciaFisica.ID_Inventario, V_ExistenciaFisica.cve_articulo, V_ExistenciaFisica.ubicacion, V_ExistenciaFisica.NConteo";
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
                    V_ExistenciaFisica.ubicacion,
                    ifnull(c_articulo.cve_articulo, '--') as clave,
                    ifnull(c_articulo.des_articulo, '--') as descripcion,
                    (SELECT ExistenciaTeorica FROM t_invpiezas WHERE ID_Inventario = {$inventario} AND cve_articulo = V_ExistenciaFisica.cve_articulo AND idy_ubica = V_ExistenciaFisica.idy_ubica AND NConteo = V_ExistenciaFisica.NConteo) as stockTeorico,
                    (SELECT Cantidad FROM t_invpiezas WHERE ID_Inventario = {$inventario} AND cve_articulo = V_ExistenciaFisica.cve_articulo AND idy_ubica = V_ExistenciaFisica.idy_ubica AND NConteo = V_ExistenciaFisica.NConteo) as stockFisico,
                    ifnull(((SELECT stockFisico) - (SELECT stockTeorico)), '--') as diferencia,
                    V_ExistenciaFisica.NConteo as conteo,
                    V_ExistenciaFisica.usuario
            from V_ExistenciaFisica 
            left join c_articulo on c_articulo.cve_articulo = V_ExistenciaFisica.cve_articulo
            left join c_lotes on c_lotes.LOTE = V_ExistenciaFisica.cve_lote
            left join c_serie on c_serie.cve_articulo = c_articulo.cve_articulo
            left join V_ExistenciaGral on V_ExistenciaGral.cve_ubicacion = V_ExistenciaFisica.idy_ubica and V_ExistenciaGral.cve_articulo = V_ExistenciaFisica.cve_articulo
            where V_ExistenciaFisica.idy_ubica 
            in (select distinct cve_ubicacion from V_Ubicacion_Inventario where ID_Inventario = {$inventario} AND tipo is not null)
            and V_ExistenciaFisica.ID_Inventario = {$inventario} AND V_ExistenciaFisica.NConteo > 0
            GROUP BY V_ExistenciaFisica.ID_Inventario, V_ExistenciaFisica.cve_articulo, V_ExistenciaFisica.ubicacion, V_ExistenciaFisica.NConteo
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
    $id = $_POST['id'];
    $id_title = str_pad($id, 2, 0, STR_PAD_LEFT);
    $title = "ReporteAjustesExistencia_{$id_title}.xlsx";

    $sqlCabecera = "SELECT  i.almacen,
                    i.consecutivo AS inventario,
                    i.fecha_final AS fecha
            FROM V_AdministracionInventario i 
            WHERE i.status = 'Cerrado' AND i.consecutivo = {$id};";

    $sql = " select 
            t_invpiezas.ID_Inventario as folio,
            c_ubicacion.CodigoCSD as ubicacion,
            t_invpiezas.cve_articulo as clave,
            c_articulo.des_articulo as descripcion,
            ExistenciaTeorica as stockTeorico,
            Cantidad as stockFisico,
            Cantidad - ExistenciaTeorica as diferencia,
            cantidad as final,
            t_invpiezas.NConteo as conteo,
            c_usuario.nombre_completo as usuario,
            c_almacenp.nombre as almacen
        from t_invpiezas
            INNER JOIN c_articulo on c_articulo.cve_articulo = t_invpiezas.cve_articulo
            INNER JOIN c_ubicacion on c_ubicacion.idy_ubica = t_invpiezas.idy_ubica
            INNER JOIN c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
            INNER JOIN c_almacenp on c_almacen.cve_almacenp = c_almacenp.id
            INNER JOIN t_conteoinventario on t_conteoinventario.ID_Inventario = t_invpiezas.ID_Inventario
            INNER JOIN c_usuario on c_usuario.cve_usuario = t_conteoinventario.cve_supervisor
        where t_invpiezas.ID_Inventario = {$id}
            and t_invpiezas.NConteo = (select MAX(NConteo) from t_invpiezas where ID_Inventario = {$id})
            and cantidad != ExistenciaTeorica
        GROUP by t_invpiezas.cve_articulo;";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');
    $queryCabecera = mysqli_query($conn, $sqlCabecera);
    $dataCabecera = mysqli_fetch_all($queryCabecera, MYSQLI_ASSOC)[0];
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($query, MYSQLI_ASSOC);
    $header_head = array(
        'Almacén',
        'Nº de Inventario',
        'Fecha'
    );
    $body_head = array(
        $dataCabecera['almacen'],
        $dataCabecera['inventario'],
        $dataCabecera['fecha']
    );
    $header_body = array(
        'Conteo',
        'Clave',
        'Descripción',
        'Ubicación',
        'Stock Teórico',
        'Stock Físico',
        'Diferencia',
        'Usuario'
    );
    $excel = new XLSXWriter();
    $excel->writeSheetRow('Sheet1', $header_head );
    $excel->writeSheetRow('Sheet1', $body_head );
    $excel->writeSheetRow('Sheet1', $header_body );
    foreach($data as $d){
        $row = array(
            $d['conteo'],
            $d['clave'],
            $d['descripcion'],
            $d['ubicacion'],
            $d['stockTeorico'],
            $d['stockFisico'],
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

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'exportPDFinvfidet'){

    $inventario = $_POST['inventario'];
  
    $sql= "
        select 
            t_invpiezas.ID_Inventario as folio,
            c_ubicacion.CodigoCSD as ubicacion,
            t_invpiezas.cve_articulo as clave,
            c_articulo.des_articulo as descripcion,
            ExistenciaTeorica as stockTeorico,
            Cantidad as stockFisico,
            Cantidad - ExistenciaTeorica as diferencia,
            cantidad as final,
            t_invpiezas.NConteo as conteo,
            c_usuario.nombre_completo as usuario,
            c_almacenp.nombre as almacen
        from t_invpiezas
            INNER JOIN c_articulo on c_articulo.cve_articulo = t_invpiezas.cve_articulo
            INNER JOIN c_ubicacion on c_ubicacion.idy_ubica = t_invpiezas.idy_ubica
            INNER JOIN c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
            INNER JOIN c_almacenp on c_almacen.cve_almacenp = c_almacenp.id
            INNER JOIN t_conteoinventario on t_conteoinventario.ID_Inventario = t_invpiezas.ID_Inventario
            INNER JOIN c_usuario on c_usuario.cve_usuario = t_conteoinventario.cve_supervisor
        where t_invpiezas.ID_Inventario = {$inventario}
            and t_invpiezas.NConteo = (select MAX(NConteo) from t_invpiezas where ID_Inventario = {$inventario})
            and cantidad != ExistenciaTeorica
        GROUP by t_invpiezas.cve_articulo;
    ";

    $res = getArraySQL($sql);
    echo json_encode($res); 
}

function getArraySQL($sql){

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    if(!$result = mysqli_query($conexion, $sql)) 
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";;

    $rawdata = array();

    $i = 0;

    while($row = mysqli_fetch_assoc($result))
    {
        $rawdata[$i] = $row;
        $i++;
    }

    mysqli_close($conexion);

    return $rawdata;
}