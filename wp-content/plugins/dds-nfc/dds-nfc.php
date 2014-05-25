/**
 * Register the post type "slide"
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function dds_person_init() {
  $labels = array(
    'name'               => _x( 'Slides', 'post type general name', 'dds-people' ),
    'singular_name'      => _x( 'Slide', 'post type singular name', 'dds-people' ),
    'menu_name'          => _x( 'Slides', 'admin menu', 'dds-people' ),
    'name_admin_bar'     => _x( 'Slide', 'add new on admin bar', 'dds-people' ),
    'add_new'            => _x( 'Add New', 'person', 'dds-people' ),
    'add_new_item'       => __( 'Add New Slide', 'dds-people' ),
    'new_item'           => __( 'New Slide', 'dds-people' ),
    'edit_item'          => __( 'Edit Slide', 'ddspeople-people' ),
    'view_item'          => __( 'View Slide', 'dds-people' ),
    'all_items'          => __( 'All Slides', 'dds-people' ),
    'search_items'       => __( 'Search Slides', 'dds-people' ),
    'parent_item_colon'  => __( 'Parent Slides:', 'dds-people' ),
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
    'rewrite'            => array( 'slug' => 'slide' ),
    'capability_type'    => 'post',
    'has_archive'        => false,
    'hierarchical'       => false,
    'supports'           => array( 'title', 'editor', 'author', 'thumbnail' ),
    'show_in_nav_menus'  => true,
    'taxonomies'         => array( 'category' ),
    'menu_position'      => 5
  );

  register_post_type( 'slide', $args );
}

add_action( 'init', 'dds_person_init' );
