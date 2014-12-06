<?php
/*
Plugin Name: DDS Push Plugin
Plugin URI: http://crew.ccs.neu.edu/
Description:  Issues commands to dds-server for distribution
Version: 0.1
Author: Eddie Hurtig, Neil Locketz, Philip Blair
Author URI: http://crew.ccs.neu.edu/people
*/
/*
function dds_pie_init() {

}

add_action( 'init', 'dds_pie_init' );
*/

// Called when a slide is created or published
function dds_slide_add($post_id){
    push_message_default_content($post_id, 'add-slide');
}
add_action('slide-published', 'dds_slide_add');

// Called when a slide is deleted
function dds_slide_delete($post_id){
    push_message_default_content($post_id, 'delete-slide');
}

add_action('before_delete_post', 'dds_slide_delete');

// Called when a slide is edited
function dds_slide_edit($post_id){
    push_message_default_content($post_id, 'edit-slide');
}
add_action('slide-updated', 'dds_slide_edit');

// Handles routing for adding and updating slides
//  (Needed or else Pies will always get an add-slide
//   message when a slide is edited)
function dds_add_update_switchboard($new_status, $old_status, $post_id){
    if ($post_id->post_type != 'slide'){ return; }
    if (($old_status == 'publish') && ($old_status == $new_status)){
      do_action('slide-updated', $post_id->ID);
    }
    else{
      do_action('slide-published', $post_id->ID);
    }
}
add_action('transition_post_status','dds_add_update_switchboard',10,3);

// dds_add_pies
// Called when a slide's groups are changed.
// If there are any new groups, add the slide to
//   that group's pies. Otherwise, do nothing.
function dds_add_pies($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids){
    if ($taxonomy != "category") { return; }
    $to_comp = array_map(function ($i) { return strval($i); }, $old_tt_ids);
    $to_add  = array_map(function ($s) { return (intval($s) - 1); }, array_diff($tt_ids,$to_comp));
    if (empty($to_add)){ return; }
    $output = array();

    // (Copied from dds-slide)
    foreach ( $to_add as $pie_group ) {
        // Get The Pies that are assigned to $pie_group
        $pies = get_posts( array(
            'posts_per_page'   => - 1,
            'category'         => $pie_group,
            'orderby'          => 'ID',
            'order'            => 'DESC',
            'post_type'        => 'pie',
            'post_status'      => 'publish',
            'suppress_filters' => false
        ) );
        // List through the slides, If the slide hasn't already been enqueued then add it to $queue
        foreach ( $pies as $pie ) {
            if ( ! in_array( $pie, $output ) ) {
                $output[] = array('name' => $pie->post_title);
            }
        }
    }
    push_message_default_content($object_id, 'add-slide', $output);

}

// dds_remove_pies
// Called when a slide's groups are changed.
// If there are any groups no longer included,
//   remove the slide from that group's pies. 
//   Otherwise, do nothing.
function dds_remove_pies($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids){
    if ($taxonomy != "category") { return; }
    $to_comp = array_map(function ($i) { return strval($i); }, $old_tt_ids);
    $to_rem  = array_map(function ($s) { return (intval($s) - 1); }, array_diff($to_comp,$tt_ids));
    if (empty($to_rem)){ return; }
    $output = array();

    // (Copied from dds-slide)
    foreach ( $to_rem as $pie_group ) {
        // Get The Pies that are assigned to $pie_group
        $pies = get_posts( array(
            'posts_per_page'   => - 1,
            'category'         => $pie_group,
            'orderby'          => 'ID',
            'order'            => 'DESC',
            'post_type'        => 'pie',
            'post_status'      => 'publish',
            'suppress_filters' => false
        ) );
        // List through the slides, If the slide hasn't already been enqueued then add it to $queue
        foreach ( $pies as $pie ) {
            if ( ! in_array( $pie, $output ) ) {
                $output[] = array('name' => $pie->post_title);
            }
        }
    }

    push_message_default_content($object_id, 'delete-slide', $output);

}
add_action('set_object_terms','dds_remove_pies',10,6);
add_action('set_object_terms','dds_add_pies',10,6);

function push_message_default_content($post_id, $action, $dest_pies = null){
    $post = get_post($post_id);
    $content = (array) $post;
    $content['meta'] = get_post_meta($post_id);
    $content['permalink'] = get_post_permalink($post_id);
    $content['duration'] = (float) get_post_meta( $post->ID, 'dds_duration', true );

    push_message($post_id, $post, $action, $content, $dest_pies);
}

function push_message($post_id, $post, $action, $content, $dest_pies = null){
    if(wp_is_post_revision($post_id)) {
        return;
    }
    if(get_post_type($post_id) != 'slide'){
        return;
    }

    if($dest_pies == null) {
      $pies = pies_for_slide($post);

      $pies = array_map(function ($pie) { return array( 'name' => $pie->post_title); }, $pies);
    }
    else {
      $pies = $dest_pies;
    }

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



