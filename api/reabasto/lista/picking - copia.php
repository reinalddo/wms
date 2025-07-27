<?php 
include '../../../config.php'; 

error_reporting(0); 

if($_GET['m']=='1'){ 
    $page = $_GET['start']; 
    $limit = $_GET['length'];  
    $search = $_GET['search']['value']; 


    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

    $sqlCount = 'SELECT 
            COUNT(ubi.`Activo`)AS total

        FROM 
            ts_ubicxart AS ubi 
        LEFT JOIN c_articulo AS ar ON ar.cve_articulo = ubi.cve_articulo 
        LEFT JOIN c_ubicacion AS u ON u.`idy_ubica`=ubi.`idy_ubica`
        LEFT JOIN V_ExistenciaGralProduccion AS ex ON ex.`cve_ubicacion`=u.`idy_ubica` AND ex.`cve_articulo`=ubi.`cve_articulo`
        LEFT JOIN c_gpoarticulo AS g ON g.`cve_gpoart`=ar.`grupo` '; 
    $query = mysqli_query($conn, $sqlCount); 
    $count = 0; 
    if($query){ 
        $count = mysqli_fetch_array($query)['total']; 
    } 



    $sql = 'SELECT 
            ar.cve_articulo AS clave_articulo,
            ar.`des_articulo` AS descripcion_articulo,
            g.`des_gpoart` AS linea,
            "Piezas" AS unidad,
            ubi.`CapacidadMinima` AS minimo,
            ubi.`CapacidadMaxima` AS maximo,
            if(ex.`Existencia` is null,0,ex.`Existencia`) AS existencia,
            IF((ex.`Existencia`)>ubi.`CapacidadMinima`,0,if((ubi.`CapacidadMaxima`)-(ex.`Existencia`) is null,0,(ubi.`CapacidadMaxima`)-(ex.`Existencia`)))AS pedir, 
            IF(ex.`Existencia`>ubi.`CapacidadMaxima`,"amarillo",IF(ex.`Existencia`>ubi.`CapacidadMinima`,"verde",IF(ex.`Existencia`<ubi.`CapacidadMinima`,"rojo","rojo")))AS sta

        FROM 
            ts_ubicxart AS ubi 
        INNER JOIN c_articulo AS ar ON ar.cve_articulo = ubi.cve_articulo 
        INNER JOIN c_ubicacion AS u ON u.`idy_ubica`=ubi.`idy_ubica`
        LEFT JOIN V_ExistenciaGralProduccion AS ex ON ex.`cve_ubicacion`=u.`idy_ubica` AND ex.`cve_articulo`=ubi.`cve_articulo`
        LEFT JOIN c_gpoarticulo AS g ON g.`cve_gpoart`=ar.`grupo`
        GROUP BY ubi.`cve_articulo`,ubi.`idy_ubica`'  ; 


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

//if($_POST['action']=='ZonasReabasto'){ 
function ZonasReabasto($cve_almacen, $cve_articulo){

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

    //$cve_almacen  = $_POST['cve_almacen'];
    //$cve_articulo = $_POST['cve_articulo'];

    $sql = "CALL SPWS_AgregaProductoReabasto('$cve_almacen', '$cve_articulo');";
    $query = mysqli_query($conn, $sql);
    
    $bls = "";
    $bls_arr = array();
    while($data = mysqli_fetch_array($query))
    {
        array_push($bls_arr, $data['CveBL']);
    }

    //$res = getArraySQL($sql);
    if($bls_arr[0])
       $bls = implode(", ", $bls_arr);

   return $bls;
/*
    echo json_encode(array(
        "bls" => $bls
    )); 
*/
}

if($_GET['m']=='2'){ 

    $sql = "SELECT * FROM (SELECT
            ar.cve_articulo as idy_ubica,
            c_almacenp.clave AS clave_almacen,
            c_ubicacion.cve_almac,
            c_ubicacion.cve_pasillo,
            c_ubicacion.cve_rack,
            c_ubicacion.cve_nivel,
            c_ubicacion.num_ancho,
            c_ubicacion.num_largo,
            c_ubicacion.num_alto,
            c_ubicacion.picking,
            IF(c_ubicacion.TECNOLOGIA='PTL','S','N') AS ptl,
            IF(a.CapacidadMaxima IS NULL,0,a.CapacidadMaxima) AS maximo,
            IF(a.CapacidadMinima IS NULL,0,a.CapacidadMinima) AS minimo,
            c_ubicacion.Seccion,
            c_ubicacion.Ubicacion,
            c_ubicacion.PesoMaximo,
            IFNULL(TRUNCATE((c_ubicacion.num_ancho / 1000) * (c_ubicacion.num_alto / 1000) * (c_ubicacion.num_largo / 1000), 2), 0) AS volumen,
            c_ubicacion.CodigoCSD,
            c_ubicacion.TECNOLOGIA,
            c_ubicacion.Activo,
            c_almacen.des_almac,
            CONCAT(c_ubicacion.num_largo,' X',c_ubicacion.num_ancho,'  X',c_ubicacion.num_alto) AS dim,
            ar.des_articulo,
            (CASE
                WHEN SUM(COALESCE(e.Existencia, 0)) > a.CapacidadMinima THEN 0
                WHEN SUM(COALESCE(e.Existencia, 0)) <= a.CapacidadMinima THEN a.CapacidadMaxima - SUM(COALESCE(e.Existencia, 0))
                ELSE 0
            END) AS reabastecer,
            '' as bls,
            SUM(COALESCE(e.Existencia, 0)) AS existencia, 
            IF(SUM(COALESCE(e.Existencia, 0))>a.CapacidadMaxima,'amarillo',IF(SUM(COALESCE(e.Existencia, 0))>a.CapacidadMinima AND SUM(COALESCE(e.Existencia, 0))<a.CapacidadMaxima,'verde',IF(SUM(COALESCE(e.Existencia, 0))<=a.CapacidadMinima,'rojo','rojo')))AS sta
            FROM ts_ubicxart AS a
            INNER JOIN c_articulo AS ar ON ar.cve_articulo = a.cve_articulo 
            INNER JOIN c_ubicacion ON c_ubicacion.`idy_ubica`=a.`idy_ubica`
            LEFT JOIN V_ExistenciaGralProduccion AS e ON e.`cve_ubicacion`= c_ubicacion.`idy_ubica` AND e.`cve_articulo`=a.`cve_articulo`
            LEFT JOIN c_gpoarticulo AS g ON g.`cve_gpoart`=ar.`grupo`
            LEFT JOIN c_almacen ON c_almacen.cve_almac=c_ubicacion.`cve_almac`
            LEFT JOIN c_almacenp ON c_almacen.cve_almacenp = c_almacenp.id
            WHERE (c_ubicacion.TECNOLOGIA!='PTL' or ISNULL(c_ubicacion.TECNOLOGIA)) and c_ubicacion.picking <> 'N'
            GROUP BY a.`cve_articulo`,a.`idy_ubica`
            ) x
            where x.existencia > 0 and x.reabastecer > 0 ORDER BY x.des_articulo
           ";
      /*
      "IF((e.Existencia) > a.CapacidadMinima, 0, IF((a.CapacidadMaxima)-(e.Existencia) is null, 0, (a.CapacidadMaxima) - (e.`Existencia`))) as reabastecer"
      " FROM
            ts_ubicxart AS a
            LEFT JOIN c_ubicacion ON a.`idy_ubica`=c_ubicacion.`idy_ubica`
            LEFT JOIN c_almacen ON c_almacen.cve_almacenp=c_ubicacion.`cve_almac`
            LEFT JOIN c_articulo AS ar ON ar.cve_articulo = a.`cve_articulo`
            LEFT JOIN V_ExistenciaGralProduccion e ON e.cve_articulo = a.cve_articulo AND e.cve_ubicacion = a.idy_ubica
            WHERE
			c_ubicacion.Activo = '1' 
			AND c_ubicacion.`picking`='S' AND c_ubicacion.TECNOLOGIA!='PTL'
      and ar.`cve_articulo` != ''
			GROUP BY a.`idy_ubica`
			ORDER BY c_ubicacion.idy_ubica"; */
    $res = getArraySQL($sql);

    //for($i = 0; $i < count($res); $i++)
    //    $res["bls"][$i] = ZonasReabasto($res["clave_almacen"][$i], $res["idy_ubica"][$i]);

    echo json_encode($res); 
}


if(isset($_POST) && !empty($_POST) && $_POST['action'] === "exportExcelProducto"){
    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = "Reporte de máximos y mínimos por producto.xlsx";

    $sql='SELECT 
            ar.cve_articulo AS clave_articulo,
            ar.`des_articulo` AS descripcion_articulo,
            g.`des_gpoart` AS linea,
            "Piezas" AS unidad,
            ubi.`CapacidadMinima` AS minimo,
            ubi.`CapacidadMaxima` AS maximo,
            ex.`Existencia` AS existencia,
            IF((ex.`Existencia`)>ubi.`CapacidadMinima`,0,((ubi.`CapacidadMaxima`)-(ex.`Existencia`)))AS pedir,
            "--" AS status
        FROM 
            ts_ubicxart AS ubi 
        INNER JOIN c_articulo AS ar ON ar.cve_articulo = ubi.cve_articulo 
        INNER JOIN c_ubicacion AS u ON u.`idy_ubica`=ubi.`idy_ubica`
        INNER JOIN V_ExistenciaGralProduccion AS ex ON ex.`cve_ubicacion`=u.`idy_ubica` AND ex.`cve_articulo`=ubi.`cve_articulo`
        LEFT JOIN c_gpoarticulo AS g ON g.`cve_gpoart`=ar.`grupo`';
    
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($query, MYSQLI_ASSOC);
    
    $head = array(
        'Producto',
        'Descripción',
        'Grupo de Artículo',
        'Unidad',
        'Máximo',
        'Mínimo',
        'Existencia',
        'Reabastecer',
    );
    $excel = new XLSXWriter();
    $excel->writeSheetRow('Sheet1', $head );
    foreach($data as $d){
        $row = array(
            $d['clave_articulo'],
            $d['descripcion_articulo'],
            $d['linea'],
            $d['unidad'],
            $d['maximo'],
            $d['minimo'],
            $d['existencia'],
            $d['pedir']
        );
        $excel->writeSheetRow('Sheet1', $row );
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === "exportExcelUbicacion"){
    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = "Reporte de máximos y mínimos por ubicación.xlsx";

    $sql = "SELECT
            ar.`cve_articulo` as idy_ubica,
            c_ubicacion.cve_almac,
            c_ubicacion.cve_pasillo,
            c_ubicacion.cve_rack,
            c_ubicacion.cve_nivel,
            c_ubicacion.num_ancho,
            c_ubicacion.num_largo,
            c_ubicacion.num_alto,
            c_ubicacion.picking,
            IF(c_ubicacion.TECNOLOGIA='PTL','S','N') AS ptl,
            IF(c_ubicacion.Maximo IS NULL,0,c_ubicacion.Maximo) AS maximo,
            IF(c_ubicacion.Minimo IS NULL,0,c_ubicacion.Minimo) AS minimo,
            c_ubicacion.Seccion,
            c_ubicacion.Ubicacion,
            c_ubicacion.PesoMaximo,
            IFNULL(TRUNCATE((c_ubicacion.num_ancho / 1000) * (c_ubicacion.num_alto / 1000) * (c_ubicacion.num_largo / 1000), 2), 0) AS volumen,
            c_ubicacion.CodigoCSD,
            c_ubicacion.TECNOLOGIA,
            c_ubicacion.Activo,
            c_almacen.cve_almac,
            c_almacen.des_almac,
            CONCAT(c_ubicacion.num_largo,' X',c_ubicacion.num_ancho,'  X',c_ubicacion.num_alto)AS dim,
            ar.`des_articulo`,
            IF((e.Existencia) > a.CapacidadMinima, 0, IF((a.CapacidadMaxima)-(e.Existencia) is null, 0, (a.CapacidadMaxima) - (e.`Existencia`))) AS reabastecer,
            COALESCE(e.Existencia, 0) AS existencia
            FROM
            ts_ubicxart AS a
            LEFT JOIN c_ubicacion ON a.`idy_ubica`=c_ubicacion.`idy_ubica`
            LEFT JOIN c_almacen ON c_almacen.cve_almacenp=c_ubicacion.`cve_almac`
            LEFT JOIN c_articulo AS ar ON ar.cve_articulo = a.`cve_articulo`
            LEFT JOIN V_ExistenciaGralProduccion e ON e.cve_articulo = a.cve_articulo AND e.cve_ubicacion = a.idy_ubica
            WHERE
            c_ubicacion.Activo = '1' 
            AND c_ubicacion.`picking`='S' OR c_ubicacion.`TECNOLOGIA`='PTL'
            GROUP BY a.`idy_ubica`
            ORDER BY c_ubicacion.idy_ubica"  ; 
    
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($query, MYSQLI_ASSOC);
    
    $head = array(
        'Clave de Producto',
        'Descripción',
        'Zona',
        'Pasillo',
        'Rack',
        'Nivel',
        'Sección',
        'Posición',
        'PesoMaximo',
        'Volumen (m3)',
        'Dimensiones (Lar. X Anc. X Alt. )',
        'Picking',
        'Ubicación PTL',
        'Máximo',
        'Mínimo',
        'Existencia',
        'Reabastecer',
    );
    $excel = new XLSXWriter();
    $excel->writeSheetRow('Sheet1', $head );
    foreach($data as $d){
        $row = array(
            $d['idy_ubica'],
            $d['des_articulo'],
            $d['CodigoCSD'],
            $d['cve_pasillo'],
            $d['cve_rack'],
            $d['cve_nivel'],
            $d['Seccion'],
            $d['Ubicacion'],
            $d['PesoMaximo'],
            $d['volumen'],
            $d['dim'],
            $d['picking'],
            $d['ptl'],
            $d['maximo'],
            $d['minimo'],
            $d['existencia'],
            $d['reabastecer']
        );
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

function getArraySQLBL($sql){

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    if(!$result = mysqli_query($conexion, $sql)) 
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";

    $rawdata = array();

    $i = 0;

    while($row = mysqli_fetch_assoc($result))
    {
        //$row["bls"] = ZonasReabasto($row["clave_almacen"], $row["idy_ubica"]);

        $cve_almacen = $row["clave_almacen"];
        $cve_articulo = $row["idy_ubica"];

        $sql = "CALL SPWS_AgregaProductoReabasto('".$cve_almacen."', '".$cve_articulo."');";
        if(!$query = mysqli_query($conexion, $sql)) 
            echo "Falló la preparación '".$cve_almacen."', '".$cve_articulo."': (" . mysqli_error($conexion) . ") ";
        //$bls = "";
        $bls_arr = array();
        $data = "";
        while($data = mysqli_fetch_assoc($query))
        {
            if(!in_array($data['CveBL'], $bls_arr))
                array_push($bls_arr, $data['CveBL']);
        }

    //$res = getArraySQL($sql);
    //if($bls_arr[0])
       $bls = implode(", ", $bls_arr);

        $row["bls"] = $bls;

        $rawdata[$i] = $row;
        $i++;
    }

    mysqli_close($conexion);

    return $rawdata;
}