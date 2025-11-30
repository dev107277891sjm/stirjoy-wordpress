<?php


/**

||-> Shortcode: Bootstrap Buttons

*/
function themeslr_btn_shortcode($params, $content) {
    extract( shortcode_atts( 
        array(
            'btn_text'      => '',
            'btn_url'       => '',
            'btn_size'      => '',
            'btn_style'      => '',
            'display_type'      => '',
            'target'      => '',
            'align'      => '',
            'color'      => '',
            'bg_color_hover'      => '',
            'text_color'      => '',
            'text_color_hover'      => '',
        ), $params ) ); 
    $content = '';

  if ($display_type) {
    $display_type = 'inline-block';
  }

  if ($target) {
    $target = 'target="_blank"';
  }

  $content .= '<div class="'.$align.' '.$btn_style.' themeslr_button themeslr_button_shortcode '.$display_type.'">';
      $content .= '<a '.$target.' data-text-color="'.$text_color.'" data-text-color-hover="'.$text_color_hover.'" data-bg="'.$color.'" data-bg-hover="'.$bg_color_hover.'" href="'.$btn_url.'" class="button-winona '.$btn_size.'" style="background-color:'.$color.';color:'.$text_color.'">'.$btn_text.'</a>';
  $content .= '</div>';
  return $content;
}
add_shortcode('mt-bootstrap-button', 'themeslr_btn_shortcode');




/**

||-> Map Shortcode in Visual Composer with: vc_map();

*/
add_action( 'vc_before_init', 'themeslr_vc_map__bootstrap_button' );
function themeslr_vc_map__bootstrap_button() {
    if (function_exists('vc_map')) {
      vc_map( array(
         "name" => esc_attr__("ThemeSLR - Button", 'themeslr'),
         "base" => "mt-bootstrap-button",
         "category" => esc_attr__('ThemeSLR', 'themeslr'),
         "icon" => "themeslr_shortcode",
         "params" => array(
             array(
                "group" => "Options",
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_attr__( "Button text", 'themeslr' ),
                "param_name" => "btn_text",
                "value" => esc_attr__( "Hello", 'themeslr' ),
                "description" => ""
             ),
             array(
                "group" => "Options",
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => esc_attr__( "Button url", 'themeslr' ),
                "param_name" => "btn_url",
                "value" => "#",
                "description" => ""
             ),
            array(
              "group" => "Options",
              "type" => "dropdown",
              "heading" => esc_attr__("Button size", 'themeslr'),
              "param_name" => "btn_size",
              "value" => array(
                esc_attr__('Small', 'themeslr')   => 'btn btn-sm',
                esc_attr__('Medium', 'themeslr')   => 'btn btn-medium',
                esc_attr__('Large', 'themeslr')   => 'btn btn-lg',
                esc_attr__('Extra-Large', 'themeslr')   => 'extra-large'
              ),
              "std" => 'normal',
              "holder" => "div",
              "class" => "",
              "description" => ""
            ),
            array(
              "group" => "Options",
              "type" => "dropdown",
              "heading" => esc_attr__("Button Style", 'themeslr'),
              "param_name" => "btn_style",
              "value" => array(
                esc_attr__('Square (Default)', 'themeslr')   => 'btn-square',
                esc_attr__('Rounded (5px Radius)', 'themeslr')   => 'btn-rounded',
                esc_attr__('Round (30px Radius)', 'themeslr')   => 'btn-round',
              ),
              "std" => 'normal',
              "holder" => "div",
              "class" => "",
              "description" => ""
            ),
            array(
              "group" => "Options",
              "type" => "dropdown",
              "heading" => esc_attr__("Alignment", 'themeslr'),
              "param_name" => "align",
              "value" => array(
                esc_attr__('Left', 'themeslr')   => 'text-left',
                esc_attr__('Center', 'themeslr')   => 'text-center',
                esc_attr__('Right', 'themeslr')   => 'text-right'
                ),
              "std" => 'normal',
              "holder" => "div",
              "class" => "",
              "description" => ""
            ),
            array(
              "group" => "Options",
              "type" => "checkbox",
              "class" => "",
              "heading" => __( "Open Link in a new tab?", "themeslr" ),
              "param_name" => "target",
              "value" => __( "target", "themeslr" ),
              "description" => __( "If checked, the link will open in a new tab", "themeslr" )
            ),
            array(
                "group" => "Styling",
                "type" => "colorpicker",
                "class" => "",
                "heading" => esc_attr__( "Background color", 'themeslr' ),
                "param_name" => "color",
             ),
            array(
                "group" => "Styling",
                "type" => "colorpicker",
                "class" => "",
                "heading" => esc_attr__( "Background color - hover", 'themeslr' ),
                "param_name" => "bg_color_hover",
                "value" => '', //Default color
             ),
            array(
                "group" => "Styling",
                "type" => "colorpicker",
                "class" => "",
                "heading" => esc_attr__( "Text color", 'themeslr' ),
                "param_name" => "text_color",
                "description" => esc_attr__( "Choose text color", 'themeslr' )
             ),
            array(
                "group" => "Styling",
                "type" => "colorpicker",
                "class" => "",
                "heading" => esc_attr__( "Text color - hover", 'themeslr' ),
                "param_name" => "text_color_hover",
                "description" => esc_attr__( "Choose text color", 'themeslr' )
            ),
            array(
              "group" => "Content",
              "type" => "checkbox",
              "class" => "",
              "heading" => __( "Inline Block", "themeslr" ),
              "param_name" => "display_type",
              "value" => __( "inline-block", "themeslr" ),
              "description" => __( "If checked, the button will allow other content next to it", "themeslr" )
            )
         )
      ));
    }
}