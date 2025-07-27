<?php

  /**
    * adminrutasurtido
  **/

  $app->map('/adminrutasurtido', function() use ($app) {

    $app->render( 'page/adminrutasurtido/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/adminrutasurtido/lists', function() use ($app) {

    $app->render( 'page/adminrutasurtido/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/adminrutasurtido/pending', function() use ($app) {

    $app->render( 'page/adminrutasurtido/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/adminrutasurtido/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/adminrutasurtido/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/adminrutasurtido/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
