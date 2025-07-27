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
     //
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
    echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";;
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

if($_POST['action'] === 'traer_ordenesRealizadas')
{
  $data = $ga->traer_ordenesRealizadas($_POST);
  
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

if($_POST['action'] === 'traer_facturas')
{
  $data = $ga->traer_facturas($_POST);
  
  echo json_encode(array(
    "success" => true,
    "facturas" => $data,
  ));
}

if($_POST['action'] === 'traer_todos_los_articulos')
{
  $data_todos = $ga->traer_todos_los_articulos_proveedores($_POST);
  $data_lotes = $ga->articulos_con_lotes($_POST);
  $data_series = $ga->articulos_con_series($_POST);
  $data_peso = $ga->articulos_con_peso($_POST);
     
  echo json_encode(array(
    "success" => true,
    "todos_los_articulos"  => $data_todos,
    "articulos_con_lotes"  => $data_lotes,
    "articulos_con_series" => $data_series,
    "articulos_con_peso"   => $data_peso,
 
  ));
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

if($_POST['action'] === 'traer_folio_Dev_Proveedores')
{
  $data = $ga->traer_folio_Dev_Proveedores($_POST);
 
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
