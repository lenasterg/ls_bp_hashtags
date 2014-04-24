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

add_filter( 'bp_after_has_activities_parse_args' , 'ls_bp_hashtags_activity' ) ;

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
            'if_activity_component' => $activity->component ,
            'if_activity_item_id' => $activity->item_id ,
            'hide_sitewide' => $activity->hide_sitewide ,
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
        $wpdb->delete( BP_HASHTAGS_TABLE , array ( 'value_id' => $activity , 'table_name' => 'bp_activity' ) ) ;
    } else {
        $wpdb->delete( BP_HASHTAGS_TABLE , array ( 'value_id' => $activity->id , 'table_name' => 'bp_activity' ) ) ;
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
        $wpdb->delete( BP_HASHTAGS_TABLE , array ( 'value_id' => $deleted_id , 'table_name' => 'bp_activity' ) ) ;
    }
}

add_action( 'bp_activity_deleted_activities' , 'ls_bp_hashtags_clear_deleted_activity' ) ;
//if an activity is marked as spam deleted from the bp_hashtags table
add_action( 'bp_activity_action_spam_activity' , 'ls_bp_hashtags_clear_deleted_activity' ) ;

/**
 *
 * @global type $wpdb
 * @param type array
 * @param string $hashtag
 * @return type
 * @version 3, 24/4/2014
 */
function ls_bp_hashtags_get_activity_ids( $args = array () ) {
    global $wpdb ;
    $bp = buddypress() ;
    bp_hashtags_set_constants() ;
    $toWhere = ls_bp_hashtags_generate_query_limitations( $args ) ;
    $results = $wpdb->get_col(
            "
	SELECT value_id
	FROM " . BP_HASHTAGS_TABLE . " WHERE  1=1   " . $toWhere ) ;
    return $results ;
}

/**
 * Create the query criteria
 * @param array $args
 * @return string
 * @version 1, 24/4/2014
 * @author stergatu
 */
function ls_bp_hashtags_generate_query_limitations( $args = array () ) {
    $bp = buddypress() ;

    if ( isset( $args[ 'hashtag_name' ] ) ) {
        $query_hashtag = ' AND hashtag_name ="' . urldecode( $args[ 'hashtag_name' ] ) . '" ' ;
    }
    if ( $args[ 'user_id' ] != 0 ) {
        $query_user = ' AND user_id=' . absint( $args[ 'user_id' ] ) ;
    }
    if ( $args[ 'if_activity_item_id' ] != 0 ) {
        $query_item_id = ' AND if_activity_item_id=' . absint( $args[ 'if_activity_item_id' ] ) ;
    }

    $args = ls_bp_hashtags_show_hidden_hashtags( $args ) ;
    if ( $args[ 'special' ] ) {
        $query_special = ' AND ' . $args[ 'special' ] ;
    }
    if ( $args[ 'hide_sitewide' ] != '' ) {
        $query_hide_sitewide = ' AND hide_sitewide=' . $args[ 'hide_sitewide' ] ;
    }

    $toWhere = $query_hashtag . $query_user . $query_item_id . $query_special . $query_hide_sitewide ;
    return $toWhere ;
}

/**
 * Define if the hide_sitewide field should by used
 * @param type $args
 * @return string
 * @version 1, 24/4/2014
 * @author stergatu
 */
function ls_bp_hashtags_show_hidden_hashtags( $args ) {
    $bp = buddypress() ;

    if ( $bp->loggedin_user->id == 0 ) {
        $args[ 'hide_sitewide' ] = '0' ;
        return $args ;
    } else {
        $user_groupids = groups_get_user_groups( $bp->loggedin_user->id ) ;
        if ( $user_groupids[ 'total' ] == 0 ) {
            $args[ 'hide_sitewide' ] = '0' ;
            return $args ;
        }
        if ( $args[ 'if_activity_item_id' ] == 0 ) {
            $group_ids = implode( ',' , $user_groupids[ 'groups' ] ) ;
            $args[ 'hide_sitewide' ] = '' ;
            $args[ 'special' ] = ' ( hide_sitewide=0 OR  if_activity_item_id in (' . $group_ids . ')) ' ;
            return $args ;
        } else {
            if ( in_array( $args[ 'if_activity_item_id' ] , $user_groupids ) ) {
                $args[ 'hide_sitewide' ] = '1' ;
                return $args ;
            }
        }
    }
}

/**
 * Generates hashtags list
 * @uses wp_generate_tag_cloud()
 * @global type $wpdb
 * @param array $args, see wp_generate_tag_cloud() for args values
 * @return string
 * @author Stergatu Lena <stergatu@cti.gr>
 * @version 2, 23/4/2014
 * @todo add filters instead of if clauses
 */
function ls_bp_hashtags_generate_cloud( $args = array () ) {
    global $wpdb ;
    $bp = buddypress() ;

    $link = $bp->root_domain . "/" . $bp->activity->slug . "/" . BP_ACTIVITY_HASHTAGS_SLUG . "/" ;
    bp_hashtags_set_constants() ;

    $toWhere = ls_bp_hashtags_generate_query_limitations( $args ) ;

    $results = $wpdb->get_results( 'SELECT COUNT(hashtag_name) as count, CONCAT("#",hashtag_name) as name, CONCAT("' . $link . '", hashtag_slug) as link
        FROM ' . BP_HASHTAGS_TABLE . ' WHERE 1=1 ' . $toWhere . ' GROUP BY hashtag_name' ) ;

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
 * @version 2, 24/4/2014
 */
function ls_bp_hashtags_cloud() {
    $args = array () ;
    if ( bp_is_activity_component() ) {
        $toHead = __( 'Popular Hashtags across network' , 'bp-hashtags' ) ;
    }
    if ( bp_is_user_activity() ) {
        $toHead = __( 'Hashtags by user' , 'bp-hashtags' ) ;
        $args[ 'user_id' ] = bp_displayed_user_id() ;
    }
    if ( bp_is_group_activity() || bp_is_group_home() ) {
        $toHead = __( 'Hashtags in group' , 'bp-hashtags' ) ;
        $args[ 'if_activity_item_id' ] = bp_get_current_group_id() ;
    }
    echo '<div align="right"><h5>' . $toHead . '</h5>' ;
    echo ls_bp_hashtags_generate_cloud( $args ) ;
    echo '</div>' ;
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
 * Adds a tab "Popular Hashtags across network" into activity directory
 * @deprecated
 */
function ls_bp_hashtags_activity_tab() {
    ?>
        <li id="activity-tags"><a href="<?php
        bp_activity_directory_permalink() ;
        echo BP_ACTIVITY_HASHTAGS_SLUG ;
        ?>" title="<?php _e( 'Popular Hashtags across network' , 'bp-hashtags' ) ; ?>"><?php _e( 'Popular Hashtags across network' , 'bp-hashtags' ) ; ?></a></li>
    <?php
}

//add_action( 'bp_activity_type_tabs' , 'ls_bp_hashtags_activity_tab' ) ;

