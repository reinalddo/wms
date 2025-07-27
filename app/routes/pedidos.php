<?php

/**
 * pedidos
 **/

$app->map('/pedidos', function() use ($app) {

    $app->render( 'page/pedidos/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/pedidos/lists', function() use ($app) {

    $app->render( 'page/pedidos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/pedidos/pending', function() use ($app) {

    $app->render( 'page/pedidos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/pedidos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/pedidos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/pedidos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
