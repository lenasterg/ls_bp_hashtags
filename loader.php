<?php
/*
  Plugin Name: BuddyPress Hashtags LS
  Plugin URI:
  Description: Based on BuddyPress Activity Stream Hashtags (http://wordpress.org/extend/plugins/buddypress-activity-stream-hashtags/) Enable #hashtags linking without changing the activity content in database.
  Author: @lenasterg
  Author URI: http://lenasterg.wordpress.com
  License: GNU GENERAL PUBLIC LICENSE 3.0 http://www.gnu.org/licenses/gpl.txt
  Version: 1.1
  Text Domain: bp-hashtags
  Network: true
 */
define( 'BP_HASHTAGS_VERSION' , '1.1' ) ;
define( 'BP_HASHTAGS_DB_VERSION' , '1.1' ) ;
define( 'BP_HASHTAGS_BASENAME' , plugin_basename( __FILE__ ) ) ;
if ( ! defined( 'BP_ACTIVITY_HASHTAGS_SLUG' ) ) {
    define( 'BP_ACTIVITY_HASHTAGS_SLUG' , 'tags' ) ;
}

/**
 *
 * @version 2, stergatu
 */
function ls_bp_hashtags_init() {
    if ( ! bp_is_active( 'activity' ) ) {
        return ;
    }
    if ( file_exists( dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' ) ) {
        load_textdomain( 'bp-hashtags' , dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' ) ;
    }

    $data = maybe_unserialize( get_option( 'ls_bp_hashtags' ) ) ;

    $path = dirname( __FILE__ ) ;
    $files = array (
        'ls_bp_hashtags_functions.php' ,
        'ls_bp_hashtags_actions.php' ,
        'ls_bp_hashtags_filters.php' ,
        'widgets.php' ,
        'shortcodes.php' ,
            ) ;

    if ( is_super_admin() ) {
        $files[] = 'admin/ls_bp_hashtags_admin.php' ;
    }

    foreach ( $files as $file ) {
        require_once $path . '/' . $file ;
    }
}

add_action( 'bp_include' , 'ls_bp_hashtags_init' , 88 ) ;

function etivite_plugin_get_version() {
    $plugin_data = get_plugin_data( __FILE__ ) ;
    $plugin_version = $plugin_data[ 'Version' ] ;
    return $plugin_version ;
}

/**
 * SQL create command for BP_HASHTAGS_TABLE
 * @since version 0.5
 * @author stergatu
 * @version 2.0, 23/4/2014
 * @param type $charset_collate
 * @return string
 */
function bp_hashtags_tableCreate( $charset_collate ) {
    $bp = buddypress() ;
    $activity_table = 'bp_activity' ;
    $toSql = $sql[] = "CREATE TABLE " . BP_HASHTAGS_TABLE . " (
		  		id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                hashtag_name VARCHAR(255) NOT NULL,
                                hashtag_slug TEXT NOT NULL,
		  		table_name VARCHAR(255) DEFAULT '" . $activity_table . "',
                                value_id bigint(20) NOT NULL,
                                if_activity_component VARCHAR(255) DEFAULT '',
                                if_activity_item_id bigint(20),
                                hide_sitewide bool DEFAULT 0,
                                user_id int NOT NULL,
                                created_ts DATETIME NOT NULL,
				KEY hashtag_name (hashtag_name),
                                KEY if_activity_item_id (if_activity_item_id),
                                KEY if_activity_component (if_activity_component),
				KEY hide_sitewide (hide_sitewide),
                                KEY user_id (user_id),
                                KEY created_ts (created_ts)
		 	   ) {$charset_collate};" ;
    return $toSql ;
}

/**
 * bp_hashtags_is_installed()
 * Checks to see if the DB tables exist or if we are running an old version
 * of the component. If the value has increased, it will run the installation function.
 * @version 1, 8/4/2014
 */
function bp_hashtags_is_installed() {
    bp_hashtags_set_constants() ;
    if ( get_site_option( 'bp-hashtags-db-version' ) < BP_HASHTAGS_DB_VERSION ) {
        bp_hashtags_install_upgrade() ;
    }
}

register_activation_hook( __FILE__ , 'bp_hashtags_is_installed' ) ;

/**
 * bp_hashtags_install_upgrade()
 *
 * Installs and/or upgrades the database tables
 * This will only run if the database version constant is
 * greater than the stored database version value or no database version found
 * @author Stergatu Eleni <stergatu@cti.gr>
 * @version 1.0, 8/4/2014 now uses add_site_option instead of add_option
 */
function bp_hashtags_install_upgrade() {
    global $wpdb ;
    $bp = buddypress() ;

    $charset_collate = '' ;
    if ( ! empty( $wpdb->charset ) ) {
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset" ;
    }

    //If there is a previous version installed then move the variables to the sitemeta table
    if ( (get_site_option( 'bp-hashtags-db-version' )) && (get_site_option( 'bp-hashtags-db-version' ) < BP_HASHTAGS_DB_VERSION) ) {
        $sql[] = bp_hashtags_tableCreate( $charset_collate ) ;
    }
    if ( ! get_site_option( 'bp-hashtags-db-version' ) ) {
        $sql[] = bp_hashtags_tableCreate( $charset_collate ) ;
        add_option( 'bp-hashtags-db-version' , BP_HASHTAGS_DB_VERSION ) ;
    }
    update_site_option( 'bp-hashtags-db-version' , BP_HASHTAGS_DB_VERSION ) ;

    require_once( ABSPATH . "wp-admin/includes/upgrade.php" ) ;
    dbDelta( $sql ) ;
}

/**
 * @author Stergatu Eleni
 * @version 1, 8/4/2014
 */
function bp_hashtags_set_constants() {
    $bp = buddypress() ;
    if ( ! defined( 'BP_HASHTAGS_TABLE' ) ) {
        define( 'BP_HASHTAGS_TABLE' , $bp->table_prefix . 'bp_hashtags' ) ;
    }
    do_action( 'bp_hashtags_constants_loaded' ) ;
}


