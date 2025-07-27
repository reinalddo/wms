<?php

  /**
    * protocolos
  **/

  $app->map('/inventariosfisicos', function() use ($app) {

    $app->render( 'page/inventariosfisicos/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/inventariosfisicos/lists', function() use ($app) {

    $app->render( 'page/inventariosfisicos/lists.php' );

  })->via( 'GET', 'POST' );


/**
    * Lists
  **/

  $app->map('/inventariosfisicos/admin', function() use ($app) {

    $app->render( 'page/inventariosfisicos/admin.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/inventariosfisicos/pending', function() use ($app) {

    $app->render( 'page/inventariosfisicos/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/inventariosfisicos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/inventariosfisicos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/inventariosfisicos/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
