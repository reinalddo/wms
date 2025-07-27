<?php

  $templates = new \Templates\Templates( $id_template );
  $mail = new \Mail\Mail();

?>
<div class="portlet box red">

  <div class="portlet-title">
    <div class="caption">
      <i class="fa fa-cogs"></i> RSVP's for <?php echo $templates->title; ?>
    </div>
  </div>

  <div class="portlet-body">
    <div class="table-scrollable">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Response</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach( $mail->getList( $id_template ) AS $t ): ?>
          <tr>
            <td><?php echo $t->first_name; ?> <?php echo $t->last_name; ?></td>
            <td><?php echo $t->email; ?></td>
            <td>
              <?php if( $t->response == 2 ): ?>
              <strong class="text-danger">Invite declined</strong>
              <?php elseif( $t->response == 1): ?>
              <strong class="text-success">Invite accepted</strong>
              <?php else: ?>
              <strong class="text-info">Not responded</strong>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
