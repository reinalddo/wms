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
    $sqlCount = "Select COUNT(*) as total from t_ruta Where Activo = '0';";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];

    mysqli_close($conn);

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $_page = 0;
    
    if (intval($page)>0) $_page = ($page-1)*$limit;//

    $sql = "SELECT
            t_ruta.ID_Ruta,
            t_ruta.cve_ruta,
            t_ruta.descripcion,
            t_ruta.`status`,
            t_ruta.Activo
            FROM
            t_ruta			
			WHERE 
			t_ruta.descripcion LIKE '%".$_criterio."%' AND t_ruta.Activo = 0;";
			
            //INNER JOIN c_compania ON t_ruta.cve_cia = c_compania.cve_cia
            //WHERE
            //t_ruta.descripcion LIKE '%".$_criterio."%' AND t_ruta.Activo = 0;";
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
        $responce->rows[$i]['id']=$row['ID_Ruta'];
        $responce->rows[$i]['cell']=array($row['ID_Ruta'], $row['cve_ruta'], $row['descripcion'], $row['status']);
        $i++;
    }
    echo json_encode($responce);
}

if(isset($_GET) && !empty($_GET)){
    $page = $_GET['page'];
    $rows = $_GET['rows'];
    $search = $_GET['search'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    /*Pedidos listos para embarcar*/
    $sql = "SELECT cve_ruta, descripcion FROM t_ruta WHERE Activo = 0";

    if(!empty($search) && $search != '%20'){
        $sql.= " AND descripcion like '%".$search."%'";
    }

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    while($row = mysqli_fetch_array($res)){
        extract($row);
        $result [] = array(
            'clave' => $cve_ruta,
            'descripcion' => $descripcion
        );
    };
    mysqli_close();
    header('Content-type: application/json');
    echo json_encode( $result);
}
