<?php
include '../../../config.php';
error_reporting(0);
if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

	$_almacen = $_POST['almacen'];

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT
            COUNT(*) as total
            FROM
            c_almacenp, c_ubicacion, c_almacen
            where c_ubicacion.cve_almac=c_almacen.cve_almac
            and c_almacen.cve_almacenp=c_almacenp.id
            and c_almacenp.clave=".$_almacen."
            and c_ubicacion.cve_almac = c_almacen.cve_almac
            and c_ubicacion.Activo = '1'
            order by c_ubicacion.".$sidx." ".$sord;
				
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['total'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$_page = 0;
	
	if (intval($page)>0) $_page = ($page-1)*$limit;//
	
	$sql = "SELECT
            c_ubicacion.idy_ubica,
            c_ubicacion.cve_almac,
            c_ubicacion.cve_pasillo,
            c_ubicacion.cve_rack,
            c_ubicacion.cve_nivel,
            c_ubicacion.num_ancho,
            c_ubicacion.num_largo,
            c_ubicacion.num_alto,
            c_ubicacion.picking,
            c_ubicacion.Seccion,
            c_ubicacion.Ubicacion,
            c_ubicacion.PesoMaximo,
            c_ubicacion.CodigoCSD,
            c_ubicacion.TECNOLOGIA,
            c_ubicacion.Activo,
            c_almacen.cve_almac,
            c_almacen.des_almac
            FROM
            c_almacenp, c_ubicacion, c_almacen
            where c_ubicacion.cve_almac=c_almacen.cve_almac
			and c_almacen.cve_almacenp=c_almacenp.id 
			and c_almacenp.clave=".$_almacen."
			and c_ubicacion.cve_almac = c_almacen.cve_almac 
			and c_ubicacion.Activo = '0' ";
			
			
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
        $arr[] = $row;
		$row['cve_rack']='0'.$row['cve_rack'];
        
		$row["dim"] = number_format($row['num_largo'], 2, '.', '').'  X  '.number_format($row['num_ancho'], 2, '.', '').'  X  '.number_format($row['num_alto'], 2, '.', '');
		
        $responce["rows"][$i]['id']=$row['idy_ubica'];
        $responce["rows"][$i]['cell']=array(
            $row['cve_almac'],
            $row['idy_ubica'],
            $row['CodigoCSD'], 
            utf8_encode($row['des_almac']), 
            utf8_encode($row['cve_pasillo']), 
            $row['cve_rack'],
            $row['cve_nivel'],
            $row['Seccion'],
            $row['Ubicacion'],
            $row['PesoMaximo'],
            $row["dim"],			
            utf8_encode($row['picking'])
        );
        $i++;
    }
    echo json_encode($responce);
}