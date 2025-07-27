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
    $sqlCount = "Select * from t_transporte Where Activo = '1';";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT
				t_transporte.*
				,c_compania.des_cia
				,tipo_transporte.desc_ttransporte
				,tipo_transporte.capacidad_carga
				,((alto/1000)*(ancho/1000)*(fondo/1000)) as capacidad_volumetrica
            FROM
            t_transporte
            INNER JOIN c_compania ON t_transporte.cve_cia = c_compania.cve_cia
			INNER JOIN tipo_transporte ON t_transporte.tipo_transporte = tipo_transporte.clave_ttransporte
            WHERE
            t_transporte.Nombre LIKE '%".$_criterio."%' AND t_transporte.Activo = 0;";
			
			
			
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
        $responce->rows[$i]['id']=$row['ID_Transporte'];
        $responce->rows[$i]['cell']=array( $row['ID_Transporte'],$row['des_cia'],$row['desc_ttransporte'],$row['Placas'],$row['Nombre'],$row['capacidad_carga'],$row['capacidad_volumetrica']);
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
    $sql = "SELECT ID_Transporte, Nombre FROM t_transporte WHERE Activo = 0";

    if(!empty($search) && $search != '%20'){
        $sql.= " AND Nombre like '%".$search."%'";
    }

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    while($row = mysqli_fetch_array($res)){
        extract($row);
        $result [] = array(
            'clave' => $ID_Transporte,
            'descripcion' => $Nombre
        );
    };
    mysqli_close();
    header('Content-type: application/json');
    echo json_encode( $result);
}