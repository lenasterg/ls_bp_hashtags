<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit ;

/**
 *
 * @param type $content
 * @return type
 * @version 2, 8/4/2014
 *
 */
function ls_bp_hashtags_filter( $content ) {
    $bp = buddypress() ;

    $hashtags = ls_bp_hashtags_get_from_string( $content ) ;
    if ( $hashtags ) {
//but we need to watch for edits and if something was already wrapped in html link - thus check for space or word boundary prior
        foreach ( ( array ) $hashtags as $hashtag ) {
            $pattern = "/(^|\s|\b)" . $hashtag . "($|\b)/u" ;
            $hashtag_noHash = str_replace( "#" , '' , $hashtag ) ;
            $content = preg_replace( $pattern , ' <a href="' . $bp->root_domain . "/" . $bp->activity->slug . "/" . BP_ACTIVITY_HASHTAGS_SLUG . "/" . urlencode( htmlspecialchars( $hashtag_noHash ) ) . '" rel="nofollow" class="hashtag">' . htmlspecialchars( $hashtag ) . '</a>' , $content ) ;
        }
    }
    return $content ;
}

add_filter( 'the_content' , 'ls_bp_hashtags_filter' ) ;

/**
 *
 * @param type $content
 * @return type
 * @version 2, 8/4/2014
 *
 */
function ls_bp_hashtags_filter2( $content ) {
    $bp = buddypress() ;
    $hashtags = ls_bp_hashtags_get_from_string( $content ) ;
    if ( $hashtags ) {
//but we need to watch for edits and if something was already wrapped in html link - thus check for space or word boundary prior
        foreach ( ( array ) $hashtags as $hashtag ) {
            $pattern = "/(^|\s|\b)" . $hashtag . "($|\b)/u" ;
            $hashtag_noHash = str_replace( "#" , '' , $hashtag ) ;
            $content = str_replace( $hashtag , ' <a href="' . $bp->root_domain . "/" . $bp->activity->slug . "/" . BP_ACTIVITY_HASHTAGS_SLUG . "/" . urlencode( htmlspecialchars( $hashtag_noHash ) ) . '" rel="nofollow" class="hashtag">' . $hashtag . '</a>' , $content ) ;
        }
    }
    return $content ;
}

add_filter( 'bp_get_activity_content_body' , 'ls_bp_hashtags_filter2' ) ;

add_filter( 'bp_get_activity_pagination_count' , 'ls_bp_hashtags_header' ) ; //defined in ls_bp_hashtags_actions.php

/**
 * Parses the hashtags into the activity query args
 * @param array $retval
 * @return array
 * @since 1.2
 * @version 1, 24/4/2014
 */
function ls_bp_hashtags_activity( $retval ) {
    $bp = buddypress() ;
    if ( ! bp_is_activity_component() || $bp->current_action != BP_ACTIVITY_HASHTAGS_SLUG ) {
        return $retval ;
    }
    if ( empty( $bp->action_variables[ 0 ] ) ) {
        return $retval ;
    }
    if ( count( $bp->action_variables ) > 1 ) {
        if ( 'feed' == $bp->action_variables[ 1 ] ) {
            return $retval ;
        }
    }
    $bp_hashtags_args = array () ;
    $bp_hashtags_args[ 'hashtag_name' ] = $bp->action_variables[ 0 ] ;
    $bp_hashtags_args[ 'user_id' ] = bp_displayed_user_id() ;
    $bp_hashtags_args[ 'if_activity_item_id' ] = bp_get_current_group_id() ;
    $bp_hashtags_args[ 'table_name' ] = 'bp_activity' ;

    $ids = ls_bp_hashtags_get_activity_ids( $bp_hashtags_args ) ;
    if ( count( $ids ) == 0 ) {
        $retval[ 'include' ] = "0,0" ;
    }
    $retval[ 'display_comments' ] = 1 ;
    $retval[ 'show_hidden' ] = 1 ;
    $retval[ 'in' ] = implode( ',' , $ids ) ;
    return $retval ;
}

//Based on http://codex.buddypress.org/plugindev/using-bp_parse_args-to-filter-buddypress-template-loops/
add_filter( 'bp_after_has_activities_parse_args' , 'ls_bp_hashtags_activity' ) ;

function etivite_bp_activity_hashtags_page_title( $title ) {
    $bp = buddypress() ;

    if ( ! bp_is_activity_component() || $bp->current_action != BP_ACTIVITY_HASHTAGS_SLUG ) {
        return $title ;
    }

    if ( empty( $bp->action_variables[ 0 ] ) ) {
        return $title ;
    }

    return apply_filters( 'bp_activity_page_title' , __( 'Activity results for #' , 'bp-hashtags' ) . esc_attr( $bp->action_variables[ 0 ] ) . $title , esc_attr( $bp->action_variables[ 0 ] ) ) ;
}

add_filter( 'wp_title' , 'etivite_bp_activity_hashtags_page_title' , 99 ) ;

function etivite_bp_activity_hashtags_activity_feed_link( $feedurl ) {
    $bp = buddypress() ;

    if ( ! bp_is_activity_component() || $bp->current_action != BP_ACTIVITY_HASHTAGS_SLUG )
        return $feedurl ;

    if ( empty( $bp->action_variables[ 0 ] ) )
        return $feedurl ;

    return $bp->root_domain . "/" . $bp->activity->slug . "/" . BP_ACTIVITY_HASHTAGS_SLUG . "/" . esc_attr( $bp->action_variables[ 0 ] ) . '/feed/' ;
}

add_filter( 'bp_get_sitewide_activity_feed_link' , 'etivite_bp_activity_hashtags_activity_feed_link' , 1 , 1 ) ;


/**
 * It is used into the
 * @param type $found_template
 * @param type $templates
 * @return type
 */
function ls_bp_hashtags_template( $found_template, $templates ) {
    $bp = buddypress();
    if ( ! bp_is_activity_component() || $bp->current_action != BP_ACTIVITY_HASHTAGS_SLUG ) {
	return $found_template;
    }
    if ( empty( $found_template ) ) {
	$found_template = bp_locate_template( 'activity/index.php' );
    }
    return apply_filters( 'ls_bp_hashtags_template_filter', $found_template );
}


