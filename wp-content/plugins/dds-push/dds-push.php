<?php
/*
Plugin Name: DDS Push Plugin
Plugin URI: http://crew.ccs.neu.edu/
Description:  Issues commands to dds-server for distribution
Version: 0.1
Author: Eddie Hurtig, Neil Locketz, Philip Blair
Author URI: http://crew.ccs.neu.edu/people
*/

/**
 * Called when a slide is created or published
 * @param WP_Post $post The post that has been added
 */
function dds_slide_add( $post ) {
    push_message_default_content( $post, 'add-slide' );
}

add_action( 'slide-added', 'dds_slide_add' );

/**
 * Called when a slide is deleted
 * @param WP_Post $post The Post that has been deleted or trashed
 */
function dds_slide_delete( $post ) {
    push_message_default_content( $post, 'delete-slide' );
}

add_action( 'slide-deleted', 'dds_slide_delete' );

/**
 * Safeguards against forced permanent deletions of slides
 * @param int $post_id The ID of the Post
 */
function dds_slide_hard_delete( $post_id ) {
    do_action( 'slide-deleted', get_post( $post_id ) );
}

add_action( 'before_delete_post', 'dds_slide_hard_delete' );

/**
 * Called when a slide is edited
 * @param WP_Post $post The Post that was edited
 */
function dds_slide_edit( $post ) {
    push_message_default_content( $post, 'edit-slide' );
}

add_action( 'slide-edited', 'dds_slide_edit' );

/**
 * Handles routing for adding and updating slides
 * @param string $new_status The new status of the Post
 * @param string $old_status The old status of the Post
 * @param WP_Post $post The Post being transitioned
 */
function dds_add_update_switchboard( $new_status, $old_status, $post ) {
    if ( $post->post_type != 'slide' ) {
        return;
    }

    if ( $old_status == 'publish' && $new_status == 'publish' ) {
        $GLOBALS['dds_push_transitions']['edited'][] = $post;
    } elseif ( $old_status == 'publish' && $new_status == 'trash' ) {
        $GLOBALS['dds_push_transitions']['deleted'][] = $post;
    } elseif ( $new_status == 'publish' ) {
        $GLOBALS['dds_push_transitions']['added'][] = $post;
    }
}

add_action( 'transition_post_status', 'dds_add_update_switchboard', 10, 3 );

/**
 * Fires all the DDS push notifications to listeners
 * @param WP $wp unused
 */
function dds_do_pie_push( $wp ) {
    if ( isset( $GLOBALS['dds_push_transitions'] ) && ! empty( $GLOBALS['dds_push_transitions'] ) ) {
        foreach ( $GLOBALS['dds_push_transitions'] as $key => $posts ) {
            array_map( function ( $post ) use ( $key ) {
                do_action( 'slide-' . $key, $post );
            }, $posts );
        }
    }
    $GLOBALS['dds_push_transitions'] = array();
}

add_action( 'save_post', 'dds_do_pie_push', 9999 );


/**
 * Called when a slide's groups are changed. If there are any new groups, add the slide to that group's pies.
 * Otherwise, do nothing.
 *
 * @param int $object_id The ID of the Object that terms are being changed on
 * @param array $terms An array of object terms
 * @param array $tt_ids An array of term taxonomy IDs
 * @param string $taxonomy Taxonomy slug
 * @param bool $append Whether to append new terms to the old terms
 * @param array $old_tt_ids Old array of term taxonomy IDs
 */
function dds_add_pies( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
    if ( $taxonomy != "category" ) {
        return;
    }

    if ( get_post_status( $object_id ) != 'publish' ) {
        return;
    }

    $to_comp = array_map( function ( $i ) {
        return strval( $i );
    }, $old_tt_ids );
    $to_add = array_map( function ( $s ) {
        return ( intval( $s ) - 1 );
    }, array_diff( $tt_ids, $to_comp ) );
    if ( empty( $to_add ) ) {
        return;
    }
    $output = array();

    // (Copied from dds-slide)
    foreach ( $to_add as $pie_group ) {
        // Get The Pies that are assigned to $pie_group
        $pies = get_posts( array(
            'posts_per_page' => -1,
            'category' => $pie_group,
            'orderby' => 'ID',
            'order' => 'DESC',
            'post_type' => 'pie',
            'post_status' => 'publish',
            'suppress_filters' => false
        ) );
        // List through the slides, If the slide hasn't already been enqueued then add it to $queue
        foreach ( $pies as $pie ) {
            if ( ! in_array( $pie, $output ) ) {
                $output[] = array( 'name' => $pie->post_title );
            }
        }
    }
    push_message_default_content( $object_id, 'add-slide', $output );

}

/**
 * Called when a slide's groups are changed. If there are any groups no longer included, remove the slide from that
 * group's pies. Otherwise, do nothing.
 *
 * @param int $object_id The ID of the Object that terms are being changed on
 * @param array $terms An array of object terms
 * @param array $tt_ids An array of term taxonomy IDs
 * @param string $taxonomy Taxonomy slug
 * @param bool $append Whether to append new terms to the old terms
 * @param array $old_tt_ids Old array of term taxonomy IDs
 */
function dds_remove_pies( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
    if ( $taxonomy != "category" ) {
        return;
    }

    if ( get_post_status( $object_id ) != 'publish' ) {
        return;
    }
    
    $to_comp = array_map( function ( $i ) {
        return strval( $i );
    }, $old_tt_ids );
    $to_rem = array_map( function ( $s ) {
        return ( intval( $s ) - 1 );
    }, array_diff( $to_comp, $tt_ids ) );
    if ( empty( $to_rem ) ) {
        return;
    }
    $output = array();

    // (Copied from dds-slide)
    foreach ( $to_rem as $pie_group ) {
        // Get The Pies that are assigned to $pie_group
        $pies = get_posts( array(
            'posts_per_page' => -1,
            'category' => $pie_group,
            'orderby' => 'ID',
            'order' => 'DESC',
            'post_type' => 'pie',
            'post_status' => 'publish',
            'suppress_filters' => false
        ) );
        // List through the slides, If the slide hasn't already been enqueued then add it to $queue
        foreach ( $pies as $pie ) {
            if ( ! in_array( $pie, $output ) ) {
                $output[] = array( 'name' => $pie->post_title );
            }
        }
    }

    push_message_default_content( $object_id, 'delete-slide', $output );

}

add_action( 'set_object_terms', 'dds_remove_pies', 10, 6 );
add_action( 'set_object_terms', 'dds_add_pies', 10, 6 );

/**
 * Prepares and pushes Message out to Pies
 * @param int|WP_Post $post The Post
 * @param $action
 * @param null $dest_pies
 */
function push_message_default_content( $post, $action, $dest_pies = null ) {
    $post = get_post( $post );
    $content = (array) $post;
    $content['meta'] = get_post_meta( $post->ID );
    $content['permalink'] = get_post_permalink( $post->ID );
    $content['duration'] = (float) get_post_meta( $post->ID, 'dds_duration', true );
    error_log( 'setting message content' );
    error_log( var_export( $content, true ) );
    push_message( $post->ID, $post, $action, $content, $dest_pies );
}

/**
 * Pushes a Message out to slides
 * @param int $post_id The ID of the Post
 * @param WP_Post $post The Post
 * @param string $action The actions to take
 * @param mixed $content The Content of the Message
 * @param array|null $dest_pies The Destination Pies
 */
function push_message( $post_id, $post, $action, $content, $dest_pies = null ) {
    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }
    if ( get_post_type( $post_id ) != 'slide' ) {
        return;
    }

    if ( $dest_pies == null ) {
        $pies = pies_for_slide( $post );

        $pies = array_map( function ( $pie ) {
            return array( 'name' => $pie->post_title );
        }, $pies );
    } else {
        $pies = $dest_pies;
    }

    // http
    $url = apply_filters( 'dds_push_endpoint', get_option( 'dds_push_endpoint', 'http://127.0.0.1:12345' ) );
    $data = array(
        'datetime' => date( 'c', time() ),
        'action' => $action,
        'pies' => $pies,
        'content' => $content,
    );

    $json = json_encode( $data );
    wp_remote_post( $url, array( 'headers' => array( 'dataType' => 'jsonp' ), 'body' => $json ) );
}



