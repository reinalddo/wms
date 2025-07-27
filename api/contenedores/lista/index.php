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
	$almacen     = $_POST['almacen'];
	$tipo_pallet = $_POST['vacio'];
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

	$pallet_generico = "";
	$pallet_generico_count = "";
	if($tipo_pallet != 2) //todos
	{
		$pallet_generico = " AND c_charolas.TipoGen = $tipo_pallet ";
		$pallet_generico_count = " AND c.TipoGen = $tipo_pallet ";
	}

    if(!$sidx) $sidx =1;
/*
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); // se conecta a la base de datos
    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "
				Select count(*) as cuenta
				FROM
            c_charolas c, c_almacenp a
				where a.clave=c.cve_almac 
				  and c.Activo = 1 
				  and a.id='$almacen'".$pallet_generico_count;
			
    if(!($res = mysqli_query($conn, $sqlCount)))
		{
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row["cuenta"];
    mysqli_close();
*/
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

		$_page = 0;

		if(intval($page)>0) $_page = ($page-1)*$limit;
		$condicion= '';
  	if($_criterio != ''){
    $condicion =" AND (c_charolas.clave_contenedor LIKE '%".$_criterio."%' or c_charolas.descripcion LIKE '%".$_criterio."%') ";}
	
	$class1 = '"text-success"';
	$class2 = '"fa fa-check"';
	$class3 = '"text-danger"';
	$class4 = '"fa fa-close"';

	$pallet_generico = "";
	if($tipo_pallet != 2) //todos
	{
		$pallet_generico = " AND c_charolas.TipoGen = $tipo_pallet ";
	}

    $sql = "
				SELECT
						c_charolas.IDContenedor,
						#IF((c_charolas.clave_contenedor = V_EntradasContenedores.Clave_Contenedor AND c_charolas.Permanente = 1) OR
						#   (c_charolas.clave_contenedor = tde.ClaveEtiqueta) OR (c_charolas.CveLP = tde.ClaveEtiqueta)
						#, 'Ocupado','Libre') AS statu,
						'' AS statu,
						c_charolas.descripcion,
						c_charolas.peso,
						c_charolas.pesomax,
						c_charolas.capavol,
						c_charolas.alto,
						c_charolas.ancho,
						c_charolas.fondo,
						c_charolas.tipo,
						c_charolas.clave_contenedor,
						c_almacenp.nombre as des_almac,
						c_charolas.cve_almac,
						IF(c_charolas.TipoGen = 1,'<div class=$class1><i class=$class2></i></div>','<div class=$class3><i class=$class4></i></div>') AS TipoGen, 
						IFNULL(c_charolas.TipoGen, 0) AS TipoGenVal
				FROM c_charolas
						LEFT JOIN c_almacenp ON (c_almacenp.id = c_charolas.cve_almac)
						#LEFT JOIN V_EntradasContenedores ON V_EntradasContenedores.Clave_Contenedor = c_charolas.clave_contenedor
						#LEFT JOIN td_entalmacenxtarima tde ON tde.ClaveEtiqueta = c_charolas.clave_contenedor OR tde.ClaveEtiqueta = c_charolas.CveLP
				WHERE #(c_almacenp.clave = c_charolas.cve_almac OR c_almacenp.id = c_charolas.cve_almac )
					c_almacenp.id = c_charolas.cve_almac
					AND c_charolas.Activo = 1
					#AND (c_almacenp.id = '$almacen' OR c_almacenp.clave = (SELECT clave FROM c_almacenp WHERE id = '$almacen'))
					AND (c_almacenp.id = '$almacen')
					AND (c_charolas.alto*c_charolas.ancho*c_charolas.fondo) > 0
					#AND IFNULL(c_charolas.TipoGen, 0) = 0
					#AND c_charolas.clave_contenedor != IFNULL(c_charolas.CveLP, '')
					AND IFNULL(c_charolas.CveLP, '') = ''
	 				".$condicion.$pallet_generico."
				GROUP BY c_charolas.IDContenedor
				";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql)))
		{
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $count = mysqli_num_rows($res);

    $sql .= " LIMIT $_page, $limit;";
    if (!($res = mysqli_query($conn, $sql)))
		{
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }


    if( $count >0 )
		{
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->query = $sql;
    $responce->criterio = $_criterio;

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) 
		{
				$row = array_map('utf8_encode', $row);
        $arr[] = $row;
				$row['peso'] = str_replace('.',',',$row['peso']);
				$row['pesomax'] = str_replace('.',',',$row['pesomax']);
        $responce->rows[$i]['id'] = $row['IDContenedor'];
        $responce->rows[$i]['cell'] = array(
						$row['IDContenedor'],
						$row[''],
						$row['clave_contenedor'],
						$row['descripcion'],
						$row['statu'],
						$row['tipo'],
						$row['alto'],
						$row['ancho'],
						$row['fondo'],
						number_format($row['peso'], 2, '.', ','),
						number_format($row['pesomax'], 2, '.', ','),
						number_format($row['capavol'], 2, '.', ','),
						$row['des_almac'],
						$row['TipoGen']
				);
        $i++;
    }
    echo json_encode($responce);
}