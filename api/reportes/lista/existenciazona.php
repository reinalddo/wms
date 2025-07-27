<?php
include '../../../config.php';



$zona= $_POST["zona"];

$sqlZona = !empty($zona) ? "AND z.cve_almac = '$zona'" : "";

$sql = "SELECT  al.nombre AS almacen,
                z.des_almac AS zona,
                u.CodigoCSD AS ubicacion,
                e.cve_articulo AS clave,
                a.des_articulo AS descripcion,
                (SELECT IFNULL(DATE_FORMAT(fecha_fin, '%d-%m-%Y'), '--') FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote LIMIT 1) AS fecha_ingreso,
                e.cve_lote AS lote,
                IFNULL(l.CADUCIDAD, '--') AS caducidad,
                IFNULL(s.numero_serie, '--') AS serie,
                e.Existencia AS existencia
        FROM V_ExistenciaGralProduccion e
        LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
        LEFT JOIN c_lotes l ON l.cve_articulo = e.cve_articulo AND l.LOTE = e.cve_lote
        LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
        LEFT JOIN c_almacen z ON z.cve_almac = u.cve_almac
        LEFT JOIN c_almacenp al ON al.id = z.cve_almacenp
        LEFT JOIN c_serie s ON s.clave_articulo = e.cve_articulo
        WHERE e.Existencia > 0 AND e.tipo COLLATE utf8mb4_general_ci = 'ubicacion' ".$sqlZona;


$res = getArraySQL($sql); 
echo json_encode($res);


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