<?php

  /**
    * protocolos
  **/

  $app->map('/contenedores', function() use ($app) {

    $app->render( 'page/contenedores/index.php' );

  })->via( 'GET', 'POST' );



/*
* importar
*/

$app->post('/contenedores/import', function() use($app){
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
            "c_charolas.cve_almac"      =>  $cell[0],
            "c_charolas.charola"      =>  $cell[1],
            "c_charolas.Pedido"       =>  $cell[2],
            "c_charolas.sufijo"        =>  $cell[3],
          "c_charolas.tipo"        =>  $cell[4],
          "c_charolas.alto"        =>  $cell[5],
          "c_charolas.ancho"        =>  $cell[6],
          "c_charolas.fondo"        =>  $cell[7],
          "c_charolas.peso"        =>  $cell[8],
          "c_charolas.clave_contenedor"        =>  $cell[9],
          "c_charolas.pesomax"        =>  $cell[10],
          "c_charolas.capavol"        =>  $cell[11],
          "c_charolas.descripcion"        =>  $cell[12]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_charolas SET ";
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
        $app->response->redirect('/contenedores/lists');
    }
});

  /**
    * Lists
  **/

  $app->map('/contenedores/lists', function() use ($app) {

    $app->render( 'page/contenedores/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/contenedores/pending', function() use ($app) {

    $app->render( 'page/contenedores/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/contenedores/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/contenedores/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/contenedores/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
