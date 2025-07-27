<?php

/**
 * clientes
 **/

$app->map('/inventariosciclicos', function() use ($app) {

    $app->render( 'page/inventariosciclicos/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/inventariosciclicos/lists', function() use ($app) {

    $app->render( 'page/inventariosciclicos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/inventariosciclicos/pending', function() use ($app) {

    $app->render( 'page/inventariosciclicos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/inventariosciclicos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/inventariosciclicos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/inventariosciclicos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
