<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();
$ga = new \UbicacionAlmacenaje\UbicacionAlmacenaje();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) 
{
  exit();
}

if( $_POST['action'] == 'add' ) 
{
  $ga->save($_POST['arrDet']);
  $arr = array(
    "success"=>true,
    "existe"=>$ga->data,
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'edit' ) 
{
  $ga->actualizarUbicacion($_POST);
  $arr = array(
    "success"=>true
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'cargar' ) 
{
  $ga->idy_ubica = $_POST["idy_ubica"];
  $ga->__getLoadUbica("idy_ubica");
  $arr = array(
    "success"        => true,
    "idy_ubica"      => $ga->data->idy_ubica,
    "cve_almac"      => $ga->data->cve_almac,
    "zona"           => $ga->data->zona,
    "cve_pasillo"    => $ga->data->cve_pasillo,
    "cve_rack"       => $ga->data->cve_rack,
    "cve_nivel"      => $ga->data->cve_nivel,
    "Seccion"        => $ga->data->Seccion,
    "Ubicacion"      => $ga->data->Ubicacion,
    "num_largo"      => $ga->data->num_largo,
    "num_alto"       => $ga->data->num_alto,
    "num_ancho"      => $ga->data->num_ancho,
    "PesoMaximo"     => $ga->data->PesoMaximo,
    "picking"        => $ga->data->picking,
    "maximo"         => $ga->data->Maximo,
    "minimo"         => $ga->data->Minimo,
    "tipo"           => $ga->data->Tipo,
    "tecnologia"     => $ga->data->TECNOLOGIA,
    "id_ap"          => $ga->data->id_ap,
    "AcomodoMixto"   => $ga->data->AcomodoMixto,
    "AreaProduccion" => $ga->data->AreaProduccion
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'exists' ) 
{
  $ga->idy_ubica = $_POST["codigo"];
  $ga->__get("idy_ubica");
  $success = false;
  if (!empty($ga->data->idy_ubica)) 
  {
    $success = true;
  }
  $arr = array(
    "success" => $success
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'delete' ) 
{
  $ga->borrarUbicacion($_POST);
  $ga->idy_ubica = $_POST["idy_ubica"];
  $arr = array(
    "success" => true,
    //"nombre_proveedor" => $ga->data->Empresa,
    //"contacto" => $ga->data->VendId
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'deleteUbica' ) 
{
  $ga->borrarUbicacion($_POST);
  $ga->cve_almac = $_POST["cve_almac"];
  $Ubicaciones = '';
  /******************************** UBICACIONES **************************************/
  foreach ($ga->dataSection as $ubica) 
  {
    $idy_ubica = $ubica["idy_ubica"];
    $ubicat = $ubica["Ubicacion"];
    $almacen = $ubica["cve_almac"];
    $nivel = $ubica["cve_nivel"];
    $section = $ubica["Seccion"];
    $Ubication .= '
    <div class="col-lg-2">
        <a href="#">
            <a class="delete" onclick="borrarUbicacion(\''.$idy_ubica.'\',\''.$nivel.'\',\''.$section.'\',\''.$ubicat.'\')" href="#" title="Borrar Ubicación"><i class="fa fa-minus-circle fa-2x"></i></a>
            <button class="btn btn-primary dim btn-large-dim" type="button">
                <i class="fa fa-indent"></i><br><span class="small-text"> Ubicación '.$ubicat.'</div>
            </button>
        </a>
    </div>';
  }
  $arr = array(
    "success" => true,
    "Ubicaciones" => $Ubication
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'ubicacion' ) 
{
  $ga->Ubicaciones($_POST);
  $ga->cve_almac = $_POST["cve_almac"];
  $Ubication = '';
  /******************************** UBICACIONES **************************************/
  foreach ($ga->dataSection as $ubica) 
  {
    $id_ubicat = $ubica["idy_ubica"];
    $ubicat = $ubica["Ubicacion"];
    $almacen = $ubica["cve_almac"];
    $nivel = $ubica["cve_nivel"];
    $section = $ubica["Seccion"];
    $Ubication .= '
    <div class="col-lg-2">
      <a href="#">
        <a class="delete" onclick="borrarUbicacion(\''.$almacen.'\',\''.$nivel.'\',\''.$section.'\',\''.$ubicat.'\')" href="#" title="Borrar Ubicación"><i class="fa fa-minus-circle fa-2x"></i></a>
        <a href="#" onclick="DetalleUbicacion(\''.$id_ubicat.'\')"><button class="btn btn-primary dim btn-large-dim" type="button">
          <i class="fa fa-indent"></i><br><span class="small-text"> Ubicación '.$ubicat.'</div>
        </button></a>
      </a>
    </div>';
  }
  $arr = array(
    "success" => true,
    "Ubicaciones" => $Ubication
  );  
  echo json_encode($arr);
}

if( $_POST['action'] == 'rack_pasillo' ) 
{
  $ga->cve_almac = $_POST["codigo"];
  $ga->__get("cve_almac");
  $Niveles = '';
  $NivelesSecciones = '';
  $Secciones = '';
  $Ubicaciones = '';
  /******************************** MENU **************************************/
  foreach ($ga->dataNiveles as $nivel) 
  {
    $valor = $nivel["cve_nivel"];
    $almacen = $nivel["cve_almac"];
    $nivels = $nivel["cve_nivel"];
    $section = $nivel["Seccion"];
    $NivelesSecciones .= '<li class="has-children">';
    $NivelesSecciones .= '<input type="checkbox" name ="sub-group-'.$valor.'" id="sub-group-'.$valor.'"><label for="sub-group-'.$valor.'"><span onclick="Secciones(\''.$almacen.'\',\''.$nivels.'\')">Nivel '.$valor.'</span></label><ul>';
    if ($cont == 0) 
    { 
      $cont = 1; 
    }
    else 
    { 
      $cont = $total+1; 
    }
    foreach ($ga->dataSecciones as $seccion) 
    {
      $valor2 = $seccion["Seccion"];
      $almacen = $seccion["cve_almac"];
      $nivel = $seccion["cve_nivel"];
      $section = $seccion["Seccion"];
      $NivelesSecciones .= '<li class="has-children">';
      $NivelesSecciones .= '<input type="checkbox" name ="sub-group-level-'.$cont.'" id="sub-group-level-'.$cont.'"><label for="sub-group-level-'.$cont.'"><span onclick="Ubicaciones(\''.$almacen.'\',\''.$nivel.'\',\''.$section.'\')">Sección '.$valor2.'</span></label>';
      $NivelesSecciones .= '<ul>';
      foreach ($ga->dataUbicaciones as $ubicacion) 
      {
        $valor3 = $ubicacion["Ubicacion"];
        $valor5 = $ubicacion["Seccion"];
        if ($valor5 == $valor2) 
        {
          $NivelesSecciones .= '<li><a href="#0">Ubicación '.$valor3.'</a></li>';
        }
      }
      $NivelesSecciones .= '</ul></li>';
      $total = $cont++;
    }
    $NivelesSecciones .= '</ul></li>';
  }
  /******************************** NIVELES **************************************/
  foreach ($ga->dataNiveles as $nivel) 
  {
    $almacen = $nivel["cve_almac"];
    $rack = $nivel["cve_rack"];
    $ubicacion = $nivel["Ubicacion"];
    $nivel = $nivel["cve_nivel"];
    $Niveles .= '<div class="col-lg-2">
    <a href="#" onclick="Secciones(\''.$almacen.'\', \''.$nivel.'\', \''.$rack.'\', \''.$ubicacion.'\')"><button class="btn btn-primary dim btn-large-dim" type="button"><i class="fa fa-tasks"></i><br><span class="small-text"> Nivel '.$nivel.'</div></button></a>
    </div>';
  }
  /******************************** SECCIONES **************************************/
  foreach ($ga->dataSecciones as $seccion) 
  {
    $section = $seccion["Seccion"];
    $almacen = $seccion["cve_almac"];
    $nivel = $seccion["cve_nivel"];
    $section = $seccion["Seccion"];
    $Secciones .= '<div class="col-lg-2">
    <a href="#" onclick="Ubicaciones(\''.$almacen.'\',\''.$nivel.'\',\''.$section.'\')"><button class="btn btn-primary dim btn-large-dim" type="button"><i class="fa fa-indent"></i><br><span class="small-text"> Sección '.$section.'</div></button></a>
    </div>';
  }
  /******************************** UBICACIONES **************************************/
  foreach ($ga->dataUbicaciones as $ubicacion) 
  {
    $ubica = $ubicacion["Ubicacion"];
    $almacen = $ubicacion["cve_almac"];
    $nivel = $ubicacion["cve_nivel"];
    $section = $ubicacion["Seccion"];
    $Ubicaciones .= '<div class="col-lg-2">
    <a href="#" onclick="Secciones(\''.$almacen.'\', \''.$nivel.'\', \''.$ubica.'\')"><button class="btn btn-primary dim btn-large-dim" type="button"><i class="fa fa-indent"></i><br><span class="small-text"> Ubicación '.$ubica.'</div></button></a>
    </div>';
  }
  /********************************************************************************/
  $arr = array(
    "success" => true,
    "cve_rack" => $ga->data["cve_rack"],
    "cve_pasillo" => $ga->data["cve_pasillo"],
    "Niveles" => $Niveles,
    "Secciones" => $Secciones,
    "Ubicaciones" => $Ubicaciones,
    "NivelesSecciones" => $NivelesSecciones
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'recovery' ) 
{
  $ga->recovery($_POST);
  $arr = array(
    "success" => true,
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'inUse' ) 
{
  $use = $ga->inUse($_POST);
  $arr = array(
    "success" => $use,
  );
  echo json_encode($arr);
}
