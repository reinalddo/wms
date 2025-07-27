<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Embarques\Embarques();

if( $_POST['action'] == 'add' ) {
    $save = $ga->save($_POST);
    echo json_encode(array("success"=>$save));
    } if( $_POST['action'] == 'edit' ) {
    $update = $ga->actualizarEmbarque($_POST);

    echo json_encode(array("success" =>$update));
}
if( $_POST['action'] == 'exists' ) {
    $ga->ID_Embarque = $_POST["ID_Embarque"];
    $ga->__get("ID_Embarque");

    $success = false;

    if (!empty($ga->data->cve_almac)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'delete' ) {
    $ga->borrarEmbarque($_POST);
    $ga->ID_Embarque = $_POST["ID_Embarque"];
    $ga->__get("ID_Embarque");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->ID_Embarque = $_POST["ID_Embarque"];
    $ga->__get("ID_Embarque");
    $arr = array(
        "success" => true,
        "cve_ubicacion" => $ga->data->cve_ubicacion,
        "cve_almac" => $ga->data->cve_almac,
        "status" => $ga->data->status
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'inUse' ) {
    $use = $ga->inUse($_POST);
    $arr = array(
        "success" => $use,
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'informe' ) {	
    //$data = $ga->invfidetconc();
    $data = $ga->informe($_POST["folio"]);
		
	$arr = array(    
		"data" => $data,	
    );

    $arr = array_merge($arr);

    echo json_encode($arr);
}


if($_POST['action'] == 'DescargarDocumentos' ) 
{

  $folio = $_POST['folio'];
  $sql = "SELECT * FROM c_embarque_documentos WHERE folio = '$folio'";

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

  $folio = $_POST['folio'];
  $sql = "SELECT * FROM c_embarque_documentos WHERE folio = '$folio'";

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
  $sql = "DELETE FROM c_embarque_documentos WHERE id = $id";
  $query = mysqli_query(\db2(), $sql);
}
