<?php
include '../../../app/load.php';

error_reporting(0);
// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \AdminOrdenTrabajo\AdminOrdenTrabajo();


    function Ejecutar_Infinity_WS($clave, $Lote, $cantidad, $um, $clave_almacen, $ejecutar_infinity, $Url_inf, $url_curl, $Servicio_inf, $User_inf, $Pswd_inf, $Empresa_inf, $hora_movimiento, $Codificado)
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


if( $_POST['action'] == 'detalle' ) {

    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    $Folio_Pro = $_POST['Folio_Pro'];
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
        if (!($res = mysqli_query($conn, $sql_charset)))
            echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
        $charset = mysqli_fetch_array($res)['charset'];
        mysqli_set_charset($conn , $charset);

    $sql = "SELECT COUNT(Cve_Articulo) as total from td_ordenprod where Folio_Pro = '$Folio_Pro'";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];

/*
    $sql = "SELECT op.Folio_Pro, op.Cve_Articulo AS clave, a.des_articulo AS descripcion, op.Cve_Lote, op.Fecha_Prod, op.Cantidad, op.Cantidad_Producida, op.Cantidad_Faltante,
    (SELECT nombre_completo FROM c_usuario WHERE id_user = op.Usr_Armo) AS usuario 
        FROM c_articulo a, td_ordenprod op 
         where op.Cve_Articulo = a.cve_articulo and op.Folio_Pro = '$Folio_Pro'
        limit $start, $limit";
*/
    $sql = "SELECT DISTINCT 
                   td.id as id_pedido,
                   op.id_ord, 
                   op.Folio_Pro, 
                   GROUP_CONCAT(DISTINCT IFNULL(pr.Nombre, '') SEPARATOR ',') AS Proveedor,
                   IFNULL(GROUP_CONCAT(DISTINCT IFNULL(pr.ID_Proveedor, '') SEPARATOR ','), '') AS ID_Proveedor,
                   op.Cve_Articulo as clave, 
                   a.des_articulo as descripcion, 
                   a.control_peso, 
                   p.status AS status_pedido,
                   IF(a.control_lotes = 'S', op.Cve_Lote, '' ) AS Cve_Lote, 
                   IF(a.Caduca = 'S', IF(DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y') = DATE_FORMAT('0000-00-00', '%Y-%m-%d'), '', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y')), '' ) AS Caducidad,
                   DATE_FORMAT(op.Fecha_Prod, '%d-%m-%Y %H:%i:%s') AS Fecha_Prod, 
                   #(op.Cantidad*orp.Cantidad) as Cantidad, 
                   #op.Cantidad as Cantidad, 
                   (ac.Cantidad*orp.Cantidad) AS Cantidad,
                   #IFNULL(SUM(e.Existencia), 0) AS Existencia,
                   #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE cve_articulo = op.Cve_Articulo AND op.Cve_Lote = cve_lote AND tipo = 'ubicacion' AND Id_Proveedor = orp.ID_Proveedor AND cve_almac = orp.cve_almac), 0) AS Existencia,
                   #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaProduccion WHERE cve_articulo = op.Cve_Articulo AND op.Cve_Lote = cve_lote AND cve_almac = orp.cve_almac), 0) AS Existencia,
                   IF(IFNULL(orp.idy_ubica, '') = '', IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaProduccion WHERE cve_articulo = op.Cve_Articulo AND op.Cve_Lote = cve_lote AND cve_almac = orp.cve_almac), 0), IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaProduccion WHERE cve_articulo = op.Cve_Articulo  AND cve_almac = orp.cve_almac AND cve_ubicacion = orp.idy_ubica), 0)) AS Existencia,
                   IFNULL(a.peso, 0) AS peso,
                   #AND Id_Proveedor = orp.ID_Proveedor
                   #AND op.Cve_Lote = cve_lote
                   (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT Cve_Usuario FROM t_ordenprod WHERE Folio_Pro = op.Folio_Pro) OR id_user = (SELECT Cve_Usuario FROM t_ordenprod WHERE Folio_Pro = op.Folio_Pro)) AS usuario,
                   IF(op.id_ord IN (SELECT id_art_rel FROM td_ordenprod WHERE Folio_Pro = op.Folio_Pro), 'N', 'S') AS boton_mas 
        FROM td_ordenprod op
        LEFT JOIN t_ordenprod orp ON orp.Folio_Pro = op.Folio_Pro
        LEFT JOIN c_articulo a ON op.Cve_Articulo = a.cve_articulo
        LEFT JOIN t_artcompuesto ac ON ac.Cve_Articulo= a.cve_articulo AND orp.Cve_Articulo = ac.Cve_ArtComponente
        LEFT JOIN c_lotes ON c_lotes.Lote = op.Cve_Lote AND c_lotes.cve_articulo = op.Cve_Articulo
        LEFT JOIN V_ExistenciaGral e ON e.cve_articulo = op.Cve_Articulo AND op.Cve_Lote = e.cve_lote AND e.tipo = 'ubicacion' AND e.Id_Proveedor = orp.ID_Proveedor
        LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = a.cve_articulo AND rap.Id_Proveedor = orp.ID_Proveedor
        LEFT JOIN c_proveedores pr ON pr.ID_Proveedor = rap.Id_Proveedor AND pr.Id_Proveedor = orp.ID_Proveedor
        LEFT JOIN th_pedido p ON p.Fol_folio = op.Folio_Pro
        LEFT JOIN td_pedido td ON td.Cve_articulo = op.Cve_Articulo AND op.Folio_Pro = td.Fol_folio AND IFNULL(op.Cve_Lote, '') = IFNULL(td.cve_lote, '')
        WHERE op.Folio_Pro = '$Folio_Pro' AND IFNULL(a.tipo_producto, '') != 'ProductoNoSurtible' 
        GROUP BY Folio_Pro, clave, Cve_Lote
        ORDER BY op.Cve_Articulo, id_ord

        limit $start, $limit";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map("utf8_encode", $row );
        extract($row);
     //   $linea = array_search($clave,$lineas) + 1;
        $arr[] = $row;

        $mostrar_lotes = "";
        if($status_pedido == 'A' && $boton_mas == 'S')
        {
            $disabled = '';
            if($boton_mas == 'N') $disabled = 'disabled';
            $select_lotes  = "<select id='lotes;;;;{$Folio_Pro};;;;{$clave}' {$disabled} style='max-width:150px;' class='select_lotes chosen-select' onchange='change_lote(this.value)'>";
            $lote_val = $Folio_Pro.";;;;".$clave.";;;; ;;;;".$id_ord.";;;;".$id_pedido;
            $select_lotes .= "<option value='{$lote_val}'>Desconocido</option>";

            if($Cve_Lote)
                $select_lotes .= "<option value='' selected>{$Cve_Lote}</option>";
            //$sql_lotes = "SELECT Lote, DATE_FORMAT(Caducidad, '%d-%m-%Y') as Caducidad FROM c_lotes WHERE Activo = 1 AND cve_articulo = '{$clave}'";
            $sql_proveedor = "";
            if($ID_Proveedor != '') $sql_proveedor = " AND e.Id_Proveedor IN ($ID_Proveedor) ";
            $sql_lotes = "SELECT DISTINCT t.Lote, DATE_FORMAT(t.Caducidad, '%d-%m-%Y') AS Caducidad
                          FROM c_lotes t
                          LEFT JOIN V_ExistenciaGral e ON e.cve_articulo = t.cve_articulo AND t.Lote = e.cve_lote AND e.tipo = 'ubicacion' AND e.Cuarentena != 1
                          LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                          WHERE t.Activo = 1 AND t.cve_articulo = '{$clave}' AND (((CURDATE() <= t.Caducidad AND a.Caduca = 'S') OR t.Caducidad = DATE_FORMAT('0000-00-00', '%Y-%m-%d')) 
                            OR ((SELECT Cve_Lote FROM td_ordenprod WHERE Folio_Pro = '{$Folio_Pro}' AND Cve_Articulo = e.cve_articulo AND IFNULL(Cve_Lote, '') = '') = '' AND a.Caduca!='S'))
                          AND t.Lote NOT IN (SELECT Cve_Lote FROM td_ordenprod WHERE Folio_Pro = '{$Folio_Pro}' AND Cve_Articulo = e.cve_articulo AND Cve_Lote = e.cve_lote) 
                          AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = '{$Folio_Pro}')
                          AND e.Existencia > 0 
                          $sql_proveedor;";
            #$sql_lotes = "SELECT l.Lote FROM c_lotes l WHERE l.Activo = 1 AND l.cve_articulo = '{$clave}' AND (SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE cve_articulo = '{$clave}' AND cve_lote = l.Lote ) >= {$Cantidad}";
            #AND cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = '{$Folio_Pro}')
            if (!($res_lotes = mysqli_query($conn, $sql_lotes))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }
            $n_lotes = 0;
            while ($row_lote = mysqli_fetch_array($res_lotes)) 
            {
                $lote = $row_lote["Lote"];
                $lote_txt = $row_lote["Lote"];
                //$Caducidad = $row_lote["Caducidad"];
                if($row_lote["Caducidad"] != '' && $row_lote["Caducidad"] != '00-00-0000') $lote_txt .= " (".$row_lote["Caducidad"].")";
                $lote_val = $Folio_Pro.";;;;".$clave.";;;;".$lote.";;;;".$id_ord.";;;;".$id_pedido;
                $selected = '';
                if($lote == $Cve_Lote) $selected = 'selected';
                $select_lotes .= "<option value='{$lote_val}' {$selected}>{$lote_txt}</option>";
                $n_lotes++;
            }
            $select_lotes .= "</select>";
            $mostrar_lotes = $select_lotes;
            $ok = "valor";//agregar_lote//($Cantidad-$Existencia)
            if(/*$Cantidad > $Existencia &&*/ $n_lotes > 0 && $Existencia > 0 /*&& $boton_mas == 'S'*/) $mostrar_lotes .= '&nbsp;<i class="fa fa-plus" onclick="cantidad_asignar(\''.$id_ord.'\', \''.$Folio_Pro.'\', \''.$clave.'\', \''.($Cantidad).'\', \''.$Existencia.'\', \''.$id_pedido.'\');" title="Agregar Lote" aria-hidden="true" style="color:green;cursor:pointer;float:right;"></i>';
        }
        else
            $mostrar_lotes = $Cve_Lote;

        if($control_peso == 'S') {$Cantidad = number_format($Cantidad, 2);$Existencia = number_format($Existencia, 2);}

        $responce->rows[$i]['id']=$i;
        $responce->rows[$i]['cell']=array($Folio_Pro, $Proveedor, $clave, utf8_encode($descripcion), $mostrar_lotes, $Caducidad,  $Fecha_Prod, $Cantidad, $Existencia, $Existencia*$peso, $usuario);
        $i++;
    }
    echo json_encode($responce);
}


if( $_POST['action'] == 'HistorialSAP' ) {

    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    $Folio_Pro = $_POST['Folio_Pro'];
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
        if (!($res = mysqli_query($conn, $sql_charset)))
            echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
        $charset = mysqli_fetch_array($res)['charset'];
        mysqli_set_charset($conn , $charset);

    $sql = "SELECT * FROM t_log_sap WHERE folio = '$Folio_Pro'";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);

    $sql.= " LIMIT $start, $limit ";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map("utf8_encode", $row );
        extract($row);
     //   $linea = array_search($clave,$lineas) + 1;
        $arr[] = $row;


        $responce->rows[$i]['id']=$i;
        $responce->rows[$i]['cell']=array($fecha, $cadena, $respuesta, $modulo, $funcion);
        $i++;
    }
    echo json_encode($responce);
}


if( $_POST['action'] == 'validar_produccion' ) 
{
    $folioPro = $_POST['folio'];

    $sql = "SELECT existencia FROM ts_existenciatarima WHERE ntarima IN (SELECT ntarima FROM t_tarima WHERE fol_folio = '$folioPro')";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($res);
    $cantidadProd = $row['existencia'];

    echo $cantidadProd;

}

if( $_POST['action'] == 'actualizarComponentes' ) 
{
    $orden_id               = $_POST['orden_id'];
    $almacen                = $_POST['almacen'];
    $Tipo_OT                = $_POST['Tipo_OT'];
    $cantidad_art_compuesto = $_POST['cantidad_art_compuesto'];
    $cve_articulo_LP        = $_POST['cve_articulo_LP'];
    $cod_art_compuesto      = strtoupper($_POST['cod_art_compuesto']);
    $lote_cambiar = $_POST['lote_cambiar'];
    $instancia = $_POST['instancia'];
    $lp_read = '';
    $id_almacen = '';



    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_instancia = "SELECT DISTINCT Valor as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
    if (!($res = mysqli_query($conn, $sql_instancia)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $instancia = mysqli_fetch_array($res)['instancia'];


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

  $sql = "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'";
  $query = mysqli_query($conn, $sql);
  $ejecutar_infinity = mysqli_fetch_array($query)['existe'];

  $sql = "SELECT IF(Cantidad = Cant_Prod, 1, 0) as terminado, Status, Tipo FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";
  $query = mysqli_query($conn, $sql);
  $row_st = mysqli_fetch_array($query);
  $status_ot = $row_st['Status'];
  $terminado = $row_st['terminado'];
  $Tipo_imp_lp = $row_st['Tipo'];

  $resul = $status_ot;

  if($Tipo_imp_lp == 'IMP_LP' && $terminado == 1 && $status_ot != 'T')
  {
    $resul = 'Terminar';
  }

  if($resul != 'T')
  {
    $resul = "cero";
    $LoteOT = "";
    if($cantidad_art_compuesto == "")
        $cantidad_art_compuesto = 1;

    $pertenece = -1; $existelp = -1;

    $codigo_correcto = true;
    $resulCodigo = "";
//****************************************************************************************
//PROCESO PARA VERIFICAR QUE SE ESTÁ LEYENDO CON EL FORMATO DE OT$CVE_ART$CANTIDAD$N_LPs
//****************************************************************************************
    if($cod_art_compuesto == strtoupper('extraerLP')) 
    {
        $sql = "SELECT CveLP FROM c_charolas WHERE IDContenedor = (SELECT ntarima FROM t_tarima WHERE fol_folio = '$orden_id' LIMIT 1)";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($res);
        $cod_art_compuesto = $row['CveLP'];

        $sql = "SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_array($res);
        $cantidad_art_compuesto = $row['Cantidad'];
    }
    $lectura = $cod_art_compuesto;
    $arr_lectura = explode("$", $lectura);
    $lectura_ot           = "";
    $lectura_cve_articulo = "";
    $lectura_cantidad     = "";
    $lectura_nLps         = "";
    $id_contenedor        = "";
    $lectura_QR = false;
    $existe_lp = 0;
    if(count($arr_lectura) == 4)
    {
        $lectura_ot           = $arr_lectura[0];
        $lectura_cve_articulo = $arr_lectura[1];
        $lectura_cantidad     = $arr_lectura[2];
        $lectura_nLps         = $arr_lectura[3];

/*
        $sql_lec_lp = "SELECT COUNT(*) as existe FROM c_charolas WHERE CveLP = '{$lectura}'";
        $res = mysqli_query($conn, $sql_lec_lp);
        $row = mysqli_fetch_array($res);
        $existe_lp = $row['existe'];
*/
        //$sql_lec_lp = "SELECT IFNULL(Referencia, '') as Referencia FROM t_ordenprod WHERE Folio_Pro = '{$lectura_ot}'";
        $sql_lec_lp = "SELECT IFNULL(Referencia, '') as Referencia FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}'";
        $res = mysqli_query($conn, $sql_lec_lp);
        $row = mysqli_fetch_array($res);
        $Referencia = $row['Referencia'];

        //if($Referencia != '') $lectura_ot = $Referencia;

        $lectura_QR = true;

        if($existe_lp)
        {
            $resulCodigo = "LecturaLPExistente";
            $codigo_correcto = false;
        }
/*
        //if($lectura_ot != $orden_id)
        if($lectura_ot != $Referencia)
        {
            $resulCodigo = "LecturaIncorrectaOT".$sql_lec_lp;
            $codigo_correcto = false;
        }
*/
        $sql_lec_art = "SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";
        $res = mysqli_query($conn, $sql_lec_art);
        $row = mysqli_fetch_array($res);
        $art_lectura = $row['Cve_Articulo'];

        if(strtoupper($art_lectura) != strtoupper($lectura_cve_articulo))
        {
            $resulCodigo = "LecturaIncorrectaArt";
            $codigo_correcto = false;
        }
    }
    else if(count($arr_lectura) > 1)
    {
        $codigo_correcto = false;
        $resulCodigo = "LecturaIncorrecta";
    }

    if($codigo_correcto && $lectura_QR)
    {
        $cantidad_art_compuesto = $lectura_cantidad;
        $cod_art_compuesto = $lectura_cve_articulo;

        $sql_almacen = "SELECT id FROM c_almacenp WHERE clave = '$almacen'";
        $res = mysqli_query($conn, $sql_almacen);
        $row = mysqli_fetch_array($res);
        $id_almacen = $row['id'];

        $lp_read = $lectura;

        $sql = "SELECT (SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1";
        $rs = mysqli_query($conn, $sql);
        $res_lp = mysqli_fetch_array($rs, MYSQLI_ASSOC);

        //if(!$res_lp['id_contenedor']) break;

        $id_contenedor = $res_lp['id_contenedor'];
        $descripcion   = $res_lp['descripcion'];
        $tipo          = $res_lp['tipo'];
        $alto          = $res_lp['alto'];
        $ancho         = $res_lp['ancho'];
        $fondo         = $res_lp['fondo'];
        $peso          = $res_lp['peso'];
        $pesomax       = $res_lp['pesomax'];
        $capavol       = $res_lp['capavol'];

        $label_lp = $lectura;

        $sql = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) 
                VALUES ({$id_almacen}, '{$label_lp}', '{$descripcion}', 0, 'Pallet', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
        $rs = mysqli_query($conn, $sql);

    }
//****************************************************************************************
//****************************************************************************************

    if($codigo_correcto)
    {
        if($Tipo_OT == 'IMP_LP')
        {

            $sql = "SELECT IFNULL(Compuesto, 'N') as Compuesto FROM c_articulo WHERE cve_articulo = '$cve_articulo_LP'";
            
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación M: (" . mysqli_error($conn) . ") ";
            $es_compuesto = mysqli_fetch_array($res)['Compuesto'];

            if($es_compuesto == 'N')
            {
                $sql = "UPDATE c_articulo SET Compuesto = 'S' WHERE cve_articulo = '$cve_articulo_LP'";
                
                if (!($res = mysqli_query($conn, $sql)))
                    echo "Falló la preparación M: (" . mysqli_error($conn) . ") ";
            }


            //$lp_read = $cod_art_compuesto;
            $cod_art_compuesto = $cve_articulo_LP;

            $sql = "SELECT COUNT(*) AS pertenece FROM t_tarima WHERE Fol_Folio = '{$orden_id}' AND ntarima IN (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$lp_read}')";
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación LP: (" . mysqli_error($conn) . ") ";
            $pertenece = mysqli_fetch_array($res)['pertenece'];
            $sql_pertenece = $sql;

            $sql = "SELECT COUNT(*) AS existelp FROM ts_existenciatarima WHERE ntarima IN (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$lp_read}') AND existencia > 0";
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación LP: (" . mysqli_error($conn) . ") ";
            $existelp = mysqli_fetch_array($res)['existelp'];

            $sql = "SELECT IFNULL(cantidad, 0) as cantidad FROM t_tarima WHERE Fol_Folio = '{$orden_id}' AND ntarima IN (SELECT IDContenedor FROM c_charolas WHERE CveLP = '{$lp_read}')";
            if (!($res = mysqli_query($conn, $sql)))
                echo "XFalló la preparación LP: (" . mysqli_error($conn) . ") ";
            $cantidad_art_compuesto = mysqli_fetch_array($res)['cantidad'];

        }
        else
        {
            $sql = "SELECT IFNULL(Compuesto, 'N') as Compuesto FROM c_articulo WHERE cve_articulo = '$cod_art_compuesto'";
            
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación M: (" . mysqli_error($conn) . ") ";
            $es_compuesto = mysqli_fetch_array($res)['Compuesto'];

            if($es_compuesto == 'N')
            {
                $sql = "UPDATE c_articulo SET Compuesto = 'S' WHERE cve_articulo = '$cod_art_compuesto'";
                
                if (!($res = mysqli_query($conn, $sql)))
                    echo "Falló la preparación M: (" . mysqli_error($conn) . ") ";
            }

        }

        $sql = "SELECT (Cantidad - Cant_Prod) as cantidad_faltante FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}'";
        if (!($res = mysqli_query($conn, $sql)))
            echo "XFalló la preparación CF: (" . mysqli_error($conn) . ") ";
        $cantidad_faltante = mysqli_fetch_array($res)['cantidad_faltante'];
    }


if(!$codigo_correcto)
{
    $resul = $resulCodigo;
}
else if($cantidad_art_compuesto > $cantidad_faltante && $Tipo_OT != 'IMP_LP')
{
    $resul = "CantidadFaltanteError";
}
else if($Tipo_OT == 'IMP_LP' && $pertenece == 0)
{
    $resul = "NoPerteneceLP";
}
else if($Tipo_OT == 'IMP_LP' && $pertenece > 0 && $existelp > 0)
{
    $resul = "LPYaDescontado";
}
else
{
    if($cod_art_compuesto)
    {

        $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
        if (!($res = mysqli_query($conn, $sql_charset)))
            echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
        $charset = mysqli_fetch_array($res)['charset'];
        mysqli_set_charset($conn , $charset);
/*
        $sql = "SELECT MIN(Cantidad) Cantidad FROM td_ordenprod WHERE Folio_Pro = '$orden_id'";
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        $cantidad_requerida = mysqli_fetch_array($res)['Cantidad'];
*/
        //$sql = "SELECT DISTINCT (Cantidad_Producida*Cantidad) as Cantidad_Producida FROM t_artcompuesto WHERE Cve_Articulo = '$cod_art_compuesto'";
        $sql = "SELECT DISTINCT (Cantidad_Producida*Cantidad) as Cantidad_Producida FROM t_artcompuesto WHERE Cve_ArtComponente = '$cod_art_compuesto'";
        
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación M: (" . mysqli_error($conn) . ") ";
        $cantidad_producida = mysqli_fetch_array($res)['Cantidad_Producida'];

        $sql_folio = "SELECT IFNULL(idy_ubica, '') as idy_ubica FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";
        $res = mysqli_query($conn, $sql_folio);
        $row = mysqli_fetch_array($res);
        $idy_ubica_OT = $row['idy_ubica'];

/*
        $sql = "SELECT DISTINCT 
                IF(IFNULL(th.Fol_folio, '') = '', ((SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '$orden_id')*ac.Cantidad - (ac.Cantidad_Producida*ac.Cantidad)), ac.Cantidad) AS Cantidad_Faltante
                FROM t_artcompuesto ac
                LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro
                LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
                LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
                LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                WHERE ac.Cve_ArtComponente = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$orden_id') AND ac.Cve_Articulo = td.Cve_Articulo
                ORDER BY Cantidad_Faltante 
                LIMIT 1";
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación L: (" . mysqli_error($conn) . ") ";
        $cantidad_faltante = mysqli_fetch_array($res)['Cantidad_Faltante'];
*/
        //$cantidad_faltante = $cantidad_requerida - $cantidad_producida;

        $SQL_IdyUbicaOT = "";

        if($idy_ubica_OT != '')
           $SQL_IdyUbicaOT = " AND vp.cve_ubicacion = $idy_ubica_OT ";

        $sql = "
            SELECT op.clave, op.control_lotes, op.Lote, op.LoteOT, op.Caduca, op.Caducidad, op.Cantidad, op.Cantidad_Producida, op.ubicacion, IFNULL(op.existencia, 0) AS existencia, op.Cve_Contenedor, op.peso, op.control_peso, op.unidad_med FROM (
                SELECT DISTINCT 
                    a.cve_articulo AS clave,
                    a.control_lotes,
                    e.cve_lote AS Lote,
                    t.Cve_Lote AS LoteOT,
                    a.Caduca,
                    IFNULL(a.control_peso, 'N') AS control_peso, 
                    um.mav_cveunimed AS unidad_med,
                    IFNULL(a.peso, 0) AS peso,                    IF(a.Caduca = 'S', l.Caducidad, '') AS Caducidad,
                    IF(IFNULL(th.Fol_folio, '') = '', ac.Cantidad, ac.Cantidad/(SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '$orden_id')) AS Cantidad,
                    (ac.Cantidad_Producida*ac.Cantidad) AS Cantidad_Producida,
                    (SELECT cu.idy_ubica FROM V_ExistenciaProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND vp.cve_articulo = a.cve_articulo AND vp.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND cu.Activo = 1 AND cu.AreaProduccion = 'S' {$SQL_IdyUbicaOT} GROUP BY vp.Existencia LIMIT 1) AS ubicacion,
                    #(SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion = 'S' AND vp.cve_articulo = a.cve_articulo AND vp.tipo = 'ubicacion' AND vp.cve_lote = td.Cve_Lote) AS existencia
                    #AND cu.AreaProduccion = 'S'
                    e.Cve_Contenedor,
                    e.Existencia AS existencia 
                FROM t_artcompuesto ac
                    LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                    LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro
                    LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
                    LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = td.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = td.Folio_Pro) AND t.idy_ubica = e.cve_ubicacion
                    LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_Articulo AND l.LOTE = e.cve_lote
                    LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
                    LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
                    LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                WHERE t.Folio_Pro = '$orden_id' AND e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') 
                AND IFNULL(a.tipo_producto, '') != 'ProductoNoSurtible' 
                #AND e.cve_lote = td.Cve_Lote AND e.cve_articulo = td.Cve_Articulo
                AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = td.Cve_Articulo #AND e.cve_lote = td.Cve_Lote
                ORDER BY Caducidad
            ) AS op WHERE op.ubicacion IS NOT NULL";
                #(SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$orden_id')
#AND ac.Cve_ArtComponente = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$orden_id') AND ac.Cve_Articulo = td.Cve_Articulo AND e.cve_lote = td.Cve_Lote
/*
        $sql = "SELECT DISTINCT 
                    a.cve_articulo AS clave,
                    a.control_lotes,
                    l.Lote,
                    a.Caduca,
                    IF(a.Caduca = 'S', l.Caducidad, '') AS Caducidad,
                    ac.Cantidad,
                    (ac.Cantidad_Producida*ac.Cantidad) AS Cantidad_Producida,
                    (SELECT cu.idy_ubica FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND vp.cve_articulo = a.cve_articulo AND vp.tipo = 'ubicacion' GROUP BY vp.Existencia DESC LIMIT 1) AS ubicacion,
                    (SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND vp.cve_articulo = a.cve_articulo AND vp.tipo = 'ubicacion') AS existencia
                FROM t_artcompuesto ac
                    LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                    LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro
                    LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = td.Cve_Articulo AND e.cve_almac = (SELECT cve_almacenp FROM c_almacen WHERE cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = td.Folio_Pro))
                    LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_Articulo AND l.LOTE = e.cve_lote
                    LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
                    LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                WHERE ac.Cve_ArtComponente = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$orden_id') AND ac.Cve_Articulo = td.Cve_Articulo
                ORDER BY Caducidad";
*/
        //$sql = "SELECT * FROM V_CantidadVSExistenciaProduccion WHERE orden_id = '$orden_id'";
        if($instancia != 'dicoisa' && $instancia != 'lacanada')
                $sql = "SELECT v.*, IFNULL(a.peso, 0) as peso, alm.id as id_almacen, MAX(v.existencia) as existencia_select
                        FROM V_CantidadVSExistenciaProduccion v 
                        LEFT JOIN c_almacenp alm on v.clave_almacen = alm.clave
                        LEFT JOIN c_articulo a ON a.cve_articulo = v.clave
                        WHERE v.orden_id = '$orden_id' AND v.cantnecesaria <= v.existencia
                        GROUP BY orden_id, cod_art_compuesto, clave
                        ";

        if($Tipo_OT == 'IMP_LP')
        {
            
        $sql = "SELECT op.clave, op.control_lotes, op.Lote, op.LoteOT, op.Caduca, op.Caducidad, op.Cantidad, op.Cantidad_Producida, op.ubicacion, IFNULL(op.existencia, 0) AS existencia, op.Cve_Contenedor, op.peso, op.control_peso, op.unidad_med FROM (
                SELECT DISTINCT 
                    a.cve_articulo AS clave,
                    a.control_lotes,
                    e.cve_lote AS Lote,
                    t.Cve_Lote AS LoteOT,
                    a.Caduca,
                    IFNULL(a.control_peso, 'N') AS control_peso, 
                    um.mav_cveunimed AS unidad_med,
                    IFNULL(a.peso, 0) AS peso,
                    IF(a.Caduca = 'S', l.Caducidad, '') AS Caducidad,
                    IF(IFNULL(th.Fol_folio, '') = '', ac.Cantidad, ac.Cantidad/(SELECT Cantidad FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}')) AS Cantidad,
                    (ac.Cantidad_Producida*ac.Cantidad) AS Cantidad_Producida,
                    (SELECT cu.idy_ubica FROM V_ExistenciaProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND vp.cve_articulo = a.cve_articulo AND vp.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND cu.Activo = 1 AND cu.AreaProduccion = 'S' {$SQL_IdyUbicaOT} GROUP BY vp.Existencia LIMIT 1) AS ubicacion,
                    #tt.cantidad AS existencia 
                    e.Cve_Contenedor,
                    IFNULL(e.Existencia, 0) AS existencia 
                FROM t_artcompuesto ac
                    LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
                    LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro
                    LEFT JOIN th_pedido th ON th.Ship_Num = t.Folio_Pro
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = t.Folio_Pro
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima 
                    LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = td.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = td.Folio_Pro) AND IFNULL(e.cve_lote, '') = IFNULL(td.cve_lote, '') AND t.idy_ubica = e.cve_ubicacion
                    LEFT JOIN c_lotes l ON l.cve_articulo = td.Cve_Articulo AND l.LOTE = e.cve_lote
                    LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
                    LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
                    LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                WHERE t.Folio_Pro = '{$orden_id}' AND e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') 
                AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = td.Cve_Articulo AND ch.CveLP = '{$lp_read}' 
                AND IFNULL(a.tipo_producto, '') != 'ProductoNoSurtible' 
                ORDER BY Caducidad
            ) AS op WHERE op.ubicacion IS NOT NULL";
            
            if($instancia != 'dicoisa' && $instancia != 'lacanada')
            {
            //$sql = "SELECT * FROM V_CantidadVSExistenciaProduccion WHERE orden_id = '$orden_id'";
                $sql = "SELECT v.*, IFNULL(a.peso, 0) as peso, alm.id as id_almacen, MAX(v.existencia) as existencia_select
                        FROM V_CantidadVSExistenciaProduccion v 
                        LEFT JOIN c_almacenp alm on v.clave_almacen = alm.clave
                        LEFT JOIN c_articulo a ON a.cve_articulo = v.clave
                        WHERE v.orden_id = '$orden_id' AND v.cantnecesaria <= v.existencia
                        GROUP BY orden_id, cod_art_compuesto, clave
                        ";
            }
        }

        $res_art = "";
        $sql_art = $sql;
        if (!($res_art = mysqli_query($conn, $sql)))
            echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";

        $acepto = true;

        while($row_art = mysqli_fetch_array($res_art))
        {
            //+$row_art['Cantidad_Producida'] 
            //&& $row_art['existencia'] > 0

            $cantidad_requerida      = $row_art['Cantidad']*$cantidad_art_compuesto;
            $control_peso_componente = $row_art['control_peso'];
            if($instancia != 'dicoisa' && $instancia != 'lacanada') $unidad_med_componente   = $row_art['um']; else $unidad_med_componente   = $row_art['unidad_med'];
            $peso_componente         = $row_art['peso'];
            $id_almacen              = $row_art['id_almacen'];

            if(!$peso_componente) $peso_componente = 1;

            if($control_peso_componente == 'S' && $unidad_med_componente == 'H87') $cantidad_requerida = round($cantidad_art_compuesto/$peso_componente, 5);

            if($cantidad_requerida > $row_art['existencia'] )
            {
                $acepto = false;
                break;
            }
        }


        //**********************************************************************************************************************
        //      PROCESO PARA QUE EN DICOISA SE PUEDA PRODUCIR SIN REVISAR STOCK MIENTRAS RESUELVEN LA INTERFASE
        //**********************************************************************************************************************
        if($instancia == 'dicoisa') $acepto = true;
        //**********************************************************************************************************************


        //if($cantidad_art_compuesto <= $cantidad_faltante)
        $caducidad = "";
        $idy_ubica = "";
        //$cantidad_faltante &&
        if($acepto)
        {
            $sql = "UPDATE t_artcompuesto SET Cantidad_Producida = IFNULL(Cantidad_Producida, 0)+$cantidad_art_compuesto WHERE Cve_ArtComponente = '$cod_art_compuesto'";
            if (!($res = mysqli_query($conn, $sql))) {
                echo "Falló la preparación J: (" . mysqli_error($conn) . ") ";
            }

            //$sql = "UPDATE t_ordenprod SET Cant_Prod = IFNULL(Cant_Prod, 0)+$cantidad_art_compuesto WHERE Folio_Pro = '$orden_id'";
            //if (!($res = mysqli_query($conn, $sql))) {
            //    echo "Falló la preparación I: (" . mysqli_error($conn) . ") ";
            //}

            if (!($res_art = mysqli_query($conn, $sql_art)))
                echo "Falló la preparación H: (" . mysqli_error($conn) . ") ";

            $listo = false;
            $LoteOT = "";
            $mensaje_error = "";

            while($row_art_1 = mysqli_fetch_array($res_art))
            {
        if($instancia == 'dicoisa' ||  $instancia == 'lacanada')
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

                $control_peso_componente = $row_art_1['control_peso'];
                $unidad_med_componente   = $row_art_1['unidad_med'];
                $peso_componente         = $row_art_1['peso'];

                if(!$peso_componente) $peso_componente = 1;

                if($control_peso_componente == 'S' && $unidad_med_componente == 'H87') $cantidad = round($cantidad_art_compuesto/$peso_componente, 5);
                else if($control_peso_componente == 'N' && $unidad_med_componente == 'H87') $cantidad = ceil($cantidad);

                if($idy_ubica == "") $mensaje_error .= "El Producto {$clave} no se encuentra en una ubicación de Producción\n";

                if($Caduca == 'S' && $caducidadMIN != '' && $caducidadMIN != '0000-00-00' && $listo == false)
                {
                    $caducidad = $caducidadMIN;
                    $listo = true;
                }


                $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and cve_lote = '$Lote'";

                if($Cve_Contenedor != '')
                    $sql = "UPDATE ts_existenciatarima SET existencia = existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and lote = '$Lote' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')";

//                if($lp_read != '')
//                {
//                    $sql = "DELETE FROM ts_existenciatarima WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' AND lote = '$Lote' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read')";//UPDATE ts_existenciatarima SET existencia = existencia - $cantidad
//                }
//

                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación G: (" . mysqli_error($conn) . ") ";
                }

            }
            else
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
  
                $res = \db()->prepare($sql);
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
                        $res = \db()->prepare($sql);
                        if(count($campos_array) == 0)
                            $res->execute();
                        else
                            $res->execute($campos_array);
                    }
                    else
                        throw $e;

                    echo 'Error de conexión: ' . $e->getMessage();
                }

/*
                $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and cve_lote = '$Lote'";

                $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$clave}', '{$Lote}', NOW(), '{$idy_ubica}', '{$orden_id}', {$cantidad}, 8, '{$cve_usuario}','{$id_almacen}')";
                $res_kardex = mysqli_query($conn, $sql_kardex);

                if($Cve_Contenedor != '')
                {
                    $sql = "UPDATE ts_existenciatarima SET existencia = existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and lote = '$Lote' AND ntarima = $ID_Contenedor";

                    $sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES 
                    ((SELECT MAX(id) FROM t_cardex), '$clave_almacen', $ID_Contenedor, CURDATE(), '{$idy_ubica}', '{$orden_id}', 8, '{$cve_usuario}', 'I')";
                    $res_kardex = mysqli_query($conn, $sql_kardex);
                }
                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación G: (" . mysqli_error($conn) . ") ";
                }
*/
      //*******************************************************************************
      //                          EJECUTAR EN INFINITY
      //*******************************************************************************

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
            }

            }

        if($resul != "LPYaDescontado")
        {
            if($mensaje_error == "")
            {
                $sql = "SELECT IFNULL(idy_ubica_dest, idy_ubica) as idy_ubica_dest FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";

                if (!($res = mysqli_query($conn, $sql)))
                    echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
                $idy_ubica_dest = mysqli_fetch_array($res)['idy_ubica_dest'];

                if($idy_ubica_dest != '') $idy_ubica = $idy_ubica_dest;

                $sql = "SELECT COUNT(*) as existe FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND cve_lote = '$LoteOT'";

                if($Tipo_OT == 'IMP_LP')
                {
                    //$sql = "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND lote = '$LoteOT' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read')";
                    $sql = "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND lote = '$LoteOT' AND ntarima = (SELECT ntarima FROM t_tarima WHERE fol_folio = '$orden_id')";
                }

               if($lectura_QR)
                $sql = "SELECT COUNT(*) as existe FROM ts_existenciatarima WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND lote = '$LoteOT' AND ntarima = {$id_contenedor}";

                if (!($res = mysqli_query($conn, $sql)))
                    echo "Falló la preparación F: (" . mysqli_error($conn) . ") ";
                $existe_producto = mysqli_fetch_array($res)['existe'];


                $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia + $cantidad_art_compuesto WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND cve_lote = '$LoteOT'";
                if($Tipo_OT == 'IMP_LP')
                {
                    //$sql = "UPDATE ts_existenciatarima SET Existencia = Existencia + $cantidad_art_compuesto WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND lote = '$LoteOT' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read')";
                    //$sql = "INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) (SELECT cve_almac, IFNULL(idy_ubica_dest, idy_ubica), Cve_Articulo, Cve_Lote, Folio_Pro, (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read'), 0, Cantidad, 1, ID_Proveedor, 0 FROM t_ordenprod WHERE Folio_Pro = '$orden_id') ON DUPLICATE KEY UPDATE existencia = existencia + $cantidad_art_compuesto";
                    $sql = "INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) (SELECT cve_almac, IFNULL(idy_ubica_dest, idy_ubica), Cve_Articulo, Cve_Lote, Folio_Pro, (SELECT ntarima FROM t_tarima WHERE fol_folio = '$orden_id'), 0, Cantidad, 1, ID_Proveedor, 0 FROM t_ordenprod WHERE Folio_Pro = '$orden_id') ON DUPLICATE KEY UPDATE existencia = existencia + $cantidad_art_compuesto";
                }
                $sql_prod = $sql;
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
// EL 04-03-2024 ME DIJERON PARA QUITAR ESTA RESTRICCIÓN, SI EL ARTICULO BASE NO LO DECLARAN CON LOTE ENTONCES SE DEJA SIN LOTE
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

                    if($control_lotes == 'S')
                    {
                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', '{$caducidad}')";

                        if($Caduca != 'S') // Si Caduca = N, se registrará una fecha de elaboración
                        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cod_art_compuesto}', '{$LoteOT}', CURDATE())";

                        if (!($res = mysqli_query($conn, $sql)))
                            echo "Falló la preparación B: (" . mysqli_error($conn) . ") ". $sql;

                        if($instancia == 'foam' && $LoteOT == "") $LoteOT = 'SLFC';
                    }


                    //$sql = "SELECT DISTINCT cve_almac FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica'";
                    //$sql = "SELECT DISTINCT cve_almac FROM V_ExistenciaGralProduccion WHERE cve_ubicacion = '$idy_ubica'";
                    $sql = "SELECT DISTINCT cve_almacenp as cve_almac FROM c_almacen WHERE cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = '$idy_ubica')";
                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación A: (" . mysqli_error($conn) . ") ";
                    $cve_almacen = mysqli_fetch_array($res)['cve_almac'];


                   //if($LoteOT == "") $LoteOT = $orden_id;
                   $sql = "UPDATE t_ordenprod SET Cve_Lote = '{$LoteOT}' WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación jj: (" . mysqli_error($conn) . ") ";
                    }

                    if(!$ID_Proveedor) $ID_Proveedor = 0;
                   $sql = "INSERT IGNORE INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) VALUES ('{$cve_almacen}', {$idy_ubica}, '{$cod_art_compuesto}', '{$LoteOT}', {$cantidad_art_compuesto}, {$ID_Proveedor})";
                   if($Tipo_OT == 'IMP_LP')
                   {
                      // $sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) VALUES ('{$cve_almacen}', {$idy_ubica}, '{$cod_art_compuesto}', '{$LoteOT}', 0, (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read'), 0, {$cantidad_art_compuesto}, 1, {$ID_Proveedor}, 0)";
                    //$sql = "INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) (SELECT cve_almac, IFNULL(idy_ubica_dest, idy_ubica), Cve_Articulo, Cve_Lote, Folio_Pro, (SELECT IDContenedor FROM c_charolas WHERE CveLp = '$lp_read'), 0, Cantidad, 1, ID_Proveedor, 0 FROM t_ordenprod WHERE Folio_Pro = '$orden_id') ON DUPLICATE KEY UPDATE existencia = existencia + $cantidad_art_compuesto";
                    $sql = "INSERT INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) (SELECT cve_almac, IFNULL(idy_ubica_dest, idy_ubica), Cve_Articulo, Cve_Lote, Folio_Pro, (SELECT ntarima FROM t_tarima WHERE fol_folio = '$orden_id'), 0, Cantidad, 1, ID_Proveedor, 0 FROM t_ordenprod WHERE Folio_Pro = '$orden_id') ON DUPLICATE KEY UPDATE existencia = existencia + $cantidad_art_compuesto";

                   }
                   //if($lectura_QR)
                   //{
                    //$sql = "INSERT IGNORE INTO ts_existenciatarima (cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) VALUES ('{$cve_almacen}', {$idy_ubica}, '{$cod_art_compuesto}', '{$LoteOT}', 0, {$id_contenedor}, 0, {$cantidad_art_compuesto}, 1, {$ID_Proveedor}, 0)";
                   //}
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

                    $sql = "UPDATE t_ordenprod SET Cant_Prod = IFNULL(Cant_Prod, 0)+$cantidad_art_compuesto WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql))) {
                        echo "Falló la preparación I: (" . mysqli_error($conn) . ") ";
                    }

                    $sql = "SELECT Cant_Prod AS Cantidad_Producida FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";
                    if (!($res = mysqli_query($conn, $sql)))
                        echo "Falló la preparación X: (" . mysqli_error($conn) . ") ". $sql;
                    $cantidad_producida = mysqli_fetch_array($res)['Cantidad_Producida'];

                    $resul = $cantidad_producida;
                }
                else
                {
                    $resul = "cuatro";//los materiales del producto compuesto no están en producción
                }
            }
            else $resul = 'error';
        }
        }
        else
        {
            $resul = "dos";
            if($acepto == false)
               $resul = "tres";
        }

    }
}

      //*******************************************************************************
      //                          EJECUTAR EN INFINITY
      //*******************************************************************************

/*
      if($ejecutar_infinity)
      {
            $sql = "SELECT 
                    c_unimed.cve_umed
                    FROM c_articulo
                    LEFT JOIN c_unimed ON c_articulo.unidadMedida = c_unimed.id_umed
                    WHERE c_articulo.cve_articulo = '$cod_art_compuesto'";
            $query = mysqli_query($conn, $sql);
            $row_ord = mysqli_fetch_array($query);
            extract($row_ord);
            //$cve_umed = $row_ord['cve_umed'];

            $sql = "SELECT Url, Servicio, User, Pswd, Empresa, TO_BASE64(CONCAT(USER,':',Pswd)) Codificado, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1";
            $query = mysqli_query($conn, $sql);
            $row_infinity = mysqli_fetch_array($query);
            $Url_inf = $row_infinity['Url'];
            $Servicio_inf = $row_infinity['Servicio'];
            $User_inf = $row_infinity['User'];
            $Pswd_inf = $row_infinity['Pswd'];
            $Empresa_inf = $row_infinity['Empresa'];
            $hora_movimiento = $row_infinity['hora_movimiento'];
            $Codificado = $row_infinity['Codificado'];

      $json = "[";

        $json .= "{";
        $json .= '"item":"'.$cod_art_compuesto.'","um":"'.$cve_umed.'","batch":"'.$LoteOT.'", "qty": '.$cantidad_art_compuesto.',"typeMov":"T","warehouse":"'.$almacen.'","dataOpe":"'.$hora_movimiento.'"';
        $json .= "}";
      //$json[strlen($json)-1] = ' ';
      $json .= "]";
*/
/*
          $curl = curl_init();
          
          curl_setopt_array($curl, array(
            // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
            CURLOPT_URL => '{$Url_inf:8080/$Servicio_inf}',
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
              'Authorization: Basic {$Codificado}'
            ),
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
          ));

          $response = curl_exec($curl);

          curl_close($curl);      
          */
          //echo $response;
          //$sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta) VALUES (NOW(), '$Servicio_inf', '$json', '')";
          //$query = mysqli_query($conn, $sql);


      //}
      //*******************************************************************************
      //*******************************************************************************
              if($ejecutar_infinity)
              {
/////////////////////$sql = "SELECT SUM(Existencia) as existencia_art_prod FROM V_ExistenciaGralProduccion WHERE cve_articulo = '{$cve_articulo_ord}' and cve_almac = '$almacen_prod' AND tipo = 'ubicacion'";
/////////////////////$query = mysqli_query($conn, $sql);
/////////////////////$row_ord = mysqli_fetch_array($query);
/////////////////////$existencia_art_prod = $row_ord['existencia_art_prod'];

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
            $query = mysqli_query($conn, $sql);
            $row_ord = mysqli_fetch_array($query);
            $cve_umed = $row_ord['cve_umed'];
            $cve_articulo_ord = $row_ord['cve_articulo'];
            $Cant_Prod_ord = $row_ord['Cant_Prod'];
            $LoteOT = $row_ord['LoteOT'];
            $clave_almacen = $row_ord['clave_almacen'];
            $StatusOT = $row_ord['Status'];
            $id_almacen = $row_ord['id_almacen'];
            $idy_ubica = $row_ord['idy_ubica'];


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
*/
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
    }

    echo $resul;
}

if( $_POST['action'] == 'productoDerivado' ) 
{
/*
    $orden_id              = $_POST['orden_id'];
    $cantidad_art_sobrante = $_POST['cantidad_art_sobrante'];
    $cod_art_compuesto     = strtoupper($_POST['cod_art_compuesto']);
    $art_sobrante          = $_POST['art_sobrante'];
    $lote_compuesto        = $_POST['lote_compuesto'];
    $derivado_merma        = $_POST['derivado_merma'];
    $cve_usuario           = $_POST['cve_usuario'];
    $cantidad_faltante     = $_POST['cantidad_faltante'];
*/
    $idy_ubica                    = $_POST['idy_ubica'];
    $orden_id                     = $_POST['orden_id'];
    $art_sobrante                 = $_POST['art_sobrante'];
    $lote_compuesto               = $_POST['lote_compuesto'];
    $cod_art_compuesto            = $_POST['cod_art_compuesto'];
    $control_lotes_d              = $_POST['control_lotes_d'];
    $Caduca_d                     = $_POST['Caduca_d'];
    $granel_d                     = $_POST['granel_d'];
    $componentes_derivado         = $_POST['componentes_derivado'];
    $lote_componente              = $_POST['lote_componente'];
    $peso_componente              = $_POST['peso_componente'];
    $unidad_medida_comp           = $_POST['unidad_medida_comp'];
    $control_peso_comp            = $_POST['control_peso_comp'];
    $componente_derivado_select   = $_POST['componente_derivado_select'];
    $cantidad_componente          = $_POST['cantidad_componente'];
    $cantidad_sobrante            = $_POST['cantidad_sobrante'];
    $lote_compuesto_derivado      = $_POST['lote_compuesto_derivado'];
    $caducidad_compuesto_derivado = $_POST['caducidad_compuesto_derivado'];
    $cve_usuario                  = $_POST['cve_usuario'];

    $radio_select                 = $_POST['radio_select'];
    $lote_componente_base         = $_POST['lote_componente_base'];
    $peso_componente_base         = $_POST['peso_componente_base'];
    $unidad_medida_comp_base      = $_POST['unidad_medida_comp_base'];
    $control_peso_comp_base       = $_POST['control_peso_comp_base'];
    $control_lotes_d_base         = $_POST['control_lotes_d_base'];
    $Caduca_d_base                = $_POST['Caduca_d_base'];
    $granel_d_base                = $_POST['granel_d_base'];
    $art_no_compuesto_val_base    = $_POST['art_compuesto_val_base'];
    $lote_compuesto_derivado_base = $_POST['lote_compuesto_derivado_base'];
    $existencia_PT                = $_POST['existencia_PT'];
    $caducidad_compuesto_derivado_base = $_POST['caducidad_compuesto_derivado_base'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


if($radio_select == 'pbase')
{
    $sql = "SELECT DISTINCT cve_almac FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
    $cve_almacen = mysqli_fetch_array($res)['cve_almac'];


    $sql = "SELECT IFNULL(Compuesto, 'N') as es_compuesto FROM c_articulo WHERE cve_articulo = '$art_no_compuesto_val_base'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
    $es_compuesto = mysqli_fetch_array($res)['es_compuesto'];

    $sql = "SELECT COUNT(*) as tiene_surtibles FROM c_articulo WHERE cve_articulo IN (SELECT Cve_Articulo FROM t_artcompuesto WHERE Cve_ArtComponente = '$art_no_compuesto_val_base') AND tipo_producto = 'ProductoNoSurtible'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
    $tiene_surtibles = mysqli_fetch_array($res)['tiene_surtibles'];


    $procedo_derivacion = true;
    if($es_compuesto == 'S' && $tiene_surtibles > 0)
    {
    $sql = "
     SELECT op.clave, op.control_lotes, op.Lote, op.LoteOT, op.Caduca, op.Caducidad, op.Cantidad, op.Cant_Prod AS Cant_OT, op.Num_cantidad AS cantnecesaria, op.Cantidad_Producida, op.ubicacion, IFNULL(op.existencia, 0) AS existencia, op.um, op.clave_almacen, op.Cve_Contenedor, IF(op.Num_cantidad > IFNULL(op.existencia, 0) OR IFNULL(op.existencia, 0) = 0, 1, 0) AS acepto FROM (

                        SELECT DISTINCT 
                            a.cve_articulo AS clave,
                            IFNULL(a.control_lotes, 'N') AS control_lotes,
                            e.cve_lote AS Lote,
                            t.Cve_Lote AS LoteOT,
                            IFNULL(a.Caduca, 'N') AS Caduca,
                            IF(a.Caduca = 'S', l.Caducidad, '') AS Caducidad,
                            ac.Cantidad AS Cantidad,
                            (ac.Cantidad_Producida*ac.Cantidad) AS Cantidad_Producida,
                            t.Cant_Prod,
                            ac.Cantidad*$cantidad_sobrante AS Num_cantidad,

                            #(SELECT cu.idy_ubica FROM V_ExistenciaProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND vp.cve_articulo = a.cve_articulo AND vp.cve_almac = '1' AND cu.Activo = 1 AND cu.AreaProduccion = 'S' AND vp.cve_ubicacion = t.idy_ubica GROUP BY vp.Existencia LIMIT 1) AS ubicacion,

                            t.idy_ubica AS ubicacion,

                            #tt.cantidad AS existencia 
                            e.Cve_Contenedor,
                            IFNULL(e.Existencia, 0) AS existencia, 
                            u.cve_umed AS um,
                            alm.clave AS clave_almacen,
                            t.Cve_Usuario AS cve_usuario,
                            IFNULL(t.ID_Proveedor, 0) AS ID_Proveedor
                        FROM t_artcompuesto ac
                            LEFT JOIN t_ordenprod  t  ON t.Folio_Pro = '$orden_id'
                    LEFT JOIN t_tarima tt ON tt.Fol_Folio = t.Folio_Pro
                    LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima 
                            LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = ac.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = t.Folio_Pro) #AND IFNULL(e.cve_lote, '') = IFNULL(td.cve_lote, '')
                            LEFT JOIN c_lotes l ON l.cve_articulo = ac.Cve_Articulo AND l.LOTE = e.cve_lote
                            LEFT JOIN c_articulo a ON a.cve_articulo = ac.Cve_Articulo
                            #LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
                            LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                            LEFT JOIN c_almacenp alm ON alm.id = e.cve_almac
                        WHERE #t.Folio_Pro = '$orden_id' AND 
                        e.cve_almac = alm.id 
                        AND ac.Cve_Articulo = e.cve_articulo
                        AND e.cve_ubicacion = '$idy_ubica'
                        AND ac.Cve_ArtComponente = '$art_no_compuesto_val_base' #AND ac.Cve_Articulo = a.Cve_Articulo #AND ch.CveLP = ''
                        #AND IFNULL(a.tipo_producto, '') = 'ProductoNoSurtible'

                UNION 


            SELECT DISTINCT ac.Cve_Articulo AS clave, 
                   '' AS control_lotes,
                   '' AS Lote, 
                   '' AS LoteOT,
                   '' AS Caduca,
                   '' AS Caducidad,
                   0  AS Cantidad, 
                   0 AS Cantidad_Producida,
                   0 AS Cant_Prod,
                   0 AS Num_cantidad,
                   '' AS ubicacion,
                   '' AS Cve_Contenedor,
                   0 AS existencia, 
                   '' AS um,
                   '' AS clave_almacen,
                   '' AS cve_usuario,
                   0 AS ID_Proveedor
             FROM t_artcompuesto ac
             LEFT JOIN t_ordenprod o ON o.Folio_pro = '$orden_id' #AND ac.Cve_ArtComponente = o.Cve_Articulo
             INNER JOIN td_ordenprod od ON od.Folio_pro = o.Folio_pro AND ac.Cve_Articulo = od.Cve_articulo
             LEFT JOIN c_articulo a ON a.cve_articulo = ac.Cve_Articulo 
             WHERE o.Folio_Pro = '$orden_id' #AND IFNULL(a.tipo_producto, '') = 'ProductoNoSurtible'
             AND ac.Cve_ArtComponente = '$art_no_compuesto_val_base'
             AND ac.Cve_Articulo NOT IN (SELECT Cve_Articulo FROM V_ExistenciaProduccion WHERE cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro))
             AND ac.Cve_Articulo NOT IN (SELECT Cve_Articulo FROM V_ExistenciaProduccion WHERE cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro) AND Existencia > 0 AND cve_ubicacion = '$idy_ubica') 


                ORDER BY Caducidad
            ) AS op WHERE op.ubicacion IS NOT NULL
 ";

        $sql_acepto = "SELECT SUM(acepto.acepto) AS acepto FROM ( ".$sql." ) AS acepto ";

        if (!($res_art = mysqli_query($conn, $sql_acepto)))
            echo "Falló la preparación K: (" . mysqli_error($conn) . ") ";

        $acepto = true;

        $row_art = mysqli_fetch_array($res_art);
        if($row_art['acepto'] > 0)
        {
            $acepto = false;
            $procedo_derivacion = false;
        }
        $sql_art = $sql;
        //$acepto = false;//elimino el proceso de restar componentes
        if($acepto)
        {//acepto

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
                $clave_almacen = $row_art_1['clave_almacen'];
                $cve_usuario = $row_art_1['cve_usuario'];

                if($idy_ubica == '') continue;
                //if($LoteOT == '') $LoteOT = $orden_id;

                //if($idy_ubica == "") $mensaje_error .= "El Producto {$clave} no se encuentra en una ubicación de Producción\n";
                if($Caduca == 'S' && $caducidadMIN != '' && $caducidadMIN != '0000-00-00' && $listo == false)
                {
                    $caducidad = $caducidadMIN;
                    $listo = true;
                }

                $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and cve_lote = '$Lote'";

                $sql_kardex = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$clave}', '{$Lote}', NOW(), '{$idy_ubica}', '{$orden_id}', {$cantidad}, 8, '{$cve_usuario}','{$cve_almacen}')";
                $res_kardex = mysqli_query($conn, $sql_kardex);


                if($Cve_Contenedor != '')
                {
                    $sql = "UPDATE ts_existenciatarima SET existencia = existencia - $cantidad WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$clave' and lote = '$Lote' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor')";

                    $sql_kardex = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES 
                    ((SELECT MAX(id) FROM t_cardex), '$clave_almacen', (SELECT IDContenedor FROM c_charolas WHERE clave_contenedor = '$Cve_Contenedor'), CURDATE(), '{$idy_ubica}', '{$orden_id}', 8, '{$cve_usuario}', 'O')";
                    $res_kardex = mysqli_query($conn, $sql_kardex);
                }
                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación G: (" . mysqli_error($conn) . ") ";
                }
            }
        }
/////////////////////////////////////////////////////

    }
    //$procedo_derivacion = true;
    if($procedo_derivacion == true)
    {
    $caducidad_arr = explode("-", $caducidad_compuesto_derivado_base);
    $caducidad_compuesto_derivado = $caducidad_arr[2]."-".$caducidad_arr[1]."-".$caducidad_arr[0];

    $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $cantidad_sobrante WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND cve_lote = '{$lote_compuesto}'";
    $cantxpeso = $cantidad_sobrante;
    if($unidad_medida_comp_base == 'H87' && $control_peso_comp_base == 'S' && $peso_componente > 0)
    {
        //$cantidad_sobrante = $cantidad_sobrante*$peso_componente;
        $cantxpeso = $cantidad_sobrante*$peso_componente;
        $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $cantxpeso WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cod_art_compuesto' AND cve_lote = '{$lote_compuesto}'";
    }

    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación: 5(" . mysqli_error($conn) . ") --> ".$sql;

    $sql = "SELECT o.Cve_Articulo, u.mav_cveunimed AS um, a.control_peso, a.peso
            FROM t_ordenprod o
            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
            LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
            WHERE o.Folio_Pro = '$orden_id'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
    $row_prod = mysqli_fetch_array($res);
    $unidad_medida = $row_prod['um'];
    $control_peso_base = $row_prod['control_peso'];
    $peso_componente = $row_prod['peso'];



    $sql = "SELECT DISTINCT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
    $ID_Proveedor = mysqli_fetch_array($res)['ID_Proveedor'];


    $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$cod_art_compuesto}', '{$lote_componente}', NOW(), 'PD_{$orden_id}', '{$idy_ubica}', {$cantxpeso}, 8, '{$cve_usuario}','{$cve_almacen}')";
    $res = mysqli_query($conn, $sql);

    $sql = "SELECT COUNT(*) as existe FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$art_no_compuesto_val_base' AND cve_lote = '{$lote_compuesto}'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
    $existe_producto = mysqli_fetch_array($res)['existe'];




    $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia + $cantidad_sobrante WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$art_no_compuesto_val_base' AND cve_lote = '{$lote_compuesto}'";

    if($existe_producto == 0)
    {
        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote) VALUES ('{$art_no_compuesto_val_base}', '{$lote_compuesto}')";
        if($control_lotes_d_base == 'S' && $Caduca_d_base == 'S')
            $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$art_no_compuesto_val_base}', '{$lote_compuesto}', '{$caducidad_compuesto_derivado}')";
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ";

       $sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor, ClaveEtiqueta) VALUES ('{$cve_almacen}', {$idy_ubica}, '{$art_no_compuesto_val_base}', '{$lote_compuesto}', {$cantidad_sobrante}, {$ID_Proveedor}, '{$orden_id}') ON DUPLICATE KEY UPDATE Existencia = Existencia + {$cantidad_sobrante}";
    }
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación: 5(" . mysqli_error($conn) . ") --> ".$sql;

    $sql = "INSERT IGNORE INTO t_tipomovimiento(id_TipoMovimiento, nombre) VALUES (25, 'Producto Derivado')";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";

    $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$art_no_compuesto_val_base}', '{$lote_compuesto}', NOW(), 'PD_{$orden_id}', '{$idy_ubica}', {$cantidad_sobrante}, 25, '{$cve_usuario}','{$cve_almacen}')";
    $res = mysqli_query($conn, $sql);

    echo ($existencia_PT-$cantidad_sobrante);
    }
    else
        echo "NoStockNoSurtibles";
}
else if($radio_select == 'excedente')
{
    $caducidad_arr = explode("-", $caducidad_compuesto_derivado_base);
    $caducidad_compuesto_derivado = $caducidad_arr[2]."-".$caducidad_arr[1]."-".$caducidad_arr[0];

    //if($unidad_medida_comp_base == 'H87' && $control_peso_comp_base == 'S' && $peso_componente > 0)
    //    $cantidad_sobrante = $cantidad_sobrante*$peso_componente;

    $sql = "SELECT DISTINCT cve_almac FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
    $cve_almacen = mysqli_fetch_array($res)['cve_almac'];

    $sql = "SELECT DISTINCT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
    $ID_Proveedor = mysqli_fetch_array($res)['ID_Proveedor'];


    $sql = "SELECT COUNT(*) as existe FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$art_no_compuesto_val_base' AND cve_lote = '{$lote_compuesto}'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
    $existe_producto = mysqli_fetch_array($res)['existe'];


    $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia + $cantidad_sobrante WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$art_no_compuesto_val_base' AND cve_lote = '{$lote_compuesto}'";

    if($existe_producto == 0)
    {
        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote) VALUES ('{$art_no_compuesto_val_base}', '{$lote_compuesto}')";
        if($control_lotes_d_base == 'S' && $Caduca_d_base == 'S')
            $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$art_no_compuesto_val_base}', '{$lote_compuesto}', '{$caducidad_compuesto_derivado}')";
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ";

       $sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor, ClaveEtiqueta) VALUES ('{$cve_almacen}', {$idy_ubica}, '{$art_no_compuesto_val_base}', '{$lote_compuesto}', {$cantidad_sobrante}, {$ID_Proveedor}, '{$orden_id}') ON DUPLICATE KEY UPDATE Existencia = Existencia + {$cantidad_sobrante}";
    }
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación: 5(" . mysqli_error($conn) . ") --> ".$sql;

    $sql = "INSERT IGNORE INTO t_tipomovimiento(id_TipoMovimiento, nombre) VALUES (28, 'Producto Excedente')";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";

    $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$cod_art_compuesto}', '{$lote_componente}', NOW(), 'PE_{$orden_id}', '{$idy_ubica}', {$cantidad_sobrante}, 28, '{$cve_usuario}','{$cve_almacen}')";
    $res = mysqli_query($conn, $sql);

    echo 1;
}
else
{
    $caducidad_arr = explode("-", $caducidad_compuesto_derivado);
    $caducidad_compuesto_derivado = $caducidad_arr[2]."-".$caducidad_arr[1]."-".$caducidad_arr[0];

    if($unidad_medida_comp == 'H87' && $control_peso_comp == 'S' && $peso_componente > 0)
    {
/*
        $sql = "SELECT DISTINCT
                IF(um.mav_cveunimed = 'H87' AND a.control_peso = 'S', ROUND((TRUNCATE(IFNULL(s.Cantidad-s.revisadas, 0), 4)*IFNULL(a.peso, 0) - ((op.Cant_Prod)*(TRUNCATE(IFNULL(ac.Cantidad, 0), 4)))), 4),IF(ROUND((TRUNCATE(IFNULL(s.Cantidad-s.revisadas, 0), 4) - ((op.Cant_Prod)*(TRUNCATE(IFNULL(ac.Cantidad, 0), 4)))), 4) = '-0', 0, ROUND((TRUNCATE(IFNULL(s.Cantidad-s.revisadas, 0), 4) - ((op.Cant_Prod)*(TRUNCATE(IFNULL(ac.Cantidad, 0), 4)))), 4))) AS cantidad
            FROM td_ordenprod  o
            LEFT JOIN t_ordenprod op ON op.Folio_Pro = o.Folio_Pro
            LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro) #AND e.cve_lote = o.Cve_Lote
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            INNER JOIN td_surtidopiezas s ON s.fol_folio = o.Folio_Pro AND s.cve_articulo = o.Cve_Articulo #AND s.LOTE = IFNULL(o.Cve_Lote, '')
            LEFT JOIN c_lotes l ON l.cve_articulo = s.cve_articulo AND l.Lote = s.Lote
            LEFT JOIN t_artcompuesto ac ON ac.Cve_Articulo = s.Cve_articulo AND ac.Cve_ArtComponente = op.Cve_Articulo
            WHERE o.Folio_Pro = '$orden_id' #AND a.Compuesto != 'S' #AND e.Cve_Contenedor = ''
             AND e.cve_ubicacion = $idy_ubica 
            ORDER BY BL, clave, caducidad ASC";
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación: 5(" . mysqli_error($conn) . ") --> ".$sql;
        $cantidad_d = mysqli_fetch_array($res)['cantidad'];

        $cantidad_componente = $cantidad_d - $cantidad_componente;
*/
        $cantidad_componente = round($cantidad_componente/$peso_componente, 5);
    }

    $sql = "UPDATE ts_existenciapiezas SET Existencia = TRUNCATE(Existencia - $cantidad_componente, 5) WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$componente_derivado_select' AND cve_lote = '{$lote_componente}'";

    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación: 5(" . mysqli_error($conn) . ") --> ".$sql;

    $sql = "SELECT DISTINCT cve_almac FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
    $cve_almacen = mysqli_fetch_array($res)['cve_almac'];

    $sql = "SELECT DISTINCT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '$orden_id'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
    $ID_Proveedor = mysqli_fetch_array($res)['ID_Proveedor'];


    $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$componente_derivado_select}', '{$lote_componente}', NOW(), 'PD_{$orden_id}', '{$idy_ubica}', {$cantidad_componente}, 8, '{$cve_usuario}','{$cve_almacen}')";
    $res = mysqli_query($conn, $sql);

    //aqui marco cuanto se ha descontado después de terminar la producción
    $sql = "UPDATE td_surtidopiezas SET revisadas = revisadas + $cantidad_componente WHERE fol_folio = '$orden_id' AND cve_articulo = '$componente_derivado_select' AND lote = '{$lote_componente}'";

    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación: 5(" . mysqli_error($conn) . ") --> ".$sql;

    $sql = "SELECT COUNT(*) as existe FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$art_sobrante' AND cve_lote = '{$lote_compuesto_derivado}'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
    $existe_producto = mysqli_fetch_array($res)['existe'];


    $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia + $cantidad_sobrante WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$art_sobrante' AND cve_lote = '{$lote_compuesto_derivado}'";

    if($existe_producto == 0)
    {
        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote) VALUES ('{$art_sobrante}', '{$lote_compuesto_derivado}')";
        if($control_lotes_d == 'S' && $Caduca_d == 'S')
            $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$art_sobrante}', '{$lote_compuesto_derivado}', '{$caducidad_compuesto_derivado}')";
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ";

       $sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor, ClaveEtiqueta) VALUES ('{$cve_almacen}', {$idy_ubica}, '{$art_sobrante}', '{$lote_compuesto_derivado}', {$cantidad_sobrante}, {$ID_Proveedor}, '{$orden_id}') ON DUPLICATE KEY UPDATE Existencia = Existencia + {$cantidad_sobrante}";
    }
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación: 5(" . mysqli_error($conn) . ") --> ".$sql;

    $sql = "INSERT IGNORE INTO t_tipomovimiento(id_TipoMovimiento, nombre) VALUES (25, 'Producto Derivado')";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";

    $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$art_sobrante}', '{$lote_compuesto_derivado}', NOW(), 'PD_{$orden_id}', '{$idy_ubica}', {$cantidad_sobrante}, 25, '{$cve_usuario}','{$cve_almacen}')";
    $res = mysqli_query($conn, $sql);

    echo 1;
}

}

if( $_POST['action'] == 'productoSobrante' ) 
{
    $orden_id              = $_POST['orden_id'];
    $cantidad_art_sobrante = $_POST['cantidad_art_sobrante'];
    $cod_art_compuesto     = strtoupper($_POST['cod_art_compuesto']);
    $art_sobrante          = $_POST['art_sobrante'];
    $lote_compuesto        = $_POST['lote_compuesto'];
    $derivado_merma        = $_POST['derivado_merma'];
    $cve_usuario           = $_POST['cve_usuario'];
    $cantidad_faltante     = $_POST['cantidad_faltante'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

/*
    $sql = "SELECT COUNT(*) as existe FROM t_artcomp_sobrante WHERE Cve_Articulo = '$art_sobrante' AND Cve_ArtComponente = '$cod_art_compuesto' AND Lote = '{$orden_id}'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    $existe_producto = mysqli_fetch_array($res)['existe'];

    $sql = "UPDATE t_artcomp_sobrante SET Cantidad = Cantidad + $cantidad_art_sobrante WHERE Cve_Articulo = '$art_sobrante' AND Cve_ArtComponente = '$cod_art_compuesto' AND Lote = '{$orden_id}'";

        $sql = "SELECT DISTINCT 
            (SELECT cu.idy_ubica FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.Activo = 1 AND cu.AreaProduccion = 'S' AND vp.cve_articulo = ac.Cve_ArtComponente AND vp.tipo = 'ubicacion' GROUP BY vp.Existencia LIMIT 1) AS ubicacion,
            (SELECT Cve_lote FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}') AS Lote,
             IFNULL(DATE_FORMAT(MIN(l.Caducidad), '%d-%m-%Y'), '') AS Caducidad
        FROM t_artcompuesto ac
            LEFT JOIN t_ordenprod  t  ON t.Cve_Articulo = ac.Cve_ArtComponente
            LEFT JOIN td_ordenprod td ON td.Folio_Pro = t.Folio_Pro
            LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = ac.Cve_ArtComponente AND e.cve_almac = (SELECT cve_almacenp FROM c_almacen WHERE cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = td.Folio_Pro))
            LEFT JOIN c_lotes l ON l.cve_articulo = ac.Cve_ArtComponente AND l.LOTE = e.cve_lote
            LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_Articulo
            LEFT JOIN c_unimed u ON u.cve_umed = ac.cve_umed
        WHERE ac.Cve_ArtComponente = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}') AND ac.Cve_Articulo = td.Cve_Articulo";
*/
    $resul = 0;

        $sql = "SELECT  o.idy_ubica AS ubicacion, 
                        o.Cve_Lote AS Lote,
                        o.cve_almac,
                        IFNULL(DATE_FORMAT(MIN(l.Caducidad), '%d-%m-%Y'), '') AS Caducidad
                FROM t_ordenprod o
                LEFT JOIN t_artcompuesto ac ON o.Cve_Articulo = ac.Cve_ArtComponente
                LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = ac.Cve_ArtComponente AND e.cve_almac = o.cve_almac
                LEFT JOIN c_lotes l ON l.cve_articulo = ac.Cve_ArtComponente AND l.LOTE = e.cve_lote
                WHERE o.Folio_Pro = '{$orden_id}'"; 
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
        $row_sobrante = mysqli_fetch_array($res);
        $idy_ubica      = $row_sobrante['ubicacion'];
        $lote_compuesto = $row_sobrante['Lote'];
        $caducidad      = $row_sobrante['Caducidad'];
        $cve_almac      = $row_sobrante['cve_almac'];

    if($derivado_merma == 'derivado' || $cantidad_faltante == 0)
    {

            $sql = "SELECT COUNT(*) as existe FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$art_sobrante' AND cve_lote = '{$lote_compuesto}'";
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
            $existe_producto = mysqli_fetch_array($res)['existe'];

        if($cantidad_faltante > 0)
        {
            $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia + $cantidad_art_sobrante WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$art_sobrante' AND cve_lote = '{$lote_compuesto}'";


            if($existe_producto == 0)
            {
                $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$art_sobrante}', '{$orden_id}', '{$caducidad}')";
                if (!($res = mysqli_query($conn, $sql)))
                    echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ";

                $sql = "SELECT DISTINCT cve_almac FROM ts_existenciapiezas WHERE idy_ubica = '$idy_ubica'";
                if (!($res = mysqli_query($conn, $sql)))
                    echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
                $cve_almacen = mysqli_fetch_array($res)['cve_almac'];

               $sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) VALUES ('{$cve_almacen}', {$idy_ubica}, '{$art_sobrante}', '{$lote_compuesto}', {$cantidad_art_sobrante}, 0) ON DUPLICATE KEY UPDATE Existencia = Existencia + {$cantidad_art_sobrante}";
            }
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación: 5(" . mysqli_error($conn) . ") --> ".$sql;

            //$sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - $cantidad_art_sobrante WHERE idy_ubica = '$idy_ubica' AND cve_lote != '{$lote_compuesto}'";
            //if (!($res = mysqli_query($conn, $sql)))
                //echo "Falló la preparación 3: (" . mysqli_error($conn) . ") --> ".$sql;

            $sql = "UPDATE t_ordenprod SET Cant_Prod = Cantidad WHERE Folio_Pro = '{$orden_id}'";
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";

            $sql = "SELECT DISTINCT ROUND((Cantidad_Producida*Cantidad / Cantidad), 0) AS Cantidad_Producida
                    FROM t_artcompuesto
                    WHERE Cve_ArtComponente = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$orden_id')";
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
            $cantidad_producida = mysqli_fetch_array($res)['Cantidad_Producida'];

            $resul = $cantidad_producida;

            $sql = "INSERT IGNORE INTO t_tipomovimiento(id_TipoMovimiento, nombre) VALUES (25, 'Producto Derivado')";
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";

            $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$art_sobrante}', '{$lote_compuesto}', NOW(), 'PD_{$orden_id}', '{$idy_ubica}', {$cantidad_art_sobrante}, 25, '{$cve_usuario}','{$cve_almac}')";
            $res = mysqli_query($conn, $sql);
        }
        else
        {
            $sql = "SELECT DISTINCT ROUND((Cantidad_Producida*Cantidad / Cantidad), 0) AS Cantidad_Producida
                    FROM t_artcompuesto
                    WHERE Cve_ArtComponente = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$orden_id')";
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
            $cantidad_producida = mysqli_fetch_array($res)['Cantidad_Producida'];

            $resul = $cantidad_producida;

            $sql = "INSERT IGNORE INTO t_tipomovimiento(id_TipoMovimiento, nombre) VALUES (27, 'Producto Sobrante')";
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";

            $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$art_sobrante}', '{$lote_compuesto}', NOW(), 'PS_{$orden_id}', '{$idy_ubica}', {$cantidad_art_sobrante}, 27, '{$cve_usuario}','{$cve_almac}')";
            $res = mysqli_query($conn, $sql);
        }
    }
    else
    {
            $sql = "UPDATE t_ordenprod SET Cant_Prod = Cantidad WHERE Folio_Pro = '{$orden_id}'";
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";

            $sql = "INSERT IGNORE INTO t_tipomovimiento(id_TipoMovimiento, nombre) VALUES (26, 'Merma OT')";
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";

            $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$cod_art_compuesto}', '{$lote_compuesto}', NOW(), 'PM_{$orden_id}', '{$idy_ubica}', {$cantidad_faltante}, 26, '{$cve_usuario}','{$cve_almac}')";
            $res = mysqli_query($conn, $sql);

    }
    echo $resul;

}

if( $_POST['action'] == 'RegistrarMermaOT' ) 
{
    $orden_id                           = $_POST['orden_id'];
    $idy_ubica                          = $_POST['idy_ubica'];
    $cve_articulo_componente            = $_POST['cve_articulo_componente'];
    $cantidad_sobrante_merma            = $_POST['cantidad_sobrante_merma'];
    $lote_compuesto_derivado_merma      = $_POST['lote_compuesto_derivado_merma'];
    $caducidad_compuesto_derivado_merma = $_POST['caducidad_compuesto_derivado_merma'];
    $lote_compuesto                     = $_POST['lote_compuesto'];
    $art_caduca                         = $_POST['art_caduca'];

    $resul = 0;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT DISTINCT  IF(IFNULL(a.control_peso, 'N') = 'S' AND u.mav_cveunimed = 'H87', 1, 0) as es_pieza, IFNULL(a.peso, 1) as peso
            FROM c_articulo a 
            LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
            WHERE a.cve_articulo = '$cve_articulo_componente'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
        $row = mysqli_fetch_array($res);
        $es_pieza = $row['es_pieza'];
        $peso = $row['peso'];

    $sql = "SELECT cve_almac, Cve_Usuario FROM t_ordenprod WHERE Folio_Pro = '{$orden_id}'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
        $row = mysqli_fetch_array($res);
        $cve_almac = $row['cve_almac'];
        $Cve_Usuario = $row['Cve_Usuario'];

        $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote) VALUES ('$cve_articulo_componente', '$lote_compuesto')";
        if($art_caduca == "S")
        {
            $caducidad_arr = explode("-", $caducidad_compuesto_derivado_merma);
            $caducidad = $caducidad_arr[2]."-".$caducidad_arr[1]."-".$caducidad_arr[0];
            $sql = "INSERT IGNORE INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('$cve_articulo_componente', '$lote_compuesto', '$caducidad')";
        }
        if (!($res = mysqli_query($conn, $sql)))


        $cantidad_kardex = $cantidad_sobrante_merma;
        if($es_pieza)
            $cantidad_sobrante_merma = round($cantidad_sobrante_merma/$peso, 5);

    $sql = "UPDATE ts_existenciapiezas SET Existencia = TRUNCATE(Existencia - $cantidad_sobrante_merma, 5) WHERE idy_ubica = '$idy_ubica' AND cve_articulo = '$cve_articulo_componente' AND cve_lote = '{$lote_compuesto_derivado_merma}'";

    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación: 5(" . mysqli_error($conn) . ") --> ".$sql;


    $sql = "UPDATE td_surtidopiezas SET revisadas = revisadas + $cantidad_sobrante_merma WHERE fol_folio = '$orden_id' AND cve_articulo = '$cve_articulo_componente' AND lote = '{$lote_compuesto_derivado_merma}'";

    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación: 5(" . mysqli_error($conn) . ") --> ".$sql;

    $sql = "INSERT IGNORE INTO t_tipomovimiento(id_TipoMovimiento, nombre) VALUES (26, 'Merma OT')";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";

    $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$cve_articulo_componente}', '{$lote_compuesto}', NOW(), 'PM_{$orden_id}', '{$idy_ubica}', {$cantidad_sobrante_merma}, 0, (0-$cantidad_sobrante_merma), 26, '{$Cve_Usuario}','{$cve_almac}')";
    $res = mysqli_query($conn, $sql);

    echo $resul;
}

if( $_POST['action'] == 'modificar_lote' ) 
{
    $folio      = $_POST['folio'];
    $lote_usado = $_POST['lote_usado'];
    $lote_cambiar = $_POST['lote_cambiar'];
    $art_compuesto = strtoupper($_POST['art_compuesto']);

    $resul = 0;

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql = "SELECT COUNT(*) as existe FROM c_lotes WHERE Lote = '{$lote_cambiar}' AND cve_articulo = '{$art_compuesto}'";
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        $existe = mysqli_fetch_array($res)['existe'];


        $sql = "SELECT COUNT(*) as existe FROM t_ordenprod WHERE Cve_Lote = '{$lote_cambiar}'";
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        $existe2 = mysqli_fetch_array($res)['existe'];

        if(/*!$existe && */ !$existe2)
        {
            if(!$existe)
            {
                $sql = "UPDATE c_lotes SET Lote = '{$lote_cambiar}' WHERE Lote = '{$lote_usado}' AND cve_articulo = '{$art_compuesto}'";
                if (!($res = mysqli_query($conn, $sql)))
                    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

            if(!$existe2)
            {
                $sql = "UPDATE t_ordenprod SET Cve_Lote = '{$lote_cambiar}' WHERE Cve_Lote = '{$lote_usado}' AND Cve_Articulo = '{$art_compuesto}' AND Folio_Pro = '{$folio}'";
                if (!($res = mysqli_query($conn, $sql)))
                    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

            $sql = "UPDATE ts_existenciapiezas SET cve_lote = '{$lote_cambiar}' WHERE cve_lote = '{$lote_usado}' AND cve_articulo = '{$art_compuesto}'";
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";

            $sql = "UPDATE ts_existenciatarima SET lote = '{$lote_cambiar}' WHERE lote = '{$lote_usado}' AND cve_articulo = '{$art_compuesto}'";
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";


            $sql = "UPDATE t_tarima SET lote = '{$lote_cambiar}' WHERE lote = '{$lote_usado}' AND cve_articulo = '{$art_compuesto}' AND Fol_Folio = '{$folio}'";
            if (!($res = mysqli_query($conn, $sql)))
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";

            $resul = 1;
        }

    echo $resul;
}

if( $_POST['action'] == 'cambiarBL' ) 
{
    $folio     = $_POST['orden_id'];
    $idy_ubica = $_POST['idy_ubica'];

    $resul = "";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql = "UPDATE t_ordenprod SET idy_ubica = '".$idy_ubica."' WHERE Folio_Pro = '{$folio}'";
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";

    echo 1;
}

if( $_POST['action'] == 'cambiarBLDest' ) 
{
    $folio     = $_POST['orden_id'];
    $idy_ubica = $_POST['idy_ubica'];

    $resul = "";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql = "UPDATE t_ordenprod SET idy_ubica_dest = '".$idy_ubica."' WHERE Folio_Pro = '{$folio}'";
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";

    echo 1;
}

if( $_POST['action'] == 'cambiar_status' ) 
{
    $folio      = $_POST['folio'];

    $resul = "";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql = "UPDATE t_ordenprod SET Status = 'I' WHERE Folio_Pro = '{$folio}'";
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";

    echo 1;
}


if( $_POST['action'] == 'asignar_cliente' ) 
{
    $folio      = $_POST['folio'];
    $clave_cliente = $_POST['clave_cliente'];

    $resul = "";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql = "UPDATE th_pedido SET Cve_clte = '{$clave_cliente}' WHERE Fol_folio = '{$folio}'";
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";

    echo 1;
}

if( $_POST['action'] == 'modificar_caducidad' ) 
{
    $folio      = $_POST['folio'];
    $lote_usado = $_POST['lote_usado'];
    $caducidad  = $_POST['caducidad'];
    $art_compuesto = strtoupper($_POST['art_compuesto']);

    $resul = "";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql = "UPDATE c_lotes SET Caducidad = STR_TO_DATE('{$caducidad}', '%d-%m-%Y') WHERE cve_articulo = '{$art_compuesto}' AND Lote = '{$lote_usado}'";
        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";

    echo 1;
}

if( $_POST['action'] == 'verificar_cronometro' ) 
{
    $folio      = $_POST['folio'];

    $resul = "";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql = "SELECT IFNULL(Hora_Ini, '') AS Hora_Ini, DATE_FORMAT(Hora_Ini, '%d/%m/%Y') FECHA_ACTUAL, DATE_FORMAT(Hora_Ini, '%H:%i:%s') HORA_ACTUAL FROM t_ordenprod WHERE Folio_Pro = '{$folio}'";

        if (!($res = mysqli_query($conn, $sql)))
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        $resul = mysqli_fetch_array($res);

        $hora_ini = $resul['Hora_Ini'];
    if($hora_ini == "")
    {
      $sql = "SELECT DATE_FORMAT(CURDATE(), '%d/%m/%Y') FECHA_ACTUAL, DATE_FORMAT(NOW(), '%H:%i:%s') HORA_ACTUAL";
      $query = mysqli_query($conn, $sql);
      $resul = mysqli_fetch_assoc($query);

      $sql = "UPDATE t_ordenprod SET Hora_Ini = NOW() WHERE Folio_Pro = '{$folio}'";
      $query = mysqli_query($conn, $sql);
    }
      $fecha = $resul['FECHA_ACTUAL'];
      $hora_inicio = $resul['HORA_ACTUAL'];
      $resul = $fecha."|".$hora_inicio;

    echo $resul;
}

if( $_POST['action'] == 'terminar_produccion' ) 
{
    $folio   = $_POST['folio'];
    $modo    = $_POST['modo'];
    $resul   = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


    $sql = "SELECT * FROM td_surtidopiezas WHERE fol_folio = '{$folio}'";
    $res = mysqli_query($conn, $sql);
    $surtido = mysqli_num_rows($res);

    $sql = "SELECT IF(Cantidad = Cant_Prod, 1, 0) as terminado from t_ordenprod where Folio_Pro = '{$folio}'";
    $res = mysqli_query($conn, $sql);
    $row_terminado = mysqli_fetch_array($res);
    $terminado     = $row_terminado["terminado"];

    if($terminado == 0 && $modo == 'icono_engranaje')
    {
        echo "";
    }
    else if($surtido == 0)
    {
        echo "Pedido_Sin_Surtir";
    }
    else
    {
        $sql = "SELECT IFNULL(Tipo, '') as Tipo, Cve_Articulo, Cve_Lote FROM t_ordenprod WHERE Folio_Pro = '$folio'";
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $row_ot = mysqli_fetch_array($res);
        $Tipo            = $row_ot["Tipo"];
        $Cve_Articulo_OT = $row_ot["Cve_Articulo"];
        $Cve_Lote_OT     = $row_ot["Cve_Lote"];

        if($Tipo == 'IMP_LP')
        {
            $sql = "UPDATE th_pedido set status='T' WHERE Fol_folio = '$folio'";
            if (!$res = mysqli_query($conn, $sql)) 
            {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

            $sql = "UPDATE th_subpedido set status='T' WHERE fol_folio = '$folio'";
            if (!$res = mysqli_query($conn, $sql)) 
            {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }
        }

      $sql = "UPDATE t_ordenprod SET Status = 'T', Hora_Fin = NOW() WHERE Folio_Pro = '{$folio}'";
      $query = mysqli_query($conn, $sql);

        $sql = "SELECT Fol_folio as folio_rel FROM th_pedido WHERE Ship_Num = '$folio'";
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $folio_rel = mysqli_fetch_array($res)["folio_rel"];

        if($folio_rel)
        {
            $sqlCount = "UPDATE th_pedido set BanEmpaque='0' where Fol_folio = '$folio_rel'";
            if (!$res = mysqli_query($conn, $sqlCount)) 
            {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

            $sqlCount = "UPDATE td_pedido set cve_lote = '$folio' where Fol_folio = '$folio_rel'";
            if (!$res = mysqli_query($conn, $sqlCount)) 
            {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

        }

        $sql = "SELECT DISTINCT Cve_Lote as Lote FROM t_ordenprod WHERE Folio_Pro = '$folio' LIMIT 1";
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $Lote = mysqli_fetch_array($res)["Lote"];

        $sql = "SELECT DATE_FORMAT(Caducidad, '%d-%m-%Y') as Caducidad FROM c_lotes WHERE Lote = '$Lote' LIMIT 1";
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $Caducidad = mysqli_fetch_array($res)["Caducidad"];

        //**************************************************************************************************
        //**************************************************************************************************

        $sql = "SELECT cve_almac, Cve_Articulo, Cve_Lote, Cant_Prod, Cve_Usuario FROM t_ordenprod WHERE Folio_Pro = '{$folio}'";
        $res = mysqli_query($conn, $sql);
        $kardex = mysqli_fetch_assoc($res);
        extract($kardex);

        //$sql = "SELECT V_ExistenciaGral.cve_ubicacion AS idy_ubica, IFNULL((SELECT IDContenedor FROM c_charolas WHERE CveLp = V_ExistenciaGral.Cve_Contenedor), '') AS IDContenedor FROM V_ExistenciaGralProduccion V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = '{$Cve_Articulo}' AND V_ExistenciaGral.cve_lote = '{$Cve_Lote}' LIMIT 1";
        $sql = "SELECT V_ExistenciaGral.cve_ubicacion AS idy_ubica, IFNULL((SELECT ntarima FROM t_tarima WHERE fol_folio = '$folio'), '') AS IDContenedor FROM V_ExistenciaGralProduccion V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = '{$Cve_Articulo}' AND V_ExistenciaGral.cve_lote = '{$Cve_Lote}' LIMIT 1";
        $res = mysqli_query($conn, $sql);
        $ubicacion = mysqli_fetch_assoc($res);
        extract($ubicacion);


        $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$Cve_Articulo}', '{$Cve_Lote}', NOW(), 'PT_{$folio}', '{$idy_ubica}', {$Cant_Prod}, 14, '{$Cve_Usuario}','{$cve_almac}')";
        $res = mysqli_query($conn, $sql);

        if($IDContenedor)
        {
            $sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES 
            ((SELECT MAX(id) FROM t_cardex), (SELECT clave FROM c_almacenp WHERE id = '{$cve_almac}'), {$IDContenedor}, CURDATE(),'PT_{$folio}', '{$idy_ubica}', 14, '{$Cve_Usuario}', 'I')";
            $res = mysqli_query($conn, $sql);
        }
        //**************************************************************************************************
        //**************************************************************************************************

        $sql = "SELECT DISTINCT
                IF(um.mav_cveunimed = 'H87' AND a.control_peso = 'S', ROUND((TRUNCATE(IFNULL(s.Cantidad-s.revisadas, 0), 5)*IFNULL(a.peso, 0) - ((op.Cant_Prod)*(TRUNCATE(IFNULL(ac.Cantidad, 0), 5)))), 4),ROUND((TRUNCATE(IFNULL(s.Cantidad-s.revisadas, 0), 5) - ((op.Cant_Prod)*(TRUNCATE(IFNULL(ac.Cantidad, 0), 5)))), 4)) AS cantidad,
                s.Cve_articulo AS clave,
                IFNULL(a.des_articulo, '') AS descripcion,
                IFNULL(s.Lote, '') AS lote, 
                IFNULL(DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS caducidad,
                u.CodigoCSD AS BL,
                um.mav_cveunimed as unidad_medida,
                a.control_peso as control_peso_comp,
                IFNULL(e.Cve_Contenedor, '') AS LP,
                IFNULL(a.peso, 0) as peso,
                o.Folio_Pro AS folio
            FROM td_ordenprod  o
            LEFT JOIN t_ordenprod op ON op.Folio_Pro = o.Folio_Pro
            LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = o.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = o.Folio_Pro) 
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_articulo a ON a.cve_articulo = o.Cve_Articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            INNER JOIN td_surtidopiezas s ON s.fol_folio = o.Folio_Pro AND s.cve_articulo = o.Cve_Articulo #AND s.LOTE = IFNULL(o.Cve_Lote, '')
            LEFT JOIN c_lotes l ON l.cve_articulo = s.cve_articulo AND l.Lote = s.Lote
            LEFT JOIN t_artcompuesto ac ON ac.Cve_Articulo = s.Cve_articulo AND ac.Cve_ArtComponente = op.Cve_Articulo
            WHERE o.Folio_Pro = '{$folio}' 
             AND e.cve_ubicacion = op.idy_ubica
            ORDER BY BL, clave, caducidad ASC";
        $res = mysqli_query($conn, $sql);
        $options_derivado = '<option value="">Seleccione Componente</option>';
        while($options = mysqli_fetch_assoc($res))
        {
            extract($options);
            $options_derivado .= '<option value="'.$clave.';;:::::;;'.$cantidad.'" data-peso="'.$peso.'" data-unidad_medida="'.$unidad_medida.'" data-control_peso="'.$control_peso_comp.'" data-lote="'.$lote.'">( '.$clave.' ) - '.$descripcion.'</option>';
        }


        //**************************************************************************************************
        //**************************************************************************************************

        $sql = "SELECT DISTINCT
                #IF(um.mav_cveunimed = 'H87' AND a.control_peso = 'S', ROUND((TRUNCATE(IFNULL(e.Existencia, 0), 4)*IFNULL(a.peso, 0)), 4),ROUND((TRUNCATE(IFNULL(e.Existencia, 0), 4)), 4)) AS cantidad,
                0 AS cantidad,
                acf.Cve_articulo AS clave,
                IFNULL(acf.des_articulo, '') AS descripcion,
                #IFNULL(e.cve_lote, '') AS lote, 
                #IFNULL(DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS caducidad,
                '' AS lote,
                '' AS caducidad,
                u.CodigoCSD AS BL,
                IFNULL(a.control_lotes, 'N') AS control_lotes, 
                IFNULL(a.Caduca, 'N') AS Caduca,
                um.mav_cveunimed AS unidad_medida,
                acf.control_peso AS control_peso_comp,
                IFNULL(e.Cve_Contenedor, '') AS LP,
                IFNULL(acf.peso, 0) AS peso,
                acf.clasificacion,
                op.Folio_Pro AS folio, 
                IF(um.mav_cveunimed='H87', 'S', 'N') AS es_pieza
            FROM t_ordenprod op
            LEFT JOIN td_ordenprod o ON op.Folio_Pro = o.Folio_Pro
            LEFT JOIN V_ExistenciaProduccion e ON e.cve_articulo = op.Cve_Articulo AND e.cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = op.Folio_Pro) 
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_articulo a ON  a.cve_articulo = op.Cve_Articulo 
            LEFT JOIN c_articulo acf ON acf.clasificacion = a.cve_articulo AND acf.cve_articulo != op.Cve_Articulo
            LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
            LEFT JOIN c_sgpoarticulo cf ON cf.cve_sgpoart = acf.clasificacion
            #INNER JOIN td_surtidopiezas s ON s.fol_folio = o.Folio_Pro AND s.cve_articulo = o.Cve_Articulo #AND s.LOTE = IFNULL(o.Cve_Lote, '')
            LEFT JOIN c_lotes l ON l.cve_articulo = e.cve_articulo AND l.Lote = e.cve_lote
            #LEFT JOIN t_artcompuesto ac ON ac.Cve_Articulo = s.Cve_articulo AND ac.Cve_ArtComponente = op.Cve_Articulo
            WHERE op.Folio_Pro = '{$folio}' #AND acf.clasificacion = a.clasificacion AND acf.clasificacion = cf.cve_sgpoart
             #AND e.cve_ubicacion = op.idy_ubica
             GROUP BY BL, clave
            ORDER BY BL, clave, caducidad ASC";
        $res = mysqli_query($conn, $sql);
        //$options_derivado_base = '<option value="">Seleccione Componente Base</option>';
        $options_derivado_base = '<option value="">Seleccione Producto</option>';
        while($options_base = mysqli_fetch_assoc($res))
        {
            extract($options_base);
            //$options_derivado_base .= '<option value="'.$clave.';;:::::;;'.$cantidad.'" data-peso="'.$peso.'" data-unidad_medida="'.$unidad_medida.'" data-control_peso="'.$control_peso_comp.'" data-lote="'.$lote.'">( '.$clave.' ) - '.$descripcion.'</option>';
            $options_derivado_base .= '<option value="'.$clave.'" data-control_lotes="'.$control_lotes.'" data-caduca="'.$Caduca.'" data-granel="'.$control_peso_comp.'" data-peso="'.$peso.'" data-espieza="'.$es_pieza.'" >( '.$clave.' ) - '.$descripcion.'</option>';
        }

        //**************************************************************************************************
        //**************************************************************************************************

        $sql = "SELECT a.cve_articulo, a.des_articulo, IFNULL(a.control_lotes, 'N') AS control_lotes, IFNULL(a.Caduca, 'N') AS Caduca, '' as lote,
                       IFNULL(a.peso, 0) AS peso, IFNULL(a.control_peso, 'N') AS control_peso, um.mav_cveunimed AS unidad_medida, 0 as cantidad
                FROM c_articulo a
                LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
                WHERE a.cve_articulo = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '{$folio}')";//WHERE Compuesto != 'S'

        $res = mysqli_query($conn, $sql);
        $options_compuesto_base = '';
        while($row_options_base = mysqli_fetch_assoc($res))
        {
            extract($row_options_base);
            //$options_compuesto_base .= '<option value="'.$cve_articulo.'" data-control_lotes="'.$control_lotes.'" data-caduca="'.$Caduca.'" data-granel="'.$control_granel.'" data-peso="'.$peso.'" data-espieza="'.$es_pieza.'" >( '.$cve_articulo.' ) - '.$des_articulo.'</option>';
            $options_compuesto_base .= '<option value="'.$cve_articulo.';;:::::;;'.$cantidad.'" data-peso="'.$peso.'" data-unidad_medida="'.$unidad_medida.'" data-control_peso="'.$control_peso.'" data-lote="'.$lote.'">( '.$cve_articulo.' ) - '.$des_articulo.'</option>';
        }

        //**************************************************************************************************
        //**************************************************************************************************

        $sql = "SELECT DISTINCT ROUND(TRUNCATE(IFNULL(Existencia, 0), 5), 4) AS cantidad FROM ts_existenciapiezas WHERE cve_articulo = '$Cve_Articulo_OT' AND cve_lote = '$Cve_Lote_OT' AND cve_almac = (SELECT cve_almac FROM t_ordenprod WHERE Folio_Pro = '{$folio}') AND idy_ubica = (SELECT idy_ubica FROM t_ordenprod WHERE Folio_Pro = '{$folio}')";
        $res = mysqli_query($conn, $sql);
        $row_existencia = mysqli_fetch_array($res);
        $existencia = $row_existencia['cantidad'];

      echo $Lote.":;;;;;;:".$Caducidad.":;;;;;;:".$options_derivado.":;;;;;;:".$options_derivado_base.":;;;;;;:".$options_compuesto_base.":;;;;;;:".$existencia;
  }
}

if( $_POST['action'] == 'iniciar_cronometro' ) 
{
    $folio   = $_POST['folio'];
    $h       = '00';
    $m       = '00';
    $s       = '00';
    $resul = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql = "SELECT IF(cronometro LIKE '%Na%' OR cronometro LIKE '%b%',TIMEDIFF (Hora_Fin, Hora_Ini),IFNULL(cronometro, '')) AS cronometro FROM t_ordenprod WHERE Folio_Pro = '{$folio}'";
      $query = mysqli_query($conn, $sql);
      $cronometro = mysqli_fetch_assoc($query)['cronometro'];

      if($cronometro != '')
      {
         $time = explode(":", $cronometro);
         $h = $time[0];
         $m = $time[1];
         $s = $time[2];
      }

    echo $h.":".$m.":".$s;
}

if( $_POST['action'] == 'seguir_cronometro' ) 
{
    $folio   = $_POST['folio'];
    $h       = $_POST['h'];
    $m       = $_POST['m'];
    $s       = $_POST['s'];

    $resul = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "UPDATE t_ordenprod SET cronometro = '{$h}:{$m}:{$s}', Hora_Fin = NOW() WHERE Folio_Pro = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    echo $h.":".$m.":".$s;
}

if( $_POST['action'] == 'enviar_ot_qa') 
{
    $folio        = $_POST['folio'];

    $resul = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
    $sql = "UPDATE td_pedido SET status = 'C' WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);
    $sql = "UPDATE td_subpedido SET Status = 'C' WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);

    $sql = "UPDATE th_pedido SET status = 'L' WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);
    $sql = "UPDATE th_subpedido SET Status = 'L' WHERE Fol_folio = '{$folio}'";
    $query = mysqli_query($conn, $sql);
*/

//CREAR EL PEDIDO PARA REVISARLO EN QA
    $sql = "INSERT INTO th_pedido(Fol_folio, Fec_Pedido, Cve_clte, status, Fec_Entrega, ID_Tipoprioridad, Fec_Entrada, cve_almac, destinatario, Cve_Usuario)
(SELECT CONCAT('PT_', Folio_Pro), NOW(), '', 'L', Fecha, 1, NOW(), cve_almac, 0, Cve_Usuario FROM t_ordenprod WHERE Folio_Pro = '{$folio}');";
    $query = mysqli_query($conn, $sql);

    $sql = "INSERT INTO td_pedido(Fol_folio, Cve_articulo, Num_cantidad, status, cve_lote, Num_revisadas, Num_Empacados, Auditado)
(SELECT CONCAT('PT_', Folio_Pro), Cve_Articulo, Cantidad, 'C', Cve_Lote, 0, 0, '' FROM t_ordenprod WHERE Folio_Pro = '{$folio}')";
    $query = mysqli_query($conn, $sql);

    $sql = "INSERT INTO th_subpedido(fol_folio, cve_almac, Sufijo, Fec_Entrada, cve_usuario, status)
(SELECT CONCAT('PT_', Folio_Pro), cve_almac, 1, NOW(), Cve_Usuario, 'L' FROM t_ordenprod WHERE Folio_Pro = '{$folio}')";
    $query = mysqli_query($conn, $sql);

    $sql = "INSERT INTO td_subpedido(fol_folio, cve_almac, Sufijo, Cve_articulo, Num_cantidad, Nun_Surtida, Status, Num_Revisda, Cve_lote)
(SELECT CONCAT('PT_', Folio_Pro), cve_almac, 1, Cve_Articulo, Cantidad, 0, 'C', 0, Cve_lote FROM t_ordenprod WHERE Folio_Pro = '{$folio}')";
    $query = mysqli_query($conn, $sql);

    $sql = "INSERT INTO td_surtidopiezas(fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, Num_Empacados, status)
(SELECT CONCAT('PT_', Folio_Pro), cve_almac, 1, Cve_Articulo, Cve_lote, Cantidad, 0, 0, 'S' FROM t_ordenprod WHERE Folio_Pro = '{$folio}')";
    $query = mysqli_query($conn, $sql);

    echo 1;
}

if( $_POST['action'] == 'asignar_lote' ) 
{
    $folio        = $_POST['folio'];
    $cve_articulo = $_POST['cve_articulo'];
    $lote         = $_POST['val_lote'];
    $id_orden     = $_POST['id_orden'];
    $id_pedido    = $_POST['id_pedido'];

    $resul = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    #$sql = "UPDATE td_ordenprod SET Cve_Lote = '{$lote}' WHERE Folio_Pro = '{$folio}' AND Cve_Articulo = '{$cve_articulo}'";
    $sql = "UPDATE td_ordenprod SET Cve_Lote = '{$lote}' WHERE id_ord = {$id_orden}";
    $query = mysqli_query($conn, $sql);

    //$sql = "UPDATE td_pedido SET cve_lote = '{$lote}' WHERE Fol_folio = '{$folio}' AND Cve_articulo = '{$cve_articulo}'";
    $sql = "UPDATE td_pedido SET cve_lote = '{$lote}' WHERE id = {$id_pedido}";
    $query = mysqli_query($conn, $sql);

    echo 1;
}

if( $_POST['action'] == 'agregar_lote' ) 
{
    $id_orden     = $_POST['id_orden'];
    $folio        = $_POST['folio'];
    $cve_articulo = $_POST['cve_articulo'];
    $cantidad     = $_POST['cantidad'];
    $existencia   = $_POST['existencia'];
    $id_pedido    = $_POST['id_pedido'];
    $cantidad_req = $_POST['cantidad_req'];

    $resul = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT id_ord FROM td_ordenprod WHERE id_ord NOT IN (SELECT id_art_rel FROM td_ordenprod WHERE Folio_Pro = '$folio' AND IFNULL(id_art_rel, '') != '') AND Folio_Pro = '$folio' AND Cve_Articulo = '$cve_articulo'";
    $query = mysqli_query($conn, $sql);
    $id_ord = mysqli_fetch_assoc($query)['id_ord'];

/*
    $sql = "SELECT id FROM td_pedido WHERE id IN (SELECT id_ot FROM td_pedido WHERE Fol_folio = '$folio' AND IFNULL(id_ot, '') != '') AND Fol_folio = '$folio' AND Cve_articulo = '$cve_articulo';";
    $query = mysqli_query($conn, $sql);
    $id_p = mysqli_fetch_assoc($query)['id'];
*/

    #and IFNULL(id_art_rel, '') = ''
    #Folio_Pro = '{$folio}' AND Cve_Articulo = '{$cve_articulo}' AND
    //$sql = "UPDATE td_ordenprod SET Cantidad = {$existencia} WHERE id_ord = {$id_ord}";
    $sql = "UPDATE td_ordenprod SET Cantidad = {$cantidad} WHERE id_ord = {$id_ord}";
    $query = mysqli_query($conn, $sql);

    #$sql = "INSERT INTO td_ordenprod(Folio_Pro, Cve_Articulo, Cantidad, id_art_rel) VALUES ('{$folio}', '{$cve_articulo}', {$cantidad}, {$id_orden})";
    if(($cantidad_req-$cantidad) > 0)
    {
        $sql = "INSERT INTO td_ordenprod(Folio_Pro, Cve_Articulo, Cantidad, id_art_rel) VALUES ('{$folio}', '{$cve_articulo}', ({$cantidad_req}-{$cantidad}), {$id_orden})";
        $query = mysqli_query($conn, $sql);
    }

    #AND IFNULL(id_ot, '') = ''
    #Fol_folio = '{$folio}' AND Cve_articulo = '{$cve_articulo}' AND
    #$sql = "UPDATE td_pedido SET Num_cantidad = {$existencia} WHERE id = {$id_pedido}";
    $sql = "UPDATE td_pedido SET Num_cantidad = {$cantidad} WHERE id = {$id_pedido}";
    $query = mysqli_query($conn, $sql);
    $sql2 = $sql;

    #$sql = "INSERT INTO td_pedido(Fol_folio, Cve_articulo, Num_cantidad, Num_Meses, status, Num_revisadas, id_ot) VALUES ('{$folio}', '{$cve_articulo}', {$cantidad}, 0, 'A', 0, {$id_pedido})";
    if(($cantidad_req-$cantidad)>0)
    {
        $sql = "INSERT INTO td_pedido(Fol_folio, Cve_articulo, Num_cantidad, Num_Meses, status, Num_revisadas, id_ot) VALUES ('{$folio}', '{$cve_articulo}', ({$cantidad_req}-{$cantidad}), 0, 'A', 0, {$id_pedido})";
        $query = mysqli_query($conn, $sql);
    }

    echo 2;
}


if( $_POST['action'] == 'evaluar_cantidad_pedido' ) 
{
    $folio_pedido = $_POST['folio_pedido'];
    $folio_orden  = $_POST['folio_orden'];

    $resul = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT Num_cantidad, LEFT(Fol_Folio, 2) AS es_copia,  FROM td_pedido WHERE fol_folio = '$folio_pedido' AND Cve_Articulo = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$folio_orden')";
    $query = mysqli_query($conn, $sql);
    $res_pedidos = mysqli_fetch_assoc($query);
    $Cantidad_Pedidos = $res_pedidos['Num_cantidad'];
    $es_copia         = $res_pedidos['es_copia'];
    
    $sql = "SELECT Cant_Prod FROM t_ordenprod WHERE Folio_Pro = '$folio_orden'";
    $query = mysqli_query($conn, $sql);
    $res_orden = mysqli_fetch_assoc($query);
    $Cantidad_Orden = $res_orden['Cant_Prod'];

    $success = 0;//pedido y orden con cantidades iguales, todo se ejecuta normal

    if(($Cantidad_Pedidos+0) > ($Cantidad_Orden+0))
        $success = 1; //la cantidad en el pedido es mayor a la cantidad de la orden por lo que puede dividirse
    else if((($Cantidad_Pedidos+0) < ($Cantidad_Orden+0)) && $es_copia == 'CP')
        $success = 2; //la cantidad de los pedidos es menor a la cantidad de la orden, no puede dividirse el pedido

    echo $success;
}


if( $_POST['action'] == 'depurar_ots' ) 
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "DELETE FROM th_pedido WHERE Fol_Folio IN (SELECT Folio_Pro FROM t_ordenprod WHERE STATUS = 'P')";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_pedido WHERE Fol_Folio IN (SELECT Folio_Pro FROM t_ordenprod WHERE STATUS = 'P')";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM td_ordenprod WHERE Folio_Pro IN (SELECT Folio_Pro FROM t_ordenprod WHERE STATUS = 'P')";
    $query = mysqli_query($conn, $sql);

    $sql = "DELETE FROM t_ordenprod WHERE STATUS = 'P'";
    $query = mysqli_query($conn, $sql);

    $success = 1;

    echo $success;
}

if( $_POST['action'] == 'select_pedidos' ) 
{
    $folio   = $_POST['folio'];
    $almacen = $_POST['almacen'];
    $resul = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT 
                t.Fol_folio, p.Cve_articulo, p.Num_cantidad
            FROM th_pedido t 
            LEFT JOIN td_pedido p ON p.Fol_folio = t.Fol_folio
            WHERE t.status = 'A' AND 
                  t.Activo = 1 AND 
                  IFNULL(t.Ship_Num, '') = '' AND 
                  t.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '$almacen') AND 
                  t.Fol_folio NOT IN (SELECT Folio_Pro FROM t_ordenprod) AND 
                  t.Fol_folio NOT IN (SELECT fol_folio FROM th_subpedido) AND 
                  (
                    SELECT COUNT(*) 
                    FROM td_pedido 
                    WHERE Fol_folio = t.Fol_folio AND 
                          Cve_articulo IN (SELECT cve_articulo FROM c_articulo WHERE Compuesto = 'S')
                  ) >= 1 AND
                  p.Cve_articulo = (SELECT Cve_Articulo FROM t_ordenprod WHERE Folio_Pro = '$folio') 
                  #AND p.Num_cantidad = (SELECT Cantidad  FROM t_ordenprod WHERE Folio_Pro = '$folio')
                  #AND (p.cve_lote = '' OR p.cve_lote IS NULL)
                  #AND IFNULL(p.cve_lote, '') = (SELECT IFNULL(Cve_Lote, '') FROM t_ordenprod WHERE Folio_Pro = '$folio')
                  ";
    $query = mysqli_query($conn, $sql);

    $select_pedidos = "<option value=''>Seleccione Pedido</option>";
    while($pedidos = mysqli_fetch_assoc($query))
    {
        $select_pedidos .= "<option value='".$pedidos['Fol_folio']."'>[".$pedidos['Fol_folio']."] - ".$pedidos['Cve_articulo']."</option>";
    }

    echo $select_pedidos;
}

if( $_POST['action'] == 'existencia_producto_terminado' ) 
{
    $folio   = $_POST['folio'];
    $resul = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT Existencia FROM ts_existenciapiezas WHERE CONCAT(cve_almac, cve_articulo,cve_lote) = (SELECT CONCAT(cve_almac, Cve_Articulo, Cve_Lote) FROM t_ordenprod WHERE Folio_Pro = '$folio') AND idy_ubica IN (SELECT IF(IFNULL(idy_ubica_dest, '') = '', idy_ubica, idy_ubica_dest) as idy_ubica FROM t_ordenprod WHERE Folio_Pro = '$folio')";
    //(SELECT idy_ubica_dest as idy_ubica FROM t_ordenprod WHERE Folio_Pro = '$folio' UNION SELECT idy_ubica as idy_ubica FROM t_ordenprod WHERE Folio_Pro = '$folio')
    $query = mysqli_query($conn, $sql);

    $existencia = 0;
    if(mysqli_num_rows($query) > 0)
       $existencia = mysqli_fetch_assoc($query)['Existencia'];

    echo number_format((float) $existencia, 4, '.', '');
}

if( $_POST['action'] == 'eliminarOT' ) 
{
    $folio   = $_POST['folio'];
    $resul = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "DELETE FROM t_ordenprod WHERE Folio_Pro = '$folio';";
    $query = mysqli_query($conn, $sql);
    $sql = "DELETE FROM td_ordenprod WHERE Folio_Pro = '$folio';";
    $query = mysqli_query($conn, $sql);
    $sql = "DELETE FROM th_pedido WHERE Fol_folio = '$folio';";
    $query = mysqli_query($conn, $sql);
    $sql = "DELETE FROM td_pedido WHERE Fol_folio = '$folio';";
    $query = mysqli_query($conn, $sql);
    $sql = "DELETE FROM th_subpedido WHERE Fol_folio = '$folio';";
    $query = mysqli_query($conn, $sql);
    $sql = "DELETE FROM td_subpedido WHERE Fol_folio = '$folio';";
    $query = mysqli_query($conn, $sql);
    $sql = "DELETE FROM td_surtidopiezas WHERE Fol_folio = '$folio';";
    $query = mysqli_query($conn, $sql);
    $sql = "DELETE FROM t_recorrido_surtido WHERE Fol_folio = '$folio';";
    $query = mysqli_query($conn, $sql);

    echo $sql;
}

if( $_POST['action'] == 'pasar_a_produccion' ) 
{
    $folio = $_POST['folio'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "UPDATE th_pedido SET status = 'T' WHERE Fol_folio = '$folio'";
    $query = mysqli_query($conn, $sql);

    $sql = "UPDATE t_ordenprod SET Status = 'I' WHERE Folio_Pro = '$folio'";
    $query = mysqli_query($conn, $sql);

    $sql = "INSERT INTO th_subpedido(fol_folio, cve_almac, Sufijo, Fec_Entrada, cve_usuario, Hora_inicio, Hora_Final, status, nivel, HIE, HFE) (SELECT Fol_folio, cve_almac, 1, NOW(), Cve_Usuario, NOW(), NOW(), 'T', 0, NOW(), NOW() FROM th_pedido WHERE Fol_folio = '$folio')";
    $query = mysqli_query($conn, $sql);


    $sql = "INSERT INTO td_subpedido (fol_folio, cve_almac, Sufijo, Cve_articulo, Num_cantidad, Nun_Surtida, Status, Num_Revisda, Cve_Lote) (SELECT Fol_folio, (SELECT cve_almac FROM th_pedido WHERE Fol_folio = '$folio'), 1, Cve_articulo, Num_cantidad, 0, 'C', 0, cve_lote FROM td_pedido WHERE Fol_folio = '$folio')";
    $query = mysqli_query($conn, $sql);

    $sql = "INSERT INTO td_surtidopiezas (fol_folio, cve_almac, Sufijo, Cve_articulo, LOTE, Cantidad, revisadas, Num_Empacados, STATUS, Id_Proveedor) (SELECT Fol_folio, (SELECT cve_almac FROM th_pedido WHERE Fol_folio = '$folio'), 1, Cve_articulo, cve_lote, Num_cantidad, Num_cantidad, 0, 'A', (SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '$folio') FROM td_pedido WHERE Fol_folio = '$folio')";
    $query = mysqli_query($conn, $sql);


    echo true;
}

if( $_POST['action'] == 'reiniciar_etiquetado' ) 
{
    $folio              = $_POST['folio'];
    $cve_articulo       = $_POST['cve_articulo'];
    $cve_lote           = $_POST['cve_lote'];
    $cantidad_producida = $_POST['cantidad_producida'];
    $almacen            = $_POST['almacen'];
    $resul = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT t.cve_almac, t.idy_ubica, t.cve_articulo, t.cve_lote, t.ID_Proveedor, 
                   IFNULL(t.Cuarentena, 0) AS Cuarentena, u.AreaProduccion
            FROM ts_existenciapiezas t 
            LEFT JOIN c_almacenp c ON c.id = t.cve_almac
            LEFT JOIN c_ubicacion u ON u.idy_ubica = t.idy_ubica
            WHERE t.cve_articulo = '{$cve_articulo}' AND t.cve_lote = '{$cve_lote}' AND c.clave = '{$almacen}' 
            LIMIT 1";
    $query = mysqli_query($conn, $sql);

    if(mysqli_num_rows($query) == 0)
    {
        $sql = "SELECT id FROM c_almacenp WHERE clave = '{$almacen}'";
        $query = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($query);   
        extract($row);

        //$sql = "SELECT idy_ubica FROM c_ubicacion WHERE AreaProduccion = 'S' AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = {$id})";
        $sql = "SELECT idy_ubica FROM t_ordenprod WHERE Folio_Pro = '{folio}'";
        $query = mysqli_query($conn, $sql);
        $row2 = mysqli_fetch_assoc($query);
        extract($row2);

        $sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor, Cuarentena) 
                VALUES({$id}, {$idy_ubica}, '{$cve_articulo}', '{$cve_lote}', {$cantidad_producida}, (SELECT DISTINCT ID_Proveedor FROM ts_existenciatarima WHERE cve_almac = {$id} AND idy_ubica = {$idy_ubica} AND cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}' LIMIT 1), 0)";
        $query = mysqli_query($conn, $sql);

        $sql = "DELETE FROM ts_existenciatarima WHERE cve_almac = {$id} AND idy_ubica = {$idy_ubica} AND cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}'";
        $query = mysqli_query($conn, $sql);
    }
    else
    {
        $row3 = mysqli_fetch_assoc($query);
        extract($row3);

        $sql = "SELECT id FROM c_almacenp WHERE clave = '{$almacen}'";
        $query = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($query);   
        extract($row);

        $sql = "UPDATE ts_existenciapiezas SET Existencia = {$cantidad_producida} WHERE cve_almac = {$id} AND idy_ubica = {$idy_ubica} AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}'";
        $query = mysqli_query($conn, $sql);

        $sql = "DELETE FROM ts_existenciatarima WHERE cve_almac = {$id} AND idy_ubica = {$idy_ubica} AND cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}'";
        $query = mysqli_query($conn, $sql);

    }

    echo true;
}


if($_POST['action'] == 'EnviarConsumoInfinity' ) 
{
    $folios = $_POST['folios'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql = "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'";
      $query = mysqli_query($conn, $sql);
      $ejecutar_infinity = mysqli_fetch_array($query)['existe'];

      $track_infinity = "";

      if($ejecutar_infinity)
      {
        $track_infinity .= "1.- Entro Ejecutar;\n\n";
    //$arr_folios = explode(",", $folios);
    foreach($folios as $orden_id)
    {
            /*
            $sql = "SELECT 
                    c_unimed.cve_umed
                    FROM c_articulo
                    LEFT JOIN c_unimed ON c_articulo.unidadMedida = c_unimed.id_umed
                    WHERE c_articulo.cve_articulo = '$cod_art_compuesto'";
            */
            $track_infinity .= "2.- Folio a ejecutar: $orden_id \n\n";
      //*******************************************************************************
      //                          EJECUTAR EN INFINITY PRODUCTO TERMINADO
      //*******************************************************************************
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
            $query = mysqli_query($conn, $sql);
            $row_ord = mysqli_fetch_array($query);
            $cve_umed = $row_ord['cve_umed'];
            $cve_articulo_ord = $row_ord['cve_articulo'];
            $Cant_Prod_ord = $row_ord['Cant_Prod'];
            $LoteOT = $row_ord['LoteOT'];
            $clave_almacen = $row_ord['clave_almacen'];
            $StatusOT = $row_ord['Status'];
            $id_almacen = $row_ord['id_almacen'];
            $idy_ubica = $row_ord['idy_ubica'];

            $track_infinity .= "3.-Ejecutar SQL con Status = $StatusOT; $sql; \n\n";
            if($StatusOT != 'T') continue;

            $sql = "SELECT Url, Servicio, User, Pswd, Empresa, TO_BASE64(CONCAT(USER,':',Pswd)) Codificado, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1";
            $query = mysqli_query($conn, $sql);
            $row_infinity = mysqli_fetch_array($query);
            $Url_inf = $row_infinity['Url'];
            $Servicio_inf = $row_infinity['Servicio'];
            $User_inf = $row_infinity['User'];
            $Pswd_inf = $row_infinity['Pswd'];
            $Empresa_inf = $row_infinity['Empresa'];
            $hora_movimiento = $row_infinity['hora_movimiento'];
            $Codificado = $row_infinity['Codificado'];

            $track_infinity .= "4.- Ejecutar SQL conexion ; $sql; \n\n";

      $json = "[";
      //$row = mysqli_fetch_array($query);
      //echo $sql;
        //extract($row_ord);

        $json .= "{";
        $json .= '"item":"'.$cve_articulo_ord.'","um":"'.$cve_umed.'","batch":"'.$LoteOT.'", "qty": '.$Cant_Prod_ord.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
        $json .= "}";
      //$json[strlen($json)-1] = ' ';
      $json .= "]";

/*
          $curl = curl_init();
            $url = $Url_inf.":8080/".$Servicio_inf;
          curl_setopt_array($curl, array(
            // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
            CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
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
              'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
            )
            ,CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
          ));

          $response = curl_exec($curl);
          var_dump($response);
          curl_close($curl);      
*/

          //echo $url;
          $track_infinity .= "5.- Response Producto Terminado =; $response ; \n\n";
          $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta) VALUES (NOW(), '$Servicio_inf', '$json', '')";
          $query = mysqli_query($conn, $sql);
          $track_infinity .= "6.- INSERTar response = ; $sql ; \n\n";

      //*******************************************************************************
      //                     EJECUTAR EN INFINITY RESTA COMPONENTES
      //*******************************************************************************

        $sql_comp = "SELECT op.clave, op.control_lotes, op.Lote, op.LoteOT, op.Caduca, op.Caducidad, op.Cantidad, op.Cant_Prod as Cant_OT, op.Num_cantidad AS cantnecesaria, op.Cantidad_Producida, op.ubicacion, IFNULL(op.existencia, 0) AS existencia, op.um, op.clave_almacen, op.Cve_Contenedor FROM (
                SELECT DISTINCT 
                    a.cve_articulo AS clave,
                    IFNULL(a.control_lotes, 'N') AS control_lotes,
                    e.cve_lote AS Lote,
                    IFNULL(t.Cve_Lote, '') AS LoteOT,
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
                    alm.clave AS clave_almacen,
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
                AND e.cve_ubicacion = '{$idy_ubica}' 
                AND ac.Cve_ArtComponente = t.Cve_Articulo AND ac.Cve_Articulo = td.Cve_Articulo #AND ch.CveLP = '{$lp_read}'
                #AND t.idy_ubica = '{$idy_ubica}'
                #AND IFNULL(e.Cve_Contenedor, '') != ''

                UNION 
            SELECT Cve_Articulo AS clave, 
                   '' AS control_lotes,
                   Cve_Lote AS Lote, 
                   '' AS LoteOT,
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
                   '' AS clave_almacen,
                   0 AS ID_Proveedor
             FROM td_ordenprod 
             WHERE Folio_Pro = '{$orden_id}' 
             #AND CONCAT(Cve_Articulo, Cve_Lote) NOT IN (SELECT CONCAT(Cve_Articulo, cve_lote) FROM V_ExistenciaProduccion WHERE cve_almac = '{$id_almacen}')
             AND Cve_Articulo NOT IN (SELECT Cve_Articulo FROM V_ExistenciaProduccion WHERE cve_almac = '{$id_almacen}' AND Existencia > 0 AND cve_ubicacion = '{$idy_ubica}')


                ORDER BY Caducidad
            ) AS op WHERE op.ubicacion IS NOT NULL";

            $track_infinity .= "7.- SQL componentes = ; $sql_comp ; \n\n";
            $query_comp = mysqli_query($conn, $sql_comp);
            while($row_comp = mysqli_fetch_array($query_comp))
            {
                $clave = $row_comp['clave'];
                $Lote = $row_comp['Lote'];
                $existencia = $row_comp['existencia'];
                
              $json = "[";

                $json .= "{";
                $json .= '"item":"'.$clave.'","um":"'.$um.'","batch":"'.$Lote.'", "qty": '.$existencia.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
                $json .= "}";
              //$json[strlen($json)-1] = ' ';
              $json .= "]";
/*
                  $curl = curl_init();
                  $url = $Url_inf.":8080/".$Servicio_inf;
                  curl_setopt_array($curl, array(
                    // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                    CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
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
                      'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
                    )
                    ,CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false

                  ));

                  $response = curl_exec($curl);

                  curl_close($curl);      
                  */
                  //echo $response;
                  $track_infinity .= "8.- Response Componentes =; $response ; \n\n";
                  $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta) VALUES (NOW(), '$Servicio_inf', '$json', '')";
                  $track_infinity .= "9.- INSERTar response = ; $sql ; \n\n";
                  $query = mysqli_query($conn, $sql);
            }
      //*******************************************************************************/
      //*******************************************************************************

    }

      }

  echo $track_infinity;
}


if($_POST['action'] == 'ConectarSAP' ) 
{
  $endPoint = '';
  $json = '';
  
  $funcion  = $_POST['funcion'];
  $metodo   = $_POST['metodo'];
  $folio_ot = $_POST['folio_ot'];
  
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


    $sql_sap = "SELECT DISTINCT Valor as SAP FROM t_configuraciongeneral WHERE cve_conf = 'SAP'";
    if (!($res = mysqli_query($conn, $sql_sap)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $conectar_sap = mysqli_fetch_array($res)['SAP'];

    if($conectar_sap)
    {

/*****************************************************************************************************
//**************************************** REGISTRAR EN LOG *******************************************
//*****************************************************************************************************
  $sql = "INSERT INTO t_log_sap(fecha, cadena, modulo, folio) VALUES (NOW(), 'Funcion: {$funcion}, Metodo: {$metodo}','Connect SAP en Orden de Trabajo', '{$folio_ot}')";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
  }
//*****************************************************************************************************/


//***********************************************************************************************************
  $sql = "SELECT * FROM c_datos_sap WHERE Empresa = (SELECT cve_proveedor FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '{$folio_ot}')) AND Activo = 1;";
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
  
  CURLOPT_SSL_VERIFYPEER => false

));

$response = curl_exec($curl);

 curl_close($curl);

  echo ($response);
}
else echo 1;
}

if($_POST['action'] == 'EjecutarOTSAP' ) 
{
  $folio_ot = $_POST['folio_ot'];
  $funcion  = $_POST['funcion'];
  $funcion2  = $_POST['funcion2'];
  $metodo   = $_POST['metodo'];

  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_sap = "SELECT DISTINCT Valor as SAP FROM t_configuraciongeneral WHERE cve_conf = 'SAP'";
    if (!($res = mysqli_query($conn, $sql_sap)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $conectar_sap = mysqli_fetch_array($res)['SAP'];

if($conectar_sap)
{

$ejecutar_mostrar_SAP = 'M';//M = Mostrar, E = Ejecutar, ME = Mostrar Botón y Ejecutar SAP

  $sql = "SELECT Valor from t_configuraciongeneral WHERE cve_conf = 'mostrar_ejecutar_json'";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
  }
  if(mysqli_num_rows($res) > 0)
  {
      $row = mysqli_fetch_array($res);
      $ejecutar_mostrar_SAP = $row['Valor'];
  }

if($_POST['ejecutar'] == 'Exits')
{
        //************************************************************************************
        //  FUNCION 2 : InventoryGenExits
        //************************************************************************************

          $json = '{';

          $sql = "SELECT * FROM c_datos_sap WHERE Empresa = (SELECT cve_proveedor FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '{$folio_ot}')) AND Activo = 1;";
          if (!($res = mysqli_query($conn, $sql))) 
          {
            echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
          }
          $row = mysqli_fetch_array($res);
          $endPoint = $row['Url'].$funcion2;

          //***********************************************************************************************************
          $sql = "SELECT DISTINCT DATE_FORMAT(op.Hora_Ini, '%Y-%m-%d') AS HoraInicio, op.Referencia, 
                                  op.Cve_Articulo, op.Cve_Lote, op.Cant_Prod, tp.Cve_Almac_Ori
                    FROM t_ordenprod op
                    LEFT JOIN td_pedido tp ON tp.Fol_folio = op.Folio_Pro
                    WHERE op.Folio_Pro =  '{$folio_ot}';";
          if (!($res = mysqli_query($conn, $sql))) 
          {
            echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ";
          }
          $row = mysqli_fetch_array($res);
          $HoraInicio    = $row['HoraInicio'];
          $Referencia    = $row['Referencia'];
          $Cve_Articulo  = $row['Cve_Articulo'];
          $Cve_Lote      = $row['Cve_Lote'];
          $Cant_Prod     = $row['Cant_Prod'];
          $Cant_Prod     = $_POST['cantidad_art_compuesto'];
          
          $Cve_Almac_Ori = $row['Cve_Almac_Ori'];

        $json .= '"DocDate":"'.$HoraInicio.'","DocDueDate":"'.$HoraInicio.'", "DocType": "dDocument_Items",';
        $json .= '"DocumentLines":[';
        //***********************************************************************************************************

          $sql_lines = "SELECT DATE_FORMAT(t.Hora_Ini, '%Y-%m-%d') AS HoraInicio, t.Referencia, 
                               td.Cve_Articulo, td.Cve_Lote, td.Num_cantidad AS Cant_Prod, 
                               td.Cve_Almac_Ori, td.itemPos
                        FROM t_ordenprod t 
                        LEFT JOIN td_pedido td ON t.Folio_Pro = td.Fol_folio
                        WHERE t.Folio_Pro = '{$folio_ot}'
                        ORDER BY itemPos;";
          if (!($res_lines = mysqli_query($conn, $sql_lines))) 
          {
            echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
          }
        while($row_lines = mysqli_fetch_array($res_lines))
        {
          $Cve_Lote      = $row_lines['Cve_Lote'];
          $Cant_Prod     = $row_lines['Cant_Prod'];
          $Cve_Almac_Ori = $row_lines['Cve_Almac_Ori'];
          $itemPos       = $row_lines['itemPos'];

            $json .= '{';

            $json .= '"BaseEntry":"'.$Referencia.'","BaseType":"202","Quantity":"'.$Cant_Prod.'", "BaseLine": '.$itemPos.',"WarehouseCode":"'.$Cve_Almac_Ori.'", 
            "BatchNumbers":[';
            //,"ItemCode":"'.$Cve_Articulo.'"
        //***********************************************************************************************************
            $sql = "SELECT Caducidad FROM c_lotes WHERE Lote = '{$Cve_Lote}'";
            if (!($res = mysqli_query($conn, $sql))) 
            {
              echo "Falló la preparación 5: (" . mysqli_error($conn) . ") ";
            }
            $row2 = mysqli_fetch_array($res);
            $Caducidad = $row2['Caducidad'];
            $json .= '{"BatchNumber":"'.$Cve_Lote.'","Quantity":"'.$Cant_Prod.'","ExpiryDate":"'.$Caducidad.'"},';

        //***********************************************************************************************************
            $json[strlen($json)-1] = ' ';
            $json .= ']},';
        }
        //***********************************************************************************************************
        $json[strlen($json)-1] = ' ';
        $json .= ']}';

        ///////echo json_encode($json);
        //echo $json;
        if($ejecutar_mostrar_SAP == 'M')
        {
            echo json_encode("JSON 1: ".$json);
            //echo $json;
        }

//****************************************************************************************
//****************************************************************************************


        if($ejecutar_mostrar_SAP == 'E' || $ejecutar_mostrar_SAP == 'ME')
        {
        //****************************************************************************************
        //****************************************************************************************

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
        $response2 = curl_exec($curl);

         curl_close($curl);

//*****************************************************************************************************
//**************************************** REGISTRAR EN LOG *******************************************
//*****************************************************************************************************
  $sql = "INSERT INTO t_log_sap(fecha, cadena, respuesta, modulo, folio, funcion) VALUES (NOW(), '{$json}', '{$response2}','Ejecutar OT SAP en Orden de Trabajo', '{$folio_ot}', 'Exits')";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 6: (" . mysqli_error($conn) . ") ";
  }
//*****************************************************************************************************

        echo $response2;
        //****************************************************************************************/
        //****************************************************************************************
        }

}
else if($_POST['ejecutar'] == 'Entries')
{
        //************************************************************************************
        //  FUNCION 1 : InventoryGenEntries
        //************************************************************************************

          $json2 = '{';

          $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

          $sql = "SELECT * FROM c_datos_sap WHERE Empresa = (SELECT cve_proveedor FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '{$folio_ot}')) AND Activo = 1;";
          if (!($res = mysqli_query($conn, $sql))) 
          {
            echo "Falló la preparación 7: (" . mysqli_error($conn) . ") ";
          }
          $row = mysqli_fetch_array($res);
          $endPoint = $row['Url'].$funcion;

          //***********************************************************************************************************
          $sql = "SELECT DATE_FORMAT(Hora_Ini, '%Y-%m-%d') AS HoraInicio, Referencia, Cve_Articulo, Cve_Lote, Cant_Prod, Cve_Almac_Ori FROM t_ordenprod WHERE Folio_Pro = '{$folio_ot}';";
          if (!($res = mysqli_query($conn, $sql))) 
          {
            echo "Falló la preparación 8: (" . mysqli_error($conn) . ") ";
          }
          $row = mysqli_fetch_array($res);
          $HoraInicio    = $row['HoraInicio'];
          $Referencia    = $row['Referencia'];
          $Cve_Articulo  = $row['Cve_Articulo'];
          $Cve_Lote      = $row['Cve_Lote'];
          $Cant_Prod     = $row['Cant_Prod'];
          if(isset($_POST['cantidad_art_compuesto']))
            $Cant_Prod     = $_POST['cantidad_art_compuesto'];

          $Cve_Almac_Ori = $row['Cve_Almac_Ori'];

        $json2 .= '"DocDate":"'.$HoraInicio.'","DocDueDate":"'.$HoraInicio.'",';
        $json2 .= '"DocumentLines":[';
        //***********************************************************************************************************
            $json2 .= '{';

            $json2 .= '"BaseEntry":"'.$Referencia.'","BaseType":"202","Quantity":"'.$Cant_Prod.'","WarehouseCode":"'.$Cve_Almac_Ori.'", 
            "BatchNumbers":[';
            //,"ItemCode":"'.$Cve_Articulo.'"
        //***********************************************************************************************************
            $sql = "SELECT Caducidad FROM c_lotes WHERE Lote = '{$Cve_Lote}'";
            if (!($res = mysqli_query($conn, $sql))) 
            {
              echo "Falló la preparación 9: (" . mysqli_error($conn) . ") ";
            }
            $row2 = mysqli_fetch_array($res);
            $Caducidad = $row2['Caducidad'];
            $json2 .= '{"BatchNumber":"'.$Cve_Lote.'","Quantity":"'.$Cant_Prod.'","ExpiryDate":"'.$Caducidad.'"},';

        //***********************************************************************************************************
            $json2[strlen($json2)-1] = ' ';
            $json2 .= ']},';
        //***********************************************************************************************************
        $json2[strlen($json2)-1] = ' ';
        $json2 .= ']}';

        if($ejecutar_mostrar_SAP == 'M')
        {
            echo json_encode("JSON2: ".$json2);
            //echo $json;
        }

        if($ejecutar_mostrar_SAP == 'E' || $ejecutar_mostrar_SAP == 'ME')
        {
        //****************************************************************************************
        //****************************************************************************************

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

          CURLOPT_POSTFIELDS =>$json2,

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

//*****************************************************************************************************
//**************************************** REGISTRAR EN LOG *******************************************
//*****************************************************************************************************
         //$response = addslashes($response);
         $response = str_replace("'", "", $response);
  $sql = "INSERT INTO t_log_sap(fecha, cadena, respuesta, modulo, folio, funcion) VALUES (NOW(), '{$json2}', '{$response}','Ejecutar OT SAP en Orden de Trabajo', '{$folio_ot}', 'Entries')";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 10: (" . mysqli_error($conn) . ") || ". $sql;
  }
//*****************************************************************************************************

          echo $response;
        //****************************************************************************************/
        //****************************************************************************************
        }
}
}
}

if($_POST['action'] == 'prueba_infinity')
{
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'[{"item":"A5-40003-001B2-040","um":"PIEZA","batch":"", "qty": 288,"typeMov":"T","warehouse":"WHCR","dataOpe":"2023-08-24 17:06:56"}]',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
  )
  ,CURLOPT_SSL_VERIFYHOST => false,
   CURLOPT_SSL_VERIFYPEER => false
));

$response = curl_exec($curl);
curl_close($curl);
echo var_dump($response);

//echo "INFINITY_RESP";
}
