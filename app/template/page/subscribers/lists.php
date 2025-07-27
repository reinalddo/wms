<?php

  $lists = new \Lists\Lists();
  $lists_subscribers = new \Lists\Subscribers();

  if( $_POST['action'] == 'create_list' ) {

    try {

      \db()->beginTransaction();

      $_POST['id_user'] = ID_USER;
      $lists->save( $_POST );

      \db()->commit();

      $error = '<div class="alert alert-success">All good, list created.</div>';

    } catch( \ErrorException $e ) {

      \db()->rollback();
      echo $e->getMessage();

    }

  }

?>

<?php echo @$error; ?>

<div class="portlet box green">

  <div class="portlet-title">
    <div class="caption">
      <i class="fa fa-gift"></i>Add list
    </div>
  </div>

  <div class="portlet-body form">

    <form action="/subscribers/lists" method="post" class="form-horizontal"><br />

      <input type="hidden" name="action" value="create_list" />

      <div class="form-group">
        <label class="control-label col-md-3">Title <span class="required">*</span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="title" required />
        </div>
      </div>

      <div class="form-actions">
        <div class="row">
          <div class="col-md-offset-3 col-md-9">
            <button type="submit" class="btn btn-circle blue">Submit</button>
            <button type="button" class="btn btn-circle default">Cancel</button>
          </div>
        </div>
      </div>

    </form>

  </div>
</div>

<div class="portlet box red">

  <div class="portlet-title">
    <div class="caption">
      <i class="fa fa-cogs"></i>Lists
    </div>
  </div>

  <div class="portlet-body">
    <div class="table-scrollable">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Name</th>
            <th>Subscribers</th>
            <th style="width: 50px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach( $lists->getList( ID_USER ) AS $l ): ?>
          <tr>
            <td><?php echo $l->title; ?></td>
            <td><?php echo count( $lists_subscribers->getList( $l->id_list ) ); ?></td>
            <td><!--<a href="/subscribers/lists/<?php echo $l->id_list; ?>" class="label label-sm label-success">Edit</a>--></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
