<?php
/*
Plugin Name: DDS API Plugin
Plugin URI: http://crew.ccs.neu.edu/
Description:  Issues commands to dds-clients upon request (HTTP) and responds with a JSON for the queue.
Version: 0.1
Author: LILILILIDUMOULIN//CREW//DUMOULINLILI
Author URI: http://crew.ccs.neu.edu/people
*/

add_action( 'wp_ajax_dds_api', 'dds_api_call');
add_action( 'wp_ajax_nopriv_dds_api', 'dds_api_call');
/**
 * Creates an array of posts and retrieves posts based on the given criteria.
 * To learn more about the get_posts command, check out this link:
 * @link https://codex.wordpress.org/Template_Tags/get_posts
 */
function dds_api_call() {
    if (!isset($_REQUEST['pie_name'])) {
	    wp_send_json(array('errors' => array( array( 'message' => '\'pie_name\' not set' ))));
    }

    $pie_name = $_REQUEST['pie_name'];

    $args = array(
        'name'             => $pie_name,  // This is not the post title... this is the post's sanitized title... sooooooo problem
        'posts_per_page'   => 1,
        'offset'           => 0,
        'category'         => '',
        'orderby'          => 'post_date',
        'order'            => 'DESC',
        'include'          => '',
        'exclude'          => '',
        'meta_key'         => '',
        'meta_value'       => '',
        'post_type'        => 'PIE',
        'post_mime_type'   => '',
        'post_parent'      => '',
        'post_status'      => 'publish',
	'suppress_filters' => true 
    );

    $pie_posts = get_posts($args);
    
    if (empty($pie_posts) || count($pie_posts) > 1 ) { 
	    wp_send_json(array('errors' => array(array('message' => 'invalid pie_name'))));
    }
    
    $pie_post = $pie_posts[0];

    $catids = wp_get_post_categories($pie_post->ID);


    $posts = array();

    foreach ($catids as $cur_category) {
        $slides = get_posts(array(
            'posts_per_page'   => -1,
            'category'         => $cur_category,
            'orderby'          => 'ID',
            'order'            => 'DESC',
            'post_type'        => 'slide',
            'post_status'      => 'publish',
            'suppress_filters' => false
        ));

        foreach ($slides as $slide) {
            if (!in_array($slide, $posts)) {
                $posts[] = $slide;
            }
        }
    }

    $actions = array();
    foreach ($posts as $p) {
        $actions[] = array('location' => get_permalink($p->ID) . '&pie_name=demo', 'duration' =>(float) get_post_meta($p->ID, 'duration', true));
    }

    $arr = array('actions' => $actions);

    wp_send_json($arr);
}
