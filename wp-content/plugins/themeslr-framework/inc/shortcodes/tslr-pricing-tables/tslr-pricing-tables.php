<?php


/**

||-> Shortcode: Pricing Tables

*/
function themeslr_pricing_table_shortcode($params, $content) {
    extract( shortcode_atts( 
        array(
            'package_differential_color_style3'           => '',
            'package_background_style3'                   => '',
            'package_background_hover_style3'             => '',
            'package_button_color_style3'                 => '',
            'package_button_hover_color_style3'           => '',
            'package_currency'                            => '',
            'package_price'                               => '',
            'package_name'                                => '',
            'package_recommended'                         => '',
            'package_period'                              => '',
            'package_subtitle'                            => '',
            'package_feature1'                            => '',
            'package_feature2'                            => '',
            'package_feature3'                            => '',
            'package_feature4'                            => '',
            'package_feature5'                            => '',
            'package_image'                                   => '',
            'package_image_style'                                   => '',
            'title_color'                                  => '',
            'border_color'                                  => '',
            'button_url'                                  => '',
            'button_text'                                 => '',
            'price_bubble_bg'                                 => '',
            'btn_style'                                 => '',
            'btn_text_color'                                 => '',
            'btn_text_color_hover'                                 => '',
            'target'      => '',
        ), $params ) );


    $pricing_table = '';

    $title_color_style = '';
    if ($title_color) {
      $title_color_style = 'color:'.$title_color;
    }

    $border_color_style = '';
    if ($border_color) {
      $border_color_style = 'border-color:'.$border_color;
    }

    $btn_text_color_style = '';
    if ($btn_text_color) {
      $btn_text_color_style = 'color:'.$btn_text_color;
    }

    $price_bubble_bg_fill = '#F9DBD9';
    if ($price_bubble_bg) {
      $price_bubble_bg_fill = $price_bubble_bg;
    }

    if ($target) {
      $target = 'target="_blank"';
    }

    $price_bubble_bg_svg = '<svg width="87" height="98" viewBox="0 0 87 98" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M58.4408 -32.4016L96.1635 -25.8422L122.747 1.47392L128.037 39.1131L110.012 72.6984L75.5591 89.4014L37.8364 82.8421L11.2532 55.5259L5.96338 17.8867L23.9874 -15.6986L58.4408 -32.4016Z" fill="'.$price_bubble_bg_fill.'"/></svg>';


      $themeslr_pricing_id = 'themeslr_pricing--'.uniqid();
      $pricing_table .= '<div class="themeslr-pricing-table" id="'.$themeslr_pricing_id.'">';
          
          $pricing_table .= '<div class="pricing pricing--pema">';
            $pricing_table .= '<div class="pricing__item '.esc_attr($package_recommended).'" style="'.$border_color_style.'">';
              $pricing_table .= '<div class="pricing__title"><span style="'.$title_color_style.'">'.esc_html($package_name).'</span></div>';
                $pricing_table .= '<div class="pricing__period">
                                    '.$price_bubble_bg_svg.'
                                    <div class="pricing__bubble_content">
                                      <strong>'.esc_attr($package_currency.$package_price).'</strong>
                                      <br />'.esc_html($package_period).'
                                    </div>
                                  </div>';
                $pricing_table .= '<p class="pricing__sentence">'.esc_html($package_subtitle).'</p>';

                if ($package_image) {
                  $pricing_table .= '<div class="themeslr-pricing-image--holder text-center">';
                    $pricing_table .= '<img class="'.$package_image_style.'" src="'.esc_url(wp_get_attachment_url( $package_image )).'" alt="'.esc_attr($package_name).'" />';
                  $pricing_table .= '</div>';
                }

              $pricing_table .= '<ul class="pricing__feature-list">';
                    if (!empty($package_feature1)){
                      $pricing_table .= '<li class="pricing__feature">'.esc_attr($package_feature1).'</li>';
                    }
                    if (!empty($package_feature2)){
                      $pricing_table .= '<li class="pricing__feature">'.esc_attr($package_feature2).'</li>';
                    }
                    if (!empty($package_feature3)){
                      $pricing_table .= '<li class="pricing__feature">'.esc_attr($package_feature3).'</li>';
                    }
                    if (!empty($package_feature4)){
                      $pricing_table .= '<li class="pricing__feature">'.esc_attr($package_feature4).'</li>';
                    }
                    if (!empty($package_feature5)){
                      $pricing_table .= '<li class="pricing__feature">'.esc_attr($package_feature5).'</li>';
                    }
                  
              $pricing_table .= '</ul>';
              $pricing_table .= '<div class="themeslr-pricing-button--holder themeslr_button_shortcode text-center '.$btn_style.'">';
                $pricing_table .= '<a '.$target.' data-text-color="'.$btn_text_color.'" data-text-color-hover="'.$btn_text_color_hover.'" style="'.$btn_text_color_style.'" class="button" href="'.esc_url($button_url).'">'.esc_html($button_text).'</a>';
              $pricing_table .= '</div>';
            $pricing_table .= '</div>';
          $pricing_table .= '</div>';
      $pricing_table .= '</div>
    
    <style type="text/css" media="screen">
          #'.$themeslr_pricing_id.' .pricing--pema .pricing__sentence {
              color: '.esc_attr($package_differential_color_style3).';
          }
          #'.$themeslr_pricing_id.' .pricing--pema .pricing__price {
              color: '.esc_attr($package_differential_color_style3).';
          }
          #'.$themeslr_pricing_id.' .pricing--pema .button {
              background-color: '.esc_attr($package_button_color_style3).';
          }
          #'.$themeslr_pricing_id.' .pricing--pema .button:hover,
          #'.$themeslr_pricing_id.' .pricing--pema .button:focus {
              background-color: '.esc_attr($package_button_hover_color_style3).';
          }
          #'.$themeslr_pricing_id.' .pricing--pema .pricing__item {
              background: '.esc_attr($package_background_style3).' none repeat scroll 0 0;
          }
          #'.$themeslr_pricing_id.' .pricing--pema .pricing__item:hover {
              background: '.esc_attr($package_background_hover_style3).' none repeat scroll 0 0;
          }
      </style>';
    return $pricing_table;
}
add_shortcode('pricing-table', 'themeslr_pricing_table_shortcode');








/**

||-> Map Shortcode in Visual Composer with: vc_map();

*/
add_action( 'vc_before_init', 'themeslr_vc_map__pricing' );
function themeslr_vc_map__pricing() {
  if (function_exists('vc_map')) {
      
    vc_map( array(
       "name" => esc_attr__("ThemeSLR - Pricing table", 'themeslr'),
       "base" => "pricing-table",
       "category" => esc_attr__('ThemeSLR', 'themeslr'),
       "icon" => "themeslr_shortcode",
       "params" => array(
          array(
             "group" => "General",
             "type" => "dropdown",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package Recommended"),
             "param_name" => "package_recommended",
             "std" => '',
             "description" => esc_attr__(""),
             "value" => array(
              'Basic'           => 'pricing__item--nofeatured',
              'Recommended'     => 'pricing__item--featured'
             )
          ),
          array(
             "group" => "General",
             "type" => "textfield",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package name", 'themeslr'),
             "param_name" => "package_name",
             "value" => esc_attr__("", 'themeslr'),
             "description" => ""
          ),
          array(
             "group" => "General",
             "type" => "textfield",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package subtitle", 'themeslr'),
             "param_name" => "package_subtitle",
             "value" => esc_attr__("", 'themeslr'),
             "description" => ""
          ),

          array(
             "group" => "General",
             "type" => "attach_image",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package Image/Icon", 'themeslr'),
             "param_name" => "package_image",
             "value" => "",
             "description" => ""
          ),
          array(
            "group" => "General",
            "type" => "dropdown",
            "heading" => esc_attr__("Image Radius (Optional)", 'themeslr'),
            "param_name" => "package_image_style",
            "value" => array(
              esc_attr__('Square (Default)', 'themeslr')   => '',
              esc_attr__('Rounded', 'themeslr')   => 'img-rounded',
              esc_attr__('Circle', 'themeslr')   => 'img-circle',
            ),
            "std" => 'normal',
            "holder" => "div",
            "class" => "",
            "description" => ""
          ),
          array(
             "group" => "Features",
             "type" => "textfield",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package's 1st feature", 'themeslr'),
             "param_name" => "package_feature1",
             "value" => esc_attr__("", 'themeslr'),
             "description" => ""
          ),
          array(
             "group" => "Features",
             "type" => "textfield",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package's 2nd feature", 'themeslr'),
             "param_name" => "package_feature2",
             "value" => esc_attr__("", 'themeslr'),
             "description" => ""
          ),
          array(
             "group" => "Features",
             "type" => "textfield",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package's 3rd feature", 'themeslr'),
             "param_name" => "package_feature3",
             "value" => esc_attr__("", 'themeslr'),
             "description" => ""
          ),
          array(
             "group" => "Features",
             "type" => "textfield",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package's 4th feature", 'themeslr'),
             "param_name" => "package_feature4",
             "value" => esc_attr__("", 'themeslr'),
             "description" => ""
          ),
          array(
             "group" => "Features",
             "type" => "textfield",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package's 5th feature", 'themeslr'),
             "param_name" => "package_feature5",
             "value" => esc_attr__("", 'themeslr'),
             "description" => ""
          ),
          array(
             "group" => "Button",
             "type" => "textfield",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package button url", 'themeslr'),
             "param_name" => "button_url",
             "value" => "",
             "description" => ""
          ),
          array(
             "group" => "Button",
             "type" => "textfield",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package button text", 'themeslr'),
             "param_name" => "button_text",
             "value" => esc_attr__("", 'themeslr'),
             "description" => ""
          ),
          array(
            "group" => "General",
            "type" => "colorpicker",
            "class" => "",
            "heading" => esc_attr__( "Title color", 'themeslr' ),
            "param_name" => "title_color",
            "value" => "", //Default color
          ),
          array(
            "group" => "General",
            "type" => "colorpicker",
            "class" => "",
            "heading" => esc_attr__( "Border color", 'themeslr' ),
            "param_name" => "border_color",
            "value" => "", //Default color
          ),
          array(
             "group" => "Price",
             "type" => "textfield",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package currency", 'themeslr'),
             "param_name" => "package_currency",
             "value" => "",
             "description" => ""
          ),
          array(
             "group" => "Price",
             "type" => "textfield",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package price", 'themeslr'),
             "param_name" => "package_price",
             "value" => "",
             "description" => ""
          ),
          array(
             "group" => "Price",
             "type" => "textfield",
             "holder" => "div",
             "class" => "",
             "heading" => esc_attr__("Package period", 'themeslr'),
             "param_name" => "package_period",
             "value" => esc_attr__("", 'themeslr'),
             "description" => ""
          ),
          array(
            "group" => "Price",
            "type" => "colorpicker",
            "class" => "",
            "heading" => esc_attr__( "Price Value Color", 'themeslr' ),
            "param_name" => "package_differential_color_style3",
            "value" => "", //Default color
            "description" => esc_attr__( "Choose the price color", 'themeslr' )
          ),
          array(
            "group" => "Price",
            "type" => "colorpicker",
            "class" => "",
            "heading" => esc_attr__( "Price Bubble Background", 'themeslr' ),
            "param_name" => "price_bubble_bg",
            "value" => "", //Default color
            "description" => esc_attr__( "Override the default rose background", 'themeslr' )
          ),
          array(
            "group" => "General",
            "type" => "colorpicker",
            "class" => "",
            "heading" => esc_attr__( "Package background color", 'themeslr' ),
            "param_name" => "package_background_style3",
            "value" => "", //Default color
            "description" => esc_attr__( "Choose package background color", 'themeslr' )
          ),
          array(
            "group" => "General",
            "type" => "colorpicker",
            "class" => "",
            "heading" => esc_attr__( "Package hover background color", 'themeslr' ),
            "param_name" => "package_background_hover_style3",
            "value" => "", //Default color
            "description" => esc_attr__( "Choose package hover background color", 'themeslr' )
          ),
          array(
            "group" => "Button",
            "type" => "colorpicker",
            "class" => "",
            "heading" => esc_attr__( "Button Background Color", 'themeslr' ),
            "param_name" => "package_button_color_style3",
            "value" => "", //Default color
          ),
          array(
            "group" => "Button",
            "type" => "colorpicker",
            "class" => "",
            "heading" => esc_attr__( "Button Background Color - Hover", 'themeslr' ),
            "param_name" => "package_button_hover_color_style3",
            "value" => "", //Default color
          ),
          array(
            "group" => "Button",
            "type" => "colorpicker",
            "class" => "",
            "heading" => esc_attr__( "Button Text Color", 'themeslr' ),
            "param_name" => "btn_text_color",
            "value" => "", //Default color
          ),
          array(
            "group" => "Button",
            "type" => "colorpicker",
            "class" => "",
            "heading" => esc_attr__( "Button Text Color - Hover", 'themeslr' ),
            "param_name" => "btn_text_color_hover",
            "value" => "", //Default color
          ),
          array(
            "group" => "Button",
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
            "group" => "Button",
            "type" => "checkbox",
            "class" => "",
            "heading" => __( "Open Link in a new tab?", "themeslr" ),
            "param_name" => "target",
            "value" => __( "target", "themeslr" ),
            "description" => __( "If checked, the link will open in a new tab", "themeslr" )
          ),
       )
    ));
  }
}