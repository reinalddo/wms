<?php

  $transactions = new \Transactions\Transactions();
  $user = new \User\User( ID_USER );
  $mp = new MP( MERCADO_CLIENT, MERCADO_SECRET );

  $packages = json_decode( file_get_contents( PATH . '/packages.json' ) );
  $data = array();

  foreach( $packages AS $p => $d ) {
    $data[$p] = array(
      "items" => array(
        array(
          "title" => SITE_TITLE . " Monthly Subscriptions",
          "currency_id" => "ARS",
          "quantity" => 1,
          "unit_price" => $d->price
        )
      )
    );
  }

  foreach( $packages AS $p => $d ) {
    ${'preference' . $p} = $mp->create_preference( $data[$p] );
  }

?>
<div class="page-head">
  <div class="page-title">
    <h1>Payments</h1>
  </div>
</div>

<?php if( !$user->due_date OR strtotime( $user->due_date ) < time() ): ?>
<div class="jumbotron" style="padding: 50px; background-color: #333; color: #fff;">

  <h2>Choose your plan</h2>

  <p>We have three available plans to choose from.. <strong>Free (50 Emails)</strong> and <strong>1000 Emails</strong> and <strong>5000 Emails</strong>. Choose which plan you would like to sign up for.</p>

  <?php foreach( $packages AS $p => $d ): ?>
  <a href="<?php echo ${'preference' . $p}["response"]["sandbox_init_point"]; ?>" target="_blank" name="MP-Checkout" class="orange-ar-m-sq-arall btn btn-danger btn-lg"><?php echo $d->emails; ?> Emails ( $<?php echo $d->price; ?> )</a>
  <?php endforeach; ?>

  <br /><br />
  <p style="font-size: 10px;">* All payments are processed with MercadoPago.com</p>

</div>
<?php endif; ?>

<div class="portlet box red">
<div class="portlet-title">
  <div class="caption">
    <i class="fa fa-cogs"></i> Transactions
  </div>
</div>
<div class="portlet-body">
  <div class="table-scrollable">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Title</th>
          <th>List</th>
          <th>Sent / Opens</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach( $transactions->getList( ID_USER ) AS $t ): ?>
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
          <td><?php echo $t->sent; ?> / 0</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</div>
