<?php

  /**
    * unidadesmedida
  **/

  $app->map('/unidadesmedida', function() use ($app) {

    $app->render( 'page/unidadesmedida/index.php' );

  })->via( 'GET', 'POST' );


/*
* importar
*/

$app->post('/unidadesmedida/import', function() use($app){
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
            "c_unimed.cve_umed"      =>  $cell[0],
            "c_unimed.des_umed"      =>  $cell[1],
            "c_unimed.mav_cveunimed"       =>  $cell[2],
            "c_unimed.imp_cosprom"        =>  $cell[3]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_unimed SET ";
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
        $app->response->redirect('/unidadesmedida/lists');
    }
});


  /**
    * Lists
  **/

  $app->map('/unidadesmedida/lists', function() use ($app) {

    $app->render( 'page/unidadesmedida/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/unidadesmedida/pending', function() use ($app) {

    $app->render( 'page/unidadesmedida/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/unidadesmedida/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/unidadesmedida/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/unidadesmedida/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
