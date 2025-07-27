<?php

/**
 * Lists
 **/

$app->map('/zonahoraria', function() use ($app) {

    $app->render( 'page/zonahoraria/lists.php' );

})->via( 'GET', 'POST' );

