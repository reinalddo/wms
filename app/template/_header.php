<?php

  $user = new \User\User( ID_USER );

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->
  <head>

    <meta charset="utf-8"/>

    <title><?php echo SITE_TITLE; ?></title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>

    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <link href="<?php echo ST; ?>global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo ST; ?>global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo ST; ?>global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo ST; ?>global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo ST; ?>global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>

    <link href="<?php echo ST; ?>global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo ST; ?>global/plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo ST; ?>global/plugins/jqvmap/jqvmap/jqvmap.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo ST; ?>global/plugins/morris/morris.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ST; ?>global/plugins/bootstrap-summernote/summernote.css" rel="stylesheet" type="text/css" />

    <link href="<?php echo ST; ?>admin/pages/css/tasks.css" rel="stylesheet" type="text/css"/>

    <link href="<?php echo ST; ?>global/css/components-rounded.css" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="<?php echo ST; ?>global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo ST; ?>admin/layout4/css/layout.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo ST; ?>admin/layout4/css/themes/light.css" rel="stylesheet" type="text/css" id="style_color"/>
    <link href="<?php echo ST; ?>admin/layout4/css/custom.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo ST; ?>bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css" rel="stylesheet" type="text/css"/>

    <link rel="shortcut icon" href="favicon.ico"/>

  </head>
  <body class="page-header-fixed page-sidebar-closed-hide-logo page-sidebar-closed-hide-logo">

    <div class="page-header navbar navbar-fixed-top">
      <div class="page-header-inner">
        <div class="page-logo">
          <a href="/">
            <img src="<?php echo ST; ?>admin/layout4/img/logo-light.png" alt="logo" class="logo-default"/>
          </a>
          <div class="menu-toggler sidebar-toggler"></div>
		    </div>

        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"></a>

        <div class="page-top">
          <div class="top-menu">
            <ul class="nav navbar-nav pull-right">
              <li class="separator hide"></li>
              <li class="dropdown dropdown-user dropdown-dark">
                <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" style="padding: 27px 15px;">
                  <span class="username username-hide-on-mobile"><?php echo $user->name; ?></span>
      					</a>

                <ul class="dropdown-menu dropdown-menu-default">
                  <li>
                    <a href="/settings"><i class="icon-user"></i> Settings </a>
                  </li>
                  <li>
                    <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/f/<?php echo $user->identifier; ?>" target="_blank"><i class="fa fa-newspaper-o"></i> Public page</a>
                  </li>
                  <li class="divider"></li>
                  <li>
                    <a href="/account/out"><i class="icon-key"></i> Log Out </a>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>

      </div>
    </div>

    <div class="clearfix"></div>

    <div class="page-container">
      <div class="page-sidebar-wrapper">
        <div class="page-sidebar navbar-collapse collapse">

          <ul class="page-sidebar-menu " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">

            <li class="start <?php if( !$page ) { echo 'active'; } ?>">
              <a href="/">
                <i class="icon-home"></i>
                <span class="title">Dashboard</span>
              </a>
            </li>

            <li<?php if( $page == 'mail' ) { echo ' class="active"'; } ?>>
              <a href="javascript:;">
                <i class="icon-rocket"></i>
                <span class="title">Newsletters & Email</span>
                <span class="arrow "></span>
              </a>
              <ul class="sub-menu">
                <li><a href="/mail/press">New Press Release</a></li>
                <li><a href="/mail/upload">Upload & Send</a></li>
                <li><a href="/mail/invitations">Invite subscribers</a></li>
              </ul>
            </li>

            <li class="<?php if( $page == 'subscribers' ) { echo 'active'; } ?>">
              <a href="javascript:;">
                <i class="icon-envelope-open"></i>
                <span class="title">Subscribers</span>
                <span class="arrow "></span>
              </a>
              <ul class="sub-menu">
                <li><a href="/subscribers">Subscribers</a></li>
                <li><a href="/subscribers/lists">Lists</a></li>
                <li><a href="/subscribers/pending">Pending subscribers</a></li>
              </ul>
            </li>

            <li class="start <?php if( $page == 'statistics' ) { echo 'active'; } ?>">
              <a href="/statistics">
                <i class="icon-graph"></i>
                <span class="title">Statistics</span>
              </a>
            </li>

            <li class="last<?php if( $page == 'settings' ) { echo ' active'; } ?>">
              <a href="javascript:;">
                <i class="icon-settings"></i>
      					<span class="title">Settings</span>
      					<span class="arrow "></span>
              </a>
              <ul class="sub-menu">
                <li><a href="/settings"><i class="icon-home"></i> Data</a></li>
                <li><a href="/settings/design"><i class="icon-pencil"></i> Design</a></li>
                <li><a href="/settings/payments"><i class="icon-pencil"></i> Payments</a></li>
              </ul>
            </li>

          </ul>

        </div>
      </div>

      <div class="page-content-wrapper">
        <div class="page-content">
