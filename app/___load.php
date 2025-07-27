<?php

  session_start();

  /**
    * Required files
  **/

  include dirname( __DIR__ ) . '/config.php';
  include 'vendor/autoload.php';
  include 'autoload.php';
  include 'db.php';

  date_default_timezone_set('America/Los_Angeles');



  /**
    * Database Connection
  **/

  $db = new PDO(
    sprintf( 'mysql:host=%s;dbname=%s;charset=utf8', DB_HOST, DB_NAME )
  , DB_USER
  , DB_PASSWORD
  );
  $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
  \db($db);



  /**
    * Path
  **/

  define( 'PATH', dirname( __DIR__ ) . '/' );



  /**
    * User ID
  **/

  $p = explode( '/', $_SERVER['REQUEST_URI'] );

  if( isset( $_SESSION['id_user'] ) AND $_SESSION['id_user'] ) {
    define( 'ID_USER', $_SESSION['id_user'] );
  } elseif( !defined('NO_LOGIN') AND $p[1] != 'f' ) {
    header( 'Location: /login' );
    exit();
  }


  /**
    * Static path
  **/

  define( 'ST', '/app/template/static/' );
