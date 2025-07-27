<?php
include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \AjustesExistencias\AjustesExistencias();

if( $_POST['action'] == 'add' ) {
    $ga->save($_POST);
    $success = true;

    $err = ($ga->resultado=="No existe el Artículo") ? $ga->resultado : "";

    $arr = array(
        "success" => $success,
        "err" => $ga->resultado
    );

    echo json_encode($arr);
} if( $_POST['action'] == 'edit' ) {
    $ga->actualizarPromocion($_POST);

    $success = true;

    $arr = array(
        "success" => $success
        //"err" => "El Número del Folio ya se Ha Introducido"
    );

    echo json_encode($arr);
   /* if (!$success) {
        exit();
    }*/
} if( $_POST['action'] == 'exists' ) {

} if( $_POST['action'] == 'delete' ) {
    $ga->borrarPromocion($_POST);
    $ga->IDpromo = $_POST["IDpromo"];
    $ga->__get("IDpromo");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}



if( $_POST['action'] == 'arDetalle' )
{
    $ga->saveDetalle($_POST);
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );
    echo json_encode($arr);
}



if( $_POST['action'] == 'LoadGrid' ) {
    $ga->LoadGrid($_POST);
    $arr = array(
        "success" => true,
    );

    $arr2["detalle"] = $ga->LoadDetalleGrid;
    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);

}if( $_POST['action'] == 'SaveLoad' ) {
    $ga->saveLoad($_POST);
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );
    echo json_encode($arr);

}if( $_POST['action'] == 'loadArt' ) {
    $ga->IDpromo = $_POST["codigo"];
    $ga->__getDetalleArt("IDpromo");
    $arr = array(
        "success" => true,
    );

    $ga->__getDetalleArt("IDpromo");
    //$ga->__getDetalle("IDpromo");

    foreach ($ga->dataDetalleArt as $nombre => $valor) $arr2[$nombre] = $valor;

    //$arr2["detalle"] = $ga->dataDetalle;
    $arr2["detalle"] = $ga->dataDetalleArt;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);

} if( $_POST['action'] == 'carga_selects' ) {
    if ($_POST["codigo"] == "") { exit; } else {
        $ga->cve_almac = $_POST["codigo"];
        $ga->__getSelectList("cve_almac");
        $success = true;
    }
    $Pasillo = '';
    $Rack = '';
    $Nivel = '';
    $Seccion = '';
    $Ubicacion = '';
    /******************************** PASILLOS **************************************/
    foreach ($ga->dataPasillos as $pasillo) {
        $id_pasillo = $pasillo["cve_pasillo"];
        $Pasillo .= '<option value="'.$id_pasillo.'">'.$id_pasillo.'</option>';
    }
    /******************************** RACK **************************************/
    foreach ($ga->dataRack as $rack) {
        $id_rack = $rack["cve_rack"];
        $Rack .= '<option value="'.$id_rack.'">'.$id_rack.'</option>';
    }
    /******************************** NIVEL **************************************/
    foreach ($ga->dataNivel as $nivel) {
        $id_nivel = $nivel["cve_nivel"];
        $Nivel .= '<option value="'.$id_nivel.'">'.$id_nivel.'</option>';
    }
    /******************************** SECCION **************************************/
    foreach ($ga->dataSeccion as $seccion) {
        $id_seccion = $seccion["Seccion"];
        $Seccion .= '<option value="'.$id_seccion.'">'.$id_seccion.'</option>';
    }
    /******************************** UBICACION **************************************/
    foreach ($ga->dataUbicacion as $ubicacion) {
        $id_ubicacion = $ubicacion["idy_ubica"];
        $name_ubicacion = $ubicacion["Ubicacion"];
        $Ubicacion .= '<option value="'.$id_ubicacion.'">'.$name_ubicacion.'</option>';
    }
    $arr = array(
        "Pasillo" => $Pasillo,
        "Rack" => $Rack,
        "Nivel" => $Nivel,
        "Seccion" => $Seccion,
        "Ubicacion" => $Ubicacion,
        "success" => $success
    );

    echo json_encode($arr);

} if( $_POST['action'] == 'load' ) {
    $ga->IDpromo = $_POST["codigo"];
    $ga->__get("IDpromo");
    $arr = array(
        "success" => true,
    );

    $ga->__getDetalle("IDpromo");

    foreach ($ga->data as $nombre => $valor) $arr2[$nombre] = $valor;

    $arr2["detalle"] = $ga->data;

    $arr = array_merge($arr, $arr2);

    echo json_encode($arr);

}

if( $_POST['action'] == 'cargarLotes' )
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $cve_articulo = $_POST['cve_articulo'];
    $lote_a_cambiar = $_POST['lote_a_cambiar'];

    $sql="SELECT Lote FROM c_lotes WHERE cve_articulo = '{$cve_articulo}'";
    $res = mysqli_query($conn, $sql);
    $num = mysqli_num_rows($res); 
    $options = "<option value=''>Seleccione Lote (".$num.")</option>";
    while($row = mysqli_fetch_array($res))
    {
        extract($row);
        $selected = "";
        if($Lote == $lote_a_cambiar) $selected = "selected";
        $options .= "<option {$selected} value='{$Lote}'>".$Lote."</option>";
    }
    
    $arr = array(
        "options" => $options,
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'cargarSeries' )
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $cve_articulo = $_POST['cve_articulo'];
    $serie_a_cambiar = $_POST['serie_a_cambiar'];

    $sql="SELECT numero_serie FROM c_serie WHERE cve_articulo = '{$cve_articulo}' AND IFNULL(numero_serie, '') != ''";
    $res = mysqli_query($conn, $sql);
    $num = mysqli_num_rows($res); 
    $options = "<option value=''>Seleccione Serie (".$num.")</option>";
    while($row = mysqli_fetch_array($res))
    {
        extract($row);
        $selected = "";
        if($numero_serie == $serie_a_cambiar) $selected = "selected";

        $options .= "<option {$selected} value='{$numero_serie}'>".$numero_serie."</option>";
    }
    
    $arr = array(
        "options" => $options,
    );

    echo json_encode($arr);
}
