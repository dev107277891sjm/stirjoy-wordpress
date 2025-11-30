<?php

function themeslr_isolated_element_shortcode( $params, $content ) {
    extract( shortcode_atts( 
        array(
            'elements'                       => '',
        ), $params ) );
    
    $shortcode_content = '';
    if (function_exists('vc_param_group_parse_atts')) {
        $elements = vc_param_group_parse_atts($params['elements']);

        if ($elements) {
            foreach($elements as $element){
                $image_attributes = wp_get_attachment_image_src( $element['image'], 'full' );
                if ($image_attributes) {
                    $shortcode_content .= '<img class="absolute" width="'.$element['width'].'" height="auto" style="left: '.$element['left_percent'].'%; top: '.$element['top_percent'].'%;" src="'.$image_attributes[0].'" alt="absolute element" />';
                }
            }
        }
    }
    

    return $shortcode_content;
}
add_shortcode('tslr-isolated-element', 'themeslr_isolated_element_shortcode');

