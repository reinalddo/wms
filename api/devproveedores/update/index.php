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

if( $_POST['action'] == 'proveedores' )
{
  $almacen = $_POST['almacen'];
  $id_user = $_POST['id_user'];

  $sql = "
    SELECT DISTINCT * FROM(
         SELECT DISTINCT
            IFNULL(
                (SELECT ID_Proveedor FROM c_proveedores WHERE ID_Proveedor = (
                    IFNULL(
                        (SELECT ID_Proveedor FROM ts_existenciapiezas WHERE ts_existenciapiezas.idy_ubica = e.cve_ubicacion AND ts_existenciapiezas.cve_articulo = e.cve_articulo LIMIT 1),
                        IFNULL(
                            (SELECT ID_Proveedor FROM ts_existenciacajas WHERE ts_existenciacajas.idy_ubica = e.cve_ubicacion AND ts_existenciacajas.cve_articulo = e.cve_articulo LIMIT 1),
                            IFNULL(
                                (SELECT ID_Proveedor FROM ts_existenciatarima WHERE ts_existenciatarima.idy_ubica = e.cve_ubicacion AND ts_existenciatarima.cve_articulo = e.cve_articulo LIMIT 1),
                                0
                            )
                        )
                    )
                )),'--'
            )AS id_proveedor,
            IFNULL(
                (SELECT nombre FROM c_proveedores WHERE ID_Proveedor = (
                    IFNULL(
                        (SELECT ID_Proveedor FROM ts_existenciapiezas WHERE ts_existenciapiezas.idy_ubica = e.cve_ubicacion AND ts_existenciapiezas.cve_articulo = e.cve_articulo LIMIT 1),
                        IFNULL(
                            (SELECT ID_Proveedor FROM ts_existenciacajas WHERE ts_existenciacajas.idy_ubica = e.cve_ubicacion AND ts_existenciacajas.cve_articulo = e.cve_articulo LIMIT 1),
                            IFNULL(
                                (SELECT ID_Proveedor FROM ts_existenciatarima WHERE ts_existenciatarima.idy_ubica = e.cve_ubicacion AND ts_existenciatarima.cve_articulo = e.cve_articulo LIMIT 1),
                                0
                            )
                        )
                    )
                )),'--'
            )AS proveedor
            FROM
                V_ExistenciaGral e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion 
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor 
            LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.num_orden != ''
            WHERE e.cve_almac = ap.id AND ap.clave = '{$almacen}'  AND e.Existencia > 0 AND e.tipo = 'ubicacion'
            ORDER BY l.CADUCIDAD ASC
                )X
            WHERE X.id_proveedor != '--'
    ";
  $res_proveedores = getArraySQL($sql);

  $sql = "
    SELECT IFNULL(id_ocompra, '') as id_ocompra, Cve_Proveedor, IF(Fact_Prov = '', '--', Fact_Prov) as Factura FROM th_entalmacen WHERE Cve_Almac = '{$almacen}';
    ";
  $res_factura = getArraySQL($sql);

  $sql = "
    SELECT DISTINCT * FROM(
       SELECT DISTINCT
          IF(a.cve_articulo IN (SELECT Cve_Articulo FROM t_movcuarentena WHERE Idy_Ubica = u.idy_ubica), 'Si', 'No') AS QA,
          u.idy_ubica,
          u.CodigoCSD AS codigo
          FROM
              V_ExistenciaGral e
          LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
          LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion 
          LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
          LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
          LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
          LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor 
          LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.num_orden != ''
              WHERE e.cve_almac = ap.id AND ap.clave = '{$almacen}' AND e.Existencia > 0 AND e.tipo = 'ubicacion'
          ORDER BY l.CADUCIDAD ASC) AS X
    WHERE X.QA = 'No';
  ";
  $res_bl = getArraySQL($sql);

  $sql = "
      SELECT DISTINCT
        ch.IDContenedor,
        IF(ch.TipoGen = 1, CONCAT(ch.descripcion, ' (Pallet Genérico)'), ch.descripcion) AS descripcion,
        ch.clave_contenedor AS clave_contenedor,
        ch.CveLP,
        IF(ch.TipoGen = 1, 'S', 'N') AS Generico
      FROM c_charolas ch
      LEFT JOIN c_almacenp ON c_almacenp.clave = ch.cve_almac
      WHERE (ch.Activo = 1 AND ch.IDContenedor NOT IN (SELECT ntarima FROM t_tarima WHERE Abierta = 0) 
      AND (ch.clave_contenedor NOT IN (SELECT ClaveEtiqueta FROM td_entalmacenxtarima) OR ch.CveLP NOT IN (SELECT ClaveEtiqueta FROM td_entalmacenxtarima))) OR ch.TipoGen = 1 AND c_almacenp.clave='$almacen'
      GROUP BY ch.IDContenedor  
      ORDER BY Generico DESC
";
  $res_pallets = getArraySQL($sql);

  $sql = "SELECT id_umed, cve_umed, des_umed FROM c_unimed WHERE Activo = 1";
  $res_unidad_medida = getArraySQL($sql);

  $sql = "
    SELECT (IFNULL(MAX(folio_dev), 0)+1) AS Consecutivo FROM c_devproveedores;
    ";
  $res_consecutivo = getArraySQL($sql);

  $sql = "
    SELECT cve_usuario,nombre_completo FROM c_usuario WHERE id_user = '$id_user';
    ";
  $res_usuario = getArraySQL($sql);

  $array = [
    "res_proveedores"   => $res_proveedores,
    "res_usuario"       => $res_usuario,
    "res_factura"       => $res_factura,
    "res_bl"            => $res_bl,
    "res_pallets"       => $res_pallets,
    "res_unidad_medida" => $res_unidad_medida,
    "Consecutivo"       => $res_consecutivo
  ];
  echo json_encode($array);
}

if( $_POST['action'] == 'proveedor_factura' )
{
  $almacen = $_POST['almacen'];
  $proveedor = $_POST['proveedor'];

  $sql_proveedor = "";$sql_proveedor_bl = ""; $mostrar_factura = "AND 0";
  if($proveedor) {$sql_proveedor = "AND Cve_Proveedor = '$proveedor'"; $sql_proveedor_bl = "AND X.id_proveedor = '$proveedor'"; $mostrar_factura = "AND 1";}

  $sql = "
    SELECT DISTINCT * FROM(
       SELECT DISTINCT
          IF(a.cve_articulo IN (SELECT Cve_Articulo FROM t_movcuarentena WHERE Idy_Ubica = u.idy_ubica), 'Si', 'No') AS QA,
            IFNULL(
                (SELECT ID_Proveedor FROM c_proveedores WHERE ID_Proveedor = (
                    IFNULL(
                        (SELECT ID_Proveedor FROM ts_existenciapiezas WHERE ts_existenciapiezas.idy_ubica = e.cve_ubicacion AND ts_existenciapiezas.cve_articulo = e.cve_articulo LIMIT 1),
                        IFNULL(
                            (SELECT ID_Proveedor FROM ts_existenciacajas WHERE ts_existenciacajas.idy_ubica = e.cve_ubicacion AND ts_existenciacajas.cve_articulo = e.cve_articulo LIMIT 1),
                            IFNULL(
                                (SELECT ID_Proveedor FROM ts_existenciatarima WHERE ts_existenciatarima.idy_ubica = e.cve_ubicacion AND ts_existenciatarima.cve_articulo = e.cve_articulo LIMIT 1),
                                0
                            )
                        )
                    )
                )),'--'
            )AS id_proveedor,
          u.idy_ubica,
          u.CodigoCSD AS codigo
          FROM
              V_ExistenciaGral e
          LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
          LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion 
          LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
          LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
          LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
          LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor 
          LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.num_orden != ''
              WHERE e.cve_almac = ap.id AND ap.clave = '{$almacen}' AND e.Existencia > 0 AND e.tipo = 'ubicacion' 
          ORDER BY l.CADUCIDAD ASC) AS X
    WHERE X.QA = 'No' {$sql_proveedor_bl};
    ";
  $res_bl = getArraySQL($sql);

  $sql = "SELECT IFNULL(id_ocompra, '') as id_ocompra, Cve_Proveedor, IF(Fact_Prov = '', '--', Fact_Prov) as Factura 
          FROM th_entalmacen 
          WHERE Cve_Almac = '{$almacen}' {$sql_proveedor} {$mostrar_factura};
    ";
  $res_factura = getArraySQL($sql);



  $sql = "
    SELECT DISTINCT * FROM(
         SELECT DISTINCT
            
            u.CodigoCSD AS codigo,
            e.cve_ubicacion,
             u.idy_ubica,
            IF(a.cve_articulo IN (SELECT Cve_Articulo FROM t_movcuarentena WHERE Idy_Ubica = u.idy_ubica), 'Si', 'No') AS QA,
            a.cve_articulo AS cve_articulo,
            a.des_articulo AS Descripcion,
            COALESCE(IF(a.control_lotes ='S',l.LOTE,''), '--') AS lote,
            COALESCE(IF(IFNULL(a.caduca, 'N') = 'S',IF(DATE_FORMAT(l.Caducidad, '%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS Caducidad,
            COALESCE(IF(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') AS serie,
            e.Existencia AS Cantidad,
            IFNULL(
                (SELECT ID_Proveedor FROM c_proveedores WHERE ID_Proveedor = (
                    IFNULL(
                        (SELECT ID_Proveedor FROM ts_existenciapiezas WHERE ts_existenciapiezas.idy_ubica = e.cve_ubicacion AND ts_existenciapiezas.cve_articulo = e.cve_articulo LIMIT 1),
                        IFNULL(
                            (SELECT ID_Proveedor FROM ts_existenciacajas WHERE ts_existenciacajas.idy_ubica = e.cve_ubicacion AND ts_existenciacajas.cve_articulo = e.cve_articulo LIMIT 1),
                            IFNULL(
                                (SELECT ID_Proveedor FROM ts_existenciatarima WHERE ts_existenciatarima.idy_ubica = e.cve_ubicacion AND ts_existenciatarima.cve_articulo = e.cve_articulo LIMIT 1),
                                0
                            )
                        )
                    )
                )),'--'
            )AS id_proveedor,
            IFNULL(ent.num_orden, '') AS folio_oc,
            TRUNCATE(a.costo,2) AS precio,
            a.num_multiplo
            FROM
                V_ExistenciaGral e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion 
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor 
            LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.num_orden != ''
                WHERE e.cve_almac = ap.id AND ap.clave = '{$almacen}' AND e.Existencia > 0 AND e.tipo = 'ubicacion' 
            ORDER BY l.CADUCIDAD ASC
                )X 
            WHERE X.QA = 'No' {$sql_proveedor_bl};
    ";
  $res_articulos = getArraySQL($sql);


  $array = [
    "res_articulos"=>$res_articulos,
    "res_bl"=>$res_bl,
    "res_factura" => $res_factura
  ];
  echo json_encode($array);
}

if( $_POST['action'] == 'pedidos_cliente' )
{
  $pedido = $_POST['pedido'];

  $sql_pedido = "";
  if($pedido) $sql_pedido = "AND a.Fol_folio = '$pedido'";

  $sql = "
    SELECT  DISTINCT a.Cve_clte, c.RazonSocial, a.Pick_Num AS Factura
    FROM th_pedido a
    LEFT JOIN c_cliente c ON c.Cve_Clte = a.Cve_clte
    WHERE a.status = 'F' {$sql_pedido}
    ";
  $res_clientes = getArraySQL($sql);

  $sql = "
    SELECT DISTINCT a.cve_articulo AS cve_articulo, 
        a.des_articulo AS Descripcion, 
        IF(IFNULL(a.control_numero_series, 'N') = 'S', p.LOTE, '') AS serie,
        IF(IFNULL(a.control_lotes, 'N') = 'S', p.LOTE, '') AS lote, 
        IF(IFNULL(a.Caduca, 'N') = 'S', DATE_FORMAT(l.Caducidad, '%Y-%m-%d'), '') AS Caducidad, 
        a.num_multiplo,
        TRUNCATE(IFNULL(t.Precio_unitario, 0), 2) as precio,
        p.revisadas AS Cantidad 
    FROM td_surtidopiezas p
    LEFT JOIN td_pedido t ON t.Fol_folio = p.fol_folio AND p.Cve_articulo = t.Cve_articulo
    LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo
    LEFT JOIN c_lotes l ON l.cve_articulo = p.Cve_articulo AND p.LOTE = l.Lote
    WHERE p.fol_folio = '$pedido' AND a.Cve_articulo = p.Cve_articulo
    ";
  $res_articulos = getArraySQL($sql);

  $array = [
    "res_clientes" => $res_clientes,
    "res_articulos" => $res_articulos
  ];
  echo json_encode($array);
}



if( $_POST['action'] == 'bl_articulos' )
{
  $almacen = $_POST['almacen'];
  $bl = $_POST['bl'];

  $sql_factura = ""; $mostrar_factura = "AND 0";
  if($bl) {
      //$sql_factura = "AND a.Pick_Num = '$bl'"; 
      $mostrar_factura = "AND 1";
  }

  $sql = "
    SELECT DISTINCT * FROM(
         SELECT DISTINCT
            
            u.CodigoCSD AS codigo,
            e.cve_ubicacion,
             u.idy_ubica,
            IF(a.cve_articulo IN (SELECT Cve_Articulo FROM t_movcuarentena WHERE Idy_Ubica = u.idy_ubica), 'Si', 'No') AS QA,
            a.cve_articulo AS cve_articulo,
            a.des_articulo AS Descripcion,
            COALESCE(IF(a.control_lotes ='S',l.LOTE,''), '--') AS lote,
            COALESCE(IF(IFNULL(a.caduca, 'N') = 'S',IF(DATE_FORMAT(l.Caducidad, '%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS Caducidad,
            COALESCE(IF(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') AS serie,
            e.Existencia AS Cantidad,
            IFNULL(
                (SELECT ID_Proveedor FROM c_proveedores WHERE ID_Proveedor = (
                    IFNULL(
                        (SELECT ID_Proveedor FROM ts_existenciapiezas WHERE ts_existenciapiezas.idy_ubica = e.cve_ubicacion AND ts_existenciapiezas.cve_articulo = e.cve_articulo LIMIT 1),
                        IFNULL(
                            (SELECT ID_Proveedor FROM ts_existenciacajas WHERE ts_existenciacajas.idy_ubica = e.cve_ubicacion AND ts_existenciacajas.cve_articulo = e.cve_articulo LIMIT 1),
                            IFNULL(
                                (SELECT ID_Proveedor FROM ts_existenciatarima WHERE ts_existenciatarima.idy_ubica = e.cve_ubicacion AND ts_existenciatarima.cve_articulo = e.cve_articulo LIMIT 1),
                                0
                            )
                        )
                    )
                )),'--'
            )AS id_proveedor,
            IFNULL(ent.num_orden, '') AS folio_oc,
            TRUNCATE(a.costo,2) AS precio,
            a.num_multiplo
            FROM
                V_ExistenciaGral e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion 
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor 
            LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.num_orden != ''
                WHERE e.cve_almac = ap.id AND ap.clave = '{$almacen}' AND e.Existencia > 0 AND e.tipo = 'ubicacion' 
            ORDER BY l.CADUCIDAD ASC
                )X 
              WHERE X.QA = 'No' AND X.idy_ubica = '{$bl}';
    ";
  $res_articulos = getArraySQL($sql);

  $array = [
    "res_articulos" => $res_articulos
  ];
  echo json_encode($array);
}

if( $_POST['action'] == 'factura_proveedor' )
{
  $almacen = $_POST['almacen'];
  $oc = $_POST['oc'];

  $sql_factura = ""; $mostrar_factura = "AND 0";
  if($oc) {
      //$sql_factura = "AND a.Pick_Num = '$oc'"; 
      $mostrar_factura = "AND 1";
  }

  $sql = "
    SELECT DISTINCT * FROM(
       SELECT DISTINCT
          IF(a.cve_articulo IN (SELECT Cve_Articulo FROM t_movcuarentena WHERE Idy_Ubica = u.idy_ubica), 'Si', 'No') AS QA,
            IFNULL(
                (SELECT ID_Proveedor FROM c_proveedores WHERE ID_Proveedor = (
                    IFNULL(
                        (SELECT ID_Proveedor FROM ts_existenciapiezas WHERE ts_existenciapiezas.idy_ubica = e.cve_ubicacion AND ts_existenciapiezas.cve_articulo = e.cve_articulo LIMIT 1),
                        IFNULL(
                            (SELECT ID_Proveedor FROM ts_existenciacajas WHERE ts_existenciacajas.idy_ubica = e.cve_ubicacion AND ts_existenciacajas.cve_articulo = e.cve_articulo LIMIT 1),
                            IFNULL(
                                (SELECT ID_Proveedor FROM ts_existenciatarima WHERE ts_existenciatarima.idy_ubica = e.cve_ubicacion AND ts_existenciatarima.cve_articulo = e.cve_articulo LIMIT 1),
                                0
                            )
                        )
                    )
                )),'--'
            )AS id_proveedor,
          u.idy_ubica,
          IFNULL(ent.num_orden, '') AS folio_oc,
          u.CodigoCSD AS codigo
          FROM
              V_ExistenciaGral e
          LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
          LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion 
          LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
          LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
          LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
          LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor 
          LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.num_orden != ''
              WHERE e.cve_almac = ap.id AND ap.clave = '{$almacen}' AND e.Existencia > 0 AND e.tipo = 'ubicacion' 
          ORDER BY l.CADUCIDAD ASC) AS X
    WHERE X.QA = 'No' AND X.folio_oc = {$oc};
    ";
  $res_bl = getArraySQL($sql);

  $sql = "
    SELECT DISTINCT * FROM(
         SELECT DISTINCT
            
            u.CodigoCSD AS codigo,
            e.cve_ubicacion,
             u.idy_ubica,
            IF(a.cve_articulo IN (SELECT Cve_Articulo FROM t_movcuarentena WHERE Idy_Ubica = u.idy_ubica), 'Si', 'No') AS QA,
            a.cve_articulo AS cve_articulo,
            a.des_articulo AS Descripcion,
            COALESCE(IF(a.control_lotes ='S',l.LOTE,''), '--') AS lote,
            COALESCE(IF(IFNULL(a.caduca, 'N') = 'S',IF(DATE_FORMAT(l.Caducidad, '%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS Caducidad,
            COALESCE(IF(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') AS serie,
            e.Existencia AS Cantidad,
            IFNULL(
                (SELECT ID_Proveedor FROM c_proveedores WHERE ID_Proveedor = (
                    IFNULL(
                        (SELECT ID_Proveedor FROM ts_existenciapiezas WHERE ts_existenciapiezas.idy_ubica = e.cve_ubicacion AND ts_existenciapiezas.cve_articulo = e.cve_articulo LIMIT 1),
                        IFNULL(
                            (SELECT ID_Proveedor FROM ts_existenciacajas WHERE ts_existenciacajas.idy_ubica = e.cve_ubicacion AND ts_existenciacajas.cve_articulo = e.cve_articulo LIMIT 1),
                            IFNULL(
                                (SELECT ID_Proveedor FROM ts_existenciatarima WHERE ts_existenciatarima.idy_ubica = e.cve_ubicacion AND ts_existenciatarima.cve_articulo = e.cve_articulo LIMIT 1),
                                0
                            )
                        )
                    )
                )),'--'
            )AS id_proveedor,
            IFNULL(ent.num_orden, '') AS folio_oc,
            TRUNCATE(a.costo,2) AS precio,
            a.num_multiplo
            FROM
                V_ExistenciaGral e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion 
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor 
            LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.num_orden != ''
                WHERE e.cve_almac = ap.id AND ap.clave = '{$almacen}' AND e.Existencia > 0 AND e.tipo = 'ubicacion' 
            ORDER BY l.CADUCIDAD ASC
                )X 
              WHERE X.QA = 'No' AND X.folio_oc = {$oc};
    ";
  $res_articulos = getArraySQL($sql);

  $array = [
    "res_bl"=>$res_bl,
    "res_articulos" => $res_articulos
  ];
  echo json_encode($array);
}

if( $_POST['action'] == 'articulos_factura' )
{
  $almacen = $_POST['almacen'];
  $cve_articulo = $_POST['cve_articulo'];

  $sql = "SELECT DISTINCT IFNULL(th.id_ocompra, '') AS id_ocompra, th.Cve_Proveedor AS Cve_Proveedor, IF(th.Fact_Prov = '', '--', th.Fact_Prov) AS Factura 
          FROM th_entalmacen th
          LEFT JOIN td_entalmacen td ON td.num_orden = th.id_ocompra 
          WHERE Cve_Almac = '{$almacen}' AND td.cve_articulo = '{$cve_articulo}'";
  $res_factura = getArraySQL($sql);

  $array = [
    "res_factura" => $res_factura
  ];
  echo json_encode($array);
}


if( $_POST['action'] == 'guardar_devolucion' )
{
  $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $almacen = $_POST['almacen'];
  $folio_dev  = $_POST['folio_dev'];
  $usuario    = $_POST['usuario'];
  $zona_recepcion = $_POST['zona_recepcion'];
  $productos_recibidos = $_POST['productos_recibidos'];
  $tipo = $_POST['tipo'];

  if($tipo == 1) $tipo = 'DVP'; else $tipo = "DPL";

  $sql = "SELECT cve_usuario FROM c_usuario WHERE id_user = {$usuario}";
  $cve_user = mysqli_query($conexion, $sql);
  $cve_user = mysqli_fetch_assoc($cve_user);
  $cve_usuario = $cve_user["cve_usuario"];


  foreach ($productos_recibidos as $row) 
  {
      $cve_articulo         = $row["cve_articulo"];
      $lote                 = $row["lote"];
      $serie                = $row["serie"];
      $caducidad            = $row["caducidad"];
      $cantidad             = $row["cantidad"];
      $pallet_cont          = $row["pallet_cont"];
      $costo                = $row["costo"];
      $proveedor            = $row['proveedor'];
      $num_multiplo         = $row['num_multiplo'];
      $factura              = $row['factura'];
      $idy_ubica            = $row['bl_id'];
      $articulo_defectuoso  = $row["defectuoso"];

      $sql = "INSERT INTO th_entalmacen(Cve_Almac, Fec_Entrada, Cve_Usuario, Cve_Proveedor, STATUS, Cve_Autorizado, tipo, id_ocompra, placas, bufer, HoraInicio, ID_Protocolo, Consec_protocolo, cve_ubicacion, HoraFin, Fact_Prov) 
              VALUES ('$almacen', NOW(), '$cve_usuario', $proveedor, 'E', '$cve_usuario', '$tipo', NULL, '', '', NOW(), '1', '$folio_dev', '', NOW(), '$factura');";

      mysqli_set_charset($conexion, "utf8");
      if(!$result = mysqli_query($conexion, $sql))
      {
        echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
      }

      $lote_serie = "";
      if($lote)  $lote_serie = $lote;
      if($serie) $lote_serie = $serie;


      $sql = "SELECT MAX(Fol_Folio) as id_entrada FROM th_entalmacen";
      $ent_entrada = mysqli_query($conexion, $sql);
      $ent_entrada = mysqli_fetch_assoc($ent_entrada);
      $id_entrada = $ent_entrada["id_entrada"];

      $sql = "INSERT INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadPedida, CantidadRecibida, CantidadDisponible, numero_serie, status, cve_usuario, cve_ubicacion, fecha_inicio, fecha_fin, tipo_entrada, costoUnitario, num_orden) 
        VALUES (".$id_entrada.", '".$cve_articulo."', '".$lote_serie."', ".$cantidad.", ".$cantidad.", '', '".$lote_serie."', 'E', '".$cve_usuario."', '', NOW(), NOW(), 1, ".$costo.", '')";

        if(!$result = mysqli_query($conexion, $sql))
        {
          echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
        }

      $sql = "INSERT INTO c_devproveedores(folio_dev, folio_entrada, cve_articulo, cve_lote, caducidad, devueltas, idy_ubica, cve_contenedor, usuario, defectuoso, proveedor, factura) 
      VALUES({$folio_dev}, '{$id_entrada}', '{$cve_articulo}', '{$lote_serie}', '{$caducidad}', {$cantidad}, '{$idy_ubica}', '{$pallet_cont}', '{$usuario}', {$articulo_defectuoso}, {$proveedor}, '{$factura}')";
        mysqli_set_charset($conexion, "utf8");
        if(!$result = mysqli_query($conexion, $sql))
        {
          echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
        }

      $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - {$cantidad} WHERE idy_ubica = '{$idy_ubica}' AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$lote_serie}' AND cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')";

        if(!$result = mysqli_query($conexion, $sql))
        {
          echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";
        }

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
        IF(IFNULL(a.Caduca, 'N') = 'S', DATE_FORMAT(l.Caducidad, '%Y-%m-%d'), '') AS Caducidad, 
        a.num_multiplo,
        TRUNCATE(IFNULL(t.Precio_unitario, 0), 2) AS precio,
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
