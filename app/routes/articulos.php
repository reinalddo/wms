<?php

/**
 * clientes
 **/

$app->map('/articulos', function() use ($app) {

    $app->render( 'page/articulos/index.php' );

})->via( 'GET', 'POST' );

/*
* importar
*/

$app->post('/articulos/import', function() use($app){
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
            "c_articulo.cve_articulo"      =>  $cell[0],
            "c_articulo.des_articulo"      =>  $cell[1],
            "c_articulo.cve_umed"      =>  $cell[2],
            "c_articulo.cve_ssgpo"      =>  ($cell[3] == "NULL") ? 0 : $cell[3],
            "c_articulo.fec_altaart"       =>  $cell[4],
            "c_articulo.imp_costo"      =>  ($cell[5] == "NULL") ? 0 : $cell[5],
            "c_articulo.des_tipo"      =>  $cell[6],
            "c_articulo.comp_cveumed"      =>  ($cell[7] == "NULL") ? 0 : $cell[7],
            "c_articulo.empq_cveumed"      =>  ($cell[8] == "NULL") ? 0 : $cell[8],
            "c_articulo.num_multiplo"      =>  $cell[9],
            "c_articulo.des_observ"        =>  $cell[10],
            "c_articulo.mav_almacenable"        =>  $cell[11],
            "c_articulo.cve_moneda"        =>  ($cell[12] == "NULL") ? 0 : $cell[12],
            "c_articulo.cve_almac"        =>  $cell[13],
            "c_articulo.mav_cveubica"        =>  $cell[14],
            "c_articulo.mav_delinea"        =>  $cell[15],
            "c_articulo.mav_obsoleto"        =>  $cell[16],
            "c_articulo.mav_pctiva"        =>  ($cell[17] == "NULL") ? 0 : $cell[17],
            "c_articulo.PrecioVenta"        => ($cell[18] == "NULL") ? 0 : $cell[18],
            "c_articulo.cve_tipcaja"        =>  ($cell[19] == "NULL") ? 0 : $cell[19],
            "c_articulo.ban_condic"        =>  ($cell[20] == "NULL") ? 0 : $cell[20],
            "c_articulo.num_volxpal"        =>  ($cell[21] == "NULL") ? 0 : $cell[21],
            "c_articulo.cve_codprov"        =>  $cell[22],
            "c_articulo.remplazo"        =>  $cell[23],
            "c_articulo.ID_Proveedor"        =>  $cell[24],
            "c_articulo.peso"              =>  (strstr($cell[25], ",")) ? str_replace(",", ".", $cell[25]) : $cell[25],
            "c_articulo.num_multiploch"    =>  $cell[26],
            "c_articulo.barras2"      =>  $cell[27],
            "c_articulo.Caduca"            =>  $cell[28],
            "c_articulo.Compuesto"            =>  $cell[29],
            "c_articulo.Max_Cajas"            =>  ($cell[30] == "NULL") ? 0 : $cell[30],
            "c_articulo.barras3"            =>  $cell[31],
            "c_articulo.cajas_palet"       =>  $cell[32],
            "c_articulo.control_lotes"       =>  $cell[33],
            "c_articulo.control_numero_series"           =>  $cell[34],
            "c_articulo.control_peso"         =>  $cell[35],
            "c_articulo.control_volumen"         =>  $cell[36],
            "c_articulo.req_refrigeracion"         =>  $cell[37],
            "c_articulo.mat_peligroso"         =>  $cell[38],
            "c_articulo.grupo"         =>  $cell[39],
            "c_articulo.clasificacion"         =>  $cell[40],
            "c_articulo.tipo"         =>  $cell[41],
            "c_articulo.tipo_caja"         =>  $cell[42],
            "c_articulo.alto"         =>  $cell[43],
            "c_articulo.fondo"         =>  $cell[44],
            "c_articulo.ancho"         =>  $cell[45]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_articulo SET ";
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
        $app->response->redirect('/articulos/lists');
    }
});
/**
 * Lists
 **/

$app->map('/articulos/lists', function() use ($app) {

    $app->render( 'page/articulos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/articulos/pending', function() use ($app) {

    $app->render( 'page/articulos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/articulos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/articulos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/articulos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

$app->get('/articulosrutas/lists', function() use ($app) {

    $app->render( 'page/articulos/articulosrutas.php');

});