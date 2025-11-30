<?php 

class TSLR_Smooth_Gallery {

    protected $tslr_shortcode_columns;
    
    public function __construct() {
        add_shortcode('tslr_smooth_gallery', array($this, 'tslr_smooth_gallery'));
        add_shortcode('tslr_smooth_gallery_item', array($this, 'tslr_smooth_gallery_item'));
        add_action('init', array($this, 'tslr_smooth_gallery_map_vc_element'));
        add_action('init', array($this, 'tslr_smooth_gallery_item_map_vc_element'));
    }


    /**
    ||-> Shortcode: Smooth Gallery Group
    */
    public function tslr_smooth_gallery($params,  $content = NULL) {
        extract( shortcode_atts( 
            array(
                'el_class'              => '',
                'gallery_columns'      => '',
                'gallery_image_shape'      => '',
                'gallery_title_alignment'      => '',
                'gallery_title_text_transform'      => '',
            ), $params ) );
        $html = '';
        $this->tslr_shortcode_columns = $gallery_columns;
        $this->tslr_shortcode_image_shape = $gallery_image_shape;
        $this->gallery_title_alignment = $gallery_title_alignment;
        $this->gallery_title_text_transform = $gallery_title_text_transform;
            
        $html .= '<div class="tslr_smooth_gallery-shortcode row">';
            $html .= do_shortcode($content);
        $html .= '</div>';
        return $html;
    }
    // add_shortcode('tslr_smooth_gallery', 'tslr_smooth_gallery');


    /**
    ||-> Shortcode: Child Shortcode v1
    */
    public function tslr_smooth_gallery_item($params, $content = NULL) {
        extract( shortcode_atts( 
            array(
                'item_title'           => '',
                'item_url'             => '',
                'item_url_text'        => '',
                'item_image'           => '',
                'target'           => '',
            ), $params ) );

        if ($target) {
          $target = 'target="_blank"';
        }

        $html = '';
        #IMG
        $img = wp_get_attachment_image_src($item_image, 'full'); 
        $html .= '<article class="tslr_smooth_gallery_item '.$this->tslr_shortcode_columns.'">';
            if (isset($item_image)) {
                $html .= '<div class="tslr_smooth_gallery_item_inner">';
                    // inner image
                    $html .= '<div class="tslr_smooth_gallery_item_inner_image">';
                        $html .= '<img class="item_image '.$this->tslr_shortcode_image_shape.'" src="'.$img[0].'" alt="" />';
                    $html .= '</div>';
                    // inner title
                    $html .= '<h3 class="item_title '.$this->gallery_title_alignment.' '.$this->gallery_title_text_transform.'">'.$item_title.'</h3>';
                    // inner link
                    $html .= '<a '.$target.' class="tslr_smooth_gallery_item_url" href="'.$item_url.'"></a>';
                $html .= '</div>';
            }
        $html .= '</article>';
        return $html;
    }
    // add_shortcode('tslr_smooth_gallery_item', 'tslr_smooth_gallery_item');


    /**
    ||-> Map Shortcode in Visual Composer with: vc_map();
    */
    function tslr_smooth_gallery_map_vc_element() {
        if (function_exists("vc_map")) {
            //Register "container" content element. It will hold all your inner (child) content elements
            vc_map( array(
                "name" => esc_attr__("TSLR - Smooth Gallery", 'themeslr'),
                "base" => "tslr_smooth_gallery",
                "as_parent" => array('only' => 'tslr_smooth_gallery_item'), // Use only|except attributes to limit child shortcodes (separate multiple values with comma)
                "content_element" => true,
                "show_settings_on_create" => true,
                "icon" => "themeslr_shortcode",
                "category" => esc_attr__('ThemeSLR', 'themeslr'),
                "is_container" => true,
                "params" => array(
                    // add params same as with any other content element
                    array(
                        "type" => "dropdown",
                        "heading" => esc_attr__("Gallery Columns", 'themeslr'),
                        "param_name" => "gallery_columns",
                        "std" => '',
                        "holder" => "div",
                        "class" => "",
                        "description" => "",
                        "value" => array(
                            esc_attr__('2 Columns', 'themeslr')  => 'col-md-6',
                            esc_attr__('3 Columns', 'themeslr')  => 'col-md-4',
                            esc_attr__('4 Columns', 'themeslr')  => 'col-md-3',
                            esc_attr__('6 Columns', 'themeslr')  => 'col-md-2',
                        )
                    ),
                    array(
                        "type" => "dropdown",
                        "heading" => esc_attr__("Gallery Images Shape", 'themeslr'),
                        "param_name" => "gallery_image_shape",
                        "std" => '',
                        "holder" => "div",
                        "class" => "",
                        "description" => "",
                        "value" => array(
                            esc_attr__('Normal', 'themeslr')  => 'img-default',
                            esc_attr__('Rounded', 'themeslr')  => 'img-rounded',
                            esc_attr__('Circle', 'themeslr')  => 'img-circle',
                            esc_attr__('Thumbnail', 'themeslr')  => 'img-thumbnail',
                        )
                    ),
                    array(
                        "type" => "dropdown",
                        "heading" => esc_attr__("Gallery Title Alignment", 'themeslr'),
                        "param_name" => "gallery_title_alignment",
                        "std" => '',
                        "holder" => "div",
                        "class" => "",
                        "description" => "",
                        "value" => array(
                            esc_attr__('Left', 'themeslr')  => 'text-left',
                            esc_attr__('Center', 'themeslr')  => 'text-center',
                            esc_attr__('Right', 'themeslr')  => 'text-right',
                        )
                    ),
                    array(
                        "type" => "dropdown",
                        "heading" => esc_attr__("Gallery Title Text Transform", 'themeslr'),
                        "param_name" => "gallery_title_text_transform",
                        "std" => '',
                        "holder" => "div",
                        "class" => "",
                        "description" => "",
                        "value" => array(
                            esc_attr__('Lowercase', 'themeslr')  => 'text-lowercase',
                            esc_attr__('Uppercase', 'themeslr')  => 'text-uppercase',
                            esc_attr__('Capitalize', 'themeslr')  => 'text-capitalize',
                        )
                    ),
                    array(
                        "type" => "textfield",
                        "heading" => __("Extra class name", "themeslr"),
                        "param_name" => "el_class",
                        "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "themeslr")
                    ),
                ),
                "js_view" => 'VcColumnView'
            ) );
        }
    }
    function tslr_smooth_gallery_item_map_vc_element() {
        if (function_exists("vc_map")) {
            vc_map( array(
                "name" => esc_attr__("TSLR - Smooth Gallery Item", 'themeslr'),
                "base" => "tslr_smooth_gallery_item",
                "icon" => "themeslr_shortcode",
                "content_element" => true,
                "as_child" => array('only' => 'tslr_smooth_gallery'), // Use only|except attributes to limit parent (separate multiple values with comma)
                "params" => array(
                    // add params same as with any other content element
                    array(
                        "group"        => "General Options",
                        "type"         => "textfield",
                        "holder"       => "div",
                        "class"        => "",
                        "param_name"   => "item_title",
                        "heading"      => esc_attr__("Title", 'themeslr'),
                        "description"  => esc_attr__("Enter title for current gallery item", 'themeslr'),
                    ),
                    array(
                        "group"        => "General Options",
                        "type"         => "textfield",
                        "holder"       => "div",
                        "class"        => "",
                        "param_name"   => "item_url",
                        "heading"      => esc_attr__("Link URL", 'themeslr'),
                        "description"  => esc_attr__("Enter Item's link url", 'themeslr'),
                    ),
                    array(
                        "group"         => "General Options",
                        "type"          => "attach_image",
                        "holder"        => "div",
                        "class"         => "",
                        "heading"       => esc_attr__( "Gallery Image", 'themeslr' ),
                        "param_name"    => "item_image",
                        "description"   => ""
                    ),
                    array(
                        "group" => "General Options",
                        "type" => "checkbox",
                        "class" => "",
                        "heading" => __( "Open Link in a new tab?", "themeslr" ),
                        "param_name" => "target",
                        "value" => __( "target", "themeslr" ),
                        "description" => __( "If checked, the link will open in a new tab", "themeslr" )
                    ),
                )
            ) );
        }
    }
}


new TSLR_Smooth_Gallery();
//Your "container" content element should extend WPBakeryShortCodesContainer class to inherit all required functionality
if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
    class WPBakeryShortCode_tslr_smooth_gallery extends WPBakeryShortCodesContainer {
    }
}
if ( class_exists( 'WPBakeryShortCode' ) ) {
    class WPBakeryShortCode_tslr_smooth_gallery_Item extends WPBakeryShortCode {
    }
}
?>