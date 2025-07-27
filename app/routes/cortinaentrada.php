<?php

/**
    * protocolos
  **/

$app->map('/cortinaentrada', function() use ($app) {

  $app->render( 'page/cortinaentrada/index.php' );

})->via( 'GET', 'POST' );



/*
* importar
*/

$app->post('/cortinaentrada/import', function() use($app){
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
      "tubicacionesretencion.cve_ubicacion"      =>  $cell[0],
      "tubicacionesretencion.cve_almacp"      =>  $cell[1],
      "tubicacionesretencion.desc_ubicacion"       =>  $cell[2]
    ];
    $last_key = key( array_slice( $data, -1, 1, TRUE ) );
    $insertStm = "INSERT IGNORE INTO tubicacionesretencion SET ";
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
    $app->response->redirect('/cortinaentrada/lists');
  }
});

/**
    * Lists
  **/

$app->map('/cortinaentrada/lists', function() use ($app) {

  $app->render( 'page/cortinaentrada/lists.php' );

})->via( 'GET', 'POST' );


/**
    * Pending
  **/

$app->map('/cortinaentrada/pending', function() use ($app) {

  $app->render( 'page/cortinaentrada/pending.php' );

})->via( 'GET', 'POST' );


/**
    * Edit Subscriber
  **/

$app->map('/cortinaentrada/edit/:id', function( $id_subscriber ) use ($app) {

  $app->render( 'page/subscribers/edit.php', array(
    'id_subscriber' => $id_subscriber
  ) );

})->via( 'GET', 'POST' );

/**
    * View Subscriber
  **/

$app->map('/cortinaentrada/view/:id', function( $id_subscriber ) use ($app) {

  $app->render( 'page/cortinaentrada/view.php', array(
    'id_subscriber' => $id_subscriber
  ) );

})->via( 'GET', 'POST' );
