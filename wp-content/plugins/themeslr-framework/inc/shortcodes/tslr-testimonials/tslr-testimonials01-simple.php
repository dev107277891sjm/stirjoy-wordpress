<?php

function themeslr_shortcode_testimonials_simple($params, $content) {
    extract( shortcode_atts( 
        array(
            'testimonial01_bg_image'   =>'',
            'testimonial01_color'   =>'',
            'testimonial01_bg'   =>'',
            'number'                =>'',
            'visible_items'         =>''
        ), $params ) );


    // text color
    if ($testimonial01_color) {
      $text_color = $testimonial01_color;
    }else{
      $text_color = '#000';
    }

    // bg color
    if ($testimonial01_bg) {
      $bg_color = $testimonial01_bg;
    }else{
      $bg_color = 'transparent';
    }

    $testimonial01_bg_image_style = '';
    if ($testimonial01_bg_image) {
      $testimonial01_bg_image_url = wp_get_attachment_url( $testimonial01_bg_image );
      $testimonial01_bg_image_style = 'background-image:url('.$testimonial01_bg_image_url.');background-position: center;background-repeat: no-repeat;background-size: 80%;';
    }

    $html = '';
 
    $html .= '<div style="background-color: '.$bg_color.';'.$testimonial01_bg_image_style.'" class="">';
      $html .= '<div class="tslr-testimonials-shortcode testimonials-container-'.$visible_items.' owl-carousel owl-theme">';
        
        $args_testimonials = array(
          'posts_per_page'   => $number,
          'orderby'          => 'post_date',
          'order'            => 'DESC',
          'post_type'        => 'testimonial',
          'post_status'      => 'publish' 
        );

        $testimonials = get_posts($args_testimonials);

          foreach ($testimonials as $testimonial) {
            #metaboxes
            $metabox_job_position = get_post_meta( $testimonial->ID, 'job-position', true );
            $metabox_company = get_post_meta( $testimonial->ID, 'company', true );
            $testimonial_id = $testimonial->ID;
            $content_post   = get_post($testimonial_id);
            $content        = $content_post->post_content;
            $content        = apply_filters('the_content', $content);
            $content        = str_replace(']]>', ']]&gt;', $content);
            #thumbnail
            $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $testimonial->ID ),'connection_testimonials_150x150' );
            
              $html.='<div class="item">';
                $html.='<div class="vc_col-md-12 relative testimonial01_item_parent">
                          <div class="testimonial01_item text-left" style="color: '.$text_color.';">
                              <svg width="96" height="18" viewBox="0 0 96 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 0H17.9381V17.9381H0V0ZM19.4332 0H37.3713V17.9381H19.4332V0ZM38.8655 0H56.8036V17.9381H38.8655V0ZM58.2987 0H76.2368V17.9381H58.2987V0ZM77.7319 0H95.67V17.9381H77.7319V0Z" fill="#00B67A"/><path d="M8.96949 12.0893L11.6979 11.3969L12.8369 14.9101L8.96949 12.0884V12.0893ZM15.2478 7.54831H10.4458L8.96949 3.02612L7.49318 7.54831H2.69116L6.57745 10.3511L5.10114 14.8724L8.98832 12.0696L11.3795 10.3511L15.2478 7.54831ZM28.4027 12.0893L31.1302 11.3969L32.2701 14.9101L28.4027 12.0884V12.0893ZM34.681 7.54831H29.8781L28.4027 3.02612L26.9264 7.54831H22.1244L26.0107 10.3511L24.5344 14.8724L28.4215 12.0696L30.8127 10.3511L34.681 7.54831ZM47.835 12.0893L50.5634 11.3969L51.7025 14.9101L47.835 12.0884V12.0893ZM54.1133 7.54831H49.3122L47.8359 3.02612L46.3605 7.54831H41.5576L45.4448 10.3511L43.9685 14.8724L47.8547 12.0696L50.2468 10.3511L54.1142 7.54831H54.1133ZM67.2682 12.0893L69.9966 11.3969L71.1357 14.9101L67.2682 12.0884V12.0893ZM73.5465 7.54831H68.7445L67.2682 3.02612L65.7919 7.54831H60.9899L64.8762 10.3511L63.4008 14.8724L67.2871 12.0696L69.6782 10.3511L73.5465 7.54831ZM86.7014 12.0893L89.4289 11.3969L90.5689 14.9101L86.7014 12.0884V12.0893ZM92.9798 7.54831H88.1768L86.7014 3.02612L85.2251 7.54831H80.4231L84.3094 10.3511L82.8331 14.8724L86.7203 12.0696L89.1114 10.3511L92.9798 7.54831Z" fill="white"/></svg>
                              <div class="testimonail01-content" style="border-color: '.$testimonial01_color.'">'.strip_tags($content).'</div>
                              <h4><strong>'. $testimonial->post_title .'</strong> - '. $metabox_job_position .'</h4>
                            </div> 
                          </div> 
                      </div>';
            

        }
    $html .= '</div>
    	</div>';
    return $html;

}
add_shortcode('testimonials01-simple', 'themeslr_shortcode_testimonials_simple');

