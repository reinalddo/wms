<?php

  /**
    * Press Release
  **/

  $app->map('/mail/press', function() use ($app) {

    $app->render( 'page/mail/press.php' );

  })->via( 'GET', 'POST' );

  /**
    * Edit Press Release
  **/

  $app->map('/mail/press/:id_template', function( $id_template ) use ($app) {

    $app->render( 'page/mail/edit.php', array(
      'id_template' => $id_template
    ) );

  })->via( 'GET', 'POST' );


  /**
    * Edit Press Release
  **/

  $app->map('/mail/invitations/:id_template', function( $id_template ) use ($app) {

    $app->render( 'page/mail/view.php', array(
      'id_template' => $id_template
    ) );

  })->via( 'GET', 'POST' );


  /**
    * Upload file
  **/

  $app->map('/mail/upload', function() use ($app) {

    $app->render( 'page/mail/upload.php' );

  })->via( 'GET', 'POST' );


  /**
    * Invitations
  **/

  $app->map('/mail/invitations', function() use ($app) {

    $app->render( 'page/mail/invitations.php' );

  })->via( 'GET', 'POST' );
