<?php

  /**
    * tipodeprioridad
  **/

  $app->map('/pedidosurgentes', function() use ($app) {

    $app->render( 'page/pedidosurgentes/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/pedidosurgentes/lists', function() use ($app) {

    $app->render( 'page/pedidosurgentes/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/pedidosurgentes/pending', function() use ($app) {

    $app->render( 'page/pedidosurgentes/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/pedidosurgentes/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/pedidosurgentes/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/pedidosurgentes/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
