<?php

/**
 * pedidos
 **/

$app->map('/manufactura', function() use ($app) {

    $app->render( 'page/manufactura/index.php' );

})->via( 'GET', 'POST' );

$app->map('/manufactura/editarcomponentes', function() use ($app) {

    $app->render( 'page/manufactura/editarcomponentes.php' );

})->via( 'GET', 'POST' );


$app->map('/manufactura/ordentrabajo', function() use ($app) {

    $app->render( 'page/ordentrabajo/agregarorden.php' );

})->via( 'GET', 'POST' );

$app->map('/manufactura/administracion', function() use ($app) {

    $app->render( 'page/adminordentrabajo/list.php' );

})->via( 'GET', 'POST' );

/**
 * Edit Subscriber
 **/

$app->map('/manufactura/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/manufactura/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/manufactura/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
