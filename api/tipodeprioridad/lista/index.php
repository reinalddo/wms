<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) 
{
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $_criterio = $_POST['criterio'];

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
    $sqlCount = "Select * from t_tiposprioridad Where Activo = '1'";
    if (!($res = mysqli_query($conn, $sqlCount))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close($conn);

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "Select *, if (t_tiposprioridad.Status='A','Activo','Baja') as Status from t_tiposprioridad Where Descripcion like '%".$_criterio."%' and Activo = '1' order by ID_Tipoprioridad ASC;";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) 
    {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } 
    else 
    {
        $total_pages = 0;
    } 
    if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        $row = array_map("utf8_encode", $row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['ID_Tipoprioridad'];
        $responce->rows[$i]['cell']=array('',$row['ID_Tipoprioridad'], $row['Descripcion']);
        $i++;
    }
    echo json_encode($responce);
}
if(isset($_GET) && !empty($_GET) && $_GET['action'] === 'obtenerSiguienteID')
{
    $clave = '';
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "SELECT COALESCE(MAX(ID_Tipoprioridad), 0) + 1 AS clave FROM t_tiposprioridad";
    $query = mysqli_query($conn, $sql);
    if($query->num_rows > 0)
    {
        $clave = mysqli_fetch_row($query)[0];
    }
    mysqli_close($conn);
    echo json_encode(array("clave" => $clave));
}