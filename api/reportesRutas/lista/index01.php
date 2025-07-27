<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST) && !isset($_POST['consolidado']) && !isset($_POST['cobranzadet']) && !isset($_POST['bitacora']) && !isset($_POST['bitacoranv']) && !isset($_POST['noventas']) && $_POST['action'] != 'getDetallesFolioGrupo') {
    $page = $_POST['page'];  // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx'];  // get index row - i.e. user click to sort
    $sord = $_POST['sord'];  // get the direction
    $almacen = $_POST['almacen'];
    $agente = $_POST['agente'];
    $cliente = $_POST['clientes'];
    $ruta = $_POST['ruta'];
    $criterio = $_POST['criterio'];
    $diao = $_POST['diao'];
    $operacion = $_POST['operacion'];
    $fecha_inicio = $_POST['fechaini'];
    $fecha_fin = $_POST['fechafin'];

    $tipoV = $_POST['tipoV'];
    $articulos = $_POST['articulos'];
    $articulos_obsq = $_POST['articulos_obsq'];
    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];

    if ($ruta == '') return;


    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    /*
        if($fecha_inicio == '')
        {
            $sql_fecha = "SELECT DATE_ADD(CURDATE(), INTERVAL -7 DAY) AS fecha_semana FROM DUAL";
            if (!($res_fecha = mysqli_query($conn, $sql_fecha)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
            $fecha_inicio = mysqli_fetch_array($res_fecha)['fecha_semana'];
        }
    */

    $SQLArticulo1 = "";
    $SQLArticulo2 = "";
    $SQLArticulo1_IN = "";
    $SQLArticulo_Obseq = ""; //$SQLArticulo_Obseq2 = "";
    $SQLArticulo1_sub = "";
    $SQLArticulo2_sub = "";
    $SQLArticulo3_sub = "";

    if (!empty($articulos)) {
        $SQLArticulo1 = " AND DetalleVet.Articulo = '$articulos' ";
        $SQLArticulo1_IN = " AND Articulo = '$articulos' ";
        $SQLArticulo2 = " AND td_pedido.Articulo = '$articulos' ";
        $SQLArticulo1_sub = " AND Articulo = '$articulos' ";
        $SQLArticulo2_sub = " AND Articulo = '$articulos' ";
        $SQLArticulo3_sub = " AND dv.Articulo = '$articulos' ";

    }

    if (!empty($articulos_obsq)) {
        $SQLArticulo_Obseq = " AND pr.SKU = '$articulos_obsq' ";
    }

    $SQLFecha = "";
    $SQLFechaIN = "";

    if (!empty($fecha_inicio) and !empty($fecha_fin)) {
        //$fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        /*
        $SQLFecha = " AND ventas.FechaBusq BETWEEN STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
        if($fecha_inicio == $fecha_fin)
            $SQLFecha = " AND ventas.FechaBusq = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
    */
        $SQLFecha = " AND DATE(ventas.FechaBusq) >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND DATE(ventas.FechaBusq) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
        $SQLFechaIN = " AND DATE(RelOperaciones.Fecha) >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') AND DATE(RelOperaciones.Fecha) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y') ";
        if ($fecha_inicio == $fecha_fin) {
            $SQLFecha = " AND DATE(ventas.FechaBusq) = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
            $SQLFechaIN = " AND DATE(RelOperaciones.Fecha) = STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
        }


    } else if (!empty($fecha_inicio)) {
        //$fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        $SQLFecha = " AND ventas.FechaBusq >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";
        $SQLFechaIN = " AND RelOperaciones.Fecha >= STR_TO_DATE('{$fecha_inicio}', '%d-%m-%Y') ";

    } else if (!empty($fecha_fin)) {
        //$fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        $SQLFecha = " AND ventas.FechaBusq <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y')";
        $SQLFechaIN = " AND RelOperaciones.Fecha <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y')";
    }

    $SQLVendedor = "";
    if ($agente) {
        $SQLVendedor = " AND ventas.vendedorID = '" . $agente . "' ";
    }


    $SQLCliente = "";
    if ($cliente) {
        $SQLCliente = " AND ventas.Cliente = '" . $cliente . "' ";
    }

    $SQLTipoV = "";
    if ($tipoV) {
        $SQLTipoV = " AND ventas.Tipo = '" . $tipoV . "' ";
    }

    $SQLRuta = "";
    $SQLRutaIN = "";//$SQLRutaContador = "";
    if ($ruta) {
        //$SQLRutaContador = " AND t_ruta.cve_ruta = '".$ruta."' ";
        $SQLRuta = " AND ventas.rutaName = '" . $ruta . "' ";
        $SQLRutaIN = " AND t_ruta.cve_ruta = '" . $ruta . "' ";

        if ($ruta == 'todas') {
            $SQLRuta = " AND ventas.rutaName != '' ";
            $SQLRutaIN = " AND t_ruta.cve_ruta != '' ";
        }
    }

    $SQLDiaO = "";
    $SQLDiaOIN = "";
    $SQLDiaOIN2 = ""; //$SQLDiaOContadorVenta = ""; $SQLDiaOContadorPreVenta = "";
    if ($diao) {
        //$SQLDiaOContadorVenta = " AND Venta.DiaO = '".$diao."' ";
        //$SQLDiaOContadorPreVenta = " AND th_pedido.DiaO = '".$diao."' ";
        $SQLDiaO = " AND (ventas.DiaOperativo = '" . $diao . "' OR ventas.DiaOperativoCobranza = '" . $diao . "' OR '" . $diao . "' IN (SELECT dc.DiaO FROM DetalleCob dc WHERE dc.Documento = ventas.Documento AND dc.DiaO = ventas.DiaOperativo)) ";
        $SQLDiaOIN = " AND RelOperaciones.DiaO = '" . $diao . "' ";
        //$SQLDiaOIN2 = " AND (Venta.DiaO = '" . $diao . "' OR Cobranza.DiaO = '" . $diao . "' OR ('" . $diao . "' IN (SELECT dc.DiaO FROM DetalleCob dc WHERE dc.Documento = Venta.Documento AND dc.DiaO = Venta.DiaO))) ";
        $SQLDiaOIN2 = " AND (Venta.DiaO = '" . $diao . "') ";

    }

    $SQLOperacion = " AND IFNULL(ventas.Importe, 0) > 0 "; //$SQLOperacionVenta = ""; $SQLOperacionPreVenta = "";
    $InnerJoinOperacion = "";

    if (isset($_POST['credito']))
        $SQLOperacion = " AND ventas.Operacion != 'PreVenta' ";

    if ($operacion) {
        //$SQLOperacionVenta = " AND 0 "; 
        //$SQLOperacionPreVenta = " AND 1 ";
        $SQLOperacion = " AND ventas.Operacion = 'PreVenta' ";

        if (isset($_POST['credito']))
            $SQLOperacion = " AND ventas.Operacion != 'PreVenta' ";

        if ($operacion == 'entrega')
            $SQLOperacion = " AND ventas.Operacion = 'Entrega' ";

        if ($operacion == 'F') {
            $InnerJoinOperacion = "INNER JOIN t_pedentregados tpe ON tpe.Fol_folio = th_pedido.Pedido";
            $SQLOperacion = " AND ventas.Operacion = 'Entrega' ";
        }

        if ($operacion == 'venta') {
            $SQLOperacion = " AND ventas.Operacion = 'Venta' AND IFNULL(ventas.Importe, 0) > 0";
            $InnerJoinOperacion = "";
            //$SQLOperacionVenta = " AND 1 "; 
            //$SQLOperacionPreVenta = " AND 0 ";
        }

        if ($operacion == 'Devoluciones')
            $SQLOperacion = " AND IFNULL(ventas.Importe, 0) < 0 ";

    }

    $SQLCriterio = ""; //$SQLCriterioVenta = ""; $SQLCriterioPreVenta = "";
    if ($criterio) {
        //$SQLCriterioVenta = " AND (Venta.Id LIKE '%".$criterio."%' OR Venta.CodCliente LIKE '%".$criterio."%' OR c_cliente.RazonSocial LIKE '%".$criterio."%' OR Venta.Documento LIKE '%".$criterio."%' OR t_vendedores.Nombre LIKE '%".$criterio."%') ";
        //$SQLCriterioPreVenta = " AND (th_pedido.Cod_Cliente LIKE '%".$criterio."%' OR c_cliente.RazonSocial LIKE '%".$criterio."%' OR td_pedido.Pedido LIKE '%".$criterio."%' OR t_vendedores.Nombre LIKE '%".$criterio."%') ";
        $SQLCriterio = " AND (ventas.Cliente LIKE '%" . $criterio . "%' OR ventas.CveCliente LIKE '%" . $criterio . "%' OR ventas.Responsable LIKE '%" . $criterio . "%' OR ventas.Folio LIKE '%" . $criterio . "%' OR ventas.Vendedor LIKE '%" . $criterio . "%' OR ventas.nombreComercial LIKE '%" . $criterio . "%' OR ventas.vendedorID LIKE '%" . $criterio . "%' OR ventas.cveVendedor LIKE '%" . $criterio . "%' OR '" . $criterio . "' IN (SELECT Articulo FROM DetalleVet WHERE RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '" . $ruta . "') AND IdEmpresa = (SELECT clave FROM c_almacenp WHERE id = '" . $almacen . "'))) ";
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $inner_venta = " LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo ";
    $inner_preventa = " LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_pedido.Articulo ";


    $field_envase_venta_preventa = "";
    $left_envase_venta = "";
    $left_envase_preventa = "";
    //$field_cantidad_venta_preventa = "";
    $field_cantidad_venta_plastico = "";
    $field_cantidad_venta_cristal = "";
    $field_cantidad_venta_garrafon = "";

    $field_cantidad_preventa_plastico = "";
    $field_cantidad_preventa_cristal = "";
    $field_cantidad_preventa_garrafon = "";

    //$field_cantdev_venta_preventa = "";

    $field_tipo_venta_preventa = "";

    if (isset($_POST['envase'])) {
        $field_envase_venta_preventa = " GROUP_CONCAT(CONCAT('(', art_env.cve_articulo, ') ', art_env.des_articulo, '::::::::::',art_env.tipo) SEPARATOR '+++++') AS Envase, ";
        //$field_cantidad_venta_preventa = " (penv.Cantidad) AS Cantidad_Envase, ";
        //$field_cantdev_venta_preventa = " (penv.Devuelto) AS Cantidad_Devuelta, ";

        $field_cantidad_venta_plastico = " IFNULL((SELECT (SUM(denv.Cantidad) - SUM(denv.Devuelto)) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase AND dart.Tipo_Envase = 'P' WHERE denv.RutaId = Venta.RutaId  AND denv.DiaO = Venta.DiaO AND denv.Docto = Venta.Documento), 0) AS Cantidad_Envase_plastico, ";
        $field_cantidad_venta_cristal = " IFNULL((SELECT (SUM(denv.Cantidad) - SUM(denv.Devuelto)) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase AND dart.Tipo_Envase = 'C' WHERE denv.RutaId = Venta.RutaId  AND denv.DiaO = Venta.DiaO AND denv.Docto = Venta.Documento), 0) AS Cantidad_Envase_cristal, ";
        $field_cantidad_venta_garrafon = " IFNULL((SELECT (SUM(denv.Cantidad) - SUM(denv.Devuelto)) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase AND dart.Tipo_Envase = 'G' WHERE denv.RutaId = Venta.RutaId  AND denv.DiaO = Venta.DiaO AND denv.Docto = Venta.Documento), 0) AS Cantidad_Envase_garrafon, ";

        $field_cantidad_preventa_plastico = " IFNULL((SELECT (SUM(denv.Cantidad) - SUM(denv.Devuelto)) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase AND dart.Tipo_Envase = 'P' WHERE denv.RutaId = th_pedido.Ruta  AND denv.DiaO = th_pedido.DiaO AND denv.Docto = th_pedido.Pedido), 0) AS Cantidad_Envase_plastico, ";
        $field_cantidad_preventa_cristal = " IFNULL((SELECT (SUM(denv.Cantidad) - SUM(denv.Devuelto)) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase AND dart.Tipo_Envase = 'C' WHERE denv.RutaId = th_pedido.Ruta  AND denv.DiaO = th_pedido.DiaO AND denv.Docto = th_pedido.Pedido), 0) AS Cantidad_Envase_cristal, ";
        $field_cantidad_preventa_garrafon = " IFNULL((SELECT (SUM(denv.Cantidad) - SUM(denv.Devuelto)) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase AND dart.Tipo_Envase = 'G' WHERE denv.RutaId = th_pedido.Ruta  AND denv.DiaO = th_pedido.DiaO AND denv.Docto = th_pedido.Pedido), 0) AS Cantidad_Envase_garrafon, ";

        $field_tipo_venta_preventa = " IF(penv.Devuelto = 0, penv.Tipo, 'Devuelta') AS TipoStatus, ";
        #$left_envase_venta_preventa = " LEFT JOIN ProductoEnvase penv ON penv.Producto = c_articulo.cve_articulo  ";#AND c_almacenp.clave = penv.IdEmpresa
        $left_envase_venta = " LEFT JOIN DevEnvases penv ON penv.Docto = DetalleVet.Docto ";
        $left_envase_preventa = " LEFT JOIN DevEnvases penv ON penv.Docto = th_pedido.Pedido ";
        $left_envase_venta_preventa_nombre_env = " LEFT JOIN c_articulo art_env ON penv.Envase = art_env.cve_articulo ";

        $inner_venta = " INNER JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo AND c_articulo.Usa_Envase = 'S' ";
        $inner_preventa = " INNER JOIN c_articulo ON c_articulo.cve_articulo = td_pedido.Articulo AND c_articulo.Usa_Envase = 'S' ";
    }

    $field_envase_venta_ec = "";
    $field_envase_preventa_ec = "";
    if (isset($_POST['estado_cuenta'])) {
        $field_envase_venta_ec = " (SELECT (GROUP_CONCAT(denv.Envase SEPARATOR ',')) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase WHERE denv.RutaId = Venta.RutaId  AND denv.DiaO = Venta.DiaO AND denv.Docto = Venta.Documento) AS Envase, ";
        $field_cantidad_venta_plastico = " IFNULL((SELECT (SUM(denv.Cantidad) - SUM(denv.Devuelto)) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase AND dart.Tipo_Envase = 'P' WHERE denv.RutaId = Venta.RutaId  AND denv.DiaO = Venta.DiaO AND denv.Docto = Venta.Documento), 0) AS Cantidad_Envase_plastico, ";
        $field_cantidad_venta_cristal = " IFNULL((SELECT (SUM(denv.Cantidad) - SUM(denv.Devuelto)) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase AND dart.Tipo_Envase = 'C' WHERE denv.RutaId = Venta.RutaId  AND denv.DiaO = Venta.DiaO AND denv.Docto = Venta.Documento), 0) AS Cantidad_Envase_cristal, ";
        $field_cantidad_venta_garrafon = " IFNULL((SELECT (SUM(denv.Cantidad) - SUM(denv.Devuelto)) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase AND dart.Tipo_Envase = 'G' WHERE denv.RutaId = Venta.RutaId  AND denv.DiaO = Venta.DiaO AND denv.Docto = Venta.Documento), 0) AS Cantidad_Envase_garrafon, ";

        $field_envase_preventa_ec = " (SELECT (GROUP_CONCAT(denv.Envase SEPARATOR ',')) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase WHERE denv.RutaId = th_pedido.Ruta  AND denv.DiaO = th_pedido.DiaO AND denv.Docto = th_pedido.Pedido) AS Envase, ";
        $field_cantidad_preventa_plastico = " IFNULL((SELECT (SUM(denv.Cantidad) - SUM(denv.Devuelto)) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase AND dart.Tipo_Envase = 'P' WHERE denv.RutaId = th_pedido.Ruta  AND denv.DiaO = th_pedido.DiaO AND denv.Docto = th_pedido.Pedido), 0) AS Cantidad_Envase_plastico, ";
        $field_cantidad_preventa_cristal = " IFNULL((SELECT (SUM(denv.Cantidad) - SUM(denv.Devuelto)) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase AND dart.Tipo_Envase = 'C' WHERE denv.RutaId = th_pedido.Ruta  AND denv.DiaO = th_pedido.DiaO AND denv.Docto = th_pedido.Pedido), 0) AS Cantidad_Envase_cristal, ";
        $field_cantidad_preventa_garrafon = " IFNULL((SELECT (SUM(denv.Cantidad) - SUM(denv.Devuelto)) FROM DevEnvases denv INNER JOIN c_articulo dart ON dart.cve_articulo = denv.Envase AND dart.Tipo_Envase = 'G' WHERE denv.RutaId = th_pedido.Ruta  AND denv.DiaO = th_pedido.DiaO AND denv.Docto = th_pedido.Pedido), 0) AS Cantidad_Envase_garrafon, ";

        $field_tipo_venta_preventa = " IF(penv.Devuelto = 0, penv.Tipo, 'Devuelta') AS TipoStatus, ";
        #$left_envase_venta_preventa = " LEFT JOIN ProductoEnvase penv ON penv.Producto = c_articulo.cve_articulo  ";#AND c_almacenp.clave = penv.IdEmpresa
        $left_envase_venta = " LEFT JOIN DevEnvases penv ON penv.Docto = DetalleVet.Docto ";
        $left_envase_preventa = " LEFT JOIN DevEnvases penv ON penv.Docto = th_pedido.Pedido ";
        $left_envase_venta_preventa_nombre_env = " LEFT JOIN c_articulo art_env ON penv.Envase = art_env.cve_articulo ";

        $inner_venta = " INNER JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo AND c_articulo.Usa_Envase = 'S' ";
        $inner_preventa = " INNER JOIN c_articulo ON c_articulo.cve_articulo = td_pedido.Articulo AND c_articulo.Usa_Envase = 'S' ";
    }

    $where_credito = "";
    if (isset($_POST['credito'])) {
        $sqlEstadoCuenta = "";
        $SQLStatus = "";
        if (isset($_POST['estado_cuenta']))
            $sqlEstadoCuenta = " AND ventas.StatusCobranza = 1 AND ventas.Cancelada = 0 ";
        if (isset($_POST['status'])) {
            $status = $_POST['status'];
            if ($status)
                $SQLStatus = " AND ventas.StatusCobranza = " . $status . " ";
        }

        $where_credito = " AND ventas.Tipo = 'Credito' AND ventas.Cancelada = 0 " . $sqlEstadoCuenta . $SQLStatus;
    }

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit * $page - $limit; // do not put $limit*($page - 1)

    if (!$sidx) $sidx = 1;

    // se conecta a la base de datos
    /*
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        // prepara la llamada al procedimiento almacenado Lis_Facturas
        $sqlCount = "SELECT
                count(t_ruta.ID_Ruta) as cuenta,
                t_ruta.cve_ruta,
                t_ruta.descripcion,
                IF(t_ruta.status='A','Activo','Baja') as status,
                t_ruta.Activo
                FROM
                t_ruta
                WHERE
                (t_ruta.descripcion LIKE '%".$_criterio."%' OR t_ruta.cve_ruta LIKE '%".$_criterio."%') AND t_ruta.Activo = 1 and t_ruta.cve_almacenp='".$almacen."' {$SQLRuta};";
        if (!($res = mysqli_query($conn, $sqlCount))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $row = mysqli_fetch_array($res);
        $count = $row[0];

        mysqli_close();
    */

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn, $charset);


    $_page = 0;

    if (intval($page) > 0) $_page = ($page - 1) * $limit;

    /*
                (SELECT COUNT(DISTINCT t_clientexruta.clave_cliente) FROM c_destinatarios d
                LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
                LEFT JOIN t_ruta tr ON tr.ID_Ruta = t_clientexruta.clave_ruta
                LEFT JOIN RelDayCli ON RelDayCli.Cve_Cliente = d.Cve_Clte AND tr.cve_ruta = RelDayCli.Cve_Ruta AND d.id_destinatario = RelDayCli.Id_Destinatario
                WHERE d.Activo = '1' AND c.Cve_Almacenp = '".$almacen."' AND tr.cve_ruta = t_ruta.cve_ruta) AS N_Clientes,
    */

    $field_liquidacion = "*";
    $field_liquidacion1 = "";
    $field_liquidacion2 = "";
    $group_by_liquidacion = "";
    if (isset($_POST['liquidacion'])) {
        $field_liquidacion = "ventas.sucursalNombre, ventas.Sucursal, ventas.Ruta, ventas.rutaName, ventas.DiaOperativo, 
       SUM(ventas.limite_credito) AS limite_credito, SUM(ventas.Importe) AS Importe, SUM(ventas.IVA) AS IVA, 
       SUM(ventas.Descuento) AS Descuento, SUM(ventas.Total) AS Total, SUM(ventas.saldoFinal) AS saldoFinal, 
       SUM(ventas.Abono) AS Abono, SUM(ventas.saldoActual) AS saldoActual, SUM(ventas.cajas_total) AS cajas_total, 
       SUM(ventas.piezas_total) AS piezas_total, SUM(ventas.PromoC) AS PromoC, SUM(ventas.PromoP) AS PromoP, 
       SUM(ventas.tienepromo) AS tienepromo, ventas.Operacion, ventas.CveCliente ";
        $field_liquidacion1 = "IF(DetalleVet.Tipo = 0, (SELECT SUM(Pza) FROM DetalleVet WHERE Docto = Venta.Documento), SUM(IFNULL(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))),TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0)), 0))) AS cajas_total,
      IF(DetalleVet.Tipo = 0, 0, SUM(IFNULL(IF(um.mav_cveunimed != 'XBX', (IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) - (c_articulo.num_multiplo*TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), 0)), 0))) as piezas_total,
      IF(pr.Tipmed = 'Caja', (SELECT SUM(Cant) FROM PRegalado WHERE Docto = Venta.Documento), 0) AS PromoC,
      IF(pr.Tipmed != 'Caja', (SELECT SUM(Cant) FROM PRegalado WHERE Docto = Venta.Documento), 0) AS PromoP,
      ";
        $field_liquidacion2 = "SUM(IFNULL(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td_pedido.Pedidas),TRUNCATE((td_pedido.Pedidas/c_articulo.num_multiplo), 0)), 0)) AS cajas_total,
      SUM(IFNULL(IF(um.mav_cveunimed != 'XBX', (td_pedido.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td_pedido.Pedidas/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, td_pedido.Pedidas, 0)), 0)) as piezas_total,
      IF(pr.Tipmed = 'Caja', SUM(pr.Cant), 0) as PromoC,
      IF(pr.Tipmed != 'Caja', SUM(pr.Cant), 0) as PromoP,
";
        $group_by_liquidacion = " GROUP BY ventas.DiaOperativo, ventas.Operacion ";
    }

    $sql = "
    SELECT {$field_liquidacion} FROM (
    SELECT DISTINCT
      #DetalleVet.Comisiones AS Comisiones,
      c_almacenp.nombre AS sucursalNombre,
      Venta.Id AS idVenta,
      Venta.IdEmpresa AS Sucursal,
      Venta.Fecha AS FechaBusq,
      DATE_FORMAT(Venta.Fecha, '%d-%m-%Y') AS Fecha,
      DATE_FORMAT(Venta.Fvence, '%d-%m-%Y') AS FechaCompromiso,
      Venta.RutaId AS Ruta,
      t_ruta.cve_ruta AS rutaName,
      Venta.CodCliente AS Cliente,
      c_cliente.Cve_Clte as CveCliente,
      c_cliente.RazonSocial AS Responsable,
      IFNULL(c_cliente.limite_credito, 0) AS limite_credito, 
      c_destinatarios.razonsocial AS nombreComercial,
      Venta.Documento AS Folio,
      Venta.TipoVta AS Tipo,
      FormasPag.Forma AS metodoPago,
      ##Venta.Subtotal AS Importe,
      #SUM(DetalleVet.Importe) AS Importe,
      ##Venta.IVA AS IVA,
      #SUM(DetalleVet.IVA) AS IVA,
      #SUM(IFNULL(DetalleVet.DescMon, 0.00)) AS Descuento,
      ##Venta.TOTAL AS Total,
      #SUM(DetalleVet.Importe+DetalleVet.IVA) AS Total,
    #SUM(DetalleVet.Importe) AS Importe,      
    (SELECT SUM(dv.Importe) FROM DetalleVet dv WHERE dv.Docto = Venta.Documento {$SQLArticulo3_sub}) AS Importe,
    #Venta.IVA AS IVA,      
    #SUM(DetalleVet.IVA) AS IVA,      
    (SELECT SUM(IFNULL(dv.IVA, 0)) FROM DetalleVet dv WHERE dv.Docto = Venta.Documento {$SQLArticulo3_sub}) AS IVA,
    #SUM(IFNULL(DetalleVet.DescMon, 0.00)) AS Descuento,      
    (SELECT SUM(IFNULL(dv.DescMon, 0)) FROM DetalleVet dv WHERE dv.Docto = Venta.Documento {$SQLArticulo3_sub}) AS Descuento,
    #Venta.TOTAL AS Total,      
    SUM(DetalleVet.Importe+DetalleVet.IVA) AS Total,      
    #0 AS Total,

      #DetalleVet.Comisiones AS Comisiones,
      #DetalleVet.Utilidad AS Utilidad,
      #c_articulo.num_multiplo AS Cajas,
      #IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) AS Piezas,
      Venta.Cancelada AS Cancelada,
      #IF(IFNULL(th_pedido.Fol_folio, '') = '', 'Venta', 'PreVenta') AS Operacion,
      'Venta' AS Operacion,
      Venta.VendedorId AS vendedorID,
      t_vendedores.Cve_Vendedor as cveVendedor, 
      t_vendedores.Nombre AS Vendedor,
      Venta.ID_Ayudante1 AS Ayudante1,
      Venta.ID_Ayudante2 AS Ayudante2,
      #DetalleVet.DescPorc AS Promociones,
      Venta.DiaO AS DiaOperativo,
      DiasO.DiaO AS DiaOperativoCobranza,
      #DetalleVet.Descripcion AS Articulo,
      IFNULL(Cob.Status, 1) AS StatusCobranza,
      Cob.Documento AS Documento,
      #(IFNULL(Cob.Saldo, 0) - IFNULL((SELECT SUM(Abono) FROM DetalleCob WHERE Documento = Venta.Documento), 0)) AS saldoFinal,
      IFNULL(Cob.Saldo, 0) AS Saldo,
      (IFNULL(Cob.Saldo, 0) - IFNULL(Cob.Abono, 0)) AS saldoFinal,
      #SUM(IFNULL(DetalleCob.Abono, 0)) AS Abono,
      #IFNULL((SELECT SUM(Abono) FROM DetalleCob WHERE Documento = Venta.Documento), 0) AS Abono,
      IFNULL(Cob.Abono, 0) AS Abono,
      IFNULL(Venta.Saldo, '0.00') AS saldoActual,
      Cob.FechaReg AS fechaRegistro,
      Cob.FechaVence AS fechaVence,
      Venta.DocSalida AS tipoDoc,
      Noventas.MotivoId AS idMotivo,
      MotivosNoVenta.Motivo AS Motivo,
      {$field_envase_venta_ec}
      {$field_envase_venta_preventa}
      {$field_cantidad_venta_plastico}
      {$field_cantidad_venta_cristal}
      {$field_cantidad_venta_garrafon}
      {$field_tipo_venta_preventa}
      {$field_liquidacion1}
      #IF(DetalleVet.Tipo = 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), SUM(IFNULL(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))),TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0)), 0))) AS cajas_total,


      #IF(DetalleVet.Tipo = 0, (SELECT SUM(Pza) FROM DetalleVet WHERE Docto = Venta.Documento), SUM(IFNULL(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))),TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0)), 0))) AS cajas_total,
      #IF(DetalleVet.Tipo = 0, 0, SUM(IFNULL(IF(um.mav_cveunimed != 'XBX', (IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) - (c_articulo.num_multiplo*TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), 0)), 0))) as piezas_total,
      #IF(pr.Tipmed = 'Caja', (SELECT SUM(Cant) FROM PRegalado WHERE Docto = Venta.Documento), 0) AS PromoC,
      #IF(pr.Tipmed != 'Caja', (SELECT SUM(Cant) FROM PRegalado WHERE Docto = Venta.Documento), 0) AS PromoP,


      #IF(pr.Tipmed = 'Caja', SUM(pr.Cant), 0) as PromoC,
      #IF(pr.Tipmed != 'Caja', SUM(pr.Cant), 0) as PromoP,
      COUNT(pr.Docto) AS tienepromo
      FROM Venta
      LEFT JOIN th_pedido ON Venta.Documento LIKE CONCAT('%',th_pedido.Fol_folio)
      LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
      INNER JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId #AND DetalleVet.RutaId = t_ruta.ID_Ruta 
      LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
      {$inner_venta}
      INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa AND c_almacenp.id = '{$almacen}'
      {$left_envase_venta}
      {$left_envase_venta_preventa_nombre_env}
      LEFT JOIN Vw_Cobranza Cob ON Cob.Documento = Venta.Documento AND Cob.Ruta = Venta.RutaId
      LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO AND DiasO.RutaId = Venta.RutaId
      #LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
      LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND Noventas.RutaId = Venta.RutaId AND Noventas.Cliente = Venta.CodCliente 
      LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
      LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
      LEFT JOIN PRegalado pr ON pr.Docto = DetalleVet.Docto AND pr.RutaId = DetalleVet.RutaId AND pr.IdEmpresa = DetalleVet.IdEmpresa AND pr.Cliente = Venta.CodCliente
      WHERE 1 
      {$SQLRutaIN} {$SQLDiaOIN2} {$SQLArticulo1} {$SQLArticulo_Obseq} 
      GROUP BY idVenta

UNION

SELECT DISTINCT
      #DetalleVet.Comisiones AS Comisiones,
      c_almacenp.nombre AS sucursalNombre,
      '' AS idVenta,
      c_almacenp.clave AS Sucursal,
      RelOperaciones.Fecha AS FechaBusq,
      DATE_FORMAT(RelOperaciones.Fecha, '%d-%m-%Y') AS Fecha,
      DATE_FORMAT(th_pedido.Fec_Entrega, '%d-%m-%Y') AS FechaCompromiso,
      IFNULL(RelOperaciones.RutaId, th_pedido.Ruta) AS Ruta,
      t_ruta.cve_ruta AS rutaName,
      th_pedido.Cod_Cliente AS Cliente,
      c_cliente.Cve_Clte as CveCliente,
      c_cliente.RazonSocial AS Responsable,
      IFNULL(c_cliente.limite_credito, 0) AS limite_credito, 
      c_destinatarios.razonsocial AS nombreComercial,
      th_pedido.Pedido AS Folio,
      th_pedido.TipoPedido AS Tipo,
      IFNULL(th_pedido.FormaPag, '') AS metodoPago,
      th_pedido.TotPedidas AS Importe,
      th_pedido.TotIVAPedidas AS IVA,
      IFNULL(th_pedido.TotDescPedidas, 0.00) AS Descuento,
      th_pedido.SubTotPedidas AS Total,
      #DetalleVet.Comisiones AS Comisiones,
      #DetalleVet.Utilidad AS Utilidad,
      #c_articulo.num_multiplo AS Cajas,
      #DetalleVet.Pza AS Piezas,
      th_pedido.Cancelada AS Cancelada,
      IF(RelOperaciones.Tipo = 'Entrega', 'Entrega', 'PreVenta') AS Operacion,
      th_pedido.cve_Vendedor AS vendedorID,
      t_vendedores.Cve_Vendedor as cveVendedor, 
      t_vendedores.Nombre AS Vendedor,
      '' AS Ayudante1,
      '' AS Ayudante2,
      #DetalleVet.DescPorc AS Promociones,
      RelOperaciones.DiaO AS DiaOperativo,
      '' AS DiaOperativoCobranza,
      #DetalleVet.Descripcion AS Articulo,
      IFNULL(Cob.Status, 1) AS StatusCobranza,
      Cob.Documento AS Documento,
      #(IFNULL(Cob.Saldo, IFNULL(th_pedido.TotPedidas, 0)) - IFNULL((SELECT SUM(Abono) FROM DetalleCob WHERE Documento = th_pedido.Pedido), 0)) AS saldoFinal,
      IFNULL(Cob.Saldo, 0) AS Saldo,
      (IFNULL(Cob.Saldo, IFNULL(th_pedido.TotPedidas, 0)) - IFNULL(Cob.Abono, 0)) AS saldoFinal,
      #SUM(IFNULL(DetalleCob.Abono, 0)) AS Abono,
      #IFNULL((SELECT SUM(Abono) FROM DetalleCob WHERE Documento = th_pedido.Pedido), 0) AS Abono,
      IFNULL(Cob.Abono, 0) AS Abono,
      '' AS saldoActual,
      Cob.FechaReg AS fechaRegistro,
      Cob.FechaVence AS fechaVence,
      '' AS tipoDoc,
      '' AS idMotivo,
      '' AS Motivo,
      {$field_envase_preventa_ec}
      {$field_envase_venta_preventa}
      {$field_cantidad_preventa_plastico}
      {$field_cantidad_preventa_cristal}
      {$field_cantidad_preventa_garrafon}
      {$field_tipo_venta_preventa}
      {$field_liquidacion2}

      #SUM(IFNULL(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td_pedido.Pedidas),TRUNCATE((td_pedido.Pedidas/c_articulo.num_multiplo), 0)), 0)) AS cajas_total,
      #SUM(IFNULL(IF(um.mav_cveunimed != 'XBX', (td_pedido.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td_pedido.Pedidas/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, td_pedido.Pedidas, 0)), 0)) as piezas_total,
      #IF(pr.Tipmed = 'Caja', SUM(pr.Cant), 0) as PromoC,
      #IF(pr.Tipmed != 'Caja', SUM(pr.Cant), 0) as PromoP,

      COUNT(pr.Docto) AS tienepromo
      FROM V_Cabecera_Pedido th_pedido
      LEFT JOIN V_Detalle_Pedido td_pedido ON td_pedido.Pedido= th_pedido.Pedido
      #INNER JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th_pedido.Pedido
      LEFT JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th_pedido.Pedido OR RelOperaciones.Folio = th_pedido.Pedido 
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = IFNULL(RelOperaciones.RutaId, th_pedido.Ruta)
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th_pedido.cve_Vendedor
      LEFT JOIN FormasPag ON FormasPag.IdFpag = th_pedido.FormaPag 
      {$inner_preventa}
      #INNER JOIN c_almacenp ON c_almacenp.clave = th_pedido.IdEmpresa AND c_almacenp.id = '{$almacen}'
      LEFT JOIN c_almacenp ON c_almacenp.clave = th_pedido.IdEmpresa AND c_almacenp.id = '{$almacen}'
      {$InnerJoinOperacion}
      {$left_envase_preventa}
      {$left_envase_venta_preventa_nombre_env}
      LEFT JOIN Vw_Cobranza Cob ON CONCAT(Cob.Ruta,'_', Cob.Documento) = th_pedido.Pedido AND Cob.Ruta = th_pedido.Ruta
      LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
      #LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th_pedido.Cod_Cliente
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
      LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
      LEFT JOIN PRegalado pr ON td_pedido.Pedido LIKE CONCAT('%',pr.Docto) 
      WHERE 1 #AND th_pedido.Pedido NOT IN (SELECT Documento FROM Venta WHERE IdEmpresa = c_almacenp.clave)
      {$SQLRutaIN} {$SQLDiaOIN} {$SQLArticulo2} {$SQLArticulo_Obseq} {$SQLFechaIN} 
      GROUP BY Folio
      ) AS ventas WHERE 1 AND IFNULL(ventas.rutaName, '') != '' {$SQLRuta} {$SQLDiaO} {$SQLOperacion} {$SQLCriterio} {$where_credito} {$SQLVendedor} {$SQLCliente} {$SQLFecha} {$SQLTipoV} 
      {$group_by_liquidacion} 
      ORDER BY STR_TO_DATE(ventas.Fecha, '%d-%m-%Y') DESC, ventas.DiaOperativo DESC 
    ";


    if (!($res = mysqli_query($conn, $sql))) {
        echo "1Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    $count = mysqli_num_rows($res);

    $lim = " LIMIT $_page, $limit;";
    $sql .= $lim;
    if (!($res = mysqli_query($conn, $sql))) {
        echo "2Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    // hace una llamada previa al procedimiento almacenado Lis_Facturas

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    }
    if ($page > $total_pages)
        $page = $total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

    $arr = array();
    $i = 0;

    $envplastico = 0;
    $envcristal = 0;
    $envgarrafon = 0;
    $ObsequioC = 0;
    $ObsequioP = 0;
    $cajas_total = '';
    $piezas_total = '';
    $promoC = '';
    $promoP = '';

//Credito = Suma de la columna Total
//Cobranza = Suma de Abonos
//Adeudo = Suma credito - suma abonos

    $total_credito = 0;
    $total_cobranza = 0;
    $total_adeudo = 0;

    while ($row = mysqli_fetch_array($res)) {


        if (isset($_POST['liquidacion'])) {
            $ObsequioC = 0;
            $ObsequioP = 0;
            if ($row['Tipo'] == 'Obsequio') {
                $ObsequioC = $row['cajas_total'];
                $ObsequioP = $row['piezas_total'];
            }
        }

        $status_calc = 1;
        $status = "<b style='color:green;'>Abierta</b>";
        if ($row['Cancelada'] == 1) {
            $status = "<b style='color:red;'>Cancelada</b>";
            $status_calc = 0;
        }

        if ($row['StatusCobranza'] == 2 && $row['Cancelada'] == 0) {
            $status = "<b style='color:blue;'>Pagada</b>";
            $status_calc = 0;
        }


        if (isset($_POST['liquidacion'])) {
            $cajas_total = $row['cajas_total'];
            $piezas_total = $row['piezas_total'];
            $promoC = $row['PromoC'];
            $promoP = $row['PromoP'];
        }

        $DiaOperativo = $row['DiaOperativo'];
        $rutaName = $row['rutaName'];
        $Fecha = $row['Fecha'];

        if (isset($_POST['envase']) || isset($_POST['estado_cuenta'])) {
            $cajas_total = $row['Cantidad_Envase_plastico'];
            $piezas_total = $row['Cantidad_Envase_cristal'];
            $promoC = $row['Cantidad_Envase_garrafon'];
            $envplastico += $cajas_total;
            $envcristal += $piezas_total;
            $envgarrafon += $promoC;

            $DiaOperativo = $row['Fecha'];
            $rutaName = $row['rutaName'];
            $Fecha = $row['DiaOperativo'];
        }

        $timporte = 0;
        $tiva = 0;
        $tdescuento = 0;
        $ttotal = 0;
        $ttotalc = 0;
        $ttotalp = 0;
        $tpromoc = 0;
        $tpromop = 0;
        $tobseqc = 0;
        $tobseqp = 0;

//EL 03-03-2023 ME DIJERON QUE REDONDEARA PARA QUE DE PROBLEMAS EN OTRAS INSTANCIAS Y LUEGO VOLVER A COLOCAR EL REDONDEO

        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id'] = $row['idVenta'];
        $responce->rows[$i]['cell'] = array('',
                                            $row['idVenta'],
                                            $DiaOperativo,
                                            $rutaName,
                                            $row['Operacion'],
                                            $Fecha,
                                            $row['FechaCompromiso'],
                                            $row['Folio'],
                                            $row['Cliente'],
                                            $row['CveCliente'],
                                            $row['Responsable'],
                                            $row['nombreComercial'],
                                            $status,
                                            //$row['Articulo'],
                                            $row['Tipo'],
                                            $row['metodoPago'],
                                            (isset($_POST['estado_cuenta'])) ? ($row['Cantidad_Envase_plastico'] + $row['Cantidad_Envase_cristal'] + $row['Cantidad_Envase_garrafon']) : (number_format($row['Importe'], 2)),
                                            (isset($_POST['estado_cuenta'])) ? ($row['Envase']) : (number_format($row['IVA'], 2)),
                                            number_format($row['Descuento'], 2),
                                            //number_format(round($row['Total'], 2), 2),
                                            (isset($_POST['credito'])) ? (number_format($row['Saldo'], 2)) : (number_format(round($row['Total'] + $row['IVA'], 2), 2)),
                                            number_format($row['Abono'], 2),
                                            number_format($row['saldoFinal'], 2),
                                            //$row['Cajas'],
                                            //$row['Piezas'],
                                            //$row['Cancelada'],
                                            $cajas_total,
                                            $piezas_total,
                                            $promoC,
                                            $promoP,
                                            $ObsequioC,
                                            $ObsequioP,
                                            $row['Vendedor'],
                                            $row['Ayudante1'],
                                            $row['Ayudante2'],
                                            '', //$row['Promociones'],
                                            $row['tienepromo'],
                                            $row['limite_credito']
        );
        $i++;
        if (isset($_POST['credito']) && $status_calc == 1) {
            $total_credito += $row['Total'];
            $total_cobranza += $row['Abono'];
        }
    }

    if (isset($_POST['credito'])) {
        $total_adeudo = $total_credito - $total_cobranza;
        $responce->total_credito = number_format($total_credito, 2);
        $responce->total_cobranza = number_format($total_cobranza, 2);
        $responce->total_adeudo = number_format($total_adeudo, 2);
    }

    if (!isset($_POST['estado_cuenta'])) {
        $importe_venta = " SUM(DetalleVet.Importe) AS Importe, ";
        $total_venta = " (SUM(DetalleVet.Importe)+SUM(DetalleVet.IVA)-SUM(IFNULL(DetalleVet.DescMon, 0.00))) AS Total, ";
        $importe_venta2 = " th_pedido.TotPedidas AS Importe, ";
        $total_venta2 = " th_pedido.SubTotPedidas AS Total, ";

        if ($SQLArticulo1 != '') {
            $importe_venta = " DetalleVet.Importe AS Importe, ";
            $importe_venta2 = " td_pedido.Precio AS Importe, ";
            $total_venta = " IFNULL(DetalleVet.Importe, 0) + IFNULL(DetalleVet.IVA, 0) - IFNULL(DetalleVet.DescMon, 0) AS Total, ";
            $total_venta2 = " IFNULL(td_pedido.Precio, 0)+IFNULL(td_pedido.IVAPedidas, 0)-IFNULL(td_pedido.DescuentoPedidas, 0) AS Total, ";
        }

        $sql_conteos = "
    SELECT SUM(ventas.Importe) AS Importe, SUM(ventas.IVA) AS IVA, SUM(ventas.Descuento) AS Descuento, SUM(ventas.Total) AS Total, 
       #SUM(ventas.cajas_total) AS cajas_total, SUM(ventas.piezas_total) AS piezas_total, 
    (SUM(ventas.cajas_total)+TRUNCATE((SUM(ventas.piezas_total)/ventas.num_multiplo), 0))-SUM(ventas.PromoC) AS cajas_total, 
    (IF(ventas.mav_cveunimed != 'XBX', (SUM(ventas.piezas_total) - (ventas.num_multiplo*TRUNCATE((SUM(ventas.piezas_total)/ventas.num_multiplo), 0))), IF(ventas.num_multiplo = 1, SUM(ventas.piezas_total), 0)))-SUM(ventas.PromoP) AS piezas_total,
       SUM(ventas.PromoC) AS PromoC, SUM(ventas.PromoP) AS PromoP, ventas.nombreComercial,
       SUM(ventas.efectivo) AS efectivo, SUM(ventas.credito) AS credito, SUM(ventas.contado) AS contado,
       SUM(ventas.ObseqC) AS ObseqC, SUM(ventas.ObseqP) AS ObseqP, ventas.FechaBusq, ventas.CveCliente
       FROM (
SELECT DISTINCT Venta.ID,
                      Venta.DiaO                                                                                    AS DiaOperativo,
                      Cobranza.DiaO                                                                                 AS DiaOperativoCobranza,
                      'Venta'                                                                                       AS Operacion,
                      t_ruta.cve_ruta                                                                               AS rutaName,
                      Venta.Fecha                                                                                   AS FechaBusq,
                      DetalleVet.Articulo,
                      Venta.CodCliente                                                                              AS Cliente,
                      c_cliente.Cve_Clte                                                                            as CveCliente,
                      Cobranza.Status                                                                               as StatusCobranza,
                      c_cliente.RazonSocial                                                                         AS Responsable,
                      c_destinatarios.razonsocial                                                                   AS nombreComercial,
                      IFNULL(c_cliente.limite_credito, 0)                                                           AS limite_credito,
                      Venta.Documento                                                                               AS Folio,
                      Venta.Documento                                                                               AS Documento,
                      t_vendedores.Nombre                                                                           AS Vendedor,
                      t_vendedores.Id_Vendedor                                                                      as vendedorID,
                      t_vendedores.Cve_Vendedor                                                                     as cveVendedor,
                      Venta.TipoVta                                                                                 AS Tipo,
                      DetalleCob.Saldo                                                                              AS saldoFinal,
                      DetalleCob.Abono                                                                              AS Abono,
                      Venta.Cancelada                                                                               AS Cancelada,
                      DetalleVet.Importe                                                                            AS Importe,
                      SUM(DetalleVet.IVA)                                                                           AS IVA,
                      SUM(IFNULL(DetalleVet.DescMon, 0.00))                                                         AS Descuento,
                     IF(FormasPag.Forma LIKE '%Efectivo%' AND Venta.TipoVta = 'Contado',
                   sum(DetalleVet.Importe) + sum(DetalleVet.IVA) - IFNULL(DetalleVet.DescMon, 0.00),
                   0)                                      AS efectivo,
                IF(Venta.TipoVta = 'Credito',
                  sum( DetalleVet.Importe) + sum(DetalleVet.IVA) - IFNULL(DetalleVet.DescMon, 0.00),
                   0)                                      AS credito,
                IF(Venta.TipoVta = 'Contado',
                   sum( DetalleVet.Importe) + sum(DetalleVet.IVA) - IFNULL(DetalleVet.DescMon, 0.00),
                   0)                                      AS contado,
                IFNULL(DetalleVet.Importe, 0) + IFNULL(DetalleVet.IVA, 0) -
                IFNULL(DetalleVet.DescMon, 0)              AS Total,
                      c_articulo.num_multiplo,
                      um.mav_cveunimed,
                      IF(DetalleVet.Tipo = 0,
                         (SELECT SUM(Pza) FROM DetalleVet WHERE Docto = Venta.Documento {$SQLArticulo1_IN}),
                        IFNULL(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0,
                                                                    IF(DetalleVet.Pza > 0, DetalleVet.Pza,
                                                                       IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))),
                                       TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza,
                                                    IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) / c_articulo.num_multiplo),
                                                0)),
                                    0))                                                                            AS cajas_total,
                      IF(DetalleVet.Tipo = 0, 0, SUM(IFNULL(IF(um.mav_cveunimed != 'XBX',
                                                               (IF(DetalleVet.Pza > 0, DetalleVet.Pza,
                                                                   IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) -
                                                                (c_articulo.num_multiplo * TRUNCATE(
                                                                        (IF(DetalleVet.Pza > 0, DetalleVet.Pza,
                                                                            IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) /
                                                                         c_articulo.num_multiplo), 0))),
                                                               IF(c_articulo.num_multiplo = 1,
                                                                  IF(DetalleVet.Pza > 0, DetalleVet.Pza,
                                                                     IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), 0)),
                                                            0)))                                                    as piezas_total,

                      IF(pr.Tipmed = 'Caja', (SELECT SUM(Cant) FROM PRegalado WHERE Docto = Venta.Documento),
                         0)                                                                                         AS PromoC,
                      IF(pr.Tipmed != 'Caja', (SELECT SUM(Cant) FROM PRegalado WHERE Docto = Venta.Documento),
                         0)                                                                                         AS PromoP,
                      #IF(pr.Tipmed = 'Caja', SUM(pr.Cant), 0) as PromoC,
                      #IF(pr.Tipmed != 'Caja', SUM(pr.Cant), 0) as PromoP,
                      IF(Venta.TipoVta = 'Obsequio', SUM(IFNULL(IF(um.mav_cveunimed = 'XBX',
                                                                   IF(c_articulo.num_multiplo = 1, 0,
                                                                      IF(DetalleVet.Pza > 0, DetalleVet.Pza,
                                                                         IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))),
                                                                   TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza,
                                                                                IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) /
                                                                             c_articulo.num_multiplo), 0)), 0)),
                         0)                                                                                         AS ObseqC,
                      IF(Venta.TipoVta = 'Obsequio', SUM(IFNULL(IF(um.mav_cveunimed != 'XBX',
                                                                   (IF(DetalleVet.Pza > 0, DetalleVet.Pza,
                                                                       IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) -
                                                                    (c_articulo.num_multiplo * TRUNCATE(
                                                                            (IF(DetalleVet.Pza > 0, DetalleVet.Pza,
                                                                                IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) /
                                                                             c_articulo.num_multiplo), 0))),
                                                                   IF(c_articulo.num_multiplo = 1,
                                                                      IF(DetalleVet.Pza > 0, DetalleVet.Pza,
                                                                         IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), 0)),
                                                                0)),
                         0)                                                                                         AS ObseqP
                          FROM Venta
      LEFT JOIN th_pedido ON Venta.Documento LIKE CONCAT('%',th_pedido.Fol_folio)
      LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
      INNER JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId AND DetalleVet.RutaId = t_ruta.ID_Ruta 
      LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
      {$inner_venta}
      INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa AND c_almacenp.id = '{$almacen}'
      LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento
      LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO AND DiasO.RutaId = Venta.RutaId
      LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
      LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND Noventas.RutaId = Venta.RutaId AND Noventas.Cliente = Venta.CodCliente 
      LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
      LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
      LEFT JOIN PRegalado pr ON pr.Docto = DetalleVet.Docto AND pr.RutaId = DetalleVet.RutaId AND pr.IdEmpresa = DetalleVet.IdEmpresa AND pr.Cliente = Venta.CodCliente
      WHERE Venta.Cancelada = 0
      {$SQLRutaIN} {$SQLDiaOIN2} {$SQLArticulo1} {$SQLArticulo_Obseq} 
      GROUP BY ID

UNION

SELECT DISTINCT
      CONCAT(td_pedido.IdEmpresa, td_pedido.Pedido) as ID,
      RelOperaciones.DiaO AS DiaOperativo,
      '' AS DiaOperativoCobranza,
      'PreVenta' AS Operacion,
      t_ruta.cve_ruta AS rutaName,
      RelOperaciones.Fecha AS FechaBusq,
      td_pedido_art.Articulo,
      th_pedido.Cve_Clte AS Cliente,
      c_cliente.Cve_Clte as CveCliente,
      Cobranza.Status as StatusCobranza,
      c_cliente.RazonSocial AS Responsable,
      c_destinatarios.razonsocial AS nombreComercial,
      IFNULL(c_cliente.limite_credito, 0) AS limite_credito, 
      th_pedido.Pedido AS Folio,
      th_pedido.Pedido AS Documento,
      t_vendedores.Nombre AS Vendedor,
      t_vendedores.Id_Vendedor as vendedorID,
      t_vendedores.Cve_Vendedor as cveVendedor, 
      th_pedido.TipoPedido AS Tipo,
      DetalleCob.Saldo AS saldoFinal,
      DetalleCob.Abono AS Abono,
      th_pedido.Cancelada AS Cancelada,
      {$importe_venta2}
      th_pedido.TotIVAPedidas AS IVA,
      IFNULL(th_pedido.TotDescPedidas, 0.00) AS Descuento,
      IF(FormasPag.Forma LIKE '%Efectivo%' AND th_pedido.TipoPedido = 'Contado', th_pedido.TotPedidas+th_pedido.TotIVAPedidas-IFNULL(th_pedido.TotDescPedidas, 0.00), 0) AS efectivo,
      IF(th_pedido.TipoPedido = 'Credito', th_pedido.TotPedidas+th_pedido.TotIVAPedidas-IFNULL(th_pedido.TotDescPedidas, 0.00), 0) AS credito,
      IF(th_pedido.TipoPedido = 'Contado', th_pedido.TotPedidas+th_pedido.TotIVAPedidas, 0) AS contado,
      th_pedido.TotPedidas AS Total,
    c_articulo.num_multiplo,
    um.mav_cveunimed,
      (IFNULL(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td_pedido_art.Pedidas),TRUNCATE((td_pedido_art.Pedidas/c_articulo.num_multiplo), 0)), 0)) AS cajas_total,
      (IFNULL(IF(um.mav_cveunimed != 'XBX', (SUM(td_pedido_art.Pedidas) - (c_articulo.num_multiplo*TRUNCATE((SUM(td_pedido_art.Pedidas)/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1,SUM(td_pedido_art.Pedidas), 0)), 0)) AS piezas_total,
      IF(pr.Tipmed = 'Caja', (pr.Cant), 0) as PromoC,
      IF(pr.Tipmed != 'Caja', (pr.Cant), 0) as PromoP,
      IF(th_pedido.TipoPedido = 'Obsequio', SUM(IFNULL(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td_pedido_art.Pedidas),TRUNCATE((td_pedido_art.Pedidas/c_articulo.num_multiplo), 0)), 0)), 0) AS ObseqC,
      IF(th_pedido.TipoPedido = 'Obsequio', SUM(IFNULL(IF(um.mav_cveunimed != 'XBX', (td_pedido_art.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td_pedido_art.Pedidas/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, td_pedido.Pedidas, 0)), 0)), 0) AS ObseqP
      FROM V_Cabecera_Pedido th_pedido
      LEFT JOIN V_Detalle_Pedido td_pedido ON td_pedido.Pedido= th_pedido.Pedido
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = th_pedido.Ruta
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th_pedido.cve_Vendedor
      LEFT JOIN FormasPag ON FormasPag.IdFpag = th_pedido.FormaPag 
      {$inner_preventa}
      {$InnerJoinOperacion}
      INNER JOIN c_almacenp ON c_almacenp.clave = th_pedido.IdEmpresa AND c_almacenp.id = '{$almacen}'
      LEFT JOIN Cobranza ON CONCAT(Cobranza.RutaId,'_', Cobranza.Documento) = th_pedido.Pedido
      INNER JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th_pedido.Pedido
      LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
      LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th_pedido.Cod_Cliente
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
      LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
      LEFT JOIN V_Detalle_Pedido td_pedido_art ON td_pedido_art.Pedido= th_pedido.Pedido AND td_pedido_art.Articulo = c_articulo.cve_articulo AND td_pedido_art.Ruta = th_pedido.Ruta
      #LEFT JOIN PRegalado pr ON td_pedido.Pedido IN (CONCAT(pr.RutaId, '_', pr.Docto),pr.Docto) AND pr.SKU = td_pedido.Articulo AND pr.IdEmpresa = th_pedido.IdEmpresa AND pr.RutaId = td_pedido.Ruta AND pr.Cliente = th_pedido.Cod_Cliente
      LEFT JOIN PRegalado pr ON td_pedido_art.Pedido = pr.Docto and pr.SKU = td_pedido_art.Articulo and pr.IdEmpresa = th_pedido.IdEmpresa and pr.RutaId = td_pedido.Ruta #and pr.Cliente = th_pedido.Cod_Cliente
      WHERE th_pedido.Cancelada = 0 AND th_pedido.Pedido NOT IN (SELECT Documento FROM Venta WHERE IdEmpresa = c_almacenp.clave)
      {$SQLRutaIN} {$SQLDiaOIN} {$SQLArticulo2} {$SQLArticulo_Obseq} {$SQLFechaIN} 
      GROUP BY ID, td_pedido_art.Articulo
      ) AS ventas WHERE 1 
        {$SQLRuta} {$SQLDiaO} {$SQLOperacion} {$SQLCriterio} {$where_credito} {$SQLVendedor} {$SQLCliente} {$SQLFecha} {$SQLTipoV}
       ";
        #{$SQLRuta} {$SQLDiaO} {$SQLOperacion} {$SQLCriterio} {$where_credito} {$SQLTipoV}

        if (!($res_conteos = mysqli_query($conn, $sql_conteos))) {
            echo "3Falló la preparación: (" . mysqli_error($conn) . ") " . $sql_conteos;
        }


        $row_conteos = mysqli_fetch_array($res_conteos);
        extract($row_conteos);
        $responce->sql_conteos = $sql_conteos;
        $responce->timporte = number_format($Importe, 2);
        $responce->tiva = number_format($IVA, 2);
        $responce->tdescuento = number_format($Descuento, 2);
        $responce->ttotal = number_format($Total, 2);
        $responce->ttotalc = $cajas_total;
        $responce->ttotalp = number_format($piezas_total, 2);
        $responce->tpromoc = $PromoC;
        $responce->tpromop = $PromoP;
        $responce->tobseqc = $ObseqC;
        $responce->tobseqp = $ObseqP;
        $responce->tefectivo = number_format($efectivo, 2);
        $responce->tcredito = number_format($credito, 2);
        $responce->tcontado = number_format($contado, 2);
        $responce->tventa = number_format($contado + $credito, 2);
        $responce->totrosdepositos = number_format($contado - $efectivo, 2);
        $responce->envplastico = $envplastico;
        $responce->envcristal = $envcristal;
        $responce->envgarrafon = $envgarrafon;

    }
    echo json_encode($responce);
} else if (isset($_POST['consolidado'])) {
    $page = $_POST['page'];  // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx'];  // get index row - i.e. user click to sort
    $sord = $_POST['sord'];  // get the direction
    $almacen = $_POST['almacen'];
    $agente = $_POST['agente'];
    $ruta = $_POST['ruta'];
    $criterio = $_POST['criterio'];
    $diao = $_POST['diao'];
    $operacion = $_POST['operacion'];
    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];

    $SQLCriterio = ""; //$SQLCriterioVenta = ""; $SQLCriterioPreVenta = "";
    if ($criterio) {
        //$SQLCriterioVenta = " AND (Venta.Id LIKE '%".$criterio."%' OR Venta.CodCliente LIKE '%".$criterio."%' OR c_cliente.RazonSocial LIKE '%".$criterio."%' OR Venta.Documento LIKE '%".$criterio."%' OR t_vendedores.Nombre LIKE '%".$criterio."%') ";
        //$SQLCriterioPreVenta = " AND (th_pedido.Cod_Cliente LIKE '%".$criterio."%' OR c_cliente.RazonSocial LIKE '%".$criterio."%' OR td_pedido.Pedido LIKE '%".$criterio."%' OR t_vendedores.Nombre LIKE '%".$criterio."%') ";
        $SQLCriterio = " AND (ventas.Cliente LIKE '%" . $criterio . "%' OR ventas.CveCliente LIKE '%" . $criterio . "%' OR ventas.Responsable LIKE '%" . $criterio . "%') ";
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $inner_venta = " LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo ";
    $inner_preventa = " LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_pedido.Articulo ";

    if (isset($_POST['envase'])) {
        $inner_venta = " INNER JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo AND c_articulo.Usa_Envase = 'S' ";
        $inner_preventa = " INNER JOIN c_articulo ON c_articulo.cve_articulo = td_pedido.Articulo AND c_articulo.Usa_Envase = 'S' ";
    }
    $where_credito = "";
    if (isset($_POST['credito'])) {
        $where_credito = " AND ventas.Tipo = 'Credito' AND ventas.Cancelada = 0";
    }

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit * $page - $limit; // do not put $limit*($page - 1)

    if (!$sidx) $sidx = 1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $_page = 0;

    if (intval($page) > 0) $_page = ($page - 1) * $limit;

    if (!isset($_criterio) && $_criterio == "") {
        /*
                $sql = "SELECT c_cliente.Cve_Clte cliente, c_cliente.RazonComercial ,Sum(ifnull(Cobranza.Saldo,0)) Total,
        sum(ifnull(DetalleCob.Abono,0)) Abono,Sum(Cobranza.Saldo)-sum(ifnull(DetalleCob.Abono,0))  Saldo,
               c_cliente.limite_credito limiteCredito,
               c_cliente.limite_credito - (Sum(Cobranza.Saldo) - sum(ifnull(DetalleCob.Abono, 0))) creditoDisponible, Cobranza.Cliente cveCliente,  c_almacenp.nombre almacen
        from Vw_Cobranza Cobranza left join DetalleCob on Cobranza.Documento=DetalleCob.Documento and Cobranza.RutaId=DetalleCob.RutaId
        LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Cobranza.Cliente
        LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
                 left join c_almacenp on c_almacenp.clave = Cobranza.IdEmpresa
        where Cobranza.Status=1
        group by c_cliente.Cve_Clte";
        */
        $sql = "SELECT c_cliente.Cve_Clte                                                                  cliente,
       c_cliente.RazonComercial,
       Sum(ifnull(Vw_Cobranza.Saldo, 0))                                                      Total,
       sum(ifnull(Vw_Cobranza.Abono, 0))                                                    Abono,
       Sum(Vw_Cobranza.Saldo) - sum(ifnull(Vw_Cobranza.Abono, 0))                              Saldo,
       c_cliente.limite_credito                                                            limiteCredito,
       c_cliente.limite_credito - (Sum(Vw_Cobranza.Saldo) - sum(ifnull(Vw_Cobranza.Abono, 0))) creditoDisponible,
       Vw_Cobranza.Cliente                                                                    cveCliente,
       c_almacenp.nombre                                                                   almacen
from Vw_Cobranza
         LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario = Vw_Cobranza.Cliente
         LEFT JOIN c_cliente ON c_cliente.Cve_Clte = c_destinatarios.Cve_Clte
         left join c_almacenp on c_almacenp.clave = Vw_Cobranza.IdEmpresa
where Vw_Cobranza.Status = 1
group by c_cliente.Cve_Clte
";
    } else {
        /*
        $sql = "select c_cliente.Cve_Clte                                                                  cliente,
       c_cliente.RazonComercial,
       Sum(ifnull(Cobranza.Saldo, 0))                                                      Total,
       sum(ifnull(DetalleCob.Abono, 0))                                                    Abono,
       Sum(Cobranza.Saldo) - sum(ifnull(DetalleCob.Abono, 0))                              Saldo,
       c_cliente.limite_credito                                                            limiteCredito,
       c_cliente.limite_credito - (Sum(Cobranza.Saldo) - sum(ifnull(DetalleCob.Abono, 0))) creditoDisponible,
       Cobranza.Cliente                                                                    cveCliente,
       c_almacenp.nombre                                                                   almacen
from Vw_Cobranza Cobranza
         left join DetalleCob on Cobranza.Documento = DetalleCob.Documento and Cobranza.RutaId = DetalleCob.RutaId
         LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario = Cobranza.Cliente
         LEFT JOIN c_cliente ON c_cliente.Cve_Clte = c_destinatarios.Cve_Clte
         left join c_almacenp on c_almacenp.clave = Cobranza.IdEmpresa
where Cobranza.Status = 1
and (c_cliente.Cve_Clte= '$_criterio' or
    c_cliente.RazonComercial like '%$_criterio%')
group by c_cliente.Cve_Clte";
*/
        $sql = "SELECT c_cliente.Cve_Clte                                                                  cliente,
       c_cliente.RazonComercial,
       Sum(ifnull(Vw_Cobranza.Saldo, 0))                                                      Total,
       sum(ifnull(Vw_Cobranza.Abono, 0))                                                    Abono,
       Sum(Vw_Cobranza.Saldo) - sum(ifnull(Vw_Cobranza.Abono, 0))                              Saldo,
       c_cliente.limite_credito                                                            limiteCredito,
       c_cliente.limite_credito - (Sum(Vw_Cobranza.Saldo) - sum(ifnull(Vw_Cobranza.Abono, 0))) creditoDisponible,
       Vw_Cobranza.Cliente                                                                    cveCliente,
       c_almacenp.nombre                                                                   almacen
from Vw_Cobranza
         LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario = Vw_Cobranza.Cliente
         LEFT JOIN c_cliente ON c_cliente.Cve_Clte = c_destinatarios.Cve_Clte
         left join c_almacenp on c_almacenp.clave = Vw_Cobranza.IdEmpresa
where Vw_Cobranza.Status = 1 and (c_cliente.Cve_Clte LIKE '%$_criterio%' OR c_cliente.RazonComercial like '%$_criterio%')
group by c_cliente.Cve_Clte
";
    }

    if (!($res = mysqli_query($conn, $sql))) {
        echo "4Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }
    $count = mysqli_num_rows($res);

    $lim = " LIMIT $_page, $limit;";
    $sql .= $lim;
    if (!($res = mysqli_query($conn, $sql))) {
        echo "5Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    // hace una llamada previa al procedimiento almacenado Lis_Facturas

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    }
    if ($page > $total_pages)
        $page = $total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

    $arr = array();
    $i = 0;


    while ($row = mysqli_fetch_array($res)) {
        $ObsequioC = 0;
        $ObsequioP = 0;
        if ($row['Tipo'] == 'Obsequio') {
            $ObsequioC = $row['cajas_total'];
            $ObsequioP = $row['piezas_total'];
        }
        $status = "<b style='color:green;'>Abierta</b>";
        if ($row['Cancelada'] == 1)
            $status = "<b style='color:red;'>Cancelada</b>";

        if ($row['StatusCobranza'] == 2 && $row['Cancelada'] == 0)
            $status = "<b style='color:blue;'>Pagada</b>";

        $timporte = 0;
        $tiva = 0;
        $tdescuento = 0;
        $ttotal = 0;
        $ttotalc = 0;
        $ttotalp = 0;
        $tpromoc = 0;
        $tpromop = 0;
        $tobseqc = 0;
        $tobseqp = 0;

        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['cell'] = array('',
                                            $row['cliente'],
                                            $row['RazonComercial'],
                                            number_format($row['Total'], 2),
                                            number_format($row['Abono'], 2),
                                            number_format($row['Saldo'], 2),
                                            number_format($row['limiteCredito'], 2),
                                            number_format($row['creditoDisponible'], 2),
                                            $row['cveCliente'],
                                            $row['almacen'],
        );
        $i++;
    }

    $sql_conteos = "
    SELECT SUM(ventas.Importe) AS Importe, SUM(ventas.IVA) AS IVA, SUM(ventas.Descuento) AS Descuento, SUM(ventas.Total) AS Total, 
       SUM(ventas.cajas_total) AS cajas_total, SUM(ventas.piezas_total) AS piezas_total, ventas.limite_credito as limite_credito, 
       SUM(ventas.PromoC) AS PromoC, SUM(ventas.PromoP) AS PromoP, ventas.nombreComercial, 
       SUM(ventas.ObseqC) AS ObseqC, SUM(ventas.ObseqP) AS ObseqP, ventas.CveCliente
       FROM (
SELECT DISTINCT
      Venta.ID,
      Venta.DiaO AS DiaOperativo,
      Cobranza.DiaO AS DiaOperativoCobranza,
      'Venta' AS Operacion,
      t_ruta.cve_ruta AS rutaName,
      Venta.CodCliente AS Cliente,
      c_cliente.Cve_Clte as CveCliente,
      c_cliente.RazonSocial AS Responsable,
      c_destinatarios.razonsocial AS nombreComercial,
      IFNULL(c_cliente.limite_credito, 0) AS limite_credito,
      Venta.Documento AS Folio,
      Venta.Documento AS Documento,
      t_vendedores.Nombre AS Vendedor,
      t_vendedores.Id_Vendedor as vendedorID,
      t_vendedores.Cve_Vendedor as cveVendedor, 
      Venta.TipoVta AS Tipo,
      DetalleCob.Saldo AS saldoFinal,
      DetalleCob.Abono AS Abono,
      Venta.Cancelada AS Cancelada,
      Venta.Subtotal AS Importe,
      Venta.IVA AS IVA,
      SUM(IFNULL(DetalleVet.DescMon, 0.00)) AS Descuento,
      Venta.TOTAL AS Total,
      IF(DetalleVet.Tipo = 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), SUM(IFNULL(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))),TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0)), 0))) AS cajas_total,
      IF(DetalleVet.Tipo = 0, 0, SUM(IFNULL(IF(um.mav_cveunimed != 'XBX', (IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) - (c_articulo.num_multiplo*TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), 0)), 0))) as piezas_total,
      IF(pr.Tipmed = 'Caja', SUM(pr.Cant), 0) as PromoC,
      IF(pr.Tipmed != 'Caja', SUM(pr.Cant), 0) as PromoP,
      IF(Venta.TipoVta = 'Obsequio', SUM(IFNULL(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))),TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0)), 0)), 0) AS ObseqC,
      IF(Venta.TipoVta = 'Obsequio', SUM(IFNULL(IF(um.mav_cveunimed != 'XBX', (IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) - (c_articulo.num_multiplo*TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), 0)), 0)), 0) AS ObseqP
      FROM Venta
      LEFT JOIN th_pedido ON Venta.Documento LIKE CONCAT('%',th_pedido.Fol_folio)
      LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
      INNER JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId AND DetalleVet.RutaId = t_ruta.ID_Ruta 
      LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
      {$inner_venta}
      INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa AND c_almacenp.id = '{$almacen}'
      LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento
      LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO AND DiasO.RutaId = Venta.RutaId
      LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
      LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND Noventas.RutaId = Venta.RutaId AND Noventas.Cliente = Venta.CodCliente 
      LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
      LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
      LEFT JOIN PRegalado pr ON pr.Docto = DetalleVet.Docto AND pr.RutaId = DetalleVet.RutaId AND pr.IdEmpresa = DetalleVet.IdEmpresa AND pr.Cliente = Venta.CodCliente
      WHERE Venta.Cancelada = 0
      GROUP BY ID

UNION

SELECT DISTINCT
      CONCAT(td_pedido.IdEmpresa, td_pedido.Pedido) as ID,
      RelOperaciones.DiaO AS DiaOperativo,
      '' AS DiaOperativoCobranza,
      'PreVenta' AS Operacion,
      t_ruta.cve_ruta AS rutaName,
      th_pedido.Cve_Clte AS Cliente,
      c_cliente.Cve_Clte as CveCliente,
      c_cliente.RazonSocial AS Responsable,
      c_destinatarios.razonsocial AS nombreComercial,
      IFNULL(c_cliente.limite_credito, 0) AS limite_credito,
      th_pedido.Pedido AS Folio,
      th_pedido.Pedido AS Documento,
      t_vendedores.Nombre AS Vendedor,
      t_vendedores.Id_Vendedor as vendedorID,
      t_vendedores.Cve_Vendedor as cveVendedor, 
      th_pedido.TipoPedido AS Tipo,
      DetalleCob.Saldo AS saldoFinal,
      DetalleCob.Abono AS Abono,
      th_pedido.Cancelada AS Cancelada,
      th_pedido.TotPedidas AS Importe,
      th_pedido.TotIVAPedidas AS IVA,
      IFNULL(th_pedido.TotDescPedidas, 0.00) AS Descuento,
      th_pedido.SubTotPedidas AS Total,
      (IFNULL(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td_pedido_art.Pedidas),TRUNCATE((td_pedido_art.Pedidas/c_articulo.num_multiplo), 0)), 0)) AS cajas_total,
      (IFNULL(IF(um.mav_cveunimed != 'XBX', (td_pedido_art.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td_pedido_art.Pedidas/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, td_pedido_art.Pedidas, 0)), 0)) AS piezas_total,
      IF(pr.Tipmed = 'Caja', SUM(pr.Cant), 0) as PromoC,
      IF(pr.Tipmed != 'Caja', SUM(pr.Cant), 0) as PromoP,
      IF(th_pedido.TipoPedido = 'Obsequio', SUM(IFNULL(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td_pedido_art.Pedidas),TRUNCATE((td_pedido_art.Pedidas/c_articulo.num_multiplo), 0)), 0)), 0) AS ObseqC,
      IF(th_pedido.TipoPedido = 'Obsequio', SUM(IFNULL(IF(um.mav_cveunimed != 'XBX', (td_pedido_art.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td_pedido_art.Pedidas/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, td_pedido.Pedidas, 0)), 0)), 0) AS ObseqP
      FROM V_Cabecera_Pedido th_pedido
      LEFT JOIN V_Detalle_Pedido td_pedido ON td_pedido.Pedido= th_pedido.Pedido
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = th_pedido.Ruta
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th_pedido.cve_Vendedor
      LEFT JOIN FormasPag ON FormasPag.IdFpag = th_pedido.FormaPag 
      {$inner_preventa}
      INNER JOIN c_almacenp ON c_almacenp.clave = th_pedido.IdEmpresa AND c_almacenp.id = '{$almacen}'
      {$InnerJoinOperacion}
      LEFT JOIN Cobranza ON CONCAT(Cobranza.RutaId,'_', Cobranza.Documento) = th_pedido.Pedido
      INNER JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th_pedido.Pedido
      LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
      LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
      LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th_pedido.Cod_Cliente
      LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
      LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
      LEFT JOIN V_Detalle_Pedido td_pedido_art ON td_pedido_art.Pedido= th_pedido.Pedido AND td_pedido_art.Articulo = c_articulo.cve_articulo AND td_pedido_art.Ruta = th_pedido.Ruta
      #LEFT JOIN PRegalado pr ON td_pedido.Pedido IN (CONCAT(pr.RutaId, '_', pr.Docto),pr.Docto) AND pr.SKU = td_pedido.Articulo AND pr.IdEmpresa = th_pedido.IdEmpresa AND pr.RutaId = td_pedido.Ruta AND pr.Cliente = th_pedido.Cod_Cliente
      LEFT JOIN PRegalado pr ON td_pedido_art.Pedido = pr.Docto #and pr.SKU = td_pedido_art.Articulo and pr.IdEmpresa = th_pedido.IdEmpresa and pr.RutaId = td_pedido.Ruta and pr.Cliente = th_pedido.Cod_Cliente
      WHERE th_pedido.Cancelada = 0 AND th_pedido.Pedido NOT IN (SELECT Documento FROM Venta WHERE IdEmpresa = '{$almacen}')
      GROUP BY ID
      ) AS ventas WHERE 1 {$SQLRuta} {$SQLDiaO} {$SQLOperacion} {$SQLCriterio} {$where_credito} {$SQLTipoV} 

       ";

    if (!($res_conteos = mysqli_query($conn, $sql_conteos))) {
        echo "6Falló la preparación: (" . mysqli_error($conn) . ") " . $sql_conteos;
    }


    $row_conteos = mysqli_fetch_array($res_conteos);
    extract($row_conteos);
    $responce->timporte = number_format($Importe, 2);
    $responce->tiva = number_format($IVA, 2);
    $responce->tdescuento = number_format($Descuento, 2);
    $responce->ttotal = number_format($Total, 2);
    $responce->ttotalc = $cajas_total;
    $responce->ttotalp = number_format($piezas_total, 2);
    $responce->tpromoc = $PromoC;
    $responce->tpromop = $PromoP;
    $responce->tobseqc = $ObseqC;
    $responce->tobseqp = $ObseqP;


    echo json_encode($responce);
} else if (isset($_POST['bitacora'])) {
    $page = $_POST['page'];  // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx'];  // get index row - i.e. user click to sort
    $sord = $_POST['sord'];  // get the direction
    $almacen = $_POST['almacen'];
    $agente = $_POST['agente'];
    $ruta = $_POST['ruta'];
    $criterio = $_POST['criterio'];
    $diao = $_POST['diao'];
    $operacion = $_POST['operacion'];
    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];


    $SQLCriterio = "";
    if ($criterio) {
        $SQLCriterio = " AND (BitacoraTiempos.Codigo LIKE '%" . $criterio . "%' OR BitacoraTiempos.DiaO LIKE '%" . $criterio . "%'
                        OR BitacoraTiempos.Descripcion LIKE '%" . $criterio . "%' OR c_cliente.RazonComercial LIKE '%" . $criterio . "%' 
                        OR c_destinatarios.razonsocial LIKE '%" . $criterio . "%' OR t_ruta.cve_ruta LIKE '%" . $criterio . "%' 
                        OR c_cliente.Cve_Clte LIKE '%" . $criterio . "%' 
                        OR t_vendedores.Nombre LIKE '%" . $criterio . "%') ";
    }

    $SQLDiaO = "";
    if ($diao) {
        $SQLDiaO = " AND BitacoraTiempos.DiaO = '{$diao}' ";
    }

    $SQLRuta = "";
    if ($ruta) {
        $SQLRuta = " AND t_ruta.cve_ruta = '{$ruta}' ";
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit * $page - $limit; // do not put $limit*($page - 1)

    if (!$sidx) $sidx = 1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    /*
        $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
        if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
        $charset = mysqli_fetch_array($res_charset)['charset'];
        mysqli_set_charset($conn , $charset);
    */
    $_page = 0;

    if (intval($page) > 0) $_page = ($page - 1) * $limit;


    $sql = "
    SELECT DISTINCT
        BitacoraTiempos.Codigo as codigo,
      BitacoraTiempos.DiaO as diaOpB,
      BitacoraTiempos.Descripcion as descripcion,
      CONVERT(IFNULL(c_cliente.RazonComercial, '') USING utf8) as Responsable,
      #'' as Responsable,
      CONVERT(IFNULL(c_destinatarios.razonsocial, '') USING utf8) as nombreComercial,
      #'' as nombreComercial,
      DATE_FORMAT(DiasO.Fecha, '%d-%m-%Y') as fechaDO,
      BitacoraTiempos.HI AS HI,
      IF(BitacoraTiempos.Visita = 1, BitacoraTiempos.HF, BitacoraTiempos.HI) AS HF,
      DATE_FORMAT(BitacoraTiempos.HI, '%d-%m-%Y %H:%i:%S') as horaIni,
      DATE_FORMAT(IF(BitacoraTiempos.Visita = 1, BitacoraTiempos.HF, BitacoraTiempos.HI), '%d-%m-%Y %H:%i:%S') as horaFin,
      IFNULL(REPLACE(BitacoraTiempos.HT, '-', ''), '00:00:00') as tiempoTraslado,
      c_cliente.Cve_Clte,
      #REPLACE(BitacoraTiempos.TS, '-', '') as tiempoServicio,
      DATE_FORMAT(IF(BitacoraTiempos.Visita = 1, SEC_TO_TIME((TIMESTAMPDIFF(SECOND, BitacoraTiempos.HI, BitacoraTiempos.HF))), '00:00:00'), '%H:%i:%S') as tiempoServicio,
      IF(BitacoraTiempos.Visita = 1, 1, 0) AS visita,
      IF(BitacoraTiempos.Programado = 1, 1, 0) AS programado,
      t_ruta.cve_ruta as rutaName,
      IF(BitacoraTiempos.Cerrado = 1, 1, 0) AS cerrado,
      BitacoraTiempos.IdVendedor as vendedorID,
      t_vendedores.Nombre as Vendedor,
      t_vendedores.Cve_Vendedor as cveVendedor, 
      BitacoraTiempos.Tip as tip,
      BitacoraTiempos.latitude as latitud,
      BitacoraTiempos.longitude as longitud,
      BitacoraTiempos.pila as pila
      
      FROM BitacoraTiempos
      
      LEFT JOIN t_ruta ON t_ruta.ID_Ruta = BitacoraTiempos.RutaId
      LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = BitacoraTiempos.IdVendedor
      LEFT JOIN c_almacenp al ON al.id= '{$almacen}' 
      INNER JOIN DiasO on DiasO.DiaO = BitacoraTiempos.DiaO AND DiasO.IdEmpresa = al.clave AND DiasO.RutaId = t_ruta.ID_Ruta
      LEFT JOIN c_destinatarios on c_destinatarios.id_destinatario = BitacoraTiempos.Codigo
      LEFT JOIN c_cliente on c_destinatarios.Cve_Clte = c_cliente.Cve_Clte
      
      WHERE 1 {$SQLDiaO} {$SQLRuta} {$SQLCriterio} 

      ORDER BY HI
    ";


    if (!($res = mysqli_query($conn, $sql))) {
        echo "4Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    $count = mysqli_num_rows($res);

    $lim = " LIMIT $_page, $limit;";
    $sql .= $lim;
    if (!($res = mysqli_query($conn, $sql))) {
        echo "5Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    //echo $sql;
    // hace una llamada previa al procedimiento almacenado Lis_Facturas

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    }
    if ($page > $total_pages)
        $page = $total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
//echo "OK here -> ".$count;
    $arr = array();
    $i = 0;

    $v_ttraslado_i = array();
    $v_ttraslado_f = array();
    $v_ttraslado_v = array();
    $v_ttraslado = array();

    /*
    while ($row = mysqli_fetch_array($res))
    {
        if($row['HI'])
            $v_ttraslado_i[$i] = new DateTime($row['HI']);
        else
            $v_ttraslado_i[$i] = new DateTime('00:00:00');

        if($row['HF'])
            $v_ttraslado_f[$i] = new DateTime($row['HF']);
        else
            $v_ttraslado_f[$i] = new DateTime('00:00:00');

        $v_ttraslado_v[$i] = $row['visita'];
        //$d = $hi->diff($hf);
        //$tiempoTraslado = $d->format('%H:%I:%S');
        $i++;
        //var_dump($v_ttraslado_i);
        //var_dump($v_ttraslado_f);
        //var_dump($v_ttraslado_v);
    }

    for($g=0; $g<$i; $g++)
    {
        //echo $v_ttraslado_i[$g+1]." - ".$v_ttraslado_f[$g];

        if($v_ttraslado_v[$g] == '1' || $g == 0)
        {
            $d = $v_ttraslado_i[$g+1]->diff($v_ttraslado_f[$g]);
            $tiempoTraslado = $d->format('%H:%I:%S');
            $v_ttraslado[$g] = $tiempoTraslado;
        }
        else
        {
            $v_ttraslado[$g] = '00:00:00';
            //echo $v_ttraslado[$g]." - ".$i." - ".$g;
        }
        //if($g >= $limit) break;
        //var_dump($v_ttraslado);
    }
    */
//echo "OK here";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "5Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $i = 0;
    $responce->query = $sql;
    while ($row = mysqli_fetch_array($res)) {

        //$row = array_map('utf8_encode', $row);

        $responce->rows[$i]['cell'] = array('',
                                            $row['rutaName'],
                                            $row['diaOpB'],
                                            $row['fechaDO'],
                                            ($row['descripcion']),
                                            ($row['Responsable']),
                                            $row['Cve_Clte'],
                                            ($row['nombreComercial']),
                                            $row['horaIni'],
                                            $row['horaFin'],
                                            //$v_ttraslado[$i],
                                            $row['tiempoTraslado'],
                                            $row['tiempoServicio'],
                                            $row['visita'],
                                            $row['programado'],
                                            $row['cerrado'],
                                            $row['vendedorId'],
                                            $row['Vendedor'],
                                            $row['tip'],
                                            $row['latitud'],
                                            $row['longitud'],
                                            $row['pila']
        );
        $i++;
    }

    echo json_encode($responce);
} else if (isset($_POST['bitacoranv'])) {
    $page = $_POST['page'];  // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx'];  // get index row - i.e. user click to sort
    $sord = $_POST['sord'];  // get the direction
    $almacen = $_POST['almacen'];
    $ruta = $_POST['ruta'];
    $diao = $_POST['diao'];
    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////


    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit * $page - $limit; // do not put $limit*($page - 1)

    if (!$sidx) $sidx = 1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $_page = 0;

    if (intval($page) > 0) $_page = ($page - 1) * $limit;

    /*
        $sql = "
        SELECT  r.cve_ruta, d.id_destinatario, d.razonsocial, d.direccion, d.colonia, d.postal, d.ciudad, d.estado, d.latitud, d.longitud
    FROM RelDayCli rdc
    LEFT JOIN c_destinatarios d ON d.id_destinatario = rdc.Id_Destinatario
    LEFT JOIN t_ruta r ON r.ID_Ruta = rdc.Cve_Ruta
    WHERE r.cve_ruta = '{$ruta}' AND rdc.Id_Destinatario NOT IN (SELECT Codigo FROM BitacoraTiempos WHERE Visita = 1 AND DiaO = '{$diao}')
        ";
    */
    $sql = "
SELECT  r.cve_ruta, d.id_destinatario, d.Cve_Clte, d.razonsocial, d.direccion, d.colonia, d.postal, d.ciudad, d.estado, d.latitud, d.longitud
FROM TH_SecVisitas rdc
LEFT JOIN c_destinatarios d ON d.id_destinatario = rdc.CodCli
LEFT JOIN t_ruta r ON r.ID_Ruta = rdc.RutaId
INNER JOIN DiasO di ON rdc.Fecha = di.Fecha AND r.ID_Ruta = di.RutaId AND di.DiaO = '{$diao}'
WHERE r.cve_ruta = '{$ruta}' AND rdc.CodCli NOT IN (SELECT Codigo FROM BitacoraTiempos WHERE Visita = 1 AND DiaO = '{$diao}')";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "4Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    $count = mysqli_num_rows($res);

    $lim = " LIMIT $_page, $limit;";
    $sql .= $lim;
    if (!($res = mysqli_query($conn, $sql))) {
        echo "5Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    // hace una llamada previa al procedimiento almacenado Lis_Facturas

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    }
    if ($page > $total_pages)
        $page = $total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

    $arr = array();
    $i = 0;

    while ($row = mysqli_fetch_array($res)) {

        //$row = array_map('utf8_encode', $row);
        $responce->rows[$i]['cell'] = array(
            $row['cve_ruta'],
            $row['Cve_Clte'],
            $row['razonsocial'],
            ($row['direccion']),
            ($row['colonia']),
            $row['postal'],
            ($row['ciudad']),
            $row['estado'],
            $row['latitud'],
            $row['longitud']
        );
        $i++;
    }

    echo json_encode($responce);
} else if (isset($_POST['noventas'])) {
    $page = $_POST['page'];  // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx'];  // get index row - i.e. user click to sort
    $sord = $_POST['sord'];  // get the direction
    $almacen = $_POST['almacen'];
    $ruta = $_POST['ruta'];
    $diao = $_POST['diao'];
    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////


    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit * $page - $limit; // do not put $limit*($page - 1)

    if (!$sidx) $sidx = 1;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $_page = 0;

    if (intval($page) > 0) $_page = ($page - 1) * $limit;

    /*
        $sql = "
        SELECT  r.cve_ruta, d.id_destinatario, d.razonsocial, d.direccion, d.colonia, d.postal, d.ciudad, d.estado, d.latitud, d.longitud
    FROM RelDayCli rdc
    LEFT JOIN c_destinatarios d ON d.id_destinatario = rdc.Id_Destinatario
    LEFT JOIN t_ruta r ON r.ID_Ruta = rdc.Cve_Ruta
    WHERE r.cve_ruta = '{$ruta}' AND rdc.Id_Destinatario NOT IN (SELECT Codigo FROM BitacoraTiempos WHERE Visita = 1 AND DiaO = '{$diao}')
        ";
    */
    $sql = "SELECT DATE_FORMAT(n.Fecha, '%d-%m-%Y') AS Fecha, r.cve_ruta, n.Cliente, d.razonsocial, m.Motivo, d.Cve_Clte
            FROM Noventas n
            LEFT JOIN t_ruta r ON r.ID_Ruta = n.RutaId
            LEFT JOIN c_destinatarios d ON d.id_destinatario = n.Cliente 
            LEFT JOIN MotivosNoVenta m ON m.IdMot = n.MotivoId
            LEFT JOIN c_almacenp a ON a.clave = n.IdEmpresa
            WHERE a.id = {$almacen} AND r.cve_ruta = '{$ruta}' AND n.DiaO = {$diao} 
            ";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "4Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    $count = mysqli_num_rows($res);

    $lim = " LIMIT $_page, $limit;";
    $sql .= $lim;
    if (!($res = mysqli_query($conn, $sql))) {
        echo "5Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    // hace una llamada previa al procedimiento almacenado Lis_Facturas

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    }
    if ($page > $total_pages)
        $page = $total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;


    $arr = array();
    $i = 0;

    while ($row = mysqli_fetch_array($res)) {

        //$row = array_map('utf8_encode', $row);
        $responce->rows[$i]['cell'] = array(
            '',
            $row['Fecha'],
            $row['cve_ruta'],
            $row['Cve_Clte'],
            $row['razonsocial'],
            $row['Motivo']
        );
        $i++;
    }

    echo json_encode($responce);
} else if (isset($_POST['cobranzadet']) && $_POST['action'] == 'realizar_abono') {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $abono = $_POST['abono'];
    $cliente = $_POST['cliente'];
    $ruta = $_POST['ruta'];
    $diao = $_POST['diao'];
    $folio = $_POST['folio'];
    $saldo_restante = $_POST['saldo_restante'];
    $almacen = $_POST['almacen'];
    $forma_pago = $_POST['forma_pago'];

    $sql = "INSERT INTO DetalleCob(IdCobranza, Abono, Fecha, RutaId, SaldoAnt, Saldo, FormaP, DiaO, Documento, Cliente, IdEmpresa, Cancelada)
            VALUES((SELECT DISTINCT id FROM Cobranza WHERE Documento = '{$folio}' AND RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$ruta}')), '$abono', NOW(), (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$ruta}'), '{$saldo_restante}', ($saldo_restante-$abono),'{$forma_pago}', '{$diao}', '{$folio}', '{$cliente}', (SELECT clave FROM c_almacenp WHERE id = '{$almacen}'), 0)";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "17Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if (($saldo_restante - $abono) == 0) {
        $sql = "UPDATE Cobranza SET Status = 2 WHERE Documento = '{$folio}'";
        if (!($res = mysqli_query($conn, $sql))) {
            echo "27Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
    }

    echo json_encode($saldo_restante - $abono);

} else if (isset($_POST['cobranzadet']) && $_POST['action'] == 'realizar_abono_grupo') {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //$abono   = $_POST['abono'];
    //$cliente = $_POST['cliente'];
    //$ruta    = $_POST['ruta'];
    //$diao    = $_POST['diao'];
    $folios = $_POST['folios'];
    //$saldo_restante = $_POST['saldo_restante'];
    //$almacen        = $_POST['almacen'];
    $forma_pago = $_POST['forma_pago'];

    $sql = "INSERT INTO DetalleCob(IdCobranza, Abono, Fecha, RutaId, SaldoAnt, Saldo, FormaP, DiaO, Documento, Cliente, IdEmpresa, Cancelada) 
            (SELECT id, 0, FechaReg, RutaId, Saldo, 0, '{$forma_pago}', DiaO, Documento, Cliente, IdEmpresa, 0 FROM Cobranza WHERE Documento IN ({$folios}))";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "X17Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    $sql = "UPDATE Cobranza SET Status = 2 WHERE Documento IN ({$folios})";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "X27Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    echo json_encode(0);

}


if (isset($_GET) && !empty($_GET) && $_GET['action'] == 'PromocionVenta') {
    $page = $_GET['page'];
    $rows = $_GET['rows'];
    $limit = $_GET['rows']; // get how many rows we want to have into the grid
    $search = $_GET['search'];
    $almacen = $_GET['almacen'];
    $folio = $_GET['folio'];
    $ruta_folio = $_GET['ruta'];

    $start = $limit * $page - $limit; // do not put $limit*($page - 1)

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_RutaEntrega = "";
    if ($ruta_folio) {
        $sql_ruta = "SELECT venta_preventa FROM t_ruta WHERE cve_ruta = '$ruta_folio'";
        if (!($res = mysqli_query($conn, $sql_ruta))) {
            echo "7Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $row_ruta = mysqli_fetch_array($res);
        if ($row_ruta['venta_preventa'] == 2)//es ruta entrega
        {
            $sql_RutaEntrega = " AND pr.Tipo = 'E' ";
        }
    }


    $sql = "SELECT DISTINCT pr.SKU AS cve_articulo, a.des_articulo, CONCAT('(', r.cve_ruta, ') ', r.descripcion) AS ruta, 
                        d.razonsocial AS cliente, d.Cve_Clte as CveCliente, pr.Cant, IFNULL(pr.Tipmed, '') AS unidad_medida
                FROM PRegalado pr 
                LEFT JOIN c_articulo a ON a.cve_articulo = pr.SKU
                LEFT JOIN t_ruta r ON r.ID_Ruta = pr.RutaId
                LEFT JOIN c_destinatarios d ON d.id_destinatario = pr.Cliente
                LEFT JOIN c_almacenp al ON al.clave = pr.IdEmpresa
                WHERE  al.id = {$almacen} AND '$folio' = pr.Docto AND IFNULL(d.razonsocial, '') != '' {$sql_RutaEntrega}
                ORDER BY des_articulo";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "7Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);
    $sql .= " LIMIT $start, $limit;";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "8Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    }
    if ($page > $total_pages)
        $page = $total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        //$row=array_map('utf8_encode', $row);
        extract($row);
        $Total = ($Importe + $IVA - $Descuento);
        $Total = number_format($Total, 2);
        $responce->rows[$i]['idVenta'] = $idVenta;
        $responce->rows[$i]['cell'] = array(
            'cve_articulo'  => $cve_articulo,
            'descripcion'   => $des_articulo,
            'cve_ruta'      => $ruta,
            'cliente'       => $cliente,
            'cantidad'      => number_format($Cant, 2),
            'unidad_medida' => $unidad_medida
        );
        $i++;
    };
    echo json_encode($responce);
} else if (isset($_GET) && !empty($_GET) && $_GET['action'] == 'PromocionVenta2') {
    $page = $_GET['page'];
    $rows = $_GET['rows'];
    $limit = $_GET['rows']; // get how many rows we want to have into the grid
    $search = $_GET['search'];
    $almacen = $_GET['almacen'];
    $folio = $_GET['folio'];
    if (is_array($folio) && count($folio) > 0 && $folio[0] != '') {
        $folio = implode(",", $folio);

    } else {
        $folio = '';
    }
    if (preg_match('/^,+$/', $folio)) {
        $folio = '';
    }
    $start = $limit * $page - $limit; // do not put $limit*($page - 1)

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    /*Pedidos listos para embarcar*/
    $sql = "SELECT DISTINCT pr.SKU AS cve_articulo, a.des_articulo, CONCAT('(', r.cve_ruta, ') ', r.descripcion) AS ruta, 
                        d.razonsocial AS cliente, d.Cve_Clte as CveCliente, pr.Cant, IFNULL(pr.Tipmed, '') AS unidad_medida
                FROM PRegalado pr 
                LEFT JOIN c_articulo a ON a.cve_articulo = pr.SKU
                LEFT JOIN t_ruta r ON r.ID_Ruta = pr.RutaId
                LEFT JOIN c_destinatarios d ON d.id_destinatario = pr.Cliente
                LEFT JOIN c_almacenp al ON al.clave = pr.IdEmpresa
                WHERE  al.id = {$almacen} AND pr.Docto in ($folio) AND IFNULL(d.razonsocial, '') != ''
                ORDER BY des_articulo";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "7Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);
    $sql .= " LIMIT $start, $limit;";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "8Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    }
    if ($page > $total_pages)
        $page = $total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        //$row=array_map('utf8_encode', $row);
        extract($row);
        $Total = ($Importe + $IVA - $Descuento);
        $Total = number_format($Total, 2);
        $responce->rows[$i]['idVenta'] = $idVenta;
        $responce->rows[$i]['cell'] = array(
            'cve_articulo'  => $cve_articulo,
            'descripcion'   => $des_articulo,
            'cve_ruta'      => $ruta,
            'cliente'       => $cliente,
            'cantidad'      => number_format($Cant, 2),
            'unidad_medida' => $unidad_medida
        );
        $i++;
    };
    echo json_encode($responce);
} else if (isset($_GET) && !empty($_GET) && $_GET['action'] == 'getDetallesFolio') {
    $page = $_GET['page'];
    $rows = $_GET['rows'];
    $limit = $_GET['rows']; // get how many rows we want to have into the grid
    $search = $_GET['search'];
    $id_venta = $_GET['id_venta'];
    $folio = $_GET['folio'];
    $cve_articulo = $_GET['cve_articulo'];

    $start = $limit * $page - $limit; // do not put $limit*($page - 1)

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    /*Pedidos listos para embarcar*/
    $sql = "";

    if (isset($_GET['consolidado'])) {
        $cliente = $_GET['cliente'];
        $almacen = $_GET['almacen'];

        $sql = "SELECT c_cliente.Cve_Clte, c_cliente.RazonComercial , Cob.Documento, Cob.Fechareg,
Sum(ifnull(Cob.Saldo,0)) Total, sum(ifnull(Cob.Abono,0)) Abono,Sum(Cob.Saldo)-sum(ifnull(Cob.Abono,0)) Saldo
from Vw_Cobranza Cob #left join DetalleCob on Cobranza.Documento=DetalleCob.Documento and Cobranza.RutaId=DetalleCob.RutaId
LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Cob.Cliente
LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
where Cob.Status=1  and Cob.Cliente='{$cliente}'
group by c_cliente.Cve_Clte,Cob.Documento";
    } else if (isset($_GET['cobranza'])) {
        $sql = "SELECT dc.Id, DATE_FORMAT(dc.Fecha, '%d-%m-%Y') as Fecha, dc.SaldoAnt, dc.Abono, dc.Saldo, IFNULL(c.limite_credito, 0) as limiteCredito
                FROM DetalleCob dc
                LEFT JOIN c_destinatarios d ON d.id_destinatario = dc.Cliente
                LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                WHERE dc.Documento = '{$folio}' 
                ORDER BY dc.Id";
    } else {

        $sqlEnvase = "";
        if (isset($_GET['usa_envase'])) {
            $sqlEnvase = " AND c_articulo.Usa_Envase = 'S' ";
        }

        $sql_articulo = "";
        if ($cve_articulo != '') {
            $sql_articulo = " AND c_articulo.cve_articulo = '$cve_articulo' ";
        }
        $sqlRuta = "";

        $operacion = '';
        if (isset($_GET['liquidacion'])) {
            $operacion = $_GET['operacion'];
        }


        if (($id_venta != 'null' && $id_venta != '') || ($operacion != '' && $operacion == 'Venta')) {
            //- IF(pr.Tipmed = 'Caja', pr.Cant, 0)
            $field_liquidacion = " DetalleVet.Comisiones AS Comisiones, c_almacenp.nombre AS sucursalNombre, Venta.Id AS idVenta, Venta.IdEmpresa AS Sucursal,
          DATE_FORMAT(Venta.Fecha, '%d/%m/%Y') AS Fecha, Venta.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, Venta.CodCliente AS Cliente, 
          c_cliente.Cve_Clte as CveCliente,
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, Venta.Documento AS Folio, Venta.TipoVta AS Tipo, FormasPag.Forma AS metodoPago,
          DetalleVet.Importe AS Importe, DetalleVet.IVA AS IVA, DetalleVet.DescMon AS Descuento, 
          '' AS TotalPedidas,
          DetalleVet.Utilidad AS Utilidad, c_articulo.num_multiplo AS Cajas, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) AS Piezas, Venta.Cancelada AS Cancelada, Venta.VendedorId AS vendedorID,
          t_vendedores.Cve_Vendedor as cveVendedor, 
          IFNULL(sh.Stock, '0') AS InvInicial,
          t_vendedores.Nombre AS Vendedor, Venta.ID_Ayudante1 AS Ayudante1, Venta.ID_Ayudante2 AS Ayudante2, 
          DetalleVet.DescPorc AS Promociones, DiasO.DiaO AS DiaOperativo, '' AS DiaOperativoCobranza, c_articulo.cve_articulo as cve_articulo,
          DetalleVet.Descripcion AS Articulo, Cobranza.Documento AS Documento, Cobranza.Saldo AS saldoInicial, DetalleCob.Abono AS Abono, Venta.Saldo AS saldoActual,
          (IF(DetalleVet.Tipo = 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))),TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0))) ) AS cajas_total,
          (IF(DetalleVet.Tipo = 0, 0, IF(um.mav_cveunimed != 'XBX', (IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) - (c_articulo.num_multiplo*TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), 0))) - IF(pr.Tipmed != 'Caja', pr.Cant, 0)) as piezas_total,
          Cobranza.FechaReg AS fechaRegistro, Cobranza.FechaVence AS fechaVence, Venta.DocSalida AS tipoDoc, Noventas.MotivoId AS idMotivo, MotivosNoVenta.Motivo AS Motivo ";
            $group_by_liquidacion = "";
            $filtro_diao_liquidacion = " Venta.Id = '{$id_venta}' ";
            if (isset($_GET['liquidacion'])) {
                $diao = $_GET['diao'];
                $field_liquidacion = " SUM(DetalleVet.Comisiones) AS Comisiones, c_almacenp.nombre AS sucursalNombre, '' AS idVenta, Venta.IdEmpresa AS Sucursal,
          '' AS Fecha, Venta.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, '' AS Cliente, 
          '' as CveCliente,
          '' AS Responsable,
          '' AS nombreComercial, '' AS Folio, '' AS Tipo, '' AS metodoPago,
          SUM(DetalleVet.Importe) AS Importe, SUM(DetalleVet.IVA) AS IVA, SUM(DetalleVet.DescMon) AS Descuento, 
          '' AS TotalPedidas,
          SUM(DetalleVet.Utilidad) AS Utilidad, SUM(c_articulo.num_multiplo) AS Cajas, SUM(IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))) AS Piezas, Venta.Cancelada AS Cancelada, '' AS vendedorID,
          '' AS cveVendedor, 
          IFNULL(sh.Stock, '0') AS InvInicial,
          '' AS Vendedor, '' AS Ayudante1, '' AS Ayudante2, 
          SUM(DetalleVet.DescPorc) AS Promociones, DiasO.DiaO AS DiaOperativo, '' AS DiaOperativoCobranza, c_articulo.cve_articulo AS cve_articulo,
          DetalleVet.Descripcion AS Articulo, '' AS Documento, SUM(Cobranza.Saldo) AS saldoInicial, DetalleCob.Abono AS Abono, SUM(Venta.Saldo) AS saldoActual,
          (IF(DetalleVet.Tipo = 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)),SUM(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))),TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0)))) - IF(pr.Tipmed = 'Caja', pr.Cant, 0)) AS cajas_total,
          (IF(DetalleVet.Tipo = 0, 0, SUM(IF(um.mav_cveunimed != 'XBX', (IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) - (c_articulo.num_multiplo*TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), 0)))) - IF(pr.Tipmed != 'Caja', pr.Cant, 0)) AS piezas_total,
          '' AS fechaRegistro, '' AS fechaVence, '' AS tipoDoc, '' AS idMotivo, '' AS Motivo ";
                $group_by_liquidacion = " GROUP BY DiaOperativo, cve_articulo, rutaName ";
                $filtro_diao_liquidacion = " Venta.DiaO = '{$diao}' ";
                $ruta = $_GET['ruta'];
                if ($ruta)
                    $sqlRuta = " AND t_ruta.cve_ruta = '$ruta' ";
            }
            $sql = "SELECT DISTINCT 
          {$field_liquidacion} 
          FROM Venta
          LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
          INNER JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId AND DetalleVet.RutaId = t_ruta.ID_Ruta 
          LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa 
          LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento
          LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO AND DiasO.RutaId = Venta.RutaId
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND Noventas.RutaId = Venta.RutaId AND Noventas.Cliente = Venta.CodCliente 
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = DetalleVet.Articulo AND sh.DiaO = Venta.DiaO AND sh.RutaID = Venta.RutaId
          LEFT JOIN PRegalado pr ON pr.SKU = DetalleVet.Articulo AND Venta.Documento IN (pr.Docto, CONCAT(pr.RutaId, '_', pr.Docto))
          WHERE {$filtro_diao_liquidacion} {$sqlEnvase} {$sql_articulo}
           {$group_by_liquidacion} {$sqlRuta}
          group BY Articulo
          ";
        } else //if($operacion!='' && $operacion == 'PreVenta')
        {


            $field_liquidacion = " '' AS Comisiones, c_almacenp.nombre AS sucursalNombre, th.Pedido AS idVenta, th.IdEmpresa AS Sucursal,
          DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') AS Fecha, th.Ruta AS Ruta, t_ruta.cve_ruta AS rutaName, th.Cve_Clte AS Cliente, 
          c_cliente.RazonSocial AS Responsable, th.Cve_Clte AS CveCliente,
          c_destinatarios.razonsocial AS nombreComercial, th.Pedido AS Folio, th.TipoPedido AS Tipo, IFNULL(th.FormaPag, '') AS metodoPago,
          td.SubTotalPedidas AS Importe, td.IVAPedidas AS IVA, td.DescuentoPedidas AS Descuento, 
          td.TotalPedidas AS TotalPedidas,
          '' AS Utilidad, c_articulo.num_multiplo AS Cajas, td.Pedidas AS Piezas, '' AS Cancelada, th.cve_Vendedor AS vendedorID,
          IFNULL(sh.Stock, '0') AS InvInicial,
          t_vendedores.Cve_Vendedor as cveVendedor, 
          t_vendedores.Nombre AS Vendedor, '' AS Ayudante1, '' AS Ayudante2, 
          '' AS Promociones, th.DiaO AS DiaOperativo, '' AS DiaOperativoCobranza, c_articulo.cve_articulo as cve_articulo,
          td.Descripcion AS Articulo, Cobranza.Documento AS Documento, Cobranza.Saldo AS saldoInicial, sum(DetalleCob.Abono) AS Abono, '' AS saldoActual,
          (IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td.Pedidas),TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0)) - IF(pr.Tipmed = 'Caja', pr.Cant, 0)) AS cajas_total,
          (IF(um.mav_cveunimed != 'XBX', (td.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, td.Pedidas, 0)) - IF(pr.Tipmed != 'Caja', pr.Cant, 0)) as piezas_total,
          Cobranza.FechaReg AS fechaRegistro, Cobranza.FechaVence AS fechaVence, '' AS tipoDoc, Noventas.MotivoId AS idMotivo, MotivosNoVenta.Motivo AS Motivo ";

            $group_by_liquidacion = "";
            $filtro_diao_liquidacion = " th.Pedido = '{$folio}' ";
            if (isset($_GET['liquidacion'])) {
                $diao = $_GET['diao'];
                $field_liquidacion = " '' AS Comisiones, c_almacenp.nombre AS sucursalNombre, '' AS idVenta, th.IdEmpresa AS Sucursal,
          '' AS Fecha, th.Ruta AS Ruta, t_ruta.cve_ruta AS rutaName, '' AS Cliente, 
          '' AS Responsable, '' AS CveCliente,
          '' AS nombreComercial, '' AS Folio, '' AS Tipo, '' AS metodoPago,
          SUM(td.SubTotalPedidas) AS Importe, SUM(td.IVAPedidas) AS IVA, SUM(td.DescuentoPedidas) AS Descuento, 
          SUM(td.TotalPedidas) AS TotalPedidas,
          '' AS Utilidad, SUM(c_articulo.num_multiplo) AS Cajas, SUM(td.Pedidas) AS Piezas, '' AS Cancelada, '' AS vendedorID,
          IFNULL(sh.Stock, '0') AS InvInicial,
          '' AS cveVendedor, 
          '' AS Vendedor, '' AS Ayudante1, '' AS Ayudante2, 
          '' AS Promociones, th.DiaO AS DiaOperativo, '' AS DiaOperativoCobranza, c_articulo.cve_articulo AS cve_articulo,
          td.Descripcion AS Articulo, '' AS Documento, SUM(Cobranza.Saldo) AS saldoInicial, SUM(DetalleCob.Abono) AS Abono, '' AS saldoActual,
          SUM((IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td.Pedidas),TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0))) - IF(pr.Tipmed = 'Caja', pr.Cant, 0) AS cajas_total,
          SUM((IF(um.mav_cveunimed != 'XBX', (td.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, td.Pedidas, 0))) - IF(pr.Tipmed != 'Caja', pr.Cant, 0) AS piezas_total,
          '' AS fechaRegistro, '' AS fechaVence, '' AS tipoDoc, '' AS idMotivo, '' AS Motivo ";

                $group_by_liquidacion = " GROUP BY DiaOperativo, cve_articulo, rutaName ";
                $filtro_diao_liquidacion = " RelOperaciones.DiaO = '{$diao}' ";
                $ruta = $_GET['ruta'];
                if ($ruta)
                    $sqlRuta = " AND t_ruta.cve_ruta = '$ruta' ";
            }


            $sql = "
            SELECT * FROM (
            SELECT DISTINCT 
          {$field_liquidacion} 
          FROM V_Cabecera_Pedido th
          LEFT JOIN V_Detalle_Pedido td ON td.Pedido = th.Pedido
          LEFT JOIN t_ruta ON t_ruta.ID_Ruta = th.Ruta 
          LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
          LEFT JOIN Cobranza ON CONCAT(Cobranza.RutaId,'_', Cobranza.Documento) = th.Pedido
          INNER JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th.Pedido
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND Noventas.RutaId = th.Ruta AND Noventas.Cliente = th.Cod_Cliente
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = td.Articulo AND sh.DiaO = RelOperaciones.DiaO AND sh.RutaID = t_ruta.ID_Ruta
          LEFT JOIN PRegalado pr ON pr.SKU = td.Articulo AND th.Pedido IN (pr.Docto, CONCAT(pr.RutaId, '_', pr.Docto))
          WHERE {$filtro_diao_liquidacion} {$sqlEnvase} {$sql_articulo} {$sqlRuta}  
          {$group_by_liquidacion} 
          group BY Articulo
          ) as det WHERE det.TotalPedidas > 0
          ";
        }
    }
    if (!($res = mysqli_query($conn, $sql))) {
        echo "9Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    $count = mysqli_num_rows($res);
    $sql .= " LIMIT $start, $limit;";


    if (!($res = mysqli_query($conn, $sql))) {
        echo "10Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    $dato_inicial_cobranza = false;
    if ($count == 0 && isset($_GET['cobranza'])) {
        $dato_inicial_cobranza = true;
    }

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    }
    if ($page > $total_pages)
        $page = $total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

    if ($dato_inicial_cobranza) {
        $responce->page = 1;
        $responce->total = 1;
        $responce->records = 1;
    }

    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        extract($row);
        if (isset($_GET['cobranza'])) {
            $responce->rows[$i]['Id'] = $Id;
            $responce->rows[$i]['cell'] = array(
                'Id'       => $Id,
                'Fecha'    => $Fecha,
                'SaldoAnt' => number_format($SaldoAnt, 2),
                'Abono'    => number_format($Abono, 2),
                'Saldo'    => number_format($Saldo, 2),
                'SaldoD'   => number_format($limiteCredito - $Saldo, 2)
            );
        } else if (isset($_GET['consolidado'])) {
            $responce->rows[$i]['Id'] = $Id;
            $responce->rows[$i]['cell'] = array(
                'fecha' => $Fechareg,
                'folio' => $Documento,
                'total' => number_format($Total, 2),
                'abono' => number_format($Abono, 2),
                'saldo' => number_format($Saldo, 2)
            );
        } else {
            $Total = ($Importe + $IVA - $Descuento);
            $Total = number_format($Total, 2);
            if ($TotalPedidas) $Total = $TotalPedidas;
            $responce->rows[$i]['idVenta'] = $idVenta;
            $responce->rows[$i]['cell'] = array(
                'clave'      => $cve_articulo,
                'articulo'   => $Articulo,
                'InvInicial' => $InvInicial,
                'cajas'      => $cajas_total,
                'piezas'     => $piezas_total,
                'importe'    => number_format($Importe, 2),
                'iva'        => number_format($IVA, 2),
                'descuento'  => number_format($Descuento, 2),
                'total'      => number_format($Total, 2),
                'promc'      => '',
                'promp'      => '',
                'tienepromo' => ''
            );
        }
        $i++;
    };

    if (isset($_GET['cobranza']) && $dato_inicial_cobranza) {
        $limite_credito = str_replace(",", "", $_GET['limite_credito']);
        $total = str_replace(",", "", $_GET['total']);;

        $responce->rows[0]['Id'] = '';
        $responce->rows[0]['cell'] = array(
            'Id'       => '',
            'Fecha'    => '',
            'SaldoAnt' => number_format($total, 2),
            'Abono'    => number_format(0, 2),
            'Saldo'    => number_format($total, 2),
            'SaldoD'   => number_format($limite_credito, 2)
        );
    }

    //mysqli_close();
    //header('Content-type: application/json');
    echo json_encode($responce);
} else if (isset($_GET) && !empty($_GET) && $_GET['action'] == 'getDetallesFolio2') {
    $page = $_GET['page'];
    $rows = $_GET['rows'];
    $limit = $_GET['rows']; // get how many rows we want to have into the grid
    $search = $_GET['search'];
    $id_venta = $_GET['id_venta'];
    $folio = $_GET['folio'];
    $cve_articulo = $_GET['cve_articulo'];
    if (is_array($folio) && count($folio) > 0 && $folio[0] != '') {
        $folio = implode(",", $folio);
    } else {
        $folio = '';
    }
    if (is_array($id_venta) && count($id_venta) > 0 && $id_venta[0] != '') {
        $id_venta = implode(",", $id_venta);
    } else {
        $id_venta = '';
    }
    $folioString = '';
    $id_ventaString = '';

    if (preg_match('/^,+$/', $folio)) {
        $folio = '';
    }
    if (preg_match('/^,+$/', $id_venta)) {
        $id_venta = '';
    }
    $start = $limit * $page - $limit; // do not put $limit*($page - 1)
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    /*Pedidos listos para embarcar*/
    $sql = "";

    if (isset($_GET['consolidado'])) {
        $cliente = $_GET['cliente'];
        $almacen = $_GET['almacen'];

        $sql = "SELECT c_cliente.Cve_Clte, c_cliente.RazonComercial , Cobranza.Documento, Cobranza.Fechareg,
Sum(ifnull(Cobranza.Saldo,0)) Total, sum(ifnull(DetalleCob.Abono,0)) Abono,Sum(Cobranza.Saldo)-sum(ifnull(DetalleCob.Abono,0)) Saldo
from  Cobranza left join DetalleCob on Cobranza.Documento=DetalleCob.Documento and Cobranza.RutaId=DetalleCob.RutaId
LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Cobranza.Cliente
LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
where Cobranza.Status=1  and Cobranza.Cliente='{$cliente}'
group by c_cliente.Cve_Clte,Cobranza.Documento";
    } else if (isset($_GET['cobranza'])) {
        $sql = "SELECT dc.Id, DATE_FORMAT(dc.Fecha, '%d-%m-%Y') as Fecha, dc.SaldoAnt, dc.Abono, dc.Saldo, IFNULL(c.limite_credito, 0) as limiteCredito
                FROM DetalleCob dc
                LEFT JOIN c_destinatarios d ON d.id_destinatario = dc.Cliente
                LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                WHERE dc.Documento = '{$folio}' 
                ORDER BY dc.Id";
    } else {

        $sqlEnvase = "";
        if (isset($_GET['usa_envase'])) {
            $sqlEnvase = " AND c_articulo.Usa_Envase = 'S' ";
        }

        $sql_articulo = "";
        if ($cve_articulo != '') {
            $sql_articulo = " AND c_articulo.cve_articulo = '$cve_articulo' ";
        }
        $sqlRuta = "";

        $operacion = '';
        if (isset($_GET['liquidacion'])) {
            $operacion = $_GET['operacion'];
        }


        if (($id_venta != 'null' && $id_venta != '') || ($operacion != '' && $operacion == 'Venta')) {
            //- IF(pr.Tipmed = 'Caja', pr.Cant, 0)
            $field_liquidacion = " DetalleVet.Comisiones AS Comisiones, c_almacenp.nombre AS sucursalNombre, Venta.Id AS idVenta, Venta.IdEmpresa AS Sucursal,
          DATE_FORMAT(Venta.Fecha, '%d/%m/%Y') AS Fecha, Venta.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, Venta.CodCliente AS Cliente, 
          c_cliente.Cve_Clte as CveCliente,
          c_cliente.RazonSocial AS Responsable,
          c_destinatarios.razonsocial AS nombreComercial, Venta.Documento AS Folio, Venta.TipoVta AS Tipo, FormasPag.Forma AS metodoPago,
          DetalleVet.Importe AS Importe, DetalleVet.IVA AS IVA, DetalleVet.DescMon AS Descuento, 
          '' AS TotalPedidas,
          DetalleVet.Utilidad AS Utilidad, c_articulo.num_multiplo AS Cajas, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) AS Piezas, Venta.Cancelada AS Cancelada, Venta.VendedorId AS vendedorID,
          t_vendedores.Cve_Vendedor as cveVendedor, 
          IFNULL(sh.Stock, '0') AS InvInicial,
          t_vendedores.Nombre AS Vendedor, Venta.ID_Ayudante1 AS Ayudante1, Venta.ID_Ayudante2 AS Ayudante2, 
          DetalleVet.DescPorc AS Promociones, DiasO.DiaO AS DiaOperativo, '' AS DiaOperativoCobranza, c_articulo.cve_articulo as cve_articulo,
          DetalleVet.Descripcion AS Articulo, Cobranza.Documento AS Documento, Cobranza.Saldo AS saldoInicial, sum(DetalleCob.Abono) AS Abono, Venta.Saldo AS saldoActual,
          (IF(DetalleVet.Tipo = 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))),TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0))) ) AS cajas_total,
          (IF(DetalleVet.Tipo = 0, 0, IF(um.mav_cveunimed != 'XBX', (IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) - (c_articulo.num_multiplo*TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), 0))) - IF(pr.Tipmed != 'Caja', pr.Cant, 0)) as piezas_total,
          Cobranza.FechaReg AS fechaRegistro, Cobranza.FechaVence AS fechaVence, Venta.DocSalida AS tipoDoc, Noventas.MotivoId AS idMotivo, MotivosNoVenta.Motivo AS Motivo ";
            $group_by_liquidacion = "";
            $filtro_diao_liquidacion = " Venta.Id in ($id_venta) ";
            if (isset($_GET['liquidacion'])) {
                $diao = $_GET['diao'];
                $field_liquidacion = " SUM(DetalleVet.Comisiones) AS Comisiones, c_almacenp.nombre AS sucursalNombre, '' AS idVenta, Venta.IdEmpresa AS Sucursal,
          '' AS Fecha, Venta.RutaId AS Ruta, t_ruta.cve_ruta AS rutaName, '' AS Cliente, 
          '' as CveCliente,
          '' AS Responsable,
          '' AS nombreComercial, '' AS Folio, '' AS Tipo, '' AS metodoPago,
          SUM(DetalleVet.Importe) AS Importe, SUM(DetalleVet.IVA) AS IVA, SUM(DetalleVet.DescMon) AS Descuento, 
          '' AS TotalPedidas,
          SUM(DetalleVet.Utilidad) AS Utilidad, SUM(c_articulo.num_multiplo) AS Cajas, SUM(IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))) AS Piezas, Venta.Cancelada AS Cancelada, '' AS vendedorID,
          '' AS cveVendedor, 
          IFNULL(sh.Stock, '0') AS InvInicial,
          '' AS Vendedor, '' AS Ayudante1, '' AS Ayudante2, 
          SUM(DetalleVet.DescPorc) AS Promociones, DiasO.DiaO AS DiaOperativo, '' AS DiaOperativoCobranza, c_articulo.cve_articulo AS cve_articulo,
          DetalleVet.Descripcion AS Articulo, '' AS Documento, SUM(Cobranza.Saldo) AS saldoInicial, SUM(DetalleCob.Abono) AS Abono, SUM(Venta.Saldo) AS saldoActual,
          (IF(DetalleVet.Tipo = 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)),SUM(IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))),TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0)))) - IF(pr.Tipmed = 'Caja', pr.Cant, 0)) AS cajas_total,
          (IF(DetalleVet.Tipo = 0, 0, SUM(IF(um.mav_cveunimed != 'XBX', (IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)) - (c_articulo.num_multiplo*TRUNCATE((IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0))/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, IF(DetalleVet.Pza > 0, DetalleVet.Pza, IF(DetalleVet.Kg > 0, DetalleVet.Kg, 0)), 0)))) - IF(pr.Tipmed != 'Caja', pr.Cant, 0)) AS piezas_total,
          '' AS fechaRegistro, '' AS fechaVence, '' AS tipoDoc, '' AS idMotivo, '' AS Motivo ";
                $group_by_liquidacion = " GROUP BY DiaOperativo, cve_articulo, rutaName ";
                $filtro_diao_liquidacion = " Venta.DiaO = '{$diao}' ";
                $ruta = $_GET['ruta'];
                if ($ruta)
                    $sqlRuta = " AND t_ruta.cve_ruta = '$ruta' ";
            }
            $sql = "SELECT DISTINCT 
          {$field_liquidacion} 
          FROM Venta
          LEFT JOIN DetalleVet ON DetalleVet.Docto = Venta.Documento
          INNER JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId AND DetalleVet.RutaId = t_ruta.ID_Ruta 
          LEFT JOIN FormasPag ON FormasPag.IdFpag = Venta.FormaPag 
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = Venta.VendedorId
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = DetalleVet.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = Venta.IdEmpresa 
          LEFT JOIN Cobranza ON Cobranza.Documento = Venta.Documento
          LEFT JOIN DiasO ON DiasO.DiaO = Venta.DiaO AND DiasO.RutaId = Venta.RutaId
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND Noventas.RutaId = Venta.RutaId AND Noventas.Cliente = Venta.CodCliente 
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=Venta.CodCliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = DetalleVet.Articulo AND sh.DiaO = Venta.DiaO AND sh.RutaID = Venta.RutaId
          LEFT JOIN PRegalado pr ON pr.SKU = DetalleVet.Articulo AND Venta.Documento IN (pr.Docto, CONCAT(pr.RutaId, '_', pr.Docto))
          WHERE {$filtro_diao_liquidacion} {$sqlEnvase} {$sql_articulo}
           {$group_by_liquidacion} {$sqlRuta}
          ORDER BY Articulo
          ";
        } else //if($operacion!='' && $operacion == 'PreVenta')
        {


            $field_liquidacion = " '' AS Comisiones, c_almacenp.nombre AS sucursalNombre, th.Pedido AS idVenta, th.IdEmpresa AS Sucursal,
          DATE_FORMAT(th.Fec_Pedido, '%d/%m/%Y') AS Fecha, th.Ruta AS Ruta, t_ruta.cve_ruta AS rutaName, th.Cve_Clte AS Cliente, 
          c_cliente.RazonSocial AS Responsable, th.Cve_Clte AS CveCliente,
          c_destinatarios.razonsocial AS nombreComercial, th.Pedido AS Folio, th.TipoPedido AS Tipo, IFNULL(th.FormaPag, '') AS metodoPago,
          td.SubTotalPedidas AS Importe, td.IVAPedidas AS IVA, td.DescuentoPedidas AS Descuento, 
          td.TotalPedidas AS TotalPedidas,
          '' AS Utilidad, c_articulo.num_multiplo AS Cajas, td.Pedidas AS Piezas, '' AS Cancelada, th.cve_Vendedor AS vendedorID,
          IFNULL(sh.Stock, '0') AS InvInicial,
          t_vendedores.Cve_Vendedor as cveVendedor, 
          t_vendedores.Nombre AS Vendedor, '' AS Ayudante1, '' AS Ayudante2, 
          '' AS Promociones, th.DiaO AS DiaOperativo, '' AS DiaOperativoCobranza, c_articulo.cve_articulo as cve_articulo,
          td.Descripcion AS Articulo, Cobranza.Documento AS Documento, Cobranza.Saldo AS saldoInicial, sum(DetalleCob.Abono) AS Abono, '' AS saldoActual,
          (IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td.Pedidas),TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0)) - IF(pr.Tipmed = 'Caja', pr.Cant, 0)) AS cajas_total,
          (IF(um.mav_cveunimed != 'XBX', (td.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, td.Pedidas, 0)) - IF(pr.Tipmed != 'Caja', pr.Cant, 0)) as piezas_total,
          Cobranza.FechaReg AS fechaRegistro, Cobranza.FechaVence AS fechaVence, '' AS tipoDoc, Noventas.MotivoId AS idMotivo, MotivosNoVenta.Motivo AS Motivo ";
            $group_by_liquidacion = "";
            $filtro_diao_liquidacion = " th.Pedido in ($folio) ";
            if (isset($_GET['liquidacion'])) {
                $diao = $_GET['diao'];
                $field_liquidacion = " '' AS Comisiones, c_almacenp.nombre AS sucursalNombre, '' AS idVenta, th.IdEmpresa AS Sucursal,
          '' AS Fecha, th.Ruta AS Ruta, t_ruta.cve_ruta AS rutaName, '' AS Cliente, 
          '' AS Responsable, '' AS CveCliente,
          '' AS nombreComercial, '' AS Folio, '' AS Tipo, '' AS metodoPago,
          SUM(td.SubTotalPedidas) AS Importe, SUM(td.IVAPedidas) AS IVA, SUM(td.DescuentoPedidas) AS Descuento, 
          SUM(td.TotalPedidas) AS TotalPedidas,
          '' AS Utilidad, SUM(c_articulo.num_multiplo) AS Cajas, SUM(td.Pedidas) AS Piezas, '' AS Cancelada, '' AS vendedorID,
          IFNULL(sh.Stock, '0') AS InvInicial,
          '' AS cveVendedor, 
          '' AS Vendedor, '' AS Ayudante1, '' AS Ayudante2, 
          '' AS Promociones, th.DiaO AS DiaOperativo, '' AS DiaOperativoCobranza, c_articulo.cve_articulo AS cve_articulo,
          td.Descripcion AS Articulo, '' AS Documento, SUM(Cobranza.Saldo) AS saldoInicial, SUM(DetalleCob.Abono) AS Abono, '' AS saldoActual,
          SUM((IF(um.mav_cveunimed = 'XBX', IF(c_articulo.num_multiplo = 1, 0, td.Pedidas),TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0))) - IF(pr.Tipmed = 'Caja', pr.Cant, 0) AS cajas_total,
          SUM((IF(um.mav_cveunimed != 'XBX', (td.Pedidas - (c_articulo.num_multiplo*TRUNCATE((td.Pedidas/c_articulo.num_multiplo), 0))), IF(c_articulo.num_multiplo = 1, td.Pedidas, 0))) - IF(pr.Tipmed != 'Caja', pr.Cant, 0) AS piezas_total,
          '' AS fechaRegistro, '' AS fechaVence, '' AS tipoDoc, '' AS idMotivo, '' AS Motivo ";

                $group_by_liquidacion = " GROUP BY DiaOperativo, cve_articulo, rutaName ";
                $filtro_diao_liquidacion = " RelOperaciones.DiaO = '{$diao}' ";
                $ruta = $_GET['ruta'];
                if ($ruta)
                    $sqlRuta = " AND t_ruta.cve_ruta = '$ruta' ";
            }


            $sql = "
            SELECT * FROM (
            SELECT DISTINCT 
          {$field_liquidacion} 
          FROM V_Cabecera_Pedido th
          LEFT JOIN V_Detalle_Pedido td ON td.Pedido = th.Pedido
          LEFT JOIN t_ruta ON t_ruta.ID_Ruta = th.Ruta 
          LEFT JOIN FormasPag ON FormasPag.IdFpag = th.FormaPag
          LEFT JOIN t_vendedores ON t_vendedores.Id_Vendedor = th.cve_Vendedor
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = td.Articulo
          INNER JOIN c_almacenp ON c_almacenp.clave = th.IdEmpresa 
          LEFT JOIN Cobranza ON CONCAT(Cobranza.RutaId,'_', Cobranza.Documento) = th.Pedido
          INNER JOIN RelOperaciones ON CONCAT(RelOperaciones.RutaId,'_', RelOperaciones.Folio) = th.Pedido
          LEFT JOIN DiasO ON DiasO.DiaO = RelOperaciones.DiaO
          LEFT JOIN DetalleCob ON DetalleCob.IdCobranza = Cobranza.id
          LEFT JOIN Noventas ON Noventas.DiaO = DiasO.DiaO AND Noventas.RutaId = th.Ruta AND Noventas.Cliente = th.Cod_Cliente
          LEFT JOIN MotivosNoVenta ON MotivosNoVenta.IdMot = Noventas.MotivoId
          LEFT JOIN c_destinatarios ON c_destinatarios.id_destinatario=th.Cod_Cliente
          LEFT JOIN c_cliente ON c_cliente.Cve_Clte=c_destinatarios.Cve_Clte
          LEFT JOIN c_unimed um ON um.id_umed = c_articulo.unidadMedida
          LEFT JOIN StockHistorico sh ON sh.Articulo = td.Articulo AND sh.DiaO = RelOperaciones.DiaO AND sh.RutaID = t_ruta.ID_Ruta
          LEFT JOIN PRegalado pr ON pr.SKU = td.Articulo AND th.Pedido IN (pr.Docto, CONCAT(pr.RutaId, '_', pr.Docto))
          WHERE {$filtro_diao_liquidacion} {$sqlEnvase} {$sql_articulo} {$sqlRuta}  
          {$group_by_liquidacion} 
          ORDER BY Articulo
          ) as det WHERE det.TotalPedidas > 0
          ";
        }
    }
    if (!($res = mysqli_query($conn, $sql))) {
        echo "9Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    $count = mysqli_num_rows($res);
    $sql .= " LIMIT $start, $limit;";


    if (!($res = mysqli_query($conn, $sql))) {
        echo "10Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    $dato_inicial_cobranza = false;
    if ($count == 0 && isset($_GET['cobranza'])) {
        $dato_inicial_cobranza = true;
    }

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    }
    if ($page > $total_pages)
        $page = $total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

    if ($dato_inicial_cobranza) {
        $responce->page = 1;
        $responce->total = 1;
        $responce->records = 1;
    }

    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        extract($row);
        if (isset($_GET['cobranza'])) {
            $responce->rows[$i]['Id'] = $Id;
            $responce->rows[$i]['cell'] = array(
                'Id'       => $Id,
                'Fecha'    => $Fecha,
                'SaldoAnt' => number_format($SaldoAnt, 2),
                'Abono'    => number_format($Abono, 2),
                'Saldo'    => number_format($Saldo, 2),
                'SaldoD'   => number_format($limiteCredito - $Saldo, 2)
            );
        } else if (isset($_GET['consolidado'])) {
            $responce->rows[$i]['Id'] = $Id;
            $responce->rows[$i]['cell'] = array(
                'fecha' => $Fechareg,
                'folio' => $Documento,
                'total' => number_format($Total, 2),
                'abono' => number_format($Abono, 2),
                'saldo' => number_format($Saldo, 2)
            );
        } else {
            $Total = ($Importe + $IVA - $Descuento);
            $Total = number_format($Total, 2);
            if ($TotalPedidas) $Total = $TotalPedidas;
            $responce->rows[$i]['idVenta'] = $idVenta;
            $responce->rows[$i]['cell'] = array(
                'clave'      => $cve_articulo,
                'articulo'   => $Articulo,
                'InvInicial' => $InvInicial,
                'cajas'      => $cajas_total,
                'piezas'     => $piezas_total,
                'importe'    => number_format($Importe, 2),
                'iva'        => number_format($IVA, 2),
                'descuento'  => number_format($Descuento, 2),
                'total'      => number_format($Total, 2),
                'promc'      => '',
                'promp'      => '',
                'tienepromo' => ''
            );
        }
        $i++;
    };

    if (isset($_GET['cobranza']) && $dato_inicial_cobranza) {
        $limite_credito = str_replace(",", "", $_GET['limite_credito']);
        $total = str_replace(",", "", $_GET['total']);;

        $responce->rows[0]['Id'] = '';
        $responce->rows[0]['cell'] = array(
            'Id'       => '',
            'Fecha'    => '',
            'SaldoAnt' => number_format($total, 2),
            'Abono'    => number_format(0, 2),
            'Saldo'    => number_format($total, 2),
            'SaldoD'   => number_format($limite_credito, 2)
        );
    }

    //mysqli_close();
    //header('Content-type: application/json');
    echo json_encode($responce);
} else if (isset($_POST) && !empty($_POST) && $_POST['action'] == 'getDetallesFolioGrupo') {

    $page = $_POST['page'];
    $rows = $_POST['rows'];
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $search = $_POST['search'];
    $folios = $_POST['folios'];
    $responce = "";

    $start = $limit * $page - $limit; // do not put $limit*($page - 1)

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $folios_arr = implode($folios, ",");
    $sql = "";
    $sql = "SELECT DISTINCT GROUP_CONCAT(DISTINCT cob.Documento) AS Id, '' AS Fecha, 
                   SUM(cob.TOTAL) AS SaldoAnt, cob.Abono AS Abono, (SUM(cob.TOTAL)-cob.Abono) AS Saldo, cob.limite_credito AS limiteCredito, 
                   cob.Cve_Clte, cob.RazonSocial
            FROM (
                SELECT DISTINCT v.*, c.limite_credito, c.Cve_Clte, c.RazonSocial, (SELECT SUM(Abono) FROM DetalleCob WHERE Documento IN ({$folios_arr})) AS Abono
                        FROM Venta v
                        LEFT JOIN c_destinatarios d ON d.id_destinatario = v.CodCliente
                        LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                        WHERE v.Documento IN ({$folios_arr}) 
            ) AS cob
            ";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "9Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    $count = mysqli_num_rows($res);
    $sql .= " LIMIT $start, $limit;";


    if (!($res = mysqli_query($conn, $sql))) {
        echo "10Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }

    $responce->folios = $folios;
    $responce->folios_arr = $folios_arr;

    //empty($folios);
    //empty($_POST['folios']);
    //empty($folios_arr);
    //clearstatcache();
    $dato_inicial_cobranza = true;

    if ($count > 0) {
        $total_pages = ceil($count / $limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    }
    if ($page > $total_pages)
        $page = $total_pages;

    //$responce->page = $page;
    //$responce->total = $total_pages;
    //$responce->records = $count;
    $responce->sql = $sql;

    if ($dato_inicial_cobranza) {
        $responce->page = 1;
        $responce->total = 1;
        $responce->records = 1;
    }

    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        extract($row);
        $responce->rows[$i]['Id'] = $Id;
        $responce->rows[$i]['cell'] = array(
            'Id'          => $Id,
            'Cve_Clte'    => $Cve_Clte,
            'RazonSocial' => $RazonSocial,
            'Fecha'       => $Fecha,
            'SaldoAnt'    => number_format($SaldoAnt, 2),
            'Abono'       => number_format($Abono, 2),
            'Saldo'       => number_format($Saldo, 2),
            'SaldoD'      => number_format($limiteCredito - $Saldo, 2)
        );
        $i++;
    };

    //mysqli_close();
    //header('Content-type: application/json');
    echo json_encode($responce);
} else if (isset($_GET) && !empty($_GET)) {
    $page = $_GET['page'];
    $rows = $_GET['rows'];
    $search = $_GET['search'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    /*Pedidos listos para embarcar*/
    $sql = "SELECT cve_ruta, descripcion FROM t_ruta WHERE Activo = 1";

    if (!empty($search) && $search != '%20') {
        $sql .= " AND descripcion like '%" . $search . "%'";
    }

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array("error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    while ($row = mysqli_fetch_array($res)) {
        extract($row);
        $result [] = array(
            'clave'       => $cve_ruta,
            'descripcion' => $descripcion
        );
    };
    mysqli_close();
    header('Content-type: application/json');
    echo json_encode($result);
}
