<?php

/**
    * protocolos
  **/

$app->map('/almacen', function() use ($app) {

  $app->render( 'page/almacen/index.php' );

})->via( 'GET', 'POST' );



/*
* importar
*/

$app->post('/almacen/import', function() use($app){
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
    if($cell[3]!=''){
      $data = [
        "c_almacen.clave_almacen"      =>  $cell[0],
        "c_almacen.cve_almacenp"      =>  $cell[1],
        "c_almacen.des_almac"       =>  $cell[2],
        "c_almacen.des_direcc"        =>  $cell[3]
      ];
    }
    else
    {
      $data = [
        "c_almacen.clave_almacen"      =>  $cell[0],
        "c_almacen.cve_almacenp"      =>  $cell[1],
        "c_almacen.des_almac"       =>  $cell[2]
      ];
    }
    $last_key = key( array_slice( $data, -1, 1, TRUE ) );
    $insertStm = "INSERT IGNORE INTO c_almacen SET ";
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
    $app->response->redirect('/almacen/lists');
  }

});


/**
    * Lists
  **/

$app->map('/almacen/lists', function() use ($app) {

  $app->render( 'page/almacen/lists.php' );

})->via( 'GET', 'POST' );


/**
    * Pending
  **/

$app->map('/almacen/pending', function() use ($app) {

  $app->render( 'page/almacen/pending.php' );

})->via( 'GET', 'POST' );


/**
    * Edit Subscriber
  **/

$app->map('/almacen/edit/:id', function( $id_subscriber ) use ($app) {

  $app->render( 'page/subscribers/edit.php', array(
    'id_subscriber' => $id_subscriber
  ) );

})->via( 'GET', 'POST' );

/**
    * View Subscriber
  **/

$app->map('/almacen/view/:id', function( $id_subscriber ) use ($app) {

  $app->render( 'page/almacen/view.php', array(
    'id_subscriber' => $id_subscriber
  ) );

})->via( 'GET', 'POST' );
