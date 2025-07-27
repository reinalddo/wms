<?php

  /**
    * protocolos
  **/

  $app->map('/roles', function() use ($app) {

    $app->render( 'page/roles/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/roles/lists', function() use ($app) {

    $app->render( 'page/roles/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/roles/pending', function() use ($app) {

    $app->render( 'page/roles/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/roles/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/roles/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/roles/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
