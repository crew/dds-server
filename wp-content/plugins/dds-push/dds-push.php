<?php
/*
Plugin Name: DDS Push Plugin
Plugin URI: http://crew.ccs.neu.edu/
Description:  Issues commands to dds-server for distribution
Version: 0.1
Author: Eddie Hurtig, Neil Locketz
Author URI: http://crew.ccs.neu.edu/people
*/

function dds_pie_init() {

}

add_action( 'init', 'dds_pie_init' );


function dds_slide_add($post_id){
    push_message_default_content($post_id, 'add-slide');
}
add_action('publish_slide', 'dds_slide_add');

function dds_slide_delete($post_id){
    push_message_default_content($post_id, 'delete-slide');
}

add_action('before_delete_post', 'dds_slide_delete');

function dds_slide_edit($post_id){
    push_message_default_content($post_id, 'edit-slide');
}
add_action('post-updated', 'dds-slide-edit');



function push_message_default_content($post_id, $action){
    $post = get_post($post_id);
    $content = (array) $post;
    $content['meta'] = get_post_meta($post_id);
    $content['permalink'] = get_post_permalink($post_id);

    push_message($post_id, $post, $action, $content);
}

function push_message($post_id, $post, $action, $content){
    if(wp_is_post_revision($post_id)) {
        return;
    }
    if(get_post_type($post_id) != 'slide'){
        return;
    }


    $pies = pies_for_slide($post);

    $pies = array_map(function ($pie) { return array( 'name' => $pie->post_title); }, $pies);


    // http
    $url = apply_filters('dds_push_endpoint', get_option('dds_push_endpoint', 'http://127.0.0.1:12345'));
    $data = array(
        'datetime' => date('c', time()),
        'action' => $action,
        'pies' => $pies,
        'content' => $content,

    );

    $json = json_encode($data);
    wp_remote_post($url, array('headers' => array('dataType' => 'jsonp'),'body' => $json));
}



