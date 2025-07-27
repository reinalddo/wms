<?php

/**
 * pedidos
 **/

$app->map('/companias', function() use ($app) {

    $app->render( 'page/companias/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/companias/lists', function() use ($app) {

    $app->render( 'page/companias/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/companias/pending', function() use ($app) {

    $app->render( 'page/companias/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/companias/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/companias/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/companias/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
