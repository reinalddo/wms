<?php

/**
 * Lists
 **/

$app->map('/importar/clientes', function() use ($app) {

    $app->render( 'page/importar/clientes.php' );

})->via( 'GET');

$app->map('/importar/pedidos', function() use ($app) {

    $app->render( 'page/importar/pedidos.php' );

})->via( 'GET');

$app->map('/importar/ocentradas', function() use ($app) {

    $app->render( 'page/importar/ocentradas.php' );

})->via( 'GET');

$app->map('/importar/cross', function() use ($app) {

    $app->render( 'page/importar/cross.php' );

})->via( 'GET');

$app->get('/importar/stock', function() use ($app) {

    $app->render( 'page/importar/stock.php' );

});

$app->post('/importar/pedidos', function() use ($app){
	$tipo = $_POST['tipo'];
	$file = $_FILES['layout']['tmp_name'];
    $reader = fopen($file, "r");
    
    if($reader){
    	$insert = "";
    	while (($line = fgets($reader)) !== false) {
    		$data = explode("|", $line);
    		$data = array_map("trim", $data);
    		if(count($data) > 1){
    			if($tipo === "th" && count($data) === 21){
    				$insertData = [
    					"Fol_folio"			=> $data[0],
    					"Fec_Pedido"		=> !empty($data[1]) ? date('Y-m-d', strtotime($data[1])) : '',
    					"Cve_clte"			=> $data[2],
    					"status"			=> 'A',
    					"Fec_Entrega"		=> !empty($data[4]) ? date('Y-m-d', strtotime($data[4])) : '',
    					"cve_Vendedor"		=> $data[5],
    					"Num_Meses"			=> $data[6],
    					"Observaciones"		=> $data[7],
    					"ID_Tipoprioridad"	=> $data[8],
    					"Fec_Entrada"		=> !empty($data[9]) ? date('Y-m-d', strtotime($data[9])) : '',
    					"transporte"		=> $data[10],
    					"cve_almac"			=> $data[16],
    					"Cve_Usuario"		=> $data[18],
    					"Ship_Num"			=> $data[19]

    				];
    				$last_key = key( array_slice( $insertData, -1, 1, TRUE ) );
    				$insertStm = "INSERT IGNORE INTO th_pedido SET ";
    				foreach($insertData as $setName => $setValue){  
	                    if(!empty($setValue)){
	                        $insertStm .= " {$setName} = \"$setValue\",";
	             
	                    }
	                }
	                $insertStm .= ";";
	                if(substr($insertStm, -2) === ',;'){
	                	$insertStm = substr($insertStm, 0, -2)."; ";
	                }
	                $insert .= $insertStm;
    			}
                if($tipo === "td" && count($data) === 11){
                    $insertData = [
                        "Fol_folio"         => $data[0],
                        "Cve_articulo"      => $data[1],
                        "Num_cantidad"      => intval($data[2]),
                        "SurtidoXPiezas"    => $data[9],
                        "status"            => "A"
                    ];
                    $last_key = key( array_slice( $insertData, -1, 1, TRUE ) );
                    $insertStm = "INSERT IGNORE INTO td_pedido SET ";
                    foreach($insertData as $setName => $setValue){  
                        if(!empty($setValue)){
                            $insertStm .= " {$setName} = \"$setValue\",";
                 
                        }
                    }
                    $insertStm .= ";";
                    if(substr($insertStm, -2) === ',;'){
                        $insertStm = substr($insertStm, 0, -2)."; ";
                    }
                    $insert .= $insertStm;
                }
    		}
    	}
    }
    fclose($reader);
    $query = mysqli_multi_query(\db2(), $insert);

    if($query){
        $app->render('page/importar/pedidos.php', array("message" => "Pedidos importados exitosamente"));
    }
    else{
        $app->render('page/importar/pedidos.php', array("error" => "Ocurrio un error al importar los pedidos ".mysqli_error(\db2())));
    }

});

$app->post('/importar/cross', function() use ($app){
    $tipo = $_POST['tipo'];
    $file = $_FILES['layout']['tmp_name'];
    $reader = fopen($file, "r");
    
    if($reader){
        $insert = "";
        while (($line = fgets($reader)) !== false) {
            $data = explode("|", $line);
            $data = array_map("trim", $data); 
            if(count($data) >1){
                if($tipo === "th" && count($data) === 19){
                    $insertData = [
                        "CodB_Prov"         =>  $data[0],
                        "NIT_Prov"          =>  $data[1],
                        "Nom_Prov"          =>  addslashes($data[2]),
                        "Cve_CteCon"        =>  $data[4],
                        "CodB_CteCon"       =>  $data[5],
                        "Nom_CteCon"        =>  addslashes($data[6]),
                        "Dir_CteCon"        =>  addslashes($data[7]),
                        "Cd_CteCon"         =>  addslashes($data[8]),
                        "NIT_CteCon"        =>  $data[9],
                        "Cod_CteCon"        =>  $data[10],
                        "CodB_CteEnv"       =>  $data[11],
                        "Nom_CteEnv"        =>  addslashes($data[12]),
                        "Dir_CteEnv"        =>  addslashes($data[13]),
                        "Cd_CteEnv"         =>  addslashes($data[14]),
                        "Tel_CteEnv"        =>  $data[15],
                        "Fec_Entrega"       =>  date("Y-m-d",strtotime($data[16])),
                        "Tot_Cajas"         =>  0,
                        "Tot_Pzs"           =>  $data[17],
                        "No_OrdComp"        =>  $data[3],
                        "Status"            =>  "A",
                        "Activo"            =>  1
                    ];
                    $last_key = key( array_slice( $insertData, -1, 1, TRUE ) );
                    $insertStm = "INSERT IGNORE INTO th_consolidado SET ";                    
                    foreach($insertData as $setName => $setValue){  
                        if(!empty($setValue)){
                            $insertStm .= " {$setName} = \"$setValue\",";
                        }
                    }
                    $insertStm .= ";";
                    if(substr($insertStm, -2) === ',;'){
                        $insertStm = substr($insertStm, 0, -2)."; ";
                    }
                    $insert .= $insertStm;
                }
                if($tipo === "td" && count($data) === 20){
                    $insertData = [
                        "Fol_PedidoCon"         =>  $data[13],
                        "No_OrdComp"            =>  $data[0],
                        "Fec_OrdCom"            =>  date("Y-m-d", strtotime($data[1])),
                        "Cve_Articulo"          =>  $data[2],
                        "Cant_Pedida"           =>  $data[8],
                        "Unid_Empaque"          =>  addslashes($data[10]),
                        "Tot_Cajas"             =>  $data[9],
                        "Status"                =>  "A",
                        "Fact_Madre"            =>  $data[13],
                        "Cve_Clte"              =>  $data[15],
                        "Cve_CteProv"           =>  $data[16],
                        "Fol_Folio"             =>  $data[17],
                        "CodB_Cte"              =>  $data[11],
                        "Cod_PV"                =>  $data[18]
                    ];
                    $last_key = key( array_slice( $insertData, -1, 1, TRUE ) );
                    $insertStm = "INSERT IGNORE INTO td_consolidado SET ";                    
                    foreach($insertData as $setName => $setValue){  
                        if(!empty($setValue)){
                            $insertStm .= " {$setName} = \"$setValue\",";
                        }
                    }
                    $insertStm .= ";";
                    if(substr($insertStm, -2) === ',;'){
                        $insertStm = substr($insertStm, 0, -2)."; ";
                    }
                    $insert .= $insertStm;
                }
            }
        }
    }

    fclose($reader);
    $query = mysqli_multi_query(\db2(), $insert);

    if($query){
        $app->render('page/importar/pedidos.php', array("message" => "Crossdocking importados exitosamente"));
    }
    else{
        $app->render('page/importar/pedidos.php', array("error" => "Ocurrio un error al importar Crossdocking".mysqli_error(\db2())));
    }

});
$app->post('/importar/stock', function() use ($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $sql = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $almacen = $cell[0];
        $zona = $cell[1];
        $ubicacion = $cell[2];
        $clave = $cell[3];
        $descripcion = $cell[4];
        $lote = $cell[5];
        $caducidad = $cell[6];
        $cantidad = $cell[8];
        $articulo = "SELECT cve_articulo FROM c_articulo WHERE CONCAT(cve_articulo, des_articulo, cve_codprov) like '%$clave%' LIMIT 1";

        if(empty($clave) || empty($cantidad) || empty($ubicacion)) {
            continue;
        }

        $sql .= "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia) VALUES ((SELECT id FROM c_almacenp WHERE nombre = '$almacen'), (SELECT idy_ubica FROM c_ubicacion WHERE CodigoCSD = '$ubicacion'), ({$articulo}), '$lote', '$cantidad'); ";

        if(!empty($lote) && !empty($caducidad)){
            $sql .= "INSERT INTO c_lotes(cve_articulo, LOTE, CADUCIDAD, Activo) VALUES (({$articulo}), '$lote', '$caducidad', 1) ON DUPLICATE KEY UPDATE CADUCIDAD = '$caducidad'; ";
        }
    }
    $query = mysqli_multi_query(\db2(), $sql);
    
    if($query){
        $app->render('page/importar/stock.php', array("message" => "Stock importado exitosamente"));
    }
    else{
        $app->render('page/importar/stock.php', array("error" => "Ocurrió un error al importar el stock <b>".mysqli_error(\db2())."</b>"));
    }
});