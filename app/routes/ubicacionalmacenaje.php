<?php

/**
 * ubicacion
 **/

$app->map('/ubicacionalmacenaje', function() use ($app) {

    $app->render( 'page/ubicacionalmacenaje/index.php' );

})->via( 'GET', 'POST' );


/*
* importar
*/

$app->post('/ubicacionalmacenaje/import', function() use($app){
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
            "c_ubicacion.cve_almac"      =>  $cell[0],
            "c_ubicacion.cve_pasillo"      =>  $cell[1],
            "c_ubicacion.cve_rack"       =>  $cell[2],
            "c_ubicacion.cve_nivel"        =>  $cell[3],
            "c_ubicacion.num_ancho"        =>  $cell[4],
            "c_ubicacion.num_largo"        =>  $cell[5],
            "c_ubicacion.num_alto"        =>  $cell[6],
            "c_ubicacion.num_volumenDisp"        =>  $cell[7],
            "c_ubicacion.Status"        =>  $cell[8],
            "c_ubicacion.picking"        =>  $cell[9],
            "c_ubicacion.Seccion"        =>  $cell[10],
            "c_ubicacion.Ubicacion"        =>  $cell[11],
            "c_ubicacion.orden_secuencia"        =>  $cell[12],
            "c_ubicacion.PesoMaximo"        =>  $cell[13],
            "c_ubicacion.PesoOcupado"        =>  $cell[14],
            "c_ubicacion.claverp"        =>  $cell[15],
            "c_ubicacion.CodigoCSD"        =>  $cell[16],
            "c_ubicacion.TECNOLOGIA"        =>  $cell[17],
            "c_ubicacion.Maneja_Cajas"        =>  $cell[18],
            "c_ubicacion.Maneja_Piezas"        =>  $cell[19],
            "c_ubicacion.Reabasto"        =>  $cell[20],
            "c_ubicacion.Tipo"        =>  $cell[21]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_ubicacion SET ";
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
        $app->response->redirect('/ubicacionalmacenaje/lists');
    }
  
});


/**
 * Lists
 **/

$app->map('/ubicacionalmacenaje/lists', function() use ($app) {

    $app->render( 'page/ubicacionalmacenaje/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/ubicacionalmacenaje/pending', function() use ($app) {

    $app->render( 'page/ubicacionalmacenaje/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/ubicacionalmacenaje/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/ubicacionalmacenaje/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ubicacionalmacenaje/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
