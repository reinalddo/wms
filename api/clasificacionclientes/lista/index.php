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
    $and = "";
    if($_criterio != '')
    {
        $and =" and tc.Des_TipoCte like '%".$_criterio."%' ";
    }
  
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $sqlCount = "
        SELECT count(*)
            FROM
            c_tipocliente tc
        where tc.Activo = '1' 
        ".$and."
        order by ".$sidx." ".$sord;

    if(isset($_POST['clasif2']))
        $sqlCount = "
            SELECT count(*)
                FROM
                c_tipocliente2 tc
            where tc.Activo = '1' 
            ".$and."
            order by ".$sidx." ".$sord;
				
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

    $_page = 0;
    if (intval($page)>0) $_page = ($page-1)*$limit;
    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

	  $_page = 0;
	  if (intval($page)>0) $_page = ($page-1)*$limit;//
	
    $sql = "
        SELECT
            tc.id,
            g.cve_grupo,
            g.des_grupo,
            tc.Cve_TipoCte,
            tc.Des_TipoCte
        FROM
            c_tipocliente tc
            LEFT JOIN c_gpoclientes g ON g.id = tc.id_grupo
        WHERE tc.Activo = '1' 
                    ".$and." 
                order by ".$sidx." ".$sord."
                LIMIT $_page, $limit;
            ";

    if(isset($_POST['clasif2']))
        $sql = "
            SELECT
                tc.id,
                ct.Cve_TipoCte AS cve_grupo,
                ct.Des_TipoCte AS des_grupo, 
                tc.Cve_TipoCte,
                tc.Des_TipoCte
            FROM
            c_tipocliente2 tc
            LEFT JOIN c_tipocliente ct ON ct.id = tc.id_tipocliente
            WHERE tc.Activo = '1'
            ".$and." 
            order by ".$sidx." ".$sord."
            LIMIT $_page, $limit;
                ";

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) 
    {
        $total_pages = ceil($count/$limit);
    } 
    else 
    {
        $total_pages = 0;
    } 
    if ($page > $total_pages)
        $page=$total_pages;

    $responce["page"] = $page;
    $responce["total"] = $total_pages;
    $responce["records"] = $count;
    //$responce["query"] = $sql;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        $arr[] = $row;
        $responce["rows"][$i]['id']=utf8_encode($row['Cve_TipoCte']);
        $responce["rows"][$i]['cell']=array('', utf8_encode("(".$row['cve_grupo'].") ".$row['des_grupo']), utf8_encode($row['Cve_TipoCte']), utf8_encode($row['Des_TipoCte']));
        $i++;
    }
    echo json_encode($responce);
}