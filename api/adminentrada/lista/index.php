<?php
include '../../../config.php';

error_reporting(0);


if(isset($_POST) && !empty($_POST))
{
if( $_POST['action'] == 'DetalleEdicion' ) 
{
    $folio  = $_POST["folio"];
    $oc  = $_POST["oc"];
    $tipo  = $_POST["tipo"];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

    $sql = "SELECT COUNT(*) as existe, num_pedimento FROM th_aduana WHERE num_pedimento IN (SELECT id_ocompra FROM th_entalmacen WHERE fol_folio = {$folio})";
    if (!($res = mysqli_query($conn, $sql))) {echo json_encode(array( "error" => "02Error al procesar la petición: (" . mysqli_error($conn) . ") "));}
    $row_tiene_oc = mysqli_fetch_array($res);
    $tiene_oc = $row_tiene_oc['existe'];
    $oc = $row_tiene_oc['num_pedimento'];

    if($tipo == 'OC' || $tiene_oc)
    $sql = "SELECT IFNULL(recurso, '') as referencia, IFNULL(Pedimento, '') as pedimento FROM th_aduana
            WHERE num_pedimento = {$oc}";
    else
        $sql = "SELECT IFNULL(Referencia_Well, '') as referencia, IFNULL(Pedimento_Well, '') as pedimento FROM th_entalmacen
            WHERE Fol_Folio = {$folio}";
    if (!($res = mysqli_query($conn, $sql))) {echo json_encode(array( "error" => "02Error al procesar la petición: (" . mysqli_error($conn) . ") "));}

    $row = mysqli_fetch_array($res);
                $responce = "";
                $responce = array();

                $responce["referencia"]  = $row['referencia'];
                $responce["pedimento"]   = $row['pedimento'];

    echo json_encode(array( "referencia" => $responce["referencia"], 
                            "pedimento" => $responce["pedimento"], 
                            "SQL" => $sql
                        ));
}
else if( $_POST['action'] == 'DetalleTransporte' ) 
{
    $folio  = $_POST["folio"];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

    $sql = "SELECT IFNULL(Operador, '') as Operador, IFNULL(No_Unidad, '') as No_Unidad, IFNULL(Placas, '') as Placas, 
                   IFNULL(Linea_Transportista, '') as Linea_Transportista, IFNULL(Observaciones, '') as Observaciones, 
                   IFNULL(Sello, '') AS Sello, DATE_FORMAT(IFNULL(Fec_Ingreso, ''), '%d-%m-%Y') AS Fec_Ingreso, 
                   DATE_FORMAT(IFNULL(Fec_Ingreso, ''), '%H:%i:%S') AS Hora_Ingreso, IFNULL(Id_Operador, '') AS Id_Operador  
            FROM t_entalmacentransporte 
            WHERE Fol_Folio = {$folio}";
    if (!($res = mysqli_query($conn, $sql))) {echo json_encode(array( "error" => "02Error al procesar la petición: (" . mysqli_error($conn) . ") "));}

    $row = mysqli_fetch_array($res);
                $responce = "";
                $responce = array();

                $responce["Operador"]           = $row['Operador'];
                $responce["No_Unidad"]        = $row['No_Unidad'];
                $responce["Placas"]                 = $row['Placas'];
                $responce["Linea_Transportista"]            = $row['Linea_Transportista'];
                $responce["Observaciones"]          = $row['Observaciones'];
                $responce["Sello"]             = $row['Sello'];
                $responce["Fec_Ingreso"]               = $row['Fec_Ingreso'];
                $responce["Hora_Ingreso"]               = $row['Hora_Ingreso'];
                $responce["Id_Operador"]             = $row["Id_Operador"];

    echo json_encode(array( "Operador" => $responce["Operador"], 
                            "No_Unidad" => $responce["No_Unidad"], 
                            "Placas" => $responce["Placas"], 
                            "Linea_Transportista" => $responce["Linea_Transportista"], 
                            "Observaciones" => $responce["Observaciones"], 
                            "Sello" => $responce["Sello"], 
                            "Fec_Ingreso" => $responce["Fec_Ingreso"], 
                            "Hora_Ingreso" => $responce["Hora_Ingreso"], 
                            "Id_Operador" => $responce["Id_Operador"], 
                            "SQL" => $sql
                        ));
}
else if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
	
	$search = $_POST['search'];
    $refWell = $_POST['refWell'];
    $pedimentoW = $_POST['pedimentoW'];
    $almacen = $_POST['almacen'];
    $filtro = $_POST['filtro'];
    $tipo_entrada = $_POST['tipo_ent'];
    $fechai= !empty($_POST['fechaInicio']) ? date('d-m-Y', strtotime($_POST['fechaInicio'])) : '';
    $fechaf= !empty($_POST['fechaFin']) ? date('d-m-Y', strtotime($_POST['fechaFin'])) : '';
    //$presupuesto = $_POST['presupuesto'];
    $aditionalSearch = '';
/*
    if(!empty($search) && !empty($filtro)){
        if($filtro === "estado" ){
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
            }
            else{
            	$realStatus = "NULL";
            }
            $search = $realStatus;
        }
        else if($filtro === "erp" ){
            $aditionalSearch .= " AND A.erp LIKE '%$search%'";
        }

        $aditionalSearch .= " AND {$filtro} LIKE '%$search%'";
    }
    else 
*/
    if(!empty($search)){
        //buscar por folio
        $aditionalSearch .= " AND (th_aduana.factura LIKE '%$search%' OR th_entalmacen.fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo LIKE '%$search%') OR th_entalmacen.fol_folio LIKE '%$search%' OR th_aduana.num_pedimento LIKE '%$search%' OR td_entalmacenxtarima.ClaveEtiqueta LIKE '%$search%') ";
    }

    if($search == '')
    {
        if(!empty($fechai) && !empty($fechaf)){
            if($fechai === $fechaf){
              //$aditionalSearch .= " AND DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') like '%$fechai%'";
                $aditionalSearch .= " AND th_entalmacen.Fec_Entrada like '%STR_TO_DATE('$fechai', '%d-%m-%Y')%' ";
            }else{
              //$aditionalSearch .= " AND DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') BETWEEN '$fechai' AND '$fechaf'";
                $aditionalSearch .= " AND th_entalmacen.Fec_Entrada BETWEEN STR_TO_DATE('$fechai', '%d-%m-%Y') AND STR_TO_DATE('$fechaf', '%d-%m-%Y') ";
            }
        }
        else{
            if(!empty($fechai)){
                //buscar por fecha mayor
                //$aditionalSearch .= " AND DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') >= '$fechai'";
                $aditionalSearch .= " AND th_entalmacen.Fec_Entrada >= STR_TO_DATE('$fechai', '%d-%m-%Y') ";
            }
            if(!empty($fechaf)){
                //buscar por fecha menor
                //$aditionalSearch .= " AND DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') <= '$fechaf'";
                $aditionalSearch .= " AND th_entalmacen.Fec_Entrada <= STR_TO_DATE('$fechaf', '%d-%m-%Y') ";
            }
        }
    }
    //if($_POST['tipo_entrada'] == 'd')
      //$aditionalSearch .= " AND (th_entalmacen.tipo = 'DV' OR th_entalmacen.tipo = 'DVL') ";
  
    //if($_POST['tipo_entrada'] == 'e')
      //$aditionalSearch .= " AND th_entalmacen.tipo != 'DV' AND th_entalmacen.tipo != 'DVL' ";

    if($tipo_entrada == 'DV')
      $aditionalSearch .= " AND (th_entalmacen.tipo = 'DV' OR th_entalmacen.tipo = 'DVL') ";

    if($tipo_entrada != 'DV' && $tipo_entrada != "")
      $aditionalSearch .= " AND th_entalmacen.tipo = '$tipo_entrada' ";

    if($refWell)
        $aditionalSearch .= " AND th_aduana.recurso LIKE '%$refWell%' ";

    if($pedimentoW)
        $aditionalSearch .= " AND th_aduana.Pedimento LIKE '%$pedimentoW%' ";


    $filtro_clientes = " WHERE 1 AND th_entalmacen.Cve_Almac = '{$almacen}'";
    if(isset($_POST['cve_cliente']))
    {
      if($_POST['cve_cliente'])
      {
          $cliente = $_POST['cve_cliente'];
          $filtro_clientes .= "AND c_proveedores.id_proveedor = (SELECT ID_Proveedor FROM c_cliente WHERE Cve_Clte = '$cliente')";
      }
    }

    if(isset($_POST['cve_proveedor']))
    {
      if($_POST['cve_proveedor'])
      {
          $cve_proveedor = $_POST['cve_proveedor'];
          $filtro_clientes .= "AND c_proveedores.id_proveedor = {$cve_proveedor}";
      }
    }

    //$prep = "";
    /*if(!empty($presupuesto)){
      $aditionalSearch .= " and presupuesto='$presupuesto'";
    }*/

	$_page = 0;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)
  //if (intval($page)>0) $_page = ($page-1)*$limit;//

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = 
        "SELECT * FROM (SELECT th_entalmacen.fol_folio AS folio_entradas,
               th_entalmacen.id_ocompra,
               th_entalmacen.tipo AS tipo,
               td_entalmacen.tipo_entrada AS tipo_entrada, 
               c_usuario.nombre_completo AS usuario_activo, 
               Date_format(th_aduana.fech_pedimento, '%d-%m-%Y %I:%i:%s %p') AS fecha_entrega, 
               (SELECT Min( Date_format(td_entalmacen.fecha_inicio, '%d-%m-%Y %I:%i:%s %p')) FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio) AS fecha_recepcion, 
               (SELECT Date_format(Max(td_entalmacen.fecha_fin), '%d-%m-%Y %I:%i:%s %p') FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio) AS fecha_fin_recepcion, 
               (SELECT Sum(td_aduana.cantidad) FROM td_aduana WHERE td_aduana.num_orden = th_entalmacen.id_ocompra) AS total_pedido,
               (SELECT Sum(c_articulo.peso * td_entalmacen.CantidadRecibida) FROM td_entalmacen, 
                c_articulo WHERE td_entalmacen.fol_folio = th_entalmacen.Fol_Folio AND td_entalmacen.cve_articulo = c_articulo.cve_articulo) AS peso_estimado, 
               th_entalmacen.status AS estado, 
               c_proveedores.nombre AS proveedor, 
               (SELECT Sum(td_entalmacen.cantidadrecibida) FROM td_entalmacen WHERE td_entalmacen.fol_folio = th_entalmacen.fol_folio) AS cantidad_recibida, 
               th_entalmacen.fact_prov AS facprov, 
               th_entalmacen.Cve_Almac AS almacen, 
               th_aduana.factura AS erp, 
               th_aduana.recurso AS recurso, 
               th_aduana.procedimiento AS procedimiento, 
               th_aduana.dictamen AS dictamen, 
               c_presupuestos.nombreDePresupuesto AS presupuesto, 
               th_aduana.condicionesdepago AS condicionesDePago, 
               th_aduana.lugardeentrega AS lugarDeEntrega, 
               Date_format(th_aduana.fechadefallo, '%d-%m-%Y') AS fechaDeFallo, 
               th_aduana.plazodeentrega AS plazoDeEntrega, 
               th_aduana.numerodeexpediente AS numeroDeExpediente, 
               th_aduana.areasolicitante AS areaSolicitante, 
               th_aduana.numsuficiencia AS numSuficiencia, 
               th_aduana.fechasuficiencia AS fechaSuficiencia, 
               th_aduana.fechacontrato AS fechaContrato, 
               th_aduana.montosuficiencia AS montoSuficiencia, 
               th_aduana.numerocontrato AS numeroContrato, 
               (SELECT ( Sum(td_entalmacen.CantidadRecibida * td_entalmacen.costoUnitario) ) FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio) AS importe 
               FROM th_entalmacen 
               LEFT JOIN c_usuario ON c_usuario.cve_usuario = th_entalmacen.cve_usuario 
               LEFT JOIN th_aduana ON th_aduana.num_pedimento = th_entalmacen.id_ocompra 
               LEFT JOIN td_aduana ON td_aduana.num_orden = th_entalmacen.id_ocompra
               LEFT JOIN c_proveedores ON th_entalmacen.cve_proveedor = c_proveedores.id_proveedor 
               LEFT JOIN td_entalmacen ON th_entalmacen.fol_folio = td_entalmacen.fol_folio 
               LEFT JOIN c_articulo ON td_aduana.cve_articulo = c_articulo.cve_articulo 
               LEFT JOIN th_entalmacen_log ON th_entalmacen_log.fol_folio = th_entalmacen.fol_folio 
               LEFT JOIN c_presupuestos ON th_aduana.presupuesto = c_presupuestos.id 
               {$filtro_clientes}
               GROUP BY folio_entradas, td_entalmacen.cantidadrecibida) A  
        WHERE almacen = '$almacen'
        {$aditionalSearch}
        GROUP BY folio_entradas
        ORDER BY folio_entradas DESC";
  
  

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

	$count = $res->num_rows;
*/
  
	$_page = 0;
	if (intval($page)>0) $_page = ($page-1)*$limit;

    //SELECT * FROM (
    $sql = 
     "SELECT DISTINCT th_entalmacen.fol_folio AS folio_entradas,
               IF(th_entalmacen.tipo = 'RL', '', th_entalmacen.id_ocompra) as id_ocompra,
               th_entalmacen.tipo AS tipo,
               td_entalmacen.tipo_entrada AS tipo_entrada, 
               c_usuario.nombre_completo AS usuario_activo, 
               IF(Date_format(th_aduana.fech_pedimento, '%d-%m-%Y %I:%i:%s %p') LIKE '%0000%', '', Date_format(th_aduana.fech_pedimento, '%d-%m-%Y %I:%i:%s %p')) AS fecha_entrega, 
               #IF((SELECT Min( Date_format(td_entalmacen.fecha_inicio, '%d-%m-%Y %I:%i:%s %p')) FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio) LIKE '%0000%', '', (SELECT Min( Date_format(td_entalmacen.fecha_inicio, '%d-%m-%Y %I:%i:%s %p')) FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio)) AS fecha_recepcion, 
               DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') AS fecha_recepcion,
               IF((SELECT Date_format(Max(td_entalmacen.fecha_fin), '%d-%m-%Y %I:%i:%s %p') FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio) LIKE '%0000%', '', (SELECT Date_format(Max(td_entalmacen.fecha_fin), '%d-%m-%Y %I:%i:%s %p') FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio)) AS fecha_fin_recepcion, 
               IFNULL(th_aduana.Pedimento, th_entalmacen.Pedimento_Well) as Pedimento,
               (SELECT Sum(td_aduana.cantidad) FROM td_aduana WHERE td_aduana.num_orden = th_entalmacen.id_ocompra) AS total_pedido,
               TRUNCATE((SELECT SUM(c_articulo.peso * td_entalmacen.CantidadRecibida) FROM td_entalmacen, 
                c_articulo WHERE td_entalmacen.fol_folio = th_entalmacen.Fol_Folio AND td_entalmacen.cve_articulo = c_articulo.cve_articulo), 2) AS peso_estimado,  
               th_entalmacen.status AS estado, 
               aduana.nombre AS empresa_proveedor, 
               c_proveedores.nombre as proveedor,
               TRUNCATE((SELECT SUM(td_entalmacen.cantidadrecibida) FROM td_entalmacen WHERE td_entalmacen.fol_folio = th_entalmacen.fol_folio), 4) AS cantidad_recibida, 
               th_entalmacen.fact_prov AS facprov, 
               th_entalmacen.Cve_Almac AS almacen, 
               th_aduana.factura AS erp, 
               IFNULL(th_aduana.recurso, th_entalmacen.Referencia_Well) AS recurso, 
               th_aduana.procedimiento AS procedimiento, 
               th_aduana.dictamen AS dictamen, 
               c_presupuestos.nombreDePresupuesto AS presupuesto, 
               th_aduana.condicionesdepago AS condicionesDePago, 
               th_aduana.lugardeentrega AS lugarDeEntrega, 
               IF(DATE_FORMAT(th_aduana.fechadefallo, '%d-%m-%Y') LIKE '%0000%', '', DATE_FORMAT(th_aduana.fechadefallo, '%d-%m-%Y')) AS fechaDeFallo, 
               th_aduana.plazodeentrega AS plazoDeEntrega, 
               th_aduana.Proyecto AS numeroDeExpediente, 
               th_aduana.areasolicitante AS areaSolicitante, 
               th_aduana.numsuficiencia AS numSuficiencia, 
               IF(th_aduana.fechasuficiencia LIKE '%0000%', '', th_aduana.fechasuficiencia) AS fechaSuficiencia, 
               IF(th_aduana.fechacontrato LIKE '%0000%', '', th_aduana.fechacontrato) AS fechaContrato, 
               th_aduana.montosuficiencia AS montoSuficiencia, 
               th_aduana.numerocontrato AS numeroContrato, 
               COUNT(td_entalmacen_enviaSAP.Id) AS envio_disponible,
               th_aduana.ID_Protocolo,
               IFNULL(th_entalmacen.Proyecto, '') as proyecto,
               CONCAT('$', TRUNCATE((SELECT ( SUM(td_entalmacen.CantidadRecibida * td_entalmacen.costoUnitario) ) FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio), 2)) AS importe
               FROM th_entalmacen 
               LEFT JOIN c_usuario ON c_usuario.cve_usuario = th_entalmacen.cve_usuario  OR c_usuario.id_user = th_entalmacen.cve_usuario 
               LEFT JOIN th_aduana ON th_aduana.num_pedimento = th_entalmacen.id_ocompra 
               #LEFT JOIN td_aduana ON td_aduana.num_orden = th_entalmacen.id_ocompra
               #LEFT JOIN c_proveedores ON th_aduana.procedimiento = c_proveedores.cve_proveedor 
               #LEFT JOIN c_proveedores ON c_proveedores.cve_proveedor = IF(IFNULL(th_aduana.procedimiento, '') != '', th_aduana.procedimiento, (SELECT Cve_Proveedor FROM c_proveedores WHERE ID_Proveedor = th_aduana.ID_Proveedor))

               #LEFT JOIN c_proveedores ON c_proveedores.cve_proveedor = IF(IFNULL(IFNULL(th_entalmacen.Proveedor, th_aduana.procedimiento), '') != '', IFNULL(th_entalmacen.Proveedor, th_aduana.procedimiento), th_entalmacen.Proveedor)
               LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = th_entalmacen.Cve_Proveedor

              #LEFT JOIN c_proveedores aduana ON th_aduana.ID_Proveedor = IFNULL(th_entalmacen.Cve_proveedor, aduana.id_proveedor)
               #LEFT JOIN c_proveedores aduana ON th_entalmacen.Cve_proveedor = aduana.id_proveedor
               #LEFT JOIN c_proveedores aduana ON IFNULL(th_aduana.ID_Proveedor, th_entalmacen.Proveedor) = IFNULL(aduana.id_proveedor, aduana.cve_proveedor)
               LEFT JOIN c_proveedores aduana ON IFNULL(th_aduana.ID_Proveedor, th_entalmacen.Proveedor) = IF(IFNULL(th_aduana.ID_Proveedor, '') = '', aduana.cve_proveedor, aduana.id_proveedor)
               LEFT JOIN td_entalmacen ON th_entalmacen.fol_folio = td_entalmacen.fol_folio 
               LEFT JOIN td_entalmacenxtarima ON td_entalmacenxtarima.Fol_Folio = th_entalmacen.fol_folio
               LEFT JOIN c_articulo ON td_entalmacen.cve_articulo = c_articulo.cve_articulo
               LEFT JOIN td_entalmacen_enviaSAP ON td_entalmacen_enviaSAP.Fol_Folio = td_entalmacen.fol_folio AND td_entalmacen_enviaSAP.Enviado = 0 AND td_entalmacen_enviaSAP.Cve_Articulo = c_articulo.cve_articulo
               #LEFT JOIN th_entalmacen_log ON th_entalmacen_log.fol_folio = th_entalmacen.fol_folio 
               LEFT JOIN c_presupuestos ON th_aduana.presupuesto = c_presupuestos.id 
               {$filtro_clientes} {$aditionalSearch}
               GROUP BY folio_entradas
            ORDER BY th_entalmacen.Fec_Entrada DESC
        ";
        //) A  WHERE almacen = '$almacen' {$aditionalSearch} GROUP BY folio_entradas        
      //echo var_dump($aditionalSearch);
    //echo var_dump($sql);
    //die();

    // hace una llamada previa al procedimiento almacenado Lis_Facturas


    $sqlCount = 
     "SELECT DISTINCT th_entalmacen.fol_folio AS folio_entradas
               FROM th_entalmacen 
               LEFT JOIN c_usuario ON c_usuario.cve_usuario = th_entalmacen.cve_usuario  OR c_usuario.id_user = th_entalmacen.cve_usuario 
               LEFT JOIN th_aduana ON th_aduana.num_pedimento = th_entalmacen.id_ocompra 
               #LEFT JOIN td_aduana ON td_aduana.num_orden = th_entalmacen.id_ocompra
               #LEFT JOIN c_proveedores ON th_aduana.procedimiento = c_proveedores.cve_proveedor 
               #LEFT JOIN c_proveedores ON c_proveedores.cve_proveedor = IF(IFNULL(th_aduana.procedimiento, '') != '', th_aduana.procedimiento, (SELECT Cve_Proveedor FROM c_proveedores WHERE ID_Proveedor = th_aduana.ID_Proveedor))

               #LEFT JOIN c_proveedores ON c_proveedores.cve_proveedor = IF(IFNULL(IFNULL(th_entalmacen.Proveedor, th_aduana.procedimiento), '') != '', IFNULL(th_entalmacen.Proveedor, th_aduana.procedimiento), th_entalmacen.Proveedor)
               LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = th_entalmacen.Cve_Proveedor

              #LEFT JOIN c_proveedores aduana ON th_aduana.ID_Proveedor = IFNULL(th_entalmacen.Cve_proveedor, aduana.id_proveedor)
               #LEFT JOIN c_proveedores aduana ON th_entalmacen.Cve_proveedor = aduana.id_proveedor
               LEFT JOIN c_proveedores aduana ON th_aduana.ID_Proveedor = aduana.id_proveedor
               LEFT JOIN td_entalmacen ON th_entalmacen.fol_folio = td_entalmacen.fol_folio 
               LEFT JOIN td_entalmacenxtarima ON td_entalmacenxtarima.Fol_Folio = th_entalmacen.fol_folio
               LEFT JOIN c_articulo ON td_entalmacen.cve_articulo = c_articulo.cve_articulo
               LEFT JOIN td_entalmacen_enviaSAP ON td_entalmacen_enviaSAP.Fol_Folio = td_entalmacen.fol_folio AND td_entalmacen_enviaSAP.Enviado = 0 AND td_entalmacen_enviaSAP.Cve_Articulo = c_articulo.cve_articulo
               #LEFT JOIN th_entalmacen_log ON th_entalmacen_log.fol_folio = th_entalmacen.fol_folio 
               LEFT JOIN c_presupuestos ON th_aduana.presupuesto = c_presupuestos.id 
               {$filtro_clientes} {$aditionalSearch}
               GROUP BY folio_entradas
            ORDER BY th_entalmacen.Fec_Entrada DESC
        ";

        if (!($res = mysqli_query($conn, $sqlCount))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        $count = mysqli_num_rows($res);
        //$res = \db()->prepare("SELECT COUNT(*) as count FROM (".$sql.") AS c");
        //$res->execute(array('filtro_clientes' => $filtro_clientes, 'aditionalSearch' => $aditionalSearch));
        //$count = $res->fetch()['count'];


    $res = ""; $sql2 = $sql;
    $sql2 .= " LIMIT $_page, $limit";
    if (!($res = mysqli_query($conn, $sql2))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    //$res = \db()->prepare($sql2);
    //$res->execute(array('filtro_clientes' => $filtro_clientes, 'aditionalSearch' => $aditionalSearch));
    

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res))
    //while ($row = $res->fetch())
    {
        
      if ($row['estado']=="A") $row['estado']='Editando';
      if ($row['estado']=="C") $row['estado']='Pendiente de Recibir';
      if ($row['estado']=="I") $row['estado']='Recibiendo';
      if ($row['estado']=="T") $row['estado']='Cerrada';
      if ($row['estado']=="P") $row['estado']='Recibiendo';
      if ($row['estado']=="E") $row['estado']='Cerrada';
      if ($row['estado']=="K") $row['estado']='Cancelado';
      if ($row['tipo_entrada'] == '1') $row['tipo_entrada']='Boton';
      else $row['tipo_entrada']='Validado';
        
      if($row['tipo']=="OC"){
		    $row['porcentaje_recibido']=number_format($row['cantidad_recibida']*100/$row['total_pedido'],2,',',' ');
        $row['total_pedido']=$row['total_pedido'];
        $row['fecha_entrega']=$row['fecha_entrega'];
      }
      if($row['tipo']=="RL"){
		    $row['porcentaje_recibido'] = 100;
        $row['total_pedido']=" - ";
        $row['fecha_entrega']=" - ";
      }
      if($row['tipo']=="CD"){
		    $row['porcentaje_recibido'] = 100;
        $row['total_pedido'] =" - ";
        $row['fecha_entrega']=" - ";
      }

		  if ($row['porcentaje_recibido']==false or $row['porcentaje_recibido']==INF ) $row['porcentaje_recibido']="0";
      $row=array_map('utf8_encode', $row);
		  $arr[] = $row;
		  $responce->rows[$i]['id']=$row['folio_entradas'];
      $responce->rows[$i]['cell']=array(
                                        $row[''],
                                        $row['tipo'],  
                                        $row['id_ocompra'],
                                        $row['erp'],
                                        $row['folio_entradas'],
                                        $row['facprov'], 
                                        $row['proyecto'], 
                                        $row['recurso'], 
                                        $row['Pedimento'], 
                                        $row['fecha_recepcion'],
                                        $row['total_pedido'],
                                        $row['cantidad_recibida'], 
                                        $row['peso_estimado'],
                                        $row['fecha_entrega'],
                                        $row['fecha_fin_recepcion'],
                                        $row['estado'],
                                        $row['usuario_activo'],
                                        intval($row['porcentaje_recibido']), 
                                        $row['tipo_entrada'], 
                                        $row['recurso'], 
                                        $row['procedimiento'], 
                                        $row['dictamen'], 
                                        $row['presupuesto'],
                                        $row['condicionesDePago'],
                                        $row['lugarDeEntrega'],
                                        $row['fechaDeFallo'],
                                        $row['plazoDeEntrega'],
                                        $row['numeroDeExpediente'],
                                        $row['importe'],
                                        $row['areaSolicitante'],
                                        $row['numSuficiencia'],
                                        $row['fechaSuficiencia'],
                                        $row['fechaContrato'],
                                        $row['montoSuficiencia'],
                                        $row['numeroContrato'],
                                        $row['empresa_proveedor'],
                                        $row['envio_disponible'],
                                        $row['proveedor'], 
                                        $row['ID_Protocolo']
                                    );
      $i++;
    }

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->sql = $sql;

  mysqli_close();
    //$records = ($responce->records); 
    //return ($records);
    echo json_encode($responce);
}
}

if(isset($_GET) && !empty($_GET)){
    $page = $_GET['page'];
    $rows = $_GET['rows'];
    $search = $_GET['search'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    /*Pedidos listos para embarcar*/
    $sql = "SELECT cve_almac, des_almac FROM c_almacen WHERE Activo = 1";

    if(!empty($search) && $search != '%20'){
        $sql.= " AND des_almac like '%".$search."%'";
    }

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    while($row = mysqli_fetch_array($res)){
        extract($row);
        $result [] = array(
            'clave' => $cve_almac,
            'descripcion' => $des_almac
        );
    };
    mysqli_close();
    header('Content-type: application/json');
    echo json_encode( $result);
}
/*
"
SELECT DISTINCT th_entalmacen.fol_folio AS folio_entradas,
               th_entalmacen.id_ocompra,
               th_entalmacen.tipo AS tipo,
               td_entalmacen.tipo_entrada AS tipo_entrada, 
               c_usuario.nombre_completo AS usuario_activo, 
               IF(DATE_FORMAT(th_aduana.fech_pedimento, '%d-%m-%Y %I:%i:%s %p') LIKE '%0000%', '', DATE_FORMAT(th_aduana.fech_pedimento, '%d-%m-%Y %I:%i:%s %p')) AS fecha_entrega, 
               DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') AS fecha_recepcion,
               #IF((SELECT Date_format(Max(td_entalmacen.fecha_fin), '%d-%m-%Y %I:%i:%s %p') FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio) LIKE '%0000%', '', (SELECT Date_format(Max(td_entalmacen.fecha_fin), '%d-%m-%Y %I:%i:%s %p') FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio)) AS fecha_fin_recepcion, 
               #(SELECT Sum(td_aduana.cantidad) FROM td_aduana WHERE td_aduana.num_orden = th_entalmacen.id_ocompra) AS total_pedido,
               #TRUNCATE((SELECT SUM(c_articulo.peso * td_entalmacen.CantidadRecibida) FROM td_entalmacen, c_articulo WHERE td_entalmacen.fol_folio = th_entalmacen.Fol_Folio AND td_entalmacen.cve_articulo = c_articulo.cve_articulo), 2) AS peso_estimado,  
               th_entalmacen.status AS estado, 
               c_proveedores.nombre AS empresa_proveedor, 
               aduana.nombre AS proveedor,
               #TRUNCATE((SELECT SUM(td_entalmacen.cantidadrecibida) FROM td_entalmacen WHERE td_entalmacen.fol_folio = th_entalmacen.fol_folio), 4) AS cantidad_recibida, 
               th_entalmacen.fact_prov AS facprov, 
               th_entalmacen.Cve_Almac AS almacen, 
               th_aduana.factura AS erp, 
               th_aduana.recurso AS recurso, 
               th_aduana.procedimiento AS procedimiento, 
               th_aduana.dictamen AS dictamen, 
               c_presupuestos.nombreDePresupuesto AS presupuesto, 
               th_aduana.condicionesdepago AS condicionesDePago, 
               th_aduana.lugardeentrega AS lugarDeEntrega, 
               IF(DATE_FORMAT(th_aduana.fechadefallo, '%d-%m-%Y') LIKE '%0000%', '', DATE_FORMAT(th_aduana.fechadefallo, '%d-%m-%Y')) AS fechaDeFallo, 
               th_aduana.plazodeentrega AS plazoDeEntrega, 
               th_aduana.numerodeexpediente AS numeroDeExpediente, 
               th_aduana.areasolicitante AS areaSolicitante, 
               th_aduana.numsuficiencia AS numSuficiencia, 
               IF(th_aduana.fechasuficiencia LIKE '%0000%', '', th_aduana.fechasuficiencia) AS fechaSuficiencia, 
               IF(th_aduana.fechacontrato LIKE '%0000%', '', th_aduana.fechacontrato) AS fechaContrato, 
               th_aduana.montosuficiencia AS montoSuficiencia, 
               th_aduana.numerocontrato AS numeroContrato, 
               #COUNT(td_entalmacen_enviaSAP.Id) AS envio_disponible,
               th_aduana.ID_Protocolo,
               #CONCAT('$', TRUNCATE((SELECT ( SUM(td_entalmacen.CantidadRecibida * td_entalmacen.costoUnitario) ) FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio), 2)) AS importe
               ''
               FROM th_entalmacen 
               LEFT JOIN c_usuario ON c_usuario.cve_usuario = th_entalmacen.cve_usuario  OR c_usuario.id_user = th_entalmacen.cve_usuario 
               LEFT JOIN th_aduana ON th_aduana.num_pedimento = th_entalmacen.id_ocompra 
               LEFT JOIN td_aduana ON td_aduana.num_orden = th_entalmacen.id_ocompra
               LEFT JOIN c_articulo ON td_aduana.cve_articulo = c_articulo.cve_articulo 
               LEFT JOIN c_proveedores ON th_entalmacen.cve_proveedor = c_proveedores.id_proveedor 
               LEFT JOIN c_proveedores aduana ON th_aduana.procedimiento = aduana.cve_proveedor 
               LEFT JOIN td_entalmacen ON th_entalmacen.fol_folio = td_entalmacen.fol_folio 
               LEFT JOIN td_entalmacen_enviaSAP ON td_entalmacen_enviaSAP.Fol_Folio = td_entalmacen.fol_folio AND td_entalmacen_enviaSAP.Enviado = 0
               #LEFT JOIN th_entalmacen_log ON th_entalmacen_log.fol_folio = th_entalmacen.fol_folio 
               LEFT JOIN c_presupuestos ON th_aduana.presupuesto = c_presupuestos.id 
               WHERE 1 AND th_entalmacen.Cve_Almac = 'AL01' AND th_entalmacen.Fec_Entrada >= STR_TO_DATE('03-01-2023', '%d-%m-%Y')
               GROUP BY folio_entradas
            ORDER BY th_entalmacen.Fec_Entrada DESC
"
*/