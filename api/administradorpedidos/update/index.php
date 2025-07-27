<?php

include '../../../app/load.php';
error_reporting(0);
// Initalize Slim
$app = new \Slim\Slim();

function GUID()
{
    if (function_exists('com_create_guid') === true)
    {
        return trim(com_create_guid(), '{}');
    }
    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

$ga = new \AdministradorPedidos\AdministradorPedidos();

if( $_POST['action'] == 'loadFotos' ) 
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($conn,'utf8');
    $response = array();

    $sql = "
        Select foto1,foto2,foto3,foto4 from td_ordenembarque 
        left join th_ordenembarque on td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
        where Fol_folio = '".$_POST["id_pedido"]."';
    ";

    if (!($res = mysqli_query($conn, $sql)))
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    while ($fila = mysqli_fetch_array($res))
    {
          $reponse[] = $fila;
    }

    echo json_encode(array(
        "success" => 200,
        "data" => $reponse,
    ));
}

if( $_POST['action'] == 'existenciasDeProductos' ) 
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $clave = $_POST['articulo'];
    $almacen = $_POST['almacen'];
    mysqli_set_charset($conn,'utf8');
    
    $sql = "
        SELECT
            u.idy_ubica ubicacion_id,
            ap.nombre as almacen,
            z.des_almac as zona,
            concat(u.cve_rack,'-',u.cve_nivel,'-',u.Ubicacion) as bl,
            u.cve_pasillo as pasillo,
            u.cve_rack as rack,
            u.cve_nivel as nivel,
            u.Seccion as seccion,
            u.Ubicacion as ubicacion,
            a.cve_articulo as clave,
            a.des_articulo as descripcion,
            COALESCE(l.LOTE, '--') as lote,
            COALESCE(l.CADUCIDAD, '--') as caducidad,
            COALESCE(s.numero_serie, '--') as nserie,
            e.Existencia as cantidad
        FROM
            V_ExistenciaGralProduccion e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN c_serie s ON s.clave_articulo = e.cve_articulo
        WHERE e.cve_almac = '{$almacen}' AND e.tipo = 'ubicacion' AND e.Existencia > 0 AND a.cve_articulo='{$clave}'
    ";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
  
    while ($fila = mysqli_fetch_array($res)) {$reponse[] = $fila;}
    echo json_encode(array(
      "success" => 200,
      'data' => $reponse
    ));
}

if($_POST['action'] === 'cerrarPedido')//1
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $almacen = $_POST['almacen'];
    $folio = $_POST["folio"];
    $sufijo = $_POST["sufijo"];
  
    $sql = "select clave from c_almacenp where id = ".$almacen;
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $row = mysqli_fetch_array($res);
  
    $sql ="CALL SPWS_TerminaPedidoNikken('".$row["clave"]."','".$folio."','".$sufijo."','".date("Y-m-d H:i:s")."')";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    echo json_encode(array(
        "success" => true,
        "sql"=>$sql,
        "almacen" => $row["clave"],
    ));
    exit;
}

if($_POST['action'] === 'horafin_surtir')//1
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $almacen = $_POST['almacen'];
    $folio = $_POST["folio"];
    $sufijo = $_POST["sufijo"];
  
    $sql = "select clave from c_almacenp where id = ".$almacen;
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $row = mysqli_fetch_array($res);
    
    $sql = "select Sufijo from th_subpedido where fol_folio = '".$folio."' " ;
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $row2 = mysqli_fetch_array($res);
  
    $sql ="CALL SPWS_TerminaPedidoNikken('".$row["clave"]."','".$folio."',".(int)$row2["Sufijo"].",'".date("Y-m-d H:i:s")."')";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    echo json_encode(array(
        "success" => true,
        "sql"=>$sql,
        "almacen" => $row["clave"],
        "sufijo" => $row2["Sufijo"],
    ));
    exit;
}

if($_POST['action'] === 'existenciasManufactura')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $folio = $_POST["folio"];
    $sufijo = $_POST["sufijo"];
    $ubicacion = $_POST["ubicacion"];
    $almacen = $_POST["almacen"];
    $status = $_POST["status"];

    $sql = "";$sql1 = "";$sql2 = "";$sql3 = "";$sql4 = "";

    $sql_prov = "SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '$folio'";
    if (!($res_prov = mysqli_query($conn, $sql_prov))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $row_prov = mysqli_fetch_array($res_prov);
    $ID_Proveedor = $row_prov['ID_Proveedor'];


    $sql = "SELECT * FROM td_surtidopiezas WHERE fol_folio = '$folio' AND Sufijo = {$sufijo}";
    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    while($row = mysqli_fetch_array($res)) 
    {
/*
        $sql = "SELECT * FROM `ts_existenciapiezas` WHERE `idy_ubica` = '$ubicacion' and cve_articulo = '".$row['Cve_articulo']."'";
        if (!($res2 = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $sql1 = $sql;

        if($res2->num_rows == 0)
        {
            $sql = "INSERT INTO ts_existenciapiezas(cve_almac,idy_ubica,cve_articulo,cve_lote, Existencia, ClaveEtiqueta, ID_Proveedor)
                    values($almacen,$ubicacion,'".$row['Cve_articulo']."','".$row["LOTE"]."',".$row["Cantidad"].",'','')";
        }
        else
        {
            $row2 = mysqli_fetch_array($res2);
            $sql = "UPDATE ts_existenciapiezas set Existencia = Existencia - ".$row["Cantidad"]." where id = ".$row2["id"].";";
        }
*/
        $sql = "INSERT INTO ts_existenciapiezas(cve_almac,idy_ubica,cve_articulo,cve_lote, Existencia, ClaveEtiqueta, ID_Proveedor)
                values($almacen,$ubicacion,'".$row['Cve_articulo']."','".$row["LOTE"]."',".$row["Cantidad"].",'', '$ID_Proveedor') 
                ON DUPLICATE KEY UPDATE Existencia = Existencia + ".$row["Cantidad"].";";

        $sql2 = $sql;
        if (!($res3 = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('".$row['Cve_articulo']."', '".$row["LOTE"]."', NOW(), '".$ubicacion."', '".$folio."', ".$row["Cantidad"].", 1, '".$_SESSION['cve_usuario']."', ".$almacen.")";
        if (!($res2 = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 10: (" . mysqli_error($conn) . ") ";}
        //$res2 = mysqli_multi_query($conn, $sql_kardex);
        mysqli_free_result($res2);
    }

    $sql = "SELECT IF((SELECT SUM(Num_cantidad) FROM td_subpedido WHERE fol_folio = '$folio') = (SELECT SUM(Cantidad) FROM td_surtidopiezas WHERE fol_folio = '$folio'), 1, 0) AS Completo FROM DUAL";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $Completo = mysqli_fetch_array($res)["Completo"];

    if($Completo == 1)
    {
        $sqlCount = "UPDATE t_ordenprod set status = 'I', idy_ubica = $ubicacion where Folio_Pro = '$folio'";
        $sql3 = $sqlCount;
        if (!$res = mysqli_query($conn, $sqlCount)) 
        {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $sqlCount = "UPDATE th_pedido set status = 'T' where Fol_folio = '$folio'";
        $sql3 = $sqlCount;
        if (!$res = mysqli_query($conn, $sqlCount)) 
        {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $sqlCount = "UPDATE th_subpedido set status = 'T' where Fol_folio = '$folio'";
        $sql3 = $sqlCount;
        if (!$res = mysqli_query($conn, $sqlCount)) 
        {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $sqlCount = "UPDATE td_pedido set status = 'C' where Fol_folio = '$folio';";
        $sql4 = $sqlCount;
        if (!$res = mysqli_query($conn, $sqlCount)) 
        {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $sqlCount = "UPDATE td_subpedido set status = 'C' where Fol_folio = '$folio';";
        $sql4 = $sqlCount;
        if (!$res = mysqli_query($conn, $sqlCount)) 
        {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
/*
        $sql = "SELECT fol_folio, Cve_articulo, LOTE from td_surtidopiezas where fol_folio = '$folio'";
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        while($row = mysqli_fetch_array($res))
        {
            $lote = $row["LOTE"];
            $Cve_articulo = $row["Cve_articulo"];

            $sql_upd = "UPDATE td_ordenprod SET Cve_Lote = '$lote' WHERE Cve_Articulo = '$Cve_articulo' AND Folio_Pro = '$folio'";
            if (!($res_upd = mysqli_query($conn, $sql_upd))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }
        }
*/

    }
    echo json_encode(array(
        "success" => true,
        "sql"=>$sql,
        "sql1"=>$sql1,
        "sql2"=>$sql2,
        "sql3"=>$sql3,
        "sql4"=>$sql4,
        "ubicacion" => $ubicacion
    ));
    exit;
}

function SurtidoWEBoAPK($folio)
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "CREATE TABLE IF NOT EXISTS t_surtido_origen (
              folio VARCHAR(100) DEFAULT NULL,
              fecha DATETIME DEFAULT NULL,
              web_apk VARCHAR(10) DEFAULT NULL
            ) ENGINE=INNODB";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";}
/*
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);
*/
    $sql = "INSERT INTO t_surtido_origen(folio, fecha, web_apk) VALUES ('$folio', NOW(), 'WEB')";
    //echo $sql;
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";}

    //echo $sql;

}

if($_POST['action'] === 'guardarSurtidoPorLP')//1
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $folio = $_POST['folio'];
    $almacen = $_POST['almacen'];

    $sql = "SELECT IF((SELECT SUM(existencia) FROM ts_existenciatarima WHERE ntarima IN (SELECT nTarima FROM td_pedidoxtarima WHERE fol_folio  = '$folio')) != (SELECT SUM(Num_Cantidad) FROM td_pedidoxtarima WHERE fol_folio  = '$folio'), 1, 0) AS tarimaxpartes FROM DUAL";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";}
    $tarimaxpartes = mysqli_fetch_array($res)["tarimaxpartes"];

    $sql = "SELECT clave FROM c_almacenp WHERE id = ".$almacen.";";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";}
    $clave = mysqli_fetch_array($res)["clave"];

    $sql = "SELECT * FROM t_registro_surtido WHERE fol_folio = '$folio'";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";}
    while($row = mysqli_fetch_array($res))
    {
        extract($row);
        $sql_insert = "";
        $sql_insert = "INSERT INTO td_surtidopiezas(fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, status) VALUES ('$fol_folio', '{$almacen}', 1, '$Cve_articulo', '$cve_lote', '$Cantidad', 0, 'S');";

        $sql_verificar = "SELECT COUNT(*) as ya_existe FROM td_surtidopiezas WHERE fol_folio = '$folio' AND Sufijo = 1 AND cve_almac = '{$almacen}' AND Cve_articulo = '{$Cve_articulo}' AND LOTE = '{$cve_lote}'";
        if (!($res_verificar = mysqli_query($conn, $sql_verificar))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
        $ya_existe = mysqli_fetch_array($res_verificar)["ya_existe"];

        if($ya_existe > 0)
        {
            $sql_insert = "";

            $cve_articulo_lp = $Cve_articulo;
            $lote_lp         = $cve_lote;
            $cantidad_lp     = $Cantidad;
            $sql_update_surtido = "UPDATE td_surtidopiezas SET Cantidad = Cantidad + {$cantidad_lp} WHERE fol_folio = '$folio' AND Cve_articulo = '{$cve_articulo_lp}' AND LOTE = '{$lote_lp}'";
            $res_update_surtido = mysqli_query($conn, $sql_update_surtido);
        }

        if($sql_insert != "")
        {
            if (!($res2 = mysqli_query($conn, $sql_insert))) {echo "Falló la preparación(5): (" . mysqli_error($conn) . ") ";}
            //$res2 = mysqli_multi_query($conn, $sql_insert);
            //mysqli_free_result($res2);
        }
/*
        $sql = "SELECT IFNULL(Cve_Contenedor, '') as Cve_Contenedor FROM V_ExistenciaGral WHERE cve_ubicacion='$idy_ubica' AND cve_articulo = '$Cve_articulo' AND cve_lote = '$cve_lote' AND tipo = 'ubicacion'";
        if (!($res5 = mysqli_query($conn, $sql))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
        //$res = mysqli_query($conn, $sql);
        $Cve_Contenedor = mysqli_fetch_array($res5)["Cve_Contenedor"];

        $sql_update = "";
        if($Cve_Contenedor)
        {
            $sql_update = "UPDATE ts_existenciatarima SET existencia = 0 WHERE ntarima = '{$ClaveEtiqueta}'";
            if (!($res3 = mysqli_query($conn, $sql_update))) {echo "Falló la preparación(6): (" . mysqli_error($conn) . ") ";}
            //$res3 = mysqli_query($conn, $sql_update);
            //mysqli_free_result($res3);
        }
*/
        if($tarimaxpartes == 1)
        {
            $sqlxp = "UPDATE ts_existenciatarima SET existencia = existencia - $Cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$Cve_articulo' AND lote = '$cve_lote' AND ntarima = '$ClaveEtiqueta'";
            if (!($res2xp = mysqli_query($conn, $sqlxp))) {echo "Falló la preparación(5): (" . mysqli_error($conn) . ") ";}
        }


        $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('$Cve_articulo', '$cve_lote', NOW(), '$idy_ubica', '$fol_folio', '$Cantidad', '8', '".$_SESSION['cve_usuario']."', ".$almacen.")";

        if (!($res2 = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 10: (" . mysqli_error($conn) . ") ";}
        //$res2 = mysqli_multi_query($conn, $sql_kardex);
        //mysqli_free_result($res2);

        //$sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) (SELECT id, Cve_Almac, '$ClaveEtiqueta', NOW(), origen, destino, 8, cve_usuario, 'O' FROM t_cardex WHERE destino = '".$fol_folio."' AND id NOT IN (SELECT id_kardex FROM t_MovCharolas WHERE Destino = '".$fol_folio."'))";

        if($tarimaxpartes == 0)
        {
            $sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) 
            VALUES ((SELECT MAX(id) FROM t_cardex), '$almacen', '$ClaveEtiqueta', NOW(), '$idy_ubica', '$fol_folio', 8, '".$_SESSION['cve_usuario']."', 'O')";

            if (!($res2 = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 10: (" . mysqli_error($conn) . ") ";}
        }
        //$res2 = mysqli_multi_query($conn, $sql_kardex);
        //mysqli_free_result($res2);


    }

    $porcentaje = 0;

    if($tarimaxpartes == 0)
    {
        $sql = "UPDATE ts_existenciatarima SET existencia = 0 WHERE ntarima IN (SELECT ClaveEtiqueta FROM t_registro_surtido WHERE fol_folio = '$folio');";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    }

    $sql = "DELETE FROM t_recorrido_surtido WHERE fol_folio = '$folio';";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $sql = "UPDATE t_registro_surtido SET Activo = 0 WHERE fol_folio = '$folio';";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $sql = "SELECT IFNULL(TipoPedido, 'P') as es_ot FROM th_pedido WHERE Fol_folio = '$folio';";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $es_ot = mysqli_fetch_array($res)["es_ot"];

    SurtidoWEBoAPK($folio);
    echo json_encode(array(
      "porcentaje" => $porcentaje,
      "es_ot" => $es_ot,
      //"es_rb" => $ubicacion_reabasto,
      "success" => true,
      "query" => $sql,
      "sql_insert" => $sql_insert,
      "sql_kardex" => $sql_kardex,
      "sql_update" => $sql_update
    ));
    mysqli_close($conn);
    exit;    

}

if($_POST['action'] === 'SurtirPedidoCompleto')//1
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $folio = $_POST['folio'];
    $sufijo = $_POST['sufijo'];


    $sql_recorrido = "SELECT  tr.idy_ubica as idy_ubica_rec, ts.cve_almac as cve_almac_rec, 
                              tr.fol_folio as fol_folio_rec, tr.Sufijo as Sufijo_rec, 
                              tr.Cve_articulo as Cve_articulo_rec, tr.cve_lote as cve_lote_rec, 
                              tr.Cantidad as Cantidad_rec 
                      FROM t_recorrido_surtido tr 
                      LEFT JOIN th_subpedido ts ON tr.fol_folio = ts.fol_folio AND tr.Sufijo = ts.Sufijo
                      WHERE tr.fol_folio = '$folio' AND tr.Sufijo = $sufijo";
    if (!($res_recorrido = mysqli_query($conn, $sql_recorrido))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}

    while($row_recorrido = mysqli_fetch_array($res_recorrido))
    {
        extract($row_recorrido);

        $sql = "SELECT c_articulo.num_multiplo, c_articulo.control_lotes, c_articulo.control_numero_series, c_articulo.control_peso, c_articulo.peso, IFNULL(c_unimed.mav_cveunimed, '') AS unidad_med FROM c_articulo LEFT JOIN c_unimed ON c_unimed.id_umed = c_articulo.unidadMedida WHERE c_articulo.cve_articulo = '$Cve_articulo_rec';";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";}
        $row_art = mysqli_fetch_array($res);
        $num_multiplo = $row_art["num_multiplo"];
        $control_lotes = $row_art["control_lotes"];
        $control_numero_series = $row_art["control_numero_series"];
        $control_peso = $row_art["control_peso"];
        $peso = $row_art["peso"];
        $unidad_med = $row_art["unidad_med"];

        if($control_peso != 'S') $peso = 1;
        else if($unidad_med == 'H87' && $control_peso == 'S') $peso = 1;

        $sql = "SELECT IFNULL(Cve_Contenedor, '') as Cve_Contenedor, Id_Proveedor, Existencia  FROM V_ExistenciaGral WHERE cve_ubicacion='$idy_ubica_rec' AND cve_articulo = '$Cve_articulo_rec' AND cve_lote = '$cve_lote_rec' AND tipo = 'ubicacion'";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
            //$res = mysqli_query($conn, $sql);
            $row_existencia = mysqli_fetch_array($res);
            $Cve_Contenedor = $row_existencia["Cve_Contenedor"];
            $Id_Proveedor = $row_existencia["Id_Proveedor"];
            $existencia_inicial = $row_existencia["Existencia"];

        $sql_update = "";
        if($Cve_Contenedor)
        {
            $sql_update = "UPDATE ts_existenciatarima SET existencia = existencia - $Cantidad_rec WHERE idy_ubica = '$idy_ubica_rec' AND cve_almac = '$cve_almac_rec' AND cve_articulo = '$Cve_articulo_rec' AND lote = '$cve_lote_rec' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '{$Cve_Contenedor}' LIMIT 1)";
        }
        else
        {
            $sql_update = "UPDATE ts_existenciapiezas SET Existencia = Existencia - ".($Cantidad_rec*$peso)." WHERE idy_ubica = $idy_ubica_rec AND cve_almac = $cve_almac_rec AND cve_articulo = '$Cve_articulo_rec' AND cve_lote = '$cve_lote_rec' AND ID_Proveedor = '$Id_Proveedor';
            ";
        }
        if (!($res3 = mysqli_query($conn, $sql_update))) {echo "Falló la preparación(6): (" . mysqli_error($conn) . ") ";}

        $sql_insert = "INSERT INTO td_surtidopiezas(fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, status, Id_Proveedor) VALUES ('$folio', '$cve_almac_rec', $sufijo,'$Cve_articulo_rec','$cve_lote_rec',".($Cantidad_rec*$peso).",0, 'S', '$Id_Proveedor') ON DUPLICATE KEY UPDATE Cantidad = Cantidad + ".($Cantidad_rec*$peso).";";

        if (!($res2 = mysqli_query($conn, $sql_insert))) {echo "Falló la preparación(5): (" . mysqli_error($conn) . ") ";}


       $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, stockinicial, ajuste) VALUES ('$Cve_articulo_rec', '$cve_lote_rec', NOW(), '$idy_ubica_rec', '".$folio."', $existencia_inicial - ".($Cantidad_rec*$peso).", 8, '".$_SESSION['cve_usuario']."', ".$cve_almac_rec.", '$existencia_inicial', ".($Cantidad_rec*$peso).")";

        if (!($res2 = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 10: (" . mysqli_error($conn) . ") ";}

    }
    SurtidoWEBoAPK($folio);
    $sql_delete = "DELETE FROM t_recorrido_surtido WHERE fol_folio='$folio' AND Sufijo = $sufijo";

    $res2 = mysqli_query($conn, $sql_delete);

    echo json_encode(array(
      "porcentaje" => 0,
      "es_ot" => 'P',
      //"es_rb" => $ubicacion_reabasto,
      "success" => true,
      "query" => $sql_recorrido,
      "sql_insert" => '',
      "sql_update" => ''
    ));
    mysqli_close($conn);
    exit;    

}

if($_POST['action'] === 'guardarSurtidoPorUbicacion')//1
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $items = $_POST['items'];
    $almacen = $_POST['almacen'];
    $con_recorrido = $_POST['con_recorrido'];
    $existencia_inicial = 0;

    $sql = "SELECT clave FROM c_almacenp WHERE id = ".$almacen.";";
    //if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(0): (" . mysqli_error($conn) . ") ";}
    //$res = mysqli_multi_query($conn, $sql);
    //$row = mysqli_fetch_array($res);
    //$clave = $row["clave"];
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";}
    $clave = mysqli_fetch_array($res)["clave"];

    $ubicacion_reabasto = 0;
    //mysqli_free_result($res);

    if($items["existencia"] > 0)
    {

        //**************************************************************************************************
        //                          COMPRUEBO SI ES UN PEDIDO TIPO REABASTO
        /**************************************************************************************************
        $sql = "SELECT idy_ubica FROM ts_ubicxart WHERE folio = '".$items["folio"]."'";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 03: (" . mysqli_error($conn) . ") ";}
        if(mysqli_num_rows($res) > 0)
            $ubicacion_reabasto = mysqli_fetch_array($res)["idy_ubica"];
        //**************************************************************************************************
        //**************************************************************************************************/

    $sql = "SELECT IFNULL(SUM(Num_cantidad), 0) as pedidas FROM td_subpedido WHERE fol_folio = '".$items["folio"]."' AND Sufijo = '".$items["sufijo"]."'";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ";}
    $pedidas = mysqli_fetch_array($res)["pedidas"];

    $sql = "SELECT IFNULL(TipoDoc, '') as TipoDoc FROM th_pedido WHERE fol_folio = '".$items["folio"]."'";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 03: (" . mysqli_error($conn) . ") ";}
    $TipoPedidoLP = mysqli_fetch_array($res)["TipoDoc"];


    $sql = "SELECT c_articulo.num_multiplo, c_articulo.control_lotes, c_articulo.control_numero_series, c_articulo.control_peso, c_articulo.peso, IFNULL(c_unimed.mav_cveunimed, '') AS unidad_med FROM c_articulo LEFT JOIN c_unimed ON c_unimed.id_umed = c_articulo.unidadMedida WHERE c_articulo.cve_articulo = '".$items["clave"]."';";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";}

    $row_art = mysqli_fetch_array($res);
    $num_multiplo = $row_art["num_multiplo"];
    $control_lotes = $row_art["control_lotes"];
    $control_numero_series = $row_art["control_numero_series"];
    $control_peso = $row_art["control_peso"];
    $peso = $row_art["peso"];
    $unidad_med = $row_art["unidad_med"];

    $lote = "";
    if($control_lotes == "S")
        $lote = $items["lote"];
    else if($control_numero_series == "S")
        $lote = $items["serie"];

        if(!$items["sufijo"])
            $items["sufijo"] = 0;
/*
        $sql = "CALL SPWS_SurteArticulo('".$clave."','".$items["folio"]."',".$items["sufijo"].",'".$items["clave"]."',".$items["surtidas"].",1,".$num_multiplo.",".$items["idy_ubica"].",'".$items["surtidor"]."','".$lote."', 0);";
        //if(!($res1 = mysqli_multi_query($conn, $sql))){echo $items["clave"]." Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
        $res1 = mysqli_multi_query($conn, $sql);
        mysqli_free_result($res1);
*/


        $sql_delete = ""; $sql_update_registro_surtido = ""; 
        $borro_de_t_recorrido_surtido = false; // aqui valido que si borró de t_recorrido_surtido entonces si realiza el surtido
        if($items["LP"] != "" && $TipoPedidoLP == 'tipo_lp')
        {
            $sql_a_borrar = "SELECT COUNT(*) as si_existe FROM t_recorrido_surtido WHERE fol_folio='".$items["folio"]."' AND ClaveEtiqueta='".$items["LPNtarima"]."'";
            if (!($res_verificar = mysqli_query($conn, $sql_a_borrar))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
            $si_existe = mysqli_fetch_array($res_verificar)["si_existe"];

            $sql_delete = "DELETE FROM t_recorrido_surtido WHERE   fol_folio='".$items["folio"]."' AND ClaveEtiqueta='".$items["LPNtarima"]."'";
            $sql_update_registro_surtido = "UPDATE t_registro_surtido SET Activo = 0 WHERE fol_folio='".$items["folio"]."' AND ClaveEtiqueta='".$items["LPNtarima"]."'";
            $borro_de_t_recorrido_surtido = true;
        }
        else
        {
            $sql_a_borrar = "SELECT COUNT(*) as si_existe FROM t_recorrido_surtido WHERE fol_folio='".$items["folio"]."' AND Sufijo=".$items["sufijo"]." AND Cve_Articulo='".$items["clave"]."' AND Cve_Lote='".$lote."' AND Idy_Ubica=".$items["idy_ubica"]."";

            $sql_delete = "DELETE FROM t_recorrido_surtido WHERE fol_folio='".$items["folio"]."' AND Sufijo=".$items["sufijo"]." AND Cve_Articulo='".$items["clave"]."' AND Cve_Lote='".$lote."' AND Idy_Ubica=".$items["idy_ubica"]."";

            $sql_update_registro_surtido = "UPDATE t_registro_surtido SET Activo = 0 WHERE fol_folio='".$items["folio"]."' AND Sufijo=".$items["sufijo"]." AND Cve_Articulo='".$items["clave"]."' AND Cve_Lote='".$lote."' AND Idy_Ubica=".$items["idy_ubica"]."";

            if($items["LPNtarima"] > 0)
            {
                $sql_a_borrar = "SELECT COUNT(*) as si_existe FROM t_recorrido_surtido WHERE   fol_folio='".$items["folio"]."' AND Sufijo=".$items["sufijo"]." AND Cve_Articulo='".$items["clave"]."' AND Cve_Lote='".$lote."' AND Idy_Ubica=".$items["idy_ubica"]." AND ClaveEtiqueta='".$items["LPNtarima"]."'";

                $sql_delete = "DELETE FROM t_recorrido_surtido WHERE   fol_folio='".$items["folio"]."' AND Sufijo=".$items["sufijo"]." AND Cve_Articulo='".$items["clave"]."' AND Cve_Lote='".$lote."' AND Idy_Ubica=".$items["idy_ubica"]." AND ClaveEtiqueta='".$items["LPNtarima"]."' ";

                $sql_update_registro_surtido = "UPDATE t_registro_surtido SET Activo = 0 WHERE fol_folio ='".$items["folio"]."' AND Sufijo=".$items["sufijo"]." AND Cve_Articulo='".$items["clave"]."' AND Cve_Lote='".$lote."' AND Idy_Ubica=".$items["idy_ubica"]." AND ClaveEtiqueta='".$items["LPNtarima"]."' ";
            }

            if (!($res_verificar = mysqli_query($conn, $sql_a_borrar))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
            $si_existe = mysqli_fetch_array($res_verificar)["si_existe"];

        }
        if($si_existe || $con_recorrido == 0) $borro_de_t_recorrido_surtido = true;

if($borro_de_t_recorrido_surtido == true)
{//$borro_de_t_recorrido_surtido = true
        $res2 = mysqli_query($conn, $sql_delete);
        mysqli_free_result($res2);
        $res_registro_surtido = mysqli_query($conn, $sql_update_registro_surtido);
        mysqli_free_result($res_registro_surtido);

        //$sql_insert = "INSERT INTO td_surtidopiezas(fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, status) VALUES ('".$items["folio"]."', '".$almacen."', ".$items["sufijo"].",'".$items["clave"]."','".$lote."',".$items["existencia"].",".$items["surtidas"].", 'S');";
        //if($ubicacion_reabasto == 0)
        //{

            $sql_insert = "";
            if($items["LP"] != "" && $TipoPedidoLP == 'tipo_lp')
            {
                $sql_insert = "INSERT INTO td_surtidopiezas(fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, status) (SELECT fol_folio, '{$almacen}', Sufijo, Cve_articulo, cve_lote, Cantidad, 0 AS revisadas, 'S' AS status FROM t_registro_surtido WHERE fol_folio = '".$items["folio"]."' AND ClaveEtiqueta = '".$items["LPNtarima"]."');";

                $sql_verificar = "SELECT COUNT(*) as ya_existe FROM td_surtidopiezas WHERE fol_folio = '".$items["folio"]."' AND Sufijo = ".$items["sufijo"]." AND cve_almac = '".$almacen."' AND Cve_articulo = '".$items["clave"]."' AND LOTE = '".$lote."'";
                if (!($res_verificar = mysqli_query($conn, $sql_verificar))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
                $ya_existe = mysqli_fetch_array($res_verificar)["ya_existe"];

                if($ya_existe > 0)
                {
                    $sql_insert = "";

                    $sql_update = "SELECT Cve_articulo, cve_lote, Cantidad FROM t_registro_surtido WHERE fol_folio = '".$items["folio"]."' AND ClaveEtiqueta = '".$items["LPNtarima"]."'";
                    if (!($res_update = mysqli_query($conn, $sql_update))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
                    while($row_update = mysqli_fetch_array($res_update))
                    {
                        $cve_articulo_lp = $row_update['Cve_articulo'];
                        $lote_lp         = $row_update['cve_lote'];
                        $cantidad_lp     = $row_update['Cantidad'];
                        $sql_update_surtido = "UPDATE td_surtidopiezas SET Cantidad = Cantidad + {$cantidad_lp} WHERE fol_folio = '".$items["folio"]."' AND Cve_articulo = '{$cve_articulo_lp}' AND LOTE = '{$lote_lp}'";
                        $res_update_surtido = mysqli_query($conn, $sql_update_surtido);
                    }
                }
            }
            else
            {
                if($control_peso != 'S') $peso = 1;
                else if($unidad_med == 'H87' && $control_peso == 'S') $peso = 1;
                $sql_insert = "INSERT INTO td_surtidopiezas(fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, status) VALUES ('".$items["folio"]."', '".$almacen."', ".$items["sufijo"].",'".$items["clave"]."','".$lote."',".($items["existencia"]*$peso).",0, 'S') ON DUPLICATE KEY UPDATE Cantidad = Cantidad + ".($items["existencia"]*$peso).";";
            }
            //UPDATE th_subpedido SET status = 'S' WHERE fol_folio = '".$items["folio"]."' AND Sufijo = '".$items["sufijo"]."';
            //El UPDATE th_subpedido SET status = 'S'... es para mantener el status = S mientras se le da clic al botón surtir

            if($sql_insert != "")
            {
                if (!($res2 = mysqli_query($conn, $sql_insert))) {echo "Falló la preparación(5): (" . mysqli_error($conn) . ") ";}
                //$res2 = mysqli_multi_query($conn, $sql_insert);
                mysqli_free_result($res2);
            }
        //}
$sql = "SELECT IFNULL(TipoPedido, 'P') as TipoPedido, Cve_Usuario, statusaurora FROM th_pedido WHERE Fol_folio ='".$items["folio"]."'";
if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
$row_tipo_pedido = mysqli_fetch_array($res);
$TipoPedido              = $row_tipo_pedido["TipoPedido"];
$Cve_Usuario_tipo_pedido = $row_tipo_pedido["Cve_Usuario"];
$cve_almacen_traslado = $row_tipo_pedido["statusaurora"];

        $sqlExistencia = "V_ExistenciaGral";
        if($TipoPedido == 'RI')
            $sqlExistencia = "V_ExistenciaGralProduccion";

        $sql = "SELECT IFNULL(Cve_Contenedor, '') as Cve_Contenedor FROM {$sqlExistencia} WHERE cve_ubicacion='".$items["idy_ubica"]."' AND cve_articulo = '".$items["clave"]."' AND cve_lote = '".$lote."' AND tipo='ubicacion'";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
        //$res = mysqli_query($conn, $sql);
        $Cve_Contenedor = mysqli_fetch_array($res)["Cve_Contenedor"];

        $sql_update = "";
        if($Cve_Contenedor != "" || ($items["LP"] != "" && $TipoPedidoLP == 'tipo_lp'))
        {
            if($items["LP"] != "" && $TipoPedidoLP == 'tipo_lp')
            {
                $sql_existencia = "SELECT existencia FROM ts_existenciatarima WHERE ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '".$items["LP"]."' LIMIT 1)";
                if (!($res_existencia = mysqli_query($conn, $sql_existencia))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
                $existencia_inicial = mysqli_fetch_array($res_existencia)["existencia"];

                $sql_update = "UPDATE ts_existenciatarima SET existencia = 0 WHERE ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '".$items["LP"]."' LIMIT 1)";
            }
            else
            {
                $sql_existencia = "SELECT existencia FROM ts_existenciatarima WHERE idy_ubica = ".$items["idy_ubica"]." AND cve_almac = ".$almacen." AND cve_articulo = '".$items["clave"]."' AND lote = '".$lote."' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '{$Cve_Contenedor}' LIMIT 1)";
                if (!($res_existencia = mysqli_query($conn, $sql_existencia))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
                $existencia_inicial = mysqli_fetch_array($res_existencia)["existencia"];

                $sql_update = "UPDATE ts_existenciatarima SET existencia = existencia - ".$items["existencia"]." WHERE idy_ubica = ".$items["idy_ubica"]." AND cve_almac = ".$almacen." AND cve_articulo = '".$items["clave"]."' AND lote = '".$lote."' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '{$Cve_Contenedor}' LIMIT 1)";
                if($items["LPNtarima"]>0)
                {
                    $lpntarima = $items["LPNtarima"];

                    $sql_existencia = "SELECT existencia FROM ts_existenciatarima WHERE idy_ubica = ".$items["idy_ubica"]." AND cve_almac = ".$almacen." AND cve_articulo = '".$items["clave"]."' AND lote = '".$lote."' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')";
                    if (!($res_existencia = mysqli_query($conn, $sql_existencia))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
                    $existencia_inicial = mysqli_fetch_array($res_existencia)["existencia"];

                    $sql_update = "UPDATE ts_existenciatarima SET existencia = existencia - ".$items["existencia"]." WHERE idy_ubica = ".$items["idy_ubica"]." AND cve_almac = ".$almacen." AND cve_articulo = '".$items["clave"]."' AND lote = '".$lote."' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')";
                }
            }
        }
        else
        {
            if($control_peso != 'S') $peso = 1;
            else if($unidad_med == 'H87' && $control_peso == 'S') $peso = 1;

            $sql_existencia = "SELECT Existencia FROM ts_existenciapiezas WHERE idy_ubica = ".$items["idy_ubica"]." AND cve_almac = ".$almacen." AND cve_articulo = '".$items["clave"]."' AND cve_lote = '".$lote."'";
            if (!($res_existencia = mysqli_query($conn, $sql_existencia))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
            $existencia_inicial = mysqli_fetch_array($res_existencia)["Existencia"];

            $sql_update = "UPDATE ts_existenciapiezas SET Existencia = Existencia - ".($items["existencia"]*$peso)." WHERE idy_ubica = ".$items["idy_ubica"]." AND cve_almac = ".$almacen." AND cve_articulo = '".$items["clave"]."' AND cve_lote = '".$lote."';
            ";
        }
        if (!($res3 = mysqli_query($conn, $sql_update))) {echo "Falló la preparación(6): (" . mysqli_error($conn) . ") ";}
        //$res3 = mysqli_query($conn, $sql_update);
        mysqli_free_result($res3);



if($TipoPedido == 'RI')
{
    $id_proveedor = $_POST['id_proveedor'];
//*******************************************************************************************************
//        PROCESO PARA CREAR LA ENTRADA DE UN PEDIDO TIPO TRASLADO DE ALMACÉN INTERNO (RI)
//*******************************************************************************************************

$sql = "SELECT COUNT(*) as existe_movimiento FROM t_tipomovimiento WHERE nombre = 'Traslado Interno'";
if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 091: (" . mysqli_error($conn) . ") ";}
$existe_movimiento = mysqli_fetch_array($res)["existe_movimiento"];

if(!$existe_movimiento)
{
    $sql = "INSERT INTO t_tipomovimiento(nombre) VALUES('Traslado Interno')";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 092: (" . mysqli_error($conn) . ") ";}
}

$sql = "SELECT id_TipoMovimiento FROM t_tipomovimiento WHERE nombre = 'Traslado Interno'";
if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 093: (" . mysqli_error($conn) . ") ";}
$id_movimiento = mysqli_fetch_array($res)["id_TipoMovimiento"];

$sql = "SELECT COUNT(*) as existe_entrada FROM th_entalmacen WHERE Fol_OEP ='".$items["folio"]."'";
$sql_existe_entrada = $sql;
if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 094: (" . mysqli_error($conn) . ") ";}
$existe_entrada = mysqli_fetch_array($res)["existe_entrada"];

$folio_OEP = $items["folio"];
$max_num_pedimento = 0; $max_kardex = 0;
if(!$existe_entrada)
{
    $sql = "SELECT (IFNULL(MAX(t.num_pedimento), 0)+1) as max_num_pedimento FROM th_aduana t";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 095: (" . mysqli_error($conn) . ") ";}
    $row_max = mysqli_fetch_array($res);
    $max_num_pedimento = $row_max["max_num_pedimento"];

    $sql = "SELECT ID_Protocolo, (IFNULL(FOLIO, 0)+1) as FOLIO FROM t_protocolo WHERE (descripcion LIKE 'Nacional%' OR ID_Protocolo = 'OCN') AND Activo = 1";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 096: (" . mysqli_error($conn) . ") ";}
    $row_protocolo = mysqli_fetch_array($res);
    $ID_Protocolo    = $row_protocolo["ID_Protocolo"];
    $Folio_Protocolo = $row_protocolo["FOLIO"];

    $sql = "UPDATE t_protocolo SET FOLIO = $Folio_Protocolo WHERE ID_Protocolo = '$ID_Protocolo'";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 097: (" . mysqli_error($conn) . ") ".$sql;}

    $sql = "INSERT INTO th_aduana (num_pedimento, fech_pedimento, Factura, fech_llegPed, status, ID_Proveedor, ID_Protocolo, Consec_protocolo, cve_usuario, Cve_Almac) VALUES ((SELECT (IFNULL(MAX(t.num_pedimento), 0)+1) as max_num_pedimento FROM th_aduana t), NOW(), '{$folio_OEP}', NOW(), 'T', $id_proveedor, '$ID_Protocolo', $Folio_Protocolo, '{$Cve_Usuario_tipo_pedido}', (SELECT clave FROM c_almacenp WHERE id = '$cve_almacen_traslado'))";
    $sql_th_aduana = $sql;
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 098: (" . mysqli_error($conn) . ") ".$sql;}

    $sql = "INSERT INTO th_entalmacen (Cve_Almac, Fec_Entrada, Fol_OEP, id_ocompra, Cve_Usuario, Cve_Proveedor, STATUS, Cve_Autorizado, tipo, HoraInicio, HoraFin) VALUES ((SELECT clave FROM c_almacenp WHERE id = '$cve_almacen_traslado'), NOW(), '{$folio_OEP}', $max_num_pedimento, '{$Cve_Usuario_tipo_pedido}', $id_proveedor, 'T', '{$Cve_Usuario_tipo_pedido}', 'TR', NOW(), NOW())";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 099: (" . mysqli_error($conn) . ") ";}
}
else
{
    $sql = "SELECT num_pedimento as max_num_pedimento FROM th_aduana WHERE Factura = '{$folio_OEP}'";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 0910: (" . mysqli_error($conn) . ") ";}
    $row_max = mysqli_fetch_array($res);
    $max_num_pedimento = $row_max["max_num_pedimento"];
}

$sql = "SELECT Fol_Folio FROM th_entalmacen WHERE Fol_OEP ='".$items["folio"]."'";
if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 0911: (" . mysqli_error($conn) . ") ";}
$Fol_Folio_entrada = mysqli_fetch_array($res)["Fol_Folio"];


    //$sql_update = "UPDATE ts_existenciapiezas SET Existencia = Existencia - ".$items["existencia"]." WHERE idy_ubica = ".$items["idy_ubica"]." AND cve_almac = ".$almacen." AND cve_articulo = '".$items["clave"]."' AND cve_lote = '".$lote."';";


    $sql_entrada = "SELECT COUNT(*) as existe_entrada FROM td_entalmacen WHERE cve_articulo = '".$items["clave"]."' AND cve_lote = '".$lote."' AND fol_folio = '{$folio_OEP}'";
    if (!($res = mysqli_query($conn, $sql_entrada))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
    $existe_entrada = mysqli_fetch_array($res)["existe_entrada"];

    if($existe_entrada)
    {
        $sql_entrada = "UPDATE td_entalmacen SET CantidadPedida = CantidadPedida + ".$items["existencia"].", CantidadRecibida = CantidadRecibida + ".$items["existencia"]." WHERE cve_articulo = '".$items["clave"]."' AND cve_lote = '".$lote."' AND fol_folio = '{$folio_OEP}'";
        if (!($res = mysqli_query($conn, $sql_entrada))) {echo "Falló la preparación 0912: (" . mysqli_error($conn) . ") ";}

        $sql_entrada = "UPDATE td_aduana SET cantidad = cantidad + ".$items["existencia"]." WHERE cve_articulo = '".$items["clave"]."' AND cve_lote = '".$lote."' AND num_orden = '{$max_num_pedimento}'";
        if (!($res = mysqli_query($conn, $sql_entrada))) {echo "Falló la preparación 0913: (" . mysqli_error($conn) . ") ";}

        $sql_kardex = "UPDATE t_cardex SET cantidad = cantidad + ".$items["existencia"]." WHERE cve_articulo = '".$items["clave"]."' AND cve_lote = '".$lote."' AND origen = '{$folio_OEP}'";
        if (!($res = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 0914: (" . mysqli_error($conn) . ") ";}
    }
    else
    {
        $sql_entrada = "INSERT IGNORE INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadPedida, CantidadRecibida, CantidadDisponible, CantidadUbicada, status, cve_usuario, fecha_inicio, fecha_fin) VALUES('{$Fol_Folio_entrada}', '".$items["clave"]."', '".$lote."', ".$items["existencia"].", ".$items["existencia"].", 0, 0, 'E', '{$Cve_Usuario_tipo_pedido}', NOW(), NOW())";
        if (!($res = mysqli_query($conn, $sql_entrada))) {echo "Falló la preparación 0915: (" . mysqli_error($conn) . ") ";}

        $sql_aduana = "INSERT IGNORE INTO td_aduana (ID_Aduana, cve_articulo, cantidad, cve_lote, num_orden) VALUES (0, '".$items["clave"]."', ".$items["existencia"].", '$lote', (SELECT (IFNULL(MAX(t.num_pedimento), 0)) as max_num_pedimento FROM th_aduana t))";
        if (!($res = mysqli_query($conn, $sql_aduana))) {echo "Falló la preparación 0916: (" . mysqli_error($conn) . ") ".$sql_aduana;}

        $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) VALUES('".$items["clave"]."', '".$lote."', NOW(), '{$folio_OEP}', '', ".$items["existencia"].", $id_movimiento, '{$Cve_Usuario_tipo_pedido}', $cve_almacen_traslado, 1, NOW())";
        if (!($res = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 0917: (" . mysqli_error($conn) . ") ";}

        $sql_kardex = "SELECT MAX(id) as max_id FROM t_cardex";
        if (!($res = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 0918: (" . mysqli_error($conn) . ") ";}
        $max_kardex = mysqli_fetch_array($res)["max_id"];
    }


    if($Cve_Contenedor)
    {
        //$sql_update = "UPDATE ts_existenciatarima SET existencia = existencia - ".$items["existencia"]." WHERE idy_ubica = ".$items["idy_ubica"]." AND cve_almac = ".$almacen." AND cve_articulo = '".$items["clave"]."' AND lote = '".$lote."';";
        $sql_entrada = "SELECT COUNT(*) as existe_entrada FROM td_entalmacenxtarima WHERE cve_articulo = '".$items["clave"]."' AND cve_lote = '".$lote."' AND ClaveEtiqueta = '$Cve_Contenedor' AND fol_folio = '{$Fol_Folio_entrada}'";
        if (!($res = mysqli_query($conn, $sql_entrada))) {echo "Falló la preparación 0919: (" . mysqli_error($conn) . ") ";}
        $existe_entrada = mysqli_fetch_array($res)["existe_entrada"];

        if($existe_entrada)
        {
            $sql_entrada = "UPDATE td_entalmacenxtarima SET Cantidad = Cantidad + ".$items["existencia"]." WHERE cve_articulo = '".$items["clave"]."' AND cve_lote = '".$lote."' AND ClaveEtiqueta = '$Cve_Contenedor' AND fol_folio = '{$Fol_Folio_entrada}'";
            if (!($res = mysqli_query($conn, $sql_entrada))) {echo "Falló la preparación 0920: (" . mysqli_error($conn) . ") ";}
        }
        else
        {
            $sql_entrada = "INSERT INTO td_entalmacenxtarima(fol_folio, cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, Ubicada, Activo, PzsXCaja, Abierto) VALUES('{$Fol_Folio_entrada}', '".$items["clave"]."', '".$lote."', '$Cve_Contenedor', ".$items["existencia"].", 'N', 1, 1, 0)";
            if (!($res = mysqli_query($conn, $sql_entrada))) {echo "Falló la preparación 0921: (" . mysqli_error($conn) . ") ";}

            $sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES($max_kardex, (SELECT clave FROM c_almacenp WHERE id = '$cve_almacen_traslado'), (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor'), NOW(), '{$folio_OEP}', '', $id_movimiento, '{$Cve_Usuario_tipo_pedido}', 'I')";
            if (!($res = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 0922: (" . mysqli_error($conn) . ") ";}
        }
    }


//*******************************************************************************************************
}
/*
        if($ubicacion_reabasto != 0) //Realizo el traslado del reabasto, ya se restaron del origen, ahora sumo en el destino
        {
            $sql = "SELECT IFNULL(Cve_Contenedor, '') as Cve_Contenedor, IFNULL(cve_lote, '') as cve_lote FROM V_ExistenciaGral WHERE cve_ubicacion='".$ubicacion_reabasto."' AND cve_articulo = '".$items["clave"]."' AND tipo = 'ubicacion' LIMIT 1";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ")";}
            //$res = mysqli_query($conn, $sql);
            $row_rb = mysqli_fetch_array($res);
            $Cve_Contenedor = $row_rb["Cve_Contenedor"];
            $lote_reabasto = $row_rb["cve_lote"];

            if($Cve_Contenedor != '')
            {
                $sql_update = "UPDATE ts_existenciatarima SET existencia = existencia + ".$items["existencia"]." WHERE idy_ubica = ".$ubicacion_reabasto." AND cve_almac = ".$almacen." AND cve_articulo = '".$items["clave"]."' AND lote = '".$lote_reabasto."';
                ";
            }
            else
            {
                $sql_update = "UPDATE ts_existenciapiezas SET Existencia = Existencia + ".$items["existencia"]." WHERE idy_ubica = ".$ubicacion_reabasto." AND cve_almac = ".$almacen." AND cve_articulo = '".$items["clave"]."' AND cve_lote = '".$lote_reabasto."';
                ";
            }
            if (!($res3 = mysqli_query($conn, $sql_update))) {echo "Falló la preparación(6): (" . mysqli_error($conn) . ") ";}
            //$res3 = mysqli_query($conn, $sql_update);
            mysqli_free_result($res3);
        }
*/
        $sql_update = "UPDATE th_subpedido SET HIE = NOW(), Hora_inicio = NOW() WHERE fol_folio = '".$items["folio"]."' AND Sufijo = '".$items["sufijo"]."';";
        if (!($res4 = mysqli_query($conn, $sql_update))) {echo "Falló la preparación(6.1): (" . mysqli_error($conn) . ") ";}
        //$res3 = mysqli_query($conn, $sql_update);
        mysqli_free_result($res4);


        $sql = "SELECT COUNT(*) AS produccion, Cve_Almac FROM t_ordenprod WHERE Folio_Pro = '".$items["folio"]."'";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 0923: (" . mysqli_error($conn) . ") ";}
        //$res = mysqli_query($conn, $sql);
        $row_prod = mysqli_fetch_array($res);
        $produccion = $row_prod["produccion"];
        $Cve_Almac_prod = $row_prod["Cve_Almac"];
        mysqli_free_result($res);

        if($produccion)
        {
            $sql = "SELECT idy_ubica FROM c_ubicacion WHERE AreaProduccion = 'S' AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = {$Cve_Almac_prod}) AND Activo = 1 LIMIT 1";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 6: (" . mysqli_error($conn) . ") ";}
            //$res = mysqli_query($conn, $sql);
            $idy_ubica = mysqli_fetch_array($res)["idy_ubica"];
            mysqli_free_result($res);
/*
            $sql = "SELECT COUNT(*) as existe FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '".$items["clave"]."'";
            //$res = mysqli_query($conn, $sql);
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 7: (" . mysqli_error($conn) . ") ";}
            $existe = mysqli_fetch_array($res)["existe"];
            mysqli_free_result($res);

            if($existe)
            {
                $sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor, Cuarentena)
                        VALUES (".$almacen.", ".$idy_ubica.", '".$items["clave"]."', '".$lote."', ".$items["existencia"].", 0, 0)
                        ON DUPLICATE KEY UPDATE Existencia = Existencia + ".$items["existencia"].";";
            }
            else
            {
                $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia + ".$items["existencia"]." WHERE idy_ubica = ".$idy_ubica." AND cve_almac = ".$almacen." AND cve_articulo = '".$items["clave"]."' AND cve_lote = '".$lote."';
            UPDATE th_subpedido SET HIE = NOW(), Hora_inicio = NOW() WHERE fol_folio = '".$items["folio"]."' AND Sufijo = '".$items["sufijo"]."'";
            }
*/
                $sql = "DELETE FROM td_ordenprod WHERE Folio_Pro = '".$items["folio"]."'";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 18: (" . mysqli_error($conn) . ") ";}
                    mysqli_free_result($res);
                $sql = "INSERT INTO td_ordenprod (Folio_Pro, Cve_Articulo, Cve_Lote, Cantidad) (SELECT fol_folio, Cve_articulo, LOTE, Cantidad FROM td_surtidopiezas WHERE fol_folio = '".$items["folio"]."')";
                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 81: (" . mysqli_error($conn) . ") ";}
                mysqli_free_result($res);

/*
                if($control_peso != 'S') $peso = 1;
                else if($unidad_med == 'H87' && $control_peso == 'S') $peso = 1;

                $sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor, Cuarentena)
                        VALUES (".$almacen.", ".$idy_ubica.", '".$items["clave"]."', '".$lote."', ".($items["existencia"]*$peso).", 0, 0)
                        ON DUPLICATE KEY UPDATE Existencia = Existencia + ".($items["existencia"]*$peso).";";
                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 8: (" . mysqli_error($conn) . ") ";}
                mysqli_free_result($res);
*/
        }

        $sql_kardex = "";
        if($items["LP"] != "" && $TipoPedidoLP == 'tipo_lp')
            $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, stockinicial, ajuste) (SELECT Cve_articulo, cve_lote, NOW(), idy_ubica, fol_folio AS destino, ($existencia_inicial-Cantidad), '8', '".$_SESSION['cve_usuario']."', ".$almacen.", '$existencia_inicial', Cantidad   FROM t_registro_surtido WHERE fol_folio = '".$items["folio"]."' AND ClaveEtiqueta = '".$items["LPNtarima"]."')";
        else
        {
            if($control_peso != 'S') $peso = 1;
            else if($unidad_med == 'H87' && $control_peso == 'S') $peso = 1;
           $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, stockinicial, ajuste) VALUES ('".$items["clave"]."', '".$lote."', NOW(), '".$items["idy_ubica"]."', '".$items["folio"]."', ($existencia_inicial-".($items["existencia"]*$peso)."), 8, '".$_SESSION['cve_usuario']."', ".$almacen.", '$existencia_inicial', ".($items["existencia"]*$peso).")";
        }

        if (!($res2 = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 10: (" . mysqli_error($conn) . ") ";}
        //$res2 = mysqli_multi_query($conn, $sql_kardex);
        mysqli_free_result($res2);


        if($items["LP"] != "" && $TipoPedidoLP != 'tipo_lp')
        {
            $sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) (SELECT id, Cve_Almac, (SELECT IDContenedor FROM c_charolas WHERE CveLP = '".$items["LP"]."'), NOW(), origen, destino, 8, '".$_SESSION['cve_usuario']."', 'O' FROM t_cardex WHERE destino = '".$items["folio"]."' AND id NOT IN (SELECT id_kardex FROM t_MovCharolas WHERE Destino = '".$items["folio"]."'))";

            if (!($res2 = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 10: (" . mysqli_error($conn) . ") ";}
            //$res2 = mysqli_multi_query($conn, $sql_kardex);
            mysqli_free_result($res2);
        }

/*
        $sql = "SELECT IFNULL(SUM(Cantidad), 0) as surtidas FROM td_surtidopiezas WHERE fol_folio = '".$items["folio"]."' AND Sufijo = '".$items["sufijo"]."'";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";}
        $surtidas = mysqli_fetch_array($res)["surtidas"];
*/
    }

    //$porcentaje = (($surtidas+$items["existencia"]) * 100)/$pedidas;
    $porcentaje = 0;

/*
    $sql = "SELECT TRUNCATE((SELECT SUM(Cantidad) FROM td_surtidopiezas WHERE Fol_Folio = '".$items["folio"]."' AND Sufijo = '".$items["sufijo"]."')/(SELECT SUM(Num_cantidad) FROM td_subpedido WHERE Fol_Folio = '".$items["folio"]."' AND Sufijo = '".$items["sufijo"]."'), 0)*100 AS porcentaje FROM DUAL";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $porcentaje = mysqli_fetch_array($res)["porcentaje"];
*/
    $sql = "SELECT COUNT(*) as existe FROM th_consolidado WHERE No_OrdComp = '".$items["folio"]."';";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($query);
    $consolidado = $row["existe"];
/*
    $sql = "SELECT COUNT(*)AS es_ot FROM t_ordenprod WHERE Folio_Pro = '".$items["folio"]."';";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $es_ot = mysqli_fetch_array($res)["es_ot"];

    if($consolidado >= 1)
        $es_ot = "ws";
*/
    $folio = $items["folio"];

    if($_POST['ultimo'])
    {
        $sql_bo = "SELECT DISTINCT COUNT(Folio_BackO) as fol_exist, Folio_BackO FROM th_backorder WHERE Fol_Folio = '$folio'";
        if (!($res_vs_bo = mysqli_query($conn, $sql_bo))) {echo " Existencia_Total: (" . mysqli_error($conn) . ") ";}
        $row_exist = mysqli_fetch_array($res_vs_bo);
        $folio_exist = $row_exist["fol_exist"];
        $folio_backorder = $row_exist["fol_exist"];

        if(!$folio_exist)
        {
            $folio_backorder = $ga->consecutivo_folio_backorder();
            $sql = "SELECT Cve_Clte, Fec_Entrega, Pick_Num FROM th_pedido WHERE Fol_folio = '$folio'";
            $res_pedido = mysqli_query($conn, $sql);
            $row_pedido = mysqli_fetch_array($res_pedido);
            $Cve_clte = $row_pedido["Cve_Clte"];
            $Fec_Entrega = $row_pedido["Fec_Entrega"];
            $Pick_Num = $row_pedido["Pick_Num"];
            $fecha_actual = $ga->fecha_actual();

            $sql = "INSERT INTO th_backorder(Folio_BackO, Fol_Folio, Cve_Clte, Fec_Pedido, Fec_Entrega, Fec_BO, Pick_num, Status) VALUES ('$folio_backorder', '$folio', '$Cve_clte', (SELECT Fec_Pedido FROM th_pedido WHERE Fol_folio = '$folio'), '$Fec_Entrega', '$fecha_actual', '$Pick_Num', 'A')";
            $res_th = mysqli_query($conn, $sql);
        }

        $backorder_array = $_POST['backorder_array'];
        foreach($backorder_array as $backorder)
        {
            extract($backorder);

            $sql_bo = "SELECT COUNT(Folio_BackO) as fol_exist FROM td_backorder WHERE Folio_BackO = '$folio_backorder' AND Cve_Articulo = '$Cve_Articulo' AND Cve_Lote = '$Cve_Lote'";
            if (!($res_vs_bo = mysqli_query($conn, $sql_bo))) {echo " Existencia_Total: (" . mysqli_error($conn) . ") ";}
            $folio_exist_td = mysqli_fetch_array($res_vs_bo)["fol_exist"];

            if(!$folio_exist_td)
            {
                $sql = "INSERT INTO td_backorder(Folio_BackO, Cve_Articulo, Cve_Lote, Cantidad_Pedido, Cantidad_BO, Status) VALUES ('$folio_backorder', '$Cve_Articulo', '$Cve_Lote', '$Cantidad_Pedido', '$Cantidad_BO', 'A')";
                $res_td = mysqli_query($conn, $sql);
            }
            else
            {
                $sql = "UPDATE td_backorder SET Cantidad_BO = (Cantidad_BO+$Cantidad_BO) WHERE Folio_BackO = '$folio_backorder' AND Cve_Articulo = '$Cve_Articulo' AND Cve_Lote = '$Cve_Lote'";
                $res_td = mysqli_query($conn, $sql);
            }

        }

    }

    //echo "OKxx $folio";
    SurtidoWEBoAPK($folio);
    //echo "OK1x";

    $sql = "SELECT IFNULL(TipoPedido, 'P') as es_ot FROM th_pedido WHERE Fol_folio = '".$items["folio"]."';";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $es_ot = mysqli_fetch_array($res)["es_ot"];

    //$porcentaje = ($items["surtidas"] * 100)/$items["pedidas"];
  }//$borro_de_t_recorrido_surtido = true
    echo json_encode(array(
      "borro_de_t_recorrido_surtido" => $borro_de_t_recorrido_surtido,
      "porcentaje" => $porcentaje,
      "es_ot" => $es_ot,
      //"es_rb" => $ubicacion_reabasto,
      "success" => true,
      "query" => $sql,
      "sql_insert" => $sql_insert,
      "sql_th_aduana" => $sql_th_aduana,
      "sql_existe_entrada" => $sql_existe_entrada,
      "sql_update" => $sql_update
    ));
    mysqli_close($conn);
    exit;    
}



if($_POST['action'] === 'verificarSiElPedidoEstaSurtiendose')
{
/*
    $folio = $_POST['folio'];
    $is_backorder = $_POST['is_backorder'];
    $sufijo = $_POST['sufijo'];
*/
   //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
   $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'utf8') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset2'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);
*/
    /*
    $sql_status = "rr";
    if($is_backorder)
        $sql_status = "SELECT Status as status FROM th_backorder WHERE Fol_Folio = '{$folio}';";
    else
    {
        if($sufijo == 0)
           $sql_status = "SELECT status FROM th_pedido WHERE Fol_folio = '{$folio}';";
       else
           $sql_status = "SELECT status FROM th_subpedido WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';";
    }
*/
    //if (!($res = mysqli_query($conn, $sql_status)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    //$status = mysqli_fetch_array($res)['status'];
    //$data = $ga->verificarStatus($_POST['folio'], $_POST['is_backorder'], $_POST['sufijo']);
    //$arr = ["success" => true, "status" => $data[0]];

    $arr = array(true);
    echo json_encode($arr);
    //exit;
    //echo "OK";
}

if($_POST['action'] === 'EditarPrioridad') 
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $folio = $_POST['folio'];
    $prioridad = $_POST['prioridad'];

    $sql = "UPDATE th_pedido SET 
                ID_Tipoprioridad = '{$prioridad}'
            WHERE Fol_folio = '{$folio}'";

    mysqli_query($conn, $sql);
    $arr = array(true);
    echo json_encode($arr);exit;
}


if($_POST['action'] === 'guardarDestinatario') 
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $folio = $_POST['folio'];
    $razonsocial = $_POST['razonsocial'];
    $direccion = $_POST['direccion'];
    $colonia = $_POST['colonia'];
    $postal = $_POST['postal'];
    $ciudad = utf8_decode($_POST['ciudad']);
    $contacto = $_POST['contacto'];
    $telefono = $_POST['telefono'];
    $estado = $_POST['estado'];

    $sql = "SELECT id_destinatario FROM Rel_PedidoDest WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);
    $result = mysqli_fetch_assoc($query);
    $id_destinatario = $result['id_destinatario'];

    $sql = "UPDATE c_destinatarios SET 
                razonsocial = '{$razonsocial}',
                direccion = '{$direccion}',
                colonia = '{$colonia}',
                postal = '{$postal}',
                ciudad = '{$ciudad}',
                contacto = '{$contacto}',
                telefono = '{$telefono}',
                estado = '{$estado}'
            WHERE id_destinatario = '{$id_destinatario}'";

    if(isset($_POST['correccion']))
    {
        if($id_destinatario == "")
        {
            $sql = "INSERT INTO c_destinatarios(razonsocial, direccion, colonia, postal, ciudad, contacto, telefono, estado) 
            VALUES ('{$razonsocial}', '{$direccion}', '{$colonia}', '{$postal}', '{$ciudad}', '{$contacto}', '{$telefono}', '{$estado}')";
        }
    }
    mysqli_multi_query($conn, $sql);
    echo json_encode($result);exit;
}

if($_POST['action'] === 'loadCajaDetalle') 
{
    $result = $ga->loadCajaDetalle($_POST);
    echo json_encode($result);
}

if($_POST['action'] === 'changeNumberBox') 
{
    $result = $ga->changeNumberBox($_POST);
    echo json_encode($result);
}


if($_POST['action'] == 'CambiarStatusVarios')
{
    $nuevo_status = $_POST['status'];
    $motivo = $_POST['motivo'];
    $folios = $_POST['folios'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    foreach($folios as $folio)
    {
        $utf8Sql = "SET NAMES 'utf8mb4';";
        $res_charset = mysqli_query($conn, $utf8Sql);

        $sql = "UPDATE th_pedido SET tipo_asignacion = 'Cambio de Status en Listo por Asignar', status = '$nuevo_status' WHERE fol_folio = '{$folio}';";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}
    }
    echo "OK";
}


if($_POST['action'] === 'cambiarStatus')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $folio = $_POST['folio'];
    $nuevo_status = $_POST['status'];
    $almacen = $_POST['almacen'];
    $motivo = $_POST['motivo'];
    $sufijo = $_POST['sufijo'];
    //$result = array();

    if($motivo == "QASEND")
    {
        if($sufijo == 0)//en app/template/page/areaembarque/list.php, 
        {               //sufijo = 0, significa que se va a pasar todo el pedido

            $sql = "
            SELECT 
                status
            FROM th_pedido 
            WHERE fol_folio = '{$folio}' AND cve_almac = '{$almacen}';
            ";
            $sql2 = "UPDATE th_subpedido SET HFE = NOW(), Hora_Final = NOW() WHERE fol_folio = '{$folio}' AND cve_almac = '{$almacen}';";
            if (!($res2 = mysqli_query($conn, $sql2))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
        }
        else
        {
            $sql = "
            SELECT 
                status
            FROM th_subpedido 
            WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}' AND cve_almac = '{$almacen}';
            ";
            $sql2 = "UPDATE th_subpedido SET HFE = NOW(), Hora_Final = NOW() WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}' AND cve_almac = '{$almacen}';";
            if (!($res2 = mysqli_query($conn, $sql2))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
        }

        if(substr($folio, 0, 2) == 'TR')
        {
            $sql = "SELECT clave FROM c_almacenp WHERE id = '{$almacen}'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}
            $cve_almac = mysqli_fetch_array($res)["clave"];

            $sql2 = "CALL SPWS_TerminaPedido('{$cve_almac}', '{$folio}', {$sufijo})";
            if (!($res2 = mysqli_query($conn, $sql2))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
        }
    }
    else
        $sql = "
        SELECT 
            status
        FROM th_pedido 
        WHERE fol_folio = '{$folio}' AND cve_almac = '{$almacen}';
        ";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $status = mysqli_fetch_array($res)["status"];
    $cambiar_status = false;
    $generar_cajas = false;
    $generar_entrada = false;
    $liberar_ubicacion = false;
    
    $sql = "SELECT * FROM th_pedido where fol_folio = '{$folio}';";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $pedido = mysqli_fetch_array($res);
  
    $sql2 ="select * from td_pedido where fol_folio = '{$folio}';";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $detalle = mysqli_fetch_array($res);

    $sql = "UPDATE th_pedido SET Observaciones = '{$motivo}' WHERE fol_folio = '{$folio}';";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
  
    switch($status)
    {
        case "I":
            switch($nuevo_status)
            {
                case "A":
                case "K":
                    $cambiar_status = true;
                    $result["success"] = true;
                break;
                default:
                    $result["success"] = false;
                    $result["msj"] = "Este cambio de status no puede realizarse";
                break;
            }
        break;
        case "A":
            switch($nuevo_status)
            {
                case "S":
                    $sql = "SELECT count(*) as cont  FROM th_subpedido where fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
                    $subpedido = mysqli_fetch_array($res)["cont"];
                
                    $sql = "SELECT count(*) as cont  FROM t_recorrido_surtido where fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}
                    $ruta = mysqli_fetch_array($res)["cont"];
                    
                    if($subpedido > 0 && $ruta > 0)
                    {
                        $cambiar_status = true;
                        $result["success"] = true;
                    }
                    else
                    {
                        $result["success"] = false;
                        $result["msj"] = "Este cambio de status no puede realizarse, favor de verificar que los datos esten correctos";
                    }
                break;
                case "T":
                    $cambiar_status = true;
                    $result["success"] = true;
                    $sql = "UPDATE th_pedido SET tipo_asignacion = 'Cambio de Status en Listo por Asignar' where fol_folio = '{$folio}';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}
                break;
                case "K":
                    $cambiar_status = true;
                    $result["success"] = true;
                break;
                default:
                    $result["success"] = false;
                    $result["msj"] = "Este cambio de status no puede realizarse";
                break;
            }
        break;
        case "S":
            switch($nuevo_status)
            {
                case "A":
                    $result["success"] = true;
                    //$result["sql"] = $sql;

                //$sql = "DELETE FROM td_surtidopiezas WHERE Fol_Folio='{$folio}';";$res = mysqli_query($conn, $sql);
                $sql = "DELETE FROM t_recorrido_surtido WHERE fol_folio='{$folio}';";$res = mysqli_query($conn, $sql);
                //$sql = "DELETE FROM t_cardex WHERE Destino='{$folio}';";$res = mysqli_query($conn, $sql);
                //$sql = "DELETE FROM t_tarima WHERE Fol_Folio='{$folio}';";$res = mysqli_query($conn, $sql);
                $sql = "DELETE FROM td_subpedido WHERE Fol_Folio='{$folio}';";$res = mysqli_query($conn, $sql);
                //$sql = "DELETE FROM rel_uembarquepedido WHERE Fol_Folio='{$folio}';";$res = mysqli_query($conn, $sql);
                //$sql = "DELETE FROM td_cajamixta WHERE Cve_Cajamix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE ";Fol_Folio='{$folio}');$res = mysqli_query($conn, $sql);
                //$sql = "DELETE FROM T_Log WHERE Fol_Folio='{$folio}';";$res = mysqli_query($conn, $sql);
                //$sql = "DELETE FROM th_cajamixta WHERE Fol_Folio='{$folio}';";$res = mysqli_query($conn, $sql);
                $sql = "DELETE FROM th_subpedido WHERE Fol_Folio='{$folio}';";$res = mysqli_query($conn, $sql);
                $sql = "DELETE FROM td_backorder WHERE Folio_BackO IN (SELECT Folio_BackO FROM th_backorder WHERE Fol_Folio='{$folio}')";$res = mysqli_query($conn, $sql);
                $sql = "DELETE FROM th_backorder WHERE Fol_Folio='{$folio}';";$res = mysqli_query($conn, $sql);
                //$sql = "DELETE FROM td_ordenembarque WHERE Fol_folio='{$folio}';";$res = mysqli_query($conn, $sql);
                $sql = "DELETE FROM t_cardex WHERE destino='{$folio}';";$res = mysqli_query($conn, $sql);
                $sql = "UPDATE th_pedido SET STATUS='A', Activo=1 WHERE Fol_Folio='{$folio}';";$res = mysqli_query($conn, $sql);
                $sql = "UPDATE td_pedido SET STATUS='A' WHERE Fol_Folio='{$folio}';";$res = mysqli_query($conn, $sql);


                    $cambiar_status = false;
                break;
                case "L":
                    $sql ="SELECT count(*) as cont FROM td_surtidopiezas where fol_folio = '".$folio."';";
                    //if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(surtido): (" . mysqli_error($conn) . ") ";}
                    $res = mysqli_query($conn, $sql);
                    $surtido = mysqli_fetch_array($res)["cont"];
                    
                    if($surtido > 0)
                    {
                        $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';";
                        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(S-L,P): (" . mysqli_error($conn) . ") ";}
                    }
                    $sql = "
                        UPDATE th_subpedido set status = 'L' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                        UPDATE th_pedido set status = 'L' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';";
                    //if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(S-A): (" . mysqli_error($conn) . ") ";}
                    $res = mysqli_multi_query($conn, $sql);
                    $result["success"] = true;
                    //$result["sql"] = $sql;
                    $cambiar_status = true;
                case "P":
                    $sql ="SELECT count(*) as cont FROM td_surtidopiezas where fol_folio = '".$folio."';";
                    //if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(surtido): (" . mysqli_error($conn) . ") ";}
                    $res = mysqli_query($conn, $sql);
                    $surtido = mysqli_fetch_array($res)["cont"];
                    
                    if($surtido > 0)
                    {
                        $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}' and Sufijo = '{$sufijo}';";
                        //if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(S-L,P): (" . mysqli_error($conn) . ") ";}
                        $res = mysqli_query($conn, $sql);
                        $cambiar_status = true;
                        $result["success"] = true;
                    }
                    else
                    {
                        $result["success"] = false;
                        $result["msj"] = "Este cambio de status no puede realizarse, favor de verificar que los datos esten correctos";
                    }
                break;
                case "C":
                case "T":
                    $cambiar_status = true;
                    $result["success"] = true;
                    $sql = "UPDATE th_pedido SET tipo_asignacion = 'Cambio de Status en Status Surtiendo' where fol_folio = '{$folio}';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}
                break;
                case "F":
                    $sql ="SELECT count(*) as cont FROM td_surtidopiezas where fol_folio = '".$folio."';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $surtido = mysqli_fetch_array($res)["cont"];

                    if($surtido > 0)
                    {
                        
                        $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}'; ";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(S-C,T,F): (" . mysqli_error($conn) . ") ";}
                        $generar_cajas = true;
                        $cambiar_status = true;
                        $result["success"] = true;
                    }
                    else
                    {
                        $result["success"] = false;
                        $result["msj"] = "Este cambio de status no puede realizarse, favor de verificar que los datos esten correctos";
                    }
                break;
                case "K":
                    $generar_entrada = true;
                    $cambiar_status = true;
                    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}'; ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(S-C,T,F): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                default:
                    $result["success"] = false;
                    $result["msj"] = "Este cambio de status no puede realizarse";
                break;
            }
        break;
        case "L":
            switch($nuevo_status)
            {
                case "A":
                    $generar_entrada = true;
                    $sql ="SELECT count(*) as cont FROM td_surtidopiezas where fol_folio = '".$folio."';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $surtido = mysqli_fetch_array($res)["cont"];
                
                    if($surtido > 0)
                    {
                        $sql = "
                            DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}';
                            DELETE FROM th_cajamixta WHERE Fol_folio = '{$folio}';
                            update th_pedido set status = 'A' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                            update th_subpedido set status = 'A', cve_usuario = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                        ";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(L-A): (" . mysqli_error($conn) . ") ";}
                        $result["success"] = true;
                    }
                    else
                    {
                        $result["success"] = false;
                        $result["msj"] = "Este cambio de status no puede realizarse, favor de verificar que los datos esten correctos";
                    }
                break;
                case "P":
                    $sql = "UPDATE th_pedido SET status = 'P'  WHERE Fol_folio = '{$folio}' and cve_almac = '{$almacen}';";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(L-P,C,T,F): (" . mysqli_error($conn) . ") ";}

                    $sql = "UPDATE th_subpedido SET status = 'P'  WHERE fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac = '{$almacen}';";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(L-P,C,T,F): (" . mysqli_error($conn) . ") ";}
                case "C":
                case "T":
                case "F":
                    $sql = "update td_surtidopiezas set revisadas = Cantidad where fol_folio = '{$folio}' and cve_almac = '{$almacen}';";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(L-P,C,T,F): (" . mysqli_error($conn) . ") ";}
                    $generar_cajas = true;
                    $cambiar_status = true;
                    $result["success"] = true;
                break;
                case "K":
                    $generar_entrada = true;
                    $cambiar_status = true;
                    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';";
                    //DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';
                            
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(S-C,T,F): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                default:
                    $result["success"] = false;
                    $result["msj"] = "Este cambio de status no puede realizarse";
                break;
            }
        break;
        case "R":
            switch($nuevo_status)
            {
                case "A":
                    $generar_entrada = true;
                    $sql ="SELECT count(*) as cont FROM td_surtidopiezas where fol_folio = '".$folio."';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $surtido = mysqli_fetch_array($res)["cont"];
                
                    if($surtido > 0)
                    {
                        $sql = "
                            DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}';
                            DELETE FROM th_cajamixta WHERE Fol_folio = '{$folio}';
                            update th_pedido set status = 'A' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                            update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                        ";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(R-A): (" . mysqli_error($conn) . ") ";}
                        $result["success"] = true;
                    }
                    else
                    {
                        $result["success"] = false;
                        $result["msj"] = "Este cambio de status no puede realizarse, favor de verificar que los datos esten correctos";
                    }
                break;
                case "L":
                    $sql = "update th_subpedido set status = 'L', Reviso = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(R-L): (" . mysqli_error($conn) . ") ";}
                    $cambiar_status = true;
                    $result["success"] = true;
                break;
                case "P":
                case "C":
                case "T":
                case "F":
                    $sql = "update td_surtidopiezas set revisadas = Cantidad where fol_folio = '{$folio}' and cve_almac = '{$almacen}';";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(R-P,C,T,F): (" . mysqli_error($conn) . ") ";}
                    $generar_cajas = true;
                    $cambiar_status = true;
                    $result["success"] = true;
                break;
                case "K":
                    $generar_entrada = true;
                    $cambiar_status = true;
                    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';";
                    //DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(S-C,T,F): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                default:
                    $result["success"] = false;
                    $result["msj"] = "Este cambio de status no puede realizarse";
                break;
            }
        break;
        case "P":
            switch($nuevo_status)
            {
                case "A":
                    $generar_entrada = true;
                    $sql = "
                        DELETE FROM td_surtidopiezas WHERE fol_folio = '{$folio}';
                        DELETE FROM th_cajamixta WHERE fol_folio = '{$folio}';
                        update th_pedido set status = 'A' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(P-A): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "L":
                    $sql = "
                        update td_surtidopiezas set revisadas = '0' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_pedido set status = 'L' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'L', Reviso = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(P-L): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "C":
                case "T":
                case "F":
                    $generar_cajas = true;
                    $cambiar_status = true;
                    $result["success"] = true;
                break;
                case "K":
                    $generar_entrada = true;
                    $cambiar_status = true;
                    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';";
                    //DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(S-C,T,F): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                default:
                    $result["success"] = false;
                    $result["msj"] = "Este cambio de status no puede realizarse";
                break;
            }
        break;
        case "M":
            switch($nuevo_status)
            {
                case "A":
                    $generar_entrada = true;
                    $sql = "
                          DELETE FROM td_surtidopiezas WHERE fol_folio = '{$folio}';
                          DELETE FROM th_cajamixta WHERE fol_folio = '{$folio}';
                          update th_pedido set status = 'A' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                          update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ', empaco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(M-A): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "L":
                    $sql = "
                        update td_surtidopiezas set revisadas = '0' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_pedido set status = 'L' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'L', Reviso = ' ', empaco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(M-L): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "P":
                case "C":
                case "T":
                case "F":
                    $generar_cajas = true;
                    $cambiar_status = true;
                    $result["success"] = true;
                break;
                case "K":
                    $generar_entrada = true;
                    $cambiar_status = true;
                    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';";
                    //DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(S-C,T,F): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                default:
                    $result["success"] = false;
                    $result["msj"] = "Este cambio de status no puede realizarse";
                break;
            }
        break;
        case "C":
            switch($nuevo_status)
            {
                case "A":
                    $generar_entrada = true;
                    $sql = "SELECT count(*) as cuenta FROM rel_uembarquepedido WHERE cve_ubicacion = (SELECT cve_ubicacion FROM rel_uembarquepedido WHERE fol_folio ='{$folio}');";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $embarque = mysqli_fetch_array($res)["cuenta"];

                    if($embarque >= 2)
                    {
                        $sql="UPDATE t_ubicacionembarque set status = 1 WHERE cve_almac= '{$almacen}';";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(Liberar_ubicacion1): (" . mysqli_error($conn) . ") ";}
                    }
                    else
                    {
                        $sql = "DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$folio}';";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(libera_ubicacion2): (" . mysqli_error($conn) . ") ";}
                    }
                    $sql = "
                        DELETE FROM td_surtidopiezas WHERE fol_folio = '{$folio}';
                        DELETE FROM th_cajamixta WHERE fol_folio = '{$folio}';
                        DELETE FROM td_cajamixta WHERE Cve_CajaMixD = (Select Cve_CajaMix from th_cajamixta WHERE fol_folio ='{$folio}');
                        update th_pedido set status = 'A' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ', empaco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(C-A): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "L":
                    $sql = "SELECT count(*) as cuenta FROM rel_uembarquepedido WHERE cve_ubicacion = (SELECT cve_ubicacion FROM rel_uembarquepedido WHERE fol_folio ='{$folio}');";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $embarque = mysqli_fetch_array($res)["cuenta"];

                    if($embarque >= 2)
                    {
                        $sql="UPDATE t_ubicacionembarque set status = 1 WHERE cve_almac= '{$almacen}';";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(Liberar_ubicacion1): (" . mysqli_error($conn) . ") ";}
                    }
                    else
                    {
                        $sql = "DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$folio}';";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(libera_ubicacion2): (" . mysqli_error($conn) . ") ";}
                    }
                    $sql = "
                        DELETE FROM th_cajamixta WHERE fol_folio = '{$folio}';
                        DELETE FROM td_cajamixta WHERE Cve_CajaMixD = (Select Cve_CajaMix from th_cajamixta WHERE fol_folio ='{$folio}');
                        update td_surtidopiezas set revisadas = '0' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_pedido set status = 'L' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'L', Reviso = ' ', empaco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(C-L): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "P":
                case "T":
                case "F":
                    $liberar_ubicacion = true;
                    $cambiar_status = true;
                    $result["success"] = true;
                break;
                case "K":
                    $generar_entrada = true;
                    $cambiar_status = true;

                    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}'; 
                            DELETE FROM td_ordenembarque WHERE Fol_folio = '{$folio}';";
                    //DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(S-C,T,F): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                default:
                    $result["success"] = false;
                    $result["msj"] = "Este cambio de status no puede realizarse";
                break;
            }
        break;
        case "E":
            switch($nuevo_status)
            {
                case "A":
                    $generar_entrada =true;
                    $sql = "SELECT count(*) as cuenta FROM rel_uembarquepedido WHERE cve_ubicacion = (SELECT cve_ubicacion FROM rel_uembarquepedido WHERE fol_folio ='{$folio}');";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $embarque = mysqli_fetch_array($res)["cuenta"];

                    if($embarque >= 2)
                    {
                        $sql="UPDATE t_ubicacionembarque set status = 1 WHERE cve_almac= '{$almacen}';";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(Liberar_ubicacion1): (" . mysqli_error($conn) . ") ";}
                    }
                    else
                    {
                        $sql = "DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$folio}';";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(libera_ubicacion2): (" . mysqli_error($conn) . ") ";}
                    }
                    $sql = "
                        DELETE FROM td_surtidopiezas WHERE fol_folio = '{$folio}';
                        DELETE FROM th_cajamixta WHERE fol_folio = '{$folio}';
                        DELETE FROM td_cajamixta WHERE Cve_CajaMixD = (Select Cve_CajaMix from th_cajamixta WHERE fol_folio ='{$folio}');
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update th_pedido set status = 'A' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(E-A): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "L":
                    $sql = "SELECT count(*) as cuenta FROM rel_uembarquepedido WHERE cve_ubicacion = (SELECT cve_ubicacion FROM rel_uembarquepedido WHERE fol_folio ='{$folio}');";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $embarque = mysqli_fetch_array($res)["cuenta"];

                    if($embarque >= 2)
                    {
                        $sql="UPDATE t_ubicacionembarque set status = 1 WHERE cve_almac= '{$almacen}';";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(Liberar_ubicacion1): (" . mysqli_error($conn) . ") ";}
                    }
                    else
                    {
                        $sql = "DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$folio}';";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(libera_ubicacion2): (" . mysqli_error($conn) . ") ";}
                    }
                    $sql = "
                        DELETE FROM th_cajamixta WHERE fol_folio = '{$folio}';
                        DELETE FROM td_cajamixta WHERE Cve_CajaMixD = (Select Cve_CajaMix from th_cajamixta WHERE fol_folio ='{$folio}');
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update td_surtidopiezas set revisadas = '0' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_pedido set status = 'L' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'L', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(E-L): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "P":
                    $sql = "SELECT count(*) as cuenta FROM rel_uembarquepedido WHERE cve_ubicacion = (SELECT cve_ubicacion FROM rel_uembarquepedido WHERE fol_folio ='{$folio}');";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $embarque = mysqli_fetch_array($res)["cuenta"];

                    if($embarque >= 2)
                    {
                        $sql="UPDATE t_ubicacionembarque set status = 1 WHERE cve_almac= '{$almacen}';";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(Liberar_ubicacion1): (" . mysqli_error($conn) . ") ";}
                    }
                    else
                    {
                        $sql = "DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$folio}';";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(libera_ubicacion2): (" . mysqli_error($conn) . ") ";}
                    }
                    $sql = "
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update th_pedido set status = 'P' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'P', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(E-P): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "C":
                    $sql = "
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update th_pedido set status = 'C' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'C', Embarco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(E-C): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "T":
                case "F":
                    $sql = "INSERT INTO td_ordenembarque(Fol_folio,status) VALUES('{$folio}','A')";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(E-TF): (" . mysqli_error($conn) . ") ";}
                    $cambiar_status = true;
                    $result["success"] = true;
                break;
                case "K":
                    $generar_entrada = true;
                    $cambiar_status = true;
                    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}'; 
                            DELETE FROM td_ordenembarque WHERE Fol_folio = '{$folio}';";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(S-C,T,F): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                default:
                    $result["success"] = false;
                    $result["msj"] = "Este cambio de status no puede realizarse";
                break;
            }
        break;
        case "T":
            switch($nuevo_status)
            {
                case "A":
                    $generar_entrada = true;
                    $sql = "
                        DELETE FROM td_surtidopiezas WHERE fol_folio = '{$folio}';
                        DELETE FROM th_cajamixta WHERE fol_folio = '{$folio}';
                        DELETE FROM td_cajamixta WHERE Cve_CajaMixD = (Select Cve_CajaMix from th_cajamixta WHERE fol_folio ='{$folio}');
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update th_pedido set status = 'A' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(T-A): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "L":
                    $sql = "
                        DELETE FROM th_cajamixta WHERE fol_folio = '{$folio}';
                        DELETE FROM td_cajamixta WHERE Cve_CajaMixD = (Select Cve_CajaMix from th_cajamixta WHERE fol_folio ='{$folio}');
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update td_surtidopiezas set revisadas = '0' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_pedido set status = 'L' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'L', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(T-L): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "S":
                    $sql = "
                        UPDATE th_pedido set status = 'S' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        UPDATE th_subpedido set status = 'S' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(T-L): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "P":
                    $sql = "
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update th_pedido set status = 'P' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'P', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(T-P): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "C":
                    $sql = "DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(T-C): (" . mysqli_error($conn) . ") ";}
                    $cambiar_status = true;
                    $result["success"] = true;
                break;
                case "F":
                    $cambiar_status = true;
                    $result["success"] = true;
                break;
                case "K":
                    $generar_entrada =true;
                    $cambiar_status = true;
                    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}'; 
                            DELETE FROM td_ordenembarque WHERE Fol_folio = '{$folio}';";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(S-C,T,F): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                default:
                    $result["success"] = false;
                    $result["msj"] = "Este cambio de status no puede realizarse";
                break;
            }
        break;
        case "F":
            switch($nuevo_status)
            {
                case "A":
                    $generar_entrada = true;
                    $sql = "
                        DELETE FROM td_surtidopiezas WHERE fol_folio = '{$folio}';
                        DELETE FROM th_cajamixta WHERE fol_folio = '{$folio}';
                        DELETE FROM td_cajamixta WHERE Cve_CajaMixD = (Select Cve_CajaMix from th_cajamixta WHERE fol_folio ='{$folio}');
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update th_pedido set status = 'A' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(F-A): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "L":
                    $sql = "
                        DELETE FROM th_cajamixta WHERE fol_folio = '{$folio}';
                        DELETE FROM td_cajamixta WHERE Cve_CajaMixD = (Select Cve_CajaMix from th_cajamixta WHERE fol_folio ='{$folio}');
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update td_surtidopiezas set revisadas = '0' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_pedido set status = 'L' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'L', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(F-L): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "P":
                    $sql = "
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update th_pedido set status = 'P' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'P', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(F-P): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "C":
                    $sql = "
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update th_pedido set status = 'C' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'C', Embarco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(F-C): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "T":
                    $cambiar_status = true;
                    $result["success"] = true;
                break;
                case "K":
                    $generar_entrada = true;
                    $cambiar_status = true;
                    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}'; 
                            DELETE FROM td_ordenembarque WHERE Fol_folio = '{$folio}';";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(S-C,T,F): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                default:
                    $result["success"] = false;
                    $result["msj"] = "Este cambio de status no puede realizarse";
                break;
            }
        break;
        case "O":
        switch($nuevo_status)
        {
            case "L":
            $result["success"] = false;
            if($sufijo == 0)
            {
                $sql = "
                    UPDATE th_pedido set status = 'L' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                    UPDATE th_subpedido set status = 'L' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                    UPDATE td_consolidado set Status = 'L' where Fol_Folio = '{$folio}';
                ";
                if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(O-L): (" . mysqli_error($conn) . ") ";}
                $result["success"] = true;
            }
            break;
        }
        break;
        case "K":
            switch($nuevo_status)
            {
                case "A":
                    $sql = "
                        SET SQL_SAFE_UPDATES = 0;
                        DELETE FROM td_surtidopiezas WHERE fol_folio = '{$folio}';
                        DELETE FROM td_cajamixta WHERE Cve_CajaMixD = (Select Cve_CajaMix from th_cajamixta WHERE fol_folio = '{$folio}');
                        DELETE FROM th_cajamixta WHERE fol_folio = '{$folio}';
                        DELETE FROM td_ordenembarque WHERE Fol_folio = '{$folio}';
                        DELETE FROM t_recorrido_surtido WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';
                        DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';
                        DELETE FROM th_subpedido where Fol_folio = '{$folio}';
                        DELETE FROM td_subpedido where Fol_folio = '{$folio}';
                        update th_pedido set status = 'A' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(K-A): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "L":
                    $sql ="SELECT count(*) as cont FROM td_surtidopiezas where fol_folio = '".$folio."';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $surtido = mysqli_fetch_array($res)["cont"];
                
                    if($surtido > 0)
                    {
                        $sql = "
                            DELETE FROM t_recorrido_surtido WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';
                            DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';
                            DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                            update th_pedido set status = 'L' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                            update th_subpedido set status = 'L', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                        ";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(K-L): (" . mysqli_error($conn) . ") ";}
                        $result["success"] = true;
                    }
                    else
                    {
                        $result["success"] = false;
                        $result["msj"] = "Este cambio de status no puede realizarse, favor de verificar que los datos esten correctos";
                    }
                break;
                case "P":
                    $sql ="SELECT * FROM td_surtidopiezas where fol_folio = '".$folio."';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $surtido_piezas = mysqli_fetch_all($res)[0];
                    $cantidad = $surtido_piezas["Cantidad"];
                    $revisadas = $surtido_piezas["revisadas"];
                
                    if($cantidad == $revisadas)
                    {
                        $sql = "
                            DELETE FROM t_recorrido_surtido WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';
                            DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';
                            DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                            update th_pedido set status = 'P' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                            update th_subpedido set status = 'P', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac= '{$almacen}';
                        ";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(K-P): (" . mysqli_error($conn) . ") ";}
                        $result["success"] = true;
                    }
                    else
                    {
                        $result["success"] = false;
                        $result["msj"] = "Este cambio de status no puede realizarse, favor de verificar que los datos esten correctos";
                    }
                break;
                case "C":
                   
                    $sql ="SELECT count(*) as cont FROM td_surtidopiezas where fol_folio = '".$folio."';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $surtido = mysqli_fetch_array($res)["cont"];
                    $cantidad = mysqli_fetch_array($res)["Cantidad"];
                    $revisadas = mysqli_fetch_array($res)["revisadas"];
                    $sql = "SELECT count(*) as cont FROM th_cajamixta where fol_folio = '".$folio."';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $cajamix = mysqli_fetch_array($res)["cont"];
                    $sql = "SELECT count(*) as cont FROM td_cajamixta where 1;";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $caja = mysqli_fetch_array($res)["cont"];
                    $sql = "SELECT count(*) as cont FROM rel_uembarquepedido where fol_folio = '".$folio."';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $rel_embarque = mysqli_fetch_array($res)["cont"];
                
                    if($cantidad == $revisadas && $cajamix > 0 && $caja > 0 && $rel_embarque > 0)
                    {
                        $sql = "
                            DELETE FROM t_recorrido_surtido WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';
                            DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        ";
                        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(K-C): (" . mysqli_error($conn) . ") ";}
                        $cambiar_status = true;
                        $result["success"] = true;
                    }
                    else
                    {
                        $result["success"] = false;
                        $result["msj"] = "Este cambio de status no puede realizarse, favor de verificar que los datos esten correctos";
                    }
                break;
                case "T":
                case "F":
                    $sql ="SELECT count(*) as cont FROM td_surtidopiezas where fol_folio = '".$folio."' AND Sufijo = '{$sufijo}';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $surtido = mysqli_fetch_array($res)["cont"];
                
                    $sql ="SELECT count(*) as cont FROM th_cajamixta where fol_folio = '".$folio."';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $cajamix = mysqli_fetch_array($res)["cont"];
                
                    $sql ="SELECT count(*) as cont FROM td_cajamixta ;";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $caja = mysqli_fetch_array($res)["cont"];
                
                    $sql ="SELECT count(*) as cont FROM td_ordenembarque where fol_folio = '".$folio."';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $orden = mysqli_fetch_array($res)["cont"];
                
                    if($surtido > 0 && $cajamix > 0 && $caja > 0 && $orden > 0)
                    {  
                        $cambiar_status = true;
                        $result["success"] = true;
                    }
                    else
                    {
                        $result["success"] = false;
                        $result["msj"] = "Este cambio de status no puede realizarse, favor de verificar que los datos esten correctos";
                    }
                break;
                case "S":
                    $sql = "
                        SET SQL_SAFE_UPDATES = 0;
                        DELETE FROM td_ordenembarque WHERE Fol_folio = '{$folio}';
                        UPDATE th_pedido set status = 'S' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        UPDATE th_subpedido set status = 'S' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        INSERT INTO t_recorrido_surtido (idy_ubica, cve_almac, cve_pasillo, cve_rack, Seccion, cve_nivel, Ubicacion, orden_secuencia, fol_folio, Sufijo, Cve_articulo, cve_usuario, picking, claverp, ClaveEtiqueta, cve_lote, Cantidad) (SELECT idy_ubica, cve_almac, cve_pasillo, cve_rack, Seccion, cve_nivel, Ubicacion, orden_secuencia, fol_folio, Sufijo, Cve_articulo, cve_usuario, picking, claverp, ClaveEtiqueta, cve_lote, Cantidad FROM t_registro_surtido WHERE fol_folio = '{$folio}' AND Activo = 1);
                    ";

                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(K-A): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                default:
                    $result["success"] = false;
                    $result["msj"] = "Este cambio de status no puede realizarse";
                break;
            }
        break;
    }
    $result['data']['sql1'] = $sql;
    if($liberar_ubicacion)
    {
        $sql = "SELECT count(*) as cuenta FROM rel_uembarquepedido WHERE cve_ubicacion = (SELECT cve_ubicacion FROM rel_uembarquepedido WHERE fol_folio ='{$folio}');";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
        $embarque = mysqli_fetch_array($res)["cuenta"];

        if($embarque >= 2)
        {
            $sql="UPDATE t_ubicacionembarque set status = 1 WHERE cve_almac= '{$almacen}';";
            if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(Liberar_ubicacion1): (" . mysqli_error($conn) . ") ";}
        }
        else
        {
            $sql = "DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$folio}';";
            if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(libera_ubicacion2): (" . mysqli_error($conn) . ") ";}
        }
    }
    if($generar_entrada)
    {
      /*se debe generar una entrada para los productos de la tabla td_surtidopiezas en th_aduana, td_aduana, INSERT
      th_entalmacen y td_entalmacen,  INSERT
      los productos en td_entalmacen se deben dejar en pendiente de acomodo y 
      guardar tambien en t_pendientesacomodo. INSERT*/
    }
    if($generar_cajas)
    {
        $sql = "SELECT cve_usuario FROM th_subpedido where fol_folio != '{$folio}';";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
        if($res->num_rows != null)
        {
            $usuario = mysqli_fetch_array($res)[0]["cve_usuario"];
            $sql="select clave from c_almacenp where id = ".$pedido["cve_almac"];
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $cve_almacen = mysqli_fetch_array($res)["clave"];
            $sp = "SPWS_PreparaPedidoSurtido".((strpos($_SERVER['HTTP_HOST'], 'nikken') !== false)?"Nik_2":"");
            $sql2 =  "call ".$sp ."('".$cve_almacen."','".$folio."','".$usuario."','".date('Y-m-d H:i:s')."');" ;
            mysqli_multi_query($conn, $sql2);
        }
    }
    //$res = "";
    if($cambiar_status)
    {
        $sql = "
            update th_pedido set status = '".$nuevo_status."' where Fol_folio = '{$folio}' and cve_almac = '{$almacen}';
            update th_subpedido set status = '".$nuevo_status."' where fol_folio = '{$folio}' and Sufijo = '{$sufijo}' and cve_almac = '{$almacen}';
        ";
        //if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(Cambiar_status): (" . mysqli_error($conn) . ") ";}
        $res = mysqli_multi_query($conn, $sql);
    }
    $result['data']['success'] = $result["success"];
    $result['data']['status'] = $status;
    $result['data']['msj'] = $result["msj"];
    $result['data']['folio'] = $folio;
    $result['data']["bandera"]['cambiar_status'] = $cambiar_status;
    $result['data']["bandera"]['generar_cajas'] = $generar_cajas;
    $result['data']["bandera"]['generar_entrada'] = $generar_entrada;
    $result['data']["bandera"]['liberar_ubicacion'] = $liberar_ubicacion;
    $result['data']['nuevo_status'] = $nuevo_status;
    $result['data']['cve_almac'] = $almacen;
    $result['data']['sql2'] = $sql2;
    $result['data']['res'] = $res;

    echo json_encode($result);
    //echo $res;
    //exit;
}

if( $_POST['action'] == 'traerporcentaje' ) 
{
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $folio = $_POST['folio'];
  
   $sql = "
       SELECT
          IFNULL(concat(FLOOR((sum(s.Cantidad)*100)/ sum(od.Num_cantidad))), '0') AS surtido
       FROM th_pedido o
         LEFT JOIN td_pedido od ON od.Fol_folio = o.Fol_folio
         LEFT JOIN td_surtidopiezas s ON s.fol_folio = od.Fol_folio AND s.Cve_articulo = od.Cve_articulo AND s.cve_almac = o.cve_almac
       WHERE o.Fol_folio = '{$folio}';
   "; 
   if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
   $row = mysqli_fetch_array($res);
   $porcentaje = $row['surtido'];

    $success = true;
    $arr = array(
        "success" => $success,
        "resp"    => $porcentaje
    );
    echo json_encode($arr);
}

if($_POST['action'] === 'asignarAreaRecepcion')
{
    $arearecepcion = $_POST['arearecepcion'];
    $folio         = $_POST['folio'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "UPDATE th_pedido SET status = 'T' WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "UPDATE th_subpedido SET status = 'T', Hora_Final = NOW(), HFE = NOW() WHERE fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);


    $sql = "UPDATE th_entalmacen SET cve_ubicacion = '{$arearecepcion}' WHERE Fol_OEP = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "UPDATE t_cardex SET destino = '{$arearecepcion}' WHERE origen = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "UPDATE t_MovCharolas SET Destino = '{$arearecepcion}' WHERE Origen = '{$folio}'";
    $query = mysqli_query($conn, $sql);


    $sql = "SELECT Fol_Folio, Cve_Proveedor FROM th_entalmacen WHERE Fol_OEP = '{$folio}';";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($query);
    $fol_folio = $row['Fol_Folio'];
    $ID_Proveedor = $row['Cve_Proveedor'];

    $sql = "UPDATE td_entalmacen SET cve_ubicacion = '{$arearecepcion}' WHERE fol_folio = '{$fol_folio}'";
    $query = mysqli_query($conn, $sql);

//*********************************************************************************
//                          PENDIENTE ACOMODO
//*********************************************************************************
    $sql_entrada = "SELECT cve_articulo, cve_lote, CantidadPedida, cve_ubicacion FROM td_entalmacen WHERE fol_folio = '{$fol_folio}'";
    if (!($res = mysqli_query($conn, $sql_entrada))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
    while($row_entrada = mysqli_fetch_array($res))
    {
        $cve_articulo   = $row_entrada['cve_articulo'];
        $cve_lote       = $row_entrada['cve_lote'];
        $CantidadPedida = $row_entrada['CantidadPedida'];
        $cve_ubicacion  = $row_entrada['cve_ubicacion'];

        $sql_rtm = "SELECT COUNT(*) as existe_entrada FROM t_pendienteacomodo WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$cve_ubicacion}' AND ID_Proveedor = '{$ID_Proveedor}'";
        if (!($res_rtm = mysqli_query($conn, $sql_rtm))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}

        if($existe_entrada)
        {
            $sql_entrada = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad + ".$CantidadPedida." WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$cve_ubicacion}' AND ID_Proveedor = '{$ID_Proveedor}'";
            if (!($res_rtm_1 = mysqli_query($conn, $sql_entrada))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
        }
        else
        {
            $sql_entrada = "INSERT INTO t_pendienteacomodo(cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) VALUES('{$cve_articulo}', '{$cve_lote}', $CantidadPedida, '{$cve_ubicacion}', $ID_Proveedor) ON DUPLICATE KEY UPDATE Cantidad = Cantidad + ".$CantidadPedida."";
            if (!($res_rtm_2 = mysqli_query($conn, $sql_entrada))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
        }
    }
//*********************************************************************************/
    echo json_encode(array("success" => true));
    exit;

}

if($_POST['action'] === 'asignarZonaEmbarque')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $folio = $_POST['folio'];
    $almacen = $_POST['almacen'];
    $sufijo = $_POST['sufijo'];
    $zonaembarque = $_POST['zonaembarque'];


    if(substr($folio, 0, 2) == 'TR')
    {
        $sql2 = "CALL SPWS_TerminaPedido('{$almacen}', '{$folio}', {$sufijo})";
        if (!($res2 = mysqli_query($conn, $sql2))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    }

    $se_guardo_relacion_en_tarima = false;
    if(isset($_POST['modulo_ot']))
    {
        $folio_pro = $_POST['folio_pro'];
        $tipo_embarque = $_POST['tipo_embarque'];

        $sql = "SELECT id FROM c_almacenp WHERE clave = '$almacen';";
        $query = mysqli_query($conn, $sql);
        $almacen = mysqli_fetch_assoc($query)['id'];


        $sql = "SELECT Cve_Usuario, Cve_Articulo, Cve_Lote, Cantidad, Cant_Prod FROM t_ordenprod WHERE Folio_Pro = '$folio_pro';";
        $query = mysqli_query($conn, $sql);
        $res = mysqli_fetch_assoc($query);
        $Cve_Usuario  = $res['Cve_Usuario'];
        $Cve_Articulo = $res['Cve_Articulo'];
        $Cve_Lote  = $res['Cve_Lote'];
        $Cantidad  = $res['Cantidad'];
        $Cant_Prod = $res['Cant_Prod'];

        //*******************************************************************************************
        //aqui cuento los productos que tiene td_pedido a ver si es necesario dividirlo con sufijos
        //*******************************************************************************************
        $sql = "SELECT * FROM td_pedido WHERE Fol_folio = '{$folio}' AND cve_articulo != '{$Cve_Articulo}'";
        $query = mysqli_query($conn, $sql);
        $num_productos = mysqli_num_rows($query);

        if($num_productos >= 1)
        {
            $i_sufijo = 1;

            while($row = mysqli_fetch_assoc($query))
            {
                extract($row);
                $sql = "INSERT INTO th_pedido(Fol_folio, Fec_Pedido, Cve_clte, status, Fec_Entrega, cve_Vendedor, Num_Meses, Observaciones, statusaurora, ID_Tipoprioridad, Fec_Entrada, TipoPedido, ruta, bloqueado, DiaO, TipoDoc, rango_hora, cve_almac, destinatario, Id_Proveedor, cve_ubicacion, Pick_Num, Cve_Usuario, Ship_Num, BanEmpaque, Cve_CteProv) 
                (SELECT CONCAT(Fol_folio,'_', '{$i_sufijo}') AS Folio, Fec_Pedido, Cve_clte, status, Fec_Entrega, cve_Vendedor, Num_Meses, Observaciones, statusaurora, ID_Tipoprioridad, Fec_Entrada, TipoPedido, ruta, bloqueado, DiaO, TipoDoc, rango_hora, cve_almac, destinatario, Id_Proveedor, cve_ubicacion, Pick_Num, Cve_Usuario, Ship_Num, BanEmpaque, Cve_CteProv FROM th_pedido WHERE Fol_folio = '{$folio}')";
                $query_insert = mysqli_query($conn, $sql);

                $sql = "INSERT INTO Rel_PedidoDest(Fol_Folio, Cve_Almac, Id_Destinatario) (SELECT CONCAT(Fol_Folio,'_', '{$i_sufijo}'), Cve_Almac, Id_Destinatario FROM Rel_PedidoDest WHERE Fol_Folio = '{$folio}');";
                $query_insert = mysqli_query($conn, $sql);

                $sql = "INSERT INTO td_pedido(Fol_folio, Cve_articulo, Num_cantidad, Num_Meses, SurtidoXCajas, SurtidoXPiezas, status, cve_cot, factor, itemPos, cve_lote, Num_revisadas, Num_Empacados, Auditado, Precio_unitario, Desc_Importe, IVA, id_ot) VALUES('{$Fol_folio}_{$i_sufijo}', '$Cve_articulo', '$Num_cantidad', '$Num_Meses', '$SurtidoXCajas', '$SurtidoXPiezas', '$status', '$cve_cot', '$factor', '$itemPos', '$cve_lote', '$Num_revisadas', '$Num_Empacados', '$Auditado', '$Precio_unitario', '$Desc_Importe', '$IVA', '$id_ot')";
                $query_insert = mysqli_query($conn, $sql);

                $i_sufijo++;
            }

            $sql = "DELETE FROM td_pedido WHERE Fol_folio = '{$folio}' AND cve_articulo = '{$Cve_Articulo}'";
            $query = mysqli_query($conn, $sql);
            $num_productos = mysqli_num_rows($query);
        }

        //*******************************************************************************************


        if($tipo_embarque == 1) // si el pedido es mayor a la cantidad de la orden por lo que debe dividirse
        {
            $sql = "SELECT Num_cantidad FROM td_pedido WHERE Fol_folio = '{$folio}';";
            $query = mysqli_query($conn, $sql);
            $res = mysqli_fetch_assoc($query);
            $Num_cantidad  = $res['Num_cantidad'];

            $nueva_cantidad = $Num_cantidad - $Cant_Prod;

            $folio_cp = $folio;

            if(substr($folio_cp, 0, 2) == "CP")
            {
                $i = 1;
                if(is_numeric(substr($folio_cp, 2, 1)))
                {
                    $i = substr($folio_cp, 2, 1);
                    $i++;
                }

                if($i == 1)
                    $folio_cp = substr($folio_cp, 0, 2).$i.substr($folio_cp, 2);
                else
                    $folio_cp = substr($folio_cp, 0, 2).$i.substr($folio_cp, 3);
            }
            else
                $folio_cp = "CP".$folio_cp;


            $sql = "INSERT INTO th_pedido(Fol_folio, Fec_Pedido, Cve_clte, status, Fec_Entrega, cve_Vendedor, Num_Meses, Observaciones, statusaurora, ID_Tipoprioridad, Fec_Entrada, TipoPedido, ruta, bloqueado, DiaO, TipoDoc, rango_hora, cve_almac, destinatario, Id_Proveedor, cve_ubicacion, Pick_Num, Cve_Usuario, Ship_Num, BanEmpaque, Cve_CteProv) 
            (SELECT '{$folio_cp}' AS Folio, Fec_Pedido, Cve_clte, status, Fec_Entrega, cve_Vendedor, Num_Meses, Observaciones, statusaurora, ID_Tipoprioridad, Fec_Entrada, TipoPedido, ruta, bloqueado, DiaO, TipoDoc, rango_hora, cve_almac, destinatario, Id_Proveedor, cve_ubicacion, Pick_Num, Cve_Usuario, Ship_Num, BanEmpaque, Cve_CteProv FROM th_pedido WHERE Fol_folio = '{$folio}')";
            $query = mysqli_query($conn, $sql);

            $sql = "INSERT INTO td_pedido(Fol_folio, Cve_articulo, Num_cantidad, Num_Meses, SurtidoXCajas, SurtidoXPiezas, STATUS, cve_cot, factor, itemPos, cve_lote, Num_revisadas, Num_Empacados, Auditado, Precio_unitario, Desc_Importe, IVA, id_ot) 
            (SELECT '{$folio_cp}' AS Folio, Cve_articulo, {$nueva_cantidad}, Num_Meses, SurtidoXCajas, SurtidoXPiezas, STATUS, cve_cot, factor, itemPos, cve_lote, Num_revisadas, Num_Empacados, Auditado, Precio_unitario, Desc_Importe, IVA, id_ot FROM td_pedido WHERE Fol_folio = '{$folio}')";
            $query = mysqli_query($conn, $sql);

        }

        $sql = "SELECT ntarima, '{$folio}', 1, cve_articulo, lote, existencia, existencia, 0 FROM ts_existenciatarima WHERE cve_articulo = '$Cve_Articulo' AND lote = '$Cve_Lote'";
        $query = mysqli_query($conn, $sql);

        $se_guardo_relacion_en_tarima = false;
        if(mysqli_num_rows($query) > 0)
        {
            $se_guardo_relacion_en_tarima = true;
            //$sql = "INSERT INTO t_tarima(ntarima, Fol_Folio, Sufijo, cve_articulo, lote, cantidad, Num_Empacados, Abierta) (SELECT ntarima, '{$folio}', 1, cve_articulo, lote, existencia, existencia, 0 FROM ts_existenciatarima WHERE cve_articulo = '$Cve_Articulo' AND lote = '$Cve_Lote')";
            $sql = "SELECT ntarima, '{$folio}', 1, cve_articulo, lote, existencia, existencia, 0 FROM ts_existenciatarima WHERE cve_articulo = '$Cve_Articulo' AND lote = '$Cve_Lote'";
            $query = mysqli_query($conn, $sql);
            $row_t = mysqli_fetch_array($query);
            extract($row_t);

            $sql = "INSERT INTO t_tarima(ntarima, Fol_Folio, Sufijo, cve_articulo, lote, cantidad, Num_Empacados, Abierta) VALUES ($ntarima, '{$folio}', 1, '$cve_articulo', '$lote', $existencia, $existencia, 0)";
            $query = mysqli_query($conn, $sql);

            if($ntarima)
            {
                $sql = "SELECT * FROM V_ExistenciaGralProduccion WHERE Cve_Contenedor = (SELECT Clave_Contenedor FROM c_charolas WHERE IDContenedor = $ntarima)";
                $query = mysqli_query($conn, $sql);
                if(mysqli_num_rows($query) == 0)
                {
                    $sql = "UPDATE c_charolas SET Activo = 0 WHERE IDContenedor = $ntarima AND TipoGen = 1";
                    $query = mysqli_query($conn, $sql);
                }
            }
        }

        //********************************************************************************************************
        // Aqui en este proceso borro todo lo que tenga que ver con el artículo y lote producido ya que el pedido 
        // se relaciona con toda la cantidad y entonces se envía todo lo que contiene
        //********************************************************************************************************
        $sql = "DELETE FROM ts_existenciapiezas WHERE cve_articulo = '$Cve_Articulo' AND cve_lote = '$Cve_Lote';";
        $query = mysqli_query($conn, $sql);

        $sql = "DELETE FROM ts_existenciatarima WHERE cve_articulo = '$Cve_Articulo' AND lote = '$Cve_Lote';";
        $query = mysqli_query($conn, $sql);
        //********************************************************************************************************
        //********************************************************************************************************

        $sql = "UPDATE th_pedido SET Ship_Num = '{$folio_pro}' WHERE Fol_folio = '{$folio}'";
        $query = mysqli_query($conn, $sql);

        $sql = "UPDATE td_pedido SET cve_lote = '{$Cve_Lote}', status = 'C' WHERE Fol_folio = '{$folio}'";
        $query = mysqli_query($conn, $sql);

        $sql = "INSERT INTO th_subpedido(fol_folio, cve_almac, Sufijo, Fec_Entrada, Hora_inicio, cve_usuario, status) 
        VALUES ('{$folio}', {$almacen}, 1, NOW(), NOW(), '{$Cve_Usuario}', 'C')";
        $query = mysqli_query($conn, $sql);

        $sql = "INSERT INTO td_subpedido(fol_folio, cve_almac, Sufijo, Cve_articulo, Num_cantidad, Nun_Surtida, Status, Num_Revisda, Cve_Lote) VALUES ('{$folio}', {$almacen}, 1, '{$Cve_Articulo}', {$Cantidad}, 0,'C', 0, '{$Cve_Lote}')";
        $query = mysqli_query($conn, $sql);

        $sql = "INSERT INTO td_surtidopiezas (fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, Num_Empacados, status) VALUES ('{$folio}', {$almacen}, 1, '{$Cve_Articulo}', '{$Cve_Lote}', {$Cantidad}, 0, 0, 'S')";
        $query = mysqli_query($conn, $sql);

    }//if(isset($_POST['modulo_ot']))



    $evaluarBLSalida = $zonaembarque;

    $arr_BL_Salida = explode("-", $evaluarBLSalida);
    $bl_salida = false;
    if($arr_BL_Salida[0] == 'ESBLSALIDA')
    {
        //es un BL de salida, registro el idy_ubica
        $zonaembarque = $arr_BL_Salida[1];
        $bl_salida = true;
    }

    if($bl_salida == false)
    {
        $sql = "SELECT COUNT(*) as existe FROM th_consolidado WHERE No_OrdComp = '$folio';";
        $query = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($query);
        $consolidado = $row["existe"];

        $sql = "INSERT INTO rel_uembarquepedido (cve_ubicacion, fol_folio, Sufijo, cve_almac, Activo) 
                    VALUES ('{$zonaembarque}', '{$folio}', '{$sufijo}','{$almacen}', 1);";
        if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}

        if($consolidado >= 1)
            $sql = "UPDATE th_subpedido set status = 'B', HFE = NOW(), Hora_Final = NOW() where fol_folio = '".$folio."' and Sufijo = '{$sufijo}';";
        else 
            $sql = "UPDATE th_subpedido set status = 'C', HFE = NOW(), Hora_Final = NOW() where fol_folio = '".$folio."' and Sufijo = '{$sufijo}';";

        if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(21): (" . mysqli_error($conn) . ") ";}

        if($consolidado >= 1)
            $sql = "SELECT count(*) as num_pedidos_C from th_subpedido where fol_folio = '".$folio."' and status = 'B'" ;
        else
            $sql = "SELECT count(*) as num_pedidos_C from th_subpedido where fol_folio = '".$folio."' and status = 'C'" ;

        if(!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";}
        $row = mysqli_fetch_array($res);
        $num_pedidos_C = $row['num_pedidos_C'];

        $sql = "SELECT count(*) as num_pedidos from th_subpedido where fol_folio = '".$folio."'" ;
        if(!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";}
        $row = mysqli_fetch_array($res);
        $num_pedidos = $row['num_pedidos'];

        if($num_pedidos_C == $num_pedidos || $num_pedidos == 0)
        {
            if($consolidado >= 1)
            {
                $folio_xd = $folio[0].$folio[1];
                if($folio_xd != 'XD')
                {
                    $sql = "UPDATE th_pedido set status = 'B' where fol_folio = '".$folio."'";
                    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(22): (" . mysqli_error($conn) . ") ";}

                    $sql = "UPDATE td_pedido set status = 'B' where fol_folio = '".$folio."'";
                    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(23): (" . mysqli_error($conn) . ") ";}

                    $sql = "UPDATE td_subpedido set status = 'B' where fol_folio = '".$folio."'";
                    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(24): (" . mysqli_error($conn) . ") ";}
                }
            }
            else
            {
                $sql = "UPDATE th_pedido set status = 'C' where fol_folio = '".$folio."'";
                if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(25): (" . mysqli_error($conn) . ") ";}

                $sql = "UPDATE td_pedido set status = 'C' where fol_folio = '".$folio."'";
                if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(26): (" . mysqli_error($conn) . ") ";}

                if($sufijo == 0)
                {
                    $sql = "UPDATE td_consolidado set Status = 'C' where Fol_Folio = '".$folio."'";
                    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(28): (" . mysqli_error($conn) . ") ";}

                    $sql = "INSERT INTO th_subpedido (Fol_folio, cve_almac, Sufijo, Fec_Entrada, status, cve_usuario, Hora_inicio, Hora_Final) VALUES ('{$folio}', {$almacen}, 1, NOW(), 'C', '', NOW(), NOW())";
                    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(29): (" . mysqli_error($conn) . ") ";}

                    $sql = "INSERT INTO td_subpedido(fol_folio, cve_almac, Sufijo, Cve_articulo, Num_cantidad, Nun_Surtida, Status, Num_Revisda, Cve_Lote) (SELECT '{$folio}', {$almacen}, 1, Cve_articulo, Num_cantidad, 0,'C', 0, cve_lote FROM td_pedido WHERE Fol_folio = '{$folio}')";
                    $query = mysqli_query($conn, $sql);
                    $sufijo = 1;
                }
                else
                {
                    $sql = "UPDATE td_subpedido set status = 'C' where fol_folio = '".$folio."'";
                    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(27): (" . mysqli_error($conn) . ") ";}
                }
            }
        }
      
        $sql = "UPDATE t_ubicacionembarque set status = 2 where cve_ubicacion  = '".$zonaembarque."' and status = 1;";
        if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ";}

        if(isset($_POST['oc_cross']))
        {
            $oc = $_POST['oc_cross'];
            $sql = "SELECT Cve_articulo, cve_lote, Num_cantidad, '$zonaembarque' as AreaStagging, (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = $oc) AS ID_Proveedor FROM td_pedido WHERE Fol_folio = '$folio' AND CONCAT(cve_articulo, IFNULL(cve_lote, '')) IN (SELECT CONCAT(cve_articulo, IFNULL(cve_lote, '')) FROM td_aduana WHERE num_orden = $oc)";

            if(!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";}
            while($row = mysqli_fetch_array($res))
            {
                $Cve_articulo = $row["Cve_articulo"];
                $cve_lote     = $row["cve_lote"];
                $AreaStagging = $row["AreaStagging"];
                $ID_Proveedor = $row["ID_Proveedor"];
                $Num_cantidad = $row["Num_cantidad"];

                $sql_existe = "SELECT * FROM ts_existenciaCD WHERE Cve_Articulo = '$Cve_articulo' AND Cve_lote = '$cve_lote' AND cve_ubicacion = '$AreaStagging' AND ID_Proveedor = '$ID_Proveedor'";
                if(!($res_existe = mysqli_query($conn, $sql_existe))) {echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";}
                $existe = mysqli_num_rows($res_existe);

                if($existe)
                {
                    $sql_existe = "UPDATE ts_existenciaCD SET Cantidad = Cantidad + $Num_cantidad WHERE Cve_Articulo = '$Cve_articulo' AND Cve_lote = '$cve_lote' AND cve_ubicacion = '$AreaStagging' AND ID_Proveedor = '$ID_Proveedor'";
                }
                else 
                    $sql_existe = "INSERT INTO ts_existenciaCD(Cve_Articulo, Cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) VALUES ('$Cve_articulo', '$cve_lote', $Num_cantidad, '$AreaStagging', '$ID_Proveedor')";

                if(!($res_e = mysqli_query($conn, $sql_existe))) {echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";}

            }
        }

    //    $sql = "SELECT count(status) as diff from th_subpedido where fol_folio = '".$folio."' and status != 'C'" ;
    //    if(!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";}

    /*
        $sql = "SELECT Sufijo from th_subpedido where fol_folio = '".$folio."' " ;
        if(!($res_empaque = mysqli_query($conn, $sql))) {echo "Falló la preparación(5): (" . mysqli_error($conn) . ") ";}
        
        $rows = array();
        while($row = mysqli_fetch_array($res_empaque)) 
        {
            $rows[] = $row;
        }

        foreach ($rows as $subpedido);
        {
          $sql = "CALL SPWS_EmpacaPedidoEnCajas ('".$almacen."','".$folio."','".$subpedido["Sufijo"]."')" ;
          if(!($res_empaque = mysqli_query($conn, $sql))) {echo "Falló la preparación(6): (" . mysqli_error($conn) . ") ";}
        }  
    */
        $sql = "SELECT COUNT(*) as con_tarima FROM td_pedidoxtarima WHERE Fol_folio = '{$folio}'";
        if(!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(10): (" . mysqli_error($conn) . ") ";}
        $row_ct = mysqli_fetch_array($res);
        $con_tarima = $row_ct['con_tarima'];

        if($con_tarima == 0)
        {
            $sql = "SELECT s.Cve_articulo, s.Cantidad AS Num_cantidad, IFNULL(s.LOTE, '') AS Cve_Lote, IFNULL(a.tipo_caja, 0) AS tipo_caja
                    FROM td_surtidopiezas s 
                    LEFT JOIN c_articulo a ON a.cve_articulo = s.Cve_articulo
                    WHERE s.fol_folio = '".$folio."' and s.Sufijo = '".$sufijo."' " ;

            if(!($res_empaque = mysqli_query($conn, $sql))) {echo "Falló la preparación(5): (" . mysqli_error($conn) . ") ";}

            if(mysqli_num_rows($res_empaque) == 0)
            {
                $res_empaque = "";
                $sql = "SELECT s.Cve_articulo, s.Num_Cantidad AS Num_cantidad, 
                               IFNULL(s.Cve_Lote, '') AS Cve_Lote, IFNULL(a.tipo_caja, 0) AS tipo_caja
                        FROM td_subpedido s 
                        LEFT JOIN c_articulo a ON a.cve_articulo = s.Cve_articulo
                        WHERE s.fol_folio = '".$folio."' AND s.Sufijo = '".$sufijo."'" ;

                if(!($res_empaque = mysqli_query($conn, $sql))) 
                {echo "Falló la preparación(5): (" . mysqli_error($conn) . ") ";}
            }

        $sql_guias = "SELECT COUNT(*) AS guias_t_guias FROM t_configuraciongeneral WHERE cve_conf = 'guias_en_t_guias' AND Valor = '1'";
        if(!($res_guias = mysqli_query($conn, $sql_guias))) {echo "Falló la preparación(10): (" . mysqli_error($conn) . ") ";}
        $row_guias = mysqli_fetch_array($res_guias);
        $guias_t_guias_existe = $row_guias['guias_t_guias'];

        $sql_ptl = "SELECT COUNT(*) AS usuario_ptl FROM th_subpedido WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}' AND Cve_Usuario = 'PTL'";
        if(!($res_ptl = mysqli_query($conn, $sql_ptl))) {echo "Falló la preparación(10): (" . mysqli_error($conn) . ") ";}
        $row_ptl = mysqli_fetch_array($res_ptl);
        $usuario_ptl = $row_ptl['usuario_ptl'];


        $Guia_t_guia = "";$guias_t_guias = 0;
        if($guias_t_guias_existe > 0 && $usuario_ptl > 0)
        {
            $guias_t_guias = 1;
            $sql_guias = "SELECT IFNULL(Guia, 1) as Guia FROM t_guia";
            if(!($res_guias = mysqli_query($conn, $sql_guias))) {echo "Falló la preparación(10): (" . mysqli_error($conn) . ") ";}
            $row_guias = mysqli_fetch_array($res_guias);
            $Guia_t_guia = $row_guias['Guia'];

            $sql_guias = "UPDATE t_guia SET Guia = (Guia+1) ";
            if(!($res_guias = mysqli_query($conn, $sql_guias))) {echo "Falló la preparación(10): (" . mysqli_error($conn) . ") ";}
        }

            $n_caja = 1;
            while($row = mysqli_fetch_array($res_empaque)) 
            {
                $sql = "UPDATE td_pedido set status = 'C' where Fol_folio = '".$folio."' AND Cve_articulo = '{$cve_articulo}' AND cve_lote = '{$Cve_Lote}'";
                if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(7): (" . mysqli_error($conn) . ") ";}

                $sql = "UPDATE td_subpedido set Status = 'C' where fol_folio = '".$folio."' AND Sufijo = '{$sufijo}' AND Cve_articulo = '{$cve_articulo}' AND Cve_Lote = '{$Cve_Lote}'";
                if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(7): (" . mysqli_error($conn) . ") ";}

                if($se_guardo_relacion_en_tarima == false)
                {
                    $sql = "SELECT (IFNULL(MAX(Cve_CajaMix), 0)+1) as Cve_CajaMix FROM th_cajamixta";
                    if(!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(6): (" . mysqli_error($conn) . ") ";}
                    $row_cm = mysqli_fetch_array($res);
                    $cve_cajamix = $row_cm['Cve_CajaMix'];

                    $cve_articulo = $row['Cve_articulo'];
                    $Num_cantidad = $row['Num_cantidad'];
                    $Cve_Lote = $row['Cve_Lote'];
                    $tipo_caja = $row['tipo_caja'];
                    $guia_caja = $folio.str_pad($cve_cajamix, 6, "0", STR_PAD_LEFT);
                    if($guias_t_guias == 1)
                        $guia_caja = $Guia_t_guia;


                    $sql = "INSERT INTO th_cajamixta(Cve_CajaMix, fol_folio, Sufijo, NCaja, abierta, embarcada, cve_tipocaja, Guia) 
                            VALUES ({$cve_cajamix}, '{$folio}', {$sufijo}, {$n_caja}, 'N', 'S', {$tipo_caja}, '{$guia_caja}') ";
                    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(7): (" . mysqli_error($conn). $sql . ") ";}

                    $sql = "INSERT INTO td_cajamixta (Cve_CajaMix, Cve_articulo, Cantidad, Cve_Lote, Num_Empacados) 
                            VALUES ({$cve_cajamix}, '{$cve_articulo}', {$Num_cantidad}, '{$Cve_Lote}', {$Num_cantidad})";
                    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(8): (" . mysqli_error($conn) . ") ";}

                    $n_caja++;
                }
            }
        }
        else //if($con_tarima > 0)
        {
            //$sql = "INSERT INTO t_tarima (ntarima, Fol_Folio, Sufijo, cve_articulo, lote, cantidad, Ban_Embarcado) (SELECT nTarima, Fol_folio, 1, Cve_articulo, cve_lote, Num_cantidad, 'N' FROM td_pedidoxtarima WHERE fol_folio = '{$folio}')";
            //if(!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(10): (" . mysqli_error($conn) . ") ";}

            $sql = "SELECT nTarima, Fol_folio, 1, Cve_articulo, cve_lote, Num_cantidad, 'N' FROM td_pedidoxtarima WHERE fol_folio = '{$folio}'";
            $query = mysqli_query($conn, $sql);
            $row_t = mysqli_fetch_array($query);
            extract($row_t);

            $sql = "INSERT INTO t_tarima(ntarima, Fol_Folio, Sufijo, cve_articulo, lote, cantidad, Num_Empacados, Abierta) VALUES ($nTarima, '{$Fol_folio}', 1, '$Cve_articulo', '$cve_lote', $Num_cantidad, $Num_cantidad, 'N')";
            $query = mysqli_query($conn, $sql);

            if($nTarima)
            {
                $sql = "SELECT * FROM V_ExistenciaGralProduccion WHERE Cve_Contenedor = (SELECT Clave_Contenedor FROM c_charolas WHERE IDContenedor = $nTarima)";
                $query = mysqli_query($conn, $sql);
                if(mysqli_num_rows($query) == 0)
                {
                    $sql = "UPDATE c_charolas SET Activo = 0 WHERE IDContenedor = $nTarima AND TipoGen = 1";
                    $query = mysqli_query($conn, $sql);
                }
            }


        }

    }//if($bl_salida == false)
    else 
    {
        //$folio
        //$almacen
        //$sufijo
        //$zonaembarque

        $sql = "SELECT r.Cve_articulo, r.cve_lote, r.Cantidad, th.Cve_Proveedor, u.idy_ubica AS claverp, r.cve_usuario
                FROM t_registro_surtido r
                LEFT JOIN td_entalmacen td ON td.cve_articulo = r.Cve_articulo AND td.cve_lote = r.cve_lote
                LEFT JOIN th_entalmacen th ON th.Fol_folio = td.Fol_folio
                LEFT JOIN c_ubicacion u ON u.CodigoCSD = r.claverp
                WHERE r.fol_folio = '{$folio}'";
        if(!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(10): (" . mysqli_error($conn) . ") ";}

        while($row = mysqli_fetch_array($res))
        {
            extract($row);
            $sql_traslado = "INSERT INTO ts_existenciapiezas (cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor, Cuarentena) VALUES ('$almacen', '$zonaembarque', '$Cve_articulo', '$cve_lote', '$Cantidad', '$Cve_Proveedor', 1) ON DUPLICATE KEY UPDATE Existencia = Existencia + $Cantidad";
            if(!($res_traslado = mysqli_query($conn, $sql_traslado))) {echo "Falló la preparación(14): (" . mysqli_error($conn) . ") ";}

            $sql_kardex = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso, Referencia) VALUES('$Cve_articulo', '$cve_lote', NOW(), '$claverp', '$zonaembarque', 0, $Cantidad, $Cantidad, 12, '$cve_usuario', '$almacen', 1, NOW(), '$folio')";
            if(!($res_kardex = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación(15): (" . mysqli_error($conn) . ") ";}

        }

        $sql = "UPDATE th_pedido set status = 'T' where fol_folio = '".$folio."'";
        if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(222): (" . mysqli_error($conn) . ") ";}

        $sql = "UPDATE th_subpedido set status = 'T' where fol_folio = '".$folio."'";
        if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(222): (" . mysqli_error($conn) . ") ";}
    }
/*
    $row = mysqli_fetch_array($res);
    $count = $row['diff'];
  
    if($count == 0)
    {
      $result = $ga->cambiarStatus($_POST);
    }
*/
        if(isset($_POST['separar_ola']))
        {
            //Aqui al separar cada pedido se inserta lo respectivo en td_surtidopiezas para evitar problemas con los futuros datos
            $sql = "UPDATE th_pedido set status = 'C' where Fol_folio = '".$folio."'";
            if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(7): (" . mysqli_error($conn) . ") ";}

            $sql = "UPDATE th_subpedido set Status = 'C' where fol_folio = '".$folio."'";
            if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(7): (" . mysqli_error($conn) . ") ";}


            $sql = "INSERT INTO td_surtidopiezas (fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, Num_Empacados, STATUS) (SELECT fol_folio, cve_almac, Sufijo, Cve_articulo, IFNULL(Cve_Lote, ''), Num_cantidad, 0, 0, 'S' FROM td_subpedido WHERE fol_folio = '{$folio}')";
            if(!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(11): (" . mysqli_error($conn) . ") ";}
        }

    SurtidoWEBoAPK($folio);


    echo json_encode(array("success" => true));
    exit;
}

if( $_POST['action'] == 'add' ) 
{
    $ga->Fol_folio = $_POST["Fol_folio"];
    $ga->__get("Fol_folio");
    $success = true;

    if (!empty($ga->data->Fol_folio)) {
        $success = false;
    }

    $arr = array(
        "success" => $success,
        "err" => "El Número del Folio ya se Ha Introducido"
    );

    if (!$success) {
        echo json_encode($arr);
        exit();
    }

    $ga->save($_POST);
    echo json_encode($arr);
    exit();
}

if( $_POST['action'] == 'edit' ) 
{
    $ga->actualizarPedido($_POST);
    $success = true;
    $arr = array("success" => $success);
    echo json_encode($arr);
}

if( $_POST['action'] == 'exists' ) {}

if( $_POST['action'] == 'delete' ) 
{
    $ga->borrarPedido($_POST);
    $ga->Fol_folio = $_POST["Fol_folio"];
    $ga->__get("Fol_folio");
    $arr = array("success" => true);
    echo json_encode($arr);
}

if( $_POST['action'] == 'load' ) 
{
    $ga->Fol_folio = $_POST["codigo"];
    $ga->__get("Fol_folio");
    $arr = array("success" => true,);
    echo json_encode($arr);
}

if( $_POST['action'] == 'loadArticulos' ) 
{
    $pedidos = $_POST["id_pedido"];//json_decode($_POST['id_pedido']);
    $arr = "";

    if(isset($_POST['modo']))
    {
          $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    //mysqli_set_charset($conn,"utf8mb3");

          $folio = $_POST['folio'];
//echo $folio;
           $sql = "
               SELECT IFNULL(Ship_Num, '') as folio_rel, IFNULL(TipoDoc, '') as TipoDoc FROM th_pedido WHERE Fol_folio = '{$pedidos}';
           ";
           if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
           $row = mysqli_fetch_array($res);
           $folio_rel = $row['folio_rel'];
           //****************************************************************************************************
           // SI EL PEDIDO ES TipoDoc = 'lp_ot', SIGNIFICA QUE FUÉ UN PEDIDO CREADO DESDE UNA OT
           //****************************************************************************************************
           $TipoDoc = $row['TipoDoc'];
           //****************************************************************************************************

           //****************************************************************************************************
           //AQUI VERIFICO SI LA INSTANCIA TRABAJA CON RECORRIDO SURTIDO O NO.
           //SI ID_Permiso = 2 y Id_Tipo = 2, Entonces trabaja con ruta de surtido
           //SI ID_Permiso = 2 y Id_Tipo = 3, Entonces NO trabaja con ruta de surtido
           /****************************************************************************************************
           $sql = "
               SELECT DISTINCT * FROM (SELECT IF(ID_Permiso = 2 AND Id_Tipo = 2, 1, IF(ID_Permiso = 2 AND Id_Tipo = 3, 0, -1)) AS con_recorrido FROM Rel_ModuloTipo WHERE Cve_Almac = (SELECT cve_almac FROM th_pedido where Fol_folio = '$folio')) AS cr WHERE cr.con_recorrido != -1;
           ";
           if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
           $row = mysqli_fetch_array($res);
           $con_recorrido = $row['con_recorrido'];
           //****************************************************************************************************/
           $TipoOT = $_POST['TipoOT'];
           $con_recorrido = $_POST['con_recorrido'];


        $data = $ga->loadArticulos($pedidos, $_POST["almacen"], $_POST["status"], $_POST['sufijo'], $_POST['is_backorder'], $folio_rel, $con_recorrido, $TipoDoc, $TipoOT);
        //echo $data;
        if($data[3] == 'A')
        {
            $arr = array(
              "success"   =>  true,
              "res_sp"    =>  $data[1],
              "sql"       =>  $data[2],//$pedidos,
              "status"    =>  $data[3],
              "articulos" =>  $data[0],
              "promocion" =>  $data[4],
              "observaciones" => nl2br($data[5]),
            );
        }
        else
        {
            $arr = array(
              "success"   =>  true,
              "res_sp"    =>  $data[1],
              "sql"       =>  $pedidos, 
              "query"     =>  $data[2],
              "status"    =>  $data[3],
              "tipo_pedido" =>  $data[5],
              "articulos" =>  $data[0],
              "articulos_surtidos" => $data[4],
              "promocion" =>  $data[4],
            );
        }
        echo json_encode($arr);
    }
    else
    {
            if($_POST["status"] == 'A')
            {//8
                  $datos_options = "";
                  $cve_usuarios = array();
                  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset3'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    //mysqli_set_charset($conn,"utf8mb3");

                  if((strpos($_SERVER['HTTP_HOST'], 'nikken') === false) && (($_SERVER['HTTP_HOST'] != 'dicoisa.assistprowms.com' && $_SERVER['HTTP_HOST'] != 'www.dicoisa.assistprowms.com')))
                  {//5

                    $pos = 0;
                    $sufijos = $_POST["sufijos"];

                    foreach ($pedidos as $folio) 
                    {//4
                        $sufijo = $sufijos[$pos];
                        $pos++;
                        $sql = "";
                        $subpedido_inner = "";
                        $where_select = "";
                        $subpedido_and = "rel_usuario_ruta.id_ruta = td_ruta_surtido.idr";

                       $sql = "SELECT nivel FROM th_subpedido WHERE fol_folio = '{$folio}' AND Sufijo = $sufijo";
                       if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                       $row = mysqli_fetch_array($res);
                       $nivel = $row['nivel'];

                        if($sufijo == 0 || $sufijo == '')
                        {
                          $sql = "
                              SELECT 
                                  td_pedido.fol_folio AS folio,
                                  0 as sufijo,
                                  c.RazonSocial AS cliente,
                                  td_pedido.Cve_articulo AS clave,
                                  a.des_articulo AS articulo,
                                  SUM(ROUND((a.peso * td_pedido.Num_cantidad),4)) AS peso,
                                  SUM(ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * td_pedido.Num_cantidad),4)) AS volumen,
                                  SUM(td_pedido.Num_cantidad) AS Pedido_Total,
                                  (SELECT sum(Existencia) as Existencia_Total FROM `VS_ExistenciaParaSurtido` WHERE VS_ExistenciaParaSurtido.cve_articulo = td_pedido.Cve_articulo GROUP BY cve_articulo)  as Existencia_Total
                              FROM td_pedido
                                  
                                  LEFT JOIN c_articulo a ON a.cve_articulo = td_pedido.Cve_articulo
                                  LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_pedido.Fol_folio
                                  LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
                                  LEFT JOIN td_surtidopiezas s ON s.fol_folio = td_pedido.Fol_folio AND s.Cve_articulo = td_pedido.Cve_articulo AND s.cve_almac = th_pedido.cve_almac
                                  LEFT JOIN (
                                      Select 
                                          V_ExistenciaGral.cve_articulo,
                                          c_ubicacion.CodigoCSD,
                                          min(V_ExistenciaGral.cve_ubicacion) as min 
                                      FROM V_ExistenciaGral 
                                          LEFT JOIN c_ubicacion on V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                                      GROUP by cve_articulo
                                  ) x ON x.cve_articulo = td_pedido.Cve_articulo
                              WHERE td_pedido.Fol_folio = '$folio'  GROUP BY clave
                          ";

                          //$res = mysqli_query($conn, $sql);
                          if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                          $claves_articulos = array();

                          while($val = mysqli_fetch_assoc($res))
                          {
                            $claves_articulos[] = "'".$val['clave']."'";
                          }
                          $cve_articulo = implode(",",$claves_articulos);

                          //$where_select = "WHERE cve_articulo IN ($cve_articulo) AND VS_ExistenciaParaSurtido.cve_lote = (SELECT Lote FROM c_lotes WHERE cve_articulo = VS_ExistenciaParaSurtido.cve_articulo AND Caducidad >= CURDATE())";
                          $where_select = "WHERE ((VS_ExistenciaParaSurtido.cve_articulo IN ($cve_articulo) AND IFNULL(VS_ExistenciaParaSurtido.cve_lote, '') IN (SELECT Lote FROM c_lotes WHERE cve_articulo = VS_ExistenciaParaSurtido.cve_articulo AND Caducidad >= CURDATE())) OR (VS_ExistenciaParaSurtido.cve_articulo IN ($cve_articulo) AND IFNULL(a.Caduca, 'N') = 'N'))";
                        }
                          else if($nivel > 0)
                          {//3
                            /*
                            $sql = "
                                SELECT 
                                      tds.fol_folio AS folio,
                                      tds.Sufijo AS sufijo,
                                      tds.Cve_articulo AS clave,
                                      a.des_articulo AS articulo,
                                      SUM(ROUND((a.peso * tds.Num_cantidad),4)) AS peso,
                                      SUM(ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * tds.Num_cantidad),4)) AS volumen,
                                      SUM(tds.Num_cantidad) AS Pedido_Total,
                                      (SELECT SUM(Existencia) AS Existencia_Total FROM `VS_ExistenciaParaSurtido` WHERE VS_ExistenciaParaSurtido.cve_articulo = tds.Cve_articulo GROUP BY cve_articulo)  AS Existencia_Total
                                  FROM td_subpedido tds
                                      LEFT JOIN c_articulo a ON a.cve_articulo = tds.Cve_articulo
                                      LEFT JOIN th_subpedido ths ON ths.Fol_folio = tds.Fol_folio
                                      LEFT JOIN td_surtidopiezas s ON s.fol_folio = tds.Fol_folio AND s.Cve_articulo = tds.Cve_articulo AND s.cve_almac = ths.cve_almac
                                      LEFT JOIN (
                                      SELECT 
                                          V_ExistenciaGral.cve_articulo,
                                          c_ubicacion.CodigoCSD,
                                          MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                                      FROM V_ExistenciaGral 
                                          LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                                      GROUP BY cve_articulo
                                      ) X ON X.cve_articulo = tds.Cve_articulo
                                  WHERE tds.Fol_folio = '$folio' AND tds.Sufijo = $sufijo GROUP BY Clave
                            ";
                            */
                            $subpedido_inner = "INNER JOIN th_subpedido ths ON ths.fol_folio = '$folio' AND ths.Sufijo = $sufijo";
                            $subpedido_and = "rel_usuario_ruta.id_ruta = ths.nivel";
                            $where_select = "";
                         }//3


                         if($nivel == "") $nivel = 0;

                         $almacen_id = $_POST["almacen"];

               //$sql = "SELECT LEFT(Fol_folio, 2) as tipo_folio FROM th_pedido WHERE Fol_folio = '{$folio}'";
               //if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
               //$row = mysqli_fetch_array($res);
               //$tipo_folio = $row['tipo_folio'];

                $sql_tipo_pedido = "SELECT TipoPedido FROM th_pedido where fol_folio = '$folio'";
                if (!($res_tipopedido = mysqli_query($conn, $sql_tipo_pedido)))
                    echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
                $TipoPedido = mysqli_fetch_array($res_tipopedido)['TipoPedido'];



                         if(($nivel == 0 && $sufijo > 0) || ($TipoPedido == 'R' || $TipoPedido == 'RI'))
                         {
                            $sql_surtidores = "SELECT DISTINCT Valor FROM t_configuraciongeneral WHERE cve_conf = 'perfil_solo_surtidores'";
                            if (!($res_surtidor = mysqli_query($conn, $sql_surtidores)))
                            echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
                            $perfil_surtidor = mysqli_fetch_array($res_surtidor)['Valor'];

                            $sql_surtidor = " AND v.cve_usuario NOT IN (SELECT u.cve_usuario AS cve_usuario FROM rel_usuario_ruta r LEFT JOIN c_usuario u ON u.id_user = r.id_usuario) ";
                            if($perfil_surtidor)
                                $sql_surtidor = " AND cuser.perfil = {$perfil_surtidor} ";

                            $sql = "SELECT DISTINCT v.cve_usuario AS cve_usuario, cuser.nombre_completo AS nombre_completo FROM V_PermisosUsuario v
                                    LEFT JOIN c_usuario cuser ON cuser.cve_usuario = v.cve_usuario
                                    WHERE v.ID_PERMISO = 2 {$sql_surtidor} ";
                            if($TipoPedido == 'R' || $TipoPedido == 'RI')
                            {
                                $sql = "SELECT DISTINCT v.cve_usuario AS cve_usuario, cuser.nombre_completo AS nombre_completo FROM V_PermisosUsuario v
                                    LEFT JOIN c_usuario cuser ON cuser.cve_usuario = v.cve_usuario
                                    WHERE v.ID_PERMISO = 2";
                            }
                        }
                         else 
                         {
                                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                                $sql = "SELECT DISTINCT IFNULL(Ship_Num, '') as folio_rel FROM th_pedido WHERE Fol_folio = '{$folio}'";
                                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                                $row = mysqli_fetch_array($res);
                                $folio_rel = $row['folio_rel'];

                               //***********************************************************************************************
                               //AQUI VERIFICO SI LA INSTANCIA TRABAJA CON RECORRIDO SURTIDO O NO.
                               //SI ID_Permiso = 2 y Id_Tipo = 2, Entonces trabaja con ruta de surtido
                               //SI ID_Permiso = 2 y Id_Tipo = 3, Entonces NO trabaja con ruta de surtido
                               //***********************************************************************************************
                               $sql = "
                                   SELECT DISTINCT * FROM (SELECT IF(ID_Permiso = 2 AND Id_Tipo = 2, 1, IF(ID_Permiso = 2 AND Id_Tipo = 3, 0, -1)) AS con_recorrido FROM Rel_ModuloTipo WHERE Cve_Almac = '$almacen_id') AS cr WHERE cr.con_recorrido != -1;
                               ";
                               if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                               $row = mysqli_fetch_array($res);
                               $con_recorrido = $row['con_recorrido'];
                               //***********************************************************************************************

                                if($folio_rel) //si está relacionado con alguna OT
                                {
                                  $sql = "
                                      SELECT DISTINCT 
                                          td_ruta_surtido.idr as idr, 
                                          rel_usuario_ruta.id_usuario as id_usuario, 
                                          c_usuario.cve_usuario as cve_usuario, 
                                          c_usuario.nombre_completo as nombre_completo
                                      FROM V_ExistenciaGralProduccion VS_ExistenciaParaSurtido
                                        $subpedido_inner
                                        INNER JOIN th_ruta_surtido th ON th.cve_almac = {$almacen_id} 
                                        INNER JOIN td_ruta_surtido ON td_ruta_surtido.idy_ubica = VS_ExistenciaParaSurtido.cve_ubicacion and td_ruta_surtido.idr = th.idr
                                        INNER JOIN rel_usuario_ruta ON $subpedido_and
                                        INNER JOIN c_usuario ON c_usuario.id_user = rel_usuario_ruta.id_usuario AND c_usuario.es_cliente = 0 AND c_usuario.cve_usuario != 'wmsmaster'
                                        INNER JOIN c_articulo a ON a.cve_articulo = VS_ExistenciaParaSurtido.cve_articulo
                                        INNER JOIN trel_us_alm ta ON ta.cve_usuario = c_usuario.cve_usuario 
                                        INNER JOIN c_almacenp ca ON ca.clave = ta.cve_almac AND ca.id = {$almacen_id}
                                      GROUP BY rel_usuario_ruta.id_usuario;
                                  ";
                                }
                                else 
                                {
                                  $sql = "
                                      SELECT DISTINCT 
                                          td_ruta_surtido.idr as idr, 
                                          rel_usuario_ruta.id_usuario as id_usuario, 
                                          c_usuario.cve_usuario as cve_usuario, 
                                          c_usuario.nombre_completo as nombre_completo
                                      FROM VS_ExistenciaParaSurtido
                                        $subpedido_inner
                                        INNER JOIN th_ruta_surtido th ON th.cve_almac = {$almacen_id} 
                                        INNER JOIN td_ruta_surtido ON td_ruta_surtido.idy_ubica = VS_ExistenciaParaSurtido.Idy_Ubica and td_ruta_surtido.idr = th.idr
                                        INNER JOIN rel_usuario_ruta ON $subpedido_and
                                        INNER JOIN c_usuario ON c_usuario.id_user = rel_usuario_ruta.id_usuario AND c_usuario.es_cliente = 0 AND c_usuario.cve_usuario != 'wmsmaster'
                                        INNER JOIN c_articulo a ON a.cve_articulo = VS_ExistenciaParaSurtido.cve_articulo
                                        INNER JOIN trel_us_alm ta ON ta.cve_usuario = c_usuario.cve_usuario 
                                        INNER JOIN c_almacenp ca ON ca.clave = ta.cve_almac AND ca.id = {$almacen_id}
                                      $where_select
                                      GROUP BY rel_usuario_ruta.id_usuario;
                                  ";
                                }

                                if($con_recorrido == 0)
                                {
                                    $sql_surtidores = "SELECT DISTINCT Valor FROM t_configuraciongeneral WHERE cve_conf = 'perfil_solo_surtidores'";
                                    if (!($res_surtidor = mysqli_query($conn, $sql_surtidores)))
                                    echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
                                    $perfil_surtidor = mysqli_fetch_array($res_surtidor)['Valor'];

                                    if($perfil_surtidor)
                                        $sql_surtidor = " AND c.perfil = {$perfil_surtidor} ";

                                    $sql = "SELECT
                                                c.id_user AS id_usuario,
                                                c.cve_usuario, 
                                                c.nombre_completo 
                                            FROM c_usuario c
                                            INNER JOIN trel_us_alm ta ON ta.cve_usuario = c.cve_usuario 
                                            INNER JOIN c_almacenp ca ON ca.clave = ta.cve_almac AND ca.id = {$almacen_id}
                                            , V_PermisosUsuario v
                                            WHERE v.ID_PERMISO = 2 AND c.cve_usuario = v.cve_usuario AND c.es_cliente = 0
                                            AND c.cve_usuario != 'wmsmaster' {$sql_surtidor}
                                            AND c.Activo =1";
                                }
                          }


                          $sth = \db()->prepare($sql);
                          $sth->execute();
                          $resultado_usuarios = $sth->fetchAll();

                          foreach($resultado_usuarios as $datos)
                          {//2
                               if(!in_array($datos["cve_usuario"], $cve_usuarios))
                               {//1
                                  $datos_options .= "<option value='".$datos["cve_usuario"]."'>".$datos["nombre_completo"]."</option>";
                                  array_push($cve_usuarios, $datos["cve_usuario"]);
                               }//1
                          }//2
                      }//4
                  }//5
                  else{//6
                      $sql = "
                        SELECT
                            c.id_user AS id_usuario,
                            c.cve_usuario, 
                            c.nombre_completo 
                        FROM c_usuario c, V_PermisosUsuario v
                        WHERE v.ID_PERMISO = 2 AND c.cve_usuario = v.cve_usuario
                        AND c.Activo =1";

                          $sth = \db()->prepare($sql);
                          $sth->execute();
                          $resultado_usuarios = $sth->fetchAll();

                          foreach($resultado_usuarios as $datos)
                          {//7
                              $datos_options .= "<option value='".$datos["cve_usuario"]."'>".$datos["nombre_completo"]."</option>";
                          }//7
                  }//6


                    $arr = array(
                      "success"   =>  true,
                      "sql" => $pedidos,
                      "query" => $sql,
                      "sufijos" => $sufijos,
                      "datos_options" => $datos_options
                    );
                    echo json_encode($arr);
                    /*
                            $arr = array(
                              "success"   =>  true,
                              "res_sp"    =>  $data[1],
                              "sql"       =>  $data[2],
                              "status"    =>  $data[3],
                              "articulos" =>  $data[0],
                            );
                    */
            }//8
            else
            {
                $data = $ga->loadArticulos($pedidos, $_POST["almacen"], $_POST["status"]);
                $arr = array(
                  "success"   =>  true,
                  "res_sp"    =>  $data[1],
                  "sql"       =>  $pedidos,
                  "status"    =>  $data[3],
                  "articulos" =>  $data[0],
                  "articulos_surtidos" => $data[4],
                );
                echo json_encode($arr);
            }
    }
    
}

if($_POST['action'] == 'detallesPedidoCabecera') 
{
    $data = $ga->detallesPedidoCabecera($_POST["id_pedido"],$_POST["almacen"]);

    $data2 = $ga->detallePedido($_POST["id_pedido"]);

    $arr = array(
        "success"   =>  true,
        "articulos_pedidos"    =>  $data[0][1],
        "articulos_existentes" =>  $data[0][2],
        "detalle_pedido" =>  $data2,
    );
    echo json_encode($arr);
}

if( $_POST['action'] == 'traerUsuarios' ) 
{
    $articulo = $_POST["acticulos"];
    $users = $ga->loadUsers($articulo);
    $arr = array(
        "success" => true,
        "usuarios" => $users
    );
    $arr = array_merge($arr);
    echo json_encode($arr);
}

if( $_POST['action'] == 'planificar' ) 
{
    $pedidos = $_POST['pedidos'];
    if(!empty($_POST['pedidos']))
    {
        foreach($pedidos as $folio)
        {
            $planificar = $ga->planificar($folio);
        }
    }

	  $arr = array("success" => true);
    echo json_encode($arr);
}

if( $_POST['action'] == 'VerificarSiTodosLosProductosFueronAsignados' )
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $folio        = $_POST['folio'];
    $almacen      = $_POST['almacen'];
    $cambiar_status = true;

    $sql="SELECT COUNT(*) num FROM td_subpedido WHERE fol_folio = '{$folio}' AND cve_almac = '{$almacen}'";
    $res = mysqli_multi_query($conn, $sql);
    $row = mysqli_fetch_array($res);
    $num_subpedidos = $row['num'];

    $sql="SELECT COUNT(*) num FROM td_pedido WHERE Fol_folio = '{$folio}'";
    $res = mysqli_multi_query($conn, $sql);
    $row = mysqli_fetch_array($res);
    $num_pedidos = $row['num'];


    if($num_pedidos == $num_subpedidos)
    {
        $sql="UPDATE th_pedido SET status = 'S' WHERE Fol_folio = '$folio';";
        $res = mysqli_multi_query($conn, $sql);
        @mysqli_close($conn);
    }
    else
    {
        $sql="UPDATE th_pedido SET status = 'A' WHERE Fol_folio = '$folio';";
        $res = mysqli_multi_query($conn, $sql);
        @mysqli_close($conn);
        $cambiar_status = false;
    }

    $arr = array(
        "success" => $cambiar_status
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'asignar' ) //1
{
    $sql_ejecutados = "";
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    /*
      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);
    */
$utf8Sql = "SET NAMES 'utf8mb4';";
$res_charset = mysqli_query($conn, $utf8Sql);


	$pedidos = $_POST['pedidos'];//json_decode($_POST['pedidos']);
    $almacen = $_POST['almacen'];
    $usuario = $_POST['usuarios'];
    $hora = $_POST['hora_inicio'];
    $fecha = $_POST['fecha'];
    $opcion = $_POST['opcion'];
    $dividir = $_POST['dividir'];
    $n_div = $_POST['n_div'];
    $con_recorrido = $_POST['con_recorrido'];
    $sql="SELECT clave from c_almacenp where id= {$almacen}";

    $sql_ejecutados .= $sql." ------ ";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "c_almacenp: (" . mysqli_error($conn) . ") ";
    }
    $val = mysqli_fetch_array($res)["clave"];


    $sql="SELECT COUNT(Proyecto) AS tipo_proyecto FROM td_pedido WHERE Fol_folio = '{$pedidos}' AND IFNULL(Proyecto, '') != ''";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "c_almacenp: (" . mysqli_error($conn) . ") ";
    }
    $tipo_proyecto = mysqli_fetch_array($res)["tipo_proyecto"];

    if($tipo_proyecto > 0)
    {
        @mysqli_close($conn);
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql="SELECT *, IFNULL(cve_lote, '') AS lote FROM td_pedido WHERE Fol_folio = '{$pedidos}'";

        if (!($res = mysqli_query($conn, $sql))) {
            echo "c_almacenp In_0: (" . mysqli_error($conn) . ") ".$sql;
        }

        while($row_pry = mysqli_fetch_array($res))
        {
            extract($row_pry);
            $sql_lote = "";
            if($lote != '') $sql_lote = " AND t.cve_lote = '$lote' ";

            $sql="SELECT * FROM V_ExistenciaGral WHERE cve_almac = '{$almacen}' AND cve_articulo = CONVERT('$Cve_articulo', CHAR) AND tipo = CONVERT('ubicacion', CHAR) 
                AND CONVERT(CONCAT(cve_almac, cve_ubicacion, cve_articulo, IFNULL(cve_lote, ''), Id_Proveedor, IFNULL(Cve_Contenedor, '')), CHAR) NOT IN 
                (
                SELECT DISTINCT CONVERT(CONCAT(t.cve_almac, t.idy_ubica, t.cve_articulo, IFNULL(t.cve_lote, ''), t.id_proveedor, IFNULL(ch.Clave_Contenedor, '')), CHAR)
                FROM t_trazabilidad_existencias t 
                LEFT JOIN c_charolas ch ON ch.IDContenedor = t.ntarima
                WHERE t.cve_almac = '{$almacen}' AND t.cve_articulo = CONVERT('$Cve_articulo', CHAR) {$sql_lote} AND t.idy_ubica IS NOT NULL AND t.proyecto = CONVERT('$Proyecto', CHAR)
                )";

            if (!($res_pry = mysqli_query($conn, $sql))) {
                echo "c_almacenp In_1: (" . mysqli_error($conn) . ") ".$sql;
            }

            if(mysqli_num_rows($res_pry) > 0)
            {
                $sql_insert = "INSERT IGNORE INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento)
                    (
                        SELECT cve_almac, cve_ubicacion, cve_articulo, cve_lote, Existencia, (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = V_ExistenciaGral.Cve_Contenedor AND clave_contenedor != ''), Id_Proveedor, '', '', '', '', '$Proyecto', 2 FROM V_ExistenciaGral WHERE cve_almac = '{$almacen}' AND cve_articulo = CONVERT('$Cve_articulo', CHAR) AND tipo = CONVERT('ubicacion', CHAR) 
                        AND CONVERT(CONCAT(cve_almac, cve_ubicacion, cve_articulo, cve_lote, Id_Proveedor, Cve_Contenedor), CHAR) NOT IN 
                        (
                        SELECT DISTINCT CONVERT(CONCAT(t.cve_almac, t.idy_ubica, t.cve_articulo, t.cve_lote, t.id_proveedor, ch.Clave_Contenedor), CHAR)
                        FROM t_trazabilidad_existencias t 
                        LEFT JOIN c_charolas ch ON ch.IDContenedor = t.ntarima
                        WHERE t.cve_almac = '{$almacen}' AND t.cve_articulo = CONVERT('$Cve_articulo', CHAR) {$sql_lote} AND t.idy_ubica IS NOT NULL AND t.proyecto = CONVERT('$Proyecto', CHAR)
                        )
                    )";
                if (!($res_insert = mysqli_query($conn, $sql_insert))) {
                    echo "c_almacenp In_2: (" . mysqli_error($conn) . ") ".$sql;
                }

            }
        }
    }

    @mysqli_close($conn);

/*SPWS_PreparaPedidoSurtidoTras(
Almacen varchar(50),
Folio   varchar(50),
Usuario varchar(50),
Fecha   datetime)*/

    if($dividir == 1)
    {
        //********************************************************************************
        //                              DIVISIÓN DE PEDIDOS
        //********************************************************************************
            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $utf8Sql = "SET NAMES 'utf8mb4';";
            $res_charset = mysqli_query($conn, $utf8Sql);

            $sql="SELECT COUNT(*) as n_reg FROM td_pedido WHERE Fol_folio = '{$pedidos}'";

            if (!($res = mysqli_query($conn, $sql))) {
                echo "c_almacenp: (" . mysqli_error($conn) . ") ";
            }
            $n_reg = mysqli_fetch_array($res)["n_reg"];

            //$n_partes = ceil($n_reg/$n_div);
            $n_partes = (int) ($n_reg/$n_div);
            $restante = ($n_reg%$n_div);
            $suf = 1;
            $d_inicial = 1;
            $d_final = $n_partes+$restante;

            $user_last = "";

            $totalUsuarios = count($usuario);
            $productosPorUsuarioBase = floor($n_reg / $totalUsuarios); // Calcula la cantidad base de productos por usuario
            $restoProductos = $n_reg % $totalUsuarios; // Calcula cuántos productos "sobran"

            $currentNumeroRegistro = 1; // Asumimos que NumeroRegistro comienza en 1 y es consecutivo

            @mysqli_close($conn);
//$res_th = "";$res_set = "";$res_n = "";$res_d = "";$res_sp = "";            

            foreach($usuario as $user)
            {
                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                //mysqli_free_result($res_th);
                //mysqli_next_result($res_th);
                $sql_th="INSERT INTO th_subpedido(fol_folio, cve_almac, Sufijo, Fec_Entrada, Cve_Usuario, status) VALUES ('{$pedidos}', {$almacen}, {$suf}, NOW(), '{$user}', 'A')";
                if (!($res_th = mysqli_query($conn, $sql_th))) {echo "1 th_subpedido 1: (" . mysqli_error($conn) . ") --- ".$sql_th;}

                //mysqli_free_result($res_set);
                //mysqli_next_result($res_set);
                $sql_set="SET @n := 0;";
                if (!($res_set = mysqli_query($conn, $sql_set))) {echo "2 SET 2: (" . mysqli_error($conn) . ") --- ".$sql_set;}

                $productosParaEsteUsuario = $productosPorUsuarioBase;

                // Distribuir los productos restantes a los primeros usuarios
                if ($restoProductos > 0) {
                    $productosParaEsteUsuario++;
                    $restoProductos--;
                }

                // Calcular el rango de NumeroRegistro para la consulta BETWEEN
                $d_inicial = $currentNumeroRegistro;
                $d_final = $currentNumeroRegistro + $productosParaEsteUsuario - 1;

                //mysqli_free_result($res_n);
                //mysqli_next_result($res_n);
                $sql_n = "SELECT * FROM (SELECT (@n:=@n+1) AS n, p.* FROM td_pedido p WHERE p.Fol_folio = '{$pedidos}') AS n WHERE n.n BETWEEN {$d_inicial} AND {$d_final}";
                if (!($res_n = mysqli_query($conn, $sql_n))) {echo "3 td_pedido p 3: (" . mysqli_error($conn) . ") --- ".$sql_n;}

                while($d = mysqli_fetch_array($res_n))
                {
                    $d_cve_articulo = $d["Cve_articulo"];
                    $d_num_cantidad = $d["Num_cantidad"];
                    $d_num_meses    = $d["Num_Meses"];
                    $d_cve_lote     = $d["cve_lote"];

                    //mysqli_free_result($res_d);
                    //mysqli_next_result($res_d);

                    $sql_td = "INSERT INTO td_subpedido (fol_folio, cve_almac, Sufijo, Cve_articulo, Num_cantidad, Nun_Surtida, Status, Num_Revisda, Num_Meses, Cve_Lote) VALUES('{$pedidos}', {$almacen}, {$suf}, '{$d_cve_articulo}', {$d_num_cantidad}, 0, 'A', 0, '{$d_num_meses}', '{$d_cve_lote}')";
                    if (!($res_d = mysqli_query($conn, $sql_td))) {echo "4 td_subpedido 4: (" . mysqli_error($conn) . ") ";}
                }

                //$d_inicial += $n_partes+$restante;
                //$d_final += $n_partes+$restante;
                //if($d_inicial == $n_div) $d_final = $n_reg;
                $currentNumeroRegistro = $d_final + 1;
                $suf++;
                @mysqli_close($conn);


                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


                //if($con_recorrido == 0)
                //{
                //      $sp = "SPWS_PreparaPedidoSurtDriveIn";
                //      $sql_sp =  "call ".$sp ."('".$val."','".$pedidos."','".$user."');" ;
                //}
                //else
                if($con_recorrido == 1)
                {
                    $sp = "SPWS_PreparaPedidoSurtido".((strpos($_SERVER['HTTP_HOST'], 'nikken') !== false)?"Nik_2":"");
                    $sql_sp =  "call ".$sp ."('".$val."','".$pedidos."','".$user."','".$fecha."');" ;
                    if (!($res_sp = mysqli_query($conn, $sql_sp))) {echo "SP: (" . mysqli_error($conn) . ") --- ".$sql_sp;}
                }

                if($tipo_proyecto > 0)
                {
                    $sp = "SPWS_PreparaPedidoProyecto";
                    $sql_sp =  "call ".$sp ."('".$val."','".$pedidos."','".$user."');" ;
                    if (!($res_sp = mysqli_query($conn, $sql_sp))) {echo "SP: (" . mysqli_error($conn) . ") --- ".$sql_sp;}
                }
                //mysqli_free_result($res_sp);
                //mysqli_next_result($res_sp);

                @mysqli_close($conn);

                //echo $sp;
            }
        //********************************************************************************
        //                              FIN DIVISIÓN DE PEDIDOS
        //********************************************************************************
    }

/*
}
if( $_POST['action'] == 'asignar24' ) //1
{
*/
    $sp = "SPWS_PreparaPedidoSurtido".((strpos($_SERVER['HTTP_HOST'], 'nikken') !== false)?"Nik_2":"");


    //if(strpos($_SERVER['HTTP_HOST'], 'nikken') == false)
    //{
        //if(substr($pedidos, 0, 2) == 'TR')

   // }
/*
    $sql_recorrido = "SELECT DISTINCT * FROM (SELECT IF(ID_Permiso = 2 AND Id_Tipo = 2, 1, IF(ID_Permiso = 2 AND Id_Tipo = 3, 0, -1)) AS con_recorrido FROM Rel_ModuloTipo WHERE Cve_Almac = '$almacen') AS cr WHERE cr.con_recorrido != -1";
    if (!($res_recorrido = mysqli_query($conn, $sql_recorrido)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $con_recorrido = mysqli_fetch_array($res_recorrido)['con_recorrido'];
*/
    //if($_SERVER['HTTP_HOST'] == 'dicoisa.assistprowms.com' || $_SERVER['HTTP_HOST'] == 'www.dicoisa.assistprowms.com')
    if($con_recorrido == 0)
    {
          $sp = "SPWS_PreparaPedidoSurtDriveIn";
    }
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_tipo_pedido = "SELECT TipoPedido FROM th_pedido where fol_folio = '$pedidos'";
    if (!($res_tipopedido = mysqli_query($conn, $sql_tipo_pedido)))
        echo "Falló la preparación TipoPedido: (" . mysqli_error($conn) . ") ";
    $TipoPedido = mysqli_fetch_array($res_tipopedido)['TipoPedido'];
        if($TipoPedido == 'R' || $TipoPedido == 'RI')
          $sp = "SPWS_PreparaPedidoSurtidoTras";

    $sql_ejecutados .= $sp." ------ ";
    $debug="";
    $call_sp_used = "";
        $val_sp = 0;
        if($pedidos && $dividir == 0 && $tipo_proyecto <= 0)
        {
            $sql = "";
            $folios = $pedidos;
            $folios_sin_existencia = "";
            $folio = $pedidos;
            
                    //*********************************************************************************************
                    //                                          BACK ORDER 
                    //*********************************************************************************************
                    $sql = "SELECT * FROM td_pedido WHERE Fol_folio = '$pedidos'";
                    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);
*/
                    if (!($res = mysqli_query($conn, $sql))) {
                        //$sql_ejecutados .= " DELETE2: (" . mysqli_error($conn) . ") ";
                        echo " Existencia_Total: (" . mysqli_error($conn) . ") ";
                    }
                    $sql_ejecutados .= $sql." ------ ";
                    $listo_folio_backorder = false;
                    $folio_backorder = "";
                    $cve_articulo_ready = "";
                    while($row = mysqli_fetch_array($res))
                    {
                        $cve_articulo = $row['Cve_articulo'];
                        $cantidad = $row['Num_cantidad'];
                        $cve_lote = $row['cve_lote'];
                        $id = $row['id'];

                        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

                        $sql = "SELECT IFNULL(Ship_Num, '') as folio_rel, IFNULL(TipoDoc, '') as TipoDoc FROM th_pedido WHERE Fol_folio = '{$folio}'";
                        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                        $row = mysqli_fetch_array($res);
                        $folio_rel = $row['folio_rel'];
                        $TipoDoc = $row['TipoDoc'];

            if($TipoDoc != 'tipo_lp')
            {
                        if($folio_rel)
                        {
                            $sql = "SELECT IFNULL(SUM(Existencia), 0) AS Existencia_Total 
                                    FROM V_ExistenciaGralProduccion VS_ExistenciaParaSurtido 
                                    INNER JOIN c_articulo a ON a.cve_articulo = VS_ExistenciaParaSurtido.cve_articulo
                                    WHERE VS_ExistenciaParaSurtido.cve_articulo = '$cve_articulo' AND VS_ExistenciaParaSurtido.cve_almac = '$almacen' AND (VS_ExistenciaParaSurtido.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = VS_ExistenciaParaSurtido.cve_articulo AND Caducidad >= CURDATE()) OR (VS_ExistenciaParaSurtido.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))";//GROUP BY cve_articulo
                        }
                        else
                        {
                            $sql = "SET NAMES 'UTF8MB4'";
                            $res_v = mysqli_query($conn, $sql);
                            $sqlTraslado = "VS_ExistenciaParaSurtido";
                            if($TipoPedido == 'R' || $TipoPedido == 'RI')
                                $sqlTraslado = "V_ExistenciaGralProduccion";
                            $sql = "SELECT IFNULL(SUM(Existencia), 0) AS Existencia_Total 
                                    FROM {$sqlTraslado} v
                                    INNER JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
                                    WHERE v.cve_articulo = '$cve_articulo' AND v.cve_almac = '$almacen' AND (v.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = v.cve_articulo AND Caducidad >= CURDATE()) OR (v.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))";//GROUP BY cve_articulo
                        }
                        $sql_ejecutados .= $sql." ------ ";

                        if (!($res_vs = mysqli_query($conn, $sql))) {
                            //$sql_ejecutados .= " DELETE2: (" . mysqli_error($conn) . ") ";
                            echo " Existencia_Total: (" . mysqli_error($conn) . ") --  ". $sql;
                        }
                        $existencia = mysqli_fetch_array($res_vs)["Existencia_Total"];
                        $cantidad_bo = $cantidad - $existencia;
                        $num_rows = mysqli_num_rows($res_vs);
                        if(!$existencia || $num_rows == 0 || $cantidad_bo > 0)
                        {

                            $sql_bo = "SELECT COUNT(Folio_BackO) as fol_exist FROM th_backorder WHERE Fol_Folio = '$pedidos'";
                            if (!($res_vs_bo = mysqli_query($conn, $sql_bo))) {echo " Existencia_Total: (" . mysqli_error($conn) . ") ";}
                            $folio_exist = mysqli_fetch_array($res_vs_bo)["fol_exist"];

                            $status_bo = 'A';
                            if($opcion == "ajustar")
                                $status_bo = 'K';

                            if(!$listo_folio_backorder && !$folio_exist)
                            {
                                $folio_backorder = $ga->consecutivo_folio_backorder();
                                $sql = "SELECT Cve_Clte, Fec_Entrega, Pick_Num FROM th_pedido WHERE Fol_folio = '$pedidos'";
                                $sql_ejecutados .= $sql." ------ ";
                                $res_pedido = mysqli_query($conn, $sql);
                                $row_pedido = mysqli_fetch_array($res_pedido);
                                $Cve_clte = $row_pedido["Cve_Clte"];
                                $Fec_Entrega = $row_pedido["Fec_Entrega"];
                                $Pick_Num = $row_pedido["Pick_Num"];
                                $fecha_actual = $ga->fecha_actual();

                                $sql = "INSERT INTO th_backorder(Folio_BackO, Fol_Folio, Cve_Clte, Fec_Pedido, Fec_Entrega, Fec_BO, Pick_num, Status) VALUES ('$folio_backorder', '$pedidos', '$Cve_clte', '$fecha', '$Fec_Entrega', '$fecha_actual', '$Pick_Num', '$status_bo') ";
                                $sql_ejecutados .= $sql." ------ ";
                                $res_th = mysqli_query($conn, $sql);
                                $listo_folio_backorder = true;
                            }

                            if($cve_articulo != $cve_articulo_ready)
                            {
                                $sql = "INSERT INTO td_backorder(Folio_BackO, Cve_Articulo, Cve_Lote, Cantidad_Pedido, Cantidad_BO, Status) VALUES ('$folio_backorder', '$cve_articulo', '$cve_lote', '$cantidad', '$cantidad_bo', '$status_bo')";
                                $sql_ejecutados .= $sql." ------ ";
                                $res_td = mysqli_query($conn, $sql);
                                $cve_articulo_ready = $cve_articulo;
                            }
                        }

                        if($opcion == "ajustar")
                        {
                            $sql = "DELETE FROM td_pedido WHERE id = $id";
                            if($existencia > 0)
                                $sql = "UPDATE td_pedido SET Num_cantidad = $cantidad WHERE id = $id";
                            $res = mysqli_query($conn, $sql);
                        }
            }//TipoDoc
                    }
                    //*********************************************************************************************

            if($TipoDoc != 'tipo_lp')
            {
                    @mysqli_close($conn);

            //$res = mysqli_multi_query($conn, $sql);
            $sql_ejecutados .= $sql." ------ ";

            $sql =  "call ".$sp ."('".$val."','".$folios."','".$usuario."','".$fecha."');" ;
            //if($_SERVER['HTTP_HOST'] == 'dicoisa.assistprowms.com' || $_SERVER['HTTP_HOST'] == 'www.dicoisa.assistprowms.com')

            if(($con_recorrido == 0 || $sp == 'SPWS_PreparaPedidoSurtDriveIn') && $sp != 'SPWS_PreparaPedidoSurtidoTras')
            $sql =  "call ".$sp ."('".$val."','".$folios."','".$usuario."');" ;

/*
            }
*/
            }

            if($TipoDoc == 'tipo_lp')
            {
                $sql = "INSERT INTO t_recorrido_surtido (idy_ubica, cve_almac, cve_pasillo, cve_rack, Seccion, cve_nivel, Ubicacion, orden_secuencia, fol_folio, Sufijo, Cve_articulo, cve_usuario, picking, claverp, ClaveEtiqueta, cve_lote, Cantidad) 
                (
                SELECT DISTINCT u.idy_ubica, u.cve_almac, u.cve_pasillo, u.cve_rack, u.Seccion, u.cve_nivel, u.Ubicacion, u.orden_secuencia, tt.Fol_folio, 1 AS Sufijo, 
                       tt.Cve_articulo, '{$usuario}' AS cve_usuario, 'S' AS picking, u.CodigoCSD AS claverp, ch.IDContenedor AS ClaveEtiqueta, tt.cve_lote, tt.Num_cantidad AS Cantidad
                FROM td_pedidoxtarima tt 
                LEFT JOIN ts_existenciatarima et ON et.ntarima = tt.nTarima 
                LEFT JOIN c_ubicacion u ON u.idy_ubica = et.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.nTarima
                WHERE tt.Fol_folio = '{$folio}'
                )";
                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                $sql = "INSERT INTO t_registro_surtido (idy_ubica, cve_almac, cve_pasillo, cve_rack, Seccion, cve_nivel, Ubicacion, orden_secuencia, fol_folio, Sufijo, Cve_articulo, cve_usuario, picking, claverp, ClaveEtiqueta, cve_lote, Cantidad) 
                (
                SELECT DISTINCT u.idy_ubica, u.cve_almac, u.cve_pasillo, u.cve_rack, u.Seccion, u.cve_nivel, u.Ubicacion, u.orden_secuencia, tt.Fol_folio, 1 AS Sufijo, 
                       tt.Cve_articulo, '{$usuario}' AS cve_usuario, 'S' AS picking, u.CodigoCSD AS claverp, ch.IDContenedor AS ClaveEtiqueta, tt.cve_lote, tt.Num_cantidad AS Cantidad
                FROM td_pedidoxtarima tt 
                LEFT JOIN ts_existenciatarima et ON et.ntarima = tt.nTarima 
                LEFT JOIN c_ubicacion u ON u.idy_ubica = et.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.nTarima
                WHERE tt.Fol_folio = '{$folio}'
                )";
                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                $sql="UPDATE th_pedido SET status = 'S', Activo = 0 WHERE Fol_folio = '$folio';";
                $res = mysqli_multi_query($conn, $sql);

                $sql="INSERT INTO th_subpedido (fol_folio, cve_almac, Sufijo, Fec_Entrada, Cve_Usuario, status) 
                    (
                    SELECT Fol_folio, cve_almac, 1, NOW(), '{$usuario}', 'S' FROM th_pedido WHERE Fol_folio = '{$folio}'
                    )";
                $res = mysqli_multi_query($conn, $sql);

                $sql="INSERT IGNORE INTO td_subpedido (fol_folio, cve_almac, Sufijo, Cve_articulo, Num_Cantidad, Nun_Surtida, Status, Num_Revisda, Cve_Lote) 
                (
                SELECT Fol_folio, (SELECT cve_almac FROM th_pedido WHERE Fol_folio = '{$folio}') AS cve_almac, 1 AS Sufijo, Cve_articulo, Num_cantidad, 0, 'A', 0, cve_lote FROM td_pedido WHERE Fol_folio = '{$folio}'
                )";
                $res = mysqli_multi_query($conn, $sql);

                //$row = mysqli_fetch_array($res);
                //$folio_rel = $row['folio_rel'];
                //$TipoDoc = $row['TipoDoc'];
            }
        }
        
            if($tipo_proyecto > 0 && $dividir == 0)
            {
                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                $sp = "SPWS_PreparaPedidoProyecto";
                $sql =  "CALL SPWS_PreparaPedidoProyecto(".$almacen.",'".$pedidos."','".$usuario."');";
                if (!($res = mysqli_query($conn, $sql))) {
                    //$sql_ejecutados .=  "CALL: (" . mysqli_error($conn) . ") ";
                    echo "CALL: (" . mysqli_error($conn) . ") ";
                }


                //@mysqli_close($conn);
                //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                //$sql="UPDATE th_pedido SET status = 'S', Activo = 0 WHERE Fol_folio = '$folio';UPDATE th_subpedido SET status = 'S' WHERE Fol_folio = '$folio';";
                //$res = mysqli_multi_query($conn, $sql);
                //$sql="UPDATE th_subpedido SET status = 'S' WHERE Fol_folio = '$folio';";
                //$res = mysqli_multi_query($conn, $sql);

            }
            else if($dividir == 0)
            {


            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      //$sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
      $sql_charset = "SET NAMES 'UTF8MB4';";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    //$charset = mysqli_fetch_array($res_charset)['charset'];
    //mysqli_set_charset($conn , $charset);

//$utf8Sql = "SET NAMES 'utf8mb4';";
//$res_charset = mysqli_query($conn, $utf8Sql);

            //$res = mysqli_query($conn, $sql);
            if (!($res = mysqli_query($conn, $sql))) {
                //$sql_ejecutados .=  "CALL: (" . mysqli_error($conn) . ") ";
                echo "CALL: (" . mysqli_error($conn) . ") ";
            }
            //$val_sp = mysqli_fetch_array($res)["Error"];
            //@mysqli_close($conn);
            }
            $call_sp_used = $sql;
            $sql_ejecutados .= $sql." ------ ";

            $folio = $pedidos;
/*
            if($num_pedidos == $num_subpedidos || $num_pedidos == $num_subpedidos1)
            {
*/
            //$sql="";
            //$res = mysqli_query($conn, $sql);

//INSERT IGNORE INTO t_registro_surtido (idy_ubica, cve_almac, cve_pasillo, cve_rack, Seccion, cve_nivel, Ubicacion, orden_secuencia, fol_folio, Sufijo, Cve_articulo, cve_usuario, picking, claverp, ClaveEtiqueta, cve_lote, Cantidad) (SELECT idy_ubica, cve_almac, cve_pasillo, cve_rack, Seccion, cve_nivel, Ubicacion, orden_secuencia, fol_folio, Sufijo, Cve_articulo, cve_usuario, picking, claverp, ClaveEtiqueta, cve_lote, Cantidad FROM t_recorrido_surtido WHERE fol_folio = '$folio');
                $sql="UPDATE th_pedido SET status = 'S', Activo = 0 WHERE Fol_folio = '$folio';";
                $res = mysqli_multi_query($conn, $sql);
                $sql="UPDATE th_subpedido SET status = 'S' WHERE Fol_folio = '$folio';";
                $res = mysqli_multi_query($conn, $sql);

                $sql_ejecutados .= $sql;
                @mysqli_close($conn);

    $arr = array(
        "success" => true,
        "resp" => $res,
        "debug"=>$debug,
        "cambiar_status" => $cambiar_status,
        "sp" => $sp,
        "val_sp" => $val_sp,
        "call_sp_used" => $call_sp_used,
        "sql_ejecutados" => $sql_ejecutados,
        "sin_existencia" => $folios_sin_existencia
    );

/*
    $arr = array(
        "pedidos" => $pedidos,
        "almacen"=>$almacen,
        "usuario"=>$usuario,
        "hora"=>$hora,
        "fecha"=>$fecha,
        "sql_ejecutados" => $sql_ejecutados
    );
*/
    echo json_encode($arr);
    //exit;
}

if( $_POST['action'] == 'cargarOla' ) 
{
	  $pedidos = $_POST['pedidos'];
    $pesoTotal = 0;
 
    $numeroOla = $ga->loadNumeroOla()[0];
    $numeroOla = 'WS'.'0'.$numeroOla;

	  if(!empty($_POST['pedidos']))
    {
		    $folios="'".$_POST['pedidos'][0]."'";
        foreach($pedidos as $folio)
        {
			      $folios = $folios.",'".$folio."'";
			      $pesoTotal += $ga->getPesoPedido($folio);
        }
		    $data = $ga->loadArticulosWave($folios);
    }

    $response = [
        "success" => true,
        "articulos" => $data,
        "numeroOla" => $numeroOla,
        "pesoOla" => $pesoTotal,
        "pedidos" =>  $pedidos
    ];
    echo json_encode($response);
    exit;
}

if( $_POST['action'] == 'planificarOla' ) 
{
    $pedidos = explode(',', $_POST['pedidos']);

    if(!empty($_POST['pedidos']))
    {
        foreach($pedidos as $folio){
            $data=$ga->guardarSubpedido($_POST, $folio);
        }
    }
    $arr = array("success" => true);
    echo json_encode($arr);
}

if( $_POST['action'] == 'planificarSubPedido' ) 
{

    $pedidos = explode(',', $_POST['pedidos']);

    if(!empty($_POST['pedidos']))
    {
        foreach($pedidos as $folio)
        {
            $data=$ga->guardarSubpedidoTD($_POST, $folio);
        }
    }
    $arr = array("success" => true);
    echo json_encode($arr);
}

if( $_POST['action'] == 'guardarAreaEmbarque' ) 
{
    $resp = $ga->saveAE($_POST);
    $arr = array("success" => true);
    echo json_encode($arr);
}

if(!empty($_POST) && $_POST['action'] === 'cargarSurtido')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $folio = $_POST['folio'];
    $totalFolio = count($folio) - 1;
    $folios = '';
    foreach($folio as $key => $value)
    {
        $folios .= "'{$value}'";
        if($key !== $totalFolio)
        {
            $folios .= ',';
        }
    }

    $start = $limit*$page - $limit;
    if(!$sidx) $sidx =1;
    $sqlCount = "SELECT COUNT(id) AS cuenta FROM `td_pedido` WHERE Fol_folio IN ({$folios})";
    if (!($res = mysqli_query($conn, $sqlCount))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    $sql = "
        SELECT
            p.Fol_folio AS folio,
            '--' AS familia,
            p.Cve_articulo AS clave,
            IFNULL(a.des_articulo, '--') AS articulo,
            IFNULL(p.cve_lote, '--') AS lote,
            (SELECT cve_ubicacion FROM th_pedido WHERE Fol_folio = p.Fol_folio) AS ubicacion,
            IFNULL(p.Num_cantidad * p.SurtidoXCajas, 0) AS existencias,
            '0' AS pedidas,
            IFNULL(p.Num_cantidad, 0) AS cajas
        FROM td_pedido p
            LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo
        WHERE p.Fol_folio IN ({$folios})
        ORDER BY p.Fol_folio, p.Cve_articulo
        LIMIT $start, $limit;
    ";

    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    if( $count >0 ) 
    {
        $total_pages = ceil($count/$limit);
    } 
    else 
    {
        $total_pages = 0;
    } 
    if ($page > $total_pages)
    {
        $page=$total_pages;
    }

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id']=$i;
        $responce->rows[$i]['cell']=array($row['familia'],$row['clave'],$row['articulo'],$row['lote'],$row['ubicacion'], $row['existencias'], $row['pedidas'], $row['cajas']);
        $i++;
    }
    echo json_encode($responce);
}

if(!empty($_POST) && $_POST['action'] === 'crearConsolidadoDeOla')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $page   = $_POST['page']; // get the requested page
    $limit  = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx   = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord   = $_POST['sord']; // get the direction
    $folio = $_POST['folios'];
    $totalFolio = count($folio) - 1;
    $folios = '';
    
    foreach($folio as $key => $value)
    {
        $folios .= "'{$value}'";
        if($key !== $totalFolio)
        {
            $folios .= ',';
        }
    }
    $start = $limit*$page - $limit;
    if(!$sidx) $sidx =1;

    $sqlCount = "SELECT COUNT(Cve_Articulo) AS cuenta FROM `td_consolidado` WHERE Fol_PedidoCon IN ({$folios})";
    if (!($res = mysqli_query($conn, $sqlCount))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];
  
    $sql = "
        SELECT
            item.Fol_folio AS folio,
            art.Cve_Articulo AS clave,
            IFNULL(art.des_articulo, '--') AS articulo,
            IFNULL(SUM(item.Num_cantidad), 0) AS pedidas,
            0 AS surtidas
        FROM td_pedido item
            LEFT JOIN c_articulo art ON art.cve_articulo = item.Cve_articulo
        WHERE item.Fol_folio IN ({$folios})
        GROUP BY art.Cve_Articulo
        ORDER BY item.Fol_folio, item.itemPos;
    ";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    if($count >0 ) 
    {
        $total_pages = ceil($count/$limit);
    } 
    else 
    {
        $total_pages = 0;
    } 
    if($page > $total_pages)
    {
        $page=$total_pages;
    }
    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id']=$i;
        $responce->rows[$i]['cell']=array($row['clave'],$row['articulo'],$row['pedidas'],$row['surtidas']);
        $i++;
    }
    echo json_encode($responce);
}

if(!empty($_POST) && $_POST['action'] === 'cargarConsolidado')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $folio = $_POST['folio'];
    $totalFolio = count($folio) - 1;
    $folios = '';
    
    foreach($folio as $key => $value)
    {
        $folios .= "'{$value}'";
        if($key !== $totalFolio)
        {
            $folios .= ',';
        }
    }
    $start = $limit*$page - $limit;

    if(!$sidx) $sidx =1;

    $sqlCount = "SELECT COUNT(Cve_Articulo) AS cuenta FROM `td_consolidado` WHERE Fol_PedidoCon IN ({$folios})";

    if (!($res = mysqli_query($conn, $sqlCount))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    $sql = "
        SELECT
            item.Fol_folio AS folio,
            art.Cve_Articulo AS clave,
            IFNULL(art.des_articulo, '--') AS articulo,
            IFNULL(SUM(item.Num_cantidad), 0) AS pedidas,
            0 AS surtidas
        FROM td_pedido item
            LEFT JOIN c_articulo art ON art.cve_articulo = item.Cve_articulo
        WHERE item.Fol_folio IN ({$folios})
        GROUP BY art.Cve_Articulo
        ORDER BY item.Fol_folio, item.itemPos;
    ";

    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    if( $count >0 ) 
    {
        $total_pages = ceil($count/$limit);
    } 
    else 
    {
        $total_pages = 0;
    } 
    if ($page > $total_pages)
    {
        $page=$total_pages;
    }
    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id']=$i;
        $responce->rows[$i]['cell']=array($row['clave'],$row['articulo'],$row['pedidas'],$row['surtidas']);
        $i++;
    }
    echo json_encode($responce);
}

if($_POST['action'] === 'BorrarPedido') 
{
    extract($_POST);
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "DELETE FROM Rel_PedidoDest WHERE Fol_Folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_surtidopiezas WHERE fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_subpedido WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM th_subpedido WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_pedido WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM th_pedido WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_pedidoxtarima WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "UPDATE Venta SET Cancelada = 1 WHERE Documento = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "SELECT COUNT(*) as existe FROM DetalleCob WHERE Documento = '{$folio}'";
    $query = mysqli_query($conn, $sql);
    $si_hay_abonos = mysqli_fetch_array($query)["existe"];

    if(!$si_hay_abonos)
    {
        $sql = "DELETE FROM DetalleCob WHERE Documento = '{$folio}'";
        $query = mysqli_query($conn, $sql);

        $sql = "DELETE FROM Cobranza WHERE Documento = '{$folio}'";
        $query = mysqli_query($conn, $sql);
    }

/*
    $sql = "DELETE FROM th_subpedido WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_subpedido WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM th_ordenembarque WHERE ID_OEmbarque = (SELECT ID_OEmbarque FROM td_ordenembarque WHERE Fol_folio = '{$folio}')";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_ordenembarque WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);
*/
    echo json_encode(array("success" => $query));
}

if($_POST['action'] === 'BorrarOT') 
{
    extract($_POST);
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "DELETE FROM t_ordenprod WHERE Folio_Pro = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_ordenprod WHERE Folio_Pro = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM th_pedido WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_pedido WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM th_subpedido WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_subpedido WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    echo json_encode(array("success" => $query));
}


if($_POST['action'] === 'cambiarPriodidad') 
{
    extract($_POST);
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "UPDATE th_pedido SET ID_Tipoprioridad = '$prioridad' WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    echo json_encode(array("success" => $query));
}


if($_POST['action'] === 'guardarsurtido') 
{
    extract($_POST);
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "
        INSERT INTO td_subpedido (fol_folio, cve_almac, Sufijo, Cve_articulo, Num_cantidad, Nun_Surtida, ManejaCajas, Status, Num_Revisda, Num_Meses, Autorizado, ManejaPiezas) 
        VALUES ('$folio', (SELECT cve_almac FROM th_pedido WHERE Fol_folio = '$folio'), 1, '$cve_articulo', '$pedidas', '$surtidas', 'N', 'A', '0', (SELECT Num_Meses FROM td_pedido WHERE Fol_folio = '$folio' AND Cve_articulo = '$cve_articulo'),NULL, 'S'); 
        INSERT INTO td_surtidopiezas (fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, status, empacado, embarcado) 
        VALUES ('$folio', (SELECT cve_almac FROM th_pedido WHERE Fol_folio = '$folio'), 1, '$cve_articulo', '$lote', '$surtidas', '0', 'A', 'N', 'N');
    ";
    $query = mysqli_multi_query($conn, $sql);

    SurtidoWEBoAPK($folio);

    echo json_encode(array("success" => $query));
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'getDataExcel') 
{
    $ga->generateExcel($_POST);
}


if($_POST['action'] === 'semaforo')//1
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);
*/
$utf8Sql = "SET NAMES 'utf8mb4';";
$res_charset = mysqli_query($conn, $utf8Sql);



    $almacen = $_POST['almacen'];
    $folio = $_POST["folio"];
    $con_recorrido = $_POST["con_recorrido"];
    //$sufijo = $_POST["sufijo"];
    


    $sql_tipo_pedido = "SELECT TipoPedido FROM th_pedido where fol_folio = '$folio'";
    if (!($res_tipopedido = mysqli_query($conn, $sql_tipo_pedido)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $TipoPedido = mysqli_fetch_array($res_tipopedido)['TipoPedido'];
/*
    $confSql = \db()->prepare("SELECT DISTINCT * FROM (SELECT IF(ID_Permiso = 2 AND Id_Tipo = 2, 1, IF(ID_Permiso = 2 AND Id_Tipo = 3, 0, -1)) AS con_recorrido FROM Rel_ModuloTipo WHERE Cve_Almac = '$id_almacen') AS cr WHERE cr.con_recorrido != -1");
    $confSql->execute();
    $row_recorrido = $confSql->fetch();
    $con_recorrido = $row_recorrido['con_recorrido'];
*/
    $sql_verificar = "V_ExistenciaGral";
    $and_sql_verificar = " AND vs.tipo = 'ubicacion' ";
    
    $union_traslado = "";
    if($TipoPedido == 'R' || $TipoPedido == 'RI')
    {
        $union_traslado = "        
        UNION 

        SELECT  vs.cve_articulo, 
                #IFNULL(vs.cve_lote, '') AS cve_lote, 
                '' AS cve_lote, 
                vs.Existencia AS Existencia 
        FROM td_pedido td
        INNER JOIN V_ExistenciaGralProduccion vs ON vs.cve_articulo = td.Cve_articulo  AND vs.tipo = 'ubicacion' 
        INNER JOIN c_ubicacion u ON u.idy_ubica = vs.cve_ubicacion AND u.Picking = 'S'
        WHERE vs.Cve_Almac = '$almacen' AND vs.Cuarentena = 0
        AND td.Fol_folio = '$folio' AND IFNULL(td.cve_lote, '') = ''
        GROUP BY cve_articulo#, cve_lote";
    }

    $vista = 'VS_ExistenciaParaSurtido';
    $v_ubica = 'vs.idy_ubica';
    if($con_recorrido == 0)
    {
        $vista = 'V_ExistenciaGralProduccion';
        $v_ubica = 'vs.cve_ubicacion';
    }

    if(isset($_POST['ot_produccion'])) 
    {
        $and_sql_verificar = "";
        $sql_verificar = "V_ExistenciaProduccion";
    }

      $sql_recorrido = "SELECT DISTINCT * FROM (SELECT IF(ID_Permiso = 2 AND Id_Tipo = 2, 1, IF(ID_Permiso = 2 AND Id_Tipo = 3, 0, -1)) AS con_recorrido FROM Rel_ModuloTipo WHERE Cve_Almac = '$almacen') AS cr WHERE cr.con_recorrido != -1";
    if (!($res_recorrido = mysqli_query($conn, $sql_recorrido)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $con_recorrido = mysqli_fetch_array($res_recorrido)['con_recorrido'];

    $sqlRecorrido = "";$sqlRecorrido2 = "";
    if($con_recorrido == 1)
    {
        $sqlRecorrido = " INNER JOIN td_ruta_surtido rs ON rs.idy_ubica = {$v_ubica} AND rs.Activo = 1 ";
        $sqlRecorrido2 = " INNER JOIN td_ruta_surtido rs ON rs.idy_ubica = vs.cve_ubicacion AND rs.Activo = 1 ";
    }

    $sql = "SELECT COUNT(*) AS num_productos_disponibles FROM (
        SELECT vs.cve_articulo, vs.cve_lote, SUM(vs.Existencia) AS Existencia 
        FROM {$vista} vs
        {$sqlRecorrido}
        INNER JOIN c_lotes lo ON lo.cve_articulo = vs.cve_articulo AND lo.Lote = vs.cve_lote AND DATE_FORMAT(lo.Caducidad, '%Y-%m-%d') >= CURDATE() #AND lo.Caducidad != DATE_FORMAT('0000-00-00', '%Y-%m-%d')
        WHERE CONCAT(IFNULL(vs.cve_articulo, ''), IFNULL(vs.cve_lote, '')) IN (SELECT CONCAT(IFNULL(cve_articulo, ''), IFNULL(cve_lote, '')) FROM td_pedido WHERE fol_folio = '$folio') 
        AND vs.Cve_Almac = '$almacen' AND vs.Existencia > 0
        GROUP BY cve_articulo, cve_lote

        UNION 

        SELECT vs.cve_articulo, vs.cve_lote, SUM(vs.Existencia) AS Existencia 
        FROM {$vista} vs
        {$sqlRecorrido}
        WHERE CONCAT(IFNULL(vs.cve_articulo, ''), IFNULL(vs.cve_lote, '')) IN (SELECT CONCAT(IFNULL(cve_articulo, ''), IFNULL(cve_lote, '')) FROM td_pedido WHERE fol_folio = '$folio') 
        AND vs.Cve_Almac = '$almacen' AND IFNULL(vs.cve_lote, '') = '' AND vs.Existencia > 0
        GROUP BY cve_articulo, cve_lote

        UNION 

        SELECT vs.cve_articulo, vs.cve_lote, SUM(vs.Existencia) AS Existencia 
        FROM {$vista} vs
         {$sqlRecorrido}
        WHERE CONCAT(IFNULL(vs.cve_articulo, ''), IFNULL(vs.cve_lote, '')) IN (SELECT CONCAT(IFNULL(cve_articulo, ''), IFNULL(cve_lote, '')) FROM td_pedido WHERE fol_folio = '$folio') 
        AND vs.Cve_Almac = '$almacen' AND IFNULL(vs.cve_lote, '') != '' AND vs.Existencia > 0
        GROUP BY cve_articulo, cve_lote

        UNION 

        SELECT vs.cve_articulo, vs.cve_lote, SUM(vs.Existencia) AS Existencia 
        FROM {$vista} vs
        {$sqlRecorrido}
        INNER JOIN c_serie s ON s.cve_articulo = vs.cve_articulo AND s.numero_serie = vs.cve_lote 
        WHERE CONCAT(IFNULL(vs.cve_articulo, ''), IFNULL(vs.cve_lote, '')) IN (SELECT CONCAT(IFNULL(cve_articulo, ''), IFNULL(cve_lote, '')) FROM td_pedido WHERE fol_folio = '$folio') 
        AND vs.Cve_Almac = '$almacen' AND vs.Existencia > 0
        GROUP BY cve_articulo, cve_lote

        UNION 

        SELECT  vs.cve_articulo, 
                #IFNULL(vs.cve_lote, '') AS cve_lote, 
                '' AS cve_lote, 
                vs.Existencia AS Existencia 
        FROM td_pedido td
        INNER JOIN {$sql_verificar} vs ON vs.cve_articulo = td.Cve_articulo {$and_sql_verificar}
        INNER JOIN c_ubicacion u ON u.idy_ubica = vs.cve_ubicacion AND u.Picking = 'S'
        {$sqlRecorrido2}
        WHERE IFNULL(td.cve_articulo, '') NOT IN (SELECT IFNULL(cve_articulo, '') FROM {$vista} WHERE Cve_Almac = '$almacen' AND cve_articulo = td.Cve_articulo  AND IFNULL(cve_lote, '') = '') 
        AND vs.Cve_Almac = '$almacen' AND vs.Cuarentena = 0
        AND td.Fol_folio = '$folio' AND IFNULL(td.cve_lote, '') = ''
        GROUP BY cve_articulo#, cve_lote

        {$union_traslado}

        ) AS disp
        ";
$sql_disp = $sql;
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ".$sql;}
    $row_count = mysqli_fetch_array($res);

    $semaforo = "rojo";
    $productoNoSurtible = " AND IFNULL(a.tipo_producto, '') != 'ProductoNoSurtible' ";
    if($TipoPedido == 'RI' || $TipoPedido == 'R')
        $productoNoSurtible = "";
    $sql_dispXarticulo = "";
    $num_productos_disponibles = $row_count["num_productos_disponibles"];
    if($num_productos_disponibles > 0)
    {
        $sql_pedido = "SELECT 
                            p.Cve_articulo, p.cve_lote, 
                            IF(u.mav_cveunimed = 'XBX', (p.Num_cantidad*a.num_multiplo), p.Num_cantidad) AS Num_cantidad
                        FROM td_pedido p
                        INNER JOIN c_articulo a ON p.Cve_articulo = a.cve_articulo {$productoNoSurtible} 
                        LEFT JOIN c_unimed u ON u.id_umed = IFNULL(p.id_unimed,a.unidadMedida)
                        WHERE p.Fol_folio = '$folio'";
        if (!($res_pedido = mysqli_query($conn, $sql_pedido))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ".$sql_pedido;}

        $num_productos_pedido = mysqli_num_rows($res_pedido);
        if($num_productos_disponibles != $num_productos_pedido)
        {
            $semaforo = "amarillo";
        }
        else 
        {
            while($row_pedido = mysqli_fetch_array($res_pedido))
            {
                extract($row_pedido);
                $sql = "SELECT * FROM (
                    SELECT vs.cve_articulo, vs.cve_lote, SUM(vs.Existencia) AS Existencia 
                    FROM {$vista} vs
                    {$sqlRecorrido}
                    INNER JOIN c_lotes lo ON lo.cve_articulo = vs.cve_articulo AND lo.Lote = vs.cve_lote AND DATE_FORMAT(lo.Caducidad, '%Y-%m-%d') >= CURDATE() #AND lo.Caducidad != DATE_FORMAT('0000-00-00', '%Y-%m-%d')
                    WHERE CONCAT(IFNULL(vs.cve_articulo, ''), IFNULL(vs.cve_lote, '')) IN (SELECT CONCAT(IFNULL(cve_articulo, ''), IFNULL(cve_lote, '')) FROM td_pedido WHERE fol_folio = '$folio') 
                    AND vs.Cve_Almac = '$almacen' AND vs.Existencia > 0
                    GROUP BY cve_articulo, cve_lote

                    UNION 

                    SELECT vs.cve_articulo, vs.cve_lote, SUM(vs.Existencia) AS Existencia 
                    FROM {$vista} vs
                    {$sqlRecorrido}
                    WHERE CONCAT(IFNULL(vs.cve_articulo, ''), IFNULL(vs.cve_lote, '')) IN (SELECT CONCAT(IFNULL(cve_articulo, ''), IFNULL(cve_lote, '')) FROM td_pedido WHERE fol_folio = '$folio') 
                    AND vs.Cve_Almac = '$almacen' AND IFNULL(vs.cve_lote, '') = '' AND vs.Existencia > 0
                    GROUP BY cve_articulo, cve_lote

                    UNION 

                    SELECT vs.cve_articulo, vs.cve_lote, SUM(vs.Existencia) AS Existencia 
                    FROM {$vista} vs
                    {$sqlRecorrido}
                    WHERE CONCAT(IFNULL(vs.cve_articulo, ''), IFNULL(vs.cve_lote, '')) IN (SELECT CONCAT(IFNULL(cve_articulo, ''), IFNULL(cve_lote, '')) FROM td_pedido WHERE fol_folio = '$folio') 
                    AND vs.Cve_Almac = '$almacen' AND IFNULL(vs.cve_lote, '') != '' AND vs.Existencia > 0
                    GROUP BY cve_articulo, cve_lote

                    UNION 

                    SELECT vs.cve_articulo, vs.cve_lote, SUM(vs.Existencia) AS Existencia 
                    FROM {$vista} vs
                    {$sqlRecorrido}
                    INNER JOIN c_serie s ON s.cve_articulo = vs.cve_articulo AND s.numero_serie = vs.cve_lote 
                    WHERE CONCAT(IFNULL(vs.cve_articulo, ''), IFNULL(vs.cve_lote, '')) IN (SELECT CONCAT(IFNULL(cve_articulo, ''), IFNULL(cve_lote, '')) FROM td_pedido WHERE fol_folio = '$folio') 
                    AND vs.Cve_Almac = '$almacen' AND vs.Existencia > 0
                    GROUP BY cve_articulo, cve_lote

                    UNION 

                    SELECT vs.cve_articulo, IFNULL(vs.cve_lote, '') AS cve_lote, SUM(vs.Existencia) AS Existencia 
                    FROM td_pedido td
                    INNER JOIN V_ExistenciaGral vs ON vs.cve_articulo = td.Cve_articulo AND vs.tipo = 'ubicacion'
                    INNER JOIN c_ubicacion u ON u.idy_ubica = vs.cve_ubicacion AND u.Picking = 'S'
                    {$sqlRecorrido2}
                    WHERE IFNULL(td.cve_articulo, '') NOT IN (SELECT IFNULL(cve_articulo, '') FROM {$vista} WHERE Cve_Almac = '$almacen' AND cve_articulo = td.Cve_articulo  AND IFNULL(cve_lote, '') = '') 
                    AND vs.Cve_Almac = '$almacen' AND vs.Cuarentena = 0
                    AND td.Fol_folio = '$folio' AND IFNULL(td.cve_lote, '') = ''
                    GROUP BY cve_articulo, cve_lote

                    {$union_traslado}

                    ) AS disp WHERE disp.cve_articulo = '$Cve_articulo' #AND IFNULL(disp.cve_lote, '') = '$cve_lote'
                ";
                $sql_dispXarticulo .= $sql.";
                ";

                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ".$sql;}

                if(mysqli_num_rows($res) > 0)
                {
                    $row = mysqli_fetch_array($res);
                    extract($row);

                    if($Existencia >= $Num_cantidad) $semaforo = "verde";
                    else $semaforo = "amarillo";
                }
            }
        }
    }
  
    echo json_encode(array(
        "success" => true,
        "num_productos_disponibles" => $num_productos_disponibles,
        "num_productos_pedido" => $num_productos_pedido,
        "sql_disp" => $sql_disp,
        "sql_dispXarticulo" => $sql_dispXarticulo,
        "idfolio" => $folio,
        "color" => $semaforo
        //"sql"=>$sql,
        //"disponible" => $row["disponible"],
        //"cantidad" => $row["cantidad"]
    ));
}

if($_POST['action'] == 'getDetalleEdicion' ) 
{
  $folio = $_POST['folio'];
  // se conecta a la base de datos
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  //id, cve_articulo, cve_lote, num_pedimento, fecha_pedimento
  $sql = "SELECT id, Cve_articulo, cve_lote, Num_cantidad, id_unimed from td_pedido where Fol_folio = '$folio'";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }

  $datos = "";
  while($row = mysqli_fetch_array($res))
  {
    $id = $row['id'];
    $cve_articulo = $row['Cve_articulo'];
    $cve_lote = $row['cve_lote'];
    $Num_cantidad = $row['Num_cantidad'];
    $id_unimed = $row['id_unimed'];

    $datos .= '<div class="row">
                  <input type="hidden" class="id_edicion datos_edicion" value="'.$id.'">
                  <input type="hidden" class="unidad_medida_ed datos_edicion" value="'.$id_unimed.'">
                  <div class="col-md-3">
                  <input type="text" class="form-control cve_articulo_ed datos_edicion" readonly value="'.$cve_articulo.'">
                  </div>
                  <div class="col-md-3">
                  <input type="text" class="form-control cve_lote_ed datos_edicion" readonly value="'.$cve_lote.'">
                  </div>
                  <div class="col-md-3">
                  <input type="text" class="form-control cantidad_ed datos_edicion" placeholder="Cantidad..." value="'.$Num_cantidad.'">
                  </div>
                  <div class="col-md-3">
                  <input type="checkbox" class="eliminar_ed datos_edicion" value="'.$id.'">
                  </div>
              </div>
              <br>';
  }


  $arr = array();

  $arr = ["datos"=>$datos];
  echo json_encode($arr);
  //echo $datos;

}

if($_POST['action'] == 'EditarPedido')
{
  $folio = $_POST['folio'];
  $arr_id_edicion = $_POST['arr_id_edicion'];
  $arr_unidad_medida_ed = $_POST['arr_unidad_medida_ed'];
  $arr_cve_articulo_ed = $_POST['arr_cve_articulo_ed'];
  $arr_cve_lote_ed = $_POST['arr_cve_lote_ed'];
  $arr_cantidad_ed = $_POST['arr_cantidad_ed'];
  $arr_eliminar_ed = $_POST['arr_eliminar_ed'];
  // se conecta a la base de datos
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  for($i = 0; $i < count($arr_cve_articulo_ed); $i++)
  {
      if($arr_eliminar_ed[$i] == '1' && $arr_id_edicion[$i] != 'add')
      {
          $sql = "DELETE FROM td_pedido WHERE id = ".$arr_id_edicion[$i];
          if (!($res = mysqli_query($conn, $sql))) 
          {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
      }
      else if($arr_id_edicion[$i] != 'add')
      {
          $sql = "UPDATE td_pedido SET Cve_articulo = '".$arr_cve_articulo_ed[$i]."', cve_lote = '".$arr_cve_lote_ed[$i]."', Num_cantidad = '".$arr_cantidad_ed[$i]."' WHERE id = ".$arr_id_edicion[$i];
          if (!($res = mysqli_query($conn, $sql))) 
          {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
      }
      else if($arr_id_edicion[$i] == 'add' && $arr_eliminar_ed[$i] == '0')
      {
          $sql = "INSERT INTO td_pedido (Fol_folio, Cve_articulo, cve_lote, Num_cantidad, id_unimed, Num_Meses, status, Num_revisadas, Precio_unitario, Desc_Importe, IVA) VALUES ('".$folio."', '".$arr_cve_articulo_ed[$i]."', '".$arr_cve_lote_ed[$i]."', '".$arr_cantidad_ed[$i]."', '".$arr_unidad_medida_ed[$i]."', 0, 'A', 0, 0.000, 0.000, 0.000)";
          if (!($res = mysqli_query($conn, $sql))) 
          {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
      }

  }

  echo 1;
}

if($_POST['action'] == 'ConectarSAP' ) 
{
  $endPoint = '';
  $json = '';
  
  $funcion  = $_POST['funcion'];
  $metodo   = $_POST['metodo'];
  $folio_oc = $_POST['folio_oc'];
  
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//***********************************************************************************************************
  $sql = "SELECT * FROM c_datos_sap WHERE Empresa = (SELECT cve_proveedor FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = '{$folio_oc}')) AND Activo = 1;";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
  }
  $row = mysqli_fetch_array($res);
  $endPoint = $row['Url'].$funcion;
  $usuario  = $row['User'];
  $password = $row['Pswd'];
  $BD       = $row['BaseD'];

    $json = '{
    "CompanyDB": "'.$BD.'",
    "UserName": "'.$usuario.'",
    "Password": "'.$password.'"
    }';
//echo $json;

    $curl = curl_init();

    curl_setopt_array($curl, array(

  CURLOPT_URL => $endPoint,

  CURLOPT_RETURNTRANSFER => true,

  CURLOPT_ENCODING => '',

  CURLOPT_MAXREDIRS => 10,

  CURLOPT_TIMEOUT => 0,

  CURLOPT_FOLLOWLOCATION => true,

  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

  CURLOPT_CUSTOMREQUEST => $metodo,
 //rie
  CURLOPT_POSTFIELDS => $json,

  CURLOPT_HTTPHEADER => array(

    'Content-Type: text/plain',

    'Cookie: B1SESSION=e148fc02-6d94-11ec-8000-0a244a1700f3; ROUTEID=.node2'

  ),

  CURLOPT_SSL_VERIFYHOST => false,
  
  CURLOPT_SSL_VERIFYPEER => false,

));

$response = curl_exec($curl);

 curl_close($curl);

  echo ($response);
}

if($_POST['action'] == 'EjecutarOVSap' ) 
{
  $folio   = $_POST['folio'];
  $sufijo  = $_POST['sufijo'];
  $funcion = $_POST['funcion'];
  $metodo  = $_POST['metodo'];

  $json = '{';

  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
  $sql = "SELECT * FROM c_datos_sap WHERE Empresa = (SELECT cve_proveedor FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = '{$folio_oc}')) AND Activo = 1;";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
  }
  $row = mysqli_fetch_array($res);
  $endPoint = $row['Url'].$funcion;
*/

//***********************************************************************************************************
  if($_SERVER['HTTP_HOST'] == 'dicoisa.assistprowms.com' || $_SERVER['HTTP_HOST'] == 'www.dicoisa.assistprowms.com')
    $sql = "SELECT DATE_FORMAT(Fec_Entrada, '%Y-%m-%d') AS Fec_Entrada FROM th_pedido WHERE fol_folio = '{$folio}';";
  else
    $sql = "SELECT DATE_FORMAT(Fec_Entrada, '%Y-%m-%d') AS Fec_Entrada FROM th_subpedido WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';";

  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
  }
  $row = mysqli_fetch_array($res);
  $Fec_Entrada = $row['Fec_Entrada'];

  $sql = "SELECT Cve_Clte, Docto_Ref, Almac_Ori FROM th_pedido WHERE Fol_folio = '{$folio}';";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
  }
  $row = mysqli_fetch_array($res);
  $Cve_Clte  = $row['Cve_Clte'];
  $Docto_Ref = $row['Docto_Ref'];
  $Almac_Ori = $row['Almac_Ori'];

$json .= '"DocDate":"'.$Fec_Entrada.'","DocDueDate":"'.$Fec_Entrada.'", "CardCode":"'.$Cve_Clte.'", "DocType":"dDocument_Items",';//"DocObjectCode":"20" ,
$json .= '"DocumentLines":[';
//***********************************************************************************************************
  //$sql = "SELECT cve_articulo, SUM(CantidadRecibida) AS CantidadRecibida FROM td_entalmacen WHERE fol_folio = '{$fol_folio}' GROUP BY cve_articulo;";
  if($_SERVER['HTTP_HOST'] == 'dicoisa.assistprowms.com' || $_SERVER['HTTP_HOST'] == 'www.dicoisa.assistprowms.com')
        $sql = "SELECT DISTINCT ts.Cve_articulo, SUM(ts.Cantidad) AS Cantidad, tp.ItemPos
                FROM td_surtidopiezas ts 
                INNER JOIN td_pedido tp ON tp.Fol_folio = ts.fol_folio AND tp.Fol_folio = '{$folio}' AND ts.Cve_articulo = tp.Cve_articulo
                WHERE ts.fol_folio = '{$folio}'
                GROUP BY Cve_articulo;";
    else
        $sql = "SELECT DISTINCT ts.Cve_articulo, SUM(ts.Cantidad) AS Cantidad, tp.ItemPos
                FROM td_surtidopiezas ts 
                INNER JOIN td_pedido tp ON tp.Fol_folio = ts.fol_folio AND tp.Fol_folio = '{$folio}' AND ts.Sufijo = '{$sufijo}' AND ts.Cve_articulo = tp.Cve_articulo
                WHERE ts.fol_folio = '{$folio}'
                GROUP BY Cve_articulo;";

  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ";
  }
  //$i = 1;
  while($row = mysqli_fetch_array($res))
  {
    $Cve_articulo = $row['Cve_articulo'];
    $Cantidad     = $row['Cantidad'];
    $ItemPos      = $row['ItemPos'];

    $json .= '{';

    $json .= '"BaseEntry":"'.$Docto_Ref.'","BaseType":"17","BaseLine":"'.$ItemPos.'","ItemCode":"'.$Cve_articulo.'","Quantity":"'.$Cantidad.'","WarehouseCode":"'.$Almac_Ori.'", 
    "BatchNumbers":[';
    $i++;
//***********************************************************************************************************

  if($_SERVER['HTTP_HOST'] == 'dicoisa.assistprowms.com' || $_SERVER['HTTP_HOST'] == 'www.dicoisa.assistprowms.com')
    $sql = "SELECT DISTINCT ts.Cve_articulo, ts.Cantidad, ts.LOTE, cl.Caducidad
            FROM td_surtidopiezas ts 
            LEFT JOIN c_lotes cl ON cl.cve_articulo = ts.Cve_articulo AND cl.Lote = ts.LOTE
            WHERE ts.fol_folio = '{$folio}' AND ts.Cve_articulo = '{$Cve_articulo}'";
    else
    $sql = "SELECT DISTINCT ts.Cve_articulo, ts.Cantidad, ts.LOTE, cl.Caducidad
            FROM td_surtidopiezas ts 
            LEFT JOIN c_lotes cl ON cl.cve_articulo = ts.Cve_articulo AND cl.Lote = ts.LOTE
            WHERE ts.fol_folio = '{$folio}' AND ts.Sufijo = '{$sufijo}' AND ts.Cve_articulo = '{$Cve_articulo}'";

    if (!($res2 = mysqli_query($conn, $sql))) 
    {
      echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
    }
    while($row2 = mysqli_fetch_array($res2))
    {
      $cve_lote  = $row2['LOTE'];
      $Cantidad  = $row2['Cantidad'];
      $Caducidad = $row2['Caducidad'];
      $json .= '{"BatchNumber":"'.$cve_lote.'","Quantity":"'.$Cantidad.'","ExpiryDate":"'.$Caducidad.'"},';

      //**************************************************************************************
      //  DESHABILITAR AL MOSTRAR JSON
      /**************************************************************************************
      $sql_update_sap = "UPDATE td_entalmacen_enviaSAP SET Enviado = 1 WHERE Id = '{$Id}';";
      if (!($res_sap = mysqli_query($conn, $sql_update_sap))) 
      {
        echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
      }
      //**************************************************************************************/

    }
//***********************************************************************************************************
    $json[strlen($json)-1] = ' ';
    $json .= ']},';
  }
//***********************************************************************************************************
$json[strlen($json)-1] = ' ';
$json .= ']}';

echo json_encode($json);
//echo $json;

//****************************************************************************************
/****************************************************************************************

  $sesion_id = $_POST['sesion_id'];
    $curl = curl_init();

    curl_setopt_array($curl, array(

  CURLOPT_URL => $endPoint,

  CURLOPT_RETURNTRANSFER => true,

  CURLOPT_ENCODING => '',

  CURLOPT_MAXREDIRS => 10,

  CURLOPT_TIMEOUT => 0,

  CURLOPT_FOLLOWLOCATION => true,

  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

  CURLOPT_CUSTOMREQUEST => $metodo,

  CURLOPT_POSTFIELDS =>$json,

  CURLOPT_HTTPHEADER => array(

    'Content-Type: text/plain',

    'Cookie: B1SESSION='.$sesion_id.'; ROUTEID=.node2'

  ),

  CURLOPT_SSL_VERIFYHOST => false,
  
  CURLOPT_SSL_VERIFYPEER => false,

));
//'Content-Type: text/plain',
//e148fc02-6d94-11ec-8000-0a244a1700f3
//application/json
$response = curl_exec($curl);

 curl_close($curl);

  echo $response;
//****************************************************************************************/
//****************************************************************************************
}

function consecutivo_folio_reabasto() 
{
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $sql = "SELECT IF(MONTH(CURRENT_DATE()) < 10, CONCAT(0, MONTH(CURRENT_DATE())), MONTH(CURRENT_DATE())) AS mes, YEAR(CURRENT_DATE()) AS _year FROM DUAL";

  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }
  $fecha = mysqli_fetch_array($res);

  $mes  = $fecha['mes'];
  $year = $fecha['_year'];


  $count = 1;
  while(true)
  {
      if($count < 10)
        $count = "0".$count;

      $folio_next = "RB".$year.$mes.$count;
      $sql = "SELECT COUNT(*) as Consecutivo FROM th_pedido WHERE Fol_Folio = '$folio_next'";

      if (!($res = mysqli_query($conn, $sql))) 
      {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
      }
      $data = mysqli_fetch_array($res);

      if($data["Consecutivo"] == 0)
        break;
      else
      {
          $count += 0; //convirtiendo a entero
          $count++;
      }
  }

  return $folio_next;
}

if($_POST['action'] == 'FinalizarReabasto' ) 
{
    $folio = $_POST['folio'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "UPDATE ts_ubicxart SET folio = '' WHERE folio = '{$folio}'";
    if (!($res2 = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $sql = "UPDATE th_pedido SET status = 'T' WHERE fol_folio = '{$folio}'";
    if (!($res2 = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $sql = "UPDATE th_subpedido SET status = 'T' WHERE fol_folio = '{$folio}'";
    if (!($res2 = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    echo 1;
}

if($_POST['action'] == 'VerificarReabasto' ) 
{
  $almacen = $_POST['almacen'];
  // se conecta a la base de datos
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  //id, cve_articulo, cve_lote, num_pedimento, fecha_pedimento
  $sql = "SELECT * FROM (
            SELECT DISTINCT
                ve.cve_articulo AS clave,
                tu.idy_ubica,
                #IF((SUM(ve.Existencia) <= IFNULL(tu.CapacidadMinima, 0)), (IFNULL(tu.CapacidadMaxima, 0)-SUM(ve.Existencia)), 0) AS reabasto,
                IF((TRUNCATE((SUM(ve.Existencia)/IFNULL(a.num_multiplo, 1)), 0) <= ROUND(((IFNULL(a.cajas_palet, 0))*0.25), 0)), (IFNULL(a.cajas_palet, 0)-TRUNCATE((SUM(ve.Existencia)/IFNULL(a.num_multiplo, 1)), 0)), 0) AS reabasto,
                IFNULL(tu.folio, '') AS folio
            FROM    V_ExistenciaGral ve
            INNER JOIN c_articulo a ON a.cve_articulo = ve.cve_articulo
            INNER JOIN ts_ubicxart tu ON tu.cve_articulo = ve.cve_articulo AND tu.idy_ubica = ve.cve_ubicacion
            INNER JOIN c_ubicacion u ON u.idy_ubica = ve.cve_ubicacion AND u.picking = 'S'
            INNER JOIN c_almacen z ON z.cve_almac = u.cve_almac
            INNER JOIN c_almacenp ze ON ze.id = z.cve_almacenp AND ze.id = '{$almacen}' 
            GROUP BY u.CodigoCSD, ve.cve_articulo
            ) AS r WHERE r.reabasto > 0 AND r.folio = ''";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }

  $datos = "";
  if(mysqli_num_rows($res) > 0)
  {
    $datos = "1";
      while($row = mysqli_fetch_array($res))
      {
            extract($row);

            $folio = consecutivo_folio_reabasto();
            $sql = "INSERT INTO th_pedido(Fol_folio, Fec_Pedido, status, Fec_Entrega, ID_Tipoprioridad, Fec_Entrada, cve_almac) VALUES ('{$folio}', CURDATE(), 'A', CURDATE(), 1, CURDATE(), {$almacen})";
            if (!($res2 = mysqli_query($conn, $sql))) 
            {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

            $sql = "INSERT INTO td_pedido(Fol_folio, Cve_articulo, Num_cantidad, id_unimed, status) VALUES ('{$folio}', '{$clave}', {$reabasto}, (SELECT id_umed FROM c_unimed WHERE mav_cveunimed = 'XBX'), 'A')";
            if (!($res2 = mysqli_query($conn, $sql))) 
            {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

            $sql = "UPDATE ts_ubicxart SET folio = '{$folio}' WHERE cve_articulo = '{$clave}' AND idy_ubica = '{$idy_ubica}'";
            if (!($res2 = mysqli_query($conn, $sql))) 
            {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }
      }
  }

  $arr = array();

  $arr = ["datos"=>$datos];
  echo json_encode($arr);
  //echo $datos;

}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'ExcelResumenPedidosArticulos')
{
    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $almacen            = $_POST['almacen'];
    $criterio           = $_POST['criterio'];
    $tipopedido         = $_POST['tipopedido'];
    $status             = $_POST['status'];
    $ruta_pedido_list   = $_POST['ruta_pedido_list'];
    $ciudad_pedido_list = $_POST['ciudad_pedido_list'];
    $fechaInicio        = $_POST['fechaInicio'];
    $fechaFin           = $_POST['fechaFin'];

    $title              = "Resumen General Pedidos por Articulos.xlsx";

    $sql_status = "";
    if($status)
        $sql_status = " AND th.status = '{$status}' ";

      $sql_ruta = "";
      if($ruta_pedido != '')
      {
          $ruta_arr = explode(";;;;;", $ruta_pedido);
          $ruta1 = $ruta_arr[0];
          $ruta2 = "";
          if(count($ruta_arr) > 1)
            $ruta2 = $ruta_arr[1];
          if($ruta1 != "")
          {
              $sql_ruta = " AND (th.ruta = '$ruta1' OR th.cve_ubicacion = '$ruta1') ";
          }

          if($ruta2 != "")
          {
              $sql_ruta = " AND (th.ruta IN ('$ruta1', '$ruta2') OR th.cve_ubicacion IN ('$ruta1', '$ruta2')) ";
          }
      }


      $sql_fecha = "";

      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql_fecha = " AND DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        //STR_TO_DATE('$fecha', '%d-%m-%Y')
        //$sql_fecha = " AND DATE_FORMAT(IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada), '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        $sql_fecha = " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) BETWEEN STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') AND STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";

      }
      elseif (!empty($fecha_inicio)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$sql_fecha .= " AND DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        $sql_fecha .= " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) >= STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') ";
      }
      elseif (!empty($fecha_fin)) 
      {
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql_fecha .= " AND DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        $sql_fecha .= " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) <= STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";
      }

      $sql_search = "";
      if (!empty($search) ) 
      {
            $sql_search = " AND (th.Fol_folio like '%$search%' OR th.Pick_Num like '%$search%' OR th.Fol_folio IN (SELECT Fol_folio FROM td_pedido WHERE Cve_articulo LIKE '%$search%')) ";
      }

      $sql_tipo_pedido1 = "";
      if($tipopedido != "")
      {
          //$sql_tipo_pedido1 = " AND IF(LEFT(th.Fol_folio, 2) = 'OT', 1, IF(LEFT(th.Fol_folio, 2) = 'TR', 2, IF(LEFT(th.Fol_folio, 2) = 'WS', 4, IF(LEFT(th.Fol_folio, 1) = 'S' AND IFNULL(th.cve_ubicacion,'') != '', 5, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) != 'R' AND LEFT(th.Fol_folio, 1) != 'P', 3, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) = 'R', 6, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) = 'P', 7, 0))))))) = '{$tipopedido}' ";
            $sql_tipo_pedido1 = " AND th.TipoPedido = '{$tipopedido}' ";
      }
      else
      {
          $sql_tipo_pedido1 = " AND IFNULL(o.ruta, '') = '' AND IFNULL(o.cve_ubicacion, '') = '' ";  //AND o.Fol_folio LIKE 'S%'
      }

    $sql = "SELECT DISTINCT DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') as Fec_Pedido, DATE_FORMAT(th.Fec_Entrega, '%d/%m/%Y') as Fec_Entrega, r.cve_ruta, c.Cve_Clte, c.RazonSocial, th.status, ce.DESCRIPCION as estatus, ce.ORDEN, th.Fol_folio, 
    td.Cve_Articulo,
    a.des_articulo,
    td.Num_Cantidad,
CASE 
WHEN th.TipoPedido = 'T' THEN 'Orden de Trabajo'
WHEN th.TipoPedido = 'R' THEN 'Traslado Externo'
WHEN th.TipoPedido = 'W' THEN 'Ola'
WHEN th.TipoPedido = 'W2' THEN 'Ola de Olas'
WHEN th.TipoPedido = 'X' THEN 'Cross Docking'
WHEN th.TipoPedido = 'RI' THEN 'Traslado Interno'
ELSE 'Pedido General'
END AS TipoPedido

FROM td_pedido td
LEFT JOIN th_pedido th ON td.Fol_Folio = th.Fol_Folio
LEFT JOIN c_articulo a ON td.Cve_Articulo = a.cve_articulo
LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN cat_estados ce ON ce.ESTADO = th.status
WHERE th.cve_almac = {$almacen} 
{$sql_tipo_pedido1} 
{$sql_fecha} 
{$sql_search} 
{$sql_ruta} 
{$sql_status} 
AND th.TipoPedido = '{$tipopedido}'

ORDER BY th.Fol_folio
#ORDER BY ce.ORDEN
#ORDER BY th.TipoPedido
";

        $query = mysqli_query(\db2(), $sql);
        //$datos = mysqli_fetch_array($query, MYSQLI_ASSOC);
        $rows = array();
        while(($row = mysqli_fetch_array($query, MYSQLI_ASSOC))) 
        {
            $rows[] = $row;
            $datos = $row;
        }

        //$cuadro7 = array('LP', 'PARTIDA','CONCEPTO','CANTIDAD','U.M.','P.U.','SUBTOTAL');
        $cuadro7 = array('Fecha Pedido', 'Fecha Entrega', 'Ruta', 'Pedido', 'Clave Cliente', 'Razón Social', 'Tipo Pedido', 'Artículo', 'Descripción', 'Cantidad');

        $excel = new XLSXWriter();
        $excel->writeSheetRow($title, $cuadro7 );
        foreach($rows as $row)
        {
            //$row = array($row["LP"], $row["cve_articulo"],$row["des_articulo"], number_format($row["cantidad"], 2),$row["umas"], $row["costo"],$row["subtotal"]);
            $row = array($row["Fec_Pedido"], $row["Fec_Entrega"], $row["cve_ruta"], $row["Fol_folio"], $row["Cve_Clte"], $row["RazonSocial"], $row["TipoPedido"], $row["Cve_Articulo"], $row["des_articulo"], $row["Num_Cantidad"]);
            $excel->writeSheetRow($title, $row );
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $title . '"');
        header('Cache-Control: max-age=0');
        $excel->writeToStdOut($title);
}


if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'generarExcelPedidos')
{
    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $almacen            = $_POST['almacen'];
    $criterio           = $_POST['criterio'];
    $tipopedido         = $_POST['tipopedido'];
    $status             = $_POST['status'];
    $ruta_pedido_list   = $_POST['ruta_pedido_list'];
    $ciudad_pedido_list = $_POST['ciudad_pedido_list'];
    $fechaInicio        = $_POST['fechaInicio'];
    $fechaFin           = $_POST['fechaFin'];

    $title              = "Resumen General de Pedidos.xlsx";

      $sql_ruta = "";
      if($ruta_pedido != '')
      {
          $ruta_arr = explode(";;;;;", $ruta_pedido);
          $ruta1 = $ruta_arr[0];
          $ruta2 = "";
          if(count($ruta_arr) > 1)
            $ruta2 = $ruta_arr[1];
          if($ruta1 != "")
          {
              $sql_ruta = " AND (th.ruta = '$ruta1' OR th.cve_ubicacion = '$ruta1') ";
          }

          if($ruta2 != "")
          {
              $sql_ruta = " AND (th.ruta IN ('$ruta1', '$ruta2') OR th.cve_ubicacion IN ('$ruta1', '$ruta2')) ";
          }
      }


      $sql_fecha = "";

      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql_fecha = " AND DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        //STR_TO_DATE('$fecha', '%d-%m-%Y')
        //$sql_fecha = " AND DATE_FORMAT(IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada), '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        $sql_fecha = " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) BETWEEN STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') AND STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";

      }
      elseif (!empty($fecha_inicio)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$sql_fecha .= " AND DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        $sql_fecha .= " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) >= STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') ";
      }
      elseif (!empty($fecha_fin)) 
      {
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql_fecha .= " AND DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        $sql_fecha .= " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) <= STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";
      }

      $sql_search = "";
      if (!empty($search) ) 
      {
            $sql_search = " AND (th.Fol_folio like '%$search%' OR th.Pick_Num like '%$search%' OR th.Fol_folio IN (SELECT Fol_folio FROM td_pedido WHERE Cve_articulo LIKE '%$search%')) ";
      }

      $sql_tipo_pedido1 = "";
      if($tipopedido != "")
      {
          //$sql_tipo_pedido1 = " AND IF(LEFT(th.Fol_folio, 2) = 'OT', 1, IF(LEFT(th.Fol_folio, 2) = 'TR', 2, IF(LEFT(th.Fol_folio, 2) = 'WS', 4, IF(LEFT(th.Fol_folio, 1) = 'S' AND IFNULL(th.cve_ubicacion,'') != '', 5, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) != 'R' AND LEFT(th.Fol_folio, 1) != 'P', 3, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) = 'R', 6, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) = 'P', 7, 0))))))) = '{$tipopedido}' ";
            $sql_tipo_pedido1 = " AND th.TipoPedido = '{$tipopedido}' ";
      }
      else
      {
          $sql_tipo_pedido1 = " AND IFNULL(o.ruta, '') = '' AND IFNULL(o.cve_ubicacion, '') = '' ";  //AND o.Fol_folio LIKE 'S%'
      }

    //$status = 'A';

    $sql = "SELECT DISTINCT DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') as Fec_Pedido, DATE_FORMAT(th.Fec_Entrega, '%d/%m/%Y') as Fec_Entrega, r.cve_ruta, c.Cve_Clte, c.RazonSocial, th.status, ce.DESCRIPCION as estatus, ce.ORDEN, th.Fol_folio, 
CASE 
WHEN th.TipoPedido = 'T' THEN 'Orden de Trabajo'
WHEN th.TipoPedido = 'R' THEN 'Traslado Externo'
WHEN th.TipoPedido = 'W' THEN 'Ola'
WHEN th.TipoPedido = 'W2' THEN 'Ola de Olas'
WHEN th.TipoPedido = 'X' THEN 'Cross Docking'
WHEN th.TipoPedido = 'RI' THEN 'Traslado Interno'
ELSE 'Pedido General'
END AS TipoPedido, 
th.Observaciones,
IFNULL(SUM(tds.Cantidad), '') as surtidas,
ce.DESCRIPCION as status,
p.Descripcion as prioridad,
IFNULL(th.Tot_Factura, '') as total_factura,
IFNULL(DATE_FORMAT(th.Fec_Aprobado, '%d-%m-%Y | %H:%i:%s'), '') as fecha_aprobacion,
IF(ths.status != 'S', IFNULL(DATE_FORMAT(ths.Hora_inicio, '%d-%m-%Y %H:%i:%s'), '--'), '--') AS fecha_ini, 
IF(ths.status != 'S', IFNULL(DATE_FORMAT(ths.Hora_Final, '%d-%m-%Y %H:%i:%s'), '--'), '--') AS fecha_fi, 
IF(ths.Fol_Folio IS NOT NULL, CONCAT('(', u.cve_usuario, ') ', u.nombre_completo), '') as usuario_surtidor

FROM th_pedido th
LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
LEFT JOIN cat_estados ce ON ce.ESTADO = th.status
LEFT JOIN t_tiposprioridad p ON p.ID_Tipoprioridad = th.ID_Tipoprioridad
LEFT JOIN th_subpedido ths ON ths.Fol_Folio = th.Fol_Folio
LEFT JOIN td_surtidopiezas tds ON tds.fol_folio = ths.fol_folio
LEFT JOIN c_usuario u ON u.cve_usuario = ths.Cve_Usuario
WHERE th.cve_almac = {$almacen}
{$sql_tipo_pedido1} 
{$sql_fecha} 
{$sql_search} 
{$sql_ruta} 
{$sql_status}
AND th.TipoPedido = '{$tipopedido}'
GROUP BY Fol_Folio
ORDER BY th.Fol_folio
#ORDER BY ce.ORDEN
#ORDER BY th.TipoPedido
";

        $query = mysqli_query(\db2(), $sql);
        //$datos = mysqli_fetch_array($query, MYSQLI_ASSOC);
        $rows = array();
        while(($row = mysqli_fetch_array($query, MYSQLI_ASSOC))) 
        {
            $rows[] = $row;
            $datos = $row;
        }

        //$cuadro7 = array('LP', 'PARTIDA','CONCEPTO','CANTIDAD','U.M.','P.U.','SUBTOTAL');
        $cuadro7 = array('Fecha Pedido', 'Fecha Entrega', 'Ruta', 'Pedido', 'Clave Cliente', 'Razón Social', 'Surtidas', 'Total Factura', 'Observaciones', 'Status', 'Usuario Surtidor', 'Inicio', 'Fin', 'Fecha Aprobación');

        $excel = new XLSXWriter();
        $excel->writeSheetRow($title, $cuadro7 );
        foreach($rows as $row)
        {
            //$row = array($row["LP"], $row["cve_articulo"],$row["des_articulo"], number_format($row["cantidad"], 2),$row["umas"], $row["costo"],$row["subtotal"]);
            $row = array($row["Fec_Pedido"], $row["Fec_Entrega"], $row["cve_ruta"], $row["Fol_folio"], $row["Cve_Clte"], $row["RazonSocial"], $row['surtidas'], $row["total_factura"], $row["Observaciones"], $row["status"], $row["usuario_surtidor"], $row["fecha_ini"], $row["fecha_fi"], $row["fecha_aprobacion"]);
            $excel->writeSheetRow($title, $row );
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $title . '"');
        header('Cache-Control: max-age=0');
        $excel->writeToStdOut($title);
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'ReporteDeSalidas')
{
    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $almacen            = $_POST['almacen'];
    $criterio           = $_POST['criterio'];
    $tipopedido         = $_POST['tipopedido'];
    $status             = $_POST['status'];
    $ruta_pedido_list   = $_POST['ruta_pedido_list'];
    $ciudad_pedido_list = $_POST['ciudad_pedido_list'];
    $fecha_inicio        = $_POST['fechaInicio'];
    $fecha_fin           = $_POST['fechaFin'];

    $title              = "Reporte de Salidas.xlsx";

      $sql_ruta = "";
      if($ruta_pedido != '')
      {
          $ruta_arr = explode(";;;;;", $ruta_pedido);
          $ruta1 = $ruta_arr[0];
          $ruta2 = "";
          if(count($ruta_arr) > 1)
            $ruta2 = $ruta_arr[1];
          if($ruta1 != "")
          {
              $sql_ruta = " AND (th.ruta = '$ruta1' OR th.cve_ubicacion = '$ruta1') ";
          }

          if($ruta2 != "")
          {
              $sql_ruta = " AND (th.ruta IN ('$ruta1', '$ruta2') OR th.cve_ubicacion IN ('$ruta1', '$ruta2')) ";
          }
      }


      $sql_fecha = "";

      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql_fecha = " AND DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        //STR_TO_DATE('$fecha', '%d-%m-%Y')
        //$sql_fecha = " AND DATE_FORMAT(IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada), '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        $sql_fecha = " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) BETWEEN STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') AND STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";

      }
      elseif (!empty($fecha_inicio)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$sql_fecha .= " AND DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        $sql_fecha .= " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) >= STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') ";
      }
      elseif (!empty($fecha_fin)) 
      {
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql_fecha .= " AND DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        $sql_fecha .= " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) <= STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";
      }

      $sql_search = "";
      if (!empty($criterio) ) 
      {
            $sql_search = " AND (th.Fol_folio like '%$criterio%' OR th.Pick_Num like '%$criterio%' OR th.Fol_folio IN (SELECT Fol_folio FROM td_pedido WHERE Cve_articulo LIKE '%$criterio%')) ";
      }

      $sql_tipo_pedido1 = "";
      if($tipopedido != "")
      {
          //$sql_tipo_pedido1 = " AND IF(LEFT(th.Fol_folio, 2) = 'OT', 1, IF(LEFT(th.Fol_folio, 2) = 'TR', 2, IF(LEFT(th.Fol_folio, 2) = 'WS', 4, IF(LEFT(th.Fol_folio, 1) = 'S' AND IFNULL(th.cve_ubicacion,'') != '', 5, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) != 'R' AND LEFT(th.Fol_folio, 1) != 'P', 3, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) = 'R', 6, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) = 'P', 7, 0))))))) = '{$tipopedido}' ";
            $sql_tipo_pedido1 = " AND th.TipoPedido = '{$tipopedido}' ";
      }
    $sql = "SELECT #IF(ths.fol_folio IS NULL, th.Fol_folio, CONCAT(ths.fol_folio, '-', ths.Sufijo)) AS Folio_A, 
                th.Fol_folio AS Folio_A, 
                IFNULL(CONCAT(c.Cve_Clte, '/', c.RazonSocial), '') AS Cliente_B, ar.cve_articulo AS Clave_C, IFNULL(ar.cve_alt, '') AS Clave_Alterna_D, ar.des_articulo AS Des_Articulo_E, td.Num_cantidad AS Cantidad_F,
               IFNULL(ad.costo, '') AS Costo_Unitario_G, (IFNULL(ad.costo, '')*td.Num_cantidad) AS Costo_Total_H, IFNULL(td.Proyecto, '') AS Proyecto_I, IFNULL(u.CodigoCSD, '') AS BL_J, IFNULL(ch.CveLP, '') AS LP_K, IFNULL(td.cve_lote, tds.Cve_Lote) AS lote_serie_L, 
               '' AS Fecha_Fabricacion_M, IF(ar.Caduca = 'S', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS Fecha_Caducidad_N, '' AS Estado_de_Serial_O, IFNULL(gr.des_gpoart, '') AS Grupo_Articulo_P, IFNULL(csf.des_sgpoart, '') AS Clasificacion_Articulo_Q,
               ar.peso AS Peso_Art_R, ar.alto AS Alto_Art_S, ar.ancho AS Ancho_Art_T, ar.fondo AS Fondo_Art_U, CONCAT(ROUND(ar.alto*ar.ancho*ar.fondo)*1000, ' m3') AS Total_M3_V, '' AS Tipo_de_Quimico_W,
               ar.num_multiplo AS Piezas_Por_Caja_X, DATE_FORMAT(th.Fec_Pedido, '%d-%m-%Y') AS Fecha_Registro_Pedido_Y, DATE_FORMAT(th.Fec_Entrada, '%H:%i:%s') AS Hora_Registro_Z, 
               IFNULL(CONCAT('(',th.Cve_Usuario, ') ', u_pedido.nombre_completo), '') AS Usuario_Registro_AA, '' AS Fecha_Solicitud_Cliente_AB, '' AS Hora_Solicitud_AC, DATE_FORMAT(th.Fec_Entrega, '%d-%m-%Y') AS Fecha_Entrega_AD,
               IFNULL(th.rango_hora, '') AS Horario_Planeado_AE, IFNULL(CONCAT('(', ths.Cve_Usuario, ') ', u_surtidor.nombre_completo), '') AS Usuario_Surtidor_AF, IFNULL(DATE_FORMAT(ths.Hora_inicio, '%d-%m-%Y'), '') AS Fecha_Inicio_Surtido_AG, IFNULL(DATE_FORMAT(ths.Hora_inicio, '%H:%i:%s'), '') AS Hora_Inicio_Surtido_AH,
               IFNULL(DATE_FORMAT(ths.Hora_Final, '%d-%m-%Y'), '') AS Fecha_Final_Surtido_AI, IFNULL(DATE_FORMAT(ths.Hora_Final, '%H:%i:%s'), '') AS Hora_Final_Surtido_AJ,
               IFNULL(th_oe.guia_transporte, '') AS Guia_Transporte_AK, IFNULL(th.Pick_Num, '') AS Orden_De_Compra_Cliente_AL, IF(td_oe.status='T', 'ENVIADA', 'ENTREGADA') AS Nota_Entrega_AM, 
               IFNULL(CONCAT(ct.nombre, ' ', ct.apellido), '-') AS Contacto_ATN_AN, th.Fol_folio AS Orden_Despacho_AO, c.RazonSocial AS Destino_AP, IFNULL(d.direccion, '') AS Direccion_AQ, 
               IFNULL(d.telefono, '') AS Contacto_Solicitante_AR, IFNULL(d.telefono, '') AS Telefono_Solicitante_AS, IFNULL(th.Observaciones, '') AS Descripcion_Detallada_AT, IFNULL(th_oe.ID_OEmbarque, '') AS Folio_Embarque_AU, 
               IFNULL(DATE_FORMAT(th_oe.fecha, '%d-%m-%Y'), '') AS Fecha_Embarque_AV, IFNULL(IF(IFNULL(th_oe.cve_usuario, '') = '', '', CONCAT('(', th_oe.cve_usuario, ') ', u_embarca.nombre_completo)), '') AS Usuario_que_Embarca_AW, 
               IFNULL(IF(tr.transporte_externo = 0, 'NO', 'SI'), '') AS Transporte_Externo_AX, IFNULL(p.Nombre, '') AS Transportadora_AY, IFNULL(IFNULL(th_oe.placa, tr.Placas), '') AS Placa_AZ, IFNULL(th_oe.chofer, '') AS Conductor_BA, 
               IFNULL(th_oe.sello_precinto, '') AS Sello_Precinto_BB, '' AS Usuario_Reporte_Cliente_BC, '' AS Fecha_Reporte_Cliente_BD, '' AS Hora_Reporte_Cliente_BE, IFNULL(UPPER(ce.DESCRIPCION), '') AS Status_BF
        FROM th_pedido th 
        LEFT JOIN th_subpedido ths ON th.Fol_folio = ths.fol_folio
        LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
        LEFT JOIN td_pedido td ON td.Fol_folio = th.Fol_folio
        LEFT JOIN td_subpedido tds ON tds.fol_folio = td.Fol_folio AND tds.Cve_articulo = td.Cve_articulo
        LEFT JOIN td_surtidopiezas s ON s.fol_folio = tds.fol_folio AND s.Sufijo = tds.Sufijo AND s.Cve_articulo = tds.Cve_articulo AND IFNULL(s.LOTE, '') = IFNULL(tds.Cve_Lote, '')
        LEFT JOIN c_articulo ar ON ar.cve_articulo = td.Cve_articulo
        LEFT JOIN t_cardex k ON k.destino = td.Fol_folio AND ar.cve_articulo = k.cve_articulo AND IFNULL(k.cve_lote, '') = IFNULL(s.LOTE, '') AND k.id_TipoMovimiento = 8
        LEFT JOIN t_MovCharolas mch ON mch.id_kardex = k.id 
        LEFT JOIN c_charolas ch ON ch.IDContenedor = mch.ID_Contenedor
        LEFT JOIN c_ubicacion u ON u.idy_ubica = k.origen
        LEFT JOIN c_lotes l ON l.cve_articulo = ar.cve_articulo AND l.lote = tds.Cve_Lote
        LEFT JOIN c_serie sr ON sr.cve_articulo = ar.cve_articulo AND sr.numero_serie = tds.Cve_Lote
        LEFT JOIN c_gpoarticulo gr ON gr.cve_gpoart = ar.grupo
        LEFT JOIN c_sgpoarticulo csf ON csf.cve_sgpoart = ar.clasificacion
        LEFT JOIN td_ordenembarque td_oe ON td_oe.Fol_folio = th.Fol_folio
        LEFT JOIN th_ordenembarque th_oe ON th_oe.ID_OEmbarque = td_oe.ID_OEmbarque
        LEFT JOIN c_usuario u_pedido ON u_pedido.cve_usuario = th.Cve_Usuario
        LEFT JOIN c_usuario u_surtidor ON u_pedido.cve_usuario = ths.Cve_Usuario
        LEFT JOIN c_usuario u_embarca ON u_pedido.cve_usuario = th_oe.cve_usuario
        LEFT JOIN c_contactos ct ON ct.id = th.contacto_id
        LEFT JOIN Rel_PedidoDest pd ON pd.Fol_Folio = th.Fol_folio
        LEFT JOIN c_destinatarios d ON d.id_destinatario = pd.Id_Destinatario
        LEFT JOIN t_transporte tr ON th_oe.ID_Transporte = tr.id
        LEFT JOIN c_proveedores p ON p.ID_Proveedor = tr.cve_cia 
        LEFT JOIN cat_estados ce ON ce.ESTADO = th.status
        LEFT JOIN td_aduana ad ON ad.cve_articulo = tds.Cve_articulo AND tds.Cve_Lote = ad.Cve_Lote
        WHERE th.cve_almac = {$almacen} {$sql_tipo_pedido1} 
        {$sql_fecha} 
        {$sql_search} 
        {$sql_ruta}
        GROUP BY Folio_A, Cliente_B, Clave_C, Proyecto_I, LP_K, BL_J, lote_serie_L";

        $query = mysqli_query(\db2(), $sql);
        //$datos = mysqli_fetch_array($query, MYSQLI_ASSOC);
        $rows = array();
        while(($row = mysqli_fetch_array($query, MYSQLI_ASSOC))) 
        {
            $rows[] = $row;
            $datos = $row;
        }

        //$cuadro7 = array('LP', 'PARTIDA','CONCEPTO','CANTIDAD','U.M.','P.U.','SUBTOTAL');
        $cuadro7 = array('Folio', 'NIT/Nombre cliente', 'Clave', 'Clave Alterna', 'DESCRIPCION Articulo', 'Cantidad', 'COSTO UNITARIO', 'COSTO TOTAL', 'PROYECTO', 'BL', 'LP', 'Lote|Serie', 'Fecha de Fabricación', 'Fecha de Caducidad', 'ESTADO DE SERIAL ( BUENO ROTO-EXPANDIDO ETC QA)', 'Grupo Articulo', 'Clasificación Articulo', 'PESO', 'ALTO', 'ANCHO', 'FONDO', 'TOTAL M3', 'Tipo de Quimico', 'Pieza por caja', 'Fecha Registro Pedido POS', 'Hora Registro POS', 'Usuario Registro POS', 'FECHA SOLICITUD (DIRECTA DEL CLIENTE)', 'HORA SOLICITUD (DIRECTA CLIENTE)', 'Fecha de Entrega Solicitada (POS)', 'Horario Planeado DESDE-HASTA (Solicitada) (POS)', 'USUARIO SURTIDOR', 'FECHA INICIO SURTIDO', 'HORA INICIO SURTIDO', 'FECHA FINALIZACION SURTIDO', 'HORA FINALIZACION SURTIDO', 'GUIA DE TRANSPORTE', 'ORDEN DE COMPRA (Número OC Cliente)', 'NOTA DE ENTREGA', 'CONTACTO / TEL ATN', 'ORDEN DE DESPACHO', 'DESTINO', 'DIRECCIÓN', 'CONTACTO SOLICITANTE', 'TELEFONO SOLICITANTE', 'Descripción Detallada (POS) ( OBSERVACIONES)', 'FOLIO EMBARQUE', 'Fecha Embarque', 'Usuario que Embarca', 'TRANSPORTE EXTERNO (SI/NO)', 'TRANSPORTADORA', 'PLACA', 'CONDUCTOR', 'SELLO/PRECINTO', 'USUARIO REPORTE CLIENTE', 'FECHA REPORTE CLIENTE', 'HORA REPORTE CLIENTE', 'STATUS');

        $excel = new XLSXWriter();
        $excel->writeSheetRow($title, $cuadro7 );
        foreach($rows as $row)
        {
            //$row = array($row["LP"], $row["cve_articulo"],$row["des_articulo"], number_format($row["cantidad"], 2),$row["umas"], $row["costo"],$row["subtotal"]);
            $row = array($row['Folio_A'], $row['Cliente_B'], $row['Clave_C'], $row['Clave_Alterna_D'], $row['Des_Articulo_E'], $row['Cantidad_F'], $row['Costo_Unitario_G'], $row['Costo_Total_H'], $row['Proyecto_I'], $row['BL_J'], $row['LP_K'], $row['lote_serie_L'], $row['Fecha_Fabricacion_M'], $row['Fecha_Caducidad_N'], $row['Estado_de_Serial_O'], $row['Grupo_Articulo_P'], $row['Clasificacion_Articulo_Q'], $row['Peso_Art_R'], $row['Alto_Art_S'], $row['Ancho_Art_T'], $row['Fondo_Art_U'], $row['Total_M3_V'], $row['Tipo_de_Quimico_W'], $row['Piezas_Por_Caja_X'], $row['Fecha_Registro_Pedido_Y'], $row['Hora_Registro_Z'], $row['Usuario_Registro_AA'], $row['Fecha_Solicitud_Cliente_AB'], $row['Hora_Solicitud_AC'], $row['Fecha_Entrega_AD'], $row['Horario_Planeado_AE'], $row['Usuario_Surtidor_AF'], $row['Fecha_Inicio_Surtido_AG'], $row['Hora_Inicio_Surtido_AH'], $row['Fecha_Final_Surtido_AI'], $row['Hora_Final_Surtido_AJ'], $row['Guia_Transporte_AK'], $row['Orden_De_Compra_Cliente_AL'], $row['Nota_Entrega_AM'], $row['Contacto_ATN_AN'], $row['Orden_Despacho_AO'], $row['Destino_AP'], $row['Direccion_AQ'], $row['Contacto_Solicitante_AR'], $row['Telefono_Solicitante_AS'], $row['Descripcion_Detallada_AT'], $row['Folio_Embarque_AU'], $row['Fecha_Embarque_AV'], $row['Usuario_que_Embarca_AW'], $row['Transporte_Externo_AX'], $row['Transportadora_AY'], $row['Placa_AZ'], $row['Conductor_BA'], $row['Sello_Precinto_BB'], $row['Usuario_Reporte_Cliente_BC'], $row['Fecha_Reporte_Cliente_BD'], $row['Hora_Reporte_Cliente_BE'], $row['Status_BF']);
            $excel->writeSheetRow($title, $row );
        }
            //$excel->writeSheetRow($title, array($sql) );

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $title . '"');
        header('Cache-Control: max-age=0');
        $excel->writeToStdOut($title);
}
