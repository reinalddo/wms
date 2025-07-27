<?php

/**
 * pedidos
 **/

$app->map('/maximosyminimos', function() use ($app) {

    $app->render( 'page/maximosyminimos/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/maximosyminimos/lists', function() use ($app) {

    $app->render( 'page/maximosyminimos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/maximosyminimos/pending', function() use ($app) {

    $app->render( 'page/maximosyminimos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/maximosyminimos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/maximosyminimos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/maximosyminimos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
