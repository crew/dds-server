<ul>
<?php
/*
Plugin Name: DDS API Plugin
Plugin URI: http://crew.ccs.neu.edu/
Description:  Retrieves Post data (JSONs) from WordPress
Version: 0.1
Author: LILILILIDUMOULIN//CREW//DUMOULINLILI
Author URI: http://crew.ccs.neu.edu/people
*/

add_action( 'init', 'dds_api_init' );
/**
 * Creates an array of posts and retrieves posts based on the given criteria
 *
 * @link https://codex.wordpress.org/Template_Tags/get_posts
 */
function dds_api_init() {
    $args = array(
        'posts_per_page'   => 5,
        'offset'           => 1,
        'category'         => '',
        'orderby'          => 'ID',
        'order'            => 'DESC',
        'include'          => '',
        'exclude'          => '',
        'meta_key'         => 'duration',
        'meta_value'       => 'true',
        'post_type'        => 'Slide',
        'post_mime_type'   => '',
        'post_parent'      => '',
        'post_status'      => 'publish',
        'suppress_filters' => true
    );

    $myposts = get_posts( $args );

    foreach ( $myposts as $post ) : setup_postdata( $post ); ?>
    <li>
        <a href="<?php the_permalink(); ?>"><?php the_content(); ?></a>
    </li>
<?php endforeach;
wp_reset_postdata();
}
?>
</ul>