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
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT
            count(c_almacen.cve_almac) as cuenta,
			 c_almacen.clave_almacen,
            c_almacen.des_almac,
            c_almacen.des_direcc,
            c_almacen.Activo,
			c_almacenp.nombre
            FROM
            c_almacen ,c_almacenp
			where c_almacen.cve_almacenp= c_almacenp.id and
			(c_almacen.des_almac like '%".$_criterio."%' or c_almacen.clave_almacen like '%".$_criterio."%') and c_almacen.Activo = '1' and c_almacen.cve_almacenp='$almacen';";
	//echo $sqlCount; exit;		
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

	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;
	
    $sql = "SELECT
            c_almacen.cve_almac,
            c_almacen.clave_almacen,
            c_almacen.des_almac,
            c_almacen.des_direcc,
            c_almacen.Activo,
            c_almacen.clasif_abc,
            c_proveedores.Nombre as empresa_proveedor,
            tipo_almacen.desc_tipo_almacen as tipo_almacen,
			c_almacenp.nombre
          FROM c_almacen 
          LEFT JOIN tipo_almacen ON c_almacen.Cve_TipoZona = tipo_almacen.id
          LEFT JOIN c_proveedores ON c_proveedores.ID_Proveedor = c_almacen.ID_Proveedor
          ,c_almacenp
			    where c_almacen.cve_almacenp = c_almacenp.id 
          and (c_almacen.des_almac like '%".$_criterio."%' or c_almacen.clave_almacen like '%".$_criterio."%') 
          and c_almacen.Activo = '1' 
          and c_almacen.cve_almacenp='$almacen' 
          order by nombre
          LIMIT $_page, $limit";
			
    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    //echo var_dump($sql);

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
        $row = array_map('utf8_encode', $row);
        $responce->rows[$i]['id']=$row['cve_almac'];
        $responce->rows[$i]['cell']=array('', $row['clave_almacen'],$row['clave_almacen'], $row['des_almac'], $row['nombre'],$row['tipo_almacen'],$row['clasif_abc'],$row['empresa_proveedor']);
        $i++;
    }
    
    echo json_encode($responce);
}

if(isset($_GET) && !empty($_GET)){
    $page = $_GET['page'];
    $rows = $_GET['rows'];
    $search = $_GET['search'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    /*Pedidos listos para embarcar*/
    $sql = "SELECT clave, nombre FROM c_almacenp WHERE Activo = 1";

    if(!empty($search) && $search != '%20'){
        $sql.= " AND nombre like '%".$search."%'";
    }

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    while($row = mysqli_fetch_array($res)){
        extract($row);
        $result [] = array(
            'clave' => $clave,
            'descripcion' => $nombre
        );
    };
    mysqli_close();
    header('Content-type: application/json');
    echo json_encode( $result);
}