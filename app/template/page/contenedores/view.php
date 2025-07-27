<?php

  $subscriber = new \Subscribers\Subscribers( $id_subscriber );
  $mail = new \Mail\Mail();
  $s_lists = new \Lists\Subscribers();

  $totals = $mail->getTotalsSubscriber( $id_subscriber );

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
			<div class="tab-pane" id="tab_3">
								<div class="portlet box blue">
									<div class="portlet-title">
										<div class="caption">
											<i class="fa fa-gift"></i>View Subscriber Info - <?php echo $subscriber->first_name; ?> <?php echo $subscriber->last_name; ?>
										</div>
										<div class="tools">
											<a href="javascript:;" class="collapse">
											</a>
											<a href="#portlet-config" data-toggle="modal" class="config">
											</a>
											<a href="javascript:;" class="reload">
											</a>
											<a href="javascript:;" class="remove">
											</a>
										</div>
									</div><div class="portlet-body form">
										<!-- BEGIN FORM-->
										<form class="form-horizontal" role="form">
											<div class="form-body">

												<h3 class="form-section">Person Info</h3>
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="control-label col-md-3">First Name:</label>
															<div class="col-md-9">
																<p class="form-control-static">
																	 <?php echo $subscriber->first_name; ?>
																</p>
															</div>
														</div>
													</div>
													<!--/span-->
													<div class="col-md-6">
														<div class="form-group">
															<label class="control-label col-md-3">Last Name:</label>
															<div class="col-md-9">
																<p class="form-control-static">
																	 <?php echo $subscriber->last_name; ?>
																</p>
															</div>
														</div>
													</div>
													<!--/span-->
												</div>
												<!--/row-->
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="control-label col-md-3">Media:</label>
															<div class="col-md-9">
																<p class="form-control-static">
																	 <?php echo $subscriber->media; ?>
																</p>
															</div>
														</div>
													</div>
													<!--/span-->
													<div class="col-md-6">
														<div class="form-group">
															<label class="control-label col-md-3">E-Mail:</label>
															<div class="col-md-9">
																<p class="form-control-static">
																	 <?php echo $subscriber->email; ?>
																</p>
															</div>
														</div>
													</div>
													<!--/span-->
												</div>
												<!--/row-->
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="control-label col-md-3">Address:</label>
															<div class="col-md-9">
																<p class="form-control-static">
																	 <?php echo $subscriber->address; ?>
																</p>
															</div>
														</div>
													</div>
													<!--/span-->
													<div class="col-md-6">
														<div class="form-group">
															<label class="control-label col-md-3">Phone:</label>
															<div class="col-md-9">
																<p class="form-control-static">
																	 <?php echo $subscriber->phone; ?>
																</p>
															</div>
														</div>
													</div>
													<!--/span-->
												</div>
												<!--/row-->
												<h3 class="form-section">Social Networks</h3>
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="control-label col-md-3">Facebook:</label>
															<div class="col-md-9">
																<p class="form-control-static">
																	 <?php echo $subscriber->facebook; ?>
																</p>
															</div>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="control-label col-md-3">Twitter:</label>
															<div class="col-md-9">
																<p class="form-control-static">
																	 <?php echo $subscriber->twitter; ?>
																</p>
															</div>
														</div>
													</div>
													<!--/span-->

													<!--/span-->
												</div>
											</div>
											<div class="form-actions">
												<div class="row">
													<div class="col-md-6">
														<div class="row">
															<div class="col-md-offset-3 col-md-9">
																<a href="/subscribers/edit/<?php echo $id_subscriber; ?>" class="btn green"><i class="fa fa-pencil"></i> Edit</a>
															</div>
														</div>
													</div>
													<div class="col-md-6">
													</div>
												</div>
											</div>
										</form>
										<!-- END FORM-->
									</div></div>
									<div class="portlet box red">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-cogs"></i>Sent/Open
							</div>
							<div class="tools">
								<a href="javascript:;" class="collapse">
								</a>
								<a href="#portlet-config" data-toggle="modal" class="config">
								</a>
								<a href="javascript:;" class="reload">
								</a>
								<a href="javascript:;" class="remove">
								</a>
							</div>
						</div>
						<div class="portlet-body" style="font-size:20px;">
						Total Sent: <?php echo $totals[0]->sent; ?> <br />
            Total Opens: <?php echo $totals[0]->opens; ?></div>
						</div>

									<div class="portlet box red">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-cogs"></i>Sent/Open
							</div>
							<div class="tools">
								<a href="javascript:;" class="collapse">
								</a>
								<a href="#portlet-config" data-toggle="modal" class="config">
								</a>
								<a href="javascript:;" class="reload">
								</a>
								<a href="javascript:;" class="remove">
								</a>
							</div>
						</div>
						<div class="portlet-body">
							<div class="table-scrollable">
								<table class="table table-hover">
								<thead>
								<tr>
									<th>
										 #
									</th>
									<th>
										 Title
									</th>
									<th>
										 Type
									</th>

									<th>
										 Status
									</th>
								</tr>
								</thead>
								<tbody>
                <?php foreach( $totals AS $l ): ?>
								<tr>
									<td>
										 <?php echo $l->id_subscriber; ?>
									</td>
									<td>
										 <?php echo $l->title; ?>
									</td>
									<td>
										 <?php echo $l->type; ?>
									</td>

									<td>
                    <?php if( $l->is_sent ): ?>
										<span class="label label-sm label-success">
										Sent </span>
                    <?php endif; ?>
									</td>
								</tr>
                <?php endforeach; ?>
								</tbody>
								</table>
							</div>
						</div>
					</div>
			<!-- END PAGE CONTENT INNER -->
		</div>
