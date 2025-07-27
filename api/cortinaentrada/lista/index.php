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
	$almacen =$_POST['almacen'];
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT
            count(tubicacionesretencion.cve_ubicacion) as cuenta
			,c_almacenp.nombre
			from 
			tubicacionesretencion,
			c_almacenp
            WHERE
            tubicacionesretencion.cve_almacp= c_almacenp.id and tubicacionesretencion.desc_ubicacion LIKE '%".$_criterio."%' AND tubicacionesretencion.Activo = 1 and c_almacenp.clave='$almacen' ";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "SELECT
            tubicacionesretencion.*
			,c_almacenp.nombre
			from 
			tubicacionesretencion,
			c_almacenp
            WHERE
            tubicacionesretencion.cve_almacp= c_almacenp.id and tubicacionesretencion.desc_ubicacion LIKE '%".$_criterio."%' AND tubicacionesretencion.Activo = 1 and c_almacenp.clave='$almacen'";
    // hace una llamada previa al procedimiento almacenado Lis_Facturas
  //echo var_dump($sql);
  //die();
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
    mysqli_set_charset($conn, 'utf8');
    while ($row = mysqli_fetch_array($res)) {
		//$row=array_map('utf8_encode',$row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['cve_ubicacion'];
        $responce->rows[$i]['cell']=array('', $row['cve_ubicacion'], utf8_encode($row['nombre']), utf8_encode($row['desc_ubicacion']), utf8_encode($row['AreaStagging']));
        $i++;
    }
    //echo var_dump($responce);

    echo json_encode($responce);
}
if(isset($_GET) && !empty($_GET)){
    $almacen = $_GET['almacen'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "SELECT cve_ubicacion AS clave, 
                    desc_ubicacion AS descripcion 
            FROM tubicacionesretencion 
            WHERE cve_almacp = {$almacen}
            AND Activo = 1";
    $query = mysqli_query($conn, $sql);
    $data = array();
    $id = 0;
    while($row = mysqli_fetch_array($query)){
        //$row=array_map('utf8_encode',$row);
        $data[$id]['value'] = array($row['clave'],$row['descripcion']);
        $id++;
    }
    mysqli_close($conn);
    echo json_encode($data);
}