<?php

  /**
    * rutassurtido
  **/

  $app->map('/rutasSurtido', function() use ($app) {

    $app->render( 'page/rutassurtido/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/rutasSurtido/lists', function() use ($app) {

    $app->render( 'page/rutassurtido/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/rutasSurtido/pending', function() use ($app) {

    $app->render( 'page/rutassurtido/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/rutasSurtido/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/rutasSurtido/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/rutassurtido/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
