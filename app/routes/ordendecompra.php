<?php

/**
 * clientes
 **/

$app->map('/ordendecompra', function() use ($app) {

    $app->render( 'page/ordendecompra/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/ordendecompra/lists', function() use ($app) {

    $app->render( 'page/ordendecompra/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/ordendecompra/pending', function() use ($app) {

    $app->render( 'page/ordendecompra/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/ordendecompra/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/ordendecompra/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ordendecompra/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
