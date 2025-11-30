<?php
/**
||-> Shortcode: Title and Subtitle
*/

function themeslr_heading_title_subtitle_shortcode($params, $content) {
    extract( shortcode_atts( 
        array(
            'title_light'               => '',
            'line_break'               => '',
            'title_bold'               => '',
            'title_subtitle_alignment'            => '',
            'subtitle'            => '',
            'title_color'         => '',
            'title_font_size'      => '',
            'subtitle_color'      => '',
            'subtitle_font_size'      => '',
            'subtitle_fullwidth'      => '',
            'space_top' => '',
            'space_bottom' => '',
        ), $params ) ); 

    $content = '';

    $style_padding_top = '';
    if (!empty($space_top) && $space_top != 'No Space') {
        $style_padding_top = 'padding-top:'.$space_top.'px;';
    }

    $style_padding_bottom = '';
    if (!empty($space_bottom) && $space_bottom != 'No Space') {
        $style_padding_bottom = 'padding-bottom:'.$space_bottom.'px;';
    }

    $style_font_size = '';
    if (isset($title_font_size) && $title_font_size != '') {
        $style_font_size = 'font-size:'.$title_font_size.'px;';
    }

    $style_subtitle_font_size = '';
    if (isset($subtitle_font_size) && $subtitle_font_size != '') {
        $style_subtitle_font_size = 'font-size:'.$subtitle_font_size.'px;';
    }

    if ($line_break) {
        $line_break = '<br />';
    }else{
        $line_break = ' ';
    }

    if ($subtitle_fullwidth) {
        $subtitle_fullwidth_style = 'width:100%;';
    }else{
        $subtitle_fullwidth_style = ' ';
    }

    $content .= '<div class="title-subtile-holder" style="'.$style_padding_top.' '.$style_padding_bottom.'">';
        $content .= '<h2 style="'.$style_font_size.'" class="section-title '.$title_color.' '.$title_subtitle_alignment.'">'.$title_light.$line_break.'<strong>'.$title_bold.'</strong></h2>';
        $content .= '<div style="'.$style_subtitle_font_size.' '.$subtitle_fullwidth_style.'" class="section-subtitle '.$subtitle_color.' '.$title_subtitle_alignment.'">'.$subtitle.'</div>';
    $content .= '</div>';

    return $content;
}
add_shortcode('heading_title_subtitle', 'themeslr_heading_title_subtitle_shortcode');


add_action( 'vc_before_init', 'themeslr_vc_map__heading_title_subtitle' );
function themeslr_vc_map__heading_title_subtitle() {
    if (function_exists('vc_map')) {
        vc_map(
            array(
                "name" => esc_attr__("ThemeSLR - Heading with Title and Subtitle", 'themeslr'),
                "base" => "heading_title_subtitle",
                "category" => esc_attr__('ThemeSLR', 'themeslr'),
                "icon" => "themeslr_shortcode",
                "params" => array(
                    array(
                        "group" => "Options",
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_attr__( "Section title (Light font-weight)", 'themeslr' ),
                        "param_name" => "title_light",
                        "value" => "",
                        "description" => ""
                    ),
                    array(
                        "group" => "Options",
                        "type" => "checkbox",
                        "class" => "",
                        "heading" => __( "Line Break?", "themeslr" ),
                        "param_name" => "line_break",
                        "description" => __( "If checked, a line break will be added between the light and the bold texts.", "themeslr" )
                    ),
                    array(
                        "group" => "Options",
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_attr__( "Section title (Bold font-weight)", 'themeslr' ),
                        "param_name" => "title_bold",
                        "value" => "",
                        "description" => ""
                    ),
                    array(
                        "group" => "Options",
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_attr__( "Title Font Size", 'themeslr' ),
                        "param_name" => "title_font_size",
                        "value" => "",
                        "description" => esc_attr__( "(Example: 50) For the default size, leave this empty", 'themeslr' )
                    ),
                    array(
                        "group" => "Options",
                        "type" => "textarea",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_attr__( "Section subtitle", 'themeslr'),
                        "param_name" => "subtitle",
                        "value" => "",
                        "description" => ""
                    ),
                    array(
                        "group" => "Options",
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_attr__( "Subitle Font Size", 'themeslr' ),
                        "param_name" => "subtitle_font_size",
                        "value" => "",
                        "description" => esc_attr__( "(Example: 18) For the default size, leave this empty", 'themeslr' )
                    ),
                    array(
                        "group" => "Options",
                        "type" => "checkbox",
                        "class" => "",
                        "heading" => __( "Subtitle Full-width?", "themeslr" ),
                        "param_name" => "subtitle_fullwidth",
                        "description" => __( "If checked, the subtitle will override the default option (70% width) and will become full-width.", "themeslr" )
                    ),
                    array(
                        "group" => "Styling",
                        "type" => "dropdown",
                        "holder" => "div",
                        "std" => '',
                        "class" => "",
                        "heading" => esc_attr__("Title / Subtitle Alignment", 'themeslr'),
                        "param_name" => "title_subtitle_alignment",
                        "description" => "",
                        "value" => array(
                            esc_attr__('Left', 'themeslr')     => 'text-left',
                            esc_attr__('Center', 'themeslr')     => 'text-center',
                            esc_attr__('Right', 'themeslr')     => 'text-right'
                        )
                    ),
                    array(
                        "group" => "Styling",
                        "type" => "dropdown",
                        "holder" => "div",
                        "std" => '',
                        "class" => "",
                        "heading" => esc_attr__("Title Color", 'themeslr'),
                        "param_name" => "title_color",
                        "description" => "",
                        "value" => array(
                            esc_attr__('Light color title for dark section', 'themeslr')     => 'light_title',
                            esc_attr__('Dark color title for light section', 'themeslr')     => 'dark_title'
                        )
                    ),
                    array(
                        "group" => "Styling",
                        "type" => "dropdown",
                        "holder" => "div",
                        "std" => '',
                        "class" => "",
                        "heading" => esc_attr__("Subtitle Color", 'themeslr'),
                        "param_name" => "subtitle_color",
                        "description" => "",
                        "value" => array(
                            esc_attr__('Light color subtitle for dark section', 'themeslr')     => 'light_subtitle',
                            esc_attr__('Dark color subtitle for light section', 'themeslr')     => 'dark_subtitle'
                        )
                    ),
                    array(
                        'type' => 'dropdown',
                        'heading' => __( 'Space Top', 'themeslr' ),
                        'param_name' => 'space_top',
                        "description" => __( 'Set a top Spacing (px).', 'themeslr' ),
                        "group" => "Styling",
                        "value" => array('No Space', '5', '10', '15', '20', '25', '30', '40', '50', '60', '70', '80', '90', '100', '110', '120', '150')
                    ),
                    array(
                        'type' => 'dropdown',
                        'heading' => __( 'Space Bottom', 'themeslr' ),
                        'param_name' => 'space_bottom',
                        "description" => __( 'Set a bottom Spacing (px).', 'themeslr' ),
                        "group" => "Styling",
                        "value" => array('No Space', '5', '10', '15', '20', '25', '30', '40', '50', '60', '70', '80', '90', '100', '110', '120', '150')
                    )
                    
                )
            )
        );
    }
}