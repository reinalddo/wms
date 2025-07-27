<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT * FROM c_tipo_car WHERE Activo = '1'";

    if(isset($_POST['caract']))
        $sqlCount = "SELECT * FROM c_caracteristicas WHERE Activo = '1'";

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT *
            FROM c_tipo_car 
            WHERE TipoCar_Desc LIKE '%".$_criterio."%' and Activo = '1';";

    if(isset($_POST['caract']))
    $sql = "SELECT c.Id_Carac, c.Cve_Carac, t.TipoCar_Desc, c.Des_Carac
            FROM c_tipo_car t, c_caracteristicas c
            WHERE t.Id_Tipo_car = c.Id_Tipo_car AND c.Des_Carac LIKE '%".$_criterio."%' AND c.Activo = '1';";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
		$row=array_map('utf8_encode', $row);
        $arr[] = $row;
        if(isset($_POST['caract']))
        {
            $responce->rows[$i]['id']=$row['Id_Carac'];
            $responce->rows[$i]['cell']=array('', ($row['Id_Carac']), ($row['Cve_Carac']), ($row['TipoCar_Desc']), ($row['Des_Carac']));
        }
        else
        {
            $responce->rows[$i]['id']=$row['Id_Tipo_car'];
            $responce->rows[$i]['cell']=array('', ($row['Id_Tipo_car']), ($row['TipoCar_Desc']));
        }
        $i++;
    }
    echo json_encode($responce);
}

if( $_POST['action'] == 'buscar_id' ) {

    $sql = "SELECT MAX(Id_Tipo_car) max_id FROM c_tipo_car";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $row = mysqli_fetch_array($res);
    $max_id = $row['max_id']+1;

    $arr = array(
        "success" => true,
        "max_id" => $max_id
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'buscar_id_caracteristica' ) {

    $sql = "SELECT MAX(Id_carac) max_id FROM c_caracteristicas";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $row = mysqli_fetch_array($res);
    $max_id = $row['max_id']+1;

    $arr = array(
        "success" => true,
        "max_id" => $max_id
    );

    echo json_encode($arr);

}
