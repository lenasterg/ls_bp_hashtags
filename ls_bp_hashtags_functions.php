<?php

if ( ! defined( 'ABSPATH' ) )
    exit;

function etivite_bp_activity_hashtags_current_activity() {
    global $activities_template;
    return $activities_template->current_activity;
}

/**
 * @see http://stackoverflow.com/questions/3060601/retrieve-all-hashtags-from-a-tweet-in-a-php-function
 * @param string $string
 * @return bool|array
 * @stergatu
 * @version 1, 8/4/2014
 */
function ls_bp_hashtags_get_from_string( $string ) {
    $hashtags = FALSE;
    $pattern = '/(#\w+)/u';
    $string = wp_strip_all_tags( $string );
    preg_match_all( "/(#\w+)/u", $string, $matches );

    if ( $matches ) {
	$hashtagsArray = array_count_values( $matches[0] );
	$hashtags = array_unique( array_keys( $hashtagsArray ) );
    }
    return $hashtags;
}

/**
 *
 * @global wpdb $wpdb
 * @param array $args
 * @return array of activity ids
 * @version 3, 24/4/2014
 */
function ls_bp_hashtags_get_activity_ids( $args = array() ) {
    global $wpdb;
    $bp = buddypress();
    bp_hashtags_set_constants();
    $toWhere = ls_bp_hashtags_generate_query_limitations( $args );
    $results = $wpdb->get_col(
	    "
	SELECT value_id
	FROM " . BP_HASHTAGS_TABLE . " WHERE  1=1   " . $toWhere );
    return $results;
}

/**
 * Create the query criteria
 * @param array $args  {
 *     An array of arguments. All items are optional.
 * @param string $hashtag_name. A specific hashtag
 *      @param int user_id. A specific user_id
 * @param bool $if_activity_item_id. If it is a item frome the bp_activity table
 * @param string $special. A complex where critirions
 * @param bool $hide_sitewide. If we can search the hide_sitewide activity records
 * }
 * @return string
 * @version 3, 25/8/2014 added taxonomy limitations
 * v2, 25/4/2014
 * @author stergatu
 */
function ls_bp_hashtags_generate_query_limitations( $args = array() ) {
    $bp = buddypress();

    $data = maybe_unserialize( get_site_option( 'ls_bp_hashtags' ) );
    $taxonomy_limit = '';
    if ( $data['blogposts']['use_taxonomy'] != '1' ) {
	$taxonomy_limit = ' AND taxonomy = "" ';
    }

    if ( ! $args ) {
	$args = array();
    }
    $query_hashtag = '';
    if ( isset( $args['hashtag_name'] ) ) {
	$query_hashtag = ' AND hashtag_name ="' . urldecode( $args['hashtag_name'] ) . '" ';
    }
    $query_user = '';
    if ( array_key_exists( 'user_id', $args ) && $args['user_id'] != 0 ) {
	$query_user = ' AND user_id=' . absint( $args['user_id'] );
    }
    $query_item_id = '';
    if ( array_key_exists( 'if_activity_item_id', $args ) && $args['if_activity_item_id'] != 0 ) {
	$query_item_id = ' AND if_activity_item_id=' . absint( $args['if_activity_item_id'] );
    }

    $args = ls_bp_hashtags_show_hidden_hashtags( $args );

    $query_special = '';
    if ( array_key_exists( 'special', $args ) ) {
	$query_special = ' AND ' . $args['special'];
    }
    $query_hide_sitewide = '';
    if ( array_key_exists( 'hide_sitewide', $args ) && $args['hide_sitewide'] != '' ) {
	$query_hide_sitewide = ' AND hide_sitewide=' . $args['hide_sitewide'];
    }

    $toWhere = $taxonomy_limit . $query_hashtag . $query_user . $query_item_id . $query_special . $query_hide_sitewide;
    return $toWhere;
}

/**
 * Define if the hide_sitewide field should by used
 * @param array $args
 * @return array
 * @version 3, 29/4/2014
 * @author stergatu
 */
function ls_bp_hashtags_show_hidden_hashtags( $args ) {
    $bp = buddypress();

    if ( $bp->loggedin_user->id == 0 ) {
	$args['hide_sitewide'] = '0';
	return $args;
    } else {
	$user_groupids = groups_get_user_groups( $bp->loggedin_user->id );
	if ( $user_groupids['total'] == 0 ) {
	    $args['hide_sitewide'] = '0';
	    return $args;
	}

	if ( ! array_key_exists( 'if_activity_item_id', $args ) || $args['if_activity_item_id'] == 0 ) {
	    $group_ids = implode( ',', $user_groupids['groups'] );
	    $args['hide_sitewide'] = '';
	    $args['special'] = ' ( hide_sitewide=0 OR  if_activity_item_id in (' . $group_ids . ')) ';
	} else {
	    if ( in_array( $args['if_activity_item_id'], $user_groupids ) ) {
		$args['hide_sitewide'] = '1';
	    }
	}
	return $args;
    }
}

/**
 * Generates hashtags list
 * @uses wp_generate_tag_cloud()
 * @global type $wpdb
 * @param array $args, see wp_generate_tag_cloud() for args values
 * @return string
 * @author Stergatu Lena <stergatu@cti.gr>
 * @version 3, 8/5/2014
 * @todo add filters instead of if clauses
 */
function ls_bp_hashtags_generate_cloud( $args = array() ) {
    $hashtags = ls_bp_hashtags_get_hashtags( $args );
    $defaults = array(
	'smallest' => 10, 'largest' => 10, 'unit' => 'pt', 'number' => 0,
	'format' => 'flat', 'separator' => ",\n\n", 'orderby' => 'count', 'order' => 'DESC',
	'topic_count_text_callback' => 'default_topic_count_text',
	'topic_count_scale_callback' => 'default_topic_count_scale', 'filter' => 1
    );
    $args = wp_parse_args( $args, $defaults );
    extract( $args );
    $tag_cloud = wp_generate_tag_cloud( $hashtags, $args );
    $tag_cloud = '<div class="hashtags">' . $tag_cloud . '</div>';

    return $tag_cloud;
}

/**
 * Fetches hashtags from database with the link and count
 * @global wpdb $wpdb
 * @param array $args
 * @return array
 * @version 2, 25/8/2014
 * v1, 8/5/2014
 */
function ls_bp_hashtags_get_hashtags( $args = array() ) {
    global $wpdb;
    $bp = buddypress();
    $link = $bp->root_domain . "/" . $bp->activity->slug . "/" . BP_ACTIVITY_HASHTAGS_SLUG . "/";
    bp_hashtags_set_constants();

    $data = maybe_unserialize( get_site_option( 'ls_bp_hashtags' ) );

    if ( $data['style']['show_hashsymbol'] == '1' ) {
	$hashtag_name = ' CONCAT( "#", hashtag_name)';
    } else {
	$hashtag_name = 'hashtag_name ';
    }


    $toWhere = ls_bp_hashtags_generate_query_limitations( $args );

    $results = $wpdb->get_results( 'SELECT COUNT(hashtag_name) as count, '
	    . $hashtag_name . ' as name, '
	    . 'CONCAT("' . $link . '", hashtag_slug) as link
        FROM ' . BP_HASHTAGS_TABLE . ' WHERE 1=1 ' . $toWhere . ' GROUP BY hashtag_name' );

    return $results;
}

/**
 * Fetches tags and categories from post as hashtags
 *
 * @global wpdb $wpdb
 * @param BP_Activity_Activity $activity
 * @return array|WP_Error The requested term data or empty array if no terms found. WP_Error if any of the $taxonomies don't exist.
 * @author stergatu
 * @since
 * @version 1, 10/4/2013
 */
function ls_bp_hashtags_getblogpost_tags_as_hashtags( $activity ) {
    global $wpdb;
    $blog_id = $activity->item_id;
    $post_id = $activity->secondary_item_id;
    switch_to_blog( $blog_id );
    $post_types_use_as_bp_hashtags = array( 'post_tag', 'category' );
    $types = apply_filters( 'custom_post_type_use_as_bp_hashtags', $post_types_use_as_bp_hashtags );
//$tags = wp_get_object_terms( $post_id , $types ) ;

    $tagsfrompost = wp_get_object_terms( $post_id, $types, array( 'fields' => 'all' ) );
    $tags = array_map( "only_usefull", $tagsfrompost );

    restore_current_blog();

    return $tags;
}

/**
 *
 * @param array $a
 * @return array
 * @version 1, 25/8/2014
 */
function only_usefull( $a ) {
    $x['name'] = $a->name;
    $x['taxonomy'] = $a->taxonomy;
    return $x;
}
