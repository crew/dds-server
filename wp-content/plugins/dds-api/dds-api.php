<?php
/*
Plugin Name: DDS API Plugin
Plugin URI: http://crew.ccs.neu.edu/
Description:  Issues commands to dds-clients upon request (HTTP) and responds with a JSON for the queue.
Version: 0.1
Author: Lili Dumoulin, Teresa Krause, Eddie Hurtig, Crew
Author URI: http://crew.ccs.neu.edu/people
*/
/**
 * Suppressing any and all warnings if this is an API Call because you shouldn't be using DUMB plugins that throw errors... cough cough
 */
if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'dds_api' ) {
	error_reporting( 0 );
	@ini_set( 'display_errors', 'Off' );
}


/**
 * Registers the PIE custom post type that represents pies
 *
 * You can Edit the names (labels) of the 'PIEs' to something like ... hint hint ... 'Displays'
 */
function dds_pie_init() {

	// The Labels for the Custom Post Type PIE
	$labels = array(
		'name'               => _x( 'PIEs', 'post type general name', 'dds-api' ),
		'singular_name'      => _x( 'PIE', 'post type singular name', 'dds-api' ),
		'menu_name'          => _x( 'PIEs', 'admin menu', 'dds-api' ),
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

	// The General Arguments for the Custom Post Type PIE
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
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail' ),
		'show_in_nav_menus'  => true,
		'taxonomies'         => array( 'category' ),
		'menu_position'      => 5
	);

	register_post_type( 'PIE', $args );
}

add_action( 'init', 'dds_pie_init' );

/**
 * Handles the API call that each PIE makes to the DDS Server.  All API Requests get routed to this function.
 * This function creates an array of actions (or errors) and returns them to the PIE that is requesting updates
 *
 * All Requests must be sent to a URL Like this:
 *    http(s)://<DDS-SERVER>/wp-admin/admin-ajax.php?action=dds_api&pie_name=<pie_name>
 *
 * A Sample call to the API Looks like:
 *    http://10.0.0.61/wp-admin/admin-ajax.php?action=dds_api&pie_name=blueberry
 *
 * NOTE: The API Request MUST provide a pie_name argument in it's call.
 *
 * The API Respones with either a List of Actions or a List of Errors in JSON format Examples for both are provided here
 *
 *    Errors:  {"errors":[{"message":"invalid pie_name key_lime"}, ...]}
 *    Actions: {"actions":[{"type" : "slide", "location":"http:\/\/10.0.0.61\/?slide=slide1&pie_name=blueberry","duration":10}, ...]}
 *
 * To modify the List of actions to return use the 'dds_pie_actions' filter which provides:
 *    - $actions A list of actions
 *    - $pie_post The PIE post
 *    - $pie_name The PIE name
 *
 *
 * In your WordPress Plugin all you need is the following code:
 *
 * function your_function_name($actions, $pie_post, $pie_name) {
 *    ... Your Code Here (add to/modify $actions) ...
 *    return $actions;
 * }
 *
 * add_filter( 'dds_pie_actions', 'your_function_name', 10, 3 );
 *
 * To learn more about WordPress actions (not to be confused with PIE actions) and WordPress filters check out:
 *
 * @link https://codex.wordpress.org/Plugin_API
 *
 * Also as a note, to create a WordPress plugin see
 * @link https://codex.wordpress.org/Writing_a_Plugin
 */
function dds_api_call() {
	if ( ! isset( $_REQUEST['pie_name'] ) ) {
		wp_send_json( array( 'errors' => array( array( 'message' => '\'pie_name\' not set' ) ) ) );
	}
	$pie_name = $_REQUEST['pie_name'];


	$args = array(
		'name'             => $pie_name,
		// This is not the post title... this is the post's sanitized title... sooooooo problem
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

	$pie_posts = get_posts( $args );

	if ( empty( $pie_posts ) || count( $pie_posts ) > 1 ) {
		wp_send_json( array( 'errors' => array( array( 'message' => "invalid pie_name $pie_name" ) ) ) );
	}

	$pie_post = $pie_posts[0];

	$actions = apply_filters( 'dds_pie_actions', array(), $pie_post, $pie_name );

	$arr = array( 'actions' => $actions );

	wp_send_json( $arr );
}

add_action( 'wp_ajax_dds_api', 'dds_api_call' );
add_action( 'wp_ajax_nopriv_dds_api', 'dds_api_call' );

/**
 * Generates the location URL for the specified slide post.
 *
 * @param $pie     string the name of the PIE as a string
 * @param $post_id number the post to get location of
 *
 * @return string the location URL as a string
 */
function get_slide_location( $pie, $post_id ) {
	$external_url = get_post_meta( $post_id, 'dds_external_url', true );
	if ( $external_url && is_string( $external_url ) ) {
		return $external_url;
	} else {
		return add_query_arg( array( 'pie_name' => $pie ), get_permalink( $post_id ) );
	}
}

/**
 * Returns true if the current request is coming from a PIE via the dds-client
 *
 * (All Requests from the dds-client must provide a pie_name variable in the request)
 *
 *
 * @return bool Whether this request is coming from a PIE via the dds-client
 */
function is_pie_request() {
	return isset( $_REQUEST['pie_name'] );
}

