<?php

/**

||-> Shortcode: Icon List Item

*/
function themeslr_icon_list_item($params, $content) {
  extract( shortcode_atts( 
      array(
          'icon_fontawesome'      => '',
          'list_icon_size'      => '',
          'list_icon_margin'      => '',
          'list_icon_margin_left'      => '',
          'list_icon_color'    => '',
          'list_icon_title'     => '',
          'list_icon_url'     => '',
          'list_icon_title_size'     => '',
          'list_icon_title_color'     => '',
          'alignment'     => '',
          'target'     => '',
      ), $params ) );


  $margin_right = '0';
  $margin_left = '0';

  // margin right
  if ($list_icon_margin) {
    $margin_right = $list_icon_margin;
  }
  // margin left
  if ($list_icon_margin_left) {
    $margin_left = $list_icon_margin_left;
  }
  if ($target) {
    $target = 'target="_blank"';
  }

  $html = '';
  $html .= '<div class="tslr-icon-list-item '.$alignment.'">';

              if (!empty($list_icon_url)) {
                $html .= '<a '.$target.' href="'.$list_icon_url.'">';
              }

      $html .= '<div class="tslr-icon-list-icon-holder">
                  <div class="tslr-icon-list-icon-holder-inner clearfix"><i style="margin-left:'.esc_attr($margin_left).'px; margin-right:'.esc_attr($margin_right).'px; color:'.esc_attr($list_icon_color).';font-size:'.esc_attr($list_icon_size).'px" class="'.esc_attr($icon_fontawesome).'"></i></div>
                </div>
                <p class="tslr-icon-list-text" style="font-size: '.esc_attr($list_icon_title_size).'px; color: '.esc_attr($list_icon_title_color).'">'.esc_attr($list_icon_title).'</p>';
              
              if (!empty($list_icon_url)) {
                $html .= '</a>';
              }

            $html .= '</div>';

  return $html;
}
add_shortcode('mt_icon_list_item', 'themeslr_icon_list_item');

add_action( 'vc_before_init', 'themeslr_vc_map__icon_list_item' );
function themeslr_vc_map__icon_list_item() {
  if (function_exists('vc_map')) {
      
    vc_map( array(
       "name" => esc_attr__("ThemeSLR - Icon List Item", 'themeslr'),
       "base" => "mt_icon_list_item",
       "category" => esc_attr__('ThemeSLR', 'themeslr'),
      'show_settings_on_create' => true,
       "icon" => "themeslr_shortcode",
       "params" => array(
        array(
            "group" => "Icon Setup",
          'type' => 'iconpicker',
          'heading' => esc_html__( 'Icon', 'js_composer' ),
          'param_name' => 'icon_fontawesome',
          'value' => 'fas fa-adjust',
          // default value to backend editor admin_label
          'settings' => array(
            'emptyIcon' => false,
            // default true, display an "EMPTY" icon?
            'iconsPerPage' => 500,
            // default 100, how many icons per/page to display, we use (big number) to display all icons in single page
          ),
          'description' => esc_html__( 'Select icon from library.', 'js_composer' ),
        ),
       
          array(
            "group" => "Options",
            "type" => "dropdown",
            "heading" => esc_attr__("Alignment", 'themeslr'),
            "param_name" => "alignment",
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
            "group" => "Icon Setup",
            "type" => "textfield",
            "holder" => "div",
            "class" => "",
            "heading" => esc_attr__("Icon Size (px)", 'themeslr'),
            "param_name" => "list_icon_size",
            "value" => "",
            "description" => "Default: 18(px)"
          ),
          array(
            "group" => "Icon Setup",
            "type" => "textfield",
            "holder" => "div",
            "class" => "",
            "heading" => esc_attr__("Icon Margin Right (px)", 'themeslr'),
            "param_name" => "list_icon_margin",
            "value" => "",
            "description" => ""
          ),
          array(
            "group" => "Icon Setup",
            "type" => "textfield",
            "holder" => "div",
            "class" => "",
            "heading" => esc_attr__("Icon Margin Left (px)", 'themeslr'),
            "param_name" => "list_icon_margin_left",
            "value" => "",
          ),
          array(
            "group" => "Icon Setup",
            "type" => "colorpicker",
            "holder" => "div",
            "class" => "",
            "heading" => esc_attr__("Icon Color", 'themeslr'),
            "param_name" => "list_icon_color",
            "value" => "",
          ),
          array(
            "group" => "Label Setup",
            "type" => "textfield",
            "heading" => esc_attr__("Label/Title", 'themeslr'),
            "param_name" => "list_icon_title",
            "std" => '',
            "holder" => "div",
            "class" => "",
            "description" => "Eg: This is a label"
          ),
          array(
            "group" => "Label Setup",
            "type" => "textfield",
            "heading" => esc_attr__("Label/Icon URL", 'themeslr'),
            "param_name" => "list_icon_url",
            "std" => '',
            "holder" => "div",
            "class" => "",
            "description" => "Eg: http://themeslr.com"
          ),
          array(
            "group" => "Label Setup",
            "type" => "checkbox",
            "class" => "",
            "heading" => __( "Open Link in a new tab?", "themeslr" ),
            "param_name" => "target",
            "value" => __( "target", "themeslr" ),
            "description" => __( "If checked, the link will open in a new tab", "themeslr" )
          ),
          array(
            "group" => "Label Setup",
            "type" => "textfield",
            "heading" => esc_attr__("Title Font Size", 'themeslr'),
            "param_name" => "list_icon_title_size",
            "std" => '',
            "holder" => "div",
            "class" => "",
            "description" => "Default: 18(px)"
          ),
          array(
            "group" => "Label Setup",
            "type" => "colorpicker",
            "heading" => esc_attr__("Title Color", 'themeslr'),
            "param_name" => "list_icon_title_color",
            "std" => '',
            "holder" => "div",
            "class" => "",
            "description" => ""
          ),
       )
    ));
  }
}