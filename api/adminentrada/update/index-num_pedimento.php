<?php
include '../../../app/load.php';

error_reporting(0);
// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \AdminEntrada\AdminEntrada();

if( $_POST['action'] == 'add' ) 
{
  $ga->calcularCostoPromedio($_POST);
	if ($_POST["tipo"]=="OC")
  {
    $resp = $ga->save($_POST);
    $ga->terminada($_POST["Fol_Folio"]);
    if($ga->isTerminado($_POST["Fol_Folio"])==true)
    {
      $ga->terminada($_POST["Fol_Folio"]);
      //$_POST["STATUS"]="E";
    }
  }
  $arr = array(
    "success" => true,
    "err" => $resp
  );
	echo json_encode($arr);
}

if( $_POST['action'] == 'edit' ) 
{
  $ga->calcularCostoPromedio($_POST);
	if ($_POST["tipo"]=="OC" && $ga->isTerminado($_POST["Fol_Folio"])==true )
  {
    $_POST["STATUS"]="E";
    $ga->actualizar($_POST);
  }
  $arr = array(
    "success" => true,
    "err" => $resp
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'borrarArticulo' ) 
{
  $id = $_POST['id'];
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  $query = mysqli_query($conn, "SELECT cve_articulo, cve_lote, CantidadRecibida, cve_ubicacion FROM td_entalmacen WHERE id = '$id'");
  if($query->num_rows > 0)
  {
    $data = mysqli_fetch_assoc($query);
    extract($data);
    $acomodo = "UPDATE  t_pendienteacomodo 
                SET Cantidad = Cantidad - {$CantidadRecibida}
                WHERE cve_articulo = '{$cve_articulo}'
                AND cve_lote = '{$cve_lote}' 
                AND cve_ubicacion = '{$cve_ubicacion}';";
    $query = mysqli_query($conn, $acomodo);
  }
  $query = mysqli_query($conn, "DELETE FROM td_entalmacen WHERE id = '$id'");
  $arr = array(
      "success" => $query
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'isTerminado' ) 
{
  $arr = array(
    "success" => true,
    "resp" => $ga->isTerminado($_POST["Fol_Folio"])
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'exists' ) 
{
  $clave=$ga->exist($_POST["clave_almacen"]);
  if($clave==true)
  {
   $success = true;
  }
  else
  {
   $success= false;
  }
  $arr = array(
  "success"=>$success
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'delete' ) 
{
  $ga->borrarAlmacen($_POST);
  $ga->cve_almac = $_POST["cve_almac"];
  $ga->__get("cve_almac");
  $arr = array(
    "success" => true,
    //"nombre_proveedor" => $ga->data->Empresa,
    //"contacto" => $ga->data->VendId
  );
  echo json_encode($arr);
}


if ($_POST['action'] === 'loadResumen') 
{
		$page = $_POST['page']; // get the requested page
		$limit = $_POST['rows']; // get how many rows we want to have into the grid
		$sidx = $_POST['sidx']; // get index row - i.e. user click to sort
		$sord = $_POST['sord']; // get the direction
		$codigo = $_POST['codigo'];
		$oc = $_POST['oc'];
		$start = $limit*$page - $limit; // do not put $limit*($page - 1)

		if(!$sidx)
		{
			$sidx =1;
		}
		// se conecta a la base de datos
		$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	
		if($oc != '')
		{
				$sql = "SELECT COUNT(cve_articulo) as total from td_aduana where num_orden = '$oc'";
				if (!($res = mysqli_query($conn, $sql))) 
				{
					echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
				}

				$row = mysqli_fetch_array($res);
				$count = $row['total'];
				$sql = "
						select	
								th_entalmacen.Cve_Proveedor,
								c_proveedores.nombre,
								c_articulo.cve_articulo as articulo,
								c_articulo.des_articulo as descripcion,
								ifnull(td_entalmacen.CantidadPedida, 0) as cantidad_pedida,
								ifnull(td_entalmacen.CantidadRecibida, 0) as cantidad_recibida,
								ifnull( td_entalmacen.CantidadPedida - td_entalmacen.CantidadRecibida ,0) as cantidad_faltante
						from td_aduana
								left join td_entalmacen ON td_entalmacen.num_orden = td_aduana.num_orden and td_entalmacen.cve_articulo = td_aduana.cve_articulo
								left join c_articulo on c_articulo.cve_articulo = td_aduana.cve_articulo
								left join th_entalmacen on td_entalmacen.fol_folio = th_entalmacen.Fol_Folio
								left join c_proveedores on th_entalmacen.Cve_Proveedor = c_proveedores.ID_Proveedor
						where td_aduana.num_orden = '$oc'
						group by td_aduana.cve_articulo
						limit $start, $limit
				";
				// hace una llamada previa al procedimiento almacenado Lis_Facturas
				if (!($res = mysqli_query($conn, $sql))) 
				{
					echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
				}
		}
		else
		{
				$sql = "SELECT COUNT(cve_articulo) as total from td_entalmacen where fol_Folio = '$codigo'";
				if (!($res = mysqli_query($conn, $sql))) 
				{
					echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
				}

				$row = mysqli_fetch_array($res);
				$count = $row['total'];

				$sql = "SELECT DISTINCT cve_articulo from td_entalmacen WHERE fol_Folio = '$codigo'";
				if (!($res = mysqli_query($conn, $sql))) 
				{
					echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
				}
				$lineas = [];

				while($row = mysqli_fetch_array($res))
				{
					$lineas [] = $row['cve_articulo'];
				}

				$sql ="
						select
								th_entalmacen.Cve_Proveedor,
								c_articulo.cve_articulo as articulo,
								c_articulo.des_articulo as descripcion,
								ifnull(td_entalmacen.CantidadPedida, '') as cantidad_pedida,
								ifnull(td_entalmacen.CantidadRecibida, 0) as cantidad_recibida,
								'' as cantidad_faltante
						from td_entalmacen
								left join th_entalmacen on th_entalmacen.Fol_Folio = td_entalmacen.fol_folio
								left join c_articulo on c_articulo.cve_articulo = td_entalmacen.cve_articulo
								left join c_usuario on c_usuario.cve_usuario = td_entalmacen.cve_usuario
						where td_entalmacen.fol_folio = '$codigo'
						limit $start, $limit
				";

				// hace una llamada previa al procedimiento almacenado Lis_Facturas
				if (!($res = mysqli_query($conn, $sql))) 
				{
					echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
				}
		}
	
  if( $count >0 ) 
  {
    $total_pages = ceil($count/$limit);
  } 
  else 
  {
    $total_pages = 0;
  } 

  if ($page > $total_pages)
  {
    $page=$total_pages;
  }
  $responce->page = $page;
  $responce->total = $total_pages;
  $responce->records = $count;
	
  $arr = array();
  $i = 0;
  while ($row = mysqli_fetch_array($res)) 
  {
    $row = array_map("utf8_encode", $row );
    extract($row);
    $linea = $i +1;
    $arr[] = $row;
    $responce->rows[$i]['id']=$linea;
    $responce->rows[$i]['cell']=array(
                                      $linea, 
                                      $articulo, 
                                      $descripcion, 
                                      $cantidad_pedida, 
                                      $cantidad_recibida, 
                                      $cantidad_faltante
                                    );
    $i++;
  }
  echo json_encode($responce);
}

if ($_POST['action'] === 'loadDetalle') 
{
  $page = $_POST['page']; // get the requested page
  $limit = $_POST['rows']; // get how many rows we want to have into the grid
  $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
  $sord = $_POST['sord']; // get the direction
  $codigo = $_POST['codigo'];
  $start = $limit*$page - $limit; // do not put $limit*($page - 1)
  if(!$sidx)
  {
    $sidx =1;
  }

  // se conecta a la base de datos
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $sql = "SELECT COUNT(cve_articulo) as total from td_entalmacen where fol_Folio = '$codigo'";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }

  $row = mysqli_fetch_array($res);
  $count = $row['total'];

  $sql = "SELECT DISTINCT cve_articulo from td_entalmacen WHERE fol_Folio = '$codigo'";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }
  $lineas = [];

  while($row = mysqli_fetch_array($res))
  {
    $lineas [] = $row['cve_articulo'];
  }
/*
  $sql = 
  "
			select
					tde.cve_articulo as clave,
					a.des_articulo as descripcion,
					tda.cantidad as cantidad_pedida,
					tde.status as status,
					tde.CantidadRecibida as cantidad_recibida,
					date_format(fecha_inicio, '%d-%m-%Y') as fecha_recepcion,
					(SELECT MIN(date_format(fecha_inicio, '%d-%m-%Y %h:%i:%s %p')) from td_entalmacen where tde.fol_folio=td_entalmacen.fol_folio) as fecha_inicio,
					(SELECT MAX(date_format(fecha_fin, '%d-%m-%Y %h:%i:%s %p')) from td_entalmacen where tde.fol_folio=td_entalmacen.fol_folio) as fecha_fin,
					tda.cantidad - tde.CantidadRecibida as cantidad_faltante,
					tde.CantidadRecibida - tde.CantidadDisponible as cantidad_danada,
					u.cve_usuario as usuario
			from td_entalmacen tde
					left join td_aduana tda on tda.cve_articulo = tde.cve_articulo and tda.num_orden = tde.num_orden
					left join c_articulo a on a.cve_articulo = tde.cve_articulo
					left join c_usuario u on u.cve_usuario = tde.cve_usuario
			where tde.fol_folio = '$codigo'
			limit $start, $limit
  ";
*/
  /*
  $sql = "
      SELECT DISTINCT
        tde.cve_articulo AS clave,
        vec.Clave_Contenedor AS contenedor,
        CONCAT('LP', LPAD(CONCAT(cch.IDContenedor,tde.fol_folio), 9, 0)) AS LP,
        a.des_articulo AS descripcion,
        tde.CantidadPedida AS cantidad_pedida,
        tde.cve_lote AS lote,
        tde.numero_serie AS serie,
        DATE_FORMAT(cl.Caducidad, '%d-%m-%Y') AS caducidad,
        tde.status AS STATUS,
        tde.CantidadRecibida AS cantidad_recibida,
        DATE_FORMAT(fecha_inicio, '%d-%m-%Y') AS fecha_recepcion,
        DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y %h:%i:%s %p') AS fecha_inicio,
        DATE_FORMAT(tde.fecha_fin, '%d-%m-%Y %h:%i:%s %p') AS fecha_fin,
        tde.CantidadPedida - tde.CantidadRecibida AS cantidad_faltante,
        tde.CantidadRecibida - tde.CantidadDisponible AS cantidad_danada,
        u.cve_usuario AS usuario
      FROM td_entalmacen tde
        LEFT JOIN td_aduana tda ON tda.num_orden = tde.num_orden
        LEFT JOIN V_EntradasContenedores vec ON vec.Fol_Folio = tde.fol_folio
        LEFT JOIN c_charolas cch ON cch.clave_contenedor = vec.Clave_Contenedor
        LEFT JOIN c_lotes cl ON cl.LOTE = tde.cve_lote AND cl.Activo=1
        LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo AND cl.cve_articulo=a.cve_articulo
        LEFT JOIN c_usuario u ON u.cve_usuario = tde.cve_usuario
        LEFT JOIN td_entalmacenxtarima tdtar ON vec.Clave_Contenedor = tdtar.ClaveEtiqueta AND tde.fol_folio = tdtar.fol_folio
      WHERE tde.fol_folio = '$codigo'
      LIMIT $start, $limit
  ";
  */
  /*
  $sql = "
      SELECT DISTINCT
              tde.cve_articulo AS clave,
              tdtar.ClaveEtiqueta AS contenedor,
              CONCAT('LP', LPAD(CONCAT(cch.IDContenedor,tde.fol_folio), 9, 0)) AS LP,
              a.des_articulo AS descripcion,
              tde.CantidadPedida AS cantidad_pedida,
              tde.cve_lote AS lote,
              tde.numero_serie AS serie,
              DATE_FORMAT(cl.Caducidad, '%d-%m-%Y') AS caducidad,
              tde.status AS STATUS,
              tde.CantidadRecibida AS cantidad_recibida,
              DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y') AS fecha_recepcion,
              DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y %h:%i:%s %p') AS fecha_inicio,
              DATE_FORMAT(tde.fecha_fin, '%d-%m-%Y %h:%i:%s %p') AS fecha_fin,
              tde.CantidadPedida - tde.CantidadRecibida AS cantidad_faltante,
              tde.CantidadRecibida - tde.CantidadDisponible AS cantidad_danada,
              u.cve_usuario AS usuario
            FROM td_entalmacen tde
              LEFT JOIN td_entalmacenxtarima tdtar ON tdtar.fol_folio = tde.fol_folio
              LEFT JOIN c_charolas cch ON cch.clave_contenedor = tdtar.ClaveEtiqueta
              LEFT JOIN c_lotes cl ON cl.LOTE = tde.cve_lote AND cl.Activo=1
              LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo AND cl.cve_articulo=a.cve_articulo
              LEFT JOIN c_usuario u ON u.cve_usuario = tde.cve_usuario
      WHERE tde.fol_folio = '$codigo'
      LIMIT $start, $limit
  ";
  */
  $sql1 = "
      SELECT DISTINCT
            tde.cve_articulo AS clave,
            a.des_articulo AS descripcion,
            tde.CantidadPedida AS cantidad_pedida,
            tde.cve_lote AS lote,
            tde.numero_serie AS serie,
            DATE_FORMAT(cl.Caducidad, '%d-%m-%Y') AS caducidad,
            tde.status AS STATUS,
            tde.CantidadRecibida AS cantidad_recibida,
            DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y') AS fecha_recepcion,
            DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y %h:%i:%s %p') AS fecha_inicio,
            DATE_FORMAT(tde.fecha_fin, '%d-%m-%Y %h:%i:%s %p') AS fecha_fin,
            tde.CantidadPedida - tde.CantidadRecibida AS cantidad_faltante,
            tde.CantidadRecibida - tde.CantidadDisponible AS cantidad_danada,
            u.cve_usuario AS usuario
      FROM td_entalmacen tde
            LEFT JOIN c_lotes cl ON cl.LOTE = tde.cve_lote AND cl.Activo=1
            LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo AND cl.cve_articulo=a.cve_articulo
            LEFT JOIN c_usuario u ON u.cve_usuario = tde.cve_usuario
      WHERE tde.fol_folio = '$codigo'
      LIMIT $start, $limit
  ";

  $sql2 = "
      SELECT DISTINCT
            tdtar.ClaveEtiqueta AS contenedor,
            CONCAT('LP', LPAD(CONCAT(cch.IDContenedor,tde.fol_folio), 9, 0)) AS LP
      FROM td_entalmacen tde
            LEFT JOIN td_entalmacenxtarima tdtar ON tdtar.fol_folio = tde.fol_folio
            LEFT JOIN c_charolas cch ON cch.clave_contenedor = tdtar.ClaveEtiqueta
      WHERE tde.fol_folio = '$codigo'
      LIMIT $start, $limit
  ";

  // hace una llamada previa al procedimiento almacenado Lis_Facturas
  if (!($res = mysqli_query($conn, $sql1)) || !($res2 = mysqli_query($conn, $sql2))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }

  if( $count >0 ) 
  {
    $total_pages = ceil($count/$limit);
  } 
  else 
  {
    $total_pages = 0;
  } 

  if ($page > $total_pages)
  {
    $page=$total_pages;  
  }

  $responce->page = $page;
  $responce->total = $total_pages;
  $responce->records = $count;

//************************************************
// Proceso para unir los resultados de las tablas
//************************************************
$lp_reg = ""; $pallet_contenedor = ""; $pallets_diferentes = true;
if(mysqli_num_rows($res2) == 1)
{
  $row = mysqli_fetch_array($res2);
  $lp_reg = $row['LP'];
  $pallet_contenedor = $row['contenedor'];
  $pallets_diferentes = false;
}

//************************************************
  $arr = array();
  $i = 0;
  while ($row = mysqli_fetch_array($res)) 
  {
    $row = array_map("utf8_encode", $row );
    extract($row);
    //$linea = array_search($clave,$lineas) + 1;
    $linea = $i+1;
    $arr[] = $row;

    if($pallets_diferentes)
    {
        $row2 = mysqli_fetch_array($res2);
        $lp_reg = $row2['LP'];
        $pallet_contenedor = $row2['contenedor'];
    }

    $responce->rows[$i]['id']=$i;
    $responce->rows[$i]['cell']=array(
                                      $linea, 
                                      $pallet_contenedor,
                                      $lp_reg,
                                      $clave, 
                                      $lote,
                                      $caducidad,
                                      $serie,
                                      $descripcion,
                                      $cantidad_pedida,
                                      $cantidad_recibida,
                                      $cantidad_faltante,
                                      // $status, 
                                      $fecha_recepcion, 

                                      $fecha_inicio, 
                                      $fecha_fin, 

                                      $cantidad_danada, 
                                      $usuario
                                     );
    $i++;
  }
  echo json_encode($responce);
}

if ($_POST['action'] === 'loadBitacora') 
{
  $page = $_POST['page']; // get the requested page
  $limit = $_POST['rows']; // get how many rows we want to have into the grid
  $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
  $sord = $_POST['sord']; // get the direction

  $codigo = $_POST['codigo'];
  $start = $limit*$page - $limit; // do not put $limit*($page - 1)

  if(!$sidx) 
  {
    $sidx =1;
  }

  // se conecta a la base de datos
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $sql = "SELECT COUNT(Fol_Folio) as total from th_entalmacen_log where Fol_Folio = '$codigo'";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }

  $row = mysqli_fetch_array($res);
  $count = $row['total'];

  $sql = 
  "
  select
    date_format(the.fecha_inicio, '%d-%m-%Y %h:%i:%s %p') as fecha_inicio,
    date_format(the.fecha_fin, '%d-%m-%Y %h:%i:%s %p') as fecha_fin,
    u.cve_usuario as usuario,
    the.quehizo as quehizo
  from th_entalmacen_log the
    left join c_usuario u on u.cve_usuario = the.cve_usuario
  where the.Fol_Folio = '$codigo'
  order by fecha_inicio desc, fecha_fin desc
  limit $start, $limit
  ";

  // hace una llamada previa al procedimiento almacenado Lis_Facturas
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }

  if( $count >0 ) 
  {
    $total_pages = ceil($count/$limit);
  } 
  else 
  {
    $total_pages = 0;
  } 

  if ($page > $total_pages)
  {
    $page=$total_pages;
  }

  $responce->page = $page;
  $responce->total = $total_pages;
  $responce->records = $count;

  $arr = array();
  $i = 0;
  while ($row = mysqli_fetch_array($res)) 
  {
    $row = array_map("utf8_encode", $row );
    extract($row);
    $arr[] = $row;
    $responce->rows[$i]['id']=$i;
    $responce->rows[$i]['cell']=array($fecha_inicio, $fecha_fin, $usuario, $quehizo);
    $i++;
  }
  echo json_encode($responce);
}

if( $_POST['action'] == 'load2' ) 
{
  $data=$ga->load($_POST["codigo"]);
  $arr = array(
      "success" => true,
      "detalle" => $data
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'guardarUsuario' ) 
{
	$ga->borrarUsuarioAlmacen($_POST["cve_almac"]);
	$usuarios = $_POST["usuarios"][0];
  if(!empty($_POST["usuarios"]))
  {
    foreach($usuarios as $usuarioAlmacen)
    {
      $ga->saveUserAl($usuarioAlmacen,$_POST["cve_almac"]);
    }
  }
  $arr = array(
      "success" => $success,
      "err" => $resp
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'loadAlmacenes' ) 
{
  $almacenUser = $ga->loadAlmacenUser($_POST["cve_usuario"]);
  $current_almacen = array(
    "Current" =>array()
  );
  foreach ($almacenUser as $currentAlmacen)
  {
    $current_almacen['Current'][] = array (
        'id' => $currentAlmacen->cve_almac,
        'desc' =>$currentAlmacen->des_almac,
    );
  }
  $almacen_data = array();
  foreach($almacenUser as $almacen)
  {
    $almacen_data[]  = $almacen->cve_almac;
  }
  $model_almacen = new \Almacen\Almacen();
  $almacenes = $model_almacen->getAll();
  $store_data = array(
    "Almacenes" =>array()
  );
  foreach ($almacenes as $almacen)
  {
    if(!in_array($almacen->cve_almac,$almacen_data))
    {
      $store_data['Almacenes'][] = array (
          'id' => $almacen->cve_almac,
          'desc' =>$almacen->des_almac
      );
    }
  }
  $finalArray = array_merge($store_data,$current_almacen);
  echo json_encode($finalArray);
}

if( $_POST['action'] == 'guardarAlmacen' ) 
{
  $ga->borrarAlmacenUsuario($_POST["cve_usuario"]);
  $almacenes = $_POST["almacenes"][0];
  if(!empty($_POST["almacenes"]))
  {
    foreach($almacenes as $almacenUsuario)
    {
      $ga->saveUserAl($_POST["cve_usuario"],$almacenUsuario);
    }
  }
  $arr = array(
      "success" => $success,
      "err" => $resp
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'traerUsuariosDeAlmacen' ) 
{
  $userAlmacen = $ga->loadUserAlmacen($_POST["cve_almac"]);
  $users = $ga->loadUsers($_POST["cve_almac"]);
  $arr = array(
    "success" => true,
    "usuariosAlmacen" => $userAlmacen,
    "todosUsuarios" => $users
  );
  $arr = array_merge($arr);
  echo json_encode($arr);
}

if( $_POST['action'] == 'traerAlmacenesDeUsuario' ) 
{
  $almacenUsuario = $ga->loadAlmacenUser($_POST["cve_usuario"]);
  $almacenes = $ga->loadAlmacenes($_POST["cve_usuario"]);
	$arr = array(
    "success" => true,
    "almacenesUsuario" => $almacenUsuario,
    "todosAlmacenes" => $almacenes
  );
  $arr = array_merge($arr);
  echo json_encode($arr);
}

if( $_POST['action'] == 'reporte' ) 
{
  $almacenUsuario = $ga->reporte();
	$arr = array(
    "data" => $almacenUsuario,
  );
  $arr = array_merge($arr);
  echo json_encode($arr);
}


if( $_POST['action'] == 'loadAll' ) 
{
  $data       = $ga->load($_POST["codigo"]);
  $detalle    = $ga->loadDetalle2($_POST["codigo"]);
  if ($ga->data) 
  {
    $flag=true;
  } 
  else 
  {
    $flag=false;
  }
  $arr = array(
    "success" => $flag,
    "data" => $ga->data,
    "detalle"=>$detalle
  );
  echo json_encode($arr);
}


if( $_GET['action'] == 'terminado' ) 
{
  var_dump( $flag);
	exit;
}


if( $_POST['action'] == 'totalesPedido' ) 
{
  $data=$ga->getTotalPedido($_POST);
  $arr = array(
    "success" => true,
    "total_pedido" => $data["total_pedido"]
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'cambiarEstatus' ) 
{
  $ga->cambiarEstatus($_POST['Fol_Folio'],$_POST['STATUS'], $_POST['STATUSADUANA']);
  $arr = array(
    "success" => true
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'importarOC' ) 
{
   //echo var_dump("prueba");
}

if( $_POST['action'] == 'cargarMonto' ) 
{
  $presupuesto = $_POST['presupuesto'];
  $data = $ga->presupuestoAsignado($presupuesto);
  $data2 = $ga->importeTotalDeOrden($presupuesto);  
  $arr = array(
    "monto" => $data[0]["monto"],
    "importeTotal"=> $data2[0]["importeTotalDePresupuesto"],
    "success" => true
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'datosResumen' ) 
{
  $codigo = $_POST['codigo'];
  $data = $ga->datosResumen($codigo);
  $arr = array(
    "success" => true,
    "nombre_proveedor" => $data["nombre_proveedor"],
    "id_ocompra" => $data["id_ocompra"]
  );
  echo json_encode($arr);
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'exportExcelEntrada')
{
    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $folio = $_POST['folio'];
		$tipo = $_POST['tipo'];
    $title = "Reporte Entrada #{$folio}.xlsx";

    if($tipo == 1)
      {
					$titulo ="Orden de Compra";
					$datos_artculos ="
							td_aduana.ID_Aduana,
							td_aduana.cve_articulo,
							td_aduana.cantidad,
							td_aduana.cve_lote,
							td_aduana.num_orden,
							td_aduana.costo,
							td_aduana.costo*td_aduana.cantidad as subtotal,
					";
					$where = "WHERE th_entalmacen.fol_folio = ".$folio;
					$from = "th_aduana
							LEFT JOIN td_aduana on td_aduana.num_orden = th_aduana.num_pedimento";
					$join = "LEFT JOIN th_entalmacen on th_entalmacen.id_ocompra = th_aduana.num_pedimento
							LEFT JOIN c_articulo on td_aduana.cve_articulo = c_articulo.cve_articulo";
      }
      else
			{
					$titulo ="Recepcion libre";
					$datos_artculos ="																			
							'' as ID_Aduana,
							td_entalmacen.cve_articulo,
							td_entalmacen.CantidadRecibida as cantidad,
							td_entalmacen.cve_lote,
							td_entalmacen.num_orden,
							td_entalmacen.costoUnitario,
							td_entalmacen.costoUnitario*td_entalmacen.CantidadRecibida as subtotal,
					";
					$where = "where th_entalmacen.fol_folio = ".$folio;
					$from = "th_entalmacen";
					$join = "LEFT JOIN th_aduana on th_aduana.num_pedimento =  th_entalmacen.id_ocompra
							LEFT JOIN td_aduana on td_aduana.num_orden = th_aduana.num_pedimento
							LEFT JOIN td_entalmacen on td_entalmacen.fol_folio = th_entalmacen.Fol_Folio
							LEFT JOIN c_articulo on td_entalmacen.cve_articulo = c_articulo.cve_articulo";
      }
      
        $sql = "
            select 
                th_aduana.ID_Aduana,
                th_aduana.fech_pedimento,
								if(th_aduana.num_pedimento != '',th_aduana.num_pedimento,th_entalmacen.fol_folio) as num_pedimento												,
                th_aduana.factura,
                th_aduana.fech_llegPed,
                th_aduana.status,
                th_aduana.ID_Proveedor,
                th_aduana.ID_Protocolo,
                th_aduana.Consec_protocolo,
                th_aduana.cve_usuario,
                th_aduana.Cve_Almac,
                th_aduana.recurso,
                th_aduana.procedimiento,
                th_aduana.dictamen,
                th_aduana.presupuesto,
                th_aduana.condicionesDePago,
                th_aduana.lugarDeEntrega,
                th_aduana.fechaDeFallo,
                th_aduana.plazoDeEntrega,
                th_aduana.numeroDeExpediente,
                th_aduana.areaSolicitante,
                th_aduana.numSuficiencia,
                th_aduana.fechaSuficiencia,
                th_aduana.fechaContrato,
                th_aduana.montoSuficiencia,
                th_aduana.numeroContrato,

                c_presupuestos.id,
                c_presupuestos.nombreDePresupuesto,
                c_presupuestos.anoDePresupuesto,
                c_presupuestos.claveDePartida,
                c_presupuestos.conceptoDePartida,
                c_presupuestos.monto,

                {$datos_artculos}

                c_articulo.cve_articulo,
                c_articulo.des_articulo,
                c_articulo.tipo_producto,
                c_articulo.umas,
                
                c_proveedores.Nombre as proveedor,
                
                (select concat(c_compania.des_rfc,'<br>',c_compania.des_direcc, ' ',c_compania.des_cp) from c_compania where cve_cia = 1) as datos_facturacion,
                (select concat(Nombre,'<br>',RUT,'<br>',direccion, '<br>',colonia,' ',cve_dane,'<br>',ciudad,', ',estado,', ',pais ) from c_proveedores where ID_Proveedor = th_aduana.ID_Proveedor) datos_proveedor
            from {$from}
            {$join}
            LEFT JOIN c_presupuestos on th_aduana.presupuesto = c_presupuestos.id
            
            LEFT JOIN c_proveedores on c_proveedores.ID_Proveedor = th_aduana.ID_Proveedor
            {$where};
        ";
      
        $query = mysqli_query(\db2(), $sql);
        //$datos = mysqli_fetch_array($query, MYSQLI_ASSOC);
        $rows = array();
        while(($row = mysqli_fetch_array($query, MYSQLI_ASSOC))) 
        {
            $rows[] = $row;
            $datos = $row;
        }

				$cabezera = array($titulo." ".$datos["num_pedimento"]);
				$cuadro1 = array('Fecha','Partida Presupuestal','Suficiencia Presupuestal');
				$cuadro2 = array($datos["fechaSuficiencia"],$datos["claveDePartida"],$datos["montoSuficiencia"]);
				$cuadro3 = array('Condiciones de Pago','Fecha de Entrega');
				$cuadro4 = array($datos["condicionesDePago"],$datos["fech_llegPed"]);
				$cuadro5 = array('Proveedor','Lugar de Entrega','Area Solicitante');
				$cuadro6 = array($datos["datos_proveedor"],$datos["lugarDeEntrega"],$datos["areaSolicitante"]);
				$cuadro7 = array('PARTIDA','CONCEPTO','CANTIDAD','U.M.','P.U.','SUBTOTAL');
				$sumaSubtotales += $row["subtotal"];
				$ivaCalculado = $sumaSubtotales*0.16;
				$totalCalculado = $sumaSubtotales + $ivaCalculado;
				$xcifra = $totalCalculado;
				//$letras = $this->convertir($xcifra);
				$cuadro8 = array('SUBTOTAL','>I.V.A (16 %)','TOTAL','PRESUPUESTO');
				$totales = array(number_format($sumaSubtotales, 2), number_format($ivaCalculado, 2), number_format($totalCalculado, 2),$datos["nombreDePresupuesto"]);

				$excel = new XLSXWriter();
				$excel->writeSheetRow('Sheet1', $cabezera );
				$excel->writeSheetRow('Sheet1', $cuadro1 );
				$excel->writeSheetRow('Sheet1', $cuadro2 );
				$excel->writeSheetRow('Sheet1', $cuadro3 );
				$excel->writeSheetRow('Sheet1', $cuadro4 );
				$excel->writeSheetRow('Sheet1', $cuadro5 );
				$excel->writeSheetRow('Sheet1', $cuadro6 );
				$excel->writeSheetRow('Sheet1', $cuadro7 );
				foreach($rows as $row)
				{
						$row = array($row["cve_articulo"],$row["des_articulo"], number_format($row["cantidad"], 2),$row["umas"], $row["costo"],$row["subtotal"]);
						$excel->writeSheetRow('Sheet1', $row );
				}
				$excel->writeSheetRow('Sheet1', $cuadro8);
				$excel->writeSheetRow('Sheet1', $totales);
		
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="' . $title . '"');
				header('Cache-Control: max-age=0');
				$excel->writeToStdOut($title);
}