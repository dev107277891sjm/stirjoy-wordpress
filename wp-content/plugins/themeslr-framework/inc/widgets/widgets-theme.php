<?php 
/**
* 
* [Widgets]
* 
*/

/**
Widget: Contact + Social links
*/
class thecrate_address_social_icons extends WP_Widget {

    function __construct() {
        parent::__construct('thecrate_address_social_icons', esc_attr__('ThemeSLR - Contact + Social links', 'themeslr'),array( 'description' => esc_attr__( 'ThemeSLR - Contact information + Social icons', 'themeslr' ), ) );
    }

    public function widget( $args, $instance ) {

        $widget_title = $instance[ 'widget_title' ];
        $widget_contact_details = $instance[ 'widget_contact_details' ];
        $widget_social_icons = $instance[ 'widget_social_icons' ];

        echo wp_kses_post($args['before_widget']); ?>

        <div class="sidebar-social-networks address-social-links">

            <?php if($widget_title) { ?>
               <h3 class="widget-title"><?php echo esc_attr($widget_title); ?></h3>
            <?php } ?>

            <?php if('on' == $instance['widget_contact_details']) { ?>
                <div class="contact-details">
                    <p><i class="icon-home"></i> <?php echo esc_html(thecrate_redux('thecrate_contact_address')); ?></p>
                    <p><i class="icon-screen-smartphone"></i> <a href="call:<?php echo esc_attr(thecrate_redux('thecrate_contact_email')); ?>"><?php echo esc_attr(thecrate_redux('thecrate_contact_phone')); ?></a></p>
                    <p><i class="icon-envelope-letter"></i> <a href="mailto:<?php echo esc_attr(thecrate_redux('thecrate_contact_email')); ?>"><?php echo esc_attr(thecrate_redux('thecrate_contact_email')); ?></a></p>
                </div>
            <?php } ?>

            <?php if('on' == $instance['widget_social_icons']) { ?>
                <ul class="social-links">
                <?php if ( thecrate_redux('thecrate_social_fb') && thecrate_redux('thecrate_social_fb') != '' ) { ?>
                    <li><a target="_blank" href="<?php echo esc_attr( thecrate_redux('thecrate_social_fb') ) ?>"><i class="fab fa-facebook-f"></i></a></li>
                <?php } ?>
                <?php if ( thecrate_redux('thecrate_social_tw') && thecrate_redux('thecrate_social_tw') != '' ) { ?>
                    <li><a target="_blank" href="https://twitter.com/<?php echo esc_attr( thecrate_redux('thecrate_social_tw') ) ?>"><i class="fab fa-twitter"></i></a></li>
                <?php } ?>
                <?php if ( thecrate_redux('thecrate_social_youtube') && thecrate_redux('thecrate_social_youtube') != '' ) { ?>
                    <li><a target="_blank" href="<?php echo esc_attr( thecrate_redux('thecrate_social_youtube') ) ?>"><i class="fab fa-youtube"></i></a></li>
                <?php } ?>
                <?php if ( thecrate_redux('thecrate_social_pinterest') && thecrate_redux('thecrate_social_pinterest') != '' ) { ?>
                    <li><a target="_blank" href="<?php echo esc_attr( thecrate_redux('thecrate_social_pinterest') ) ?>"><i class="fab fa-pinterest"></i></a></li>
                <?php } ?>
                <?php if ( thecrate_redux('thecrate_social_linkedin') && thecrate_redux('thecrate_social_linkedin') != '' ) { ?>
                    <li><a target="_blank" href="<?php echo esc_attr( thecrate_redux('thecrate_social_linkedin') ) ?>"><i class="fab fa-linkedin"></i></a></li>
                <?php } ?>
                <?php if ( thecrate_redux('thecrate_social_skype') && thecrate_redux('thecrate_social_skype') != '' ) { ?>
                    <li><a target="_blank" href="<?php echo esc_attr( thecrate_redux('thecrate_social_skype') ) ?>"><i class="fab fa-skype"></i></a></li>
                <?php } ?>
                <?php if ( thecrate_redux('thecrate_social_instagram') && thecrate_redux('thecrate_social_instagram') != '' ) { ?>
                    <li><a target="_blank" href="<?php echo esc_attr( thecrate_redux('thecrate_social_instagram') ) ?>"><i class="fab fa-instagram"></i></a></li>
                <?php } ?>
                <?php if ( thecrate_redux('thecrate_social_dribbble') && thecrate_redux('thecrate_social_dribbble') != '' ) { ?>
                    <li><a target="_blank" href="<?php echo esc_attr( thecrate_redux('thecrate_social_dribbble') ) ?>"><i class="fab fa-dribbble"></i></a></li>
                <?php } ?>
                <?php if ( thecrate_redux('thecrate_social_deviantart') && thecrate_redux('thecrate_social_deviantart') != '' ) { ?>
                    <li><a target="_blank" href="<?php echo esc_attr( thecrate_redux('thecrate_social_deviantart') ) ?>"><i class="fab fa-deviantart"></i></a></li>
                <?php } ?>
                <?php if ( thecrate_redux('thecrate_social_digg') && thecrate_redux('thecrate_social_digg') != '' ) { ?>
                    <li><a target="_blank" href="<?php echo esc_attr( thecrate_redux('thecrate_social_digg') ) ?>"><i class="fab fa-digg"></i></a></li>
                <?php } ?>
                <?php if ( thecrate_redux('thecrate_social_flickr') && thecrate_redux('thecrate_social_flickr') != '' ) { ?>
                    <li><a target="_blank" href="<?php echo esc_attr( thecrate_redux('thecrate_social_flickr') ) ?>"><i class="fab fa-flickr"></i></a></li>
                <?php } ?>
                <?php if ( thecrate_redux('thecrate_social_stumbleupon') && thecrate_redux('thecrate_social_stumbleupon') != '' ) { ?>
                    <li><a target="_blank" href="<?php echo esc_attr( thecrate_redux('thecrate_social_stumbleupon') ) ?>"><i class="fab fa-stumbleupon"></i></a></li>
                <?php } ?>
                <?php if ( thecrate_redux('thecrate_social_tumblr') && thecrate_redux('thecrate_social_tumblr') != '' ) { ?>
                    <li><a target="_blank" href="<?php echo esc_attr( thecrate_redux('thecrate_social_tumblr') ) ?>"><i class="fab fa-tumblr"></i></a></li>
                <?php } ?>
                <?php if ( thecrate_redux('thecrate_social_vimeo') && thecrate_redux('thecrate_social_vimeo') != '' ) { ?>
                    <li><a target="_blank" href="<?php echo esc_attr( thecrate_redux('thecrate_social_vimeo') ) ?>"><i class="fab fa-vimeo-square"></i></a></li>
                <?php } ?>
                </ul>
            <?php } ?>
            
        </div>
        <?php echo wp_kses_post($args['after_widget']);
    }

    public function form( $instance ) {

        # Widget Title
        if ( isset( $instance[ 'widget_title' ] ) ) {
            $widget_title = $instance[ 'widget_title' ];
        }?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'widget_title' )); ?>"><?php esc_attr_e( 'Widget Title:', 'themeslr' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'widget_title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'widget_title' )); ?>" type="text" value="<?php echo esc_attr( $widget_title ); ?>">
        </p>
        <p>
            <input type="checkbox" <?php checked($instance['widget_contact_details'], 'on'); ?> id="<?php echo esc_attr($this->get_field_name('widget_contact_details')); ?>" name="<?php echo esc_attr($this->get_field_name('widget_contact_details')); ?>" /> 
            <label for="<?php echo esc_attr($this->get_field_name('widget_contact_details')); ?>"><?php esc_attr_e( 'Show contact informations box', 'themeslr' ); ?></label>
        </p>
        <p>
            <input type="checkbox" <?php checked($instance['widget_social_icons'], 'on'); ?> id="<?php echo esc_attr($this->get_field_name('widget_social_icons')); ?>" name="<?php echo esc_attr($this->get_field_name('widget_social_icons')); ?>" /> 
            <label for="<?php echo esc_attr($this->get_field_name('widget_social_icons')); ?>"><?php esc_attr_e( 'Show social icons', 'themeslr' ); ?></label>
        </p>


        <p><?php esc_attr_e( '* Social Network account must be set from ThemeSLR - Theme Panel.', 'themeslr' ); ?></p>
        <?php 
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['widget_title'] = ( ! empty( $new_instance['widget_title'] ) ) ?  $new_instance['widget_title']  : '';
        $instance['widget_contact_details'] = ( ! empty( $new_instance['widget_contact_details'] ) ) ?  $new_instance['widget_contact_details']  : '';
        $instance['widget_social_icons'] = ( ! empty( $new_instance['widget_social_icons'] ) ) ?  $new_instance['widget_social_icons']  : '';

        return $instance;
    }
}


/**
Widget: Recent Posts with thumbnails
*/
class thecrate_recent_entries_with_thumbnail extends WP_Widget {

    function __construct() {
        parent::__construct('thecrate_recent_entries_with_thumbnail', esc_attr__('ThemeSLR - Recent Posts with thumbnails', 'themeslr'),array( 'description' => esc_attr__( 'ThemeSLR - Recent Posts with thumbnails', 'themeslr' ), ) );
    }

    public function widget( $args, $instance ) {
        $recent_posts_title = $instance[ 'recent_posts_title' ];
        $recent_posts_number = $instance[ 'recent_posts_number' ];

        echo wp_kses_post($args['before_widget']);

        $args_recenposts = array(
                'posts_per_page'   => $recent_posts_number,
                'orderby'          => 'post_date',
                'order'            => 'DESC',
                'post_type'        => 'post',
                'post_status'      => 'publish' 
                );

        $recentposts = get_posts($args_recenposts);
        $myContent  = "";
        $myContent .= '<h3 class="widget-title">'.$recent_posts_title.'</h3>';
        $myContent .= '<ul>';

        foreach ($recentposts as $post) {
            $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ),'thecrate_post_widget_pic70x70' );

            $myContent .= '<li class="row">';
                $myContent .= '<div class="col-md-3 post-thumbnail relative">';
                    $myContent .= '<a href="'. get_permalink($post->ID) .'">';
                        if($thumbnail_src) { $myContent .= '<img src="'. $thumbnail_src[0] . '" alt="'. $post->post_title .'" />';
                        }else{ $myContent .= '<img src="http://placehold.it/70x70" alt="'. $post->post_title .'" />'; }
                        $myContent .= '<div class="thumbnail-overlay absolute">';
                            $myContent .= '<i class="icon-magnifier icons absolute"></i>';
                        $myContent .= '</div>';
                    $myContent .= '</a>';
                $myContent .= '</div>';
                $myContent .= '<div class="col-md-9 post-details">';
                    $myContent .= '<a href="'. get_permalink($post->ID) .'">'. $post->post_title.'</a>';
                    $myContent .= '<span class="post-date">'.get_the_date(get_option( 'date_format'), $post->ID ).'</span>';
                $myContent .= '</div>';
            $myContent .= '</li>';
        }
        $myContent .= '</ul>';

        echo wp_kses_post($myContent);
        echo wp_kses_post($args['after_widget']);
    }

    public function form( $instance ) {
        
        # Widget Title
        if ( isset( $instance[ 'recent_posts_title' ] ) ) {
            $recent_posts_title = $instance[ 'recent_posts_title' ];
        } else {
            $recent_posts_title = esc_attr__( 'Recent posts', 'themeslr' );
        }

        # Number of posts
        if ( isset( $instance[ 'recent_posts_number' ] ) ) {
            $recent_posts_number = $instance[ 'recent_posts_number' ];
        } else {
            $recent_posts_number = '5';
        }
        ?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'recent_posts_title' )); ?>"><?php esc_attr_e( 'Widget Title:', 'themeslr' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'recent_posts_title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'recent_posts_title' )); ?>" type="text" value="<?php echo esc_attr( $recent_posts_title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'recent_posts_number' )); ?>"><?php esc_attr_e( 'Number of posts:', 'themeslr' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'recent_posts_number' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'recent_posts_number' )); ?>" type="text" value="<?php echo esc_attr( $recent_posts_number ); ?>">
        </p>
        <?php 
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['recent_posts_title'] = ( ! empty( $new_instance['recent_posts_title'] ) ) ?  $new_instance['recent_posts_title']  : '';
        $instance['recent_posts_number'] = ( ! empty( $new_instance['recent_posts_number'] ) ) ? strip_tags( $new_instance['recent_posts_number'] ) : '';
        return $instance;
    }

} 


/**
Widget: Post thumbnails slider
*/
class thecrate_post_thumbnails_slider extends WP_Widget {

    function __construct() {
        parent::__construct('thecrate_post_thumbnails_slider', esc_attr__('ThemeSLR - Post thumbnails slider', 'themeslr'),array( 'description' => esc_attr__( 'ThemeSLR - Post thumbnails slider', 'themeslr' ), ) );
    }

    public function widget( $args, $instance ) {
        $recent_posts_title = $instance[ 'recent_posts_title' ];
        $recent_posts_number = $instance[ 'recent_posts_number' ];

        echo wp_kses_post($args['before_widget']);

        $args_recenposts = array(
                'posts_per_page'   => $recent_posts_number,
                'orderby'          => 'post_date',
                'order'            => 'DESC',
                'post_type'        => 'post',
                'post_status'      => 'publish' 
                );

        $recentposts = get_posts($args_recenposts);
        $myContent  = "";
        $myContent .= '<h3 class="widget-title">'.$recent_posts_title.'</h3>';
        $myContent .= '<div class="slider_holder relative">';
            $myContent .= '<div class="slider_navigation absolute">';
                $myContent .= '<a class="btn prev pull-left"><i class="fa fa-angle-left"></i></a>';
                $myContent .= '<a class="btn next pull-right"><i class="fa fa-angle-right"></i></a>';
            $myContent .= '</div>';
            $myContent .= '<div class="post_thumbnails_slider owl-carousel owl-theme">';

            foreach ($recentposts as $post) {
                $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ),'thecrate_post_pic700x450' );
                $myContent .= '<div class="item">';
                    $myContent .= '<a href="'. get_permalink($post->ID) .'">';
                        if($thumbnail_src) { $myContent .= '<img src="'. $thumbnail_src[0] . '" alt="'. $post->post_title .'" />';
                        }else{ $myContent .= '<img src="http://placehold.it/700x450" alt="'. $post->post_title .'" />'; }
                    $myContent .= '</a>';
                $myContent .= '</div>';
            }
            $myContent .= '</div>';
        $myContent .= '</div>';

        echo wp_kses_post($myContent);
        echo wp_kses_post($args['after_widget']);
    }

    public function form( $instance ) {
        
        # Widget Title
        if ( isset( $instance[ 'recent_posts_title' ] ) ) {
            $recent_posts_title = $instance[ 'recent_posts_title' ];
        } else {
            $recent_posts_title = esc_attr__( 'Post thumbnails slider', 'themeslr' );
        }

        # Number of posts
        if ( isset( $instance[ 'recent_posts_number' ] ) ) {
            $recent_posts_number = $instance[ 'recent_posts_number' ];
        } else {
            $recent_posts_number = '5';
        }
        ?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'recent_posts_title' )); ?>"><?php esc_attr_e( 'Widget Title:', 'themeslr' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'recent_posts_title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'recent_posts_title' )); ?>" type="text" value="<?php echo esc_attr( $recent_posts_title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'recent_posts_number' )); ?>"><?php esc_attr_e( 'Number of posts:', 'themeslr' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'recent_posts_number' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'recent_posts_number' )); ?>" type="text" value="<?php echo esc_attr( $recent_posts_number ); ?>">
        </p>
        <?php 
    }


    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['recent_posts_title'] = ( ! empty( $new_instance['recent_posts_title'] ) ) ?  $new_instance['recent_posts_title']  : '';
        $instance['recent_posts_number'] = ( ! empty( $new_instance['recent_posts_number'] ) ) ? strip_tags( $new_instance['recent_posts_number'] ) : '';
        return $instance;
    }

} 



/**
Widget: Social Share Icons
*/
class thecrate_social_share extends WP_Widget {

    function __construct() {
        parent::__construct('thecrate_social_share', esc_attr__('ThemeSLR - Social Accounts (Profiles)', 'themeslr'),array( 'description' => esc_attr__( 'ThemeSLR - Social Accounts (Profiles)', 'themeslr' ), ) );
    }

    public function widget( $args, $instance ) {

        $widget_title = $instance[ 'widget_title' ];


        $themeslr_before_text = ((array_key_exists('themeslr_before_text', $instance))?$instance['themeslr_before_text']:"");
        $facebook = $instance['share-facebook'] ? 'true' : 'false';
        $twitter = $instance['share-twitter'] ? 'true' : 'false';
        $linkedin = $instance['share-linkedin'] ? 'true' : 'false';
        $digg = $instance['share-digg'] ? 'true' : 'false';
        $pinterest = $instance['share-pinterest'] ? 'true' : 'false';
        $skype = $instance['share-skype'] ? 'true' : 'false';
        $instagram = $instance['share-instagram'] ? 'true' : 'false';
        $youtube = $instance['share-youtube'] ? 'true' : 'false';
        $tiktok = ((array_key_exists('share-tiktok', $instance))?$instance['share-tiktok']:"false");

        if ($instance['share-btns-alignment']) {
            $alignment = $instance['share-btns-alignment'];
        }else{
            $alignment = 'text-left';
        }

        echo wp_kses_post($args['before_widget']);
        ?>

        <div class="sidebar-share-social-links">
            <?php if($widget_title) { ?>
               <h3 class="widget-title"><?php echo esc_attr($widget_title); ?></h3>
            <?php } ?>
            <ul class="share-social-links <?php echo esc_attr($alignment); ?>">
                <li class="follow-us-label">
                    <?php 
                    if ($themeslr_before_text) {
                        echo esc_html($themeslr_before_text);
                    }else{
                        echo esc_html__('Follow us: ', 'themeslr'); 
                    } ?>
                </li>
                <?php if('on' == $instance['share-facebook'] ) { ?>
                    <?php if ( thecrate_redux('thecrate_social_fb') && thecrate_redux('thecrate_social_fb') != '' ) { ?>
                        <li class="facebook">
                            <a href="<?php echo esc_url( thecrate_redux('thecrate_social_fb') ) ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        </li>
                    <?php } ?>
                <?php } if('on' == $instance['share-twitter'] ) {?>
                    <?php if ( thecrate_redux('thecrate_social_fb') && thecrate_redux('thecrate_social_fb') != '' ) { ?>
                        <li class="twitter">
                            <a href="https://twitter.com/<?php echo esc_attr( thecrate_redux('thecrate_social_tw') ) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                        </li>
                    <?php } ?>
                <?php } if('on' == $instance['share-linkedin'] ) {?>
                    <?php if ( thecrate_redux('thecrate_social_linkedin') && thecrate_redux('thecrate_social_linkedin') != '' ) { ?>
                        <li class="linkedin">
                            <a href="<?php echo esc_url( thecrate_redux('thecrate_social_linkedin') ) ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                        </li>
                    <?php } ?>
                <?php } if('on' == $instance['share-digg'] ) {?>
                    <?php if ( thecrate_redux('thecrate_social_digg') && thecrate_redux('thecrate_social_digg') != '' ) { ?>
                        <li class="digg">
                            <a href="<?php echo esc_url( thecrate_redux('thecrate_social_digg') ) ?>" target="_blank"><i class="fab fa-digg"></i></a>
                        </li>
                    <?php } ?>
                <?php } if('on' == $instance['share-pinterest'] ) {?>
                    <?php if ( thecrate_redux('thecrate_social_pinterest') && thecrate_redux('thecrate_social_pinterest') != '' ) { ?>
                        <li class="pinterest">
                            <a href="<?php echo esc_url( thecrate_redux('thecrate_social_pinterest') ) ?>" target="_blank"><i class="fab fa-pinterest"></i></a>
                        </li>
                    <?php } ?>
                <?php } if('on' == $instance['share-skype'] ) {?>
                    <?php if ( thecrate_redux('thecrate_social_skype') && thecrate_redux('thecrate_social_skype') != '' ) { ?>
                        <li class="pinterest">
                            <a href="<?php echo esc_url( thecrate_redux('thecrate_social_skype') ) ?>" target="_blank"><i class="fab fa-skype"></i></a>
                        </li>
                    <?php } ?>
                <?php } if('on' == $instance['share-instagram'] ) {?>
                    <?php if ( thecrate_redux('thecrate_social_instagram') && thecrate_redux('thecrate_social_instagram') != '' ) { ?>
                        <li class="pinterest">
                            <a href="<?php echo esc_url( thecrate_redux('thecrate_social_instagram') ) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                        </li>
                    <?php } ?>
                <?php } if('on' == $instance['share-youtube'] ) {?>
                    <?php if ( thecrate_redux('thecrate_social_youtube') && thecrate_redux('thecrate_social_youtube') != '' ) { ?>
                        <li class="pinterest">
                            <a href="<?php echo esc_url( thecrate_redux('thecrate_social_youtube') ) ?>" target="_blank"><i class="fab fa-youtube"></i></a>
                        </li>
                    <?php } ?>
                <?php } if('on' == $tiktok ) {?>
                    <?php if ( thecrate_redux('thecrate_social_tiktok') && thecrate_redux('thecrate_social_tiktok') != '' ) { ?>
                        <li class="tiktok">
                            <a href="<?php echo esc_url( thecrate_redux('thecrate_social_tiktok') ) ?>" target="_blank"><i class="fab fa-tiktok"></i></a>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
        </div>
        <?php 
        echo wp_kses_post($args['after_widget']);
    }

    public function form( $instance ) {

        # Widget Title
        if ( isset( $instance[ 'widget_title' ] ) ) {
            $widget_title = $instance[ 'widget_title' ];
        } else {
            $widget_title = esc_attr__( 'Social icons', 'themeslr' );
        }
        if ( isset( $instance[ 'themeslr_before_text' ] ) ) {
            $themeslr_before_text = $instance[ 'themeslr_before_text' ];
        } else {
            $themeslr_before_text = esc_html__( 'Follow us: ', 'themeslr' );
        }
        if ( isset( $instance[ 'share-facebook' ] ) ) { $fb = $instance[ 'share-facebook' ]; } else { $fb = 'off'; }
        if ( isset( $instance[ 'share-twitter' ] ) ) { $tw = $instance[ 'share-twitter' ]; } else { $tw = 'off'; }
        if ( isset( $instance[ 'share-linkedin' ] ) ) { $linkedin = $instance[ 'share-linkedin' ]; } else { $linkedin = 'off'; }
        if ( isset( $instance[ 'share-digg' ] ) ) { $digg = $instance[ 'share-digg' ]; } else { $digg = 'off'; }
        if ( isset( $instance[ 'share-pinterest' ] ) ) { $pinterest = $instance[ 'share-pinterest' ]; } else { $pinterest = 'off'; }
        if ( isset( $instance[ 'share-skype' ] ) ) { $skype = $instance[ 'share-skype' ]; } else { $skype = 'off'; }
        if ( isset( $instance[ 'share-instagram' ] ) ) { $instagram = $instance[ 'share-instagram' ]; } else { $instagram = 'off'; }
        if ( isset( $instance[ 'share-youtube' ] ) ) { $youtube = $instance[ 'share-youtube' ]; } else { $youtube = 'off'; }
        if ( isset( $instance[ 'share-tiktok' ] ) ) { $tiktok = $instance[ 'share-tiktok' ]; } else { $tiktok = 'off'; }
        if ( isset( $instance[ 'share-btns-alignment' ] ) ) { $alignment = $instance[ 'share-btns-alignment' ]; } else { $alignment = 'text-left'; }
        ?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'widget_title' )); ?>"><?php esc_attr_e( 'Widget Title:', 'themeslr' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'widget_title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'widget_title' )); ?>" type="text" value="<?php echo esc_attr( $widget_title ); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'themeslr_before_text' )); ?>"><?php esc_attr_e( 'Before Text:', 'themeslr' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'themeslr_before_text' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'themeslr_before_text' )); ?>" type="text" value="<?php echo esc_attr( $themeslr_before_text ); ?>">
            <i><?php esc_attr_e( 'A text to replace the "Follow us" text. If this is left empty, "Follow us" text will be shown.', 'themeslr' ); ?></i>
        </p>

        <p><?php esc_html_e( 'Check what Social Profiles do you want to display', 'themeslr' ); ?></p>
        <p>
            <input class="checkboxsocial" type="checkbox" <?php checked($fb, 'on'); ?> id="<?php echo esc_attr($this->get_field_name('share-facebook')); ?>" name="<?php echo esc_attr($this->get_field_name('share-facebook')); ?>" /> 
            <label for="<?php echo esc_attr($this->get_field_name('share-facebook')); ?>"><?php esc_attr_e( 'Facebook', 'themeslr' ); ?></label>
        </p>
        <p>
            <input class="checkboxsocial" type="checkbox" <?php checked($tw, 'on'); ?> id="<?php echo esc_attr($this->get_field_name('share-twitter')); ?>" name="<?php echo esc_attr($this->get_field_name('share-twitter')); ?>" /> 
            <label for="<?php echo esc_attr($this->get_field_name('share-twitter')); ?>"><?php esc_attr_e( 'Twitter', 'themeslr' ); ?></label>
        </p>
        <p>
            <input class="checkboxsocial" type="checkbox" <?php checked($linkedin, 'on'); ?> id="<?php echo esc_attr($this->get_field_name('share-linkedin')); ?>" name="<?php echo esc_attr($this->get_field_name('share-linkedin')); ?>" /> 
            <label for="<?php echo esc_attr($this->get_field_name('share-linkedin')); ?>"><?php esc_attr_e( 'Linkedin', 'themeslr' ); ?></label>
        </p>
        <p>
            <input class="checkboxsocial" type="checkbox" <?php checked($digg, 'on'); ?> id="<?php echo esc_attr($this->get_field_name('share-digg')); ?>" name="<?php echo esc_attr($this->get_field_name('share-digg')); ?>" /> 
            <label for="<?php echo esc_attr($this->get_field_name('share-digg')); ?>"><?php esc_attr_e( 'Digg', 'themeslr' ); ?></label>
        </p>
        <p>
            <input class="checkboxsocial" type="checkbox" <?php checked($pinterest, 'on'); ?> id="<?php echo esc_attr($this->get_field_name('share-pinterest')); ?>" name="<?php echo esc_attr($this->get_field_name('share-pinterest')); ?>" /> 
            <label for="<?php echo esc_attr($this->get_field_name('share-pinterest')); ?>"><?php esc_attr_e( 'Pinterest', 'themeslr' ); ?></label>
        </p>
        <p>
            <input class="checkboxsocial" type="checkbox" <?php checked($skype, 'on'); ?> id="<?php echo esc_attr($this->get_field_name('share-skype')); ?>" name="<?php echo esc_attr($this->get_field_name('share-skype')); ?>" /> 
            <label for="<?php echo esc_attr($this->get_field_name('share-skype')); ?>"><?php esc_attr_e( 'Skype', 'themeslr' ); ?></label>
        </p>
        <p>
            <input class="checkboxsocial" type="checkbox" <?php checked($instagram, 'on'); ?> id="<?php echo esc_attr($this->get_field_name('share-instagram')); ?>" name="<?php echo esc_attr($this->get_field_name('share-instagram')); ?>" /> 
            <label for="<?php echo esc_attr($this->get_field_name('share-instagram')); ?>"><?php esc_attr_e( 'Instagram', 'themeslr' ); ?></label>
        </p>
        <p>
            <input class="checkboxsocial" type="checkbox" <?php checked($youtube, 'on'); ?> id="<?php echo esc_attr($this->get_field_name('share-youtube')); ?>" name="<?php echo esc_attr($this->get_field_name('share-youtube')); ?>" /> 
            <label for="<?php echo esc_attr($this->get_field_name('share-youtube')); ?>"><?php esc_attr_e( 'YouTube', 'themeslr' ); ?></label>
        </p>
        <p>
            <input class="checkboxsocial" type="checkbox" <?php checked($tiktok, 'on'); ?> id="<?php echo esc_attr($this->get_field_name('share-tiktok')); ?>" name="<?php echo esc_attr($this->get_field_name('share-tiktok')); ?>" /> 
            <label for="<?php echo esc_attr($this->get_field_name('share-tiktok')); ?>"><?php esc_attr_e( 'TikTok', 'themeslr' ); ?></label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_name('share-btns-alignment')); ?>"><?php esc_attr_e( 'Alignment', 'themeslr' ); ?></label>
            <select name="<?php echo esc_attr($this->get_field_name('share-btns-alignment')); ?>">
                <option <?php echo (($alignment=='text-left')?'selected="selected"':""); ?> value="<?php echo 'text-left'; ?>"><?php echo __('Left', 'themeslr'); ?></option>
                <option <?php echo (($alignment=='text-center')?'selected="selected"':""); ?> value="<?php echo 'text-center'; ?>"><?php echo __('Center', 'themeslr'); ?></option>
                <option <?php echo (($alignment=='text-right')?'selected="selected"':""); ?> value="<?php echo 'text-right'; ?>"><?php echo __('Right', 'themeslr'); ?></option>
            </select>
        </p>

        <p><?php echo __('Note: In order to see the social profiles, you should make sure that all of the selected ones are also filled (Check the Theme Panel - Social Media)', 'themeslr'); ?>
        <?php 
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['widget_title'] = ( ! empty( $new_instance['widget_title'] ) ) ?  $new_instance['widget_title']  : '';
        $instance['themeslr_before_text'] = ( ! empty( $new_instance['themeslr_before_text'] ) ) ?  $new_instance['themeslr_before_text']  : '';
        $instance['share-facebook'] = ( ! empty( $new_instance['share-facebook'] ) ) ?  $new_instance['share-facebook']  : '';
        $instance['share-twitter'] = ( ! empty( $new_instance['share-twitter'] ) ) ?  $new_instance['share-twitter']  : '';
        $instance['share-linkedin'] = ( ! empty( $new_instance['share-linkedin'] ) ) ?  $new_instance['share-linkedin']  : '';
        $instance['share-digg'] = ( ! empty( $new_instance['share-digg'] ) ) ?  $new_instance['share-digg']  : '';
        $instance['share-pinterest'] = ( ! empty( $new_instance['share-pinterest'] ) ) ?  $new_instance['share-pinterest']  : '';
        $instance['share-skype'] = ( ! empty( $new_instance['share-skype'] ) ) ?  $new_instance['share-skype']  : '';
        $instance['share-instagram'] = ( ! empty( $new_instance['share-instagram'] ) ) ?  $new_instance['share-instagram']  : '';
        $instance['share-youtube'] = ( ! empty( $new_instance['share-youtube'] ) ) ?  $new_instance['share-youtube']  : '';
        $instance['share-tiktok'] = ( ! empty( $new_instance['share-tiktok'] ) ) ?  $new_instance['share-tiktok']  : '';
        $instance['share-btns-alignment'] = ( ! empty( $new_instance['share-btns-alignment'] ) ) ?  $new_instance['share-btns-alignment']  : '';

        return $instance;
    }
}


/**
Register Widgets
*/
function thecrate_register_widgets() {
    register_widget( 'thecrate_address_social_icons' );
    register_widget( 'thecrate_social_share' );
    register_widget( 'thecrate_recent_entries_with_thumbnail' );
    register_widget( 'thecrate_post_thumbnails_slider' );

}
add_action( 'widgets_init', 'thecrate_register_widgets' );