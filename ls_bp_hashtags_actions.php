<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit ;

/**
 *
 * @return type
 * @version 3, stergatu 27/11/2014
 */
function ls_bp_hashtags_header() {
    $bp = buddypress();
    if ( ! bp_is_activity_component() || $bp->current_action != BP_ACTIVITY_HASHTAGS_SLUG )
	return;
    printf( "<div style='margin: 10px;'>  " . __( "Activity results for #%s ", 'bp-hashtags' ), urldecode( $bp->action_variables[0] ) . '</div>' );
    echo ' <div class="generic-button reset-hashtags" style="margin: 10px;">  <a href="' . home_url() . '/' . bp_get_activity_slug() . '">' . __( 'Remove filter', 'bp-hashtags' ) . '</a></div>';
}

add_action( 'bp_before_directory_activity_list', 'ls_bp_hashtags_header' );

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

/**
 *
 * @global type $wpdb
 * @param object $activity
 * @author Stergatu Lena <stergatu@cti.gr>
 * @version 2, 25/8/2014 add check for blog posts taxomony
 * v1, 8/4/2014
 */
function ls_bp_hashtags_add_activity_id( $activity ) {
    global $wpdb ;
    bp_hashtags_set_constants() ;
    do_action( 'pre_hashtags_insert' , $activity ) ;


    $data = maybe_unserialize( get_site_option( 'ls_bp_hashtags' ) ) ;
    if ( $data[ 'blogposts' ][ 'use_taxonomy' ] == '1' ) {
        if ( ($activity->type == 'new_blog_post') || ('new_groupblog_post') ) {
            $hashtags_from_post_types = ls_bp_hashtags_getblogpost_tags_as_hashtags( $activity ) ;
            foreach ( $hashtags_from_post_types as $key => $value ) {
                $wpdb->insert(
                        BP_HASHTAGS_TABLE , array (
                    'hashtag_name' => htmlspecialchars( $value[ 'name' ] ) ,
                    'hashtag_slug' => urlencode( htmlspecialchars( $value[ 'name' ] ) ) ,
                    'value_id' => $activity->id ,
                    'if_activity_component' => $activity->component ,
                    'if_activity_item_id' => $activity->item_id ,
                    'hide_sitewide' => $activity->hide_sitewide ,
                    'created_ts' => $activity->date_recorded ,
                    'user_id' => $activity->user_id ,
                    'taxonomy' => $value[ 'taxonomy' ]
                ) ) ;
            }
        }
    }
    $hashtags_included_to_content = str_replace( "#" , '' , ls_bp_hashtags_get_from_string( $activity->content ) ) ;
    foreach ( $hashtags_included_to_content as $hashtag ) {
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

add_action( 'bp_activity_after_save' , 'ls_bp_hashtags_add_activity_id' ) ;

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

add_action( 'pre_hashtags_insert' , 'ls_bp_hashtags_delete_activity_id' ) ;
add_action( 'bp_activity_action_delete_activity' , 'ls_bp_hashtags_delete_activity_id' ) ;

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

add_action( 'bp_before_directory_activity_list', 'ls_bp_hashtags_cloud', 1 );

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
