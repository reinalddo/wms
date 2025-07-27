<?php

  /**
    * Subscribers
  **/

  $app->map('/subscribers', function() use ($app) {

    $app->render( 'page/subscribers/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/subscribers/lists', function() use ($app) {

    $app->render( 'page/subscribers/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/subscribers/pending', function() use ($app) {

    $app->render( 'page/subscribers/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/subscribers/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/subscribers/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
