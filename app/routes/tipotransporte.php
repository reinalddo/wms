<?php

/**
    * protocolos
  **/

$app->map('/tipotransporte', function() use ($app) {

  $app->render( 'page/tipotransporte/index.php' );

})->via( 'GET', 'POST' );



/*
* importar
*/

$app->post('/tipotransporte/import', function() use($app){
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
      "tipo_transporte.clave_ttransporte"      =>  $cell[0],
      "tipo_transporte.alto"      =>  $cell[1],
      "tipo_transporte.fondo"       =>  $cell[2],
      "tipo_transporte.ancho"        =>  $cell[3],
      "tipo_transporte.capacidad_carga"        =>  $cell[4],
      "tipo_transporte.desc_ttransporte"        =>  $cell[5],
      "tipo_transporte.imagen"        =>  $cell[6]
    ];
    $last_key = key( array_slice( $data, -1, 1, TRUE ) );
    $insertStm = "INSERT IGNORE INTO tipo_transporte SET ";
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
    $app->response->redirect('/tipotransporte/lists');
  }
});


/**
    * Lists
  **/

$app->map('/tipotransporte/lists', function() use ($app) {

  $app->render( 'page/tipotransporte/lists.php' );

})->via( 'GET', 'POST' );


/**
    * Pending
  **/

$app->map('/tipotransporte/pending', function() use ($app) {

  $app->render( 'page/tipotransporte/pending.php' );

})->via( 'GET', 'POST' );


/**
    * Edit Subscriber
  **/

$app->map('/tipotransporte/edit/:id', function( $id_subscriber ) use ($app) {

  $app->render( 'page/subscribers/edit.php', array(
    'id_subscriber' => $id_subscriber
  ) );

})->via( 'GET', 'POST' );

/**
    * View Subscriber
  **/

$app->map('/tipotransporte/view/:id', function( $id_subscriber ) use ($app) {

  $app->render( 'page/tipotransporte/view.php', array(
    'id_subscriber' => $id_subscriber
  ) );

})->via( 'GET', 'POST' );
