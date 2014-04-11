<?php
/*
Plugin Name: DDS Slides Plugin
Plugin URI: http://crew.ccs.neu.edu/
Description:  Adds slides capability to DDS
Version: 0.1
Author: TERESATERESAKRAUSE//CREW//KRAUSETERESA
Author URI: http://crew.ccs.neu.edu/people
*/

add_action( 'init', 'dds_slide_init' );
add_action('init', 'dds_pie_init');
/**
 * Register the post type "slide"
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */

function dds_slide_init() {
    $labels = array(
        'name'               => _x( 'Slides', 'post type general name', 'dds-slides' ),
        'singular_name'      => _x( 'Slide', 'post type singular name', 'dds-slides' ),
        'menu_name'          => _x( 'Slides', 'admin menu', 'dds-slides' ),
        'name_admin_bar'     => _x( 'Slide', 'add new on admin bar', 'dds-slides' ),
        'add_new'            => _x( 'Add New', 'slide', 'dds-slides' ),
        'add_new_item'       => __( 'Add New Slide', 'dds-slides' ),
        'new_item'           => __( 'New Slide', 'dds-slides' ),
        'edit_item'          => __( 'Edit Slide', 'dds-slides' ),
        'view_item'          => __( 'View Slide', 'dds-slides' ),
        'all_items'          => __( 'All Slides', 'dds-slides' ),
        'search_items'       => __( 'Search Slides', 'dds-slides' ),
        'parent_item_colon'  => __( 'Parent Slides:', 'dds-slides' ),
        'not_found'          => __( 'No slides found.', 'dds-slides' ),
        'not_found_in_trash' => __( 'No slides found in Trash.', 'dds-slides' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'slide' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'supports'           => array( 'title', 'editor', 'author', 'thumbnail'),
        'show_in_nav_menus'  => true,
        'taxonomies'         => array('category'),
        'menu_position'      => 5
    );

    register_post_type( 'slide', $args );
}

function dds_pie_init() {
    $labels = array(
        'name'               => _x( 'PIEs', 'post type general name', 'dds-api'),
        'singular_name'      => _x( 'PIE', 'post type singular name', 'dds-api' ),
        'menu_name'          => _x( 'PIEs', 'admin menu', 'dds-api'),
        'name_admin_bar'     => _x( 'PIE', 'add new on admin bar', 'dds-api' ),
        'add_new'            => _x( 'Add New', 'slide', 'dds-api' ),
        'add_new_item'       => __( 'Add New PIE', 'dds-api' ),
        'new_item'           => __( 'New PIE', 'dds-api' ),
        'edit_item'          => __( 'Edit PIE', 'dds-api' ),
        'view_item'          => __( 'View PIE', 'dds-api' ),
        'all_items'          => __( 'All PIEs', 'dds-api' ),
        'search_items'       => __( 'Search PIEs', 'dds-api' ),
        'parent_item_colon'  => __( 'Parent PIEs:', 'dds-api' ),
        'not_found'          => __( 'No PIEs found.', 'dds-api' ),
        'not_found_in_trash' => __( 'No PIEs found in Trash.', 'dds-api' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'slide' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'supports'           => array( 'title', 'editor', 'author', 'thumbnail'),
        'show_in_nav_menus'  => true,
        'taxonomies'         => array('category'),
        'menu_position'      => 5
    );

    register_post_type( 'PIE', $args );
}