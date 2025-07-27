<?php

  $subscribers = new \Subscribers\Subscribers();
  $lists = new \Lists\Lists();
  $lists_subscribers = new \Lists\Subscribers();

  if( $_POST['action'] == 'create_subscriber' ) {

    if( isset( $_POST['lists'] ) ) {

      try {

        \db()->beginTransaction();

        $_POST['id_user'] = ID_USER;
        $_POST['approved'] = 1;
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

?>

<?php echo @$error; ?>

<div class="portlet box green">

  <div class="portlet-title">
    <div class="caption">
      <i class="fa fa-gift"></i>Add subscriber
    </div>
  </div>

  <div class="portlet-body form">

    <?php if( $lists->getList( ID_USER ) ): ?>
    <form action="/subscribers" method="post" class="form-horizontal"><br />

      <input type="hidden" name="action" value="create_subscriber" />

      <div class="form-group">
        <label class="control-label col-md-3">First name <span class="required">*</span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="first_name" required />
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">Last name <span class="required">*</span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="last_name" required />
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">Media <span class="required">* </span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="media" required />
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">E-mail <span class="required">*</span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="email" required />
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">Address</label>
				<div class="col-md-4">
          <input type="text" class="form-control" name="address"/>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">Phone</label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="phone"/>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">Facebook</label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="facebook"/>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-md-3">Twitter</label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="twitter"/>
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

<div class="portlet box red">

  <div class="portlet-title">
    <div class="caption">
      <i class="fa fa-cogs"></i>Subscribers
    </div>
  </div>

  <div class="portlet-body">
    <div class="table-scrollable">
      <table class="table table-hover">
        <thead>
          <tr>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Email</th>
						<th>Media</th>
						<th>Status</th>
						<th style="width: 120px;"></th>
					</tr>
        </thead>
        <tbody>
          <?php foreach( $subscribers->getList( ID_USER ) AS $s ): ?>
          <tr>
            <td><?php echo $s->first_name; ?></td>
            <td><?php echo $s->last_name; ?></td>
            <td><?php echo $s->email; ?></td>
            <td><?php echo $s->media; ?></td>
            <td><?php if( $s->approved == 1 ) { echo '<span class="text-success">Approved</span>'; } elseif( $s->approved == 2 ) { echo '<span class="text-danger">Declined</span>'; } else { echo '<span class="text-info">Pending approval</span>'; } ?></td>
            <td><a href="/subscribers/edit/<?php echo $s->id_subscriber; ?>" class="label label-sm label-success"><i class="fa fa-pencil"></i> Edit</a> <a href="/subscribers/view/<?php echo $s->id_subscriber; ?>" class="label label-sm label-success"><i class="fa fa-eye"></i> View</a></td>
          </tr>
          <?php endforeach; ?>
				</tbody>
      </table>
    </div>
  </div>
</div>
