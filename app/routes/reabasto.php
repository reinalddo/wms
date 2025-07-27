<?php

/**
 * protocolos
 **/

$app->map('/reabasto/picking', function() use ($app) {

    $app->render( 'page/reabasto/picking.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/reabasto/ptl', function() use ($app) {

    $app->render( 'page/reabasto/ptl.php' );

})->via( 'GET', 'POST' );

