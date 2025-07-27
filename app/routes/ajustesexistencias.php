<?php

/**
 * ajustesexistencias
 **/

$app->map('/ajustesexistencias', function() use ($app) {

    $app->render( 'page/ajustesexistencias/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/ajustesexistencias/lists', function() use ($app) {

    $app->render( 'page/ajustesexistencias/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/ajustesexistencias/pending', function() use ($app) {

    $app->render( 'page/ajustesexistencias/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/ajustesexistencias/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/ajustesexistencias/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ajustesexistencias/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
