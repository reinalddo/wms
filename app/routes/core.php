<?php

  /**
    * Index
  **/

  $app->get('/', function() use ($app) {

    $app->render( 'page/index.php' );

  });

  $app->map('/dashboard/inventario', function() use ($app) {

    $app->render( 'page/index.inventario.php' );
  })->via( 'GET', 'POST' );


  $app->map('/dashboard/resumen', function() use ($app) {

    $app->render( 'page/index.resumen.ejecutivo.php' );

  })->via( 'GET', 'POST' );

  $app->map('/dashboard/monitoreo', function() use ($app) {

    $app->render( 'page/index.monitoreo.php' );

  })->via( 'GET', 'POST' );

  $app->post('/api/sendMail/', 'sendMail');

  function sendMail () {

    $templates = new \Templates\Templates();
    $file = new \File\File();

    $id = $_POST['id'];

    $templates->id_template = $id;
    $row = $templates->__get('id_template');
    $title = $templates->__get('title');
    $container = $templates->__get('content');
    $mailer = new PHPMailer();

    $mailer->From     = SMTP_USERNAME;
    $mailer->FromName = SITE_TITLE;
    $mailer->AddAddress("luisfraino@gmail.com");
    $mailer->isHTML(true);
    $mailer->Body = $container;
    $mailer->Subject = "UPLOAD";

    foreach ($file->getList($id) AS $f ) {
      $_f = "/data/uploads/".trim($f->filename);
      $mailer->AddAttachment($_f, $f->filename);
    }

    header("Content-Type: application/json");
    $_arr = array("success" => "true");
    echo json_encode($_arr);
    
    if(!$mailer->Send()) {
      echo "<script>alert('Mailer Error: " . $mailer->ErrorInfo."')</script>";
    } else {
      echo "<script>alert('Your request has been submitted. We will contact you soon.')</script>";
      //Header('Location: main.php');
    }

    exit();
  }
  /**
    * Before all Routes
  **/
function getRealIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];
       
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
   
    return $_SERVER['REMOTE_ADDR'];
}

  $app->hook('slim.before.dispatch', function() use ($app) {
    if(!isset($_REQUEST['nofooternoheader'])){
      $id = $_SESSION['id_user'];
      $IP = getRealIP();

      $sql = 'INSERT INTO users_online (id_usuario, last_updated, IP_Address) VALUES('.$id.', NOW(), "'.$IP.'") ON DUPLICATE KEY UPDATE last_updated = NOW();';
      $data = mysqli_query(\db2(), $sql);
      $subdomain = $_SESSION['identifier'];
      $app->render( 'header.php', array(
        'page' => $page[0]
      ) );
    }

  });

  /**
    * After all Routes
  **/

  $app->hook('slim.after.dispatch', function() use ($app) {

    if(!isset($_REQUEST['nofooternoheader'])){
      $page = explode( '/', ltrim( $_SERVER['REQUEST_URI'], '/' ) );
      $subdomain = $_SESSION['identifier'];
      $app->render( 'footer.php' );
    }

  });
