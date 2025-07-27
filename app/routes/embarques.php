<?php

  /**
    * protocolos
  **/

  $app->map('/embarques', function() use ($app) {

    $app->render( 'page/embarques/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/embarques/lists', function() use ($app) {

    $app->render( 'page/embarques/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/embarques/pending', function() use ($app) {

    $app->render( 'page/embarques/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/embarques/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/embarques/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/embarques/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );


 $app->get('/embarques/informe', function() use ($app) {

    $app->render( 'page/embarques/informe.php' );

  });

  $app->post('/embarques/informe', function() use ($app) {
    $folio = $_POST['folio'];
    $enlace = "<a href='{$_SERVER['REQUEST_URI']}'>Regresar</a>";
    echo sprintf("Sección en construcción su folio es %s. Será redireccionado en 5 seg. %s", $folio, $enlace);
  });