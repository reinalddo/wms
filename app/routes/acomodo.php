<?php

/**
 * clientes
 **/

$app->map('/acomodo', function() use ($app) {

    $app->render( 'page/acomodo/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/acomodo/lists', function() use ($app) {

    $app->render( 'page/acomodo/lists.php' );

})->via( 'GET', 'POST' );


$app->map('/acomodo/report', function() use ($app) {

    $app->render( 'page/acomodo/report.php' );

})->via( 'GET', 'POST' );
/**
 * Pending
 **/

$app->map('/acomodo/pending', function() use ($app) {

    $app->render( 'page/acomodo/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/acomodo/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/acomodo/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/acomodo/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
