<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
    $fecha_inicio= $_POST['fechaInicio'];
    $fecha_fin =$_POST['fechaFin'];
    $search =  $_POST['search'];
    $searchL = $_POST['searchL'];
    $split = "";

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];
	  $almacen = $_POST['almacen'];

    $sqlAlmacen = '' ;

    if(!empty($almacen)){
        $sqlAlmacen = " AND c_almacenp.clave='{$almacen}' ";
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($fecha_fin)) $fecha_inicio = date("d/m/Y", strtotime($fecha_inicio));
    if (!empty($fecha_fin)) $fecha_fin = date("d/m/Y", strtotime($fecha_fin));
    if(isset($search) && $search != "")
    {
        $split .= " AND c_articulo.cve_articulo like '%".$search."%' ";  
    }
  
    if(isset($searchL) && $searchL != "")
    {
        $split .= " AND c_lotes.LOTE like '%".$searchL."%' ";  
    }

    if ($fecha_inicio && $fecha_fin)
    {
        $split.= " and str_to_date(l.CADUCIDAD, '%d-%m-%Y') >=  str_to_date('$fecha_inicio', '%d/%m/%Y') and str_to_date(l.CADUCIDAD, '%d-%m-%Y') <= str_to_date('$fecha_fin', '%d/%m/%Y') ";
    }
  
    if ($almacen!="")
    {
        $split.= " and c_almacenp.clave='$almacen' ";
    }
	
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

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
        where c_lotes.Caducidad > now()
            and Caducidad <= date_add(now(), interval 3 month)
            and Caducidad != '0000-00-00'
            and Existencia > 0
            ".$split."
            order by ts_existenciapiezas.cve_articulo, ts_existenciapiezas.cve_lote;
    ";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }



    $row = mysqli_fetch_array($res);
    $count = $row['total'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	
	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;

    $sql = "SELECT 
                c_articulo.cve_articulo AS articulo,
                c_articulo.des_articulo AS descripcion,
                c_lotes.LOTE AS lote,
                DATE_FORMAT(c_lotes.Caducidad,'%d-%m-%Y') AS caducidad,
                c_ubicacion.CodigoCSD AS ubicacion,
                vp.Existencia AS cantidad,
                c_unimed.des_umed AS um
            FROM V_ExistenciaGral vp
            LEFT JOIN c_lotes ON c_lotes.LOTE = vp.cve_lote AND c_lotes.cve_articulo = vp.cve_articulo
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = vp.cve_articulo
            LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = vp.cve_ubicacion
            LEFT JOIN c_unimed ON c_unimed.id_umed = c_articulo.unidadMedida
            LEFT JOIN c_almacenp ON c_almacenp.id = vp.cve_almac
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vp.ID_Proveedor
            WHERE c_lotes.Caducidad BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 12 MONTH)
            AND c_lotes.Caducidad != '0000-00-00'
            AND vp.tipo = 'ubicacion'
            AND Existencia > 0
            AND c_ubicacion.CodigoCSD != ''
            {$sqlAlmacen}
            ORDER BY caducidad ASC";



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

    $responce["page"] = $page;
    $responce["total"] = $total_pages;
    $responce["records"] = $count;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
           $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        $responce["rows"][$i]['id']=$row['id'];
        $responce["rows"][$i]['cell']=array($row[''],$row['articulo'],$row['descripcion'], utf8_encode($row['lote']),$row['caducidad'], utf8_encode($row['ubicacion']),$row['cantidad'], $row['um']);
        $i++;
    }
    echo json_encode($responce);
}

if(isset($_GET) && !empty($_GET) && !empty($_GET['cve_articulo'])){
    $clave = $_GET['cve_articulo'];
    $sql = "SELECT LOTE FROM c_lotes WHERE cve_articulo = '$clave'";
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $query = mysqli_query($conn, $sql) or die (mysqli_error(\db2()));
    if($query->num_rows >0){
        $data= [];
        while($row = mysqli_fetch_array($query)){
            $data [] = $row['LOTE'];
        }
    }
    mysqli_close();
    echo json_encode($data);
}