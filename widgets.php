<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit ;

// Class wpb_widget ends here
// Register and load the widget
function ls_bp_hashtags_popular_widget_load() {
    register_widget( 'BP_Hashtags_Popular_Widget' ) ;
}

add_action( 'widgets_init' , 'ls_bp_hashtags_popular_widget_load' ) ;

//if ( get_current_blog_id() == '1' ) {
//    add_action( 'widgets_init' , create_function( '' , 'register_widget("BP_Hashtags_CurrentGroup_Widget");' ) ) ;
//}
//
//if ( is_active_widget( false , false , 'bp_hashtags_popular_widget' ) ) {
//    add_action( 'wp_enqueue_scripts' , 'bp_group_documents_add_my_stylesheet' ) ;
//}
//
///**
// * Enqueue plugin style-file
// */
//function bp_group_documents_add_my_stylesheet() {
//    // Respects SSL, Style.css is relative to the current file
//    wp_register_style( 'bp-hashtags' , WP_PLUGIN_URL . '/' . BP_GROUP_DOCUMENTS_DIR . '/css/style.css' , false , BP_GROUP_DOCUMENTS_VERSION ) ;
//    wp_enqueue_style( 'bp-hashtags' ) ;
//}


class BP_Hashtags_Popular_Widget extends WP_Widget {

    function __construct() {

        parent::__construct(
                // Base ID of your widget
                'bp_hashtags_popular_widget' ,
                // Widget name will appear in UI
                __( 'Popular Hashtags' , 'bp-hashtags' ) , array ( 'description' => __( 'The most commonly used hashtags. Only for public activity' , 'bp-hashtags' ) ,
            'classname' => 'bp_hashtags_widget' )
        ) ;
//
//        if ( is_active_widget( false , false , $this->id_base ) ) {
//            add_action( '' , 'bp_group_documents_add_my_stylesheet' ) ;
//        }
    }

    function widget( $args , $instance ) {

        $title = apply_filters( 'widget_title' , $instance[ 'title' ] ) ;
        if ( "" == $title ) {
            $title = __( 'Popular Hashtags' , 'bp-hashtags' ) ;
        }
        // before and after widget arguments are defined by themes
        echo $args[ 'before_widget' ] ;
        if ( ! empty( $title ) ) {
            echo $args[ 'before_title' ] . $title . $args[ 'after_title' ] ;
        }
        do_action( 'bp_hashtags_popular_widget_before_html' ) ;
        echo ls_bp_hashtags_generate_cloud() ;
        do_action( 'bp_hashtags_popular_widget_after_html' ) ;
        echo $args[ 'after_widget' ] ;
    }

// Widget Backend
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ] ;
        } else {
            $title = __( 'Popular Hashtags' , 'bp-hashtags' ) ;
        }
// Widget admin form
        ?>
                <p>
            <label for="<?php echo $this->get_field_id( 'title' ) ; ?>"><?php _e( 'Title:' ) ; ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ) ; ?>" name="<?php echo $this->get_field_name( 'title' ) ; ?>" type="text" value="<?php echo esc_attr( $title ) ; ?>" />
        </p>
        <?php
    }

// Updating widget replacing old instances with new
    public function update( $new_instance , $old_instance ) {
        $instance = array () ;
        $instance[ 'title' ] = ( ! empty( $new_instance[ 'title' ] ) ) ? strip_tags( $new_instance[ 'title' ] ) : '' ;
    
        return $instance ;
    }

}
