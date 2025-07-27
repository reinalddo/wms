<?php

  $subscribers = new \Subscribers\Subscribers( $id_subscriber );
  $lists = new \Lists\Lists();
  $lists_subscribers = new \Lists\Subscribers();

  if( $_POST['action'] == 'edit_subscriber' ) {

    if( isset( $_POST['lists'] ) ) {

      try {

        \db()->beginTransaction();

        $_POST['id_user'] = ID_USER;
        $result = $subscribers->save( $_POST );

        foreach( $_POST['lists'] AS $name => $key ) {

          $lists_subscribers->save( array(
            'id_subscriber' => $result['id_subscriber']
          , 'id_list' => $key
          ) );

        }

        \db()->commit();

        $error = '<div class="alert alert-success">All good, subscriber added</div>';

      } catch( \ErrorException $e ) {

        \db()->rollback();
        echo $e->getMessage();

      }

    } else {

      $error = '<div class="alert alert-danger">Please choose at least one list the user subscribes to.</div>';

    }

  }

  $current = $lists_subscribers->getList( false, $id_subscriber, false );

  $old_list = array();

  foreach( $current AS $c ) {
    $old_list[] = $c->id_list;
  }

?>

<?php echo @$error; ?>

<div class="portlet box green">

  <div class="portlet-title">
    <div class="caption">
      <i class="fa fa-gift"></i>Edit subscriber
    </div>
  </div>

  <div class="portlet-body form">

    <?php if( $lists->getList( ID_USER ) ): ?>
    <form action="/subscribers/edit/<?php echo $id_subscriber; ?>" method="post" class="form-horizontal"><br />

      <input type="hidden" name="action" value="edit_subscriber" />

      <div class="form-group">
        <label class="control-label col-md-3">First name <span class="required">*</span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="first_name" value="<?php echo $subscribers->first_name; ?>" required />
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">Last name <span class="required">*</span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="last_name" value="<?php echo $subscribers->last_name; ?>" required />
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">Media <span class="required">* </span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="media" value="<?php echo $subscribers->media; ?>" required />
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">E-mail <span class="required">*</span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="email" value="<?php echo $subscribers->email; ?>" required />
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">Address</label>
				<div class="col-md-4">
          <input type="text" class="form-control" name="address" value="<?php echo $subscribers->address; ?>" />
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">Phone</label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="phone" value="<?php echo $subscribers->phone; ?>" />
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">Facebook</label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="facebook" value="<?php echo $subscribers->facebook; ?>" />
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">Twitter</label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="twitter" value="<?php echo $subscribers->twitter; ?>" />
        </div>
      </div>

      <hr />

      <div class="form-group">
        <label class="control-label col-md-3">What list/s do you want to add to?</label>
        <div class="col-md-4" style="padding-top: 10px;">
          <?php foreach( $lists->getList( ID_USER ) AS $l ): ?>
          <input type="checkbox" name="lists[]" value="<?php echo $l->id_list; ?>" <?php if( in_array( $l->id_list, $old_list ) ) { echo 'checked="checked"'; } ?>><?php echo $l->title; ?><br />
          <?php endforeach; ?>
        </div>
      </div>

      <hr />

      <div class="form-group">
        <label class="control-label col-md-3">Status</label>
        <div class="col-md-4" style="padding-top: 10px;">
          <input type="checkbox" name="approved" value="1" <?php if( $subscribers->approved == 1 ) { echo 'checked="checked"'; } ?>> User is approved and will recieve emails<br />
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
    <p>Please create a list the user can subscribe to before you create a new subscriber</p>
    <?php endif; ?>

  </div>
</div>
