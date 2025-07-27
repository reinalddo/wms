<?php
include '../../../app/load.php';
// Initalize Slim
$app = new \Slim\Slim();
if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {exit();}

$ga = new \OrdenCompra\OrdenCompra();
/*
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
*/


function ConectarSAP($metodo) 
{
  $endPoint = '';
  $json = '';
  
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//***********************************************************************************************************

  $sql = "SELECT * FROM c_datos_sap WHERE Activo = 1;";
  if (!($res = mysqli_query($conn, $sql))) 
  {
    echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
  }

    $row = mysqli_fetch_array($res);
    $endPoint = $row['Url'].$funcion;
    $usuario  = $row['User'];
    $password = $row['Pswd'];
    $BD       = $row['BaseD'];

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

$response = curl_exec($curl);

 curl_close($curl);

  //echo ($response);
  return $response;
}



if( $_POST['action'] == 'load')
{
  $arr2 = array();
  $ga->num_pedimento = $_POST["codigo"];
  $ga->__get("num_pedimento");
  $arr = array(
        "success" => true,
  );
  $ga->__getDetalle("num_pedimento");
  foreach ($ga->data as $nombre => $valor) $arr2[$nombre] = $valor;
  $arr2["detalle"] = $ga->dataDetalle;
  $arr2["sinCompletar"] = $ga->dataDetalle2;
  $arr = array_merge($arr, $arr2);
  echo json_encode($arr);
}



if($_POST['action'] === 'receiveOC')
{
  //$ga->calcularCostoPromedio($_POST);

  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//***********************************************************************************************************


  $result = $ga->receiveOC($_POST);

  $instanciasap = false;
  //if($_SERVER['HTTP_HOST'] == 'dev.assistpro-adl.com')
/*
  if($_SERVER['HTTP_HOST'] == 'fc.assistpro-adl.com')
  {
      $instanciasap = true;

      $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
      if (!($res_charset = mysqli_query($conn, $sql_charset)))
          echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
      $charset = mysqli_fetch_array($res_charset)['charset'];
      mysqli_set_charset($conn , $charset);

      $json = "";
      $sql = "SELECT e.cve_articulo, 
                     (IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE cve_articulo = e.cve_articulo AND tipo = 'ubicacion'), 0)+IFNULL(SUM(e.CantidadRecibida), 0)) AS Cantidad, 
                     u.cve_umed AS UM, 
                     NOW() AS fecha_operacion, 
                     th.Cve_Almac
              FROM td_entalmacen e
              LEFT JOIN th_entalmacen th ON th.Fol_Folio = e.fol_folio
              LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
              LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
              WHERE e.fol_folio = (SELECT MAX(Fol_Folio) FROM th_entalmacen) 
              GROUP BY cve_articulo";
      if (!($res = mysqli_query($conn, $sql))) 
      {
          echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
      }
      $json = '[';
      while($row = mysqli_fetch_array($res))
      {
        $json .= '{';

          $cve_articulo    = $row["cve_articulo"];
          $Cantidad        = $row["Cantidad"];
          $UM              = $row["UM"];
          $fecha_operacion = $row["fecha_operacion"];
          $Cve_Almac       = $row["Cve_Almac"];

          $json .= '"item": "'.$cve_articulo.'",';
          $json .= '"um": "'.$UM.'",';
          $json .= '"qty": '.$Cantidad.',';
          $json .= '"typeMov": "T",';
          $json .= '"warehouse": "'.$Cve_Almac.'",';
          $json .= '"dataOpe": "'.$fecha_operacion.'"';

        $json .= '},';
      }
      $json[strlen($json)-1] = ' ';


      $json .= ']';

      mysqli_close($conn);
      $sesion = ConectarSAP('Post');

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

  //echo $response;
//****************************************************************************************
//****************************************************************************************

  }
*/
    echo json_encode(array(
      "success" => true,
      "data"=> $result,
      "instanciasap" => $instanciasap,
      "json" => $json,
      "responseSAP" => $response
      //"success" => true
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
  /*
    if (!empty($ga->data->ID_Aduana)) {
      $success = false;
    }
    */
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

if( $_POST['action'] == 'ExisteLote' )
{
  $lote = $_POST['lote'];
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


if( $_POST['action'] == 'CambiarStatusOC' )
{
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $oc = $_POST['oc'];
  $status = $_POST['status'];
  
  $sql = "
    UPDATE th_aduana SET status = '$status' WHERE num_pedimento = {$oc};
    ";
  if (!($res = mysqli_query($conn, $sql))) 
  {
      echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }

  if($status == 'T')
    $status = 'E';

  if($status == 'C')
    $status = 'P';

  $sql = "
    UPDATE th_entalmacen SET status = '$status' WHERE id_ocompra = {$oc};
    ";
  if (!($res = mysqli_query($conn, $sql))) 
  {
      echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
  }

  mysqli_close($conn);

  $array = [
    "success"=>true
  ];

  echo json_encode($array);

}

if( $_POST['action'] == 'EliminarOC' )
{
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $oc = $_POST['oc'];
  $sql = "
    DELETE FROM th_aduana WHERE num_pedimento = {$oc};
    ";
  if (!($res = mysqli_query($conn, $sql))) 
  {
      echo "Falló la preparación 1: (" . mysqli_error($conn) . ") ";
  }

  $sql = "
    DELETE FROM td_aduana WHERE num_orden = {$oc};
    ";

  if (!($res = mysqli_query($conn, $sql))) 
  {
      echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
  }

  $sql = "
    DELETE FROM td_aduanaxtarima WHERE Num_Orden = {$oc};
    ";

  if (!($res = mysqli_query($conn, $sql))) 
  {
      echo "Falló la preparación 2: (" . mysqli_error($conn) . ") ";
  }

  $sql_entrada_rel = "SELECT Fol_Folio FROM th_entalmacen WHERE id_ocompra = {$oc}";
  $res = mysqli_query($conn, $sql_entrada_rel);
  $entrada_relacionada = mysqli_fetch_assoc($res)['Fol_Folio'];

  if($entrada_relacionada != '')
  {
    $folio = $entrada_relacionada;
      $entrada = "DELETE FROM t_cardex WHERE origen = '{$folio}';";
      $query = mysqli_query($conn, $entrada);

      $entrada = "DELETE FROM t_MovCharolas WHERE origen = '{$folio}';";
      $query = mysqli_query($conn, $entrada);

      $entrada = "DELETE FROM td_entalmacen_fotos WHERE td_entalmacen_producto_id = (SELECT id FROM th_entalmacen_fotos WHERE th_entalmacen_folio = '{$folio}');";
      $query = mysqli_query($conn, $entrada);

      $entrada = "DELETE FROM th_entalmacen_fotos WHERE th_entalmacen_folio = '{$folio}';";
      $query = mysqli_query($conn, $entrada);

      $entrada = "DELETE FROM th_entalmacen WHERE Fol_Folio = '{$folio}';";
      $query = mysqli_query($conn, $entrada);

      $entrada = "DELETE FROM td_entalmacen WHERE fol_folio = '{$folio}';";
      $query = mysqli_query($conn, $entrada);

      $entrada = "DELETE FROM td_entalmacenxtarima WHERE fol_folio = '{$folio}';";
      $query = mysqli_query($conn, $entrada);
  }

  mysqli_close($conn);

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

if($_POST['action'] === 'traer_lps')
{
  $data = $ga->traer_lps($_POST);
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

if($_POST['action'] === 'traer_crossdocking')
{
  $data = $ga->traer_crossdocking($_POST);
  
  echo json_encode(array(
    "success" => true,
    "crossdocking" => $data,
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
  $data_todos = $ga->traer_todos_los_articulos($_POST);
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

    $sql = "SELECT num_multiplo, control_peso, control_lotes, control_numero_series FROM c_articulo where cve_articulo = '$cve_articulo'";

  $res = "";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $num_multiplo = $row['num_multiplo'];
    $control_lotes = $row['control_lotes'];
    $control_numero_series = $row['control_numero_series'];
    $control_peso = $row['control_peso'];

    echo json_encode(array(
      "success" => true,
      "num_multiplo"   => $num_multiplo,
      "control_lotes"  => $control_lotes,
      "control_numero_series"  => $control_numero_series,
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
