<?php

$app->map('/dashboard/productos-en-piso', function() use ($app) {
    $app->render( 'page/index.productos_en_piso.php' );
})->via( 'GET', 'POST' );
