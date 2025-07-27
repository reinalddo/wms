<?php

  /**
    * protocolos
  **/

 $app->map('/kardex', function() use ($app) {

    $app->render( 'page/kardex/index.php' );

})->via( 'GET', 'POST' );

