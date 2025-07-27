<?php

/**
 * clientes
 **/

$app->map('/areaembarque', function() use ($app) {

    $app->render( 'page/areaembarque/index.php' );

})->via( 'GET', 'POST' );


/*
* importar
*/

$app->post('/areaembarque/import', function() use($app){
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
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "t_ubicacionembarque.cve_ubicacion"      =>  $cell[0],
            "t_ubicacionembarque.cve_almac"      =>  $cell[1],
            "t_ubicacionembarque.status"       =>  $cell[2],
            "t_ubicacionembarque.descripcion"        =>  $cell[3]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO t_ubicacionembarque SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                 if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/areaembarque/lists');
    }
});


/**
 * Lists
 **/

$app->map('/areaembarque/lists', function() use ($app) {

    $app->render( 'page/areaembarque/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/areaembarque/pending', function() use ($app) {

    $app->render( 'page/areaembarque/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/areaembarque/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/areaembarque/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/areaembarque/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
