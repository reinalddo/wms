<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    //$fecha_inicio= $_POST['fechaInicio'];
    //$fecha_fin =$_POST['fechaFin'];
    //$buscarR =  $_POST['buscarR'];
    //$buscarL = $_POST['buscarL'];
    $split = "";

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    //$_criterio = $_POST['criterio'];
	  $almacen = $_POST['almacen'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
    if (!empty($fecha_fin)) $fecha_inicio = date("d/m/Y", strtotime($fecha_inicio));
    if (!empty($fecha_fin)) $fecha_fin = date("d/m/Y", strtotime($fecha_fin));
  
    if(isset($buscarR) && $buscarR != "")
    {
        $split .= " AND c_articulo.cve_articulo like '%".$buscarR."%' ";  
    }
  
    if(isset($buscarL) && $buscarL != "")
    {
        $split .= " AND c_lotes.LOTE like '%".$buscarL."%' ";  
    }

    if ($fecha_inicio && $fecha_fin)
    {
        $split.= " and str_to_date(l.CADUCIDAD, '%d-%m-%Y') >=  str_to_date('$fecha_inicio', '%d/%m/%Y') and str_to_date(l.CADUCIDAD, '%d-%m-%Y') <= str_to_date('$fecha_fin', '%d/%m/%Y') ";
    }
*/
    if ($almacen!="")
    {
        $split.= " and c_almacenp.clave='$almacen' ";
    }
	
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;
/*
    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "
        SELECT 
           c_ubicacion.idy_ubica as id
        FROM ts_existenciapiezas
            inner join c_lotes on c_lotes.LOTE = ts_existenciapiezas.cve_lote and c_lotes.cve_articulo = ts_existenciapiezas.cve_articulo
            inner join c_articulo on c_articulo.cve_articulo = ts_existenciapiezas.cve_articulo
            inner join c_ubicacion on c_ubicacion.idy_ubica = ts_existenciapiezas.idy_ubica
            inner join c_unimed on c_unimed.id_umed = c_articulo.unidadMedida
            inner join c_almacenp on c_almacenp.id = ts_existenciapiezas.cve_almac
        where c_lotes.Caducidad < now()
            and Caducidad != DATE_FORMAT('0000-00-00', '%Y-%m-%d')
            and Existencia > 0
            AND c_articulo.Caduca = 'S'
            ".$split."
            order by ts_existenciapiezas.cve_articulo, ts_existenciapiezas.cve_lote;
    ";
  
    if (!($res = mysqli_query($conn, $sqlCount))) 
    {
        echo "Falló la preparación(1): (" . mysqli_error($conn) . ") ";
    }


    $row = mysqli_fetch_array($res);
    $count = $row['total'];

    mysqli_close();
*/
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $conexion = "";
/*
    if($conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME))
        $conexion = "OK";
    else 
        $conexion = "FAIL";
*/	
	  $_page = 0;
	
	  //if (intval($page)>0) $_page = ($page-1)*$limit;
/*
    $sql = "
        SELECT 
            c_articulo.cve_articulo as articulo,
            c_articulo.des_articulo as descripcion,
            c_lotes.LOTE as lote,
            date_format(c_lotes.Caducidad,'%d-%m-%Y') as caducidad,
            c_ubicacion.CodigoCSD as ubicacion,
            ts_existenciapiezas.Existencia as cantidad,
            c_unimed.des_umed as um
        FROM ts_existenciapiezas
            inner join c_lotes on c_lotes.LOTE = ts_existenciapiezas.cve_lote and c_lotes.cve_articulo = ts_existenciapiezas.cve_articulo
            inner join c_articulo on c_articulo.cve_articulo = ts_existenciapiezas.cve_articulo
            inner join c_ubicacion on c_ubicacion.idy_ubica = ts_existenciapiezas.idy_ubica
            inner join c_unimed on c_unimed.id_umed = c_articulo.unidadMedida
            inner join c_almacenp on c_almacenp.id = ts_existenciapiezas.cve_almac
        where c_lotes.Caducidad < now()
            and Caducidad != '0000-00-00'
            and Existencia > 0
            ".$split."
            order by ts_existenciapiezas.cve_articulo, ts_existenciapiezas.cve_lote;
    ";
*/
    $res = "";
    $sql = "
        SELECT 
                CONVERT(c_articulo.cve_articulo, CHAR) AS articulo,
                CONVERT(c_articulo.des_articulo, CHAR) AS descripcion,
                CONVERT(c_lotes.LOTE, CHAR) AS lote,
                DATE_FORMAT(c_lotes.Caducidad,'%d-%m-%Y') AS caducidad,
                CONVERT(c_ubicacion.CodigoCSD, CHAR) AS ubicacion,
                vp.Existencia AS cantidad,
                CONVERT(c_unimed.des_umed, CHAR) AS um
            FROM V_ExistenciaGralProduccion vp
                LEFT JOIN c_lotes ON c_lotes.LOTE = vp.cve_lote AND CONVERT(c_lotes.cve_articulo, CHAR) = CONVERT(vp.cve_articulo, CHAR)
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = vp.cve_articulo
                LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = vp.cve_ubicacion
                LEFT JOIN c_unimed ON c_unimed.id_umed = c_articulo.unidadMedida
                LEFT JOIN c_almacenp ON c_almacenp.id = vp.cve_almac
            WHERE DATE_FORMAT(c_lotes.Caducidad, '%Y-%m-%d') < CURDATE()
                AND DATE_FORMAT(c_lotes.Caducidad, '%Y-%m-%d') != DATE_FORMAT('0000-00-00', '%Y-%m-%d')
                AND vp.tipo = 'ubicacion'
                AND vp.Existencia > 0
                AND c_ubicacion.CodigoCSD != ''
                ".$split."
                AND c_articulo.Caduca = 'S'
                ORDER BY vp.cve_articulo, vp.cve_lote";

    //$fallo = "";

    $datos = "";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        //$fallo = "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
    else
    {
        $datos = mysqli_fetch_object($res);
    }
/*
    $sql = "
        SELECT 
                COUNT(*) as total
            FROM V_ExistenciaGralProduccion vp
                LEFT JOIN c_lotes ON c_lotes.LOTE = vp.cve_lote AND CONVERT(c_lotes.cve_articulo, CHAR) = CONVERT(vp.cve_articulo, CHAR)
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = vp.cve_articulo
                LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = vp.cve_ubicacion
                LEFT JOIN c_unimed ON c_unimed.id_umed = c_articulo.unidadMedida
                LEFT JOIN c_almacenp ON c_almacenp.id = vp.cve_almac
            WHERE DATE_FORMAT(c_lotes.Caducidad, '%Y-%m-%d') < CURDATE()
                AND DATE_FORMAT(c_lotes.Caducidad, '%Y-%m-%d') != DATE_FORMAT('0000-00-00', '%Y-%m-%d')
                AND vp.tipo = 'ubicacion'
                AND vp.Existencia > 0
                AND c_ubicacion.CodigoCSD != ''
                ".$split."
                AND c_articulo.Caduca = 'S'
                ORDER BY vp.cve_articulo, vp.cve_lote";

    //$fallo = "";
    if (!($resCount = mysqli_query($conn, $sql))) 
    {
        //$fallo = "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    //$count = mysqli_fetch_array($resCount)["total"];
*/
    if( $count >0 ) 
    {
        $total_pages = ceil($count/$limit);
    } 
    else 
    {
        $total_pages = 0;
    } 
    if ($page > $total_pages)
    {
        $page=$total_pages;
    }
    $responce["page"] = $page;
    $responce["total"] = $total_pages;
    $responce["records"] = $count;
    $responce["sql"] = $sql;
    $responce["limit"] = $limit;
    $responce["res"] = $res;
    $responce["conexion"] = $conexion;
    $responce["datos"] = $datos;//mysqli_fetch_array($resCount);
    //$responce["fallo"] = $fallo;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
    {
        //$row=array_map('utf8_encode', $row);
        $arr[] = $row;
        $responce["rows"][$i]['id']=$row['id'];
        $responce["rows"][$i]['cell']=array('',$row['articulo'],$row['descripcion'], ($row['lote']),$row['caducidad'], ($row['ubicacion']),$row['cantidad'], $row['um']);
        $i++;
    }
    echo json_encode($responce);
}

if(isset($_GET) && !empty($_GET) && !empty($_GET['cve_articulo'])){
    $clave = $_GET['cve_articulo'];
    $sql = "SELECT LOTE FROM c_lotes WHERE cve_articulo = '$clave'";
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $query = mysqli_query($conn, $sql) or die (mysqli_error(\db2()));
    if($query->num_rows >0)
    {
        $data= [];
        while($row = mysqli_fetch_array($query)){$data [] = $row['LOTE'];}
    }
    mysqli_close();
    echo json_encode($data);
}