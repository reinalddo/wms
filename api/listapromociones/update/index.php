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
    SELECT l.Lista, l.Descripcion, l.Tipo, DATE_FORMAT(l.FechaI, '%d-%m-%Y') AS FechaIni, DATE_FORMAT(l.FechaF, '%d-%m-%Y') AS FechaFin, c.clave, l.Caduca
    FROM ListaPromo l
    LEFT JOIN c_almacenp c ON c.id = l.Cve_Almac
    WHERE l.id = {$id}
    ";
  $cab_lista = getArraySQL($sql);


  $sql_base = "
    SELECT IF(IFNULL(l.Articulo, '') = '', l.cve_gpoart, l.Articulo) AS Articulo, IF(IFNULL(l.Articulo, '') = '', g.des_gpoart,a.des_articulo) AS des_articulo, 
            '' AS Monto, l.Cantidad AS Cantidad, u.id_umed, CONCAT('( ',u.cve_umed, ' ) ', u.des_umed) AS unimed, '' AS Nivel, 
            IF(IFNULL(l.Articulo, '') = '', 1, 0) as TipoReg,
            0 AS Tipo
            FROM DetalleGpoPromo l 
            LEFT JOIN c_articulo a ON a.cve_articulo = l.Articulo
            LEFT JOIN c_unimed u ON u.id_umed = l.TipMed
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = l.cve_gpoart
            WHERE l.PromoId = {$id} 

    UNION

    SELECT IF(IFNULL(l.Articulo, '') != '', l.Articulo, l.Grupo_Art) as Articulo, 
    IF(IFNULL(a.des_articulo, '') != '', a.des_articulo, g.des_gpoart) AS des_articulo, 
    l.Monto AS Monto, l.Cantidad AS Cantidad, u.id_umed, CONCAT('( ',u.cve_umed, ' ) ', u.des_umed) AS unimed, l.Nivel, 
    IF(IFNULL(l.Articulo, '') = '', 1, 0) as TipoReg,
    0 AS Tipo
            FROM DetallePromo l 
            LEFT JOIN c_articulo a ON a.cve_articulo = l.Articulo
            LEFT JOIN c_unimed u ON u.id_umed = l.UniMed
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = IF(IFNULL(l.Articulo, '') != '', l.Articulo, l.Grupo_Art)
            WHERE l.PromoId = {$id} AND l.Tipo = 0
    ";
  $det_lista_base = getArraySQL($sql_base);


  $sql_productos_nivel = "
    SELECT IF(IFNULL(l.Articulo, '') != '', l.Articulo, l.Grupo_Art) AS Articulo, 
           IF(IFNULL(a.des_articulo, '') != '', a.des_articulo, g.des_gpoart) AS des_articulo, 
          l.Monto AS Monto, l.Cantidad AS Cantidad, u.id_umed, CONCAT('( ',u.cve_umed, ' ) ', u.des_umed) AS unimed, l.Nivel, 
          IF(IFNULL(l.Articulo, '') = '', 1, 0) as TipoReg,
          1 AS Tipo
            FROM DetallePromo l 
            LEFT JOIN c_articulo a ON a.cve_articulo = l.Articulo
            LEFT JOIN c_unimed u ON u.id_umed = l.UniMed
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = l.Grupo_Art
            WHERE l.PromoId = {$id} AND l.Tipo = 1
    ";
  $det_lista_productos_nivel = getArraySQL($sql_productos_nivel);

  $arr = array(
    "success" => true,
    "cab_lista"=>$cab_lista,
    "det_lista_base"=>$det_lista_base,
    "det_lista_productos_nivel" => $det_lista_productos_nivel
  );
  echo json_encode($arr);
}


if($_GET['action'] == 'listas_selects'){

    $almacen = $_GET['almacen'];

    $sql_promociones = "SELECT p.Id, p.ListaMaster 
                        FROM ListaPromoMaster p
                        LEFT JOIN c_almacenp a ON a.id = p.Cve_Almac
                        WHERE a.clave = '{$almacen}'";

    $Sql = \db()->prepare($sql_promociones);
    $Sql->execute();
    $lista_promociones = $Sql->fetchAll(PDO::FETCH_ASSOC);

    $lista_promociones_select = "<option value=''>Seleccione Grupo de Promociones</option>";
    foreach($lista_promociones as $p)
    {
        $lista_promociones_select .= "<option value='".$p["Id"]."'>[".$p["Id"]."] - [".$p["ListaMaster"]."]</option>";
    }


    $arr = array(
        "success" => true,
        "lista_promociones" => $lista_promociones_select
    );

    echo json_encode($arr);

}

if($_GET['action'] == 'asignarLista'){

    $listas_asignar = $_GET['listas_asignar'];
    $idGrupoLista = $_GET['lista_promociones_select'];
    $almacen = $_GET['almacen'];
    //$Sql = \db()->prepare("SELECT departamento,des_municipio  FROM c_dane WHERE cod_municipio='".$_POST["codigo"]."'");
    //$Sql->execute();
      $sqlAlmacen = "SELECT id FROM c_almacenp WHERE clave = '{$almacen}'";
      $Sql = \db()->prepare($sqlAlmacen);
      $Sql->execute();
      $id_almacen = $Sql->fetchAll(PDO::FETCH_ASSOC);
      $id_almacen = $id_almacen[0]["id"];


    $listas_asignar = explode(",",$listas_asignar);

    $msj = "";
    foreach($listas_asignar as $d)
    {
        $sql_existe = "SELECT COUNT(*) AS existe FROM DetalleLProMaster WHERE IdLm = {$idGrupoLista} AND IdPromo = {$d} AND Cve_Almac = {$id_almacen}";
        $Sql = \db()->prepare($sql_existe);
        $Sql->execute();
        $existeSQL = $Sql->fetchAll(PDO::FETCH_ASSOC);
        $existe     = $existeSQL[0]["existe"];

        if(!$existe)
        {
            $Sql = \db()->prepare("INSERT INTO DetalleLProMaster(IdLm, IdPromo, Cve_Almac) VALUES({$idGrupoLista}, {$d}, {$id_almacen})");
            $Sql->execute();
        }

        //$msj .= $existe.", ";
    }

    $arr = array(
        "success" => true,
        "mensaje" =>$msj,
        "listas_asignar" =>$listas_asignar
    );

    echo json_encode($arr);

}

if($_POST['action'] == 'eliminar_lista_promociones'){

    $id_lista = $_POST['id_lista'];

    $msj = "";

    $sqlQuery = "DELETE FROM DetalleGpoPromo WHERE PromoId = {$id_lista}";
    $Sql = \db()->prepare($sqlQuery);
    $Sql->execute();

    $sqlQuery = "DELETE FROM DetalleLProMaster WHERE IdPromo = {$id_lista}";
    $Sql = \db()->prepare($sqlQuery);
    $Sql->execute();

    $sqlQuery = "DELETE FROM DetallePromo WHERE PromoId = {$id_lista}";
    $Sql = \db()->prepare($sqlQuery);
    $Sql->execute();

    $sqlQuery = "DELETE FROM ListaPromo WHERE id = {$id_lista}";
    $Sql = \db()->prepare($sqlQuery);
    $Sql->execute();

    $arr = array(
        "success" => true,
        "mensaje" =>$msj
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
    $clave_lista   = $_POST['clave_lista'];
    $nombre_lista  = $_POST['nombre_lista'];
    $Almcen        = $_POST['Almcen'];
    $caduca        = $_POST['caduca'];
    $fechaini      = $_POST['fechaini'];
    $fechafin      = $_POST['fechafin'];
    $tipo_lista    = $_POST['tipo_lista'];
    //$articulo      = $_POST['articulo'];
    //$grupo         = $_POST['grupo'];
    $arrDetalle    = $_POST['productos'];
    $arrDetalle2    = $_POST['productos2'];


    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $sql = "
    SELECT  COUNT(*) as existe FROM ListaPromo WHERE Lista = '{$clave_lista}' AND Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = '{$Almcen}');
    ";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $existe  = mysqli_fetch_assoc($res)['existe'];


    if($existe == 0)
    {
        $articulo_grupo = "";
        if($tipo_lista == 0) {$tipo_lista = "volumen";}
        else if($tipo_lista == 1) {$tipo_lista = "monto";}
        else {$tipo_lista = "grupo";}


        $sql = "SELECT id FROM c_almacenp WHERE clave = '{$Almcen}'";
        $res = "";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
        $id_almac  = mysqli_fetch_assoc($res)['id'];

        $sql = "INSERT INTO ListaPromo(Lista, Descripcion, Caduca, FechaI, FechaF, Grupo, Activa, Tipo, Cve_Almac) VALUES('{$clave_lista}', '{$nombre_lista}', {$caduca}, STR_TO_DATE('{$fechaini}', '%d-%m-%Y'), STR_TO_DATE('{$fechafin}', '%d-%m-%Y'),'{$articulo_grupo}', 1, '{$tipo_lista}',{$id_almac})";

        $res = "";
          if (!($res = mysqli_query($conn, $sql))) {
              echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }


        $sql = "SELECT IFNULL(MAX(id), 0) as id FROM ListaPromo";
        $res = "";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
        $id = mysqli_fetch_assoc($res)['id'];
          $i = 0;
          foreach($arrDetalle as $row)
          {
              $codigo = $row['codigo'];
              $cantidad    = $row['cantidad'];
              $monto    = $row['monto'];
              $cve_medida = $row['cve_medida'];
              $tipo = $row['tipo'];
              $articulo = "";
              $grupo = "";

              if($tipo == 0) $articulo = $codigo; else $grupo = $codigo;

              if($tipo_lista == 'grupo')
                $sql = "INSERT INTO DetalleGpoPromo(PromoId, Articulo, cve_gpoart, Cantidad, TipMed, IdEmpresa) VALUES({$id}, '{$articulo}', '{$grupo}', {$cantidad}, {$cve_medida}, {$id_almac})";  
              else 
                $sql = "INSERT INTO DetallePromo(Articulo, PromoId, Cantidad, Tipo, TipoProm, Monto, UniMed, Cve_Almac, Nivel, Grupo_Art) VALUES('{$articulo}', {$id}, {$cantidad}, 0, '{$tipo_lista}', {$monto}, {$cve_medida}, {$id_almac}, {$i}, '{$grupo}')";
              #(SELECT grupo FROM c_articulo WHERE cve_articulo = '{$codigo}')
              if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación d: (" . mysqli_error($conn) . ") ";}
          }

          foreach($arrDetalle2 as $row2)
          {
              $codigo = $row2['codigo'];
              $cantidad    = $row2['cantidad'];
              $monto    = $row2['monto'];
              $cve_medida = $row2['cve_medida'];
              $tipo = $row2['tipo'];
              $articulo = "";
              $grupo_art = "";

              if($tipo == 0) $articulo = $codigo; else $grupo_art = $codigo;

              //***************************************************************************************
              // LA DIFERENCIA DE LOS INSERT AQUI ES QUE AL ESTAR VACÍO NECESITA COMILLAS $grupo_art
              // Y PARA INGRESAR UNA SENTENCIA NO LOS NECESITA YA QUE GENERA ERROR
              //***************************************************************************************
              $sql = "INSERT INTO DetallePromo(Articulo, PromoId, Cantidad, Tipo, TipoProm, Monto, UniMed, Cve_Almac, Nivel, Grupo_Art) VALUES('{$articulo}', {$id}, {$cantidad}, 1, '{$tipo_lista}', {$monto}, {$cve_medida}, {$id_almac}, {$i}, '{$grupo_art}')";
              if($tipo_lista == 'grupo') 
              {
                 //$grupo_art = "(SELECT grupo FROM c_articulo WHERE cve_articulo = '{$articulo}')";
                  $grupo_art = "(SELECT g.cve_gpoart FROM Rel_Articulo_Almacen r LEFT JOIN c_gpoarticulo g ON g.id = r.Grupo_ID WHERE r.Cve_Articulo = '{$articulo}' AND r.Cve_Almac = '{$id_almac}')";
                 $sql = "INSERT INTO DetallePromo(Articulo, PromoId, Cantidad, Tipo, TipoProm, Monto, UniMed, Cve_Almac, Nivel, Grupo_Art) VALUES('{$articulo}', {$id}, {$cantidad}, 1, '{$tipo_lista}', {$monto}, {$cve_medida}, {$id_almac}, {$i}, '{$grupo_art}')";
              }
              //***************************************************************************************


              #(SELECT grupo FROM c_articulo WHERE cve_articulo = '{$codigo}')
              if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación e: (" . mysqli_error($conn) . ") ".$sql;}
              $i++;
          }

    }

    echo json_encode($existe);
} 

if( $_POST['action'] == 'edit' )
{
    $id_lista      = $_POST['id_lista'];
    $clave_lista   = $_POST['clave_lista'];
    $nombre_lista  = $_POST['nombre_lista'];
    $Almcen        = $_POST['Almcen'];
    $caduca        = $_POST['caduca'];
    $fechaini      = $_POST['fechaini'];
    $fechafin      = $_POST['fechafin'];
    $tipo_lista    = $_POST['tipo_lista'];
    //$articulo      = $_POST['articulo'];
    //$grupo         = $_POST['grupo'];
    $arrDetalle    = $_POST['productos'];
    $arrDetalle2    = $_POST['productos2'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if($tipo_lista == 0) {$tipo_lista = "volumen";}
    else if($tipo_lista == 1) {$tipo_lista = "monto";}
    else {$tipo_lista = "grupo";}

    $sql = "SELECT id FROM c_almacenp WHERE clave = '{$Almcen}'";
    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $id_almac  = mysqli_fetch_assoc($res)['id'];

    $sql = "UPDATE ListaPromo SET Lista = '{$clave_lista}', 
                                  Descripcion = '{$nombre_lista}', 
                                  Tipo = '{$tipo_lista}', 
                                  FechaI = STR_TO_DATE('{$fechaini}', '%d-%m-%Y'), 
                                  FechaF = STR_TO_DATE('{$fechafin}', '%d-%m-%Y'), 
                                  Caduca = {$caduca},
                                  Cve_Almac = {$id_almac}
            WHERE id = {$id_lista}";

    $res = "";
      if (!($res = mysqli_query($conn, $sql))) {
          echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
      }



    $sql = "DELETE FROM DetalleGpoPromo WHERE PromoId = {$id_lista}";
    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $sql = "DELETE FROM DetallePromo WHERE PromoId = {$id_lista}";
    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

          $i = 0;
          if(!empty($arrDetalle))          
          {
              foreach($arrDetalle as $row)
              {
                  $codigo = $row['codigo'];
                  $cantidad    = $row['cantidad'];
                  $monto    = $row['monto'];
                  $cve_medida = $row['cve_medida'];
                  $tipo = $row['tipo'];
                  $articulo = "";
                  $grupo = "";

                  if($cve_medida == "") $cve_medida = 'NULL';

                  if($tipo == 0) $articulo = $codigo; else $grupo = $codigo;

                  if($tipo_lista == 'grupo')
                    $sql = "INSERT INTO DetalleGpoPromo(PromoId, Articulo, cve_gpoart, Cantidad, TipMed, IdEmpresa) VALUES({$id_lista}, '{$articulo}', '{$grupo}', {$cantidad}, {$cve_medida}, {$id_almac})";  
                  else 
                    $sql = "INSERT INTO DetallePromo(Articulo, PromoId, Cantidad, Tipo, TipoProm, Monto, UniMed, Cve_Almac, Nivel, Grupo_Art) VALUES('{$articulo}', {$id_lista}, {$cantidad}, 0, '{$tipo_lista}', {$monto}, {$cve_medida}, {$id_almac}, {$i}, '{$grupo}')";
                  #(SELECT grupo FROM c_articulo WHERE cve_articulo = '{$codigo}')
                  if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación DetalleGpoPromo: (" . mysqli_error($conn) . ") ".$sql;}
              }
          }

          if(!empty($arrDetalle2))
          {
            foreach($arrDetalle2 as $row2)
            {
                $codigo = $row2['codigo'];
                $cantidad    = $row2['cantidad'];
                $monto    = $row2['monto'];
                $cve_medida = $row2['cve_medida'];
                $tipo = $row2['tipo'];
                $grupo_art = "";
                $articulo = "";
                //$grupo_art = "";

              if($cve_medida == "") $cve_medida = 'NULL';
              if($tipo == 0) $articulo = $codigo; else $grupo_art = $codigo;

                //if($tipo_lista == 'grupo') $grupo_art = "(SELECT grupo FROM c_articulo WHERE cve_articulo = '{$codigo}')";
                if($tipo_lista == 'grupo')
                {
                    $grupo_art = "(SELECT g.cve_gpoart FROM Rel_Articulo_Almacen r LEFT JOIN c_gpoarticulo g ON g.id = r.Grupo_ID WHERE r.Cve_Articulo = '{$articulo}' AND r.Cve_Almac = '{$id_almac}')";

                    $sql = "INSERT INTO DetallePromo(Articulo, PromoId, Cantidad, Tipo, TipoProm, Monto, UniMed, Cve_Almac, Nivel, Grupo_Art) VALUES('{$articulo}', {$id_lista}, {$cantidad}, 1, '{$tipo_lista}', {$monto}, {$cve_medida}, {$id_almac}, {$i}, {$grupo_art})";
                }
                else
                {
                    $sql = "INSERT INTO DetallePromo(Articulo, PromoId, Cantidad, Tipo, TipoProm, Monto, UniMed, Cve_Almac, Nivel, Grupo_Art) VALUES('{$articulo}', {$id_lista}, {$cantidad}, 1, '{$tipo_lista}', {$monto}, {$cve_medida}, {$id_almac}, {$i}, '{$grupo_art}')";
                }
                #(SELECT grupo FROM c_articulo WHERE cve_articulo = '{$codigo}')
                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación DetallePromo: (" . mysqli_error($conn) . ") ".$sql;}
                $i++;
            }
          }

    echo json_encode(0);
} 

if( $_POST['action'] == 'eliminar_destinatario' )
{
    $id_lista         = $_POST['id_lista'];
    $id_destinatario  = $_POST['id_destinatario'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //NECESITO SABER SI EL DESTINATARIO ESTÁ ASIGNADO EN ALGUN TIPO DE LISTA PARA SABER SI VOY A ELIMINAR O MODIFICAR
    $sql = "SELECT COUNT(*) as existe FROM RelCliLis WHERE Id_Destinatario = {$id_destinatario} AND (IFNULL(ListaP, 0) > 0  OR IFNULL(ListaPromo, 0) > 0)";
    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $existe = mysqli_fetch_assoc($res)["existe"];
   
    $sql = "DELETE FROM RelCliLis WHERE Id_Destinatario = {$id_destinatario}";

    if($existe)
      $sql = "UPDATE RelCliLis SET ListaD = NULL WHERE Id_Destinatario = {$id_destinatario}";

    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $arr = array(
      "success"=>true
    );
    echo json_encode($arr);
} 

if( $_POST['action'] == 'asignar_destinatario_descuento' )
{
    $id_lista         = $_POST['id_lista'];
    $id_destinatario  = $_POST['id_destinatario'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT COUNT(*) as existe FROM RelCliLis WHERE Id_Destinatario = {$id_destinatario}";
    $res = "";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $existe = mysqli_fetch_assoc($res)["existe"];

    $sql = "INSERT INTO RelCliLis(Id_Destinatario, ListaPromo) VALUES ({$id_destinatario}, {$id_lista})";

    if($existe)
      $sql = "UPDATE RelCliLis SET ListaPromo = {$id_lista} WHERE Id_Destinatario = {$id_destinatario}";

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

if( $_POST['action'] == 'existeClave' )
{
  $clave_lista = $_POST['clave_lista'];
  $sql = "
    SELECT  COUNT(*) as existe FROM ListaPromo WHERE Lista = '{$clave_lista}';
    ";
  $res = getArraySQL($sql);
  $array = [
    "res"=>$res
  ];
  echo json_encode($array);
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
