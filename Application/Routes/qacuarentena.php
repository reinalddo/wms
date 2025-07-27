<?php


$route->group('/qa-cuarentena', function () use ($route)
{
    
    $route->get('/', '\Application\Controllers\AlmacenPController:all');

});

