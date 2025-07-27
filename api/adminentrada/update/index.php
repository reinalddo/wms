<?php
include '../../../app/load.php';
use Framework\Http\Response;
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


if( $_POST['action'] == 'borrar_entrada' ) 
{
  $folio = $_POST['folio'];
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

/*
  $entrada = "SELECT al.id, td.cve_articulo, td.cve_lote, th.Cve_Proveedor, IFNULL(tt.Cantidad, td.CantidadUbicada) AS CantidadUbicada, 
                     IFNULL(tt.ClaveEtiqueta, '') AS clave_contenedor, IFNULL(ch.CveLP, '') AS CveLP, p.Nombre AS Proveedor, 
                     td.cve_ubicacion,
                     IFNULL(ch.IDContenedor, '') AS ntarima
              FROM td_entalmacen td
              LEFT JOIN th_entalmacen th ON th.Fol_Folio = td.fol_folio
              LEFT JOIN td_entalmacenxtarima tt ON tt.fol_folio = th.Fol_Folio AND td.cve_articulo = tt.cve_articulo AND IFNULL(td.cve_lote, '') = IFNULL(tt.cve_lote, '')
              LEFT JOIN c_charolas ch ON ch.clave_contenedor = tt.ClaveEtiqueta
              LEFT JOIN c_almacenp al ON al.clave = th.Cve_Almac
              LEFT JOIN c_proveedores p ON p.ID_Proveedor = th.Cve_Proveedor
              WHERE td.fol_folio = '{$folio}';";*/
  $entrada = "SELECT td.cve_almac AS id, td.cve_articulo, td.cve_lote, th.Cve_Proveedor, SUM(td.cantidad) AS CantidadUbicada, 
                     IFNULL(ch.clave_contenedor, '') AS clave_contenedor, IFNULL(ch.CveLP, '') AS CveLP, p.Nombre AS Proveedor, 
                     td.idy_ubica AS cve_ubicacion, IFNULL(ch.IDContenedor, '') AS ntarima
              FROM t_trazabilidad_existencias td
              LEFT JOIN th_entalmacen th ON th.Fol_Folio = td.folio_entrada
              LEFT JOIN c_charolas ch ON ch.IDContenedor = td.ntarima
              LEFT JOIN c_proveedores p ON p.ID_Proveedor = th.Cve_Proveedor
              WHERE td.folio_entrada = {$folio} 
              AND td.idy_ubica IS NOT NULL 
              GROUP BY Cve_Proveedor, clave_contenedor, cve_articulo, cve_lote, cve_ubicacion";
  $query_entrada = mysqli_query($conn, $entrada);

  $mensaje_no_borrar = ""; 
  $borrar_proveedor = "";
  while($row = mysqli_fetch_assoc($query_entrada))
  {
      $almacen = $row['id'];
      $cve_articulo = $row['cve_articulo'];
      $lote = $row['cve_lote'];
      $ID_Proveedor = $row['Cve_Proveedor'];
      $borrar_proveedor = $row['Cve_Proveedor'];
      $CantidadUbicada = $row['CantidadUbicada'];
      $clave_contenedor = $row['clave_contenedor'];
      $CveLP = $row['CveLP'];
      $ntarima = $row['ntarima'];
      $cve_ubicacion = $row['cve_ubicacion'];
      $Proveedor = $row['Proveedor'];
      //$entrada = "DELETE FROM ts_existenciapiezas WHERE cve_articulo='{$cve_articulo}' AND cve_lote = '{$lote}' AND cve_almac = '{$almacen}' AND ID_Proveedor = '{$ID_Proveedor}';";

      $pendienteacomodo = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad - {$CantidadUbicada} WHERE cve_articulo='{$cve_articulo}' AND cve_lote = '{$lote}' AND cve_ubicacion = '{$cve_ubicacion}' AND ID_Proveedor = '{$ID_Proveedor}';";
      $query = mysqli_query($conn, $pendienteacomodo);

      if($clave_contenedor == '')
      {
          $entrada = "UPDATE ts_existenciapiezas SET existencia = existencia - {$CantidadUbicada} WHERE cve_articulo='{$cve_articulo}' AND cve_lote = '{$lote}' AND cve_almac = '{$almacen}' AND ID_Proveedor = '{$ID_Proveedor}';";
          $query = mysqli_query($conn, $entrada);
      }
      else
      {
          $entrada = "UPDATE ts_existenciatarima SET existencia = existencia - {$CantidadUbicada} WHERE cve_articulo='{$cve_articulo}' AND lote = '{$lote}' AND cve_almac = '{$almacen}' AND ID_Proveedor = '{$ID_Proveedor}';";
          $query = mysqli_query($conn, $entrada);
      }

/*
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      // ESTA VALIDACION DE CONTAR EN ts_existenciapiezas ANTES DE BORRAR ES POR SI POR EJEMPLO EL ARTICULO EN CUESTION SE MOVIO EN 2 PARTES 
      // (EJEMPLO QUE SEA CANTIDAD 120 Y SE MOVIERON 60 A UNA UBICACION Y 60 A OTRA, ENTONCES QUIERE DECIR QUE SE ESTÁ USANDO QUIZÁS SE RESTÓ)
      // ENTONCES PUEDE HABER 150 EN ESA UBICACION Y LE RESTO LOS 120, PERO DEBE ESTAR COMPLETO
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

      if($clave_contenedor == '')
      {
        $sql_verificar_existencia = "SELECT COUNT(*) as conteo FROM ts_existenciapiezas WHERE cve_articulo='{$cve_articulo}' AND cve_lote = '{$lote}' AND cve_almac = '{$almacen}' AND ID_Proveedor = '{$ID_Proveedor}'";
        $query_verif = mysqli_query($conn, $sql_verificar_existencia);
        $row_verif = mysqli_fetch_assoc($query_verif);

        if($row_verif['conteo'] > 1)
        {
            $mensaje_no_borrar .= "\nArticulo: $cve_articulo - Lote: $lote - Proveedor: $Proveedor";
        }
        else
        {
          $entrada = "UPDATE ts_existenciapiezas SET existencia = existencia - {$CantidadUbicada} WHERE cve_articulo='{$cve_articulo}' AND cve_lote = '{$lote}' AND cve_almac = '{$almacen}' AND ID_Proveedor = '{$ID_Proveedor}';";
          $query = mysqli_query($conn, $entrada);
        }
      }
      else
      {
          $sql_verificar_existencia = "SELECT COUNT(*) as conteo FROM ts_existenciatarima WHERE cve_articulo='{$cve_articulo}' AND lote = '{$lote}' AND cve_almac = '{$almacen}' AND ID_Proveedor = '{$ID_Proveedor}' AND ntarima = {$ntarima}";
          $query_verif = mysqli_query($conn, $sql_verificar_existencia);
          $row_verif = mysqli_fetch_assoc($query_verif);

          if($row_verif['conteo'] > 1)
          {
              $mensaje_no_borrar .= "\nLP: $CveLP - Articulo: $cve_articulo - Lote: $lote - Proveedor: $Proveedor";
          }
          else
          {
            $entrada = "UPDATE ts_existenciatarima SET existencia = existencia - {$CantidadUbicada} WHERE cve_articulo='{$cve_articulo}' AND lote = '{$lote}' AND cve_almac = '{$almacen}' AND ID_Proveedor = '{$ID_Proveedor}';";
            $query = mysqli_query($conn, $entrada);
          }
      }
*/
  }

  $sql_eliminar_proveedor = "SELECT COUNT(*) as existe FROM th_entalmacen WHERE Cve_Proveedor = '{$borrar_proveedor}'";
  $res = mysqli_query($conn, $sql_eliminar_proveedor);
  $existe_proveedor_en_entradas = mysqli_fetch_assoc($res)['existe'];
$sql1 = $sql_eliminar_proveedor; $sql2 = "";
  if($existe_proveedor_en_entradas == 0)
  {
    $sql_eliminar_prov = "DELETE FROM rel_articulo_proveedor WHERE CONCAT(cve_articulo, ID_Proveedor) IN (SELECT DISTINCT CONCAT(td.cve_articulo, th.Cve_Proveedor)
                                                                                                FROM td_entalmacen td
                                                                                                LEFT JOIN th_entalmacen th ON th.Fol_Folio = td.fol_folio
                                                                                                LEFT JOIN c_proveedores p ON p.ID_Proveedor = th.Cve_Proveedor
                                                                                                WHERE td.fol_folio = '{$folio}')";
    $sql2 = $sql_eliminar_prov;
    $query = mysqli_query($conn, $sql_eliminar_prov);
  }


  $sql_oc_rel = "SELECT num_pedimento as oc_relacionada FROM th_aduana WHERE num_pedimento = (SELECT IFNULL(id_ocompra, '') FROM th_entalmacen WHERE Fol_Folio = '{$folio}')";
  $res = mysqli_query($conn, $sql_oc_rel);
  $oc_relacionada = mysqli_fetch_assoc($res)['oc_relacionada'];

  $entrada = "DELETE FROM t_cardex WHERE origen = '{$folio}' AND Id_TipoMovimiento = 1;";
  $query = mysqli_query($conn, $entrada);

  $entrada = "DELETE FROM t_MovCharolas WHERE Origen = '{$folio}' AND Id_TipoMovimiento = 1;";
  $query = mysqli_query($conn, $entrada);

  $entrada = "DELETE FROM td_entalmacen_fotos WHERE td_entalmacen_producto_id = (SELECT id FROM th_entalmacen_fotos WHERE th_entalmacen_folio = '{$folio}');";
  $query = mysqli_query($conn, $entrada);

  $entrada = "DELETE FROM th_entalmacen_fotos WHERE th_entalmacen_folio = '{$folio}';";
  $query = mysqli_query($conn, $entrada);

  $entrada = "DELETE FROM td_entalmacen WHERE fol_folio = '{$folio}';";
  $query = mysqli_query($conn, $entrada);

  $entrada = "DELETE FROM td_entalmacenxtarima WHERE fol_folio = '{$folio}';";
  $query = mysqli_query($conn, $entrada);

  $entrada = "DELETE FROM ts_existenciacajas WHERE Id_Caja IN (SELECT Id_Caja FROM td_entalmacencaja WHERE Fol_Folio = '{$folio}');";
  $query = mysqli_query($conn, $entrada);

  $entrada = "DELETE FROM td_entalmacencaja WHERE Fol_Folio = '{$folio}';";
  $query = mysqli_query($conn, $entrada);

  $entrada = "DELETE FROM th_entalmacen WHERE Fol_Folio = '{$folio}';";
  $query = mysqli_query($conn, $entrada);

  $entrada = "DELETE FROM t_trazabilidad_existencias WHERE folio_entrada = '{$folio}';";
  $query = mysqli_query($conn, $entrada);


  if($oc_relacionada != "")
  {
      $oc = $oc_relacionada;

      $sql = "
        DELETE FROM td_aduanaxtarima WHERE Num_Orden = {$oc};
        ";

      if (!($res = mysqli_query($conn, $sql))) 
      {
          echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
      }

      $sql = "
        DELETE FROM td_aduana WHERE num_orden = {$oc};
        ";

      if (!($res = mysqli_query($conn, $sql))) 
      {
          echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
      }

      $sql = "
        DELETE FROM th_aduana WHERE num_pedimento = {$oc};
        ";

      if (!($res = mysqli_query($conn, $sql))) 
      {
          echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
      }


  }

  //$entrada = "DELETE FROM ts_existenciatarima WHERE Fol_Folio = '{$folio}';";
  //$query = mysqli_query($conn, $entrada);

  $entrada = "UPDATE ts_existenciatarima SET existencia = 0 WHERE existencia < 0;";
  $query = mysqli_query($conn, $entrada);

  $entrada = "UPDATE ts_existenciapiezas SET existencia = 0 WHERE existencia < 0;";
  $query = mysqli_query($conn, $entrada);

/*
  if($mensaje_no_borrar != '')
      $mensaje_no_borrar = "\n\nLos Siguientes artículos no se borraron porque ya tienen movimientos realizados: \n".$mensaje_no_borrar."\n\n Por favor realice un Ajuste de Existencias";
    */
  $arr = array(
      "success" => $entrada,
      "sql1" => $sql1,
      "sql2" => $sql2,
      "existe_proveedor_en_entradas" => $existe_proveedor_en_entradas,
      "mensaje" => $mensaje_no_borrar
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
						SELECT DISTINCT 
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
  $sql1 = "
      SELECT DISTINCT
            tde.id AS id,
            tde.cve_articulo AS clave,
            a.des_articulo AS descripcion,
            IFNULL(a.control_lotes, 'N') AS band_lote,
            IFNULL(a.Caduca, 'N') AS band_caducidad,
            IFNULL(a.control_numero_series, 'N') AS band_serie,
            IFNULL(tde.CantidadPedida, tde.CantidadRecibida) AS cantidad_pedida,
            tde.cve_lote AS lote,
            tde.numero_serie AS serie,
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            IFNULL(a.cajas_palet, 0) cajasxpallets,
            IFNULL(a.num_multiplo, 0) piezasxcajas, 
            DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y') AS caducidad,
            tde.status AS STATUS,
            #IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '$codigo'), tde.CantidadRecibida, ta.Cantidad) AS cantidad_recibida,
            IFNULL(ta.Cantidad, tde.CantidadRecibida) AS cantidad_recibida,
            (IFNULL(tde.CantidadRecibida, 0)-IFNULL(tde.CantidadUbicada, 0)) as pendiente_acomodar,
            (IFNULL(tde.CantidadUbicada, 0)) as cantidad_acomodada,
            DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y') AS fecha_recepcion,
            DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y %h:%i:%s %p') AS fecha_inicio,
            DATE_FORMAT(tde.fecha_fin, '%d-%m-%Y %h:%i:%s %p') AS fecha_fin,
            #tde.CantidadPedida - IF(th_entalmacen.tipo = 'OC', tde.CantidadRecibida, 0) AS cantidad_faltante,
            #tde.CantidadPedida - IF(th_entalmacen.tipo = 'OC', tde.CantidadRecibida, 0) AS cantidad_danada,
            tde.CantidadPedida - IFNULL(ta.Cantidad, tde.CantidadRecibida) AS cantidad_faltante,
            tde.CantidadPedida - IFNULL(ta.Cantidad, tde.CantidadRecibida) AS cantidad_danada,
            u.cve_usuario AS usuario,
            #IF(c_charolas.CveLP != '',c_charolas.CveLP, '') AS pallet,
            #c_charolas.clave_contenedor AS pallet,
            #ta.ClaveEtiqueta AS contenedor
            c_charolas.clave_contenedor AS pallet,
            c_charolas.CveLP AS contenedor
      FROM td_entalmacen tde
          LEFT JOIN td_aduana tda ON tda.cve_articulo = tde.cve_articulo AND tda.num_orden = tde.num_orden
          LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo
          LEFT JOIN c_usuario u ON u.cve_usuario = tde.cve_usuario
          LEFT JOIN th_entalmacen ON th_entalmacen.Fol_Folio = tde.fol_folio
          LEFT JOIN c_lotes ON c_lotes.LOTE = tde.cve_lote AND c_lotes.cve_articulo = tde.cve_articulo
          LEFT JOIN td_entalmacenxtarima ta ON ta.fol_folio = tde.fol_folio AND tde.cve_articulo = ta.cve_articulo AND tde.cve_lote = ta.cve_lote #AND tde.CantidadRecibida = ta.Cantidad
          LEFT JOIN c_charolas ON c_charolas.clave_contenedor= ta.ClaveEtiqueta OR c_charolas.CveLP= ta.ClaveEtiqueta
      WHERE tde.fol_folio = '$codigo'
      ORDER BY clave DESC
  ";//LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo AND cl.cve_articulo=a.cve_articulo
*/

  


  $sql1 = "SELECT 
        tde.id AS id
      FROM td_entalmacen tde
          LEFT JOIN td_aduana tda ON tda.cve_articulo = tde.cve_articulo AND tda.num_orden = tde.num_orden AND IFNULL(tda.Cve_Lote, '') = IFNULL(tde.cve_lote, '')
          LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo
          LEFT JOIN c_usuario u ON u.cve_usuario = tde.cve_usuario
          LEFT JOIN th_entalmacen ON th_entalmacen.Fol_Folio = tde.fol_folio
          LEFT JOIN c_lotes ON c_lotes.LOTE = tde.cve_lote AND c_lotes.cve_articulo = tde.cve_articulo
          LEFT JOIN td_entalmacenxtarima ta ON ta.fol_folio = tde.fol_folio AND tde.cve_articulo = ta.cve_articulo AND tde.cve_lote = ta.cve_lote 
          LEFT JOIN c_charolas ON c_charolas.clave_contenedor = ta.ClaveEtiqueta
          LEFT JOIN c_tipocaja cc ON cc.id_tipocaja = a.tipo_caja
          LEFT JOIN td_entalmacencaja tc ON tc.Fol_Folio = tde.fol_folio AND tc.Cve_Articulo = a.cve_articulo AND IFNULL(tc.Cve_Lote, '') = IFNULL(tde.cve_lote, '')
          LEFT JOIN c_charolas ch_caja ON ch_caja.IDContenedor = tc.Id_Caja
      WHERE tde.fol_folio = '$codigo'

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

  // hace una llamada previa al procedimiento almacenado Lis_Facturas  || !($res2 = mysqli_query($conn, $sql2))
///// if (!($res = mysqli_query($conn, $sql1))) 
///// {
    ///// echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
///// }
  try{
     //mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //$pdo = new PDO("mysql:host={$dbhost};dbname={$dbname}", $dbuser, $dbpass);
    //$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);//(DB_HOST, DB_PASSWORD, DB_USER, DB_PASSWORD);
    $pdo = \db();
    //$res = $pdo->prepare("SELECT COUNT(*) as count FROM ($sql1) AS c");
    $res = $pdo->query($sql1);
    //$res->execute(array('codigo' => $codigo));
    $count = $res->rowCount();

  }catch (PDOException $e) {
    echo 'Error de conexión: ' . $e->getMessage();
}


  $sql1 = "SELECT DISTINCT
        tde.id AS id,
        IFNULL(ch_caja.CveLP, '') AS EtiquetaCaja,
              tde.cve_articulo AS clave,
              a.des_articulo AS descripcion,
              IFNULL(a.control_lotes, 'N') AS band_lote,
              IFNULL(a.Caduca, 'N') AS band_caducidad,
              IFNULL(a.control_numero_series, 'N') AS band_serie,
              IFNULL(tda.cantidad, '') AS cantidad_pedida,
              IF(a.control_lotes = 'S', c_lotes.LOTE,'') AS lote,
              IF(a.control_numero_series = 'S', tde.cve_lote,'') AS serie,
              0 AS PalletDiv,
              0 AS Caja, 
              0 AS Piezas,
              IFNULL(a.cajas_palet, 0) cajasxpallets,
              IFNULL(a.num_multiplo, 0) piezasxcajas, 
              IF(IF(a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), '') LIKE '%0000%', '', IF(a.Caduca = 'S', DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y'), '')) AS caducidad,
              tde.status AS status,

              IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT DISTINCT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '$codigo'), 
               IF(CONCAT(tde.cve_articulo, tde.cve_lote) IN (SELECT DISTINCT CONCAT(Cve_Articulo, Cve_Lote) FROM td_entalmacencaja WHERE Fol_Folio = '$codigo' AND Id_Caja = ch_caja.IDContenedor AND Cve_Articulo = tde.cve_articulo AND IFNULL(Cve_Lote, '') = IFNULL(tde.cve_lote, '') GROUP BY Id_Caja, Cve_Articulo, Cve_Lote), (SELECT DISTINCT SUM(PzsXCaja) FROM td_entalmacencaja WHERE Fol_Folio = '$codigo' AND Id_Caja = ch_caja.IDContenedor AND Cve_Articulo = tde.cve_articulo AND IFNULL(Cve_Lote, '') = IFNULL(tde.cve_lote, '') GROUP BY Id_Caja, Cve_Articulo, Cve_Lote), tde.CantidadRecibida), 
               IF(CONCAT(tde.cve_articulo, tde.cve_lote) IN (SELECT DISTINCT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '$codigo') AND CONCAT(tde.cve_articulo, tde.cve_lote) IN (SELECT DISTINCT CONCAT(Cve_Articulo, Cve_Lote) FROM td_entalmacencaja WHERE Fol_Folio = '$codigo' AND Id_Caja = ch_caja.IDContenedor AND Cve_Articulo = tde.cve_articulo AND IFNULL(Cve_Lote, '') = IFNULL(tde.cve_lote, '') GROUP BY Id_Caja, Cve_Articulo, Cve_Lote), (SELECT DISTINCT SUM(PzsXCaja) FROM td_entalmacencaja WHERE Fol_Folio = '$codigo' AND Id_Caja = ch_caja.IDContenedor AND Cve_Articulo = tde.cve_articulo AND IFNULL(Cve_Lote, '') = IFNULL(tde.cve_lote, '') GROUP BY Id_Caja, Cve_Articulo, Cve_Lote),ta.Cantidad)
               ) AS cantidad_recibida,

              tda.Factura as factura_articulo, 

        IFNULL(((IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '$codigo'), tde.CantidadRecibida, ta.Cantidad)) - (IF(IFNULL(ta.Ubicada, 'S') = 'S', IFNULL(IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '$codigo'  AND fol_folio = tde.fol_folio AND tde.cve_articulo = cve_articulo AND IFNULL(tde.cve_lote, '') = IFNULL(cve_lote, '')), tde.CantidadUbicada, ta.Cantidad), 0), 0))), tde.CantidadRecibida) AS pendiente_acomodar,

        IF(IFNULL(ta.Ubicada, 'S') = 'S', IFNULL(IF(CONCAT(tde.cve_articulo, tde.cve_lote) NOT IN (SELECT CONCAT(cve_articulo, cve_lote) FROM td_entalmacenxtarima WHERE fol_folio = '$codigo'  AND fol_folio = tde.fol_folio AND tde.cve_articulo = cve_articulo AND IFNULL(tde.cve_lote, '') = IFNULL(cve_lote, '')), tde.CantidadUbicada, ta.Cantidad), 0), 0) AS cantidad_acomodada,
              IFNULL(tc.Ubicada, 'N') AS Ubicada,
              (SELECT MIN(DATE_FORMAT(fecha_inicio, '%d-%m-%Y %h:%i:%s %p')) FROM td_entalmacen WHERE tde.num_orden = td_entalmacen.num_orden) AS fecha_recepcion,

              DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y %h:%i:%s %p') AS fecha_inicio,
              DATE_FORMAT(tde.fecha_fin, '%d-%m-%Y %h:%i:%s %p') AS fecha_fin,
              tda.cantidad - tde.CantidadRecibida AS cantidad_faltante,
              IF(tde.CantidadDisponible-tde.CantidadRecibida<0, '0', tde.CantidadDisponible-tde.CantidadRecibida) AS cantidad_danada,    
        u.cve_usuario AS usuario,
        IF(c_charolas.CveLP != '',c_charolas.CveLP, '') AS pallet,
        ta.ClaveEtiqueta AS contenedor
      FROM td_entalmacen tde
          LEFT JOIN td_aduana tda ON tda.cve_articulo = tde.cve_articulo AND tda.num_orden = tde.num_orden AND IFNULL(tda.Cve_Lote, '') = IFNULL(tde.cve_lote, '')
          LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo
          LEFT JOIN c_usuario u ON u.cve_usuario = tde.cve_usuario
          LEFT JOIN th_entalmacen ON th_entalmacen.Fol_Folio = tde.fol_folio
          LEFT JOIN c_lotes ON c_lotes.LOTE = tde.cve_lote AND c_lotes.cve_articulo = tde.cve_articulo
          LEFT JOIN td_entalmacenxtarima ta ON ta.fol_folio = tde.fol_folio AND tde.cve_articulo = ta.cve_articulo AND tde.cve_lote = ta.cve_lote 
          LEFT JOIN c_charolas ON c_charolas.clave_contenedor = ta.ClaveEtiqueta
          LEFT JOIN c_tipocaja cc ON cc.id_tipocaja = a.tipo_caja
          LEFT JOIN td_entalmacencaja tc ON tc.Fol_Folio = tde.fol_folio AND tc.Cve_Articulo = a.cve_articulo AND IFNULL(tc.Cve_Lote, '') = IFNULL(tde.cve_lote, '')
          LEFT JOIN c_charolas ch_caja ON ch_caja.IDContenedor = tc.Id_Caja
      WHERE tde.fol_folio = '$codigo'

      ";



//$count = mysqli_num_rows($res);
  $sql1 .= " LIMIT $start, $limit ";
//if (!($res = mysqli_query($conn, $sql1))) 
//{
  //echo "Falló la preparación: (" . mysqli_error($conn) . ") ". $sql1;
//}

  try{
    $res = \db()->query($sql1);
    //$res->execute(array('codigo' => $codigo, 'start' => $start, 'limit' => $limit));
} catch (PDOException $e) {
    echo 'Error de conexión: ' . $e->getMessage();
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
  $responce->sql = $sql1;

//************************************************
// Proceso para unir los resultados de las tablas
/************************************************
$lp_reg = ""; $pallet_contenedor = ""; $pallets_diferentes = true;
if(mysqli_num_rows($res2) == 1)
{
  $row = mysqli_fetch_array($res2);
  $lp_reg = $row['LP'];
  $pallet_contenedor = $row['contenedor'];
  $pallets_diferentes = false;
}

//************************************************/
  $arr = array();
  $i = 0;
  $clave_actual = ""; $num_rows = 0; 
  $lote_actual = ""; $lote_comp = "";
  $clave_actual_pedido = "";

  $cant_pedida = 0; $cant_recibida = 0; $diferencia = 0;
  //while ($row = mysqli_fetch_array($res)) 
  while ($row = $res->fetch()) 
  {
    //$row = array_map("utf8_encode", $row );
    extract($row);
    //$linea = array_search($clave,$lineas) + 1;
    $linea = $i+1;
    $i_rows++;

    $lote = $row['lote'];
/*
    if($num_rows != $i_rows) $cantidad_danada = "";
    $arr[] = $row;

    if($pallets_diferentes)
    {
        $row2 = mysqli_fetch_array($res2);
        $lp_reg = $row2['LP'];
        $pallet_contenedor = $row2['contenedor'];
    }
*/
    if($band_serie == 'S')
    {
        //$serie = $lote;
        $lote = "";
        $caducidad = "";
    }
    else if($band_lote == 'S')
    {
        $serie = "";
        if($band_caducidad == 'N') $caducidad = "";
    }
    else
    {
      $lote = "";
      $serie = "";
      $caducidad = "";
    }
    $clave_actual_pedido = $row['clave'];

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
    /*
        $valor1 = 0;
        //$piezasxcajas = $pendiente_acomodar;
        if($piezasxcajas > 0)
           $valor1 = $cantidad_pedida/$piezasxcajas;

        if($cajasxpallets > 0)
           $valor1 = $valor1/$cajasxpallets;
       else
           $valor1 = 0;

        $Pallet = intval($valor1);

        $valor2 = 0;
        $cantidad_restante = $cantidad_pedida - ($Pallet*$piezasxcajas*$cajasxpallets);
        if(!is_int($valor1) || $valor1 == 0)
        {
            if($piezasxcajas > 0)
               $valor2 = ($cantidad_restante/$piezasxcajas);// - ($Pallet*$cantidad_pedida);
        }
        $Caja = intval($valor2);

        $Piezas = 0;
        if($piezasxcajas == 1) 
        {
            $valor2 = 0; 
            $Caja = 0;
            $Piezas = $cantidad_restante;
        }
        else if($piezasxcajas == 0 || $piezasxcajas == "")
        {
            if($piezasxcajas == "") $piezasxcajas = 0;
            $Caja = 0;
            $Piezas = $cantidad_restante;
        }

        $cantidad_restante = $cantidad_restante - ($Caja*$piezasxcajas);

        if(!is_int($valor2))
        {
           //$Piezas = ($Caja*$cantidad_restante) - $piezasxcajas;
            $Piezas = $cantidad_restante;
        }
        //**************************************************
*/
        //*********************************************
        // Se tomará las pedidas como las piezas
        //*********************************************
        $Piezas = $cantidad_recibida;
        //*********************************************
//**********************************************************************************************************************
if($EtiquetaCaja != "") 
{
    $Caja = 1; 
    if($Ubicada == 'N') $pendiente_acomodar = $Piezas; else $pendiente_acomodar = 0; 
    $cantidad_acomodada = 0; 
    $Piezas = 0;
}

    $responce->rows[$i]['id']=$i;
    $responce->rows[$i]['cell']=array(
                                      '<a href="#" onclick="ver_fotos_td('.$id.', '."'".$descripcion."'".')"><i class="fa fa-camera" title="Fotos"></i></a>',
                                      $linea, 
                                      $contenedor,
                                      $pallet,
                                      $EtiquetaCaja,
                                      $clave, 
                                      $lote,
                                      $caducidad,
                                      $serie,
                                      $descripcion,
                                      $PalletDiv, 
                                      $Caja,
                                      $Piezas,
                                      $pendiente_acomodar,
                                      $cantidad_acomodada,
                                      abs($cantidad_faltante),
                                      // $status, 
                                      $fecha_recepcion, 

                                      $fecha_inicio, 
                                      $fecha_fin, 

                                      $cantidad_danada, 
                                      $factura_articulo,
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

    if($tipo == 'OC')
      {
					$titulo ="Orden de Compra";
/*
					$datos_articulos ="
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
*/
      }
      else
			{
					$titulo ="Recepcion libre";
/*
					$datos_articulos ="																			
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
*/
      }
  
            $datos_articulos ="                                     
              '' as ID_Aduana,
              td_entalmacen.cve_articulo,
              IF(IFNULL(tdt.fol_folio, '') = '', td_entalmacen.CantidadRecibida, tdt.Cantidad) as cantidad,
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
    
        $sql = "
            SELECT 
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

                {$datos_articulos}

                c_articulo.cve_articulo,
                c_articulo.des_articulo,
                c_articulo.tipo_producto,
                c_articulo.umas,
                IFNULL(IFNULL(ch.CveLP, ch.clave_contenedor), '') AS LP,
                c_proveedores.Nombre as proveedor,
                
                (select concat(c_compania.des_rfc,'<br>',c_compania.des_direcc, ' ',c_compania.des_cp) from c_compania where cve_cia = 1) as datos_facturacion,
                (select concat(Nombre,'<br>',RUT,'<br>',direccion, '<br>',colonia,' ',cve_dane,'<br>',ciudad,', ',estado,', ',pais ) from c_proveedores where ID_Proveedor = th_aduana.ID_Proveedor) datos_proveedor
            from {$from}
            {$join}
            LEFT JOIN c_presupuestos on th_aduana.presupuesto = c_presupuestos.id
            
            LEFT JOIN c_proveedores on c_proveedores.ID_Proveedor = th_aduana.ID_Proveedor

            LEFT JOIN td_entalmacenxtarima tdt ON tdt.fol_folio = th_entalmacen.Fol_Folio AND tdt.cve_articulo = td_entalmacen.cve_articulo AND tdt.cve_lote = td_entalmacen.cve_lote
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = tdt.ClaveEtiqueta

            {$where} 
            GROUP BY LP, c_articulo.cve_articulo, cve_lote;
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
				//$cuadro1 = array('Fecha','Partida Presupuestal','Suficiencia Presupuestal');
        $cuadro1 = array('','','');
				//$cuadro2 = array($datos["fechaSuficiencia"],$datos["claveDePartida"],$datos["montoSuficiencia"]);
        $cuadro2 = array('','','');
        //$cuadro1 = "";
        //$cuadro2 = "";
				$cuadro3 = array('Condiciones de Pago','Fecha de Entrega');
				$cuadro4 = array($datos["condicionesDePago"],$datos["fech_llegPed"]);
				//$cuadro5 = array('Proveedor','Lugar de Entrega','Area Solicitante');
				//$cuadro6 = array($datos["datos_proveedor"],$datos["lugarDeEntrega"],$datos["areaSolicitante"]);
				$cuadro7 = array('LP', 'PARTIDA','CONCEPTO','CANTIDAD','U.M.','P.U.','SUBTOTAL');
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
        $excel->writeSheetRow('Sheet1', $cuadro1 );
				//$excel->writeSheetRow('Sheet1', $cuadro5 );
				//$excel->writeSheetRow('Sheet1', $cuadro6 );
				$excel->writeSheetRow('Sheet1', $cuadro7 );
        $excel->writeSheetRow('Sheet1', $cuadro1 );
				foreach($rows as $row)
				{
						$row = array($row["LP"], $row["cve_articulo"],$row["des_articulo"], number_format($row["cantidad"], 2),$row["umas"], $row["costo"],$row["subtotal"]);
						$excel->writeSheetRow('Sheet1', $row );
				}
        $excel->writeSheetRow('Sheet1', $cuadro1 );
				$excel->writeSheetRow('Sheet1', $cuadro8);
				$excel->writeSheetRow('Sheet1', $totales);


				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="' . $title . '"');
				header('Cache-Control: max-age=0');
				$excel->writeToStdOut($title);
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'exportExcelReporteEntradas')
{
    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $folio = $_POST['folio'];
    $title = "Reporte Entrada #{$folio}.xlsx";

/*
    $sql = "SELECT DISTINCT p.cve_proveedor AS clave_proveedor, p.Nombre AS nombre_proveedor, oc.num_pedimento AS folio_oc, oc.Factura AS factura_oc, 
       ent.Fol_Folio AS folio_entrada, ent.Fol_OEP AS factura_entrada, dt.factura_articulo AS factura_articulo, IFNULL(tdt_oc.ClaveEtiqueta, '') AS LP, 
       a.cve_articulo AS clave_articulo,
       a.cve_alt AS clave_alterna, a.des_articulo AS des_articulo, IFNULL(dt.cve_lote, '') AS lote, 
       IF(a.Caduca = 'S', DATE_FORMAT(lt.Caducidad, '%d-%m-%Y'), '') as Caducidad, prot.descripcion AS tipo_de_protocolo, 
       ga.des_gpoart AS grupo_articulo, IFNULL(tdt_oc.Cantidad, dt_oc.cantidad) AS cantidad, um.des_umed AS um_articulo, 
       DATE_FORMAT(ent.Fec_Entrada, '%d-%m-%Y') AS fecha_entrada, DATE_FORMAT(ent.Fec_Entrada, '%H:%i:%S') AS hora_entrada,
       '' AS estado_serial, dt_oc.costo AS valor_unitario, dt_oc.costo*IFNULL(tdt_oc.Cantidad, dt_oc.cantidad) AS valor_total, ent.Proyecto, u.CodigoCSD AS ubicacion, ent.Cve_Usuario AS usuario,
       etr.Observaciones, etr.No_Unidad AS num_unidad, etr.Linea_Transportista AS clave_transportadora, etr.Placas AS placa, etr.Sello, etr.Id_Operador AS id_chofer,
       etr.Operador AS nombre_conductor, DATE_FORMAT(etr.Fec_Ingreso, '%d-%m-%Y') AS fecha_transporte, DATE_FORMAT(etr.Fec_Ingreso, '%H:%i:%S') AS hora_transporte,
       '' AS declaracion_importacion, '' AS documento_transporte_internacional, '' AS destino_direccion, '' AS DO
FROM td_aduana dt_oc 
LEFT JOIN td_aduanaxtarima tdt_oc ON tdt_oc.Cve_Articulo = dt_oc.cve_articulo AND IFNULL(tdt_oc.Cve_Lote, '') = IFNULL(dt_oc.Cve_Lote, '') AND tdt_oc.Num_Orden = dt_oc.num_orden
LEFT JOIN th_aduana oc ON oc.num_pedimento = dt_oc.num_orden
LEFT JOIN c_articulo a ON a.cve_articulo = dt_oc.cve_articulo
LEFT JOIN th_entalmacen ent ON ent.id_ocompra = oc.num_pedimento
LEFT JOIN td_entalmacen dt ON dt.Fol_folio = ent.Fol_folio AND a.cve_articulo = dt.cve_articulo
LEFT JOIN td_entalmacenxtarima tdt ON tdt.cve_articulo = dt.cve_articulo AND IFNULL(tdt.cve_lote, '') = IFNULL(dt.cve_lote, '') AND dt.fol_folio = tdt.fol_folio
LEFT JOIN c_lotes lt ON lt.cve_articulo = a.cve_articulo AND lt.lote = dt.cve_lote
LEFT JOIN c_proveedores p ON ent.Cve_Proveedor = p.ID_Proveedor
LEFT JOIN t_protocolo prot ON prot.ID_Protocolo = ent.ID_Protocolo
LEFT JOIN c_gpoarticulo ga ON ga.cve_gpoart = a.grupo
LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
LEFT JOIN t_entalmacentransporte etr ON etr.Fol_Folio = ent.Fol_Folio
LEFT JOIN c_charolas ch ON ch.CveLP = tdt_oc.ClaveEtiqueta
LEFT JOIN t_trazabilidad_existencias tr ON tr.folio_entrada = ent.fol_folio AND tr.cve_articulo = dt_oc.cve_articulo AND IFNULL(tdt_oc.cve_lote, dt_oc.cve_lote) = IFNULL(tr.cve_lote, '') AND IFNULL(tr.ntarima, '') = IFNULL(ch.IDContenedor, '') AND tr.idy_ubica IS NOT NULL
LEFT JOIN c_ubicacion u ON tr.idy_ubica = u.idy_ubica
WHERE ent.Fol_Folio = $folio 
AND dt_oc.num_orden = oc.num_pedimento AND dt.cve_articulo = dt_oc.cve_articulo 
AND a.cve_articulo = dt_oc.cve_articulo AND IFNULL(tdt.cve_lote, dt.cve_lote) = IFNULL(tr.cve_lote, '') ";
*/
  $sql1 = "SELECT  
             p.cve_proveedor AS clave_proveedor, p.Nombre AS nombre_proveedor, tr.folio_oc AS folio_oc, tr.factura_oc AS factura_oc, 
             tr.folio_entrada AS folio_entrada, tr.factura_ent AS factura_entrada, IFNULL(dt.factura_articulo, '') AS factura_articulo, IFNULL(ch.CveLP, '') AS LP, 
             tr.cve_articulo AS clave_articulo, a.cve_alt AS clave_alterna, a.des_articulo AS des_articulo, #IFNULL(tr.cve_lote, '') AS lote, 
             IFNULL(lt.lote, '') AS lote, IFNULL(s.numero_serie, '') AS serie,
             IFNULL(lt.Caducidad, '') AS Caducidad, prot.descripcion AS tipo_de_protocolo, 
             ga.des_gpoart AS grupo_articulo, (tr.cantidad) AS cantidad, um.des_umed AS um_articulo, 
             DATE_FORMAT(ent.Fec_Entrada, '%d-%m-%Y') AS fecha_entrada, DATE_FORMAT(ent.Fec_Entrada, '%H:%i:%S') AS hora_entrada,
             '' AS estado_serial, dt_oc.costo AS valor_unitario, dt_oc.costo*(tr.cantidad) AS valor_total, tr.proyecto AS Proyecto, u.CodigoCSD AS ubicacion, ent.Cve_Usuario AS usuario,
             etr.Observaciones, etr.No_Unidad AS num_unidad, etr.Linea_Transportista AS clave_transportadora, etr.Placas AS placa, etr.Sello, etr.Id_Operador AS id_chofer,
             etr.Operador AS nombre_conductor, DATE_FORMAT(etr.Fec_Ingreso, '%d-%m-%Y') AS fecha_transporte, DATE_FORMAT(etr.Fec_Ingreso, '%H:%i:%S') AS hora_transporte,
             '' AS declaracion_importacion, '' AS documento_transporte_internacional, '' AS destino_direccion, '' AS DO
          FROM t_trazabilidad_existencias tr
          LEFT JOIN c_articulo a ON a.cve_articulo = tr.cve_articulo
          LEFT JOIN th_entalmacen ent ON ent.id_ocompra = tr.folio_oc AND tr.folio_entrada = ent.Fol_Folio
          LEFT JOIN td_entalmacen dt ON dt.Fol_folio = tr.folio_entrada AND tr.cve_articulo = dt.cve_articulo AND IFNULL(tr.cve_lote, '') = IFNULL(dt.cve_lote, '')
          LEFT JOIN c_lotes lt ON lt.cve_articulo = tr.cve_articulo AND IFNULL(TRIM(lt.lote), '') = IFNULL(TRIM(tr.cve_lote), '') AND a.control_lotes = 'S'
          LEFT JOIN c_serie s ON s.cve_articulo = tr.cve_articulo AND IFNULL(TRIM(s.numero_serie), '') = IFNULL(TRIM(tr.cve_lote), '') AND a.control_numero_series = 'S'
          LEFT JOIN c_proveedores p ON ent.Cve_Proveedor = p.ID_Proveedor
          LEFT JOIN t_protocolo prot ON prot.ID_Protocolo = ent.ID_Protocolo
          LEFT JOIN c_gpoarticulo ga ON ga.cve_gpoart = a.grupo
          LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
          LEFT JOIN t_entalmacentransporte etr ON etr.Fol_Folio = tr.folio_entrada
          LEFT JOIN c_charolas ch ON ch.IDContenedor = tr.ntarima
          LEFT JOIN td_aduana dt_oc ON dt_oc.num_orden = tr.folio_oc AND tr.cve_articulo = dt_oc.cve_articulo AND IFNULL(tr.ntarima, '') = IFNULL(ch.IDContenedor, '') AND tr.idy_ubica IS NOT NULL AND IFNULL(dt_oc.cve_lote, dt.cve_lote) = IFNULL(tr.cve_lote, '')
          LEFT JOIN c_ubicacion u ON tr.idy_ubica = u.idy_ubica
          WHERE tr.folio_entrada = $folio
          #AND tr.idy_ubica IS NOT NULL 
          AND tr.id_tipo_movimiento = 2
          GROUP BY clave_proveedor, LP, clave_articulo, lote, serie, ubicacion";

  $sql2 = "SELECT * FROM(
            SELECT  
             p.cve_proveedor AS clave_proveedor, p.Nombre AS nombre_proveedor, tr.folio_oc AS folio_oc, tr.factura_oc AS factura_oc, 
             tr.folio_entrada AS folio_entrada, tr.factura_ent AS factura_entrada, IFNULL(dt.factura_articulo, '') AS factura_articulo, IFNULL(ch.CveLP, '') AS LP, 
             tr.cve_articulo AS clave_articulo, a.cve_alt AS clave_alterna, a.des_articulo AS des_articulo, #IFNULL(tr.cve_lote, '') AS lote, 
             IFNULL(lt.lote, '') AS lote, IFNULL(s.numero_serie, '') AS serie,
             IFNULL(lt.Caducidad, '') AS Caducidad, prot.descripcion AS tipo_de_protocolo, 
             ga.des_gpoart AS grupo_articulo, (tr.cantidad) AS cantidad, um.des_umed AS um_articulo, 
             DATE_FORMAT(ent.Fec_Entrada, '%d-%m-%Y') AS fecha_entrada, DATE_FORMAT(ent.Fec_Entrada, '%H:%i:%S') AS hora_entrada,
             '' AS estado_serial, dt_oc.costo AS valor_unitario, dt_oc.costo*(tr.cantidad) AS valor_total, tr.proyecto AS Proyecto, u.CodigoCSD AS ubicacion, ent.Cve_Usuario AS usuario,
             etr.Observaciones, etr.No_Unidad AS num_unidad, etr.Linea_Transportista AS clave_transportadora, etr.Placas AS placa, etr.Sello, etr.Id_Operador AS id_chofer,
             etr.Operador AS nombre_conductor, DATE_FORMAT(etr.Fec_Ingreso, '%d-%m-%Y') AS fecha_transporte, DATE_FORMAT(etr.Fec_Ingreso, '%H:%i:%S') AS hora_transporte,
             '' AS declaracion_importacion, '' AS documento_transporte_internacional, '' AS destino_direccion, '' AS DO
          FROM t_trazabilidad_existencias tr
          LEFT JOIN c_articulo a ON a.cve_articulo = tr.cve_articulo
          LEFT JOIN th_entalmacen ent ON ent.id_ocompra = tr.folio_oc AND tr.folio_entrada = ent.Fol_Folio
          LEFT JOIN td_entalmacen dt ON dt.Fol_folio = tr.folio_entrada AND tr.cve_articulo = dt.cve_articulo AND IFNULL(tr.cve_lote, '') = IFNULL(dt.cve_lote, '')
          LEFT JOIN c_lotes lt ON lt.cve_articulo = tr.cve_articulo AND IFNULL(TRIM(lt.lote), '') = IFNULL(TRIM(tr.cve_lote), '') AND a.control_lotes = 'S'
          LEFT JOIN c_serie s ON s.cve_articulo = tr.cve_articulo AND IFNULL(TRIM(s.numero_serie), '') = IFNULL(TRIM(tr.cve_lote), '') AND a.control_numero_series = 'S'
          LEFT JOIN c_proveedores p ON ent.Cve_Proveedor = p.ID_Proveedor
          LEFT JOIN t_protocolo prot ON prot.ID_Protocolo = ent.ID_Protocolo
          LEFT JOIN c_gpoarticulo ga ON ga.cve_gpoart = a.grupo
          LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
          LEFT JOIN t_entalmacentransporte etr ON etr.Fol_Folio = tr.folio_entrada
          LEFT JOIN c_charolas ch ON ch.IDContenedor = tr.ntarima
          LEFT JOIN td_aduana dt_oc ON dt_oc.num_orden = tr.folio_oc AND tr.cve_articulo = dt_oc.cve_articulo AND IFNULL(tr.ntarima, '') = IFNULL(ch.IDContenedor, '') AND tr.idy_ubica IS NOT NULL AND IFNULL(dt_oc.cve_lote, dt.cve_lote) = IFNULL(tr.cve_lote, '')
          LEFT JOIN c_ubicacion u ON tr.idy_ubica = u.idy_ubica
          WHERE tr.folio_entrada = $folio
          #AND tr.idy_ubica IS NOT NULL 
          AND tr.id_tipo_movimiento = 1
          GROUP BY clave_proveedor, LP, clave_articulo, lote, serie, ubicacion) as ent
          WHERE CONCAT(ent.clave_proveedor, ent.folio_oc, ent.factura_oc, ent.folio_entrada, ent.factura_entrada, ent.LP, ent.clave_articulo, ent.clave_alterna, ent.lote, ent.serie, ent.um_articulo, ent.Proyecto, ent.usuario, ent.num_unidad, ent.clave_transportadora, ent.placa) NOT IN (SELECT CONCAT(ent2.clave_proveedor, ent2.folio_oc, ent2.factura_oc, ent2.folio_entrada, ent2.factura_entrada, ent2.LP, ent2.clave_articulo, ent2.clave_alterna, ent2.lote, ent2.serie, ent2.um_articulo, ent2.Proyecto, ent2.usuario, ent2.num_unidad, ent2.clave_transportadora, ent2.placa) FROM ($sql1) as ent2)";


          $sql = $sql1." UNION ".$sql2;




        $query = mysqli_query(\db2(), $sql);
        //$datos = mysqli_fetch_array($query, MYSQLI_ASSOC);
        $rows = array();
        while(($row = mysqli_fetch_array($query, MYSQLI_ASSOC))) 
        {
            $rows[] = $row;
            $datos = $row;
        }

        //$cuadro7 = array('LP', 'PARTIDA','CONCEPTO','CANTIDAD','U.M.','P.U.','SUBTOTAL');
        $cuadro7 = array('Clave Proveedor', 'Nombre Proveedor', 'Folio OC', 'Factura OC', 'Folio Entrada', 'Factura Entrada', 'Factura Articulo', 'License Plate', 'Clave Articulo', 'Clave Alterna', 'Descripcion Articulo', 'Lote|Serie', 'Caducidad', 'Tipo de Protocolo', 'Grupo de Artículo', 'Cantidad', 'UM artículo', 'Fecha de Registro', 'Hora de Registro', 'Estado Serial', 'Valor Unitario (Costo por Unidad)', 'Valor Total (Costo Total)', 'Proyecto', 'Ubicación', 'Usuario', 'Observaciones', 'Numero de Unidad', 'Clave transportadora', 'Placa', 'Sello/Precinto', 'ID Chofer', 'Nombre Conductor', 'Fecha de Transporte', 'Hora de Transporte', 'Declaración Importación', 'Documento de Transporte Internacional', 'Destino/Dirección', 'DO');

        $excel = new XLSXWriter();
        $excel->writeSheetRow("Reporte Entrada #{$folio}", $cuadro7 );
        foreach($rows as $row)
        {
            //$row = array($row["LP"], $row["cve_articulo"],$row["des_articulo"], number_format($row["cantidad"], 2),$row["umas"], $row["costo"],$row["subtotal"]);
            $row = array($row["clave_proveedor"], $row["nombre_proveedor"], $row["folio_oc"], $row["factura_oc"], $row["folio_entrada"], $row["factura_entrada"], $row["factura_articulo"], $row["LP"], $row["clave_articulo"], $row["clave_alterna"], $row["des_articulo"], (($row["lote"] != '')?($row["lote"]):($row["serie"])), $row["Caducidad"], $row["tipo_de_protocolo"], $row["grupo_articulo"], $row["cantidad"], $row["um_articulo"], $row["fecha_entrada"], $row["hora_entrada"], $row["estado_serial"], $row["valor_unitario"], $row["valor_total"], $row["Proyecto"], $row["ubicacion"], $row["usuario"], $row["Observaciones"], $row["num_unidad"], $row["clave_transportadora"], $row["placa"], $row["Sello"], $row["id_chofer"], $row["nombre_conductor"], $row["fecha_transporte"], $row["hora_transporte"], $row["declaracion_importacion"], $row["documento_transporte_internacional"], $row["destino_direccion"], $row["DO"]);
            $excel->writeSheetRow("Reporte Entrada #{$folio}", $row );
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $title . '"');
        header('Cache-Control: max-age=0');
        $excel->writeToStdOut($title);
}

//Estudiar si debo colocar el enlace con GET
if(isset($_GET) && !empty($_GET) && $_GET['action'] === 'exportExcelReporteEntradas')
{
    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $folio = $_GET['folio'];
    $title = "Reporte Entrada #{$folio}.xlsx";

  $sql1 = "SELECT  
             p.cve_proveedor AS clave_proveedor, p.Nombre AS nombre_proveedor, tr.folio_oc AS folio_oc, tr.factura_oc AS factura_oc, 
             tr.folio_entrada AS folio_entrada, tr.factura_ent AS factura_entrada, IFNULL(dt.factura_articulo, '') AS factura_articulo, IFNULL(ch.CveLP, '') AS LP, 
             tr.cve_articulo AS clave_articulo, a.cve_alt AS clave_alterna, a.des_articulo AS des_articulo, #IFNULL(tr.cve_lote, '') AS lote, 
             IFNULL(lt.lote, '') AS lote, IFNULL(s.numero_serie, '') AS serie,
             IFNULL(lt.Caducidad, '') AS Caducidad, prot.descripcion AS tipo_de_protocolo, 
             ga.des_gpoart AS grupo_articulo, (tr.cantidad) AS cantidad, um.des_umed AS um_articulo, 
             DATE_FORMAT(ent.Fec_Entrada, '%d-%m-%Y') AS fecha_entrada, DATE_FORMAT(ent.Fec_Entrada, '%H:%i:%S') AS hora_entrada,
             '' AS estado_serial, dt_oc.costo AS valor_unitario, dt_oc.costo*(tr.cantidad) AS valor_total, tr.proyecto AS Proyecto, u.CodigoCSD AS ubicacion, ent.Cve_Usuario AS usuario,
             etr.Observaciones, etr.No_Unidad AS num_unidad, etr.Linea_Transportista AS clave_transportadora, etr.Placas AS placa, etr.Sello, etr.Id_Operador AS id_chofer,
             etr.Operador AS nombre_conductor, DATE_FORMAT(etr.Fec_Ingreso, '%d-%m-%Y') AS fecha_transporte, DATE_FORMAT(etr.Fec_Ingreso, '%H:%i:%S') AS hora_transporte,
             '' AS declaracion_importacion, '' AS documento_transporte_internacional, '' AS destino_direccion, '' AS DO
          FROM t_trazabilidad_existencias tr
          LEFT JOIN c_articulo a ON a.cve_articulo = tr.cve_articulo
          LEFT JOIN th_entalmacen ent ON ent.id_ocompra = tr.folio_oc AND tr.folio_entrada = ent.Fol_Folio
          LEFT JOIN td_entalmacen dt ON dt.Fol_folio = tr.folio_entrada AND tr.cve_articulo = dt.cve_articulo AND IFNULL(tr.cve_lote, '') = IFNULL(dt.cve_lote, '')
          LEFT JOIN c_lotes lt ON lt.cve_articulo = tr.cve_articulo AND IFNULL(TRIM(lt.lote), '') = IFNULL(TRIM(tr.cve_lote), '') AND a.control_lotes = 'S'
          LEFT JOIN c_serie s ON s.cve_articulo = tr.cve_articulo AND IFNULL(TRIM(s.numero_serie), '') = IFNULL(TRIM(tr.cve_lote), '') AND a.control_numero_series = 'S'
          LEFT JOIN c_proveedores p ON ent.Cve_Proveedor = p.ID_Proveedor
          LEFT JOIN t_protocolo prot ON prot.ID_Protocolo = ent.ID_Protocolo
          LEFT JOIN c_gpoarticulo ga ON ga.cve_gpoart = a.grupo
          LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
          LEFT JOIN t_entalmacentransporte etr ON etr.Fol_Folio = tr.folio_entrada
          LEFT JOIN c_charolas ch ON ch.IDContenedor = tr.ntarima
          LEFT JOIN td_aduana dt_oc ON dt_oc.num_orden = tr.folio_oc AND tr.cve_articulo = dt_oc.cve_articulo AND IFNULL(tr.ntarima, '') = IFNULL(ch.IDContenedor, '') AND tr.idy_ubica IS NOT NULL AND IFNULL(dt_oc.cve_lote, dt.cve_lote) = IFNULL(tr.cve_lote, '')
          LEFT JOIN c_ubicacion u ON tr.idy_ubica = u.idy_ubica
          WHERE tr.folio_entrada = $folio
          #AND tr.idy_ubica IS NOT NULL 
          AND tr.id_tipo_movimiento = 2
          GROUP BY clave_proveedor, LP, clave_articulo, lote, serie, ubicacion";

  $sql2 = "SELECT * FROM(
            SELECT  
             p.cve_proveedor AS clave_proveedor, p.Nombre AS nombre_proveedor, tr.folio_oc AS folio_oc, tr.factura_oc AS factura_oc, 
             tr.folio_entrada AS folio_entrada, tr.factura_ent AS factura_entrada, IFNULL(dt.factura_articulo, '') AS factura_articulo, IFNULL(ch.CveLP, '') AS LP, 
             tr.cve_articulo AS clave_articulo, a.cve_alt AS clave_alterna, a.des_articulo AS des_articulo, #IFNULL(tr.cve_lote, '') AS lote, 
             IFNULL(lt.lote, '') AS lote, IFNULL(s.numero_serie, '') AS serie,
             IFNULL(lt.Caducidad, '') AS Caducidad, prot.descripcion AS tipo_de_protocolo, 
             ga.des_gpoart AS grupo_articulo, (tr.cantidad) AS cantidad, um.des_umed AS um_articulo, 
             DATE_FORMAT(ent.Fec_Entrada, '%d-%m-%Y') AS fecha_entrada, DATE_FORMAT(ent.Fec_Entrada, '%H:%i:%S') AS hora_entrada,
             '' AS estado_serial, dt_oc.costo AS valor_unitario, dt_oc.costo*(tr.cantidad) AS valor_total, tr.proyecto AS Proyecto, u.CodigoCSD AS ubicacion, ent.Cve_Usuario AS usuario,
             etr.Observaciones, etr.No_Unidad AS num_unidad, etr.Linea_Transportista AS clave_transportadora, etr.Placas AS placa, etr.Sello, etr.Id_Operador AS id_chofer,
             etr.Operador AS nombre_conductor, DATE_FORMAT(etr.Fec_Ingreso, '%d-%m-%Y') AS fecha_transporte, DATE_FORMAT(etr.Fec_Ingreso, '%H:%i:%S') AS hora_transporte,
             '' AS declaracion_importacion, '' AS documento_transporte_internacional, '' AS destino_direccion, '' AS DO
          FROM t_trazabilidad_existencias tr
          LEFT JOIN c_articulo a ON a.cve_articulo = tr.cve_articulo
          LEFT JOIN th_entalmacen ent ON ent.id_ocompra = tr.folio_oc AND tr.folio_entrada = ent.Fol_Folio
          LEFT JOIN td_entalmacen dt ON dt.Fol_folio = tr.folio_entrada AND tr.cve_articulo = dt.cve_articulo AND IFNULL(tr.cve_lote, '') = IFNULL(dt.cve_lote, '')
          LEFT JOIN c_lotes lt ON lt.cve_articulo = tr.cve_articulo AND IFNULL(TRIM(lt.lote), '') = IFNULL(TRIM(tr.cve_lote), '') AND a.control_lotes = 'S'
          LEFT JOIN c_serie s ON s.cve_articulo = tr.cve_articulo AND IFNULL(TRIM(s.numero_serie), '') = IFNULL(TRIM(tr.cve_lote), '') AND a.control_numero_series = 'S'
          LEFT JOIN c_proveedores p ON ent.Cve_Proveedor = p.ID_Proveedor
          LEFT JOIN t_protocolo prot ON prot.ID_Protocolo = ent.ID_Protocolo
          LEFT JOIN c_gpoarticulo ga ON ga.cve_gpoart = a.grupo
          LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
          LEFT JOIN t_entalmacentransporte etr ON etr.Fol_Folio = tr.folio_entrada
          LEFT JOIN c_charolas ch ON ch.IDContenedor = tr.ntarima
          LEFT JOIN td_aduana dt_oc ON dt_oc.num_orden = tr.folio_oc AND tr.cve_articulo = dt_oc.cve_articulo AND IFNULL(tr.ntarima, '') = IFNULL(ch.IDContenedor, '') AND tr.idy_ubica IS NOT NULL AND IFNULL(dt_oc.cve_lote, dt.cve_lote) = IFNULL(tr.cve_lote, '')
          LEFT JOIN c_ubicacion u ON tr.idy_ubica = u.idy_ubica
          WHERE tr.folio_entrada = $folio
          #AND tr.idy_ubica IS NOT NULL 
          AND tr.id_tipo_movimiento = 1
          GROUP BY clave_proveedor, LP, clave_articulo, lote, serie, ubicacion) as ent
          WHERE CONCAT(ent.clave_proveedor, ent.folio_oc, ent.factura_oc, ent.folio_entrada, ent.factura_entrada, ent.LP, ent.clave_articulo, ent.clave_alterna, ent.lote, ent.serie, ent.um_articulo, ent.Proyecto, ent.usuario, ent.num_unidad, ent.clave_transportadora, ent.placa) NOT IN (SELECT CONCAT(ent2.clave_proveedor, ent2.folio_oc, ent2.factura_oc, ent2.folio_entrada, ent2.factura_entrada, ent2.LP, ent2.clave_articulo, ent2.clave_alterna, ent2.lote, ent2.serie, ent2.um_articulo, ent2.Proyecto, ent2.usuario, ent2.num_unidad, ent2.clave_transportadora, ent2.placa) FROM ($sql1) as ent2)";


          $sql = $sql1." UNION ".$sql2;




        $query = mysqli_query(\db2(), $sql);
        //$datos = mysqli_fetch_array($query, MYSQLI_ASSOC);
        $rows = array();
        while(($row = mysqli_fetch_array($query, MYSQLI_ASSOC))) 
        {
            $rows[] = $row;
            $datos = $row;
        }

        //$cuadro7 = array('LP', 'PARTIDA','CONCEPTO','CANTIDAD','U.M.','P.U.','SUBTOTAL');
        $cuadro7 = array('Clave Proveedor', 'Nombre Proveedor', 'Folio OC', 'Factura OC', 'Folio Entrada', 'Factura Entrada', 'Factura Articulo', 'License Plate', 'Clave Articulo', 'Clave Alterna', 'Descripcion Articulo', 'Lote|Serie', 'Caducidad', 'Tipo de Protocolo', 'Grupo de Artículo', 'Cantidad', 'UM artículo', 'Fecha de Registro', 'Hora de Registro', 'Estado Serial', 'Valor Unitario (Costo por Unidad)', 'Valor Total (Costo Total)', 'Proyecto', 'Ubicación', 'Usuario', 'Observaciones', 'Numero de Unidad', 'Clave transportadora', 'Placa', 'Sello/Precinto', 'ID Chofer', 'Nombre Conductor', 'Fecha de Transporte', 'Hora de Transporte', 'Declaración Importación', 'Documento de Transporte Internacional', 'Destino/Dirección', 'DO');

        $excel = new XLSXWriter();
        $excel->writeSheetRow("Reporte Entrada #{$folio}", $cuadro7 );
        foreach($rows as $row)
        {
            //$row = array($row["LP"], $row["cve_articulo"],$row["des_articulo"], number_format($row["cantidad"], 2),$row["umas"], $row["costo"],$row["subtotal"]);
            $row = array($row["clave_proveedor"], $row["nombre_proveedor"], $row["folio_oc"], $row["factura_oc"], $row["folio_entrada"], $row["factura_entrada"], $row["factura_articulo"], $row["LP"], $row["clave_articulo"], $row["clave_alterna"], $row["des_articulo"], (($row["lote"] != '')?($row["lote"]):($row["serie"])), $row["Caducidad"], $row["tipo_de_protocolo"], $row["grupo_articulo"], $row["cantidad"], $row["um_articulo"], $row["fecha_entrada"], $row["hora_entrada"], $row["estado_serial"], $row["valor_unitario"], $row["valor_total"], $row["Proyecto"], $row["ubicacion"], $row["usuario"], $row["Observaciones"], $row["num_unidad"], $row["clave_transportadora"], $row["placa"], $row["Sello"], $row["id_chofer"], $row["nombre_conductor"], $row["fecha_transporte"], $row["hora_transporte"], $row["declaracion_importacion"], $row["documento_transporte_internacional"], $row["destino_direccion"], $row["DO"], $sql);
            $excel->writeSheetRow("Reporte Entrada #{$folio}", $row );
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $title . '"');
        header('Cache-Control: max-age=0');
        $excel->writeToStdOut($title);
}


if($_POST['action'] == 'cargarFotosTH' ) 
{

  $folio = $_POST['folio'];
  $sql = "SELECT id, th_entalmacen_folio, ruta, descripcion, type, foto FROM th_entalmacen_fotos WHERE th_entalmacen_folio = $folio";

  $query = mysqli_query(\db2(), $sql);

  $imagenes = "";
  while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
  {
        //echo $row['ruta'].'!***********!'.$row['descripcion'];
        $imagenes .= "<div class='col-xs-4' style='margin: 20px 0;'> <img src='../".$row['ruta']."' width='100%' height='100px' /><br><b>".utf8_encode($row['descripcion'])."</b>&nbsp;&nbsp;<a href='#' onclick='eliminar_foto_th(".$row['id'].")'><i class='fa fa-times' style='color: #F00;' title='eliminar'></i></a></div>";
  }
  echo $imagenes;
}

if($_POST['action'] == 'cargarFotosTD' ) 
{
//SELECT DISTINCT descripcion FROM td_entalmacen_fotos WHERE td_entalmacen_producto_id = 1138
  $id = $_POST['id'];
  $sql = "SELECT id, td_entalmacen_producto_id, ruta, descripcion, type, foto FROM td_entalmacen_fotos WHERE td_entalmacen_producto_id = $id";

  $query = mysqli_query(\db2(), $sql);

  $imagenes = "";
  while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
  {
        //echo $row['ruta'].'!***********!'.$row['descripcion'];
        $imagenes .= "<div class='col-xs-4' style='margin: 20px 0;'> <img src='../".$row['ruta']."' width='100%' height='100px' /><a href='#' onclick='eliminar_foto_td(".$row['id'].")'><i class='fa fa-times' style='color: #F00;' title='eliminar'></i></a></div>";
  }
  echo $imagenes;
}

if($_POST['action'] == 'DescripcionDefectoTD' ) 
{
  $id = $_POST['id'];
  $sql = "SELECT DISTINCT descripcion FROM td_entalmacen_fotos WHERE td_entalmacen_producto_id = $id";

  $query = mysqli_query(\db2(), $sql);

  $row = mysqli_fetch_array($query, MYSQLI_ASSOC);

  echo $row['descripcion'];
}

if($_POST['action'] == 'eliminarFotosTH' ) 
{

  $id = $_POST['id'];
  $sql = "DELETE FROM th_entalmacen_fotos WHERE id = $id";
  $query = mysqli_query(\db2(), $sql);
}

if($_POST['action'] == 'eliminarFotosTD' ) 
{

  $id = $_POST['id'];
  $sql = "DELETE FROM td_entalmacen_fotos WHERE id = $id";
  $query = mysqli_query(\db2(), $sql);
}

if($_POST['action'] == 'getLP' ) 
{
  $folio = $_POST['folio'];
  $sql = "SELECT DISTINCT ClaveEtiqueta FROM td_entalmacenxtarima WHERE fol_folio = $folio";
  $query = mysqli_query(\db2(), $sql);

  $lp = "";
  while($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
    $lp .= "<option value='".$row["ClaveEtiqueta"]."'>".$row["ClaveEtiqueta"]."</option>";
  //$lp .= '<option value='."'".$row["ClaveEtiqueta"]."''".'>'.$row["ClaveEtiqueta"].'</option>';

  $arr = array(
    "lp" => $lp
  );
  echo json_encode($arr);
  //echo $lp;
}

if($_POST['action'] == 'ConectarSAP' ) 
{
  $endPoint = '';
  $json = '';
  
  $funcion  = $_POST['funcion'];
  $metodo   = $_POST['metodo'];
  $folio_oc = $_POST['folio_oc'];
  
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//***********************************************************************************************************
  //$sql = "SET NAMES 'utf8mb4';";
  //$res = mysqli_query($conn, $sql);

/*****************************************************************************************************
//**************************************** REGISTRAR EN LOG *******************************************
//*****************************************************************************************************
  $sql = "INSERT INTO t_log_sap(fecha, cadena, respuesta, modulo, folio, funcion) VALUES (NOW(), 'Funcion: {$funcion}, Metodo: {$metodo}','', 'Connect SAP en Entradas', '{$folio_oc}', 'Connect')";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
  }
//*****************************************************************************************************/

  $sql = "SELECT * FROM c_datos_sap WHERE Activo = 1;";
  //echo $sql;
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
  }

  $num_row = mysqli_num_rows($res);
  if($num_row == 1)
  {
    $row = mysqli_fetch_array($res);
    $endPoint = $row['Url'].$funcion;
    $usuario  = $row['User'];
    $password = $row['Pswd'];
    $BD       = $row['BaseD'];
  }
  else if($num_row != 0)
  {
    $sql = "SELECT * FROM c_datos_sap WHERE Empresa = (SELECT cve_proveedor FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = '{$folio_oc}')) AND Activo = 1;";
    if (!($res = mysqli_query($conn, $sql))) 
    {
      echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
    }
    //echo $sql;
    $row = mysqli_fetch_array($res);
    $endPoint = $row['Url'].$funcion;
    $usuario  = $row['User'];
    $password = $row['Pswd'];
    $BD       = $row['BaseD'];
  }
    $json = '{
    "CompanyDB": "'.$BD.'",
    "UserName": "'.$usuario.'",
    "Password": "'.$password.'"
    }';
//echo $json;

    $curl = curl_init();

    curl_setopt_array($curl, array(

  CURLOPT_URL => $endPoint,

  CURLOPT_RETURNTRANSFER => true,

  CURLOPT_ENCODING => '',

  CURLOPT_MAXREDIRS => 10,

  CURLOPT_TIMEOUT => 0,

  CURLOPT_FOLLOWLOCATION => true,

  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

  CURLOPT_CUSTOMREQUEST => $metodo,
 //rie
  CURLOPT_POSTFIELDS => $json,

  CURLOPT_HTTPHEADER => array(

    'Content-Type: text/plain',

    'Cookie: B1SESSION=e148fc02-6d94-11ec-8000-0a244a1700f3; ROUTEID=.node2'

  ),

  CURLOPT_SSL_VERIFYHOST => false,
  
  CURLOPT_SSL_VERIFYPEER => false,

));
//echo $curl;
$response = curl_exec($curl);
//echo $response;

 curl_close($curl);

  echo ($response);
}

if($_POST['action'] == 'EjecutarOCSAP' ) 
{
  $folio_oc           = $_POST['folio_oc'];
  $folio_entrada      = $_POST['folio_entrada'];
  $funcion            = $_POST['funcion'];
  $metodo             = $_POST['metodo'];

  $id_entrada = "";
  $enviar_sap = false;
  if(isset($_POST['id_entrada']))
  {
    $id_entrada         = $_POST['id_entrada'];
    $enviar_sap = true;
  }
  else if($_SERVER['HTTP_HOST'] == 'dicoisa.assistprowms.com')
  {
    $sql = "SELECT ID_Protocolo FROM th_aduana WHERE num_pedimento = '{$folio_oc}';";
    if (!($res = mysqli_query($conn, $sql))) 
    {
      echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
    }
    $row = mysqli_fetch_array($res);
    $ID_Protocolo = $row['ID_Protocolo'];

    if($ID_Protocolo == '01')
       $enviar_sap = true;
  }

if($enviar_sap == true)
{

  $pedimento_oc       = $_POST['pedimento_oc'];
  $fecha_pedimento_oc = $_POST['fecha_pedimento_oc'];
  $tipo_cambio        = $_POST['tipo_cambio'];

  $json = '{';

  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $sql = "SET NAMES 'utf8mb4';";
  $res = mysqli_query($conn, $sql);
  //****************************************************************************************************
  // ACTUALIZAR NUMERO PEDIMENTO, FECHA PEDIMENTO Y TIPO CAMBIO EN TH_ENTALMACEN Y TD_ENTALMACEN
  //****************************************************************************************************
    //$arrDetalle = json_decode($arrDetalle);
    //echo var_dump($arrDetalle); 
      $sql_update_sap = "UPDATE th_entalmacen SET TipoCambioSAP = '{$tipo_cambio}' WHERE Fol_Folio = '{$folio_entrada}';";
      if (!($res_sap = mysqli_query($conn, $sql_update_sap))) 
      {
        echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
      }

    if(isset($_POST['id_entrada']))
    {
      for($i = 0; $i < count($id_entrada); $i++)
      {
        $num_p   = $pedimento_oc[$i];
        $fecha_p = $fecha_pedimento_oc[$i];
        $id      = $id_entrada[$i];

        $sql_update_sap = "UPDATE td_entalmacen SET num_pedimento = '{$num_p}', fecha_pedimento = '{$fecha_p}' WHERE id = '{$id}';";
        if (!($res_sap = mysqli_query($conn, $sql_update_sap))) 
        {
          echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
        }
      }
    }
    //echo var_dump($arrDetalle);
  //****************************************************************************************************/

  $sql = "SELECT * FROM c_datos_sap WHERE Empresa = (SELECT cve_proveedor FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = '{$folio_oc}')) AND Activo = 1;";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
  }
  $row = mysqli_fetch_array($res);
  $endPoint = $row['Url'].$funcion;

//***********************************************************************************************************
  $sql = "SELECT DATE_FORMAT(fech_pedimento, '%Y-%m-%d') AS fech_pedimento, procedimiento, Factura, recurso, dictamen FROM th_aduana WHERE num_pedimento = '{$folio_oc}';";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
  }
  $row = mysqli_fetch_array($res);
  $fech_pedimento = $row['fech_pedimento'];
  $procedimiento  = $row['procedimiento'];
  $Factura        = $row['Factura'];
  $recurso        = $row['recurso'];
  $dictamen       = $row['dictamen'];

  $v_fact = explode("_", $Factura);

  if(count($v_fact) > 1)
    $Factura = $v_fact[1];

  //***********************************************************************************************************
  //$sql = "SELECT DATE_FORMAT(HoraInicio, '%Y-%m-%d') AS HoraInicio, fol_folio FROM th_entalmacen WHERE id_ocompra = '{$folio_oc}';";
  $sql = "SELECT DISTINCT DATE_FORMAT(MAX(fecha_fin), '%Y-%m-%d') AS HoraInicio, fol_folio, (SELECT TipoCambioSAP FROM th_entalmacen WHERE Fol_Folio = '{$folio_entrada}') as t_cambio FROM td_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM th_entalmacen WHERE id_ocompra = '{$folio_oc}')";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
  }
  $row = mysqli_fetch_array($res);
  $HoraInicio = $row['HoraInicio'];
  $fol_folio  = $row['fol_folio'];
  $t_cambio   = $row['t_cambio'];

$json .= '"DocDate":"'.$HoraInicio.'","DocDueDate":"'.$HoraInicio.'", "CardCode":"'.$procedimiento.'", "DocRate":"'.$t_cambio.'", "DocType":"dDocument_Items",';//"DocObjectCode":"20" ,
$json .= '"DocumentLines":[';
//***********************************************************************************************************
  //$sql = "SELECT cve_articulo, SUM(CantidadRecibida) AS CantidadRecibida FROM td_entalmacen WHERE fol_folio = '{$fol_folio}' GROUP BY cve_articulo;";
  $sql = "SELECT sap.cve_articulo, SUM(sap.Cant_Rec) AS CantidadRecibida, sap.Item FROM td_entalmacen_enviaSAP sap WHERE sap.Fol_Folio = '{$fol_folio}' AND sap.Enviado = 0 GROUP BY cve_articulo ORDER BY Item";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ";
  }
  $i = 1;
  while($row = mysqli_fetch_array($res))
  {
    $cve_articulo = $row['cve_articulo'];
    $CantidadRecibida = $row['CantidadRecibida'];
    $Item = $row['Item'];
    $json .= '{';

    $json .= '"BaseEntry":"'.$dictamen.'","BaseType":"22","BaseLine":"'.$Item.'","ItemCode":"'.$cve_articulo.'","Quantity":"'.$CantidadRecibida.'","WarehouseCode":"'.$recurso.'", 
    "BatchNumbers":[';
    $i++;
//***********************************************************************************************************
    //$sql = "SELECT e.cve_articulo, IFNULL(e.cve_lote, '') AS cve_lote, SUM(e.CantidadRecibida) AS CantidadRecibida, c.Caducidad FROM td_entalmacen e LEFT JOIN c_lotes c ON c.Lote = e.cve_lote AND c.cve_articulo = e.cve_articulo WHERE e.fol_folio = '{$fol_folio}' AND e.cve_articulo = '{$cve_articulo}' GROUP BY cve_articulo, cve_lote;";
    $sql = "SELECT e.Id, e.Cve_Articulo AS cve_articulo, IFNULL(e.Cve_lote, '') AS cve_lote, SUM(e.Cant_Rec) AS CantidadRecibida, c.Caducidad, IF(IFNULL(a.num_pedimento, '') = '', e.Fol_Folio, a.num_pedimento) AS num_pedimento FROM td_entalmacen_enviaSAP e LEFT JOIN c_lotes c ON c.Lote = e.Cve_lote AND c.cve_articulo = e.Cve_Articulo LEFT JOIN td_entalmacen a ON a.cve_articulo = e.Cve_Articulo AND a.cve_lote = e.Cve_lote AND a.fol_folio = e.Fol_Folio WHERE e.fol_folio = '{$fol_folio}' AND e.cve_articulo = '{$cve_articulo}' AND e.Enviado = 0 GROUP BY cve_articulo, cve_lote";
    if (!($res2 = mysqli_query($conn, $sql))) 
    {
      echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
    }
    while($row2 = mysqli_fetch_array($res2))
    {
      $Id               = $row2['Id'];
      $cve_lote         = $row2['cve_lote'];
      $CantidadRecibida = $row2['CantidadRecibida'];
      $Caducidad        = $row2['Caducidad'];
      $num_pedimento    = $row2['num_pedimento'];
      $json .= '{"BatchNumber":"'.$cve_lote.'","Quantity":"'.$CantidadRecibida.'","ExpiryDate":"'.$Caducidad.'", "U_BXP_PEDIMENTO":"'.$num_pedimento.'"},';

      //**************************************************************************************
      //  DESHABILITAR AL MOSTRAR JSON
      /**************************************************************************************
      $sql_update_sap = "UPDATE td_entalmacen_enviaSAP SET Enviado = 1 WHERE Id = '{$Id}';";
      if (!($res_sap = mysqli_query($conn, $sql_update_sap))) 
      {
        echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
      }
      //**************************************************************************************/

    }
//***********************************************************************************************************
    $json[strlen($json)-1] = ' ';
    $json .= ']},';
  }
//***********************************************************************************************************
$json[strlen($json)-1] = ' ';
$json .= ']}';

//echo json_encode($json);
//echo $json;


//****************************************************************************************
//****************************************************************************************

  $sesion_id = $_POST['sesion_id'];
    $curl = curl_init();

    curl_setopt_array($curl, array(

  CURLOPT_URL => $endPoint,

  CURLOPT_RETURNTRANSFER => true,

  CURLOPT_ENCODING => '',

  CURLOPT_MAXREDIRS => 10,

  CURLOPT_TIMEOUT => 0,

  CURLOPT_FOLLOWLOCATION => true,

  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

  CURLOPT_CUSTOMREQUEST => $metodo,

  CURLOPT_POSTFIELDS =>$json,

  CURLOPT_HTTPHEADER => array(

    'Content-Type: text/plain',

    'Cookie: B1SESSION='.$sesion_id.'; ROUTEID=.node2'

  ),

  CURLOPT_SSL_VERIFYHOST => false,
  
  CURLOPT_SSL_VERIFYPEER => false,

));
//'Content-Type: text/plain',
//e148fc02-6d94-11ec-8000-0a244a1700f3
//application/json
$response = curl_exec($curl);

 curl_close($curl);

//*****************************************************************************************************
//**************************************** REGISTRAR EN LOG *******************************************
//*****************************************************************************************************
  $sql = "INSERT INTO t_log_sap(fecha, cadena, respuesta, modulo, folio, funcion) VALUES (NOW(), '{$json}','{$response}','Ejecutar OC SAP en Entradas', '{$fol_folio}', 'EjecutarOCSAP')";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
  }
//*****************************************************************************************************/


  echo $response;
//****************************************************************************************/
//****************************************************************************************

}else
  echo "NO";
}

if($_POST['action'] == 'getDetalleEntrada' ) 
{
  $folio_entrada = $_POST['folio_entrada'];
  // se conecta a la base de datos
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  //id, cve_articulo, cve_lote, num_pedimento, fecha_pedimento

  //$sql = "SELECT id, cve_articulo, cve_lote, num_pedimento, DATE_FORMAT(fecha_pedimento, '%Y-%m-%d') as fecha_pedimento from td_entalmacen where Fol_Folio = '$folio_entrada'";

    $sql = "SELECT DISTINCT e.id, e.cve_articulo, e.cve_lote, e.num_pedimento, 
                            DATE_FORMAT(e.fecha_pedimento, '%Y-%m-%d') AS fecha_pedimento 
            FROM td_entalmacen e
            INNER JOIN td_entalmacen_enviaSAP s ON s.Fol_Folio = e.fol_folio AND s.Cve_Articulo = e.cve_articulo AND s.Cve_lote = e.cve_lote AND s.Enviado = 0
            WHERE e.Fol_Folio = '$folio_entrada'";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }

  $datos = "";
  while($row = mysqli_fetch_array($res))
  {
    $id = $row['id'];
    $cve_articulo = $row['cve_articulo'];
    $cve_lote = $row['cve_lote'];
    $num_pedimento = $row['num_pedimento'];
    $fecha_pedimento = $row['fecha_pedimento'];

    $datos .= '<div class="row">
                  <input type="hidden" class="id_entrada datos_oc" readonly value="'.$id.'">
                  <div class="col-md-3">
                  <input type="text" class="form-control cve_articulo_oc datos_oc" readonly value="'.$cve_articulo.'">
                  </div>
                  <div class="col-md-3">
                  <input type="text" class="form-control cve_lote_oc datos_oc" readonly value="'.$cve_lote.'">
                  </div>
                  <div class="col-md-3">
                  <input type="text" class="form-control pedimento_oc datos_oc" placeholder="Pedimento..." value="'.$num_pedimento.'">
                  </div>
                  <div class="col-md-3">
                  <input type="date" class="form-control fecha_pedimento_oc datos_oc" placeholder="Pedimento..." value="'.$fecha_pedimento.'">
                  </div>
              </div>
              <br>';
  }


  $sql = "SELECT TipoCambioSAP from th_entalmacen WHERE Fol_Folio = '$folio_entrada'";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }

  $row = mysqli_fetch_array($res);
  $TipoCambioSAP = $row['TipoCambioSAP'];

  $arr = array();

  $arr = ["datos"=>$datos, "TipoCambioSAP"=>$TipoCambioSAP];
  echo json_encode($arr);
  //echo $datos;

}


if($_POST['action'] == 'CambiarAEnviadoOCSAP' ) 
{
  $folio_oc           = $_POST['folio_oc'];
  $folio_entrada      = $_POST['folio_entrada'];

  $json = '{';

  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $sql = "SELECT sap.cve_articulo, SUM(sap.Cant_Rec) AS CantidadRecibida FROM td_entalmacen_enviaSAP sap WHERE sap.Fol_Folio = '{$folio_entrada}' AND sap.Enviado = 0 GROUP BY cve_articulo";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ";
  }

  while($row = mysqli_fetch_array($res))
  {
    $cve_articulo = $row['cve_articulo'];
    $CantidadRecibida = $row['CantidadRecibida'];
//***********************************************************************************************************
    //$sql = "SELECT e.cve_articulo, IFNULL(e.cve_lote, '') AS cve_lote, SUM(e.CantidadRecibida) AS CantidadRecibida, c.Caducidad FROM td_entalmacen e LEFT JOIN c_lotes c ON c.Lote = e.cve_lote AND c.cve_articulo = e.cve_articulo WHERE e.fol_folio = '{$fol_folio}' AND e.cve_articulo = '{$cve_articulo}' GROUP BY cve_articulo, cve_lote;";
    $sql = "SELECT e.Id, e.Cve_Articulo AS cve_articulo, IFNULL(e.Cve_lote, '') AS cve_lote, SUM(e.Cant_Rec) AS CantidadRecibida, c.Caducidad, a.num_pedimento FROM td_entalmacen_enviaSAP e LEFT JOIN c_lotes c ON c.Lote = e.Cve_lote AND c.cve_articulo = e.Cve_Articulo LEFT JOIN td_entalmacen a ON a.cve_articulo = e.Cve_Articulo AND a.cve_lote = e.Cve_lote AND a.fol_folio = e.Fol_Folio WHERE e.fol_folio = '{$folio_entrada}' AND e.cve_articulo = '{$cve_articulo}' AND e.Enviado = 0 GROUP BY cve_articulo, cve_lote";
    if (!($res2 = mysqli_query($conn, $sql))) 
    {
      echo "Falló la preparación 4: (" . mysqli_error($conn) . ") ";
    }
    while($row2 = mysqli_fetch_array($res2))
    {
      $Id               = $row2['Id'];
      $cve_lote         = $row2['cve_lote'];
      $CantidadRecibida = $row2['CantidadRecibida'];
      $Caducidad        = $row2['Caducidad'];
      $num_pedimento    = $row2['num_pedimento'];

      //**************************************************************************************
      //  DESHABILITAR AL MOSTRAR JSON
      //**************************************************************************************
      $sql_update_sap = "UPDATE td_entalmacen_enviaSAP SET Enviado = 1 WHERE Id = '{$Id}';";
      if (!($res_sap = mysqli_query($conn, $sql_update_sap))) 
      {
        echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
      }
      //**************************************************************************************/

    }
//***********************************************************************************************************
  }
//***********************************************************************************************************
//****************************************************************************************

  echo 1;
//****************************************************************************************/
//****************************************************************************************
}


if($_POST['action'] == 'EditarTransporte' ) 
{
  $folio_ent            = trim($_POST['folio_ent']);
  $nombre_operador      = trim($_POST['nombre_operador']);
  $id_chofer            = trim($_POST['id_chofer']);
  $num_unidad           = trim($_POST['num_unidad']);
  $cve_transportadora   = trim($_POST['cve_transportadora']);
  $placa                = trim($_POST['placa']);
  $sello                = trim($_POST['sello']);
  $fecha_transp         = trim($_POST['fecha_transp']);
  $hora_transp          = trim($_POST['hora_transp']);
  $observaciones_transp = trim($_POST['observaciones_transp']);

  $data = 1;
  if($fecha_transp)
  {
     $arr = explode("-", $fecha_transp);
     if(count($arr) == 3)
      $fecha_transp = $arr[2]."-".$arr[1]."-".$arr[0];
     else 
     {
      $fecha_transp = '0000-00-00';
      $data = 2;
     }
  }

  if($hora_transp)
  {
     $arr = explode(":", $hora_transp);
     if(count($arr) != 3)
     {
      $hora_transp = '00:00:00';
      $data = 2;
     }
  }

  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $sql = "UPDATE t_entalmacentransporte SET Operador = '$nombre_operador', 
                                            No_Unidad = '$num_unidad', 
                                            Placas = '$placa', 
                                            Linea_Transportista = '$cve_transportadora', 
                                            Observaciones = '$observaciones_transp', 
                                            Sello = '$sello', 
                                            Fec_Ingreso = DATE_FORMAT('$fecha_transp $hora_transp', '%Y-%m-%d %H:%i:%S'), 
                                            Id_Operador = '$id_chofer'
          WHERE Fol_Folio = '$folio_ent'";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ";
    exit;
  }

  echo $data;
//****************************************************************************************/
//****************************************************************************************
}


if($_POST['action'] == 'EditarDatos' ) 
{
  $folio_oc         = trim($_POST['folio_oc']);
  $folio_ent        = trim($_POST['folio_ent']);
  $tipo             = trim($_POST['tipo']);
  $pedimentoW_edit  = trim($_POST['pedimentoW_edit']);
  $referenciaW_edit = trim($_POST['referenciaW_edit']);


  $data = 1;
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


  $sql = "SELECT COUNT(*) as existe, num_pedimento FROM th_aduana WHERE num_pedimento IN (SELECT id_ocompra FROM th_entalmacen WHERE fol_folio = {$folio_ent})";
  if (!($res = mysqli_query($conn, $sql))) {echo json_encode(array( "error" => "02Error al procesar la petición: (" . mysqli_error($conn) . ") "));}
  $row_tiene_oc = mysqli_fetch_array($res);
  $tiene_oc = $row_tiene_oc['existe'];
  $folio_oc = $row_tiene_oc['num_pedimento'];

  $sql = "UPDATE th_entalmacen SET Referencia_Well = '$referenciaW_edit', 
                               Pedimento_Well = '$pedimentoW_edit' 
          WHERE Fol_Folio = '$folio_ent'";
  if($tipo == 'OC' || $tiene_oc)
    $sql = "UPDATE th_aduana SET recurso = '$referenciaW_edit', 
                                 Pedimento = '$pedimentoW_edit' 
            WHERE num_pedimento = '$folio_oc'";

  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ".$sql;
    exit;
  }

  echo $data;
//****************************************************************************************/
//****************************************************************************************
}

if($_POST['action'] == 'EditarDatosProyecto' ) 
{
  $folio_oc         = trim($_POST['folio_oc']);
  $folio_ent        = trim($_POST['folio_ent']);
  $proyecto         = trim($_POST['proyecto']);

  $data = 1;
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


  $sql = "UPDATE t_trazabilidad_existencias SET proyecto = '$proyecto' WHERE folio_entrada = '$folio_ent'";

  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ".$sql;
    exit;
  }
  $sql = "UPDATE th_entalmacen SET Proyecto = '$proyecto' WHERE Fol_Folio = '$folio_ent'";

  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 3: (" . mysqli_error($conn) . ") ".$sql;
    exit;
  }

  echo $data;
//****************************************************************************************/
//****************************************************************************************
}
