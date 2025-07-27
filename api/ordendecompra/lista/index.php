<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
   $page = $_POST['page']; // get the requested page
   $limit = $_POST['rows']; // get how many rows we want to have into the grid
   $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
   $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $search = $_POST['criterio'];
    $almacen = $_POST['almacen'];
    $status_oc = $_POST['status_oc'];
    $filtro = $_POST['filtro'];
    $fechai= !empty($_POST['fechai']) ? date('Y-m-d', strtotime($_POST['fechai'])) : '';
    $fechaf= !empty($_POST['fechaf']) ? date('Y-m-d', strtotime($_POST['fechaf'])) : '';
    $presupuesto = $_POST['presupuesto'];
    $aditionalSearch = '';

    if(!empty($search) && !empty($filtro)){
        if($filtro === "th_aduana.status" ){
            $realStatus = "";
            if(stripos("Recibiendo", $search) !== false && stripos("Recibiendo", $search) >= 0){
                $realStatus = "I";
            }
            elseif(stripos("Pendiente de Recibir", $search) !== false && stripos("Pendiente de Recibir", $search) >= 0){
                $realStatus = "C";
            }
            elseif(stripos("Editando", $search) !== false && stripos("Editando", $search) >= 0){
                $realStatus = "A";
            }
            elseif(stripos("Cerrada", $search) !== false && stripos("Cerrada", $search) >= 0){
                $realStatus = "T";
            }else{
                $realStatus = "NULL";
            }
            $search = $realStatus;
        }

        $aditionalSearch .= " AND ({$filtro} LIKE '%$search%' OR Pedimento LIKE '%$search%')";
    }
    else if(!empty($search))
    {
        $aditionalSearch .= " AND (th_aduana.num_pedimento LIKE '%$search%' OR th_aduana.Factura LIKE '%$search%' OR th_aduana.Factura LIKE '%$search%' OR c_proveedores.Nombre LIKE '%$search%')";
    }

    if(!empty($fechai) && !empty($fechaf)){
        if($fechai === $fechaf){
          $aditionalSearch .= " AND DATE(th_aduana.fech_pedimento) = '$fechai'";
        }else{
          $aditionalSearch .= " AND th_aduana.fech_pedimento BETWEEN STR_TO_DATE('$fechai','%Y-%m-%d') AND STR_TO_DATE('$fechaf','%Y-%m-%d')";
        }
    }
    else{
        if(!empty($fechai)){
            //buscar por fecha mayor
            $aditionalSearch .= " AND th_aduana.fech_pedimento > STR_TO_DATE('$fechai','%Y-%m-%d')";
        }
        if(!empty($fechaf)){
            //buscar por fecha menor
            $aditionalSearch .= " AND th_aduana.fech_pedimento <  STR_TO_DATE('$fechaf','%Y-%m-%d')";
        }
    }
  
     //$prep = "";
    if(!empty($presupuesto)){
      $aditionalSearch .= " and presupuesto='$presupuesto'";
    }
    if(!empty($status_oc)){
      $aditionalSearch .= " AND th_aduana.status='$status_oc'";
    }

	$_page = 0;
  
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sqlCount = "SELECT
                        count(ID_Aduana) AS cuenta
			     FROM th_aduana, c_proveedores 
			     WHERE th_aduana.Activo = 1 AND Cve_Almac='$almacen' and th_aduana.ID_Proveedor = c_proveedores.ID_Proveedor";

    $sqlCount .= $aditionalSearch;

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

	  $_page = 0;

	  if (intval($page)>0) $_page = ($page-1)*$limit;
        $sql = "SELECT
                        th_aduana.ID_Aduana,
                        th_aduana.num_pedimento,
                        DATE_FORMAT(fech_pedimento,'%d-%m-%Y'  ) as fech_pedimento,
                        DATE_FORMAT(fech_llegPed,'%d-%m-%Y'  ) as fech_llegPed,
                        th_aduana.aduana,
                        th_aduana.factura as ocentrada,
                        th_aduana.status as status,
                        th_aduana.ID_Proveedor,
                        th_aduana.ID_Protocolo,
                        th_aduana.Consec_protocolo,
                        c_usuario.nombre_completo as usuario,
                        th_aduana.Cve_Almac,
                        th_aduana.cve_usuario,
                        th_aduana.Activo,
                        th_aduana.Pedimento,
                        c_proveedores.ID_Proveedor,
                  			t_protocolo.descripcion as Protocolo,
                        c_proveedores.Nombre as Empresa,
                        aduana.Nombre as Proveedor,
			                  c_almacenp.nombre as Almacen,
                        recurso,
                        procedimiento,
                        dictamen,
                        c_presupuestos.nombreDePresupuesto,
                        presupuesto,
                        condicionesDePago,
                        lugarDeEntrega,
                        DATE_FORMAT(fechaDeFallo,'%d-%m-%Y'  ) as fechaDeFallo,
                        plazoDeEntrega,
                        Proyecto as numeroDeExpediente,
                        #CONCAT('$', TRUNCATE((SELECT (SUM(td_aduana.cantidad*td_aduana.costo)) FROM td_aduana WHERE num_pedimento = td_aduana.num_orden), 2)) AS importe,
                        areaSolicitante,
                        numSuficiencia,
                        fechaSuficiencia,
                        fechaContrato,
                        montoSuficiencia,
                        numeroContrato
			          from th_aduana
                INNER JOIN c_proveedores ON th_aduana.ID_Proveedor = c_proveedores.ID_Proveedor
                LEFT JOIN c_proveedores aduana ON aduana.cve_proveedor = th_aduana.procedimiento
          			LEFT JOIN t_protocolo ON th_aduana.ID_Protocolo= t_protocolo.ID_Protocolo
          			LEFT JOIN cat_estados ON th_aduana.status=cat_estados.ESTADO
          			LEFT JOIN c_almacenp ON th_aduana.Cve_Almac= c_almacenp.clave
          			LEFT JOIN c_usuario ON th_aduana.cve_usuario IN (c_usuario.id_user, c_usuario.cve_usuario)
                LEFT JOIN c_presupuestos ON th_aduana.presupuesto = c_presupuestos.id
                LEFT JOIN td_aduana ON th_aduana.num_pedimento = td_aduana.num_orden
			          Where th_aduana.Activo = '1' and th_aduana.Cve_Almac='$almacen' 
                ";
//AND th_aduana.status = 'C' 
//$orderby = "c_proveedores.Nombre ASC, th_aduana.Factura+0 DESC";
//if((strpos($_SERVER['HTTP_HOST'], 'dicoisa') === false))
    //$orderby = "th_aduana.fech_pedimento DESC";
    $orderby = "th_aduana.num_pedimento DESC";

    $sql .= $aditionalSearch;
    $sql .= "GROUP BY num_pedimento ORDER BY $orderby";

    //echo var_dump($sql);
    //die();
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
  
    $count = mysqli_num_rows($res);

    $sql .= " LIMIT $start, $limit; ";

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
			if ($row['status']=="A") $row['status2']='Editando';
		if ($row['status']=="C") $row['status2']='Pendiente de Recibir';
		if ($row['status']=="I") $row['status2']='Recibiendo';
        if ($row['status']=="T") $row['status2']='Cerrada';
        if ($row['status']=="K") $row['status2']='Cancelada';
		$row=array_map('utf8_encode', $row);
		$arr[] = $row;
        $responce->rows[$i]['id']=$row['ID_Aduana'];
        $responce->rows[$i]['cell']=array
                                    (
                                      $row[''],
                                      $row['ID_Aduana'],
                                      $row['fech_pedimento'],
                                      $row['num_pedimento'],
                                      $row['ocentrada'],
                                      $row['Empresa'], 
                                      $row['Proveedor'], 
                                      $row['Pedimento'], 
                                      $row['Protocolo'],
                                      $row['Consec_protocolo'],
                                      $row['fech_llegPed'],
                                      $row['status2'],
                                      $row['status'],
                                      $row['Almacen'],
                                      $row['usuario'],
                                      $row['cve_usuario'],
                                      $row['recurso'],
                                      $row['procedimiento'],
                                      $row['dictamen'],
                                      $row['nombreDePresupuesto'],
                                      $row['condicionesDePago'],
                                      $row['lugarDeEntrega'],
                                      $row['fechaDeFallo'],
                                      $row['plazoDeEntrega'],
                                      $row['numeroDeExpediente'],
                                      //$row['importe'],
                                      $row['areaSolicitante'],
                                      $row['numSuficiencia'],
                                      $row['fechaSuficiencia'],
                                      $row['fechaContrato'],
                                      $row['montoSuficiencia'],
                                      $row['numeroContrato']
                                    );
        $i++;
    }
    echo json_encode($responce);
}

if (isset($_GET) && !empty($_GET) && $_GET['action'] === "getDetallesFolio") 
{
   $page = $_GET['page']; // get the requested page
   $limit = $_GET['rows']; // get how many rows we want to have into the grid
   $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
   $sord = $_GET['sord']; // get the direction
   $folio = $_GET['folio'];

   $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
    $sqlCount = "SELECT COUNT(cve_articulo) AS total FROM td_aduana WHERE num_orden = '{$folio}';";

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_fetch_array($res)['total'];
    $sqlCount = "SELECT COUNT(*) total FROM td_entalmacen WHERE num_orden = '{$folio}'";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $total = mysqli_fetch_array($res)['total'];
*/
//if($total == 0)
//{
    $_page = 0;
    if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql = "SELECT DISTINCT
    IFNULL(tdt.ClaveEtiqueta, '') AS LP, 
td.cve_articulo AS clave,
(SELECT des_articulo FROM c_articulo WHERE cve_articulo = td.cve_articulo) AS descripcion,
    0 AS Pallet,
    0 AS Caja, 
    0 AS Piezas,
    IFNULL(td.Factura, '') as Factura_Art,
    IFNULL(ar.cajas_palet, 0) cajasxpallets,
    IFNULL(ar.num_multiplo, 0) piezasxcajas, 
COALESCE((SELECT SUM(CantidadRecibida) FROM td_entalmacen WHERE cve_articulo = td.cve_articulo AND num_orden = td.num_orden), 0) AS surtidas,
(ar.peso * (SELECT surtidas))AS peso,
((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000) * (SELECT surtidas)) AS volumen,
IF((SELECT COUNT(*) serie FROM c_articulo c, td_aduana ctd WHERE c.cve_articulo = td.cve_articulo AND c.control_numero_series = 'S' AND ctd.cve_articulo = c.cve_articulo AND ctd.num_orden = '{$folio}') = 0, td.cve_lote, '') AS lote,
IF(ar.Caduca = 'S' AND IFNULL(td.cve_lote, '') != '', IF(td.caducidad = DATE_FORMAT('0000-00-00', '%Y-%m-%d'), '', DATE_FORMAT(td.caducidad, '%d-%m-%Y')),'') AS caducidad,
IF((SELECT COUNT(*) serie FROM c_articulo c, td_aduana ctd WHERE c.cve_articulo = td.cve_articulo AND c.control_numero_series = 'S' AND ctd.cve_articulo = c.cve_articulo AND ctd.num_orden = '{$folio}') > 0, td.cve_lote, '') AS serie,
IFNULL(tdt.Cantidad, SUM(td.cantidad)) AS pedidas,
td.costo AS precioU,
(td.costo*td.cantidad) AS importeTotal
FROM td_aduana td
LEFT JOIN td_aduanaxtarima tdt ON tdt.Num_Orden = td.num_orden AND td.cve_articulo = tdt.Cve_Articulo AND IFNULL(tdt.Cve_Lote, '') = IFNULL(td.cve_lote, '') 
LEFT JOIN c_articulo ar ON ar.cve_articulo = td.cve_articulo
LEFT JOIN th_aduana th ON th.num_pedimento = td.num_orden
WHERE ar.cve_articulo = td.cve_articulo AND td.num_orden = th.num_pedimento AND td.num_orden = '{$folio}'
GROUP BY LP, lote,serie, clave";
/*
}
else
{

    $sql = "SELECT DISTINCT
td.cve_articulo AS clave,
(SELECT des_articulo FROM c_articulo WHERE cve_articulo = td.cve_articulo) AS descripcion,
    0 AS Pallet,
    0 AS Caja, 
    0 AS Piezas,
    IFNULL(ar.cajas_palet, 0) cajasxpallets,
    IFNULL(ar.num_multiplo, 0) piezasxcajas, 
COALESCE((SELECT SUM(CantidadRecibida) FROM td_entalmacen WHERE cve_articulo = td.cve_articulo AND num_orden = td.num_orden), 0) AS surtidas,
(ar.peso * (SELECT surtidas))AS peso,
((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000) * (SELECT surtidas)) AS volumen,
#IF((SELECT COUNT(*) serie FROM c_articulo c, td_aduana ctd WHERE c.cve_articulo = td.cve_articulo AND c.control_numero_series = 'S' AND ctd.cve_articulo = c.cve_articulo AND ctd.num_orden = '{$folio}') = 0, (SELECT cve_lote FROM td_entalmacen WHERE num_orden = '{$folio}' AND cve_articulo = ar.cve_articulo), '') AS lote,
IF(ar.control_lotes = 'S', td.cve_lote, '') AS lote,
IF(ar.Caduca = 'S', IF(td.caducidad = DATE_FORMAT('0000-00-00', '%Y-%m-%d'), '', DATE_FORMAT(td.caducidad, '%d-%m-%Y')), '') AS caducidad,
#IF((SELECT COUNT(*) serie FROM c_articulo c, td_aduana ctd WHERE c.cve_articulo = td.cve_articulo AND c.control_numero_series = 'S' AND ctd.cve_articulo = c.cve_articulo AND ctd.num_orden = '{$folio}') > 0, (SELECT cve_lote FROM td_entalmacen WHERE num_orden = '{$folio}' AND cve_articulo = ar.cve_articulo), '') AS serie,
IF(ar.control_numero_series = 'S', td.cve_lote, '') AS serie,
#SUM(td.cantidad) * (SELECT surtidas) AS pedidas,
td.cantidad AS pedidas,
ROUND(td.costo * (SELECT surtidas),2) AS precioU,
ROUND(((SELECT precioU) * (SELECT surtidas)),2) AS importeTotal
FROM td_aduana td, c_articulo ar, th_aduana th, td_entalmacen tde
WHERE ar.cve_articulo = td.cve_articulo AND td.num_orden = th.num_pedimento AND td.num_orden = '{$folio}' #AND td.num_orden = '{$folio}'
GROUP BY clave, lote";
}
*/
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);

    $sql .= " LIMIT $_page, $limit";
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

    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);

//**********************************************************************************************************************
        //**************************************************
        //FUNCION PARA LAS COLUMNAS PALLET | CAJAS | PIEZAS
        //**************************************************
        //Archivos donde se encuentra esta función:
        //api\embarques\lista\index.php
        //api\reportes\lista\existenciaubica.php
        //api\reportes\lista\concentradoexistencia.php
        //app\template\page\reportes\existenciaubica.php
        //\Application\Controllers\EmbarquesController.php
        //\Application\Controllers\InventarioController.php
        //**************************************************
        //clave busqueda: COLPCP
        //**************************************************
        $valor1 = 0;
        if($row['piezasxcajas'] > 0)
           $valor1 = $row['pedidas']/$row['piezasxcajas'];

        if($row['cajasxpallets'] > 0)
           $valor1 = $valor1/$row['cajasxpallets'];
       else
           $valor1 = 0;

        $row['Pallet'] = intval($valor1);

        $valor2 = 0;
        $cantidad_restante = $row['pedidas'] - ($row['Pallet']*$row['piezasxcajas']*$row['cajasxpallets']);
        if(!is_int($valor1) || $valor1 == 0)
        {
            if($row['piezasxcajas'] > 0)
               $valor2 = ($cantidad_restante/$row['piezasxcajas']);// - ($row['Pallet']*$row['pedidas']);
        }
        $row['Caja'] = intval($valor2);

        $row['Piezas'] = 0;
        if($row['piezasxcajas'] == 1) 
        {
            $valor2 = 0; 
            $row['Caja'] = 0;
            $row['Piezas'] = $cantidad_restante;
        }
        else if($row['piezasxcajas'] == 0 || $row['piezasxcajas'] == "")
        {
            if($row['piezasxcajas'] == "") $row['piezasxcajas'] = 0;
            $row['Caja'] = 0;
            $row['Piezas'] = $cantidad_restante;
        }

        $cantidad_restante = $cantidad_restante - ($row['Caja']*$row['piezasxcajas']);

        if(!is_int($valor2))
        {
           //$row['Piezas'] = ($row['Caja']*$cantidad_restante) - $row['piezasxcajas'];
            $row['Piezas'] = $cantidad_restante;
        }
        //**************************************************

//**********************************************************************************************************************

        $responce->rows[$i]['id']=$row['clave'];
        $responce->rows[$i]['cell']=array($row['LP'], 
                                          $row['clave'], 
                                          $row['descripcion'], 
                                          $row['lote'],
                                          $row['caducidad'], 
                                          $row['serie'],
                                          $row['Factura_Art'],
                                          $row['Pallet'],
                                          $row['Caja'],
                                          $row['Piezas'],
                                          $row['pedidas'], 
                                          $row['surtidas'],
                                          $row['peso'],
                                          $row['volumen'],
                                          $row['precioU'],
                                          round($row['importeTotal'], 2)
                                          );
        $i++;
    }
    echo json_encode($responce);
}
