<?php

  if( isset( $_GET['id_subscriber'] ) AND isset( $_GET['status'] ) ) {

    $subscribers = new \Subscribers\Subscribers();
    $subscribers->setValue( $_GET['status'], 'approved', $_GET['id_subscriber'] );


  }

  $subscribers = new \Subscribers\Subscribers();
  $lists = new \Lists\Lists();
  $lists_subscribers = new \Lists\Subscribers();

?>
<div class="portlet box red">

  <div class="portlet-title">
    <div class="caption">
      <i class="fa fa-cogs"></i> Pending approval
    </div>
  </div>

  <div class="portlet-body">
    <div class="table-scrollable">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th style="width: 130px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach( $subscribers->getList( ID_USER, 3 ) AS $l ): ?>
          <tr>
            <td><a href="/subscribers/view/<?php echo $l->id_subscriber; ?>"><?php echo $l->first_name; ?> <?php echo $l->last_name; ?></a></td>
            <td><?php echo $l->email; ?></td>
            <td>
              <a href="/subscribers/pending?id_subscriber=<?php echo $l->id_subscriber; ?>&status=1" class="label label-sm label-success">Approve</a>
              <a href="/subscribers/pending?id_subscriber=<?php echo $l->id_subscriber; ?>&status=2" class="label label-sm label-danger">Decline</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
