<?php

/**
 * clientes
 **/

$app->map('/arearevision', function() use ($app) {

    $app->render( 'page/arearevision/index.php' );

})->via( 'GET', 'POST' );



/*
* importar
*/

$app->post('/arearevision/import', function() use($app){
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
            "t_ubicaciones_revision.cve_almac"      =>  $cell[0],
            "t_ubicaciones_revision.cve_ubicacion"      =>  $cell[1],
            "t_ubicaciones_revision.fol_folio"       =>  $cell[2],
            "t_ubicaciones_revision.sufijo"        =>  $cell[3],
            "t_ubicaciones_revision.Checado"        =>  $cell[4],
            "t_ubicaciones_revision.descripcion"        =>  $cell[5]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO t_ubicaciones_revision SET ";
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
        $app->response->redirect('/arearevision/lists');
    }
});

/**
 * Lists
 **/

$app->map('/arearevision/lists', function() use ($app) {

    $app->render( 'page/arearevision/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/arearevision/pending', function() use ($app) {

    $app->render( 'page/arearevision/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/arearevision/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/arearevision/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/arearevision/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
