<?php

/**
 * clientes
 **/

$app->map('/clientes', function() use ($app) {

    $app->render( 'page/clientes/index.php' );

})->via( 'GET', 'POST' );

/*
 * importar
*/

$app->post('/clientes/import', function() use($app){
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
            "c_cliente.Cve_Clte"          =>  $cell[0],
            "c_cliente.RazonSocial"       =>  $cell[1],
            "c_cliente.RazonComercial"    =>  $cell[2],
            "c_cliente.CalleNumero"       =>  $cell[3],
            "c_cliente.Colonia"           =>  $cell[4],
            "c_cliente.Ciudad"            =>  $cell[5],
            "c_cliente.Estado"            =>  $cell[6],
            "c_cliente.Pais"              =>  $cell[7],
            "c_cliente.CodigoPostal"      =>  $cell[8],
            "c_cliente.RFC"               =>  $cell[9],
            "c_cliente.Telefono1"         =>  $cell[10],
            "c_cliente.Telefono2"         =>  $cell[11],
            "c_cliente.Telefono3"         =>  $cell[12],
            "c_cliente.ClienteTipo"       =>  $cell[13],
            "c_cliente.ClienteGrupo"       =>  $cell[14],
            "c_cliente.ClienteFamilia"       =>  $cell[15],
            "c_cliente.CondicionPago"     =>  $cell[16],
            "c_cliente.MedioEmbarque"     =>  $cell[17],
            "c_cliente.ViaEmbarque"     =>  $cell[18],
            "c_cliente.CondicionEmbarque"     =>  $cell[19],
            "c_cliente.cve_ruta"     =>  $cell[20],
            "c_cliente.ID_Proveedor"      =>  $cell[21],
            "c_cliente.Cve_CteProv"      =>  $cell[22],
            "c_cliente.Cve_Almacenp"      =>  $cell[23]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_cliente SET ";
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
        $app->response->redirect('/clientes/lists');
    }
});

/**
 * Lists
 **/

$app->map('/clientes/lists', function() use ($app) {

    $app->render( 'page/clientes/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/clientes/pending', function() use ($app) {

    $app->render( 'page/clientes/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/clientes/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/clientes/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/clientes/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
