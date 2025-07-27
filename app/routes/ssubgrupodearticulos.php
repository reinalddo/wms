<?php

/**
 * subgrupoarticulos
 **/

$app->map('/ssubgrupodearticulos', function() use ($app) {

    $app->render( 'page/ssubgrupodearticulos/index.php' );

})->via( 'GET', 'POST' );




/*
* importar
*/

$app->post('/ssubgrupodearticulos/import', function() use($app){
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
            "c_ssgpoarticulo.cve_ssgpoart"      =>  $cell[0],
            "c_ssgpoarticulo.cve_sgpoart"      =>  $cell[1],
            "c_ssgpoarticulo.des_ssgpoart"       =>  $cell[2]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_ssgpoarticulo SET ";
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
        $app->response->redirect('/ssubgrupodearticulos/lists');
    }
});

/**
 * Lists
 **/

$app->map('/ssubgrupodearticulos/list', function() use ($app) {

    $app->render( 'page/ssubgrupodearticulos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/ssubgrupodearticulos/pending', function() use ($app) {

    $app->render( 'page/ssubgrupodearticulos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/ssubgrupodearticulos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/ssubgrupodearticulos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ssubgrupodearticulos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
