<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $search = $_POST['criterio'];
    $almacen = $_POST['almacen'];

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
    $sqlCount = "Select COUNT(*) AS cuenta from t_ubicaciones_revision Where cve_almac = '$almacen' AND Activo = '1';";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "SELECT
                    r.ID_URevision,
                    a.nombre as cve_almac,
                    r.cve_ubicacion,
                    r.descripcion,
                    r.AreaStagging
			FROM t_ubicaciones_revision r
            LEFT JOIN c_almacenp a ON a.clave = r.cve_almac
			WHERE r.cve_almac = '$almacen'";
    if(!empty($search)){
        $sql .= " AND r.descripcion LIKE '%$search%'";
    }
    $sql .= " AND r.Activo = 1 LIMIT $start, $limit";
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
        $row=array_map('utf8_encode',$row);
        $responce->rows[$i]['id']=$row['ID_URevision'];
        $responce->rows[$i]['cell']=array('',
            $row['ID_URevision'], 
            $row['cve_ubicacion'], 
            $row['cve_almac'],
            $row['descripcion'],
            $row['AreaStagging']
        );
        $i++;
    }
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
                    descripcion
            FROM t_ubicaciones_revision 
            WHERE cve_almac = {$almacen}
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