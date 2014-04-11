SINGLE-SLIDE.PHP
<?php
if (isset($_GET['pie_name'])) { include_once('pie-html.php'); } //show page for PIEs
else { ?>

<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */

get_header(); ?>

        <div id="primary" class="content-area">
                <div id="content" class="site-content" role="main">
                        <?php
                                // Start the Loop.
                                while ( have_posts() ) : the_post();
 					echo "<iframe id='pie_display' width='100%' height='100%' src='".esc_url(get_permalink())."&pie_name=demo'></iframe>";
                                        /*
                                         * Include the post format-specific template for the content. If you want to
                                         * use this in a child theme, then include a file called called content-___.php
                                         * (where ___ is the post format) and that will be used instead.
                                         */
                                        //get_template_part( 'content', get_post_format() );

                                        // Previous/next post navigation.
                                        //twentyfourteen_post_nav();

                                        // If comments are open or we have at least one comment, load up the comment template.
                                        if ( comments_open() || get_comments_number() ) {
                                                comments_template();
                                        }
                                endwhile;
                        ?>
                </div><!-- #content -->
        </div><!-- #primary -->

<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();










} //end if
