<?php
/**
||-> Shortcode: Knowledge List Accordion
*/
function themeslr_carousel_everything_holder($params,  $content = NULL) {
    extract( shortcode_atts( 
        array(
            'slide_bg_image'       =>'',
            'heading_title'       =>'',
        ), $params ) );

  $html = '';

  $id = 'carousel_everything_'.uniqid();

  $bg_image_style = '';
  if ($slide_bg_image) {
    $bg_image_url = wp_get_attachment_url( $slide_bg_image );
    $bg_image_style = 'background-image:url('.$bg_image_url.');background-position: center;background-repeat: no-repeat;background-size: auto 100%;';
  }

  $html .= '<div style="'.$bg_image_style.'" class="tslr-carousel-everything-shortcode-group">';
    $html .= '<div class="tslr-carousel-everything-shortcode owl-carousel owl-theme" id="'.$id.'">';
      $html .= do_shortcode($content);
    $html .= '</div>';
  $html .= '</div>';

  return $html;
}
add_shortcode('themeslr_carousel_everything_holder_shortcode', 'themeslr_carousel_everything_holder');
/**
||-> Shortcode: Child Shortcode v1
*/
function themeslr_carousel_everything_inner_content($params, $content = NULL) {
  extract( shortcode_atts( 
    array(
        'slide_heading' => '',
        'slide_heading_color' => '',
        'slide_subheading' => '',
        'slide_subheading_color' => '',
        'slide_paragraph' => '',
        'slide_paragraph_color' => '',
        'slide_button' => '',
        'slide_button_link' => '',
    ), $params )
  );

  $html = '';

  $html .= '<div class="relative tslr-carousel-everything-slide">';
    $html .= '<h2 class="tslr-carousel-everything-slide--heading" style="color:'.$slide_heading_color.';">'.$slide_heading.'</h2>';
    $html .= '<h5 class="tslr-carousel-everything-slide--subheading" style="color:'.$slide_subheading_color.';">'.$slide_subheading.'</h5>';
    $html .= '<p class="tslr-carousel-everything-slide--paragraph" style="color:'.$slide_paragraph_color.';">'.$slide_paragraph.'</p>';
    $html .= '<div class="tslr-carousel-everything-slide--button">'.do_shortcode('[mt-bootstrap-button btn_text="'.$slide_button.'" btn_url="'.$slide_button_link.'" btn_size="btn btn-lg" align="text-left" color="#f26226" border_color="#2e62af" text_color="#fffffa"]').'</div>';
  $html.='</div>';

  return $html;
}
add_shortcode('themeslr_carousel_everything_inner_content_shortcode', 'themeslr_carousel_everything_inner_content');
