<?php
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

/**
 * Add the hashtags into the activity query_string
 * @param string $query_string
 * @param type $object
 * @return string
 * @version 2, stergatu 8/4/2014
 */
function ls_bp_hashtags_querystring( $query_string , $object ) {
    $bp = buddypress() ;
    if ( ! bp_is_activity_component() || $bp->current_action != BP_ACTIVITY_HASHTAGS_SLUG ) {
        return $query_string ;
    }

    if ( empty( $bp->action_variables[ 0 ] ) ) {

        $query_string = "" ;
        echo $query_string ;
        return $query_string ;
    }
    if ( count( $bp->action_variables ) > 1 ) {
        if ( 'feed' == $bp->action_variables[ 1 ] ) {
            return $query_string ;
        }
    }
    $ids = ls_bp_hashtags_get_activity_ids( $bp->action_variables[ 0 ] ) ;
    if ( count( $ids ) == 0 ) {
        $query_string .= "&include=0,0" ;
    }
    $query_string.='&display_comments&in=' . implode( ',' , $ids ) ;
    return $query_string ;
}

add_filter( 'bp_ajax_querystring' , 'ls_bp_hashtags_querystring' , 11 , 2 ) ;

//thanks r-a-y for the snippet
/**
 *
 * @return type
 * @version 2, stergatu 8/4/2014
 */
function ls_bp_hashtags_header() {
    $bp = buddypress() ;
    if ( ! bp_is_activity_component() || $bp->current_action != BP_ACTIVITY_HASHTAGS_SLUG )
        return ;
    printf( "<div style='margin: 10px;'>  " . __( "Activity results for #%s " , 'bp-hashtags' ) , urldecode( $bp->action_variables[ 0 ] ) . '</div>' ) ;
    echo ' <div class="generic-button reset-hashtags" style="margin: 10px;">  <a href="/' . bp_get_activity_slug() . '">' . __( 'Remove filter' , 'bp-hashtags' ) . '</a></div>' ;
}

add_filter( 'bp_get_activity_pagination_count' , 'ls_bp_hashtags_header' ) ;
add_action( 'bp_before_activity_loop' , 'ls_bp_hashtags_header' ) ;

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

function etivite_bp_activity_hashtags_insert_rel_head() {
    $bp = buddypress() ;

    if ( ! bp_is_activity_component() || $bp->current_action != BP_ACTIVITY_HASHTAGS_SLUG )
        return false ;

    if ( empty( $bp->action_variables[ 0 ] ) )
        return false ;

    $link = $bp->root_domain . "/" . $bp->activity->slug . "/" . BP_ACTIVITY_HASHTAGS_SLUG . "/" . esc_attr( $bp->action_variables[ 0 ] ) . '/feed/' ;

    echo '<link rel="alternate" type="application/rss+xml" title="' . get_blog_option( BP_ROOT_BLOG , 'blogname' ) . ' | ' . esc_attr( $bp->action_variables[ 0 ] ) . ' | Hashtag" href="' . $link . '" />' ;
}

add_action( 'bp_head' , 'etivite_bp_activity_hashtags_insert_rel_head' ) ;

function etivite_bp_activity_hashtags_activity_feed_link( $feedurl ) {
    global $bp ;

    if ( ! bp_is_activity_component() || $bp->current_action != BP_ACTIVITY_HASHTAGS_SLUG )
        return $feedurl ;

    if ( empty( $bp->action_variables[ 0 ] ) )
        return $feedurl ;

    return $bp->root_domain . "/" . $bp->activity->slug . "/" . BP_ACTIVITY_HASHTAGS_SLUG . "/" . esc_attr( $bp->action_variables[ 0 ] ) . '/feed/' ;
}

add_filter( 'bp_get_sitewide_activity_feed_link' , 'etivite_bp_activity_hashtags_activity_feed_link' , 1 , 1 ) ;

/**
 *
 * @global type $bp
 * @global type $wp_query
 * @return boolean
 * @version 2, stergatu
 */
function etivite_bp_activity_hashtags_action_router() {
    global $wp_query ;
    $bp = buddypress() ;

    if ( ! bp_is_activity_component() || $bp->current_action != BP_ACTIVITY_HASHTAGS_SLUG )
        return false ;

    if ( empty( $bp->action_variables[ 0 ] ) ) {
        bp_core_load_template( 'activity/index' ) ;
    }
    if ( count( $bp->action_variables ) > 1 ) {
        if ( 'feed' == $bp->action_variables[ 1 ] ) {

            $link = $bp->root_domain . "/" . $bp->activity->slug . "/" . BP_ACTIVITY_HASHTAGS_SLUG . "/" . esc_attr( $bp->action_variables[ 0 ] ) ;
            $link_self = $bp->root_domain . "/" . $bp->activity->slug . "/" . BP_ACTIVITY_HASHTAGS_SLUG . "/" . esc_attr( $bp->action_variables[ 0 ] ) . '/feed/' ;

            $wp_query->is_404 = false ;
            status_header( 200 ) ;

            include_once( dirname( __FILE__ ) . '/feeds/bp-activity-hashtags-feed.php' ) ;
            die ;
        }
    }

    bp_core_load_template( 'activity/index' ) ;
}

add_action( 'wp' , 'etivite_bp_activity_hashtags_action_router' , 3 ) ;

function etivite_bp_activity_hashtags_current_activity() {
    global $activities_template ;
    return $activities_template->current_activity ;
}

/**
 * @see http://stackoverflow.com/questions/3060601/retrieve-all-hashtags-from-a-tweet-in-a-php-function
 * @param type $string
 * @return type
 * @stergatu
 * @version 1, 8/4/2014
 */
function ls_bp_hashtags_get_from_string( $string ) {
    $hashtags = FALSE ;
    $pattern = '/(#\w+)/u' ;
    $string = wp_strip_all_tags( $string ) ;
    //$pattern = '/(?(?<!color: )(?<!color: )[#]([_0-9a-zA-Z-]+)|(^|\s|\b)[#]([_0-9a-zA-Z-]+))/';
    //$pattern = '/(?(?<!color: )(?<!color: )[#]([_0-9a-zA-Z-]+)|(^|\s|\b)[#]([\w]+))/u' ;
    //  preg_match_all( $pattern , $string , $matches ) ;
    preg_match_all( "/(#\w+)/u" , $string , $matches ) ;

    if ( $matches ) {
        $hashtagsArray = array_count_values( $matches[ 0 ] ) ;
        $hashtags = array_keys( $hashtagsArray ) ;
    }
    return $hashtags ;
}

/**
 *
 * @global type $wpdb
 * @param object $activity
 * @author Stergatu Lena <stergatu@cti.gr>
 * @version 1, 8/4/2014
 */
function ls_bp_hashtags_add_activity_id( $activity ) {
    global $wpdb ;
    bp_hashtags_set_constants() ;
    do_action( 'pre_hashtags_insert' , $activity ) ;

    if ( ($activity->type == 'new_blog_post') || ('new_groupblog_post') ) {
        $hashtags_from_post_types = ls_bp_hashtags_getblogpost_tags_as_hashtags( $activity ) ;
    }
    $hashtags_included_to_content = str_replace( "#" , '' , ls_bp_hashtags_get_from_string( $activity->content ) ) ;


    $hashtags = array_unique( array_merge( $hashtags_from_post_types , $hashtags_included_to_content ) ) ;

    foreach ( $hashtags as $hashtag ) {
        $wpdb->insert(
                BP_HASHTAGS_TABLE , array (
            'hashtag_name' => htmlspecialchars( $hashtag ) ,
            'hashtag_slug' => urlencode( htmlspecialchars( $hashtag ) ) ,
            'value_id' => $activity->id ,
            'created_ts' => $activity->date_recorded ,
            'user_id' => $activity->user_id
        ) ) ;
    }
}

add_action( 'pre_hashtags_insert' , 'ls_bp_hashtags_delete_activity_id' ) ;
add_action( 'bp_activity_action_delete_activity' , 'ls_bp_hashtags_delete_activity_id' ) ;

/**
 * When saved an updated activity post, it deletes previous inserted hashtags from this activity
 * @global type $wpdb
 * @param type $activity
 * @author Stergatu Lena <stergatu@cti.gr>
 * @version 1, 8/4/2014
 */
function ls_bp_hashtags_delete_activity_id( $activity ) {
    global $wpdb ;
    bp_hashtags_set_constants() ;

    if ( is_int( $activity ) ) {
        $wpdb->delete( BP_HASHTAGS_TABLE , array ( 'value_id' => $activity ) ) ;
    } else {
        $wpdb->delete( BP_HASHTAGS_TABLE , array ( 'value_id' => $activity->id ) ) ;
    }
}

/**
 * Clear hashtags for deleted activity items.
 *
 * @since 1.0
 * @author stergatu
 * @param array $deleted_ids IDs of deleted activity items.
 */
function ls_bp_hashtags_clear_deleted_activity( $deleted_ids ) {
    global $wpdb ;
    bp_hashtags_set_constants() ;
    foreach ( ( array ) $deleted_ids as $deleted_id ) {
        $wpdb->delete( BP_HASHTAGS_TABLE , array ( 'value_id' => $deleted_id ) ) ;
    }
}

add_action( 'bp_activity_deleted_activities' , 'ls_bp_hashtags_clear_deleted_activity' ) ;

/**
 *
 * @global type $wpdb
 * @param type $hashtag
 * @return type
 * @version 1, 9/4/2014
 */
function ls_bp_hashtags_get_activity_ids( $hashtag ) {
    global $wpdb ;
    bp_hashtags_set_constants() ;

    $results = $wpdb->get_col(
            "
	SELECT value_id
	FROM " . BP_HASHTAGS_TABLE . " WHERE  hashtag_name = '" . urldecode( $hashtag ) . "'" ) ;

    return $results ;
}

/**
 * Generates hashtags list
 * @uses wp_generate_tag_cloud()
 * @global type $wpdb
 * @param array $args, see wp_generate_tag_cloud() for args values
 * @return string
 * @author Stergatu Lena <stergatu@cti.gr>
 * @version 1, 16/4/2014
 */
function ls_bp_hashtags_generate_cloud( $args = array () ) {
    global $wpdb ;
    $bp = buddypress() ;

    $link = $bp->root_domain . "/" . $bp->activity->slug . "/" . BP_ACTIVITY_HASHTAGS_SLUG . "/" ;
    bp_hashtags_set_constants() ;
    $results = $wpdb->get_results( 'SELECT COUNT(hashtag_name) as count, CONCAT("#",hashtag_name) as name, CONCAT("' . $link . '", hashtag_slug) as link
        FROM ' . BP_HASHTAGS_TABLE . ' GROUP BY hashtag_name' ) ;
//    $results = urlencode_deep( $results ) ;

    $defaults = array (
        'smallest' => 10 , 'largest' => 10 , 'unit' => 'pt' , 'number' => 0 ,
        'format' => 'flat' , 'separator' => ",\n\n" , 'orderby' => 'count' , 'order' => 'DESC' ,
        'topic_count_text_callback' => 'default_topic_count_text' ,
        'topic_count_scale_callback' => 'default_topic_count_scale' , 'filter' => 1 ,
            ) ;
    $args = wp_parse_args( $args , $defaults ) ;
    extract( $args ) ;
    $tag_cloud = wp_generate_tag_cloud( $results , $args ) ;
    return $tag_cloud ;
}

/**
 * echo's the tagcloud
 */
function ls_bp_hashtags_cloud() {

    if ( bp_is_activity_directory() ) {
        echo '<div align="right"><h5>' . __( 'Popular Hashtags' , 'bp-hashtags' ) . '</h5>' ;
        echo ls_bp_hashtags_generate_cloud() ;
        echo '</div>' ;
    }
}

add_action( 'bp_before_activity_loop' , 'ls_bp_hashtags_cloud' , 1 ) ;

/**
 * Fetches tags and categories from post as hashtags
 *
 * @global type $wpdb
 * @param type $activity
 * @return type
 * @author stergatu
 * @since
 * @version 1, 10/4/2013
 */
function ls_bp_hashtags_getblogpost_tags_as_hashtags( $activity ) {
    global $wpdb ;
    $blog_id = $activity->item_id ;
    $post_id = $activity->secondary_item_id ;
    switch_to_blog( $blog_id ) ;
    $post_types_use_as_bp_hashtags = array ( 'post_tag' , 'category' ) ;
    $types = apply_filters( 'custom_post_type_use_as_bp_hashtags' , $post_types_use_as_bp_hashtags ) ;
    //$tags = wp_get_object_terms( $post_id , $types ) ;

    $tags = wp_get_object_terms( $post_id , $types , array ( 'fields' => 'names' ) ) ;
    restore_current_blog() ;

    return $tags ;
}

/**
 * Add a description under the activity post form about the hashtag usage
 * @author stergatu
 * @version 1, 11/4/2014
 */
function ls_bp_hashtags_add_hashtags_text() {
    _e( 'Add the # symbol, to mark keywords(just like in twitter). When a user clicks on the word with #, all relavant posts fill appear.' , 'bp-hashtags' ) ;
}

add_action( 'bp_activity_post_form_options' , 'ls_bp_hashtags_add_hashtags_text' ) ;

/**
 * Adds a tab "Popular Terms" into activity directory
 */
function ls_bp_hashtags_activity_tab() {
    ?>
        <li id="activity-tags"><a href="<?php
        bp_activity_directory_permalink() ;
        echo BP_ACTIVITY_HASHTAGS_SLUG ;
        ?>" title="<?php esc_attr_e( 'Popular Terms.' , 'bp-hashtags' ) ; ?>"><?php _e( 'Popular Terms ' , 'bp-hashtags' ) ; ?></a></li>
    <?php
}

add_action( 'bp_activity_type_tabs' , 'ls_bp_hashtags_activity_tab' ) ;

