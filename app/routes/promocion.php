<?php

/**
 * pedidos
 **/

$app->map('/promocion', function() use ($app) {

    $app->render( 'page/promocion/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/promocion/lists', function() use ($app) {

    $app->render( 'page/promocion/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/promocion/pending', function() use ($app) {

    $app->render( 'page/promocion/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/promocion/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/promocion/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/promocion/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
