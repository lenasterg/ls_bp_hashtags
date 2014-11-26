<?php

/**
 *
 * @param array $atts, see wp_generate_tag_cloud() for args values
 * @return string
 */
function ls_bp_hashtags_shortcode( $atts = array () ) {

    do_utcw( array ( 'strategy' => new utcwStrategyBpHashtags() ) ) ;
//    return ls_bp_hashtags_generate_cloud( $atts ) ;
}

add_shortcode( 'ls_bp_hashtags' , 'ls_bp_hashtags_shortcode' ) ;
