<?php
include '../../../config.php';

error_reporting(0);

if( $_POST['action'] == 'traer_cant_ac' ){
		$page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $_criterio = $_POST['criterio'];
    $_lp = $_POST['lp'];
	  $almacen =$_POST['almacen'];
    $split = "";
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
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
  
  	$prestamo = utf8_decode('Préstamo');

	$sql = "
		SELECT * FROM (
		SELECT DISTINCT
			c_charolas.IDContenedor,
			c_charolas.tipo AS tipo,
			IF((V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor), 'Ocupado',IF(c_cliente.Cve_Clte != '', '$prestamo', 'Libre')) AS statu
		FROM c_charolas
			LEFT JOIN c_almacenp ON c_almacenp.clave = c_charolas.cve_almac
			LEFT JOIN td_entalmacenxtarima ON td_entalmacenxtarima.ClaveEtiqueta = c_charolas.clave_contenedor
			LEFT JOIN t_tarima ON t_tarima.ntarima= c_charolas.IDContenedor AND t_tarima.Activo = 1
			LEFT JOIN th_pedido ON th_pedido.Fol_folio = t_tarima.Fol_Folio
			LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
			LEFT JOIN V_ExistenciaGralProduccion ON V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor
			LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = V_ExistenciaGralProduccion.cve_ubicacion
		WHERE c_charolas.Activo = 1 
			".$split."
			AND c_almacenp.id = '$almacen'
		) AS p WHERE p.statu = '$prestamo' AND p.tipo = 'Pallet'
	";

    if (!($res1 = mysqli_query($conn, $sql)))
		{
        echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";
    }

	$sql = "
		SELECT 
			tipo,
			statu
		FROM c_palletsdevueltos WHERE tipo = 'Pallet' AND cve_almac = (SELECT cve_almac FROM c_almacenp WHERE id='$almacen')
	";

    if (!($res2 = mysqli_query($conn, $sql)))
		{
        echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";
    }


	$sql = "
		SELECT * FROM (
		SELECT DISTINCT
			c_charolas.IDContenedor,
			c_charolas.tipo AS tipo,
			IF((V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor), 'Ocupado',IF(c_cliente.Cve_Clte != '', '$prestamo', 'Libre')) AS statu
		FROM c_charolas
			LEFT JOIN c_almacenp ON c_almacenp.clave = c_charolas.cve_almac
			LEFT JOIN td_entalmacenxtarima ON td_entalmacenxtarima.ClaveEtiqueta = c_charolas.clave_contenedor
			LEFT JOIN t_tarima ON t_tarima.ntarima= c_charolas.IDContenedor AND t_tarima.Activo = 1
			LEFT JOIN th_pedido ON th_pedido.Fol_folio = t_tarima.Fol_Folio
			LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
			LEFT JOIN V_ExistenciaGralProduccion ON V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor
			LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = V_ExistenciaGralProduccion.cve_ubicacion
		WHERE c_charolas.Activo = 1 
			".$split."
			AND c_almacenp.id = '$almacen'
		) AS p WHERE p.statu = '$prestamo' AND p.tipo = 'Contenedor'
	";

    if (!($res3 = mysqli_query($conn, $sql)))
		{
        echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";
    }

	$sql = "
		SELECT 
			tipo,
			statu
		FROM c_palletsdevueltos WHERE tipo = 'Contenedor' AND cve_almac = (SELECT cve_almac FROM c_almacenp WHERE id='$almacen')
	";

    if (!($res4 = mysqli_query($conn, $sql)))
		{
        echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";
    }

    $responce->con_pallet = mysqli_num_rows($res1);
	$responce->dev_pallet = mysqli_num_rows($res2);
	$responce->con_contenedor = mysqli_num_rows($res3);
	$responce->dev_contenedor = mysqli_num_rows($res4);

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

	$prestamo = utf8_decode('Préstamo');

    if(isset($_POST['vacio']))
    {
        if($_POST['vacio'] != "0")
        {
        	 $statusol = $_POST['vacio'];
             $split.= "AND IF((V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor), '1','2') = $statusol ";
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$start = $limit*$page - $limit; // do not put $limit*($page - 1)
    if(!$sidx) $sidx =1;
    // se conecta a la base de datos

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

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
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

    $sql = "
    		SELECT * FROM (
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
						DATE_FORMAT(th_subpedido.HFE,'%d-%m-%Y') AS fecha,
						'' as fechadev,
						TIMESTAMPDIFF(DAY, th_subpedido.HFE, curdate()) AS dias,
						if(c_charolas.Pedido != '','Cliente',if(V_ExistenciaGralProduccion.tipo = 'ubicacion',c_ubicacion.CodigoCSD,if(V_ExistenciaGralProduccion.tipo = 'area','RTM',IF(c_cliente.Cve_Clte != '', c_cliente.Cve_Clte, 'Libre')))) AS bl
				FROM c_charolas
					LEFT JOIN c_almacenp ON c_almacenp.clave = c_charolas.cve_almac
					LEFT JOIN td_entalmacenxtarima ON td_entalmacenxtarima.ClaveEtiqueta = c_charolas.clave_contenedor
					LEFT JOIN t_tarima ON t_tarima.ntarima= c_charolas.IDContenedor AND t_tarima.Activo = 1
					LEFT JOIN th_pedido ON th_pedido.Fol_folio = t_tarima.Fol_Folio
					LEFT JOIN th_subpedido ON th_subpedido.Fol_folio = th_pedido.Fol_folio
					LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
					LEFT JOIN V_ExistenciaGralProduccion ON V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor
					LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = V_ExistenciaGralProduccion.cve_ubicacion
				WHERE c_charolas.Activo = 1 ".$split."
					AND c_almacenp.id='$almacen'
					".$condicion."
				GROUP BY c_charolas.IDContenedor	
				ORDER BY c_charolas.IDContenedor
			) AS p WHERE p.statu = '$prestamo'
			UNION 

			SELECT 
			ID,
			cve_almac,
			descripcion,
			tipo,
			clave,
			ClaveLP, 
			desc_almac,
			statu,
			pedido,
			razon,
			cliente,
			direccion,
			destino,
			fecha,
			DATE_FORMAT(fechadev,'%d-%m-%Y') AS fechadev,
			dias,
			bl
			FROM c_palletsdevueltos
				LIMIT $_page, $limit;";

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
            $row['statu'], 
            $row['tipo'],
            $row['bl'],
            $row['fecha'],
            $row['fechadev'],
            $row['dias'],
        );
        $i++;
    }
    echo json_encode($responce);
}

if ($_POST['action'] == 'traer_cliente_pallet')
{
	$almacen = $_POST['almacen'];
	$clave = $_POST['clave'];

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT
    	c_charolas.tipo AS tipo,
    	DATE_FORMAT(th_pedido.Fec_Pedido,'%d-%m-%Y') AS fecha,
		c_cliente.Cve_Clte AS cliente,
		c_cliente.RazonSocial AS razon
	FROM c_charolas
		LEFT JOIN c_almacenp ON c_almacenp.clave = c_charolas.cve_almac
		LEFT JOIN td_entalmacenxtarima ON td_entalmacenxtarima.ClaveEtiqueta = c_charolas.clave_contenedor
		LEFT JOIN t_tarima ON t_tarima.ntarima= c_charolas.IDContenedor
		LEFT JOIN th_pedido ON th_pedido.Fol_folio = t_tarima.Fol_Folio
		LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
		LEFT JOIN V_ExistenciaGralProduccion ON V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor
		LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = V_ExistenciaGralProduccion.cve_ubicacion
	WHERE c_charolas.Activo = 1 
	AND c_almacenp.id='$almacen'
	AND c_cliente.Cve_Clte != ''
	AND c_charolas.clave_contenedor = '$clave'
	GROUP BY c_charolas.IDContenedor	
	";

	$res = mysqli_query($conn, $sql);
	$cliente = mysqli_fetch_array($res);

	$success = true;
	if(mysqli_num_rows($res) == 0)
	{
		$cliente = "";
		$success = false;
	}
    $array = [
    	"success" => $success,
        "cliente"=>$cliente
    ];

    echo json_encode($array);
}

if ($_POST['action'] == 'fecha-actual')
{
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT DATE_FORMAT(CURDATE(), '%d-%m-%Y') AS actual FROM DUAL";

	$res = mysqli_query($conn, $sql);
	$fecha_actual = mysqli_fetch_array($res);

    $array = [
        "fecha_actual"=>$fecha_actual
    ];

    echo json_encode($array);
}

if ($_POST['action'] == 'registrar-devolucion')
{
	$almacen = $_POST['almacen'];
	$clave = $_POST['pallet'];

	//$prestamo = utf8_decode('Préstamo');
	//$devolucion = utf8_decode('Devolución');

	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


    $sql = "SELECT
    	c_charolas.IDContenedor,
		c_charolas.cve_almac,
		c_charolas.descripcion,
		c_charolas.tipo AS tipo,
		c_charolas.clave_contenedor AS clave,
		c_charolas.CveLP AS ClaveLP, 
		c_almacenp.nombre AS desc_almac,
		'Devuelto' as statu,
		c_charolas.Pedido AS pedido,
		c_cliente.RazonSocial AS razon,
		c_cliente.Cve_Clte AS cliente,
		CONCAT(c_cliente.CalleNumero,'-',c_cliente.Colonia) AS direccion,
		th_pedido.destinatario AS destino,
		DATE_FORMAT(th_subpedido.HFE,'%d-%m-%Y') AS fecha,
		'' as fechadev,
		TIMESTAMPDIFF(DAY, th_subpedido.HFE, curdate()) AS dias,
		if(c_charolas.Pedido != '','Cliente',if(V_ExistenciaGralProduccion.tipo = 'ubicacion',c_ubicacion.CodigoCSD,if(V_ExistenciaGralProduccion.tipo = 'area','RTM',IF(c_cliente.Cve_Clte != '', c_cliente.Cve_Clte, 'Libre')))) AS bl
	FROM c_charolas
		LEFT JOIN c_almacenp ON c_almacenp.clave = c_charolas.cve_almac
		LEFT JOIN td_entalmacenxtarima ON td_entalmacenxtarima.ClaveEtiqueta = c_charolas.clave_contenedor
		LEFT JOIN t_tarima ON t_tarima.ntarima= c_charolas.IDContenedor AND t_tarima.Activo = 1
		LEFT JOIN th_pedido ON th_pedido.Fol_folio = t_tarima.Fol_Folio
		LEFT JOIN th_subpedido ON th_subpedido.Fol_folio = th_pedido.Fol_folio
		LEFT JOIN c_cliente ON c_cliente.Cve_Clte = th_pedido.Cve_clte
		LEFT JOIN V_ExistenciaGralProduccion ON V_ExistenciaGralProduccion.Cve_Contenedor = c_charolas.clave_contenedor
		LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = V_ExistenciaGralProduccion.cve_ubicacion
	WHERE c_charolas.Activo = 1 
	AND c_almacenp.id='$almacen'
	AND c_cliente.Cve_Clte != ''
	AND c_charolas.clave_contenedor = '$clave'
	GROUP BY c_charolas.IDContenedor";

	$res = mysqli_query($conn, $sql);
	$data = mysqli_fetch_array($res);

	$IDContenedor = utf8_decode($data['IDContenedor']);
	$cve_almac = utf8_decode($data['cve_almac']);
	$descripcion = utf8_decode($data['descripcion']);
	$tipo = utf8_decode($data['tipo']);
	$clave = utf8_decode($data['clave']);
	$ClaveLP = utf8_decode($data['ClaveLP']);
	$desc_almac = utf8_decode($data['desc_almac']);
	$statu = utf8_decode($data['statu']);
	$pedido = utf8_decode($data['pedido']);
	$razon = utf8_decode($data['razon']);
	$cliente = utf8_decode($data['cliente']);
	$direccion = utf8_decode($data['direccion']);
	$destino = utf8_decode($data['destino']);
	$fecha = utf8_decode($data['fecha']);
	$dias = utf8_decode($data['dias']);
	$bl = utf8_decode($data['bl']);

	$sql = "INSERT INTO c_palletsdevueltos(cve_almac, descripcion, tipo, clave, ClaveLP, desc_almac, statu, pedido, razon, cliente, direccion, destino, fecha, fechadev, dias, bl) VALUES ('$cve_almac', '$descripcion', '$tipo', '$clave', '$ClaveLP', '$desc_almac', '$statu', '$pedido', '$razon', '$cliente', '$direccion', '$destino', '$fecha', NOW(),'$dias', '$bl')";
	$res = mysqli_query($conn, $sql);

	$sql = "UPDATE t_tarima SET Activo = 0 WHERE ntarima = $IDContenedor";
	$res = mysqli_query($conn, $sql);

    $array = [
        "success"=>true
    ];

    echo json_encode($array);
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
								LEFT JOIN c_lotes cl ON cl.LOTE = t_tarima.lote AND cl.Activo=1
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
								LEFT JOIN c_lotes cl ON cl.LOTE = ts_existenciatarima.lote AND cl.Activo=1
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
					SELECT
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
							LEFT JOIN c_lotes cl ON cl.LOTE = td_entalmacenxtarima.cve_lote AND cl.Activo=1
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