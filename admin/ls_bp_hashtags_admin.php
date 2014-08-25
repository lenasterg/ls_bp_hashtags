<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit ;

/**
 * add admin_menu page
 */
function ls_bp_hashtags_admin_add_admin_menu() {

    add_submenu_page( ls_bp_hashtags_find_admin_location() , __( 'Buddypress Hashtags Admin' , 'bp-hashtags' ) , __( 'Buddypress Hashtags' , 'bp-hashtags' ) , 'manage_options' , 'bp-activity-hashtags-settings' , 'ls_bp_hashtags_admin' ) ;

    //set up defaults
    $new = Array () ;
    $new[ 'slug' ] = 'tag' ;
    $new[ 'install_version' ] = etivite_plugin_get_version() ;
    add_site_option( 'ls_bp_hashtags' , $new ) ;
//    if ( $old = get_option( 'ls_bp_hashtags' ) ) {
//        update_site_option( 'ls_bp_hashtags' , $old ) ;
//        delete_option( 'ls_bp_hashtags' ) ;
//    }
}

add_action( bp_core_admin_hook() , 'ls_bp_hashtags_admin_add_admin_menu' ) ;

/**
 *
 * @global type $bp
 * @version 2, 25/8/2014 stergatu added option of displaying hashtag symbol
 */
function ls_bp_hashtags_admin() {
    $bp = buddypress() ;

    if ( isset( $_POST[ 'submit' ] ) && check_admin_referer( 'ls_bp_hashtags_admin' ) ) {

        $new = Array () ;

        if ( isset( $_POST[ 'ah_tag_slug' ] ) && ! empty( $_POST[ 'ah_tag_slug' ] ) ) {
            $new[ 'slug' ] = $_POST[ 'ah_tag_slug' ] ;
        } else {
            $new[ 'slug' ] = false ;
        }
        if ( isset( $_POST[ 'ls_show_hashtags_symbol' ] ) && ! empty( $_POST[ 'ls_show_hashtags_symbol' ] ) ) {
            $new[ 'style' ][ 'show_hashsymbol' ] = '1' ;
        } else {
            $new[ 'style' ][ 'show_hashsymbol' ] = '0' ;
        }

        if ( isset( $_POST[ 'ah_blog_taxomony' ] ) && ! empty( $_POST[ 'ah_blog_taxomony' ] ) && $_POST[ 'ah_blog_taxomony' ] == 1 ) {
            $new[ 'blogposts' ][ 'use_taxonomy' ] = '1' ;
        } else {
            $new[ 'blogposts' ][ 'use_taxonomy' ] = '0' ;
        }

        //		if( isset( $_POST['ah_blog'] ) && !empty( $_POST['ah_blog'] ) && $_POST['ah_blog'] == 1) {
//	        $new['blogposts']['enabled'] = true;
//		} else {
//			$new['blogposts']['enabled'] = false;
//		}

        update_site_option( 'ls_bp_hashtags' , $new ) ;

        $updated = true ;
    }
    ?>

        <div class="wrap">
        <h2><?php _e( 'Buddypress Hastags Admin' , 'bp-hashtags' ) ; ?></h2>

            <?php
            if ( isset( $updated ) ) : echo "<div id='message' class='updated fade'><p>" . __( 'Settings updated.' , 'bp-hashtags' ) . "</p></div>" ;
            endif ;

            $data = maybe_unserialize( get_site_option( 'ls_bp_hashtags' ) ) ;
        ?>

                <form action="<?php echo network_admin_url( '/admin.php?page=bp-activity-hashtags-settings' ) ?>" name="groups-autojoin-form" id="groups-autojoin-form" method="post">

                    <h4><?php _e( 'Hashtag Base Slug' , 'bp-hashtags' ) ; ?></h4>
                <table class="form-table">
                    <tr>
                        <th><label for="ah_tag_slug"><?php _e( 'Slug' , 'bp-hashtags' ) ?></label></th>
                        <td><input type="text" name="ah_tag_slug" id="ah_tag_slug" value="<?php echo $data[ 'slug' ] ; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="ls_show_hashtags_symbol"><?php _e( 'Show # for word each in widget and in hashtag list? ' , 'bp-hashtags' ) ?></label></th>
                        <td><input type="checkbox" name="ls_show_hashtags_symbol" id="ls_show_hashtags_symbol" value="1"      <?php checked( $data[ 'style' ][ 'show_hashsymbol' ] , '1' ) ; ?> /></td>
                    </tr>
                </table>
                <h4><?php _e( 'Blog Posts' , 'bp-hashtags' ) ; ?></h4>

                    <table class="form-table">
                    <tr>
                        <th><label for="ah_blog_taxomony"><?php _e( 'Use posts categories/tags also as hashtags?' , 'bp-hashtags' ) ?></label></th>
                        <td><input type="checkbox" name="ah_blog_taxomony" id="ah_blog_taxomony" value="1"  <?php checked( $data[ 'blogposts' ][ 'use_taxonomy' ] , '1' ) ; ?> /></td>

                        </tr>
                </table>

                <?php if ( ! is_multisite() ) { ?>
                    <h4><?php _e( 'Blog Posts/Comments - in Main Blog' , 'bp-hashtags' ) ; ?></h4>

                                <table class="form-table">
                            <tr>
                                <th><label for="ah_blog"><?php _e( 'Enable hashtags on main blog' , 'bp-hashtags' ) ?></label></th>
                                <td><input type="checkbox" name="ah_blog" id="ah_blog" value="1" <?php
                                    if ( $data[ 'blogposts' ][ 'enabled' ] ) {
                                        echo 'checked' ;
                                    }
                                    ?>/></td>
                            </tr>
                        </table>
                    <?php } ?>

                    <?php wp_nonce_field( 'ls_bp_hashtags_admin' ) ; ?>

                <p class="submit"><?php submit_button() ; ?></p>

                </form>
        </div>
        <?php
    }

    /**
     * Finds the url of settings page
     * @global type $wpdb
     * @global type $bp
     * @return string
     * @author lenasterg
     * @version 1, 9/4/2014
     */
    function ls_bp_hashtags_find_admin_location() {
    global $wpdb , $bp ;
    if ( ! is_super_admin() )
        return false ;
    $locationMu = 'settings.php' ;
    $location = bp_core_do_network_admin() ? $locationMu : 'options-general.php' ;
    return $location ;
}

/**
 *  Add settings link on plugin page
 *  @param type $links
 * @param type $file
 * @return array
 * @version 1, 9/4/2014 stergatu
 */
function ls_bp_hashtags_settings_link( $links , $file ) {
    if ( $file == BP_HASHTAGS_BASENAME ) {
        return array_merge( $links , array (
            'settings' => '<a href="' . add_query_arg( array ( 'page' => 'bp-activity-hashtags-settings' ) , ls_bp_hashtags_find_admin_location() ) . '">' . __( 'Settings' , 'bp-hashtags' ) . '</a>' ,
                ) ) ;
    }
    return $links ;
}

/// Add link to settings page
add_filter( 'plugin_action_links' , 'ls_bp_hashtags_settings_link' , 10 , 2 ) ;
add_filter( 'network_admin_plugin_action_links' , 'ls_bp_hashtags_settings_link' , 10 , 2 ) ;
