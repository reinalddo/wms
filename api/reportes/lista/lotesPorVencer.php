<?php
include '../../../config.php';


if(isset($_POST) && !empty($_POST)){

    $almacen = $_POST['almacen'];
    $fecha_limite = $_POST['fecha_limite'];
    $sqlAlmacen = '' ;

    if(!empty($almacen)){
        $sqlAlmacen = " AND c_almacenp.id='{$almacen}' ";
    }

    $fecha_lim = "DATE_ADD(CURDATE(),INTERVAL 3 MONTH)";
    if(!empty($fecha_limite)){
        $fecha_lim = "STR_TO_DATE('{$fecha_limite}', '%Y-%m-%d')";
    }

    $sql = "SELECT 
                c_articulo.cve_articulo AS cve_articulo,
                c_articulo.des_articulo AS articulo,
                c_lotes.LOTE AS lote,
                DATE_FORMAT(c_lotes.Caducidad,'%d-%m-%Y') AS caducidad,
                c_ubicacion.CodigoCSD AS ubicacion,
                vp.Existencia AS existencia,
                (SELECT IFNULL(DATE_FORMAT(fecha_fin, '%d-%m-%Y'), '--') FROM td_entalmacen WHERE cve_articulo = c_lotes.cve_articulo ORDER BY id DESC LIMIT 1) AS fecha_ingreso,
                p.Nombre AS Proveedor
            FROM V_ExistenciaGral vp
            LEFT JOIN c_lotes ON c_lotes.LOTE = vp.cve_lote AND c_lotes.cve_articulo = vp.cve_articulo
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = vp.cve_articulo
            LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = vp.cve_ubicacion
            LEFT JOIN c_unimed ON c_unimed.id_umed = c_articulo.unidadMedida
            LEFT JOIN c_almacenp ON c_almacenp.id = vp.cve_almac
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vp.ID_Proveedor
            WHERE c_lotes.Caducidad BETWEEN CURDATE() AND {$fecha_lim}
            AND DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y') != DATE_FORMAT('00-00-0000', '%d-%m-%Y')
            AND vp.tipo = 'ubicacion'
            AND Existencia > 0
            AND c_ubicacion.CodigoCSD != ''
            {$sqlAlmacen}
            ORDER BY caducidad ASC";

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