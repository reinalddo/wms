<?php

  $mail = new \Mail\Mail();
  $templates = new \Templates\Templates( $id_template );
  $lists = new \Lists\Lists();
  $subscribers_lists = new \Lists\Subscribers();
  $user = new \User\User( ID_USER );

  if( $_POST['action'] == 'createMail' ) {

    $sendto = array();

    try {

      \db()->beginTransaction();

      $_POST['type'] = 'PRESS';
      $_POST['id_user'] = ID_USER;
      $_POST['id_template'] = $id_template;
      $result = $templates->save( $_POST );

      \db()->commit();

      $error = '<div class="alert alert-success">All good, press release updated.</div>';

    } catch( \ErrorException $e ) {

      \db()->rollback();
      echo $e->getMessage();

    }

  }

?>
<div class="portlet box green">
  <div class="portlet-title">
    <div class="caption">
      <i class="fa fa-gift"></i>Edit Press Release
    </div>
  </div>

  <div class="portlet-body form">
    <?php if( $lists->getList( ID_USER ) ): ?>
    <form action="/mail/press/<?php echo $id_template; ?>" method="post" class="form-horizontal">

      <input type="hidden" name="action" value="createMail" />

      <div class="form-body">

        <div class="form-group">
          <label class="control-label col-md-3">Title <span class="required">*</span></label>
          <div class="col-md-4">
            <input type="text" class="form-control" name="title" value="<?php echo $templates->title; ?>" required />
          </div>
        </div>

        <div class="form-body">
          <div class="form-group last">
            <label class="control-label col-md-3">Mail</label>
            <div class="col-md-9">
              <textarea name="content" rows="18" id="summernote_1"><?php echo $templates->content; ?></textarea>
            </div>
          </div>
        </div>

      </div>

      <div class="form-actions">
        <div class="row">
          <div class="col-md-offset-3 col-md-9">
  					<button type="submit" class="btn btn-circle blue">Submit</button>
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
