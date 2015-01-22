<?php 

$theme = get_post_meta(get_the_ID(), 'dds_theme', true);
if (file_exists( WP_PLUGIN_DIR . '/dds-slides/themes/' . $theme . '/index.php' )) {
	if (have_posts()) : 
		the_post(); 
	endif;
	include(WP_PLUGIN_DIR . '/dds-slides/themes/' . $theme . '/index.php');
} else {
?>
<html>
<head>
    <title>CCIS Digital Display System</title>
    <?php

    wp_head();
    ?>
    <style>
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
        }
    </style>
    <?php
        if ($theme) { ?>
            <link href="<?php echo plugins_url("dds-slides/themes/$theme/style.css"); ?>" rel="stylesheet" media="screen">
        <?php } ?>

</head>

<body>
    <div id="primary" class="content-area">
    <div id="content" class="site-content" role="main">
<?php

    // Start the Loop.
    while ( have_posts() ) : the_post();

        /*
        * Include the post format-specific template for the content. If you want to
        * use this in a child theme, then include a file called called content-___.php
        * (where ___ is the post format) and that will be used instead.
        */
        get_template_part( 'content', get_post_format() );

    endwhile;
?>
    </div><!-- #content -->
    </div><!-- #primary -->
</body>
</html>
<?php 
}

