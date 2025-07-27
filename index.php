<?php

if (!defined('DS')) define( 'DS', DIRECTORY_SEPARATOR );
if (!defined('PATH_ROOT')) define( 'PATH_ROOT', realpath($_SERVER['DOCUMENT_ROOT']) . DS );
if (!defined('PATH_FRAMEWORK')) define( 'PATH_FRAMEWORK', PATH_ROOT . 'Framework'.DS );
if (!defined('PATH_APP')) define( 'PATH_APP', PATH_ROOT .'Application'.DS );


try {
  include 'Framework/app.php';
} catch (Exception $e) {
  echo 'Excepción capturada: ',  $e->getMessage(), "\n";
}


require_once 'app/load.php';

// Initalize Slim
$app = new \Slim\Slim([
  'templates.path' => 'app/template'
]);


require_once 'app/main.php';
// Run Slim
$app->run();
