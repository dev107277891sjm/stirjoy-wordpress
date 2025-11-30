<?php 
// SHORTCODES
include_once( 'tslr-testimonials/tslr-testimonials01.php' ); # Testimonials 01
include_once( 'tslr-testimonials/tslr-testimonials01-simple.php' ); # Testimonials 01
include_once( 'tslr-clients/tslr-clients.php' ); # Clients
include_once( 'tslr-skills/tslr-skills.php' ); # Skills
include_once( 'tslr-title-subtitle/tslr-title-subtitle.php' ); # Title Subtitle
include_once( 'tslr-pricing-tables/tslr-pricing-tables.php' ); # Pricing Tables
include_once( 'tslr-blog-posts/tslr-blogpost01.php' ); # blogpost01
include_once( 'tslr-icon-list-item/tslr-icon-list-item.php' ); # Mailchimp Subscribe Form
include_once( 'tslr-bootstrap-button/tslr-bootstrap-button.php' ); # Bootstrap Buttons
include_once( 'tslr-bootstrap-listgroup/tslr-bootstrap-listgroup.php' ); # Bootstrap List Group
include_once( 'tslr-tweets/tslr-tweets.php' ); # Bootstrap Buttons
include_once( 'tslr-smooth-gallery/tslr-smooth-gallery.php' ); # Bootstrap Buttons
include_once( 'tslr-carousel-everything/tslr-carousel-everything.php' ); # Bootstrap Buttons
include_once( 'tslr-isolated-element/tslr-isolated-element.php' ); # Bootstrap Buttons
include_once( 'tslr-row-overlay/tslr-row-overlay.php' ); # Bootstrap Buttons
include_once( 'tslr-hero-slider/tslr-hero-slider.php' ); # Bootstrap Buttons


function themeslr_video_popup_shortcode($params, $content) {

    extract( shortcode_atts( 
        array(
            'source_vimeo'              => '',
            'source_youtube'            => '',
            'video_source'              => '',
            'video_label_next_to_icon'              => '',
            'icon_fontawesome'              => '',
            'vimeo_link_id'             => '',
            'youtube_link_id'           => '',
            'button_image_alignment'           => '',
            'button_image'              => '',
            'custom_image_checkbox'              => ''
        ), $params ) );

    $thumb      = wp_get_attachment_image_src($button_image, "full");
	$video_image_class = '';
	if ($thumb && $custom_image_checkbox) {
		$video_image_class = $button_image_alignment;
	}

    $html = '';
	$html .= '<div class="themeslr-video-popup-shortcode '.$video_image_class.'">';
		if ($video_source == 'source_vimeo') {
			$href = "https://vimeo.com/".$vimeo_link_id;
		} elseif ($video_source == 'source_youtube') {
			$href = "https://www.youtube.com/watch?v=".$youtube_link_id;
		}

		$html .= '<a class="themeslr-vimeo-popup" href="'.$href.'">';
			if ($thumb) {
				$html .= '<img class="buton_image_class" src="'.esc_attr($thumb[0]).'" data-src="'.esc_attr($thumb[0]).'" alt="'.esc_attr__('Video Popup', 'themeslr').'">';
			}else{
				$html .= '<i class="'.esc_attr($icon_fontawesome).'"></i> '.esc_html($video_label_next_to_icon);
			}
		$html .= '</a>';
	$html .= '</div>';

    return $html;
}

add_shortcode('themeslr-video-popup-shortcode', 'themeslr_video_popup_shortcode');


add_action( 'vc_before_init', 'themeslr_vc_map__shortcodes' );
function themeslr_vc_map__shortcodes() {


	if (function_exists('vc_map')) {





	   vc_map( array(
	     "name" => esc_attr__("ThemeSLR - Testimonials Slider (Simple&Full)", 'themeslr'),
	     "base" => "testimonials01-simple",
	     "category" => esc_attr__('ThemeSLR', 'themeslr'),
	     "icon" => "themeslr_shortcode",
	     "params" => array(
	        array(
	          "group" => "Options",
	          "type" => "textfield",
	          "holder" => "div",
	          "class" => "",
	          "heading" => esc_attr__( "Number of testimonials", 'themeslr' ),
	          "param_name" => "number",
	          "value" => "",
	          "description" => esc_attr__( "Enter number of testimonials to show.", 'themeslr' )
	        ),
	        array(
	          "group" => "Options",
	          "type" => "dropdown",
	          "heading" => esc_attr__("Visible Testimonials per slide", 'themeslr'),
	          "param_name" => "visible_items",
	          "std" => '',
	          "holder" => "div",
	          "class" => "",
	          "description" => "",
	          "value" => array(
	            '1'   => '1',
	            '2'   => '2',
	            '3'   => '3'
	            )
	        ),
	        array(
	          "group" => "Styling",
	          "type" => "colorpicker",
	          "class" => "",
	          "heading" => esc_attr__( "Testimonial Content - Text Color", 'themeslr' ),
	          "param_name" => "testimonial01_color",
	          "value" => "", //Default color
	          "description" => esc_attr__( "Choose testimonial color", 'themeslr' )
	        ),
	        array(
	          "group" => "Styling",
	          "type" => "colorpicker",
	          "class" => "",
	          "heading" => esc_attr__( "Testimonial Content - Background Color", 'themeslr' ),
	          "param_name" => "testimonial01_bg",
	          "value" => "", //Default color
	          "description" => esc_attr__( "Choose testimonial color", 'themeslr' )
	        ),
	        array(
	          "group" => "Styling",
	          "type" => "attach_image",
	          "class" => "",
	          "heading" => esc_attr__( "Testimonial Content - Background Image", 'themeslr' ),
	          "param_name" => "testimonial01_bg_image",
	          "value" => "", //Default color
	          "description" => esc_attr__( "Default: Semitransparent logo", 'themeslr' )
	        ),
	      )
	  ));

	    vc_map( 
	        array(
	            "name" => esc_attr__("ThemeSLR - Row Overlay", 'themeslr'),
	            "base" => "tslr-row-overlay",
	            "icon" => "themeslr_shortcode",
	            "category" => esc_attr__('ThemeSLR', 'themeslr'),
	            "params" => array(
	                array(
	                    "type" => "colorpicker",
	                    "holder" => "div",
	                    "class" => "",
	                    "heading" => esc_attr__("Background Color", 'themeslr'),
	                    "param_name" => "background",
	                ),
	                array(
	                    "type" => "checkbox",
	                    "class" => "",
	                    "heading" => __( "Keep in Column?", "themeslr" ),
	                    "param_name" => "inner_column",
	                    "description" => __( "If checked, the overlay will be only applied in a column. By default, it will be applied on row.", "themeslr" )
	                ),
	           )
	        )
	    );  

	    vc_map( 
	      array(
	       "name" => esc_attr__("ThemeSLR - Isolated Element", 'themeslr'),
	       "base" => "tslr-isolated-element",
	       "icon" => "themeslr_shortcode",
	       "category" => esc_attr__('ThemeSLR', 'themeslr'),
	       "params" => array(
	            array(
	                "group" => esc_attr__( "Settings", 'themeslr' ),
	                'type' => 'param_group',
	                'value' => '',
	                'param_name' => 'elements',
	                // Note params is mapped inside param-group:
	                'params' => array(
	                    array(
	                        "type" => "attach_image",
	                        "holder" => "div",
	                        "class" => "",
	                        "heading" => esc_attr__("Element Image", 'themeslr'),
	                        "param_name" => "image",
	                    ),
	                    array(
	                        "type" => "textfield",
	                        "holder" => "div",
	                        "class" => "",
	                        "heading" => esc_attr__("Left (%) - Do not write the '%'", 'themeslr'),
	                        "param_name" => "left_percent",
	                    ),
	                    array(
	                        "type" => "textfield",
	                        "holder" => "div",
	                        "class" => "",
	                        "heading" => esc_attr__("Top (%) - Do not write the '%'", 'themeslr'),
	                        "param_name" => "top_percent",
	                    ),
	                    array(
	                        "type" => "textfield",
	                        "holder" => "div",
	                        "class" => "",
	                        "heading" => esc_attr__("Width (px) - Do not write the 'px'", 'themeslr'),
	                        "param_name" => "width",
	                    ),
	                )
	            ),
	       )
	    ));  

		vc_map( array(
		 "name" => esc_attr__("ThemeSLR - Clients (Slider)", 'themeslr'),
		 "base" => "clients01",
		 "category" => esc_attr__('ThemeSLR', 'themeslr'),
		 "icon" => "themeslr_shortcode",
		 "params" => array(
		     array(
		        "group" => "Options",
		        "type" => "textfield",
		        "holder" => "div",
		        "class" => "",
		        "heading" => esc_attr__( "Number of clients", 'themeslr' ),
		        "param_name" => "number",
		        "value" => "",
		        "description" => esc_attr__( "Enter number of clients to show.", 'themeslr' )
		     ),
		     array(
		      "group" => "Options",
		      "type" => "dropdown",
		      "heading" => esc_attr__("Visible Clients per slide", 'themeslr'),
		      "param_name" => "visible_items_clients",
		      "std" => '',
		      "holder" => "div",
		      "class" => "",
		      "description" => "",
		      "value" => array(
		        '1'   => '1',
		        '2'   => '2',
		        '3'   => '3',
		        '4'   => '4',
		        '5'   => '5',
		        '6'   => '6',
		        )
		    ),
		    array(
		      "group" => "Options",
		      "type" => "colorpicker",
		      "class" => "",
		      "heading" => esc_attr__( "Logo Background Overlay", 'themeslr' ),
		      "param_name" => "background_color_overlay",
		      "value" => "", //Default color
		      "description" => esc_attr__( "Client Logo Background Overlay", 'themeslr' )
		    ),
		 )
		));

	    vc_map( array(
	        "name" => esc_attr__("ThemeSLR - Carousel Everything", 'themeslr'),
	        "base" => "themeslr_carousel_everything_holder_shortcode",
	        "as_parent" => array('only' => 'themeslr_carousel_everything_inner_content_shortcode,vc_column_text'), 
	        "content_element" => true,
	        "show_settings_on_create" => true,
	        "category" => esc_attr__('ThemeSLR', 'themeslr'),
	        "is_container" => true,
	        "params" => array(
	            // add params same as with any other content element 
	           array(
	              "group" => "Options",
	              "type" => "textfield",
	              "holder" => "div",
	              "class" => "",
	              "heading" => esc_attr__( "Heading title", 'themeslr' ),
	              "param_name" => "heading_title",
	              "value" => "",
	              "description" => ""
	            ),
	            array(
	              "group" => "Styling",
	              "type" => "attach_image",
	              "class" => "",
	              "heading" => esc_attr__( "Background Image", 'themeslr' ),
	              "param_name" => "slide_bg_image",
	              "value" => "", //Default color
	              "description" => esc_attr__( "Default: Semitransparent logo", 'themeslr' )
	            )
	        ),
	        "js_view" => 'VcColumnView'
	    ) );
	    vc_map( array(
	        "name" => esc_attr__("ThemeSLR - Carousel Slide", 'themeslr'),
	        "base" => "themeslr_carousel_everything_inner_content_shortcode",
	        "content_element" => true,
	        "as_child" => array('only' => 'themeslr_carousel_everything_holder_shortcode'), // Use only|except attributes to limit parent (separate multiple values with comma)
	        "params" => array(
	          array(
	            "group" => "Heading",
	            "type" => "textfield",
	            "holder" => "div",
	            "class" => "",
	            "heading" => esc_attr__( "Slide Heading", 'themeslr' ),
	            "param_name" => "slide_heading",
	            "value" => "",
	            "description" => esc_attr__( "Type a heading for the current slide", 'themeslr' )
	          ),
	          array(
	            "group" => "Heading",
	            "type" => "colorpicker",
	            "holder" => "div",
	            "class" => "",
	            "heading" => esc_attr__( "Slide Heading Color", 'themeslr' ),
	            "param_name" => "slide_heading_color",
	            "value" => "",
	          ),
	          array(
	            "group" => "Subheading",
	            "type" => "textfield",
	            "holder" => "div",
	            "class" => "",
	            "heading" => esc_attr__( "Slide Subheading", 'themeslr' ),
	            "param_name" => "slide_subheading",
	            "value" => "",
	            "description" => esc_attr__( "Type a sub heading for the current slide", 'themeslr' )
	          ),
	          array(
	            "group" => "Subheading",
	            "type" => "colorpicker",
	            "holder" => "div",
	            "class" => "",
	            "heading" => esc_attr__( "Slide Subheading Color", 'themeslr' ),
	            "param_name" => "slide_subheading_color",
	            "value" => "",
	          ),
	          array(
	            "group" => "Paragraph",
	            "type" => "textfield",
	            "holder" => "div",
	            "class" => "",
	            "heading" => esc_attr__( "Slide Paragraph", 'themeslr' ),
	            "param_name" => "slide_paragraph",
	            "value" => "",
	            "description" => esc_attr__( "Type a paragraph for the current slide", 'themeslr' )
	          ),
	          array(
	            "group" => "Paragraph",
	            "type" => "colorpicker",
	            "holder" => "div",
	            "class" => "",
	            "heading" => esc_attr__( "Slide Paragraph Color", 'themeslr' ),
	            "param_name" => "slide_paragraph_color",
	            "value" => "",
	          ),
	          array(
	            "group" => "Button",
	            "type" => "textfield",
	            "holder" => "div",
	            "class" => "",
	            "heading" => esc_attr__( "Slide Button Text", 'themeslr' ),
	            "param_name" => "slide_button",
	            "value" => "",
	          ),
	          array(
	            "group" => "Button",
	            "type" => "textfield",
	            "holder" => "div",
	            "class" => "",
	            "heading" => esc_attr__( "Slide Button Link", 'themeslr' ),
	            "param_name" => "slide_button_link",
	            "value" => "",
	          ),
	        )
	    ) );
	    if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
	        class WPBakeryShortCode_themeslr_carousel_everything_holder_shortcode extends WPBakeryShortCodesContainer {
	        }
	    }
	    if ( class_exists( 'WPBakeryShortCode' ) ) {
	        class WPBakeryShortCode_themeslr_carousel_everything_inner_content extends WPBakeryShortCode {
	        }
	    }

        vc_map( 
            array(
            "name" => esc_attr__("ThemeSLR - List group", 'themeslr'),
            "base" => "list_group",
            "category" => esc_attr__('ThemeSLR', 'themeslr'),
            "icon" => "themeslr_shortcode",
            "params" => array(
                array(
                    'group' => 'Options',
                    'type' => 'dropdown',
                    'heading' => __( 'Icon library', 'themeslr' ),
                    'value' => array(
                        __( 'Font Awesome', 'themeslr' ) => 'fontawesome',
                        __( 'Open Iconic', 'themeslr' ) => 'openiconic',
                        __( 'Typicons', 'themeslr' ) => 'typicons',
                        __( 'Entypo', 'themeslr' ) => 'entypo',
                        __( 'Linecons', 'themeslr' ) => 'linecons'
                    ),
                    'param_name' => 'icon_type',
                    'description' => __( 'Select icon library.', 'themeslr' ),
                ),
                array(
                    'group' => 'Options',
                    'type' => 'iconpicker',
                    'heading' => __( 'Icon', 'themeslr' ),
                    'param_name' => 'icon_fontawesome',
                    'value' => 'fa fa-info-circle',
                    'settings' => array(
                        'emptyIcon' => false,
                        // default true, display an "EMPTY" icon?
                        'iconsPerPage' => 4000,
                        // default 100, how many icons per/page to display
                    ),
                    'dependency' => array(
                        'element' => 'icon_type',
                        'value' => 'fontawesome',
                    ),
                    'description' => __( 'Select icon from library.', 'themeslr' ),
                ),
                array(
                    'group' => 'Options',
                    'type' => 'iconpicker',
                    'heading' => __( 'Icon', 'themeslr' ),
                    'param_name' => 'icon_openiconic',
                    'settings' => array(
                        'emptyIcon' => false,
                        // default true, display an "EMPTY" icon?
                        'type' => 'openiconic',
                        'iconsPerPage' => 4000,
                        // default 100, how many icons per/page to display
                    ),
                    'dependency' => array(
                        'element' => 'icon_type',
                        'value' => 'openiconic',
                    ),
                    'description' => __( 'Select icon from library.', 'themeslr' ),
                ),
                array(
                    'group' => 'Options',
                    'type' => 'iconpicker',
                    'heading' => __( 'Icon', 'themeslr' ),
                    'param_name' => 'icon_typicons',
                    'settings' => array(
                        'emptyIcon' => false,
                        // default true, display an "EMPTY" icon?
                        'type' => 'typicons',
                        'iconsPerPage' => 4000,
                        // default 100, how many icons per/page to display
                    ),
                    'dependency' => array(
                        'element' => 'icon_type',
                        'value' => 'typicons',
                    ),
                    'description' => __( 'Select icon from library.', 'themeslr' ),
                ),
                array(
                    'group' => 'Options',
                    'type' => 'iconpicker',
                    'heading' => __( 'Icon', 'themeslr' ),
                    'param_name' => 'icon_entypo',
                    'settings' => array(
                        'emptyIcon' => false,
                        // default true, display an "EMPTY" icon?
                        'type' => 'entypo',
                        'iconsPerPage' => 4000,
                        // default 100, how many icons per/page to display
                    ),
                    'dependency' => array(
                        'element' => 'icon_type',
                        'value' => 'entypo',
                    ),
                ),
                array(
                    'group' => 'Options',
                    'type' => 'iconpicker',
                    'heading' => __( 'Icon', 'themeslr' ),
                    'param_name' => 'icon_linecons',
                    'settings' => array(
                        'emptyIcon' => false,
                        // default true, display an "EMPTY" icon?
                        'type' => 'linecons',
                        'iconsPerPage' => 4000,
                        // default 100, how many icons per/page to display
                    ),
                    'dependency' => array(
                        'element' => 'icon_type',
                        'value' => 'linecons',
                    ),
                    'description' => __( 'Select icon from library.', 'themeslr' ),
                ),
                array(
                    "group" => "Options",
                    "type" => "textfield",
                    "holder" => "div",
                    "class" => "",
                    "heading" => esc_attr__( "List group item heading", 'themeslr' ),
                    "param_name" => "heading",
                    "value" => esc_attr__( "List group item heading", 'themeslr' ),
                    "description" => ""
                ),
                array(
                    "group" => "Options",
                    "type" => "textarea",
                    "holder" => "div",
                    "class" => "",
                    "heading" => esc_attr__( "List group item description", 'themeslr' ),
                    "param_name" => "description",
                    "value" => esc_attr__( "Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius blandit.", 'themeslr' ),
                    "description" => ""
                ),
                array(
                    "group" => "Options",
                    "type" => "dropdown",
                    "heading" => esc_attr__("Status", 'themeslr'),
                    "param_name" => "active",
                    "value" => array(
                        esc_attr__('Active', 'themeslr')   => 'active',
                        esc_attr__('Normal', 'themeslr')   => 'normal',
                    ),
                    "std" => '',
                    "holder" => "div",
                    "class" => "",
                    "description" => ""
                )
            )
        ));


		vc_map( 
			array(
				"name" => esc_attr__("ThemeSLR - Video Popup", "themeslr"),
				"base" => "themeslr-video-popup-shortcode",
				"category" => esc_attr__('ThemeSLR', "themeslr"),
				"params" => array(
					array(
						"group" => "Options",
						'type' => 'iconpicker',
						'heading' => esc_html__( 'Icon', 'js_composer' ),
						'param_name' => 'icon_fontawesome',
						'value' => 'fas fa-adjust',
						'settings' => array(
							'emptyIcon' => false,
							'iconsPerPage' => 500,
						),
						'description' => esc_html__( 'Select icon from library.', 'js_composer' ),
					),
					array(
						"group" => esc_attr__("Options", "themeslr"),
						"type" => "textfield",
						"holder" => "div",
						"class" => "",
						"heading" => esc_attr__("Text Label Next to Icon", 'themeslr'),
						"param_name" => "video_label_next_to_icon",
						"value" => "",
						"description" => "Example: How it Works"
					),
	                array(
	                    "group" => "Options",
	                    "type" => "checkbox",
	                    "class" => "",
	                    "heading" => __( "Custom Image Icon", "themeslr" ),
	                    "param_name" => "custom_image_checkbox",
	                    "description" => __( "If checked, an image upload field will appear. The text/font icon will be overwritten.", "themeslr" )
	                ),
					array(
						"group" => esc_attr__("Options", "themeslr"),
						"type" => "attach_images",
						"holder" => "div",
						"class" => "",
						"heading" => esc_attr__( "Choose image", "themeslr" ),
						"param_name" => "button_image",
						"value" => "",
						"description" => esc_attr__( "Choose image for play button", "themeslr" ),
						'dependency' => array(
						 	'element' => 'custom_image_checkbox',
						  	'value' => 'true',
						),
					),
	                array(
	                    "group" => "Options",
	                    "type" => "dropdown",
	                    "holder" => "div",
	                    "std" => '',
	                    "class" => "",
	                    "heading" => esc_attr__("Image Icon Alignment", 'themeslr'),
	                    "param_name" => "button_image_alignment",
	                    "description" => "",
	                    "value" => array(
	                        esc_attr__('Left', 'themeslr')     => 'image-left text-left',
	                        esc_attr__('Center', 'themeslr')     => 'image-center text-center',
	                        esc_attr__('Right', 'themeslr')     => 'image-right text-right'
	                    ),
						'dependency' => array(
						 	'element' => 'custom_image_checkbox',
						  	'value' => 'true',
						),
	                ),
					array(
						"group" => esc_attr__("Options", "themeslr"),
						"type" => "dropdown",
						"holder" => "div",
						"class" => "",
						"heading" => esc_attr__("Video source", "themeslr"),
						"param_name" => "video_source",
						"std" => '',
						"value" => array(
							'Youtube'   => 'source_youtube',
							'Vimeo'     => 'source_vimeo',
						)
					),
					array(
						"group" => esc_attr__("Options", "themeslr"),
						"dependency" => array(
							'element' => 'video_source',
							'value' => array( 'source_vimeo' ),
						),
						"type" => "textfield",
						"holder" => "div",
						"class" => "",
						"heading" => esc_attr__("Vimeo id link", "themeslr"),
						"param_name" => "vimeo_link_id",
					),
					array(
						"group" => esc_attr__("Options", "themeslr"),
						"dependency" => array(
							'element' => 'video_source',
							'value' => array( 'source_youtube' ),
						),
						"type" => "textfield",
						"holder" => "div",
						"class" => "",
						"heading" => esc_attr__("Youtube id link", "themeslr"),
						"param_name" => "youtube_link_id",
					)
				)
			)
		);
	}
}