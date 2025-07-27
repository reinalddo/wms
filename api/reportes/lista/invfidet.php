<?php 
include '../../../config.php'; 

error_reporting(0);

if(isset($_GET) && !empty($_GET))
{
    $page = $_GET['start']; 
    $limit = $_GET['length'];  
    $search = $_GET['search']['value']; 

    if($_GET['fecha']!='')
    {
        $fecha = " and STR_TO_DATE(i.fecha_final, '%d-%m-%Y') = '".$_GET['fecha']."' ";
    }

    if($_GET['almacen']!='')
    {
        $almacen = " and i.almacen = (SELECT nombre FROM c_almacenp WHERE id = ".$_GET['almacen'].")";
    }

    if($_GET['inventario']!='')
    {
        $inventario = " and i.consecutivo = ".$_GET['inventario']."";
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');

    $sqlCount = "
        SELECT  COUNT(*) AS total
        FROM V_AdministracionInventario i 
        WHERE i.status = 'Cerrado'
            $fecha
            $almacen
            $inventario
        ORDER BY i.consecutivo DESC;
    ";
    $query = mysqli_query($conn, $sqlCount); 
    $count = 0; 
    if($query)
    { 
        $count = mysqli_fetch_array($query)['total']; 
    } 

    $sql = "
        SELECT 
            i.almacen,
            i.consecutivo AS inventario,
            i.fecha_inicio,
            i.fecha_final,
            (SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = i.consecutivo) AS conteos,
            (SELECT COUNT(DISTINCT cve_articulo) FROM t_invpiezas WHERE ID_Inventario = i.consecutivo) AS nproductos,
            (SELECT SUM((p.Cantidad - p.ExistenciaTeorica)) FROM t_invpiezas p WHERE p.ID_Inventario = i.consecutivo AND p.NConteo > 0) AS diferencias,
            i.usuario AS supervisor
        FROM V_AdministracionInventario i 
        WHERE i.status = 'Cerrado'
            $fecha
            $almacen
            $inventario
        ORDER BY i.consecutivo DESC
        LIMIT {$page}, {$limit};
    ";

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") ")); 
    } 

    $data = array(); 
    $i = 0; 
    while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) 
    { 
        $row['acciones'] = "<button class='btn btn-sm btn-default no' title='Ver Detalle' onclick='see(".$row["inventario"].")'> <i class='fa fa-search'></i> </button>";
        $row['acciones'] .= "&nbsp; <button class='btn btn-sm btn-danger no' title='Reporte PDF' onclick='printPDF(".$row["inventario"].")'> <i class='fa fa-print'></i><sup> PDF</sup></button>";
        $row['acciones'] .= "&nbsp; <button class='btn btn-sm btn-primary no' title='Reporte Excel' onclick='printExcel(".$row["inventario"].")'> <i class='fa fa-print'></i><sup> Excel</sup></button>";
        $data[] = $row; 
        $i++; 
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

if(isset($_POST) && !empty($_POST) && !isset($_POST['action']))
{ 
    $start = $_POST['start']; 
    $limit = $_POST['length'];  
    $search = $_POST['search']['value']; 
    $inventario = $_POST['inventario'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');

    $sqlCount = "
        SELECT  COUNT(*) AS total
        FROM V_ExistenciaFisica 
        WHERE V_ExistenciaFisica.idy_ubica in ( select distinct cve_ubicacion from V_Ubicacion_Inventario where ID_Inventario = {$inventario} AND tipo is not null)
            and V_ExistenciaFisica.ID_Inventario = {$inventario} AND V_ExistenciaFisica.NConteo > 0
        GROUP BY V_ExistenciaFisica.ID_Inventario, V_ExistenciaFisica.cve_articulo, V_ExistenciaFisica.ubicacion, V_ExistenciaFisica.NConteo;
    ";
    $query = mysqli_query($conn, $sqlCount); 
    if($query)
    {
        $count = mysqli_fetch_all($query, MYSQLI_ASSOC); 
        $c_total = 0;
        foreach($count as $c)
        {
            $c_total += $c['total'];            
        }
        $count = $c_total;
    } 

    $sql = "
        Select
            Coalesce(c_ubicacion.CodigoCSD, tubicacionesretencion.desc_ubicacion) as ubicacion,
            t_invpiezas.cve_articulo as clave,
            c_articulo.des_articulo as descripcion,
            IF(t_invpiezas.cve_lote = '','--',t_invpiezas.cve_lote) as lote,
            Ifnull(Date_format(c_lotes.caducidad, '%d-%m-%Y'), '--') as caducidad,
            '--' as serie,
            t_invpiezas.NConteo as conteo,
            (t_invpiezas.existenciateorica) as stockTeorico,
            (t_invpiezas.cantidad) as stockFisico,
            (t_invpiezas.cantidad - t_invpiezas.existenciateorica) as diferencia,
            c_usuario.nombre_completo as usuario,
            'Piezas' as unidad_medida,
            c_almacen.des_almac as zona
        FROM t_invpiezas
            LEFT JOIN t_ubicacioninventario on t_ubicacioninventario.id_inventario = t_invpiezas.id_inventario
            LEFT JOIN c_articulo on c_articulo.cve_articulo = t_invpiezas.cve_articulo
            LEFT JOIN c_lotes on c_lotes.lote = t_invpiezas.cve_lote AND t_invpiezas.cve_articulo = c_lotes.cve_articulo
            LEFT JOIN c_serie on c_serie.cve_articulo = t_invpiezas.cve_articulo 
            LEFT JOIN c_usuario on c_usuario.cve_usuario = t_invpiezas.cve_usuario
            LEFT JOIN tubicacionesretencion ON tubicacionesretencion.cve_ubicacion = t_ubicacioninventario.cve_ubicacion
            LEFT JOIN c_ubicacion ON t_ubicacioninventario.idy_ubica = c_ubicacion.idy_ubica
            LEFT JOIN c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
        WHERE  t_invpiezas.id_inventario = $inventario
        and t_invpiezas.NConteo = (select MAX(NConteo) from t_invpiezas where ID_Inventario = {$inventario})
        and cantidad != ExistenciaTeorica
        order by  t_invpiezas.NConteo, c_articulo.des_articulo
        LIMIT {$start},{$limit};
    ";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") ")); 
    } 

    $data = array(); 
    $i = 0; 
    while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) 
    {
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

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'exportExcelinvfidet')
{
    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $id = $_POST['id'];
    $id_title = str_pad($id, 2, 0, STR_PAD_LEFT);
    $title = "ReporteInventarioFisico_{$id_title}.xlsx";

  /*  $sqlCabecera = "
         Select 
                c_almacenp.nombre as almacen,
                t_invpiezas.ID_Inventario as inventario,
                t_invpiezas.fecha as fecha_inicio,
                max(t_invpiezas.fecha_fin) as fecha_final,
                max(t_invpiezas.NConteo) as conteos,
                count(distinct(t_invpiezas.cve_articulo)) as nproductos,
                sum(if(t_invpiezas.NConteo = (select max(a.NConteo) from t_invpiezas a where a.ID_Inventario = t_invpiezas.ID_Inventario),(t_invpiezas.Cantidad - t_invpiezas.ExistenciaTeorica),0)) as diferencias,
                (select t_conteoinventario.cve_supervisor from t_conteoinventario where t_conteoinventario.ID_Inventario = t_invpiezas.ID_Inventario GROUP by t_conteoinventario.cve_supervisor)as supervisor
            from t_invpiezas
                left join c_ubicacion on c_ubicacion.idy_ubica = t_invpiezas.idy_ubica
                left join c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
                left join c_almacenp on c_almacenp.id = c_almacen.cve_almacenp
            where ID_Inventario = {$id};
    ";*/

    /*$sql = "
       Select
            Coalesce(c_ubicacion.CodigoCSD, tubicacionesretencion.desc_ubicacion) as ubicacion,
            t_invpiezas.cve_articulo as clave,
            c_articulo.des_articulo as descripcion,
            IF(t_invpiezas.cve_lote = '','--',t_invpiezas.cve_lote) as lote,
            Ifnull(Date_format(c_lotes.caducidad, '%d-%m-%Y'), '--') as caducidad,
            '--' as serie,
            t_invpiezas.NConteo as conteo,
            (t_invpiezas.existenciateorica) as stockTeorico,
            (t_invpiezas.cantidad) as stockFisico,
            (t_invpiezas.cantidad - t_invpiezas.existenciateorica) as diferencia,
            ifnull(c_usuario.nombre_completo,'--') as usuario,
            'Piezas' as unidad_medida,
            c_almacen.des_almac as zona
        FROM t_invpiezas
            LEFT JOIN t_ubicacioninventario on t_ubicacioninventario.id_inventario = t_invpiezas.id_inventario
            LEFT JOIN c_articulo on c_articulo.cve_articulo = t_invpiezas.cve_articulo
            LEFT JOIN c_lotes on c_lotes.lote = t_invpiezas.cve_lote AND t_invpiezas.cve_articulo = c_lotes.cve_articulo
            LEFT JOIN c_serie on c_serie.cve_articulo = t_invpiezas.cve_articulo 
            LEFT JOIN c_usuario on c_usuario.cve_usuario = t_invpiezas.cve_usuario
            LEFT JOIN tubicacionesretencion ON tubicacionesretencion.cve_ubicacion = t_ubicacioninventario.cve_ubicacion
            LEFT JOIN c_ubicacion ON t_ubicacioninventario.idy_ubica = c_ubicacion.idy_ubica
            LEFT JOIN c_almacen on c_almacen.cve_almac = c_ubicacion.cve_almac
        WHERE  t_invpiezas.id_inventario = $id
        order by  t_invpiezas.NConteo, c_articulo.des_articulo;
    ";*/
  
    $sql="
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
        where t_invpiezas.ID_Inventario = {$id}
            and t_invpiezas.NConteo = (select MAX(NConteo) from t_invpiezas where ID_Inventario = {$id})
            and cantidad != ExistenciaTeorica
        GROUP by t_invpiezas.cve_articulo;
    ";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');
    $queryCabecera = mysqli_query($conn, $sqlCabecera);
    $dataCabecera = mysqli_fetch_all($queryCabecera, MYSQLI_ASSOC)[0];
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($query, MYSQLI_ASSOC);
    $header_head = array('Inventario Físico','Almacén','Supervisor');
    $body_head = array($data[0]['folio'],$data[0]['almacen'],$data[0]['usuario']);
    $header_body = array('Clave','Descripción','Ubicación','Stock Teórico','Stock Físico','Diferencia','Stock Final');
    $excel = new XLSXWriter();
    $excel->writeSheetRow('Sheet1', $header_head );
    $excel->writeSheetRow('Sheet1', $body_head );
    $excel->writeSheetRow('Sheet1', $header_body );
    foreach($data as $d)
    {
        $row = array($d['clave'],$d['descripcion'],$d['ubicacion'],$d['stockTeorico'],$d['stockFisico'],$d['diferencia'],$d['final']);
        $excel->writeSheetRow('Sheet1', $row );
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
}