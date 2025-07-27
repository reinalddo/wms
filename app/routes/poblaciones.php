<?php

  /**
    * tipodeprioridad
  **/

  $app->map('/poblaciones', function() use ($app) {

    $app->render( 'page/poblaciones/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/poblaciones/lists', function() use ($app) {

    $app->render( 'page/poblaciones/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/poblaciones/pending', function() use ($app) {

    $app->render( 'page/poblaciones/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/poblaciones/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/poblaciones/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/poblaciones/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
