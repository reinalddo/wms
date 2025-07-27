<?php

  /**
    * Log out
  **/

  $app->get('/account/out', function() use ($app) {
    $id = $_SESSION['id_user'];
    $sql = 'DELETE FROM `users_online` WHERE id_usuario = '.$id.';';
    $data = mysqli_query(\db2(), $sql);
    session_destroy();
    $app->redirect( '/login' );

  });


  /**
    * Update data settings
  **/

  $app->map('/settings', function() use ($app) {

    $app->render( 'page/settings/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Update design settings
  **/

  $app->map('/settings/design', function() use ($app) {

    $app->render( 'page/settings/design.php' );

  })->via( 'GET', 'POST' );


  /**
    * Payments
  **/

  $app->map('/settings/payments', function() use ($app) {

    $app->render( 'page/settings/payment.php' );

  })->via( 'GET', 'POST' );

