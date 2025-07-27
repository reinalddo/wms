<?php

/**
 * clientes
 **/

$app->map('/moveryacomodar', function() use ($app) {

    $app->render( 'page/moveryacomodar/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/moveryacomodar/lists', function() use ($app) {

    $app->render( 'page/moveryacomodar/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/moveryacomodar/pending', function() use ($app) {

    $app->render( 'page/moveryacomodar/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/moveryacomodar/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/moveryacomodar/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/moveryacomodar/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
