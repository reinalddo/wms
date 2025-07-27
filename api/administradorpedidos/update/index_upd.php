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

    $sql = "";$sql1 = "";$sql2 = "";$sql3 = "";$sql4 = "";
    $sql = "SELECT * FROM td_surtidopiezas WHERE fol_folio = '$folio' AND Sufijo = {$sufijo}";
    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    while($row = mysqli_fetch_array($res)) 
    {
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
        $sql2 = $sql;
        if (!($res3 = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('".$row['Cve_articulo']."', '".$row["LOTE"]."', NOW(), '".$ubicacion."', '".$folio."-".$sufijo."', ".$row["Cantidad"].", 1, '".$_SESSION['cve_usuario']."', ".$almacen.")";
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
        $sqlCount = "UPDATE t_ordenprod set status = 'I' where Folio_Pro = '$folio'";
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

        $sqlCount = "UPDATE td_pedido set status = 'T' where Fol_folio = '$folio';";
        $sql4 = $sqlCount;
        if (!$res = mysqli_query($conn, $sqlCount)) 
        {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $sqlCount = "UPDATE td_subpedido set status = 'T' where Fol_folio = '$folio';";
        $sql4 = $sqlCount;
        if (!$res = mysqli_query($conn, $sqlCount)) 
        {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

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

if($_POST['action'] === 'guardarSurtidoPorUbicacion')//1
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $items = $_POST['items'];
    $almacen = $_POST['almacen'];

    $sql = "SELECT clave FROM c_almacenp WHERE id = ".$almacen.";";
    //if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(0): (" . mysqli_error($conn) . ") ";}
    //$res = mysqli_multi_query($conn, $sql);
    //$row = mysqli_fetch_array($res);
    //$clave = $row["clave"];
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";}
    $clave = mysqli_fetch_array($res)["clave"];

    //mysqli_free_result($res);

    if($items["existencia"] > 0)
    {

    $sql = "SELECT IFNULL(SUM(Num_cantidad), 0) as pedidas FROM td_subpedido WHERE fol_folio = '".$items["folio"]."' AND Sufijo = '".$items["sufijo"]."'";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ";}
    $pedidas = mysqli_fetch_array($res)["pedidas"];

    $sql = "SELECT num_multiplo, control_lotes, control_numero_series FROM c_articulo WHERE cve_articulo = '".$items["clave"]."';";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";}

    $row_art = mysqli_fetch_array($res);
    $num_multiplo = $row_art["num_multiplo"];
    $control_lotes = $row_art["control_lotes"];
    $control_numero_series = $row_art["control_numero_series"];

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


        $sql_delete = "DELETE  FROM t_recorrido_surtido WHERE   fol_folio='".$items["folio"]."' AND Sufijo=".$items["sufijo"]." AND Cve_Articulo='".$items["clave"]."' AND Cve_Lote='".$lote."' AND Idy_Ubica=".$items["idy_ubica"]."";
        $res2 = mysqli_query($conn, $sql_delete);
        mysqli_free_result($res2);

        //$sql_insert = "INSERT INTO td_surtidopiezas(fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, status) VALUES ('".$items["folio"]."', '".$almacen."', ".$items["sufijo"].",'".$items["clave"]."','".$lote."',".$items["existencia"].",".$items["surtidas"].", 'S');";
        $sql_insert = "INSERT INTO td_surtidopiezas(fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, status) VALUES ('".$items["folio"]."', '".$almacen."', ".$items["sufijo"].",'".$items["clave"]."','".$lote."',".$items["existencia"].",0, 'S') ON DUPLICATE KEY UPDATE Cantidad = Cantidad + ".$items["existencia"].";";
        //UPDATE th_subpedido SET status = 'S' WHERE fol_folio = '".$items["folio"]."' AND Sufijo = '".$items["sufijo"]."';
        //El UPDATE th_subpedido SET status = 'S'... es para mantener el status = S mientras se le da clic al botón surtir
        if (!($res2 = mysqli_query($conn, $sql_insert))) {echo "Falló la preparación(5): (" . mysqli_error($conn) . ") ";}
        //$res2 = mysqli_multi_query($conn, $sql_insert);
        mysqli_free_result($res2);

        $sql_update = "UPDATE ts_existenciapiezas SET Existencia = Existencia - ".$items["existencia"]." WHERE idy_ubica = ".$items["idy_ubica"]." AND cve_almac = ".$almacen." AND cve_articulo = '".$items["clave"]."' AND cve_lote = '".$lote."';
            UPDATE th_subpedido SET HIE = NOW(), Hora_inicio = NOW() WHERE fol_folio = '".$items["folio"]."' AND Sufijo = '".$items["sufijo"]."'";
        //if (!($res3 = mysqli_multi_query($conn, $sql_update))) {echo "Falló la preparación(6): (" . mysqli_error($conn) . ") ";}
        $res3 = mysqli_query($conn, $sql_update);
        mysqli_free_result($res3);

        $sql = "SELECT COUNT(*) AS produccion FROM t_ordenprod WHERE Folio_Pro = '".$items["folio"]."'";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 09: (" . mysqli_error($conn) . ") ";}
        //$res = mysqli_query($conn, $sql);
        $produccion = mysqli_fetch_array($res)["produccion"];
        mysqli_free_result($res);

        if($produccion)
        {
            $sql = "SELECT idy_ubica FROM c_ubicacion WHERE AreaProduccion = 'S' LIMIT 1";
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
                $sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor, Cuarentena)
                        VALUES (".$almacen.", ".$idy_ubica.", '".$items["clave"]."', '".$lote."', ".$items["existencia"].", 0, 0)
                        ON DUPLICATE KEY UPDATE Existencia = Existencia + ".$items["existencia"].";";

                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 8: (" . mysqli_error($conn) . ") ";}
                //$res = mysqli_query($conn, $sql);
                mysqli_free_result($res);
        }

        $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('".$items["clave"]."', '".$lote."', NOW(), '".$idy_ubica."', '".$items["folio"]."-".$items["sufijo"]."', ".$items["existencia"].", 8, '".$_SESSION['cve_usuario']."', ".$almacen.")";
        if (!($res2 = mysqli_query($conn, $sql_kardex))) {echo "Falló la preparación 10: (" . mysqli_error($conn) . ") ";}
        //$res2 = mysqli_multi_query($conn, $sql_kardex);
        mysqli_free_result($res2);

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

    //$porcentaje = ($items["surtidas"] * 100)/$items["pedidas"];
  
    echo json_encode(array(
      "porcentaje" => $porcentaje,
      "success" => true,
      "query" => $sql,
      "sql_insert" => $sql_insert,
      "sql_update" => $sql_update
    ));
    mysqli_close($conn);
    exit;    
}

if($_POST['action'] === 'verificarSiElPedidoEstaSurtiendose')
{
    $data = $ga->verificarStatus($_POST['folio'], $_POST['is_backorder'], $_POST['sufijo']);
    echo json_encode(array(
        "success" => true,
        "status"     => $data[0],
    ));
    exit;
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
        $sql = "
        SELECT 
            status
        FROM th_subpedido 
        WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}' AND cve_almac = '{$almacen}';
        ";
        $sql2 = "UPDATE th_subpedido SET HFE = NOW(), Hora_Final = NOW() WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}' AND cve_almac = '{$almacen}';";
        if (!($res2 = mysqli_query($conn, $sql2))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
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
                    $result["success"] = false;
                    //$result["sql"] = $sql;
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
                    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}'; 
                            DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';";
                            
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
                    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';
                            DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}'; ";
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
                    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}'; 
                            DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';";
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
                    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}'; 
                            DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';";
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
                            DELETE FROM td_surtidopiezas WHERE Fol_folio = '{$folio}';
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

if($_POST['action'] === 'asignarZonaEmbarque')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $folio = $_POST['folio'];
    $almacen = $_POST['almacen'];
    $sufijo = $_POST['sufijo'];
    $zonaembarque = $_POST['zonaembarque'];

    $sql = "INSERT INTO rel_uembarquepedido (cve_ubicacion, fol_folio, Sufijo, cve_almac, Activo) 
            VALUES ('{$zonaembarque}', '{$folio}', '{$sufijo}','{$almacen}', 1);";
    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}

    $sql = "UPDATE th_subpedido set status = 'C', HFE = NOW(), Hora_Final = NOW() where fol_folio = '".$folio."' and Sufijo = '{$sufijo}';";
    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}

    $sql = "SELECT count(*) as num_pedidos_C from th_subpedido where fol_folio = '".$folio."' and status = 'C'" ;
    if(!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";}
    $row = mysqli_fetch_array($res);
    $num_pedidos_C = $row['num_pedidos_C'];

    $sql = "SELECT count(*) as num_pedidos from th_subpedido where fol_folio = '".$folio."'" ;
    if(!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";}
    $row = mysqli_fetch_array($res);
    $num_pedidos = $row['num_pedidos'];

    if($num_pedidos_C == $num_pedidos)
    {
        $sql = "UPDATE th_pedido set status = 'C' where fol_folio = '".$folio."'";
        if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}
    }
  
    $sql = "UPDATE t_ubicacionembarque set status = 2 where cve_ubicacion  = '".$zonaembarque."' and status = 1;";
    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ";}

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
    $sql = "SELECT s.Cve_articulo, s.Num_cantidad, s.Cve_Lote, a.tipo_caja 
            FROM td_subpedido s 
            LEFT JOIN c_articulo a ON a.cve_articulo = s.Cve_articulo
            WHERE s.fol_folio = '".$folio."' and s.Sufijo = '".$sufijo."' " ;

    if(!($res_empaque = mysqli_query($conn, $sql))) {echo "Falló la preparación(5): (" . mysqli_error($conn) . ") ";}

    $n_caja = 1;
    while($row = mysqli_fetch_array($res_empaque)) 
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

        $sql = "INSERT INTO th_cajamixta(Cve_CajaMix, fol_folio, Sufijo, NCaja, abierta, embarcada, cve_tipocaja, Guia) 
                VALUES ({$cve_cajamix}, '{$folio}', {$sufijo}, {$n_caja}, 'N', 'S', {$tipo_caja}, '{$guia_caja}') ";
        if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(7): (" . mysqli_error($conn) . ") ";}

        $sql = "INSERT INTO td_cajamixta (Cve_CajaMix, Cve_articulo, Cantidad, Cve_Lote, Num_Empacados) 
                VALUES ({$cve_cajamix}, '{$cve_articulo}', {$Num_cantidad}, '{$Cve_Lote}', {$Num_cantidad})";
        if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(8): (" . mysqli_error($conn) . ") ";}

        $n_caja++;
    }
/*
    $row = mysqli_fetch_array($res);
    $count = $row['diff'];
  
    if($count == 0)
    {
      $result = $ga->cambiarStatus($_POST);
    }
*/
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
          $folio = $_POST['folio'];

           $sql = "
               SELECT IFNULL(Ship_Num, '') as folio_rel FROM th_pedido WHERE Fol_folio = '{$pedidos}';
           ";
           if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
           $row = mysqli_fetch_array($res);
           $folio_rel = $row['folio_rel'];

        $data = $ga->loadArticulos($pedidos, $_POST["almacen"], $_POST["status"], $_POST['sufijo'], $_POST['is_backorder'], $folio_rel);
        if($data[3] == 'A')
        {
            $arr = array(
              "success"   =>  true,
              "res_sp"    =>  $data[1],
              "sql"       =>  $pedidos, //$data[2],
              "status"    =>  $data[3],
              "articulos" =>  $data[0],
            );
        }
        else
        {
            $arr = array(
              "success"   =>  true,
              "res_sp"    =>  $data[1],
              "sql"       =>  $pedidos, //$data[2],
              "status"    =>  $data[3],
              "articulos" =>  $data[0],
              "articulos_surtidos" => $data[4],
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
                  if((strpos($_SERVER['HTTP_HOST'], 'nikken') === false))
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

                          //$where_select = "WHERE cve_articulo IN ($cve_articulo) AND VS_ExistenciaParaSurtido.cve_lote = (SELECT Lote FROM c_lotes WHERE cve_articulo = VS_ExistenciaParaSurtido.cve_articulo AND Caducidad > CURDATE())";
                          $where_select = "WHERE ((VS_ExistenciaParaSurtido.cve_articulo IN ($cve_articulo) AND IFNULL(VS_ExistenciaParaSurtido.cve_lote, '') IN (SELECT Lote FROM c_lotes WHERE cve_articulo = VS_ExistenciaParaSurtido.cve_articulo AND Caducidad > CURDATE())) OR (VS_ExistenciaParaSurtido.cve_articulo IN ($cve_articulo) AND IFNULL(a.Caduca, 'N') = 'N'))";
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

                         if($nivel == 0 && $sufijo > 0)
                            $sql = "SELECT v.cve_usuario AS cve_usuario, cuser.nombre_completo AS nombre_completo FROM V_PermisosUsuario v
                                    LEFT JOIN c_usuario cuser ON cuser.cve_usuario = v.cve_usuario
                                    WHERE v.ID_PERMISO = 2 AND v.cve_usuario NOT IN (SELECT u.cve_usuario AS cve_usuario FROM rel_usuario_ruta r LEFT JOIN c_usuario u ON u.id_user = r.id_usuario)";
                         else 
                         {
                                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                                $sql = "SELECT IFNULL(Ship_Num, '') as folio_rel FROM th_pedido WHERE Fol_folio = '{$folio}'";
                                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                                $row = mysqli_fetch_array($res);
                                $folio_rel = $row['folio_rel'];

                                if($folio_rel) //si está relacionado con alguna OT
                                {
                                  $sql = "
                                      SELECT 
                                          td_ruta_surtido.idr as idr, 
                                          rel_usuario_ruta.id_usuario as id_usuario, 
                                          c_usuario.cve_usuario as cve_usuario, 
                                          c_usuario.nombre_completo as nombre_completo
                                      FROM V_ExistenciaGralProduccion VS_ExistenciaParaSurtido
                                        $subpedido_inner
                                        INNER JOIN td_ruta_surtido ON td_ruta_surtido.idy_ubica = VS_ExistenciaParaSurtido.cve_ubicacion
                                        INNER JOIN rel_usuario_ruta ON $subpedido_and
                                        INNER JOIN c_usuario ON c_usuario.id_user = rel_usuario_ruta.id_usuario
                                        INNER JOIN c_articulo a ON a.cve_articulo = VS_ExistenciaParaSurtido.cve_articulo
                                      GROUP BY rel_usuario_ruta.id_usuario;
                                  ";
                                }
                                else 
                                {
                                  $sql = "
                                      SELECT 
                                          td_ruta_surtido.idr as idr, 
                                          rel_usuario_ruta.id_usuario as id_usuario, 
                                          c_usuario.cve_usuario as cve_usuario, 
                                          c_usuario.nombre_completo as nombre_completo
                                      FROM VS_ExistenciaParaSurtido
                                        $subpedido_inner
                                        INNER JOIN td_ruta_surtido ON td_ruta_surtido.idy_ubica = VS_ExistenciaParaSurtido.Idy_Ubica 
                                        INNER JOIN rel_usuario_ruta ON $subpedido_and
                                        INNER JOIN c_usuario ON c_usuario.id_user = rel_usuario_ruta.id_usuario
                                        INNER JOIN c_articulo a ON a.cve_articulo = VS_ExistenciaParaSurtido.cve_articulo
                                      $where_select
                                      GROUP BY rel_usuario_ruta.id_usuario;
                                  ";
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
    $arr = array(
        "success"   =>  true,
        "articulos_pedidos"    =>  $data[0][1],
        "articulos_existentes" =>  $data[0][2],
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

	$pedidos = $_POST['pedidos'];//json_decode($_POST['pedidos']);
    $almacen = $_POST['almacen'];
    $usuario = $_POST['usuarios'];
    $hora = $_POST['hora_inicio'];
    $fecha = $_POST['fecha'];
    $opcion = $_POST['opcion'];
    $sql="select clave from c_almacenp where id= {$almacen}";

    $sql_ejecutados .= $sql." ------ ";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "c_almacenp: (" . mysqli_error($conn) . ") ";
    }
    $val = mysqli_fetch_array($res)["clave"];
    @mysqli_close($conn);

    $sp = "SPWS_PreparaPedidoSurtido".((strpos($_SERVER['HTTP_HOST'], 'nikken') !== false)?"Nik_2":"");
    $sql_ejecutados .= $sp." ------ ";
    $debug="";
    $call_sp_used = "";
        $val_sp = 0;
        if($pedidos)
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
                        $sql = "SELECT IFNULL(Ship_Num, '') as folio_rel FROM th_pedido WHERE Fol_folio = '{$folio}'";
                        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                        $row = mysqli_fetch_array($res);
                        $folio_rel = $row['folio_rel'];

                        if($folio_rel)
                        {
                            $sql = "SELECT IFNULL(SUM(Existencia), 0) AS Existencia_Total 
                                    FROM V_ExistenciaGralProduccion VS_ExistenciaParaSurtido 
                                    INNER JOIN c_articulo a ON a.cve_articulo = VS_ExistenciaParaSurtido.cve_articulo
                                    WHERE VS_ExistenciaParaSurtido.cve_articulo = '$cve_articulo' AND VS_ExistenciaParaSurtido.cve_almac = '$almacen' AND (VS_ExistenciaParaSurtido.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = VS_ExistenciaParaSurtido.cve_articulo AND Caducidad > CURDATE()) OR (VS_ExistenciaParaSurtido.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))";//GROUP BY cve_articulo
                        }
                        else
                        {
                            $sql = "SELECT IFNULL(SUM(Existencia), 0) AS Existencia_Total 
                                    FROM VS_ExistenciaParaSurtido 
                                    INNER JOIN c_articulo a ON a.cve_articulo = VS_ExistenciaParaSurtido.cve_articulo
                                    WHERE VS_ExistenciaParaSurtido.cve_articulo = '$cve_articulo' AND VS_ExistenciaParaSurtido.cve_almac = '$almacen' AND (VS_ExistenciaParaSurtido.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = VS_ExistenciaParaSurtido.cve_articulo AND Caducidad > CURDATE()) OR (VS_ExistenciaParaSurtido.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))";//GROUP BY cve_articulo
                        }
                        $sql_ejecutados .= $sql." ------ ";

                        if (!($res_vs = mysqli_query($conn, $sql))) {
                            //$sql_ejecutados .= " DELETE2: (" . mysqli_error($conn) . ") ";
                            echo " Existencia_Total: (" . mysqli_error($conn) . ") ";
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

                    }
                    //*********************************************************************************************

                    @mysqli_close($conn);

            //$res = mysqli_multi_query($conn, $sql);
            $sql_ejecutados .= $sql." ------ ";

            $sql =  "call ".$sp ."('".$val."','".$folios."','".$usuario."','".$fecha."');" ;
            $call_sp_used = $sql;
            $sql_ejecutados .= $sql." ------ ";

            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            //$res = mysqli_query($conn, $sql);
            if (!($res = mysqli_query($conn, $sql))) {
                //$sql_ejecutados .=  "CALL: (" . mysqli_error($conn) . ") ";
                echo "CALL: (" . mysqli_error($conn) . ") ";
            }
            $val_sp = mysqli_fetch_array($res)["Error"];
            //@mysqli_close($conn);


            $folio = $pedidos;
/*
            if($num_pedidos == $num_subpedidos || $num_pedidos == $num_subpedidos1)
            {
*/
                $sql="UPDATE th_pedido SET status = 'S', Activo = 0 WHERE Fol_folio = '$folio';";
                $res = mysqli_multi_query($conn, $sql);
                @mysqli_close($conn);
/*
            }
*/
        }
        

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
    echo json_encode(array("success" => $query));
}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'getDataExcel') 
{
    $ga->generateExcel($_POST);
}




