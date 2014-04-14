<?php
if (isset($_GET['pie_name'])) {
    // get styles.
    include_once('pie-html.php');
} //show page for PIEs
else {
    ?>

    <?php
    /**
     * The Template for displaying all single posts
     *
     * @package WordPress
     * @subpackage Twenty_Fourteen
     * @since Twenty Fourteen 1.0
     */

    get_header(); ?>
    <style type="text/css">
        html, body, #page, #main {
            height: 100%;
        }

        iframe[seamless] {
            background-color: transparent;
            border: 0px none transparent;
            padding: 0px;
            overflow: hidden;
        }

    </style>
    <div id="primary" class="content-area" style="height: 100%; padding-top: 0">
        <div id="content" class="site-content" role="main" style="height: 100%;">
            <?php

            // Start the Loop.
            while (have_posts()) : the_post();



                ?>
                <span style="margin:0px;padding:0px;overflow:hidden;width:100%;height:100%">
                    <iframe id='pie_display' src='<?php echo esc_url(get_slide_location("demo", get_the_ID())); ?>'
                            frameborder="0"
                            style="overflow:hidden;height:100%;width:150%" height="100%" width="150%" seamless></iframe>
                </span>
                <?php
                // If comments are open or we have at least one comment, load up the comment template.
                if (comments_open() || get_comments_number()) {
                    comments_template();
                }
            endwhile;
            ?>
        </div>
        <!-- #content -->
    </div><!-- #primary -->

    <?php
    get_sidebar('content');
    get_sidebar();
    get_footer();

} //end if
