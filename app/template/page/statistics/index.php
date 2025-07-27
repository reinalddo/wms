<?php

  $mail = new \Mail\Mail();
  $templates = new \Templates\Templates();

?>
			<div class="page-head">
				<!-- BEGIN PAGE TITLE -->
				<div class="page-title">
					<h1>Statistics</h1>
				</div>
				<!-- END PAGE TITLE -->

			</div>
			<!-- END PAGE HEAD -->

			<!-- END PAGE HEADER-->
		<div class="portlet box red">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-cogs"></i>Subscriber Statistics
							</div>
						</div>
						<div class="portlet-body">
							<div class="table-scrollable">
								<table class="table table-hover">
								<thead>
								<tr>
									<th>
										 Name
									</th>
									<th>
										 Media
									</th>
									<th>
										 Sent
									</th>
										<th>

									</th>
									</tr>
								</thead>
								<tbody>
                  <?php foreach( $mail->getTotals() AS $m ): ?>
                  <tr>
                    <td><?php echo $m->first_name; ?> <?php echo $m->last_name; ?></td>
                    <td><?php echo $m->media; ?></td>
                    <td><?php echo $m->sent; ?></td>
                    <td style="width: 120px;"><a href="/subscribers/edit/<?php echo $m->id_subscriber; ?>" class="label label-sm label-success"><i class="fa fa-pencil"></i> Edit</a> <a href="/subscribers/view/<?php echo $m->id_subscriber; ?>" class="label label-sm label-success"><i class="fa fa-eye"></i> View</a></td>
                  </tr>
                  <?php endforeach; ?>
								</tbody>
								</table>
							</div>
						</div>
					</div>
						<div class="portlet box red">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-cogs"></i>Press Releases
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
                    <?php foreach( $templates->getList( ID_USER, 'PRESS' ) AS $t ): ?>
                    <tr>
                      <td><?php echo $t->title; ?></td>
                      <td>
                        <?php
                          if( is_array( json_decode( $t->lists ) ) ) {
                            $list = json_decode( $t->lists );
                            foreach( $list AS $name => $key ) {
                              $lists = new \Lists\Lists( $key );
                              echo $lists->title . ', ';
                            }
                          }
                        ?>
                      </td>
                      <td><?php echo $t->sent; ?> / <?php echo $t->opens; ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
							</div>
						</div>
					</div>
			<!-- END PAGE CONTENT INNER -->
