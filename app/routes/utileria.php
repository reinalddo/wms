<?php 

$app->map('/utileria/mensajes', function() use ($app) {

  $app->render( 'page/utileria/mensajes.php' );

})->via( 'GET', 'POST' );
