<?php

  /**
    * protocolos
  **/

  $app->map('/almacenp', function() use ($app) {

    $app->render( 'page/almacenp/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/almacenp/lists', function() use ($app) {

    $app->render( 'page/almacenp/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/almacenp/pending', function() use ($app) {

    $app->render( 'page/almacenp/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/almacenp/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/almacenp/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/almacenp/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
