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
    $almacen = $_POST['almacen'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
/*
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    // prepara la llamada al procedimiento almacenado Lis_Facturas

    $sqlCount = "Select * from t_transporte Where Activo = '1';";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close();
*/
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "SELECT
				t_transporte.*
				,IFNULL(c_compania.des_cia, p.Nombre) AS des_cia
				,tipo_transporte.desc_ttransporte
				,tipo_transporte.capacidad_carga
                ,c_almacenp.nombre AS almacen
				,((alto/1000)*(ancho/1000)*(fondo/1000)) as capacidad_volumetrica
            FROM
            t_transporte
            LEFT JOIN c_compania ON t_transporte.cve_cia = c_compania.cve_cia
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = t_transporte.cve_cia
			LEFT JOIN tipo_transporte ON t_transporte.tipo_transporte = tipo_transporte.clave_ttransporte 
            LEFT JOIN c_almacenp ON c_almacenp.id = t_transporte.id_almac
            WHERE
            (t_transporte.Nombre LIKE '%".$_criterio."%' OR t_transporte.ID_Transporte LIKE '%".$_criterio."%' OR t_transporte.Placas LIKE '%".$_criterio."%' OR t_transporte.num_ec LIKE '%".$_criterio."%') AND t_transporte.Activo = 1 AND t_transporte.id_almac = ".$almacen."";
			
			
    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);

    $res = ""; $sql2 = $sql;
    $sql2 .= " LIMIT $start, $limit";
    if (!($res = mysqli_query($conn, $sql2))) {
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
    $responce->sql = $sql2;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
		$row=array_map('utf8_encode', $row);
        $arr[] = $row;
        $transporte_externo = $row['transporte_externo'];
        if($transporte_externo == 0) $transporte_externo = 'No'; else $transporte_externo = 'Si';
        $responce->rows[$i]['id']=$row['ID_Transporte'];
        $responce->rows[$i]['cell']=array( '', $row['ID_Transporte'],$row['Nombre'],$row['Placas'],$row['num_ec'], $transporte_externo,$row['desc_ttransporte'],$row['capacidad_carga'],$row['capacidad_volumetrica'],$row['almacen'], $row['des_cia']);
        $i++;
    }
    echo json_encode($responce);
}

if(isset($_GET) && !empty($_GET)){
    /*$page = $_GET['page'];
    $rows = $_GET['rows'];
    $search = $_GET['search'];*/

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    /*Pedidos listos para embarcar*/
    $sql = "SELECT 
            t.ID_Transporte AS id, 
            t.Nombre AS nombre, 
            t.Placas AS placas, 
            tt.desc_ttransporte AS tipo
            FROM t_transporte t 
            LEFT JOIN tipo_transporte tt 
            ON tt.clave_ttransporte = t.tipo_transporte 
            WHERE t.Activo = 1";

    /*if(!empty($search) && $search != '%20'){
        $sql.= " AND t.Nombre like '%".$search."%'";
    }*/

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    while($row = mysqli_fetch_array($res)){
        extract($row);
        $result [] = array(
            'clave'     => $id,
            'nombre'    => $nombre,
            'placas'    => $placas,
            'tipo'      => $tipo
        );
    };
    mysqli_close();
    header('Content-type: application/json');
    echo json_encode( $result);
}