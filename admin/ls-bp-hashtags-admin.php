<?php

/**
 *
 * @global type $bp
 * @version 2 stergatu
 */
function ls_bp_hashtags_admin() {
    $bp = buddypress() ;

    if ( isset( $_POST[ 'submit' ] ) && check_admin_referer( 'ls_bp_hashtags_admin' ) ) {

        $new = Array();

		if( isset( $_POST['ah_tag_slug'] ) && !empty( $_POST['ah_tag_slug'] ) ) {
	        $new['slug'] = $_POST['ah_tag_slug'];
		} else {
			$new['slug'] = false;
		}


        //		if( isset( $_POST['ah_blog'] ) && !empty( $_POST['ah_blog'] ) && $_POST['ah_blog'] == 1) {
//	        $new['blogposts']['enabled'] = true;
//		} else {
//			$new['blogposts']['enabled'] = false;
//		}

        update_option( 'ls_bp_hashtags' , $new ) ;

        $updated = true;

	}
?>

	<div class="wrap">
            <h2><?php _e( 'Buddypress Hastags Admin' , 'bp-hashtags' ) ; ?></h2>

		<?php if ( isset($updated) ) : echo "<div id='message' class='updated fade'><p>" . __( 'Settings updated.', 'bp-hashtags' ) . "</p></div>"; endif;

		$data = maybe_unserialize( get_option( 'ls_bp_hashtags' ) ) ;
    ?>

		<form action="<?php echo network_admin_url('/admin.php?page=bp-activity-hashtags-settings') ?>" name="groups-autojoin-form" id="groups-autojoin-form" method="post">

			<h4><?php _e( 'Hashtag Base Slug', 'bp-hashtags' ); ?></h4>
			<table class="form-table">
				<tr>
					<th><label for="ah_tag_slug"><?php _e('Slug','bp-hashtags') ?></label></th>
					<td><input type="text" name="ah_tag_slug" id="ah_tag_slug" value="<?php echo $data['slug']; ?>" /></td>
				</tr>
			</table>
                        <!--
                                                            <h4><?php _e( 'Blog Posts/Comments - in Activity Stream' , 'bp-hashtags' ) ; ?></h4>

                        <!--			<table class="form-table">
                                                            <tr>
                                        <th><label for="ah_activity"><?php _e( 'Enable hashtags in blog activity stream' , 'bp-hashtags' ) ?></label></th>

				</tr>
                                                </table>-->

			<?php if ( !is_multisite() ) { ?>
                        <h4><?php _e( 'Blog Posts/Comments - in Main Blog' , 'bp-hashtags' ) ; ?></h4>

				<table class="form-table">
					<tr>
                                            <th><label for="ah_blog"><?php _e( 'Enable hashtags on main blog' , 'bp-hashtags' ) ?></label></th>
                                                    <td><input type="checkbox" name="ah_blog" id="ah_blog" value="1" <?php if ( $data['blogposts']['enabled'] ) { echo 'checked'; } ?>/></td>
					</tr>
				</table>
			<?php } ?>
                                -->
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
