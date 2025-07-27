<?php
include '../../../config.php';

error_reporting(0);


if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
	$almacenp = $_POST['almacen'];
    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];
	
	$ands ="";
	
	if (!empty($_criterio)){
	   $ands .= " AND d.RazonSocial like '%$_criterio%' ";
	}
	
	
	$codigo = $_POST['codigo'];
	if (!empty($codigo)) $ands.= " AND d.postal = '$codigo' ";
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT COUNT(id_destinatario) AS cuenta FROM c_destinatarios d WHERE Activo = '1' $ands";

	
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row['cuenta'];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT  d.id_destinatario AS id,
                    c.RazonSocial AS cliente, 
                    d.razonsocial AS destinatario,
                    d.direccion,
                    d.colonia,
                    d.postal,
                    d.ciudad,
                    d.estado,
                    d.contacto,
                    d.telefono
            FROM c_destinatarios d, c_cliente c

            WHERE d.Activo = '1' and c.Cve_Almacenp = '".$almacenp."' and c.Cve_Clte = d.Cve_Clte $ands 
            ORDER BY d.id_destinatario DESC
            LIMIT {$start}, {$limit} ;";

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
		$row=array_map('utf8_encode',$row);
        extract($row);
        $responce->rows[$i]['id']=$id;
        $responce->rows[$i]['cell']=array($id, $cliente, $destinatario, $direccion, $colonia, $postal, $ciudad, $estado, $contacto, $telefono);
        $i++;
    }
    echo json_encode($responce);
}

