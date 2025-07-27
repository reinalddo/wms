<?php
include '../../../config.php';

error_reporting(0);

if(isset($_GET) && !empty($_GET)){
    $page = $_GET['start'];
    $limit = $_GET['length'];
    $search = $_GET['search']['value'];

    $articulo = $_GET["articulo"];
    $almacen = $_GET["almacen"];
    $zona = $_GET["zona"];
    $proveedor = $_GET["proveedor"];
    $bl = $_GET["bl"];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn,"utf8");
  
    $sql1 = 'SELECT * FROM c_almacenp WHERE id = "'.$almacen.'"';
    $result = getArraySQL($sql1);
    $responce = array();
    $responce["bl"] = $result[0]["BL"];

    $sqlZona = !empty($zona) ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";
    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '{$articulo}'" : "";
    $sqlProveedor = !empty($proveedor) ? "AND x.proveedor = '{$proveedor}'" : "";
    $sqlbl = !empty($bl) ? "AND x.codigo like '%{$bl}%'" : "";

    $sqlCount = "SELECT
                          count(e.cve_articulo) as total
                  FROM V_ExistenciaGral e
                    LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                  WHERE e.cve_almac = '{$almacen}' AND e.tipo = 'ubicacion' AND e.Existencia > 0 {$sqlArticulo} {$sqlZona} {$sqlProveedor} {$sqlbl}";
    $query = mysqli_query($conn, $sqlCount);
    $count = 0;
    if($query){
        $count = mysqli_fetch_array($query)['total'];
    }

    /*Pedidos pendiente de acomodo*/
    $sql = "
    SELECT * FROM(
        SELECT
          c_almacenp.nombre as almacen,
          c_almacen.des_almac as zona,
          c_ubicacion.CodigoCSD as codigo,
          ts_existenciatarima.ntarima as clave,
          c_charolas.descripcion as descripcion,
          ts_existenciatarima.existencia as cantidad,
          (SELECT DATE_FORMAT(MAX(td_entalmacen.fecha_fin),'%d-%m-%Y %H:%i:%s %p'))  as fecha_ingreso,
          CONCAT(
          CASE
          WHEN c_ubicacion.Tipo = 'L' THEN 'Libre'
          WHEN c_ubicacion.Tipo = 'R' THEN 'Reservada'
          WHEN c_ubicacion.Tipo = 'Q' THEN 'Cuarentena'
          END, '| Picking ',
          CASE 
          WHEN c_ubicacion.Picking = 'S' THEN '<i class=\"fa fa-check text-success\"></i>'
          WHEN c_ubicacion.Picking = 'N' THEN '<i class=\"fa fa-times text-danger\"></i>'
          END
          ) AS tipo_ubicacion
      FROM ts_existenciatarima
          LEFT JOIN c_almacen ON c_almacen.cve_almacenp = ts_existenciatarima.cve_almac
          LEFT JOIN c_almacenp ON c_almacenp.id = c_almacen.cve_almacenp and c_almacenp.id = ts_existenciatarima.cve_almac
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = ts_existenciatarima.cve_articulo
          LEFT JOIN c_charolas ON c_charolas.IDContenedor = ts_existenciatarima.ntarima
          LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = ts_existenciatarima.idy_ubica
          LEFT JOIN td_entalmacen ON td_entalmacen.fol_folio = ts_existenciatarima.Fol_Folio
      WHERE ts_existenciatarima.cve_almac = 3
        AND ts_existenciatarima.existencia > 0   {$sqlZona}
        #{$sqlArticulo}
      GROUP BY ts_existenciatarima.ntarima 
      ORDER BY ts_existenciatarima.ntarima ASC
        )x
      WHERE 1
        {$sqlbl}
    ";
  
    $l = " LIMIT $page,$limit; ";
    $sql .= $l;
  
    //echo var_dump($sql);
    //die();
  
    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        //$row = array_map("utf8_encode", $row);
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
        "sql" => $sql,
        "bl" => $responce
    );
    echo json_encode($output);exit;
}


if(isset($_POST) && !empty($_POST) && $_POST['action'] === "exportExcelExistenciaUbica"){

    $almacen = $_POST['almacen'];
    $zona = $_POST['zona'];
    $articulo = $_POST['articulo'];

    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = "Reporte de existencia por ubicación.xlsx";

    $sqlZona = !empty($zona) ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '$zona')" : "";
    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '$articulo'" : "";
    $sql = "SELECT
                        ap.nombre as almacen,
                        z.des_almac as zona,
                        u.CodigoCSD as codigo,
                        a.cve_articulo as clave,
                        a.des_articulo as descripcion,
                        COALESCE(l.LOTE, '--') as lote,
                        COALESCE(l.CADUCIDAD, '--') as caducidad,
                        COALESCE(s.numero_serie, '--') as nserie,
                        e.Existencia as cantidad,
                        (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote LIMIT 1) AS folio,
                        COALESCE((SELECT Nombre FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = (SELECT folio))),'--') AS proveedor, 
                        COALESCE((SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote ORDER BY id DESC LIMIT 1), '--') AS fecha_ingreso,
                        CONCAT(CASE
                                    WHEN u.Tipo = 'L' THEN 'Libre'
                                    WHEN u.Tipo = 'R' THEN 'Reservada'
                                    WHEN u.Tipo = 'Q' THEN 'Cuarentena'
                                END, '| Picking ',
                                CASE 
                                    WHEN u.Picking = 'S' THEN '✓'
                                    WHEN u.Picking = 'N' THEN '✕'
                                END
                         ) AS tipo_ubicacion,
                         a.costoPromedio as costoPromedio,
                         a.costoPromedio*e.Existencia as subtotalPromedio,
                (SELECT SUM(a.costoPromedio*e.Existencia) FROM V_ExistenciaGralProduccion e LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo) as importeTotalPromedio,
                (SELECT BL FROM c_almacenp WHERE id = '$almacen' LIMIT 1) AS codigo_BL
            FROM
                V_ExistenciaGralProduccion e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN c_serie s ON s.cve_articulo = e.cve_articulo
            WHERE e.cve_almac = '$almacen' AND e.tipo = 'ubicacion' AND e.Existencia > 0  {$sqlArticulo}  {$sqlZona}
    ";
  
    //echo var_dump($sql);
    //die();
  
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($query, MYSQLI_ASSOC);
  
    $bl = 0;
    foreach($data as $d)
    {
       $bl = $d['codigo_BL'];
    }
  
    $bl1 = $bl;

    $header = array(
        'Almacén',
        'Zona de Almacenaje',
        'Codigo BL'." ".$bl1.'',
        'Clave',
        'Descripción',
        'Lote',
        'Caducidad',
        'N. Serie',
        'Cantidad',
        'Proveedor',
        'Fecha de Ingreso',
        'Tipo de Ubicación',
        'Costo Promedio',
        'Subtotal',
        'Importe'
    );
    $excel = new XLSXWriter();
    $excel->writeSheetRow('Sheet1', $header );

    $sum = 0;
    foreach($data as $d)
    {
       $sum+= $d['subtotalPromedio'];
    }
  
    $sum1 = $sum;
  
    foreach($data as $d){
      
        $row = array(
            $d['almacen'],
            $d['zona'],
            $d['codigo'],
            $d['clave'],
            $d['descripcion'],
            $d['lote'],
            $d['caducidad'],
            $d['nserie'],
            $d['cantidad'],
            $d['proveedor'],
            $d['fecha_ingreso'],
            $d['tipo_ubicacion'],
            $d['costoPromedio'],
            $d['subtotalPromedio'],
            $sum1
          
        );
        $sum1 = "";
        $excel->writeSheetRow('Sheet1', $row );
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
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

if( $_POST['action'] == 'traer_BL' ) 
{
  $almacen = $_POST["almacen"];
  $responce = "";
  $sql = 'SELECT * FROM c_almacenp WHERE id = "'.$almacen.'" and Activo = 1';
  $result = getArraySQL($sql);
  $responce = array();
  $responce["bl"] = $result[0]["BL"];
 
  echo json_encode($responce["bl"]);
}