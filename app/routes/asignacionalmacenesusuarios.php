<?php

/**
 * clientes
 **/

$app->map('/asignacionalmacenesusuarios', function() use ($app) {

    $app->render( 'page/asignacionalmacenesusuarios/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/asignacionalmacenesusuarios/lists', function() use ($app) {

    $app->render( 'page/asignacionalmacenesusuarios/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/asignacionalmacenesusuarios/pending', function() use ($app) {

    $app->render( 'page/asignacionalmacenesusuarios/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/asignacionalmacenesusuarios/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/asignacionalmacenesusuarios/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/asignacionalmacenesusuarios/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );