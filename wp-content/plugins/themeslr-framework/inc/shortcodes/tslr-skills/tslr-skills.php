<?php

/**

||-> Shortcode: Skills

*/
function themeslr_skills_shortcode($params, $content) {
    extract( shortcode_atts( 
        array(
            'icon'          => '', 
            'title'         => '',
            'skillvalue'    => '',
            'has_border'    => ''
        ), $params ) );
    $skill = '';
    $skill .= '<div class="stats-block statistics '.esc_attr($has_border).'">';
        $skill .= '<div class="stats-head">';
            $skill .= '<p class="stat-number skill">';
                $skill .= '<i class="'.esc_attr($icon).'"></i>';
            $skill .= '</p>';
        $skill .= '</div>';
        $skill .= '<div class="stats-content percentage" data-perc="'.esc_attr($skillvalue).'">';
            $skill .= '<span class="skill-count">'.esc_attr($skillvalue).'</span>';
            $skill .= '<p>'.esc_attr($title).'</p>';
        $skill .= '</div>';
    $skill .= '</div>';
    return $skill;
}
add_shortcode('mt_skill', 'themeslr_skills_shortcode');

add_action( 'vc_before_init', 'themeslr_vc_map__skill' );
function themeslr_vc_map__skill() {

    if (function_exists('vc_map')) {
        
        $fa_list = themeslr_fa_icons();

        #SHORTCODE: Skill counter shortcode
        vc_map( array(
        "name" => esc_attr__("ThemeSLR - Skill counter", 'themeslr'),
        "base" => "mt_skill",
        "category" => esc_attr__('ThemeSLR', 'themeslr'),
        "icon" => "themeslr_shortcode",
        "params" => array(
        array(
          "group" => "Options",
          "type" => "dropdown",
          "heading" => esc_attr__("Icon class", 'themeslr'),
          "param_name" => "icon",
          "std" => '',
          "holder" => "div",
          "class" => "",
          "description" => "",
          "value" => $fa_list
        ),
        array(
           "group" => "Options",
           "type" => "textfield",
           "holder" => "div",
           "class" => "",
           "heading" => esc_attr__("Title", 'themeslr'),
           "param_name" => "title",
           "value" => "",
           "description" => ""
        ),
        array(
           "group" => "Options",
           "type" => "textfield",
           "holder" => "div",
           "class" => "",
           "heading" => esc_attr__("Skill value", 'themeslr'),
           "param_name" => "skillvalue",
           "value" => "",
           "description" => ""
        ),
        array(
          "group" => "Options",
          "type" => "dropdown",
          "heading" => esc_attr__("Bordered", 'themeslr'),
          "param_name" => "has_border",
          "std" => '',
          "holder" => "div",
          "class" => "",
          "description" => "",
          "value" => array(
              esc_attr__('Bordered', 'themeslr')  => 'bordered',
              esc_attr__('Without border', 'themeslr') => 'unbordered',
              )
        ),
        )
        ));
    }
}