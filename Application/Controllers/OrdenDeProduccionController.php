<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\OrdenDeProduccion;
use Illuminate\Database\Capsule\Manager as Capsule;

use PDO;
use PDOException;
use Exception;


/**
 * @version 1.0.0
 * @category Ubicaciones
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class OrdenDeProduccionController extends Controller
{
    //const FOLIO_ORDEN         = ;
    const OT_CLIENTE          = 0;
    const CVE_ART_COMPUESTO   = 1;
    const LOTE                = 2;
    const CADUCIDAD           = 3;
    const CANTIDAD_A_PRODUCIR = 4;
    const FECHA_COMPROMISO    = 5;
    const LP                  = 6;


/*
    private $camposRequeridos = [
        self::ALMACEN => 'Almacén', 
        self::ZONA => 'Zona', 
        self::PASILLO => 'Pasillo',
    ];
*/
    /**
     * Renderiza la vista general
     *
     * @return void
     */
    public function index()
    {
       
    }

    public function importarRL($id_almacen, $id_proveedor, $palletizar_entrada, $sobreescribir_existencias, $articulos, $lp_prod, $ubicacion_prod, $folio_ot, $pdo = "")
    {
/*
        $errores = [];
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }

        $xlsx = new SimpleXLSX( $file );
*/
        //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if($pdo == "") $pdo = \db();

        $linea = 1; $productos = 0;

        $bl_array = array();
        $lp_array = array();
        $id_contenedor_array = array();
/*
        $almacen                   = $_POST['almacenes'];
        $proveedor                 = $_POST['proveedor'];
        $usuario                   = $_POST['txtUsuario'];
        $cve_ubicacion             = $_POST['zonarecepcioni'];
        $protocol                  = $_POST['Protocol'];
        $consecut                  = $_POST['Consecut'];
        $palletizar_entrada        = $_POST['palletizar_entrada'];
        $sobreescribir_existencias = $_POST['sobreescribir_existencias'];
*/
        $sql_tracking = "";

        $bl_no_existentes = "";
        $articulos_no_existentes = "";

        foreach ($articulos as $row)
        {

            $cve_articulo = $row["Cve_articulo"];
            $cve_lote     = $row["Cve_Lote"];
            $cantidad     = $row["Num_cantidad"];
            $bl           = $ubicacion_prod;
            $lp           = $lp_prod;

            $sql_tracking .= "\n- OK datos - cve_articulo = $cve_articulo - LP = $lp - BL = $bl - \n";

            $label_lp = $lp;

            $sql = "SELECT COUNT(*) as existe FROM c_articulo WHERE cve_articulo = '{$cve_articulo}'";
            //$rs = mysqli_query($conn, $sql);
            //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $rs = $pdo->prepare( $sql ); $rs->execute(); 
            $resul = $rs->fetch();
            $existe_articulo = $resul['existe'];
            $rs->closeCursor();

            $existe_lp = 0;
            if($lp != '')
            {
                //IDContenedor NOT IN (SELECT ntarima FROM ts_existenciatarima) AND
                $sql = "SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$lp}' AND TipoGen = 0";
                //$rs = mysqli_query($conn, $sql);
                //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $rs = $pdo->prepare( $sql ); $rs->execute(); 
                $resul = $rs->fetch();
                //if(mysqli_num_rows($rs) > 0)
                if($rs->rowCount() > 0)
                   $existe_lp = $resul['IDContenedor'];
                $rs->closeCursor();
            }

            if(!$existe_articulo) {$articulos_no_existentes .= $cve_articulo."\n";}

            $sql = "SELECT COUNT(*) as existe FROM c_ubicacion WHERE CodigoCSD = '{$bl}' LIMIT 1";
            //$rs = mysqli_query($conn, $sql);
            //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $rs = $pdo->prepare( $sql ); $rs->execute(); $resul = $rs->fetch();$rs->closeCursor();
            $existe_bl = $resul['existe'];

            if(!$existe_bl) {$bl_no_existentes .= $bl."\n";}

            if(!$existe_articulo || !$existe_bl) {continue;}// || ($lp!="" && $existe_lp > 0)


            $sql = "SELECT control_lotes, Caduca, control_numero_series FROM c_articulo WHERE cve_articulo = '{$cve_articulo}'";
            //$rs = mysqli_query($conn, $sql);
            //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $rs = $pdo->prepare( $sql ); $rs->execute(); $resul = $rs->fetch();$rs->closeCursor();
            $control_lotes  = $resul['control_lotes'];
            $caduca         = $resul['Caduca'];
            $control_series = $resul['control_numero_series'];

            if($control_lotes == 'S')
            {
                $sql = "SELECT COUNT(*) as existe FROM c_lotes WHERE cve_articulo = '{$cve_articulo}' AND Lote = '{$cve_lote}'";
                //$rs = mysqli_query($conn, $sql);
                //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $rs = $pdo->prepare( $sql ); $rs->execute(); $resul = $rs->fetch();$rs->closeCursor();
                $existe  = $resul['existe'];

                if(!$existe)
                {
                    if($caduca == 'S')
                    {
                          $date=date_create($caducidad);
                          $caducidad = date_format($date,"Y-m-d");
                    }

                    if(!$caducidad) $caducidad = '0000-00-00';

                    $sql = "INSERT INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cve_articulo}', '{$cve_lote}', '{$caducidad}')";
                    //$rs = mysqli_query($conn, $sql);
                    $rs = $pdo->prepare( $sql ); $rs->execute(); $rs->closeCursor();
                }
            }
            else if($control_series == 'S')
            {
                $sql = "SELECT COUNT(*) as existe FROM c_serie WHERE cve_articulo = '{$cve_articulo}' AND numero_serie = '{$cve_lote}'";
                //$rs = mysqli_query($conn, $sql);
                //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $rs = $pdo->prepare( $sql ); $rs->execute(); $resul = $rs->fetch();$rs->closeCursor();
                $existe  = $resul['existe'];

                if(!$existe)
                {
                    $sql = "INSERT INTO c_serie(cve_articulo, numero_serie, fecha_ingreso) VALUES ('{$cve_articulo}', '{$cve_lote}', NOW())";
                    //$rs = mysqli_query($conn, $sql);
                    $rs = $pdo->prepare( $sql ); $rs->execute(); $rs->closeCursor();
                }
            }else
            {
                $cve_lote = '';
            }


//*************************************************************************************
//*************************************************************************************
            $sql = "SELECT idy_ubica, IF(AcomodoMixto = '', 'N', IFNULL(AcomodoMixto, 'N')) as es_mixto FROM c_ubicacion WHERE CodigoCSD = '{$bl}' LIMIT 1";
            //$rs = mysqli_query($conn, $sql);
            //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $rs = $pdo->prepare( $sql ); $rs->execute(); $resul = $rs->fetch();$rs->closeCursor();
            $idy_ubica = $resul['idy_ubica'];
            $es_mixto = $resul['es_mixto'];

            $existe = 0; $id_contenedor = "";

            $Folio = $idy_ubica;
            $dicoisa = false;
            //if((strpos($_SERVER['HTTP_HOST'], 'dicoisa') === true)) 
            if($_SERVER['HTTP_HOST'] == 'dicoisa.assistprowms.com')
                $dicoisa = true;

            if(!in_array($bl, $bl_array) || !in_array($label_lp, $lp_array) || $dicoisa == true)
            {
                if($palletizar_entrada)
                {
                    $id_contenedor = $existe_lp;
                    if(!$existe_lp)
                    {
                        $sql = "SELECT (SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1";
                        //$rs = mysqli_query($conn, $sql);
                        //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                        $rs = $pdo->prepare( $sql ); $rs->execute(); $resul = $rs->fetch();$rs->closeCursor();

                        if(!$resul['id_contenedor']) break;

                        $id_contenedor = $resul['id_contenedor'];
                        $descripcion   = $resul['descripcion'];
                        //$tipo          = $resul['tipo'];
                        $alto          = $resul['alto'];
                        $ancho         = $resul['ancho'];
                        $fondo         = $resul['fondo'];
                        $peso          = $resul['peso'];
                        $pesomax       = $resul['pesomax'];
                        $capavol       = $resul['capavol'];

                        $label_lp = $lp;
                        if($lp == "")
                           $label_lp = "LP".str_pad($id_contenedor.$Folio, 9, "0", STR_PAD_LEFT);

                        $sql = "SELECT tipolp_traslado FROM c_almacenp WHERE id = '{$id_almacen}'";
                        //$rs = mysqli_query($conn, $sql);
                        //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                        $rs = $pdo->prepare( $sql ); $rs->execute(); $resul = $rs->fetch();$rs->closeCursor();
                        $tipo = $resul['tipolp_traslado'];

                        $sql = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) 
                                VALUES ({$id_almacen}, '{$label_lp}', '{$descripcion}', 0, '{$tipo}', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
                        //$rs = mysqli_query($conn, $sql);
                        $rs = $pdo->prepare( $sql ); $rs->execute(); $rs->closeCursor();

                        $sql = "SELECT IDContenedor FROM c_charolas WHERE CveLP = '$label_lp'";
                        //$rs = mysqli_query($conn, $sql);
                        //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                        $rs = $pdo->prepare( $sql ); $rs->execute(); $resul = $rs->fetch();$rs->closeCursor();
                        $id_contenedor = $resul['IDContenedor'];
                    }

                    array_push($lp_array, $label_lp);
                    array_push($id_contenedor_array, $id_contenedor);
                }
                if($es_mixto == 'N') array_push($bl_array, $bl);
            }
            else
            {
                $pos = array_search($bl, $bl_array);
                if($palletizar_entrada)
                   $id_contenedor = $id_contenedor_array[$pos];

                $label_lp = $lp;
                if($lp == "")
                   $label_lp = $lp_array[$pos];

            }
//*************************************************************************************
//*************************************************************************************


/*
            //***********************************************************************
            //                              ACOMODAR 
            //***********************************************************************

            $sql = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad - {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND cve_ubicacion = '{$cve_ubicacion}' AND ID_Proveedor = {$id_proveedor}";
            $rs = mysqli_query($conn, $sql);
*/

            $sql = "SELECT COUNT(*) AS existe FROM ts_existenciapiezas WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen}";

            if($palletizar_entrada && $id_contenedor)
            {
                $sql = "SELECT COUNT(*) AS existe FROM t_tarima WHERE cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}' AND ntarima = {$id_contenedor} AND fol_folio = '$folio_ot'";
            }
            //return $sql;
            //$rs = mysqli_query($conn, $sql);
            //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $rs = $pdo->prepare( $sql ); $rs->execute(); $resul = $rs->fetch();$rs->closeCursor();
            $existe = $resul['existe'];

            if(!$existe)
            {
/*
                $sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) 
                            VALUES ({$id_almacen}, {$idy_ubica}, '{$cve_articulo}', '{$cve_lote}', {$cantidad}, {$id_proveedor})";
                if($palletizar_entrada)
                {
                    $sql = "INSERT INTO ts_existenciatarima(cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, ID_Proveedor) 
                            VALUES ({$id_almacen}, {$idy_ubica}, '{$cve_articulo}', '{$cve_lote}', {$Folio}, {$id_contenedor}, 0, {$cantidad}, {$id_proveedor})";
                }
                $rs = mysqli_query($conn, $sql);
*/
                $sql = "INSERT INTO th_cajamixta(Cve_CajaMix, fol_folio, Sufijo, NCaja, abierta, embarcada, cve_tipocaja, Guia) VALUES ((SELECT (IFNULL(MAX(tc.Cve_CajaMix), 0) + 1) FROM th_cajamixta tc), '{$folio_ot}', 1, (SELECT (IFNULL(MAX(tc.NCaja), 0) + 1) FROM th_cajamixta tc WHERE tc.fol_folio = '{$folio_ot}'), 'N', 'N', 0, (SELECT CONCAT('{$folio_ot}',(IFNULL(MAX(tc.Cve_CajaMix), 0) + 1)) FROM th_cajamixta tc))";

                //$rs = mysqli_query($conn, $sql);
                $rs = $pdo->prepare( $sql ); $rs->execute(); $rs->closeCursor();

                $sql = "INSERT INTO td_cajamixta (Cve_CajaMix, Cve_articulo, Cantidad,  Cve_Lote, Num_Empacados) VALUES ((SELECT MAX(tc.Cve_CajaMix) FROM th_cajamixta tc), '{$cve_articulo}', {$cantidad},'{$cve_lote}', {$cantidad})";                

                //$rs = mysqli_query($conn, $sql);
                $rs = $pdo->prepare( $sql ); $rs->execute(); $rs->closeCursor();


                if($palletizar_entrada)
                {
                    $sql = "INSERT INTO t_tarima(ntarima, Fol_Folio, Sufijo, cve_articulo, lote, cantidad, Num_Empacados, Caja_ref, Ban_Embarcado, Abierta, Activo) VALUES ({$id_contenedor}, '{$folio_ot}', 1, '{$cve_articulo}', '{$cve_lote}', {$cantidad}, {$cantidad}, (SELECT MAX(tc.Cve_CajaMix) FROM th_cajamixta tc), 'N', 0, 1)";
                }
                //$rs = mysqli_query($conn, $sql);
                $rs = $pdo->prepare( $sql ); $rs->execute(); $rs->closeCursor();

            }
            else
            {
/*
                $sql = "UPDATE ts_existenciapiezas SET Existencia = existencia + {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen}";

                if($sobreescribir_existencias)
                    $sql = "UPDATE ts_existenciapiezas SET Existencia = {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}' AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen}";

                if($palletizar_entrada)
                {
                    $sql = "UPDATE ts_existenciatarima SET existencia = existencia + {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}' AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen} AND ntarima = {$id_contenedor}";

                    if($sobreescribir_existencias)
                        $sql = "UPDATE ts_existenciatarima SET existencia = {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}' AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$id_proveedor} AND cve_almac = {$id_almacen} AND ntarima = {$id_contenedor}";
                }
                $rs = mysqli_query($conn, $sql);
*/
                if($palletizar_entrada)
                {
                    $sql = "UPDATE t_tarima SET cantidad = cantidad + {$cantidad}, Num_Empacados = Num_Empacados + {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}' AND ntarima = {$id_contenedor} AND Fol_Folio = '{$folio_ot}'";
                }
                //$rs = mysqli_query($conn, $sql);
                $rs = $pdo->prepare( $sql ); $rs->execute(); $rs->closeCursor();

            }

            //***********************************************************************
            //***********************************************************************

            $productos++;
        }

        return $sql_tracking;
    }


    public function Ejecutar_Infinity_WS($clave, $Lote, $cantidad, $um, $clave_almacen, $ejecutar_infinity, $Url_inf, $url_curl, $Servicio_inf, $User_inf, $Pswd_inf, $Empresa_inf, $hora_movimiento, $Codificado)
    {

        //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

          //*******************************************************************************
          //                          EJECUTAR EN INFINITY
          //*******************************************************************************
          //$sql = "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'";
          //$query = mysqli_query($conn, $sql);
          //$ejecutar_infinity = mysqli_fetch_array($query)['existe'];


          if($ejecutar_infinity)
          {
////////////////$sql = "SELECT Url, Servicio, User, Pswd, Empresa, TO_BASE64(CONCAT(User,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1";
////////////////$query = mysqli_query($conn, $sql);
////////////////$row_infinity = mysqli_fetch_array($query);

                //$sql = "SELECT Url, Servicio, User, Pswd, Empresa, TO_BASE64(CONCAT(User,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1";
                //try{
                //$query = \db()->prepare($sql);
                //$query->execute();
                //$row_infinity = $query->fetch();
                //} catch (PDOException $e) {
                //    echo 'Error de conexión: ' . $e->getMessage();
                //}

                //$Url_inf = $row_infinity['Url'];
                //$url_curl = $row_infinity['url_curl'];
                //$Servicio_inf = $row_infinity['Servicio'];
                //$User_inf = $row_infinity['User'];
                //$Pswd_inf = $row_infinity['Pswd'];
                //$Empresa_inf = $row_infinity['Empresa'];
                //$hora_movimiento = $row_infinity['hora_movimiento'];
                //$Codificado = $row_infinity['Codificado'];

          $json = "[";

            $json .= "{";
            $json .= '"item":"'.$clave.'","um":"'.$um.'","batch":"'.$Lote.'", "qty": '.$cantidad.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
            $json .= "}";
          //$json[strlen($json)-1] = ' ';
          $json .= "]";

              $curl = curl_init();
              //$url_curl = $Url_inf.':8080/'.$Servicio_inf;

              curl_setopt_array($curl, array(
                // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                CURLOPT_URL => "$url_curl",
                //CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>
                // Aquí cambia la cadena JSON
                $json,
                CURLOPT_HTTPHEADER => array(
                  'Content-Type: application/json',
                  //'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
                  'Authorization: Basic '.$Codificado.''
                )
                ,CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false
              ));

              $response = curl_exec($curl);
              $response_ot .= $response."\n";

              curl_close($curl);      
              //echo $response;

              //$response = 'Pendiente';
              //$sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', 'Transformacion', 'WEB')";
              //$query = mysqli_query($conn, $sql);
                $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), :Servicio_inf, :json, :response, 'Transformacion', 'WEB')";
                try{
                $query = \db()->prepare($sql);
                $query->execute(array('Servicio_inf' => $Servicio_inf, 'json' => $json, 'response' => $response));
                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }



          }
          //*******************************************************************************/
          //*******************************************************************************
    }

    public function consecutivo_folio_traslado($pdo) 
    {
      //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql = "SELECT IF(MONTH(CURRENT_DATE()) < 10, CONCAT(0, MONTH(CURRENT_DATE())), MONTH(CURRENT_DATE())) AS mes, YEAR(CURRENT_DATE()) AS _year FROM DUAL";
      //$res = mysqli_query($conn, $sql);
      $res = $pdo->prepare($sql);$res->execute();
      //$fecha = mysqli_fetch_array($res, MYSQLI_ASSOC);
      $fecha = $res->fetch();$res->closeCursor();

      $mes  = $fecha['mes'];
      $year = $fecha['_year'];


      $folio_next = "";
      $count = 1;
      while(true)
      {
          if($count < 10)
            $count = "0".$count;

          $folio_next = "TR".$year.$mes.$count;
          $sql = "SELECT COUNT(*) as Consecutivo FROM th_pedido WHERE Fol_Folio = '$folio_next'";
          //$res = mysqli_query($conn, $sql);
          $res = $pdo->prepare($sql);$res->execute();
          //$data = mysqli_fetch_array($res, MYSQLI_ASSOC);
          $data = $res->fetch();$res->closeCursor();

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

public function limpiarDato($valor) {
    // Elimina espacios invisibles, saltos de línea, tabulaciones
    $valor = trim($valor);
    $valor = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $valor); // caracteres de control
    $valor = str_replace(["\r", "\n", "\t", "\0", "\x0B"], '', $valor);

    // Convierte comillas raras o formateadas
    $valor = str_replace(
        ['“', '”', '‘', '’', '´', '`'],
        ['"', '"', "'", "'", "'", "'"],
        $valor
    );

    // Elimina caracteres invisibles de UTF-8 como BOM o ZWSP
    $valor = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $valor);

    return $valor;
}

    public function importarNew()
    {
        //set_time_limit(12000);
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }

        try {
            $pdo = new PDO(
              sprintf( 'mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME )
              , DB_USER
              , DB_PASSWORD
              , array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4", PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true) 
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql_charset = "SET NAMES 'utf8mb4'";
            ////if (!($res_doc = mysqli_query($conn, $sql_doc))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $res_charset = $pdo->exec($sql_charset);//$res_charset->execute();
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }

    $intentos_maximos = 3;
    for ($i = 0; $i < $intentos_maximos; $i++) 
    {
        try{
        $pdo->beginTransaction();

        $interfase = new \Interfases\Interfases();
        $referencias = array();
        //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        //$query = mysqli_query($conn, "SELECT Url, Servicio, User, Pswd, IFNULL(Puerto, '8080') as Puerto, Empresa, to_base64(CONCAT(USER,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1");
        $query = $pdo->prepare("SELECT Url, Servicio, User, Pswd, IFNULL(Puerto, '8080') as Puerto, Empresa, to_base64(CONCAT(USER,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1");$query->execute();
        //$row_infinity = mysqli_fetch_assoc($query);
        $row_infinity = $query->fetch();$query->closeCursor();

        $Url_inf = $row_infinity['Url'];
        $url_curl = $row_infinity['url_curl'];
        $Servicio_inf = $row_infinity['Servicio'];
        $User_inf = $row_infinity['User'];
        $Pswd_inf = $row_infinity['Pswd'];
        $Empresa_inf = $row_infinity['Empresa'];
        $hora_movimiento = $row_infinity['hora_movimiento'];
        $Codificado = $row_infinity['Codificado'];

        $sql = "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'";
        $result = $pdo->prepare($sql);
        $result->execute();
        $row_inf = $result->fetch();$result->closeCursor();
        $ejecutar_infinity = $row_inf['existe'];

        $xlsx = new SimpleXLSX( $file );

        $response_ot = "";
        $url_curl = "";
        $Folio_vacio = ""; $Folio_Anterior = "";$sql_check = ""; $OT_Folio_Anterior = "";
        $linea = 1; $registros = 0;
        $id_proveedor        = $_POST['Proveedor2'];
        $idy_ubica_ot        = $_POST['idy_ubica_ot_import'];
        $realizar_produccion = $_POST['realizar_produccion'];
        $folios_creados = array();

        $Folio_Pro = "";
        $registros_vacios = 0;


        $tipo_traslado_input = $_POST["tipo_traslado_input"];
        $almacen_dest = $_POST["almacen_dest"];
        $traslado_interno_externo_input = $_POST["traslado_interno_externo_input"];

        foreach ($xlsx->rows() as $row)
        {
            $ot_cliente = $this->limpiarDato($this->pSQL($row[self::OT_CLIENTE]));

            if($linea == 1 || $ot_cliente == "") {
                $registros_vacios++;
                $linea++;continue;
            }
            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new OrdenDeProduccion(); 
            }

            $cve_articulo = $this->limpiarDato($row[self::CVE_ART_COMPUESTO]);

            $sql = "SELECT COUNT(*) AS existe FROM t_artcompuesto WHERE Cve_ArtComponente = :cve_articulo";
            $rs = $pdo->prepare($sql);
            $rs->execute(array('cve_articulo' => $cve_articulo));
            $row_existe = $rs->fetch();$rs->closeCursor();
            $existe = $row_existe['existe'];

            $existe_folio = 0;
            if($Folio_Pro != '')
            {
                if($tipo_traslado_input == 1)
                {
                    $sql = "SELECT COUNT(*) AS existe_folio FROM th_pedido WHERE Fol_Folio = :Folio_Pro AND Pick_Num = :ot_cliente";
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('Folio_Pro' => $Folio_Pro, 'ot_cliente' => $ot_cliente));
                    $resul = $rs->fetch();$rs->closeCursor();
                    $existe_folio = $resul['existe_folio'];
                }
                else
                {

                    $sql = "SELECT COUNT(*) AS existe_folio FROM t_ordenprod WHERE Folio_Pro = :Folio_Pro AND Referencia = :ot_cliente";
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('Folio_Pro' => $Folio_Pro, 'ot_cliente' => $ot_cliente));
                    $resul = $rs->fetch();$rs->closeCursor();
                    $existe_folio = $resul['existe_folio'];
                }
            }

            //if($Folio_Pro == "" && $Folio_vacio == "")
            if($existe_folio == 0)
            {
                if($tipo_traslado_input == 1)
                {
                    $Folio_Pro = $this->consecutivo_folio_traslado();
                }
                else 
                {
                    $sql = "SELECT `fct_consecutivo_documentos`('t_ordenprod', 6);";
                    //$rs = mysqli_query($conn, $sql);
                    $rs = $pdo->prepare($sql);$rs->execute();
                    //$row_folio_pro = mysqli_fetch_array($rs);
                    $row_folio_pro = $rs->fetch();$rs->closeCursor();
                    $Folio_Pro = "OT".$row_folio_pro[0];
                }
                $Folio_vacio = $Folio_Pro;
            }
            else if($Folio_Pro == "" && $Folio_vacio != "")
            {
                $Folio_Pro = $Folio_vacio;
            }

            $sql = "SELECT id from c_almacenp where id = (SELECT cve_almacenp FROM c_almacen where cve_almac = ".$_POST['cboZonaAlmacenImport'].") ;";
            //$query = mysqli_query($conn, $sql);
            $query = $pdo->prepare($sql);$query->execute();
            //if($query->num_rows > 0)
            if($query->rowCount() > 0)//el $query->closeCursor(); esta en el else de este if
            {
                //$id_almacen = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["id"];
                $id_almacen = $query->fetch()["id"];
                $query->closeCursor();
            if($existe && $existe_folio == 0)
            {//3
                $fa = getdate();
                $dia  = $fa['mday'];
                $mes  = $fa['mon'];
                $year = $fa['year'];
                if($dia < 10) $dia = '0'.$dia;
                if($mes < 10) $mes = '0'.$mes;
                $fecha = $year.'-'.$mes.'-'.$dia;

                $fecha_compromiso = $this->limpiarDato($this->pSQL($row[self::FECHA_COMPROMISO]));
                if($fecha_compromiso)
                {//1
                    $fc = explode('-', $fecha_compromiso);
                    $fecha_compromiso = $fc[2]."-".$fc[1]."-".$fc[0];
                }//1

                if($tipo_traslado_input != 1)
                {
                    /*
                    $model->Folio_Pro     = $Folio_Pro;
                    $model->cve_almac     = $id_almacen;//$this->pSQL($row[self::AREA_DE_PRODUCCION]);
                    $model->Cve_Articulo  = $this->limpiarDato($this->pSQL($row[self::CVE_ART_COMPUESTO]));
                    $model->Cve_Lote      = $this->limpiarDato($this->pSQL($row[self::LOTE]));
                    $model->Cantidad      = $this->limpiarDato($this->pSQL($row[self::CANTIDAD_A_PRODUCIR]));
                    $model->ID_Proveedor  = $id_proveedor;//$this->pSQL($row[self::AREA_DE_PRODUCCION]);
                    $model->Cant_Prod     = 0;
                    $model->Cve_Usuario   = $this->limpiarDato($this->pSQL($_SESSION['id_user']));
                    $model->Fecha         = $fecha;
                    $model->Referencia    = $ot_cliente;
                    $model->FechaReg      = ($fecha_compromiso !='')?($fecha_compromiso):($fecha);
                    $model->Status        = 'P';
                    $model->id_zona_almac = $_POST['cboZonaAlmacenImport'];
                    $model->idy_ubica     = $idy_ubica_ot;

                    $model->save();
                    */
                    $sql = "INSERT INTO t_ordenprod(Folio_Pro, cve_almac, Cve_Articulo, Cve_Lote, Cantidad, ID_Proveedor, Cant_Prod, Cve_Usuario, Fecha, Referencia, FechaReg, Status, id_zona_almac, idy_ubica) VALUES(:Folio_Pro, :cve_almac, :Cve_Articulo, :Cve_Lote, :Cantidad, :ID_Proveedor, :Cant_Prod, :Cve_Usuario, :Fecha, :Referencia, :FechaReg, :Status, :id_zona_almac, :idy_ubica)";
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array(
                        'Folio_Pro' => $Folio_Pro,
                        'cve_almac' => $id_almacen, 
                        'Cve_Articulo' => $this->limpiarDato($this->pSQL($row[self::CVE_ART_COMPUESTO])),
                        'Cve_Lote' => $this->limpiarDato($this->pSQL($row[self::LOTE])),
                        'Cantidad' => $this->limpiarDato($this->pSQL($row[self::CANTIDAD_A_PRODUCIR])),
                        'ID_Proveedor' => $id_proveedor,
                        'Cant_Prod' => 0,
                        'Cve_Usuario' => $this->limpiarDato($this->pSQL($_SESSION['id_user'])),
                        'Fecha' => $fecha,
                        'Referencia' => $ot_cliente,
                        'FechaReg' => ($fecha_compromiso !='')?($fecha_compromiso):($fecha),
                        'Status' => 'P',
                        'id_zona_almac' => $_POST['cboZonaAlmacenImport'],
                        'idy_ubica' => $idy_ubica_ot
                    ));
                    $rs->closeCursor();
                }

                $articulos = array();
                $nuevo_pedido = new \NuevosPedidos\NuevosPedidos();//

                $sql = "SELECT Cve_Articulo, Cantidad FROM t_artcompuesto WHERE Cve_ArtComponente = :cve_articulo AND Cve_Articulo IN (SELECT cve_articulo FROM c_articulo WHERE IFNULL(tipo_producto, '') != 'ProductoNoSurtible')";
                $res = $pdo->prepare($sql);
                $res->execute(array('cve_articulo' => $cve_articulo));

                while($row_orden = $res->fetch())
                {//2
                    if($tipo_traslado_input != 1)
                    {
                            $sql = "INSERT INTO td_ordenprod(Folio_Pro,Cve_Articulo,Fecha_Prod,Cantidad,Usr_Armo,Activo) VALUES (:Folio_Pro,:Cve_Articulo,NOW(),:Cantidad,:id_user,1) ON DUPLICATE  KEY UPDATE Cantidad = Cantidad + :Cantidad";
                            $res_ord = $pdo->prepare($sql);
                            $res_ord->execute(array('Folio_Pro' => $Folio_Pro,
                                                    'Cve_Articulo' => $row_orden['Cve_Articulo'],
                                                    'Cantidad' => $row_orden['Cantidad'],
                                                    'id_user' => $_SESSION['id_user']));
                            $res_ord->closeCursor();
                        //***************************************************************************
                        //***************************************************************************

                    }
                    $cve_art = $row_orden['Cve_Articulo'];

                    $lp = $this->limpiarDato($this->pSQL($row[self::LP]));
                    $sql_art = "SELECT a.control_peso, a.peso, u.mav_cveunimed, a.unidadMedida FROM c_articulo a LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida WHERE a.cve_articulo = :cve_art";
                    $rs_art = $pdo->prepare($sql_art);
                    $rs_art->execute(array('cve_art' => $cve_art));
                    $row_art = $rs_art->fetch();
                    $unidadMedida = $row_art["unidadMedida"];
                    $rs_art->closeCursor();

                    array_push($articulos,array(
                        "Cve_articulo" => $row_orden['Cve_Articulo'],
                        "Num_cantidad" => ($row_orden['Cantidad']*$this->limpiarDato($this->pSQL($row[self::CANTIDAD_A_PRODUCIR]))),
                        "id_unimed" => $unidadMedida,
                        "Num_Meses" => ""
                    ));

                }//2
                $res->closeCursor();
                $lote = $this->limpiarDato($this->pSQL($row[self::LOTE]));
                $caducidad = $this->limpiarDato($this->pSQL($row[self::CADUCIDAD]));
                $sql = "SELECT IFNULL(control_lotes, 'N') as control_lotes, IFNULL(Caduca, 'N') as Caduca FROM c_articulo WHERE cve_articulo = :cve_articulo";
                $rs = $pdo->prepare($sql);
                $rs->execute(array('cve_articulo' => $cve_articulo));
                $resul = $rs->fetch(); $rs->closeCursor();
                $control_lotes = $resul['control_lotes'];
                $Caduca = $resul['Caduca'];

                $sql = "SELECT COUNT(*) as existe_lote FROM c_lotes WHERE cve_articulo = :cve_articulo AND Lote = :lote";
                $rs = $pdo->prepare($sql);
                $rs->execute(array('cve_articulo' => $cve_articulo, 'lote' => $lote));
                $resul = $rs->fetch();$rs->closeCursor();
                $existe_lote = $resul['existe_lote'];

                if($lote != '' && $control_lotes == 'S' && $existe_lote == 0)
                {//3
////////////////////$sql = "INSERT INTO c_lotes (cve_articulo, Lote) VALUES('{$cve_articulo}', '{$lote}')";
                    if($Caduca == 'S' && $caducidad != '')
                    {
                        $caducidad = $this->limpiarDato($this->pSQL($row[self::CADUCIDAD]));
                        $fc = explode('-', $caducidad);
                        $caducidad = $fc[2]."-".$fc[1]."-".$fc[0];

////////////////////////$sql = "INSERT INTO c_lotes (cve_articulo, Lote, Caducidad) VALUES('{$cve_articulo}', '{$lote}', '{$caducidad}')";
                        $sql = "INSERT INTO c_lotes (cve_articulo, Lote, Caducidad) VALUES(:cve_articulo, :lote, :caducidad)";
                        $rs = $pdo->prepare($sql);
                        $rs->execute(array('cve_articulo' => $cve_articulo, 'lote' => $lote, 'caducidad' => $caducidad));
                        $rs->closeCursor();
                    }
                    else
                    {
                        $sql = "INSERT INTO c_lotes (cve_articulo, Lote) VALUES(:cve_articulo, :lote)";
                        $rs = $pdo->prepare($sql);
                        $rs->execute(array('cve_articulo' => $cve_articulo, 'lote' => $lote));
                        $rs->closeCursor();
                    }

////////////////////$rs = mysqli_query($conn, $sql);
                }//3

            }//3
            else if($tipo_traslado_input != 1) //REVISAR BIEN AQUI
            {
                $lp = $this->limpiarDato($this->pSQL($row[self::LP]));
                if($existe && $existe_folio && $lp)
                {
                    $cant_prod = $this->limpiarDato($this->pSQL($row[self::CANTIDAD_A_PRODUCIR]));

                    $sql = "UPDATE t_ordenprod SET Cantidad = Cantidad + :cant_prod WHERE Folio_Pro = :Folio_Pro";
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('cant_prod' => $cant_prod, 'Folio_Pro' => $Folio_Pro));
                    $rs->closeCursor();

                    $sql = "SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = :Folio_Pro";
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('Folio_Pro' => $Folio_Pro));
                    $resul = $rs->fetch();$rs->closeCursor();
                    $cant_prod = $resul['Cantidad'];

                    $sql = "SELECT Cantidad, Cve_Articulo FROM td_ordenprod WHERE Folio_Pro = :Folio_Pro";
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('Folio_Pro' => $Folio_Pro));

                    while($resul = $rs->fetch())
                    {
                        $cve_art = $resul["Cve_Articulo"];
                        $cant    = $resul["Cantidad"]*$cant_prod;

                        $sql = "UPDATE td_pedido SET Num_cantidad = :cant WHERE Fol_folio = :Folio_Pro AND Cve_articulo = :cve_art";
                        $rs_td = $pdo->prepare($sql);
                        $rs_td->execute(array('cant'=> $cant, 'Folio_Pro' => $Folio_Pro, 'cve_art' => $cve_art));
                        $rs_td->closeCursor();
                    }
                    $rs->closeCursor();
                }
                else
                {
                    $linea++;
                    continue;
                }
            }


            //Crear pedido para manufactura
            if($Folio_Anterior != $Folio_Pro)
            {
                $cve_almac_traslado = ""; $TipoPedido = 'T';
                if($tipo_traslado_input == 1)
                {
                  $cve_almac_traslado = $id_almacen;
                  $id_almacen = $almacen_dest;
                  $TipoPedido = $traslado_interno_externo_input;

                }

                $fecha_compromiso = $this->limpiarDato($this->pSQL($row[self::FECHA_COMPROMISO]));
                if($fecha_compromiso)
                {//1
                    $fc = explode('-', $fecha_compromiso);
                    $fecha_compromiso = $fc[2]."-".$fc[1]."-".$fc[0];
                }//1

                $data = array(
                    'Fol_folio' => $Folio_Pro,
                    'Fec_Pedido' => date('Y-m-d H:m:s'),
                    'Cve_clte' => '',
                    'status' => 'A',
                    'Fec_Entrega' => $fecha_compromiso,
                    'cve_Vendedor' => "",
                    'Fec_Entrada' => date('Y-m-d H:m:s'),
                    'Pick_Num' => $ot_cliente,
                    'destinatario' => 0,
                    'Cve_Usuario' => $_SESSION['id_user'],
                    'Observaciones' => "",
                    'ID_Tipoprioridad' => 0,
                    'cve_almac' => $id_almacen,
                    'statusaurora_traslado' => $cve_almac_traslado,
                    'TipoPedido' => $TipoPedido,
                    'arrDetalle' => $articulos
                );
                $nuevo_pedido->save($data, $pdo);
                $Folio_Anterior = $Folio_Pro;
                $folios_creados[] = $Folio_Pro;
                $registros++;
            }


                $lp = $this->limpiarDato($this->pSQL($row[self::LP]));
                $sql_tracking = "NO entró a IF";

                if($existe && $lp && $tipo_traslado_input != 1)
                {
                    $sql_tracking = "SI entró a IF";


                    $sql = "UPDATE t_ordenprod SET Tipo = 'IMP_LP', Status = 'I' WHERE Folio_Pro = :Folio_Pro";
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('Folio_Pro' => $Folio_Pro));
                    $rs->closeCursor();

                    $sql = "SELECT CodigoCSD, idy_ubica FROM c_ubicacion WHERE AreaProduccion = 'S' AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = :id_almacen) LIMIT 1";
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('id_almacen' => $id_almacen));
                    $resul = $rs->fetch();
                    $rs->closeCursor();

                    $ubicacion_prod = $resul['CodigoCSD'];
                    $idy_ubica = $resul['idy_ubica'];
                    $articulos_ot = array();
                    $cve_articulo_comp = $this->limpiarDato($this->pSQL($row[self::CVE_ART_COMPUESTO]));
                    $cve_lote_comp = $this->limpiarDato($this->pSQL($row[self::LOTE]));
                    //$num_cantidad_comp = $this->pSQL($row[self::CANTIDAD_A_PRODUCIR]);
                    array_push($articulos_ot,array(
                        "Cve_articulo" => $this->limpiarDato($this->pSQL($row[self::CVE_ART_COMPUESTO])),
                        "Cve_Lote" => $this->limpiarDato($this->pSQL($row[self::LOTE])),
                        "Num_cantidad" => $this->limpiarDato($this->pSQL($row[self::CANTIDAD_A_PRODUCIR]))
                    ));

                    $sql_tracking = $this->importarRL($id_almacen, $id_proveedor, 1, 0, $articulos_ot, $lp, $ubicacion_prod, $Folio_Pro, $pdo);


                    $sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) (SELECT :id_almacen, :idy_ubica, cve_articulo, :cve_lote_comp, 0, ntarima, 0, 0, 1, (SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = :Folio_Pro), 0 FROM t_tarima WHERE fol_folio = :Folio_Pro)";
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('id_almacen' => $id_almacen,
                                       'idy_ubica' => $idy_ubica,
                                       'cve_lote_comp' => $cve_lote_comp,
                                       'Folio_Pro' => $Folio_Pro));
                    $rs->closeCursor();

                    //}

                    //$registros++;
                    
                }
                //else
                    //$registros++;
            }
            else 
            {
                $query->closeCursor();
            }
            $linea++;
        }
        
        @unlink($file);

        //*****************************************************************************************************************
        //PROCESO PARA SEPARAR LOS PEDIDOS DE ACUERDO A LAS ETAPAS QUE COMPRENDAN LOS COMPONENTES
        //*****************************************************************************************************************
        $folios_etapas = $folios_creados;

            foreach($folios_etapas as $fol_etapa)
            {

                $sql = "SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = :fol_etapa";
                $rs = $pdo->prepare($sql);
                $rs->execute(array('fol_etapa' => $fol_etapa));
                $resul = $rs->fetch();
                $Cve_Articulo_etapa = $resul['Cve_Articulo'];
                $rs->closeCursor();

                $sql = "SELECT MAX(IFNULL(Etapa, 0)) AS etapas FROM t_artcompuesto WHERE Cve_ArtComponente = :Cve_Articulo_etapa";
                $rs = $pdo->prepare($sql);
                $rs->execute(array('Cve_Articulo_etapa' => $Cve_Articulo_etapa));
                $resul = $rs->fetch();
                $etapas = $resul['etapas'];
                $rs->closeCursor();

                if($etapas > 0)
                {
                    for($et = 0; $et < $etapas; $et++)
                    {
                        if($et == 0)
                        {
////////////////////////////$sql = "UPDATE th_pedido SET orden_etapa = 1 WHERE Fol_folio = '$fol_etapa'";
////////////////////////////$rs = mysqli_query($conn, $sql);
                            $sql = "UPDATE th_pedido SET orden_etapa = 1 WHERE Fol_folio = :fol_etapa";
                            $rs = $pdo->prepare($sql);
                            $rs->execute(array('fol_etapa' => $fol_etapa));
                            $rs->closeCursor();
                        }
                        else
                        {
                            $sql = "SELECT `fct_consecutivo_documentos`('t_ordenprod', 6);";
                            //$rs = mysqli_query($conn, $sql);
                            //$row_folio_pro = mysqli_fetch_array($rs);
                            $rs = $pdo->prepare( $sql ); $rs->execute(); $row_folio_pro = $rs->fetch();$rs->closeCursor();
                            $Folio_Pro = "OT".$row_folio_pro[0];

                            $sql = "INSERT INTO th_pedido (Fol_folio, Fec_Pedido, Cve_clte, status, Fec_Entrega, cve_Vendedor, Num_Meses, Observaciones, statusaurora, ID_Tipoprioridad, Fec_Entrada, TipoPedido, ruta, bloqueado, DiaO, TipoDoc, rango_hora, cve_almac, destinatario, Id_Proveedor, cve_ubicacion, Pick_Num, Cve_Usuario, Ship_Num, BanEmpaque, Cve_CteProv, Activo, orden_etapa) (SELECT :Folio_Pro, Fec_Pedido, Cve_clte, status, Fec_Entrega, cve_Vendedor, Num_Meses, Observaciones, statusaurora, ID_Tipoprioridad, Fec_Entrada, TipoPedido, ruta, bloqueado, DiaO, TipoDoc, rango_hora, cve_almac, destinatario, Id_Proveedor, cve_ubicacion, Pick_Num, Cve_Usuario, Ship_Num, BanEmpaque, Cve_CteProv, Activo, ($et+1) FROM th_pedido WHERE Fol_folio = :fol_etapa)";
                            $rs = $pdo->prepare($sql);
                            $rs->execute(array('Folio_Pro' => $Folio_Pro, 
                                               'fol_etapa' => $fol_etapa
                                           ));
                            $rs->closeCursor();


                            $sql = "UPDATE td_pedido SET Fol_folio = :Folio_Pro WHERE Fol_folio = :fol_etapa AND Cve_articulo IN (SELECT Cve_Articulo FROM t_artcompuesto WHERE Cve_ArtComponente = :Cve_Articulo_etapa AND Etapa = (:et+1))";
                            $rs = $pdo->prepare($sql);
                            $rs->execute(array('Folio_Pro' => $Folio_Pro,
                                               'fol_etapa' => $fol_etapa, 
                                               'Cve_Articulo_etapa' => $Cve_Articulo_etapa, 
                                               'et' => $et));
                            $rs->closeCursor();

                            $sql = "INSERT INTO t_ordenprod (Folio_Pro, cve_almac, ID_Proveedor, Cve_Articulo, Cve_Lote, Cantidad, Cant_Prod, Cve_Usuario, Fecha, FechaReg, id_umed, Status, Referencia, id_zona_almac, idy_ubica) (SELECT :Folio_Pro, cve_almac, ID_Proveedor, Cve_Articulo, Cve_Lote, Cantidad, Cant_Prod, Cve_Usuario, Fecha, FechaReg, id_umed, Status, Referencia, id_zona_almac, idy_ubica FROM t_ordenprod WHERE Folio_Pro = :fol_etapa)";

                            $rs = $pdo->prepare($sql);
                            $rs->execute(array('Folio_Pro' => $Folio_Pro,
                                               'fol_etapa' => $fol_etapa));
                            $rs->closeCursor();

                            $sql = "UPDATE td_ordenprod SET Folio_Pro = :Folio_Pro WHERE Folio_Pro = :fol_etapa AND Cve_Articulo IN (SELECT Cve_Articulo FROM t_artcompuesto WHERE Cve_ArtComponente = :Cve_Articulo_etapa AND Etapa = (:et+1))";
                            $rs = $pdo->prepare($sql);
                            $rs->execute(array('Folio_Pro' => $Folio_Pro,
                                               'fol_etapa' => $fol_etapa, 
                                               'Cve_Articulo_etapa' => $Cve_Articulo_etapa, 
                                               'et' => $et));
                            $rs->closeCursor();
                        }



                    }
                }
                $sql_insert = "INSERT INTO td_surtidopiezas(fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, status) (SELECT Fol_folio, :id_almacen, 1, Cve_articulo, cve_lote, Num_cantidad, 0, 'S' FROM td_pedido where Fol_folio = :Folio_Pro) ON DUPLICATE KEY UPDATE Cantidad = Cantidad + VALUES(Cantidad) ;";
                $rs = $pdo->prepare($sql_insert);
                $rs->execute(array(
                                'id_almacen' => $id_almacen,
                                'Folio_Pro' => $fol_etapa
                                   ));
                $rs->closeCursor();
            }
        //*****************************************************************************************************************/
        //*****************************************************************************************************************

        $folios_sin_stock = array();
        $cod_art_compuesto = "";
        $cantidad_art_compuesto = "";
        $cve_almacen = "";
        //mysqli_close($conn);
        if($realizar_produccion == 1 && $registros > 0)
        {
            //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            foreach($folios_creados as $orden_id)
            {

                $sql = "SELECT Cve_Articulo, Cantidad, Tipo, Referencia FROM t_ordenprod WHERE Folio_Pro = :orden_id";
                $res_art = $pdo->prepare($sql);
                $res_art->execute(array('orden_id' => $orden_id));
                $row_comp = $res_art->fetch();
                $res_art->closeCursor();
                
                $cod_art_compuesto      = $row_comp["Cve_Articulo"];
                $Tipo_OT                = $row_comp["Tipo"];
                $cantidad_art_compuesto = $row_comp["Cantidad"];
                $ReferenciaProd         = $row_comp["Referencia"];
                $sql_check .= ";1;".$sql."\n\n;\n";

                if($Tipo_OT == 'IMP_LP')
                {
//*****************************************************************************************************************

                $sql = "SELECT DISTINCT ch.CveLP, t.cantidad
                        FROM t_tarima t 
                        LEFT JOIN c_charolas ch ON ch.IDContenedor = t.ntarima
                        WHERE t.Fol_Folio = :orden_id";

                $res_art = $pdo->prepare($sql);
                $res_art->execute(array('orden_id' => $orden_id));

                $sql_check .= ";2;".$sql."\n\n;\n";

                //while($row_comp = mysqli_fetch_array($res_art))
                while($row_comp = $res_art->fetch())
                {//while
                    $lp_read = $row_comp['CveLP'];
                    $cantidad_art_compuesto = $row_comp["cantidad"];
                    //$sql = "SELECT * FROM V_CantidadVSExistenciaProduccion WHERE orden_id = :orden_id";
                    $SQL_IdyUbicaOT = "";

                    if($idy_ubica_ot != '')
                       $SQL_IdyUbicaOT = " AND vp.cve_ubicacion = $idy_ubica_ot ";

                    $sql = "SELECT v.*, IFNULL(a.peso, 0) as peso, alm.id as id_almacen, MAX(v.existencia) as existencia_select
                            FROM V_CantidadVSExistenciaProduccion v 
                            LEFT JOIN c_almacenp alm on v.clave_almacen = alm.clave
                            LEFT JOIN c_articulo a ON a.cve_articulo = v.clave
                            WHERE v.orden_id = :orden_id AND v.cantnecesaria <= v.existencia
                            GROUP BY orden_id, cod_art_compuesto, clave
                            ";

                        $res_artw = "";
                        $sql_art = $sql;
                        $sql_check .= ";3;".$sql."\n\n;\n";
                        $sql_acepto = "SELECT SUM(acepto.acepto) AS acepto FROM ( ".$sql." ) AS acepto ";

                        /////////if (!($res_artw = mysqli_query($conn, $sql_acepto)))
                        /////////    echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";
                            $res_artw = $pdo->prepare($sql_acepto);
                            $res_artw->execute(array('orden_id' => $orden_id));
                            $row_art = $res_artw->fetch();
                            $res_artw->closeCursor();

                                $acepto = true;

                        ////////$row_art = mysqli_fetch_array($res_artw);
                                if($row_art['acepto'] > 0)
                                {
                                    $acepto = false;
                                    $referencias[] = $ReferenciaProd;
                                    continue;
                                }
                                $i_num_rows = 0;
                        //////////////////////////////////////////////////////////////////////////////////////////
                        //////////////////////////////////////////////////////////////////////////////////////////
                        if($acepto)
                        {//acepto

                            $sql = "UPDATE t_artcompuesto SET Cantidad_Producida = IFNULL(Cantidad_Producida, 0)+:cantidad_art_compuesto WHERE Cve_ArtComponente = :cod_art_compuesto";
                            $res = $pdo->prepare($sql);
                            $res->execute(array('cantidad_art_compuesto' => $cantidad_art_compuesto, 'cod_art_compuesto' => $cod_art_compuesto));
                            $res->closeCursor();

                            $sql_check .= ";04;".$sql."\n\n;\n";
                            $sql_check .= ";4 sql_art = ;".$sql_art."\n\n;\n";

                            $res_artw = $pdo->prepare($sql_art);
                            $res_artw->execute(array('orden_id' => $orden_id));

                            $listo = false;
                            $LoteOT = "";
                            $mensaje_error = "";
                            $caducidad  = ""; $last_idy_ubica = "";
                            //while($row_art_1 = mysqli_fetch_array(res_artw))
                            while($row_art_1 = $res_artw->fetch())
                            {
                                $cantidad = $row_art_1['cantnecesaria'];
                                $idy_ubica = $row_art_1['ubicacion'];
                                $clave = $row_art_1['clave'];
                                $Lote = $row_art_1['Lote'];
                                $LoteOT = $row_art_1['LoteOT'];
                                $caducidadMIN = $row_art_1['Caducidad'];
                                $Caduca = $row_art_1['Caduca'];
                                $Cve_Contenedor = $row_art_1['Cve_Contenedor'];
                                $ID_Contenedor = $row_art_1['Id_Contenedor'];
                                $CveLP = $row_art_1['Cve_Contenedor'];
                                $ID_Proveedor = $row_art_1['ID_Proveedor'];
                                $existencia = $row_art_1['existencia'];
                                $um = $row_art_1['um'];
                                $mav_cveunimed = $row_art_1['mav_cveunimed'];
                                $clave_almacen = $row_art_1['clave_almacen'];
                                $cve_usuario = $row_art_1['cve_usuario'];
                                $control_peso = $row_art_1['control_peso'];
                                $id_almacen   = $row_art_1['id_almacen'];

                                if($idy_ubica == '') continue;
                                if($LoteOT == '') $LoteOT = $orden_id;

                                if($control_peso == 'N' && $mav_cveunimed == 'H87') $cantidad = ceil($cantidad);

                                if($CveLP == '') $ID_Contenedor = 0;
                                $sql = "CALL SPAD_RestarPT(:id_almacen, :idy_ubica, :cve_usuario,:orden_id,:clave,:Lote,:cantidad, :CveContenedor,:ID_Contenedor)";

                                $res = $pdo->prepare($sql);
                                $res->execute(array('id_almacen' => $id_almacen, 
                                                    'idy_ubica' => $idy_ubica, 
                                                    'cve_usuario' => $cve_usuario, 
                                                    'orden_id' => $orden_id, 
                                                    'clave' => $clave, 
                                                    'Lote' => $Lote, 
                                                    'cantidad' => $cantidad, 
                                                    'CveContenedor' => $CveLP, 
                                                    'ID_Contenedor' => $ID_Contenedor
                                                ));
                                $res->closeCursor();
                                $sql_check .= ";5 SPAD_RestarPT = ;".$sql."\n\n;\n";
                            }
                            $res_artw->closeCursor();
                            $idy_ubica = $last_idy_ubica;

                                    //$sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = IFNULL(Cant_Prod, 0)+$cantidad_art_compuesto WHERE Folio_Pro = '$orden_id'";
                                $sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = Cantidad WHERE Folio_Pro = :orden_id";
                                $query = $pdo->prepare($sql);
                                $query->execute(array('orden_id' => $orden_id));
                                $query->closeCursor();


                            //xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

                                $sql = "SELECT u.cve_usuario, t.Folio_Pro, t.idy_ubica, t.Cve_Articulo, t.Cve_Lote, l.Caducidad, t.Cant_Prod, t.ID_Proveedor, tt.ntarima
                                            FROM t_ordenprod t 
                                            LEFT JOIN t_tarima tt ON tt.fol_folio = t.Folio_Pro
                                            LEFT JOIN c_usuario u ON t.Cve_Usuario IN (u.id_user, u.cve_usuario)
                                            LEFT JOIN c_lotes l ON l.cve_articulo = t.Cve_Articulo AND t.Cve_Lote = l.Lote
                                            WHERE t.Folio_Pro = :orden_id
                                            order by Caducidad DESC
                                            LIMIT 1";
                                $query_valores = $pdo->prepare($sql);
                                $query_valores->execute(array('orden_id' => $orden_id));
                                $row_valores = $query_valores->fetch();
                                $query_valores->closeCursor();

                                $cve_usuario_SP_Guardar = $row_valores['cve_usuario'];
                                $Folio_Pro_SP_Guardar = $row_valores['Folio_Pro'];
                                $idy_ubica_SP_Guardar = $row_valores['idy_ubica'];
                                $Cve_Articulo_SP_Guardar = $row_valores['Cve_Articulo'];
                                $Cve_Lote_SP_Guardar = $row_valores['Cve_Lote'];
                                $Caducidad_SP_Guardar = $row_valores['Caducidad'];
                                $Cant_Prod_SP_Guardar = $row_valores['Cant_Prod'];
                                $ID_Proveedor_SP_Guardar = $row_valores['ID_Proveedor'];
                                $nTarima_SP_Guardar = $row_valores['ntarima'];


                                $sql = "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE idy_ubica = '$idy_ubica_SP_Guardar' AND cve_articulo = '$Cve_Articulo_SP_Guardar' AND lote = '$Cve_Lote_SP_Guardar' AND ntarima = '$nTarima_SP_Guardar'";
                                $res_query = $pdo->prepare($sql);
                                $res_query->execute();
                                $row_existe = $res_query->fetch();
                                $existe_producto = $row_existe['existe'];
                                $res_query->closeCursor();


                                $sql_check .= ";6F;".$sql."\n\n;\n";

                                if($existe_producto == 0)
                                {
                                    $cod_art_compuesto = $Cve_Articulo_SP_Guardar;
                                    $sql = "SELECT DISTINCT control_lotes, Caduca FROM c_articulo WHERE cve_articulo = '$cod_art_compuesto'";
                                    $res_query2 = $pdo->prepare($sql);
                                    $res_query2->execute();
                                    $row_control = $res_query2->fetch();
                                    $res_query2->closeCursor();
                                    $control_lotes = $row_control['control_lotes'];
                                    $Caduca = $row_control['Caduca'];


                                    $sql_check .= ";9;".$sql."\n\n;\n";

                                    $LoteOT = $Cve_Lote_SP_Guardar;
                                    if($LoteOT)
                                    {
                                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', '{$caducidad}')";

                                        if($Caduca != 'S') // Si Caduca = N, se registrará una fecha de elaboración
                                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', CURDATE())";

                                        //if (!($res = mysqli_query($conn, $sql)))
                                          //  echo "Falló la preparación B: (" . mysqli_error($conn) . ") ". $sql;
                                        $res = $pdo->prepare($sql);
                                        $res->execute();
                                        $res->closeCursor();
                                    }

                                    $idy_ubica = $idy_ubica_SP_Guardar;
                                    $sql_check .= ";10;".$sql."\n\n;\n";
                                    $sql = "SELECT DISTINCT cve_almac FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = '$idy_ubica'";

                                    $res = $pdo->prepare($sql);
                                    $res->execute();
                                    $cve_almacen = $res->fetch()['cve_almac'];
                                    $res->closeCursor();

                                   $sql = "UPDATE t_ordenprod SET Cve_Lote = '{$LoteOT}' WHERE Folio_Pro = '$orden_id'";
                                    //if (!($res = mysqli_query($conn, $sql))) {
                                    //    echo "Falló la preparación jj: (" . mysqli_error($conn) . ") ";
                                    //}
                                    $res = $pdo->prepare($sql);
                                    $res->execute();
                                    $res->closeCursor();

                                    $sql_check .= ";11;".$sql."\n\n;\n";

                                    if(!$ID_Proveedor) $ID_Proveedor = 0;
                                    $sql_check .= ";11-1 idy_ubica = ;".$idy_ubica."\n\n;\n";
                                }
                                else
                                {
                                        $sql = "UPDATE ts_existenciatarima SET Existencia = Existencia + $cantidad_art_compuesto WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND lote = '$LoteOT' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read')";
                                        //echo $sql;exit;
                                        $sql_check .= ";7;".$sql."\n\n;\n";
                                        $res = $pdo->prepare($sql);
                                        $res->execute();
                                        $res->closeCursor();

                                }
                                    if(!$ID_Proveedor) $ID_Proveedor = 0;
                                    if($LoteOT == "") $LoteOT = $orden_id;

                                if($idy_ubica)
                                {
                                    $sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) (SELECT '{$id_almacen}', {$idy_ubica}, cve_articulo, '{$LoteOT}', 0, ntarima, 0, cantidad, 1, (SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}'), 0 FROM t_tarima WHERE fol_folio = '{$orden_id}')";
                                    $sql_check .= ";12;".$sql."\n\n;\n";

                                    $res = $pdo->prepare($sql);
                                    $res->execute();
                                    $res->closeCursor();

                                    $sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = Cantidad WHERE Folio_Pro = '$orden_id'";
                                    $res = $pdo->prepare($sql);
                                    $res->execute();
                                    $res->closeCursor();

                                        $sql_check .= ";13;".$sql."\n\n;\n";

                                    $sql = "SELECT 
                                                c_unimed.cve_umed,
                                                t.cve_almac as almacen_prod,
                                                t.Cve_Articulo AS cve_articulo,
                                                t.Cve_Usuario as cve_usuario,
                                                t.Cant_Prod
                                            FROM t_ordenprod t 
                                            LEFT JOIN c_articulo a ON a.cve_articulo = t.Cve_Articulo AND a.Compuesto = 'S'
                                            LEFT JOIN c_unimed ON a.unidadMedida = c_unimed.id_umed
                                            WHERE a.cve_articulo = t.Cve_Articulo AND t.Folio_Pro = '{$orden_id}'";

                                    $res = $pdo->prepare($sql);
                                    $res->execute();
                                    $row_ord = $res->fetch();
                                    $res->closeCursor();
                                    $cve_umed = $row_ord['cve_umed'];
                                    $almacen_prod = $row_ord['almacen_prod'];
                                    $cve_articulo_ord = $row_ord['cve_articulo'];
                                    $Cant_Prod_ord = $row_ord['Cant_Prod'];
                                    $cve_usuario = $row_ord['cve_usuario'];

                                $sql_tarima = "SELECT ntarima as tarima_kdx, Fol_Folio AS folio_kdx, cve_articulo AS cve_articulo_kdx, lote AS cve_lote_kdx, cantidad AS cantidad_kdx FROM t_tarima WHERE Fol_Folio = '{$orden_id}'";
                                //$res_tarima = mysqli_query($conn, $sql_tarima);
                                $res_tarima = $pdo->prepare($sql_tarima);
                                $res_tarima->execute();

                                while($row_tarima = $res_tarima->fetch())
                                {
                                    extract($row_tarima);
                                    $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('$cve_articulo_kdx', '$cve_lote_kdx', NOW(), 'PT_{$folio_kdx}', '$idy_ubica', '$cantidad_kdx', 14, '$cve_usuario', '$id_almacen')";
                                    //$res_kardex = mysqli_query($conn, $sql_kardex);
                                    $res_kardex = $pdo->prepare($sql_kardex);
                                    $res_kardex->execute();$res_kardex->closeCursor();

                                    $sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES 
                                    ((SELECT MAX(id) FROM t_cardex), '$clave_almacen', {$tarima_kdx}, CURDATE(),'PT_{$folio_kdx}', '{$idy_ubica}', 14, '{$cve_usuario}', 'I')";
                                    //$res_kardex = mysqli_query($conn, $sql_kardex);
                                    $res_kardex = $pdo->prepare($sql_kardex);
                                    $res_kardex->execute();$res_kardex->closeCursor();
                                }
                                $res_tarima->closeCursor();

                              //*******************************************************************************/
                              //*******************************************************************************
                                }
                            //xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

                            }//acepto
                            else
                            {
                                //acepto == false
                                $folios_sin_stock[] = $orden_id;
                            }

                }//while
                $res_art->closeCursor();
//*****************************************************************************************************************
                }
                else //else Tipo_OT
                {
                $sql = "
                SELECT op.clave, op.control_lotes, op.Lote, op.LoteOT, op.Caduca, op.Caducidad, op.control_peso, op.Cantidad, op.Cantidad_Producida, op.ubicacion, IFNULL(op.existencia, 0) AS existencia, op.Cve_Contenedor, op.mav_cveunimed FROM (
                    SELECT DISTINCT 
                        a.cve_articulo AS clave,
                        IFNULL(a.control_lotes, 'N') AS control_lotes,
                        e.cve_lote AS Lote,
                        t.Cve_Lote AS LoteOT,
                        IFNULL(a.control_peso, 'N') AS control_peso,
                        IFNULL(a.Caduca, 'N') AS Caduca,
                        IF(a.Caduca = 'S', l.Caducidad, '') AS Caducidad,
                        IF(IFNULL(th.Fol_folio, '') = '', ac.Cantidad, ac.Cantidad/(SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '$orden_id')) AS Cantidad,
                        (ac.Cantidad_Producida*ac.Cantidad) AS Cantidad_Producida,

                        t.idy_ubica as ubicacion,

                        e.Cve_Contenedor,
                        t.Cve_Usuario as cve_usuario,
                        u.mav_cveunimed,
                        e.Existencia AS existencia 
                    FROM t_artcompuesto ac
                        LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                        LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro
                        LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
                        LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = td.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = td.Folio_Pro)
                        LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_Articulo AND l.LOTE = e.cve_lote
                        LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
                        LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                    WHERE t.Folio_Pro = '$orden_id' AND e.cve_almac = '{$id_almacen}' 
                    #AND e.cve_lote = td.Cve_Lote AND e.cve_articulo = td.Cve_Articulo
                    AND t.idy_ubica = '{$idy_ubica_ot}'
                    AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = td.Cve_Articulo #AND e.cve_lote = td.Cve_Lote
                    ORDER BY Caducidad
                ) AS op WHERE op.ubicacion IS NOT NULL";

                $res_art = "";
                $sql_art = $sql;
                //if (!($res_art = mysqli_query($conn, $sql)))
                //    echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";
                $res_art = $pdo->prepare( $sql ); $res_art->execute(); 

                $acepto = true;

                //while($row_art = mysqli_fetch_array($res_art))
                while($row_art = $res_art->fetch())
                {
                    //+$row_art['Cantidad_Producida'] 
                    //&& $row_art['existencia'] > 0
                    if(($row_art['Cantidad']*$cantidad_art_compuesto) > $row_art['existencia'] )
                    {
                        $acepto = false;
                        break;
                    }
                }

                if($acepto == false)
                    $folios_sin_stock[] = $orden_id;
                else
                {
                    while($row_art_1 = $res_art->fetch())
                    {
                        //if($Tipo_OT == 'IMP_LP') $cantidad_art_compuesto = $row_art_1['existencia'];
                        $cantidad = $row_art_1['Cantidad']*$cantidad_art_compuesto;
                        $idy_ubica = $row_art_1['ubicacion'];
                        $clave = $row_art_1['clave'];
                        $Lote = $row_art_1['Lote'];
                        $LoteOT = $row_art_1['LoteOT'];
                        $caducidadMIN = $row_art_1['Caducidad'];
                        $Caduca = $row_art_1['Caduca'];
                        $Cve_Contenedor = $row_art_1['Cve_Contenedor'];
                        $cve_usuario = $row_art_1['cve_usuario'];
                        $mav_cveunimed = $row_art_1['mav_cveunimed'];
                        $control_peso = $row_art_1['control_peso'];

                        //if($idy_ubica == "") $mensaje_error .= "El Producto {$clave} no se encuentra en una ubicación de Producción\n";
                        $caducidad = "";
                        $listo = false;
                        $LoteOT = "";
                        $mensaje_error = "";

                        if($Caduca == 'S' && $caducidadMIN != '' && $caducidadMIN != '0000-00-00' && $listo == false)
                        {
                            $caducidad = $caducidadMIN;
                            $listo = true;
                        }

                        if($control_peso == 'N' && $mav_cveunimed == 'H87') $cantidad = ceil($cantidad);

                        $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and cve_lote = '$Lote'";

                        if($Cve_Contenedor != '')
                            $sql = "UPDATE ts_existenciatarima SET existencia = existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and lote = '$Lote' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')";

                        //if (!($res = mysqli_query($conn, $sql))) 
                        //    echo "Falló la preparación G: (" . mysqli_error($conn) . ") ";
                        $res = $pdo->prepare( $sql ); $res->execute(); $res->closeCursor();

                        $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$clave}', '{$Lote}', NOW(), '{$idy_ubica}', '{$orden_id}', {$cantidad}, 8, '{$cve_usuario}','{$id_almacen}')";
                        //$res_kardex = mysqli_query($conn, $sql_kardex);
                        $res_kardex = $pdo->prepare( $sql ); $res_kardex->execute(); $res_kardex->closeCursor();
                        //  }

                        //**************************************************************************************************************************
                        $sql = "SELECT COUNT(*) as existe FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND cve_lote = '$LoteOT'";

                        //if (!($res = mysqli_query($conn, $sql)))
                        //    echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
                        $res = $pdo->prepare( $sql ); $res->execute(); 
                        //$existe_producto = mysqli_fetch_array($res)['existe'];
                        $existe_producto = $res->fetch()['existe'];
                        $res->closeCursor();


                        $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia + $cantidad_art_compuesto WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND cve_lote = '$LoteOT'";

                        if($existe_producto == 0)
                        {
                            $sql = "SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";
                            //if (!($res = mysqli_query($conn, $sql)))
                            //    echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
                            $res = $pdo->prepare( $sql ); $res->execute(); 
                            //$ID_Proveedor = mysqli_fetch_array($res)['ID_Proveedor'];
                            $ID_Proveedor = $res->fetch()['ID_Proveedor'];$res->closeCursor();

                            $sql = "SELECT DISTINCT control_lotes, Caduca FROM c_articulo WHERE cve_articulo = '$cod_art_compuesto'";
                            //if (!($res = mysqli_query($conn, $sql)))
                            //    echo "Falló la preparación E: (" . mysqli_error($conn) . ") ";
                            $res = $pdo->prepare( $sql ); $res->execute(); 
                            //$row_control = mysqli_fetch_array($res);
                            $row_control = $res->fetch();$res->closeCursor();
                            $control_lotes = $row_control['control_lotes'];
                            $Caduca = $row_control['Caduca'];

                            if($LoteOT)
                            {
                                $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', '{$caducidad}')";

                                if($Caduca != 'S') // Si Caduca = N, se registrará una fecha de elaboración
                                $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', CURDATE())";

                                //if (!($res = mysqli_query($conn, $sql)))
                                //    echo "Falló la preparación B: (" . mysqli_error($conn) . ") ". $sql;
                                $res = $pdo->prepare( $sql ); $res->execute(); $res->closeCursor(); 
                            }

                            //$sql = "SELECT DISTINCT cve_almac FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica'";
                            $sql = "SELECT DISTINCT cve_almac FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = '$idy_ubica'";
                            //if (!($res = mysqli_query($conn, $sql)))
                            //    echo "Falló la preparación A: (" . mysqli_error($conn) . ") ";
                            $res = $pdo->prepare( $sql ); $res->execute(); 
                            //$cve_almacen = mysqli_fetch_array($res)['cve_almac'];
                            $cve_almacen = $res->fetch()['cve_almac'];$res->closeCursor();

                            if($LoteOT == "") $LoteOT = $orden_id;
                           $sql = "UPDATE t_ordenprod SET Cve_Lote = '{$LoteOT}' WHERE Folio_Pro = '$orden_id'";
                            //if (!($res = mysqli_query($conn, $sql))) {
                            //    echo "Falló la preparación jj: (" . mysqli_error($conn) . ") ";
                            //}
                           $res = $pdo->prepare( $sql ); $res->execute(); $res->closeCursor();

                            if(!$ID_Proveedor) $ID_Proveedor = 0;
                           $sql = "INSERT IGNORE INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) VALUES ('{$cve_almacen}', {$idy_ubica}, '{$cod_art_compuesto}', '{$LoteOT}', {$cantidad_art_compuesto}, {$ID_Proveedor})";
                        }

                        if($idy_ubica)
                        {//if($idy_ubica)02547

                            //if (!($res = mysqli_query($conn, $sql))) {
                            //    echo "Falló la preparación Z: (" . mysqli_error($conn) . ") " . $sql;
                            //}
                            $res = $pdo->prepare( $sql ); $res->execute(); $res->closeCursor();

                            $sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = IFNULL(Cant_Prod, 0)+$cantidad_art_compuesto WHERE Folio_Pro = '$orden_id'";
                            //if (!($res = mysqli_query($conn, $sql))) {
                            //    echo "Falló la preparación I: (" . mysqli_error($conn) . ") ";
                            //}
                            $res = $pdo->prepare( $sql ); $res->execute(); $res->closeCursor();

                            $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('$cod_art_compuesto', '$LoteOT', NOW(), 'PT_{$orden_id}', '$idy_ubica', '$cantidad_art_compuesto', 14, '$cve_usuario', '$id_almacen')";
                            //$res_kardex = mysqli_query($conn, $sql_kardex);
                            $res_kardex = $pdo->prepare( $sql_kardex ); $res_kardex->execute(); $res_kardex->closeCursor();

                              //*******************************************************************************
                              //                          EJECUTAR EN INFINITY
                              //*******************************************************************************
                              $sql = "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'";
                              //$query = mysqli_query($conn, $sql);
                              $query = $pdo->prepare( $sql ); $query->execute(); 
                              //$ejecutar_infinity = mysqli_fetch_array($query)['existe'];
                              $ejecutar_infinity = $query->fetch()['existe'];
                              $query->closeCursor();

                              if($ejecutar_infinity)
                              {//if($ejecutar_infinity)00214
                                    $sql = "SELECT 
                                                c_unimed.cve_umed,
                                                t.Cve_Articulo AS cve_articulo,
                                                t.Cant_Prod
                                            FROM t_ordenprod t 
                                            LEFT JOIN c_articulo a ON a.cve_articulo = t.Cve_Articulo AND a.Compuesto = 'S'
                                            LEFT JOIN c_unimed ON a.unidadMedida = c_unimed.id_umed
                                            WHERE a.cve_articulo = t.Cve_Articulo AND t.Folio_Pro = '{$orden_id}'";
                                    //$query = mysqli_query($conn, $sql);
                                    $query = $pdo->prepare( $sql ); $query->execute(); 
                                    //$row_ord = mysqli_fetch_array($query);
                                    $row_ord = $query->fetch();
                                    $cve_umed = $row_ord['cve_umed'];
                                    $cve_articulo_ord = $row_ord['cve_articulo'];
                                    $Cant_Prod_ord = $row_ord['Cant_Prod'];
                                    $query->closeCursor();


                                    $sql = "SELECT Url, Servicio, User, Pswd, Empresa, to_base64(CONCAT(User,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1";
                                    //$query = mysqli_query($conn, $sql);
                                    $query = $pdo->prepare( $sql ); $query->execute(); 
                                    //$row_infinity = mysqli_fetch_array($query);
                                    $row_infinity = $query->fetch();$query->closeCursor();
                                    $Url_inf = $row_infinity['Url'];
                                    $url_curl = $row_infinity['url_curl'];
                                    $Servicio_inf = $row_infinity['Servicio'];
                                    $User_inf = $row_infinity['User'];
                                    $Pswd_inf = $row_infinity['Pswd'];
                                    $Empresa_inf = $row_infinity['Empresa'];
                                    $hora_movimiento = $row_infinity['hora_movimiento'];
                                    $Codificado = $row_infinity['Codificado'];


                                      $json = "[";
                                        extract($row_ord);
                                            $LoteOT = "";
                                        $json .= "{";
                                        $json .= '"item":"'.$cve_articulo_ord.'","um":"'.$cve_umed.'","batch":"'.$LoteOT.'", "qty": '.$Cant_Prod_ord.',"typeMov":"T","warehouse":"'.$cve_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                                        $json .= "}";
                                      //$json[strlen($json)-1] = ' ';
                                      $json .= "]";


                                  $curl = curl_init();
                                  //$url_curl = $Url_inf.':8080/'.$Servicio_inf;

                                  curl_setopt_array($curl, array(
                                    // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                                    CURLOPT_URL => "$url_curl",
                                    //CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => '',
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => 'POST',
                                    CURLOPT_POSTFIELDS =>
                                    // Aquí cambia la cadena JSON
                                    $json,
                                    CURLOPT_HTTPHEADER => array(
                                      'Content-Type: application/json',
                                      //'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
                                      'Authorization: Basic '.$Codificado.''
                                    )
                                    ,CURLOPT_SSL_VERIFYHOST => false,
                                    CURLOPT_SSL_VERIFYPEER => false
                                  ));

                                  $response = curl_exec($curl);
                                  //$response_ot .= $response."\n";

                                  curl_close($curl);      
                                  //echo $response;
                                  
                                  //$response = 'Pendiente';
                                  $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', 'Transformacion', 'WEB')";
                                  //$query = mysqli_query($conn, $sql);
                                  $query = $pdo->prepare( $sql ); $query->execute(); $query->closeCursor();

                              }//if($ejecutar_infinity)00214
                              //*******************************************************************************/
                              //*******************************************************************************
                        }//if($idy_ubica)02547

//**************************************************************************************************************************

                    }//while
                }//else
                $res_art->closeCursor();
                }//else Tipo_OT



            }//foreach
        }
        $msj_sin_stock = "";
        if(count($folios_sin_stock) > 0)
        {
            $folios_implode = implode($folios_sin_stock, ", ");
            $msj_sin_stock = "Los Folios: $folios_implode No poseen Stock Para producir, puede producirlos en Administración de OT después de surtir material";
        }
        $referencias_sin_stock = "";
        if(count($referencias) > 0)
        {
            $folios_implode = implode($referencias, ", ");
            $msj_sin_stock = "Las Referencias: $folios_implode No poseen Stock Para producir, puede producirlos en Administración de OT después de surtir material o volver a importar cuando surta los componentes";
        }

        $msj_creados = "";
        if(count($folios_creados) > 0)
        {
            $folios_implode = implode($folios_creados, ", ");
            $msj_creados = "Fueron Creados Los Folios: $folios_implode \n\n";
        }

        if($realizar_produccion && $ejecutar_infinity && $msj_creados)
        {
            $folios_implode = implode($folios_creados, "','");
            $folios_implode = "'".$folios_implode."'";

            //************************************************************************************************************
            //                          ENVIAR A INFINITY LOS PRODUCTOS TERMINADOS
            //************************************************************************************************************

            $sql = "SELECT DISTINCT
                        c_unimed.cve_umed,
                        t.Cve_Articulo AS cve_articulo,
                        IF(t.Cve_Lote = t.Folio_Pro, '', IFNULL(t.Cve_Lote, '')) AS LoteOT,
                        alm.clave AS clave_almacen,
                        alm.id AS id_almacen,
                        t.Status,
                        t.idy_ubica,
                        t.Cant_Prod
                    FROM t_ordenprod t 
                    LEFT JOIN c_articulo a ON a.cve_articulo = t.Cve_Articulo AND a.Compuesto = 'S'
                    LEFT JOIN c_unimed ON a.unidadMedida = c_unimed.id_umed
                    LEFT JOIN c_almacenp alm ON alm.id = t.cve_almac 
                    WHERE a.cve_articulo = t.Cve_Articulo AND t.Folio_Pro IN ($folios_implode)";
                    //$query = mysqli_query($conn, $sql);
                    //$row_ord = mysqli_fetch_array($query);
                    $query = $pdo->prepare($sql);
                    $query->execute();

                    while($row_ord = $query->fetch())
                    {
                        $cve_umed = $row_ord['cve_umed'];
                        $cve_articulo_ord = $row_ord['cve_articulo'];
                        $Cant_Prod_ord = $row_ord['Cant_Prod'];
                        $LoteOT = $row_ord['LoteOT'];
                        $clave_almacen = $row_ord['clave_almacen'];
                        $StatusOT = $row_ord['Status'];
                        $id_almacen = $row_ord['id_almacen'];
                        $idy_ubica = $row_ord['idy_ubica'];

                        $sql_prod = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = :cve_articulo_ord and cve_almac = :id_almacen AND tipo = 'ubicacion'";
                        $query_prod = $pdo->prepare($sql_prod);
                        $query_prod->execute(array('cve_articulo_ord' => $cve_articulo_ord, 'id_almacen' => $id_almacen));
                        $row_ord_prod = $query_prod->fetch();
                        $existencia_art_prod = $row_ord_prod['existencia_art_prod'];
                        $query_prod->closeCursor();

                        if(!$existencia_art_prod) $existencia_art_prod = 0;

                          $json = "[";
                                $LoteOT = "";
                            $json .= "{";
                            $json .= '"item":"'.$cve_articulo_ord.'","um":"'.$cve_umed.'","batch":"", "qty": '.$existencia_art_prod.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                            $json .= "}";
                          //$json[strlen($json)-1] = ' ';
                          $json .= "]";

                        $interfase->RegistrarCadena($json, $Servicio_inf, $Codificado, $url_curl, 'Transformacion', 'WEB', $pdo);
                    }
                    $query->closeCursor();

            //************************************************************************************************************
            //                                      ENVIAR LOS COMPONENTES A INFINITY
            //************************************************************************************************************
                    $sql = "SELECT DISTINCT v.Cve_Articulo as clave, u.cve_umed as um, IFNULL(v.cve_lote, '') as Lote, IFNULL(CONVERT(SUM(eg.Existencia), FLOAT), 0) as existencia_art_prod, alm.clave as clave_almacen
                            FROM t_cardex v 
                            LEFT JOIN t_ordenprod t ON t.Folio_Pro = v.destino
                            LEFT JOIN c_almacenp alm on t.cve_almac = alm.id
                            LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
                            LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                            LEFT JOIN V_ExistenciaGralProduccion eg ON eg.cve_almac = alm.id and eg.tipo = 'ubicacion' and eg.cve_articulo = v.cve_articulo and IFNULL(eg.Cve_Lote, '') = IFNULL(v.cve_lote, '')
                            WHERE v.destino IN ($folios_implode) 
                            AND v.id_TipoMovimiento = 8 
                            GROUP BY v.Cve_Articulo, IFNULL(v.cve_lote, '')";
                    $query = $pdo->prepare($sql);
                    $query->execute();
                    //$query->execute(array('clave' => $clave, 'cve_lote' => $Lote,'id_almacen' => $id_almacen));
                        while($row_ord = $query->fetch())
                        {
                            extract($row_ord);
                              $json = "[";

                                $json .= "{";
                                $json .= '"item":"'.$clave.'","um":"'.$um.'","batch":"'.$Lote.'", "qty": '.$existencia_art_prod.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                                $json .= "}";
                              //$json[strlen($json)-1] = ' ';
                              $json .= "]";
                            $interfase->RegistrarCadena($json, $Servicio_inf, $Codificado, $url_curl, 'Transformacion', 'WEB', $pdo);
                        }
                        $query->closeCursor();
            //************************************************************************************************************
        }
        $pdo->commit();
        $pdo = null;
        //@mysqli_close($conn);
        $this->response(200, [
            'statusText' =>  "Ordenes de Producción importados con exito. Total de Ordenes: \"{$registros}\" \n\n $msj_creados \n\n $msj_sin_stock \n\n$msj_folio_vacio",
            'msj_tracking' => $sql_tracking,
            'folios_creados' => $folios_creados,
            'realizar_produccion' => $realizar_produccion,
            'sql_check' => $sql_check,
            'responses' => $response_ot,
            'url_curl' => "{$url_curl}",
            'articulos' => $articulos
        ]);
        } catch (\Exception $e) {
            $pdo->rollBack();

                // Si es un deadlock, permitimos que el bucle `for` continúe para reintentar.
                if ($e->errorInfo[1] == 1213) { 
                    if ($i < $intentos_maximos - 1) {
                        usleep(250000); // Espera antes del siguiente intento
                        continue; // Pasa a la siguiente iteración del bucle for
                    }
                }
                
                // Si no es un deadlock o es el último intento, lanza el error y detiene todo.
                echo "Error en la importación: " . $e->getMessage();
                throw $e;
        }
    }//for ($i = 0; $i < $intentos_maximos; $i++) 


    }

    //public function importar_Ver_Anterior()
    public function importar()
    {
        //set_time_limit(12000);
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }

        $interfase = new \Interfases\Interfases();
/*
        if ( $_FILES['file']['type'] != 'application/vnd.ms-excel' AND
                $_FILES['file']['type'] != 'application/msexcel' AND
                $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' AND
                $_FILES['file']['type'] != 'application/xls' )
            {
            @unlink($file);
            $this->response(400, [
                'statusText' =>  "Error en el formato del fichero",
            ]);
        }
*/
        $referencias = array();
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
////////$sql = "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'";
////////$query = mysqli_query($conn, $sql);
////////$ejecutar_infinity = mysqli_fetch_array($query)['existe'];
        //$sql = "SELECT Url, Servicio, User, Pswd, Empresa, TO_BASE64(CONCAT(User,':',Pswd)) as Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1";
        $query = mysqli_query($conn, "SELECT Url, Servicio, User, Pswd, IFNULL(Puerto, '8080') as Puerto, Empresa, to_base64(CONCAT(USER,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1");
        $row_infinity = mysqli_fetch_assoc($query);

        try{
//        $query = \db()->prepare($sql);
//        $query->execute();
//        $row_infinity = $query->fetch();

        $Url_inf = $row_infinity['Url'];
        $url_curl = $row_infinity['url_curl'];
        $Servicio_inf = $row_infinity['Servicio'];
        $User_inf = $row_infinity['User'];
        $Pswd_inf = $row_infinity['Pswd'];
        $Empresa_inf = $row_infinity['Empresa'];
        $hora_movimiento = $row_infinity['hora_movimiento'];
        $Codificado = $row_infinity['Codificado'];

        } catch (PDOException $e) {
            echo 'Error de conexión: ' . $e->getMessage();
        }

        $sql = "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'";
    $pdo = \db();
    try{
        $result = $pdo->prepare($sql);
        $result->execute();
        $row_inf = $result->fetch();
        $ejecutar_infinity = $row_inf['existe'];
    } catch (PDOException $e) {
        echo 'Error de conexión: ' . $e->getMessage();
    }


        $xlsx = new SimpleXLSX( $file );
/*
        $linea = 1;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }

            $eval = $this->validarRequeridosImportar($row);
            if( $eval === TRUE ){
            }

            else {
                $this->response(400, [
                    'statusText' =>  "La columna \"{$eval}\" de la Fila Nro. {$linea} está vacía. Corrija esto para continuar con la importación.",
                ]);
            }

            $linea++;
        }
*/
        $response_ot = "";
        $url_curl = "";
        $Folio_vacio = ""; $Folio_Anterior = "";$sql_check = ""; $OT_Folio_Anterior = "";
        $linea = 1; $registros = 0;
        $id_proveedor        = $_POST['Proveedor2'];
        $idy_ubica_ot        = $_POST['idy_ubica_ot_import'];
        $realizar_produccion = $_POST['realizar_produccion'];
        $folios_creados = array();

        $Folio_Pro = "";
        $registros_vacios = 0;


        $tipo_traslado_input = $_POST["tipo_traslado_input"];
        $almacen_dest = $_POST["almacen_dest"];
        $traslado_interno_externo_input = $_POST["traslado_interno_externo_input"];

        foreach ($xlsx->rows() as $row)
        {
            $ot_cliente = $this->pSQL($row[self::OT_CLIENTE]);

            if($linea == 1 || $ot_cliente == "") {
                $registros_vacios++;
                $linea++;continue;
            }
            //$clave = $this->pSQL($row[self::CODIGO_BL]);
            //$element = Ubicaciones::where('CodigoCSD', '=', $clave)->first();
/*
            $tipo = '';
            if($row[self::LIBRE] == 'S') $tipo = 'L';
            if($row[self::CUARENTENA] == 'S') $tipo = 'Q';
            if($row[self::RESERVADA] == 'S') $tipo = 'R';
*/
            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new OrdenDeProduccion(); 
            }

            //$model = new Ubicaciones(); 
            //$id_zona = ZonasDeAlmacenaje::where('clave_almacen', "'".$row[self::ZONA]."'")->get(['cve_
            //$Folio_Pro = $this->pSQL($row[self::FOLIO_ORDEN]);

            //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $cve_articulo = $row[self::CVE_ART_COMPUESTO];
////////////$sql = "SELECT COUNT(*) AS existe FROM t_artcompuesto WHERE Cve_ArtComponente = '{$cve_articulo}'";
////////////$rs = mysqli_query($conn, $sql);
////////////$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
////////////$existe = $resul['existe'];

            try{
            $sql = "SELECT COUNT(*) AS existe FROM t_artcompuesto WHERE Cve_ArtComponente = :cve_articulo";
            $rs = $pdo->prepare($sql);
            $rs->execute(array('cve_articulo' => $cve_articulo));
            $row_existe = $rs->fetch();
            $existe = $row_existe['existe'];
            } catch (PDOException $e) {
                echo 'Error de conexión: ' . $e->getMessage();
            }


            $existe_folio = 0;
            if($Folio_Pro != '')
            {
                if($tipo_traslado_input == 1)
                {
////////////////////$sql = "SELECT COUNT(*) AS existe_folio FROM th_pedido WHERE Fol_Folio = '{$Folio_Pro}' AND Pick_Num = '{$ot_cliente}'";
////////////////////$rs = mysqli_query($conn, $sql);
////////////////////$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
////////////////////$existe_folio = $resul['existe_folio'];

                    $sql = "SELECT COUNT(*) AS existe_folio FROM th_pedido WHERE Fol_Folio = :Folio_Pro AND Pick_Num = :ot_cliente";
                    try{
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('Folio_Pro' => $Folio_Pro, 'ot_cliente' => $ot_cliente));
                    $resul = $rs->fetch();
                    $existe_folio = $resul['existe_folio'];
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                }
                else
                {
////////////////////$sql = "SELECT COUNT(*) AS existe_folio FROM t_ordenprod WHERE Folio_Pro = '{$Folio_Pro}' AND Referencia = '{$ot_cliente}'";
////////////////////$rs = mysqli_query($conn, $sql);
////////////////////$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
////////////////////$existe_folio = $resul['existe_folio'];

                    $sql = "SELECT COUNT(*) AS existe_folio FROM t_ordenprod WHERE Folio_Pro = :Folio_Pro AND Referencia = :ot_cliente";
                    try{
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('Folio_Pro' => $Folio_Pro, 'ot_cliente' => $ot_cliente));
                    $resul = $rs->fetch();
                    $existe_folio = $resul['existe_folio'];
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                }
            }

            //if($Folio_Pro == "" && $Folio_vacio == "")
            if($existe_folio == 0)
            {
                if($tipo_traslado_input == 1)
                {
                    $Folio_Pro = $this->consecutivo_folio_traslado();
                }
                else 
                {
                    $sql = "SELECT `fct_consecutivo_documentos`('t_ordenprod', 6);";
                    $rs = mysqli_query($conn, $sql);
                    $row_folio_pro = mysqli_fetch_array($rs);
                    $Folio_Pro = "OT".$row_folio_pro[0];
                }
                $Folio_vacio = $Folio_Pro;
            }
            else if($Folio_Pro == "" && $Folio_vacio != "")
            {
                $Folio_Pro = $Folio_vacio;
            }

/*
            $existe_ot_cliente = 2;//ot_cliente vacio
            if($ot_cliente != "")
            {
                $sql = "SELECT COUNT(*) AS existe_ot_cliente FROM t_ordenprod WHERE Referencia = '{$ot_cliente}'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $existe_ot_cliente = $resul['existe_ot_cliente'];
                //existe_ot_cliente = 1 -> Existe
                //existe_ot_cliente = 0 -> No Existe
            }
*/

            $sql = "SELECT id from c_almacenp where id = (SELECT cve_almacenp FROM c_almacen where cve_almac = ".$_POST['cboZonaAlmacenImport'].") ;";
            $query = mysqli_query($conn, $sql);
            if($query->num_rows > 0)
            {
                $id_almacen = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["id"];

            if($existe && $existe_folio == 0)
            {//3
                $fa = getdate();
                $dia  = $fa['mday'];
                $mes  = $fa['mon'];
                $year = $fa['year'];
                if($dia < 10) $dia = '0'.$dia;
                if($mes < 10) $mes = '0'.$mes;
                $fecha = $year.'-'.$mes.'-'.$dia;

                $fecha_compromiso = $this->pSQL($row[self::FECHA_COMPROMISO]);
                if($fecha_compromiso)
                {//1
                    $fc = explode('-', $fecha_compromiso);
                    $fecha_compromiso = $fc[2]."-".$fc[1]."-".$fc[0];
                }//1

                if($tipo_traslado_input != 1)
                {
                    $model->Folio_Pro     = $Folio_Pro;
                    $model->cve_almac     = $id_almacen;//$this->pSQL($row[self::AREA_DE_PRODUCCION]);
                    $model->Cve_Articulo  = $this->pSQL($row[self::CVE_ART_COMPUESTO]);
                    $model->Cve_Lote      = $this->pSQL($row[self::LOTE]);
                    $model->Cantidad      = $this->pSQL($row[self::CANTIDAD_A_PRODUCIR]);
                    $model->ID_Proveedor  = $id_proveedor;//$this->pSQL($row[self::AREA_DE_PRODUCCION]);
                    $model->Cant_Prod     = 0;
                    $model->Cve_Usuario   = $this->pSQL($_SESSION['id_user']);
                    $model->Fecha         = $fecha;
                    $model->Referencia    = $ot_cliente;
                    $model->FechaReg      = ($fecha_compromiso !='')?($fecha_compromiso):($fecha);
                    $model->Status        = 'P';
                    $model->id_zona_almac = $_POST['cboZonaAlmacenImport'];
                    $model->idy_ubica     = $idy_ubica_ot;

                    $model->save();
                }

                $articulos = array();
                $nuevo_pedido = new \NuevosPedidos\NuevosPedidos();
////////////////$sql = "SELECT Cve_Articulo, Cantidad FROM t_artcompuesto WHERE Cve_ArtComponente = '{$cve_articulo}' AND Cve_Articulo IN (SELECT cve_articulo FROM c_articulo WHERE IFNULL(tipo_producto, '') != 'ProductoNoSurtible')";
////////////////$res = mysqli_query($conn, $sql);
////////////////while($row_orden = mysqli_fetch_array($res, MYSQLI_ASSOC))

                $sql = "SELECT Cve_Articulo, Cantidad FROM t_artcompuesto WHERE Cve_ArtComponente = :cve_articulo AND Cve_Articulo IN (SELECT cve_articulo FROM c_articulo WHERE IFNULL(tipo_producto, '') != 'ProductoNoSurtible')";
                try{
                $res = $pdo->prepare($sql);
                $res->execute(array('cve_articulo' => $cve_articulo));
                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }
                while($row_orden = $res->fetch())
                {//2
                    if($tipo_traslado_input != 1)
                    {
                        /*
                        $sql = "CALL SPAD_AddUpdateTDOrdenProd (";
                        $sql .= "'".$Folio_Pro."','".$row_orden['Cve_Articulo']."',NOW(),'".$row_orden['Cantidad']."','".$_SESSION['id_user']."','1');";
                        $rs = mysqli_query($conn, $sql);
                        */
                        //***************************************************************************
                        //CAMBIAR POR INSERT IGNORE / INSERT DUPLICATE KEY UPDATE
                        //***************************************************************************
                        /*
                        $sql = "SELECT COUNT(*) as existe FROM td_ordenprod WHERE Folio_Pro = '$Folio_Pro' AND Cve_Articulo = '".$row_orden['Cve_Articulo']."'";
                        $res_ord = mysqli_query($conn, $sql);
                        $existe_row_ord = mysqli_fetch_array($res_ord, MYSQLI_ASSOC)['existe'];

                        if($existe_row_ord)
                        {
                            $sql = "UPDATE  td_ordenprod
                                    SET     Cantidad = Cantidad + ".$row_orden['Cantidad']."
                                    WHERE   Folio_Pro='$Folio_Pro' AND Cve_Articulo='".$row_orden['Cve_Articulo']."'";
                            $res_ord = mysqli_query($conn, $sql);
                        }
                        else 
                        {
                            $sql = "INSERT INTO td_ordenprod(Folio_Pro,Cve_Articulo,Fecha_Prod,Cantidad,Usr_Armo,Activo) VALUES 
                                    ('".$Folio_Pro."','".$row_orden['Cve_Articulo']."',NOW(),'".$row_orden['Cantidad']."','".$_SESSION['id_user']."','1')";
                            $res_ord = mysqli_query($conn, $sql);
                        }
                        */
//////////////////////////////$sql = "INSERT INTO td_ordenprod(Folio_Pro,Cve_Articulo,Fecha_Prod,Cantidad,Usr_Armo,Activo) VALUES 
//////////////////////////////        ('".$Folio_Pro."','".$row_orden['Cve_Articulo']."',NOW(),'".$row_orden['Cantidad']."','".$_SESSION['id_user']."','1') ON DUPLICATE KEY UPDATE Cantidad = Cantidad + '".$row_orden['Cantidad']."'";
///////////////////////////////$res_ord = mysqli_query($conn, $sql);

                            $sql = "INSERT INTO td_ordenprod(Folio_Pro,Cve_Articulo,Fecha_Prod,Cantidad,Usr_Armo,Activo) VALUES (:Folio_Pro,:Cve_Articulo,NOW(),:Cantidad,:id_user,1) ON DUPLICATE  KEY UPDATE Cantidad = Cantidad + :Cantidad";
                            try{
                            $res_ord = $pdo->prepare($sql);
                            $res_ord->execute(array('Folio_Pro' => $Folio_Pro,
                                                    'Cve_Articulo' => $row_orden['Cve_Articulo'],
                                                    'Cantidad' => $row_orden['Cantidad'],
                                                    'id_user' => $_SESSION['id_user']));
                            } catch (PDOException $e) {
                                echo 'Error de conexión: ' . $e->getMessage();
                            }

                        //***************************************************************************
                        //***************************************************************************

                    }
                    $cve_art = $row_orden['Cve_Articulo'];
////////////////////$sql_art = "SELECT a.control_peso, a.peso, u.mav_cveunimed, a.unidadMedida FROM c_articulo a LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida WHERE a.cve_articulo = '$cve_art'";
////////////////////$rs_art = mysqli_query($conn, $sql_art);
////////////////////$row_art = mysqli_fetch_array($rs_art);
//////////////////////$band_granel = $row_art["control_peso"];
//////////////////////$peso = $row_art["peso"];
//////////////////////$mav_cveunimed = $row_art["mav_cveunimed"];
////////////////////$unidadMedida = $row_art["unidadMedida"];

                    $lp = $this->pSQL($row[self::LP]);
                    $sql_art = "SELECT a.control_peso, a.peso, u.mav_cveunimed, a.unidadMedida FROM c_articulo a LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida WHERE a.cve_articulo = :cve_art";
                    try{
                    $rs_art = $pdo->prepare($sql_art);
                    $rs_art->execute(array('cve_art' => $cve_art));
                    $row_art = $rs_art->fetch();
                    $unidadMedida = $row_art["unidadMedida"];
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                    array_push($articulos,array(
                        "Cve_articulo" => $row_orden['Cve_Articulo'],
                        "Num_cantidad" => ($row_orden['Cantidad']*$this->pSQL($row[self::CANTIDAD_A_PRODUCIR])),
                        "id_unimed" => $unidadMedida,
                        "Num_Meses" => ""
                    ));

                }//2
/*
                $sql = "SELECT clave from c_almacenp where id = (SELECT cve_almacenp FROM c_almacen where cve_almac = ".$_POST['cboZonaAlmacenImport'].") ;";
                $query = mysqli_query($conn, $sql);
                if($query->num_rows > 0){
                    $id_almacen = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["clave"];
*/
                $lote = $this->pSQL($row[self::LOTE]);
                $caducidad = $this->pSQL($row[self::CADUCIDAD]);
////////////////$sql = "SELECT IFNULL(control_lotes, 'N') as control_lotes, IFNULL(Caduca, 'N') as Caduca FROM c_articulo WHERE cve_articulo = '{$cve_articulo}'";
////////////////$rs = mysqli_query($conn, $sql);
////////////////$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
////////////////$control_lotes = $resul['control_lotes'];
////////////////$Caduca = $resul['Caduca'];

                $sql = "SELECT IFNULL(control_lotes, 'N') as control_lotes, IFNULL(Caduca, 'N') as Caduca FROM c_articulo WHERE cve_articulo = :cve_articulo";
                try{
                $rs = $pdo->prepare($sql);
                $rs->execute(array('cve_articulo' => $cve_articulo));
                $resul = $rs->fetch();
                $control_lotes = $resul['control_lotes'];
                $Caduca = $resul['Caduca'];
                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }


////////////////$sql = "SELECT COUNT(*) as existe_lote FROM c_lotes WHERE cve_articulo = '{$cve_articulo}' AND Lote = '{$lote}'";
////////////////$rs = mysqli_query($conn, $sql);
////////////////$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
////////////////$existe_lote = $resul['existe_lote'];

                $sql = "SELECT COUNT(*) as existe_lote FROM c_lotes WHERE cve_articulo = :cve_articulo AND Lote = :lote";
                try{
                $rs = $pdo->prepare($sql);
                $rs->execute(array('cve_articulo' => $cve_articulo, 'lote' => $lote));
                $resul = $rs->fetch();
                $existe_lote = $resul['existe_lote'];
                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }

                if($lote != '' && $control_lotes == 'S' && $existe_lote == 0)
                {//3
////////////////////$sql = "INSERT INTO c_lotes (cve_articulo, Lote) VALUES('{$cve_articulo}', '{$lote}')";
                    if($Caduca == 'S' && $caducidad != '')
                    {
                        $caducidad = $this->pSQL($row[self::CADUCIDAD]);
                        $fc = explode('-', $caducidad);
                        $caducidad = $fc[2]."-".$fc[1]."-".$fc[0];

////////////////////////$sql = "INSERT INTO c_lotes (cve_articulo, Lote, Caducidad) VALUES('{$cve_articulo}', '{$lote}', '{$caducidad}')";
                        $sql = "INSERT INTO c_lotes (cve_articulo, Lote, Caducidad) VALUES(:cve_articulo, :lote, :caducidad)";
                        try{
                        $rs = $pdo->prepare($sql);
                        $rs->execute(array('cve_articulo' => $cve_articulo, 'lote' => $lote, 'caducidad' => $caducidad));
                        } catch (PDOException $e) {
                            echo 'Error de conexión: ' . $e->getMessage();
                        }
                    }
                    else
                    {
                        $sql = "INSERT INTO c_lotes (cve_articulo, Lote) VALUES(:cve_articulo, :lote)";
                        try{
                        $rs = $pdo->prepare($sql);
                        $rs->execute(array('cve_articulo' => $cve_articulo, 'lote' => $lote));
                        } catch (PDOException $e) {
                            echo 'Error de conexión: ' . $e->getMessage();
                        }
                    }

////////////////////$rs = mysqli_query($conn, $sql);
                }//3

            }//3
            else if($tipo_traslado_input != 1) //REVISAR BIEN AQUI
            {
                $lp = $this->pSQL($row[self::LP]);
                if($existe && $existe_folio && $lp)
                {
                    $cant_prod = $this->pSQL($row[self::CANTIDAD_A_PRODUCIR]);
////////////////////$sql = "UPDATE t_ordenprod SET Cantidad = Cantidad + {$cant_prod} WHERE Folio_Pro = '{$Folio_Pro}'";
////////////////////$rs = mysqli_query($conn, $sql);

                    $sql = "UPDATE t_ordenprod SET Cantidad = Cantidad + :cant_prod WHERE Folio_Pro = :Folio_Pro";
                    try{
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('cant_prod' => $cant_prod, 'Folio_Pro' => $Folio_Pro));
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

////////////////////$sql = "SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '{$Folio_Pro}'";
////////////////////$rs = mysqli_query($conn, $sql);
////////////////////$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
////////////////////$cant_prod = $resul['Cantidad'];

                    $sql = "SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = :Folio_Pro";
                    try{
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('Folio_Pro' => $Folio_Pro));
                    $resul = $rs->fetch();
                    $cant_prod = $resul['Cantidad'];
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }


////////////////////$sql = "SELECT Cantidad, Cve_Articulo FROM td_ordenprod WHERE Folio_Pro = '{$Folio_Pro}'";
////////////////////$rs = mysqli_query($conn, $sql);

                    $sql = "SELECT Cantidad, Cve_Articulo FROM td_ordenprod WHERE Folio_Pro = :Folio_Pro";
                    try{
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('Folio_Pro' => $Folio_Pro));
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }
                    //foreach($articulos as $art)
                    //while($resul = mysqli_fetch_array($rs, MYSQLI_ASSOC))
                    while($resul = $rs->fetch())
                    {
                        $cve_art = $resul["Cve_Articulo"];
                        $cant    = $resul["Cantidad"]*$cant_prod;

                        //$sql = "UPDATE td_ordenprod SET Cantidad = Cantidad + {$cant} WHERE Folio_Pro = '{$Folio_Pro}' AND Cve_Articulo = '{$cve_art}'";
                        //$rs = mysqli_query($conn, $sql);

/////////////////////////$sql = "UPDATE td_pedido SET Num_cantidad = {$cant} WHERE Fol_folio = '{$Folio_Pro}' AND Cve_articulo = '{$cve_art}'";
/////////////////////////$res = mysqli_query($conn, $sql);
                        $sql = "UPDATE td_pedido SET Num_cantidad = :cant WHERE Fol_folio = :Folio_Pro AND Cve_articulo = :cve_art";
                        try{
                        $rs = $pdo->prepare($sql);
                        $rs->execute(array('cant'=> $cant, 'Folio_Pro' => $Folio_Pro, 'cve_art' => $cve_art));
                        } catch (PDOException $e) {
                            echo 'Error de conexión: ' . $e->getMessage();
                        }
                    }

                    /*
                    $sql = "SELECT CodigoCSD FROM c_ubicacion WHERE AreaProduccion = 'S' AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = {$id_almacen}) LIMIT 1";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $ubicacion_prod = $resul['CodigoCSD'];

                    $sql_tracking = $this->importarRL($id_almacen, $id_proveedor, 1, 0, $articulos, $lp, $ubicacion_prod, $Folio_Pro);
                    */
                }
                else
                {
                    $linea++;
                    continue;
                }
            }


            //Crear pedido para manufactura
            if($Folio_Anterior != $Folio_Pro)
            {
                $cve_almac_traslado = ""; $TipoPedido = 'T';
                if($tipo_traslado_input == 1)
                {
                  $cve_almac_traslado = $id_almacen;
                  $id_almacen = $almacen_dest;
                  $TipoPedido = $traslado_interno_externo_input;

                  //$sql = "SELECT clave FROM c_almacenp WHERE id = $id_almacen";
                  //$rs = mysqli_query($conn, $sql);
                  //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                  //$almacen_val_clave = $resul['clave'];

                  //$sql = "SELECT Cve_Almac FROM t_ordenprod WHERE Folio_Pro = '$Folio_Pro'";
                  //$rs = mysqli_query($conn, $sql);
                  //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                  //$cve_almac_traslado = $resul['Cve_Almac'];

                  //$sql = "SELECT id from c_almacenp where id = (SELECT cve_almacenp FROM c_almacen where cve_almac = ".$_POST['cboZonaAlmacenImport'].") ;";
                  //$query = mysqli_query($conn, $sql);
                  //if($query->num_rows > 0)
                  //$cve_almac_traslado = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["id"];
                }

                $fecha_compromiso = $this->pSQL($row[self::FECHA_COMPROMISO]);
                if($fecha_compromiso)
                {//1
                    $fc = explode('-', $fecha_compromiso);
                    $fecha_compromiso = $fc[2]."-".$fc[1]."-".$fc[0];
                }//1

                $data = array(
                    'Fol_folio' => $Folio_Pro,
                    'Fec_Pedido' => date('Y-m-d H:m:s'),
                    'Cve_clte' => '',
                    'status' => 'A',
                    'Fec_Entrega' => $fecha_compromiso,
                    'cve_Vendedor' => "",
                    'Fec_Entrada' => date('Y-m-d H:m:s'),
                    'Pick_Num' => $ot_cliente,
                    'destinatario' => 0,
                    'Cve_Usuario' => $_SESSION['id_user'],
                    'Observaciones' => "",
                    'ID_Tipoprioridad' => 0,
                    'cve_almac' => $id_almacen,
                    'statusaurora_traslado' => $cve_almac_traslado,
                    'TipoPedido' => $TipoPedido,
                    'arrDetalle' => $articulos
                );
                $nuevo_pedido->save($data);
                $Folio_Anterior = $Folio_Pro;
                $folios_creados[] = $Folio_Pro;
                $registros++;
            }


                $lp = $this->pSQL($row[self::LP]);
                $sql_tracking = "NO entró a IF";

                if($existe && $lp && $tipo_traslado_input != 1)
                {
                    $sql_tracking = "SI entró a IF";

////////////////////$sql = "UPDATE t_ordenprod SET Tipo = 'IMP_LP', Status = 'I' WHERE Folio_Pro = '{$Folio_Pro}'";
////////////////////$rs = mysqli_query($conn, $sql);

                    $sql = "UPDATE t_ordenprod SET Tipo = 'IMP_LP', Status = 'I' WHERE Folio_Pro = :Folio_Pro";
                    try{
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('Folio_Pro' => $Folio_Pro));
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }


////////////////////$sql = "SELECT CodigoCSD, idy_ubica FROM c_ubicacion WHERE AreaProduccion = 'S' AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = {$id_almacen}) LIMIT 1";
////////////////////$rs = mysqli_query($conn, $sql);
////////////////////$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

                    $sql = "SELECT CodigoCSD, idy_ubica FROM c_ubicacion WHERE AreaProduccion = 'S' AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = :id_almacen) LIMIT 1";
                    try{
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('id_almacen' => $id_almacen));
                    $resul = $rs->fetch();
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                    $ubicacion_prod = $resul['CodigoCSD'];
                    $idy_ubica = $resul['idy_ubica'];
                    $articulos_ot = array();
                    $cve_articulo_comp = $this->pSQL($row[self::CVE_ART_COMPUESTO]);
                    $cve_lote_comp = $this->pSQL($row[self::LOTE]);
                    //$num_cantidad_comp = $this->pSQL($row[self::CANTIDAD_A_PRODUCIR]);
                    array_push($articulos_ot,array(
                        "Cve_articulo" => $this->pSQL($row[self::CVE_ART_COMPUESTO]),
                        "Cve_Lote" => $this->pSQL($row[self::LOTE]),
                        "Num_cantidad" => $this->pSQL($row[self::CANTIDAD_A_PRODUCIR])
                    ));

                    //echo "importarRL($id_almacen, $id_proveedor, 1, 0, $articulos_ot, $lp, $ubicacion_prod, $Folio_Pro);";exit;
                    $sql_tracking = $this->importarRL($id_almacen, $id_proveedor, 1, 0, $articulos_ot, $lp, $ubicacion_prod, $Folio_Pro);

                    //if($realizar_produccion == 0)
                    //{
                    //$sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) (SELECT $id_almacen, $idy_ubica, cve_articulo, '$cve_lote_comp', 0, ntarima, 0, 0, 1, (SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '$Folio_Pro'), 0 FROM t_tarima WHERE fol_folio = '$Folio_Pro')";
                    //$rs = mysqli_query($conn, $sql);

                    $sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) (SELECT :id_almacen, :idy_ubica, cve_articulo, :cve_lote_comp, 0, ntarima, 0, 0, 1, (SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = :Folio_Pro), 0 FROM t_tarima WHERE fol_folio = :Folio_Pro)";
                    try{
                    $rs = $pdo->prepare($sql);
                    $rs->execute(array('id_almacen' => $id_almacen,
                                       'idy_ubica' => $idy_ubica,
                                       'cve_lote_comp' => $cve_lote_comp,
                                       'Folio_Pro' => $Folio_Pro));
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                    //}

                    //$registros++;
                    
                }
                //else
                    //$registros++;


            }
            $linea++;
        }
        
        @unlink($file);

        //*****************************************************************************************************************
        //PROCESO PARA SEPARAR LOS PEDIDOS DE ACUERDO A LAS ETAPAS QUE COMPRENDAN LOS COMPONENTES
        //*****************************************************************************************************************
        $folios_etapas = $folios_creados;

            foreach($folios_etapas as $fol_etapa)
            {
////////////////$sql = "SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '{$fol_etapa}'";
////////////////$rs = mysqli_query($conn, $sql);
////////////////$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
////////////////$Cve_Articulo_etapa = $resul['Cve_Articulo'];

                $sql = "SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = :fol_etapa";
                try{
                $rs = $pdo->prepare($sql);
                $rs->execute(array('fol_etapa' => $fol_etapa));
                $resul = $rs->fetch();
                $Cve_Articulo_etapa = $resul['Cve_Articulo'];
                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }


////////////////$sql = "SELECT MAX(IFNULL(Etapa, 0)) AS etapas FROM t_artcompuesto WHERE Cve_ArtComponente = '{$Cve_Articulo_etapa}'";
////////////////$rs = mysqli_query($conn, $sql);
////////////////$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
////////////////$etapas = $resul['etapas'];

                $sql = "SELECT MAX(IFNULL(Etapa, 0)) AS etapas FROM t_artcompuesto WHERE Cve_ArtComponente = :Cve_Articulo_etapa";
                try{
                $rs = $pdo->prepare($sql);
                $rs->execute(array('Cve_Articulo_etapa' => $Cve_Articulo_etapa));
                $resul = $rs->fetch();
                $etapas = $resul['etapas'];
                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }


                if($etapas > 0)
                {
                    for($et = 0; $et < $etapas; $et++)
                    {
                        if($et == 0)
                        {
////////////////////////////$sql = "UPDATE th_pedido SET orden_etapa = 1 WHERE Fol_folio = '$fol_etapa'";
////////////////////////////$rs = mysqli_query($conn, $sql);
                            $sql = "UPDATE th_pedido SET orden_etapa = 1 WHERE Fol_folio = :fol_etapa";
                            try{
                            $rs = $pdo->prepare($sql);
                            $rs->execute(array('fol_etapa' => $fol_etapa));
                            } catch (PDOException $e) {
                                echo 'Error de conexión: ' . $e->getMessage();
                            }

                        }
                        else
                        {
                            $sql = "SELECT `fct_consecutivo_documentos`('t_ordenprod', 6);";
                            $rs = mysqli_query($conn, $sql);
                            $row_folio_pro = mysqli_fetch_array($rs);
                            $Folio_Pro = "OT".$row_folio_pro[0];

////////////////////////////$sql = "INSERT INTO th_pedido (Fol_folio, Fec_Pedido, Cve_clte, status, Fec_Entrega, cve_Vendedor, Num_Meses, Observaciones, statusaurora, ID_Tipoprioridad, Fec_Entrada, TipoPedido, ruta, bloqueado, DiaO, TipoDoc, rango_hora, cve_almac, destinatario, Id_Proveedor, cve_ubicacion, Pick_Num, Cve_Usuario, Ship_Num, BanEmpaque, Cve_CteProv, Activo, orden_etapa) (SELECT '$Folio_Pro', Fec_Pedido, Cve_clte, status, Fec_Entrega, cve_Vendedor, Num_Meses, Observaciones, statusaurora, ID_Tipoprioridad, Fec_Entrada, TipoPedido, ruta, bloqueado, DiaO, TipoDoc, rango_hora, cve_almac, destinatario, Id_Proveedor, cve_ubicacion, Pick_Num, Cve_Usuario, Ship_Num, BanEmpaque, Cve_CteProv, Activo, ($et+1) FROM th_pedido WHERE Fol_folio = '$fol_etapa')";
////////////////////////////$rs = mysqli_query(\db2(), $sql);

                            $sql = "INSERT INTO th_pedido (Fol_folio, Fec_Pedido, Cve_clte, status, Fec_Entrega, cve_Vendedor, Num_Meses, Observaciones, statusaurora, ID_Tipoprioridad, Fec_Entrada, TipoPedido, ruta, bloqueado, DiaO, TipoDoc, rango_hora, cve_almac, destinatario, Id_Proveedor, cve_ubicacion, Pick_Num, Cve_Usuario, Ship_Num, BanEmpaque, Cve_CteProv, Activo, orden_etapa) (SELECT :Folio_Pro, Fec_Pedido, Cve_clte, status, Fec_Entrega, cve_Vendedor, Num_Meses, Observaciones, statusaurora, ID_Tipoprioridad, Fec_Entrada, TipoPedido, ruta, bloqueado, DiaO, TipoDoc, rango_hora, cve_almac, destinatario, Id_Proveedor, cve_ubicacion, Pick_Num, Cve_Usuario, Ship_Num, BanEmpaque, Cve_CteProv, Activo, ($et+1) FROM th_pedido WHERE Fol_folio = :fol_etapa)";
                            try{
                            $rs = $pdo->prepare($sql);
                            $rs->execute(array('Folio_Pro' => $Folio_Pro, 
                                               'fol_etapa' => $fol_etapa
                                           ));
                            } catch (PDOException $e) {
                                echo 'Error de conexión: ' . $e->getMessage();
                            }


////////////////////////////$sql = "UPDATE td_pedido SET Fol_folio = '$Folio_Pro' WHERE Fol_folio = '$fol_etapa' AND Cve_articulo IN (SELECT Cve_Articulo FROM t_artcompuesto WHERE Cve_ArtComponente = '{$Cve_Articulo_etapa}' AND Etapa = ($et+1))";
////////////////////////////$rs = mysqli_query($conn, $sql);

                            $sql = "UPDATE td_pedido SET Fol_folio = :Folio_Pro WHERE Fol_folio = :fol_etapa AND Cve_articulo IN (SELECT Cve_Articulo FROM t_artcompuesto WHERE Cve_ArtComponente = :Cve_Articulo_etapa AND Etapa = (:et+1))";
                            try{
                            $rs = $pdo->prepare($sql);
                            $rs->execute(array('Folio_Pro' => $Folio_Pro,
                                               'fol_etapa' => $fol_etapa, 
                                               'Cve_Articulo_etapa' => $Cve_Articulo_etapa, 
                                               'et' => $et));
                            } catch (PDOException $e) {
                                echo 'Error de conexión: ' . $e->getMessage();
                            }

////////////////////////////$sql = "INSERT INTO t_ordenprod (Folio_Pro, cve_almac, ID_Proveedor, Cve_Articulo, Cve_Lote, Cantidad, Cant_Prod, Cve_Usuario, Fecha, FechaReg, id_umed, Status, Referencia, id_zona_almac, idy_ubica) (SELECT '$Folio_Pro', cve_almac, ID_Proveedor, Cve_Articulo, Cve_Lote, Cantidad, Cant_Prod, Cve_Usuario, Fecha, FechaReg, id_umed, Status, Referencia, id_zona_almac, idy_ubica FROM t_ordenprod WHERE Folio_Pro = '$fol_etapa')";
////////////////////////////$rs = mysqli_query($conn, $sql);

                            $sql = "INSERT INTO t_ordenprod (Folio_Pro, cve_almac, ID_Proveedor, Cve_Articulo, Cve_Lote, Cantidad, Cant_Prod, Cve_Usuario, Fecha, FechaReg, id_umed, Status, Referencia, id_zona_almac, idy_ubica) (SELECT :Folio_Pro, cve_almac, ID_Proveedor, Cve_Articulo, Cve_Lote, Cantidad, Cant_Prod, Cve_Usuario, Fecha, FechaReg, id_umed, Status, Referencia, id_zona_almac, idy_ubica FROM t_ordenprod WHERE Folio_Pro = :fol_etapa)";
                            try{
                            $rs = $pdo->prepare($sql);
                            $rs->execute(array('Folio_Pro' => $Folio_Pro,
                                               'fol_etapa' => $fol_etapa));
                            } catch (PDOException $e) {
                                echo 'Error de conexión: ' . $e->getMessage();
                            }

////////////////////////////$sql = "UPDATE td_ordenprod SET Folio_Pro = '$Folio_Pro' WHERE Folio_Pro = '$fol_etapa' AND Cve_Articulo IN (SELECT Cve_Articulo FROM t_artcompuesto WHERE Cve_ArtComponente = '{$Cve_Articulo_etapa}' AND Etapa = ($et+1))";
////////////////////////////$rs = mysqli_query($conn, $sql);

                            $sql = "UPDATE td_ordenprod SET Folio_Pro = :Folio_Pro WHERE Folio_Pro = :fol_etapa AND Cve_Articulo IN (SELECT Cve_Articulo FROM t_artcompuesto WHERE Cve_ArtComponente = :Cve_Articulo_etapa AND Etapa = (:et+1))";
                            try{
                            $rs = $pdo->prepare($sql);
                            $rs->execute(array('Folio_Pro' => $Folio_Pro,
                                               'fol_etapa' => $fol_etapa, 
                                               'Cve_Articulo_etapa' => $Cve_Articulo_etapa, 
                                               'et' => $et));
                            } catch (PDOException $e) {
                                echo 'Error de conexión: ' . $e->getMessage();
                            }

                        }



                    }
                }
                $sql_insert = "INSERT INTO td_surtidopiezas(fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, status) (SELECT Fol_folio, :id_almacen, 1, Cve_articulo, cve_lote, Num_cantidad, 0, 'S' FROM td_pedido where Fol_folio = :Folio_Pro) ON DUPLICATE KEY UPDATE Cantidad = Cantidad + VALUES(Cantidad) ;";
                try{
                $rs = $pdo->prepare($sql_insert);
                $rs->execute(array(
                                'id_almacen' => $id_almacen,
                                'Folio_Pro' => $fol_etapa
                                   ));
                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }

            }
        //*****************************************************************************************************************/
        //*****************************************************************************************************************


        $folios_sin_stock = array();
        $cod_art_compuesto = "";
        $cantidad_art_compuesto = "";
        $cve_almacen = "";
        mysqli_close($conn);
        if($realizar_produccion == 1 && $registros > 0)
        {
            //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            foreach($folios_creados as $orden_id)
            {
////////////////$sql = "SELECT Cve_Articulo, Cantidad, Tipo FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}'";
////////////////if (!($res_art = mysqli_query($conn, $sql)))
////////////////{
////////////////    echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";
////////////////    //$sql_check = "Falló la preparación K: (" . mysqli_error($conn) . ") ";
////////////////}
////////////////$row_comp = mysqli_fetch_array($res_art);

                $sql = "SELECT Cve_Articulo, Cantidad, Tipo, Referencia FROM t_ordenprod WHERE Folio_Pro = :orden_id";
                try{
                $res_art = $pdo->prepare($sql);
                $res_art->execute(array('orden_id' => $orden_id));
                $row_comp = $res_art->fetch();
                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }

                $cod_art_compuesto      = $row_comp["Cve_Articulo"];
                $Tipo_OT                = $row_comp["Tipo"];
                $cantidad_art_compuesto = $row_comp["Cantidad"];
                $ReferenciaProd         = $row_comp["Referencia"];
                ///Cve_Articulo  = $this->pSQL($row[self::CVE_ART_COMPUESTO]);
                ///Cantidad      = $this->pSQL($row[self::CANTIDAD_A_PRODUCIR]);

                //$lp_read = "";
                //if($Tipo_OT == 'IMP_LP')
                //$lp_read = $cod_art_compuesto;
                //$sql_check = $Tipo_OT;
                $sql_check .= ";1;".$sql."\n\n;\n";

                if($Tipo_OT == 'IMP_LP')
                {
//*****************************************************************************************************************

//////////////////$sql = "SELECT DISTINCT ch.CveLP, t.cantidad
//////////////////        FROM t_tarima t 
//////////////////        LEFT JOIN c_charolas ch ON ch.IDContenedor = t.ntarima
//////////////////        WHERE t.Fol_Folio = '{$orden_id}'";
//////////////////if (!($res_art = mysqli_query($conn, $sql)))
//////////////////    echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";


                $sql = "SELECT DISTINCT ch.CveLP, t.cantidad
                        FROM t_tarima t 
                        LEFT JOIN c_charolas ch ON ch.IDContenedor = t.ntarima
                        WHERE t.Fol_Folio = :orden_id";

                try{
                $res_art = $pdo->prepare($sql);
                $res_art->execute(array('orden_id' => $orden_id));
                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }

                $sql_check .= ";2;".$sql."\n\n;\n";

                //while($row_comp = mysqli_fetch_array($res_art))
                while($row_comp = $res_art->fetch())
                {//while
                    $lp_read = $row_comp['CveLP'];
                    $cantidad_art_compuesto = $row_comp["cantidad"];
/*
        $sql = "SELECT op.clave, op.control_lotes, op.Lote, op.LoteOT, op.Caduca, op.Caducidad, op.control_peso, op.Cantidad, op.Cant_Prod as Cant_OT, op.Num_cantidad AS cantnecesaria, op.Cantidad_Producida, op.ubicacion, IFNULL(op.existencia, 0) AS existencia, op.um, op.mav_cveunimed, op.clave_almacen, op.Cve_Contenedor, IF(op.Num_cantidad > IFNULL(op.existencia, 0), 1, 0) AS acepto FROM (
                SELECT DISTINCT 
                    a.cve_articulo AS clave,
                    IFNULL(a.control_lotes, 'N') AS control_lotes,
                    e.cve_lote AS Lote,
                    t.Cve_Lote AS LoteOT,
                    IFNULL(a.control_peso, 'N') AS control_peso,
                    IFNULL(a.Caduca, 'N') AS Caduca,
                    IF(a.Caduca = 'S', l.Caducidad, '') AS Caducidad,
                    IF(IFNULL(th.Fol_folio, '') = '', ac.Cantidad, ac.Cantidad/(SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}')) AS Cantidad,
                    (ac.Cantidad_Producida*ac.Cantidad) AS Cantidad_Producida,
                    t.Cant_Prod,
                    p.Num_cantidad,

                    #(SELECT cu.idy_ubica FROM V_ExistenciaProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND vp.cve_articulo = a.cve_articulo AND vp.cve_almac = '{$id_almacen}' AND cu.Activo = 1 AND cu.AreaProduccion = 'S' AND vp.cve_ubicacion = t.idy_ubica GROUP BY vp.Existencia LIMIT 1) AS ubicacion,

                    t.idy_ubica as ubicacion,

                    #tt.cantidad AS existencia 
                    e.Cve_Contenedor,
                    IFNULL(e.Existencia, 0) AS existencia, 
                    u.cve_umed AS um,
                    u.mav_cveunimed,
                    alm.clave AS clave_almacen,
                    t.Cve_Usuario AS cve_usuario,
                    IFNULL(t.ID_Proveedor, 0) AS ID_Proveedor
                FROM t_artcompuesto ac
                    LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                    LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro
                    LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = t.Folio_Pro
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima 
                    LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = td.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = td.Folio_Pro) #AND IFNULL(e.cve_lote, '') = IFNULL(td.cve_lote, '')
                    LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_Articulo AND l.LOTE = e.cve_lote
                    LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
                    #LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                    LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                    LEFT JOIN td_pedido p ON p.Fol_folio = td.Folio_Pro AND p.Cve_articulo = td.Cve_Articulo
                    LEFT JOIN c_almacenp alm ON alm.id = e.cve_almac
                WHERE t.Folio_Pro = '{$orden_id}' AND e.cve_almac = '{$id_almacen}' 
                AND ac.Cve_Articulo = e.cve_articulo
                AND e.cve_ubicacion = '{$idy_ubica_ot}' 
                AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = td.Cve_Articulo #AND ch.CveLP = '{$lp_read}'
                #AND t.idy_ubica = '{$idy_ubica_ot}'
                #AND IFNULL(e.Cve_Contenedor, '') != ''

                UNION 

                        SELECT DISTINCT 
                            a.cve_articulo AS clave,
                            IFNULL(a.control_lotes, 'N') AS control_lotes,
                            e.cve_lote AS Lote,
                            t.Cve_Lote AS LoteOT,
                            IFNULL(a.control_peso, 'N') AS control_peso,
                            IFNULL(a.Caduca, 'N') AS Caduca,
                            IF(a.Caduca = 'S', l.Caducidad, '') AS Caducidad,
                            IF(IFNULL(th.Fol_folio, '') = '', ac.Cantidad, ac.Cantidad/(SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}')) AS Cantidad,
                            (ac.Cantidad_Producida*ac.Cantidad) AS Cantidad_Producida,
                            t.Cant_Prod,
                            ac.Cantidad*t.Cantidad AS Num_cantidad,

                            #(SELECT cu.idy_ubica FROM V_ExistenciaProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND vp.cve_articulo = a.cve_articulo AND vp.cve_almac = '39' AND cu.Activo = 1 AND cu.AreaProduccion = 'S' AND vp.cve_ubicacion = t.idy_ubica GROUP BY vp.Existencia LIMIT 1) AS ubicacion,

                            t.idy_ubica AS ubicacion,

                            #tt.cantidad AS existencia 
                            e.Cve_Contenedor,
                            IFNULL(e.Existencia, 0) AS existencia, 
                            u.cve_umed AS um,
                            u.mav_cveunimed,
                            alm.clave AS clave_almacen,
                            t.Cve_Usuario AS cve_usuario,
                            IFNULL(t.ID_Proveedor, 0) AS ID_Proveedor
                        FROM t_artcompuesto ac
                            LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                            LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
                    LEFT JOIN t_tarima tt ON tt.Fol_Folio = t.Folio_Pro
                    LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima 
                            LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = ac.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = t.Folio_Pro) #AND IFNULL(e.cve_lote, '') = IFNULL(td.cve_lote, '')
                            LEFT JOIN c_lotes l ON l.cve_articulo = ac.Cve_Articulo AND l.LOTE = e.cve_lote
                            LEFT JOIN c_articulo a ON a.cve_articulo = ac.Cve_Articulo
                            #LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                            LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                            LEFT JOIN td_pedido p ON p.Fol_folio = t.Folio_Pro AND p.Cve_articulo = ac.Cve_Articulo
                            LEFT JOIN c_almacenp alm ON alm.id = e.cve_almac
                        WHERE t.Folio_Pro = '{$orden_id}' AND e.cve_almac = '{$id_almacen}' 
                        AND ac.Cve_Articulo = e.cve_articulo
                        AND e.cve_ubicacion = '{$idy_ubica_ot}' 
                        AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = a.Cve_Articulo #AND ch.CveLP = ''
                        AND IFNULL(a.tipo_producto, '') = 'ProductoNoSurtible'

                UNION 

            SELECT Cve_Articulo AS clave, 
                   '' AS control_lotes,
                   Cve_Lote AS Lote, 
                   '' AS LoteOT,
                   '' AS control_peso,
                   '' AS Caduca,
                   '' AS Caducidad,
                   0  AS Cantidad, 
                   0 AS Cantidad_Producida,
                   0 as Cant_Prod,
                   0 AS Num_cantidad,
                   '' AS ubicacion,
                   '' AS Cve_Contenedor,
                   0 AS existencia, 
                   '' AS um,
                   '' AS mav_cveunimed,
                   '' AS clave_almacen,
                   '' AS cve_usuario,
                   0 AS ID_Proveedor
             FROM td_ordenprod td
             WHERE Folio_Pro = '{$orden_id}' 
             #AND CONCAT(Cve_Articulo, Cve_Lote) NOT IN (SELECT CONCAT(Cve_Articulo, cve_lote) FROM V_ExistenciaProduccion WHERE cve_almac = '{$id_almacen}')
             AND Cve_Articulo NOT IN (SELECT Cve_Articulo FROM V_ExistenciaProduccion WHERE cve_almac = '{$id_almacen}' AND Existencia > 0 AND cve_ubicacion = '{$idy_ubica_ot}')


                ORDER BY Caducidad
            ) AS op WHERE op.ubicacion IS NOT NULL AND op.Num_cantidad <= IFNULL(op.existencia, 0) 
            GROUP BY clave, ubicacion";
*/
        //$sql = "SELECT * FROM V_CantidadVSExistenciaProduccion WHERE orden_id = :orden_id";
        $SQL_IdyUbicaOT = "";

        if($idy_ubica_ot != '')
           $SQL_IdyUbicaOT = " AND vp.cve_ubicacion = $idy_ubica_ot ";

                $sql = "SELECT v.*, IFNULL(a.peso, 0) as peso, alm.id as id_almacen, MAX(v.existencia) as existencia_select
                        FROM V_CantidadVSExistenciaProduccion v 
                        LEFT JOIN c_almacenp alm on v.clave_almacen = alm.clave
                        LEFT JOIN c_articulo a ON a.cve_articulo = v.clave
                        WHERE v.orden_id = :orden_id AND v.cantnecesaria <= v.existencia
                        GROUP BY orden_id, cod_art_compuesto, clave
                        ";

        $res_art = "";
        $sql_art = $sql;
        $sql_check .= ";3;".$sql."\n\n;\n";
        $sql_acepto = "SELECT SUM(acepto.acepto) AS acepto FROM ( ".$sql." ) AS acepto ";

/////////if (!($res_art = mysqli_query($conn, $sql_acepto)))
/////////    echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";
            try{
            $res_art = $pdo->prepare($sql_acepto);
            $res_art->execute(array('orden_id' => $orden_id));
            $row_art = $res_art->fetch();
            } catch (PDOException $e) {
                echo 'Error de conexión: ' . $e->getMessage();
            }

        $acepto = true;

////////$row_art = mysqli_fetch_array($res_art);
        if($row_art['acepto'] > 0)
        {
            $acepto = false;
            $referencias[] = $ReferenciaProd;
            continue;
        }
        //$num_rows = mysqli_num_rows($res_art);

        //$sql_check .= "num_rows = $num_rows"."\n;\n";
        $i_num_rows = 0;
//////////////////////////////////////////////////////////////////////////////////////////
        /*
        while($row_art = mysqli_fetch_array($res_art))
        {
            //+$row_art['Cantidad_Producida'] 
            //&& $row_art['existencia'] > 0
            //if(($row_art['Cantidad']*$row_art['Cant_OT']) > $row_art['existencia'] )
            $sql_check .= ";if(".$row_art['cantnecesaria']." > ".$row_art['existencia'].") (".$row_art['clave'].")"."\n;\n";
            if($row_art['cantnecesaria'] > $row_art['existencia'])
            {
                $acepto = false;
                break;
            }
            $i_num_rows++;

            if($i_num_rows >= $num_rows) break; //por si se queda pegado el while
        }
        */
//////////////////////////////////////////////////////////////////////////////////////////
        if($acepto)
        {//acepto
//////////////$sql = "UPDATE t_artcompuesto SET Cantidad_Producida = IFNULL(Cantidad_Producida, 0)+$cantidad_art_compuesto WHERE Cve_ArtComponente = '$cod_art_compuesto'";
///////////////if (!($res = mysqli_query($conn, $sql))) {
///////////////    echo "Falló la preparación J: (" . mysqli_error($conn) . ") ";
///////////////}

            $sql = "UPDATE t_artcompuesto SET Cantidad_Producida = IFNULL(Cantidad_Producida, 0)+:cantidad_art_compuesto WHERE Cve_ArtComponente = :cod_art_compuesto";
            try{
            $res = $pdo->prepare($sql);
            $res->execute(array('cantidad_art_compuesto' => $cantidad_art_compuesto, 'cod_art_compuesto' => $cod_art_compuesto));
            } catch (PDOException $e) {
                echo 'Error de conexión: ' . $e->getMessage();
            }

            $sql_check .= ";04;".$sql."\n\n;\n";
            $sql_check .= ";4 sql_art = ;".$sql_art."\n\n;\n";
//////////////if (!($res_art = mysqli_query($conn, $sql_art)))
//////////////    echo "Falló la preparación H: (" . mysqli_error($conn) . ") ";

            try{
            $res_art = $pdo->prepare($sql_art);
            $res_art->execute(array('orden_id' => $orden_id));
            } catch (PDOException $e) {
                echo 'Error de conexión: ' . $e->getMessage();
            }

            $listo = false;
            $LoteOT = "";
            $mensaje_error = "";
            $caducidad  = ""; $last_idy_ubica = "";
            //while($row_art_1 = mysqli_fetch_array($res_art))
            while($row_art_1 = $res_art->fetch())
            {
                $cantidad = $row_art_1['cantnecesaria'];
                $idy_ubica = $row_art_1['ubicacion'];
                $clave = $row_art_1['clave'];
                $Lote = $row_art_1['Lote'];
                $LoteOT = $row_art_1['LoteOT'];
                $caducidadMIN = $row_art_1['Caducidad'];
                $Caduca = $row_art_1['Caduca'];
                $Cve_Contenedor = $row_art_1['Cve_Contenedor'];
                $ID_Contenedor = $row_art_1['Id_Contenedor'];
                $CveLP = $row_art_1['Cve_Contenedor'];
                $ID_Proveedor = $row_art_1['ID_Proveedor'];
                $existencia = $row_art_1['existencia'];
                $um = $row_art_1['um'];
                $mav_cveunimed = $row_art_1['mav_cveunimed'];
                $clave_almacen = $row_art_1['clave_almacen'];
                $cve_usuario = $row_art_1['cve_usuario'];
                $control_peso = $row_art_1['control_peso'];
                $id_almacen   = $row_art_1['id_almacen'];

                if($idy_ubica == '') continue;
                if($LoteOT == '') $LoteOT = $orden_id;

                if($control_peso == 'N' && $mav_cveunimed == 'H87') $cantidad = ceil($cantidad);

                //mysqli_close($conn);
                //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                //$sql = "CALL SPAD_RestarPT('$clave_almacen', $idy_ubica, '$cve_usuario','$orden_id','$clave','$Lote',$cantidad,'$CveLP')";
                //if (!($res = mysqli_query($conn, $sql))) {
                //    echo "Falló la preparación G: (" . mysqli_error($conn) . ") ";
                //}
                //mysqli_close($conn);
                //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

                if($CveLP == '') $ID_Contenedor = 0;
                $sql = "CALL SPAD_RestarPT(:id_almacen, :idy_ubica, :cve_usuario,:orden_id,:clave,:Lote,:cantidad, :CveContenedor,:ID_Contenedor)";
                try{
  
                $res = $pdo->prepare($sql);
                $res->execute(array('id_almacen' => $id_almacen, 
                                    'idy_ubica' => $idy_ubica, 
                                    'cve_usuario' => $cve_usuario, 
                                    'orden_id' => $orden_id, 
                                    'clave' => $clave, 
                                    'Lote' => $Lote, 
                                    'cantidad' => $cantidad, 
                                    'CveContenedor' => $CveLP, 
                                    'ID_Contenedor' => $ID_Contenedor
                                ));

                } catch (PDOException $e) {

                    if($e->errorInfo[0]==40001 //(ISO/ANSI) Serialization failure, e.g. timeout or deadlock
                        && $pdoDBHandle->getAttribute(\PDO::ATTR_DRIVER_NAME)=="mysql"
                        && $e->errorInfo[1]==1213) //(MySQL SQLSTATE) ER_LOCK_DEADLOCK
                    {
                        $res = $pdo->prepare($sql);
                        if(count($campos_array) == 0)
                            $res->execute();
                        else
                            $res->execute($campos_array);
                    }
                    else
                        throw $e;

                    echo 'Error de conexión: ' . $e->getMessage();
                }

                $sql_check .= ";5 SPAD_RestarPT = ;".$sql."\n\n;\n";
/*
                //if($idy_ubica == "") $mensaje_error .= "El Producto {$clave} no se encuentra en una ubicación de Producción\n";
                if($Caduca == 'S' && $caducidadMIN != '' && $caducidadMIN != '0000-00-00' && $listo == false)
                {
                    $caducidad = $caducidadMIN;
                    $listo = true;
                }

                if($control_peso == 'N' && $mav_cveunimed == 'H87') $cantidad = ceil($cantidad);
                $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and cve_lote = '$Lote'";

                $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$clave}', '{$Lote}', NOW(), '{$idy_ubica}', '{$orden_id}', {$cantidad}, 8, '{$cve_usuario}','{$id_almacen}')";
                $res_kardex = mysqli_query($conn, $sql_kardex);


                if($Cve_Contenedor != '')
                {
                    $sql = "UPDATE ts_existenciatarima SET existencia = existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and lote = '$Lote' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')";

                    $sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES 
                    ((SELECT MAX(id) FROM t_cardex), '$clave_almacen', (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor'), CURDATE(), '{$idy_ubica}', '{$orden_id}', 8, '{$cve_usuario}', 'I')";
                    $res_kardex = mysqli_query($conn, $sql_kardex);
                }

//                if($lp_read != '')
//                {
//                    $sql = "DELETE FROM ts_existenciatarima WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' AND lote = '$Lote' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read')";//UPDATE ts_existenciatarima SET existencia = existencia - $cantidad
//                }

                $last_idy_ubica = $idy_ubica;

                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación G: (" . mysqli_error($conn) . ") ";
                }
                //$Lote = "";
*/
/*
                if($ejecutar_infinity)
                {
////////////////////$sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = '{$clave}' and cve_almac = '$id_almacen' AND tipo = 'ubicacion'";
////////////////////$query = mysqli_query($conn, $sql);
////////////////////$row_ord = mysqli_fetch_array($query);

                    $sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = :clave and cve_lote = :cve_lote and cve_almac = :id_almacen AND tipo = 'ubicacion'";
                    try{
                    $query = \db()->prepare($sql);
                    $query->execute(array('clave' => $clave, 'cve_lote' => $Lote,'id_almacen' => $id_almacen));
                    $row_ord = $query->fetch();
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                    $existencia_art_prod = $row_ord['existencia_art_prod'];

                    if(!$existencia_art_prod) $existencia_art_prod = 0;

                      $json = "[";

                        $json .= "{";
                        $json .= '"item":"'.$clave.'","um":"'.$um.'","batch":"'.$Lote.'", "qty": '.$existencia_art_prod.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                        $json .= "}";
                      //$json[strlen($json)-1] = ' ';
                      $json .= "]";

                          $curl = curl_init();
                          //$url_curl = $Url_inf.':8080/'.$Servicio_inf;

                          curl_setopt_array($curl, array(
                            // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                            CURLOPT_URL => "$url_curl",
                            //CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS =>
                            // Aquí cambia la cadena JSON
                            $json,
                            CURLOPT_HTTPHEADER => array(
                              'Content-Type: application/json',
                              //'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
                              'Authorization: Basic '.$Codificado.''
                            )
                            ,CURLOPT_SSL_VERIFYHOST => false,
                            CURLOPT_SSL_VERIFYPEER => false
                          ));

                          $response = curl_exec($curl);
                          $response_ot .= $response."\n";

                          curl_close($curl);      
                          //echo $response;

                          //$response = 'Pendiente';
                          //$sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', 'Transformacion', 'WEB')";
                          //$query = mysqli_query($conn, $sql);
                            $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), :Servicio_inf, :json, :response, 'Transformacion', 'WEB')";
                            try{
                            $query = \db()->prepare($sql);
                            $query->execute(array('Servicio_inf' => $Servicio_inf, 'json' => $json, 'response' => $response));
                            } catch (PDOException $e) {
                                echo 'Error de conexión: ' . $e->getMessage();
                            }

                }
*/
                if($ejecutar_infinity)
                {
////////////////////$sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = '{$clave}' and cve_almac = '$id_almacen' AND tipo = 'ubicacion'";
////////////////////$query = mysqli_query($conn, $sql);
////////////////////$row_ord = mysqli_fetch_array($query);
                    /*************************************************
                    $sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = :clave and cve_lote = :cve_lote and cve_almac = :id_almacen AND tipo = 'ubicacion'";
                    try{
                    $query = \db()->prepare($sql);
                    $query->execute(array('clave' => $clave, 'cve_lote' => $Lote,'id_almacen' => $id_almacen));
                    $row_ord = $query->fetch();
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                    $existencia_art_prod = $row_ord['existencia_art_prod'];

                    if(!$existencia_art_prod) $existencia_art_prod = 0;

                      $json = "[";

                        $json .= "{";
                        $json .= '"item":"'.$clave.'","um":"'.$um.'","batch":"'.$Lote.'", "qty": '.$existencia_art_prod.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                        $json .= "}";
                      //$json[strlen($json)-1] = ' ';
                      $json .= "]";
                      ******************************************/
                      //$response = ''; $intentos = 7;
                      /*
                          $curl = curl_init();
                          //$url_curl = $Url_inf.':8080/'.$Servicio_inf;
                      //while($response == '' && $intentos > 0)
                      //{

                          curl_setopt_array($curl, array(
                            // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                            CURLOPT_URL => "$url_curl",
                            //CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_SSL_VERIFYHOST => false,
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS =>
                            // Aquí cambia la cadena JSON
                            $json,
                            CURLOPT_HTTPHEADER => array(
                              'Content-Type: application/json',
                              //'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
                              'Authorization: Basic '.$Codificado.''
                            )
                          ));

                          $response = curl_exec($curl);

                          //$intentos--;
                      //}
                          //$response_ot .= $response."\n";

                          curl_close($curl);      
                          //echo $response;

                          //$response = 'Pendiente';
                          $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', 'Transformacion', 'WEB')";
                          //$query = mysqli_query($conn, $sql);
                            //$sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), :Servicio_inf, :json, :response, 'Transformacion', 'WEB')";
                            try{
                            $query = \db()->prepare($sql);
                            //$query->execute(array('Servicio_inf' => $Servicio_inf, 'json' => $json, 'response' => $response));
                            $query->execute();
                            } catch (PDOException $e) {
                                echo 'Error de conexión: ' . $e->getMessage();
                            }
                            */

                        /////////$interfase->RegistrarCadena($json, $Servicio_inf, $Codificado, $url_curl, 'Transformacion', 'WEB');

                }

            }
            $idy_ubica = $last_idy_ubica;

                    //$sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = IFNULL(Cant_Prod, 0)+$cantidad_art_compuesto WHERE Folio_Pro = '$orden_id'";
                $sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = Cantidad WHERE Folio_Pro = :orden_id";
                try{
                $query = $pdo->prepare($sql);
                $query->execute(array('orden_id' => $orden_id));
                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }


            //xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
/*
                $sql = "SELECT u.cve_usuario, t.Folio_Pro, t.idy_ubica, t.Cve_Articulo, t.Cve_Lote, l.Caducidad, t.Cant_Prod, t.ID_Proveedor
                            FROM t_ordenprod t 
                            LEFT JOIN c_usuario u ON t.Cve_Usuario IN (u.id_user, u.cve_usuario)
                            LEFT JOIN c_lotes l ON l.cve_articulo = t.Cve_Articulo AND t.Cve_Lote = l.Lote
                            WHERE t.Folio_Pro = '$orden_id'";
                $query_valores = mysqli_query($conn, $sql);
                $row_valores = mysqli_fetch_array($query_valores);
*/
                $sql = "SELECT u.cve_usuario, t.Folio_Pro, t.idy_ubica, t.Cve_Articulo, t.Cve_Lote, l.Caducidad, t.Cant_Prod, t.ID_Proveedor, tt.ntarima
                            FROM t_ordenprod t 
                            LEFT JOIN t_tarima tt ON tt.fol_folio = t.Folio_Pro
                            LEFT JOIN c_usuario u ON t.Cve_Usuario IN (u.id_user, u.cve_usuario)
                            LEFT JOIN c_lotes l ON l.cve_articulo = t.Cve_Articulo AND t.Cve_Lote = l.Lote
                            WHERE t.Folio_Pro = :orden_id
                            order by Caducidad DESC
                            LIMIT 1";
                try{
                $query_valores = $pdo->prepare($sql);
                $query_valores->execute(array('orden_id' => $orden_id));
                $row_valores = $query_valores->fetch();
                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }

                $cve_usuario_SP_Guardar = $row_valores['cve_usuario'];
                $Folio_Pro_SP_Guardar = $row_valores['Folio_Pro'];
                $idy_ubica_SP_Guardar = $row_valores['idy_ubica'];
                $Cve_Articulo_SP_Guardar = $row_valores['Cve_Articulo'];
                $Cve_Lote_SP_Guardar = $row_valores['Cve_Lote'];
                $Caducidad_SP_Guardar = $row_valores['Caducidad'];
                $Cant_Prod_SP_Guardar = $row_valores['Cant_Prod'];
                $ID_Proveedor_SP_Guardar = $row_valores['ID_Proveedor'];
                $nTarima_SP_Guardar = $row_valores['ntarima'];

                //$sql = "CALL SPAD_GuardarPT('$cve_usuario_SP_Guardar', '$Folio_Pro_SP_Guardar', $idy_ubica_SP_Guardar, '$Cve_Articulo_SP_Guardar', '$Cve_Lote_SP_Guardar', '$Caducidad_SP_Guardar', $Cant_Prod_SP_Guardar, $ID_Proveedor_SP_Guardar, '$lp_read')";
                //if (!($res = mysqli_query($conn, $sql)))
                //    echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
/*
                $sql = "CALL SPAD_GuardarPT(:cve_usuario_SP_Guardar, :Folio_Pro_SP_Guardar, :idy_ubica_SP_Guardar, :Cve_Articulo_SP_Guardar, :Cve_Lote_SP_Guardar, :Caducidad_SP_Guardar, :Cant_Prod_SP_Guardar, :ID_Proveedor_SP_Guardar, :lp_read)";
                try{
                $res = \db()->prepare($sql);
                $res->execute(array('cve_usuario_SP_Guardar'=> $cve_usuario_SP_Guardar, 'Folio_Pro_SP_Guardar'=> $Folio_Pro_SP_Guardar, 'idy_ubica_SP_Guardar'=> $idy_ubica_SP_Guardar, 'Cve_Articulo_SP_Guardar'=> $Cve_Articulo_SP_Guardar, 'Cve_Lote_SP_Guardar'=> $Cve_Lote_SP_Guardar, 'Caducidad_SP_Guardar'=> $Caducidad_SP_Guardar, 'Cant_Prod_SP_Guardar'=> $Cant_Prod_SP_Guardar, 'ID_Proveedor_SP_Guardar'=> $ID_Proveedor_SP_Guardar, 'lp_read'=> $lp_read));

                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }

                $sql_check .= ";6 SPAD_GuardarPT = ;".$sql."\n\n;\n";
*/

                //$sql = "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND lote = '$LoteOT' AND ntarima in (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read')";

                try{
                $sql = "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE idy_ubica = '$idy_ubica_SP_Guardar' AND cve_articulo = '$Cve_Articulo_SP_Guardar' AND lote = '$Cve_Lote_SP_Guardar' AND ntarima = '$nTarima_SP_Guardar'";
                $res_query = $pdo->prepare($sql);
                $res_query->execute();
                $row_existe = $res_query->fetch();
                $existe_producto = $row_existe['existe'];
                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }

                //$sql = "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE idy_ubica = '$idy_ubica_SP_Guardar' AND cve_articulo = '$Cve_Articulo_SP_Guardar' AND lote = '$Cve_Lote_SP_Guardar' AND ntarima = '$nTarima_SP_Guardar'";

                //if (!($res = mysqli_query($conn, $sql)))
                //    echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
                //$row_existe = mysqli_fetch_array($res);
                //$existe_producto = $row_existe['existe'];

                $sql_check .= ";6F;".$sql."\n\n;\n";

                if($existe_producto == 0)
                {

                    //$sql = "SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";
                    //if (!($res = mysqli_query($conn, $sql)))
                        //echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
                    //$ID_Proveedor = mysqli_fetch_array($res)['ID_Proveedor'];

                    //$sql_check .= ";8;".$sql."\n\n;\n";
/*
                    $sql = "SELECT DISTINCT control_lotes, Caduca FROM c_articulo WHERE cve_articulo = '$cod_art_compuesto'";

                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación E: (" . mysqli_error($conn) . ") ";
                    $row_control = mysqli_fetch_array($res);
                    $control_lotes = $row_control['control_lotes'];
                    $Caduca = $row_control['Caduca'];
*/
                    try{
                        $cod_art_compuesto = $Cve_Articulo_SP_Guardar;
                    $sql = "SELECT DISTINCT control_lotes, Caduca FROM c_articulo WHERE cve_articulo = '$cod_art_compuesto'";
                    $res_query2 = $pdo->prepare($sql);
                    $res_query2->execute();
                    $row_control = $res_query2->fetch();
                    $control_lotes = $row_control['control_lotes'];
                    $Caduca = $row_control['Caduca'];
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }


                    $sql_check .= ";9;".$sql."\n\n;\n";

                    //if($control_lotes != 'S')
                    //{
                    //    $sql = "UPDATE c_articulo SET control_lotes = 'S' WHERE cve_articulo = '$cod_art_compuesto'";
                    //    if (!($res = mysqli_query($conn, $sql)))
                    //        echo "Falló la preparación D: (" . mysqli_error($conn) . ") ";
                    //}

                    //if($Caduca != 'S' && $caducidad != '')
                    //{
                    //    $sql = "UPDATE c_articulo SET Caduca = 'S' WHERE cve_articulo = '$cod_art_compuesto'";
                    //    if (!($res = mysqli_query($conn, $sql)))
                    //        echo "Falló la preparación C: (" . mysqli_error($conn) . ") ";
                    //}

                    //$sql = "SELECT DISTINCT (Cantidad_Producida*Cantidad) as Cantidad_Producida FROM t_artcompuesto WHERE Cve_ArtComponente = '$cod_art_compuesto'";
                    
                    //if (!($res = mysqli_query($conn, $sql)))
                    //    echo "Falló la preparación M: (" . mysqli_error($conn) . ") ";
                    //$cantidad_producida = mysqli_fetch_array($res)['Cantidad_Producida'];

                    $LoteOT = $Cve_Lote_SP_Guardar;
                    if($LoteOT)
                    {
                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', '{$caducidad}')";

                        if($Caduca != 'S') // Si Caduca = N, se registrará una fecha de elaboración
                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', CURDATE())";

                        //if (!($res = mysqli_query($conn, $sql)))
                          //  echo "Falló la preparación B: (" . mysqli_error($conn) . ") ". $sql;
                    try{
                    $res = $pdo->prepare($sql);
                    $res->execute();
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }


                    }

                    $idy_ubica = $idy_ubica_SP_Guardar;
                    $sql_check .= ";10;".$sql."\n\n;\n";
                    //$sql = "SELECT DISTINCT cve_almac FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica'";
                    $sql = "SELECT DISTINCT cve_almac FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = '$idy_ubica'";
                    //if (!($res = mysqli_query($conn, $sql)))
                    //    echo "Falló la preparación A: (" . mysqli_error($conn) . ") ";
                    //$cve_almacen = mysqli_fetch_array($res)['cve_almac'];

                    try{
                    //$sql = "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE idy_ubica = '$idy_ubica_SP_Guardar' AND cve_articulo = '$Cve_Articulo_SP_Guardar' AND lote = '$Cve_Lote_SP_Guardar' AND ntarima = '$nTarima_SP_Guardar'";
                    $res = $pdo->prepare($sql);
                    $res->execute();
                    $cve_almacen = $res->fetch()['cve_almac'];
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }


                   $sql = "UPDATE t_ordenprod SET Cve_Lote = '{$LoteOT}' WHERE Folio_Pro = '$orden_id'";
                    //if (!($res = mysqli_query($conn, $sql))) {
                    //    echo "Falló la preparación jj: (" . mysqli_error($conn) . ") ";
                    //}
                    try{
                    $res = $pdo->prepare($sql);
                    $res->execute();
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                    $sql_check .= ";11;".$sql."\n\n;\n";

                    if(!$ID_Proveedor) $ID_Proveedor = 0;
                    $sql_check .= ";11-1 idy_ubica = ;".$idy_ubica."\n\n;\n";
                }
                else
                {
                        $sql = "UPDATE ts_existenciatarima SET Existencia = Existencia + $cantidad_art_compuesto WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND lote = '$LoteOT' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read')";
                        echo $sql;
                        exit;
                        $sql_check .= ";7;".$sql."\n\n;\n";
                        try{
                        $res = $pdo->prepare($sql);
                        $res->execute();
                        } catch (PDOException $e) {
                            echo 'Error de conexión: ' . $e->getMessage();
                        }

                        //if (!($res = mysqli_query($conn, $sql))) {
                        //    echo "Falló la preparación jj: (" . mysqli_error($conn) . ") ";
                        //}

                }
                    if(!$ID_Proveedor) $ID_Proveedor = 0;
                    if($LoteOT == "") $LoteOT = $orden_id;

                if($idy_ubica)
                {

                    //$sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) VALUES ('{$cve_almacen}', {$idy_ubica}, '{$cod_art_compuesto}', '{$LoteOT}', 0, (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read'), 0, {$cantidad_art_compuesto}, 1, {$ID_Proveedor}, 0)";

                    //$sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) VALUES ('{$id_almacen}', {$idy_ubica}, '{$cod_art_compuesto}', '{$LoteOT}', 0, (SELECT ntarima FROM t_tarima WHERE fol_folio = '$orden_id' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$lp_read}')), 0, {$cantidad_art_compuesto}, 1, {$ID_Proveedor}, 0)";
                    $sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) (SELECT '{$id_almacen}', {$idy_ubica}, cve_articulo, '{$LoteOT}', 0, ntarima, 0, cantidad, 1, (SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}'), 0 FROM t_tarima WHERE fol_folio = '{$orden_id}')";
                    $sql_check .= ";12;".$sql."\n\n;\n";

                    try{
                    $res = $pdo->prepare($sql);
                    $res->execute();
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                    //if (!($res = mysqli_query($conn, $sql))) {
                    //    echo "Falló la preparación Z: (" . mysqli_error($conn) . ") " . $sql;
                    //}



                    //$sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = IFNULL(Cant_Prod, 0)+$cantidad_art_compuesto WHERE Folio_Pro = '$orden_id'";
                    $sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = Cantidad WHERE Folio_Pro = '$orden_id'";
                    try{
                    $res = $pdo->prepare($sql);
                    $res->execute();
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }
                    //if (!($res = mysqli_query($conn, $sql))) {
                    //    echo "Falló la preparación I: (" . mysqli_error($conn) . ") ";
                    //}
                        $sql_check .= ";13;".$sql."\n\n;\n";

                //$sql = "SELECT DISTINCT ROUND((Cantidad_Producida*Cantidad / Cantidad), 0) AS Cantidad_Producida
                        //FROM t_artcompuesto
                        //WHERE Cve_ArtComponente = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$orden_id')";

                    //$sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = IFNULL(Cant_Prod, 0)+$cantidad_art_compuesto WHERE Folio_Pro = '$orden_id'";
                    //if (!($res = mysqli_query($conn, $sql))) {
                    //    echo "Falló la preparación I: (" . mysqli_error($conn) . ") ";
                    //}

                    $sql = "SELECT 
                                c_unimed.cve_umed,
                                t.cve_almac as almacen_prod,
                                t.Cve_Articulo AS cve_articulo,
                                t.Cve_Usuario as cve_usuario,
                                t.Cant_Prod
                            FROM t_ordenprod t 
                            LEFT JOIN c_articulo a ON a.cve_articulo = t.Cve_Articulo AND a.Compuesto = 'S'
                            LEFT JOIN c_unimed ON a.unidadMedida = c_unimed.id_umed
                            WHERE a.cve_articulo = t.Cve_Articulo AND t.Folio_Pro = '{$orden_id}'";

                    try{
                    $res = $pdo->prepare($sql);
                    $res->execute();
                    $row_ord = $res->fetch();
                    $cve_umed = $row_ord['cve_umed'];
                    $almacen_prod = $row_ord['almacen_prod'];
                    $cve_articulo_ord = $row_ord['cve_articulo'];
                    $Cant_Prod_ord = $row_ord['Cant_Prod'];
                    $cve_usuario = $row_ord['cve_usuario'];
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                    //$query = mysqli_query($conn, $sql);
                    //$row_ord = mysqli_fetch_array($query);
                    //$cve_umed = $row_ord['cve_umed'];
                    //$almacen_prod = $row_ord['almacen_prod'];
                    //$cve_articulo_ord = $row_ord['cve_articulo'];
                    //$Cant_Prod_ord = $row_ord['Cant_Prod'];
                    //$cve_usuario = $row_ord['cve_usuario'];


//'{$id_almacen}', {$idy_ubica}, cve_articulo, '{$LoteOT}', 0, ntarima, 0, cantidad, 1, 0

//(SELECT cve_articulo, cve_lote, NOW(), 'PT_{$orden_id}', '{$idy_ubica}', {$Cant_Prod_ord}, 14, '{$cve_usuario}','{$id_almacen}' FROM t_tarima WHERE fol_folio = '{$orden_id}')

                $sql_tarima = "SELECT ntarima as tarima_kdx, Fol_Folio AS folio_kdx, cve_articulo AS cve_articulo_kdx, lote AS cve_lote_kdx, cantidad AS cantidad_kdx FROM t_tarima WHERE Fol_Folio = '{$orden_id}'";
                //$res_tarima = mysqli_query($conn, $sql_tarima);
                try{
                $res_tarima = $pdo->prepare($sql_tarima);
                $res_tarima->execute();

                while($row_tarima = $res_tarima->fetch())
                {
                    extract($row_tarima);
                    $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('$cve_articulo_kdx', '$cve_lote_kdx', NOW(), 'PT_{$folio_kdx}', '$idy_ubica', '$cantidad_kdx', 14, '$cve_usuario', '$id_almacen')";
                    //$res_kardex = mysqli_query($conn, $sql_kardex);
                    try{
                    $res_kardex = $pdo->prepare($sql_kardex);
                    $res_kardex->execute();
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }
                    $sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES 
                    ((SELECT MAX(id) FROM t_cardex), '$clave_almacen', {$tarima_kdx}, CURDATE(),'PT_{$folio_kdx}', '{$idy_ubica}', 14, '{$cve_usuario}', 'I')";
                    //$res_kardex = mysqli_query($conn, $sql_kardex);
                    try{
                    $res_kardex = $pdo->prepare($sql_kardex);
                    $res_kardex->execute();
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                }

                } catch (PDOException $e) {
                    echo 'Error de conexión: ' . $e->getMessage();
                }



              //*******************************************************************************
              //                          EJECUTAR EN INFINITY
              //*******************************************************************************
              //$sql = "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'";
              //$query = mysqli_query($conn, $sql);
              //$ejecutar_infinity = mysqli_fetch_array($query)['existe'];
/*
              if($ejecutar_infinity)
              {
/////////////////////$sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = '{$cve_articulo_ord}' and cve_almac = '$almacen_prod' AND tipo = 'ubicacion'";
/////////////////////$query = mysqli_query($conn, $sql);
/////////////////////$row_ord = mysqli_fetch_array($query);
/////////////////////$existencia_art_prod = $row_ord['existencia_art_prod'];
                    $sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = :cve_articulo_ord and cve_almac = :almacen_prod AND tipo = 'ubicacion'";
                    try{
                    $query = \db()->prepare($sql);
                    $query->execute(array('cve_articulo_ord' => $cve_articulo_ord, 'almacen_prod' => $almacen_prod));
                    $row_ord = $query->fetch();
                    $existencia_art_prod = $row_ord['existencia_art_prod'];
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                    if(!$existencia_art_prod) $existencia_art_prod = 0;
                      $json = "[";
                      //$row = mysqli_fetch_array($query);
                      //echo $sql;
                        extract($row_ord);
                        //if($this->pSQL($row[self::LOTE]) == "") 
                            $LoteOT = "";
                        $json .= "{";
                        $json .= '"item":"'.$cve_articulo_ord.'","um":"'.$cve_umed.'","batch":"'.$LoteOT.'", "qty": '.$existencia_art_prod.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                        $json .= "}";
                      //$json[strlen($json)-1] = ' ';
                      $json .= "]";

                
                  $curl = curl_init();

                  //$url_curl = $Url_inf.':8080/'.$Servicio_inf;
                  curl_setopt_array($curl, array(
                    // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                    CURLOPT_URL => "$url_curl",
                    //CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>
                    // Aquí cambia la cadena JSON
                    $json,
                    CURLOPT_HTTPHEADER => array(
                      'Content-Type: application/json',
                      //'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
                      'Authorization: Basic '.$Codificado.''
                    )
                    ,CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                  ));

                  $response = curl_exec($curl);
                  $response_ot .= $response."\n";

                  curl_close($curl);      
                  //echo $response;
                  
                  //$response = 'Pendiente';
                  //$sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', 'Transformacion', 'WEB')";
                  //$query = mysqli_query($conn, $sql);
                    $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), :Servicio_inf, :json, :response, 'Transformacion', 'WEB')";
                    try{
                    $query = \db()->prepare($sql);
                    $query->execute(array('Servicio_inf' => $Servicio_inf, 'json' => $json, 'response' => $response));
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

              }
              */

              if($ejecutar_infinity)
              {
/////////////////////$sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = '{$cve_articulo_ord}' and cve_almac = '$almacen_prod' AND tipo = 'ubicacion'";
/////////////////////$query = mysqli_query($conn, $sql);
/////////////////////$row_ord = mysqli_fetch_array($query);
/////////////////////$existencia_art_prod = $row_ord['existencia_art_prod'];

/************************************************************************
            $sql = "SELECT 
                        c_unimed.cve_umed,
                        t.Cve_Articulo AS cve_articulo,
                        IF(t.Cve_Lote = t.Folio_Pro, '', IFNULL(t.Cve_Lote, '')) AS LoteOT,
                        alm.clave AS clave_almacen,
                        alm.id AS id_almacen,
                        t.Status,
                        t.idy_ubica,
                        t.Cant_Prod
                    FROM t_ordenprod t 
                    LEFT JOIN c_articulo a ON a.cve_articulo = t.Cve_Articulo AND a.Compuesto = 'S'
                    LEFT JOIN c_unimed ON a.unidadMedida = c_unimed.id_umed
                    LEFT JOIN c_almacenp alm ON alm.id = t.cve_almac 
                    WHERE a.cve_articulo = t.Cve_Articulo AND t.Folio_Pro = '{$orden_id}'";
                    //$query = mysqli_query($conn, $sql);
                    //$row_ord = mysqli_fetch_array($query);
                    try{
                    $query = \db()->prepare($sql);
                    $query->execute();
                    $row_ord = $query->fetch();

                    $cve_umed = $row_ord['cve_umed'];
                    $cve_articulo_ord = $row_ord['cve_articulo'];
                    $Cant_Prod_ord = $row_ord['Cant_Prod'];
                    $LoteOT = $row_ord['LoteOT'];
                    $clave_almacen = $row_ord['clave_almacen'];
                    $StatusOT = $row_ord['Status'];
                    $id_almacen = $row_ord['id_almacen'];
                    $idy_ubica = $row_ord['idy_ubica'];

                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                    $sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = :cve_articulo_ord and cve_almac = :id_almacen AND tipo = 'ubicacion'";
                    try{
                    $query = \db()->prepare($sql);
                    $query->execute(array('cve_articulo_ord' => $cve_articulo_ord, 'id_almacen' => $id_almacen));
                    $row_ord = $query->fetch();
                    $existencia_art_prod = $row_ord['existencia_art_prod'];
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                    if(!$existencia_art_prod) $existencia_art_prod = 0;
/*
                    $sql = "SELECT Url, Servicio, User, Pswd, Empresa, TO_BASE64(CONCAT(User,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1";
                    $query = mysqli_query($conn, $sql);
                    $row_infinity = mysqli_fetch_array($query);
                    $Url_inf = $row_infinity['Url'];
                    $url_curl = $row_infinity['url_curl'];
                    $Servicio_inf = $row_infinity['Servicio'];
                    $User_inf = $row_infinity['User'];
                    $Pswd_inf = $row_infinity['Pswd'];
                    $Empresa_inf = $row_infinity['Empresa'];
                    $hora_movimiento = $row_infinity['hora_movimiento'];
                    $Codificado = $row_infinity['Codificado'];
**************************************************************************
                      $json = "[";
                      //$row = mysqli_fetch_array($query);
                      //echo $sql;
                        extract($row_ord);
                        //if($this->pSQL($row[self::LOTE]) == "") 
                            $LoteOT = "";
                        $json .= "{";
                        $json .= '"item":"'.$cod_art_compuesto.'","um":"'.$cve_umed.'","batch":"", "qty": '.$existencia_art_prod.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                        $json .= "}";
                      //$json[strlen($json)-1] = ' ';
                      $json .= "]";
***********************************************************************/
                /*
                  $curl = curl_init();

                  //$url_curl = $Url_inf.':8080/'.$Servicio_inf;
                  curl_setopt_array($curl, array(
                    // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                    CURLOPT_URL => "$url_curl",
                    //CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>
                    // Aquí cambia la cadena JSON
                    $json,
                    CURLOPT_HTTPHEADER => array(
                      'Content-Type: application/json',
                      //'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
                      'Authorization: Basic '.$Codificado.''
                    )
                    ,CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                  ));

                  $response = curl_exec($curl);
                  //$response_ot .= $response."\n";

                  curl_close($curl);      
                  //echo $response;
                  
                  //$response = 'Pendiente';
                  //$sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', 'Transformacion', 'WEB')";
                  //$query = mysqli_query($conn, $sql);
                    $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), :Servicio_inf, :json, :response, 'Transformacion', 'WEB')";
                    try{
                    $query = \db()->prepare($sql);
                    $query->execute(array('Servicio_inf' => $Servicio_inf, 'json' => $json, 'response' => $response));
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }
*/
                    ////////////////////$interfase->RegistrarCadena($json, $Servicio_inf, $Codificado, $url_curl, 'Transformacion', 'WEB');
              }

              //*******************************************************************************/
              //*******************************************************************************



                }
            //xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

            }//acepto
            else
            {
                //acepto == false
                $folios_sin_stock[] = $orden_id;
            }

                }//while
//*****************************************************************************************************************
                }
                else //else Tipo_OT
                {
                    //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                $sql = "
                SELECT op.clave, op.control_lotes, op.Lote, op.LoteOT, op.Caduca, op.Caducidad, op.control_peso, op.Cantidad, op.Cantidad_Producida, op.ubicacion, IFNULL(op.existencia, 0) AS existencia, op.Cve_Contenedor, op.mav_cveunimed FROM (
                    SELECT DISTINCT 
                        a.cve_articulo AS clave,
                        IFNULL(a.control_lotes, 'N') AS control_lotes,
                        e.cve_lote AS Lote,
                        t.Cve_Lote AS LoteOT,
                        IFNULL(a.control_peso, 'N') AS control_peso,
                        IFNULL(a.Caduca, 'N') AS Caduca,
                        IF(a.Caduca = 'S', l.Caducidad, '') AS Caducidad,
                        IF(IFNULL(th.Fol_folio, '') = '', ac.Cantidad, ac.Cantidad/(SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '$orden_id')) AS Cantidad,
                        (ac.Cantidad_Producida*ac.Cantidad) AS Cantidad_Producida,

                        #(SELECT cu.idy_ubica FROM V_ExistenciaProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND vp.cve_articulo = a.cve_articulo AND vp.cve_almac = '{$id_almacen}' AND cu.Activo = 1 AND cu.AreaProduccion = 'S' AND vp.cve_ubicacion = t.idy_ubica GROUP BY vp.Existencia LIMIT 1) AS ubicacion,
                        t.idy_ubica as ubicacion,

                        #(SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion = 'S' AND vp.cve_articulo = a.cve_articulo AND vp.tipo = 'ubicacion' AND vp.cve_lote = td.Cve_Lote) AS existencia
                        #AND cu.AreaProduccion = 'S'
                        e.Cve_Contenedor,
                        t.Cve_Usuario as cve_usuario,
                        u.mav_cveunimed,
                        e.Existencia AS existencia 
                    FROM t_artcompuesto ac
                        LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                        LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro
                        LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
                        LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = td.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = td.Folio_Pro)
                        LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_Articulo AND l.LOTE = e.cve_lote
                        LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
                        LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                    WHERE t.Folio_Pro = '$orden_id' AND e.cve_almac = '{$id_almacen}' 
                    #AND e.cve_lote = td.Cve_Lote AND e.cve_articulo = td.Cve_Articulo
                    AND t.idy_ubica = '{$idy_ubica_ot}'
                    AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = td.Cve_Articulo #AND e.cve_lote = td.Cve_Lote
                    ORDER BY Caducidad
                ) AS op WHERE op.ubicacion IS NOT NULL";

                $res_art = "";
                $sql_art = $sql;
                if (!($res_art = mysqli_query($conn, $sql)))
                    echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";

                $acepto = true;

                while($row_art = mysqli_fetch_array($res_art))
                {
                    //+$row_art['Cantidad_Producida'] 
                    //&& $row_art['existencia'] > 0
                    if(($row_art['Cantidad']*$cantidad_art_compuesto) > $row_art['existencia'] )
                    {
                        $acepto = false;
                        break;
                    }
                }
                if($acepto == false)
                    $folios_sin_stock[] = $orden_id;
                else
                {
                    while($row_art_1 = mysqli_fetch_array($res_art))
                    {
                        //if($Tipo_OT == 'IMP_LP') $cantidad_art_compuesto = $row_art_1['existencia'];
                        $cantidad = $row_art_1['Cantidad']*$cantidad_art_compuesto;
                        $idy_ubica = $row_art_1['ubicacion'];
                        $clave = $row_art_1['clave'];
                        $Lote = $row_art_1['Lote'];
                        $LoteOT = $row_art_1['LoteOT'];
                        $caducidadMIN = $row_art_1['Caducidad'];
                        $Caduca = $row_art_1['Caduca'];
                        $Cve_Contenedor = $row_art_1['Cve_Contenedor'];
                        $cve_usuario = $row_art_1['cve_usuario'];
                        $mav_cveunimed = $row_art_1['mav_cveunimed'];
                        $control_peso = $row_art_1['control_peso'];

                        //if($idy_ubica == "") $mensaje_error .= "El Producto {$clave} no se encuentra en una ubicación de Producción\n";
                        $caducidad = "";
                        $listo = false;
                        $LoteOT = "";
                        $mensaje_error = "";

                        if($Caduca == 'S' && $caducidadMIN != '' && $caducidadMIN != '0000-00-00' && $listo == false)
                        {
                            $caducidad = $caducidadMIN;
                            $listo = true;
                        }

                        if($control_peso == 'N' && $mav_cveunimed == 'H87') $cantidad = ceil($cantidad);

                        $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and cve_lote = '$Lote'";

                        if($Cve_Contenedor != '')
                            $sql = "UPDATE ts_existenciatarima SET existencia = existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and lote = '$Lote' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')";

                        if (!($res = mysqli_query($conn, $sql))) {
                            echo "Falló la preparación G: (" . mysqli_error($conn) . ") ";

                        $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$clave}', '{$Lote}', NOW(), '{$idy_ubica}', '{$orden_id}', {$cantidad}, 8, '{$cve_usuario}','{$id_almacen}')";
                        $res_kardex = mysqli_query($conn, $sql_kardex);


                        }

//**************************************************************************************************************************
                $sql = "SELECT COUNT(*) as existe FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND cve_lote = '$LoteOT'";

                if (!($res = mysqli_query($conn, $sql)))
                    echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
                $existe_producto = mysqli_fetch_array($res)['existe'];


                $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia + $cantidad_art_compuesto WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND cve_lote = '$LoteOT'";

                if($existe_producto == 0)
                {
                    $sql = "SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
                    $ID_Proveedor = mysqli_fetch_array($res)['ID_Proveedor'];

                    $sql = "SELECT DISTINCT control_lotes, Caduca FROM c_articulo WHERE cve_articulo = '$cod_art_compuesto'";
                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación E: (" . mysqli_error($conn) . ") ";
                    $row_control = mysqli_fetch_array($res);
                    $control_lotes = $row_control['control_lotes'];
                    $Caduca = $row_control['Caduca'];

                    /*
                    if($control_lotes != 'S')
                    {
                        $sql = "UPDATE c_articulo SET control_lotes = 'S' WHERE cve_articulo = '$cod_art_compuesto'";
                        if (!($res = mysqli_query($conn, $sql)))
                            echo "Falló la preparación D: (" . mysqli_error($conn) . ") ";
                    }

                    if($Caduca != 'S' && $caducidad != '')
                    {
                        $sql = "UPDATE c_articulo SET Caduca = 'S' WHERE cve_articulo = '$cod_art_compuesto'";
                        if (!($res = mysqli_query($conn, $sql)))
                            echo "Falló la preparación C: (" . mysqli_error($conn) . ") ";
                    }
                    */
                    if($LoteOT)
                    {
                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', '{$caducidad}')";

                        if($Caduca != 'S') // Si Caduca = N, se registrará una fecha de elaboración
                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', CURDATE())";

                        if (!($res = mysqli_query($conn, $sql)))
                            echo "Falló la preparación B: (" . mysqli_error($conn) . ") ". $sql;
                    }


                    //$sql = "SELECT DISTINCT cve_almac FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica'";
                    $sql = "SELECT DISTINCT cve_almac FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = '$idy_ubica'";
                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación A: (" . mysqli_error($conn) . ") ";
                    $cve_almacen = mysqli_fetch_array($res)['cve_almac'];
                    if($LoteOT == "") $LoteOT = $orden_id;
                   $sql = "UPDATE t_ordenprod SET Cve_Lote = '{$LoteOT}' WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación jj: (" . mysqli_error($conn) . ") ";
                    }

                    if(!$ID_Proveedor) $ID_Proveedor = 0;
                   $sql = "INSERT IGNORE INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) VALUES ('{$cve_almacen}', {$idy_ubica}, '{$cod_art_compuesto}', '{$LoteOT}', {$cantidad_art_compuesto}, {$ID_Proveedor})";
                }

                if($idy_ubica)
                {
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación Z: (" . mysqli_error($conn) . ") " . $sql;
                    }

    /*
                $sql = "SELECT DISTINCT ROUND((Cantidad_Producida*Cantidad / Cantidad), 0) AS Cantidad_Producida
                        FROM t_artcompuesto
                        WHERE Cve_ArtComponente = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$orden_id')";
    */

                    $sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = IFNULL(Cant_Prod, 0)+$cantidad_art_compuesto WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación I: (" . mysqli_error($conn) . ") ";
                    }


                $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('$cod_art_compuesto', '$LoteOT', NOW(), 'PT_{$orden_id}', '$idy_ubica', '$cantidad_art_compuesto', 14, '$cve_usuario', '$id_almacen')";
                $res_kardex = mysqli_query($conn, $sql_kardex);

              //*******************************************************************************
              //                          EJECUTAR EN INFINITY
              //*******************************************************************************
              $sql = "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'";
              $query = mysqli_query($conn, $sql);
              $ejecutar_infinity = mysqli_fetch_array($query)['existe'];

              if($ejecutar_infinity)
              {
                    $sql = "SELECT 
                                c_unimed.cve_umed,
                                t.Cve_Articulo AS cve_articulo,
                                t.Cant_Prod
                            FROM t_ordenprod t 
                            LEFT JOIN c_articulo a ON a.cve_articulo = t.Cve_Articulo AND a.Compuesto = 'S'
                            LEFT JOIN c_unimed ON a.unidadMedida = c_unimed.id_umed
                            WHERE a.cve_articulo = t.Cve_Articulo AND t.Folio_Pro = '{$orden_id}'";
                    $query = mysqli_query($conn, $sql);
                    $row_ord = mysqli_fetch_array($query);
                    $cve_umed = $row_ord['cve_umed'];
                    $cve_articulo_ord = $row_ord['cve_articulo'];
                    $Cant_Prod_ord = $row_ord['Cant_Prod'];


                    $sql = "SELECT Url, Servicio, User, Pswd, Empresa, to_base64(CONCAT(User,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1";
                    $query = mysqli_query($conn, $sql);
                    $row_infinity = mysqli_fetch_array($query);
                    $Url_inf = $row_infinity['Url'];
                    $url_curl = $row_infinity['url_curl'];
                    $Servicio_inf = $row_infinity['Servicio'];
                    $User_inf = $row_infinity['User'];
                    $Pswd_inf = $row_infinity['Pswd'];
                    $Empresa_inf = $row_infinity['Empresa'];
                    $hora_movimiento = $row_infinity['hora_movimiento'];
                    $Codificado = $row_infinity['Codificado'];


              $json = "[";
              //$row = mysqli_fetch_array($query);
                extract($row_ord);
                //if($this->pSQL($row[self::LOTE]) == "") 
                    $LoteOT = "";
                $json .= "{";
                $json .= '"item":"'.$cve_articulo_ord.'","um":"'.$cve_umed.'","batch":"'.$LoteOT.'", "qty": '.$Cant_Prod_ord.',"typeMov":"T","warehouse":"'.$cve_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                $json .= "}";
              //$json[strlen($json)-1] = ' ';
              $json .= "]";


                  $curl = curl_init();
                  //$url_curl = $Url_inf.':8080/'.$Servicio_inf;

                  curl_setopt_array($curl, array(
                    // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                    CURLOPT_URL => "$url_curl",
                    //CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>
                    // Aquí cambia la cadena JSON
                    $json,
                    CURLOPT_HTTPHEADER => array(
                      'Content-Type: application/json',
                      //'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
                      'Authorization: Basic '.$Codificado.''
                    )
                    ,CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                  ));

                  $response = curl_exec($curl);
                  //$response_ot .= $response."\n";

                  curl_close($curl);      
                  //echo $response;
                  
                  //$response = 'Pendiente';
                  $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', 'Transformacion', 'WEB')";
                  $query = mysqli_query($conn, $sql);

              }
              //*******************************************************************************/
              //*******************************************************************************



                }

//**************************************************************************************************************************

                    }//while
                }//else
                }//else Tipo_OT



            }//foreach
        }
/*
        $registros_vacios--;
        $msj_folio_vacio = "";
        if($registros_vacios > 0)
        {
            if($registros_vacios == 1)
                $msj_folio_vacio = "\nHay 1 registro en el archivo que se omitió ya que se subió con el Folio vacío\n\n";
            else 
                $msj_folio_vacio = "\nExisten ".($registros_vacios)." registros con Folios vacíos en el archivo que se omitieron\n\n";
        }

*/
        $msj_sin_stock = "";
        if(count($folios_sin_stock) > 0)
        {
            $folios_implode = implode($folios_sin_stock, ", ");
            $msj_sin_stock = "Los Folios: $folios_implode No poseen Stock Para producir, puede producirlos en Administración de OT después de surtir material";
        }
        $referencias_sin_stock = "";
        if(count($referencias) > 0)
        {
            $folios_implode = implode($referencias, ", ");
            $msj_sin_stock = "Las Referencias: $folios_implode No poseen Stock Para producir, puede producirlos en Administración de OT después de surtir material o volver a importar cuando surta los componentes";
        }

        $msj_creados = "";
        if(count($folios_creados) > 0)
        {
            $folios_implode = implode($folios_creados, ", ");
            $msj_creados = "Fueron Creados Los Folios: $folios_implode \n\n";
        }

        if($realizar_produccion && $ejecutar_infinity && $msj_creados)
        {
            $folios_implode = implode($folios_creados, "','");
            $folios_implode = "'".$folios_implode."'";

            //************************************************************************************************************
            //                          ENVIAR A INFINITY LOS PRODUCTOS TERMINADOS
            //************************************************************************************************************

            $sql = "SELECT DISTINCT
                        c_unimed.cve_umed,
                        t.Cve_Articulo AS cve_articulo,
                        IF(t.Cve_Lote = t.Folio_Pro, '', IFNULL(t.Cve_Lote, '')) AS LoteOT,
                        alm.clave AS clave_almacen,
                        alm.id AS id_almacen,
                        t.Status,
                        t.idy_ubica,
                        t.Cant_Prod
                    FROM t_ordenprod t 
                    LEFT JOIN c_articulo a ON a.cve_articulo = t.Cve_Articulo AND a.Compuesto = 'S'
                    LEFT JOIN c_unimed ON a.unidadMedida = c_unimed.id_umed
                    LEFT JOIN c_almacenp alm ON alm.id = t.cve_almac 
                    WHERE a.cve_articulo = t.Cve_Articulo AND t.Folio_Pro IN ($folios_implode)";
                    //$query = mysqli_query($conn, $sql);
                    //$row_ord = mysqli_fetch_array($query);
                    try{
                    $query = $pdo->prepare($sql);
                    $query->execute();
                    
                    while($row_ord = $query->fetch())
                    {
                        $cve_umed = $row_ord['cve_umed'];
                        $cve_articulo_ord = $row_ord['cve_articulo'];
                        $Cant_Prod_ord = $row_ord['Cant_Prod'];
                        $LoteOT = $row_ord['LoteOT'];
                        $clave_almacen = $row_ord['clave_almacen'];
                        $StatusOT = $row_ord['Status'];
                        $id_almacen = $row_ord['id_almacen'];
                        $idy_ubica = $row_ord['idy_ubica'];

                        $sql_prod = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = :cve_articulo_ord and cve_almac = :id_almacen AND tipo = 'ubicacion'";
                        try{
                        $query_prod = $pdo->prepare($sql_prod);
                        $query_prod->execute(array('cve_articulo_ord' => $cve_articulo_ord, 'id_almacen' => $id_almacen));
                        $row_ord_prod = $query_prod->fetch();
                        $existencia_art_prod = $row_ord_prod['existencia_art_prod'];
                        } catch (PDOException $e) {
                            echo 'Error de conexión: ' . $e->getMessage();
                        }

                        if(!$existencia_art_prod) $existencia_art_prod = 0;

                          $json = "[";
                          //$row = mysqli_fetch_array($query);
                          //echo $sql;
                            //extract($row_ord);
                            //if($this->pSQL($row[self::LOTE]) == "") 
                                $LoteOT = "";
                            $json .= "{";
                            $json .= '"item":"'.$cve_articulo_ord.'","um":"'.$cve_umed.'","batch":"", "qty": '.$existencia_art_prod.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                            $json .= "}";
                          //$json[strlen($json)-1] = ' ';
                          $json .= "]";

                        $interfase->RegistrarCadena($json, $Servicio_inf, $Codificado, $url_curl, 'Transformacion', 'WEB');
                    }
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

            //************************************************************************************************************
            //                                      ENVIAR LOS COMPONENTES A INFINITY
            //************************************************************************************************************
                    //$sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = :clave and cve_lote = :cve_lote and cve_almac = :id_almacen AND tipo = 'ubicacion'";
                    $sql = "SELECT DISTINCT v.Cve_Articulo as clave, u.cve_umed as um, IFNULL(v.cve_lote, '') as Lote, IFNULL(CONVERT(SUM(eg.Existencia), FLOAT), 0) as existencia_art_prod, alm.clave as clave_almacen
                            FROM t_cardex v 
                            LEFT JOIN t_ordenprod t ON t.Folio_Pro = v.destino
                            LEFT JOIN c_almacenp alm on t.cve_almac = alm.id
                            LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
                            LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                            LEFT JOIN V_ExistenciaGralProduccion eg ON eg.cve_almac = alm.id and eg.tipo = 'ubicacion' and eg.cve_articulo = v.cve_articulo and IFNULL(eg.Cve_Lote, '') = IFNULL(v.cve_lote, '')
                            WHERE v.destino IN ($folios_implode) 
                            AND v.id_TipoMovimiento = 8 
                            GROUP BY v.Cve_Articulo, IFNULL(v.cve_lote, '')";
                    try{
                    $query = $pdo->prepare($sql);
                    $query->execute();
                    //$query->execute(array('clave' => $clave, 'cve_lote' => $Lote,'id_almacen' => $id_almacen));
                        while($row_ord = $query->fetch())
                        {
                            extract($row_ord);
                              $json = "[";

                                $json .= "{";
                                $json .= '"item":"'.$clave.'","um":"'.$um.'","batch":"'.$Lote.'", "qty": '.$existencia_art_prod.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                                $json .= "}";
                              //$json[strlen($json)-1] = ' ';
                              $json .= "]";
                            $interfase->RegistrarCadena($json, $Servicio_inf, $Codificado, $url_curl, 'Transformacion', 'WEB');
                        }
                    } catch (PDOException $e) {
                        echo 'Error de conexión: ' . $e->getMessage();
                    }

                    //$existencia_art_prod = $row_ord['existencia_art_prod'];

                    //if(!$existencia_art_prod) $existencia_art_prod = 0;


            //************************************************************************************************************
        }
        $pdo = null;
        @mysqli_close($conn);
        $this->response(200, [
            'statusText' =>  "Ordenes de Producción importados con exito. Total de Ordenes: \"{$registros}\" \n\n $msj_creados \n\n $msj_sin_stock \n\n$msj_folio_vacio",
            'msj_tracking' => $sql_tracking,
            'folios_creados' => $folios_creados,
            'realizar_produccion' => $realizar_produccion,
            'sql_check' => $sql_check,
            'responses' => $response_ot,
            'url_curl' => "{$url_curl}",
            'articulos' => $articulos
        ]);

    }


/*
    public function validarRequeridosImportar($row)
    {
        foreach ($this->camposRequeridos as $key => $campo){
            if( empty($row[$key]) ){
                return $campo;
            }
        }
        return true;
    }
*/
    /**
     * Undocumented function
     *
     * @return void
     */
    public function exportar()
    {
        $columnas = [
            'Fecha OT',
            'Hora OT',
            'Folio OT',
            'Pedido',
            'Clave Producto',
            'Nombre Producto',
            'Lote | Serie',
            'Caducidad',
            'Cant. Solicitada',
            'Cant. Producida',
            'Usuario', 
            'Fecha Compromiso',
            'Status',
            'Empresa | Proveedor',
            utf8_decode('Almacén')
        ];


        $almacen = $_GET['almacen'];

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            $sql_chatset = "SET NAMES utf8mb4;";
            if (!($res = mysqli_query($conn, $sql_chatset))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }


        $sql = "SELECT DISTINCT
            c.Folio_Pro, 
            c.Cve_Articulo, 
            a.control_peso,
            DATE_FORMAT(c.FechaReg, '%d-%m-%Y') AS Fecha,
            DATE_FORMAT(c.FechaReg, '%h:%i:%s %p') AS Hora_OT, 
            DATE_FORMAT(c.Fecha, '%d-%m-%Y') AS FechaCompromiso, 
            (select des_articulo from c_articulo where c.Cve_Articulo = cve_articulo) as descripcion, 
            pd.Fol_folio AS folio_rel,
            IF(a.control_lotes = 'S', IFNULL(l.Lote, ''), '') AS Cve_Lote, 
            IFNULL(IF(a.Caduca = 'S', IF(l.Caducidad=DATE_FORMAT('0000-00-00', '%Y-%m-%d'),'', DATE_FORMAT(l.Caducidad, '%d-%m-%Y')), ''), '') AS Caducidad,
            #IF(a.control_peso = 'S', CONCAT(TRUNCATE((SELECT SUM(Cantidad) FROM td_ordenprod WHERE Folio_Pro = c.Folio_Pro), 3), ''), (SELECT SUM(Cantidad) FROM td_ordenprod WHERE Folio_Pro = c.Folio_Pro)) AS Cantidad, 
            c.Cantidad,
            c.Cant_Prod, 
            IFNULL((SELECT nombre_completo FROM c_usuario WHERE c.Cve_Usuario = id_user), (SELECT nombre_completo FROM c_usuario WHERE c.Cve_Usuario = cve_usuario)) AS usuario, 
            DATE_FORMAT(c.Hora_Ini, '%d-%m-%Y') AS Fecha_Ini, 
            DATE_FORMAT(c.Hora_Ini, '%h:%i:%s %p') AS Hora_Ini, 
            DATE_FORMAT(c.Hora_Fin, '%d-%m-%Y') AS Fecha_Fin, 
            DATE_FORMAT(c.Hora_Fin, '%h:%i:%s %p') AS Hora_Fin, 
            c.Status as StatusOT,
            (CASE 
                WHEN IFNULL(pd.Fol_folio, '') != '' THEN 'Env&iacute;o Relacionado PV'
                WHEN (SELECT COUNT(*) FROM V_ExistenciaGralProduccion WHERE cve_articulo = c.Cve_Articulo AND cve_lote = c.Cve_Lote AND cve_ubicacion = (SELECT idy_ubica FROM c_ubicacion WHERE AreaProduccion = 'N' AND V_ExistenciaGralProduccion.cve_ubicacion = idy_ubica)) > 0 THEN 'Envio a Almac&eacute;n'
                WHEN c.Status = 'P' THEN 'Pendiente' 
                WHEN c.Status = 'I' THEN 'En Producci&oacute;n' 
                WHEN c.Status = 'T' THEN 'Terminado' 
                WHEN c.Status = 'B' THEN 'BackOrder' 
            END) as status, 
            #IFNULL((SELECT des_almac FROM c_almacen WHERE cve_almacenp = c.cve_almac), '--') AS zona,
            '' AS zona,
            p.Nombre as proveedor,
            al.nombre AS almacen,
            (SELECT COUNT(*) FROM ts_existenciapiezas WHERE cve_articulo = c.Cve_Articulo AND cve_lote = c.Cve_Lote AND cve_almac = c.cve_almac) AS traslado,
            IFNULL((SELECT GROUP_CONCAT(AreaProduccion SEPARATOR '' ) FROM c_ubicacion WHERE idy_ubica IN (SELECT DISTINCT idy_ubica FROM ts_existenciapiezas WHERE cve_articulo = c.Cve_Articulo AND cve_lote = c.Cve_Lote AND cve_almac = c.cve_almac)), 0) AS ubicacion_produccion,
            (SELECT COUNT(*) FROM ts_existenciatarima WHERE cve_articulo = c.Cve_Articulo AND lote = c.Cve_Lote AND cve_almac = c.cve_almac) AS palletizado
        from t_ordenprod c 
        LEFT JOIN c_articulo a ON a.cve_articulo = c.Cve_Articulo
        LEFT JOIN c_lotes l ON c.Cve_Articulo = l.cve_articulo AND c.Cve_Lote = l.Lote
        LEFT JOIN th_pedido pd ON pd.Ship_Num = c.Folio_Pro
        LEFT JOIN c_almacen ca ON ca.cve_almacenp = c.cve_almac
        LEFT JOIN c_almacenp al ON al.id = ca.cve_almacenp
        LEFT JOIN c_proveedores p ON p.ID_Proveedor = c.ID_Proveedor
        WHERE 1 AND al.clave = '{$almacen}' AND c.Status = 'P'";

            if (!($res = mysqli_query($conn, $sql))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

        //$data_oc = mysqli_fetch_assoc($res);
        $filename = "OT_Pendientes_".date('d-m-Y').".xls";
        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        //foreach($data_oc as $row)
        while($row = mysqli_fetch_assoc($res))
        {
            echo $this->clear_column($row['Fecha']) . "\t";
            echo $this->clear_column($row['Hora_OT']) . "\t";
            echo $this->clear_column($row['Folio_Pro']) . "\t";
            echo $this->clear_column($row['folio_rel']) . "\t";
            echo $this->clear_column($row['Cve_Articulo']) . "\t";
            echo $this->clear_column($row['descripcion']) . "\t";
            echo $this->clear_column($row['Cve_Lote']) . "\t";
            echo $this->clear_column($row['Caducidad']) . "\t";
            echo $this->clear_column($row['Cantidad']) . "\t";
            echo $this->clear_column($row['Cant_Prod']) . "\t";
            echo $this->clear_column($row['usuario']) . "\t";
            echo $this->clear_column($row['FechaCompromiso']) . "\t";
            echo $this->clear_column($row['status']) . "\t";
            echo $this->clear_column($row['proveedor']) . "\t";
            echo $this->clear_column($row['almacen']) . "\t";
            echo  "\r\n";
        }
        exit;
        
    }

    public function get_folios()
    {
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }

        $xlsx = new SimpleXLSX( $file );

        $linea = 1;
        $folios = array();
        foreach ($xlsx->rows() as $row)
        {
            $ot_cliente = $this->pSQL($row[self::OT_CLIENTE]);

            if($linea == 1 || $ot_cliente == "") {
                $registros_vacios++;
                $linea++;continue;
            }

            //$folios[] = "\"".$ot_cliente."\"";
            $folios[] = $ot_cliente;
        }

        for($f = 0; $f < count($folios); $f++)
        {
            $foliof = $folios[$f];
            $folios[$f] = "'".$foliof."'";
        }
        $implode_folios = implode(",", $folios);

        $this->response(400, [
            'folios' =>  $implode_folios
        ]);
    }

    public function importar_foam()
    {
        error_reporting(0);
        //ini_set('default_socket_timeout', 6000);
//        set_time_limit(12000);
        //ini_set("memory_limit",-1);
        //set_time_limit(0);

        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }

/*
        if ( $_FILES['file']['type'] != 'application/vnd.ms-excel' AND
                $_FILES['file']['type'] != 'application/msexcel' AND
                $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' AND
                $_FILES['file']['type'] != 'application/xls' )
            {
            @unlink($file);
            $this->response(400, [
                'statusText' =>  "Error en el formato del fichero",
            ]);
        }
*/
        $xlsx = new SimpleXLSX( $file );
/*
        $linea = 1;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }

            $eval = $this->validarRequeridosImportar($row);
            if( $eval === TRUE ){
            }

            else {
                $this->response(400, [
                    'statusText' =>  "La columna \"{$eval}\" de la Fila Nro. {$linea} está vacía. Corrija esto para continuar con la importación.",
                ]);
            }

            $linea++;
        }
*/
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'";
        $query = mysqli_query($conn, $sql);
        $ejecutar_infinity = mysqli_fetch_array($query)['existe'];


$sql = "SELECT Url, Servicio, User, Pswd, Empresa, to_base64(CONCAT(User,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1";
$query = mysqli_query($conn, $sql);
$row_infinity = mysqli_fetch_array($query);

                $Url_inf = $row_infinity['Url'];
                $url_curl = $row_infinity['url_curl'];
                $Servicio_inf = $row_infinity['Servicio'];
                $User_inf = $row_infinity['User'];
                $Pswd_inf = $row_infinity['Pswd'];
                $Empresa_inf = $row_infinity['Empresa'];
                $hora_movimiento = $row_infinity['hora_movimiento'];
                $Codificado = $row_infinity['Codificado'];


        $response_ot = "";
        $url_curl = "";
        $Folio_vacio = ""; $Folio_Anterior = "";$sql_check = ""; $OT_Folio_Anterior = "";
        $linea = 1; $registros = 0;
        $id_proveedor        = $_POST['Proveedor2'];
        $idy_ubica_ot        = $_POST['idy_ubica_ot_import'];
        $realizar_produccion = $_POST['realizar_produccion'];
        $folios_creados = array();

        $Folio_Pro = "";
        $registros_vacios = 0;


        $tipo_traslado_input = $_POST["tipo_traslado_input"];
        $almacen_dest = $_POST["almacen_dest"];
        $traslado_interno_externo_input = $_POST["traslado_interno_externo_input"];



        foreach ($xlsx->rows() as $row)
        {
            $ot_cliente = $this->pSQL($row[self::OT_CLIENTE]);

            if($linea == 1 || $ot_cliente == "") {
                $registros_vacios++;
                $linea++;continue;
            }
            //$clave = $this->pSQL($row[self::CODIGO_BL]);
            //$element = Ubicaciones::where('CodigoCSD', '=', $clave)->first();
/*
            $tipo = '';
            if($row[self::LIBRE] == 'S') $tipo = 'L';
            if($row[self::CUARENTENA] == 'S') $tipo = 'Q';
            if($row[self::RESERVADA] == 'S') $tipo = 'R';
*/
            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new OrdenDeProduccion(); 
            }

            //$model = new Ubicaciones(); 
            //$id_zona = ZonasDeAlmacenaje::where('clave_almacen', "'".$row[self::ZONA]."'")->get(['cve_
            //$Folio_Pro = $this->pSQL($row[self::FOLIO_ORDEN]);

            //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $cve_articulo = $row[self::CVE_ART_COMPUESTO];
            $sql = "SELECT COUNT(*) AS existe FROM t_artcompuesto WHERE Cve_ArtComponente = '{$cve_articulo}'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe = $resul['existe'];

            $existe_folio = 0;
            if($Folio_Pro != '')
            {
                if($tipo_traslado_input == 1)
                {
                    $sql = "SELECT COUNT(*) AS existe_folio FROM th_pedido WHERE Fol_Folio = '{$Folio_Pro}' AND Pick_Num = '{$ot_cliente}'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $existe_folio = $resul['existe_folio'];
                }
                else
                {
                    $sql = "SELECT COUNT(*) AS existe_folio FROM t_ordenprod WHERE Folio_Pro = '{$Folio_Pro}' AND Referencia = '{$ot_cliente}'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $existe_folio = $resul['existe_folio'];
                }
            }

            //if($Folio_Pro == "" && $Folio_vacio == "")
            if($existe_folio == 0)
            {
                if($tipo_traslado_input == 1)
                {
                    $Folio_Pro = $this->consecutivo_folio_traslado();
                }
                else 
                {
                    $sql = "SELECT `fct_consecutivo_documentos`('t_ordenprod', 6);";
                    $rs = mysqli_query(\db2(), $sql);
                    $row_folio_pro = mysqli_fetch_array($rs);
                    $Folio_Pro = "OT".$row_folio_pro[0];
                }
                $Folio_vacio = $Folio_Pro;
            }
            else if($Folio_Pro == "" && $Folio_vacio != "")
            {
                $Folio_Pro = $Folio_vacio;
            }

/*
            $existe_ot_cliente = 2;//ot_cliente vacio
            if($ot_cliente != "")
            {
                $sql = "SELECT COUNT(*) AS existe_ot_cliente FROM t_ordenprod WHERE Referencia = '{$ot_cliente}'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $existe_ot_cliente = $resul['existe_ot_cliente'];
                //existe_ot_cliente = 1 -> Existe
                //existe_ot_cliente = 0 -> No Existe
            }
*/

            $sql = "SELECT id from c_almacenp where id = (SELECT cve_almacenp FROM c_almacen where cve_almac = ".$_POST['cboZonaAlmacenImport'].") ;";
            $query = mysqli_query($conn, $sql);
            if($query->num_rows > 0)
            {
                $id_almacen = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["id"];

            if($existe && $existe_folio == 0)
            {//3
                $fa = getdate();
                $dia  = $fa['mday'];
                $mes  = $fa['mon'];
                $year = $fa['year'];
                if($dia < 10) $dia = '0'.$dia;
                if($mes < 10) $mes = '0'.$mes;
                $fecha = $year.'-'.$mes.'-'.$dia;

                $fecha_compromiso = $this->pSQL($row[self::FECHA_COMPROMISO]);
                if($fecha_compromiso)
                {//1
                    $fc = explode('-', $fecha_compromiso);
                    $fecha_compromiso = $fc[2]."-".$fc[1]."-".$fc[0];
                }//1

                if($tipo_traslado_input != 1)
                {
                    $model->Folio_Pro     = $Folio_Pro;
                    $model->cve_almac     = $id_almacen;//$this->pSQL($row[self::AREA_DE_PRODUCCION]);
                    $model->Cve_Articulo  = $this->pSQL($row[self::CVE_ART_COMPUESTO]);
                    $model->Cve_Lote      = $this->pSQL($row[self::LOTE]);
                    $model->Cantidad      = $this->pSQL($row[self::CANTIDAD_A_PRODUCIR]);
                    $model->ID_Proveedor  = $id_proveedor;//$this->pSQL($row[self::AREA_DE_PRODUCCION]);
                    $model->Cant_Prod     = 0;
                    $model->Cve_Usuario   = $this->pSQL($_SESSION['id_user']);
                    $model->Fecha         = $fecha;
                    $model->Referencia    = $ot_cliente;
                    $model->FechaReg      = ($fecha_compromiso !='')?($fecha_compromiso):($fecha);
                    $model->Status        = 'P';
                    $model->id_zona_almac = $_POST['cboZonaAlmacenImport'];
                    $model->idy_ubica     = $idy_ubica_ot;

                    $model->save();
                }

                $articulos = array();
                $nuevo_pedido = new \NuevosPedidos\NuevosPedidos();
                $sql = "SELECT Cve_Articulo, Cantidad FROM t_artcompuesto WHERE Cve_ArtComponente = '{$cve_articulo}' AND Cve_Articulo IN (SELECT cve_articulo FROM c_articulo WHERE IFNULL(tipo_producto, '') != 'ProductoNoSurtible')";
                $res = mysqli_query($conn, $sql);
                while($row_orden = mysqli_fetch_array($res, MYSQLI_ASSOC))
                {//2
                    if($tipo_traslado_input != 1)
                    {
                        
                        $sql = "CALL SPAD_AddUpdateTDOrdenProd (";
                        $sql .= "'".$Folio_Pro."','".$row_orden['Cve_Articulo']."',NOW(),'".$row_orden['Cantidad']."','".$_SESSION['id_user']."','1');";
                        $rs = mysqli_query($conn, $sql);
                        
                        /*
                        $sql = "SELECT COUNT(*) as existe FROM td_ordenprod WHERE Folio_Pro = '$Folio_Pro' AND Cve_Articulo = '".$row_orden['Cve_Articulo']."'";
                        $res_ord = mysqli_query($conn, $sql);
                        $existe_row_ord = mysqli_fetch_array($res_ord, MYSQLI_ASSOC)['existe'];

                        if($existe_row_ord)
                        {
                            $sql = "UPDATE  td_ordenprod
                                    SET     Cantidad = Cantidad + ".$row_orden['Cantidad']."
                                    WHERE   Folio_Pro='$Folio_Pro' AND Cve_Articulo='".$row_orden['Cve_Articulo']."'";
                            $res_ord = mysqli_query($conn, $sql);
                        }
                        else 
                        {
                            $sql = "INSERT INTO td_ordenprod(Folio_Pro,Cve_Articulo,Fecha_Prod,Cantidad,Usr_Armo,Activo) VALUES 
                                    ('".$Folio_Pro."','".$row_orden['Cve_Articulo']."',NOW(),'".$row_orden['Cantidad']."','".$_SESSION['id_user']."','1')";
                            $res_ord = mysqli_query($conn, $sql);
                        }
                        */

                    }
                    $cve_art = $row_orden['Cve_Articulo'];
                    $sql_art = "SELECT a.control_peso, a.peso, u.mav_cveunimed, a.unidadMedida FROM c_articulo a LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida WHERE a.cve_articulo = '$cve_art'";
                    $rs_art = mysqli_query(\db2(), $sql_art);
                    $row_art = mysqli_fetch_array($rs_art);
                    //$band_granel = $row_art["control_peso"];
                    //$peso = $row_art["peso"];
                    //$mav_cveunimed = $row_art["mav_cveunimed"];
                    $unidadMedida = $row_art["unidadMedida"];
                    $lp = $this->pSQL($row[self::LP]);

                    array_push($articulos,array(
                        "Cve_articulo" => $row_orden['Cve_Articulo'],
                        "Num_cantidad" => ($row_orden['Cantidad']*$this->pSQL($row[self::CANTIDAD_A_PRODUCIR])),
                        "id_unimed" => $unidadMedida,
                        "Num_Meses" => ""
                    ));

                }//2
/*
                $sql = "SELECT clave from c_almacenp where id = (SELECT cve_almacenp FROM c_almacen where cve_almac = ".$_POST['cboZonaAlmacenImport'].") ;";
                $query = mysqli_query($conn, $sql);
                if($query->num_rows > 0){
                    $id_almacen = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["clave"];
*/
                $lote = $this->pSQL($row[self::LOTE]);
                $caducidad = $this->pSQL($row[self::CADUCIDAD]);
                $sql = "SELECT IFNULL(control_lotes, 'N') as control_lotes, IFNULL(Caduca, 'N') as Caduca FROM c_articulo WHERE cve_articulo = '{$cve_articulo}'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $control_lotes = $resul['control_lotes'];
                $Caduca = $resul['Caduca'];


                $sql = "SELECT COUNT(*) as existe_lote FROM c_lotes WHERE cve_articulo = '{$cve_articulo}' AND Lote = '{$lote}'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $existe_lote = $resul['existe_lote'];


                if($lote != '' && $control_lotes == 'S' && $existe_lote == 0)
                {//3
                    $sql = "INSERT INTO c_lotes (cve_articulo, Lote) VALUES('{$cve_articulo}', '{$lote}')";
                    if($Caduca == 'S' && $caducidad != '')
                    {
                        $caducidad = $this->pSQL($row[self::CADUCIDAD]);
                        $fc = explode('-', $caducidad);
                        $caducidad = $fc[2]."-".$fc[1]."-".$fc[0];

                        $sql = "INSERT INTO c_lotes (cve_articulo, Lote, Caducidad) VALUES('{$cve_articulo}', '{$lote}', '{$caducidad}')";
                    }

                    $rs = mysqli_query($conn, $sql);
                }//3

            }//3
            else if($tipo_traslado_input != 1) //REVISAR BIEN AQUI
            {
                $lp = $this->pSQL($row[self::LP]);
                if($existe && $existe_folio && $lp)
                {
                    $cant_prod = $this->pSQL($row[self::CANTIDAD_A_PRODUCIR]);
                    $sql = "UPDATE t_ordenprod SET Cantidad = Cantidad + {$cant_prod} WHERE Folio_Pro = '{$Folio_Pro}'";
                    $rs = mysqli_query($conn, $sql);

                    $sql = "SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '{$Folio_Pro}'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $cant_prod = $resul['Cantidad'];

                    $sql = "SELECT Cantidad, Cve_Articulo FROM td_ordenprod WHERE Folio_Pro = '{$Folio_Pro}'";
                    $rs = mysqli_query($conn, $sql);

                    //foreach($articulos as $art)
                    while($resul = mysqli_fetch_array($rs, MYSQLI_ASSOC))
                    {
                        $cve_art = $resul["Cve_Articulo"];
                        $cant    = $resul["Cantidad"]*$cant_prod;

                        //$sql = "UPDATE td_ordenprod SET Cantidad = Cantidad + {$cant} WHERE Folio_Pro = '{$Folio_Pro}' AND Cve_Articulo = '{$cve_art}'";
                        //$rs = mysqli_query($conn, $sql);

                        $sql = "UPDATE td_pedido SET Num_cantidad = {$cant} WHERE Fol_folio = '{$Folio_Pro}' AND Cve_articulo = '{$cve_art}'";
                        $res = mysqli_query($conn, $sql);
                    }

                    /*
                    $sql = "SELECT CodigoCSD FROM c_ubicacion WHERE AreaProduccion = 'S' AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = {$id_almacen}) LIMIT 1";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $ubicacion_prod = $resul['CodigoCSD'];

                    $sql_tracking = $this->importarRL($id_almacen, $id_proveedor, 1, 0, $articulos, $lp, $ubicacion_prod, $Folio_Pro);
                    */
                }
                else
                {
                    $linea++;
                    continue;
                }
            }


            //Crear pedido para manufactura
            if($Folio_Anterior != $Folio_Pro)
            {
                $cve_almac_traslado = ""; $TipoPedido = 'T';
                if($tipo_traslado_input == 1)
                {
                  $cve_almac_traslado = $id_almacen;
                  $id_almacen = $almacen_dest;
                  $TipoPedido = $traslado_interno_externo_input;

                  //$sql = "SELECT clave FROM c_almacenp WHERE id = $id_almacen";
                  //$rs = mysqli_query($conn, $sql);
                  //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                  //$almacen_val_clave = $resul['clave'];

                  //$sql = "SELECT Cve_Almac FROM t_ordenprod WHERE Folio_Pro = '$Folio_Pro'";
                  //$rs = mysqli_query($conn, $sql);
                  //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                  //$cve_almac_traslado = $resul['Cve_Almac'];

                  //$sql = "SELECT id from c_almacenp where id = (SELECT cve_almacenp FROM c_almacen where cve_almac = ".$_POST['cboZonaAlmacenImport'].") ;";
                  //$query = mysqli_query($conn, $sql);
                  //if($query->num_rows > 0)
                  //$cve_almac_traslado = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]["id"];
                }

                $fecha_compromiso = $this->pSQL($row[self::FECHA_COMPROMISO]);
                if($fecha_compromiso)
                {//1
                    $fc = explode('-', $fecha_compromiso);
                    $fecha_compromiso = $fc[2]."-".$fc[1]."-".$fc[0];
                }//1

                $data = array(
                    'Fol_folio' => $Folio_Pro,
                    'Fec_Pedido' => date('Y-m-d H:m:s'),
                    'Cve_clte' => '',
                    'status' => 'A',
                    'Fec_Entrega' => $fecha_compromiso,
                    'cve_Vendedor' => "",
                    'Fec_Entrada' => date('Y-m-d H:m:s'),
                    'Pick_Num' => $ot_cliente,
                    'destinatario' => 0,
                    'Cve_Usuario' => $_SESSION['id_user'],
                    'Observaciones' => "",
                    'ID_Tipoprioridad' => 0,
                    'cve_almac' => $id_almacen,
                    'statusaurora_traslado' => $cve_almac_traslado,
                    'TipoPedido' => $TipoPedido,
                    'arrDetalle' => $articulos
                );
                $nuevo_pedido->save($data);
                $Folio_Anterior = $Folio_Pro;
                $folios_creados[] = $Folio_Pro;
                $registros++;
            }


                $lp = $this->pSQL($row[self::LP]);
                $sql_tracking = "NO entró a IF";

                if($existe && $lp && $tipo_traslado_input != 1)
                {
                    $sql_tracking = "SI entró a IF";

                    $sql = "UPDATE t_ordenprod SET Tipo = 'IMP_LP', Status = 'I' WHERE Folio_Pro = '{$Folio_Pro}'";
                    $rs = mysqli_query($conn, $sql);


                    $sql = "SELECT CodigoCSD, idy_ubica FROM c_ubicacion WHERE AreaProduccion = 'S' AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = {$id_almacen}) LIMIT 1";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);


                    $ubicacion_prod = $resul['CodigoCSD'];
                    $idy_ubica = $resul['idy_ubica'];
                    $articulos_ot = array();
                    $cve_articulo_comp = $this->pSQL($row[self::CVE_ART_COMPUESTO]);
                    $cve_lote_comp = $this->pSQL($row[self::LOTE]);
                    //$num_cantidad_comp = $this->pSQL($row[self::CANTIDAD_A_PRODUCIR]);
                    array_push($articulos_ot,array(
                        "Cve_articulo" => $this->pSQL($row[self::CVE_ART_COMPUESTO]),
                        "Cve_Lote" => $this->pSQL($row[self::LOTE]),
                        "Num_cantidad" => $this->pSQL($row[self::CANTIDAD_A_PRODUCIR])
                    ));

                    $sql_tracking = $this->importarRL($id_almacen, $id_proveedor, 1, 0, $articulos_ot, $lp, $ubicacion_prod, $Folio_Pro);

                    //if($realizar_produccion == 0)
                    //{
                    $sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) (SELECT $id_almacen, $idy_ubica, cve_articulo, '$cve_lote_comp', 0, ntarima, 0, 0, 1, (SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '$Folio_Pro'), 0 FROM t_tarima WHERE fol_folio = '$Folio_Pro')";
                    $rs = mysqli_query($conn, $sql);
                    //}

                    //$registros++;
                    
                }
                //else
                    //$registros++;


            }
            $linea++;
        }
        
        @unlink($file);

        //*****************************************************************************************************************
        //PROCESO PARA SEPARAR LOS PEDIDOS DE ACUERDO A LAS ETAPAS QUE COMPRENDAN LOS COMPONENTES
        //*****************************************************************************************************************
        $folios_etapas = $folios_creados;

            foreach($folios_etapas as $fol_etapa)
            {
                $sql = "SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '{$fol_etapa}'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $Cve_Articulo_etapa = $resul['Cve_Articulo'];

                $sql = "SELECT MAX(IFNULL(Etapa, 0)) AS etapas FROM t_artcompuesto WHERE Cve_ArtComponente = '{$Cve_Articulo_etapa}'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $etapas = $resul['etapas'];

                if($etapas > 0)
                {
                    for($et = 0; $et < $etapas; $et++)
                    {
                        if($et == 0)
                        {
                            $sql = "UPDATE th_pedido SET orden_etapa = 1 WHERE Fol_folio = '$fol_etapa'";
                            $rs = mysqli_query(\db2(), $sql);
                        }
                        else
                        {
                            $sql = "SELECT `fct_consecutivo_documentos`('t_ordenprod', 6);";
                            $rs = mysqli_query(\db2(), $sql);
                            $row_folio_pro = mysqli_fetch_array($rs);
                            $Folio_Pro = "OT".$row_folio_pro[0];
                            $sql = "INSERT INTO th_pedido (Fol_folio, Fec_Pedido, Cve_clte, status, Fec_Entrega, cve_Vendedor, Num_Meses, Observaciones, statusaurora, ID_Tipoprioridad, Fec_Entrada, TipoPedido, ruta, bloqueado, DiaO, TipoDoc, rango_hora, cve_almac, destinatario, Id_Proveedor, cve_ubicacion, Pick_Num, Cve_Usuario, Ship_Num, BanEmpaque, Cve_CteProv, Activo, orden_etapa) (SELECT '$Folio_Pro', Fec_Pedido, Cve_clte, status, Fec_Entrega, cve_Vendedor, Num_Meses, Observaciones, statusaurora, ID_Tipoprioridad, Fec_Entrada, TipoPedido, ruta, bloqueado, DiaO, TipoDoc, rango_hora, cve_almac, destinatario, Id_Proveedor, cve_ubicacion, Pick_Num, Cve_Usuario, Ship_Num, BanEmpaque, Cve_CteProv, Activo, ($et+1) FROM th_pedido WHERE Fol_folio = '$fol_etapa')";
                            $rs = mysqli_query(\db2(), $sql);

                            $sql = "UPDATE td_pedido SET Fol_folio = '$Folio_Pro' WHERE Fol_folio = '$fol_etapa' AND Cve_articulo IN (SELECT Cve_Articulo FROM t_artcompuesto WHERE Cve_ArtComponente = '{$Cve_Articulo_etapa}' AND Etapa = ($et+1))";
                            $rs = mysqli_query(\db2(), $sql);

                            $sql = "INSERT INTO t_ordenprod (Folio_Pro, cve_almac, ID_Proveedor, Cve_Articulo, Cve_Lote, Cantidad, Cant_Prod, Cve_Usuario, Fecha, FechaReg, id_umed, Status, Referencia, id_zona_almac, idy_ubica) (SELECT '$Folio_Pro', cve_almac, ID_Proveedor, Cve_Articulo, Cve_Lote, Cantidad, Cant_Prod, Cve_Usuario, Fecha, FechaReg, id_umed, Status, Referencia, id_zona_almac, idy_ubica FROM t_ordenprod WHERE Folio_Pro = '$fol_etapa')";
                            $rs = mysqli_query(\db2(), $sql);

                            $sql = "UPDATE td_ordenprod SET Folio_Pro = '$Folio_Pro' WHERE Folio_Pro = '$fol_etapa' AND Cve_Articulo IN (SELECT Cve_Articulo FROM t_artcompuesto WHERE Cve_ArtComponente = '{$Cve_Articulo_etapa}' AND Etapa = ($et+1))";
                            $rs = mysqli_query(\db2(), $sql);

                        }

                    }
                }
            }
        //*****************************************************************************************************************/
        //*****************************************************************************************************************


        $folios_sin_stock = array();
        $cod_art_compuesto = "";
        $cantidad_art_compuesto = "";
        $cve_almacen = "";
        $folios_completados = 0;

//************************************************************************************************************************************************
//******************************************PROCESO PARA FOAM SOLO PARA GENERAR TRANSFORMACIONES CON LP*******************************************
//************************************************************************************************************************************************
    if($realizar_produccion == 1 && $registros > 0)
    {
            for($f = 0; $f < count($folios_creados); $f++)
            {
                $foliof = $folios_creados[$f];
                $folios_creados[$f] = "\"".$foliof."\"";
            }
            $folios_a_implementar = implode(",", $folios_creados);
            //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql = "SELECT op.orden_id, op.cod_art_compuesto, op.clave, op.control_lotes, op.Lote, op.LoteOT, op.Caduca, op.Caducidad, op.control_peso, op.Cantidad, op.Cant_Prod as Cant_OT, op.Num_cantidad AS cantnecesaria, op.Cantidad_Producida, op.ubicacion, IFNULL(op.existencia, 0) AS existencia, op.um, op.mav_cveunimed, op.clave_almacen, op.Cve_Contenedor, op.CveLP, IF(op.Num_cantidad > IFNULL(op.existencia, 0), 1, 0) AS acepto FROM (
                SELECT DISTINCT 
                    t.Folio_Pro AS orden_id,
                    t.Cve_Articulo AS cod_art_compuesto,
                    a.cve_articulo AS clave,
                    IFNULL(a.control_lotes, 'N') AS control_lotes,
                    e.cve_lote AS Lote,
                    t.Cve_Lote AS LoteOT,
                    IFNULL(a.control_peso, 'N') AS control_peso,
                    IFNULL(a.Caduca, 'N') AS Caduca,
                    IF(a.Caduca = 'S', l.Caducidad, '') AS Caducidad,
                    IF(IFNULL(th.Fol_folio, '') = '', ac.Cantidad, ac.Cantidad/(SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro IN ($folios_a_implementar))) AS Cantidad,
                    (ac.Cantidad_Producida*ac.Cantidad) AS Cantidad_Producida,
                    t.Cant_Prod,
                    p.Num_cantidad,

                    t.idy_ubica as ubicacion,

                    e.Cve_Contenedor,
                    #IFNULL(ch.Clave_Contenedor, '') AS Cve_Contenedor,
                    #'' AS CveLP,
                    IFNULL(ch.CveLP, '') AS CveLP,
                    IFNULL(e.Existencia, 0) AS existencia, 
                    u.cve_umed AS um,
                    u.mav_cveunimed,
                    alm.clave AS clave_almacen,
                    t.Cve_Usuario AS cve_usuario,
                    IFNULL(t.ID_Proveedor, 0) AS ID_Proveedor
                FROM t_artcompuesto ac
                    LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                    LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro
                    LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = t.Folio_Pro
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima 
                    LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = td.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = td.Folio_Pro) #AND IFNULL(e.cve_lote, '') = IFNULL(td.cve_lote, '')
                    LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_Articulo AND l.LOTE = e.cve_lote
                    LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
                    #LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                    LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                    LEFT JOIN td_pedido p ON p.Fol_folio = td.Folio_Pro AND p.Cve_articulo = td.Cve_Articulo
                    LEFT JOIN c_almacenp alm ON alm.id = e.cve_almac
                WHERE t.Folio_Pro IN ($folios_a_implementar) AND e.cve_almac = '{$id_almacen}' 
                AND ac.Cve_Articulo = e.cve_articulo
                AND e.cve_ubicacion = '{$idy_ubica_ot}' 
                AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = td.Cve_Articulo #AND ch.CveLP = '{$lp_read}'
                #AND t.idy_ubica = '{$idy_ubica_ot}'
                #AND IFNULL(e.Cve_Contenedor, '') != ''

                UNION 

                        SELECT DISTINCT 
                            t.Folio_Pro AS orden_id,
                            t.Cve_Articulo AS cod_art_compuesto,
                            a.cve_articulo AS clave,
                            IFNULL(a.control_lotes, 'N') AS control_lotes,
                            e.cve_lote AS Lote,
                            t.Cve_Lote AS LoteOT,
                            IFNULL(a.control_peso, 'N') AS control_peso,
                            IFNULL(a.Caduca, 'N') AS Caduca,
                            IF(a.Caduca = 'S', l.Caducidad, '') AS Caducidad,
                            IF(IFNULL(th.Fol_folio, '') = '', ac.Cantidad, ac.Cantidad/(SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro IN ($folios_a_implementar))) AS Cantidad,
                            (ac.Cantidad_Producida*ac.Cantidad) AS Cantidad_Producida,
                            t.Cant_Prod,
                            ac.Cantidad*t.Cantidad AS Num_cantidad,

                            #(SELECT cu.idy_ubica FROM V_ExistenciaProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND vp.cve_articulo = a.cve_articulo AND vp.cve_almac = '39' AND cu.Activo = 1 AND cu.AreaProduccion = 'S' AND vp.cve_ubicacion = t.idy_ubica GROUP BY vp.Existencia LIMIT 1) AS ubicacion,

                            t.idy_ubica AS ubicacion,

                            #tt.cantidad AS existencia 
                            e.Cve_Contenedor,
                            #IFNULL(ch.Clave_Contenedor, '') AS Cve_Contenedor,
                            #'' AS CveLP,
                            IFNULL(ch.CveLP, '') AS CveLP,
                            IFNULL(e.Existencia, 0) AS existencia, 
                            u.cve_umed AS um,
                            u.mav_cveunimed,
                            alm.clave AS clave_almacen,
                            t.Cve_Usuario AS cve_usuario,
                            IFNULL(t.ID_Proveedor, 0) AS ID_Proveedor
                        FROM t_artcompuesto ac
                            LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                            LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
                    LEFT JOIN t_tarima tt ON tt.Fol_Folio = t.Folio_Pro
                    LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima 
                            LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = ac.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = t.Folio_Pro) #AND IFNULL(e.cve_lote, '') = IFNULL(td.cve_lote, '')
                            LEFT JOIN c_lotes l ON l.cve_articulo = ac.Cve_Articulo AND l.LOTE = e.cve_lote
                            LEFT JOIN c_articulo a ON a.cve_articulo = ac.Cve_Articulo
                            #LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                            LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                            LEFT JOIN td_pedido p ON p.Fol_folio = t.Folio_Pro AND p.Cve_articulo = ac.Cve_Articulo
                            LEFT JOIN c_almacenp alm ON alm.id = e.cve_almac
                        WHERE t.Folio_Pro IN ($folios_a_implementar) AND e.cve_almac = '{$id_almacen}' 
                        AND ac.Cve_Articulo = e.cve_articulo
                        AND e.cve_ubicacion = '{$idy_ubica_ot}' 
                        AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = a.Cve_Articulo #AND ch.CveLP = ''
                        AND IFNULL(a.tipo_producto, '') = 'ProductoNoSurtible'

                UNION 

            SELECT 
                   td.Folio_Pro AS orden_id,
                   '' AS cod_art_compuesto,
                   Cve_Articulo AS clave, 
                   '' AS control_lotes,
                   Cve_Lote AS Lote, 
                   '' AS LoteOT,
                   '' AS control_peso,
                   '' AS Caduca,
                   '' AS Caducidad,
                   0  AS Cantidad, 
                   0 AS Cantidad_Producida,
                   0 as Cant_Prod,
                   0 AS Num_cantidad,
                   '' AS ubicacion,
                   '' AS Cve_Contenedor,
                   '' AS CveLP,
                   0 AS existencia, 
                   '' AS um,
                   '' AS mav_cveunimed,
                   '' AS clave_almacen,
                   '' AS cve_usuario,
                   0 AS ID_Proveedor
             FROM td_ordenprod td
             WHERE Folio_Pro IN ($folios_a_implementar) 
             #AND CONCAT(Cve_Articulo, Cve_Lote) NOT IN (SELECT CONCAT(Cve_Articulo, cve_lote) FROM V_ExistenciaProduccion WHERE cve_almac = '{$id_almacen}')
             AND Cve_Articulo NOT IN (SELECT Cve_Articulo FROM V_ExistenciaProduccion WHERE cve_almac = '{$id_almacen}' AND Existencia > 0 AND cve_ubicacion = '{$idy_ubica_ot}')
                ORDER BY Caducidad
            ) AS op WHERE op.ubicacion IS NOT NULL AND op.Num_cantidad <= IFNULL(op.existencia, 0) 
            ";

        $res_art = "";
        $sql_art = $sql;
        $sql_check .= ";3;".$sql."\n\n;\n";
        $sql_acepto = "SELECT SUM(acpt.acepto) as acepto FROM (SELECT acepto.clave, acepto.Lote, SUM(acepto.cantnecesaria) AS cantnecesaria, acepto.existencia, IF(SUM(acepto.cantnecesaria) > IFNULL(acepto.existencia, 0), 1, 0) AS acepto FROM ( ".$sql." ) AS acepto GROUP BY clave, Lote) as acpt ";

        if (!($res_art = mysqli_query($conn, $sql_acepto)))
            echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";

        $acepto = true;

        $row_art = mysqli_fetch_array($res_art);
        if($row_art['acepto'] > 0)
            $acepto = false;

        $i_num_rows = 0;
        if($acepto)
        {//acepto
/*
            $sql = "UPDATE t_artcompuesto SET Cantidad_Producida = IFNULL(Cantidad_Producida, 0)+$cantidad_art_compuesto WHERE Cve_ArtComponente = '$cod_art_compuesto'";
            if (!($res = mysqli_query($conn, $sql))) {
                echo "Falló la preparación J: (" . mysqli_error($conn) . ") ";
            }
*/

            $sql_check .= ";04;".$sql."\n\n;\n";
            $sql_check .= ";4 sql_art = ;".$sql_art."\n\n;\n";
            if (!($res_art = mysqli_query($conn, $sql_art)))
                echo "Falló la preparación H: (" . mysqli_error($conn) . ") ";

            $listo = false;
            $LoteOT = "";
            $mensaje_error = "";
            $caducidad  = ""; $last_idy_ubica = "";
            while($row_art_1 = mysqli_fetch_array($res_art))
            {
                //if($Tipo_OT == 'IMP_LP') $cantidad_art_compuesto = $row_art_1['existencia'];
                //$cantidad = $row_art_1['Cantidad']*$row_art_1['Cant_OT'];//$cantidad_art_compuesto;
                $cod_art_compuesto = $row_art_1['cod_art_compuesto'];
                $orden_id = $row_art_1['orden_id'];
                $cantidad = $row_art_1['cantnecesaria'];
                $idy_ubica = $row_art_1['ubicacion'];
                $clave = $row_art_1['clave'];
                $Lote = $row_art_1['Lote'];
                $LoteOT = $row_art_1['LoteOT'];
                $caducidadMIN = $row_art_1['Caducidad'];
                $Caduca = $row_art_1['Caduca'];
                $Cve_Contenedor = $row_art_1['Cve_Contenedor'];
                $lp_read = $row_art_1['CveLP'];
                $ID_Proveedor = $row_art_1['ID_Proveedor'];
                $existencia = $row_art_1['existencia'];
                $um = $row_art_1['um'];
                $mav_cveunimed = $row_art_1['mav_cveunimed'];
                $clave_almacen = $row_art_1['clave_almacen'];
                $cve_usuario = $row_art_1['cve_usuario'];
                $control_peso = $row_art_1['control_peso'];

                if($idy_ubica == '') continue;
                if($LoteOT == '') $LoteOT = $orden_id;

                //if($idy_ubica == "") $mensaje_error .= "El Producto {$clave} no se encuentra en una ubicación de Producción\n";
                if($Caduca == 'S' && $caducidadMIN != '' && $caducidadMIN != '0000-00-00' && $listo == false)
                {
                    $caducidad = $caducidadMIN;
                    $listo = true;
                }

                if($control_peso == 'N' && $mav_cveunimed == 'H87') $cantidad = ceil($cantidad);
                $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and cve_lote = '$Lote'";

                $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$clave}', '{$Lote}', NOW(), '{$idy_ubica}', '{$orden_id}', {$cantidad}, 8, '{$cve_usuario}','{$id_almacen}')";
                $res_kardex = mysqli_query($conn, $sql_kardex);


                if($Cve_Contenedor != '')
                {
                    $sql = "UPDATE ts_existenciatarima SET existencia = existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and lote = '$Lote' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')";

                    $sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES 
                    ((SELECT MAX(id) FROM t_cardex), '$clave_almacen', (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor'), CURDATE(), '{$idy_ubica}', '{$orden_id}', 8, '{$cve_usuario}', 'I')";
                    $res_kardex = mysqli_query($conn, $sql_kardex);
                }

                $last_idy_ubica = $idy_ubica;

                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación G: (" . mysqli_error($conn) . ") ";
                }
                //$Lote = "";

                if($ejecutar_infinity)
                {
                $sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = '{$clave}' and cve_almac = '$id_almacen' AND tipo = 'ubicacion'";
                $query = mysqli_query($conn, $sql);
                $row_ord = mysqli_fetch_array($query);
                $existencia_art_prod = $row_ord['existencia_art_prod'];

                if(!$existencia_art_prod) $existencia_art_prod = 0;

                $this->Ejecutar_Infinity_WS($clave, $Lote, $existencia_art_prod, $um, $clave_almacen, $ejecutar_infinity, $Url_inf, $url_curl, $Servicio_inf, $User_inf, $Pswd_inf, $Empresa_inf, $hora_movimiento, $Codificado);

                $sql_check .= ";5;".$sql."\n\n;\n";
                }
            //}//llave del while para ampliarlo (aqui se elimina para ampliar abajo)
            $idy_ubica = $last_idy_ubica;


            //xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
                    $sql = "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND lote = '$LoteOT' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read')";

                if (!($res = mysqli_query($conn, $sql)))
                    echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
                $existe_producto = mysqli_fetch_array($res)['existe'];

                $sql_check .= ";6;".$sql."\n\n;\n";

                if($existe_producto == 0)
                {
                    $sql = "SELECT DISTINCT control_lotes, Caduca FROM c_articulo WHERE cve_articulo = '$cod_art_compuesto'";
                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación E: (" . mysqli_error($conn) . ") ";
                    $row_control = mysqli_fetch_array($res);
                    $control_lotes = $row_control['control_lotes'];
                    $Caduca = $row_control['Caduca'];

                    $sql_check .= ";9;".$sql."\n\n;\n";

                    if($LoteOT)
                    {
                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', '{$caducidad}')";

                        if($Caduca != 'S') // Si Caduca = N, se registrará una fecha de elaboración
                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', CURDATE())";

                        if (!($res = mysqli_query($conn, $sql)))
                            echo "Falló la preparación B: (" . mysqli_error($conn) . ") ". $sql;
                    }

                    $sql_check .= ";10;".$sql."\n\n;\n";
                    //$sql = "SELECT DISTINCT cve_almac FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica'";
                    $sql = "SELECT DISTINCT cve_almac FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = '$idy_ubica'";
                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación A: (" . mysqli_error($conn) . ") ";
                    $cve_almacen = mysqli_fetch_array($res)['cve_almac'];

                   $sql = "UPDATE t_ordenprod SET Cve_Lote = '{$LoteOT}' WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación jj: (" . mysqli_error($conn) . ") ";
                    }

                    $sql_check .= ";11;".$sql."\n\n;\n";

                    if(!$ID_Proveedor) $ID_Proveedor = 0;
                    $sql_check .= ";11-1 idy_ubica = ;".$idy_ubica."\n\n;\n";
                }
                else
                {
                        $sql = "UPDATE ts_existenciatarima SET Existencia = Existencia + $cantidad_art_compuesto WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND lote = '$LoteOT' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read')";
                        $sql_check .= ";7;".$sql."\n\n;\n";
                        if (!($res = mysqli_query($conn, $sql))) {
                            echo "Falló la preparación jj: (" . mysqli_error($conn) . ") ";
                        }

                }
                    if(!$ID_Proveedor) $ID_Proveedor = 0;
                    if($LoteOT == "") $LoteOT = $orden_id;

                if($idy_ubica)
                {
                    $sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) (SELECT '{$id_almacen}', {$idy_ubica}, cve_articulo, '{$LoteOT}', 0, ntarima, 0, cantidad, 1, (SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}'), 0 FROM t_tarima WHERE fol_folio = '{$orden_id}')";
                    $sql_check .= ";12;".$sql."\n\n;\n";

                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación Z: (" . mysqli_error($conn) . ") " . $sql;
                    }

                    //$sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = IFNULL(Cant_Prod, 0)+$cantidad_art_compuesto WHERE Folio_Pro = '$orden_id'";
                    $sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = Cantidad WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación I: (" . mysqli_error($conn) . ") ";
                    }
                        $sql_check .= ";13;".$sql."\n\n;\n";


                    $sql = "SELECT 
                                c_unimed.cve_umed,
                                t.cve_almac as almacen_prod,
                                t.Cve_Articulo AS cve_articulo,
                                t.Cve_Usuario as cve_usuario,
                                t.Cant_Prod
                            FROM t_ordenprod t 
                            LEFT JOIN c_articulo a ON a.cve_articulo = t.Cve_Articulo AND a.Compuesto = 'S'
                            LEFT JOIN c_unimed ON a.unidadMedida = c_unimed.id_umed
                            WHERE a.cve_articulo = t.Cve_Articulo AND t.Folio_Pro = '{$orden_id}'";
                    $query = mysqli_query($conn, $sql);
                    $row_ord = mysqli_fetch_array($query);
                    $cve_umed = $row_ord['cve_umed'];
                    $almacen_prod = $row_ord['almacen_prod'];
                    $cve_articulo_ord = $row_ord['cve_articulo'];
                    $Cant_Prod_ord = $row_ord['Cant_Prod'];
                    $cve_usuario = $row_ord['cve_usuario'];


//'{$id_almacen}', {$idy_ubica}, cve_articulo, '{$LoteOT}', 0, ntarima, 0, cantidad, 1, 0

//(SELECT cve_articulo, cve_lote, NOW(), 'PT_{$orden_id}', '{$idy_ubica}', {$Cant_Prod_ord}, 14, '{$cve_usuario}','{$id_almacen}' FROM t_tarima WHERE fol_folio = '{$orden_id}')

                $sql_tarima = "SELECT ntarima as tarima_kdx, Fol_Folio AS folio_kdx, cve_articulo AS cve_articulo_kdx, lote AS cve_lote_kdx, cantidad AS cantidad_kdx FROM t_tarima WHERE Fol_Folio = '{$orden_id}'";
                $res_tarima = mysqli_query($conn, $sql_tarima);
                while($row_tarima = mysqli_fetch_array($res_tarima))
                {
                    extract($row_tarima);
                    $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('$cve_articulo_kdx', '$cve_lote_kdx', NOW(), 'PT_{$folio_kdx}', '$idy_ubica', '$cantidad_kdx', 14, '$cve_usuario', '$id_almacen')";
                    $res_kardex = mysqli_query($conn, $sql_kardex);

                    $sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES 
                    ((SELECT MAX(id) FROM t_cardex), '$clave_almacen', {$tarima_kdx}, CURDATE(),'PT_{$folio_kdx}', '{$idy_ubica}', 14, '{$cve_usuario}', 'I')";
                    $res_kardex = mysqli_query($conn, $sql_kardex);
                }


              //*******************************************************************************
              //                          EJECUTAR EN INFINITY
              //*******************************************************************************
              if($ejecutar_infinity)
              {
                    $sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = '{$cve_articulo_ord}' and cve_almac = '$almacen_prod' AND tipo = 'ubicacion'";
                    $query = mysqli_query($conn, $sql);
                    $row_ord_inf = mysqli_fetch_array($query);
                    $existencia_art_prod = $row_ord_inf['existencia_art_prod'];
                    if(!$existencia_art_prod) $existencia_art_prod = 0;

                    $sql = "SELECT Url, Servicio, User, Pswd, Empresa, to_base64(CONCAT(User,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1";
                    $query = mysqli_query($conn, $sql);
                    $row_infinity = mysqli_fetch_array($query);
                    $Url_inf = $row_infinity['Url'];
                    $url_curl = $row_infinity['url_curl'];
                    $Servicio_inf = $row_infinity['Servicio'];
                    $User_inf = $row_infinity['User'];
                    $Pswd_inf = $row_infinity['Pswd'];
                    $Empresa_inf = $row_infinity['Empresa'];
                    $hora_movimiento = $row_infinity['hora_movimiento'];
                    $Codificado = $row_infinity['Codificado'];

                      $json = "[";
                      //$row = mysqli_fetch_array($query);
                      //echo $sql;
                        extract($row_ord_inf);
                        //if($this->pSQL($row[self::LOTE]) == "") 
                            $LoteOT = "";
                        $json .= "{";
                        $json .= '"item":"'.$cve_articulo_ord.'","um":"'.$cve_umed.'","batch":"'.$LoteOT.'", "qty": '.$existencia_art_prod.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                        $json .= "}";
                      //$json[strlen($json)-1] = ' ';
                      $json .= "]";

                    
                  $curl = curl_init();

                  //$url_curl = $Url_inf.':8080/'.$Servicio_inf;
                  curl_setopt_array($curl, array(
                    // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                    CURLOPT_URL => "$url_curl",
                    //CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>
                    // Aquí cambia la cadena JSON
                    $json,
                    CURLOPT_HTTPHEADER => array(
                      'Content-Type: application/json',
                      //'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
                      'Authorization: Basic '.$Codificado.''
                    )
                    ,CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                  ));

                  $response = curl_exec($curl);
                  $response_ot .= $response."\n";

                  curl_close($curl);      
                  //echo $response;
                  
                  //$response = 'Pendiente';
                  $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', 'Transformacion', 'WEB')";
                  $query = mysqli_query($conn, $sql);

              }
              //*******************************************************************************/
              //*******************************************************************************



                }
            //xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

                //$folios_completados++;
                //$porcentaje = (($folios_completados/count($folios_creados))*100)."%";
                //echo $porcentaje;
                //header('Content-Length: ' . $porcentaje);
                /*
                $this->response(250, [
                'porcentaje' => $porcentaje
                ]);
                */

            }//llave del while para ampliarlo
            }//acepto

    }
//************************************************************************************************************************************************
//************************************************************************************************************************************************
//************************************************************************************************************************************************

        $pasar = false;
        if($realizar_produccion == 1 && $registros > 0 && $pasar == true)
        {
            //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            foreach($folios_creados as $orden_id)
            {
                $sql = "SELECT Cve_Articulo, Cantidad, Tipo FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}'";
                if (!($res_art = mysqli_query($conn, $sql)))
                {
                    echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";
                    //$sql_check = "Falló la preparación K: (" . mysqli_error($conn) . ") ";
                }
                $row_comp = mysqli_fetch_array($res_art);
                $cod_art_compuesto      = $row_comp["Cve_Articulo"];
                $Tipo_OT                = $row_comp["Tipo"];
                $cantidad_art_compuesto = $row_comp["Cantidad"];
                ///Cve_Articulo  = $this->pSQL($row[self::CVE_ART_COMPUESTO]);
                ///Cantidad      = $this->pSQL($row[self::CANTIDAD_A_PRODUCIR]);

                //$lp_read = "";
                //if($Tipo_OT == 'IMP_LP')
                //$lp_read = $cod_art_compuesto;
                //$sql_check = $Tipo_OT;
                $sql_check .= ";1;".$sql."\n\n;\n";

                if($Tipo_OT == 'IMP_LP')
                {
//*****************************************************************************************************************

                    $sql = "SELECT DISTINCT ch.CveLP, t.cantidad
                            FROM t_tarima t 
                            LEFT JOIN c_charolas ch ON ch.IDContenedor = t.ntarima
                            WHERE t.Fol_Folio = '{$orden_id}'";
                if (!($res_art = mysqli_query($conn, $sql)))
                    echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";


                $sql_check .= ";2;".$sql."\n\n;\n";

                while($row_comp = mysqli_fetch_array($res_art))
                {//while
                    $lp_read = $row_comp['CveLP'];
                    $cantidad_art_compuesto = $row_comp["cantidad"];
        $sql = "SELECT op.clave, op.control_lotes, op.Lote, op.LoteOT, op.Caduca, op.Caducidad, op.control_peso, op.Cantidad, op.Cant_Prod as Cant_OT, op.Num_cantidad AS cantnecesaria, op.Cantidad_Producida, op.ubicacion, IFNULL(op.existencia, 0) AS existencia, op.um, op.mav_cveunimed, op.clave_almacen, op.Cve_Contenedor, IF(op.Num_cantidad > IFNULL(op.existencia, 0), 1, 0) AS acepto FROM (
                SELECT DISTINCT 
                    a.cve_articulo AS clave,
                    IFNULL(a.control_lotes, 'N') AS control_lotes,
                    e.cve_lote AS Lote,
                    t.Cve_Lote AS LoteOT,
                    IFNULL(a.control_peso, 'N') AS control_peso,
                    IFNULL(a.Caduca, 'N') AS Caduca,
                    IF(a.Caduca = 'S', l.Caducidad, '') AS Caducidad,
                    IF(IFNULL(th.Fol_folio, '') = '', ac.Cantidad, ac.Cantidad/(SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}')) AS Cantidad,
                    (ac.Cantidad_Producida*ac.Cantidad) AS Cantidad_Producida,
                    t.Cant_Prod,
                    p.Num_cantidad,

                    #(SELECT cu.idy_ubica FROM V_ExistenciaProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND vp.cve_articulo = a.cve_articulo AND vp.cve_almac = '{$id_almacen}' AND cu.Activo = 1 AND cu.AreaProduccion = 'S' AND vp.cve_ubicacion = t.idy_ubica GROUP BY vp.Existencia LIMIT 1) AS ubicacion,

                    t.idy_ubica as ubicacion,

                    #tt.cantidad AS existencia 
                    #e.Cve_Contenedor,
                    ch.Clave_Contenedor AS Cve_Contenedor,
                    IFNULL(e.Existencia, 0) AS existencia, 
                    u.cve_umed AS um,
                    u.mav_cveunimed,
                    alm.clave AS clave_almacen,
                    t.Cve_Usuario AS cve_usuario,
                    IFNULL(t.ID_Proveedor, 0) AS ID_Proveedor
                FROM t_artcompuesto ac
                    LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                    LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro
                    LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = t.Folio_Pro
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima 
                    LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = td.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = td.Folio_Pro) #AND IFNULL(e.cve_lote, '') = IFNULL(td.cve_lote, '')
                    LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_Articulo AND l.LOTE = e.cve_lote
                    LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
                    #LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                    LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                    LEFT JOIN td_pedido p ON p.Fol_folio = td.Folio_Pro AND p.Cve_articulo = td.Cve_Articulo
                    LEFT JOIN c_almacenp alm ON alm.id = e.cve_almac
                WHERE t.Folio_Pro = '{$orden_id}' AND e.cve_almac = '{$id_almacen}' 
                AND ac.Cve_Articulo = e.cve_articulo
                AND e.cve_ubicacion = '{$idy_ubica_ot}' 
                AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = td.Cve_Articulo #AND ch.CveLP = '{$lp_read}'
                #AND t.idy_ubica = '{$idy_ubica_ot}'
                #AND IFNULL(e.Cve_Contenedor, '') != ''

                UNION 

                        SELECT DISTINCT 
                            a.cve_articulo AS clave,
                            IFNULL(a.control_lotes, 'N') AS control_lotes,
                            e.cve_lote AS Lote,
                            t.Cve_Lote AS LoteOT,
                            IFNULL(a.control_peso, 'N') AS control_peso,
                            IFNULL(a.Caduca, 'N') AS Caduca,
                            IF(a.Caduca = 'S', l.Caducidad, '') AS Caducidad,
                            IF(IFNULL(th.Fol_folio, '') = '', ac.Cantidad, ac.Cantidad/(SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}')) AS Cantidad,
                            (ac.Cantidad_Producida*ac.Cantidad) AS Cantidad_Producida,
                            t.Cant_Prod,
                            ac.Cantidad*t.Cantidad AS Num_cantidad,

                            #(SELECT cu.idy_ubica FROM V_ExistenciaProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND vp.cve_articulo = a.cve_articulo AND vp.cve_almac = '39' AND cu.Activo = 1 AND cu.AreaProduccion = 'S' AND vp.cve_ubicacion = t.idy_ubica GROUP BY vp.Existencia LIMIT 1) AS ubicacion,

                            t.idy_ubica AS ubicacion,

                            #tt.cantidad AS existencia 
                            #e.Cve_Contenedor,
                            ch.Clave_Contenedor AS Cve_Contenedor,
                            IFNULL(e.Existencia, 0) AS existencia, 
                            u.cve_umed AS um,
                            u.mav_cveunimed,
                            alm.clave AS clave_almacen,
                            t.Cve_Usuario AS cve_usuario,
                            IFNULL(t.ID_Proveedor, 0) AS ID_Proveedor
                        FROM t_artcompuesto ac
                            LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                            LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
                    LEFT JOIN t_tarima tt ON tt.Fol_Folio = t.Folio_Pro
                    LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima 
                            LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = ac.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = t.Folio_Pro) #AND IFNULL(e.cve_lote, '') = IFNULL(td.cve_lote, '')
                            LEFT JOIN c_lotes l ON l.cve_articulo = ac.Cve_Articulo AND l.LOTE = e.cve_lote
                            LEFT JOIN c_articulo a ON a.cve_articulo = ac.Cve_Articulo
                            #LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                            LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                            LEFT JOIN td_pedido p ON p.Fol_folio = t.Folio_Pro AND p.Cve_articulo = ac.Cve_Articulo
                            LEFT JOIN c_almacenp alm ON alm.id = e.cve_almac
                        WHERE t.Folio_Pro = '{$orden_id}' AND e.cve_almac = '{$id_almacen}' 
                        AND ac.Cve_Articulo = e.cve_articulo
                        AND e.cve_ubicacion = '{$idy_ubica_ot}' 
                        AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = a.Cve_Articulo #AND ch.CveLP = ''
                        AND IFNULL(a.tipo_producto, '') = 'ProductoNoSurtible'

                UNION 

            SELECT Cve_Articulo AS clave, 
                   '' AS control_lotes,
                   Cve_Lote AS Lote, 
                   '' AS LoteOT,
                   '' AS control_peso,
                   '' AS Caduca,
                   '' AS Caducidad,
                   0  AS Cantidad, 
                   0 AS Cantidad_Producida,
                   0 as Cant_Prod,
                   0 AS Num_cantidad,
                   '' AS ubicacion,
                   '' AS Cve_Contenedor,
                   0 AS existencia, 
                   '' AS um,
                   '' AS mav_cveunimed,
                   '' AS clave_almacen,
                   '' AS cve_usuario,
                   0 AS ID_Proveedor
             FROM td_ordenprod td
             WHERE Folio_Pro = '{$orden_id}' 
             #AND CONCAT(Cve_Articulo, Cve_Lote) NOT IN (SELECT CONCAT(Cve_Articulo, cve_lote) FROM V_ExistenciaProduccion WHERE cve_almac = '{$id_almacen}')
             AND Cve_Articulo NOT IN (SELECT Cve_Articulo FROM V_ExistenciaProduccion WHERE cve_almac = '{$id_almacen}' AND Existencia > 0 AND cve_ubicacion = '{$idy_ubica_ot}')


                ORDER BY Caducidad
            ) AS op WHERE op.ubicacion IS NOT NULL AND op.Num_cantidad <= IFNULL(op.existencia, 0) 
            GROUP BY clave, ubicacion";

        $res_art = "";
        $sql_art = $sql;
        $sql_check .= ";3;".$sql."\n\n;\n";
        $sql_acepto = "SELECT SUM(acepto.acepto) AS acepto FROM ( ".$sql." ) AS acepto ";

        if (!($res_art = mysqli_query($conn, $sql_acepto)))
            echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";

        $acepto = true;

        $row_art = mysqli_fetch_array($res_art);
        if($row_art['acepto'] > 0)
            $acepto = false;
        //$num_rows = mysqli_num_rows($res_art);

        //$sql_check .= "num_rows = $num_rows"."\n;\n";
        $i_num_rows = 0;
//////////////////////////////////////////////////////////////////////////////////////////
        /*
        while($row_art = mysqli_fetch_array($res_art))
        {
            //+$row_art['Cantidad_Producida'] 
            //&& $row_art['existencia'] > 0
            //if(($row_art['Cantidad']*$row_art['Cant_OT']) > $row_art['existencia'] )
            $sql_check .= ";if(".$row_art['cantnecesaria']." > ".$row_art['existencia'].") (".$row_art['clave'].")"."\n;\n";
            if($row_art['cantnecesaria'] > $row_art['existencia'])
            {
                $acepto = false;
                break;
            }
            $i_num_rows++;

            if($i_num_rows >= $num_rows) break; //por si se queda pegado el while
        }
        */
//////////////////////////////////////////////////////////////////////////////////////////
        if($acepto)
        {//acepto
            $sql = "UPDATE t_artcompuesto SET Cantidad_Producida = IFNULL(Cantidad_Producida, 0)+$cantidad_art_compuesto WHERE Cve_ArtComponente = '$cod_art_compuesto'";
            if (!($res = mysqli_query($conn, $sql))) {
                echo "Falló la preparación J: (" . mysqli_error($conn) . ") ";
            }


            $sql_check .= ";04;".$sql."\n\n;\n";
            $sql_check .= ";4 sql_art = ;".$sql_art."\n\n;\n";
            if (!($res_art = mysqli_query($conn, $sql_art)))
                echo "Falló la preparación H: (" . mysqli_error($conn) . ") ";

            $listo = false;
            $LoteOT = "";
            $mensaje_error = "";
            $caducidad  = ""; $last_idy_ubica = "";
            while($row_art_1 = mysqli_fetch_array($res_art))
            {
                //if($Tipo_OT == 'IMP_LP') $cantidad_art_compuesto = $row_art_1['existencia'];
                //$cantidad = $row_art_1['Cantidad']*$row_art_1['Cant_OT'];//$cantidad_art_compuesto;
                $cantidad = $row_art_1['cantnecesaria'];
                $idy_ubica = $row_art_1['ubicacion'];
                $clave = $row_art_1['clave'];
                $Lote = $row_art_1['Lote'];
                $LoteOT = $row_art_1['LoteOT'];
                $caducidadMIN = $row_art_1['Caducidad'];
                $Caduca = $row_art_1['Caduca'];
                $Cve_Contenedor = $row_art_1['Cve_Contenedor'];
                $ID_Proveedor = $row_art_1['ID_Proveedor'];
                $existencia = $row_art_1['existencia'];
                $um = $row_art_1['um'];
                $mav_cveunimed = $row_art_1['mav_cveunimed'];
                $clave_almacen = $row_art_1['clave_almacen'];
                $cve_usuario = $row_art_1['cve_usuario'];
                $control_peso = $row_art_1['control_peso'];

                if($idy_ubica == '') continue;
                if($LoteOT == '') $LoteOT = $orden_id;

                //if($idy_ubica == "") $mensaje_error .= "El Producto {$clave} no se encuentra en una ubicación de Producción\n";
                if($Caduca == 'S' && $caducidadMIN != '' && $caducidadMIN != '0000-00-00' && $listo == false)
                {
                    $caducidad = $caducidadMIN;
                    $listo = true;
                }

                if($control_peso == 'N' && $mav_cveunimed == 'H87') $cantidad = ceil($cantidad);
                $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and cve_lote = '$Lote'";

                $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$clave}', '{$Lote}', NOW(), '{$idy_ubica}', '{$orden_id}', {$cantidad}, 8, '{$cve_usuario}','{$id_almacen}')";
                $res_kardex = mysqli_query($conn, $sql_kardex);


                if($Cve_Contenedor != '')
                {
                    $sql = "UPDATE ts_existenciatarima SET existencia = existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and lote = '$Lote' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')";

                    $sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES 
                    ((SELECT MAX(id) FROM t_cardex), '$clave_almacen', (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor'), CURDATE(), '{$idy_ubica}', '{$orden_id}', 8, '{$cve_usuario}', 'I')";
                    $res_kardex = mysqli_query($conn, $sql_kardex);
                }
/*
                if($lp_read != '')
                {
                    $sql = "DELETE FROM ts_existenciatarima WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' AND lote = '$Lote' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read')";//UPDATE ts_existenciatarima SET existencia = existencia - $cantidad
                }
*/
                $last_idy_ubica = $idy_ubica;

                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación G: (" . mysqli_error($conn) . ") ";
                }
                //$Lote = "";

                $sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = '{$clave}' and cve_almac = '$id_almacen' AND tipo = 'ubicacion'";
                $query = mysqli_query($conn, $sql);
                $row_ord = mysqli_fetch_array($query);
                $existencia_art_prod = $row_ord['existencia_art_prod'];

                if(!$existencia_art_prod) $existencia_art_prod = 0;

                $this->Ejecutar_Infinity_WS($clave, '', $existencia_art_prod, $um, $clave_almacen, $ejecutar_infinity, $Url_inf, $url_curl, $Servicio_inf, $User_inf, $Pswd_inf, $Empresa_inf, $hora_movimiento, $Codificado);
                $sql_check .= ";5;".$sql."\n\n;\n";
            }
            $idy_ubica = $last_idy_ubica;


            //xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
                    $sql = "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND lote = '$LoteOT' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read')";

                if (!($res = mysqli_query($conn, $sql)))
                    echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
                $existe_producto = mysqli_fetch_array($res)['existe'];

                $sql_check .= ";6;".$sql."\n\n;\n";

                if($existe_producto == 0)
                {
/*
                    $sql = "SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
                    $ID_Proveedor = mysqli_fetch_array($res)['ID_Proveedor'];

                    $sql_check .= ";8;".$sql."\n\n;\n";

*/
                    $sql = "SELECT DISTINCT control_lotes, Caduca FROM c_articulo WHERE cve_articulo = '$cod_art_compuesto'";
                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación E: (" . mysqli_error($conn) . ") ";
                    $row_control = mysqli_fetch_array($res);
                    $control_lotes = $row_control['control_lotes'];
                    $Caduca = $row_control['Caduca'];

                    $sql_check .= ";9;".$sql."\n\n;\n";
/*
                    if($control_lotes != 'S')
                    {
                        $sql = "UPDATE c_articulo SET control_lotes = 'S' WHERE cve_articulo = '$cod_art_compuesto'";
                        if (!($res = mysqli_query($conn, $sql)))
                            echo "Falló la preparación D: (" . mysqli_error($conn) . ") ";
                    }

                    if($Caduca != 'S' && $caducidad != '')
                    {
                        $sql = "UPDATE c_articulo SET Caduca = 'S' WHERE cve_articulo = '$cod_art_compuesto'";
                        if (!($res = mysqli_query($conn, $sql)))
                            echo "Falló la preparación C: (" . mysqli_error($conn) . ") ";
                    }
*/
                        /*
                                $sql = "SELECT DISTINCT (Cantidad_Producida*Cantidad) as Cantidad_Producida FROM t_artcompuesto WHERE Cve_ArtComponente = '$cod_art_compuesto'";
                                
                                if (!($res = mysqli_query($conn, $sql)))
                                    echo "Falló la preparación M: (" . mysqli_error($conn) . ") ";
                                $cantidad_producida = mysqli_fetch_array($res)['Cantidad_Producida'];
                        */
                    if($LoteOT)
                    {
                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', '{$caducidad}')";

                        if($Caduca != 'S') // Si Caduca = N, se registrará una fecha de elaboración
                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', CURDATE())";

                        if (!($res = mysqli_query($conn, $sql)))
                            echo "Falló la preparación B: (" . mysqli_error($conn) . ") ". $sql;
                    }

                    $sql_check .= ";10;".$sql."\n\n;\n";
                    //$sql = "SELECT DISTINCT cve_almac FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica'";
                    $sql = "SELECT DISTINCT cve_almac FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = '$idy_ubica'";
                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación A: (" . mysqli_error($conn) . ") ";
                    $cve_almacen = mysqli_fetch_array($res)['cve_almac'];

                   $sql = "UPDATE t_ordenprod SET Cve_Lote = '{$LoteOT}' WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación jj: (" . mysqli_error($conn) . ") ";
                    }

                    $sql_check .= ";11;".$sql."\n\n;\n";

                    if(!$ID_Proveedor) $ID_Proveedor = 0;
                    $sql_check .= ";11-1 idy_ubica = ;".$idy_ubica."\n\n;\n";
                }
                else
                {
                        $sql = "UPDATE ts_existenciatarima SET Existencia = Existencia + $cantidad_art_compuesto WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND lote = '$LoteOT' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read')";
                        $sql_check .= ";7;".$sql."\n\n;\n";
                        if (!($res = mysqli_query($conn, $sql))) {
                            echo "Falló la preparación jj: (" . mysqli_error($conn) . ") ";
                        }

                }
                    if(!$ID_Proveedor) $ID_Proveedor = 0;
                    if($LoteOT == "") $LoteOT = $orden_id;

                if($idy_ubica)
                {
                    //$sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) VALUES ('{$cve_almacen}', {$idy_ubica}, '{$cod_art_compuesto}', '{$LoteOT}', 0, (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read'), 0, {$cantidad_art_compuesto}, 1, {$ID_Proveedor}, 0)";

                    //$sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) VALUES ('{$id_almacen}', {$idy_ubica}, '{$cod_art_compuesto}', '{$LoteOT}', 0, (SELECT ntarima FROM t_tarima WHERE fol_folio = '$orden_id' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$lp_read}')), 0, {$cantidad_art_compuesto}, 1, {$ID_Proveedor}, 0)";
                    $sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) (SELECT '{$id_almacen}', {$idy_ubica}, cve_articulo, '{$LoteOT}', 0, ntarima, 0, cantidad, 1, (SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}'), 0 FROM t_tarima WHERE fol_folio = '{$orden_id}')";
                    $sql_check .= ";12;".$sql."\n\n;\n";

                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación Z: (" . mysqli_error($conn) . ") " . $sql;
                    }

                    //$sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = IFNULL(Cant_Prod, 0)+$cantidad_art_compuesto WHERE Folio_Pro = '$orden_id'";
                    $sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = Cantidad WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación I: (" . mysqli_error($conn) . ") ";
                    }
                        $sql_check .= ";13;".$sql."\n\n;\n";

    /*
                $sql = "SELECT DISTINCT ROUND((Cantidad_Producida*Cantidad / Cantidad), 0) AS Cantidad_Producida
                        FROM t_artcompuesto
                        WHERE Cve_ArtComponente = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$orden_id')";
    */

                    //$sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = IFNULL(Cant_Prod, 0)+$cantidad_art_compuesto WHERE Folio_Pro = '$orden_id'";
                    //if (!($res = mysqli_query($conn, $sql))) {
                    //    echo "Falló la preparación I: (" . mysqli_error($conn) . ") ";
                    //}

                    $sql = "SELECT 
                                c_unimed.cve_umed,
                                t.cve_almac as almacen_prod,
                                t.Cve_Articulo AS cve_articulo,
                                t.Cve_Usuario as cve_usuario,
                                t.Cant_Prod
                            FROM t_ordenprod t 
                            LEFT JOIN c_articulo a ON a.cve_articulo = t.Cve_Articulo AND a.Compuesto = 'S'
                            LEFT JOIN c_unimed ON a.unidadMedida = c_unimed.id_umed
                            WHERE a.cve_articulo = t.Cve_Articulo AND t.Folio_Pro = '{$orden_id}'";
                    $query = mysqli_query($conn, $sql);
                    $row_ord = mysqli_fetch_array($query);
                    $cve_umed = $row_ord['cve_umed'];
                    $almacen_prod = $row_ord['almacen_prod'];
                    $cve_articulo_ord = $row_ord['cve_articulo'];
                    $Cant_Prod_ord = $row_ord['Cant_Prod'];
                    $cve_usuario = $row_ord['cve_usuario'];


//'{$id_almacen}', {$idy_ubica}, cve_articulo, '{$LoteOT}', 0, ntarima, 0, cantidad, 1, 0

//(SELECT cve_articulo, cve_lote, NOW(), 'PT_{$orden_id}', '{$idy_ubica}', {$Cant_Prod_ord}, 14, '{$cve_usuario}','{$id_almacen}' FROM t_tarima WHERE fol_folio = '{$orden_id}')

                $sql_tarima = "SELECT ntarima as tarima_kdx, Fol_Folio AS folio_kdx, cve_articulo AS cve_articulo_kdx, lote AS cve_lote_kdx, cantidad AS cantidad_kdx FROM t_tarima WHERE Fol_Folio = '{$orden_id}'";
                $res_tarima = mysqli_query($conn, $sql_tarima);
                while($row_tarima = mysqli_fetch_array($res_tarima))
                {
                    extract($row_tarima);
                    $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('$cve_articulo_kdx', '$cve_lote_kdx', NOW(), 'PT_{$folio_kdx}', '$idy_ubica', '$cantidad_kdx', 14, '$cve_usuario', '$id_almacen')";
                    $res_kardex = mysqli_query($conn, $sql_kardex);

                    $sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES 
                    ((SELECT MAX(id) FROM t_cardex), '$clave_almacen', {$tarima_kdx}, CURDATE(),'PT_{$folio_kdx}', '{$idy_ubica}', 14, '{$cve_usuario}', 'I')";
                    $res_kardex = mysqli_query($conn, $sql_kardex);
                }


              //*******************************************************************************
              //                          EJECUTAR EN INFINITY
              //*******************************************************************************
              $sql = "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'";
              $query = mysqli_query($conn, $sql);
              $ejecutar_infinity = mysqli_fetch_array($query)['existe'];

              if($ejecutar_infinity)
              {
                    $sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = '{$cve_articulo_ord}' and cve_almac = '$almacen_prod' AND tipo = 'ubicacion'";
                    $query = mysqli_query($conn, $sql);
                    $row_ord = mysqli_fetch_array($query);
                    $existencia_art_prod = $row_ord['existencia_art_prod'];
                    if(!$existencia_art_prod) $existencia_art_prod = 0;

                    $sql = "SELECT Url, Servicio, User, Pswd, Empresa, to_base64(CONCAT(User,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1";
                    $query = mysqli_query($conn, $sql);
                    $row_infinity = mysqli_fetch_array($query);
                    $Url_inf = $row_infinity['Url'];
                    $url_curl = $row_infinity['url_curl'];
                    $Servicio_inf = $row_infinity['Servicio'];
                    $User_inf = $row_infinity['User'];
                    $Pswd_inf = $row_infinity['Pswd'];
                    $Empresa_inf = $row_infinity['Empresa'];
                    $hora_movimiento = $row_infinity['hora_movimiento'];
                    $Codificado = $row_infinity['Codificado'];

                      $json = "[";
                      //$row = mysqli_fetch_array($query);
                      //echo $sql;
                        extract($row_ord);
                        //if($this->pSQL($row[self::LOTE]) == "") 
                            $LoteOT = "";
                        $json .= "{";
                        $json .= '"item":"'.$cve_articulo_ord.'","um":"'.$cve_umed.'","batch":"'.$LoteOT.'", "qty": '.$existencia_art_prod.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                        $json .= "}";
                      //$json[strlen($json)-1] = ' ';
                      $json .= "]";


                  $curl = curl_init();

                  //$url_curl = $Url_inf.':8080/'.$Servicio_inf;
                  curl_setopt_array($curl, array(
                    // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                    CURLOPT_URL => "$url_curl",
                    //CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>
                    // Aquí cambia la cadena JSON
                    $json,
                    CURLOPT_HTTPHEADER => array(
                      'Content-Type: application/json',
                      //'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
                      'Authorization: Basic '.$Codificado.''
                    )
                    ,CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                  ));

                  $response = curl_exec($curl);
                  $response_ot .= $response."\n";

                  curl_close($curl);      
                  //echo $response;
                  
                //$response = 'Pendiente';
                  $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', 'Transformacion', 'WEB')";
                  $query = mysqli_query($conn, $sql);

              }
              //*******************************************************************************/
              //*******************************************************************************



                }
            //xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

            }//acepto
            else
            {
                //acepto == false
                $folios_sin_stock[] = $orden_id;
            }

                }//while
//*****************************************************************************************************************
                }
                else //else Tipo_OT
                {
                $sql = "
                SELECT op.clave, op.control_lotes, op.Lote, op.LoteOT, op.Caduca, op.Caducidad, op.control_peso, op.Cantidad, op.Cantidad_Producida, op.ubicacion, IFNULL(op.existencia, 0) AS existencia, op.Cve_Contenedor, op.mav_cveunimed FROM (
                    SELECT DISTINCT 
                        a.cve_articulo AS clave,
                        IFNULL(a.control_lotes, 'N') AS control_lotes,
                        e.cve_lote AS Lote,
                        t.Cve_Lote AS LoteOT,
                        IFNULL(a.control_peso, 'N') AS control_peso,
                        IFNULL(a.Caduca, 'N') AS Caduca,
                        IF(a.Caduca = 'S', l.Caducidad, '') AS Caducidad,
                        IF(IFNULL(th.Fol_folio, '') = '', ac.Cantidad, ac.Cantidad/(SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '$orden_id')) AS Cantidad,
                        (ac.Cantidad_Producida*ac.Cantidad) AS Cantidad_Producida,

                        #(SELECT cu.idy_ubica FROM V_ExistenciaProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND vp.cve_articulo = a.cve_articulo AND vp.cve_almac = '{$id_almacen}' AND cu.Activo = 1 AND cu.AreaProduccion = 'S' AND vp.cve_ubicacion = t.idy_ubica GROUP BY vp.Existencia LIMIT 1) AS ubicacion,
                        t.idy_ubica as ubicacion,

                        #(SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion = 'S' AND vp.cve_articulo = a.cve_articulo AND vp.tipo = 'ubicacion' AND vp.cve_lote = td.Cve_Lote) AS existencia
                        #AND cu.AreaProduccion = 'S'
                        e.Cve_Contenedor,
                        t.Cve_Usuario as cve_usuario,
                        u.mav_cveunimed,
                        e.Existencia AS existencia 
                    FROM t_artcompuesto ac
                        LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                        LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro
                        LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
                        LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = td.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = td.Folio_Pro)
                        LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_Articulo AND l.LOTE = e.cve_lote
                        LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
                        LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                    WHERE t.Folio_Pro = '$orden_id' AND e.cve_almac = '{$id_almacen}' 
                    #AND e.cve_lote = td.Cve_Lote AND e.cve_articulo = td.Cve_Articulo
                    AND t.idy_ubica = '{$idy_ubica_ot}'
                    AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = td.Cve_Articulo #AND e.cve_lote = td.Cve_Lote
                    ORDER BY Caducidad
                ) AS op WHERE op.ubicacion IS NOT NULL";

                $res_art = "";
                $sql_art = $sql;
                if (!($res_art = mysqli_query($conn, $sql)))
                    echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";

                $acepto = true;

                while($row_art = mysqli_fetch_array($res_art))
                {
                    //+$row_art['Cantidad_Producida'] 
                    //&& $row_art['existencia'] > 0
                    if(($row_art['Cantidad']*$cantidad_art_compuesto) > $row_art['existencia'] )
                    {
                        $acepto = false;
                        break;
                    }
                }
                if($acepto == false)
                    $folios_sin_stock[] = $orden_id;
                else
                {
                    while($row_art_1 = mysqli_fetch_array($res_art))
                    {
                        //if($Tipo_OT == 'IMP_LP') $cantidad_art_compuesto = $row_art_1['existencia'];
                        $cantidad = $row_art_1['Cantidad']*$cantidad_art_compuesto;
                        $idy_ubica = $row_art_1['ubicacion'];
                        $clave = $row_art_1['clave'];
                        $Lote = $row_art_1['Lote'];
                        $LoteOT = $row_art_1['LoteOT'];
                        $caducidadMIN = $row_art_1['Caducidad'];
                        $Caduca = $row_art_1['Caduca'];
                        $Cve_Contenedor = $row_art_1['Cve_Contenedor'];
                        $cve_usuario = $row_art_1['cve_usuario'];
                        $mav_cveunimed = $row_art_1['mav_cveunimed'];
                        $control_peso = $row_art_1['control_peso'];

                        //if($idy_ubica == "") $mensaje_error .= "El Producto {$clave} no se encuentra en una ubicación de Producción\n";
                        $caducidad = "";
                        $listo = false;
                        $LoteOT = "";
                        $mensaje_error = "";

                        if($Caduca == 'S' && $caducidadMIN != '' && $caducidadMIN != '0000-00-00' && $listo == false)
                        {
                            $caducidad = $caducidadMIN;
                            $listo = true;
                        }

                        if($control_peso == 'N' && $mav_cveunimed == 'H87') $cantidad = ceil($cantidad);

                        $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and cve_lote = '$Lote'";

                        if($Cve_Contenedor != '')
                            $sql = "UPDATE ts_existenciatarima SET existencia = existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and lote = '$Lote' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')";

                        if (!($res = mysqli_query($conn, $sql))) {
                            echo "Falló la preparación G: (" . mysqli_error($conn) . ") ";

                        $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$clave}', '{$Lote}', NOW(), '{$idy_ubica}', '{$orden_id}', {$cantidad}, 8, '{$cve_usuario}','{$id_almacen}')";
                        $res_kardex = mysqli_query($conn, $sql_kardex);


                        }

//**************************************************************************************************************************
                $sql = "SELECT COUNT(*) as existe FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND cve_lote = '$LoteOT'";

                if (!($res = mysqli_query($conn, $sql)))
                    echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
                $existe_producto = mysqli_fetch_array($res)['existe'];


                $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia + $cantidad_art_compuesto WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND cve_lote = '$LoteOT'";

                if($existe_producto == 0)
                {
                    $sql = "SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
                    $ID_Proveedor = mysqli_fetch_array($res)['ID_Proveedor'];

                    $sql = "SELECT DISTINCT control_lotes, Caduca FROM c_articulo WHERE cve_articulo = '$cod_art_compuesto'";
                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación E: (" . mysqli_error($conn) . ") ";
                    $row_control = mysqli_fetch_array($res);
                    $control_lotes = $row_control['control_lotes'];
                    $Caduca = $row_control['Caduca'];

                    /*
                    if($control_lotes != 'S')
                    {
                        $sql = "UPDATE c_articulo SET control_lotes = 'S' WHERE cve_articulo = '$cod_art_compuesto'";
                        if (!($res = mysqli_query($conn, $sql)))
                            echo "Falló la preparación D: (" . mysqli_error($conn) . ") ";
                    }

                    if($Caduca != 'S' && $caducidad != '')
                    {
                        $sql = "UPDATE c_articulo SET Caduca = 'S' WHERE cve_articulo = '$cod_art_compuesto'";
                        if (!($res = mysqli_query($conn, $sql)))
                            echo "Falló la preparación C: (" . mysqli_error($conn) . ") ";
                    }
                    */
                    if($LoteOT)
                    {
                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', '{$caducidad}')";

                        if($Caduca != 'S') // Si Caduca = N, se registrará una fecha de elaboración
                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', CURDATE())";

                        if (!($res = mysqli_query($conn, $sql)))
                            echo "Falló la preparación B: (" . mysqli_error($conn) . ") ". $sql;
                    }


                    //$sql = "SELECT DISTINCT cve_almac FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica'";
                    $sql = "SELECT DISTINCT cve_almac FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = '$idy_ubica'";
                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación A: (" . mysqli_error($conn) . ") ";
                    $cve_almacen = mysqli_fetch_array($res)['cve_almac'];
                    if($LoteOT == "") $LoteOT = $orden_id;
                   $sql = "UPDATE t_ordenprod SET Cve_Lote = '{$LoteOT}' WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación jj: (" . mysqli_error($conn) . ") ";
                    }

                    if(!$ID_Proveedor) $ID_Proveedor = 0;
                   $sql = "INSERT IGNORE INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) VALUES ('{$cve_almacen}', {$idy_ubica}, '{$cod_art_compuesto}', '{$LoteOT}', {$cantidad_art_compuesto}, {$ID_Proveedor})";
                }

                if($idy_ubica)
                {
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación Z: (" . mysqli_error($conn) . ") " . $sql;
                    }

    /*
                $sql = "SELECT DISTINCT ROUND((Cantidad_Producida*Cantidad / Cantidad), 0) AS Cantidad_Producida
                        FROM t_artcompuesto
                        WHERE Cve_ArtComponente = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$orden_id')";
    */

                    $sql = "UPDATE t_ordenprod SET Status = 'T', Cant_Prod = IFNULL(Cant_Prod, 0)+$cantidad_art_compuesto WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación I: (" . mysqli_error($conn) . ") ";
                    }


                $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('$cod_art_compuesto', '$LoteOT', NOW(), 'PT_{$orden_id}', '$idy_ubica', '$cantidad_art_compuesto', 14, '$cve_usuario', '$id_almacen')";
                $res_kardex = mysqli_query($conn, $sql_kardex);

                //$folios_completados++;
                //$porcentaje = (($folios_completados/count($folios_creados))*100)."%";
                //$this->response(200, [
                //'porcentaje' => $porcentaje
                //]);
              //*******************************************************************************
              //                          EJECUTAR EN INFINITY
              //*******************************************************************************
              $sql = "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'";
              $query = mysqli_query($conn, $sql);
              $ejecutar_infinity = mysqli_fetch_array($query)['existe'];

              if($ejecutar_infinity)
              {
                    $sql = "SELECT 
                                c_unimed.cve_umed,
                                t.Cve_Articulo AS cve_articulo,
                                t.Cant_Prod
                            FROM t_ordenprod t 
                            LEFT JOIN c_articulo a ON a.cve_articulo = t.Cve_Articulo AND a.Compuesto = 'S'
                            LEFT JOIN c_unimed ON a.unidadMedida = c_unimed.id_umed
                            WHERE a.cve_articulo = t.Cve_Articulo AND t.Folio_Pro = '{$orden_id}'";
                    $query = mysqli_query($conn, $sql);
                    $row_ord = mysqli_fetch_array($query);
                    $cve_umed = $row_ord['cve_umed'];
                    $cve_articulo_ord = $row_ord['cve_articulo'];
                    $Cant_Prod_ord = $row_ord['Cant_Prod'];


                    $sql = "SELECT Url, Servicio, User, Pswd, Empresa, to_base64(CONCAT(User,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1";
                    $query = mysqli_query($conn, $sql);
                    $row_infinity = mysqli_fetch_array($query);
                    $Url_inf = $row_infinity['Url'];
                    $url_curl = $row_infinity['url_curl'];
                    $Servicio_inf = $row_infinity['Servicio'];
                    $User_inf = $row_infinity['User'];
                    $Pswd_inf = $row_infinity['Pswd'];
                    $Empresa_inf = $row_infinity['Empresa'];
                    $hora_movimiento = $row_infinity['hora_movimiento'];
                    $Codificado = $row_infinity['Codificado'];


              $json = "[";
              //$row = mysqli_fetch_array($query);
                extract($row_ord);
                //if($this->pSQL($row[self::LOTE]) == "") 
                    $LoteOT = "";
                $json .= "{";
                $json .= '"item":"'.$cve_articulo_ord.'","um":"'.$cve_umed.'","batch":"'.$LoteOT.'", "qty": '.$Cant_Prod_ord.',"typeMov":"T","warehouse":"'.$cve_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                $json .= "}";
              //$json[strlen($json)-1] = ' ';
              $json .= "]";

            
                  $curl = curl_init();
                  //$url_curl = $Url_inf.':8080/'.$Servicio_inf;

                  curl_setopt_array($curl, array(
                    // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                    CURLOPT_URL => "$url_curl",
                    //CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>
                    // Aquí cambia la cadena JSON
                    $json,
                    CURLOPT_HTTPHEADER => array(
                      'Content-Type: application/json',
                      //'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
                      'Authorization: Basic '.$Codificado.''
                    )
                    ,CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                  ));

                  $response = curl_exec($curl);
                  $response_ot .= $response."\n";

                  curl_close($curl);      
                  //echo $response;
                  
                  //$response = 'Pendiente';
                  $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', 'Transformacion', 'WEB')";
                  $query = mysqli_query($conn, $sql);

              }
              //*******************************************************************************/
              //*******************************************************************************



                }

//**************************************************************************************************************************

                    }//while
                }//else
                }//else Tipo_OT



            }//foreach
        }
/*
        $registros_vacios--;
        $msj_folio_vacio = "";
        if($registros_vacios > 0)
        {
            if($registros_vacios == 1)
                $msj_folio_vacio = "\nHay 1 registro en el archivo que se omitió ya que se subió con el Folio vacío\n\n";
            else 
                $msj_folio_vacio = "\nExisten ".($registros_vacios)." registros con Folios vacíos en el archivo que se omitieron\n\n";
        }

*/        $msj_sin_stock = "";
        if(count($folios_sin_stock) > 0)
        {
            $folios_implode = implode($folios_sin_stock, ", ");
            $msj_sin_stock = "Los Folios: $folios_implode No poseen Stock Para producir, puede producirlos en Administración de OT después de surtir material";
        }

        $msj_creados = "";
        if(count($folios_creados) > 0)
        {
            $folios_implode = implode($folios_creados, ", ");
            $msj_creados = "Fueron Creados Los Folios: $folios_implode \n\n";
        }

        $this->response(200, [
            'statusText' =>  "Ordenes de Producción importados con exito. Total de Ordenes: \"{$registros}\" \n\n $msj_creados \n\n $msj_sin_stock \n\n$msj_folio_vacio",
            'msj_tracking' => $sql_tracking,
            'folios_creados' => $folios_creados,
            'realizar_produccion' => $realizar_produccion,
            'sql_check' => $sql_check,
            'responses' => $response_ot,
            'url_curl' => "{$url_curl}",
            'articulos' => $articulos
        ]);

    }




    public function exportarBOMTodos()
    {
        $columnas = [
            'Producto Compuesto',
            'Producto',
            'Descripcion',
            'Cantidad Requerida',
            'Unidad de Medida'
        ];

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            $sql_chatset = "SET NAMES utf8mb4;";
            if (!($res = mysqli_query($conn, $sql_chatset))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }


    $sql = "SELECT
                    t_artcompuesto.Cve_Articulo,
                    t_artcompuesto.Cve_ArtComponente,
                    t_artcompuesto.Cantidad,
                    t_artcompuesto.Status,
                    t_artcompuesto.Activo,
                    c_unimed.cve_umed,
                    CONVERT(CAST(c_articulo.des_articulo AS BINARY) USING utf8) AS des_articulo,
                    c_articulo.control_peso,
                    c_unimed.des_umed
                    FROM
                    t_artcompuesto
                    INNER JOIN c_articulo ON t_artcompuesto.Cve_Articulo = c_articulo.cve_articulo
                    LEFT  JOIN c_unimed ON c_articulo.unidadMedida = c_unimed.id_umed
                    ORDER BY Cve_ArtComponente
            ";

            if (!($res = mysqli_query($conn, $sql))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

        //$data_oc = mysqli_fetch_assoc($res);
        $filename = "BOM_Articulos".date('d-m-Y').".xls";
        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        //foreach($data_oc as $row)
        while($row = mysqli_fetch_assoc($res))
        {
            echo $this->clear_column($row['Cve_ArtComponente']) . "\t";
            echo $this->clear_column($row['Cve_Articulo']) . "\t";
            echo $this->clear_column($row['des_articulo']) . "\t";
            echo $this->clear_column($row['Cantidad']) . "\t";
            echo $this->clear_column($row['cve_umed']) . "\t";
            echo  "\r\n";
        }
        exit;
        
    }

    /**
     * Undocumented function
     *
     * @param [type] $str
     * @return void
     */
    private function clear_column(&$str)
    {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        return $str;
    }


}
