<?php
/*
Plugin Name: DDS UI Modifications Plugin
Plugin URI: http://crew.ccs.neu.edu/
Description:  UI modifications for DDS
Version: 0.1
Author: Eddie Hurtig
Author URI: http://crew.ccs.neu.edu/people
*/



/**
 * Init method for General Wordpress Mods
 */
function dds_mods_init() {
    global $wp_taxonomies;


    /* Rename 'Categories' to 'Groups' */

    //  The list of labels we can modify comes from
    //  http://codex.wordpress.org/Function_Reference/register_taxonomy
    //  http://core.trac.wordpress.org/browser/branches/3.0/wp-includes/taxonomy.php#L350
    $wp_taxonomies['category']->labels = (object)array(
        'name' => 'Groups',
        'menu_name' => 'Groups',
        'singular_name' => 'Group',
        'search_items' => 'Search Groups',
        'popular_items' => 'Popular Groups',
        'all_items' => 'All Groups',
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => 'Edit Group',
        'update_item' => 'Update Group',
        'add_new_item' => 'Add new Group',
        'new_item_name' => 'New Group',
        'separate_items_with_commas' => 'Separate groups with commas',
        'add_or_remove_items' => 'Add or remove Groups',
        'choose_from_most_used' => 'Choose from the most used groups',
    );

    $wp_taxonomies['category']->label = 'Groups';


}

add_action('init', 'dds_mods_init');

/**
 * Removes unnessesary admin menu items from the Wordpress Admin For non-administrators
 */
function dds_mods_remove_menus() {
    if (is_user_logged_in()) {
        // Getting the Role of the Currently Logged In User
        global $current_user, $wpdb;
        $role = $wpdb->prefix . 'capabilities';
        $current_user->role = array_keys($current_user->$role);
        $role = $current_user->role[0];


        if ($role != 'administrator') {
            remove_menu_page('edit.php');
            remove_menu_page('upload.php');
            remove_menu_page('edit.php?post_type=page');
            remove_menu_page('edit.php?post_type=pie');
            remove_menu_page('edit-comments.php');
            remove_menu_page('tools.php');

        }
    }
}

add_action('admin_menu', 'dds_mods_remove_menus');