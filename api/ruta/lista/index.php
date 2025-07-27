<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST) && $_POST['action'] == 'loadDetalle') {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $id_ruta= $_POST['id_ruta'];
    $cve_ruta= $_POST['cve_ruta'];
    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // prepara la llamada al procedimiento almacenado Lis_Facturas

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $_page = 0;
    
    if (intval($page)>0) $_page = ($page-1)*$limit;
/*
    $sql = "SELECT
            t_ruta.ID_Ruta,
            #(COUNT(DISTINCT t_clientexruta.id_clientexruta)) AS N_Clientes,
            


#            IF(t_ruta.venta_preventa = 2,(SELECT COUNT(DISTINCT rdx.Cve_Cliente) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND rdx.Cve_Cliente IN (
 #                        SELECT th.Cve_Clte 
#                         FROM th_pedido th
#                         LEFT JOIN t_ruta rp ON rp.ID_Ruta = th.ruta
#                         LEFT JOIN t_ruta rpc ON rpc.cve_ruta = th.cve_ubicacion
#                         WHERE th.status = 'T' AND th.TipoPedido = 'P' 
#                         AND th.Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = rdx.Cve_Almac) 
#                         AND (th.ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ) OR rpc.ID_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ))
#                        ) AND IFNULL(rdx.Cve_Vendedor, '') != ''), (SELECT COUNT(txr.IdCliente) FROM RelClirutas txr WHERE txr.IdRuta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta))) AS N_Clientes,



            #IF(t_ruta.venta_preventa = 2, 0, (SELECT COUNT(txr.IdCliente) FROM RelClirutas txr WHERE txr.IdRuta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta))) AS N_Clientes,
            #IF(t_ruta.venta_preventa = 2,(COUNT(DISTINCT rcr.IdCliente)), (SELECT COUNT(txr.IdCliente) FROM RelClirutas txr WHERE txr.IdRuta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta))) AS N_Clientes,
            IF(t_ruta.venta_preventa = 2,(COUNT(DISTINCT rcr.IdCliente)), (COUNT(DISTINCT rcr.IdCliente))) AS N_Clientes,
            #(COUNT(DISTINCT rcr.IdCliente)) AS N_Clientes,
            IF(t_ruta.venta_preventa != 2,(SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND Lu IS NOT NULL), (SELECT COUNT(rdx.Lu) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND rdx.Cve_Cliente IN (
                         SELECT th.Cve_Clte 
                         FROM th_pedido th
                         LEFT JOIN t_ruta rp ON rp.ID_Ruta = th.ruta
                         LEFT JOIN t_ruta rpc ON rpc.cve_ruta = th.cve_ubicacion
                         WHERE th.status = 'T' AND th.TipoPedido = 'P' 
                         AND th.Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = rdx.Cve_Almac) 
                         AND (th.ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ) OR rpc.ID_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ))
                        ) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Lu, '') != '')+(COUNT(DISTINCT rd.Lu))) AS Lu,
            IF(t_ruta.venta_preventa != 2,(SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND Ma IS NOT NULL), (SELECT COUNT(rdx.Ma) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND rdx.Cve_Cliente IN (
                         SELECT th.Cve_Clte 
                         FROM th_pedido th
                         LEFT JOIN t_ruta rp ON rp.ID_Ruta = th.ruta
                         LEFT JOIN t_ruta rpc ON rpc.cve_ruta = th.cve_ubicacion
                         WHERE th.status = 'T' AND th.TipoPedido = 'P' 
                         AND th.Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = rdx.Cve_Almac) 
                         AND (th.ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ) OR rpc.ID_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ))
                        ) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Ma, '') != '')+(COUNT(DISTINCT rd.Ma))) AS Ma,
            IF(t_ruta.venta_preventa != 2,(SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND Mi IS NOT NULL), (SELECT COUNT(rdx.Mi) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND rdx.Cve_Cliente IN (
                         SELECT th.Cve_Clte 
                         FROM th_pedido th
                         LEFT JOIN t_ruta rp ON rp.ID_Ruta = th.ruta
                         LEFT JOIN t_ruta rpc ON rpc.cve_ruta = th.cve_ubicacion
                         WHERE th.status = 'T' AND th.TipoPedido = 'P' 
                         AND th.Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = rdx.Cve_Almac) 
                         AND (th.ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ) OR rpc.ID_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ))
                        ) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Mi, '') != '')+(COUNT(DISTINCT rd.Mi))) AS Mi,
            IF(t_ruta.venta_preventa != 2,(SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND Ju IS NOT NULL), (SELECT COUNT(rdx.Ju) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND rdx.Cve_Cliente IN (
                         SELECT th.Cve_Clte 
                         FROM th_pedido th
                         LEFT JOIN t_ruta rp ON rp.ID_Ruta = th.ruta
                         LEFT JOIN t_ruta rpc ON rpc.cve_ruta = th.cve_ubicacion
                         WHERE th.status = 'T' AND th.TipoPedido = 'P' 
                         AND th.Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = rdx.Cve_Almac) 
                         AND (th.ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ) OR rpc.ID_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ))
                        ) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Ju, '') != '')+(COUNT(DISTINCT rd.Ju))) AS Ju,
            IF(t_ruta.venta_preventa != 2,(SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND Vi IS NOT NULL), (SELECT COUNT(rdx.Vi) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND rdx.Cve_Cliente IN (
                         SELECT th.Cve_Clte 
                         FROM th_pedido th
                         LEFT JOIN t_ruta rp ON rp.ID_Ruta = th.ruta
                         LEFT JOIN t_ruta rpc ON rpc.cve_ruta = th.cve_ubicacion
                         WHERE th.status = 'T' AND th.TipoPedido = 'P' 
                         AND th.Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = rdx.Cve_Almac) 
                         AND (th.ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ) OR rpc.ID_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ))
                        ) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Vi, '') != '')+(COUNT(DISTINCT rd.Vi))) AS Vi,
            IF(t_ruta.venta_preventa != 2,(SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND Sa IS NOT NULL), (SELECT COUNT(rdx.Sa) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND rdx.Cve_Cliente IN (
                         SELECT th.Cve_Clte 
                         FROM th_pedido th
                         LEFT JOIN t_ruta rp ON rp.ID_Ruta = th.ruta
                         LEFT JOIN t_ruta rpc ON rpc.cve_ruta = th.cve_ubicacion
                         WHERE th.status = 'T' AND th.TipoPedido = 'P' 
                         AND th.Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = rdx.Cve_Almac) 
                         AND (th.ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ) OR rpc.ID_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ))
                        ) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Sa, '') != '')+(COUNT(DISTINCT rd.Sa))) AS Sa,
            IF(t_ruta.venta_preventa != 2,(SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND DO IS NOT NULL), (SELECT COUNT(rdx.Do) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND rdx.Cve_Cliente IN (
                         SELECT th.Cve_Clte 
                         FROM th_pedido th
                         LEFT JOIN t_ruta rp ON rp.ID_Ruta = th.ruta
                         LEFT JOIN t_ruta rpc ON rpc.cve_ruta = th.cve_ubicacion
                         WHERE th.status = 'T' AND th.TipoPedido = 'P' 
                         AND th.Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = rdx.Cve_Almac) 
                         AND (th.ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ) OR rpc.ID_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ))
                        ) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Do, '') != '')+(COUNT(DISTINCT rd.Do))) AS DO

            FROM
            t_ruta
            LEFT JOIN Rel_Ruta_Agentes ra ON ra.cve_ruta = t_ruta.ID_Ruta
            LEFT JOIN c_usuario tv ON tv.id_user = ra.cve_vendedor 
            LEFT JOIN RelDayCli rd ON rd.Cve_Ruta = t_ruta.ID_Ruta AND rd.Cve_Vendedor = tv.id_user
            LEFT JOIN RelClirutas rcr on rcr.IdRuta = t_ruta.ID_Ruta
            LEFT JOIN t_clientexruta ON t_clientexruta.clave_ruta = t_ruta.ID_Ruta
            LEFT JOIN c_destinatarios d ON d.id_destinatario = rcr.IdCliente 
        WHERE t_ruta.Activo = 1 AND d.Activo = '1' 
        AND t_ruta.ID_Ruta='$id_ruta' 
            GROUP BY t_ruta.ID_Ruta";
*/

    $sql = "SELECT
            t_ruta.ID_Ruta,
            #(COUNT(DISTINCT t_clientexruta.id_clientexruta)) AS N_Clientes,
            


#            IF(t_ruta.venta_preventa = 2,(SELECT COUNT(DISTINCT rdx.Cve_Cliente) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND rdx.Cve_Cliente IN (
 #                        SELECT th.Cve_Clte 
#                         FROM th_pedido th
#                         LEFT JOIN t_ruta rp ON rp.ID_Ruta = th.ruta
#                         LEFT JOIN t_ruta rpc ON rpc.cve_ruta = th.cve_ubicacion
#                         WHERE th.status = 'T' AND th.TipoPedido = 'P' 
#                         AND th.Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = rdx.Cve_Almac) 
#                         AND (th.ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ) OR rpc.ID_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas ))
#                        ) AND IFNULL(rdx.Cve_Vendedor, '') != ''), (SELECT COUNT(txr.IdCliente) FROM RelClirutas txr WHERE txr.IdRuta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta))) AS N_Clientes,



            #IF(t_ruta.venta_preventa = 2, 0, (SELECT COUNT(txr.IdCliente) FROM RelClirutas txr WHERE txr.IdRuta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta))) AS N_Clientes,
            #IF(t_ruta.venta_preventa = 2,(COUNT(DISTINCT rcr.IdCliente)), (SELECT COUNT(txr.IdCliente) FROM RelClirutas txr WHERE txr.IdRuta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta))) AS N_Clientes,
            #IF(t_ruta.venta_preventa = 2,(COUNT(DISTINCT rcr.IdCliente)), (COUNT(DISTINCT rcr.IdCliente))) AS N_Clientes,
            (SELECT COUNT(DISTINCT Id_Destinatario) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta) AS N_Clientes,
            #(COUNT(DISTINCT rcr.IdCliente)) AS N_Clientes,
            (SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND Lu IS NOT NULL) AS Lu,
            (SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND Ma IS NOT NULL) AS Ma,
            (SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND Mi IS NOT NULL) AS Mi,
            (SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND Ju IS NOT NULL) AS Ju,
            (SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND Vi IS NOT NULL) AS Vi,
            (SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND Sa IS NOT NULL) AS Sa,
            (SELECT COUNT(*) FROM RelDayCli WHERE Cve_Ruta = rd.Cve_Ruta AND DO IS NOT NULL) AS DO

            FROM
            t_ruta
            LEFT JOIN Rel_Ruta_Agentes ra ON ra.cve_ruta = t_ruta.ID_Ruta
            LEFT JOIN c_usuario tv ON tv.id_user = ra.cve_vendedor 
            LEFT JOIN RelDayCli rd ON rd.Cve_Ruta = t_ruta.ID_Ruta AND rd.Cve_Vendedor = tv.id_user
            LEFT JOIN RelClirutas rcr on rcr.IdRuta = t_ruta.ID_Ruta
            LEFT JOIN t_clientexruta ON t_clientexruta.clave_ruta = t_ruta.ID_Ruta
            LEFT JOIN c_destinatarios d ON d.id_destinatario = rcr.IdCliente 
        WHERE t_ruta.Activo = 1 AND d.Activo = '1' 
        AND t_ruta.ID_Ruta='$id_ruta' 
            GROUP BY t_ruta.ID_Ruta";

            //INNER JOIN c_compania ON t_ruta.cve_cia = c_compania.cve_cia
            //WHERE
            //t_ruta.descripcion LIKE '%".$_criterio."%' AND t_ruta.Activo = 1;";
    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ".$sql;
    }

    $count = 1;
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

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id']=$row['ID_Ruta'];

        $responce->rows[$i]['cell']=array(
        $row['N_Clientes'],
        $row['Lu'],
        $row['Ma'],
        $row['Mi'],
        $row['Ju'],
        $row['Vi'],
        $row['Sa'],
        $row['DO']
        );
        $i++;
    }
    echo json_encode($responce);
}
else if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $almacen= $_POST['almacen'];
    $agente= $_POST['agente'];
    $ruta= $_POST['ruta'];
    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];

    if($agente)
    {
        $agente = " AND tv.cve_usuario = '".$agente."' ";
    }

    if($ruta)
    {
        $SQLRuta = " AND t_ruta.cve_ruta = '".$ruta."' ";
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
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
        $sql = "SET @id_rut:= 0;";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $sql = "SELECT
            t_ruta.ID_Ruta,
            t_ruta.cve_ruta,
            t_ruta.descripcion,
            #GROUP_CONCAT(DISTINCT tv.nombre_completo SEPARATOR ', ') AS Agentes, 
            tv.cve_usuario,
            tv.nombre_completo AS Agentes, 
            IFNULL(tt.num_ec, tt.ID_Transporte) AS Vehiculo,
            IF(t_ruta.status='A','Activo','Baja') as status,
            #((COUNT(DISTINCT rd.Lu))+(COUNT(DISTINCT rd.Ma))+(COUNT(DISTINCT rd.Mi))+(COUNT(DISTINCT rd.Ju))+(COUNT(DISTINCT rd.Vi))+(COUNT(DISTINCT rd.Sa))+(COUNT(DISTINCT rd.Do))) AS N_Clientes,
            #(COUNT(DISTINCT t_clientexruta.id_clientexruta)) AS N_Clientes,


            #IF(t_ruta.venta_preventa != 2,(COUNT(DISTINCT t_clientexruta.id_clientexruta)), (SELECT COUNT(txr.id_clientexruta) FROM t_clientexruta txr WHERE txr.clave_ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta))) AS N_Clientes,
            #IF(t_ruta.venta_preventa != 2,(COUNT(DISTINCT rd.Lu)), (SELECT COUNT(rdx.Lu) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Lu, '') != '')+(COUNT(DISTINCT rd.Lu))) AS Lu,
            #IF(t_ruta.venta_preventa != 2,(COUNT(DISTINCT rd.Ma)), (SELECT COUNT(rdx.Ma) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Ma, '') != '')+(COUNT(DISTINCT rd.Ma))) AS Ma,
            #IF(t_ruta.venta_preventa != 2,(COUNT(DISTINCT rd.Mi)), (SELECT COUNT(rdx.Mi) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Mi, '') != '')+(COUNT(DISTINCT rd.Mi))) AS Mi,
            #IF(t_ruta.venta_preventa != 2,(COUNT(DISTINCT rd.Ju)), (SELECT COUNT(rdx.Ju) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Ju, '') != '')+(COUNT(DISTINCT rd.Ju))) AS Ju,
            #IF(t_ruta.venta_preventa != 2,(COUNT(DISTINCT rd.Vi)), (SELECT COUNT(rdx.Vi) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Vi, '') != '')+(COUNT(DISTINCT rd.Vi))) AS Vi,
            #IF(t_ruta.venta_preventa != 2,(COUNT(DISTINCT rd.Sa)), (SELECT COUNT(rdx.Sa) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Sa, '') != '')+(COUNT(DISTINCT rd.Sa))) AS Sa,
            #IF(t_ruta.venta_preventa != 2,(COUNT(DISTINCT rd.Do)), (SELECT COUNT(rdx.Do) FROM RelDayCli rdx WHERE rdx.Cve_Ruta IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas WHERE id_ruta_entrega = t_ruta.ID_Ruta) AND IFNULL(rdx.Cve_Vendedor, '') != '' AND IFNULL(rdx.Do, '') != '')+(COUNT(DISTINCT rd.Do))) AS Do,

            #COUNT(DISTINCT rd.Lu) AS Lu,
            #COUNT(DISTINCT rd.Ma) AS Ma,
            #COUNT(DISTINCT rd.Mi) AS Mi,
            #COUNT(DISTINCT rd.Ju) AS Ju,
            #COUNT(DISTINCT rd.Vi) AS Vi,
            #COUNT(DISTINCT rd.Sa) AS Sa,
            #COUNT(DISTINCT rd.Do) AS Do,
            IF(t_ruta.control_pallets_cont = 'S', 'Si', 'No') AS control_pallets_cont,
            IF(t_ruta.venta_preventa = 1, 'Si', 'No') AS venta_preventa,
            IF(t_ruta.venta_preventa = 2, 'Si', 'No') AS ruta_entrega,
            #@id_rut:= t_ruta.ID_Ruta AS asig_id,
            t_ruta.ID_Ruta AS asig_id,
            #IF(t_ruta.venta_preventa = 2, (SELECT DISTINCT GROUP_CONCAT(DISTINCT r.cve_ruta SEPARATOR ', ') FROM t_ruta r INNER JOIN rel_RutasEntregas rr ON rr.id_ruta_venta_preventa = r.ID_Ruta AND rr.id_ruta_entrega = @id_rut), '') AS cve_ruta_entrega,
            IFNULL(IF(t_ruta.venta_preventa = 2, (SELECT DISTINCT GROUP_CONCAT(DISTINCT r.cve_ruta SEPARATOR ', ') FROM t_ruta r INNER JOIN rel_RutasEntregas rr ON rr.id_ruta_venta_preventa = r.ID_Ruta AND rr.id_ruta_entrega = t_ruta.ID_Ruta), ''), '') AS cve_ruta_entrega,
            #'' AS cve_ruta_entrega,
            pr.Nombre AS Nombre_Proveedor,
            t_ruta.Activo
            FROM
            t_ruta
            LEFT JOIN Rel_Ruta_Transporte rt ON rt.cve_ruta = t_ruta.cve_ruta
            LEFT JOIN t_transporte tt ON tt.id = rt.id_transporte
            LEFT JOIN Rel_Ruta_Agentes ra ON ra.cve_ruta = t_ruta.ID_Ruta
            LEFT JOIN c_usuario tv ON tv.id_user = ra.cve_vendedor 
            LEFT JOIN RelDayCli rd ON rd.Cve_Ruta = t_ruta.ID_Ruta AND rd.Cve_Vendedor = tv.id_user
            LEFT JOIN t_clientexruta ON t_clientexruta.clave_ruta = t_ruta.ID_Ruta
            LEFT JOIN c_proveedores pr ON pr.ID_Proveedor = IFNULL(t_ruta.ID_Proveedor, '')
            WHERE t_ruta.Activo = 1 AND 
            (t_ruta.descripcion LIKE '%".$_criterio."%' OR t_ruta.cve_ruta LIKE '%".$_criterio."%' OR tt.Nombre LIKE '%".$_criterio."%' OR tv.nombre_completo LIKE '%".$_criterio."%') {$agente} AND t_ruta.cve_almacenp='".$almacen."' {$SQLRuta}
            GROUP BY t_ruta.ID_Ruta
            #ORDER BY Agentes DESC
            LIMIT $_page, $limit;";
            
            //INNER JOIN c_compania ON t_ruta.cve_cia = c_compania.cve_cia
            //WHERE
            //t_ruta.descripcion LIKE '%".$_criterio."%' AND t_ruta.Activo = 1;";
    // hace una llamada previa al procedimiento almacenado Lis_Facturas
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

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id']=$row['ID_Ruta'];

        if(strpos($_SERVER['HTTP_HOST'], 'sctp') == true) $row['Agentes'] = utf8_decode($row['Agentes']);

        $responce->rows[$i]['cell']=array(
        '',
        $row['ID_Ruta'],
        $row['cve_ruta'],
        $row['descripcion'],
        $row['Agentes'],
        $row['Vehiculo'],
        '', //$row['N_Clientes'],
        '', //$row['Lu'],
        '', //$row['Ma'],
        '', //$row['Mi'],
        '', //$row['Ju'],
        '', //$row['Vi'],
        '', //$row['Sa'],
        '', //$row['Do'],
        $row['venta_preventa'],
        $row['ruta_entrega'],
        $row['cve_ruta_entrega'],
        $row['Nombre_Proveedor'],
        $row['control_pallets_cont'],
        $row['status'],
        $row['cve_usuario']);
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
