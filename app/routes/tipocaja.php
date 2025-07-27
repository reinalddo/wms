<?php

/**
 * tipocaja
 **/

$app->map('/tipocaja', function() use ($app) {

    $app->render( 'page/tipocaja/index.php' );

})->via( 'GET', 'POST' );


/*
* importar
*/

$app->post('/tipocaja/import', function() use($app){
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
            "c_tipocaja.clave"      =>  $cell[0],
            "c_tipocaja.descripcion"      =>  $cell[1],
            "c_tipocaja.largo"       =>  $cell[2],
            "c_tipocaja.alto"        =>  $cell[3],
            "c_tipocaja.ancho"        =>  $cell[4]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_tipocaja SET ";
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
        $app->response->redirect('/tipocaja/lists');
    }
});

/**
 * Lists
 **/

$app->map('/tipocaja/list', function() use ($app) {

    $app->render( 'page/tipocaja/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/tipocaja/pending', function() use ($app) {

    $app->render( 'page/tipocaja/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/tipocaja/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/tipocaja/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/tipocaja/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
