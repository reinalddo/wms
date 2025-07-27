<?php

  /**
    * protocolos
  **/

  $app->map('/adminembarques', function() use ($app) {

    $app->render( 'page/adminembarques/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/adminembarques/lists', function() use ($app) {

    $app->render( 'page/adminembarques/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/adminembarques/pending', function() use ($app) {

    $app->render( 'page/adminembarques/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/adminembarques/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/adminembarques/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/adminembarques/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
