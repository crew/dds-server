<?php
/*
Plugin Name: DDS UI Modifications Plugin
Plugin URI: http://crew.ccs.neu.edu/
Description:  UI modifications for DDS
Version: 0.1
Author: Eddie Hurtig <hurtige@ccs.neu.edu>, Crew
Author URI: http://crew.ccs.neu.edu/members
*/

/**
 * Init method for General WordPress Mods
 */
function dds_mods_init() {
	global $wp_taxonomies;

	/* Rename 'Categories' to 'Groups' */
	if (strpos($_SERVER['REQUEST_URI'], '/wp-admin/index.php') !== false ) {
		wp_redirect('/wp-admin/edit.php?post_type=slide', 302);
		exit;
	}

    //  Changing the name of The Default WordPress 'Categories' to 'Groups'
    //  STYLE NOTE: Should Register a custom taxonomy and leave WordPress Categories alone... Future Project
	//  The list of labels we can modify comes from
	//  http://codex.wordpress.org/Function_Reference/register_taxonomy
	//  http://core.trac.wordpress.org/browser/branches/3.0/wp-includes/taxonomy.php#L350
	$wp_taxonomies['category']->labels = (object) array(
		'name'                       => 'Groups',
		'menu_name'                  => 'Groups',
		'singular_name'              => 'Group',
		'search_items'               => 'Search Groups',
		'popular_items'              => 'Popular Groups',
		'all_items'                  => 'All Groups',
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => 'Edit Group',
		'update_item'                => 'Update Group',
		'add_new_item'               => 'Add new Group',
		'new_item_name'              => 'New Group',
		'separate_items_with_commas' => 'Separate groups with commas',
		'add_or_remove_items'        => 'Add or remove Groups',
		'choose_from_most_used'      => 'Choose from the most used groups',
	);

	$wp_taxonomies['category']->label = 'Groups';
}

add_action( 'init', 'dds_mods_init' );

/**
 * Removes unnessesary admin menu items from the WordPress Admin For non-administrators
 */
function dds_mods_remove_menus() {
	if ( is_user_logged_in() ) {
		// Getting the Role of the Currently Logged In User
		global $current_user, $wpdb;
		$role               = $wpdb->prefix . 'capabilities';
		$current_user->role = array_keys( $current_user->$role );
		$role               = $current_user->role[0];

		if ( $role != 'administrator' ) {
			remove_menu_page( 'edit.php' );
			remove_menu_page( 'upload.php' );
			remove_menu_page( 'edit.php?post_type=page' );
			remove_menu_page( 'edit.php?post_type=pie' );
			remove_menu_page( 'edit-comments.php' );
			remove_menu_page( 'tools.php' );
			remove_menu_page( 'edit.php?post_type=person' );
		}
	}
}

add_action( 'admin_menu', 'dds_mods_remove_menus' );


/**
 * Forces Metaboxes to be hidden
 *
 * @param $hidden The already hidden metabox slugs
 * @param $screen The current screen that the metaboxes will be shown on
 *
 * @author Eddie Hurtig <hurtige@sudbury.ma.us>
 *
 * @return array An array of the new hidden metabox slugs
 */
function sudbury_hide_meta_boxes( $hidden, $screen ) {
	if ( 'post' == $screen->base ) {
		$hidden = array( 'discussion-settings',
			'formatdiv',
			'slugdiv',
			'trackbacksdiv',
			'commentstatusdiv',
			'commentsdiv',
			'authordiv',
		);
	} elseif ( 'dashboard' == $screen->base ) {
		wp_redirect('edit.php?post_type=slide', 302);
		exit; 
		$hidden = array( 'dashboard_right_now',
			'dashboard_recent_comments',
			'dashboard_incoming_links',
			'dashboard_recent_drafts',
			'dashboard_primary',
			'dashboard_secondary' );
	}


	return $hidden;
}

// Potentially use default_hidden_meta_boxes filter... future
add_filter( 'hidden_meta_boxes', 'sudbury_hide_meta_boxes', 10, 2 );

/**
 * Removes the nagging out-of-date browser notifications on the WordPress dashboard
 *
 * @author Eddie Hurtig <hurtige@sudbury.ma.us>, Town of Sudbury
 */
// WE MAKE WORDPRESS ANGRY!!!!!
function sudbury_remove_browse_happy_nag() {
	global $wp_meta_boxes;
	unset( $wp_meta_boxes['dashboard']['normal']['high']['dashboard_browser_nag'] );
	unset( $wp_meta_boxes['dashboard-network']['normal']['high']['dashboard_browser_nag'] );
}

add_action( 'wp_dashboard_setup', 'sudbury_remove_browse_happy_nag' );
add_action( 'wp_network_dashboard_setup', 'sudbury_remove_browse_happy_nag' );
