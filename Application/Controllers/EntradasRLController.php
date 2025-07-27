<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\Articulos;
use Application\Models\ArticulosImportados;  
use Application\Models\Lotes;
use Application\Models\OrdenesDeCompra;
use Application\Models\OrdenesDeCompraItems;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class EntradasRLController extends Controller
{
    const CVE_ARTICULO    = 0;
    const LOTE            = 1;
    const CADUCIDAD       = 2;
    const EXISTENCIA      = 3;
    const BL              = 4;
    const LP              = 5;
    const CANTIDAD_PEDIDA = 6;
    const FECHA_ENTRADA   = 7;
    const COSTO_UNITARIO  = 8;
    const FACTURA_ART     = 9;
    const CAJA            = 10;
    const LOTE_ALTERNO    = 11;

 /////////////////////////////////////////////////////////////////////////////////////////////////////

    const BL_W              = 0;
    const LP_W              = 1;
    const CVE_ARTICULO_W    = 2;
    const LOTE_W            = 3;
    const CADUCIDAD_W       = 4;
    const EXISTENCIA_W      = 5;
    const REFERENCIA        = 6;
    const NUM_PEDIMENTO     = 7;
    const TIPO_OPERACION = 8;
    const CVE_PEDIMENTO = 9;
    const NUM_ACUSE = 10;
    const ADUANA_ES = 11;
    const ADUANA_DESPACHO = 12;
    const PESO_BRUTO = 13;
    const MARCAS_NUM_BULTOS = 14;
    const CLAVE_IMP_EXP = 15;
    const RAZON_SOCIAL_IMP_EXP = 16;
    const CLIENTE = 17;
    const CVE_CLIENTE = 18;
    const DESTINATARIO = 19;
    const CVE_DESTINATARIO = 20;
    const ORDEN_DE_COMPRA = 21;
    const BL_MASTER = 22;
    const BL_HOUSE = 23;
    const CONTENEDOR = 24;
    const NUM_PARTIDA = 25;
    const CONS_FACTURA = 26;
    const FACTURA = 27;
    const FECHA_FACTURA = 28;
    const PROVEEDOR = 29;
    const CVE_PROVEEDOR = 30;
    const PARTE_M3 = 31;
    const PARTE = 32;
    const FRACCION = 33;
    const NICO = 34;
    const DESCRIPCION_AA = 35;
    const PAIS_ORIGEN = 36;
    const PAIS_VEND_COMP = 37;
    const CANTIDAD_FACTURA = 38;
    const UM_COMERCIALIZACION = 39;
    const CANT_TARIFA = 40;
    const UM_TARIFA = 41;
    const PRECIO_UNITARIO = 42;
    const VALOR_FACTURA = 43;
    const UM_COVE = 44;
    const CANTIDAD_COVE = 45;
    const VALOR_MERC_COVE = 46;
    const PRECIO_UNIT_COVE = 47;
    const DESC_FACT_COVE = 48;
    const PLACAS_TRANSPORTE = 49;


    //const CANTIDAD_PEDIDA_W = 6;
 ///////////////////////////////////////////////////////////////////////////////////////////////////// 

    function fpdo($pdo, $sql, $campos_array, $Ejecutar_Contar_Fetch = 'E', $msj_sql_error = '')
    {
        try
        {
            //\db()->query("BEGIN;");
            //$conex = \db()->prepare("BEGIN;");$conex->execute();
            //$pdo = null;
            //$pdo = \db();
            $res_pdo = $pdo->prepare($sql);

            if($campos_array == '') $campos_array = array();

            if(count($campos_array) == 0)
                $res_pdo->execute();
            else
                $res_pdo->execute($campos_array);

            if($Ejecutar_Contar_Fetch == 'F')
            {
                $row_pdo = $res_pdo->fetch();
                //$pdo = null;
                return $row_pdo;
            }

            if($Ejecutar_Contar_Fetch == 'C')
            {
                $row_pdo = $res_pdo->rowCount();
                //$pdo = null;
                return $row_pdo;
            }
            //\db()->query("COMMIT;");
            //$conex = \db()->prepare("COMMIT;");$conex->execute();
            //$pdo = null;

        } catch (PDOException $e) {
                if($e->errorInfo[0]==40001 /*(ISO/ANSI) Serialization failure, e.g. timeout or deadlock*/
                    && $pdoDBHandle->getAttribute(\PDO::ATTR_DRIVER_NAME)=="mysql"
                    && $e->errorInfo[1]==1213  /*(MySQL SQLSTATE) ER_LOCK_DEADLOCK*/)
                {
                    $res_pdo = \db()->prepare($sql);
                    if(count($campos_array) == 0)
                        $res_pdo->execute();
                    else
                        $res_pdo->execute($campos_array);
                    //$pdo = null;
                }
                else
                {
                    throw $e;
                    //$pdo = null;
                }

            if($msj_sql_error == '') $msj_sql_error = 'Error en consulta';
             echo '$msj_sql_error: ' . $e->getMessage().' \n\n'.$sql;
             //$pdo = null;
             //\db()->query("ROLLBACK;");
            //$conex = \db()->prepare("ROLLBACK;");$conex->execute();

        }
    }

    public function importarRL()
    {
        $errores = [];
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);
        //\db()->query("BEGIN;");
        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }

        $xlsx = new SimpleXLSX( $file );
        $ga = new \OrdenCompra\OrdenCompra();

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $pdo = \db();

        $recepcion_por_cajas = 0;
        //$sql = "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'recepcion_por_cajas'";
        //if(!$res_cajas = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
        //if(mysqli_num_rows($res_cajas) > 0)
        //{
        //  $row_cajas = mysqli_fetch_assoc($res_cajas);
        //  $recepcion_por_cajas = $row_cajas['Valor'];
        //}
/*
        $sql = "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'recepcion_por_cajas'";
        $row_cajas = $this->fpdo($pdo, $sql, '', 'F', 'Error en Consulta');
        $recepcion_por_cajas = $row_cajas['Valor'];
*/
        $linea = 1; $productos = 0;
        $lineas = $xlsx->rows();

        $bl_array = array();
        $lp_array = array();
        $id_contenedor_array = array();

        $almacen                   = $_POST['almacenes'];
        $empresa                   = $_POST['empresa'];
        $proveedor                 = $_POST['proveedor'];
        $usuario                   = $_POST['txtUsuario'];
        $cve_ubicacion             = $_POST['zonarecepcioni'];
        $protocol                  = $_POST['Protocol'];
        $consecut                  = $_POST['Consecut'];
        $factura_remision          = trim($_POST['factura_remision']);
        $tipo_cambio               = ($_POST['tipo_cambio'] > 0)?($_POST['tipo_cambio']):(1);
        $palletizar_entrada        = $_POST['palletizar_entrada'];
        $sobreescribir_existencias = $_POST['sobreescribir_existencias'];
        $convertir_a_oc            = $_POST['convertir_a_oc'];
        $mandar_a_rtm              = $_POST['mandar_a_rtm'];
        $factura_oc                = trim($_POST['factura_oc']);
        $proyecto                  = $_POST['claveproyecto'];
        $ultima_caja               = "::::";
        $ultimo_folio_caja         = "";




        //$sql_entradas = "Almacen = ".$almacen." | Proveedor = ".$proveedor." | usuario = ".$usuario." | cve_ubicacion = ".$cve_ubicacion." | protocol = ".$protocol." | consecut = ".$consecut." | palletizar_entrada = ".$palletizar_entrada." | sobreescribir_existencias = ".$sobreescribir_existencias." | convertir_a_oc = ".$convertir_a_oc." | factura_oc = ".$factura_oc;

        $arr_almacen = explode("**WMS-WMS**", $almacen);
        $clave_almacen = $arr_almacen[0];
        $id_almacen = $arr_almacen[1];

        //$sql_charset = "SET NAMES 'utf8mb4';";
        //$rs_charset = mysqli_query($conn, $sql_charset);
        $sql_BL = "";
        $procesar_entrada = true; $bls_unicos_por_lp = true; $lp_unico = true; $primer_lp_distinto = ""; $primer_bl_distinto = "";
        $n_cambio_lp = 0; $n_cambio_bl = 0; $bl_no_existente = ""; $lp_ocupado = "";$numerodeorden_ocupado = ""; $num_ord_disponible = true;
        $factura_disponible = true; $factura_ocupada = ""; $bl_ant = ""; $articulo_existente = true; $articulo_no_existente = "";
        $cajas_arr = array();
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }
            $lp = trim($this->pSQL($row[self::LP]));
            $bl = trim($this->pSQL($row[self::BL]));
            $caja = trim($this->pSQL($row[self::CAJA]));
            $cve_articulo    = trim($this->pSQL($row[self::CVE_ARTICULO]));
            $cantidad        = trim($this->pSQL($row[self::EXISTENCIA]));

            if($caja != '' && !is_numeric($caja)) $cajas_arr[] = $caja;

            if(!$ga->VerificarFacturaOC_ERP_Repetido($factura_oc) && $convertir_a_oc == 1)
            {
                $numerodeorden_ocupado = $factura_oc;
                $num_ord_disponible = false;
                $procesar_entrada = false;
                break;
            }

            if(!$ga->VerificarFacturaEntrada_Repetida($factura_remision) && $convertir_a_oc == 1)
            {
                $factura_ocupada = $factura_remision;
                $factura_disponible = false;
                $procesar_entrada = false;
                break;
            }

            $sql = "SELECT COUNT(*) AS existe FROM c_articulo a, Rel_Articulo_Almacen r  WHERE a.cve_articulo = :cve_articulo AND a.cve_articulo = r.Cve_Articulo AND r.Cve_Almac = :id_almacen";
            $resul = $this->fpdo($pdo, $sql, array('cve_articulo' => $cve_articulo, 'id_almacen' => $id_almacen), 'F', '');
            $existe_articulo = $resul['existe'];

            if(!$existe_articulo && $cve_articulo != '' && $cantidad != '')
            {
                $articulo_no_existente = $cve_articulo;
                $procesar_entrada = false;
                break;
            }


            if($lp != '')
            {
                //$sql = "SELECT COUNT(*) AS ocupado 
                //        FROM td_entalmacenxtarima et 
                //        INNER JOIN c_charolas ch ON ch.clave_contenedor = et.ClaveEtiqueta AND ch.cve_almac = $id_almacen
                //        WHERE ch.CveLP = '{$lp}'";
                //$rs = mysqli_query($conn, $sql);
                //$row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $sql = "SELECT COUNT(*) AS ocupado 
                        FROM td_entalmacenxtarima et 
                        INNER JOIN c_charolas ch ON ch.clave_contenedor = et.ClaveEtiqueta AND ch.cve_almac = :id_almacen
                        WHERE ch.CveLP = :lp";
                $row_max = $this->fpdo($pdo, $sql, array('id_almacen'=>$id_almacen, 'lp'=>$lp), 'F', '');
                $ocupado = $row_max["ocupado"];

                $existe = 1;
                //if($bl_ant != $bl)
                //{
                    //$sql = "SELECT COUNT(*) AS existe
                    //        FROM c_ubicacion u
                    //        INNER JOIN c_almacen a ON a.cve_almac = u.cve_almac
                    //        WHERE u.CodigoCSD = CONVERT('{$bl}', CHAR) AND a.cve_almacenp = $id_almacen AND u.Activo = 1";
                    //$rs = mysqli_query($conn, $sql);
                    //$row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                if($mandar_a_rtm == 0)
                {
                    $sql = "SELECT COUNT(*) AS existe
                            FROM c_ubicacion u
                            INNER JOIN c_almacen a ON a.cve_almac = u.cve_almac
                            WHERE u.CodigoCSD = CONVERT(:bl, CHAR) AND a.cve_almacenp = :id_almacen AND u.Activo = 1";
                    $row_max = $this->fpdo($pdo, $sql, array('bl'=>$bl, 'id_almacen'=>$id_almacen), 'F', '');
                    $existe = $row_max["existe"];
                    $sql_BL = "SELECT COUNT(*) AS existe
                            FROM c_ubicacion u
                            INNER JOIN c_almacen a ON a.cve_almac = u.cve_almac
                            WHERE u.CodigoCSD = CONVERT('$bl', CHAR) AND a.cve_almacenp = $id_almacen AND u.Activo = 1";
                    //$bl_ant = $bl;
                //}

                if($existe == 0)
                {
                    $bl_no_existente = $bl;
                    $procesar_entrada = false;
                    break;
                }
                }

                if($ocupado > 0)
                {
                    $lp_unico = false;
                    $lp_ocupado = $lp;
                    $procesar_entrada = false;
                    break;
                }

                if($lp != $primer_lp_distinto)
                {
                   $primer_lp_distinto = $lp;
                   $primer_bl_distinto = "";
                   $n_cambio_lp = 0;
                   $n_cambio_bl = 0;
                }
                else
                    $n_cambio_lp++;

                if($bl != $primer_bl_distinto && $mandar_a_rtm == 0)
                {
                   $primer_bl_distinto = $bl;
                   //$n_cambio_lp = 0;
                   //$n_cambio_bl = 0;
                }
                else if($mandar_a_rtm == 0)
                    $n_cambio_bl++;

                if($n_cambio_bl != $n_cambio_lp)
                {
                    $bls_unicos_por_lp = false;
                    $procesar_entrada = false;
                    $n_cambio_lp = 0;
                    $n_cambio_bl = 0;
                    break;
                }
            }
            else if($bl != '' && $mandar_a_rtm == 0)
            {
                $existe = 1;
                //if($bl_ant != $bl)
                //{
                //$sql = "SELECT COUNT(*) AS existe
                //        FROM c_ubicacion u
                //        INNER JOIN c_almacen a ON a.cve_almac = u.cve_almac
                //        WHERE u.CodigoCSD = CONVERT('{$bl}', CHAR) AND a.cve_almacenp = $id_almacen AND u.Activo = 1";
                //$rs = mysqli_query($conn, $sql);
                //$row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);

                $sql = "SELECT COUNT(*) AS existe
                        FROM c_ubicacion u
                        INNER JOIN c_almacen a ON a.cve_almac = u.cve_almac
                        WHERE u.CodigoCSD = CONVERT(:bl, CHAR) AND a.cve_almacenp = :id_almacen AND u.Activo = 1";
                $row_max = $this->fpdo($pdo, $sql, array('bl'=>$bl, 'id_almacen'=>$id_almacen), 'F', '');
                $existe = $row_max["existe"];
                //    $bl_ant = $bl;
                //}

                if($existe == 0)
                {
                    $bl_no_existente = $bl;
                    $procesar_entrada = false;
                    break;
                }
            }
        }

        $cajas_usadas = "";
        if(count($cajas_arr) > 0)
        {
            $cajas_usadas = implode("','", $cajas_arr);
            $cajas_usadas = "'".$cajas_usadas."'";

            $sql = "SELECT GROUP_CONCAT(DISTINCT cj.CveLP SEPARATOR ', ') as cajas_usadas FROM (
                    SELECT CveLP FROM c_charolas where CveLP IN (:cajas_usadas) AND IDContenedor IN (select Id_Caja from ts_existenciacajas) 
                    UNION
                    SELECT CveLP FROM c_charolas where CveLP IN (:cajas_usadas) AND IDContenedor IN (select Id_Caja from td_entalmacencaja) 
                    ) as cj";
            $row_cajas = $this->fpdo($pdo, $sql, array('cajas_usadas'=>$cajas_usadas), 'F', '');
            $cajas_usadas = $row_cajas["cajas_usadas"];
            if($cajas_usadas != '') $procesar_entrada = false;
        }

        //$idy_ubica_ant = "::dif2:::"; $es_mixto_ant = ""; $idy_ubica = ":::dif:::"; $es_mixto = "";
        if($procesar_entrada)
        {//if($procesar_entrada)
        $linea = 1; $productos = 0;
        $num_pedimento = "";
        //if($convertir_a_oc == 1)
        //{
            $sql = "SELECT IFNULL(MAX(num_pedimento), 0) AS Maximo FROM th_aduana";
            //$rs = mysqli_query($conn, $sql);
            //$row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $row_max = $this->fpdo($pdo, $sql, '', 'F', '');
            $num_pedimento = $row_max["Maximo"]+1;
        //}

        $arr_proveedor = explode("-", $proveedor);
        $clave_proveedor = $arr_proveedor[0];
        $id_proveedor = $arr_proveedor[1];

        $arr_empresa = explode("-", $empresa);
        $clave_empresa = $arr_empresa[0];
        $id_empresa = $arr_empresa[1];

        $tipoentrada = 'RL';
        if($convertir_a_oc == 1) $tipoentrada = 'OC';

        $sql = "INSERT IGNORE INTO th_entalmacen(Cve_Almac, Fec_Entrada, Cve_Proveedor, STATUS, Cve_Usuario, Cve_Autorizado, tipo, HoraInicio, ID_Protocolo, Consec_protocolo, cve_ubicacion, HoraFin, id_ocompra, Fol_OEP, Fact_Prov, TipoCambioSAP, Proyecto) VALUES('{$clave_almacen}', NOW(), {$id_proveedor}, 'E', '{$usuario}', '{$usuario}', '$tipoentrada', NOW(), '{$protocol}', {$consecut}, '{$cve_ubicacion}', NOW(), '{$num_pedimento}', '{$factura_oc}', '{$factura_remision}', {$tipo_cambio}, '{$proyecto}');";
        //echo $sql;
        //exit;
        //$rs = mysqli_query($conn, $sql);
        $this->fpdo($pdo, $sql, '', 'E', '');

        //if($convertir_a_oc == 1)
        //{
        $sql = "INSERT IGNORE INTO th_aduana(num_pedimento, fech_pedimento, Factura, fech_llegPed, STATUS, ID_Proveedor, ID_Protocolo, Consec_protocolo, cve_usuario, Cve_Almac, procedimiento, Tipo_Cambio) (SELECT id_ocompra, NOW(), Fol_OEP, NOW(), 'T', $id_empresa, ID_Protocolo, Consec_protocolo, Cve_Usuario, Cve_Almac, '$clave_proveedor', $tipo_cambio FROM th_entalmacen WHERE id_ocompra = {$num_pedimento} ORDER BY Fec_Entrada DESC LIMIT 1)";
        //$rs = mysqli_query($conn, $sql);
        $this->fpdo($pdo, $sql, '', 'E', '');
        //}

        //$sql_entradas = $sql; 

        $sql = "SELECT MAX(Fol_Folio) as Folio FROM th_entalmacen";
        //$rs = mysqli_query($conn, $sql);
        //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $resul = $this->fpdo($pdo, $sql, '', 'F', '');
        $Folio = $resul['Folio'];

        //$sql = "UPDATE t_protocolo SET FOLIO = {$consecut} WHERE id = {$protocol}";
        //$rs = mysqli_query($conn, $sql);
        $sql = "UPDATE t_protocolo SET FOLIO = :consecut WHERE id = :protocol";
        $this->fpdo($pdo, $sql, array('consecut' => $consecut, 'protocol' => $protocol), 'E', '');
        $bl_no_existentes = "";
        $articulos_no_existentes = "";

        $sql_entradas = "";

        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }
            $cve_articulo    = trim($this->pSQL($row[self::CVE_ARTICULO]));
            $cve_lote        = trim($this->pSQL($row[self::LOTE]));
            $caducidad       = trim($this->pSQL($row[self::CADUCIDAD]));
            $cantidad        = trim($this->pSQL($row[self::EXISTENCIA]));
            $bl              = trim($this->pSQL($row[self::BL]));
            $lp              = trim($this->pSQL($row[self::LP]));
            $cantidad_pedida = str_replace(",", "", trim($this->pSQL($row[self::CANTIDAD_PEDIDA])));
            $fecha_entrada   = trim($this->pSQL($row[self::FECHA_ENTRADA]));
            $costo_unitario  = trim($this->pSQL($row[self::COSTO_UNITARIO]));
            $factura_art     = trim($this->pSQL($row[self::FACTURA_ART]));
            $lote_alterno    = trim($this->pSQL($row[self::LOTE_ALTERNO]));

            if($cantidad_pedida == "") $cantidad_pedida = $cantidad;
            //$sql_entradas .= $cve_articulo." | ";

            $label_lp = $lp;

            //$sql = "SELECT COUNT(*) as existe FROM c_articulo WHERE cve_articulo = '{$cve_articulo}'";
            //$sql = "SELECT COUNT(*) AS existe FROM c_articulo a, Rel_Articulo_Almacen r  WHERE a.cve_articulo = '{$cve_articulo}' AND a.cve_articulo = r.Cve_Articulo AND r.Cve_Almac = $id_almacen";
            //$rs = mysqli_query($conn, $sql);
            //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

/////////////$sql = "SELECT COUNT(*) AS existe FROM c_articulo a, Rel_Articulo_Almacen r  WHERE a.cve_articulo = :cve_articulo AND a.cve_articulo = r.Cve_Articulo AND r.Cve_Almac = :id_almacen";
/////////////$resul = $this->fpdo($pdo, $sql, array('cve_articulo' => $cve_articulo, 'id_almacen' => $id_almacen), 'F', '');
/////////////$existe_articulo = $resul['existe'];

            $existe_lp = 0;
            if($lp != '')
            {
                //IDContenedor NOT IN (SELECT ntarima FROM ts_existenciatarima) AND
                //$sql = "SELECT IDContenedor FROM c_charolas WHERE  CveLP = '{$lp}' AND TipoGen = 0 AND cve_almac = $id_almacen";
                //$rs = mysqli_query($conn, $sql);
                //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                //if(mysqli_num_rows($rs) > 0)
                //   $existe_lp = $resul['IDContenedor'];

               $sql = "SELECT IDContenedor FROM c_charolas WHERE  CveLP = '{$lp}' AND TipoGen = 0 AND cve_almac = $id_almacen";
               $resul = $this->fpdo($pdo, $sql, array('lp' => $lp, 'id_almacen' => $id_almacen), 'F', '');
               $existe_lp = $resul['IDContenedor'];
               if(!$existe_lp) $existe_lp = 0;
            }

/////////////if(!$existe_articulo) {$articulos_no_existentes .= $cve_articulo."\n";}


/////////////$existe_bl = 1;
            /*
            $sql = "SELECT COUNT(*) as existe FROM c_ubicacion WHERE CodigoCSD = CONVERT('{$bl}', CHAR) AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = $id_almacen) LIMIT 1";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe_bl = $resul['existe'];
            */


/////////////if(!$existe_bl) {$bl_no_existentes .= $bl."\n";}

/////////////if(!$existe_articulo || !$existe_bl) {$linea++; continue;}// || ($lp!="" && $existe_lp > 0)


            //$sql = "SELECT COUNT(*) as existe FROM rel_articulo_proveedor WHERE Cve_Articulo = '{$cve_articulo}' AND Id_Proveedor = '{$id_proveedor}'";
            //$rs = mysqli_query($conn, $sql);
            //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
/////////////$sql = "SELECT COUNT(*) as existe FROM rel_articulo_proveedor WHERE Cve_Articulo = :cve_articulo AND Id_Proveedor = :id_proveedor";
/////////////$resul = $this->fpdo($pdo, $sql, array('cve_articulo' => $cve_articulo, 'id_proveedor' => $id_proveedor), 'F', '');
/////////////$existe_articulo_proveedor = $resul['existe'];

            //$sql_entradas = $sql;

/////////////if($existe_articulo_proveedor == 0)
/////////////{
                //$sql = "INSERT IGNORE INTO rel_articulo_proveedor(Cve_Articulo, Id_Proveedor) VALUES ('{$cve_articulo}','{$id_proveedor}')";
                //$rs = mysqli_query($conn, $sql);
                $sql = "INSERT IGNORE INTO rel_articulo_proveedor(Cve_Articulo, Id_Proveedor) VALUES (:cve_articulo,:id_proveedor)";
                $this->fpdo($pdo, $sql, array('cve_articulo'=>$cve_articulo, 'id_proveedor'=>$id_proveedor), 'E', '');
/////////////}

            //$sql = "SELECT control_lotes, Caduca, control_numero_series FROM c_articulo WHERE cve_articulo = '{$cve_articulo}'";
            //$rs = mysqli_query($conn, $sql);
            //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $sql = "SELECT control_lotes, Caduca, control_numero_series FROM c_articulo WHERE cve_articulo = :cve_articulo";
            $resul = $this->fpdo($pdo, $sql, array('cve_articulo'=>$cve_articulo), 'F', '');
            $control_lotes  = $resul['control_lotes'];
            $caduca         = $resul['Caduca'];
            $control_series = $resul['control_numero_series'];

            if($control_lotes == 'S')
            {
                //$sql_charset = "SET NAMES 'utf8mb4';";
                //$rs_charset = mysqli_query($conn, $sql_charset);

                //$sql = "SELECT COUNT(*) as existe FROM c_lotes WHERE cve_articulo = '{$cve_articulo}' AND Lote = CONVERT('{$cve_lote}', CHAR)";
                //$rs = mysqli_query($conn, $sql);
                //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $sql = "SELECT COUNT(*) as existe FROM c_lotes WHERE cve_articulo = :cve_articulo AND Lote = CONVERT(:cve_lote, CHAR)";
                $resul = $this->fpdo($pdo, $sql, array('cve_articulo'=>$cve_articulo, 'cve_lote'=>$cve_lote), 'F', '');
                $existe  = $resul['existe'];

                if(!$existe && $cve_lote != '')
                {
                    if($caduca == 'S')
                    {
                          //$date=date_create($caducidad);
                          //$caducidad = date_format($date,"Y-m-d");
                    }

                    if(!$caducidad) 
                    {
                        $caducidad = '0000-00-00';
                        //$sql = "INSERT INTO c_lotes(cve_articulo, Lote, Fec_Prod, Lote_Alterno) VALUES ('{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), CURDATE(), CONVERT('{$lote_alterno}', CHAR))";
                        $sql = "INSERT INTO c_lotes(cve_articulo, Lote, Fec_Prod, Lote_Alterno) VALUES (:cve_articulo, CONVERT(:cve_lote, CHAR), CURDATE(), CONVERT(:lote_alterno, CHAR))";
                        $this->fpdo($pdo, $sql, array('cve_articulo'=>$cve_articulo, 'cve_lote' => $cve_lote, 'lote_alterno' => $lote_alterno), 'E', '');
                    }
                    else
                    {
                        //$sql = "INSERT INTO c_lotes(cve_articulo, Lote, Caducidad, Fec_Prod, Lote_Alterno) VALUES ('{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), DATE_FORMAT('{$caducidad}', '%Y-%m-%d'), CURDATE(), CONVERT('{$lote_alterno}', CHAR))";
                        $sql = "INSERT INTO c_lotes(cve_articulo, Lote, Caducidad, Fec_Prod, Lote_Alterno) VALUES (:cve_articulo, CONVERT(:cve_lote, CHAR), DATE_FORMAT(:caducidad, '%Y-%m-%d'), CURDATE(), CONVERT(:lote_alterno, CHAR))";
                        $this->fpdo($pdo, $sql, array('cve_articulo'=>$cve_articulo, 'cve_lote' => $cve_lote, 'caducidad' => $caducidad, 'lote_alterno' => $lote_alterno), 'E', '');
                    }

                    //$rs = mysqli_query($conn, $sql);
                }
            }
            else if($control_series == 'S')
            {
                //$sql = "SELECT COUNT(*) as existe FROM c_serie WHERE cve_articulo = '{$cve_articulo}' AND numero_serie = CONVERT('{$cve_lote}', CHAR)";
                //$rs = mysqli_query($conn, $sql);
                //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $sql = "SELECT COUNT(*) as existe FROM c_serie WHERE cve_articulo = :cve_articulo AND numero_serie = CONVERT(:cve_lote, CHAR)";
                $resul = $this->fpdo($pdo, $sql, array('cve_articulo'=>$cve_articulo, 'cve_lote'=>$cve_lote), 'F', '');
                $existe  = $resul['existe'];

                if(!$existe && $cve_lote != '') 
                {
                    //$sql = "INSERT INTO c_serie(cve_articulo, numero_serie, fecha_ingreso, Cve_Activo) VALUES ('{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), NOW(), CONVERT('{$lote_alterno}', CHAR))";
                    //$rs = mysqli_query($conn, $sql);
                    $sql = "INSERT INTO c_serie(cve_articulo, numero_serie, fecha_ingreso, Cve_Activo) VALUES (:cve_articulo, CONVERT(:cve_lote, CHAR), NOW(), CONVERT(:lote_alterno, CHAR))";
                    $this->fpdo($pdo, $sql, array('cve_articulo'=>$cve_articulo, 'cve_lote' => $cve_lote, 'lote_alterno' => $lote_alterno), 'E', '');
                }
            }else
            {
                $cve_lote = '';
            }


//*************************************************************************************
//*************************************************************************************
            //if($idy_ubica != $idy_ubica_ant)
            //{
                //$sql = "SELECT idy_ubica, IF(AcomodoMixto = '', 'N', IFNULL(AcomodoMixto, 'N')) as es_mixto FROM c_ubicacion WHERE CodigoCSD = CONVERT('{$bl}', CHAR) AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = $id_almacen) LIMIT 1";
                //$rs = mysqli_query($conn, $sql);
                //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $sql = "SELECT idy_ubica, IF(AcomodoMixto = '', 'N', IFNULL(AcomodoMixto, 'N')) as es_mixto FROM c_ubicacion WHERE CodigoCSD = CONVERT(:bl, CHAR) AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = :id_almacen) LIMIT 1";
                $resul = $this->fpdo($pdo, $sql, array('bl'=>$bl, 'id_almacen'=>$id_almacen), 'F', '');
                $idy_ubica = $resul['idy_ubica'];
                $es_mixto = $resul['es_mixto'];

                //$idy_ubica_ant = $idy_ubica;
                //$es_mixto_ant = $es_mixto;
            //}

            //$sql_entradas = $sql;

            $existe = 0; $id_contenedor = "";

            $dicoisa = false;
            //if((strpos($_SERVER['HTTP_HOST'], 'dicoisa') === true)) 
            if($_SERVER['HTTP_HOST'] == 'dicoisa.assistprowms.com')
                $dicoisa = true;

            if(!in_array($bl, $bl_array) || !in_array($label_lp, $lp_array) || $dicoisa == true || $palletizar_entrada)
            {
                //$sql_entradas = $bl."|".$label_lp."|".$palletizar_entrada;
                if($palletizar_entrada || $label_lp)
                {
                    $id_contenedor = $existe_lp;
                    //$sql_entradas = $bl."|".$label_lp."|".$palletizar_entrada."|".$existe_lp;
                    if(!$existe_lp)
                    {
                        $sql = "SELECT (SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1";//AQUI NO VA VALIDACION DE ALMACEN
                        //$rs = mysqli_query($conn, $sql);
                        //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                        $resul = $this->fpdo($pdo, $sql, '', 'F', '');

                        //$sql_entradas = $sql;

                        if(!$resul['id_contenedor']) break;

                        $id_contenedor = $resul['id_contenedor'];
                        $descripcion   = $resul['descripcion'];
                        $tipo          = $resul['tipo'];
                        $alto          = $resul['alto'];
                        $ancho         = $resul['ancho'];
                        $fondo         = $resul['fondo'];
                        $peso          = $resul['peso'];
                        $pesomax       = $resul['pesomax'];
                        $capavol       = $resul['capavol'];

                        $label_lp = $lp;
                        if($lp == "")
                           $label_lp = "LP".str_pad($id_contenedor.$Folio, 9, "0", STR_PAD_LEFT);

                        /*$sql = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) 
                                VALUES ({$id_almacen}, '{$label_lp}', '{$descripcion}', 0, 'Pallet', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
                                */
                        //$rs = mysqli_query($conn, $sql);
                        $sql = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES (:id_almacen, :label_lp, :descripcion, 0, 'Pallet', :alto, :ancho, :fondo, :peso, :pesomax, :capavol, :label_lp, 0)";
                        //echo $sql; exit;
                        $arr = array(
                                'id_almacen' => $id_almacen,
                                'label_lp' => $label_lp,
                                'descripcion' => $descripcion,
                                'alto' => $alto,
                                'ancho' => $ancho,
                                'fondo' => $fondo,
                                'peso' => $peso,
                                'pesomax' => $pesomax,
                                'capavol' => $capavol,
                                'label_lp' => $label_lp
                            );
                        $this->fpdo($pdo, $sql, $arr, 'E', '');
                        //$sql_entradas = $sql;
                    }

                    $sql_id_contenedor = "SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = :label_lp";
                    $resul_id_contenedor = $this->fpdo($pdo, $sql_id_contenedor, array('label_lp'=>$label_lp), 'F', '');
                    $id_contenedor  = $resul_id_contenedor['IDContenedor'];

                    array_push($lp_array, $label_lp);
                    array_push($id_contenedor_array, $id_contenedor);
                }
                if($es_mixto == 'N') array_push($bl_array, $bl);
            }
            else
            {
                $pos = array_search($bl, $bl_array);
                if($palletizar_entrada || $label_lp)
                   $id_contenedor = $id_contenedor_array[$pos];

                $label_lp = $lp;
                if($lp == "")
                   $label_lp = $lp_array[$pos];

            }
//*************************************************************************************
//*************************************************************************************

            if($palletizar_entrada || $label_lp)
            {
                //$sql = "SELECT COUNT(*) AS existe FROM td_entalmacenxtarima WHERE fol_folio = {$Folio} AND cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$cve_lote}', CHAR) AND ClaveEtiqueta = '{$label_lp}'";

                //$sql_entradas = $sql; 
                //$rs = mysqli_query($conn, $sql);
                //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

                $sql = "SELECT COUNT(*) AS existe FROM td_entalmacenxtarima WHERE fol_folio = :Folio AND cve_articulo = :cve_articulo AND cve_lote = CONVERT(:cve_lote, CHAR) AND ClaveEtiqueta = :label_lp";
                $resul = $this->fpdo($pdo, $sql, array('Folio' => $Folio, 'cve_articulo' => $cve_articulo, 'cve_lote'=>$cve_lote, 'label_lp'=>$label_lp));
                $existe = $resul['existe'];

                if(!$existe && ($label_lp || $palletizar_entrada))
                {
                    //$sql = "INSERT INTO td_entalmacenxtarima(fol_folio, cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, Ubicada, PzsXCaja) 
                    //        VALUES ({$Folio}, '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), '{$label_lp}', {$cantidad}, 'S', 1)";
                    //$rs = mysqli_query($conn, $sql);
                    $sql = "INSERT INTO td_entalmacenxtarima(fol_folio, cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, Ubicada, PzsXCaja) 
                            VALUES (:Folio, :cve_articulo, CONVERT(:cve_lote, CHAR), :label_lp, :cantidad, 'S', 1) ON DUPLICATE KEY UPDATE Cantidad = Cantidad + :cantidad";
                    $this->fpdo($pdo, $sql, array('Folio'=> $Folio, 'cve_articulo' => $cve_articulo, 'cve_lote' => $cve_lote, 'label_lp' => $label_lp, 'cantidad' => $cantidad), 'E', '');

                    //if($convertir_a_oc == 1)
                    //{
                    //$sql = "INSERT INTO td_aduanaxtarima(Num_Orden, Cve_Articulo, Cve_Lote, ClaveEtiqueta, Cantidad, Recibida) VALUES ('{$num_pedimento}', '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), '{$label_lp}', {$cantidad}, 'S')";
                    $sql = "INSERT INTO td_aduanaxtarima(Num_Orden, Cve_Articulo, Cve_Lote, ClaveEtiqueta, Cantidad, Recibida) VALUES (:num_pedimento, :cve_articulo, CONVERT(:cve_lote, CHAR), :label_lp, :cantidad, 'S')";
                    $this->fpdo($pdo, $sql, array('num_pedimento'=>$num_pedimento, 'cve_articulo'=>$cve_articulo, 'cve_lote'=>$cve_lote, 'label_lp'=>$label_lp, 'cantidad'=>$cantidad), 'E', '');
                    //$rs = mysqli_query($conn, $sql);

                    //}
                }
                else if($label_lp || $palletizar_entrada)
                {
                    //$sql = "UPDATE td_entalmacenxtarima SET Cantidad = Cantidad + {$cantidad} WHERE fol_folio = {$Folio} AND cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$cve_lote}', CHAR) AND ClaveEtiqueta = '{$label_lp}'";
                    //$rs = mysqli_query($conn, $sql);
                    $sql = "UPDATE td_entalmacenxtarima SET Cantidad = Cantidad + :cantidad WHERE fol_folio = :Folio AND cve_articulo = :cve_articulo AND cve_lote = CONVERT(:cve_lote, CHAR) AND ClaveEtiqueta = :label_lp";
                    $this->fpdo($pdo, $sql, array('cantidad' => $cantidad, 'Folio' => $Folio, 'cve_articulo' => $cve_articulo, 'cve_lote' => $cve_lote, 'label_lp' => $label_lp), 'E', '');
                    //if($convertir_a_oc == 1)
                    //{
                    //$sql = "UPDATE td_aduanaxtarima SET Cantidad = Cantidad + {$cantidad_pedida} WHERE Num_Orden = '{$num_pedimento}' AND Cve_Articulo = '{$cve_articulo}' AND Cve_Lote = CONVERT('{$cve_lote}', CHAR) AND ClaveEtiqueta = '{$label_lp}'";
                    //$rs = mysqli_query($conn, $sql);
                    $sql = "UPDATE td_aduanaxtarima SET Cantidad = Cantidad + :cantidad_pedida WHERE Num_Orden = :num_pedimento AND Cve_Articulo = :cve_articulo AND Cve_Lote = CONVERT(:cve_lote, CHAR) AND ClaveEtiqueta = :label_lp";
                    $this->fpdo($pdo, $sql, array('cantidad_pedida' => $cantidad_pedida, 'num_pedimento' => $num_pedimento, 'cve_articulo' => $cve_articulo, 'cve_lote' => $cve_lote, 'label_lp' => $label_lp), 'E', '');
                    //}
                }
            }
 

            $recibir_por_caja = trim($this->pSQL($row[self::CAJA]));
            if($recibir_por_caja != '')
            {
//**************************************************************************************************************************
//                                              INSERTAR EN td_entalmacencaja POR CANTIDAD IMPORTADOR
//**************************************************************************************************************************
                  $clave_articulo = $cve_articulo;
                  $lote = $cve_lote;
                  $lote = trim($lote);

                  $cambiar_folio_caja = false;
                  if($recibir_por_caja != $ultima_caja)
                  {
                    $ultima_caja = $recibir_por_caja;
                    $cambiar_folio_caja = true;
                  }

                  /*
                  $sql = "SELECT IFNULL(num_multiplo, 1) as multiplo FROM c_articulo WHERE cve_articulo = '{$clave_articulo}'";
                  if(!$res_multiplo = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  $row_multiplo = mysqli_fetch_assoc($res_multiplo);
                  $pzasxcaja = $row_multiplo['multiplo'];
                  if($pzasxcaja == 0) $pzasxcaja = 1;
                  $n_cajas = $cantidad_pedida/$pzasxcaja;
                  */
                  //for($n = 0; $n < $n_cajas; $n++)
                  //{
                  //$sql ="SELECT AUTO_INCREMENT  AS id FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'c_charolas'";
                if($cambiar_folio_caja)
                {
                  if(is_numeric($recibir_por_caja))
                  {
                      //ESTO ES PARA AUTOGENERAR LA ETIQUETA DE LA CAJA
                      $sql = "SELECT (MAX(IFNULL(IDContenedor, 0))+1) as nextid FROM c_charolas";
                      //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                      //$row_autoid = mysqli_fetch_assoc($res_id);
                      $row_autoid = $this->fpdo($pdo, $sql, '', 'F', '');
                      $nextid = $row_autoid['nextid'];
                  }

                  $sql ="SELECT IFNULL(tipo_caja, '') as tipo_caja FROM c_articulo WHERE cve_articulo = '$clave_articulo'";
                  //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  //$row_caja = mysqli_fetch_assoc($res_id);
                  $sql ="SELECT IFNULL(tipo_caja, '') as tipo_caja FROM c_articulo WHERE cve_articulo = :clave_articulo";
                  $row_caja = $this->fpdo($pdo, $sql, array('clave_articulo' => $clave_articulo), 'F', '');
                  $tipo_caja = $row_caja['tipo_caja'];

                  if($tipo_caja != "")
                      $sql ="SELECT * FROM c_tipocaja WHERE id_tipocaja = $tipo_caja";
                  else
                      $sql ="SELECT * FROM c_tipocaja WHERE clave = '1'"; //caja generica

                  //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  //$row_caja = mysqli_fetch_assoc($res_id);
                  $row_caja = $this->fpdo($pdo, $sql, '', 'F', '');

                  $clave_caja = $row_caja['clave'];
                  $descripcion = $row_caja['descripcion'];
                  $alto = $row_caja['alto'];
                  $ancho = $row_caja['ancho'];
                  $largo = $row_caja['largo'];
                  $peso = $row_caja['peso'];
                  if($alto == '') $alto = 0;if($ancho == '') $ancho = 0; if($largo == '') $largo = 0; if($peso == '') $peso = 0;
                  $volumen = $alto*$ancho*$largo;

                    if(is_numeric($recibir_por_caja))
                    {
                      //ESTO ES PARA AUTOGENERAR LA ETIQUETA DE LA CAJA
                     $label_caja = "CJ".str_pad($nextid, 6, "0", STR_PAD_LEFT)."-".$recibir_por_caja;
                    }
                    else 
                        $label_caja = $ultima_caja;

                  $cve_almacen = $clave_almacen;
                  //$sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES((SELECT id FROM c_almacenp WHERE clave = '$cve_almacen'), CONCAT('$clave_caja','-', '$nextid'), '$descripcion', 0, 'Caja', 1, '$alto', '$ancho', '$largo', '$peso', 0, $volumen, '$label_caja', 0)";


                  //$sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES((SELECT id FROM c_almacenp WHERE clave = :cve_almacen), CONCAT(:clave_caja,'-', :nextid), :descripcion, 0, 'Caja', 1, :alto, :ancho, :largo, :peso, 0, :volumen, :label_caja, 0)";
                  $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES((SELECT id FROM c_almacenp WHERE clave = :cve_almacen), :label_caja, :descripcion, 0, 'Caja', 1, :alto, :ancho, :largo, :peso, 0, :volumen, :label_caja, 0)";

                  $arr = array(
                        'cve_almacen' => $cve_almacen,
                        //'clave_caja' => $clave_caja,
                        //'nextid' => $nextid,
                        'descripcion' => $descripcion,
                        'alto' => $alto,
                        'ancho' => $ancho,
                        'largo' => $largo,
                        'peso' => $peso,
                        'volumen' => $volumen,
                        'label_caja' => $label_caja
                  );
                  $this->fpdo($pdo, $sqlGuardar, $arr, 'E', '');
                  //if(!$res_id = mysqli_query($conn, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  //*************************************************************************************
                  //$clave_caja = $clave_caja.'-'.$nextid;
                  $clave_caja = $label_caja;
                  //$sql ="SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$clave_caja'";
                  //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  //$sql ="SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = :clave_caja";
                  $sql ="SELECT IDContenedor FROM c_charolas WHERE CveLP = :clave_caja";
                  $row_caja = $this->fpdo($pdo, $sql, array('clave_caja' => $clave_caja), 'F', '');
                  $id_caja = $row_caja['IDContenedor'];
                  $ultimo_folio_caja = $clave_caja;
                  //*************************************************************************************
                }
                else
                {
                  $clave_caja = $ultimo_folio_caja;
                  //$sql ="SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$clave_caja'";
                  //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  //$row_caja = mysqli_fetch_assoc($res_id);
                  //$id_caja = $row_caja['IDContenedor'];

                  //$sql ="SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = :clave_caja";
                  $sql ="SELECT IDContenedor FROM c_charolas WHERE CveLP = :clave_caja";
                  $row_caja = $this->fpdo($pdo, $sql, array('clave_caja' => $clave_caja), 'F', '');
                  $id_caja = $row_caja['IDContenedor'];
                }

                  $claveEtiqueta = $label_lp;
                  if($claveEtiqueta)
                  {
                    $sql = "INSERT INTO td_entalmacencaja(Fol_Folio, Cve_Almac, Cve_Articulo, Cve_Lote, PzsXCaja, Id_Caja, ClaveEtiqueta) VALUES (:Folio, :cve_almacen, :clave_articulo, :lote, :cantidad, :id_caja, :claveEtiqueta)";
                    $arr = array(
                        'Folio' => $Folio,
                        'cve_almacen' => $cve_almacen,
                        'clave_articulo' => $clave_articulo,
                        'lote' => $lote,
                        'cantidad' => $cantidad,
                        'id_caja' => $id_caja,
                        'claveEtiqueta' => $claveEtiqueta
                    );
                    $this->fpdo($pdo, $sql, $arr, 'E', '');
                  }
                  else
                  {
                        //$sql = "INSERT INTO td_entalmacencaja(Fol_Folio, Cve_Almac, Cve_Articulo, Cve_Lote, PzsXCaja, Id_Caja) VALUES ('$Folio', '$cve_almacen', '$clave_articulo', '$lote', $cantidad, $id_caja)";
                        $sql = "INSERT INTO td_entalmacencaja(Fol_Folio, Cve_Almac, Cve_Articulo, Cve_Lote, PzsXCaja, Id_Caja) VALUES (:Folio, :cve_almacen, :clave_articulo, :lote, :cantidad, :id_caja)";

                        $arr = array(
                            'Folio' => $Folio,
                            'cve_almacen' => $cve_almacen,
                            'clave_articulo' => $clave_articulo,
                            'lote' => $lote,
                            'cantidad' => $cantidad,
                            'id_caja' => $id_caja
                        );
                        $this->fpdo($pdo, $sql, $arr, 'E', '');
                    }

                  //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")".$sql;
                  //}

                  if($idy_ubica && $mandar_a_rtm == 0)
                  {
                      //$sql ="SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$label_lp'";
                      //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                      //$row_caja = mysqli_fetch_assoc($res_id);
                      $sql ="SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = :label_lp";
                      $row_caja = $this->fpdo($pdo, $sql, array('label_lp' => $label_lp), 'F', '');
                      $id_contenedor_cajas = $row_caja['IDContenedor'];

                      if($claveEtiqueta)
                      {
                        //$sql = "INSERT IGNORE INTO ts_existenciacajas (idy_ubica, cve_articulo, cve_lote, PiezasXCaja, Id_Caja, Cve_Almac, nTarima, Id_Pzs) VALUES ('$idy_ubica', '$clave_articulo', '$lote', '$cantidad', $id_caja, $id_almacen, '$id_contenedor_cajas', 0)";
                        $sql = "INSERT IGNORE INTO ts_existenciacajas (idy_ubica, cve_articulo, cve_lote, PiezasXCaja, Id_Caja, Cve_Almac, nTarima, Id_Pzs) VALUES (:idy_ubica, :clave_articulo, :lote, :cantidad, :id_caja, :id_almacen, :id_contenedor_cajas, 0)";
                        $arr = array('idy_ubica' => $idy_ubica,'clave_articulo' => $clave_articulo,'lote' => $lote,'cantidad' => $cantidad,'id_caja' => $id_caja,'id_almacen' => $id_almacen,'id_contenedor_cajas' => $id_contenedor_cajas);
                        $this->fpdo($pdo, $sql, $arr, 'E', '');
                      }
                      else
                      {
                        //$sql = "INSERT IGNORE INTO ts_existenciacajas (idy_ubica, cve_articulo, cve_lote, PiezasXCaja, Id_Caja, Cve_Almac, nTarima, Id_Pzs) VALUES ('$idy_ubica', '$clave_articulo', '$lote', '$cantidad', $id_caja, $id_almacen, NULL, 1)";
                        $sql = "INSERT IGNORE INTO ts_existenciacajas (idy_ubica, cve_articulo, cve_lote, PiezasXCaja, Id_Caja, Cve_Almac, nTarima, Id_Pzs) VALUES (:idy_ubica, :clave_articulo, :lote, :cantidad, :id_caja, :id_almacen, NULL, 1)";
                        $arr = array('idy_ubica' => $idy_ubica,'clave_articulo' => $clave_articulo,'lote' => $lote,'cantidad' => $cantidad,'id_caja' => $id_caja,'id_almacen' => $id_almacen);
                        $this->fpdo($pdo, $sql, $arr, 'E', '');
                      }

                      //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")".$sql;

                      //$sql = "INSERT INTO t_MovCharolas(Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status, EsCaja)
                      //      VALUES('{$clave_almacen}', {$id_caja}, NOW(), '{$cve_ubicacion}', '{$idy_ubica}', 2, '{$usuario}', 'I', 'S')";
                      //$rs = mysqli_query($conn, $sql);

                        $sql = "INSERT INTO t_MovCharolas(Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status, EsCaja) VALUES(:clave_almacen, :id_caja, NOW(), :cve_ubicacion, :idy_ubica, 2, :usuario, 'I', 'S')";
                        $arr = array(
                            'clave_almacen' => $clave_almacen,
                            'id_caja' => $id_caja,
                            'cve_ubicacion' => $cve_ubicacion,
                            'idy_ubica' => $idy_ubica,
                            'usuario' => $usuario
                        );
                        $this->fpdo($pdo, $sql, $arr, 'E', '');
                  }

                  //$sql = "INSERT INTO t_MovCharolas(Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status, EsCaja)
                  //      VALUES('{$clave_almacen}', {$id_caja}, NOW(), '{$Folio}', '{$cve_ubicacion}', 1, '{$usuario}', 'I', 'S')";
                    $sql = "INSERT INTO t_MovCharolas(Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status, EsCaja) 
                        VALUES(:clave_almacen, :id_caja, NOW(), :Folio, :cve_ubicacion, 1, :usuario, 'I', 'S')";
                        //$rs = mysqli_query($conn, $sql);
                    $arr = array(
                        'clave_almacen' => $clave_almacen,
                        'id_caja' => $id_caja,
                        'Folio' => $Folio,
                        'cve_ubicacion' => $cve_ubicacion,
                        'usuario' => $usuario
                    );
                    $this->fpdo($pdo, $sql, $arr, 'E', '');

//**************************************************************************************************************************
//**************************************************************************************************************************
            }
            else if($recepcion_por_cajas)
            {
//**************************************************************************************************************************
//                                              INSERTAR EN td_entalmacencaja POR NUM_MULTIPLO
//**************************************************************************************************************************
                  $clave_articulo = $cve_articulo;
                  $lote = $cve_lote;
                  $lote = trim($lote);

                  //$sql = "SELECT IFNULL(num_multiplo, 1) as multiplo FROM c_articulo WHERE cve_articulo = '{$clave_articulo}'";
                  //if(!$res_multiplo = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  //$row_multiplo = mysqli_fetch_assoc($res_multiplo);
                  $sql = "SELECT IFNULL(num_multiplo, 1) as multiplo FROM c_articulo WHERE cve_articulo = :clave_articulo";
                  $row_multiplo = $this->fpdo($pdo, $sql, array('clave_articulo'=>$clave_articulo), 'F', '');
                  $pzasxcaja = $row_multiplo['multiplo'];
                  if($pzasxcaja == 0) $pzasxcaja = 1;
                  //echo "cantidad_pedida = ".$cantidad_pedida." - pzasxcaja = ".$pzasxcaja;exit;
                  $n_cajas = $cantidad_pedida/$pzasxcaja;
                  for($n = 0; $n < $n_cajas; $n++)
                  {
                  //$sql ="SELECT AUTO_INCREMENT  AS id FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'c_charolas'";
                  $sql = "SELECT (MAX(IFNULL(IDContenedor, 0))+1) as nextid FROM c_charolas";
                  //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  //$row_autoid = mysqli_fetch_assoc($res_id);
                  $row_autoid = $this->fpdo($pdo, $sql, '', 'F', '');
                  $nextid = $row_autoid['nextid'];

                  $sql ="SELECT IFNULL(tipo_caja, '') as tipo_caja FROM c_articulo WHERE cve_articulo = '$clave_articulo'";

                  //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  //$row_caja = mysqli_fetch_assoc($res_id);
                  $sql ="SELECT IFNULL(tipo_caja, '') as tipo_caja FROM c_articulo WHERE cve_articulo = :clave_articulo";
                  $row_caja = $this->fpdo($pdo, $sql, array('clave_articulo'=>$clave_articulo), 'F', '');
                  $tipo_caja = $row_caja['tipo_caja'];

                  if($tipo_caja != "")
                      $sql ="SELECT * FROM c_tipocaja WHERE id_tipocaja = $tipo_caja";
                  else
                      $sql ="SELECT * FROM c_tipocaja WHERE clave = '1'"; //caja generica

                  //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  //$row_caja = mysqli_fetch_assoc($res_id);
                  $row_caja = $this->fpdo($pdo, $sql, '', 'F', '');

                  $clave_caja = $row_caja['clave'];
                  $descripcion = $row_caja['descripcion'];
                  $alto = $row_caja['alto'];
                  $ancho = $row_caja['ancho'];
                  $largo = $row_caja['largo'];
                  $peso = $row_caja['peso'];
                  if($alto == '') $alto = 0;if($ancho == '') $ancho = 0; if($largo == '') $largo = 0; if($peso == '') $peso = 0;
                  $volumen = $alto*$ancho*$largo;

                     $label_caja = "CJ".str_pad($nextid, 6, "0", STR_PAD_LEFT);
                  $cve_almacen = $clave_almacen;
                  //$sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES((SELECT id FROM c_almacenp WHERE clave = '$cve_almacen'), CONCAT('$clave_caja','-', '$nextid'), '$descripcion', 0, 'Caja', 1, '$alto', '$ancho', '$largo', '$peso', 0, $volumen, '$label_caja', 0)";
                  $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES((SELECT id FROM c_almacenp WHERE clave = :cve_almacen), CONCAT(:clave_caja,'-', :nextid), :descripcion, 0, 'Caja', 1, :alto, :ancho, :largo, :peso, 0, :volumen, :label_caja, 0)";
                  $arr = array(
                        'cve_almacen' => $cve_almacen,
                        'clave_caja' => $clave_caja,
                        'nextid' => $nextid,
                        'descripcion' => $descripcion,
                        'alto' => $alto,
                        'ancho' => $ancho,
                        'largo' => $largo,
                        'peso' => $peso,
                        'volumen' => $volumen,
                        'label_caja' => $label_caja
                  );
                  $this->fpdo($pdo, $sqlGuardar, $arr, 'E', '');
                  //if(!$res_id = mysqli_query($conn, $sqlGuardar)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  //*************************************************************************************
                  $clave_caja = $clave_caja.'-'.$nextid;
                  //$sql ="SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$clave_caja'";
                  //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                  //$row_caja = mysqli_fetch_assoc($res_id);
                  $sql ="SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = :clave_caja";
                  $row_caja = $this->fpdo($pdo, $sql, array('clave_caja' => $clave_caja), 'F', '');
                  $id_caja = $row_caja['IDContenedor'];
                  //*************************************************************************************

                  $claveEtiqueta = $label_lp;
                  if($claveEtiqueta)
                  {
                    $sql = "INSERT INTO td_entalmacencaja(Fol_Folio, Cve_Almac, Cve_Articulo, Cve_Lote, PzsXCaja, Id_Caja, ClaveEtiqueta) VALUES (:Folio, :cve_almacen, :clave_articulo, :lote,:pzasxcaja :id_caja, :claveEtiqueta)";
                    $arr = array(
                        'Folio' => $Folio,
                        'cve_almacen' => $cve_almacen,
                        'clave_articulo' => $clave_articulo,
                        'lote' => $lote,
                        'pzasxcaja' => $pzasxcaja,
                        'id_caja' => $id_caja,
                        'claveEtiqueta' => $claveEtiqueta
                    );
                    $this->fpdo($pdo, $sql, $arr, 'E', '');
                  }
                  else
                  {
                    //$sql = "INSERT INTO td_entalmacencaja(Fol_Folio, Cve_Almac, Cve_Articulo, Cve_Lote, PzsXCaja, Id_Caja) VALUES ('$Folio', '$cve_almacen', '$clave_articulo', '$lote', $pzasxcaja, $id_caja)";
                    $sql = "INSERT INTO td_entalmacencaja(Fol_Folio, Cve_Almac, Cve_Articulo, Cve_Lote, PzsXCaja, Id_Caja) VALUES (:Folio, :cve_almacen, :clave_articulo, :lote, :pzasxcaja, :id_caja)";
                    $arr = array(
                        'Folio' => $Folio,
                        'cve_almacen' => $cve_almacen,
                        'clave_articulo' => $clave_articulo,
                        'lote' => $lote,
                        'pzasxcaja' => $pzasxcaja,
                        'id_caja' => $id_caja
                    );

                    $this->fpdo($pdo, $sql, $arr, 'E', '');
                  }

                    //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")".$sql;
                  }

                  if($idy_ubica && $mandar_a_rtm == 0)
                  {
                      //$sql ="SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$label_lp'";
                      $sql ="SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = :label_lp";
                      //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")";
                      //$row_caja = mysqli_fetch_assoc($res_id);
                      $row_caja = $this->fpdo($pdo, $sql, array('label_lp'=>$label_lp), 'F', '');
                      $id_contenedor_cajas = $row_caja['IDContenedor'];
                      $arr = "";
                      if($claveEtiqueta)
                      {
                        //$sql = "INSERT IGNORE INTO ts_existenciacajas (idy_ubica, cve_articulo, cve_lote, PiezasXCaja, Id_Caja, Cve_Almac, nTarima, Id_Pzs) VALUES ('$idy_ubica', '$clave_articulo', '$lote', '$pzasxcaja', $id_caja, $id_almacen, '$id_contenedor_cajas', 0)";
                        $sql = "INSERT IGNORE INTO ts_existenciacajas (idy_ubica, cve_articulo, cve_lote, PiezasXCaja, Id_Caja, Cve_Almac, nTarima, Id_Pzs) VALUES (:idy_ubica, :clave_articulo, :lote, :pzasxcaja, :id_caja, :id_almacen, :id_contenedor_cajas, 0)";
                        $arr = array(
                            'idy_ubica' => $idy_ubica,
                            'clave_articulo' => $clave_articulo,
                            'lote' => $lote,
                            'pzasxcaja' => $pzasxcaja,
                            'id_caja' => $id_caja,
                            'id_almacen' => $id_almacen,
                            'id_contenedor_cajas' => $id_contenedor_cajas
                        );

                      }
                      else
                      {
                        //$sql = "INSERT IGNORE INTO ts_existenciacajas (idy_ubica, cve_articulo, cve_lote, PiezasXCaja, Id_Caja, Cve_Almac, nTarima, Id_Pzs) VALUES ('$idy_ubica', '$clave_articulo', '$lote', '$pzasxcaja', $id_caja, $id_almacen, NULL, 1)";
                        $sql = "INSERT IGNORE INTO ts_existenciacajas (idy_ubica, cve_articulo, cve_lote, PiezasXCaja, Id_Caja, Cve_Almac, nTarima, Id_Pzs) VALUES (:idy_ubica, :clave_articulo, :lote, :pzasxcaja, :id_caja, :id_almacen, NULL, 1)";
                        $arr = array(
                            'idy_ubica' => $idy_ubica,
                            'clave_articulo' => $clave_articulo,
                            'lote' => $lote,
                            'pzasxcaja' => $pzasxcaja,
                            'id_caja' => $id_caja,
                            'id_almacen' => $id_almacen
                        );
                      }

                      //if(!$res_id = mysqli_query($conn, $sql)) echo "Falló la preparación: (".mysqli_error($conn).")".$sql;
                      $this->fpdo($pdo, $sql, $arr, 'E', '');

                      //$sql = "INSERT INTO t_MovCharolas(Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status, EsCaja)
                      //      VALUES('{$clave_almacen}', {$id_caja}, NOW(), '{$cve_ubicacion}', '{$idy_ubica}', 2, '{$usuario}', 'I', 'S')";
                      //$rs = mysqli_query($conn, $sql);
                      $sql = "INSERT INTO t_MovCharolas(Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status, EsCaja)
                              VALUES(:clave_almacen, :id_caja, NOW(), :cve_ubicacion, :idy_ubica, 2, :usuario, 'I', 'S')";
                      $arr = array(
                            'clave_almacen' => $clave_almacen,
                            'id_caja' => $id_caja,
                            'cve_ubicacion' => $cve_ubicacion,
                            'idy_ubica' => $idy_ubica,
                            'usuario' => $usuario
                      );
                      $this->fpdo($pdo, $sql, $arr, 'E', '');
                  }

                  //$sql = "INSERT INTO t_MovCharolas(Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status, EsCaja)
                  //      VALUES('{$clave_almacen}', {$id_caja}, NOW(), '{$Folio}', '{$cve_ubicacion}', 1, '{$usuario}', 'I', 'S')";
                  //$rs = mysqli_query($conn, $sql);
                    $sql = "INSERT INTO t_MovCharolas(Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status, EsCaja)
                          VALUES('{$clave_almacen}', '{$id_caja}', NOW(), '{$Folio}', '{$cve_ubicacion}', 1, '{$usuario}', 'I', 'S')";
                    $arr = array(
                        'clave_almacen' => $clave_almacen,
                        'id_caja' => $id_caja,
                        'Folio' => $Folio,
                        'cve_ubicacion' => $cve_ubicacion,
                        'usuario' => $usuario
                    );
                    $this->fpdo($pdo, $sql, $arr, 'E', '');
//**************************************************************************************************************************
//**************************************************************************************************************************/
            }

            //$sql = "SELECT COUNT(*) AS existe FROM td_entalmacen WHERE fol_folio = {$Folio} AND cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$cve_lote}', CHAR) AND cve_ubicacion = '{$cve_ubicacion}'";
            //$rs = mysqli_query($conn, $sql);
            //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $sql = "SELECT COUNT(*) AS existe FROM td_entalmacen WHERE fol_folio = {$Folio} AND cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$cve_lote}', CHAR) AND cve_ubicacion = '{$cve_ubicacion}'";
            $resul = $this->fpdo($pdo, $sql, array('Folio' => $Folio, 'cve_articulo' => $cve_articulo, 'cve_lote' => $cve_lote, 'cve_ubicacion' => $cve_ubicacion), 'F', '');
            $existe = $resul['existe'];

            if(!$cantidad) $cantidad = 0;
            if(!$existe)
            {

                if($fecha_entrada != '')
                {
                    //$sql = "INSERT INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadRecibida, CantidadUbicada, cve_usuario, cve_ubicacion, fecha_inicio, fecha_fin, num_orden) 
                      //  VALUES ({$Folio}, '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, 0, '{$usuario}', '{$cve_ubicacion}', '$fecha_entrada', '$fecha_entrada', '{$num_pedimento}')";
                    $sql = "INSERT INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadRecibida, CantidadUbicada, cve_usuario, cve_ubicacion, fecha_inicio, fecha_fin, num_orden) VALUES (:Folio, :cve_articulo, CONVERT(:cve_lote, CHAR), :cantidad, 0, :usuario, :cve_ubicacion,:fecha_entrada, :fecha_entrada, :num_pedimento)";
                    $arr = array(
                        'Folio' => $Folio,
                        'cve_articulo' => $cve_articulo,
                        'cve_lote' => $cve_lote,
                        'cantidad' => $cantidad,
                        'usuario' => $usuario,
                        'cve_ubicacion' => $cve_ubicacion,
                        'fecha_entrada' => $fecha_entrada,
                        'fecha_entrada' => $fecha_entrada,
                        'num_pedimento' => $num_pedimento
                    );
                    $this->fpdo($pdo, $sql, $arr, 'E', '');
                }
                else
                {
                    //$sql = "INSERT INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadRecibida, CantidadUbicada, cve_usuario, cve_ubicacion, fecha_inicio, fecha_fin, num_orden) VALUES ({$Folio}, '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, 0, '{$usuario}', '{$cve_ubicacion}', NOW(), NOW(), '{$num_pedimento}')";
                    $sql = "INSERT INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadRecibida, CantidadUbicada, cve_usuario, cve_ubicacion, fecha_inicio, fecha_fin, num_orden) VALUES (:Folio, :cve_articulo, CONVERT(:cve_lote, CHAR), :cantidad, 0, :usuario, :cve_ubicacion, NOW(), NOW(), :num_pedimento)";
                    $arr = array(
                        'Folio' => $Folio,
                        'cve_articulo' => $cve_articulo,
                        'cve_lote' => $cve_lote,
                        'cantidad' => $cantidad,
                        'usuario' => $usuario,
                        'cve_ubicacion' => $cve_ubicacion,
                        'num_pedimento' => $num_pedimento
                    );
                    $this->fpdo($pdo, $sql, $arr, 'E', '');
                }

                //$rs = mysqli_query($conn, $sql);
                //$this->fpdo($pdo, $sql, '', 'E', '');

                //if($convertir_a_oc == 1)
                //{
                $arr = "";
                if($costo_unitario != '')
                {
                    //$sql = "INSERT INTO td_aduana(cve_articulo, cantidad, cve_lote, num_orden, costo, Factura) VALUES ('{$cve_articulo}', {$cantidad_pedida}, CONVERT('{$cve_lote}', CHAR), '{$num_pedimento}', {$costo_unitario}, '{$factura_art}')";
                    $sql = "INSERT INTO td_aduana(cve_articulo, cantidad, cve_lote, num_orden, costo, Factura) VALUES (:cve_articulo, :cantidad_pedida, CONVERT(:cve_lote, CHAR), :num_pedimento, :costo_unitario, :factura_art)";
                    $arr = array(
                        'cve_articulo' => $cve_articulo,
                        'cantidad_pedida' => $cantidad_pedida,
                        'cve_lote' => $cve_lote,
                        'num_pedimento' => $num_pedimento,
                        'costo_unitario' => $costo_unitario,
                        'factura_art' => $factura_art
                    );
                }
                else
                {
                    $sql = "INSERT INTO td_aduana(cve_articulo, cantidad, cve_lote, num_orden, Factura) VALUES (:cve_articulo, :cantidad_pedida, CONVERT(:cve_lote, CHAR), :num_pedimento, :factura_art)";
                    $arr = array(
                        'cve_articulo' => $cve_articulo,
                        'cantidad_pedida' => $cantidad_pedida,
                        'cve_lote' => $cve_lote,
                        'num_pedimento' => $num_pedimento,
                        'factura_art' => $factura_art
                    );
                }
                //$rs = mysqli_query($conn, $sql);
                $this->fpdo($pdo, $sql, $arr, 'E', '');
                //}

                //$sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                  //      VALUES ('{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), NOW(), '{$Folio}', '{$cve_ubicacion}', {$cantidad}, 1, '{$usuario}', {$id_almacen}, 1, NOW())";
                $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                        VALUES (:cve_articulo, CONVERT(:cve_lote, CHAR), NOW(), :Folio, :cve_ubicacion, :cantidad, 1, :usuario, :id_almacen, 1, NOW())";
                $arr = array(
                    'cve_articulo' => $cve_articulo,
                    'cve_lote' => $cve_lote,
                    'Folio' => $Folio,
                    'cve_ubicacion' => $cve_ubicacion,
                    'cantidad' => $cantidad,
                    'usuario' => $usuario,
                    'id_almacen' => $id_almacen
                );
                //$rs = mysqli_query($conn, $sql);
                $this->fpdo($pdo, $sql, $arr, 'E', '');
                //$this->fpdo($pdo, $sql, '', 'E', '');
                //$rs = \db()->prepare($sql);
                //$rs->execute($arr);

                if($palletizar_entrada || $label_lp)
                {
                    //$sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                    //        VALUES((SELECT MAX(id) FROM t_cardex), '{$clave_almacen}', {$id_contenedor}, NOW(), '{$Folio}', '{$cve_ubicacion}', 1, '{$usuario}', 'I')";
                    $sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                            VALUES((SELECT MAX(id) FROM t_cardex), :clave_almacen, :id_contenedor, NOW(), :Folio, :cve_ubicacion, 1, :usuario, 'I')";
                    //$rs = mysqli_query($conn, $sql);
                    $arr = array(
                        'clave_almacen' => $clave_almacen,
                        'id_contenedor' => $id_contenedor,
                        'Folio' => $Folio,
                        'cve_ubicacion' => $cve_ubicacion,
                        'usuario' => $usuario
                    );
                    $this->fpdo($pdo, $sql, $arr, 'E', '');

                    //$sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, NULL, '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, {$id_contenedor}, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 1)";
                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES (:id_almacen, NULL, :cve_articulo, CONVERT(:cve_lote, CHAR), :cantidad, :id_contenedor, :id_proveedor, :Folio, :num_pedimento, :factura_remision, :factura_oc, :proyecto, 1)";
                    //$rs = mysqli_query($conn, $sql);
                    $arr = array(
                        'id_almacen' => $id_almacen,
                        'cve_articulo' => $cve_articulo,
                        'cve_lote' => $cve_lote,
                        'cantidad' => $cantidad,
                        'id_contenedor' => $id_contenedor,
                        'id_proveedor' => $id_proveedor,
                        'Folio' => $Folio,
                        'num_pedimento' => $num_pedimento,
                        'factura_remision' => $factura_remision,
                        'factura_oc' => $factura_oc,
                        'proyecto' => $proyecto
                    );
                    $this->fpdo($pdo, $sql, $arr, 'E', '');
                }
                else
                {
                    //$sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, NULL, '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, NULL, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 1)";
                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES (:id_almacen, NULL, :cve_articulo, CONVERT(:cve_lote, CHAR), :cantidad, NULL, :id_proveedor, :Folio, :num_pedimento, :factura_remision, :factura_oc, :proyecto, 1)";
                    //$rs = mysqli_query($conn, $sql);
                    $arr = array(
                        'id_almacen' => $id_almacen,
                        'cve_articulo' => $cve_articulo,
                        'cve_lote' => $cve_lote,
                        'cantidad' => $cantidad,
                        'id_proveedor' => $id_proveedor,
                        'Folio' => $Folio,
                        'num_pedimento' => $num_pedimento,
                        'factura_remision' => $factura_remision,
                        'factura_oc' => $factura_oc,
                        'proyecto' => $proyecto
                    );
                    $this->fpdo($pdo, $sql, $arr, 'E', '');
                }

                //$sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                  //      VALUES ('{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), NOW(), '{$cve_ubicacion}', '{$idy_ubica}', {$cantidad}, 2, '{$usuario}', {$id_almacen}, 1, NOW())";
                if($mandar_a_rtm == 0)
                {
                $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                        VALUES (:cve_articulo, CONVERT(:cve_lote, CHAR), NOW(), :cve_ubicacion, :idy_ubica, :cantidad, 2, :usuario, :id_almacen, 1, NOW())";
                $arr = array(
                    'cve_articulo' => $cve_articulo,
                    'cve_lote' => $cve_lote,
                    'cve_ubicacion' => $cve_ubicacion,
                    'idy_ubica' => $idy_ubica,
                    'cantidad' => $cantidad,
                    'usuario' => $usuario,
                    'id_almacen' => $id_almacen
                );
                //$rs = mysqli_query($conn, $sql);
                $this->fpdo($pdo, $sql, $arr, 'E', '');

                if($palletizar_entrada || $label_lp)
                {
                    //$sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                      //      VALUES((SELECT MAX(id) FROM t_cardex), '{$clave_almacen}', {$id_contenedor}, NOW(), '{$cve_ubicacion}', '{$idy_ubica}', 2, '{$usuario}', 'I')";
                    $sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                            VALUES((SELECT MAX(id) FROM t_cardex), :clave_almacen, :id_contenedor, NOW(), :cve_ubicacion, :idy_ubica, 2, :usuario, 'I')";
                    $arr = array(
                        'clave_almacen' => $clave_almacen,
                        'id_contenedor' => $id_contenedor,
                        'cve_ubicacion' => $cve_ubicacion,
                        'idy_ubica' => $idy_ubica,
                        'usuario' => $usuario
                    );
                    //$rs = mysqli_query($conn, $sql);
                    $this->fpdo($pdo, $sql, $arr, 'E', '');

                    //$sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, '{$idy_ubica}', '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, {$id_contenedor}, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 2)";
                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES (:id_almacen, :idy_ubica, :cve_articulo, CONVERT(:cve_lote, CHAR), :cantidad, :id_contenedor, :id_proveedor, :Folio, :num_pedimento, :factura_remision, :factura_oc, :proyecto, 2)";
                    $arr = array(
                        'id_almacen' => $id_almacen, 
                        'idy_ubica' => $idy_ubica, 
                        'cve_articulo' => $cve_articulo, 
                        'cve_lote' => $cve_lote, 
                        'cantidad' => $cantidad, 
                        'id_contenedor' => $id_contenedor, 
                        'id_proveedor' => $id_proveedor, 
                        'Folio' => $Folio, 
                        'num_pedimento' => $num_pedimento, 
                        'factura_remision' => $factura_remision, 
                        'factura_oc' => $factura_oc, 
                        'proyecto' => $proyecto
                    );
                    //$rs = mysqli_query($conn, $sql);
                    $this->fpdo($pdo, $sql, $arr, 'E', '');
                }
                else
                {
                    //$sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, '{$idy_ubica}', '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, NULL, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 2)";
                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES (:id_almacen, :idy_ubica, :cve_articulo, CONVERT(:cve_lote, CHAR), :cantidad, NULL, :id_proveedor, :Folio, :num_pedimento, :factura_remision, :factura_oc, :proyecto, 2)";
                    //$rs = mysqli_query($conn, $sql);
                    $arr = array(
                        'id_almacen' => $id_almacen,
                        'idy_ubica' => $idy_ubica,
                        'cve_articulo' => $cve_articulo,
                        'cve_lote' => $cve_lote,
                        'cantidad' => $cantidad,
                        'id_proveedor' => $id_proveedor,
                        'Folio' => $Folio,
                        'num_pedimento' => $num_pedimento,
                        'factura_remision' => $factura_remision,
                        'factura_oc' => $factura_oc,
                        'proyecto' => $proyecto
                    );
                    $this->fpdo($pdo, $sql, $arr, 'E', '');
                }
                }
            }
            else
            {
                //$sql = "UPDATE td_entalmacen SET CantidadRecibida = CantidadRecibida + {$cantidad} WHERE fol_folio = {$Folio} AND cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$cve_lote}', CHAR) AND cve_ubicacion = '{$cve_ubicacion}'";
                $sql = "UPDATE td_entalmacen SET CantidadRecibida = CantidadRecibida + :cantidad WHERE fol_folio = :Folio AND cve_articulo = :cve_articulo AND cve_lote = CONVERT(:cve_lote, CHAR) AND cve_ubicacion = :cve_ubicacion";
                //$rs = mysqli_query($conn, $sql);
                $arr = array(
                    'cantidad' => $cantidad,
                    'Folio' => $Folio,
                    'cve_articulo' => $cve_articulo,
                    'cve_lote' => $cve_lote,
                    'cve_ubicacion' => $cve_ubicacion
                );
                $this->fpdo($pdo, $sql, $arr, 'E', '');

                //if($convertir_a_oc == 1)
                //{
                //$sql = "UPDATE td_aduana SET cantidad = cantidad + {$cantidad_pedida} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$cve_lote}', CHAR) AND num_orden = '{$num_pedimento}'";
                $sql = "UPDATE td_aduana SET cantidad = cantidad + :cantidad_pedida WHERE cve_articulo = :cve_articulo AND cve_lote = CONVERT(:cve_lote, CHAR) AND num_orden = :num_pedimento";

                $arr = array(
                    'cantidad_pedida' => $cantidad_pedida,
                    'cve_articulo' => $cve_articulo,
                    'cve_lote' => $cve_lote,
                    'num_pedimento' => $num_pedimento
                );

                //$rs = mysqli_query($conn, $sql);
                $this->fpdo($pdo, $sql, $arr, 'E', '');
                //}

                //$sql = "UPDATE t_cardex SET cantidad = cantidad + {$cantidad} WHERE origen = '{$Folio}' AND cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$cve_lote}', CHAR) AND destino = '{$cve_ubicacion}'";

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                if($palletizar_entrada || $label_lp)
                {
                    //$sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                    //        VALUES((SELECT MAX(id) FROM t_cardex), '{$clave_almacen}', {$id_contenedor}, NOW(), '{$Folio}', '{$cve_ubicacion}', 1, '{$usuario}', 'I')";
                    $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                            VALUES (:cve_articulo, CONVERT(:cve_lote, CHAR), NOW(), :Folio, :cve_ubicacion, :cantidad, 1, :usuario, :id_almacen, 1, NOW())";
                    $arr = array(
                        'cve_articulo' => $cve_articulo,
                        'cve_lote' => $cve_lote,
                        'Folio' => $Folio,
                        'cve_ubicacion' => $cve_ubicacion,
                        'cantidad' => $cantidad,
                        'usuario' => $usuario,
                        'id_almacen' => $id_almacen
                    );
                    //$rs = mysqli_query($conn, $sql);
                    $this->fpdo($pdo, $sql, $arr, 'E', '');
                    //$this->fpdo($pdo, $sql, '', 'E', '');
                    //$rs = \db()->prepare($sql);
                    //$rs->execute($arr);

                    $sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                            VALUES((SELECT MAX(id) FROM t_cardex), :clave_almacen, :id_contenedor, NOW(), :Folio, :cve_ubicacion, 1, :usuario, 'I')";
                    //$rs = mysqli_query($conn, $sql);
                    $arr = array(
                        'clave_almacen' => $clave_almacen,
                        'id_contenedor' => $id_contenedor,
                        'Folio' => $Folio,
                        'cve_ubicacion' => $cve_ubicacion,
                        'usuario' => $usuario
                    );
                    $this->fpdo($pdo, $sql, $arr, 'E', '');

                    //$sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, NULL, '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, {$id_contenedor}, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 1)";
                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES (:id_almacen, NULL, :cve_articulo, CONVERT(:cve_lote, CHAR), :cantidad, :id_contenedor, :id_proveedor, :Folio, :num_pedimento, :factura_remision, :factura_oc, :proyecto, 1)";
                    //$rs = mysqli_query($conn, $sql);
                    $arr = array(
                        'id_almacen' => $id_almacen,
                        'cve_articulo' => $cve_articulo,
                        'cve_lote' => $cve_lote,
                        'cantidad' => $cantidad,
                        'id_contenedor' => $id_contenedor,
                        'id_proveedor' => $id_proveedor,
                        'Folio' => $Folio,
                        'num_pedimento' => $num_pedimento,
                        'factura_remision' => $factura_remision,
                        'factura_oc' => $factura_oc,
                        'proyecto' => $proyecto
                    );
                    $this->fpdo($pdo, $sql, $arr, 'E', '');

                    if($mandar_a_rtm == 0)
                    {
                    $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                            VALUES (:cve_articulo, CONVERT(:cve_lote, CHAR), NOW(), :cve_ubicacion, :idy_ubica, :cantidad, 2, :usuario, :id_almacen, 1, NOW())";
                    $arr = array(
                        'cve_articulo' => $cve_articulo,
                        'cve_lote' => $cve_lote,
                        'cve_ubicacion' => $cve_ubicacion,
                        'idy_ubica' => $idy_ubica,
                        'cantidad' => $cantidad,
                        'usuario' => $usuario,
                        'id_almacen' => $id_almacen
                    );
                    //$rs = mysqli_query($conn, $sql);
                    $this->fpdo($pdo, $sql, $arr, 'E', '');

                    //$sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                      //      VALUES((SELECT MAX(id) FROM t_cardex), '{$clave_almacen}', {$id_contenedor}, NOW(), '{$cve_ubicacion}', '{$idy_ubica}', 2, '{$usuario}', 'I')";
                    $sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                            VALUES((SELECT MAX(id) FROM t_cardex), :clave_almacen, :id_contenedor, NOW(), :cve_ubicacion, :idy_ubica, 2, :usuario, 'I')";
                    $arr = array(
                        'clave_almacen' => $clave_almacen,
                        'id_contenedor' => $id_contenedor,
                        'cve_ubicacion' => $cve_ubicacion,
                        'idy_ubica' => $idy_ubica,
                        'usuario' => $usuario
                    );
                    //$rs = mysqli_query($conn, $sql);
                    $this->fpdo($pdo, $sql, $arr, 'E', '');

                    //$sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, '{$idy_ubica}', '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, {$id_contenedor}, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 2)";
                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES (:id_almacen, :idy_ubica, :cve_articulo, CONVERT(:cve_lote, CHAR), :cantidad, :id_contenedor, :id_proveedor, :Folio, :num_pedimento, :factura_remision, :factura_oc, :proyecto, 2)";
                    $arr = array(
                        'id_almacen' => $id_almacen, 
                        'idy_ubica' => $idy_ubica, 
                        'cve_articulo' => $cve_articulo, 
                        'cve_lote' => $cve_lote, 
                        'cantidad' => $cantidad, 
                        'id_contenedor' => $id_contenedor, 
                        'id_proveedor' => $id_proveedor, 
                        'Folio' => $Folio, 
                        'num_pedimento' => $num_pedimento, 
                        'factura_remision' => $factura_remision, 
                        'factura_oc' => $factura_oc, 
                        'proyecto' => $proyecto
                    );
                    //$rs = mysqli_query($conn, $sql);
                    $this->fpdo($pdo, $sql, $arr, 'E', '');
                    }
                }
                else
                {

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                $sql = "UPDATE t_cardex SET cantidad = cantidad + :cantidad WHERE origen = :Folio AND cve_articulo = :cve_articulo AND cve_lote = CONVERT(:cve_lote, CHAR) AND destino = :cve_ubicacion";
                //$rs = mysqli_query($conn, $sql);
                $arr = array(
                    'cantidad' => $cantidad,
                    'Folio' => $Folio,
                    'cve_articulo' => $cve_articulo,
                    'cve_lote' => $cve_lote,
                    'cve_ubicacion' => $cve_ubicacion
                );

                $this->fpdo($pdo, $sql, $arr, 'E', '');

                //$sql = "UPDATE t_cardex SET cantidad = cantidad + {$cantidad} WHERE origen = '{$cve_ubicacion}' AND cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$cve_lote}', CHAR) AND destino = '{$idy_ubica}'";
                $sql = "UPDATE t_cardex SET cantidad = cantidad + :cantidad WHERE origen = :cve_ubicacion AND cve_articulo = :cve_articulo AND cve_lote = CONVERT(:cve_lote, CHAR) AND destino = :idy_ubica";
                //$rs = mysqli_query($conn, $sql);
                $arr = array(
                    'cantidad' => $cantidad,
                    'cve_ubicacion' => $cve_ubicacion,
                    'cve_articulo' => $cve_articulo,
                    'cve_lote' => $cve_lote,
                    'idy_ubica' => $idy_ubica
                );
                $this->fpdo($pdo, $sql, $arr, 'E', '');
                }

/*
//*************************************************************************************************************************

                if($palletizar_entrada || $label_lp)
                {
                    $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                            VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$Folio}', '{$cve_ubicacion}', {$cantidad}, 1, '{$usuario}', {$id_almacen}, 1, NOW())";
                    $rs = mysqli_query($conn, $sql);

                    $sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                            VALUES((SELECT MAX(id) FROM t_cardex), '{$clave_almacen}', {$id_contenedor}, NOW(), '{$Folio}', '{$cve_ubicacion}', 1, '{$usuario}', 'I')";
                    $rs = mysqli_query($conn, $sql);
                    $sql_tracking .= "4-".$sql."\n\n";

                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, NULL, '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, {$id_contenedor}, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 1)";
                    $rs = mysqli_query($conn, $sql);


                    $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                            VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$cve_ubicacion}', '{$idy_ubica}', {$cantidad}, 2, '{$usuario}', {$id_almacen}, 1, NOW())";
                    $rs = mysqli_query($conn, $sql);

                    $sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                            VALUES((SELECT MAX(id) FROM t_cardex), '{$clave_almacen}', {$id_contenedor}, NOW(), '{$cve_ubicacion}', '{$idy_ubica}', 2, '{$usuario}', 'I')";
                    $rs = mysqli_query($conn, $sql);
                    $sql_tracking .= "6-".$sql."\n\n";

                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, '{$idy_ubica}', '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, {$id_contenedor}, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 2)";
                    $rs = mysqli_query($conn, $sql);

                }
                else 
                {
                    $sql = "UPDATE t_cardex SET cantidad = cantidad + {$cantidad} WHERE origen = '{$Folio}' AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND destino = '{$cve_ubicacion}'";
                    $rs = mysqli_query($conn, $sql);

                    $sql = "UPDATE t_cardex SET cantidad = cantidad + {$cantidad} WHERE origen = '{$cve_ubicacion}' AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND destino = '{$idy_ubica}'";
                    $rs = mysqli_query($conn, $sql);
                }
//*************************************************************************************************************************
*/

            }

/*
            $sql = "SELECT COUNT(*) AS existe FROM t_pendienteacomodo WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$cve_ubicacion}' AND ID_Proveedor = {$id_proveedor}";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe = $resul['existe'];

            if(!$existe)
            {
                $sql = "INSERT INTO t_pendienteacomodo(cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) 
                        VALUES ('{$cve_articulo}', '{$cve_lote}', {$cantidad}, '{$cve_ubicacion}', {$id_proveedor})";
                $rs = mysqli_query($conn, $sql);
            }
            else
            {
                $sql = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad + {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$cve_ubicacion}' AND ID_Proveedor = {$id_proveedor}";
                $rs = mysqli_query($conn, $sql);
            }


            //***********************************************************************
            //                              ACOMODAR 
            //***********************************************************************
        
            $sql = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad - {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$cve_ubicacion}' AND ID_Proveedor = {$id_proveedor}";
            $rs = mysqli_query($conn, $sql);
*/
        if($mandar_a_rtm == 0)
        {
            //$sql = "SELECT COUNT(*) AS existe FROM ts_existenciapiezas WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$cve_lote}', CHAR) AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen}";
            $sql = "SELECT COUNT(*) AS existe FROM ts_existenciapiezas WHERE cve_articulo = :cve_articulo AND cve_lote = CONVERT(:cve_lote, CHAR) AND idy_ubica = :idy_ubica AND ID_Proveedor = :id_proveedor AND cve_almac = :id_almacen";

            if($palletizar_entrada || $label_lp)
            {
                //$sql = "SELECT COUNT(*) AS existe FROM ts_existenciatarima WHERE cve_articulo = '{$cve_articulo}' AND lote = CONVERT('{$cve_lote}', CHAR) AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen} AND ntarima = {$id_contenedor}";
                $sql = "SELECT COUNT(*) AS existe FROM ts_existenciatarima WHERE cve_articulo = :cve_articulo AND lote = CONVERT(:cve_lote, CHAR) AND idy_ubica = :idy_ubica AND ID_Proveedor = :id_proveedor AND cve_almac = :id_almacen AND ntarima = :id_contenedor";
                $arr = array(
                    'cve_articulo' => $cve_articulo,
                    'cve_lote' => $cve_lote,
                    'idy_ubica' => $idy_ubica,
                    'id_proveedor' => $id_proveedor,
                    'id_almacen' => $id_almacen,
                    'id_contenedor' => $id_contenedor
                );
            }
            else
            {
                $arr = array(
                    'cve_articulo' => $cve_articulo,
                    'cve_lote' => $cve_lote,
                    'idy_ubica' => $idy_ubica,
                    'id_proveedor' => $id_proveedor,
                    'id_almacen' => $id_almacen
                );
            }
            //$rs = mysqli_query($conn, $sql);
            //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $resul = $this->fpdo($pdo, $sql, $arr, 'F', '');
            $existe = $resul['existe'];

            if(!$existe)
            {
                //$sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) 
                //            VALUES ({$id_almacen}, {$idy_ubica}, '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, {$id_proveedor})";
                $sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) 
                            VALUES (:id_almacen, :idy_ubica, :cve_articulo, CONVERT(:cve_lote, CHAR), :cantidad, :id_proveedor)";

                if($palletizar_entrada || $label_lp)
                {
                    //$sql = "INSERT INTO ts_existenciatarima(cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, ID_Proveedor) 
                      //      VALUES ({$id_almacen}, {$idy_ubica}, '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$Folio}, {$id_contenedor}, 0, {$cantidad}, {$id_proveedor})";
                    $sql = "INSERT INTO ts_existenciatarima(cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, ID_Proveedor) 
                            VALUES (:id_almacen, :idy_ubica, :cve_articulo, CONVERT(:cve_lote, CHAR), :Folio, :id_contenedor, 0, :cantidad, :id_proveedor)";
                    $arr = array(
                        'id_almacen' => $id_almacen,
                        'idy_ubica' => $idy_ubica,
                        'cve_articulo' => $cve_articulo,
                        'cve_lote' => $cve_lote,
                        'Folio' => $Folio,
                        'id_contenedor' => $id_contenedor,
                        'cantidad' => $cantidad,
                        'id_proveedor' => $id_proveedor
                    );
                }
                else
                {
                    $arr = array(
                        'id_almacen' => $id_almacen,
                        'idy_ubica' => $idy_ubica,
                        'cve_articulo' => $cve_articulo,
                        'cve_lote' => $cve_lote,
                        'cantidad' => $cantidad,
                        'id_proveedor' => $id_proveedor
                    );
                }
                //$rs = mysqli_query($conn, $sql);
                //$sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) VALUES ({$id_almacen}, {$idy_ubica}, '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, {$id_proveedor})";
               //echo $sql;exit;
                $this->fpdo($pdo, $sql, $arr, 'E', '');
            }
            else
            {
                //$sql = "UPDATE ts_existenciapiezas SET Existencia = existencia + {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$cve_lote}', CHAR) AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen}";
                $sql = "UPDATE ts_existenciapiezas SET Existencia = existencia + :cantidad WHERE cve_articulo = :cve_articulo AND cve_lote = CONVERT(:cve_lote, CHAR) AND idy_ubica = :idy_ubica AND ID_Proveedor = :id_proveedor AND cve_almac = :id_almacen";

                $arr = array(
                    'cantidad' => $cantidad,
                    'cve_articulo' => $cve_articulo,
                    'cve_lote' => $cve_lote,
                    'idy_ubica' => $idy_ubica,
                    'id_proveedor' => $id_proveedor,
                    'id_almacen' => $id_almacen
                );
                if($sobreescribir_existencias)
                {
                    //$sql = "UPDATE ts_existenciapiezas SET Existencia = {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$cve_lote}', CHAR) AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen}";
                    $sql = "UPDATE ts_existenciapiezas SET Existencia = :cantidad WHERE cve_articulo = :cve_articulo AND cve_lote = CONVERT(:cve_lote, CHAR) AND idy_ubica = :idy_ubica AND ID_Proveedor = :id_proveedor AND cve_almac = :id_almacen";

                    $arr = array(
                        'cantidad' => $cantidad,
                        'cve_articulo' => $cve_articulo,
                        'cve_lote' => $cve_lote,
                        'idy_ubica' => $idy_ubica,
                        'id_proveedor' => $id_proveedor,
                        'id_almacen' => $id_almacen
                    );
                }

                if($palletizar_entrada || $label_lp)
                {
                    //$sql = "UPDATE ts_existenciatarima SET existencia = existencia + {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND lote = CONVERT('{$cve_lote}', CHAR) AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen} AND ntarima = {$id_contenedor}";
                    $sql = "UPDATE ts_existenciatarima SET existencia = existencia + :cantidad WHERE cve_articulo = :cve_articulo AND lote = CONVERT(:cve_lote, CHAR) AND idy_ubica = :idy_ubica AND ID_Proveedor = :id_proveedor AND cve_almac = :id_almacen AND ntarima = :id_contenedor";
                    $arr = array(
                        'cantidad' => $cantidad,
                        'cve_articulo' => $cve_articulo,
                        'cve_lote' => $cve_lote,
                        'idy_ubica' => $idy_ubica,
                        'id_proveedor' => $id_proveedor,
                        'id_almacen' => $id_almacen,
                        'id_contenedor' => $id_contenedor
                    );

                    if($sobreescribir_existencias)
                    {
                        //$sql = "UPDATE ts_existenciatarima SET existencia = {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND lote = CONVERT('{$cve_lote}', CHAR) AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen} AND ntarima = {$id_contenedor}";
                        $sql = "UPDATE ts_existenciatarima SET existencia = :cantidad WHERE cve_articulo = :cve_articulo AND lote = CONVERT(:cve_lote, CHAR) AND idy_ubica = :idy_ubica AND ID_Proveedor = :id_proveedor AND cve_almac = :id_almacen AND ntarima = :id_contenedor";

                        $arr = array(
                            'cantidad' => $cantidad,
                            'cve_articulo' => $cve_articulo,
                            'cve_lote' => $cve_lote,
                            'idy_ubica' => $idy_ubica,
                            'id_proveedor' => $id_proveedor,
                            'id_almacen' => $id_almacen,
                            'id_contenedor' => $id_contenedor
                        );
                    }
                }
                //$rs = mysqli_query($conn, $sql);
                $resul = $this->fpdo($pdo, $sql, $arr, 'E', '');
            }

            //$sql = "UPDATE td_entalmacen SET CantidadUbicada = CantidadUbicada + {$cantidad} WHERE fol_folio = {$Folio} AND cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$cve_lote}', CHAR) AND cve_ubicacion = '{$cve_ubicacion}'";
            $sql = "UPDATE td_entalmacen SET CantidadUbicada = CantidadUbicada + :cantidad WHERE fol_folio = :Folio AND cve_articulo = :cve_articulo AND cve_lote = CONVERT(:cve_lote, CHAR) AND cve_ubicacion = :cve_ubicacion";
            //$rs = mysqli_query($conn, $sql);

            $arr = array(
                'cantidad' => $cantidad,
                'Folio' => $Folio,
                'cve_articulo' => $cve_articulo,
                'cve_lote' => $cve_lote,
                'cve_ubicacion' => $cve_ubicacion
            );
            $this->fpdo($pdo, $sql, $arr, 'E', '');
        }
        else //if($mandar_a_rtm == 1)
        {
            $sql = "INSERT INTO t_pendienteacomodo(cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) 
                    VALUES ('{$cve_articulo}', '{$cve_lote}', {$cantidad}, '{$cve_ubicacion}', '{$id_proveedor}') ON DUPLICATE KEY UPDATE Cantidad = Cantidad + $cantidad";
            $rs = mysqli_query($conn, $sql);

        }
            //***********************************************************************
            //***********************************************************************

            $productos++;
            $linea++;
        }

//if($convertir_a_oc == 1)
//{
        //$sql = "INSERT INTO th_aduana(num_pedimento, fech_pedimento, Factura, fech_llegPed, STATUS, ID_Proveedor, ID_Protocolo, Consec_protocolo, cve_usuario, Cve_Almac, procedimiento) (SELECT id_ocompra, NOW(), Fol_OEP, NOW(), 'T', Cve_Proveedor, ID_Protocolo, Consec_protocolo, Cve_Usuario, Cve_Almac, (SELECT cve_proveedor FROM c_proveedores WHERE ID_Proveedor = th_entalmacen.Cve_Proveedor) AS procedimiento FROM th_entalmacen WHERE id_ocompra = {$num_pedimento})";
/*
        $sql = "INSERT INTO th_aduana(num_pedimento, fech_pedimento, Factura, fech_llegPed, STATUS, ID_Proveedor, ID_Protocolo, Consec_protocolo, cve_usuario, Cve_Almac, procedimiento) (SELECT id_ocompra, NOW(), Fol_OEP, NOW(), 'T', Cve_Proveedor, ID_Protocolo, Consec_protocolo, Cve_Usuario, Cve_Almac, '$clave_empresa' FROM th_entalmacen WHERE id_ocompra = {$num_pedimento} ORDER BY Fec_Entrada DESC LIMIT 1)";
        $rs = mysqli_query($conn, $sql);

        $sql = "INSERT INTO td_aduana(cve_articulo, cantidad, cve_lote, num_orden) (SELECT cve_articulo, CantidadPedida, cve_lote, '{$num_pedimento}' FROM td_entalmacen WHERE num_orden = '{$num_pedimento}')";
        $rs = mysqli_query($conn, $sql);

        $sql = "INSERT INTO td_aduanaxtarima(Num_Orden, Cve_Articulo, Cve_Lote, ClaveEtiqueta, Cantidad, Recibida) (SELECT '{$num_pedimento}', cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, 'S' FROM td_entalmacenxtarima WHERE fol_folio = (SELECT Fol_Folio FROM th_entalmacen WHERE id_ocompra = {$num_pedimento}))";
        $rs = mysqli_query($conn, $sql);
*/
//}

        $mensaje_bl_no_existentes = "";
        $mensaje_articulos_no_existentes = "";

        
        //if($articulos_no_existentes)
        if($articulo_no_existente)
        {
            $mensaje_articulos_no_existentes = "<br>Los siguientes artículos no existen en el sistema: <br><textarea rows='3' style='width:100%;'>".$articulo_no_existente."</textarea>";
            //if (file_exists("tmp/articulos-no-existentes.txt")){
            //$archivo = fopen("/tmp/articulos-no-existentes.txt", "w");
            //fwrite($archivo, PHP_EOL ."$mensaje_articulos_no_existentes");
            //fclose($archivo);
            //}
            //$mensaje_articulos_no_existentes = "\nArtículos no Existentes: <a href='/tmp/articulos-no-existentes.txt' target='_blank'>Descargar</a>";
            //$mensaje_articulos_no_existentes = "\nArtículos no Existentes: <a href='#' id='ver_art_no_ex'>Ver</a>";
        }
        if($bl_no_existentes)
        {
            $mensaje_bl_no_existentes = "<br>Los siguientes BL no existen en el sistema: <br><textarea rows='3' style='width:100%;'>".$bl_no_existentes."</textarea>";
            //if (file_exists("tmp/BL-no-existentes.txt")){
            //$archivo = fopen("/tmp/BL-no-existentes.txt", "w");
            //fwrite($archivo, PHP_EOL ."$mensaje_bl_no_existentes");
            //fclose($archivo);
            //}
            //$mensaje_bl_no_existentes = "\nBL no Existentes: <a href='/tmp/BL-no-existentes.txt' target='_blank'>Descargar</a>";
            //$mensaje_bl_no_existentes = "\nBL no Existentes: <a href='#' id='ver_bl_no_ex'>Ver</a>";
        }
      
        //\db()->query("COMMIT;");
        $pdo = null;
        @mysqli_close($conn);
        $this->response(200, [
            'statusText' =>  "Entrada importada con éxito. Total de Productos: \"{$productos}\" <br><br>".$mensaje_articulos_no_existentes."<br><br>".$mensaje_bl_no_existentes,
            'sql_entradas' => $sql_entradas,
            "success" => 1,
            //'mensaje_articulos_no_existentes' => $mensaje_articulos_no_existentes,
            //'mensaje_bl_no_existentes' => $mensaje_bl_no_existentes,
            "lineas"=>$lineas
        ]);
    }//if($procesar_entrada)
    else
    {
        $pdo = null;
        @mysqli_close($conn);
        $mensaje = "";
        if($articulo_no_existente)
        {
            $mensaje = "El Artículo {$articulo_no_existente} No existe en este almacén";
        }
        else if(!$bls_unicos_por_lp)
        {
            $mensaje = "El LP {$primer_lp_distinto} está ubicado en más de 1 BL";
        }
        else if($bl_no_existente)
        {
            $mensaje = "El BL {$bl_no_existente} No existe en el sistema, está registrado en otro almacén o está inactivo";
        }
        else if(!$lp_unico)
        {
            $mensaje = "El LP {$lp_ocupado} ya se ocupó en otra entrada";
        }
        else if(!$num_ord_disponible)
        {
            $mensaje = "El Numero de Orden de OC {$numerodeorden_ocupado} ya se ocupó en otra OC";
        }
        else if(!$factura_disponible)
        {
            $mensaje = "La Factura {$factura_ocupada} ya está ocupada";
        }
        else if($cajas_usadas)
        {
            $mensaje = "La(s) caja(s) {$cajas_usadas} ya están en uso en entradas y/o existencias";
        }


        $this->response(200, [
            'statusText' =>  $mensaje,
            "success" => 0,
            "bls_unicos_por_lp" => $bls_unicos_por_lp,
            "bl_no_existente" => $bl_no_existente,
            "lp_unico" => $lp_unico,
            "sql_BL" => $sql_BL,
            //'mensaje_articulos_no_existentes' => $mensaje_articulos_no_existentes,
            //'mensaje_bl_no_existentes' => $mensaje_bl_no_existentes,
            "lineas"=>$lineas
        ]);

    }
        $pdo = null;
        @mysqli_close($conn);
    }


    public function importarRLW()
    {
        $errores = [];
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }

        $xlsx = new SimpleXLSX( $file );
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


        $linea = 1; $productos = 0;
        $lineas = $xlsx->rows();

        $bl_array = array();
        $lp_array = array();
        $id_contenedor_array = array();

        $almacen                   = $_POST['almacenes'];
        $empresa                   = $_POST['empresa'];
        $proveedor                 = $_POST['proveedor'];
        $usuario                   = $_POST['txtUsuario'];
        $cve_ubicacion             = $_POST['zonarecepcioni'];
        $protocol                  = $_POST['Protocol'];
        $consecut                  = $_POST['Consecut'];
        $palletizar_entrada        = $_POST['palletizar_entrada'];
        $sobreescribir_existencias = $_POST['sobreescribir_existencias'];
        $convertir_a_oc            = $_POST['convertir_a_oc'];
        $factura_oc                = trim($_POST['factura_oc']);
        $referencia_well           = trim($_POST['referencia_well']);
        $pedimento_well            = trim($_POST['pedimento_well']);
        $proyecto                  = trim($_POST['claveproyecto']);
        $factura_remision          = trim($_POST['factura_remision']);

//$sql_entradas = "Almacen = ".$almacen." | Proveedor = ".$proveedor." | usuario = ".$usuario." | cve_ubicacion = ".$cve_ubicacion." | protocol = ".$protocol." | consecut = ".$consecut." | palletizar_entrada = ".$palletizar_entrada." | sobreescribir_existencias = ".$sobreescribir_existencias." | convertir_a_oc = ".$convertir_a_oc." | factura_oc = ".$factura_oc;

        $arr_almacen = explode("**WMS-WMS**", $almacen);
        $clave_almacen = $arr_almacen[0];
        $id_almacen = $arr_almacen[1];

        $procesar_entrada = true; $bls_unicos_por_lp = true; $lp_unico = true; $primer_lp_distinto = ""; $primer_bl_distinto = "";
        $n_cambio_lp = 0; $n_cambio_bl = 0; $bl_no_existente = ""; $lp_ocupado = ""; $hay_referencia = false; $hay_pedimento = false;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }
            $lp = trim($this->pSQL($row[self::LP_W]));
            $bl = trim($this->pSQL($row[self::BL_W]));

            if($lp != '')
            {
                $sql = "SELECT COUNT(*) AS ocupado 
                        FROM td_entalmacenxtarima et 
                        INNER JOIN c_charolas ch ON ch.clave_contenedor = et.ClaveEtiqueta AND ch.cve_almac = $id_almacen
                        WHERE ch.CveLP = '{$lp}'";
                $rs = mysqli_query($conn, $sql);
                $row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $ocupado = $row_max["ocupado"];

                $sql = "SELECT COUNT(*) AS existe
                        FROM c_ubicacion u
                        INNER JOIN c_almacen a ON a.cve_almac = u.cve_almac
                        WHERE u.CodigoCSD = CONVERT('{$bl}', CHAR) AND a.cve_almacenp = $id_almacen AND u.Activo = 1";
                $rs = mysqli_query($conn, $sql);
                $row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $existe = $row_max["existe"];

                if($existe == 0)
                {
                    $bl_no_existente = $bl;
                    $procesar_entrada = false;
                    break;
                }

                if($ocupado > 0)
                {
                    $lp_unico = false;
                    $lp_ocupado = $lp;
                    $procesar_entrada = false;
                    break;
                }

                if($lp != $primer_lp_distinto)
                {
                   $primer_lp_distinto = $lp;
                   $primer_bl_distinto = "";
                   $n_cambio_lp = 0;
                   $n_cambio_bl = 0;
                }
                else
                    $n_cambio_lp++;

                if($bl != $primer_bl_distinto)
                {
                   $primer_bl_distinto = $bl;
                   //$n_cambio_lp = 0;
                   //$n_cambio_bl = 0;
                }
                else
                    $n_cambio_bl++;

                if($n_cambio_bl != $n_cambio_lp)
                {
                    $bls_unicos_por_lp = false;
                    $procesar_entrada = false;
                    $n_cambio_lp = 0;
                    $n_cambio_bl = 0;
                    break;
                }
            }

            if($referencia_well == '' && !$hay_referencia)
            {
                $referencia_rev = trim($this->pSQL($row[self::REFERENCIA]));
                if($referencia_rev)
                {
                    $hay_referencia = true;
                }
            }
            else 
                $hay_referencia = true;

            if($pedimento_well == '' && !$hay_pedimento)
            {
                $pedimento_rev = trim($this->pSQL($row[self::NUM_PEDIMENTO]));
                if($pedimento_rev)
                {
                    $hay_pedimento = true;
                }
            }
            else 
                $hay_pedimento = true;
        }



        if(!$hay_referencia || !$hay_pedimento)
        {
            $procesar_entrada = false;
        }

        if($procesar_entrada)
        {//if($procesar_entrada)
        $linea = 1; $productos = 0;
        $num_pedimento = "";
        //if($convertir_a_oc == 1)
        //{
            $sql = "SELECT IFNULL(MAX(num_pedimento), 0) AS Maximo FROM th_aduana";
            $rs = mysqli_query($conn, $sql);
            $row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $num_pedimento = $row_max["Maximo"]+1;
        //}

        $arr_proveedor = explode("-", $proveedor);
        $clave_proveedor = $arr_proveedor[0];
        $id_proveedor = $arr_proveedor[1];

        $arr_empresa = explode("-", $empresa);
        $clave_empresa = $arr_empresa[0];
        $id_empresa = $arr_empresa[1];

        //**********************
        //welldex th_aduana
        //**********************
        $recurso = ($referencia_well=='')?(trim($this->pSQL($row[self::REFERENCIA]))):($referencia_well);
        $Pedimento = ($pedimento_well=='')?(trim($this->pSQL($row[self::NUM_PEDIMENTO]))):($pedimento_well);
        $dictamen = trim($this->pSQL($row[self::CVE_PEDIMENTO]));
        $presupuesto = trim($this->pSQL($row[self::NUM_ACUSE]));
        $aduana = trim($this->pSQL($row[self::ADUANA_ES]));
        $dictamenActivo = trim($this->pSQL($row[self::ADUANA_DESPACHO]));
        $AduanaDespacho = trim($this->pSQL($row[self::ADUANA_DESPACHO]));
        $procedimiento = trim($this->pSQL($row[self::CLAVE_IMP_EXP]));
        $areaSolicitante = trim($this->pSQL($row[self::CVE_CLIENTE]));
        $BlMaster = trim($this->pSQL($row[self::BL_MASTER]));
        $BlHouse = trim($this->pSQL($row[self::BL_HOUSE]));
        $numeroContrato = trim($this->pSQL($row[self::NUM_PARTIDA]));
        //**********************
        //welldex th_aduana_extra
        //**********************
        $ClaveDestinatario = trim($this->pSQL($row[self::CVE_DESTINATARIO]));
        $PaisOrigenDestino = trim($this->pSQL($row[self::PAIS_ORIGEN]));
        $PaisVendedorComprador = trim($this->pSQL($row[self::PAIS_VEND_COMP]));
        //**********************

        $sql = "INSERT INTO th_entalmacen(Cve_Almac, Fec_Entrada, Cve_Proveedor, STATUS, Cve_Usuario, Cve_Autorizado, tipo, HoraInicio, ID_Protocolo, Consec_protocolo, cve_ubicacion, HoraFin, id_ocompra, Fol_OEP, Proyecto) VALUES('{$clave_almacen}', NOW(), {$id_proveedor}, 'E', '{$usuario}', '{$usuario}', 'RL', NOW(), '{$protocol}', {$consecut}, '{$cve_ubicacion}', NOW(), '{$num_pedimento}', '{$factura_oc}', '{$proyecto}');";
        $rs = mysqli_query($conn, $sql);

        $sql_tracking = "1-".$sql."\n\n";

        //, '$clave_proveedor'
        if($procedimiento == '') $procedimiento = $clave_proveedor;
        $sql = "INSERT INTO th_aduana(num_pedimento, fech_pedimento, Factura, fech_llegPed, STATUS, ID_Proveedor, ID_Protocolo, Consec_protocolo, cve_usuario, Cve_Almac, recurso, Pedimento, dictamen, presupuesto, aduana, AduanaDespacho, procedimiento, areaSolicitante, BlMaster, BlHouse, numeroContrato) (SELECT id_ocompra, NOW(), Fol_OEP, NOW(), 'T', $id_empresa, ID_Protocolo, Consec_protocolo, Cve_Usuario, Cve_Almac, '$recurso', '$Pedimento', '$dictamen', '$presupuesto', '$aduana', '$AduanaDespacho', '$procedimiento', '$areaSolicitante', '$BlMaster', '$BlHouse', '$numeroContrato' FROM th_entalmacen WHERE id_ocompra = {$num_pedimento} ORDER BY Fec_Entrada DESC LIMIT 1)";
        $rs = mysqli_query($conn, $sql);

        $sql_tracking .= "2-".$sql."\n\n";

        $sql = "INSERT INTO th_aduana_extra(num_pedimento, ClaveDestinatario, PaisOrigenDestino, PaisVendedorComprador) VALUES ('$num_pedimento', '$ClaveDestinatario', '$PaisOrigenDestino', '$PaisVendedorComprador')";
        $rs = mysqli_query($conn, $sql);

        //$sql_entradas = $sql; 

        $sql = "SELECT MAX(Fol_Folio) as Folio FROM th_entalmacen";
        $rs = mysqli_query($conn, $sql);
        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $Folio = $resul['Folio'];

        $sql = "UPDATE t_protocolo SET FOLIO = {$consecut} WHERE id = {$protocol}";
        $rs = mysqli_query($conn, $sql);

        $bl_no_existentes = "";
        $articulos_no_existentes = "";

        $sql_entradas = "";

        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }
            $cve_articulo    = trim($this->pSQL($row[self::CVE_ARTICULO_W]));
            $cve_lote        = trim($this->pSQL($row[self::LOTE_W]));
            $caducidad       = trim($this->pSQL($row[self::CADUCIDAD_W]));
            $cantidad        = str_replace(",", "", trim($this->pSQL($row[self::EXISTENCIA_W])));
            $bl              = trim($this->pSQL($row[self::BL_W]));
            $lp              = trim($this->pSQL($row[self::LP_W]));
            $cantidad_pedida = 0;//$this->pSQL($row[self::CANTIDAD_PEDIDA_W]);

            if($cantidad_pedida == "") $cantidad_pedida = $cantidad;
            //$sql_entradas .= $cve_articulo." | ";

            $label_lp = $lp;

            //$sql = "SELECT COUNT(*) as existe FROM c_articulo WHERE cve_articulo = '{$cve_articulo}'";
            $sql = "SELECT COUNT(*) AS existe FROM c_articulo a, Rel_Articulo_Almacen r  WHERE a.cve_articulo = '{$cve_articulo}' AND a.cve_articulo = r.Cve_Articulo AND r.Cve_Almac = $id_almacen";

            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe_articulo = $resul['existe'];

            $existe_lp = 0;
            if($lp != '')
            {
                //IDContenedor NOT IN (SELECT ntarima FROM ts_existenciatarima) AND
                $sql = "SELECT IDContenedor FROM c_charolas WHERE  CveLP = '{$lp}' AND TipoGen = 0 AND cve_almac = $id_almacen";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                if(mysqli_num_rows($rs) > 0)
                   $existe_lp = $resul['IDContenedor'];
            }

            if(!$existe_articulo) {$articulos_no_existentes .= $cve_articulo."\n";}

            $sql = "SELECT COUNT(*) as existe FROM c_ubicacion WHERE CodigoCSD = CONVERT('{$bl}', CHAR) AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = $id_almacen) LIMIT 1";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe_bl = $resul['existe'];

            if(!$existe_bl) {$bl_no_existentes .= $bl."\n";}

            if(!$existe_articulo || !$existe_bl) {$linea++; continue;}// || ($lp!="" && $existe_lp > 0)


            $sql = "SELECT COUNT(*) as existe FROM rel_articulo_proveedor WHERE Cve_Articulo = '{$cve_articulo}' AND Id_Proveedor = '{$id_proveedor}'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe_articulo_proveedor = $resul['existe'];

            //$sql_entradas = $sql;

            if($existe_articulo_proveedor == 0)
            {
                $sql = "INSERT IGNORE INTO rel_articulo_proveedor(Cve_Articulo, Id_Proveedor) VALUES ('{$cve_articulo}','{$id_proveedor}')";
                $rs = mysqli_query($conn, $sql);
            }

            $sql = "SELECT control_lotes, Caduca, control_numero_series FROM c_articulo WHERE cve_articulo = '{$cve_articulo}'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $control_lotes  = $resul['control_lotes'];
            $caduca         = $resul['Caduca'];
            $control_series = $resul['control_numero_series'];

            if($control_lotes == 'S')
            {
                $sql = "SELECT COUNT(*) as existe FROM c_lotes WHERE cve_articulo = '{$cve_articulo}' AND Lote = '{$cve_lote}'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $existe  = $resul['existe'];

                if(!$existe && $cve_lote != '')
                {
                    if($caduca == 'S')
                    {
                          //$date=date_create($caducidad);
                          //$caducidad = date_format($date,"Y-m-d");
                    }

                    if(!$caducidad) 
                    {
                        $caducidad = '0000-00-00';
                        $sql = "INSERT INTO c_lotes(cve_articulo, Lote, Fec_Prod, Lote_Alterno) VALUES ('{$cve_articulo}', '{$cve_lote}', CURDATE(), CONVERT('{$lote_alterno}', CHAR))";
                    }
                    else
                        $sql = "INSERT INTO c_lotes(cve_articulo, Lote, Caducidad, Fec_Prod, Lote_Alterno) VALUES ('{$cve_articulo}', '{$cve_lote}', DATE_FORMAT('{$caducidad}', '%Y-%m-%d'), CURDATE(), CONVERT('{$lote_alterno}', CHAR))";

                    $rs = mysqli_query($conn, $sql);
                }
            }
            else if($control_series == 'S')
            {
                $sql = "SELECT COUNT(*) as existe FROM c_serie WHERE cve_articulo = '{$cve_articulo}' AND numero_serie = '{$cve_lote}'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $existe  = $resul['existe'];

                if(!$existe && $cve_lote != '') 
                {
                    $sql = "INSERT INTO c_serie(cve_articulo, numero_serie, fecha_ingreso, Cve_Activo) VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), CONVERT('{$lote_alterno}', CHAR))";
                    $rs = mysqli_query($conn, $sql);
                }
            }else
            {
                $cve_lote = '';
            }


//*************************************************************************************
//*************************************************************************************
            $sql = "SELECT idy_ubica, IF(AcomodoMixto = '', 'N', IFNULL(AcomodoMixto, 'N')) as es_mixto FROM c_ubicacion WHERE CodigoCSD = CONVERT('{$bl}', CHAR) AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = $id_almacen) LIMIT 1";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $idy_ubica = $resul['idy_ubica'];
            $es_mixto = $resul['es_mixto'];

            //$sql_entradas = $sql;

            $existe = 0; $id_contenedor = "";

            $dicoisa = false;
            //if((strpos($_SERVER['HTTP_HOST'], 'dicoisa') === true)) 
            if($_SERVER['HTTP_HOST'] == 'dicoisa.assistprowms.com')
                $dicoisa = true;

            if(!in_array($bl, $bl_array) || !in_array($label_lp, $lp_array) || $dicoisa == true || $palletizar_entrada)
            {
                //$sql_entradas = $bl."|".$label_lp."|".$palletizar_entrada;
                if($palletizar_entrada || $label_lp)
                {
                    $id_contenedor = $existe_lp;
                    //$sql_entradas = $bl."|".$label_lp."|".$palletizar_entrada."|".$existe_lp;
                    if(!$existe_lp)
                    {
                        $sql = "SELECT (SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1"; // AQUI NO VA VALIDACION DE ALMACEN
                        $rs = mysqli_query($conn, $sql);
                        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

                        //$sql_entradas = $sql;

                        if(!$resul['id_contenedor']) break;

                        $id_contenedor = $resul['id_contenedor'];
                        $descripcion   = $resul['descripcion'];
                        $tipo          = $resul['tipo'];
                        $alto          = $resul['alto'];
                        $ancho         = $resul['ancho'];
                        $fondo         = $resul['fondo'];
                        $peso          = $resul['peso'];
                        $pesomax       = $resul['pesomax'];
                        $capavol       = $resul['capavol'];

                        $label_lp = $lp;
                        if($lp == "")
                           $label_lp = "LP".str_pad($id_contenedor.$Folio, 9, "0", STR_PAD_LEFT);

                        $sql = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) 
                                VALUES ({$id_almacen}, '{$label_lp}', '{$descripcion}', 0, 'Pallet', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
                        $rs = mysqli_query($conn, $sql);
                        //$sql_entradas = $sql;
                    }

                    array_push($lp_array, $label_lp);
                    array_push($id_contenedor_array, $id_contenedor);
                }
                if($es_mixto == 'N') array_push($bl_array, $bl);
            }
            else
            {
                $pos = array_search($bl, $bl_array);
                if($palletizar_entrada || $lp)
                   $id_contenedor = $id_contenedor_array[$pos];

                $label_lp = $lp;
                if($lp == "")
                   $label_lp = $lp_array[$pos];

            }
//*************************************************************************************
//*************************************************************************************

            if($palletizar_entrada || $label_lp)
            {
                $sql = "SELECT COUNT(*) AS existe FROM td_entalmacenxtarima WHERE fol_folio = {$Folio} AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND ClaveEtiqueta = '{$label_lp}'";

                //$sql_entradas = $sql; 
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $existe = $resul['existe'];

                if(!$existe && ($label_lp || $palletizar_entrada))
                {
                    $sql = "INSERT INTO td_entalmacenxtarima(fol_folio, cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, Ubicada, PzsXCaja) 
                            VALUES ({$Folio}, '{$cve_articulo}', '{$cve_lote}', '{$label_lp}', {$cantidad}, 'S', 1)";
                    $rs = mysqli_query($conn, $sql);

                    $sql = "INSERT INTO td_aduanaxtarima(Num_Orden, Cve_Articulo, Cve_Lote, ClaveEtiqueta, Cantidad, Recibida) VALUES ('{$num_pedimento}', '{$cve_articulo}', '{$cve_lote}', '{$label_lp}', {$cantidad}, 'S')";
                    $rs = mysqli_query($conn, $sql);
                }
                else if($label_lp)
                {
                    $sql = "UPDATE td_entalmacenxtarima SET Cantidad = Cantidad + {$cantidad} WHERE fol_folio = {$Folio} AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND ClaveEtiqueta = '{$label_lp}'";
                    $rs = mysqli_query($conn, $sql);

                    $sql = "UPDATE td_aduanaxtarima SET Cantidad = Cantidad + {$cantidad_pedida} WHERE Num_Orden = '{$num_pedimento}' AND Cve_Articulo = '{$cve_articulo}' AND Cve_Lote = '{$cve_lote}' AND ClaveEtiqueta = '{$label_lp}'";
                    $rs = mysqli_query($conn, $sql);
                }
            }
            $sql = "SELECT COUNT(*) AS existe FROM td_entalmacen WHERE fol_folio = {$Folio} AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$cve_ubicacion}'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe = $resul['existe'];

//BL_W, LP_W, CVE_ARTICULO_W, EXISTENCIA_W, LOTE_W, CADUCIDAD_W, REFERENCIA, NUM_PEDIMENTO, TIPO_OPERACION, CVE_PEDIMENTO, NUM_ACUSE, ADUANA_ES, ADUANA_DESPACHO, PESO_BRUTO, MARCAS_NUM_BULTOS, CLAVE_IMP_EXP, RAZON_SOCIAL_IMP_EXP, CLIENTE, CVE_CLIENTE, DESTINATARIO, CVE_DESTINATARIO, ORDEN_DE_COMPRA, BL_MASTER, BL_HOUSE, CONTENEDOR, NUM_PARTIDA, CONS_FACTURA, FACTURA, FECHA_FACTURA, PROVEEDOR, CVE_PROVEEDOR, PARTE_M3, PARTE, FRACCION, NICO, DESCRIPCION_AA, PAIS_ORIGEN, PAIS_VEND_COMP, CANTIDAD_FACTURA, UM_COMERCIALIZACION, CANT_TARIFA, UM_TARIFA, PRECIO_UNITARIO, VALOR_FACTURA, UM_COVE, CANTIDAD_COVE, VALOR_MERC_COVE, PRECIO_UNIT_COVE, DESC_FACT_COVE, PLACAS_TRANSPORTE,         

            //******************************
            //welldex td_aduana
            //******************************
            $Peso = trim($this->pSQL($row[self::PESO_BRUTO]));
            $MarcaNumTotBultos = trim($this->pSQL($row[self::MARCAS_NUM_BULTOS]));
            $Ref_Docto = trim($this->pSQL($row[self::ORDEN_DE_COMPRA]));
            $Contendores = trim($this->pSQL($row[self::CONTENEDOR]));
            $Item = trim($this->pSQL($row[self::CONS_FACTURA]));
            $Factura = trim($this->pSQL($row[self::FACTURA]));
            $Fec_Factura = trim($this->pSQL($row[self::FECHA_FACTURA]));

            //******************************
            //welldex td_aduana_extra
            //******************************
            $ParteM3 = trim($this->pSQL($row[self::PARTE_M3]));
            $Parte = trim($this->pSQL($row[self::PARTE]));
            $Fraccion = trim($this->pSQL($row[self::FRACCION]));
            $Nico = trim($this->pSQL($row[self::NICO]));
            $Des_AA = trim($this->pSQL($row[self::DESCRIPCION_AA]));
            $CantidadFactura = trim($this->pSQL($row[self::CANTIDAD_FACTURA]));
            $UMComercializacion = trim($this->pSQL($row[self::UM_COMERCIALIZACION]));
            $CantidadTarifa = trim($this->pSQL($row[self::CANT_TARIFA]));
            $UMTarifa = trim($this->pSQL($row[self::UM_TARIFA]));
            $PrecioUnitario = trim($this->pSQL($row[self::PRECIO_UNITARIO]));
            $ValorFactura = trim($this->pSQL($row[self::VALOR_FACTURA]));
            $UMCOVE = trim($this->pSQL($row[self::UM_COVE]));
            $CantidadCOVE = trim($this->pSQL($row[self::CANTIDAD_COVE]));
            $ValorMercanciaCOVE = trim($this->pSQL($row[self::VALOR_MERC_COVE]));
            $PrecioUnitarioCOVE = trim($this->pSQL($row[self::PRECIO_UNIT_COVE]));
            $DescripcionFacturaCOVE = trim($this->pSQL($row[self::DESC_FACT_COVE]));
            $PlacasTranposte = trim($this->pSQL($row[self::PLACAS_TRANSPORTE]));
            //******************************

            if(!$existe)
            {
                $sql = "INSERT INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadRecibida, CantidadUbicada, cve_usuario, cve_ubicacion, fecha_inicio, fecha_fin, num_orden) 
                        VALUES ({$Folio}, '{$cve_articulo}', '{$cve_lote}', {$cantidad}, 0, '{$usuario}', '{$cve_ubicacion}', NOW(), NOW(), '{$num_pedimento}')";
                $rs = mysqli_query($conn, $sql);
                $sql_entradas = $sql;
                

                $sql = "INSERT INTO td_aduana(cve_articulo, cantidad, cve_lote, num_orden, Peso, MarcaNumTotBultos, Ref_Docto, Contendores, Item, Factura, Fec_Factura) VALUES ('{$cve_articulo}', {$cantidad_pedida}, '{$cve_lote}', '{$num_pedimento}', '$Peso', '$MarcaNumTotBultos', '$Ref_Docto', '$Contendores', '$Item', '$Factura', '$Fec_Factura')";
                $rs = mysqli_query($conn, $sql);

                $sql = "INSERT INTO td_aduana_extra(ParteM3, Parte, Fraccion, Nico, Des_AA, CantidadFactura, UMComercializacion, CantidadTarifa, UMTarifa, PrecioUnitario, ValorFactura, UMCOVE, CantidadCOVE, ValorMercanciaCOVE, PrecioUnitarioCOVE, DescripcionFacturaCOVE, PlacasTranposte) VALUES ('$ParteM3', '$Parte', '$Fraccion', '$Nico', '$Des_AA', '$CantidadFactura', '$UMComercializacion', '$CantidadTarifa', '$UMTarifa', '$PrecioUnitario', '$ValorFactura', '$UMCOVE', '$CantidadCOVE', '$ValorMercanciaCOVE', '$PrecioUnitarioCOVE', '$DescripcionFacturaCOVE', '$PlacasTranposte')";
                $rs = mysqli_query($conn, $sql);

                $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                        VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$Folio}', '{$cve_ubicacion}', {$cantidad}, 1, '{$usuario}', {$id_almacen}, 1, NOW())";
                $rs = mysqli_query($conn, $sql);

                $sql_tracking .= "3-".$sql."\n\n";

                if($palletizar_entrada || $label_lp)
                {
                    $sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                            VALUES((SELECT MAX(id) FROM t_cardex), '{$clave_almacen}', {$id_contenedor}, NOW(), '{$Folio}', '{$cve_ubicacion}', 1, '{$usuario}', 'I')";
                    $rs = mysqli_query($conn, $sql);
                    $sql_tracking .= "4-".$sql."\n\n";

                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, NULL, '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, {$id_contenedor}, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 1)";
                    $rs = mysqli_query($conn, $sql);

                }
                else
                {
                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, NULL, '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, NULL, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 1)";
                    $rs = mysqli_query($conn, $sql);
                }

                $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                        VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$cve_ubicacion}', '{$idy_ubica}', {$cantidad}, 2, '{$usuario}', {$id_almacen}, 1, NOW())";
                $rs = mysqli_query($conn, $sql);
                $sql_tracking .= "5-".$sql."\n\n";

                if($palletizar_entrada || $label_lp)
                {
                    $sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                            VALUES((SELECT MAX(id) FROM t_cardex), '{$clave_almacen}', {$id_contenedor}, NOW(), '{$cve_ubicacion}', '{$idy_ubica}', 2, '{$usuario}', 'I')";
                    $rs = mysqli_query($conn, $sql);
                    $sql_tracking .= "6-".$sql."\n\n";

                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, '{$idy_ubica}', '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, {$id_contenedor}, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 2)";
                    $rs = mysqli_query($conn, $sql);

                }
                else
                {
                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, '{$idy_ubica}', '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, NULL, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 2)";
                    $rs = mysqli_query($conn, $sql);
                }

            }
            else
            {
                $sql = "UPDATE td_entalmacen SET CantidadRecibida = CantidadRecibida + {$cantidad} WHERE fol_folio = {$Folio} AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$cve_ubicacion}'";
                $rs = mysqli_query($conn, $sql);

                $sql = "UPDATE td_aduana SET cantidad = cantidad + {$cantidad_pedida} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND num_orden = '{$num_pedimento}'";
                $rs = mysqli_query($conn, $sql);

//*************************************************************************************************************************

                if($palletizar_entrada || $label_lp)
                {
                    $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                            VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$Folio}', '{$cve_ubicacion}', {$cantidad}, 1, '{$usuario}', {$id_almacen}, 1, NOW())";
                    $rs = mysqli_query($conn, $sql);

                    $sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                            VALUES((SELECT MAX(id) FROM t_cardex), '{$clave_almacen}', {$id_contenedor}, NOW(), '{$Folio}', '{$cve_ubicacion}', 1, '{$usuario}', 'I')";
                    $rs = mysqli_query($conn, $sql);
                    $sql_tracking .= "4-".$sql."\n\n";

                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, NULL, '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, {$id_contenedor}, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 1)";
                    $rs = mysqli_query($conn, $sql);


                    $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                            VALUES ('{$cve_articulo}', '{$cve_lote}', NOW(), '{$cve_ubicacion}', '{$idy_ubica}', {$cantidad}, 2, '{$usuario}', {$id_almacen}, 1, NOW())";
                    $rs = mysqli_query($conn, $sql);

                    $sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                            VALUES((SELECT MAX(id) FROM t_cardex), '{$clave_almacen}', {$id_contenedor}, NOW(), '{$cve_ubicacion}', '{$idy_ubica}', 2, '{$usuario}', 'I')";
                    $rs = mysqli_query($conn, $sql);
                    $sql_tracking .= "6-".$sql."\n\n";

                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, '{$idy_ubica}', '{$cve_articulo}', CONVERT('{$cve_lote}', CHAR), {$cantidad}, {$id_contenedor}, {$id_proveedor}, '{$Folio}', '{$num_pedimento}', '{$factura_remision}', '{$factura_oc}', '{$proyecto}', 2)";
                    $rs = mysqli_query($conn, $sql);

                }
                else 
                {
                    $sql = "UPDATE t_cardex SET cantidad = cantidad + {$cantidad} WHERE origen = '{$Folio}' AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND destino = '{$cve_ubicacion}'";
                    $rs = mysqli_query($conn, $sql);

                    $sql = "UPDATE t_cardex SET cantidad = cantidad + {$cantidad} WHERE origen = '{$cve_ubicacion}' AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND destino = '{$idy_ubica}'";
                    $rs = mysqli_query($conn, $sql);
                }
//*************************************************************************************************************************


            }

/*
            $sql = "SELECT COUNT(*) AS existe FROM t_pendienteacomodo WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$cve_ubicacion}' AND ID_Proveedor = {$id_proveedor}";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe = $resul['existe'];

            if(!$existe)
            {
                $sql = "INSERT INTO t_pendienteacomodo(cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) 
                        VALUES ('{$cve_articulo}', '{$cve_lote}', {$cantidad}, '{$cve_ubicacion}', {$id_proveedor})";
                $rs = mysqli_query($conn, $sql);
            }
            else
            {
                $sql = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad + {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$cve_ubicacion}' AND ID_Proveedor = {$id_proveedor}";
                $rs = mysqli_query($conn, $sql);
            }


            //***********************************************************************
            //                              ACOMODAR 
            //***********************************************************************

            $sql = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad - {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$cve_ubicacion}' AND ID_Proveedor = {$id_proveedor}";
            $rs = mysqli_query($conn, $sql);
*/

            $sql = "SELECT COUNT(*) AS existe FROM ts_existenciapiezas WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen}";

            if($palletizar_entrada || $label_lp)
            {
                $sql = "SELECT COUNT(*) AS existe FROM ts_existenciatarima WHERE cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}' AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen} AND ntarima = {$id_contenedor}";
            }
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe = $resul['existe'];

            if(!$existe)
            {
                $sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) 
                            VALUES ({$id_almacen}, {$idy_ubica}, '{$cve_articulo}', '{$cve_lote}', {$cantidad}, {$id_proveedor})";
                if($palletizar_entrada || $label_lp)
                {
                    $sql = "INSERT INTO ts_existenciatarima(cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, ID_Proveedor) 
                            VALUES ({$id_almacen}, {$idy_ubica}, '{$cve_articulo}', '{$cve_lote}', {$Folio}, {$id_contenedor}, 0, {$cantidad}, {$id_proveedor})";
                }
                $rs = mysqli_query($conn, $sql);
            }
            else
            {
                $sql = "UPDATE ts_existenciapiezas SET Existencia = existencia + {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen}";

                if($sobreescribir_existencias)
                    $sql = "UPDATE ts_existenciapiezas SET Existencia = {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen}";

                if($palletizar_entrada || $label_lp)
                {
                    $sql = "UPDATE ts_existenciatarima SET existencia = existencia + {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}' AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen} AND ntarima = {$id_contenedor}";

                    if($sobreescribir_existencias)
                        $sql = "UPDATE ts_existenciatarima SET existencia = {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}' AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen} AND ntarima = {$id_contenedor}";
                }
                $rs = mysqli_query($conn, $sql);
            }

            $sql = "UPDATE td_entalmacen SET CantidadUbicada = CantidadUbicada + {$cantidad} WHERE fol_folio = {$Folio} AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$cve_ubicacion}'";
            $rs = mysqli_query($conn, $sql);

            //***********************************************************************
            //***********************************************************************

            $productos++;
            $linea++;
        }

//if($convertir_a_oc == 1)
//{
        //$sql = "INSERT INTO th_aduana(num_pedimento, fech_pedimento, Factura, fech_llegPed, STATUS, ID_Proveedor, ID_Protocolo, Consec_protocolo, cve_usuario, Cve_Almac, procedimiento) (SELECT id_ocompra, NOW(), Fol_OEP, NOW(), 'T', Cve_Proveedor, ID_Protocolo, Consec_protocolo, Cve_Usuario, Cve_Almac, (SELECT cve_proveedor FROM c_proveedores WHERE ID_Proveedor = th_entalmacen.Cve_Proveedor) AS procedimiento FROM th_entalmacen WHERE id_ocompra = {$num_pedimento})";
/*
        $sql = "INSERT INTO th_aduana(num_pedimento, fech_pedimento, Factura, fech_llegPed, STATUS, ID_Proveedor, ID_Protocolo, Consec_protocolo, cve_usuario, Cve_Almac, procedimiento) (SELECT id_ocompra, NOW(), Fol_OEP, NOW(), 'T', Cve_Proveedor, ID_Protocolo, Consec_protocolo, Cve_Usuario, Cve_Almac, '$clave_empresa' FROM th_entalmacen WHERE id_ocompra = {$num_pedimento} ORDER BY Fec_Entrada DESC LIMIT 1)";
        $rs = mysqli_query($conn, $sql);

        $sql = "INSERT INTO td_aduana(cve_articulo, cantidad, cve_lote, num_orden) (SELECT cve_articulo, CantidadPedida, cve_lote, '{$num_pedimento}' FROM td_entalmacen WHERE num_orden = '{$num_pedimento}')";
        $rs = mysqli_query($conn, $sql);

        $sql = "INSERT INTO td_aduanaxtarima(Num_Orden, Cve_Articulo, Cve_Lote, ClaveEtiqueta, Cantidad, Recibida) (SELECT '{$num_pedimento}', cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, 'S' FROM td_entalmacenxtarima WHERE fol_folio = (SELECT Fol_Folio FROM th_entalmacen WHERE id_ocompra = {$num_pedimento}))";
        $rs = mysqli_query($conn, $sql);
*/
//}

        $mensaje_bl_no_existentes = "";
        $mensaje_articulos_no_existentes = "";
        if($articulos_no_existentes)
        {
            $mensaje_articulos_no_existentes = "<br>Los siguientes artículos no existen en el sistema: <br><textarea rows='3' style='width:100%;'>".$articulos_no_existentes."</textarea>";
            //if (file_exists("tmp/articulos-no-existentes.txt")){
            //$archivo = fopen("/tmp/articulos-no-existentes.txt", "w");
            //fwrite($archivo, PHP_EOL ."$mensaje_articulos_no_existentes");
            //fclose($archivo);
            //}
            //$mensaje_articulos_no_existentes = "\nArtículos no Existentes: <a href='/tmp/articulos-no-existentes.txt' target='_blank'>Descargar</a>";
            //$mensaje_articulos_no_existentes = "\nArtículos no Existentes: <a href='#' id='ver_art_no_ex'>Ver</a>";
        }
        if($bl_no_existentes)
        {
            $mensaje_bl_no_existentes = "<br>Los siguientes BL no existen en el sistema: <br><textarea rows='3' style='width:100%;'>".$bl_no_existentes."</textarea>";
            //if (file_exists("tmp/BL-no-existentes.txt")){
            //$archivo = fopen("/tmp/BL-no-existentes.txt", "w");
            //fwrite($archivo, PHP_EOL ."$mensaje_bl_no_existentes");
            //fclose($archivo);
            //}
            //$mensaje_bl_no_existentes = "\nBL no Existentes: <a href='/tmp/BL-no-existentes.txt' target='_blank'>Descargar</a>";
            //$mensaje_bl_no_existentes = "\nBL no Existentes: <a href='#' id='ver_bl_no_ex'>Ver</a>";
        }
      
        $this->response(200, [
            'statusText' =>  "Entrada importada con éxito. Total de Productos: \"{$productos}\" <br><br>".$mensaje_articulos_no_existentes."<br><br>".$mensaje_bl_no_existentes,
            'sql_entradas' => $sql_entradas,
            "success" => 1,
            'sql_tracking' => $sql_tracking, 
            //'mensaje_articulos_no_existentes' => $mensaje_articulos_no_existentes,
            //'mensaje_bl_no_existentes' => $mensaje_bl_no_existentes,
            "lineas"=>$lineas
        ]);

    }//if($procesar_entrada)
    else
    {
        $mensaje = "";
        if(!$bls_unicos_por_lp)
        {
            $mensaje = "El LP {$primer_lp_distinto} está ubicado en más de 1 BL";
        }
        else if($bl_no_existente)
        {
            $mensaje = "El BL {$bl_no_existente} No existe en el sistema, está registrado en otro almacén o está inactivo";
        }
        else if(!$lp_unico)
        {
            $mensaje = "El LP {$lp_ocupado} ya se ocupó en otra entrada";
        }
        else if(!$hay_referencia)
        {
            $mensaje = "Debe Registrar una Referencia";
        }
        else if(!$hay_pedimento)
        {
            $mensaje = "Debe Registrar un Pedimento";
        }

        $this->response(200, [
            'statusText' =>  $mensaje,
            "success" => 0,
            "bls_unicos_por_lp" => $bls_unicos_por_lp,
            "bl_no_existente" => $bl_no_existente,
            "lp_unico" => $lp_unico,
            //'mensaje_articulos_no_existentes' => $mensaje_articulos_no_existentes,
            //'mensaje_bl_no_existentes' => $mensaje_bl_no_existentes,
            "lineas"=>$lineas
        ]);

    }


    }
}
