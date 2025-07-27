<?php
include '../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$mail = new \Mail\Mail();
$templates = new \Templates\Templates();
$lists = new \Lists\Lists();
$subscribers_lists = new \Lists\Subscribers();
$subscribers = new \Subscribers\Subscribers();
$file = new \File\File();

if( $_POST['action'] == 'createMail' ) {
    $_POST['content'] = $_POST["content_html"];
    $sendto = array();
    $sendtoMail = array();

    try {
        $mailer = new PHPMailer();
        $mailer->From     = SMTP_USERNAME;
        $mailer->FromName = SITE_TITLE;

        \db()->beginTransaction();

        $_POST['type'] = 'UPLOAD';
        $_POST['id_user'] = ID_USER;

        $result = $templates->save( $_POST );

        $moved = array();

        if( !empty( $_FILES ) ) {
            foreach( $_FILES[ 'photos' ][ 'tmp_name' ] AS $index => $tmpName ) {

                $error = null;

                // Check if file is empty
                if( !empty( $_FILES[ 'photos' ][ 'error' ][ $index ] ) )
                    $error = array( 'msg' => 'Some error occured with the file in index.' );

                // Check if it's an uploaded file
                if( !empty( $tmpName ) && is_uploaded_file( $tmpName ) ) {

                    // New file name
                    $name = md5( ID_USER . $_FILES[ 'photos' ][ 'name' ][ $index ] . microtime() );

                    // Extension
                    $target_ext = pathinfo( $_FILES[ 'photos' ][ 'name' ][ $index ], PATHINFO_EXTENSION );

                    // Path
                    //$target = PATH . 'data/uploads/' . $name . '.' . $target_ext;
                    $target = PATH . SUBDOMAIN_USER . '/' . $name . '.' . $target_ext;

					$_f = $target; //$_SERVER["DOCUMENT_ROOT"] . "/data/uploads/" . trim($f->filename);
					//$mailer->AddAttachment($_f, $name . '.' . $target_ext);
				
                    // Is a picture
                    if( getimagesize( $tmpName ) === false )
                        $error = array( 'msg' => 'File is not an image.' );

                    // Check for errors
                    if( $error['msg'] )
                        throw new \ErrorException( $error['msg'] );

                    // Move file
                    if ( !move_uploaded_file( $tmpName, $target ) )
                        $error = array( 'msg' => 'The file could not be uploaded.' );

                    // Check for errors
                    if( $error['msg'] )
                        throw new \ErrorException( $error['msg'] );

                    $file->save( array(
                        'id_template' => $result['id_template']
                    , 'file_original' => $_FILES[ 'photos' ][ 'name' ][ $index ]
                    , 'filename' => $name . '.' . $target_ext
                    , 'filesize' => $_FILES[ 'photos' ][ 'size' ][ $index ]
                    ) );

                    // Add to uploaded
                    $moved[] = $target;

                }
            }
        }

        if( isset( $_POST['lists'] ) ) {
            foreach( $_POST['lists'] AS $name => $key ) {

                foreach( $subscribers_lists->getList( $key ) AS $sl ) {

                    if( !in_array( $sl->id_subscriber, $sendto ) ) {
                        $mail->save( array(
                            'id_template' => $result['id_template']
                        , 'id_subscriber' => $sl->id_subscriber
                        , 'id_user' => ID_USER
                        , 'is_sent' => 0
                        ) );

                        $sendto[] = $sl->id_subscriber;
                        foreach ($subscribers->getList($sl->id_subscriber) AS $_s) {
                            $sendtoMail[] = $_s->email;
                        }
                    }
                }

            }
        }

        \db()->commit();

        $error = '<div class="alert alert-success">All good, subscriber added</div>';

        if (!empty($sendtoMail)) {

            //$templates->id_template = $id;
            //$row = $templates->__get('id_template');
            $title = $_POST['title']; //$templates->__get('title');
            $content = $_POST['content']; //$templates->__get('content');

            foreach ($sendtoMail as $m) {
                $mailer->AddAddress($m);
            }

            if( $file->getList( $result['id_template'] ) ) {
                $content .= "<br /><br /><h3>Download file</h3>\n";
                $content .= "<p>Click the link(s) to download the file(s) sent to you.</p>\n";
                foreach( $file->getList( $result['id_template'] ) AS $f ) {
                    $_f = trim($f->filename);
                    $content .= "<a href='http://".DYNAMIC_SUBDOMAIN."/".$_f."'>Download file: ".$_f."</a><br>";
                }
            }

            $mailer->isHTML(true);
            $mailer->Body = $content;
            $mailer->Subject = "UPLOAD";

            if (!$mailer->Send()) {
                //echo "<script>alert('Mailer Error: " . $mailer->ErrorInfo."')</script>";
            } else {
                //echo "<script>alert('Your request has been submitted. We will contact you soon.')</script>";
                //Header('Location: main.php');
            }
        }
        $_arr = array("success" => "true");
        echo json_encode($_arr);
        exit();

    } catch( \ErrorException $e ) {

        // Remove uploaded files
        if( $moved ) {
            foreach( $moved AS $m )
                unlink( $m );
        }

        \db()->rollback();

        echo $e->getMessage();

    }

}

if( $_POST['action'] == 'deleteMail' ) {

    $_arr = array("success" => "true");
    echo json_encode($_arr);
    exit();
}

