<?php
include '../../config.php';

error_reporting(0);

if($_POST['action'] == 'enter-view')
{
    //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $id_user = $_POST['id_user'];
    $sql = "SELECT c_almacenp.id, c_almacenp.clave, CONVERT(c_almacenp.nombre USING utf8) AS nombre FROM c_almacenp LEFT JOIN t_usu_alm_pre ON c_almacenp.clave = t_usu_alm_pre.cve_almac WHERE c_almacenp.Activo = 1 AND t_usu_alm_pre.id_user = '{$id_user}'";

    $res = getArraySQL($sql);

    $array = [
        "almacen"=>$res
    ];

    echo json_encode($array);

/*
    //$sql = 'SELECT id, clave, nombre FROM c_almacenp WHERE Activo = 1';
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $rawdata = array();

    $i = 0;

    while($row = mysqli_fetch_array($res))
    {
        $rawdata[$i] = $row;
        $i++;
    }

    $array = [
        "almacen"=>$rawdata
    ];
    //echo var_dump($array);
    echo json_encode($array);
*/
}
else if($_POST['action'] == 'getLotesArticulosKardex')
{
    if(isset($_POST['cve_articulo']))
    {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $cve_aritculo = $_POST['cve_articulo'];
        $sql = "SELECT DISTINCT cve_lote FROM t_cardex WHERE cve_articulo = '{$cve_aritculo}'";

        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $i = 0;
        $option = "";
        while ($row = mysqli_fetch_array($res)) 
        {
            $option .= "<option value='".$row["cve_lote"]."'>".$row["cve_lote"]."</option>";
            $i++;
        }

        $options = "<option value=''>Seleccione Lote | Serie (".$i.")</option>".$option;
        $arr = array(
            "success" => true,
            "lotes" => $options
        );
        $arr = array_merge($arr);

        echo json_encode($arr);
    }
}
else if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $almacen= $_POST['almacen'];
    $lote= $_POST['lote'];
    $cve_articulo= $_POST['cve_articulo'];
    $fecha_inicio= $_POST['fechaI'];
    $fecha_final= $_POST['fechaF'];
    $tipomovimiento= $_POST['tipomovimiento'];

    //if (!empty($fecha_inicio)) $fecha_inicio = date("Y-m-d", strtotime($fecha_inicio));
    //if (!empty($fecha_final)) $fecha_final = date("Y-m-d", strtotime($fecha_final));

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];

    $SQLCriterio = "";
    if($_criterio)
    {
        $SQLCriterio = " AND (k.cve_articulo LIKE '%".$_criterio."%' OR k.cve_lote LIKE '%".$_criterio."%' OR a.des_articulo LIKE '%".$_criterio."%' OR uo.CodigoCSD LIKE '%".$_criterio."%' OR ud.CodigoCSD LIKE '%".$_criterio."%' OR ch.clave_contenedor LIKE '%".$_criterio."%' OR ch.CveLP LIKE '%".$_criterio."%' OR rd.desc_ubicacion LIKE '%".$_criterio."%' OR m.nombre LIKE '%".$_criterio."%' OR k.cve_usuario LIKE '%".$_criterio."%' OR rd.cve_ubicacion LIKE '%".$_criterio."%' OR k.origen LIKE '%".$_criterio."%' OR k.destino LIKE '%".$_criterio."%') ";
    }

    $SQLArticulo = "";
    if($cve_articulo)
    {
        $SQLArticulo = " AND k.cve_articulo = '".$cve_articulo."' ";
    }

    $SQLLote = "";
    if($lote)
    {
        $SQLLote = " AND k.cve_lote = '".$lote."' ";
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $SQLFecha = "";
    if($fecha_inicio && $fecha_final)
    {
        $SQLFecha = " AND k.fecha BETWEEN STR_TO_DATE('".$fecha_inicio."', '%d-%m-%Y') AND STR_TO_DATE('".$fecha_final."', '%d-%m-%Y')";
    }
    else if(!$fecha_inicio && $fecha_final)
    {
        $SQLFecha = " AND k.fecha <= STR_TO_DATE('".$fecha_final."', '%d-%m-%Y')";
    }
    else if($fecha_inicio && !$fecha_final)
    {
        $SQLFecha = " AND k.fecha >= STR_TO_DATE('".$fecha_inicio."', '%d-%m-%Y')";
    }

/*
    if(!$SQLArticulo && !$SQLLote && !$SQLFecha && !$_criterio)
    {
        $SQLFecha = " AND DATE_FORMAT(k.fecha, '%d-%m-%Y') = DATE_FORMAT((SELECT MAX(fecha) FROM t_cardex), '%d-%m-%Y') ";
    }
*/

    $SQLMovimientos = " AND k.id_TipoMovimiento IN (2, 12) ";
    if($tipomovimiento)
    {
        $SQLMovimientos = " AND k.id_TipoMovimiento = $tipomovimiento ";
    }

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;


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
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $_page = 0;

    if (intval($page)>0) $_page = ($page-1)*$limit;

/*
            (SELECT COUNT(DISTINCT t_clientexruta.clave_cliente) FROM c_destinatarios d 
            LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
            LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
            LEFT JOIN t_ruta tr ON tr.ID_Ruta = t_clientexruta.clave_ruta
            LEFT JOIN RelDayCli ON RelDayCli.Cve_Cliente = d.Cve_Clte AND tr.cve_ruta = RelDayCli.Cve_Ruta AND d.id_destinatario = RelDayCli.Id_Destinatario
            WHERE d.Activo = '1' AND c.Cve_Almacenp = '".$almacen."' AND tr.cve_ruta = t_ruta.cve_ruta) AS N_Clientes,
*/

    $sql = "SELECT DISTINCT 
IFNULL(IF(k.origen LIKE 'TR2%', alm_orig.clave, tr_origen.clave), IFNULL(ent_alm_orig.clave,  al.clave)) AS Almacen_Origen,
IFNULL(IF(k.destino LIKE 'TR2%', alm_dest.clave, tr_destino.clave), IFNULL(ent_alm_dest.clave, al.clave)) AS Almacen_Destino,
k.cve_articulo AS id_articulo, a.des_articulo AS des_articulo, k.cve_lote AS cve_lote, 
                IF(IFNULL(a.Caduca, 'N') = 'S', IFNULL(IF(DATE_FORMAT(lote.Caducidad, '%Y-%m-%d') != DATE_FORMAT('0000-00-00', '%Y-%m-%d'),DATE_FORMAT(lote.Caducidad, '%d-%m-%Y'), DATE_FORMAT(lote.Caducidad, '%d-%m-%Y')),''), '') AS Caducidad, 
                DATE_FORMAT(k.fecha, '%d-%m-%Y | %H:%m:%i') AS fecha, 
                #IF(LEFT(k.origen, 4) != 'Inv_',IFNULL(IFNULL(uo.CodigoCSD, ro.desc_ubicacion), IF(LEFT(k.origen, 3) = 'PT_', k.origen, 'RTM')), k.origen) AS origen, 
                IF(LEFT(k.origen, 4) != 'Inv_' AND k.origen != 'Inventario Inicial',IFNULL(IFNULL(uo.CodigoCSD, ro.desc_ubicacion), IF(LEFT(k.origen, 3) = 'PT_' OR LEFT(k.origen, 2) = 'OT', k.origen, IF(k.id_TipoMovimiento = 20, k.origen, 'RTM'))), k.origen) AS origen, 
                IFNULL(ch.clave_contenedor,'') AS clave_contenedor, IFNULL(ch.CveLP, '') AS CveLP, IFNULL(uo.CodigoCSD, '') AS bl, k.cantidad, IFNULL(k.ajuste, '') as ajuste, IFNULL(k.stockinicial, '') as stockinicial, 
                IFNULL(ud.CodigoCSD, IFNULL(rd.desc_ubicacion, k.destino)) AS destino, 
                IF(m.nombre LIKE 'Salida%', CONCAT('<span style=\'color:red;\'>-',k.cantidad, '</span>'), k.cantidad) as cantidad,
                m.nombre AS movimiento, 
                k.cve_usuario, al.nombre AS almacen 
                FROM t_cardex k 
                LEFT JOIN c_articulo a ON a.cve_articulo = k.cve_articulo
                LEFT JOIN c_almacenp al ON al.id = k.Cve_Almac

                LEFT JOIN th_pedido th_orig ON th_orig.Fol_folio = k.origen
                LEFT JOIN th_pedido th_dest ON th_dest.Fol_folio = k.destino
                LEFT JOIN c_almacenp alm_orig ON alm_orig.id = th_orig.cve_almac
                LEFT JOIN c_almacenp alm_dest ON alm_dest.id = th_dest.statusaurora
        
                LEFT JOIN th_entalmacen ent_orig ON ent_orig.Fol_Folio = k.origen
                LEFT JOIN th_entalmacen ent_dest ON ent_dest.Fol_Folio = k.destino
                LEFT JOIN c_almacenp ent_alm_orig ON ent_alm_orig.clave = ent_orig.cve_almac
                LEFT JOIN c_almacenp ent_alm_dest ON ent_alm_dest.clave = ent_dest.cve_almac

                LEFT JOIN t_MovCharolas mch ON k.id = mch.id_kardex #OR (k.origen = mch.Origen AND k.destino = mch.Destino AND k.id_TipoMovimiento = mch.Id_TipoMovimiento AND k.cve_usuario = mch.Cve_Usuario)
                LEFT JOIN c_charolas ch ON ch.IDContenedor = mch.ID_Contenedor 
                LEFT JOIN c_ubicacion uo ON uo.idy_ubica = k.origen 
                LEFT JOIN tubicacionesretencion ro ON ro.cve_ubicacion = k.origen 
                LEFT JOIN c_ubicacion ud ON ud.idy_ubica = k.destino 

                LEFT JOIN c_almacen tr_ori ON tr_ori.cve_almac = uo.cve_almac
                LEFT JOIN c_almacenp tr_origen ON tr_origen.id = tr_ori.cve_almacenp
                
                LEFT JOIN c_almacen tr_des ON tr_des.cve_almac = ud.cve_almac
                LEFT JOIN c_almacenp tr_destino ON tr_destino.id = tr_des.cve_almacenp

                LEFT JOIN tubicacionesretencion rd ON rd.cve_ubicacion = k.destino 
                LEFT JOIN t_tipomovimiento m ON m.id_TipoMovimiento = k.id_TipoMovimiento
                LEFT JOIN c_lotes lote ON lote.cve_articulo = k.cve_articulo AND lote.Lote = k.cve_lote
                #WHERE k.Cve_Almac = {$almacen} {$SQLArticulo} {$SQLLote} {$SQLCriterio} {$SQLFecha}
                WHERE (k.Cve_Almac IN ({$almacen}, IF(tr_destino.id = tr_origen.id, {$almacen}, IF(tr_destino.id = {$almacen} OR tr_origen.id = {$almacen}, IF(tr_origen.id = {$almacen}, tr_destino.id, tr_origen.id), 0)))) {$SQLArticulo} {$SQLLote} {$SQLCriterio} {$SQLFecha} {$SQLMovimientos} 

                #GROUP BY id_articulo, cve_lote, almacen, origen, destino, clave_contenedor, CveLP, movimiento
                #GROUP BY ext.ID_Contenedor
                ORDER BY k.id DESC
            ";
            //echo $sql;
            //INNER JOIN c_compania ON t_ruta.cve_cia = c_compania.cve_cia
            //WHERE
            //t_ruta.descripcion LIKE '%".$_criterio."%' AND t_ruta.Activo = 1;";
    // hace una llamada previa al procedimiento almacenado Lis_Facturas

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);
    $sql .= " LIMIT $_page, $limit;";
//echo $sql;
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;
    //$responce->fecha_inicio = $fecha_inicio;
    //$responce->fecha_final = $fecha_final;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id_articulo']=$row['id_articulo'];
        $responce->rows[$i]['cell']=array(
            $row['fecha'],
            $row['id_articulo'], 
            $row['des_articulo'], 
            $row['cve_lote'], 
            $row['Caducidad'], 
            $row['clave_contenedor'], 
            $row['CveLP'], 
            $row['movimiento'], 
            $row['clave_origen'], 
            $row['Almacen_Origen'], 
            $row['origen'], 
            $row['Almacen_Destino'], 
            $row['destino'], 
            $row['bl'], 
            $row['stockinicial'], 
            $row['cantidad'], 
            $row['ajuste'], 
            $row['cve_usuario']
            );
        $i++;
    }
    echo json_encode($responce);
}

if(isset($_GET) && !empty($_GET)){
    $page = $_GET['page'];
    $rows = $_GET['rows'];
    $search = $_GET['search'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    /*Pedidos listos para embarcar*/
    $sql = "SELECT cve_ruta, descripcion FROM t_ruta WHERE Activo = 1";

    if(!empty($search) && $search != '%20'){
        $sql.= " AND descripcion like '%".$search."%'";
    }

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    while($row = mysqli_fetch_array($res)){
        extract($row);
        $result [] = array(
            'clave' => $cve_ruta,
            'descripcion' => $descripcion
        );
    };
    mysqli_close();
    header('Content-type: application/json');
    echo json_encode( $result);
}


function getArraySQL($sql){

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    if(!$result = mysqli_query($conexion, $sql)) 
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ".$sql;

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