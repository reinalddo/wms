<?php
include '../../../app/load.php';
include '../../../config.php';
// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \MaximosMinimos\MaximosMinimos();

if( $_POST['action'] == 'save' ) {
    $ga->save($_POST);
    $success = true;

    $err = ($ga->resultado=="No existe el Artículo") ? $ga->resultado : "";

    $arr = array(
        "success" => $success,
        "des_articulo" => $ga->des_articulo,
        "detalle" => $ga->resultado
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'UpdateArt' ) {
    $ga->UpdateArt($_POST);
    $success = true;

    $arr = array(
        "success" => $success,
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'delete' ) {
    $ga->borrarPromocion($_POST);
    $ga->IDpromo = $_POST["IDpromo"];
    $ga->__get("IDpromo");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}
if($_POST['action'] === 'guardar'){
    //extract($_POST);
    $cve_articulo = $_POST['cve_articulo'];
    $idy_ubica = $_POST['idy_ubica'];
    $minimo = $_POST['minimo'];
    $maximo = $_POST['maximo'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT cve_articulo FROM ts_ubicxart WHERE cve_articulo = '$cve_articulo' AND idy_ubica = '$idy_ubica' AND Activo = 1;";
    $query = mysqli_query($conn, $sql);
    if(mysqli_num_rows($query) > 0){
        $sql = "UPDATE ts_ubicxart SET CapacidadMaxima = '$maximo', CapacidadMinima = '$minimo' WHERE cve_articulo = '$cve_articulo' AND idy_ubica = '$idy_ubica' AND Activo = 1;";
    }else{
        $sql = "INSERT INTO ts_ubicxart (cve_articulo, idy_ubica, CapacidadMinima, CapacidadMaxima, Activo) VALUES ('$cve_articulo', '$idy_ubica', '$minimo', '$maximo', 1);";
    }
    $query = mysqli_query($conn, $sql);

    echo json_encode(array("success" => true, "sql" => $sql));
}

if($_POST['action'] === 'reabastecer'){
    //extract($_POST);
    $id_almacen        = $_POST['id_almacen'];
    $cve_usuario       = $_POST['cve_usuario'];
    $arr_articulo      = $_POST['arr_articulo'];
    $arr_ubicacion     = $_POST['arr_ubicacion'];
    $arr_reabastop     = $_POST['arr_reabastop'];
    $arr_reabastoc     = $_POST['arr_reabastoc'];
    $realizar_reabasto = $_POST['realizar_reabasto'];
    $arr_BL            = $_POST['arr_BL'];

    $reabastos_realizados = 0;
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $datos_reabastecer = "<table class='table table-striped' id='tabla_res_reabastecimiento'>";
    $datos_reabastecer .= "<thead><td>Artículo</td><td>BL Origen</td><td>BL Destino</td><td>Cantidad a Mover</td></thead>";
    for($i = 0; $i < count($arr_articulo); $i++)
    {
        $cve_articulo_r = $arr_articulo[$i];
        $idy_ubica_r    = $arr_ubicacion[$i];
        $cantidad_r     = $arr_reabastop[$i];
        $BL_r           = $arr_BL[$i];

        $sql2 = "SELECT DISTINCT e.cve_ubicacion, e.cve_articulo, e.cve_lote, e.Existencia, e.Cve_Contenedor, u.CodigoCSD AS bl,
                IF(a.Caduca = 'S', DATE_FORMAT(l.Caducidad, '%Y%m%d'), IF(a.control_numero_series = 'S', DATE_FORMAT(s.fecha_ingreso, '%Y%m%d'), DATE_FORMAT(ent.fecha_inicio, '%Y%m%d'))) AS fecha, e.Id_Proveedor
                FROM V_ExistenciaGral e 
                INNER JOIN c_articulo a ON e.cve_articulo = a.cve_articulo
                INNER JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = a.cve_articulo AND ra.Cve_Almac = {$id_almacen}
                INNER JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion AND u.picking = 'N'
                LEFT JOIN c_lotes l ON l.cve_articulo = e.cve_articulo AND e.cve_lote = l.Lote AND l.Activo = 1
                LEFT JOIN c_serie s ON s.cve_articulo = a.cve_articulo 
                LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote
                WHERE e.cve_articulo = '{$cve_articulo_r}' AND e.cve_almac = {$id_almacen} AND e.tipo = 'ubicacion' AND IFNULL(e.Cuarentena, 0) = 0 AND e.cve_ubicacion != '{$idy_ubica_r}'
                ORDER BY fecha ASC";

        $query = mysqli_query($conn, $sql2);

        if(mysqli_num_rows($query) == 0)
        {
            $sql2 = "SELECT DISTINCT e.cve_ubicacion, e.cve_articulo, e.cve_lote, e.Existencia, e.Cve_Contenedor, 
                    u.CodigoCSD AS bl,
                    IF(a.Caduca = 'S', DATE_FORMAT(l.Caducidad, '%Y%m%d'), IF(a.control_numero_series = 'S', DATE_FORMAT(s.fecha_ingreso, '%Y%m%d'), DATE_FORMAT(ent.fecha_inicio, '%Y%m%d'))) AS fecha, e.Id_Proveedor
                    FROM V_ExistenciaGral e 
                    INNER JOIN c_articulo a ON e.cve_articulo = a.cve_articulo
                    INNER JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = a.cve_articulo AND ra.Cve_Almac = {$id_almacen}
                    INNER JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion AND u.cve_nivel >= 2
                    LEFT JOIN c_lotes l ON l.cve_articulo = e.cve_articulo AND e.cve_lote = l.Lote AND l.Activo = 1
                    LEFT JOIN c_serie s ON s.cve_articulo = a.cve_articulo 
                    LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote
                    WHERE e.cve_articulo = '{$cve_articulo_r}' AND e.cve_almac = {$id_almacen} AND e.tipo = 'ubicacion' AND IFNULL(e.Cuarentena, 0) = 0 AND e.cve_ubicacion != '{$idy_ubica_r}'
                    ORDER BY fecha ASC";

            $query = mysqli_query($conn, $sql2);
        }

        while($row = mysqli_fetch_array($query) )
        {
            if($cantidad_r > 0)
            {
                //****************************************
                //origen
                //****************************************
                $Existencia     = $row["Existencia"];
                $cve_ubicacion  = $row["cve_ubicacion"];
                $Cve_Contenedor = $row["Cve_Contenedor"];
                $cve_lote       = $row["cve_lote"];
                $Id_Proveedor   = $row["Id_Proveedor"];
                $bl             = $row["bl"];
                //****************************************
                $cantidad_reabasto = 0;
                if($cantidad_r <= $Existencia)
                {
                   $cantidad_reabasto = $cantidad_r;
                   $cantidad_r = 0;
                }
               else
               {
                   $cantidad_reabasto = $Existencia;
                   $cantidad_r -= $Existencia;
               }

                //$datos_reabastecer.= $cve_articulo_r.":::::".$bl.":::::".$cantidad_reabasto."\n";
               $datos_reabastecer .= "<tr><td>".$cve_articulo_r."</td><td>".$bl."</td><td>".$BL_r."</td><td>".$cantidad_reabasto."</td></tr>";
               if($realizar_reabasto == 1)
               {
                    $sql3 = "INSERT IGNORE INTO t_tipomovimiento(id_TipoMovimiento, nombre) VALUES (100, 'Reabasto')";
                    $query3 = mysqli_query($conn, $sql3);

                    $idy_origen = 0;
                    $idy_destino = 0;

                    if($Cve_Contenedor != '')
                    {
                        $clave_contenedor = $Cve_Contenedor;
                        $sql5 = "UPDATE ts_existenciatarima SET existencia = existencia - $cantidad_reabasto WHERE cve_articulo = '$cve_articulo_r' AND lote = '$cve_lote' AND idy_ubica = '$cve_ubicacion' AND ID_Proveedor = '$Id_Proveedor' AND cve_almac = '$id_almacen' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$clave_contenedor')";
                        $query5 = mysqli_query($conn, $sql5);
                    }
                    else
                    {
                        $sql5 = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $cantidad_reabasto WHERE cve_articulo = '$cve_articulo_r' AND cve_lote = '$cve_lote' AND idy_ubica = '$cve_ubicacion' AND ID_Proveedor = '$Id_Proveedor' AND cve_almac = '$id_almacen'";
                        $query5 = mysqli_query($conn, $sql5);
                    }

                    //verifico si el DESTINO está en piezas o en tarimas para restarlo en la tabla correspondiente
                    $sql4 = "SELECT IFNULL(Cve_Contenedor, '') as Cve_Contenedor, Id_Proveedor FROM V_ExistenciaGral WHERE cve_articulo = '$cve_articulo_r' AND cve_ubicacion = '$idy_ubica_r' AND tipo = 'ubicacion' AND cve_almac = '$id_almacen' LIMIT 1";
                    $query4 = mysqli_query($conn, $sql4);
                    $row4 = mysqli_fetch_array($query4);

                    $id_proveedor = $row4['Id_Proveedor'];
                    $clave_contenedor = $row4['Cve_Contenedor'];
                    if($clave_contenedor != '')
                    {
                        $sql5 = "INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, existencia, ntarima,ID_Proveedor, Cuarentena) VALUES ({$id_almacen}, {$idy_ubica_r}, '{$cve_articulo_r}', '{$cve_lote}', {$cantidad_reabasto}, (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$clave_contenedor'), '$id_proveedor', 0) ON DUPLICATE KEY UPDATE existencia = existencia + $cantidad_reabasto";
                        $query5 = mysqli_query($conn, $sql5);
                    }
                    else
                    {
                        $sql5 = "INSERT INTO ts_existenciapiezas (cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia,ID_Proveedor, Cuarentena) VALUES ({$id_almacen}, {$idy_ubica_r}, '{$cve_articulo_r}', '{$cve_lote}', {$cantidad_reabasto}, '$id_proveedor', 0) ON DUPLICATE KEY UPDATE Existencia = Existencia + $cantidad_reabasto";
                        $query5 = mysqli_query($conn, $sql5);
                    }

                    $sql6 = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('$cve_articulo_r', '$cve_lote', NOW(), '$cve_ubicacion', '$idy_ubica_r', '$cantidad_reabasto', 100, '$cve_usuario', '$id_almacen')";
                    $query6 = mysqli_query($conn, $sql6);

                    if($row4['Cve_Contenedor'] != '')
                    {
                        $sql6 = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES ((SELECT MAX(id) FROM t_cardex), '$id_almacen', (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$clave_contenedor'), NOW(), '$cve_ubicacion', '$idy_ubica_r', 100, '$cve_usuario', 'I')";
                        $query6 = mysqli_query($conn, $sql6);
                    }
                    $reabastos_realizados++;
               }
            }
        }

        if(mysqli_num_rows($query) == 0)
        { 
            //$datos_reabastecer.= $cve_articulo_r."::::: :::::No hay ubicaciones disponibles para realizar el reabasto de este artículo\n";
            $datos_reabastecer .= "<tr><td>".$cve_articulo_r."</td><td></td><td>".$BL_r."</td><td>No hay ubicaciones disponibles para realizar el reabasto de este artículo</td></tr>";
        }

    }

    $datos_reabastecer .= "</table>";
/*
    if(mysqli_num_rows($query) > 0){}
    //$query = mysqli_query($conn, $sql);
*/
    echo json_encode(array("success" => true, "data_reabasto" => $datos_reabastecer, "realizar_reabasto" => $realizar_reabasto, "reabastos_realizados" => $reabastos_realizados));
}