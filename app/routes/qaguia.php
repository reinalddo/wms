<?php

/**
 * Lists
 **/

$app->map('/qaguia/lists', function() use ($app) {

    $app->render( 'page/qaguia/lists.php' );

})->via( 'GET', 'POST' );
