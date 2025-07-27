<?php

/**
 * ubicacion
 **/

$app->map('/ubicaciones', function() use ($app) {

    $app->render( 'page/ubicaciones/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/ubicaciones/lists', function() use ($app) {

    $app->render( 'page/ubicaciones/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/ubicaciones/pending', function() use ($app) {

    $app->render( 'page/ubicaciones/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/ubicaciones/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/ubicaciones/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ubicaciones/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
