<?php

$app->group('/qacuarentena', function() use ($app)
{
    $app->get('/lists', function() use ($app) {
        $app->render( 'page/qacuarentena/index.php' );
    });
});




/**
 * Edit Subscriber
 **/

$app->map('/qacuarentena/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/qacuarentena/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/qacuarentena/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
