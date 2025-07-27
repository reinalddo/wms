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
    $ubicacion = $_POST["ubicacion"];
    $almacen = $_POST["almacen"];

    $sql = "SELECT * FROM `td_surtidopiezas` WHERE `fol_folio` = '$folio'";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    while($row = mysqli_fetch_array($res)) 
    {
        $sql = "SELECT * FROM `ts_existenciapiezas` WHERE `idy_ubica` = '$ubicacion' and cve_articulo = '".$row['Cve_articulo']."'";
        if (!($res2 = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        if($res2->num_rows == null)
        {
            $sql = "Insert into ts_existenciapiezas(cve_almac,idy_ubica,cve_articulo,cve_lote, Existencia, ClaveEtiqueta, ID_Proveedor)
                    values($almacen,$ubicacion,'".$row['Cve_articulo']."','',".$row["Cantidad"].",'','')";
        }
        else
        {
            $row2 = mysqli_fetch_array($res2);
            $sql = "update ts_existenciapiezas set Existencia = Existencia + ".$row["Cantidad"]." where id = ".$row2["id"].";";
        }
        if (!($res3 = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
    }
  
    $sqlCount = "update th_pedido set status = 'T' where Fol_folio = '$folio'";
    if (!$res = mysqli_query($conn, $sqlCount)) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $sqlCount = "update td_pedido set status = 'T' where Fol_folio = '$folio';";
    if (!$res = mysqli_query($conn, $sqlCount)) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
  
    echo json_encode(array(
        "success" => true,
        "sql"=>$sql,
        "almacen" => $row["clave"],
    ));
    exit;
}

if($_POST['action'] === 'guardarSurtidoPorUbicacion')//1
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $items = $_POST['items'];
    $almacen = $_POST['almacen'];

    $sql = "SELECT clave FROM c_almacenp WHERE id = ".$almacen;
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
    $row = mysqli_fetch_array($res);
  
    if($items["existencia"] > 0)
    {
        $sql = "CALL SPWS_SurteArticulo('".$row["clave"]."','".$items["folio"]."',".$items["sufijo"].",'".$items["clave"]."',".$items["surtidas"].",1,0,".$items["id_ubicacion"].",'".$items["surtidor"]."','".$items["lote"]."');";
        if (!($res = mysqli_query($conn, $sql)))
        {
            echo $items["clave"]."Falló la preparación(2): (" . mysqli_error($conn) . ") ";
        }
    }
    $porcentaje = ($surtidas * 100)/$total;
  
    echo json_encode(array(
      "success" => true,
      "query" => $sql
    ));
    mysqli_close($conn);
    exit;    
}

if($_POST['action'] === 'verificarSiElPedidoEstaSurtiendose')
{
    $data = $ga->verificarStatus($_POST['folio']);
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
    $result = array();

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
                    $sql = "SELECT count(*) as cont  FROM th_subpedido where fol_folio = '{$folio}';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
                    $subpedido = mysqli_fetch_array($res)["cont"];
                
                    $sql = "SELECT count(*) as cont  FROM t_recorrido_surtido where fol_folio = '{$folio}';";
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
                    $sql = "
                        DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}';
                        DELETE FROM td_surtidopiezas where fol_folio = '{$folio}';
                        DELETE FROM th_cajamixta where fol_folio ='{$folio}';
                        UPDATE th_subpedido set status = 'A', cve_usuario = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        UPDATE th_pedido set status = 'A' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(S-A): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "L":
                case "P":
                    $sql ="SELECT count(*) as cont FROM td_surtidopiezas where fol_folio = '".$folio."';";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(surtido): (" . mysqli_error($conn) . ") ";}
                    $surtido = mysqli_fetch_array($res)["cont"];
                    
                    if($surtido > 0)
                    {
                        $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}';";
                        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(S-L,P): (" . mysqli_error($conn) . ") ";}
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
                        
                        $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}'; ";
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
                            update th_subpedido set status = 'A', cve_usuario = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                            update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                    $sql = "update th_subpedido set status = 'L', Reviso = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';";
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
                        update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(P-A): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "L":
                    $sql = "
                        update td_surtidopiezas set revisadas = '0' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_pedido set status = 'L' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'L', Reviso = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                          update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ', empaco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(M-A): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "L":
                    $sql = "
                        update td_surtidopiezas set revisadas = '0' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_pedido set status = 'L' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'L', Reviso = ' ', empaco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                        update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ', empaco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                        update th_subpedido set status = 'L', Reviso = ' ', empaco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                        update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                        update th_subpedido set status = 'L', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                        update th_subpedido set status = 'P', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(E-P): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "C":
                    $sql = "
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update th_pedido set status = 'C' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'C', Embarco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                        update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                        update th_subpedido set status = 'L', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(T-L): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "P":
                    $sql = "
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update th_pedido set status = 'P' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'P', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                        update th_subpedido set status = 'A', cve_usuario = ' ', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                        update th_subpedido set status = 'L', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(F-L): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "P":
                    $sql = "
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update th_pedido set status = 'P' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'P', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                    ";
                    if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(F-P): (" . mysqli_error($conn) . ") ";}
                    $result["success"] = true;
                break;
                case "C":
                    $sql = "
                        DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                        update th_pedido set status = 'C' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                        update th_subpedido set status = 'C', Embarco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                        DELETE FROM t_recorrido_surtido WHERE fol_folio = '{$folio}';
                        DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$folio}';
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
                            DELETE FROM t_recorrido_surtido WHERE fol_folio = '{$folio}';
                            DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$folio}';
                            DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                            update th_pedido set status = 'L' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                            update th_subpedido set status = 'L', Reviso = ' ', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                            DELETE FROM t_recorrido_surtido WHERE fol_folio = '{$folio}';
                            DELETE FROM rel_uembarquepedido WHERE fol_folio = '{$folio}';
                            DELETE FROM td_ordenembarque WHERE fol_folio = '{$folio}';
                            update th_pedido set status = 'P' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
                            update th_subpedido set status = 'P', empaco = ' ', Embarco = ' ' where Fol_folio = '{$folio}' and cve_almac= '{$almacen}';
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
                            DELETE FROM t_recorrido_surtido WHERE fol_folio = '{$folio}';
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
                    $sql ="SELECT count(*) as cont FROM td_surtidopiezas where fol_folio = '".$folio."';";
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
    if($cambiar_status)
    {
        $sql = "
            update th_pedido set status = '".$nuevo_status."' where Fol_folio = '{$folio}' and cve_almac = '{$almacen}';
            update th_subpedido set status = '".$nuevo_status."' where fol_folio = '{$folio}' and cve_almac = '{$almacen}';
        ";
        if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación(Cambiar_status): (" . mysqli_error($conn) . ") ";}
    }
    $result['data']['status'] = $status;
    $result['data']["bandera"]['cambiar_status'] = $cambiar_status;
    $result['data']["bandera"]['generar_cajas'] = $generar_cajas;
    $result['data']["bandera"]['generar_entrada'] = $generar_entrada;
    $result['data']["bandera"]['liberar_ubicacion'] = $liberar_ubicacion;
    $result['data']['nuevo_status'] = $nuevo_status;
    $result['data']['cve_almac'] = $almacen;
    $result['data']['sql2'] = $sql2;
    echo json_encode($result);
    exit;
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
    
    $success = true;
    $arr = array(
        "success" => $success,
        "resp"    => $row
    );
    echo json_encode($arr);
}

if($_POST['action'] === 'asignarZonaEmbarque')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $folio = $_POST['folio'];
    $almacen = $_POST['almacen'];
    $zonaembarque = $_POST['zonaembarque'];

    $sql = "INSERT INTO rel_uembarquepedido (cve_ubicacion, fol_folio, Sufijo, cve_almac, Activo) 
            VALUES ('{$zonaembarque}', '{$folio}', 1,'{$almacen}', 1);";
    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
    
    $sql = "UPDATE th_subpedido set status = 'C' where fol_folio = '".$folio."';";
    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";}
  
    $sql = "UPDATE t_ubicacionembarque set status = 2 where ID_Embarque = '".$zonaembarque."' and status = 1;";
    if(!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ";}
  
    $sql = "select count(status) as dif from th_subpedido where fol_folio = '".$folio."' and status != 'C'" ;
    if(!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";}
    
    $sql = "select Sufijo from th_subpedido where fol_folio = '".$folio."' " ;
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

    $row = mysqli_fetch_array($res);
    $count = $row['diff'];
  
    if($count == 0)
    {
      $result = $ga->cambiarStatus($_POST);
    }

    echo json_encode(array("success" => $result));
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

if($_POST['action'] == 'loadArticulos') 
{
    $data = $ga->loadArticulos($_POST["id_pedido"],$_POST["almacen"],$_POST["status"]);
    if($data[3] == 'A')
    {
        $arr = array(
          "success"   =>  true,
          "res_sp"    =>  $data[1],
          "sql"       =>  $data[2],
          "status"    =>  $data[3],
          "articulos" =>  $data[0],
        );
    }
    else
    {
        $arr = array(
          "success"   =>  true,
          "res_sp"    =>  $data[1],
          "sql"       =>  $data[2],
          "status"    =>  $data[3],
          "articulos" =>  $data[0],
          "articulos_surtidos" => $data[4],
        );
    }

    echo json_encode($arr);
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

if( $_POST['action'] == 'asignar' ) //1
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	  $pedidos = $_POST['pedidos'];
    $almacen = $_POST['almacen'];
    $usuario = $_POST['usuarios'];
    $hora = $_POST['hora_inicio'];
    $fecha = $_POST['fecha'];
    $sql="select clave from c_almacenp where id= {$almacen}";
    $res = mysqli_query($conn, $sql);
    $val = mysqli_fetch_array($res)["clave"];
    $sp = "SPWS_PreparaPedidoSurtido".((strpos($_SERVER['HTTP_HOST'], 'nikken') !== false)?"Nik_2":"");
  
    $res=array();
    $debug="";
    if(!empty($_POST['pedidos']))
    {
        $sql = "";
        $folios = $pedidos;
        $sql =  "call ".$sp ."('".$val."','".$folios."','".$usuario."','".$fecha."');" ;
       
        $asignar = $ga->asignar($sql);
        $res[] = $asignar;
      
        $folios_sin_existencia = "";
        foreach($pedidos as $folio)
        {
            $sql = "select * from t_recorrido_surtido where fol_folio = '".$folio."'";
            if (!($res = mysqli_query($conn, $sql))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }
            
            if($res->num_rows == null)
            {
                $result = strpos($_SERVER['HTTP_HOST'], 'nikken');
                if(gettype($result) !== 'integer')
                {
                    $folios_sin_existencia .= $folio.",";
                }
                $sql = "DELETE FROM th_subpedido WHERE Fol_folio = '{$folio}';";
                mysqli_multi_query($conn, $sql);
                $sql = "DELETE FROM td_subpedido WHERE Fol_folio = '{$folio}';";
                mysqli_multi_query($conn, $sql);
                $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '{$folio}';";
                mysqli_multi_query($conn, $sql);
                $sql = "UPDATE th_pedido SET status = 'A' WHERE Fol_folio = '{$folio}';";
                mysqli_multi_query($conn, $sql);
            }
        }
    }
    
    $arr = array(
        "success" => true,
        "resp" => $res,
        "debug"=>$debug,
        "sin_existencia" => $folios_sin_existencia
    );
    echo json_encode($arr);exit;
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




