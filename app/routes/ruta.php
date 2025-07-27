<?php

  /**
    * protocolos
  **/

  $app->map('/ruta', function() use ($app) {

    $app->render( 'page/ruta/index.php' );

  })->via( 'GET', 'POST' );


/*
* importar
*/

$app->post('/ruta/import', function() use($app){
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
            "t_ruta.cve_ruta"      =>  $cell[0],
            "t_ruta.descripcion"      =>  $cell[1],
            "t_ruta.status"       =>  $cell[2],
            "t_ruta.cve_almacenp"        =>  $cell[3]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO t_ruta SET ";
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
        $app->response->redirect('/ruta/lists');
    }
});


  /**
    * Lists
  **/

  $app->map('/ruta/lists', function() use ($app) {

    $app->render( 'page/ruta/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/ruta/pending', function() use ($app) {

    $app->render( 'page/ruta/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/ruta/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/ruta/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ruta/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
