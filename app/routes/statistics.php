<?php

  /**
    * Statistics
  **/

  $app->get('/statistics', function() use ($app) {

    $app->render( 'page/statistics/index.php' );

  });
