<!DOCTYPE html>
<html class="login-bg">
<head>
	<title>CCIS Digital Display System</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- bootstrap -->
    <link href="<?php echo plugins_url('css/bootstrap/bootstrap.css', __FILE__); ?>" rel="stylesheet" />
    <link href="<?php echo plugins_url('css/bootstrap/bootstrap-responsive.css', __FILE__); ?>" rel="stylesheet" />
    <link href="<?php echo plugins_url('css/bootstrap/bootstrap-overrides.css', __FILE__); ?>" type="text/css" rel="stylesheet" />

    <!-- global styles -->
    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url('css/layout.cssi', __FILE__); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url('css/elements.css', __FILE__); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url('css/icons.css', __FILE__); ?>" />

    <!-- libraries -->
    <link rel="stylesheet" type="text/css" href="<?php echo plugins_url('css/lib/font-awesome.css', __FILE__); ?>" />
    
    <!-- this page specific styles -->
    <link rel="stylesheet" href="<?php echo plugins_url('css/compiled/signin.css', __FILE__); ?>" type="text/css" media="screen" />

    <!-- open sans font -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css' />
<?php if (has_post_thumbnail(get_the_ID())) : ?>
<style>
.login-bg {
    background:url(<?php echo wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); ?>) no-repeat center center fixed; 
    background-size: cover;
}   
</style>
<?php endif; ?>
<!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
<body class="login-bg" style="font-size:2em;">
    <div class="login-wrapper" style="padding:10px;">
        <div class="row-fluid">
            <div class="span12">
                 <a href="index.html">
                    <img class="logo" src="<?php echo plugins_url('images/ne-logo-header-2x.png', __FILE__); ?>" />
                </a>
            </div> 
        </div>
        <div class="row-fluid page">
            <div class="span12 main">
                <div class="content-wrap">
                    <h1><?php the_title(); ?></h1>
                    <hr>
                    <p><?php the_content(); ?></p>
                </div>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span4 credits">
                <?php if (get_option('credit_crew', true) && get_post_meta(get_the_ID(), 'credit_crew', true) !== 'false' ) : ?>
        			<p>Powered by the CCIS Digital Display System. A Crew Project.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
