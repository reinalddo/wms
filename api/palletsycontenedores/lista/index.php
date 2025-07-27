<?php
include '../../../config.php';

error_reporting(0);

if( $_POST['action'] == 'traer_cant_ac' ){
		$page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $split = "";

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];
    $_lp = $_POST['lp'];
	  $almacen =$_POST['almacen'];
  

    if(isset($_POST['cliente']))
    {
				if(!empty($_POST['cliente']))
				{
						$split.="and c_cliente.Cve_Clte='".$_POST['cliente']."'";
				}
    }
    if(isset($_POST['tipo']))
    {
				if($_POST['tipo'] == "Pallet" || $_POST['tipo'] == "Contenedor")
				{
						$split.= "and c_charolas.tipo ='".$_POST['tipo']."'";
				}
    }
    if(isset($_POST['clave'])){
				if(!empty($_POST['clave']))
				{
						$split.= "and c_charolas.clave_contenedor='".$_POST['clave']."'";
				}
    }
    if(isset($_POST['bl'])){
				if(!empty($_POST['bl']))
				{
						if($_POST['bl'])
						$split.= " AND (IF(c_charolas.Pedido != '','Cliente',IF(V_ExistenciaGralProduccion.tipo = 'ubicacion',c_ubicacion.CodigoCSD,IF(V_ExistenciaGralProduccion.tipo = 'area','RTM','Libre')))) = '".$_POST['bl']."'";
				}
    }
    if(isset($_POST['fecha'])){
				if(!empty($_POST['fecha']))
				{
						if($_POST['fecha'])
						$split.= " ";
				}
    }
    if(isset($_POST['fecha-fin'])){
				if(!empty($_POST['fecha-fin']))
				{
						if($_POST['fecha-fin'])
						$split.= " ";
				}
    }

    if(isset($_POST['vacio']))
    {
        if($_POST['vacio'] != "0")
        {
        	 $statusol = $_POST['vacio'];
             $split.= "AND IF((V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor), '1','2') = $statusol ";
        }
    }
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
		if(isset($_POST['cliente']))
    {
				if(!empty($_POST['cliente']))
				{
						$split.="and c_cliente.Cve_Clte='".$_POST['cliente']."'";
				}
    }
    if(isset($_POST['tipo']))
    {
				if($_POST['tipo'] == "Pallet" || $_POST['tipo'] == "Contenedor")
				{
					$split.= "and c_charolas.tipo ='".$_POST['tipo']."'";
				}
    }
    if(isset($_POST['clave']))
		{
				if(!empty($_POST['clave']))
				{
					$split.= "and c_charolas.clave_contenedor='".$_POST['clave']."'";
				}
    }
    if(isset($_POST['bl']))
		{
				if(!empty($_POST['bl']))
				{
						if($_POST['bl'])
						$split.= " AND (IF(c_charolas.Pedido != '','Cliente',IF(V_ExistenciaGralProduccion.tipo = 'ubicacion',c_ubicacion.CodigoCSD,IF(V_ExistenciaGralProduccion.tipo = 'area','RTM','Libre')))) = '".$_POST['bl']."'";
				}
    }
    if(isset($_POST['fecha'])){
      if(!empty($_POST['fecha']))
      {
        if($_POST['fecha'])
        $split.= " ";
      }
    }
    if(isset($_POST['fecha-fin']))
		{
				if(!empty($_POST['fecha-fin']))
				{
						if($_POST['fecha-fin'])
						$split.= " ";
				}
    }
  /*
    $sql = " SELECT * from (
				SELECT
						c_charolas.IDContenedor,
						c_charolas.cve_almac,
						c_charolas.descripcion,
						c_charolas.tipo AS tipo,
						c_charolas.clave_contenedor AS clave,
						c_charolas.CveLP AS ClaveLP, 
						c_almacenp.nombre AS des_almac,
						IF((V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor), 'Ocupado',IF(c_cliente.Cve_Clte != '', '$prestamo', 'Libre')) as statu,
						c_charolas.Pedido AS pedido,
						c_cliente.RazonSocial AS razon,
						c_cliente.Cve_Clte AS cliente,
						CONCAT(c_cliente.CalleNumero,'-',c_cliente.Colonia) AS direcion,
						th_pedido.destinatario AS destino,
						DATE_FORMAT(th_pedido.Fec_Pedido,'%d-%m-%Y') AS fecha,
						TIMESTAMPDIFF(DAY, th_pedido.Fec_Pedido, curdate()) AS dias,
						if(c_charolas.Pedido != '','Cliente',if(V_ExistenciaGralProduccion.tipo = 'ubicacion',c_ubicacion.CodigoCSD,if(V_ExistenciaGralProduccion.tipo = 'area','RTM',IF(c_cliente.Cve_Clte != '', c_cliente.Cve_Clte, 'Libre')))) AS bl
				FROM c_charolas
					LEFT JOIN c_almacenp ON c_almacenp.id = c_charolas.cve_almac
					LEFT JOIN td_entalmacenxtarima ON td_entalmacenxtarima.ClaveEtiqueta = c_charolas.clave_contenedor
					LEFT JOIN t_tarima ON t_tarima.ntarima= c_charolas.IDContenedor AND t_tarima.Activo = 1
					LEFT JOIN th_pedido ON th_pedido.Fol_folio = t_tarima.Fol_Folio
					LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
					LEFT JOIN V_ExistenciaGralProduccion ON V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor AND V_ExistenciaGralProduccion.cve_almac = '$almacen' and V_ExistenciaGralProduccion.tipo = 'ubicacion' and c_charolas.Clave_Contenedor != '' 
					LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = V_ExistenciaGralProduccion.cve_ubicacion
				WHERE c_charolas.Activo = 1 ".$split."
				AND c_charolas.tipo != 'Caja' 
					AND c_almacenp.id='$almacen'
					and c_charolas.Clave_Contenedor != '' 
					".$condicion."
				GROUP BY c_charolas.IDContenedor	
				ORDER BY c_charolas.IDContenedor
				) as s where s.statu != ''";
  */

	$sql = "SELECT COUNT(*) total_tarimas from c_charolas where cve_almac = $almacen AND tipo = 'Pallet' AND Clave_Contenedor != '' AND Activo = 1 AND IFNULL(CveLP, '') != ''";
    if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
    $row = mysqli_fetch_array($res);
    $total_tarimas_pallet = $row["total_tarimas"];

	$sql = "SELECT COUNT(*) total_tarimas from c_charolas where cve_almac = $almacen AND tipo = 'Contenedor' AND Clave_Contenedor != '' AND Activo = 1 AND IFNULL(CveLP, '') != ''";
    if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
    $row = mysqli_fetch_array($res);
    $total_tarimas_contenedor = $row["total_tarimas"];

	$sql = "SELECT COUNT(DISTINCT v.Cve_Contenedor) tarimas_ocupadas 
			FROM V_ExistenciaGralProduccion v
			left join c_charolas ch on ch.Clave_Contenedor = v.Cve_Contenedor and ch.Clave_Contenedor != '' 
					and v.cve_almac = ch.cve_almac AND IFNULL(ch.CveLP, '') != '' 
			where v.cve_almac = $almacen and v.Cve_Contenedor != '' and ch.tipo = 'Pallet' AND ch.Activo = 1 and ch.Clave_Contenedor != ''";
    if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
    $row = mysqli_fetch_array($res);
    $tarimas_ocupadas_pallet = $row["tarimas_ocupadas"];

	$sql = "SELECT COUNT(DISTINCT v.Cve_Contenedor) tarimas_ocupadas 
			FROM V_ExistenciaGralProduccion v
			left join c_charolas ch on ch.Clave_Contenedor = v.Cve_Contenedor and ch.Clave_Contenedor != '' 
									   and v.cve_almac = ch.cve_almac AND IFNULL(ch.CveLP, '') != ''
			where v.cve_almac = $almacen and v.Cve_Contenedor != '' and ch.tipo = 'Contenedor' AND ch.Activo = 1 and ch.Clave_Contenedor != ''"; 
	if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
    $row = mysqli_fetch_array($res);
    $tarimas_ocupadas_contenedor = $row["tarimas_ocupadas"];

	$sql = "SELECT count(DISTINCT ch.Clave_Contenedor) as tarimas_a_clientes 
			from th_pedido th
			LEFT JOIN td_pedidoxtarima tdt ON tdt.Fol_folio = th.Fol_folio
			left join t_tarima t on th.Fol_folio = t.fol_folio
			left join c_charolas ch on ch.cve_almac = $almacen and IFNULL(t.ntarima, tdt.nTarima) = ch.IDContenedor 
										and ch.tipo = 'Pallet' AND IFNULL(ch.CveLP, '') != '' AND ch.TipoGen = '1'
			where th.cve_almac = $almacen AND ch.Activo = 1 and ch.Clave_Contenedor != '' and ch.Clave_Contenedor not in (select DISTINCT Cve_Contenedor FROM V_ExistenciaGralProduccion where cve_almac = $almacen and Cve_Contenedor != '')";
    if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
    $row = mysqli_fetch_array($res);
    $tarimas_consignacion_pallet = $row["tarimas_a_clientes"];

	$sql = "SELECT count(DISTINCT ch.Clave_Contenedor) as tarimas_a_clientes 
			from th_pedido th
			LEFT JOIN td_pedidoxtarima tdt ON tdt.Fol_folio = th.Fol_folio
			left join t_tarima t on th.Fol_folio = t.fol_folio
			left join c_charolas ch on ch.cve_almac = $almacen and IFNULL(t.ntarima, tdt.nTarima) = ch.IDContenedor and ch.tipo = 'Contenedor' AND IFNULL(ch.CveLP, '') != '' AND ch.TipoGen = '1'
			where th.cve_almac = $almacen AND ch.Activo = 1 and ch.Clave_Contenedor != '' and ch.Clave_Contenedor not in (select DISTINCT Cve_Contenedor FROM V_ExistenciaGralProduccion where cve_almac = $almacen and Cve_Contenedor != '')";
    if (!($res = mysqli_query($conn, $sql))){echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";}
    $row = mysqli_fetch_array($res);
    $tarimas_consignacion_contenedor = $row["tarimas_a_clientes"];



    $responce->total_tarimas_pallet = $total_tarimas_pallet;
	$responce->tarimas_ocupadas_pallet = $tarimas_ocupadas_pallet;
	$responce->tarimas_consignacion_pallet = $tarimas_consignacion_pallet;
	$responce->tarimas_libres_pallet = $total_tarimas_pallet-($tarimas_ocupadas_pallet+$tarimas_consignacion_pallet);

    $responce->total_tarimas_contenedor = $total_tarimas_contenedor;
	$responce->tarimas_ocupadas_contenedor = $tarimas_ocupadas_contenedor;
	$responce->tarimas_consignacion_contenedor = $tarimas_consignacion_contenedor;
	$responce->tarimas_libres_contenedor = $total_tarimas_contenedor-($tarimas_ocupadas_contenedor+$tarimas_consignacion_contenedor);

		//$responce->sql = $sql;
	/*
    while ($row = mysqli_fetch_array($res)) 
		{
        $row = array_map('utf8_encode', $row);
        //if($row['pedido'] == '')
        //{
			if($row['tipo'] == 'Contenedor')
			{
					if($row['statu'] != 'Libre')
					{
							 $responce->almacen1++;
					}
					else
					{
							$responce->almacen2++;
					}
			}
			else
			{
					if($row['statu'] != 'Libre')
					{
							 $responce->almacen3++;
					}
					else
					{
							$responce->almacen4++;
					}
			}
          	
       // }
    }
    */
/*
    if($_POST['ruta'])
    {
    	$ruta = $_POST['ruta'];
    	$sql = "SELECT consig_pallets, consig_cont FROM t_ruta WHERE ID_Ruta = '{$ruta}' AND control_pallets_cont = 'S'";
	    $res = mysqli_query($conn, $sql);
	    $row = mysqli_fetch_array($res);
	    $consig_pallets = $row['consig_pallets'];
	    $consig_cont = $row['consig_cont'];
		$responce->consignacion1 = $consig_cont;
		$responce->consignacion2 = $consig_pallets;
    }
    else
*/
    if($_POST['ruta'])
    {
    	$sql = "SELECT SUM(consig_pallets) as consig_pallets, SUM(consig_cont) as consig_cont FROM t_ruta WHERE control_pallets_cont = 'S' AND cve_almacenp = '$almacen'";
	    $res = mysqli_query($conn, $sql);
	    $row = mysqli_fetch_array($res);
	    $consig_pallets = $row['consig_pallets'];
	    $consig_cont = $row['consig_cont'];
		$responce->consignacion1 = $consig_cont;
		$responce->consignacion2 = $consig_pallets;
    }

    echo json_encode($responce);
    return;
} 

if (isset($_POST) && empty($_POST['action'])) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $split = "";

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];
    $_lp = $_POST['lp'];
	  $almacen =$_POST['almacen'];
  

    if(isset($_POST['cliente']))
    {
				if(!empty($_POST['cliente']))
				{
						$split.="and c_cliente.Cve_Clte='".$_POST['cliente']."'";
				}
    }
    if(isset($_POST['tipo']))
    {
				if($_POST['tipo'] == "Pallet" || $_POST['tipo'] == "Contenedor")
				{
						$split.= "and c_charolas.tipo ='".$_POST['tipo']."'";
				}
    }
    if(isset($_POST['clave'])){
				if(!empty($_POST['clave']))
				{
						$split.= "and c_charolas.clave_contenedor='".$_POST['clave']."'";
				}
    }
    if(isset($_POST['bl'])){
				if(!empty($_POST['bl']))
				{
						if($_POST['bl'])
						$split.= " AND (IF(c_charolas.Pedido != '','Cliente',IF(V_ExistenciaGralProduccion.tipo = 'ubicacion',c_ubicacion.CodigoCSD,IF(V_ExistenciaGralProduccion.tipo = 'area','RTM','Libre')))) = '".$_POST['bl']."'";
				}
    }
    if(isset($_POST['fecha'])){
				if(!empty($_POST['fecha']))
				{
						if($_POST['fecha'])
						$split.= " ";
				}
    }
    if(isset($_POST['fecha-fin'])){
				if(!empty($_POST['fecha-fin']))
				{
						if($_POST['fecha-fin'])
						$split.= " ";
				}
    }

    $SQLStatu = '';
    if(isset($_POST['vacio']))
    {
        if($_POST['vacio'] != "0")
        {
        	 $statu = $_POST['vacio'];
             //$split.= "AND IF((V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor), '1','2') = $statusol ";
             $SQLStatu = " AND s.statu = '$statu' ";
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//$start = $limit*$page - $limit; // do not put $limit*($page - 1)

    $start = 0;
    
    if (intval($page)>0) $start = ($page-1)*$limit;

    if(!$sidx) $sidx =1;
    // se conecta a la base de datos
/*
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "
				SELECT count(*) AS cuenta
				FROM c_charolas
						LEFT JOIN c_almacenp ON c_almacenp.clave = c_charolas.cve_almac
						LEFT JOIN td_entalmacenxtarima ON td_entalmacenxtarima.ClaveEtiqueta = c_charolas.clave_contenedor
						LEFT JOIN th_pedido ON th_pedido.Fol_folio = c_charolas.Pedido
						LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
						LEFT JOIN V_ExistenciaGralProduccion ON V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor
						LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = V_ExistenciaGralProduccion.cve_ubicacion
				WHERE c_charolas.Activo = 1 ".$split."
				  AND c_almacenp.id='$almacen'";
			
    if (!($res = mysqli_query($conn, $sqlCount))) 
		{
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row["cuenta"];
    mysqli_close();
*/
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $_page = 0;
    if(intval($page)>0) $_page = ($page-1)*$limit;
    $condicion= '';
    if($_criterio != '')
		{
				$condicion .=" AND (c_charolas.clave_contenedor LIKE '%".$_criterio."%' AND c_charolas.descripcion LIKE '%".$_criterio."%') ";
		}
	
    if($_lp != '')
		{
				$condicion .=" AND (c_charolas.CveLP LIKE '%".$_lp."%') ";
		}
		//$prestamo = utf8_decode('Préstamo');

    $sql = "SELECT * from (
				SELECT
						c_charolas.IDContenedor,
						c_charolas.cve_almac,
						c_charolas.descripcion,
						c_charolas.tipo AS tipo,
						c_charolas.clave_contenedor AS clave,
						IF(c_charolas.TipoGen = 1, c_charolas.CveLP, '') AS ClaveLP, 
						c_almacenp.nombre AS des_almac,
						IF((V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor), 'Ocupado',IF((IFNULL(c_cliente.Cve_Clte, '') != '' or t_tarima.fol_folio is not null) AND c_charolas.TipoGen = '1', 'Consignacion', 'Libre')) as statu,
						c_charolas.Pedido AS pedido,
						IF(c_charolas.TipoGen = '1', c_cliente.RazonSocial, '') AS razon,
						IF(c_charolas.TipoGen = '1', c_cliente.Cve_Clte, '') AS cliente,
						IF(c_charolas.TipoGen = '1', CONCAT(c_cliente.CalleNumero,'-',c_cliente.Colonia), '') AS direcion,
						IF(c_charolas.TipoGen = '1', th_pedido.destinatario, '') AS destino,
						IF(c_charolas.TipoGen = '1', DATE_FORMAT(th_pedido.Fec_Pedido,'%d-%m-%Y'), '') AS fecha,
						IF(c_charolas.TipoGen = '1', TIMESTAMPDIFF(DAY, th_pedido.Fec_Pedido, curdate()), '') AS dias,
						#if(c_charolas.Pedido != '','Cliente',if(V_ExistenciaGralProduccion.tipo = 'ubicacion',c_ubicacion.CodigoCSD,if(V_ExistenciaGralProduccion.tipo = 'area','RTM',IF(c_cliente.Cve_Clte != '', c_cliente.Cve_Clte, '')))) AS bl
						CASE 
						#WHEN c_charolas.IDContenedor IN (SELECT ntarima FROM t_tarima) THEN 'RTS' 
						WHEN c_charolas.Pedido != '' THEN 'Cliente'
						WHEN V_ExistenciaGralProduccion.tipo = 'ubicacion' THEN c_ubicacion.CodigoCSD
						WHEN V_ExistenciaGralProduccion.tipo = 'area' THEN 'RTM'
						#WHEN (SELECT COUNT(*) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = #tp.Fol_folio AND tp.status = 'C' AND tds.cve_almac = '$almacen' 
							# AND tds.Cve_articulo IN (SELECT v_ep.cve_articulo FROM V_ExistenciaGralProduccion v_ep)
							# ) > 0 
							#THEN 'RTS' 
						WHEN c_cliente.Cve_Clte != '' AND c_charolas.TipoGen = 1 THEN c_cliente.Cve_Clte ELSE ''
						END AS bl
				FROM c_charolas
					LEFT JOIN c_almacenp ON c_almacenp.id = c_charolas.cve_almac
					LEFT JOIN td_entalmacenxtarima ON td_entalmacenxtarima.ClaveEtiqueta = c_charolas.clave_contenedor
					LEFT JOIN t_tarima ON t_tarima.ntarima= c_charolas.IDContenedor AND t_tarima.Activo = 1
					LEFT JOIN th_pedido ON th_pedido.Fol_folio = t_tarima.Fol_Folio
					LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
					LEFT JOIN V_ExistenciaGralProduccion ON V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor AND V_ExistenciaGralProduccion.cve_almac = '$almacen' and c_charolas.Clave_Contenedor != ''
					LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = V_ExistenciaGralProduccion.cve_ubicacion
				WHERE c_charolas.Activo = 1 ".$split."
					AND c_almacenp.id='$almacen'
					AND c_charolas.cve_almac='$almacen'
					AND c_charolas.tipo != 'Caja' 
					AND IFNULL(c_charolas.CveLP, '') != ''
					".$condicion."
				GROUP BY c_charolas.IDContenedor	
				ORDER BY c_charolas.IDContenedor
				) as s where s.statu != '' 
    			{$SQLStatu} 
				";

    if(!($res = mysqli_query($conn, $sql))) 
	{
        echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);


    $sql .= " LIMIT $_page, $limit;";
    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if(!($res = mysqli_query($conn, $sql))) 
	{
        echo "Falló la preparación(3): (" . mysqli_error($conn) . ") ";
    }

    if($count >0) 
		{
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    }else 
		{
        $total_pages = 0;
    } 
		if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
	$responce->sql = $sql;


    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
		{
        $row = array_map('utf8_encode', $row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['IDContenedor'];
        $responce->rows[$i]['cell']=array(
            $row['IDContenedor'],
            $row[''],
            $row['clave'], 
            $row['descripcion'], 
            ($row['statu']=='Consignacion')?('Consignaci&oacute;n'):($row['statu']), 
            $row['tipo'],
            $row['ClaveLP'],
            $row['bl'],
            $row['cliente'],
            $row['razon'],
            $row['destino'],
            $row['direcion'],
            $row['fecha'],
            $row['dias'],
					  $row['des_almac']
        );
        $i++;
    }
    echo json_encode($responce);
}

///////////////////////////////////////**DETALLES**////////////////////////////////////////
if ($_POST['action'] == 'detalles')
{
		$clave = $_POST['clave'];
		$ubicacion = $_POST['ubicacion'];
		$cliente = $_POST['cliente'];
	//echo var_dump($clave);
		// se conecta a la base de datos
		$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

		if($cliente !="")
		{
				$sql = "
						SELECT COUNT(c_charolas.clave_contenedor) as total 
						FROM t_tarima
								LEFT JOIN c_charolas on c_charolas.IDContenedor = t_tarima.ntarima
						WHERE c_charolas.IDContenedor = t_tarima.ntarima
							AND c_charolas.clave_contenedor = '$clave'";

				if (!($res = mysqli_query($conn, $sql))) 
				{
					echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";
				}
				$row = mysqli_fetch_array($res);
				$count = $row['total'];
				$sql = "
						SELECT
								c_charolas.clave_contenedor as ClaveEtiqueta
						FROM t_tarima
								LEFT JOIN c_charolas on c_charolas.IDContenedor = t_tarima.ntarima
						WHERE c_charolas.IDContenedor = t_tarima.ntarima
							AND c_charolas.clave_contenedor = '$clave'";

				if (!($res = mysqli_query($conn, $sql))) 
				{
						echo "Falló la preparación(5): (" . mysqli_error($conn) . ") ";
				}

				$lineas = [];
				while($row = mysqli_fetch_array($res))
				{
						$lineas [] = $row['ClaveEtiqueta'];
				}

				$sql = "
						SELECT DISTINCT
								c_charolas.clave_contenedor AS clave,
								IF(c_charolas.CveLP != '',c_charolas.CveLP, '') AS pallet,
								t_tarima.cve_articulo AS articulo,
								c_articulo.des_articulo AS descripcion,
								IF(c_articulo.control_lotes ='S',t_tarima.lote,'')  AS lote,
								DATE_FORMAT(cl.Caducidad, '%d-%m-%Y') AS caducidad,
								IF(c_articulo.control_numero_series ='S',t_tarima.lote,'')  AS serie,
								t_tarima.cantidad AS existencia
						FROM t_tarima
								LEFT JOIN c_charolas ON c_charolas.IDContenedor = t_tarima.ntarima
								LEFT JOIN c_articulo ON c_articulo.cve_articulo = t_tarima.cve_articulo
								LEFT JOIN c_lotes cl ON IFNULL(cl.LOTE, '') = IFNULL(t_tarima.lote, '') AND cl.Activo=1 AND IFNULL(cl.LOTE, '') != ''
						WHERE c_charolas.clave_contenedor = '{$clave}'
				";
				// hace una llamada previa al procedimiento almacenado Lis_Facturas
				if (!($res = mysqli_query($conn, $sql))) 
				{
						echo "Falló la preparación(6): (" . mysqli_error($conn) . ") ";
				}
				$response = ['success' => true, 'data'=>[]];
		}
		else if($ubicacion != "RTM")
		{
				$sql = "
						SELECT COUNT(c_charolas.clave_contenedor) as total 
						FROM ts_existenciatarima
								LEFT JOIN c_charolas on c_charolas.IDContenedor = ts_existenciatarima.ntarima
						WHERE c_charolas.IDContenedor = ts_existenciatarima.ntarima
							AND c_charolas.clave_contenedor = '$clave'";

				if (!($res = mysqli_query($conn, $sql))) 
				{
					echo "Falló la preparación(4): (" . mysqli_error($conn) . ") ";
				}
				$row = mysqli_fetch_array($res);
				$count = $row['total'];
				$sql = "
						SELECT
								c_charolas.clave_contenedor as ClaveEtiqueta
						FROM ts_existenciatarima
								LEFT JOIN c_charolas on c_charolas.IDContenedor = ts_existenciatarima.ntarima
						WHERE c_charolas.IDContenedor = ts_existenciatarima.ntarima
							AND c_charolas.clave_contenedor = '$clave'";

				if (!($res = mysqli_query($conn, $sql))) 
				{
						echo "Falló la preparación(5): (" . mysqli_error($conn) . ") ";
				}

				$lineas = [];
				while($row = mysqli_fetch_array($res))
				{
						$lineas [] = $row['ClaveEtiqueta'];
				}

				$sql = "
						SELECT DISTINCT
								c_charolas.clave_contenedor as clave,
								IF(c_charolas.CveLP != '',c_charolas.CveLP, '') as pallet,
								ts_existenciatarima.cve_articulo  as articulo,
								c_articulo.des_articulo as descripcion,
								if(c_articulo.control_lotes ='S',ts_existenciatarima.lote,'')  as lote,
								DATE_FORMAT(cl.Caducidad, '%d-%m-%Y') AS caducidad,
    						if(c_articulo.control_numero_series ='S',ts_existenciatarima.lote,'')  as serie,
								ts_existenciatarima.existencia as existencia
						FROM ts_existenciatarima
								LEFT JOIN c_charolas on c_charolas.IDContenedor = ts_existenciatarima.ntarima
								LEFT JOIN c_articulo on c_articulo.cve_articulo = ts_existenciatarima.cve_articulo
								LEFT JOIN c_lotes cl ON IFNULL(cl.LOTE, '') = IFNULL(ts_existenciatarima.lote, '') AND cl.Activo=1 AND IFNULL(cl.LOTE, '') != ''
						WHERE c_charolas.clave_contenedor = '{$clave}'
				";
				// hace una llamada previa al procedimiento almacenado Lis_Facturas
				if (!($res = mysqli_query($conn, $sql))) 
				{
						echo "Falló la preparación(6): (" . mysqli_error($conn) . ") ";
				}
				$response = ['success' => true, 'data'=>[]];
		}
		else //if($ubicacion == "RTM")
		{
				$sql = "
						SELECT COUNT(c_charolas.clave_contenedor) as total 
						FROM td_entalmacenxtarima
								LEFT JOIN c_charolas on c_charolas.clave_contenedor = td_entalmacenxtarima.ClaveEtiqueta
						WHERE c_charolas.clave_contenedor = '$clave'";

			if (!($res = mysqli_query($conn, $sql))) 
			{
				echo "Falló la preparación(7): (" . mysqli_error($conn) . ") ";
			}
			$row = mysqli_fetch_array($res);
			$count = $row['total'];
			$sql = "
					SELECT
							c_charolas.clave_contenedor as ClaveEtiqueta
					FROM td_entalmacenxtarima
							LEFT JOIN c_charolas on c_charolas.clave_contenedor = td_entalmacenxtarima.ClaveEtiqueta
					WHERE c_charolas.clave_contenedor = '$clave'";

			if (!($res = mysqli_query($conn, $sql))) 
			{
					echo "Falló la preparación(8): (" . mysqli_error($conn) . ") ";
			}

			$lineas = [];
			while($row = mysqli_fetch_array($res))
			{
					$lineas [] = $row['ClaveEtiqueta'];
			}

			$sql = "
					SELECT DISTINCT 
							c_charolas.clave_contenedor as clave,
							IF(c_charolas.CveLP != '',c_charolas.CveLP, '') as pallet,
							td_entalmacenxtarima.cve_articulo  as articulo,
							c_articulo.des_articulo as descripcion,
							if(c_articulo.control_lotes ='S',td_entalmacenxtarima.cve_lote,'')  as lote,
							DATE_FORMAT(cl.Caducidad, '%d-%m-%Y') AS caducidad,
    					if(c_articulo.control_numero_series ='S',td_entalmacenxtarima.cve_lote,'')  as serie,
							td_entalmacenxtarima.Cantidad as existencia
					FROM td_entalmacenxtarima
							LEFT JOIN c_charolas on c_charolas.clave_contenedor = td_entalmacenxtarima.ClaveEtiqueta
							LEFT JOIN c_articulo on c_articulo.cve_articulo = td_entalmacenxtarima.cve_articulo
							LEFT JOIN c_lotes cl ON IFNULL(cl.LOTE, '') = IFNULL(td_entalmacenxtarima.cve_lote, '') AND cl.Activo=1 AND IFNULL(cl.LOTE, '') != ''
					WHERE c_charolas.clave_contenedor = '{$clave}'
			";
			// hace una llamada previa al procedimiento almacenado Lis_Facturas
			
			
			if (!($res = mysqli_query($conn, $sql))) 
			{
					echo "Falló la preparación(9): (" . mysqli_error($conn) . ") ";
			}
			$response = ['success' => true, 'data'=>[]];
		}
	
		while ($row = mysqli_fetch_array($res)) 
		{
				$row = array_map("utf8_encode", $row );
				extract($row);
				$linea = array_search($clave, $lineas) + 1;
				$response['data'][] = array(
						'clave'        => $clave,
						'pallet'       => $pallet,
						'articulo'     => $articulo,
						'descripcion'  => $descripcion,
						'lote'         => $lote,
						'caducidad'    => $caducidad,
						'serie'        => $serie,
						'existencia'   => $existencia,
				);
		}
		echo json_encode($response);
}

if($_POST['action'] == 'devolver_pallets_contenedores')
{
	$id_ruta = $_POST['id_ruta'];
	$dev_pallets = $_POST['num_pallets'];
	$dev_contenedores = $_POST['num_contenedores'];
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

	$sql = "UPDATE t_ruta SET consig_pallets = consig_pallets-{$dev_pallets}, consig_cont = consig_cont-{$dev_contenedores} WHERE control_pallets_cont = 'S' AND ID_Ruta = {$id_ruta}";
	mysqli_query($conn, $sql);

	echo true;
}