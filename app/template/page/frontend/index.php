<?php

  $user = new \User\User( false, $identifier );
  $templates = new \Templates\Templates();
  //$subscribers = new \Subscribers\Subscribers();

  if( $_POST['action'] == 'create_subscriber' ) {
    if( $_POST['email'] AND $_POST['first_name'] AND $_POST['last_name'] ) {
      try {

        \db()->beginTransaction();

        $_POST['id_user'] = $user->id_user;
        $result = $subscribers->save( $_POST );

        \db()->commit();

        $error = '<div class="alert alert-success">All good, subscriber added</div>';

      } catch( \ErrorException $e ) {

        \db()->rollback();
        echo $e->getMessage();

      }

    } else {
      $error = '<div class="alert alert-warning">Please fill out all the fields.</div>';
    }

  }

?>
<!DOCTYPE html>
<html itemscope="" itemtype="http://schema.org/Blog" lang="en-US"><!--<![endif]--><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="UTF-8">
	<title><?php echo $user->settings_company; ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="HandheldFriendly" content="True">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		
<link rel="stylesheet" id="ebor-roboto-font-css" href="/data/front/css" type="text/css" media="all">
<link rel="stylesheet" id="ebor-theme-layout-css" href="/data/front/caesar-layout.css" type="text/css" media="all">
<link rel="stylesheet" id="ebor-style-css" href="/data/front/style.css" type="text/css" media="all">
<style id="ebor-style-inline-css" type="text/css">

			.main-logo {
			  height: 56px;
			}
			a {
			  color: #1976d2;
			}
			a:hover {
			  color: #1565c0;
			}
			blockquote {
			  color: #1c1c1c;
			  background-color: #f0f0f0;
			  border-left: 4px solid #1976d2;
			}
			code {
			  border-radius: 2px;
			  color: #1c1c1c;
			  background-color: #f0f0f0;
			}
			ul,
			ol {
			  color: #1c1c1c;
			}
			li:before {
			  color: #999999;
			}
			pre {
			  color: #f0f0f0;
			  background-color: #1c1c1c;
			}
			h1,
			h2,
			h3,
			h4,
			h5,
			h6,
			.h1,
			.h2,
			.h3,
			.h4,
			.h5,
			.h6,
			.quote,
			.post-title,
			.post--link .post-body p:first-of-type > a,
			p.post-more,
			.post--quote .post-body blockquote:first-of-type {
			  font-family: Roboto;
			  font-weight: 500;
			  color: #1c1c1c;
			}
			body,
			.p {
			  font-family: Roboto;
			  font-weight: 300;
			  color: #4a4a4a;
			}
			strong,
			b {
			  font-weight: 500;
			}
			.dialogue,
			.post--chat .post-body ul:first-of-type {
			  color: #1c1c1c;
			}
			.dialogue-line--alt,
			.post--chat .post-body ul:first-of-type > li:nth-child( odd ) {
			  color: #4a4a4a;
			}
			.dialogue-title,
			.post--chat .post-body ul:first-of-type > li > strong {
			  color: #999999;
			}
			.footnotes {
			  color: #999999;
			}
			.footnotes-link,
			.post-body [href^="#fn"] {
			  border-bottom: none;
			}
			.meta {
			  color: #999999;
			  text-transform: capitalize;
			}
			.meta-action:before,
			.meta-item:before {
			  color: #b8b8b8;
			}
			.meta-item:hover {
			  color: #707070;
			}
			.meta-action:hover:before,
			.meta-item:hover:before,
			.meta-shares[data-js~="click-active"]:before {
			  color: #999999;
			}
			.nav {
			  color: #616161;
			}
			.nav-item:hover {
			  color: #1c1c1c;
			}
			.overlay,
			.msg,
			.post--overlay .meta {
			  color: #ededed;
			}
			.overlay-inner,
			.msg {
			  background: rgba(25,118,210, 0.9);
			}
			.overlay-item:before,
			.msg-back:after,
			.post--overlay .meta-action:before,
			.post--overlay .meta-item:before,
			.post--overlay .post-tags,
			.post--overlay .post-tags .nav-item:before {
			  color: #e8e8e8;
			}
			.overlay-item:hover,
			.msg-back:hover,
			.post--overlay .meta-item:hover {
			  color: #ffffff;
			}
			.overlay-item:hover:before,
			.msg-back:hover:after,
			.post--overlay .meta-action:hover:before,
			.post--overlay .meta-item:hover:before,
			.post--overlay .meta-shares[data-js~="click-active"]:before,
			.post--overlay .post-tags .nav-item:hover,
			.post--overlay .post-tags .nav-item:hover:before {
			  color: #fafafa;
			}
			.popup {
			  color: #999999;
			  background-color: #ffffff;
			  border-radius: 3px;
			  box-shadow: 0 1px 4px 1px rgba(0, 0, 0, 0.075);
			  border: 1px solid #e8e8e8;
			}
			.popup:before {
			  border: 8px solid transparent;
			  border-bottom-color: #e8e8e8;
			}
			.popup:after {
			  border: 6px solid transparent;
			  border-bottom-color: #ffffff;
			}
			.popup-item {
			  color: inherit;
			  text-transform: capitalize;
			  border-bottom: 1px solid #e8e8e8;
			}
			.popup-item:hover {
			  color: #707070;
			}
			.popup-item:last-child {
			  border-bottom: 0;
			}
			.quote,
			.post--quote .post-body blockquote:first-of-type {
			  border: 0;
			  background-color: transparent;
			}
			.quote-source,
			.post--quote .post-body blockquote:first-of-type + p {
			  color: #4a4a4a;
			}
			::-moz-selection{
			  background: #1976d2;
			  color: #1c1c1c;
			}
			::selection {
			  background: #1976d2;
			  color: #1c1c1c;
			}
			::-moz-selection {
			  background: #1976d2;
			  color: #1c1c1c;
			}
			body,
			.popup ul ul {
			  background-color: #ffffff;
			}
			.main:after {
			  box-shadow: inset -1px 1px 4px 0 rgba(0, 0, 0, 0.075);
			  border-top: 1px solid #e8e8e8;
			}
			.main-title {
			  color: #1c1c1c;
			}
			.main-post,
			.post-item,
			.post-body hr {
			  border-bottom: 1px solid #e8e8e8;
			}
			.main-item:last-child,
			.post-item:last-child {
			  border-bottom: 0;
			}
			.main-nav,
			.main-nav .popup {
			  color: #616161;
			}
			.main-nav:hover,
			.main-nav[data-js*="click-active"],
			.main-nav .popup-item:hover {
			  color: #1c1c1c;
			}
			.main-current,
			.main-current:hover {
			  color: #999999;
			}
			@media (min-width: 1169px) {
			  .main:after {
			    box-shadow: inset 1px -1px 4px 0 rgba(0, 0, 0, 0.075);
			    border-bottom: 0;
			    border-left: 1px solid #e8e8e8;
			  }
			  .main-nav .popup {
			    border: none;
			    box-shadow: none;
			    background: none;
			  }
			  .main-nav .popup-item {
			    border-bottom: none;
			  }
			}
			.post-icon {
			  color: #1976d2;
			}
			.post-body p a,
			.quote-source a,
			.post--quote .post-body blockquote:first-of-type + p a {
			  border-bottom: 1px solid rgba(25, 118, 210, 0.3);
			}
			.post-body p a:hover {
			  border-bottom-color: #1565c0;
			}
			p.post-more a {
			  border-bottom: 0;
			  color: inherit;
			}
			.post-body mark {
			  color: #f0f0f0;
			  background-color: #1976d2;
			}
			.post-tags,
			.post-tags .nav-item:before {
			  color: #b8b8b8;
			}
			.post-tags .nav-item:hover,
			.post-tags .nav-item:hover:before {
			  color: #999999;
			}
			.post--chat .post-body ul:first-of-type > li > strong {
			  font-weight: 300;
			}
			.post--chat .post-body ul:first-of-type > li > em {
			  font-style: normal;
			}
			.post--link .post-body p:first-of-type > a {
			  border-bottom: 0;
			}
			.sidebar {
			  color: #7d7d7d;
			}
			.sidebar-title {
			  color: #5c5c5c;
			}
			.sidebar-item .nav,
			.sidebar a {
			  color: #7d7d7d;
			}
			.sidebar-item .nav-item:hover,
			.sidebar a:hover {
			  color: #5c5c5c;
			}
			.sidebar-credits .nav {
			  color: #b8b8b8;
			}
			.sidebar-credits .nav-item:hover {
			  color: #7d7d7d;
			}
			textarea,
			input[type="text"],
			input[type="password"],
			input[type="datetime"],
			input[type="datetime-local"],
			input[type="date"],
			input[type="month"],
			input[type="time"],
			input[type="week"],
			input[type="number"],
			input[type="email"],
			input[type="url"],
			input[type="search"],
			input[type="tel"],
			input[type="color"],
			input[type="submit"] {
				border-bottom: 1px solid #e8e8e8;
			}
		/* minimal
*******************************************************************************/
button.minimal {
  background: #e3e3e3;
  border: 1px solid #bbb;
  border-radius: 3px;
  -webkit-box-shadow: inset 0 0 1px 1px #f6f6f6;
  box-shadow: inset 0 0 1px 1px #f6f6f6;
  color: #333;
  font: bold 12px/1 "helvetica neue", helvetica, arial, sans-serif;
  padding: 8px 0 9px;
  text-align: center;
  text-shadow: 0 1px 0 #fff;
  width: 150px; }
  button.minimal:hover {
    background: #d9d9d9;
    -webkit-box-shadow: inset 0 0 1px 1px #eaeaea;
    box-shadow: inset 0 0 1px 1px #eaeaea;
    color: #222;
    cursor: pointer; }
  button.minimal:active {
    background: #d0d0d0;
    -webkit-box-shadow: inset 0 0 1px 1px #e3e3e3;
    box-shadow: inset 0 0 1px 1px #e3e3e3;
    color: #000; }
    .date2{color:#ff0000;font-weight:bold;text-align:right;}
    #facebookicono{width:25px;margin-right:5px;}
    #twittericono{width:25px;}
    #formulario{width:90%;margin:0 auto;}
    #suscribete{width:100%;text-align:center;}
    #textoformulario{float:left;width:30%;font-size:0.95em;line-height: 2.7em;margin:0;}
    #camposform{float:right;width:69%;line-height: 50%;}
    #campo{padding: 8px;border: 1px solid #cccccc;border-radius: 3px;margin-bottom: 10px;width: 90%;box-sizing: border-box;color: #2C3E50;font-size: 13px;}
    #footertext{text-align:center;margin-top:10px;margin-bottom:15px;font-size:13px;}
</style>
</head>

<body class="home blog body">


<div class="wrapper body-wrapper">
	<div class="main" data-js="main main-start">
		<div class="wrapper main-wrapper">
		
			<header role="header" class="main-item main-header" data-js="main-item">
				<div class="wrapper">
				
											<h1 itemprop="name" class="main-title">
							<a itemprop="url" href="#" title="Audi">
											<img src="<?php if( $user->settings_logo ): ?>/data/logos/<?php echo $user->settings_logo; ?><?php else: ?>http://placehold.it/100x50<?php endif; ?>" style="height:100px;">				</a>
						</h1>
										
					
				
				</div>
			</header>
<article>
	<div class="wrapper post-wrapper">
	
		
		
		<?php foreach( $templates->getList( $user->id_user, 'PRESS' ) AS $t ): ?>
		<section class="post-item post-body post-content">
		<p class="date2">29/01/2016</p>
			<!--<h2 class="post-title"><a itemprop="url" href="/f/<?php /*echo $identifier; */?>/<?php /*echo $t->id_template; */?>" title="--><?php /*echo $t->title; */?>
			<h2 class="post-title"><a itemprop="url" href="/<?php echo $identifier; ?>/<?php echo $t->id_template; ?>/" title="<?php echo $t->title; ?>”
"><?php echo $t->title; ?>
</a></h2>
<p class="post-excerpt"><?php echo $t->content; ?></p>

<p class="post-more"><a href="#" class="icon-ui icon-ui-next">Leer</a></p>
		</section>
		<?php endforeach; ?>
		
			
	</div>
</article>
	</div><!--/wrapper-->
	</div><!--/main-->

	<div class="sidebar" data-js="sidebar sidebar-start">
	<div class="wrapper sidebar-wrapper">
		<div id="text-2" class="sidebar-item sidebox widget widget_text">			<div class="nav nav--icons"><a href="#" target="_blank"><img src="/data/front/facebook500.png" id="facebookicono"></a> <a href="#" target="_blank"><img src="/data/front/twitter.png" id="twittericono"></a></div>
		</div>	<div id="suscribete">
			<h4>Suscribete al Press Room</h4>

			<?php echo @$error; ?>
        <!--<form method="post" action="http://<?php /*echo $_SERVER['HTTP_HOST']; */?>/f/<?php /*echo $user->identifier; */?>">-->
        <form method="post" action="http://<?php echo $_SERVER['HTTP_HOST']; ?>/<?php echo $user->identifier; ?>/">
			<input type="hidden" name="action" value="create_subscriber" />
				<div id="formulario">
		 			<div id="textoformulario"><p>Nombre:<br /> Apellido: <br /> Medio: <br /> Email: </p></div>
					 <div id="camposform"><input type="text" name="first_name" id="campo" /><br /><input type="text" name="last_name" id="campo" /><br /><input type="text" name="media" id="campo" /><br /><input type="text" name="email" id="campo"/><br />
      				</div><div style="clear:both;"></div>
				</div>
				
			<button type="submit" class="minimal">Sucribirme</button>
			</form>
		</div>
		
		<!-- <div style="width:100%;text-align:center;margin-top:30px;">
			<h4>Media Contact</h4>
			<div style="font-size:14px;">
				<p><strong>Director de Prensa</strong> <br />Pablo Perez <br />+5411555555<br /> pperez@gmail.com
			</div>
		</div> -->
</div>
</div><!--/wrapper-->
</div><div id="footertext">Powered by <a href="#">PressHunters</a></div>

</body></html>