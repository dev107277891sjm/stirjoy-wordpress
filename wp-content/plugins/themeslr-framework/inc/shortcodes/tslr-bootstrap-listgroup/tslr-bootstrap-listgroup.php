<?php
/**

||-> Shortcode: Bootstrap List Group

*/
function themeslr_list_group_shortcode($params, $content) {
    extract( shortcode_atts( 
        array(
            'heading'       => '',
            'description'   => '',
            'active'        => '',
        ), $params ) ); 
    $content = '';
    $content .= '<a href="#" class="list-group-item '.$active.'">';
        $content .= '<h4 class="list-group-item-heading">'.$heading.'</h4>';
        $content .= '<p class="list-group-item-text">'.$description.'</p>';
    $content .= '</a>';
    return $content;
}
add_shortcode('list_group', 'themeslr_list_group_shortcode');

