<?php

/**
 * clientes
 **/

$app->map('/adminentrada', function() use ($app) {

    $app->render( 'page/adminentrada/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/adminentrada/lists', function() use ($app) {

    $app->render( 'page/adminentrada/lists.php' );

})->via( 'GET', 'POST' );


$app->map('/adminentrada/report', function() use ($app) {

    $app->render( 'page/adminentrada/report.php' );

})->via( 'GET', 'POST' );
/**
 * Pending
 **/

$app->map('/adminentrada/pending', function() use ($app) {

    $app->render( 'page/adminentrada/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/adminentrada/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/adminentrada/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/adminentrada/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
