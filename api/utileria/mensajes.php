<?php 
include '../../config.php';

if(isset($_POST) && !empty($_POST)){
	$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	if($_POST['action'] === 'loadOnce'){
		extract($_POST);
		$sql = "SELECT 	id,
						clave,
						descripcion,
						mensaje,
						DATE_FORMAT(fecha_inicio, '%d-%m-%Y') AS fecha_inicio,
						DATE_FORMAT(fecha_inicio, '%d-%m-%Y') AS fecha_final
				FROM t_mensaje
				WHERE	id = {$id};
		";
		$query = mysqli_query($conn, $sql);
		$data = mysqli_fetch_assoc($query);
		echo json_encode(
			$data
		);
	}
	if($_POST['action'] === 'add'){
		extract($_POST);
		$sql = "INSERT INTO t_mensaje
				SET 
					clave = '{$clave}',
					descripcion = '{$descripcion}',
					mensaje = '{$mensaje}',
					fecha_inicio = '{$fecha_inicio}',
					fecha_final = '{$fecha_final}';
		";
		$query = mysqli_query($conn, $sql);
		echo json_encode(
			array(
				"title" => $query ? "Éxito" : "Error",
				"message" => $query ? "El mensaje se ha añadido correctamente" : "Ocurrión un error: ".mysqli_error($conn),
				"type" => $query ? "success" : "error"
			)
		);
	}
	if($_POST['action'] === 'edit'){
		extract($_POST);
		$sql = "UPDATE t_mensaje
				SET 
					clave = '{$clave}',
					descripcion = '{$descripcion}',
					mensaje = '{$mensaje}',
					fecha_inicio = '{$fecha_inicio}',
					fecha_final = '{$fecha_final}'
					WHERE id = {$id};
		";
		$query = mysqli_query($conn, $sql);
		echo json_encode(
			array(
				"title" => $query ? "Éxito" : "Error",
				"message" => $query ? "El mensaje se ha editado correctamente" : "Ocurrión un error: ".mysqli_error($conn),
				"type" => $query ? "success" : "error"
			)
		);
	}

	if($_POST['action'] === 'delete'){
		extract($_POST);
		$sql = "UPDATE t_mensaje
				SET 
					activo = '0'
					WHERE id = {$id};
		";
		$query = mysqli_query($conn, $sql);
		echo json_encode(
			array(
				"title" => $query ? "Éxito" : "Error",
				"message" => $query ? "El mensaje se ha eliminado correctamente" : "Ocurrión un error: ".mysqli_error($conn),
				"type" => $query ? "success" : "error"
			)
		);
	}
	if($_POST['action'] === 'load'){
		$page = $_POST['page']; // get the requested page
	    $limit = $_POST['rows']; // get how many rows we want to have into the grid
	    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
	    $sord = $_POST['sord']; // get the direction

	    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

	    if(!$sidx) $sidx =1;

	    // se conecta a la base de datos
	    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	    // prepara la llamada al procedimiento almacenado Lis_Facturas
	    $sqlCount = "SELECT COUNT(id) AS total FROM t_mensaje WHERE activo = '1';";
	    if (!($res = mysqli_query($conn, $sqlCount))) {
	        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
	    }

	    $row = mysqli_fetch_array($res);
	    $count = $row["total"];
	    if (intval($page)>0) $_page = ($page-1)*$limit;

	    $sql = "SELECT
	                id,
	                clave,
	                descripcion,
	                mensaje,
	                DATE_FORMAT(fecha_inicio, '%d-%m-%Y') AS fecha_inicio,
	                DATE_FORMAT(fecha_final, '%d-%m-%Y') AS fecha_final
	    		FROM t_mensaje
	            Where activo = '1' AND clave != 'FPAG' LIMIT $start, $limit;";

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
	    $responce = new StdClass();
	    $responce->page = $page;
	    $responce->total = $total_pages;
	    $responce->records = $count;

	    $arr = array();
	    $i = 0;

	    while ($row = mysqli_fetch_array($res)) {
	        $row = array_map('utf8_encode', $row);
	        extract($row);
	        $responce->rows[$i]['id']=$id;
	        $responce->rows[$i]['cell']=array($id, $clave, $descripcion, $mensaje, $fecha_inicio, $fecha_final);
	        $i++;
	    }
	    echo json_encode($responce);
	}
}

 ?>