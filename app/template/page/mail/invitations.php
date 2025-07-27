<?php

  $mail = new \Mail\Mail();
  $templates = new \Templates\Templates();
  $lists = new \Lists\Lists();
  $subscribers_lists = new \Lists\Subscribers();

  if( $_POST['action'] == 'createMail' ) {

    $sendto = array();

    try {

      \db()->beginTransaction();

      $_POST['type'] = 'INVITE';
      $_POST['id_user'] = ID_USER;
      $result = $templates->save( $_POST );

      foreach( $_POST['lists'] AS $name => $key ) {

        foreach( $subscribers_lists->getList( $key ) AS $sl ) {

          if( !in_array( $sl->id_subscriber, $sendto ) ) {
            $mail->save( array(
              'id_template' => $result['id_template']
            , 'id_subscriber' => $sl->id_subscriber
            , 'id_user' => ID_USER
            , 'invite' => true
            , 'is_sent' => 0
            ) );

            $sendto[] = $sl->id_subscriber;
          }
        }

      }

      \db()->commit();

      $error = '<div class="alert alert-success">All good, subscriber added</div>';

    } catch( \ErrorException $e ) {

      \db()->rollback();
      echo $e->getMessage();

    }

  }

?>
<div class="portlet box green">
  <div class="portlet-title">
    <div class="caption">
      <i class="fa fa-gift"></i>New invitation
    </div>
  </div>

  <div class="portlet-body form">
    <?php if( $lists->getList( ID_USER ) ): ?>
    <form action="/mail/invitations" method="post" class="form-horizontal">

      <input type="hidden" name="action" value="createMail" />

      <div class="form-body">

        <div class="form-group">
          <label class="control-label col-md-3">Title <span class="required">*</span></label>
          <div class="col-md-4">
            <input type="text" class="form-control" name="title" required />
          </div>
        </div>

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
      <i class="fa fa-cogs"></i> Events
    </div>
  </div>

  <div class="portlet-body">
    <div class="table-scrollable">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Title</th>
            <th>List</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach( $templates->getList( ID_USER, 'INVITE' ) AS $t ): ?>
          <tr>
            <td><?php echo $t->title; ?></td>
            <td>
              <?php
                $list = json_decode( $t->lists );
                foreach( $list AS $name => $key ) {
                  $lists = new \Lists\Lists( $key );
                  echo $lists->title . ', ';
                }
              ?>
            </td>
            <td style="width: 20px;"><a href="/mail/invitations/<?php echo $t->id_template; ?>"><i class="fa fa-eye"></i></a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
