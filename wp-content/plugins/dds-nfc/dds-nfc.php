<?php
/*
Plugin Name: DDS NFC plugin
Plugin URI: http://crew.ccs.neu.edu/
Description:  Manages storage for NFC communication/ profiles
Version: 0.1
Author: Teresa Krause, Eddie Hurtig, Crew
Author URI: http://crew.ccs.neu.edu/people
*/

/**
 * Register the post type "person"
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function dds_person_init() {
  $labels = array(
    'name'               => _x( 'People', 'post type general name', 'dds-people' ),
    'singular_name'      => _x( 'Person', 'post type singular name', 'dds-people' ),
    'menu_name'          => _x( 'People', 'admin menu', 'dds-people' ),
    'name_admin_bar'     => _x( 'Person', 'add new on admin bar', 'dds-people' ),
    'add_new'            => _x( 'Add New', 'person', 'dds-people' ),
    'add_new_item'       => __( 'Add New Person', 'dds-people' ),
    'new_item'           => __( 'New Person', 'dds-people' ),
    'edit_item'          => __( 'Edit Person', 'dds-people' ),
    'view_item'          => __( 'View Person', 'dds-people' ),
    'all_items'          => __( 'All People', 'dds-people' ),
    'search_items'       => __( 'Search People', 'dds-people' ),
    'parent_item_colon'  => __( 'Parent People:', 'dds-people' ),
    'not_found'          => __( 'No people found.', 'dds-people' ),
    'not_found_in_trash' => __( 'No people found in Trash.', 'dds-people' ),
  );

  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'rewrite'            => array( 'slug' => 'person' ),
    'capability_type'    => 'post',
    'has_archive'        => false,
    'hierarchical'       => false,
    'supports'           => array( 'custom-fields'),
    'show_in_nav_menus'  => false,
    'menu_position'      => 5,
    // 'capabilities'       => array(
    //               "manage_options",
    //               "manage_options",
    //               "manage_options",
    //               "manage_options",
    //               "manage_options",
    //               "manage_options",
    //               "manage_options"),
  );

  register_post_type( 'person', $args );
}

add_action( 'init', 'dds_person_init' );
