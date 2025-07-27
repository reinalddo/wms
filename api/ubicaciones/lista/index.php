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
    $sqlCount = "Select * from c_ubicacion Where Activo = '1';";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT
            c_ubicacion.idy_ubica,
            c_ubicacion.cve_almac,
            c_ubicacion.cve_pasillo,
            c_ubicacion.cve_rack,
            c_ubicacion.cve_nivel,
            c_ubicacion.num_ancho,
            c_ubicacion.num_largo,
            c_ubicacion.num_alto,
            c_ubicacion.num_volumenDisp,
            c_ubicacion.`Status`,
            c_ubicacion.picking,
            c_ubicacion.Seccion,
            c_ubicacion.Ubicacion,
            c_ubicacion.orden_secuencia,
            c_ubicacion.PesoMaximo,
            c_ubicacion.PesoOcupado,
            c_ubicacion.claverp,
            c_ubicacion.CodigoCSD,
            c_ubicacion.TECNOLOGIA,
            c_ubicacion.Maneja_Cajas,
            c_ubicacion.Maneja_Piezas,
            c_ubicacion.Reabasto,
            c_ubicacion.Activo,
            c_almacen.cve_almac,
            c_almacen.des_almac
            FROM c_ubicacion
            INNER JOIN c_almacen ON c_ubicacion.cve_almac = c_almacen.cve_almac 
            Where c_ubicacion.Ubicacion like '%".$_criterio."%' and c_ubicacion.Activo = '1';";

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
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['idy_ubica'];
        $responce->rows[$i]['cell']=array($row['idy_ubica'], $row['des_almac'], $row['Ubicacion']);
        $i++;
    }
    echo json_encode($responce);
}