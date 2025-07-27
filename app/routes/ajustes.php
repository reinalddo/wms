<?php 

$app->map('/ajustes/codigocsd', function() use ($app) {

    $app->render( 'page/ajustes/codigocsd.php' );

})->via( 'GET');
