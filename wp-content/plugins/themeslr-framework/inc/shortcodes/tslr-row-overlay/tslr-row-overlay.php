<?php

function themeslr_row_overlay( $params, $content ) {
    extract( shortcode_atts( 
        array(
            'background'                       => '',
            'inner_column'                       => '',
        ), $params ) );
    
    if ($inner_column) {
        $inner_column = 'yes';
    }else{
        $inner_column = ' ';
    }

    $shortcode_content = '';
    $shortcode_content .= '<div data-inner-column="'.esc_attr($inner_column).'" style="background: '.esc_html($background).'" class="themeslr-row-overlay"></div>';

    return $shortcode_content;
}
add_shortcode('tslr-row-overlay', 'themeslr_row_overlay');

