<?php

/**
 * subgrupoarticulos
 **/

$app->map('/subgrupodearticulos', function() use ($app) {

    $app->render( 'page/subgrupodearticulos/index.php' );

})->via( 'GET', 'POST' );



/*
* importar
*/

$app->post('/subgrupodearticulos/import', function() use($app){
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
            "c_sgpoarticulo.cve_sgpoart"      =>  $cell[0],
            "c_sgpoarticulo.cve_gpoart"      =>  $cell[1],
            "c_sgpoarticulo.des_sgpoart"       =>  $cell[2]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_sgpoarticulo SET ";
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
        $app->response->redirect('/subgrupodearticulos/lists');
    }
});


/**
 * Lists
 **/

$app->map('/subgrupodearticulos/list', function() use ($app) {

    $app->render( 'page/subgrupodearticulos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/subgrupodearticulos/pending', function() use ($app) {

    $app->render( 'page/subgrupodearticulos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/subgrupodearticulos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/subgrupodearticulos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subgrupodearticulos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
