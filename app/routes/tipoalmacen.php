<?php

  /**
    * tipoalmacen
  **/

  $app->map('/tipoalmacen', function() use ($app) {

    $app->render( 'page/tipoalmacen/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/tipoalmacen/lists', function() use ($app) {

    $app->render( 'page/tipoalmacen/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/tipoalmacen/pending', function() use ($app) {

    $app->render( 'page/tipoalmacen/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/tipoalmacen/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/tipoalmacen/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/tipoalmacen/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
