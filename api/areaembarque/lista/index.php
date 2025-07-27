<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) 
{
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];
	  $almacen= $_POST['almacen'];
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
/*
    $sqlCount = "
        SELECT
            count(u.ID_Embarque) as cuenta,
            u.cve_ubicacion,			
            a.nombre,
            u.status,
            u.descripcion
			  FROM 
            t_ubicacionembarque u,
            c_almacenp a
        WHERE
            u.cve_almac = a.id AND 
            u.descripcion LIKE '%{$_criterio}%' AND 
            u.Activo = 1 AND 
            a.clave = '{$almacen}'
    ";
			
    if (!($res = mysqli_query($conn, $sqlCount))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close();
*/
    $sql_stagging = "";
    if($_POST['area_stagging'] != "")
    {
        $stagging = $_POST['area_stagging'];
        $sql_stagging = " AND u.AreaStagging = '{$stagging}' ";
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "
        SELECT
            u.ID_Embarque,
            u.cve_ubicacion,
            a.nombre,
            case 
              when u.status = 1 then 'Libre'
              when u.status = 2 then 'Embarcando'
              when u.status = 3 then 'Completo'
            end as status,
            u.descripcion,
            u.AreaStagging
        from 
            t_ubicacionembarque u,
            c_almacenp a
        WHERE u.cve_almac= a.id 
            and u.descripcion LIKE '%".$_criterio."%' 
            {$sql_stagging}
            AND u.Activo = 1 
            and a.clave='$almacen'
    ";
    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_num_rows($res);

    $sql .= " LIMIT $start, $limit ";
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

    //$_page = 0;
    //if (intval($page)>0) $_page = ($page-1)*$limit;


    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
		    $row=array_map('utf8_encode',$row);
        $arr[] = $row;
        if($row['AreaStagging'] == "S")
        {
            $row['AreaStagging'] = "SI";
        }
        else
        {
            $row['AreaStagging'] = "NO";
        }
        $responce->rows[$i]['id']=$row['ID_Embarque'];
        $responce->rows[$i]['cell']=array($row['cve_ubicacion'], '',$row['cve_ubicacion'],$row['descripcion'],$row['status'], $row['AreaStagging'],$row['nombre']);
        $i++;
    }
    echo json_encode($responce);
}
if(isset($_GET) && !empty($_GET))
{
    $almacen = $_GET['almacen'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sql = "
        SELECT cve_ubicacion AS clave, descripcion 
        FROM t_ubicacionembarque 
        WHERE cve_almac = {$almacen}
        AND Activo = 1
    ";
    $query = mysqli_query($conn, $sql);
    $data = array();
    $id = 0;
    while($row = mysqli_fetch_array($query))
    {
        //$row=array_map('utf8_encode',$row);
        $data[$id]['value'] = array($row['clave'],$row['descripcion']);
        $id++;
    }
    mysqli_close($conn);
    echo json_encode($data);
}