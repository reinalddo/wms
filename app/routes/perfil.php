<?php

/**
 * clientes
 **/

$app->map('/perfil', function() use ($app) {

    $app->render( 'page/perfil/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/perfil/lists', function() use ($app) {

    $app->render( 'page/perfil/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/perfil/pending', function() use ($app) {

    $app->render( 'page/perfil/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/perfil/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/perfil/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/perfil/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );