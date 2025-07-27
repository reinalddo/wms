<?php

  /**
    * tipocompania
  **/

  $app->map('/tipocompania', function() use ($app) {

    $app->render( 'page/tipocompania/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/tipocompania/lists', function() use ($app) {

    $app->render( 'page/tipocompania/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/tipocompania/pending', function() use ($app) {

    $app->render( 'page/tipocompania/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/tipocompania/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/tipocompania/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/tipocompania/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
