<?php
include '../../../config.php';

error_reporting(0);
/*
if (isset($_GET)) {
   $page = $_POST['page']; // get the requested page
   $limit = $_POST['rows']; // get how many rows we want to have into the grid
   $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
   $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = "SELECT
                        count(th_aduana.ID_Aduana) AS total
			          FROM th_aduana
                INNER JOIN c_proveedores ON th_aduana.ID_Proveedor = c_proveedores.ID_Proveedor
                LEFT JOIN td_aduana ad ON ad.`ID_Aduana`=th_aduana.`ID_Aduana`
          			LEFT JOIN t_protocolo ON th_aduana.ID_Protocolo= t_protocolo.ID_Protocolo
          			LEFT JOIN cat_estados ON th_aduana.status=cat_estados.ESTADO
          			LEFT JOIN c_almacenp ON th_aduana.Cve_Almac= c_almacenp.clave
          			LEFT JOIN c_usuario ON th_aduana.cve_usuario = c_usuario.id_user ";


    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

	  $_page = 0;

	  if (intval($page)>0) $_page = ($page-1)*$limit;
        $sql = "SELECT
                        th_aduana.ID_Aduana,
                        ad.`num_orden`,
                        th_aduana.num_pedimento,
                        SUM(ad.cantidad) AS cantidad,
                        DATE_FORMAT(fech_pedimento, '%d-%m-%Y %H:%i:%s') AS fech_pedimento,
                        DATE_FORMAT(fech_llegPed, '%d-%m-%Y %H:%i:%s') AS fech_llegPed,
                        th_aduana.aduana,
                        th_aduana.factura,
                        th_aduana.status AS STATUS,
                        th_aduana.ID_Proveedor,
                        th_aduana.ID_Protocolo,
                        th_aduana.Consec_protocolo,
                        c_usuario.nombre_completo AS usuario,
                        th_aduana.Cve_Almac,
                        th_aduana.cve_usuario,
                        th_aduana.Activo,
                        c_proveedores.ID_Proveedor,
                  	    t_protocolo.descripcion AS Protocolo,
                        c_proveedores.Nombre AS Empresa,
			            c_almacenp.nombre AS Almacen
			          FROM th_aduana
                INNER JOIN c_proveedores ON th_aduana.ID_Proveedor = c_proveedores.ID_Proveedor
                LEFT JOIN td_aduana ad ON ad.`ID_Aduana` = th_aduana.`ID_Aduana`
          			LEFT JOIN t_protocolo ON th_aduana.ID_Protocolo = t_protocolo.ID_Protocolo
          			LEFT JOIN cat_estados ON th_aduana.status = cat_estados.ESTADO
          			LEFT JOIN c_almacenp ON th_aduana.Cve_Almac = c_almacenp.clave
          			LEFT JOIN c_usuario ON th_aduana.cve_usuario = c_usuario.id_user
          			GROUP BY ad.`num_orden` ";

    $sql .= " ORDER BY th_aduana.num_pedimento DESC;";

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
		$row['acciones'] = "<button class='btn btn-sm btn-default no' title='Ver Detalle' onclick='see(".$row["ID_Aduana"].")'><i class='fa fa-search'></i></button>";
        $row['acciones'] .= "&nbsp; <button class='btn btn-sm btn-danger no' title='Reporte PDF' onclick='printPDF(".$row["ID_Aduana"].")'> <i class='fa fa-print'></i><sup> PDF</sup></button>";
        $row['acciones'] .= "&nbsp; <button class='btn btn-sm btn-primary no' title='Reporte Excel' onclick='printExcel(".$row["ID_Aduana"].")'> <i class='fa fa-print'></i><sup> Excel</sup></button>";
        $data[] = $row; 
        $i++; 
    }

    mysqli_close($conn); 
    header('Content-type: application/json'); 
    $output = array( 
        "draw" => $_GET["draw"], 
        "recordsTotal" => $count, 
        "recordsFiltered" => $count, 
        "data" => $data 
    );  
    echo json_encode($output); 
}*/

if (isset($_POST) && !empty($_POST['action']) && $_POST['action'] === 'init') {

    $id_almacen = $_POST['id_almacen'];
/*
    $sql = "SELECT 
                  th_ordenembarque.ID_OEmbarque as folio,
                  c_cliente.RazonSocial as cliente,
                  GROUP_CONCAT(DISTINCT th_pedido.Pick_Num) AS factura,
                  GROUP_CONCAT(DISTINCT th_pedido.Fol_folio) as pedido,
                  #sum(x.cantidad) as cantidad,
                  #sum(x.total_cajas) as total_cajas
                  IFNULL(SUM(x.cantidad), (SELECT SUM(Num_cantidad) FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio)) AS cantidad,
                  IFNULL(SUM(x.total_cajas), 0) AS total_cajas
            FROM th_ordenembarque
            inner JOIN td_ordenembarque on td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            inner JOIN th_pedido on th_pedido.Fol_folio = td_ordenembarque.Fol_folio
            LEFT JOIN c_cliente on c_cliente.Cve_Clte = th_pedido.Cve_clte
            left join (
                        select 
                          th_cajamixta.fol_folio, 
                                    count(Guia) as total_cajas,
                                    sum(td_cajamixta.Cantidad) as cantidad
                        from th_cajamixta
                                inner join td_cajamixta on td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix
                                group by th_cajamixta.fol_folio
                      )x on x.fol_folio = td_ordenembarque.Fol_folio
            WHERE th_ordenembarque.Cve_Almac = {$id_almacen}
            GROUP BY th_ordenembarque.ID_OEmbarque 
            ORDER BY th_ordenembarque.ID_OEmbarque DESC";
  */

    $sql = "SELECT 
                cab.folio, cab.FechaEmbarque, cab.FechaEnvio, cab.cliente, cab.factura, cab.pedido, cab.transporte, cab.chofer, cab.id_transporte, cab.sello
                #, SUM(cab.cantidad) AS cantidad, COUNT(cab.caja) AS total_cajas
            FROM (
            SELECT 
              th_ordenembarque.ID_OEmbarque AS folio,
              DATE_FORMAT(th_ordenembarque.fecha, '%d-%m-%Y') AS FechaEmbarque,
              DATE_FORMAT(IFNULL(th_ordenembarque.FechaEnvio, ''), '%d-%m-%Y') AS FechaEnvio,
              IFNULL(c_cliente.RazonSocial, '') AS cliente,
              IFNULL(GROUP_CONCAT(DISTINCT th_pedido.Pick_Num), '') AS factura,
              IFNULL(GROUP_CONCAT(DISTINCT th_pedido.Fol_folio), '') AS pedido,
              IFNULL(th_ordenembarque.chofer, '') AS chofer,
              REPLACE(IFNULL(CONCAT('(',t.ID_Transporte,') - ', t.Nombre), ''), '\"', '') AS transporte,
              IFNULL(t.ID_Transporte, '') AS id_transporte,
              IFNULL(th_ordenembarque.sello_precinto, '') as sello,
              #IFNULL(td_cajamixta.Cve_articulo, tds.Cve_articulo) AS clave,
              #IFNULL(tds.LOTE, '') AS Lote_Serie,
              #IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), ''), '') AS caducidad,
              #a.des_articulo AS articulo,
              #IFNULL(ch.CveLP, '') AS lp,
              #IFNULL(th_cajamixta.Guia, '') AS guia,
              #IFNULL(tt.cantidad, IFNULL(IF(a.control_lotes = 'S' AND IFNULL(c_lotes.Lote, '') = '', tds.Cantidad, (td_cajamixta.Cantidad)), (SELECT Num_cantidad FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio AND td_pedido.Cve_articulo = td_cajamixta.Cve_articulo AND td_pedido.cve_lote = td_cajamixta.Cve_Lote))) AS cantidad,
              #IFNULL(th_cajamixta.cve_tipocaja, '') AS caja
              ''
        FROM th_ordenembarque
        LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
        LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
        LEFT JOIN t_transporte t ON t.id = th_ordenembarque.ID_Transporte
        #LEFT JOIN td_pedido de ON th_pedido.Fol_folio = de.Fol_folio
        LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
        #LEFT JOIN td_surtidopiezas tds ON tds.fol_folio = th_pedido.Fol_folio #AND de.Cve_articulo = tds.Cve_articulo
        #LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio =th_pedido.Fol_folio AND th_cajamixta.Sufijo = tds.Sufijo
        #LEFT JOIN td_cajamixta ON td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix AND td_cajamixta.Cve_articulo = tds.Cve_articulo AND IFNULL(td_cajamixta.Cve_Lote, '') = IFNULL(tds.LOTE, '')
        #LEFT JOIN c_articulo a ON a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo,tds.Cve_articulo)
        #LEFT JOIN t_tarima tt ON tt.Fol_Folio = tds.fol_folio AND tt.cve_articulo = tds.Cve_articulo AND IFNULL(tt.lote, '') = IFNULL(tds.LOTE, '')
        #LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
      #LEFT JOIN c_lotes ON c_lotes.Lote = tds.LOTE AND c_lotes.cve_articulo = td_cajamixta.Cve_articulo
      #LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo AND c_serie.numero_serie = tds.LOTE
        WHERE th_ordenembarque.Cve_Almac = {$id_almacen} AND th_ordenembarque.Activo = 1 #AND a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo, tds.Cve_articulo)  
        #AND IFNULL(ch.CveLP, ch.clave_contenedor) NOT IN (SELECT ClaveEtiqueta FROM t_recorrido_surtido WHERE fol_folio = th_pedido.Fol_folio) 
        #AND IFNULL(IF(IFNULL(td_cajamixta.cve_lote, '') != '', td_cajamixta.cve_lote, ''), IFNULL(c_lotes.Lote, '')) = IFNULL(IF(IFNULL(td_cajamixta.cve_lote, '') != '', tds.LOTE, ''), '')
            GROUP BY folio #,clave, Lote_Serie, lp

            ) AS cab
            GROUP BY cab.folio
            ORDER BY cab.folio DESC";
    $res = getArraySQL($sql);

    $array = [
        "res"=>$res
    ];

    echo json_encode($array);

}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'detalle') {

   $folio = $_POST['inventario'];
/*
    $sql = "SELECT 
                  th_ordenembarque.ID_OEmbarque AS embarque,
                  th_pedido.Fol_folio AS folio,
                  IFNULL(td_cajamixta.Cve_articulo, td_pedido.cve_articulo) AS clave,
                  c_articulo.des_articulo AS articulo,
                  IFNULL(td_cajamixta.Cantidad, (SELECT SUM(Num_cantidad) FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio)) AS cantidad,
                  IFNULL(th_cajamixta.cve_tipocaja, 0) AS caja,
                  IFNULL(th_cajamixta.Guia, '') AS guia
            FROM th_ordenembarque
            LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
            LEFT JOIN td_pedido ON th_pedido.Fol_folio = td_pedido.Fol_folio
            LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
            LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio =th_pedido.Fol_folio
            LEFT JOIN td_cajamixta ON td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = td_pedido.Cve_articulo
            WHERE th_ordenembarque.ID_OEmbarque = '{$folio}'
            ORDER BY th_pedido.Fol_folio, th_cajamixta.NCaja";
*/
    $sql = "SELECT 
                  th_ordenembarque.ID_OEmbarque AS embarque,
                  th_pedido.Fol_folio AS folio,
                  IFNULL(td_cajamixta.Cve_articulo, tds.Cve_articulo) AS clave,
              #IFNULL(IF(a.control_lotes = 'S', c_lotes.Lote, IF(a.control_numero_series = 'S', c_serie.numero_serie, '')), IFNULL(de.cve_lote, IFNULL(td_cajamixta.cve_lote, ''))) AS Lote_Serie,
              IFNULL(tds.LOTE, '') AS Lote_Serie,
              IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), ''), '') AS caducidad,
                  a.des_articulo AS articulo,
                  IFNULL(IFNULL(IFNULL(ppt.Num_cantidad, tt.cantidad), IFNULL(IF(a.control_lotes = 'S' AND IFNULL(c_lotes.Lote, '') = '', tds.Cantidad, (td_cajamixta.Cantidad)), (SELECT Num_cantidad FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio AND td_pedido.Cve_articulo = td_cajamixta.Cve_articulo AND td_pedido.cve_lote = td_cajamixta.Cve_Lote))), tds.Cantidad) AS cantidad,
                  IFNULL(th_cajamixta.cve_tipocaja, 'Pallet') AS caja,
                  IFNULL(ch.CveLP, '') AS lp,
                  IFNULL(a.peso, 0) AS peso,
                  IFNULL(th_cajamixta.Guia, '') AS guia
            FROM th_ordenembarque
            LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
            #LEFT JOIN td_pedido de ON th_pedido.Fol_folio = de.Fol_folio
            LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
            LEFT JOIN td_surtidopiezas tds ON tds.fol_folio = th_pedido.Fol_folio #AND de.Cve_articulo = tds.Cve_articulo
            LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio =th_pedido.Fol_folio AND th_cajamixta.Sufijo = tds.Sufijo
            LEFT JOIN td_cajamixta ON td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix AND td_cajamixta.Cve_articulo = tds.Cve_articulo AND IFNULL(td_cajamixta.Cve_Lote, '') = IFNULL(tds.LOTE, '')
            LEFT JOIN c_articulo a ON a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo,tds.Cve_articulo)
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = tds.fol_folio AND tt.cve_articulo = tds.Cve_articulo AND IFNULL(tt.lote, '') = IFNULL(tds.LOTE, '')
            LEFT JOIN td_pedidoxtarima ppt ON ppt.Fol_folio = tds.Fol_folio AND ppt.Cve_Articulo = tds.Cve_articulo AND ppt.cve_lote = tds.LOTE
            LEFT JOIN c_charolas ch ON ch.IDContenedor = IFNULL(ppt.nTarima, tt.ntarima) #ch.IDContenedor = tt.ntarima
          LEFT JOIN c_lotes ON c_lotes.Lote = tds.LOTE AND c_lotes.cve_articulo = td_cajamixta.Cve_articulo
          LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo AND c_serie.numero_serie = tds.LOTE
            WHERE th_ordenembarque.ID_OEmbarque = '{$folio}' AND a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo, tds.Cve_articulo)  
            AND IFNULL(ch.CveLP, ch.clave_contenedor) NOT IN (SELECT ClaveEtiqueta FROM t_recorrido_surtido WHERE fol_folio = th_pedido.Fol_folio AND ClaveEtiqueta != 0) 
            AND IFNULL(IF(IFNULL(td_cajamixta.cve_lote, '') != '', td_cajamixta.cve_lote, ''), IFNULL(c_lotes.Lote, '')) = IFNULL(IF(IFNULL(td_cajamixta.cve_lote, '') != '', tds.LOTE, ''), '')
            GROUP BY clave, Lote_Serie, folio, lp
            #ORDER BY th_pedido.Fol_folio, th_cajamixta.NCaja;

        ";
   $res = getArraySQL($sql);

    $array = [
        "res"=>$res
    ];

    echo json_encode($array);
}
/*
if (isset($_POST) && !empty($_POST)) {
   $page = $_POST['page']; // get the requested page
   $limit = $_POST['rows']; // get how many rows we want to have into the grid
   $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
   $sord = $_POST['sord']; // get the direction
   $folio = $_POST['inventario'];

   $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sqlCount = "SELECT  COUNT(ar.cve_articulo) AS total
            FROM td_aduana ad
            LEFT JOIN td_entalmacen al ON al.cve_articulo = ad.cve_articulo AND al.fol_folio = ad.num_orden
            LEFT JOIN c_articulo ar ON ar.cve_articulo = ad.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = ad.cve_articulo AND l.LOTE = al.cve_lote
            LEFT JOIN c_serie s ON s.clave_articulo = ad.cve_articulo
            WHERE ad.`ID_Aduana` = '{$folio}';";

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_fetch_array($res)['total'];

    $sql = "SELECT  
                    '' as nro_cliente,
                    '' as cliente,
                    ar.cve_articulo AS clave,
                    ar.des_articulo AS descripcion,
                    COALESCE((SELECT SUM(CantidadRecibida) FROM td_entalmacen WHERE cve_articulo = ad.cve_articulo AND fol_folio = ad.num_orden), 0) AS surtidas,
                    (ar.peso * (SELECT surtidas))AS peso,
                    ((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000) * (SELECT surtidas)) AS volumen,
                    COALESCE(al.cve_lote, '--') AS lote,
                    COALESCE(l.CADUCIDAD, '--') AS caducidad,
                    COALESCE(s.numero_serie, '--') AS serie,
                    SUM(ad.`cantidad`)AS cantidad,
                    ad.cantidad AS pedidas
            FROM td_aduana ad
            LEFT JOIN td_entalmacen al ON al.cve_articulo = ad.cve_articulo AND al.fol_folio = ad.num_orden
            LEFT JOIN c_articulo ar ON ar.cve_articulo = ad.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = ad.cve_articulo AND l.LOTE = al.cve_lote
            LEFT JOIN c_serie s ON s.clave_articulo = ad.cve_articulo
            WHERE ad.`ID_Aduana` = '{$folio}'
            GROUP BY ad.`cve_articulo`";

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
        $data[] = $row; 
        $i++; 
    }

    mysqli_close($conn); 
    header('Content-type: application/json'); 
    $output = array( 
        "draw" => $_GET["draw"], 
        "recordsTotal" => $count, 
        "recordsFiltered" => $count, 
        "data" => $data 
    );  
    echo json_encode($output); 
}
*/
if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'txt'){

    header("Content-type: text/plain");
    header("Content-Disposition: attachment; filename=Reporte_ASN.txt");
    print "No. Cliente || Nombre Comercial || Clave || Descripción || Cantidad || Lote || Caducidada || Serie \n";

    /*$myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
    $txt = "Mickey Mouse\n";
    fwrite($myfile, $txt);
    $txt = "Minnie Mouse\n";
    fwrite($myfile, $txt);
    fclose($myfile);*/
   //print $\n;

    $id = $_POST['id'];

    $sql = "SELECT  
                    '' as nro_cliente,
                    '' as cliente,
                    ar.cve_articulo AS clave,
                    ar.des_articulo AS descripcion,
                    COALESCE((SELECT SUM(CantidadRecibida) FROM td_entalmacen WHERE cve_articulo = ad.cve_articulo AND fol_folio = ad.num_orden), 0) AS surtidas,
                    (ar.peso * (SELECT surtidas))AS peso,
                    ((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000) * (SELECT surtidas)) AS volumen,
                    COALESCE(al.cve_lote, '--') AS lote,
                    COALESCE(l.CADUCIDAD, '--') AS caducidad,
                    COALESCE(s.numero_serie, '--') AS serie,
                    SUM(ad.`cantidad`)AS cantidad,
                    ad.cantidad AS pedidas
            FROM td_aduana ad
            LEFT JOIN td_entalmacen al ON al.cve_articulo = ad.cve_articulo AND al.fol_folio = ad.num_orden
            LEFT JOIN c_articulo ar ON ar.cve_articulo = ad.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = ad.cve_articulo AND l.LOTE = al.cve_lote
            LEFT JOIN c_serie s ON s.clave_articulo = ad.cve_articulo
            WHERE ad.`ID_Aduana` = '{$id}'
            GROUP BY ad.`cve_articulo`";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($query, MYSQLI_ASSOC);

    foreach($data as $d){

        print $d['nro_cliente']." || ".$d['cliente']." || ".$d['clave']." || ".$d['descripcion']." || ".$d['cantidad']." || ".$d['lote']." || ".$d['caducidad']." || ".$d['serie']."\n";

    }
}
if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'exportExcelinvfidet'){
    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $id = $_POST['id'];
    //$consolidado = $_POST['consolidado'];

    $title = "Reporte de Embarque ASN #{$id}.xlsx";
  
    $sqlHeader = "SELECT 
                  th_ordenembarque.ID_OEmbarque AS folio,
                  c_cliente.RazonSocial AS cliente,
                  '' AS factura,
                  GROUP_CONCAT(DISTINCT th_pedido.Fol_folio) AS pedido,
                  IFNULL(SUM(td_cajamixta.Cantidad), (SELECT SUM(Num_cantidad) FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio)) AS cantidad,
                  COUNT(th_cajamixta.Guia) AS total_cajas
            FROM th_ordenembarque
            LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
            LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
            LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio =th_pedido.Fol_folio
            LEFT JOIN td_cajamixta ON td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix
            WHERE th_ordenembarque.ID_OEmbarque = '{$id}'
            GROUP BY th_ordenembarque.ID_OEmbarque";
    

    /*$sql = "SELECT 
                  th_ordenembarque.ID_OEmbarque AS embarque,
                  th_pedido.Fol_folio AS folio,
                  IFNULL(td_cajamixta.Cve_articulo, de.Cve_articulo) AS clave,
                  a.des_articulo AS articulo,
                  IFNULL(td_cajamixta.Cantidad, (SELECT SUM(Num_cantidad) FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio)) AS cantidad,
                  th_cajamixta.cve_tipocaja AS caja,
                  IFNULL(IF(a.control_lotes = 'S', c_lotes.Lote, IF(a.control_numero_series = 'S', c_serie.numero_serie, '')), '') AS Lote_Serie,
                  IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), ''), '') AS caducidad,
                  th_cajamixta.Guia AS guia
            FROM th_ordenembarque
            LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
            LEFT JOIN td_pedido de ON th_pedido.Fol_folio = de.Fol_folio
            LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
            LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio =th_pedido.Fol_folio
            LEFT JOIN td_cajamixta ON td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix
            LEFT JOIN c_articulo a ON a.cve_articulo = de.Cve_articulo
            LEFT JOIN c_lotes ON c_lotes.Lote = de.cve_lote AND c_lotes.cve_articulo = de.cve_articulo 
            LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo AND c_serie.numero_serie = de.cve_lote
            WHERE th_ordenembarque.ID_OEmbarque = '{$id}' AND a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo, de.Cve_articulo) 
            GROUP BY clave, Lote_Serie
            ORDER BY th_pedido.Fol_folio, th_cajamixta.NCaja";*/
/*
    $sql = "SELECT 
                  th_ordenembarque.ID_OEmbarque AS embarque,
                  th_pedido.Fol_folio AS folio,
                  IFNULL(td_cajamixta.Cve_articulo, de.Cve_articulo) AS clave,
              IFNULL(IF(a.control_lotes = 'S', c_lotes.Lote, IF(a.control_numero_series = 'S', c_serie.numero_serie, '')), '') AS Lote_Serie,
              IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), ''), '') AS caducidad,
                  a.des_articulo AS articulo,
                  IFNULL(tt.cantidad, IFNULL(SUM(td_cajamixta.Cantidad), (SELECT SUM(Num_cantidad) FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio))) AS cantidad,
                  IFNULL(th_cajamixta.cve_tipocaja, '') AS caja,
                  IFNULL(ch.CveLP, '') AS LP,
                  IFNULL(th_cajamixta.Guia, '') AS guia
            FROM th_ordenembarque
            LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
            LEFT JOIN td_pedido de ON th_pedido.Fol_folio = de.Fol_folio
            LEFT JOIN t_tarima tt ON tt.Fol_Folio = th_pedido.Fol_folio
            LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
            LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
            LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio =th_pedido.Fol_folio
            LEFT JOIN td_cajamixta ON td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix
            LEFT JOIN c_articulo a ON a.cve_articulo = de.Cve_articulo
          LEFT JOIN c_lotes ON c_lotes.Lote = de.cve_lote AND c_lotes.cve_articulo = de.cve_articulo 
          LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo AND c_serie.numero_serie = de.cve_lote
            WHERE th_ordenembarque.ID_OEmbarque = '$id' AND a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo, de.Cve_articulo) 
            GROUP BY clave, Lote_Serie, LP
            ORDER BY th_pedido.Fol_folio, th_cajamixta.NCaja";

    if($consolidado)
*/
        $sql = "SELECT 
                      th_ordenembarque.ID_OEmbarque AS embarque,
                      DATE_FORMAT(th_ordenembarque.fecha, '%d-%m-%Y') AS FechaEmbarque,
                      DATE_FORMAT(IFNULL(th_ordenembarque.FechaEnvio, ''), '%d-%m-%Y') AS FechaEnvio,
                      GROUP_CONCAT(DISTINCT CONCAT('(',c_cliente.Cve_Clte, ') - ', c_cliente.RazonSocial)) AS cliente, 
                      th_pedido.Fol_folio AS folio,
                      IFNULL(td_cajamixta.Cve_articulo, ts.Cve_articulo) AS clave,
                  IFNULL(IF(a.control_lotes = 'S', ts.LOTE, IF(a.control_numero_series = 'S', ts.LOTE, '')), '') AS Lote_Serie,
                  IFNULL(IF(a.control_lotes = 'S' AND a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), ''), '') AS caducidad,
                      a.des_articulo AS articulo,
                      IFNULL(tt.cantidad, IFNULL((ts.Cantidad), (SELECT SUM(Num_cantidad) FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio))) AS cantidad,
                      IFNULL(th_cajamixta.cve_tipocaja, '') AS caja,
                      IFNULL(ch.CveLP, '') AS LP,
                      IFNULL(th_cajamixta.Guia, '') AS guia
                FROM th_ordenembarque
                LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
                LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
                LEFT JOIN td_surtidopiezas ts ON ts.fol_folio = th_pedido.Fol_folio 
                LEFT JOIN t_tarima tt ON tt.Fol_Folio = th_pedido.Fol_folio AND tt.cve_articulo = ts.Cve_articulo AND tt.lote = ts.LOTE
                LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
                LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
                LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio =th_pedido.Fol_folio
                LEFT JOIN td_cajamixta ON td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix AND td_cajamixta.Cve_articulo = ts.Cve_articulo AND td_cajamixta.Cve_Lote = ts.LOTE
                LEFT JOIN c_articulo a ON a.cve_articulo = ts.Cve_articulo
              LEFT JOIN c_lotes ON c_lotes.Lote = ts.LOTE AND c_lotes.cve_articulo = ts.Cve_articulo
              LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo AND c_serie.numero_serie = ts.LOTE
                WHERE th_ordenembarque.ID_OEmbarque = '$id' AND a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo, ts.Cve_articulo) 
                GROUP BY folio, clave, Lote_Serie, LP
                ORDER BY th_pedido.Fol_folio, th_cajamixta.NCaja";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');
    $excel = new XLSXWriter();
/*
    $queryCabecera = mysqli_query($conn, $sqlHeader);
    $dataCabecera = mysqli_fetch_all($queryCabecera, MYSQLI_ASSOC)[0];
    $header_head = array('Folio de Embarque','Cliente','Factura','Pedido','Cantidad','Total Cajas');
    $body_head = array($dataCabecera['folio'],$dataCabecera['cliente'],$dataCabecera['factura'],$dataCabecera['pedido'],$dataCabecera['cantidad'],$dataCabecera['total_cajas']);
    $excel->writeSheetRow('Sheet1', $header_head );
    $excel->writeSheetRow('Sheet1', $body_head);
  */
    
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($query, MYSQLI_ASSOC);
    $header = array('Embarque', 'Fecha Embarque', 'Fecha Envio','Folio', 'Cliente','Clave','Articulo', 'Lote|Serie', 'Caducidad','Cantidad', 'LP','Clave Caja','Guia');
    $excel->writeSheetRow('Sheet1', $header );
    foreach($data as $d){
        $row = array($d['embarque'],$d['FechaEmbarque'],$d['FechaEnvio'],$d['folio'],$d['cliente'],$d['clave'],$d['articulo'], $d['Lote_Serie'], $d['caducidad'], $d['cantidad'], $d['LP'] ,$d['caja'],$d['guia']);
        $excel->writeSheetRow('Sheet1', $row );
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
}


if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'exportExcelinvfidetDespacho'){
    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $id = $_POST['id'];
    //$consolidado = $_POST['consolidado'];

    $title = "Archivo de Despacho #{$id}.xlsx";
  
    $sqlHeader = "SELECT 
                  th_ordenembarque.ID_OEmbarque AS folio,
                  c_cliente.RazonSocial AS cliente,
                  '' AS factura,
                  GROUP_CONCAT(DISTINCT th_pedido.Fol_folio) AS pedido,
                  IFNULL(SUM(td_cajamixta.Cantidad), (SELECT SUM(Num_cantidad) FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio)) AS cantidad,
                  COUNT(th_cajamixta.Guia) AS total_cajas
            FROM th_ordenembarque
            LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
            LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
            LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
            LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio =th_pedido.Fol_folio
            LEFT JOIN td_cajamixta ON td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix
            WHERE th_ordenembarque.ID_OEmbarque = '{$id}'
            GROUP BY th_ordenembarque.ID_OEmbarque";
    
/*
        $sql = "SELECT 
    IFNULL(th_cajamixta.Guia, '') AS guia,
    th_pedido.Fol_folio AS factura,
    c_cliente.Colonia AS distrito,
    c_cliente.CodigoPostal AS cdane,
    c_cliente.RazonSocial AS cliente,
    c_cliente.CalleNumero AS direccion,
    c_cliente.Ciudad AS ciudad,
    IFNULL(th_cajamixta.cve_tipocaja, '') AS caja,
    '' AS KL,
    '' AS v_decldo,
    '' AS o_compra,
    IFNULL(tt.cantidad, IFNULL((ts.Cantidad), (SELECT SUM(Num_cantidad) FROM td_pedido WHERE Fol_folio = th_pedido.Fol_folio))) AS cantidad,
    th_pedido.Observaciones AS Observaciones

                FROM th_ordenembarque
                LEFT JOIN td_ordenembarque ON td_ordenembarque.ID_OEmbarque = th_ordenembarque.ID_OEmbarque
                LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_ordenembarque.Fol_folio
                LEFT JOIN td_surtidopiezas ts ON ts.fol_folio = th_pedido.Fol_folio 
                LEFT JOIN t_tarima tt ON tt.Fol_Folio = th_pedido.Fol_folio AND tt.cve_articulo = ts.Cve_articulo AND tt.lote = ts.LOTE
                LEFT JOIN c_charolas ch ON ch.IDContenedor = tt.ntarima
                LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
                LEFT JOIN th_cajamixta ON th_cajamixta.fol_folio =th_pedido.Fol_folio
                LEFT JOIN td_cajamixta ON td_cajamixta.Cve_CajaMix = th_cajamixta.Cve_CajaMix AND td_cajamixta.Cve_articulo = ts.Cve_articulo AND td_cajamixta.Cve_Lote = ts.LOTE
                LEFT JOIN c_articulo a ON a.cve_articulo = ts.Cve_articulo
              LEFT JOIN c_lotes ON c_lotes.Lote = ts.LOTE AND c_lotes.cve_articulo = ts.Cve_articulo
              LEFT JOIN c_serie ON c_serie.cve_articulo = a.cve_articulo AND c_serie.numero_serie = ts.LOTE
                WHERE th_ordenembarque.ID_OEmbarque = '{$id}' AND a.cve_articulo = IFNULL(td_cajamixta.Cve_articulo, ts.Cve_articulo) 
                GROUP BY factura
                ORDER BY th_pedido.Fol_folio, th_cajamixta.NCaja";
*/
    $sql = "SELECT  MAX(IfNull(M.Guia,0)) Guia,H.Fol_folio factura,D.Colonia Distrito,D.postal cdane,D.RazonSocial Cliente,
                    D.direccion,D.Ciudad,Count(M.Cve_CajaMix) Cajas,'' KL,'' v_decldo,H.Pick_Num o_compra,
                    (Select SUM(Cantidad) From td_surtidopiezas Where Fol_Folio=H.Fol_folio) cantidad,H.Observaciones
            From    th_pedido H Join th_cajamixta M On H.Fol_folio=M.fol_folio
                    Left Join c_destinatarios D On H.Cve_Clte=D.Cve_Clte And H.Cve_CteProv=D.clave_destinatario
                    Join th_subpedido B on H.Fol_folio=B.Fol_folio And M.Sufijo=B.Sufijo
                    Join td_ordenembarque E On M.fol_folio=E.Fol_folio
            Where   E.ID_OEmbarque='{$id}'
            Group By H.Fol_folio,D.ciudad,D.postal,D.RazonSocial,D.direccion,D.Ciudad,H.Pick_Num,H.Observaciones;";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
    mysqli_set_charset($conn, 'utf8');
    $excel = new XLSXWriter();
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($query, MYSQLI_ASSOC);
    //$header = array('Guia', 'Factura', 'Distrito', 'C Dane', 'Cliente', 'Direccion', 'Ciudad', 'Cajas', 'KL', 'V Decldo', 'O Compra', 'T Unids', 'Observaciones');
    $header = array('Guia', 'Factura', 'Distrito', 'C Dane', 'Cliente', 'Direccion', 'Ciudad', 'Cajas', 'KL', 'V Decldo', 'O Compra', 'T Unids', 'Observaciones');
    $excel->writeSheetRow('Despacho', $header );
    foreach($data as $d){
        //$row = array(' '.$d['guia'],$d['factura'],$d['distrito'],$d['cdane'],$d['cliente'],$d['direccion'],$d['ciudad'], $d['caja'], $d['KL'], $d['v_decldo'], $d['o_compra'] ,$d['cantidad'],$d['Observaciones']);
        $row = array(' '.$d['Guia'],$d['factura'],$d['Distrito'],$d['cdane'],$d['Cliente'],$d['direccion'],$d['Ciudad'], $d['Cajas'], $d['KL'], $d['v_decldo'], $d['o_compra'] ,$d['cantidad'],$d['Observaciones']);
        $excel->writeSheetRow('Despacho', $row );
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
}


function getArraySQL($sql){

    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    mysqli_set_charset($conexion, "utf8");

    if(!$result = mysqli_query($conexion, $sql)) 
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";;

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