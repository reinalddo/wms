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



if( $_POST['action'] == 'load_lista')
{
  $id = $_POST["codigo"];

  $sql = "
    SELECT l.Lista, l.Tipo, DATE_FORMAT(l.FechaIni, '%d-%m-%Y') AS FechaIni, DATE_FORMAT(l.FechaFin, '%d-%m-%Y') AS FechaFin, c.clave, IFNULL(l.id_moneda, '') as id_moneda
    FROM listap l
    LEFT JOIN c_almacenp c ON c.id = l.Cve_Almac
    WHERE l.id = {$id}
    ";
  $cab_lista = getArraySQL($sql);

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $tipo_servicio = 'N';
    $sql_tiposervicio = "SELECT TipoServ FROM listap WHERE id = '$id'";
    if (!($res_servicio = mysqli_query($conn, $sql_tiposervicio)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $tipo_servicio = mysqli_fetch_array($res_servicio)['TipoServ'];


  $sql = "
    SELECT l.Cve_Articulo, a.des_articulo, l.PrecioMin, l.PrecioMax, a.costo, l.ComisionPor, l.ComisionMon 
    FROM detallelp l 
    LEFT JOIN c_articulo a ON a.cve_articulo = l.Cve_Articulo
    WHERE l.ListaId = {$id}
    ";

if($tipo_servicio == 'S')
    $sql = "SELECT l.Cve_Articulo, a.Des_Servicio AS des_articulo, l.PrecioMin, l.PrecioMax, l.ComisionPor, l.ComisionMon, 0 as costo
            FROM detallelp l 
            LEFT JOIN c_servicios a ON a.Cve_Servicio = l.Cve_Articulo
            WHERE l.ListaId = $id
    ";

  $det_lista = getArraySQL($sql);

  $arr = array(
    "success" => true,
    "cab_lista"=>$cab_lista,
    "det_lista"=>$det_lista
  );
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
    $nombre_lista  = $_POST['nombre_lista'];
    $Almcen        = $_POST['Almcen'];
    $fechaini      = $_POST['fechaini'];
    $fechafin      = $_POST['fechafin'];
    $tipo_lista    = $_POST['tipo_lista'];
    $arrDetalle    = $_POST['arrDetalle'];
    $moneda        = $_POST['moneda'];

   $tipo_servicio = 'N';
   if(isset($_POST['tipo_servicio']))
   {
      if($_POST['tipo_servicio'] == 1)
        $tipo_servicio = 'S';
   }


    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT id FROM c_almacenp WHERE clave = '{$Almcen}'";
    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $id_almac  = mysqli_fetch_assoc($res)['id'];

    $sql = "INSERT INTO listap(Lista, Tipo, FechaIni, FechaFin, Cve_Almac, TipoServ, id_moneda) VALUES('{$nombre_lista}', {$tipo_lista}, STR_TO_DATE('{$fechaini}', '%d-%m-%Y'), STR_TO_DATE('{$fechafin}', '%d-%m-%Y'), {$id_almac}, '$tipo_servicio', '$moneda')";

    $res = "";
      if (!($res = mysqli_query($conn, $sql))) {
          echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
      }


    $sql = "SELECT IFNULL(MAX(id), 0) as id FROM listap";
    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $id = mysqli_fetch_assoc($res)['id'];

      foreach($arrDetalle as $row)
      {
          $cve_articulo = $row['codigo'];
          $preciomin    = $row['preciomin'];
          $preciomax    = $row['preciomax'];
          $comisionporc = $row['comisionporc'];
          $comisionprec = $row['comisionprec'];

          $sql = "INSERT INTO detallelp(ListaId, Cve_Articulo, PrecioMin, PrecioMax, Cve_Almac, ComisionPor, ComisionMon) VALUES({$id}, '{$cve_articulo}', {$preciomin}, {$preciomax}, {$id_almac}, {$comisionporc}, {$comisionprec})";
          if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
      }

    echo json_encode($responce);
} 

if( $_POST['action'] == 'edit' )
{
    $id_lista      = $_POST['id_lista'];
    $nombre_lista  = $_POST['nombre_lista'];
    $Almcen        = $_POST['Almcen'];
    $fechaini      = $_POST['fechaini'];
    $fechafin      = $_POST['fechafin'];
    $tipo_lista    = $_POST['tipo_lista'];
    //$arrDetalle    = $_POST['arrDetalle'];
    $arrDetalleBorrar  = $_POST['arrDetalleBorrar'];
    $arrDetalleAgregar = $_POST['arrDetalleAgregar'];
    $moneda = $_POST['moneda'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT id FROM c_almacenp WHERE clave = '{$Almcen}'";
    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $id_almac  = mysqli_fetch_assoc($res)['id'];

    $sql = "UPDATE listap SET Lista = '{$nombre_lista}', 
                              Tipo = {$tipo_lista}, 
                              FechaIni = STR_TO_DATE('{$fechaini}', '%d-%m-%Y'), 
                              FechaFin = STR_TO_DATE('{$fechafin}', '%d-%m-%Y'), 
                              id_moneda = '$moneda', 
                              Cve_Almac = {$id_almac}
                          WHERE id = {$id_lista}";

    $res = "";
      if (!($res = mysqli_query($conn, $sql))) {
          echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
      }

    $res = "";

    if($arrDetalleBorrar)
    {
      foreach($arrDetalleBorrar as $rowArt)
      {
          $art_borrar = $rowArt['codigo'];
          $sql = "DELETE FROM detallelp WHERE ListaId = {$id_lista} AND Cve_Articulo = '{$art_borrar}'";
          if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
      }
    }
    $sql_inserts = "";
    $sql_i = 0;
    $sql = "";
    //$count = count($arrDetalle);
    if($arrDetalleAgregar)
    {
      foreach($arrDetalleAgregar as $row)
      {
          $cve_articulo = $row['codigo'];
          $preciomin    = $row['preciomin'];
          $preciomax    = $row['preciomax'];
          $comisionporc = $row['comisionporc'];
          $comisionprec = $row['comisionprec'];

          //$sql_in = "INSERT INTO detallelp(ListaId, Cve_Articulo, PrecioMin, PrecioMax, Cve_Almac, ComisionPor, ComisionMon) VALUES({$id_lista}, '{$cve_articulo}', {$preciomin}, {$preciomax}, {$id_almac}, {$comisionporc}, {$comisionprec});";
          $sql .= "INSERT INTO detallelp(ListaId, Cve_Articulo, PrecioMin, PrecioMax, Cve_Almac, ComisionPor, ComisionMon) VALUES({$id_lista}, '{$cve_articulo}', {$preciomin}, {$preciomax}, {$id_almac}, {$comisionporc}, {$comisionprec});";

          //$sql_i++;
          //$sql_inserts .= $sql_i.".-".$sql_in."\n";
      }
      if (!($res = mysqli_multi_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    }
    
      //$responce = $sql_inserts."\n"."Count = ".$count;
      $responce = "";
    echo json_encode($responce);
} 

if( $_POST['action'] == 'eliminar_destinatario' )
{
    $id_lista         = $_POST['id_lista'];
    $id_destinatario  = $_POST['id_destinatario'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //NECESITO SABER SI EL DESTINATARIO ESTÁ ASIGNADO EN ALGUN TIPO DE LISTA PARA SABER SI VOY A ELIMINAR O MODIFICAR
    $sql = "SELECT COUNT(*) as existe FROM RelCliLis WHERE Id_Destinatario = {$id_destinatario} AND (IFNULL(ListaD, 0) > 0  OR IFNULL(ListaPromo, 0) > 0)";
    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $existe = mysqli_fetch_assoc($res)["existe"];
   
    $sql = "DELETE FROM RelCliLis WHERE Id_Destinatario = {$id_destinatario}";

    if($existe)
      $sql = "UPDATE RelCliLis SET ListaP = NULL WHERE Id_Destinatario = {$id_destinatario}";

    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $arr = array(
      "success"=>true
    );
    echo json_encode($arr);
} 

if( $_POST['action'] == 'asignar_destinatario' )
{
    $id_lista         = $_POST['id_lista'];
    $id_destinatario  = $_POST['id_destinatario'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT COUNT(*) as existe FROM RelCliLis WHERE Id_Destinatario = {$id_destinatario}";
    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $existe = mysqli_fetch_assoc($res)["existe"];
   
    $sql = "INSERT INTO RelCliLis(Id_Destinatario, ListaP) VALUES ({$id_destinatario}, {$id_lista})";

    if($existe)
      $sql = "UPDATE RelCliLis SET ListaP = {$id_lista} WHERE Id_Destinatario = {$id_destinatario}";

    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $arr = array(
      "success"=>true
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

if( $_POST['action'] == 'deleteList' )
{

  $id_lista = $_POST['id_lista'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "DELETE FROM listap WHERE id= $id_lista";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $sql = "UPDATE RelCliLis SET ListaP=NULL WHERE ListaP= $id_lista";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $sql = "DELETE FROM detallelp WHERE ListaId= $id_lista";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

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
