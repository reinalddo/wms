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

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));
  
    $and = "";
    if($_criterio != '')
    {
        $and =" and ss.des_ssgpoart like '%".$_criterio."%' ";
    }
  

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "
        SELECT
            ss.cve_ssgpoart,
            ss.cve_sgpoart,
            ss.des_ssgpoart,
            ss.Opcinal,
            ss.Activo,
            s.des_sgpoart
        FROM
            c_ssgpoarticulo ss
        inner join c_sgpoarticulo s on ss.cve_sgpoart = s.cve_sgpoart
		    where ss.Activo = '1' 
        ".$and."
        order by ss.".$sidx." ".$sord;
			
    if (!($res = mysqli_query($conn, $sqlCount))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	  $_page = 0;
	
	  if (intval($page)>0) $_page = ($page-1)*$limit;//
	
    $sql = "
        SELECT
			      ss.id,
            ss.cve_ssgpoart,
            ss.cve_sgpoart,
            ss.des_ssgpoart,
            ss.Opcinal,
            ss.Activo,
            s.des_sgpoart
        FROM  c_ssgpoarticulo ss
        inner join c_sgpoarticulo s on ss.cve_sgpoart = s.cve_sgpoart
        where ss.Activo = '1' 
            ".$and." 
        order by ss.".$sidx." ".$sord."
        LIMIT $_page, $limit;
    ";

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

    $responce["page"] = $page;
    $responce["total"] = $total_pages;
    $responce["records"] = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        $arr[] = $row;
        $responce["rows"][$i]['id']=$row['id'];
        $responce["rows"][$i]['cell']=array($row['id'],$row['cve_ssgpoart'], $row['des_sgpoart'],utf8_encode($row['des_ssgpoart']));
        $i++;
    }
    echo json_encode($responce);
}