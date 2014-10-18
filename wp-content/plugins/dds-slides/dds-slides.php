<?php
/*
Plugin Name: DDS Slides Plugin
Plugin URI: http://crew.ccs.neu.edu/
Description:  Adds slides capability to DDS
Version: 0.1
Author: Teresa Krause, Eddie Hurtig, Crew
Author URI: http://crew.ccs.neu.edu/people
*/

define( 'MAX_SLIDE_DURATION', 60 );

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
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail' ),
		'show_in_nav_menus'  => true,
		'taxonomies'         => array( 'category' ),
		'menu_position'      => 5
	);

	register_post_type( 'slide', $args );
}

add_action( 'init', 'dds_slide_init' );


/**
 * Makes changes to the wp_editor on a slide's edit page
 */
function dds_slide_editor() {
	$screen = get_current_screen();
	if ( $screen->id == 'slide' ) {
		$id   = get_the_ID();
		$post = get_post( $id );
		if ( $post->post_type == 'slide' ) {
			$theme = get_post_meta( $id, 'dds_theme', true );
			if ( $theme ) {
				add_editor_style( plugins_url( "themes/$theme/style.css", __FILE__ ) );
			}
		}
	}
}

add_action( 'admin_enqueue_scripts', 'dds_slide_editor' );

/**
 * Registers the slide metabox for the 'slide' custom post type
 */
function dds_register_slide_metabox() {
	add_meta_box( 'dds-slide-themes', 'Slide Options', 'dds_slide_metabox', 'slide' );
}

add_action( 'add_meta_boxes', 'dds_register_slide_metabox' );


/**
 * Renders the slide metabox on a slides edit page
 */
function dds_slide_metabox() {
	$id = get_the_ID(); ?>
	<div class="dds-metabox">

		<label for="dds_theme"><b>Theme:</b> <i>This will set them theme for the slide if you are creating the slide
				from scratch.</i>
			<select id="dds_theme" name="dds_theme">
				<?php
				$current_theme = get_post_meta( $id, 'dds_theme', true );
				$dds_theme_dir = __DIR__ . '/themes/';
				$themes        = scandir( $dds_theme_dir );

				if ( $themes ) :
					foreach ( $themes as $theme ) :
						if ( strpos( $theme, '.css' ) == strlen( $theme ) - 4 ) {
							// Processing for a drop-in css file to themes/themestyle.css... Not Supported because you should create a directory for your theme!
						} else if ( is_dir( $dds_theme_dir . $theme ) && ! in_array( $theme, array( '.', '..' ) ) ) {

							$stylesheet = $theme . '/style.css';
							if ( ! is_file( $dds_theme_dir . $stylesheet ) ) {
								continue;
							}
						} else {
							continue;
						}
						$theme_info = get_file_data( $dds_theme_dir . $stylesheet, array( 'Name'     => 'Theme Name',
						                                                             'Template' => 'Template'
							) );
						?>
						<option value="<?php echo $theme ?>" <?php selected( $current_theme == $theme ); ?>><?php echo $theme_info['Name'] ?></option>
					<?php
					endforeach;
				endif; ?>
			</select>
		</label>
		<br>
		<label for="dds_duration"><b>Duration:</b> <i>How long this slide should show on the screen.</i>
			<select id="dds_duration" name="dds_duration">
				<?php
				$current_duration = get_post_meta( $id, 'dds_duration', true );
				$n                = 0;
				while ( $n < MAX_SLIDE_DURATION ) :
					$n += ( $n < 20 ? 1 : 5 );
					?>

					<option value="<?php echo $n; ?>" <?php selected( $n == $current_duration ); ?>>
                        <?php echo $n . _n( ' Second', ' Seconds', $n ); ?>
                    </option>
				<?php endwhile; ?>
			</select>
		</label>
		<br>
		<label for="dds_external_url"><b>External URL:</b>
            <i>Load external web page instead of a post. Remember, this page should be formatted for long distance viewing.</i>
			<?php
			$current_external_url = get_post_meta( $id, 'dds_external_url', true );
			?>
			<input type="text" name="dds_external_url" placeholder="http://www.google.com"
			       value="<?php echo $current_external_url; ?>" style="width: 50%; min-width: 400px;">
		</label>


	</div>
<?php
}

/**
 * Saves the duration, theme, and external url of the slide with the given $post_id
 *
 * @param int $post_id The post ID of the slide
 */
function dds_save_slide_options( $post_id ) {
  
  $queried_post = get_post($post_id);
  $title = $queried_post->post_title;
  echo $title;
  echo $queried_post->post_content;
  
  
	if ( get_post_type( $post_id ) != 'slide' ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( isset( $_POST['dds_theme'] ) && isset( $_POST['dds_duration'] ) ) {
		update_post_meta( $post_id, 'dds_theme', $_POST['dds_theme'] );
		update_post_meta( $post_id, 'dds_duration', $_POST['dds_duration'] );
	}

	if ( isset( $_POST['dds_external_url'] ) ) {
		update_post_meta( $post_id, 'dds_external_url', esc_url_raw( $_POST['dds_external_url'] ) );
	}
}

add_action( 'save_post', 'dds_save_slide_options' );

/**
 * Gets the List of slides that the dds client with name $pie_name needs to show
 *
 * @param $actions The Current List of actions that has already been compiled by other plugins
 * @param $pie_post The post representing the PIE that is currently requesting commands
 * @param $pie_name The name of the PIE that is currently requesting commands
 *
 * @return array An array of Actions that $pie_name should make
 */
function dds_slide_actions( $actions, $pie_post, $pie_name ) {
    // Get the groups that the PIE is a member of
	$groups_for_pie = wp_get_post_categories( $pie_post->ID );

    // The Final List of Slides that It should Run
	$queue = array();

	foreach ( $groups_for_pie as $pie_group ) {
		// Get The Slides that are assigned to $pie_group
        $slides = get_posts( array(
			'posts_per_page'   => - 1,
			'category'         => $pie_group,
			'orderby'          => 'ID',
			'order'            => 'DESC',
			'post_type'        => 'slide',
			'post_status'      => 'publish',
			'suppress_filters' => false
		) );

        // List through the slides, If the slide hasn't already been enqueued then add it to $queue
		foreach ( $slides as $slide ) {
			if ( ! in_array( $slide, $queue ) ) {
				$queue[] = $slide;
			}
		}
	}

    // Convert the list of WP_Post Objects into a List of DDS Actions
	foreach ( $queue as $post ) {
		$actions[] = array(
			'type'     => 'slide',
			'location' => get_slide_location( $pie_name, $post->ID ),
			'duration' => (float) get_post_meta( $post->ID, 'dds_duration', true )
		);
	}

	return $actions;
}

add_filter( 'dds_pie_actions', 'dds_slide_actions', 10, 3 );
