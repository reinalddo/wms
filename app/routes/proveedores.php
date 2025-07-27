<?php

  /**
    * proveedores
  **/

  $app->map('/proveedores', function() use ($app) {

    $app->render( 'page/proveedores/index.php' );

  })->via( 'GET', 'POST' );



/*
* importar
*/

$app->post('/proveedores/import', function() use($app){
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
      //array_map("utf8_encode", $cell);
      $Empresa = $cell[0];
      $Nombre = addslashes($cell[1]);
      $RUT = $cell[2];
      $direccion = addslashes($cell[3]);
      $cve_dane = $cell[4];
      $ID_Externo = $cell[5];
      $cve_proveedor = $cell[6];
      $colonia = addslashes($cell[7]);
      $ciudad = addslashes($cell[8]);
      $estado = addslashes($cell[9]);
      $pais = addslashes($cell[10]);
      $telefono1 = $cell[11];
      $telefono2 = $cell[12];

      $insert .= "INSERT IGNORE INTO c_proveedores (Empresa, Nombre, RUT, direccion, cve_dane, ID_Externo, cve_proveedor, colonia, ciudad, estado, pais, telefono1, telefono2) VALUES ('$Empresa', '$Nombre', '$RUT', '$direccion', '$cve_dane', '$ID_Externo', '$cve_proveedor', '$colonia', '$ciudad', '$estado', '$pais', '$telefono1', '$telefono2');\n";
    }
    mysqli_set_charset(\db2(), 'utf8');
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/proveedores/lists');
    }
});



  /**
    * Lists
  **/

  $app->map('/proveedores/lists', function() use ($app) {

    $app->render( 'page/proveedores/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/proveedores/pending', function() use ($app) {

    $app->render( 'page/proveedores/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/proveedores/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/proveedores/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/proveedores/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
