<?php

/**
 * clientes
 **/

$app->map('/asignacionusuariosalmacen', function() use ($app) {

    $app->render( 'page/asignacionusuariosalmacen/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/asignacionusuariosalmacen/lists', function() use ($app) {

    $app->render( 'page/asignacionusuariosalmacen/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/asignacionusuariosalmacen/pending', function() use ($app) {

    $app->render( 'page/asignacionusuariosalmacen/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/asignacionusuariosalmacen/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/asignacionusuariosalmacen/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/asignacionusuariosalmacen/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );