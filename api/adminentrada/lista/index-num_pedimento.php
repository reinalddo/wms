<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
	
	$search = $_POST['search'];
    $almacen = $_POST['almacen'];
    $filtro = $_POST['filtro'];
    $fechai= !empty($_POST['fechaInicio']) ? date('Y-m-d', strtotime($_POST['fechaInicio'])) : '';
    $fechaf= !empty($_POST['fechaFin']) ? date('Y-m-d', strtotime($_POST['fechaFin'])) : '';
    //$presupuesto = $_POST['presupuesto'];
    $aditionalSearch = '';

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
        elseif($filtro === "erp" ){
           // $aditionalSearch .= " AND factura LIKE '%$search%'";
        }

        $aditionalSearch .= " AND {$filtro} LIKE '%$search%'";
    }
    elseif(!empty($search)){
        //buscar por folio
        $aditionalSearch .= " AND numero_oc LIKE '%$search%'";
    }

    if(!empty($fechai) && !empty($fechaf)){
        if($fechai === $fechaf){
          $aditionalSearch .= " AND STR_TO_DATE(fecha_entrega, '%d-%m-%Y %I:%i:%s %p') like '%$fechai%'";
        }else{
          $aditionalSearch .= " AND STR_TO_DATE(fecha_entrega, '%d-%m-%Y %I:%i:%s %p') BETWEEN '$fechai' AND '$fechaf'";
        }
    }
    else{
        if(!empty($fechai)){
            //buscar por fecha mayor
            $aditionalSearch .= " AND STR_TO_DATE(fecha_entrega, '%d-%m-%Y %I:%i:%s %p') >= '$fechai'";
        }
        if(!empty($fechaf)){
            //buscar por fecha menor
            $aditionalSearch .= " AND STR_TO_DATE(fecha_entrega, '%d-%m-%Y %I:%i:%s %p') <= '$fechaf'";
        }
    }
  
    //$prep = "";
    /*if(!empty($presupuesto)){
      $aditionalSearch .= " and presupuesto='$presupuesto'";
    }*/

	$_page = 0;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = 
      /*"SELECT numero_oc AS total from (
	      SELECT th_entalmacen.Fol_Folio as numero_oc,
        th_entalmacen.tipo as tipo,
        c_usuario.nombre_completo as usuario_activo,
        DATE_FORMAT(th_aduana.fech_pedimento,'%d-%m-%Y %I:%i:%s %p') as fecha_entrega,
        (SELECT MIN(DATE_FORMAT(td_entalmacen.fecha_inicio,'%d-%m-%Y %I:%i:%s %p')) FROM td_entalmacen WHERE fol_folio = th_aduana.num_pedimento) as fecha_recepcion,
        (SELECT DATE_FORMAT(MAX(td_entalmacen.fecha_fin),'%d-%m-%Y %I:%i:%s %p') FROM td_entalmacen WHERE fol_folio = th_aduana.num_pedimento) as fecha_fin_recepcion,
        (select sum(td_aduana.cantidad) from td_aduana where td_aduana.num_orden=th_entalmacen.Fol_Folio) as total_pedido,
        (select sum(c_articulo.peso * td_aduana.cantidad) from td_aduana, c_articulo where td_aduana.num_orden=th_entalmacen.Fol_Folio and td_aduana.cve_articulo=c_articulo.cve_articulo) as peso_estimado,
        th_aduana.status as estado,
        c_proveedores.Nombre as proveedor,
        (select sum(td_entalmacen.CantidadRecibida) from td_entalmacen where td_entalmacen.fol_folio=th_entalmacen.Fol_Folio) as cantidad_recibida,
        th_entalmacen.Fact_Prov as facprov,
        th_entalmacen.Cve_Almac as almacen,
        th_aduana.factura as erp,
        th_aduana.presupuesto
            FROM th_entalmacen
          
        LEFT JOIN c_usuario on c_usuario.cve_usuario= th_entalmacen.Cve_Usuario
        LEFT JOIN th_aduana on th_aduana.num_pedimento= th_entalmacen.Fol_Folio
        LEFT JOIN td_aduana  on td_aduana.num_orden =th_entalmacen.Fol_Folio

        LEFT JOIN c_proveedores ON th_entalmacen.Cve_Proveedor= c_proveedores.ID_Proveedor
        LEFT JOIN td_entalmacen on th_entalmacen.Fol_Folio= td_entalmacen.fol_folio
        LEFT JOIN c_articulo on td_aduana.cve_articulo= c_articulo.cve_articulo
        LEFT JOIN th_entalmacen_log on th_entalmacen_log.Fol_Folio= th_entalmacen.Fol_Folio
        group by numero_oc, td_entalmacen.CantidadRecibida

        union all

    select th_aduana.num_pedimento as numero_oc,
        'OC' AS tipo,
        c_usuario.nombre_completo AS usuario_activo,
        DATE_FORMAT(th_aduana.fech_pedimento,'%d-%m-%Y %I:%i:%s %p') AS fecha_entrega,
        (SELECT MIN(DATE_FORMAT(td_entalmacen.fecha_inicio,'%d-%m-%Y %I:%i:%s %p')) FROM td_entalmacen WHERE fol_folio = th_aduana.num_pedimento) as fecha_recepcion,
        (SELECT DATE_FORMAT(MAX(td_entalmacen.fecha_fin),'%d-%m-%Y %I:%i:%s %p') FROM td_entalmacen WHERE fol_folio = th_aduana.num_pedimento) as fecha_fin_recepcion,
        sum(td_aduana.cantidad) AS total_pedido,
            sum(c_articulo.peso * td_aduana.cantidad) AS peso_estimado,
        th_aduana.status as estado,
        c_proveedores.Nombre AS proveedor,
        '0' as  cantidad_recibida,
        '' as facprov,
        th_aduana.Cve_Almac as almacen,
        th_aduana.factura as erp,
        th_aduana.presupuesto
        from th_aduana
    LEFT JOIN td_aduana ON td_aduana.num_orden = th_aduana.num_pedimento
    LEFT JOIN td_entalmacen ON td_entalmacen.fol_folio = th_aduana.num_pedimento
        LEFT JOIN c_usuario ON c_usuario.id_user = th_aduana.cve_usuario
        LEFT JOIN c_proveedores ON th_aduana.ID_Proveedor = c_proveedores.ID_Proveedor
    LEFT JOIN c_articulo ON td_aduana.cve_articulo=c_articulo.cve_articulo
    GROUP BY numero_oc
        )A
        WHERE almacen = '$almacen'
        {$aditionalSearch}
        GROUP BY numero_oc
        ORDER BY numero_oc DESC
        LIMIT $_page, $limit;";*/
      
        "SELECT * FROM (SELECT th_entalmacen.fol_folio AS numero_oc,
               th_entalmacen.tipo AS tipo,
               td_entalmacen.tipo_entrada AS tipo_entrada, 
               c_usuario.nombre_completo AS usuario_activo, 
               Date_format(th_aduana.fech_pedimento, '%d-%m-%Y %I:%i:%s %p') AS fecha_entrega, 
               (SELECT Min( Date_format(td_entalmacen.fecha_inicio, '%d-%m-%Y %I:%i:%s %p')) FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio) AS fecha_recepcion, 
               (SELECT Date_format(Max(td_entalmacen.fecha_fin), '%d-%m-%Y %I:%i:%s %p') FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio) AS fecha_fin_recepcion, 
               (SELECT Sum(td_aduana.cantidad) FROM td_aduana WHERE td_aduana.num_orden = th_entalmacen.fol_folio) AS total_pedido,
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
               c_presupuestos.nombredepresupuesto, 
               th_aduana.presupuesto AS presupuesto, 
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
               LEFT JOIN th_aduana ON th_aduana.num_pedimento = th_entalmacen.fol_folio 
               LEFT JOIN td_aduana ON td_aduana.num_orden = th_entalmacen.fol_folio 
               LEFT JOIN c_proveedores ON th_entalmacen.cve_proveedor = c_proveedores.id_proveedor 
               LEFT JOIN td_entalmacen ON th_entalmacen.fol_folio = td_entalmacen.fol_folio 
               LEFT JOIN c_articulo ON td_aduana.cve_articulo = c_articulo.cve_articulo 
               LEFT JOIN th_entalmacen_log ON th_entalmacen_log.fol_folio = th_entalmacen.fol_folio 
               LEFT JOIN c_presupuestos ON th_aduana.presupuesto = c_presupuestos.id 
               GROUP BY numero_oc, td_entalmacen.cantidadrecibida) A  
        WHERE almacen = '$almacen'
        {$aditionalSearch}
        GROUP BY numero_oc
        ORDER BY numero_oc DESC
        LIMIT $_page, $limit;";
  
  

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

	$count = $res->num_rows;

    
  
	$_page = 0;

	if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql = 
      /*"SELECT * FROM (
            SELECT th_entalmacen.Fol_Folio AS numero_oc,
                th_entalmacen.tipo AS tipo,
                td_entalmacen.tipo_entrada as tipo_entrada,
                c_usuario.nombre_completo AS usuario_activo,
                DATE_FORMAT(th_aduana.fech_pedimento,'%d-%m-%Y %I:%i:%s %p') AS fecha_entrega,
                (SELECT MIN(DATE_FORMAT(td_entalmacen.fecha_inicio,'%d-%m-%Y %I:%i:%s %p')) FROM td_entalmacen WHERE fol_folio = th_aduana.num_pedimento) as fecha_recepcion,
                (SELECT DATE_FORMAT(MAX(td_entalmacen.fecha_fin),'%d-%m-%Y %I:%i:%s %p') FROM td_entalmacen WHERE fol_folio = th_aduana.num_pedimento) as fecha_fin_recepcion,
                (SELECT sum(td_aduana.cantidad) FROM td_aduana WHERE td_aduana.num_orden = th_entalmacen.Fol_Folio) as total_pedido,
                (SELECT sum(c_articulo.peso * td_aduana.cantidad) FROM td_aduana, c_articulo where td_aduana.num_orden=th_entalmacen.Fol_Folio and td_aduana.cve_articulo=c_articulo.cve_articulo) as peso_estimado,
                th_entalmacen.status  as estado,
                
                c_proveedores.Nombre as proveedor,
                (SELECT sum(td_entalmacen.CantidadRecibida) FROM td_entalmacen WHERE td_entalmacen.fol_folio=th_entalmacen.Fol_Folio) as cantidad_recibida,
                th_entalmacen.Fact_Prov as facprov,
                th_aduana.Cve_Almac as almacen,
                th_aduana.factura as erp,
                th_aduana.recurso as recurso,
                th_aduana.procedimiento as procedimiento,
                th_aduana.dictamen as dictamen,
                c_presupuestos.nombreDePresupuesto,
                th_aduana.presupuesto as presupuesto,
                th_aduana.condicionesDePago as condicionesDePago,
                th_aduana.lugarDeEntrega as lugarDeEntrega,
                DATE_FORMAT(th_aduana.fechaDeFallo,'%d-%m-%Y') AS fechaDeFallo,
                th_aduana.plazoDeEntrega as plazoDeEntrega,
                th_aduana.numeroDeExpediente as numeroDeExpediente,
                th_aduana.areaSolicitante as areaSolicitante,
                th_aduana.numSuficiencia as numSuficiencia,
                th_aduana.fechaSuficiencia as fechaSuficiencia,
                th_aduana.fechaContrato as fechaContrato,
                th_aduana.montoSuficiencia as montoSuficiencia,
                th_aduana.numeroContrato as numeroContrato,
                
                (SELECT (SUM(td_aduana.cantidad*td_aduana.costo)) FROM td_aduana WHERE num_pedimento = td_aduana.num_orden) as importe
            FROM th_entalmacen
                LEFT JOIN c_usuario ON c_usuario.cve_usuario= th_entalmacen.Cve_Usuario
                LEFT JOIN th_aduana ON th_aduana.num_pedimento= th_entalmacen.Fol_Folio
                LEFT JOIN td_aduana  ON td_aduana.num_orden =th_entalmacen.Fol_Folio
                LEFT JOIN c_proveedores ON th_entalmacen.Cve_Proveedor= c_proveedores.ID_Proveedor
                LEFT JOIN td_entalmacen on th_entalmacen.Fol_Folio= td_entalmacen.fol_folio
                LEFT JOIN c_articulo ON td_aduana.cve_articulo = c_articulo.cve_articulo
                LEFT JOIN th_entalmacen_log ON th_entalmacen_log.Fol_Folio= th_entalmacen.Fol_Folio
                LEFT JOIN c_presupuestos ON th_aduana.presupuesto = c_presupuestos.id
                
                GROUP BY numero_oc,td_entalmacen.CantidadRecibida
        
            
        ) A
                WHERE almacen = '$almacen'
                {$aditionalSearch}
                GROUP BY numero_oc
                ORDER BY numero_oc DESC
                LIMIT $_page, $limit;";*/
      
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
               GROUP BY folio_entradas, td_entalmacen.cantidadrecibida) A  
        WHERE almacen = '$almacen'
        {$aditionalSearch}
        GROUP BY folio_entradas
        ORDER BY folio_entradas DESC
        LIMIT $_page, $limit;";
  
    //echo var_dump($aditionalSearch);
    //echo var_dump($sql);
    //die();

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
    while ($row = mysqli_fetch_array($res))
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
                                        $row['folio_entradas'],
                                        $row['proveedor'],
                                        $row['id_ocompra'],
                                        $row['erp'],
                                        $row['tipo'],  
                                        $row['total_pedido'],
                                        $row['cantidad_recibida'], 
                                        $row['peso_estimado'],
                                        $row['fecha_entrega'],
                                        $row['fecha_recepcion'],
                                        $row['fecha_fin_recepcion'],
                                        $row['estado'],
                                        $row['usuario_activo'],
                                        intval($row['porcentaje_recibido']), 
                                        $row['facprov'], 
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
                                        $row['numeroContrato']
                                       );
      $i++;
    }
    //$records = ($responce->records); 
    //return ($records);
    echo json_encode($responce);
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
