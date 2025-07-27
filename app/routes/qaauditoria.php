<?php

/**
 * clientes
 **/

$app->map('/qaauditoria', function() use ($app) {

    $app->render( 'page/qaauditoria/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/qaauditoria/lists', function() use ($app) {

    $app->render( 'page/qaauditoria/lists.php' );

})->via( 'GET', 'POST' );




$app->map('/qaauditoria/admin', function() use ($app) {

    $app->render( 'page/qaauditoria/admin.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/qaauditoria/pending', function() use ($app) {

    $app->render( 'page/qaauditoria/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/qaauditoria/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/qaauditoria/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/qaauditoria/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
