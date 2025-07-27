<?php

/**
 * clientes
 **/

$app->map('/recepcionoc', function() use ($app) {

    $app->render( 'page/recepcionoc/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/recepcionoc/lists', function() use ($app) {

    $app->render( 'page/recepcionoc/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/recepcionoc/pending', function() use ($app) {

    $app->render( 'page/recepcionoc/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/recepcionoc/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/recepcionoc/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/recepcionoc/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
