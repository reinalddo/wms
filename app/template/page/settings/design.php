<?php

  $user = new \User\User( ID_USER );

  if( $_POST['action'] == 'update' ) {

    if( $_FILES['upload']['name'] ) {

      $file_ext = pathinfo( basename( $_FILES['upload']['name'] ), PATHINFO_EXTENSION );
      $file_name = sha1( microtime() ) . '.' . $file_ext;
      $file_target = PATH . 'data/logos/' . $file_name;

      if( !move_uploaded_file ($_FILES['upload']['tmp_name'], $file_target)) {
        throw new ErrorException( 'File failed to upload' );
      }

      $_POST['settings_logo'] = $file_name;

    }

    $_POST['id_user'] = ID_USER;
    $user->settings_design( $_POST );
    $error = '<div class="alert alert-success">All good, design modified</div>';
  }

?>
<div class="page-head">
  <div class="page-title">
    <h1>Design</h1>
  </div>
</div>

<?php echo @$error; ?>

<div class="portlet box green">

  <div class="portlet-title">
    <div class="caption">
      <i class="fa fa-gift"></i>Edit design
    </div>
  </div>

  <div class="portlet-body form">
    <form action="/settings/design" method="post" class="form-horizontal" enctype="multipart/form-data">
      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="settings_logo" value="<?php echo $user->settings_logo; ?>" />
											<div class="form-body">
                        <div class="form-group">
                          <label class="control-label col-md-3">Logo <span class="required">*</span></label>
                          <div class="col-md-4">
                            <input type="file" name="upload" />
                            <hr />
                            <img src="<?php if( $user->settings_logo ): ?>/data/logos/<?php echo $user->settings_logo; ?><?php else: ?>http://placehold.it/100x50<?php endif; ?>" style="max-width: 100%;" />
                          </div>
                        </div>
												<div class="form-group">
													<label class="col-md-3 control-label">Primary color</label>
													<div class="col-md-4">
														<input type="text" class="form-control input-circle colorpicker" name="settings_primary_color" value="<?php echo $user->settings_primary_color; ?>" placeholder="Enter text">

													</div>
												</div>
												<div class="form-group">
													<label class="col-md-3 control-label">Secondary color</label>
													<div class="col-md-4">
															<input type="text" class="form-control input-circle colorpicker" name="settings_secondary_color" value="<?php echo $user->settings_secondary_color; ?>" placeholder="Email Address">
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
