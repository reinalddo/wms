<?php

/**
 * adminpedidos
 **/

$app->map('/adminpedidos', function() use ($app) {

    $app->render( 'page/adminpedidos/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/adminpedidos/lists', function() use ($app) {

    $app->render( 'page/adminpedidos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/adminpedidos/pending', function() use ($app) {

    $app->render( 'page/adminpedidos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/adminpedidos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/adminpedidos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/adminpedidos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
