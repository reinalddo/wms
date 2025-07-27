<?php

  define( 'NO_LOGIN', true );
  include 'load.php';

  $mail = new \Mail\Mail();

  foreach( $mail->getNotSent() AS $m ) {

    $mailer = new PHPMailer;

    $mailer->isSMTP();                                      // Set mailer to use SMTP
    $mailer->Host = SMTP_HOSTNAME;  // Specify main and backup SMTP servers
    $mailer->SMTPAuth = true;                               // Enable SMTP authentication
    $mailer->Username = SMTP_USERNAME;                 // SMTP username
    $mailer->Password = SMTP_PASSWORD;                           // SMTP password
    $mailer->SMTPSecure = SMTP_PROTOCOL;                            // Enable TLS encryption, `ssl` also accepted
    $mailer->Port = SMTP_PORT;

    $mailer->setFrom(SITE_EMAIL, SITE_TITLE);

    $mailer->addAddress( $m->email, $m->first_name . ' ' . $m->last_name );
    $mailer->isHTML( true );

    $mailer->Subject = $m->title;
    $content = $m->content;

    if( $m->invite ) {

      $content .= "<br /><br /><h3>Accept or decline this invite</h3>\n";
      $content .= "<a href='".SITE_URL."respond?status=1&hash=".$m->hash."'><strong>Accept</strong> this invitation</a>\n";
      $content .= "<a href='".SITE_URL."respond?status=2&hash=".$m->hash."'><strong>Decline</strong> this invitation</a>\n";

    }

    if( $m->type == 'UPLOAD' ) {

      $file = new \File\File();
      $content .= "<br /><br /><h3>Download file</h3>\n";
      $content .= "<p>Click the link(s) to download the file(s) sent to you.</p>\n";

      if( $file->getList( $m->id_template ) ) {
        foreach( $file->getList( $m->id_template ) AS $f ) {
          $content .= "<a href='".SITE_URL."download?status=1&hash=".$f->hash."'>Download file</a>\n";
        }
      }

    }

    $content .= "<img src='".SITE_URL."opened?hash=".$m->hash."' />\n";

    $mailer->Body = $content;

    if(!$mailer->send()) {
      echo 'Message could not be sent.';
      echo 'Mailer Error: ' . $mailer->ErrorInfo;
    } else {
      $mail->setSent( $m->id_mail );
    }

  }
