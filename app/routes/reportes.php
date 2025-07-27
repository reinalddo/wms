<?php

/**
 * protocolos
 **/

$app->map('/reportes', function() use ($app) {

    $app->render( 'page/reportes/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/reportes/ocupacionalmacen', function() use ($app) {

    $app->render( 'page/reportes/ocupacionAlmacen.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/qa', function() use ($app) {

    $app->render( 'page/reportes/qa.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/asn', function() use ($app) {

    $app->render( 'page/reportes/asn.php' );

})->via( 'GET', 'POST' );



$app->map('/reportes/concentradoexistencia', function() use ($app) {

    $app->render( 'page/reportes/concentradoexistencia.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/ajustesexistencia', function() use ($app) {

    $app->render( 'page/reportes/ajustesexistencia.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/entrada', function() use ($app) {

    $app->render( 'page/reportes/entrada.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/comprobantei', function() use ($app) {

    $app->render( 'page/reportes/comprobantei.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/invfidet', function() use ($app) {

    $app->render( 'page/reportes/invfidet.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/invfidetconc', function() use ($app) {

    $app->render( 'page/reportes/invfidetconc.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/difconteos', function() use ($app) {

    $app->render( 'page/reportes/difconteos.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/invcicl', function() use ($app) {

    $app->render( 'page/reportes/invcicl.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/invcicldet', function() use ($app) {

    $app->render( 'page/reportes/invcicldet.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/maxmin', function() use ($app) {

    $app->render( 'page/reportes/maxmin.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/guiasembarque', function() use ($app) {

    $app->render( 'page/reportes/guiasembarque.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/ubicaciones', function() use ($app) {

    $app->render( 'page/reportes/ubicaciones.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/productos', function() use ($app) {

    $app->render( 'page/reportes/productos.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/lotesPorVencer', function() use ($app) {

    $app->render( 'page/reportes/lotesPorVencer.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/lotesVencidos', function() use ($app) {

    $app->render( 'page/reportes/lotesVencidos.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/existenciaubica', function() use ($app) {

    $app->render( 'page/reportes/existenciaubica.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/existenciaszonas', function() use ($app) {

    $app->render( 'page/reportes/existenciaszonas.php' );

})->via( 'GET', 'POST' );

/**
 * Pending
 **/

$app->map('/reportes/pending', function() use ($app) {

    $app->render( 'page/reportes/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/reportes/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/reportes/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/reportes/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );


$app->get('/reportes/pendienteacomodo', function() use ($app) {
    $app->render( 'page/reportes/pendienteacomodo.php');
});

$app->get('/reportes/pdf/pendienteacomodo', function() use ($app) {
    $pdf = new \ReportePDF\PDF($_GET['cia'], 'Reporte Pendiente de Acomodo', 'L');
    $data = new \Reportes\PendienteAcomodo();
    $content = $data->obtenerTodos();
    $pdf->setContent($content);
    $pdf->stream();
    exit;
});

$app->post('/reportes/pdf/etiquetas', function() use ($app) {
    $folio = $_POST['ordenp'];
    $sql = "UPDATE t_ordenprod SET Status = 'T', Hora_Fin = NOW() WHERE Folio_Pro = '$folio'";
    $query = mysqli_query(\db2(), $sql);

    $pdf = new \ReportePDF\Etiquetas();

    $data = array(
        "articulo"      => $_POST['des_articulo'],
        "clave"         => $_POST['cve_articulo'],
        "lote"          => $_POST['lote'],
        "ordenp"        => $_POST['ordenp'],
        "cantidad"      => $_POST['unidades_caja'],
        "etiquetas"     => $_POST['numero_impresiones'],
        "barras_art"    => $_POST['barras2'],
        "barras_caja"   => $_POST['barras3']
    );

    if($_POST['etiqueta'] === 'caja'){
        $pdf->generarCodigoCaja($data);
    }
    if($_POST['etiqueta'] === 'articulo'){
        $pdf->generarCodigoArticulo($data);
    }
    else{
        echo 'Etiqueta no dispnible';
    }
    exit;
});

$app->post('/reportes/pdf/remision', function() use ($app) {
    $folio = $_POST['folio'];
    $pdf = new \ReportePDF\Remision($folio);
    $pdf->generarHojaSurtido();
    exit;
});