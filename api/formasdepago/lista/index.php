<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio   = $_POST['criterio'];
    $cve_almacen = $_POST['cve_almacen'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    mysqli_set_charset('utf8');

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));
    $SqlBusqueda = "";
    if(isset($_POST['criterio']))
    {
        if(!empty($_POST['criterio']))
        {
            $SqlBusqueda = "and (Id like '%".$_criterio."%' OR ListaMaster like '%".$_criterio."%' OR Promociones like '%".$_criterio."%')"; 
        }
    }

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "
        Select 
            COUNT(*) as total
        from ListaPromoMaster 
        Where 1 AND Cve_Almac = (SELECT id FROM c_almacenp WHERE clave = '{$cve_almacen}') ".$SqlBusqueda." ;";
	
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    //$row = mysqli_fetch_array($res);
    //$count = $row['total'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;//

    $sql = "
        SELECT *
        from FormasPag 
        Where IdEmpresa = '{$cve_almacen}' AND Status = 1
        order by IdFpag
        ";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
    $count = mysqli_num_rows($res);

    $sql .= "LIMIT $_page, $limit;";

    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce["page"] = $page;
    $responce["total"] = $total_pages;
    $responce["records"] = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $arr[] = $row;

        $responce["rows"][$i]['IdFpag']=$row['IdFpag'];
        $responce["rows"][$i]['cell']=array('', $row['Clave'], utf8_encode($row['Forma']));
        $i++;
    }
    echo json_encode($responce);
}

if (isset($_GET) && !empty($_GET) && $_GET['action'] === "getListasGrupo") 
{
   $page = $_GET['page']; // get the requested page
   $limit = $_GET['rows']; // get how many rows we want to have into the grid
   $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
   $sord = $_GET['sord']; // get the direction
   $id = $_GET['id'];

   $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
    $sqlCount = "SELECT COUNT(*) AS total FROM detallelp WHERE ListaId = {$id};";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_fetch_array($res)['total'];
*/
    $sql = "SELECT lp.id, lp.Lista, lp.Descripcion
            FROM ListaPromo lp 
            LEFT JOIN DetalleLProMaster dlp ON lp.id = dlp.IdPromo
            WHERE dlp.IdLm = {$id}";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);

    $sql .= " LIMIT $start, $limit;";
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

    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row=array_map('utf8_encode', $row);
        $responce->rows[$i]['id']=$row['id'];
        $responce->rows[$i]['cell']=array(
                                          '',
                                          $row['id'], 
                                          $row['Lista'], 
                                          utf8_decode($row['Descripcion']),
                                          );
        $i++;
    }
    echo json_encode($responce);
}
