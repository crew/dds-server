<?php
/*
Plugin Name: DDS API Plugin
Plugin URI: http://crew.ccs.neu.edu/
Description:  Issues commands to dds-clients upon request (HTTP) and responds with a JSON for the queue.
Version: 0.1
Author: LILILILIDUMOULIN//CREW//DUMOULINLILI
Author URI: http://crew.ccs.neu.edu/people
*/

add_action( 'wp_ajax_nopriv_dds_api', 'dds_api_call' );
/**
 * Creates an array of posts and retrieves posts based on the given criteria.
 * To learn more about the get_posts command, check out this link:
 * @link https://codex.wordpress.org/Template_Tags/get_posts
 */
function dds_api_call() {
    $query_args = array(
        'posts_per_page'   => -1,
        'category'         => '',
        'orderby'          => 'ID',
        'order'            => 'DESC',
        // 'meta_key'         => 'duration',
        // 'meta_value'       => 'true',
        'post_type'        => 'slide',
        'post_status'      => 'publish',
        'suppress_filters' => false
    );
/**
 * TODO: add an abstraction here that links this and the list of slides plugin
 */
    $myposts = get_posts( $query_args );

    $actions = array();
    foreach ($myposts as $p) {
        $actions[] = array('location' => get_permalink($p->ID) . '&pie=true', 'duration' =>(float) get_post_meta($p->ID, 'duration', true));
    }

    $arr = array('actions' => $actions);

    wp_send_json($arr);
}
