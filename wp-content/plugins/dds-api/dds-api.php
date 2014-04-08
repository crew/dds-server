<ul>
<?php
/*
Plugin Name: DDS API Plugin
Plugin URI: http://crew.ccs.neu.edu/
Description:  Issues commands to dds-clients upon request (HTTP) and responds with a JSON list for the queue
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
/**
*note for later: add an abstraction here that links this and the list of slides plugin
 */
    $myposts = get_posts( $args );

    /**
    * I'm not sure if I need to use a foreach loop here, or really how we're going to call the actions for each
     * of the slides without changing it all manually. I don't know enough about WordPress or PHP at the moment
     * to do this, so I'm leaving in the template Eddie wrote for the time being. I'll go in and replace it all later
    */
    $arr = array(
       'actions' =>
                    array(
                    array('url' => <url>, 'duration' => <duration>, ...),
                    array('url' => <url>, 'duration' => <duration>, ...),
                    array('url' => <url>, 'duration' => <duration>, ...)
    );

    wp_send_json($arr);

    foreach ( $myposts as $post ) : setup_postdata( $post ); ?>
    <li>
        <a href="<?php the_permalink(); ?>"><?php the_content(); ?></a>
    </li>
<?php endforeach;
wp_reset_postdata();
}
?>
</ul>