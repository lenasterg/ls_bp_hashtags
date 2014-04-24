<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit ;

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
                __( 'Popular Hashtags across network' , 'bp-hashtags' ) , array ( 'description' => __( 'The most commonly used hashtags. Only for public activity and from groups of logged in user.' , 'bp-hashtags' ) ,
            'classname' => 'bp_hashtags_widget' )
        ) ;
    }

    function widget( $args , $instance ) {

        $title = apply_filters( 'widget_title' , $instance[ 'title' ] ) ;
        if ( "" == $title ) {
            $title = __( 'Popular Hashtags across network' , 'bp-hashtags' ) ;
        }
        // before and after widget arguments are defined by themes
        echo $args[ 'before_widget' ] ;
        if ( ! empty( $title ) ) {
            echo $args[ 'before_title' ] . sanitize_text_field( $title ) . $args[ 'after_title' ] ;
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
            $title = __( 'Popular Hashtags across network' , 'bp-hashtags' ) ;
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
        $instance[ 'title' ] = ( ! empty( $new_instance[ 'title' ] ) ) ? sanitize_text_field( $new_instance[ 'title' ] ) : '' ;
                return $instance ;
            }

}

/**
 * Current group hashtag widget
 * @version 1, 22/4/2014, stergatu
 */
class BP_Hashtags_CurrentGroup_Widget extends WP_Widget {

    function __construct() {

        parent::__construct(
                // Base ID of your widget
                'bp_hashtags_current_group_widget' ,
                // Widget name will appear in UI
                __( 'Hashtags in this group' , 'bp-hashtags' ) , array ( 'description' => __( 'The most commonly used hashtags in this group' , 'bp-hashtags' ) ,
            'classname' => 'bp_hashtags_widget' )
        ) ;
    }

    function widget( $args , $instance ) {
        $bp = buddypress() ;
        $instance[ 'group_id' ] = bp_get_current_group_id() ;

        if ( $instance[ 'group_id' ] > 0 ) {
            $group = $bp->groups->current_group ;
            // If the group  public, or the user is super_admin or the user is member of group
            if ( ($group->status == 'public') || (is_super_admin()) || (groups_is_user_member( bp_loggedin_user_id() , $group_id )) ) {
                extract( $args ) ;
                $title = apply_filters( 'widget_title' , empty( $instance[ 'title' ] ) ? sprintf( __( 'Hashtags from the group' , 'bp-group-documents' ) , $this->bp_group_documents_name ) : sanitize_text_field( $instance[ 'title' ] )  ) ;
                        echo $before_widget . $before_title . $title . $after_title ;

                        do_action( 'ls_bp_hashtags_current_group_widget_before_html' ) ;
                        echo ls_bp_hashtags_generate_cloud() ;
                        echo $after_widget ;
                }
            }
        }

        function update( $new_instance , $old_instance ) {
            do_action( 'bp_hashtags_current_group_widget_update' ) ;
                $instance = $old_instance ;
            $instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] ) ;

            return $instance ;
        }

        function form( $instance ) {
            do_action( 'bp_hashtags_current_group_widget_form' ) ;
                $title = sanitize_text_field( $instance[ 'title' ] ) ;
                ?>

        <p><label><?php _e( 'Title:' , 'bp-hashtags' ) ; ?></label><input class="widefat" id="<?php echo $this->get_field_id( 'title' ) ; ?>" name="<?php echo $this->get_field_name( 'title' ) ; ?>" type="text" value="<?php echo $title ; ?>" /></p>
        <!--           <p><label><?php _e( 'Number of items to show:' , 'bp-group-documents' ) ; ?></label> <input class="widefat" id="<?php echo $this->get_field_id( 'num_items' ) ; ?>" name="<?php echo $this->get_field_name( 'num_items' ) ; ?>" type="text" value="<?php echo absint( $num_items ) ; ?>" style="width: 30%" /></p>-->
                <?php
        }

    }
