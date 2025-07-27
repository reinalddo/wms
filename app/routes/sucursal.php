<?php

/**
 * pedidos
 **/

$app->map('/sucursal', function() use ($app) {

    $app->render( 'page/sucursal/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/sucursal/lists', function() use ($app) {

    $app->render( 'page/sucursal/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/sucursal/pending', function() use ($app) {

    $app->render( 'page/sucursal/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/sucursal/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/sucursal/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/sucursal/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
