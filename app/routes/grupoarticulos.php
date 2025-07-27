<?php

/**
 * grupoarticulos
 **/

$app->map('/grupoarticulos', function() use ($app) {

    $app->render( 'page/grupoarticulos/index.php' );

})->via( 'GET', 'POST' );



/*
* importar
*/

$app->post('/grupoarticulos/import', function() use($app){
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
            "c_gpoarticulo.cve_gpoart"      =>  $cell[0],
            "c_gpoarticulo.des_gpoart"      =>  $cell[1],
            "c_gpoarticulo.por_depcont"       =>  $cell[2],
            "c_gpoarticulo.por_depfical"        =>  $cell[3]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_gpoarticulo SET ";
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
        $app->response->redirect('/grupoarticulos/lists');
    }
});


/**
 * Lists
 **/

$app->map('/grupoarticulos/list', function() use ($app) {

    $app->render( 'page/grupoarticulos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/grupoarticulos/pending', function() use ($app) {

    $app->render( 'page/grupoarticulos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/grupoarticulos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/grupoarticulos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/grupoarticulos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
