<?php 

$app->map('/administracionubicaciones', function() use ($app) {
    $app->render( 'page/administracionubicaciones/lists.php' );
})->via( 'GET', 'POST' );

$app->map('/administracionrecepcion', function() use ($app) {
    $app->render( 'page/administracionrecepcion/lists.php' );
})->via( 'GET', 'POST' );

$app->map('/administracionembarque', function() use ($app) {
    $app->render( 'page/administracionembarque/lists.php' );
})->via( 'GET', 'POST' );

