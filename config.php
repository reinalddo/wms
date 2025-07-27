<?php

  /**
    * Error Reporting --- ignorar
  **/

  error_reporting( E_ALL & ~E_NOTICE );
  ini_set( 'display_errors', 'On' );


  /**
    * MySQL Details
  **/
/*
u: root pass: Adventech10@#  host:89.117.146.27 port: 3306   assistpr_wmsdev
*/

define( 'DB_HOST', '10.0.0.38' );
define( 'DB_USER', 'assistpr_wms4971' );
define( 'DB_NAME', 'assistpr_wms4971' );
define( 'DB_PASSWORD', '0sGYvVuzeGX)' );

//define( 'DB_HOST', '89.117.146.27' );
//define( 'DB_USER', 'advlsystem' );
//define( 'DB_NAME', 'assistpr_wmsdev' );
//define( 'DB_PASSWORD', 'Adventech10@#' );
  /**
    * SQL Server Details
  **/
define( 'DB_REMOTE_HOST', 'vps176454.vps.ovh.ca' );
define( 'DB_REMOTE_USER', 'sa' );
define( 'DB_REMOTE_NAME', 'lacentral' );
define( 'DB_REMOTE_PASSWORD', 'KG4t+|1tB8)' );

  /****
    * General Details
  **/

  define( 'SITE_TITLE', 'Assistpro ADVL WMS | OC2020' );
  define( 'SITE_EMAIL', 'send@advanceware.com' );
  define( 'SITE_URL', 'http://wms.local.com/' );


  /**
    * SMTP Details
  ****/

  define( 'SMTP_HOSTNAME', 'mail.advanceware.com' );          // Semi-colon (;) seperated list for multiple entries
  define( 'SMTP_USERNAME', 'send@advanceware.com' );
  define( 'SMTP_PASSWORD', 'test2' );
  define( 'SMTP_PROTOCOL', 'tls' );
  define( 'SMTP_PORT', '25' );


  /**
    * Mercado Pago
  **/

  define( 'MERCADO_CLIENT', '7151238173629837' );
  define( 'MERCADO_SECRET', 'Vpq911rjWF0R9MoM44u9wytNyh5Lu2CT' );
