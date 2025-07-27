<?php

/**
 * nuevospedidos
 **/

$app->map('/nuevospedidos', function() use ($app) {

    $app->render( 'page/nuevospedidos/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/nuevospedidos/lists', function() use ($app) {

    $app->render( 'page/nuevospedidos/lists.php' );

})->via( 'GET', 'POST' );
