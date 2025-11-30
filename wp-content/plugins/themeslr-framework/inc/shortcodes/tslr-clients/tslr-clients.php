<?php
if ( ! defined( 'ABSPATH' ) ) {
  die( '-1' );
}
function themeslr_shortcode_clients01($params, $content) {
    extract( shortcode_atts( 
        array(
            'visible_items_clients'   =>'',
            'number'                  =>'',
            'background_color_overlay' =>''
        ), $params ) );
    $html = '';
    
    $args_clients = array(
        'posts_per_page'   => $number,
        'orderby'          => 'post_date',
        'order'            => 'DESC',
        'post_type'        => 'clients',
        'post_status'      => 'publish' 
    );
      
	    $html .= '<div class="row">';
		    $html .= '<div class="themeslr_clients_slider col-md-12 clients_container_shortcode-'.$visible_items_clients.' owl-carousel owl-theme">';
			    $clients = get_posts($args_clients);
			        foreach ($clients as $client) {
		            $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $client->ID ),'full' );
		            $html .= '<div class="clients_image_holder post">';
  		            $html .= '<div class="item col-md-12">';
                    $html .= '<div class="clients_image_holder_inside post" style="background-color:'.$background_color_overlay.';">';
    	                if($thumbnail_src) { 
                        $html .= '<img class="client_image" src="'. $thumbnail_src[0] . '" alt="'. $client->post_title .'" />';
    	                }else{ 
                        $html .= '<img src="http://placehold.it/160x100" alt="'. $client->post_title .'" />'; 
                      }
                    $html .= '</div>';
  		            $html .= '</div>';
					    $html .= '</div>';
			        }
		    $html .= '</div>';
	    $html .= '</div>';
	    
    return $html;
}
add_shortcode('clients01', 'themeslr_shortcode_clients01');

