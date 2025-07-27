<?php

  /**
    * destinatarios
  **/

  $app->map('/destinatarios', function() use ($app) {

    $app->render( 'page/destinatarios/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/destinatarios/lists', function() use ($app) {

    $app->render( 'page/destinatarios/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/destinatarios/pending', function() use ($app) {

    $app->render( 'page/destinatarios/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/destinatarios/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/destinatarios/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/destinatarios/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
