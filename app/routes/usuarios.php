<?php

  /**
    * protocolos
  **/

  $app->map('/usuarios', function() use ($app) {

    $app->render( 'page/usuarios/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/usuarios/lists', function() use ($app) {

    $app->render( 'page/usuarios/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/usuarios/pending', function() use ($app) {

    $app->render( 'page/usuarios/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/usuarios/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/usuarios/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/usuarios/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
