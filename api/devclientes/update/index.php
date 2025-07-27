<?php
include '../../../app/load.php';
// Initalize Slim
$app = new \Slim\Slim();
if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {exit();}

$ga = new \OrdenCompra\OrdenCompra();

class apiCall
{
  private $result = array(
    "success" => false,
    "error"   => "",
  );


  function __construct()
  {

  }
  
  public function action($data=[])
  {
    if(method_exists($this,$data["action"]))
    {
      $this->{$data["action"]}($data);
      echo json_encode($this->result);
    }
  }
  
  
  private function load($data)
  {
    $ordenCompra = new \OrdenCompra\OrdenCompra();
    $ordenCompra->ID_Aduana = $data["codigo"];
    $ordenCompra->__get("ID_Aduana");
    foreach ($ordenCompra->data as $k => $v)
    {
      $this->result[$k] = $v;
    }
    $ordenCompra->__getDetalle("ID_Aduana");
    $this->result["detalle"] = $ordenCompra->dataDetalle;
    $this->result["sinCompletar"] = $ordenCompra->dataDetalle2;
    $this->result["success"] = true;
  }
  
  
}

$apiCallInstance = new apiCall();
$apiCallInstance->action($_POST);



if( $_POST['action'] == 'load')
{
  $ga->ID_Aduana = $_POST["codigo"];
  $ga->__get("ID_Aduana");
  $arr = array(
        "success" => true,
  );
  $ga->__getDetalle("ID_Aduana");
  foreach ($ga->data as $nombre => $valor) $arr2[$nombre] = $valor;
  $arr2["detalle"] = $ga->dataDetalle;
  $arr2["sinCompletar"] = $ga->dataDetalle2;
  $arr = array_merge($arr, $arr2);
  echo json_encode($arr);
}



if($_POST['action'] === 'receiveOC')
{
  $ga->calcularCostoPromedio($_POST);
  $result = $ga->receiveOC($_POST);
  echo json_encode(array(
    //"success" => $result
    "success" => true
  ));
}

if($_POST['action'] === 'guardarEntradaLibre'){
  $ga->calcularCostoPromedio($_POST);
  $ga->guardarEntradaLibre($_POST);
  $arr = array("success" => true);
  echo json_encode($arr);
}

if( $_POST['action'] == 'add' )
{
    $ga->ID_Aduana = $_POST["num_pedimento"];
    $ga->__get("ID_Aduana");
    $success = true;
  
    if (!empty($ga->data->ID_Aduana)) {
      $success = false;
    }
    $arr = array(
      "success" => $success,
      "err" => "El Número del Folio ya se Ha Introducido"
    );
    if (!$success) {
      echo json_encode($arr);
      exit();
    }


    $factura = $_POST["factura"];

    if($ga->VerificarFacturaOC_ERP_Repetido($factura))
    {
       $ga->save($_POST);
       echo json_encode($arr);
    }
    else 
    {

      $arr = array(
        "success" => false,
        "err" => "El Numero de Orden (ERP) ya está usado"
      );
      echo json_encode($arr);
      //echo "existe_factura_true";
      exit();
    }

} 

if( $_POST['action'] == 'edit' )
{
  $ga->actualizarOrden($_POST);
  $arr = array(
    "success" => true,
    "err" => ""
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'exists' )
{
  $clave=$ga->exist($_POST["num_pedimento"]);

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
  $ga->borrarCliente($_POST);
  $ga->Cve_Clte = $_POST["Cve_Clte"];
  $ga->__get("Cve_Clte");
  $arr = array(
      "success" => true,
  );
  echo json_encode($arr);
}



if( $_POST['action'] == 'load2' )
{
  $ga->load2($_POST["codigo"]);
  echo json_encode($ga->data);
}
if( $_POST['action'] == 'getAlmacen' )
{
  $data=$ga->getAllProv($_POST["almacen"]);
  $arr = array(
    "success" => true,
    "oc" => $data
  );
	echo json_encode($arr);
}
 
if( $_POST['action'] == 'editando' ) {
  $ga->modoEdicion($_POST["codigo"],$_POST["status"],$_POST['id_user']);
}

if( $_POST['action'] == 'getConsecutivo' )
{
  $consecutivo=$ga->consecutivo($_POST["ID_Protocolo"]);
  $arr = array(
      "success" => true,
      "consecutivo" => $consecutivo
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'folioConsecutico' )
{
  $consecutivo=$ga->folioConsecutico();
  $arr = array(
      "success" => true,
      "folioConsecutivo" => $consecutivo["Fol_Folio"]
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'getArticulo' ) {
  $data=$ga->getArticulo($_POST["codigo"],$_POST["articulo"]);
 // echo var_dump($data);
 // die();
  if ($data)
  {
    $arr = array(
      "success" => true,
      "consecutivo" => $consecutivo,
      "des_articulo" => $data->des_articulo,
      "cantidad" => $data->cantidad,
      "costo" => $data->costo
    );
  } 
  else
  {
    $arr = array(
      "success" => false,
      "consecutivo" => $consecutivo,
      "cantidad" =>  $data->cantidad
    );
  }
  echo json_encode($arr);
  
}

if( $_POST['action'] == 'getArticuloLibre' ) {
  $data=$ga->getArticuloLibre($_POST["articulo"]);

  $arr = array(
    "success" => true,
    "descripcion_articulo" => $data["des_articulo"],
    "clave_articulo" => $data["cve_articulo"]
  );
	echo json_encode($arr);
}

if( $_POST['action'] == 'getxfecha' )
{
  $data=$ga->getFecha($_POST["fecha"]);
  if ($data)
  {
    $arr = array(
      "success" => true,
      "consecutivo" => $consecutivo,
      "compras" => $data
    );
  }
  else
  {
    $arr = array(
      "success" => false,
      "consecutivo" => $consecutivo,
      "compras" => $data
    );
  }
  
  echo json_encode($arr);
}

if( $_POST['action'] == 'ERP' )
{
  $almacen = $_POST['almacen'];
  $sql = "
    SELECT  a.num_pedimento,
      a.ID_Aduana,
      a.factura,
      c_proveedores.Nombre
    FROM th_aduana a
    LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = a.ID_Proveedor
    LEFT JOIN c_almacenp p ON a.Cve_Almac=p.clave 
    WHERE (a.status = 'C' OR a.status = 'I' ) AND p.clave='$almacen';
    ";
  $res = getArraySQL($sql);
  $array = [
    "res"=>$res
  ];
  echo json_encode($array);
}

if( $_POST['action'] == 'pedidos' )
{
  $almacen = $_POST['almacen'];
  $id_user = $_POST['id_user'];
  $cve_proveedor = $_POST['cve_proveedor'];
  $tipo_pedido   = $_POST['tipo_pedido'];

  $sql_Proveedor_login = "";
  $sql_Proveedor_pedido = "";

  if($cve_proveedor)
  {
      $sql_Proveedor_login = " AND c.ID_Proveedor = '{$cve_proveedor}' ";
      $sql_Proveedor_pedido = " AND a.Cve_clte IN (SELECT Cve_Clte FROM c_cliente WHERE ID_Proveedor = '{$cve_proveedor}') ";
  }

  $sql = "
    SELECT  a.Fol_folio, a.Pick_Num AS Factura
    FROM th_pedido a
    LEFT JOIN c_almacenp p ON a.cve_almac=p.id 
    WHERE (a.status = 'F' OR a.status = 'T') AND p.clave='$almacen';
    ";
  $res = getArraySQL($sql);

  $sql = "
    SELECT  DISTINCT a.Cve_clte, c.RazonSocial
    FROM th_pedido a
    LEFT JOIN c_cliente c ON c.Cve_Clte = a.Cve_clte
    LEFT JOIN c_almacenp p ON a.cve_almac=p.id 
    WHERE (a.status = 'F' OR a.status = 'T') AND p.clave='$almacen' AND c.ID_Proveedor IS NOT NULL AND c.ID_Proveedor != '' AND c.ID_Proveedor != 0 
    {$sql_Proveedor_login};
    ";
  $res_clientes = getArraySQL($sql);

  $sql = "
    SELECT  DISTINCT a.Pick_Num AS Factura
    FROM th_pedido a
    LEFT JOIN c_cliente c ON c.Cve_Clte = a.Cve_clte
    LEFT JOIN c_almacenp p ON a.cve_almac=p.id 
    WHERE (a.status = 'F' OR a.status = 'T') AND p.clave='$almacen' AND c.ID_Proveedor IS NOT NULL AND c.ID_Proveedor != '' AND c.ID_Proveedor != 0 AND a.Pick_Num != '' {$sql_Proveedor_login};
    ";
  $res_factura = getArraySQL($sql);

  $sql = "
    SELECT DISTINCT t.cve_ubicacion, t.desc_ubicacion
    FROM tubicacionesretencion t
    LEFT JOIN c_almacenp p ON t.cve_almacp=p.id 
    WHERE t.Activo = 1 AND p.clave='$almacen';
  ";
  $res_recepcion = getArraySQL($sql);

  $sql = "
    SELECT DISTINCT t.ID_Ruta, t.cve_ruta, t.descripcion 
    FROM Descarga d
    LEFT JOIN t_ruta t ON t.ID_Ruta = d.IdRuta AND t.venta_preventa = 1 AND d.Cantidad > 0 
    LEFT JOIN c_almacenp a ON a.id = t.cve_almacenp AND a.clave = d.IdEmpresa
    WHERE t.cve_ruta IS NOT NULL AND a.clave = '$almacen'
    ";
    //{$sql_Proveedor_login}
  $res_rutas = getArraySQL($sql);

/*
  $sql = "
      SELECT DISTINCT
        ch.IDContenedor,
        IF(ch.TipoGen = 1, CONCAT(ch.descripcion, ' (Pallet Genérico)'), ch.descripcion) AS descripcion,
        ch.clave_contenedor AS clave_contenedor,
        ch.CveLP,
        IF(ch.TipoGen = 1, 'S', 'N') AS Generico
      FROM c_charolas ch
      INNER JOIN c_almacenp ON c_almacenp.clave = ch.cve_almac
      WHERE ((ch.Activo = 1 AND ch.IDContenedor NOT IN (SELECT ntarima FROM t_tarima WHERE Abierta = 0) 
            AND (ch.clave_contenedor NOT IN (SELECT ClaveEtiqueta FROM td_entalmacenxtarima) AND ch.CveLP NOT IN (SELECT ClaveEtiqueta FROM td_entalmacenxtarima))) OR ch.TipoGen = 1) AND c_almacenp.clave='$almacen'
      GROUP BY ch.IDContenedor  
      ORDER BY Generico DESC
";
  $res_pallets = getArraySQL($sql);
*/
  $sql = "SELECT id_umed, cve_umed, des_umed FROM c_unimed WHERE Activo = 1";
  $res_unidad_medida = getArraySQL($sql);

  $sql = "
    SELECT (IFNULL(MAX(folio_dev), 0)+1) AS Consecutivo FROM c_devclientes;
    ";
  $res_consecutivo = getArraySQL($sql);

  $sql = "
    SELECT cve_usuario,nombre_completo FROM c_usuario WHERE id_user = '$id_user';
    ";
  $res_usuario = getArraySQL($sql);

  $array = [
    "res"=>$res,
    "res_clientes"      => $res_clientes,
    "res_usuario"       => $res_usuario,
    "res_factura"       => $res_factura,
    "res_recepcion"     => $res_recepcion,
    "res_pallets"       => '',//$res_pallets,
    "res_unidad_medida" => $res_unidad_medida,
    "res_rutas"         => $res_rutas,
    "Consecutivo"       => $res_consecutivo
  ];
  echo json_encode($array);
}

if( $_POST['action'] == 'cliente_pedido' )
{
  $almacen = $_POST['almacen'];
  $cliente = $_POST['cliente'];
  $pedido  = $_POST['pedido'];
  $cve_proveedor = $_POST['cve_proveedor'];

  $sql_cliente = ""; $mostrar_factura = "AND 0";
  if($cliente) {$sql_cliente = "AND a.Cve_clte = '$cliente'"; $mostrar_factura = "AND 1";}

  $sql_Proveedor_login = "";
  $sql_Proveedor_pedido = "";
  if($cve_proveedor)
  {
      $sql_Proveedor_login = " AND c.ID_Proveedor = '{$cve_proveedor}' ";
      $sql_Proveedor_pedido = " AND a.Cve_clte IN (SELECT Cve_Clte FROM c_cliente WHERE ID_Proveedor = '{$cve_proveedor}') ";
  }

  $sql = "
    SELECT  a.Fol_folio
    FROM th_pedido a
    LEFT JOIN c_almacenp p ON a.cve_almac=p.id 
    WHERE a.status = 'F' AND p.clave='$almacen' {$sql_cliente} {$sql_Proveedor_pedido};
    ";
  $res_pedido = getArraySQL($sql);

  $sql = "
    SELECT  DISTINCT a.Pick_Num AS Factura
    FROM th_pedido a
    LEFT JOIN c_cliente c ON c.Cve_Clte = a.Cve_clte
    LEFT JOIN c_almacenp p ON a.cve_almac=p.id 
    WHERE a.status = 'F' AND p.clave='$almacen' AND a.Fol_folio = '$pedido' AND c.ID_Proveedor IS NOT NULL AND c.ID_Proveedor != '' AND c.ID_Proveedor != 0 
    {$sql_cliente} {$mostrar_factura} {$sql_Proveedor_login};
    ";
  $res_factura = getArraySQL($sql);

  $array = [
    "res_pedido"=>$res_pedido,
    "res_factura" => $res_factura
  ];
  echo json_encode($array);
}

if( $_POST['action'] == 'rutas_pedido' )
{
  $almacen = $_POST['almacen'];
  $cliente = $_POST['cliente'];
  $pedido  = $_POST['pedido'];
  $id_ruta = $_POST['id_ruta'];
  $cve_proveedor = $_POST['cve_proveedor'];

  $sql_cliente = ""; $mostrar_factura = "AND 0";
  if($cliente) {$sql_cliente = "AND a.Cve_clte = '$cliente'"; $mostrar_factura = "AND 1";}

  $sql_ruta = "";
  if($id_ruta) {$sql_ruta = " AND a.ruta = '$id_ruta' ";}

  $sql_Proveedor_login = "";
  $sql_Proveedor_pedido = "";
  if($cve_proveedor)
  {
      $sql_Proveedor_login = " AND c.ID_Proveedor = '{$cve_proveedor}' ";
      $sql_Proveedor_pedido = " AND a.Cve_clte IN (SELECT Cve_Clte FROM c_cliente WHERE ID_Proveedor = '{$cve_proveedor}') ";
  }

  /*
  $sql1 = "
    SELECT  a.Fol_folio
    FROM th_pedido a
    LEFT JOIN c_almacenp p ON a.cve_almac=p.id 
    WHERE (a.status = 'F' OR a.status = 'T') AND p.clave='$almacen' {$sql_cliente} {$sql_Proveedor_pedido} {$sql_ruta};
    ";
    */
  $sql1 = "SELECT DISTINCT d.Folio AS Fol_folio 
            FROM Descarga d
            LEFT JOIN c_almacenp a ON a.clave = d.IdEmpresa
            WHERE d.IdRuta = '$id_ruta' AND d.IdEmpresa = '$almacen' AND d.Cantidad > 0";
  $res_pedido = getArraySQL($sql1);
/*
  $sql2 = "
    SELECT  DISTINCT a.Pick_Num AS Factura
    FROM th_pedido a
    LEFT JOIN c_cliente c ON c.Cve_Clte = a.Cve_clte
    LEFT JOIN c_almacenp p ON a.cve_almac=p.id 
    WHERE (a.status = 'F' OR a.status = 'T') AND p.clave='$almacen' AND a.Fol_folio = '$pedido' AND c.ID_Proveedor IS NOT NULL AND c.ID_Proveedor != '' AND c.ID_Proveedor != 0 
    {$sql_cliente} {$mostrar_factura} {$sql_Proveedor_login} {$sql_ruta};
    ";
  $res_factura = getArraySQL($sql2);
*/
  $array = [
    "res_pedido"=>$res_pedido,
    //"res_factura" => $res_factura, 
    "sql1" => $sql1 
    //"sql2" => $sql2
  ];
  echo json_encode($array);
}


if( $_POST['action'] == 'pedidos_cliente' )
{
  $pedido        = $_POST['pedido'];
  $cve_proveedor = $_POST['cve_proveedor'];
  $tipo_pedido   = $_POST['tipo_pedido'];
  $id_ruta       = $_POST['id_ruta'];

  $sql_pedido = "";
  if($pedido) $sql_pedido = "AND a.Fol_folio = '$pedido'";

  $sql_Proveedor_login = "";
  $sql_Proveedor_pedido = "";

  if($cve_proveedor)
  {
      $sql_Proveedor_login = " AND c.ID_Proveedor = '{$cve_proveedor}' ";
      $sql_Proveedor_pedido = " AND a.Cve_clte IN (SELECT Cve_Clte FROM c_cliente WHERE ID_Proveedor = '{$cve_proveedor}') ";
  }
  $field_cantidad = " p.Cantidad AS Cantidad ";
  $left_join_cantidad = "";
  if($tipo_pedido == 3 && $id_ruta)
  {
    $field_cantidad = " st.Stock AS Cantidad ";
    $left_join_cantidad = " LEFT JOIN Stock st ON st.Articulo = p.Cve_articulo AND st.Ruta = {$id_ruta} ";
  }

  $sql = "
    SELECT  DISTINCT a.Cve_clte, c.RazonSocial, a.Pick_Num AS Factura
    FROM th_pedido a
    LEFT JOIN c_cliente c ON c.Cve_Clte = a.Cve_clte
    WHERE a.status = 'F' AND c.ID_Proveedor IS NOT NULL AND c.ID_Proveedor != '' AND c.ID_Proveedor != 0 {$sql_pedido} {$sql_Proveedor_login} {$sql_Proveedor_pedido}
    ";
  $res_clientes = getArraySQL($sql);

  $sql = "
    SELECT DISTINCT a.cve_articulo AS cve_articulo, 
        a.des_articulo AS Descripcion, 
        IF(IFNULL(a.control_numero_series, 'N') = 'S', p.LOTE, '') AS serie,
        IF(IFNULL(a.control_lotes, 'N') = 'S', p.LOTE, '') AS lote, 
        IF(IFNULL(a.Caduca, 'N') = 'S', l.Caducidad, '') AS Caducidad, 
        a.num_multiplo,
        IFNULL(t.Precio_unitario, 0) as precio,
        IFNULL(pr.ID_Proveedor, '') AS id_proveedor, 
        IFNULL(pr.Nombre, '') AS nombre_proveedor,
        {$field_cantidad}
    FROM td_surtidopiezas p
    LEFT JOIN td_pedido t ON t.Fol_folio = p.fol_folio AND p.Cve_articulo = t.Cve_articulo
    LEFT JOIN th_pedido th ON th.Fol_folio = t.Fol_folio
    LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_clte
    LEFT JOIN c_proveedores pr ON pr.ID_Proveedor = c.ID_Proveedor
    LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo
    LEFT JOIN c_lotes l ON l.cve_articulo = p.Cve_articulo AND p.LOTE = l.Lote
    {$left_join_cantidad}
    WHERE p.fol_folio = '$pedido' AND a.Cve_articulo = p.Cve_articulo {$sql_Proveedor_login}
    ";
  $res_articulos = getArraySQL($sql);

  $array = [
    "res_clientes" => $res_clientes,
    "res_articulos" => $res_articulos
  ];
  echo json_encode($array);
}


if( $_POST['action'] == 'factura_pedido' )
{
  $almacen = $_POST['almacen'];
  $factura = $_POST['factura'];
  $cve_proveedor = $_POST['cve_proveedor'];

  $sql_factura = ""; $mostrar_factura = "AND 0";
  if($factura) {$sql_factura = "AND a.Pick_Num = '$factura'"; $mostrar_factura = "AND 1";}

  $sql_Proveedor_login = "";
  $sql_Proveedor_pedido = "";
  if($cve_proveedor)
  {
      $sql_Proveedor_login = " AND c.ID_Proveedor = '{$cve_proveedor}' ";
      $sql_Proveedor_pedido = " AND a.Cve_clte IN (SELECT Cve_Clte FROM c_cliente WHERE ID_Proveedor = '{$cve_proveedor}') ";
  }

  $sql = "
    SELECT  a.Fol_folio
    FROM th_pedido a
    LEFT JOIN c_almacenp p ON a.cve_almac=p.id 
    WHERE a.status = 'F' AND p.clave='$almacen' {$sql_factura} {$sql_Proveedor_pedido};
    ";
  $res_pedido = getArraySQL($sql);

  $sql = "
    SELECT  DISTINCT a.Cve_clte, c.RazonSocial
    FROM th_pedido a
    LEFT JOIN c_cliente c ON c.Cve_Clte = a.Cve_clte
    WHERE a.status = 'F' AND c.ID_Proveedor IS NOT NULL AND c.ID_Proveedor != '' AND c.ID_Proveedor != 0 {$sql_factura} {$sql_Proveedor_login}
    ";
  $res_clientes = getArraySQL($sql);

  $array = [
    "res_pedido"=>$res_pedido,
    "res_clientes" => $res_clientes
  ];
  echo json_encode($array);
}

if( $_POST['action'] == 'proveedores_articulos' )
{
  $almacen = $_POST['almacen'];
  $cve_articulo = $_POST['cve_articulo'];

  $sql = "
    SELECT DISTINCT c.ID_Proveedor, c.cve_proveedor, c.Nombre
    FROM c_proveedores c, rel_articulo_proveedor r 
    WHERE r.Id_Proveedor = c.ID_Proveedor AND r.Cve_Articulo = '$cve_articulo';
    ";
  $res_proveedores = getArraySQL($sql);

  $array = [
    "res_proveedores" => $res_proveedores
  ];
  echo json_encode($array);
}


if( $_POST['action'] == 'guardar_devolucion' )
{
  $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $almacen = $_POST['almacen'];
  $pedido  = $_POST['pedido'];
  $folio_dev  = $_POST['folio_dev'];
  $usuario    = $_POST['usuario'];
  $zona_recepcion = $_POST['zona_recepcion'];
  $productos_recibidos = $_POST['productos_recibidos'];
  $tipo = $_POST['tipo'];
  $motivo_dev = $_POST['motivo_dev'];

  if($tipo == 1) $tipo = 'DV'; else $tipo = "DVL";

  $sql = "SELECT cve_usuario FROM c_usuario WHERE id_user = {$usuario}";
  $cve_user = mysqli_query($conexion, $sql);
  $cve_user = mysqli_fetch_assoc($cve_user);
  $cve_usuario = $cve_user["cve_usuario"];


  foreach ($productos_recibidos as $row) 
  {
      $cve_articulo = $row["cve_articulo"];
      $lote         = $row["lote"];
      $serie        = $row["serie"];
      $caducidad    = $row["caducidad"];
      $z_recepcion  = $row["z_recepcion"];
      $cantidad     = $row["cantidad"];
      $pallet_cont  = $row["pallet_cont"];
      $costo        = $row["costo"];
      //$proveedor    = $row['proveedor'];
      $num_multiplo = $row['num_multiplo'];
      $articulo_defectuoso  = $row["defectuoso"];

      /*
      SE USARÁ EL CAMPO placas DE th_entalmacen PARA IDENTIFICAR EL MOTIVO DE DEVOLUCIÓN, CUANDO EL TIPO = DV o DVL, ENTONCES placas SE CONVIERTE EN MOTIVO DE DEVOLUCIÓN
      */
      $proveedor = " (SELECT ID_Proveedor FROM c_cliente WHERE Cve_Clte = (SELECT Cve_Clte FROM th_pedido WHERE Fol_Folio = '$pedido') LIMIT 1) ";
      $sql = "INSERT INTO th_entalmacen(Cve_Almac, Fec_Entrada, Cve_Usuario, Cve_Proveedor, STATUS, Cve_Autorizado, tipo, id_ocompra, placas, entarimado, bufer, HoraInicio, ID_Protocolo, Consec_protocolo, cve_ubicacion, HoraFin, Fact_Prov) 
              VALUES ('$almacen', NOW(), '$cve_usuario', $proveedor, 'E', '$cve_usuario', '$tipo', NULL, '{$motivo_dev}', '', '', NOW(), '1', '$folio_dev', '$zona_recepcion', NOW(), '');";
      //mysqli_set_charset($conexion, "utf8");
      if(!$result = mysqli_query($conexion, $sql))
      {
        echo "Falló la preparación 1: (" . mysqli_error($conexion) . ") ".$sql;
      }

      $lote_serie = "";
      if($lote)  $lote_serie = $lote;
      if($serie) $lote_serie = $serie;


      $sql = "SELECT MAX(Fol_Folio) as id_entrada FROM th_entalmacen";
      $ent_entrada = mysqli_query($conexion, $sql);
      $ent_entrada = mysqli_fetch_assoc($ent_entrada);
      $id_entrada = $ent_entrada["id_entrada"];

      $sql = "INSERT INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadPedida, CantidadRecibida, CantidadDisponible, numero_serie, status, cve_usuario, cve_ubicacion, fecha_inicio, fecha_fin, tipo_entrada, costoUnitario, num_orden) 
        VALUES (".$id_entrada.", '".$cve_articulo."', '".$lote_serie."', ".$cantidad.", ".$cantidad.", '', '".$lote_serie."', 'E', '".$cve_usuario."', '".$zona_recepcion."', NOW(), NOW(), 1, ".$costo.", '')";

        if(!$result = mysqli_query($conexion, $sql))
        {
          echo "Falló la preparación 2 : (" . mysqli_error($conexion) . ") ".$sql;
        }

      $sql = "INSERT INTO c_devclientes(folio_dev, folio_pedido, folio_entrada, cve_articulo, cve_lote, caducidad, cantidad_devuelta, zona_recepcion, cve_contenedor, usuario, defectuoso) VALUES({$folio_dev}, '{$pedido}', '{$id_entrada}', '{$cve_articulo}', '{$lote_serie}', '{$caducidad}', {$cantidad}, '{$z_recepcion}', '{$pallet_cont}', '{$usuario}', {$articulo_defectuoso})";
        mysqli_set_charset($conexion, "utf8");
        if(!$result = mysqli_query($conexion, $sql))
        {
          echo "Falló la preparación 3: (" . mysqli_error($conexion) . ") ".$sql;
        }


        $sql = "SELECT id FROM t_pendienteacomodo WHERE cve_articulo='$cve_articulo' AND cve_lote='$lote_serie' AND cve_ubicacion='$zona_recepcion' AND ID_Proveedor=$proveedor";
        $ent_entrada = mysqli_query($conexion, $sql);
        $id_entrada_pendienteacomodo = mysqli_fetch_assoc($ent_entrada);
        $id_entrada_pendienteacomodo = $id_entrada_pendienteacomodo["id"];

        if($id_entrada_pendienteacomodo)
        {
            $sql = "UPDATE t_pendienteacomodo SET Cantidad=Cantidad+$cantidad WHERE id=$id_entrada_pendienteacomodo AND cve_articulo='$cve_articulo' AND cve_lote='$lote_serie' AND cve_ubicacion='$zona_recepcion' AND ID_Proveedor=$proveedor";

            if(!$result = mysqli_query($conexion, $sql))
            {
              echo "Falló la preparación 4: (" . mysqli_error($conexion) . ") ".$sql;
            }
        }
        else 
        {
            $sql = "INSERT INTO t_pendienteacomodo(cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) VALUES('$cve_articulo', '$lote_serie', $cantidad, '$zona_recepcion', $proveedor)";

            if(!$result = mysqli_query($conexion, $sql))
            {
              echo "Falló la preparación 5: (" . mysqli_error($conexion) . ") ".$sql;
            }
        }
//********************************************************************************************************************
//********************************************************************************************************************
        if($pallet_cont != "" && $num_multiplo > 1)
        {
            if($lote_serie == NULL){$lote_serie = " ";}
            $can = $cantidad / $num_multiplo;
            $can = round($can, 0, PHP_ROUND_HALF_DOWN);
          
            if($can == 0)
            {
              $can = $cantidad;
            }
                $residuo = $cantidad % $num_multiplo;
                $sql = "INSERT INTO td_entalmacenxtarima(fol_folio, cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, Ubicada, Activo, PzsXCaja) 
                        VALUES($id_entrada, '$cve_articulo', '$lote_serie', '$pallet_cont', $cantidad, 'N', 1, $num_multiplo)";
                if(!$result = mysqli_query($conexion, $sql))
                {
                  echo "Falló la preparación 6: (" . mysqli_error($conexion) . ") ".$sql;
                }

          //     $a = 'LP';
          //if(preg_match("/{$a}/", $pallet_cont))
          //{
          //   $sql = "UPDATE c_charolas
          //   SET CveLP = '{$pallet_cont}'
          //   WHERE IDContenedor = '{$item['id_con']}';
          //   ";  
          //   $sth = \db()->prepare( $sql );
          //   $sth->execute();
          //}

          if($residuo > 0)
          {
               $insert_xtarimaa = array(
               "fol_folio"      => $id_entrada,
               "cve_articulo"   => $item['cve_articulo'],
               "cve_lote"       => $item['lote'],
               "ClaveEtiqueta"  => $item['contenedor'],
               "Cantidad"       => 1,
               "Ubicada"        => "N",
               "Activo"         => 1,
               "PzsXCaja"       => $residuo
               );

              // echo var_dump( $insert_xtarimaa);
              // die();
                $sql = "INSERT INTO td_entalmacenxtarima(fol_folio, cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, Ubicada, Activo, PzsXCaja) 
                        VALUES($id_entrada, '$cve_articulo', '$lote_serie', '$pallet_cont', 1, 'N', 1, $residuo)";
                if(!$result = mysqli_query($conexion, $sql))
                {
                  echo "Falló la preparación 7: (" . mysqli_error($conexion) . ") ".$sql;
                }
          }

        //insertar datos de contenedor en td_entalmacencajas
        if($num_multiplo > 1)
        {
          $can = $cantidad / $num_multiplo;
          $can = round($can, 0, PHP_ROUND_HALF_DOWN);
              if($can == 0)
              {
                  $can = $cantidad;
              }
               $residuo = $cantidad % $num_multiplo;

            $insert_xcaja = array(
                "Fol_Folio"      => $id_entrada,
                "Cve_Articulo"   => $item['cve_articulo'],
                "Cve_Lote"       => $item['lote'],
                "PiezasXCaja"    => $item['multiplo'],
                "NCajas"         => $can,
                "Ubicadas"        => 0
            );

            $sql = "INSERT INTO td_entalmacencajas(Fol_Folio, Cve_Articulo, Cve_Lote, PiezasXCaja, NCajas, Ubicadas) 
                    VALUES($id_entrada, '$cve_articulo', '$lote_serie', '$num_multiplo', $cantidad, 0)";
            if(!$result = mysqli_query($conexion, $sql))
            {
              echo "Falló la preparación 8: (" . mysqli_error($conexion) . ") ".$sql;
            }
        }
      
          if($residuo > 0)
          {
             $insert_xcajaa = array(
           "Fol_Folio"      => $id_entrada,
           "Cve_Articulo"   => $item['cve_articulo'],
           "Cve_Lote"       => $item['lote'],
           "PiezasXCaja"    => $residuo,
           "NCajas"         => 1,
           "Ubicadas"        => 0
           );

            $sql = "INSERT INTO td_entalmacencajas(Fol_Folio, Cve_Articulo, Cve_Lote, PiezasXCaja, NCajas, Ubicadas) 
                    VALUES($id_entrada, '$cve_articulo', '$lote_serie', '$residuo', 1, 0)";
            if(!$result = mysqli_query($conexion, $sql))
            {
              echo "Falló la preparación 9: (" . mysqli_error($conexion) . ") ".$sql;
            }

          }
       }
//********************************************************************************************************************
//********************************************************************************************************************

  }

  $array = [
    "success"=>true
  ];
  echo json_encode($array);
}


if( $_POST['action'] == 'partida' )
{
  $presupuesto = $_POST['presupuesto'];
  $data = $ga->partida_select($presupuesto);
  $arr = array(
    "clave" => $data["claveDePartida"],
    "concepto" => $data["conceptoDePartida"],
    "success" => true
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'cargarMonto' ) {
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

if( $_POST['action'] == 'totalesPedido' )
{
  $data=$ga->getTotalPedido($_POST);
  $arr = array(
    "success" => true,
    "total_pedido" => $data["total_pedido"]
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'marcar_como_merma' )
{
    $id_entrada   = $_POST['id_entrada'];
    $cve_articulo = $_POST['cve_articulo'];
    $lote_serie   = $_POST['lote_serie'];
    $cantidad     = $_POST['cantidad'];
    $clave_zona   = $_POST['clave_zona'];
    $id_proveedor = $_POST['id_proveedor'];
    $cve_usuario  = $_POST['cve_usuario'];
    $id_almacen   = $_POST['id_almacen'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "UPDATE td_entalmacen SET status = 'M' WHERE id = $id_entrada";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $sql = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad - $cantidad WHERE cve_articulo = '$cve_articulo' AND cve_lote = '$lote_serie' AND cve_ubicacion = '$clave_zona' AND ID_Proveedor = '$id_proveedor'";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

   $sql = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, cantidad, ajuste, stockinicial, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) VALUES ('$cve_articulo', '$lote_serie', NOW(), (SELECT fol_folio FROM td_entalmacen WHERE id = $id_entrada), 'MERMA', 0, '$cantidad', '$cantidad', 8, '$cve_usuario', '$id_almacen', 1, NOW())";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    if($lp)
    {
       $sql = "INSERT INTO t_MovCharolas (id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES ((SELECT MAX(id) FROM t_cardex), '$id_almacen', (SELECT IDContenedor FROM c_charolas WHERE CveLP = '$lp'), NOW(), (SELECT fol_folio FROM td_entalmacen WHERE id = $id_entrada), 'MERMA', 8, '$cve_usuario', 'O')";
       if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    }

      $array = [
        "success" => true
      ];
      echo json_encode($array);

}


if( $_POST['action'] == 'cambiar_lote' )
{
    $cve_articulo         = $_POST['cve_articulo'];
    $nuevo_lote_serie     = $_POST['nuevo_lote_serie'];
    $id_entrada           = $_POST['id_entrada'];
    $id_descarga          = $_POST['id_descarga'];
    $tiene_serie          = $_POST['tiene_serie'];
    $tiene_lote           = $_POST['tiene_lote'];
    $tiene_caducidad      = $_POST['tiene_caducidad'];
    $caducidad            = $_POST['cambiar_caducidad'];
    $cambiar_lote         = $_POST['cambiar_lote'];
    $lote_serie           = $_POST['lote_serie'];
    $clave_zona           = $_POST['clave_zona'];
    $id_proveedor         = $_POST['id_proveedor'];
    $producto_derivado    = $_POST['producto_derivado'];
    $articulos_conversion = $_POST['articulos_conversion'];
    $cantidad_derivado    = $_POST['cantidad_derivado'];
    $cve_usuario          = $_POST['cve_usuario'];
    $id_almacen           = $_POST['id_almacen'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if($nuevo_lote_serie)
       $lote_serie = $nuevo_lote_serie;

     if($producto_derivado == 1)
     {
        $sql = "SELECT IFNULL(CantidadRecibida, 0) as CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada";
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ".$sql;
        }
        $row = mysqli_fetch_array($res);
        $cantidad_inicial = $row['CantidadRecibida'];

          $sql = "INSERT IGNORE INTO t_tipomovimiento(id_TipoMovimiento, nombre) VALUES (21, 'Conversión a Producto Derivado')";
          if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
          //********************************************************************************************
          //al artículo que no tiene lote registrado, le resto la cantidad de la descarga
          //********************************************************************************************
          $sql = "SELECT * FROM t_pendienteacomodo WHERE cve_articulo = '$cve_articulo' AND cve_lote = '$lote_serie' AND cve_ubicacion = '$clave_zona' AND ID_Proveedor = '$id_proveedor'";
          if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

          if(mysqli_num_rows($res) > 0)
          {
            $sql = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad - (SELECT CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada) WHERE cve_articulo = '$cve_articulo' AND cve_lote = '$lote_serie' AND cve_ubicacion = '$clave_zona' AND ID_Proveedor = '$id_proveedor'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
          }
          //********************************************************************************************

        $sql = "UPDATE td_entalmacen SET cve_articulo = '$articulos_conversion', CantidadRecibida = {$cantidad_derivado} WHERE id = $id_entrada";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

        //$sql = "UPDATE Descarga SET Articulo = '$articulos_conversion', Cantidad = {$cantidad_derivado} WHERE ID = $id_descarga";
        //if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación D: (" . mysqli_error($conn) . ") ".$sql;}


       $sql = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) VALUES ('$cve_articulo', '', NOW(), '$clave_zona', '$clave_zona', {$cantidad_inicial}, 0, (-1)*{$cantidad_inicial}, 21, '$cve_usuario', '$id_almacen', 1, NOW())";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

        $cve_articulo = $articulos_conversion;

       $sql = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) VALUES ('$cve_articulo', '$lote_serie', NOW(), '$clave_zona', '$clave_zona', 0, {$cantidad_derivado}, $cantidad_derivado, 21, '$cve_usuario', '$id_almacen', 1, NOW())";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}


     }


    if($lote_serie != '')
    {
    if($tiene_lote == 'S')
    {
        $sql = "SELECT Lote FROM c_lotes where cve_articulo = '$cve_articulo'";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
        if(mysqli_num_rows($res) == 0)
        {
            //No existe el lote
            $sql = "INSERT INTO c_lotes (cve_articulo, Lote, Activo) VALUES('$cve_articulo', '$lote_serie', 1)";

            if($tiene_caducidad == 'S')
            {
              $sql = "INSERT INTO c_lotes (cve_articulo, Lote, Caducidad, Activo) VALUES('$cve_articulo', '$lote_serie', '$caducidad', 1)";
            }
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
        }
        else
        {
            $sql = "SELECT * FROM c_lotes where cve_articulo = '$cve_articulo' AND Lote = '$lote_serie'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

            if(mysqli_num_rows($res) == 0)
            {
                //si el lote no existe en la tabla de lotes

                $sql = "SELECT * FROM c_lotes where cve_articulo = '$cve_articulo' AND Lote = ''";
                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                if(mysqli_num_rows($res) > 0)
                {
                    //si hay un lote vacío registrado con este artículo, edito el lote, sino lo registro
                    $sql = "UPDATE c_lotes SET Lote = '$lote_serie', Caducidad = '$caducidad' WHERE cve_articulo = '$cve_articulo' AND Lote = ''";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                }
                else
                {
                  $sql = "INSERT INTO c_lotes (cve_articulo, Lote, Activo) VALUES('$cve_articulo', '$lote_serie', 1)";
                  if($tiene_caducidad == 'S')
                  {
                    $sql = "INSERT INTO c_lotes (cve_articulo, Lote, Caducidad, Activo) VALUES('$cve_articulo', '$lote_serie', '$caducidad', 1)";
                  }
                  if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }
            }
        }
    }
    else if($tiene_serie == 'S')
    {
        $sql = "SELECT numero_serie FROM c_serie where cve_articulo = '$cve_articulo'";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

        if(mysqli_num_rows($res) == 0)
        {
            //No existe el lote
              $sql = "INSERT INTO c_serie (cve_articulo, numero_serie, fecha_ingreso) VALUES('$cve_articulo', '$lote_serie', CURDATE())";
              if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
        }
        else
        {
            $sql = "SELECT * FROM c_serie where cve_articulo = '$cve_articulo' AND numero_serie = '$lote_serie'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

            if(mysqli_num_rows($res) == 0)
            {
                //si el lote no existe en la tabla de lotes

                $sql = "SELECT * FROM c_serie where cve_articulo = '$cve_articulo' AND numero_serie = ''";
                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                if(mysqli_num_rows($res) > 0)
                {
                    //si hay un lote vacío registrado con este artículo, edito el lote, sino lo registro
                    $sql = "UPDATE c_serie SET numero_serie = '$lote_serie', fecha_ingreso = CURDATE() WHERE cve_articulo = '$cve_articulo' AND numero_serie = ''";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                }
                else
                {
                    $sql = "INSERT INTO c_serie (cve_articulo, numero_serie, fecha_ingreso) VALUES('$cve_articulo', '$lote_serie', CURDATE())";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }
            }
        }
    }

    //si el lote existe lo aumento, si no existe lo registro
    $sql = "INSERT INTO t_pendienteacomodo (cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) VALUES ('$cve_articulo', '$lote_serie', (SELECT CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada), '$clave_zona', '$id_proveedor') ON DUPLICATE KEY UPDATE Cantidad = Cantidad + (SELECT CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada)";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}


    //********************************************************************************************
    //al artículo que no tiene lote registrado, le resto la cantidad de la descarga
    //********************************************************************************************
    $sql = "SELECT * FROM t_pendienteacomodo WHERE cve_articulo = '$cve_articulo' AND cve_lote = '' AND cve_ubicacion = '$clave_zona' AND ID_Proveedor = '$id_proveedor'";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    if(mysqli_num_rows($res) > 0)
    {
      $sql = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad - (SELECT CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada) WHERE cve_articulo = '$cve_articulo' AND cve_lote = '' AND cve_ubicacion = '$clave_zona' AND ID_Proveedor = '$id_proveedor'";
      if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    }
    //********************************************************************************************

    $sql = "UPDATE td_entalmacen SET cve_lote = '$lote_serie', status = 'H' WHERE id = $id_entrada";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    if($producto_derivado != 1)
    {
      $sql = "INSERT IGNORE INTO t_tipomovimiento(id_TipoMovimiento, nombre) VALUES (22, 'Cambio de Lote')";
      if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    }

     $sql = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) VALUES ('$cve_articulo', '', NOW(), (SELECT CONCAT('Entrada ', fol_folio) FROM td_entalmacen WHERE id = $id_entrada), 'Lote|Serie: $lote_serie', (SELECT CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada), (SELECT CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada), 0, 22, '$cve_usuario', '$id_almacen', 1, NOW())";
      if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

  }
  $array = [
    "success" => true
  ];
  echo json_encode($array);
}

if( $_POST['action'] == 'cambiar_lote_varios' )
{
  $almacen = $_POST['almacen'];
  $cliente = $_POST['cliente'];
  $pedido  = $_POST['pedido'];
  $diao    = $_POST['diao'];
  $id_ruta = $_POST['id_ruta'];
  $cve_proveedor = $_POST['cve_proveedor'];
  $fecha_inicio  = $_POST['fecha_inicio'];
  $fecha_fin     = $_POST['fecha_fin'];
  $asignar_todos = $_POST['asignar_todos'];
  $arr_entradas  = $_POST['arr_entradas'];
  $arr_descargas = $_POST['arr_descargas'];
  $lote_serie = $_POST['nuevo_lote'];
  $caducidad  = $_POST['nueva_caducidad'];
  $producto_derivado    = $_POST['producto_derivado'];
  $articulos_conversion = $_POST['articulos_conversion'];
  $cantidad_derivado    = $_POST['cantidad_derivado'];
  $cve_usuario          = $_POST['cve_usuario'];
  $id_almacen           = $_POST['id_almacen'];


  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


      $sql_diao = "";
      if($diao)
      {
            $sql_diao = " AND d.Diao = '$diao' ";
      }

      $sql_pedido = "";
      if($pedido)
      {
            $sql_pedido = " AND d.Folio = '$pedido' ";
      }


      $sql_fecha = "";
      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));

        $sql_fecha = " AND DATE_FORMAT(d.Fecha, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";

      }
      elseif (!empty($fecha_inicio)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));

        $sql_fecha .= " AND DATE_FORMAT(d.Fecha, '%d-%m-%Y') >= '{$fecha_inicio}' ";

      }
      elseif (!empty($fecha_fin)) 
      {
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));

        $sql_fecha .= " AND DATE_FORMAT(d.Fecha, '%d-%m-%Y') <= '{$fecha_fin}'";
      }

      $sql = ""; $vector_entradas = array(); $vector_descargas = array();
    if($asignar_todos == 1)
    {
        $sql = "SELECT * FROM (
                SELECT DISTINCT 
                tde.id,
                d.ID as id_descarga,
                IF(tde.status = 'M', 'EM',IF(IFNULL(tde.cve_lote, '') = '', 'PL', IF(IFNULL(tde.CantidadUbicada, 0) > 0, 'U', 'PA'))) AS estatus
                FROM Descarga d
                LEFT JOIN c_almacenp alm ON alm.clave = d.IdEmpresa
                LEFT JOIN c_articulo a ON a.cve_articulo = d.Articulo
                INNER JOIN th_entalmacen e ON e.Fact_Prov = d.Folio
                INNER JOIN td_entalmacen tde ON tde.cve_articulo = d.Articulo AND tde.fol_folio = e.Fol_Folio
                LEFT JOIN td_entalmacenxtarima tdt ON tdt.cve_articulo = d.Articulo AND tdt.fol_folio = e.Fol_Folio AND tdt.cve_lote = tde.cve_lote
                LEFT JOIN t_ruta r ON r.ID_Ruta = d.IdRuta
                LEFT JOIN tubicacionesretencion ub ON ub.cve_ubicacion = e.cve_ubicacion
                LEFT JOIN c_lotes l ON l.cve_articulo = d.Articulo AND tde.cve_lote = l.Lote
                LEFT JOIN c_unimed um ON um.id_umed = a.unidadMedida
                WHERE d.IdRuta = '$id_ruta' AND d.IdEmpresa = '$almacen' AND d.Cantidad > 0 AND (IFNULL(a.control_lotes, 'N') = 'S' OR IFNULL(a.control_numero_series, 'N') = 'S')
                {$sql_pedido} {$sql_diao} {$sql_fecha} 
                ) AS cambio WHERE cambio.estatus = 'PL'
                ";

        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ".$sql;
        }
        while($row = mysqli_fetch_array($res))
        {
            array_push($vector_entradas, $row['id']);
            array_push($vector_descargas, $row['id_descarga']);
        }
    }
    else
    {
        //$vector_entradas = $arr_entradas;
        $vector_entradas = explode(",", $arr_entradas);
        $vector_descargas = explode(",", $arr_descargas);
    }

      $i = 0;
      foreach($vector_entradas as $id_entrada)
      {

          $sql = "SELECT  
                    tde.cve_articulo,
                    tde.cve_ubicacion,
                    th.Cve_Proveedor,
                    IFNULL(a.control_lotes, 'N') AS control_lotes, 
                    IFNULL(a.Caduca, 'N') AS Caduca
                  FROM td_entalmacen tde 
                  LEFT JOIN th_entalmacen th ON th.Fol_Folio = tde.fol_folio
                  LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo
                  WHERE tde.id = $id_entrada";

          if (!($res = mysqli_query($conn, $sql))) {
              echo "Falló la preparación: (" . mysqli_error($conn) . ") ".$sql;
          }
          $row = mysqli_fetch_array($res);

          $cve_articulo = $row['cve_articulo'];
          $clave_zona = $row['cve_ubicacion'];
          $id_proveedor = $row['Cve_Proveedor'];
          $tiene_lote = $row['control_lotes'];
          $tiene_caducidad = $row['Caduca'];

     if($producto_derivado == 1)
     {
        $sql = "SELECT IFNULL(CantidadRecibida, 0) as CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada";
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ".$sql;
        }
        $row = mysqli_fetch_array($res);
        $cantidad_inicial = $row['CantidadRecibida'];

          $sql = "INSERT IGNORE INTO t_tipomovimiento(id_TipoMovimiento, nombre) VALUES (21, 'Conversión a Producto Derivado')";
          if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
          //********************************************************************************************
          //al artículo que no tiene lote registrado, le resto la cantidad de la descarga
          //********************************************************************************************
          $sql = "SELECT * FROM t_pendienteacomodo WHERE cve_articulo = '$cve_articulo' AND cve_lote = '$lote_serie' AND cve_ubicacion = '$clave_zona' AND ID_Proveedor = '$id_proveedor'";
          if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

          if(mysqli_num_rows($res) > 0)
          {
            $sql = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad - (SELECT CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada) WHERE cve_articulo = '$cve_articulo' AND cve_lote = '$lote_serie' AND cve_ubicacion = '$clave_zona' AND ID_Proveedor = '$id_proveedor'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
          }
          //********************************************************************************************

        $sql = "UPDATE td_entalmacen SET cve_articulo = '$articulos_conversion', CantidadRecibida = {$cantidad_derivado} WHERE id = $id_entrada";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

        //$sql = "UPDATE Descarga SET Articulo = '$articulos_conversion', Cantidad = {$cantidad_derivado} WHERE ID = $vector_descargas[$i]";
        //if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación D: (" . mysqli_error($conn) . ") ".$sql;}


       $sql = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) VALUES ('$cve_articulo', '', NOW(), '$clave_zona', '$clave_zona', {$cantidad_inicial}, 0, (-1)*{$cantidad_inicial}, 21, '$cve_usuario', '$id_almacen', 1, NOW())";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

        $cve_articulo = $articulos_conversion;

       $sql = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) VALUES ('$cve_articulo', '$lote_serie', NOW(), '$clave_zona', '$clave_zona', 0, {$cantidad_derivado}, $cantidad_derivado, 21, '$cve_usuario', '$id_almacen', 1, NOW())";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
     }


          if($tiene_lote == 'S')
          {
              $sql = "SELECT Lote FROM c_lotes where cve_articulo = '$cve_articulo'";
              if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
              if(mysqli_num_rows($res) == 0)
              {
                  //No existe el lote
                  $sql = "INSERT INTO c_lotes (cve_articulo, Lote, Activo) VALUES('$cve_articulo', '$lote_serie', 1)";

                  if($tiene_caducidad == 'S')
                  {
                    $sql = "INSERT INTO c_lotes (cve_articulo, Lote, Caducidad, Activo) VALUES('$cve_articulo', '$lote_serie', '$caducidad', 1)";
                  }
                  if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
              }
              else
              {
                  $sql = "SELECT * FROM c_lotes where cve_articulo = '$cve_articulo' AND Lote = '$lote_serie'";
                  if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                  if(mysqli_num_rows($res) == 0)
                  {
                      //si el lote no existe en la tabla de lotes

                      $sql = "SELECT * FROM c_lotes where cve_articulo = '$cve_articulo' AND Lote = ''";
                      if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                      if(mysqli_num_rows($res) > 0)
                      {
                          //si hay un lote vacío registrado con este artículo, edito el lote, sino lo registro
                          $sql = "UPDATE c_lotes SET Lote = '$lote_serie', Caducidad = '$caducidad' WHERE cve_articulo = '$cve_articulo' AND Lote = ''";
                          if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                      }
                      else
                      {
                        $sql = "INSERT INTO c_lotes (cve_articulo, Lote, Activo) VALUES('$cve_articulo', '$lote_serie', 1)";
                        if($tiene_caducidad == 'S')
                        {
                          $sql = "INSERT INTO c_lotes (cve_articulo, Lote, Caducidad, Activo) VALUES('$cve_articulo', '$lote_serie', '$caducidad', 1)";
                        }
                        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                      }
                  }
              }

              if($producto_derivado != 1)
              {
                $sql = "INSERT IGNORE INTO t_tipomovimiento(id_TipoMovimiento, nombre) VALUES (22, 'Cambio de Lote')";
                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
              }

           $sql = "INSERT INTO t_cardex (cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) VALUES ('$cve_articulo', '', NOW(), (SELECT CONCAT('Entrada ', fol_folio) FROM td_entalmacen WHERE id = $id_entrada), 'Lote|Serie: $lote_serie', (SELECT CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada), (SELECT CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada), 0, 22, '$cve_usuario', '$id_almacen', 1, NOW())";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

          }


          //si el lote existe lo aumento, si no existe lo registro
          $sql = "INSERT INTO t_pendienteacomodo (cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) VALUES ('$cve_articulo', '$lote_serie', (SELECT CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada), '$clave_zona', '$id_proveedor') ON DUPLICATE KEY UPDATE Cantidad = Cantidad + (SELECT CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada)";
          if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}


          //********************************************************************************************
          //al artículo que no tiene lote registrado, le resto la cantidad de la descarga
          //********************************************************************************************
          $sql = "SELECT * FROM t_pendienteacomodo WHERE cve_articulo = '$cve_articulo' AND cve_lote = '' AND cve_ubicacion = '$clave_zona' AND ID_Proveedor = '$id_proveedor'";
          if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

          if(mysqli_num_rows($res) > 0)
          {
            $sql = "UPDATE t_pendienteacomodo SET Cantidad = Cantidad - (SELECT CantidadRecibida FROM td_entalmacen WHERE id = $id_entrada) WHERE cve_articulo = '$cve_articulo' AND cve_lote = '' AND cve_ubicacion = '$clave_zona' AND ID_Proveedor = '$id_proveedor'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
          }
          //********************************************************************************************

          $sql = "UPDATE td_entalmacen SET cve_lote = '$lote_serie', status = 'H' WHERE id = $id_entrada";
          if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

          $i++;
      }

  $array = [
    //"vector_entradas" => $vector_entradas,
    "success" => true

  ];
  echo json_encode($array);
}


if( $_POST['action'] == 'existenciasDeArticulo' )
{
  $data = $ga->existenciasDeArticulo($_POST);
  $arr = array(
    "costoPromedio" => $data["costoPromedio"],
    "Existencia_Total"=> $data2["Existencia_Total"],
    "success" => true
  );
  echo json_encode($arr);
}

function getArraySQL($sql){
  $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  mysqli_set_charset($conexion, "utf8");
  if(!$result = mysqli_query($conexion, $sql))
  {
    echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
  }
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

if($_POST['action'] === 'traer_almacenes')
{
  $data = $ga->traer_almacenes($_POST);
  echo json_encode(array(
    "success" => true,
    "almacenes"  => $data,
  ));
}

if($_POST['action'] === 'traer_zonas')
{
  $data = $ga->traer_zonas($_POST);
  echo json_encode(array(
    "success" => true,
    "zonas"  => $data,
   
  ));
}

if($_POST['action'] === 'traer_contenedores')
{
  $data = $ga->traer_contenedores($_POST);
  echo json_encode(array(
    "success" => true,
    "contenedores"  => $data,
  ));
}

if($_POST['action'] === 'traer_medidas')
{
  $data = $ga->traer_medidas($_POST);
  echo json_encode(array(
    "success" => true,
    "medidas"  => $data,
  ));
}

if($_POST['action'] === 'traer_ordenes')
{
  $data = $ga->traer_ordenes($_POST);
  
  echo json_encode(array(
    "success" => true,
    "ordenes" => $data,
  ));
}

if($_POST['action'] === 'traer_proveedores')
{
  $data = $ga->traer_proveedores($_POST);
  
  echo json_encode(array(
    "success" => true,
    "proveedores" => $data,
  ));
}
  
if($_POST['action'] === 'traer_todos_los_articulos')
{
  $cliente = $_POST['cliente'];

  $sql_cliente = "";
  if($cliente) $sql_cliente = "AND th.Cve_clte = '{$cliente}'";
  $sql = "
    SELECT DISTINCT a.cve_articulo AS cve_articulo, 
        a.des_articulo AS Descripcion, 
        IF(IFNULL(a.control_numero_series, 'N') = 'S', p.LOTE, '') AS serie,
        IF(IFNULL(a.control_lotes, 'N') = 'S', p.LOTE, '') AS lote, 
        IF(IFNULL(a.Caduca, 'N') = 'S', l.Caducidad, '') AS Caducidad, 
        a.num_multiplo,
        IFNULL(t.Precio_unitario, 0) AS precio,
        p.revisadas AS Cantidad 
    FROM td_surtidopiezas p
    LEFT JOIN td_pedido t ON t.Fol_folio = p.fol_folio AND p.Cve_articulo = t.Cve_articulo
    LEFT JOIN th_pedido th ON th.Fol_folio = t.Fol_folio
    LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo
    LEFT JOIN c_lotes l ON l.cve_articulo = p.Cve_articulo AND p.LOTE = l.Lote
    WHERE a.Cve_articulo = p.Cve_articulo AND th.status = 'F' {$sql_cliente}
    ";
  $res_articulos = getArraySQL($sql);

  $array = [
    "res_articulos" => $res_articulos
  ];
  echo json_encode($array);
}

if($_POST['action'] === 'traer_lotes')
{
  $data = $ga->traer_lotes($_POST);
  echo json_encode(array(
    "success" => true,
    "lotes"  => $data,
  ));
}

if($_POST['action'] === 'hora_actual')
{
  $data = $ga->hora_actual($_POST);
  echo json_encode(array(
    "success" => true,
    "hora_actual"  => strval($data[0]->hora_actual),
  ));
}

if($_POST['action'] === 'traer_folio_R')
{
  $data = $ga->traer_folio_R($_POST);
 
  echo json_encode(array(
    "success" => true,
    "data"  => $data,
    
  ));
}  

if($_POST['action'] === 'guardar_entrada')
{
  $data = $ga->guardar_entrada($_POST);
  echo json_encode(array(
    "success" => true,
    "data"  => $data,
  ));
}

if($_POST['action'] === 'activos_fijos')
{
  $data = $ga->activos_fijos($_POST);
  echo json_encode(array(
    "success" => true,
    "data"  => $data,
  ));
}

if ($_POST['action'] === "getUnidadesCaja") 
{
    $cve_articulo = $_POST['cve_articulo'];
    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT num_multiplo, control_peso, control_lotes FROM c_articulo where cve_articulo = '$cve_articulo'";

  $res = "";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $num_multiplo = $row['num_multiplo'];
    $control_lotes = $row['control_lotes'];
    $control_peso = $row['control_peso'];

    echo json_encode(array(
      "success" => true,
      "num_multiplo"   => $num_multiplo,
      "control_lotes"  => $control_lotes,
      "control_peso"   => $control_peso
    ));
}

if ($_POST['action'] === "getDetallesFolio") 
{
    $folio = $_POST['folio'];
    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT DISTINCT
                    td.ID_Aduana AS ID_Aduana,
                    td.cve_articulo AS clave,
                    (SELECT des_articulo FROM c_articulo WHERE cve_articulo = td.cve_articulo) AS descripcion,
                    COALESCE((SELECT SUM(CantidadRecibida) FROM td_entalmacen WHERE cve_articulo = td.cve_articulo AND num_orden = td.num_orden), 0) AS surtidas,
                    (ar.peso * (SELECT surtidas))AS peso,
                    ((ar.alto/1000) * (ar.ancho/1000) * (ar.fondo/1000) * (SELECT surtidas)) AS volumen,
                    IF((SELECT COUNT(*) serie FROM c_articulo c, td_aduana ctd WHERE c.cve_articulo = td.cve_articulo AND c.control_numero_series = 'S' AND ctd.cve_articulo = c.cve_articulo AND ctd.num_orden = '{$folio}') = 0, td.cve_lote, '') AS lote,
                    IF(td.caducidad = '0000-00-00 00:00:00', '', DATE_FORMAT(td.caducidad, '%d-%m-%Y')) AS caducidad,
                    IF((SELECT COUNT(*) serie FROM c_articulo c, td_aduana ctd WHERE c.cve_articulo = td.cve_articulo AND c.control_numero_series = 'S' AND ctd.cve_articulo = c.cve_articulo AND ctd.num_orden = '{$folio}') > 0, td.cve_lote, '') AS serie,
                    SUM(td.cantidad) AS pedidas,
                    td.costo AS precioU,
                    (td.costo*td.cantidad) AS importeTotal
            FROM td_aduana td, c_articulo ar, th_aduana th
            WHERE ar.cve_articulo = td.cve_articulo AND td.num_orden = th.num_pedimento AND td.num_orden = '$folio'
            GROUP BY lote, serie";

  $res = "";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
/*
    $responce = "";
    while ($row = mysqli_fetch_array($res)) {
      $responce .= "<option value='".$row['clave']."'>".$row['descripcion']."</option>";
    }

    echo utf8_encode($responce);
*/

    while ($row = mysqli_fetch_array($res)) 
    {
        $lote_serie = "";
        if($row['lote']) $lote_serie = " - LOTE [".$row['lote']."]";
        if($row['serie']) $lote_serie = " - SERIE [".$row['serie']."]";
        $row=array_map('utf8_encode', $row);
        $responce->rows[0].="<option value='".$row['ID_Aduana']."*-*".$row['clave']."*-*".$row['pedidas']."*-*".$row['lote']."*-*".$row['serie']."*-*".$row['caducidad']."'>"."[".$row['clave']."] - ".$row['descripcion'].$lote_serie."</option>";
    }
    echo json_encode($responce);

}
