<?php

$mail = new \Mail\Mail();
$templates = new \Templates\Templates();
$lists = new \Lists\Lists();
$subscribers_lists = new \Lists\Subscribers();
$file = new \File\File();

if( $_POST['action'] == 'sendMail' ) {
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

    if(!$mailer->Send()) {
        echo "<script>alert('Mailer Error: " . $mailer->ErrorInfo."')</script>";
    } else {
        echo "<script>alert('Your request has been submitted. We will contact you soon.')</script>";
        //Header('Location: main.php');
    }
    $_arr = array("success" => "true");
    echo json_encode($_arr);
    exit();
}

if( $_POST['action'] == 'deleteMail' ) {

    $_arr = array("success" => "true");
    echo json_encode($_arr);
    exit();
}

if( $_POST['action'] == 'createMail' ) {

    $sendto = array();

    try {

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
                    $target = PATH . 'data/uploads/' . $name . '.' . $target_ext;

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
                    }
                }

            }
        }

        \db()->commit();

        $error = '<div class="alert alert-success">All good, subscriber added</div>';

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

?>
<script>
    $modal0 = null;

    function sendMail() {
        $modal0 = $("#pleaseWaitDialog");
        $modal0.modal('show');
        var formData = new FormData($("form#dataf")[0]);
        //var htmlData = $('textarea[name="content"]').html($('#summernote_1').code());
        var htmlData = $('textarea[name="content"]').html($('#summernote_1').code());
        formData.append("content_html", htmlData.val());
        /*$.ajax({
            type: "POST",
            dataType: "json",
            data: formData,
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); } },
            url: '/api/index.php',
            success: function(data) {
                $modal0.modal('hide');
                location.href = "/mail/upload";
            }
        });*/
        $.ajax({
            url: '/api/index.php',
            type: 'POST',
            data: formData,
            cache: false,
            dataType: 'json',
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            success: function(data, textStatus, jqXHR)
            {
                if(typeof data.error === 'undefined')
                {
                    // Success so call function to process the form
                    console.log('SUCCESS: ' + data.success);
                    $modal0.modal('hide');
                    location.href = "/mail/upload";
                }
                else
                {
                    // Handle errors here
                    console.log('ERRORS: ' + data.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                // Handle errors here
                console.log('ERRORS: ' + textStatus);
                // STOP LOADING SPINNER
            }
        });
    }

    /*function deleteList(id) {
        $modal0 = $("#pleaseWaitDialog");
        $modal0.modal('show');
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {
                action: 'deleteMail',
                id: id
            },
            beforeSend: function(x) { if(x && x.overrideMimeType) { x.overrideMimeType("application/json;charset=UTF-8"); } },
            url: '/api/index.php',
            success: function(data) {
                $modal0.modal('hide');
                location.href = "/mail/upload";
            }
        });
    }*/
</script>
<div class="modal" id="pleaseWaitDialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Processing...</h2>
            </div>
            <div class="modal-body">
                <div class="progress progress-striped active">
                    <div class="progress-bar" style="width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="portlet box green">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-gift"></i>New upload
        </div>
    </div>

    <div class="portlet-body form">
        <?php if( $lists->getList( ID_USER ) ): ?>
            <!--<form action="/mail/upload" method="post" class="form-horizontal" enctype="multipart/form-data">-->
            <form id="dataf" class="form-horizontal" enctype="multipart/form-data">

                <input type="hidden" name="action" value="createMail" />

                <div class="form-body">

                    <div class="form-group">
                        <label class="control-label col-md-3">Title <span class="required">*</span></label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="title" required />
                        </div>
                    </div>

                    <hr />

                    <div class="form-group">

                        <label class="control-label col-md-3">Files</label>
                        <div class="col-md-9">
                            <a href="#" class="btn btn-primary property-preview-photos-upload-go">Upload a file <i class="fa fa-plus-circle"></i></a>
                            <br /><br />

                            <div class="uploaded_preview">

                            </div>

                            <div class="uploaded_files">
                                <input type="file" name="photos[]" class="btn btn-grey property-preview-photos-upload" data-number="1" style="display: none;" />
                            </div>
                        </div>

                    </div>

                    <hr />

                    <div class="form-body">
                        <div class="form-group last">
                            <label class="control-label col-md-3">Mail</label>
                            <div class="col-md-9">
                                <textarea name="content" rows="18" id="summernote_1"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-md-3">What list/s do you want to add to?</label>
                        <div class="col-md-4">
                            <?php foreach( $lists->getList( ID_USER ) AS $l ): ?>
                                <input type="checkbox" name="lists[]" value="<?php echo $l->id_list; ?>"><?php echo $l->title; ?><br />
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <button type="button" class="btn btn-circle blue" onclick="sendMail();">Submit</button>
                        </div>
                    </div>
                </div>

            </form>
        <?php else: ?>
            <div class="form-body">
                <p>Please <a href="/subscribers/lists">create a list</a> the user can subscribe to before you create a new subscriber</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="portlet box red">

    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-cogs"></i> Uploads
        </div>
    </div>

    <div class="portlet-body">
        <div class="table-scrollable">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>List</th>
                    <!--<th>Attach</th>
                    <th></th>-->
                </tr>
                </thead>
                <tbody>
                <?php
                foreach( $templates->getList( ID_USER, 'UPLOAD' ) AS $t ) {
                    ?>
                    <tr>
                        <td>
                            <?php echo
                            trim($t->title);
                            ?></td>
                        <td>
                            <?php
                            /*$list = json_decode( $t->lists );
                            if( $list ) {
                              foreach( $list AS $name => $key ) {
                                $lists = new \Lists\Lists( $key );
                                echo $lists->title . ', ';
                              }
                            }*/
                            foreach( $file->getList( $t->id_template ) AS $f ) {
                                $_f = trim($f->filename);
                                echo '<a href="http://'.DYNAMIC_SUBDOMAIN.'/'.$_f.'">'.$_f.'</a><br>';
                            }
                            ?></td>
                        <!--<td><input type="checkbox" id="_chk<?php echo $_f; ?>"></td>
                        <td><a href="#" onclick="deleteList('<?php /*echo $t->id_template; */?>');">Delete</a></td>-->
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
