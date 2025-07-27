<?php

/**
 * clientes
 **/

$app->map('/ajustedeentradas', function() use ($app) {

    $app->render( 'page/ajustedeentradas/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/ajustedeentradas/lists', function() use ($app) {

    $app->render( 'page/ajustedeentradas/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/ajustedeentradas/pending', function() use ($app) {

    $app->render( 'page/ajustedeentradas/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/ajustedeentradas/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/ajustedeentradas/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ajustedeentradas/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
