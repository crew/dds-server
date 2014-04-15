<html>
<head>
    <title>CCIS Digital Display System</title>
    <?php

    add_action('wp_enqueue_scripts', 'dds_mods_remove_twentyfourteen_css', 25);

    wp_head();
    ?>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            border: 0;
        }
    </style>
    <?php
        $theme = get_post_meta(get_the_ID(), 'dds_theme', true);
        if ($theme) { ?>
            <link href="<?php echo esc_url($theme); ?>" rel="stylesheet" media="screen">
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
