<?php

  $user = new \User\User( ID_USER );

  if( $_POST['action'] == 'update' ) {

    $_POST['id_user'] = ID_USER;
    $user->settings_data( $_POST );
    $error = '<div class="alert alert-success">All good, company data modified</div>';
  }

?>
<div class="page-head">
  <div class="page-title">
    <h1>Data</h1>
  </div>
</div>

<?php echo @$error; ?>

<div class="portlet box green">

  <div class="portlet-title">
    <div class="caption">
      <i class="fa fa-gift"></i>Edit Data
    </div>
  </div>

  <div class="portlet-body form">
    <form action="/settings" method="post" class="form-horizontal">
      <input type="hidden" name="action" value="update" />
											<div class="form-body">
												<div class="form-group">
													<label class="col-md-3 control-label">Company Name</label>
													<div class="col-md-4">
														<input type="text" class="form-control input-circle" name="settings_company" placeholder="Enter text" value="<?php echo $user->settings_company; ?>">

													</div>
												</div>

                        <div class="form-group">
													<label class="col-md-3 control-label">Public URL</label>
													<div class="col-md-4">
														<input type="text" class="form-control input-circle" name="identifier" placeholder="Enter your wanted public url" value="<?php echo $user->identifier; ?>">
													</div>
												</div>

												<div class="form-group">
													<label class="col-md-3 control-label">Description</label>
													<div class="col-md-4">
														<input type="text" class="form-control input-circle" name="settings_description" value="<?php echo $user->settings_description; ?>" placeholder="Enter text">

													</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label">Press Contact Email</label>
													<div class="col-md-4">
														<div class="input-group">
															<span class="input-group-addon input-circle-left">
															<i class="fa fa-envelope"></i>
															</span>
															<input type="email" class="form-control input-circle-right" name="settings_press_contact" value="<?php echo $user->settings_press_contact; ?>" placeholder="Email Address">
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
										<!-- END FORM-->
									</div>
								</div>
			<!-- END PAGE CONTENT INNER -->
