<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$a = new \Articulos\Articulos();

if( $_POST['action'] == 'add' ) 
{
/*
  foreach ($_POST as $llave => $valor) {
      $_POST[$llave] = ($valor == null)?0:$_POST[$llave];
  }
  */

  $ExisteCodigoBarras = true;
  $resp = "";

  if($_POST["cve_codprov"] == '' && $_POST["barras2"] == '' && $_POST["barras3"])
  {
        $ExisteCodigoBarras = true;
        $resp = "error";
  }
  else
  {
    $ExisteCodigoBarras = $a->ExisteCodigoBarras($_POST);
    $resp = "error2";
  }

  if($ExisteCodigoBarras == true)
  {
      $resp = "error3";
      $resp = $a->save($_POST);
      $resp1 = $a->save_Rel_Art_Almacen($_POST);
      $resp = "error4";
  }
  //$success = true;
  $success = $ExisteCodigoBarras;
  
  $arr = array(
    "success" => $success,
    "err" => $resp,
    "post" => $_POST
  );
  echo json_encode($arr);
} 

if( $_POST['action'] == 'edit' ) 
{
  $ExisteCodigoBarras = true;
  $resp = "";

  if($_POST["cve_codprov"] == '' && $_POST["barras2"] == '' && $_POST["barras3"])
  {
        $ExisteCodigoBarras = true;
        $resp = "error";
  }
  else
  {
    $ExisteCodigoBarras = $a->ExisteCodigoBarras($_POST);
    $resp = "error2";
  }

  if($ExisteCodigoBarras == true)
  {
      $resp = $a->actualizarArticulos($_POST);
      $resp1 = $a->save_Rel_Art_Almacen($_POST);
  }
  //$success = true;
  $success = $ExisteCodigoBarras;

  //$success = true;

  $arr = array(
    "success" => $success,
    "err" => $resp
  );
  echo json_encode($arr);
  //echo var_dump($resp);
}


if( $_POST['action'] == 'existeCodigoArticulo' ) {
    $clave=$a->existeCodigoArticulo($_POST);

    if($clave==true)
        $success = true;
    else
        $success= false;

    $arr = array(
        "success"=>$success
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'exists' ) 
{
  $clave=$a->exist($_POST);
  $clave_otro_almacen = $a->existe_en_otro_almacen($_POST);
  $success_mismo_almacen = $a->existe_mismo_almacen($_POST);
  $success_otro_almacen = false;

  if($clave==true)
  {
    $success = true;
    if($clave_otro_almacen == true)
        $success_otro_almacen = true;
  }
  else
  {
    $success= false;
  }
  $arr = array(
    "success_otro_almacen"=>$success_otro_almacen,
    "success_mismo_almacen"=>$success_mismo_almacen,
    "success"=>$success
  );
  echo json_encode($arr);
}

if($_POST['action'] == 'DescargarDocumentos' ) 
{

  $cve_articulo = $_POST['folio'];
  $sql = "SELECT * FROM c_articulo_documento WHERE cve_articulo = '$cve_articulo'";

  $query = mysqli_query(\db2(), $sql);

  $imagenes = "<div class='row'>";
  while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
  {
        //echo $row['ruta'].'!***********!'.$row['descripcion'];

        if(substr($row['type'], 0, 5) == 'image')
            $imagenes .= "<div class='row row_descargar'>
                             <div class='col-xs-2'> <img src='../".$row['ruta']."' width='50%' /></div>
                             <div class='col-xs-8'><b style='word-break: break-all;font-size:10pt'>".($row['descripcion'])."</b></div>
                             <div class='col-xs-2'><a href='../".$row['ruta']."' target='_blank'><i class='fa fa-download' style='color: green;' title='Descargar'></i></a></div>
                         </div>";
        else
            $imagenes .= "<div class='row row_descargar'>
                            <div class='col-xs-2'> <div class='fa fa-file-text-o' aria-hidden='true'></div></div>
                            <div class='col-xs-8'><b style='word-break: break-all;font-size:10pt'>".($row['descripcion'])."</b></div>
                            <div class='col-xs-2'><a href='../".$row['ruta']."'  target='_blank'><i class='fa fa-download' style='color: green;' title='Descargar'></i></a></div>
                            </div>";
/*
        if(substr($row['type'], 0, 5) == 'image')
            $imagenes .= "<div class='col-xs-4' style='margin: 20px 0;'> <img src='../".$row['ruta']."' width='100%' height='100px' /><br><b style='word-break: break-all;'>".($row['descripcion'])."</b>&nbsp;&nbsp;<br><a href='../".$row['ruta']."' target='_blank'><i class='fa fa-download' style='color: green; font-size:20px;' title='Descargar'></i></a></div>";
        else
            $imagenes .= "<div class='col-xs-4' style='margin: 20px 0; text-align:center;'> <div class='fa fa-file-text-o' aria-hidden='true' style='font-size:100px'></div><br><b style='word-break: break-all;'>".($row['descripcion'])."</b>&nbsp;&nbsp;<br><a href='../".$row['ruta']."'  target='_blank'><i class='fa fa-download' style='color: green; font-size:20px;' title='Descargar'></i></a></div>";
    
*/
  }
  $imagenes .= "</div>";
  echo $imagenes;
}

if($_POST['action'] == 'cargarFotosTH' ) 
{

  $cve_articulo = $_POST['folio'];
  $sql = "SELECT * FROM c_articulo_documento WHERE cve_articulo = '$cve_articulo'";

  $query = mysqli_query(\db2(), $sql);

  $imagenes = "<div class='row'>";
  while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
  {
        //echo $row['ruta'].'!***********!'.$row['descripcion'];

        if(substr($row['type'], 0, 5) == 'image')
            $imagenes .= "<div class='col-xs-4' style='margin: 20px 0;'> <img src='../".$row['ruta']."' width='100%' height='100px' /><br><b style='word-break: break-all;'>".($row['descripcion'])."</b>&nbsp;&nbsp;<a href='#' onclick='eliminar_foto_th(".$row['id'].")'><i class='fa fa-times' style='color: #F00;' title='eliminar'></i></a></div>";
        else
            $imagenes .= "<div class='col-xs-4' style='margin: 20px 0; text-align:center;'> <div class='fa fa-file-text-o' aria-hidden='true' style='font-size:100px'></div><br><b style='word-break: break-all;'>".($row['descripcion'])."</b>&nbsp;&nbsp;<a href='#' onclick='eliminar_foto_th(".$row['id'].")'><i class='fa fa-times' style='color: #F00;' title='eliminar'></i></a></div>";
    
  }
  $imagenes .= "</div>";
  echo $imagenes;
}

if($_POST['action'] == 'eliminarFotosTH' ) 
{

  $id = $_POST['id'];
  $sql = "DELETE FROM c_articulo_documento WHERE id = $id";
  $query = mysqli_query(\db2(), $sql);
}

if( $_POST['action'] == 'CopiarArticuloA_Almacen' ) 
{
    $cve_articulo = $_POST['cve_articulo'];
    $id_almacen = $_POST['id_almacen'];

    $sql = "INSERT INTO Rel_Articulo_Almacen(Cve_Almac, Cve_Articulo, Grupo_ID, Clasificacion_ID, Tipo_Art_ID) (SELECT '{$id_almacen}', Cve_Articulo, Grupo_ID, Clasificacion_ID, Tipo_Art_ID FROM Rel_Articulo_Almacen WHERE Cve_Articulo = '{$cve_articulo}' LIMIT 1)";
    $Sql = \db()->prepare($sql);
    $Sql->execute();

  $arr = array(
    "success"=>true
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'ReplicarArticulos' ) 
{
    $id_almacen_replicar = $_POST['id_almacen_replicar'];
    $id_almacen_origen = $_POST['id_almacen_origen'];

    $sql = "INSERT IGNORE INTO Rel_Articulo_Almacen(Cve_Almac, Cve_Articulo, Grupo_ID, Clasificacion_ID, Tipo_Art_ID) (SELECT '{$id_almacen_replicar}', Cve_Articulo, Grupo_ID, Clasificacion_ID, Tipo_Art_ID FROM Rel_Articulo_Almacen WHERE Cve_Almac = '{$id_almacen_origen}')";
    $Sql = \db()->prepare($sql);
    $Sql->execute();

  $arr = array(
    "success"=>true
  );
  echo json_encode($arr);
}


if( $_POST['action'] == 'existeEnUbicacion' ) {
	$clave=$a->existeEnUbicacion($_POST);

    if($clave==true)
        $success = true;
    else
		$success= false;

    $arr = array(
		"success"=>$success
	);

    echo json_encode($arr);
}

  if( $_POST['action'] == 'delete' ) {
    $a->borrarArticulo($_POST);
    $a->cve_articulo = $_POST["cve_articulo"];
    $a->__get("cve_articulo");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} 
if( $_POST['action'] == 'load' ) 
{
  $a->cve_articulo = $_POST["cve_articulo"];
  
  //$a->__get("cve_articulo"."::::::::::".$_POST['almacen']);
  $a->__get("cve_articulo"."::::::::::".$_SESSION['id_almacen']);
  $arr = array(
    "success" => true,
  );
  $arr2 = array();
  //echo var_dump($a->data);
  //if($a->data)
  foreach ($a->data as $nombre => $valor)
  {
    $arr2[$nombre] = $valor;
  } 
  $arr = array_merge($arr, $arr2);
  echo json_encode($arr);
}

if( $_POST['action'] == 'loadVer' ) {
    $a->cve_articulo = $_POST["cve_articulo"];
    $a->__getVer("cve_articulo");
    $arr = array(
        "success" => true,
    );

    foreach ($a->data as $nombre => $valor) $arr2[$nombre] = $valor;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);
}

if( $_POST['action'] == 'loadEmpty' ) {
    $_arr[] = array("a" => "1", "b" => "2", "c" => "3", "d" => "4", "e" => "5");

    $arr = array(
        "data" => $_arr
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'load2' ) {
    $a->cve_articulo = $_POST["cve_articulo"];
    $a->__get("cve_articulo");
    $arr = array(
        "success" => true,
    );

    foreach ($a->data as $nombre => $valor) $arr2[$nombre] = $valor;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);
}

if( $_POST['action'] == 'traerClasificacionDeGrupo' ) {
    $clasificaciones = $a->loadClasificaciones($_POST["grupo"]);

	$arr = array(
        "success" => true,
		"clasificaciones" => $clasificaciones
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'traerTipoDeClasificacion' ) {
    $tipos = $a->loadTipos($_POST["clasificacion"]);

	$arr = array(
        "success" => true,
		"tipos" => $tipos
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}

if( $_POST['action'] == 'recovery' ) {
    $a->recoveryArticulo($_POST);
    $a->cve_articulo = $_POST["id"];
    $a->__get("cve_articulo");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'loadAll' ) {
    $data=$a->getAll();
    $arr = array(
        "success" => true,
		"detalle" => $data,
    );

    echo json_encode($arr);

}

if(isset($_GET) && $_GET['action'] == 'getArtCompuestos' ) {
    $search = $_GET['search']['value'];
    $start = $_GET['start'];
    $length = $_GET['length'];
    $data=$a->getArtCompuestos($_GET["almacen"], $start, $length, $search);
    $total=$a->getArtCompuestosTotalCount($_GET["almacen"], $search);

    $output = array(
        "draw" => $_GET["draw"],
        "recordsTotal" => $total,
        "recordsFiltered" => $total,
        "data" => $data
    );

    echo json_encode($output);

}
