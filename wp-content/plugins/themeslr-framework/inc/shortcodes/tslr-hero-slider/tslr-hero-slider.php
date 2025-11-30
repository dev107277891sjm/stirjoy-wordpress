<?php


/*---------------------------------------------*/
/*--- Shortcode: Listed Subcategories ---*/
/*---------------------------------------------*/
function themeslr_hero_slider_parent_shortcode($params,  $content = NULL) {
    extract( shortcode_atts( 
        array(
          'extra_class'     => '',
          'pagination'     => '',
          'navigation'     => '',
        ), $params ) );

    ob_start(); ?>
    
    <?php 
    $shortcode_as_string = $content; 
    $slides_count = 2;
    if ($shortcode_as_string) {
        $slides_count = intval(substr_count($shortcode_as_string, 'themeslr_hero_slide'));
    }
    var_dump($navigation); 
    // var_dump($slides_count);
    ?>

    <div id="bootstrap-touch-slider" class="carousel themeslr-fade bs-slider control-round indicators-line <?php echo esc_attr($extra_class); ?>" data-ride="carousel" data-pause="hover" data-interval="false" >

        <?php if($pagination == true) { ?>
            <!-- Indicators -->
            <ol class="carousel-indicators">
                <?php for ($count = 0; $count <= $slides_count-1; $count++) { ?>
                    <li data-target="#bootstrap-touch-slider" data-slide-to="<?php echo esc_attr($count); ?>" class="<?php if($count == 0){ echo 'active';} ?>"></li>
                <?php } ?>
            </ol>
        <?php } ?>

        <!-- Wrapper For Slides -->
        <div class="carousel-inner" role="listbox">
            <?php echo do_shortcode($content); ?>
        </div><!-- End of Wrapper For Slides -->

        <?php if($navigation == true) { ?>
            <!-- Left Control -->
            <a class="left carousel-control" href="#bootstrap-touch-slider" role="button" data-slide="prev">
                <span class="fa fa-angle-left" aria-hidden="true"></span>
                <span class="sr-only"><?php echo esc_html__('Previous', 'themeslr'); ?></span>
            </a>

            <!-- Right Control -->
            <a class="right carousel-control" href="#bootstrap-touch-slider" role="button" data-slide="next">
                <span class="fa fa-angle-right" aria-hidden="true"></span>
                <span class="sr-only"><?php echo esc_html__('Next', 'themeslr'); ?></span>
            </a>
        <?php } ?>

    </div><!-- End  bootstrap-touch-slider Slider -->
    
    <?php
    return ob_get_clean();
}
add_shortcode('themeslr_slider_hero_parent', 'themeslr_hero_slider_parent_shortcode');

/**
||-> Shortcode: Child Shortcode v1
*/
function themeslr_hero_slide_shortcode($params, $content = NULL) {

    extract( shortcode_atts( 
        array(
          'background_image' => '',
          'slide_layout' => '',
          'alignment' => '',
          'side_element' => '',
          'active_status' => '',
          'heading'   => '',
          'subheading'   => '',
          'button1'   => '',
          'button1_link'   => '',
          'button2'   => '',
          'button2_link'   => '',
        ), $params ) );

    $url_1 = vc_build_link($button1_link);
    $url_2 = vc_build_link($button2_link);
    if ($active_status == true) {
        $slide_active = 'active';
    }else{
        $slide_active = '';
    }
    ob_start(); ?>

        <!-- Third Slide -->
        <div class="item themeslr-single-hero-slide <?php echo esc_attr($slide_active); ?>">

            <img src="http://themeslr.com/live/political-html/img/header-banners/top_banner_short.jpg" alt="slide"  class="slide-image"/>
            <div class="bs-slider-overlay"></div>

            <div class="container">
                <div class="row">
                    <!-- Slide Text Layer -->
                    <?php
                        $inner_class = "col-md-6";
                    ?>
                    <?php if ($slide_layout == '1_column'){ ?>
                        <?php
                            $inner_class = "col-md-12";
                        ?>
                    <?php } ?>
                    <div class="slide-text <?php echo esc_attr($slide_layout.' '.$alignment); ?>">
                        

                        <?php if ($slide_layout == '2_cols_right_elem'){ ?>
                            <div class="<?php echo esc_attr($inner_class); ?>">
                                <h1 data-animation="animated fadeIn"><?php echo esc_html($heading); ?></h1>
                                <p data-animation="animated fadeIn"><?php echo esc_html($subheading); ?></p>
                                <?php if($button1_link){ ?>
                                    <div data-animation="animated fadeIn" class="text-left btn-rounded themeslr_button themeslr_button_shortcode inline-block"><a data-text-color="#ffeeec" data-text-color-hover="#ffeeec" data-bg="#444444" data-bg-hover="#ee4d28" href="<?php echo $url_1['href']; ?>" class="button-winona btn btn-lg" style="background-color:#444444;color:#ffeeec" rel="<?php echo $url_1['rel']; ?>" target="<?php echo $url_1['target']; ?>"><?php echo $url_1['title']; ?></a></div>
                                <?php } ?>
                                
                                <?php if($button2_link){ ?>
                                    <div data-animation="animated fadeIn" class="text-left btn-rounded themeslr_button themeslr_button_shortcode inline-block"><a data-text-color="#000000" data-text-color-hover="#ffeeec" data-bg="#ffffff" data-bg-hover="#ee4d28" href="<?php echo $url_2['href']; ?>" class="button-winona btn btn-lg" style="background: rgb(255, 255, 255); color: rgb(0, 0, 0);" rel="<?php echo $url_2['rel']; ?>" target="<?php echo $url_2['target']; ?>"><?php echo $url_2['title']; ?></a></div>
                                <?php } ?>
                            </div>

                            <?php if ($slide_layout != '1_column'){ ?>
                                <div class="<?php echo esc_attr($inner_class); ?>">
                                    <img src="http://themeslr.com/live/political-html/img/Elements/political_business_president_man.png" alt="slide" data-animation="animated fadeIn" class="slide-image"/>
                                </div>
                            <?php } ?>
                        <?php }else{ ?>
                            <?php if ($slide_layout != '1_column'){ ?>
                                <div class="<?php echo esc_attr($inner_class); ?>">
                                    <img src="http://themeslr.com/live/political-html/img/Elements/political_business_president_man.png" alt="slide" data-animation="animated fadeIn" class="slide-image"/>
                                </div>
                            <?php } ?>

                            <div class="<?php echo esc_attr($inner_class); ?>">
                                <h1 data-animation="animated fadeIn"><?php echo esc_html($heading); ?></h1>
                                <p data-animation="animated fadeIn"><?php echo esc_html($subheading); ?></p>
                                <?php if($button1_link){ ?>
                                    <div data-animation="animated fadeIn" class="text-left btn-rounded themeslr_button themeslr_button_shortcode inline-block"><a data-text-color="#ffeeec" data-text-color-hover="#ffeeec" data-bg="#444444" data-bg-hover="#ee4d28" href="<?php echo $url_1['href']; ?>" class="button-winona btn btn-lg" style="background-color:#444444;color:#ffeeec" rel="<?php echo $url_1['rel']; ?>" target="<?php echo $url_1['target']; ?>"><?php echo $url_1['title']; ?></a></div>
                                <?php } ?>
                                
                                <?php if($button2_link){ ?>
                                    <div data-animation="animated fadeIn" class="text-left btn-rounded themeslr_button themeslr_button_shortcode inline-block"><a data-text-color="#000000" data-text-color-hover="#ffeeec" data-bg="#ffffff" data-bg-hover="#ee4d28" href="<?php echo $url_2['href']; ?>" class="button-winona btn btn-lg" style="background: rgb(255, 255, 255); color: rgb(0, 0, 0);" rel="<?php echo $url_2['rel']; ?>" target="<?php echo $url_2['target']; ?>"><?php echo $url_2['title']; ?></a></div>
                                <?php } ?>
                            </div>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>

    <?php
    return ob_get_clean();
}
add_shortcode('themeslr_hero_slide', 'themeslr_hero_slide_shortcode');


add_action( 'vc_before_init', 'themeslr_vc_map__hero_slider' );
function themeslr_vc_map__hero_slider() {
    if (function_exists('vc_map')) {
        vc_map( array(
            "name" => esc_attr__("ThemeSLR - Hero Slider", "themeslr"),
            "base" => "themeslr_slider_hero_parent",
            "as_parent" => array('only' => 'themeslr_hero_slide'), 
            "category" => esc_attr__('ThemeSLR', 'themeslr'),
            "content_element" => true,
            "show_settings_on_create" => false,
            "is_container" => true,
            "js_view"                 => 'VcColumnView',
            "params" => array(
                array(
                    "group" => "General",
                    "type" => "textfield",
                    "holder" => "div",
                    "class" => "",
                    "heading" => esc_attr__("Extra Clas", "themeslr"),
                    "param_name" => "extra_class",
                ),
                array(
                    "group" => "General",
                    "type" => "checkbox",
                    "holder" => "div",
                    "class" => "",
                    "heading" => esc_attr__("Pagination (Dots)", "themeslr"),
                    "param_name" => "pagination",
                    
                ),
                array(
                    "group" => "General",
                    "type" => "checkbox",
                    "holder" => "div",
                    "class" => "",
                    "heading" => esc_attr__("Navigation (Left / Right Buttons)", "themeslr"),
                    "param_name" => "navigation",
                    
                ),
            ),
        ) );
        vc_map( array(
            "name" => esc_attr__("ThemeSLR - Hero Slide", "themeslr"),
            "base" => "themeslr_hero_slide",
            "content_element" => true,
            "as_child" => array('only' => 'themeslr_slider_hero_parent'),
            "params" => array(
                array(
                   "group" => "General",
                   "type" => "attach_image",
                   "holder" => "div",
                   "class" => "",
                   "heading" => esc_attr__("Background Image", 'themeslr'),
                   "param_name" => "background_image",
                   "value" => "",
                   "description" => ""
                ),
                array(
                    "group" => "General",
                    "type" => "dropdown",
                    "holder" => "div",
                    "class" => "",
                    "heading" => esc_attr__("Layout", 'themeslr'),
                    "param_name" => "slide_layout",
                    "value" => array(
                        'Choose'                 => '',
                        '2 Columns (Texts left / Image Right)'                 => '2_cols_right_elem',
                        '2 Columns (Texts Right / Image Left)'                 => '2_cols_left_elem',
                        '1 Column'                 => '1_column',
                    ),
                ),
                array(
                    "group" => "General",
                    "type" => "dropdown",
                    "holder" => "div",
                    "class" => "",
                    "heading" => esc_attr__("Alignment (texts & buttons)", 'themeslr'),
                    "param_name" => "alignment",
                    "std" => 'left',
                    "value" => array(
                        'Choose'                 => '',
                        'Left'                 => 'text-left',
                        'Center'                 => 'text-center',
                        'Right'                 => 'text-right',
                    ),
                ),
                array(
                   "group" => "Side Image",
                   "type" => "attach_image",
                   "holder" => "div",
                   "class" => "",
                   "heading" => esc_attr__("Side Element (Image)", 'themeslr'),
                   "param_name" => "side_element",
                   "value" => "",
                   "description" => "",
                    "dependency" => array(
                      'element' => 'slide_layout',
                      'value' => array('2_cols_right_elem', '2_cols_left_elem'),
                    ),
                ),
                array(
                    "group" => "General",
                    "type" => "checkbox",
                    "holder" => "div",
                    "class" => "",
                    "heading" => esc_attr__("Active", "themeslr"),
                    "param_name" => "active_status",
                    "description" => __( "Only 1 slide can be active.", "modeltheme-addons-for-wpbakery" )
                ),
                array(
                    "group" => "Heading",
                    "type" => "textfield",
                    "holder" => "div",
                    "admin_label" => true,
                    "class" => "",
                    "heading" => esc_attr__("Heading", "themeslr"),
                    "param_name" => "heading",
                ),
                array(
                    "group" => "Sub-heading",
                    "type" => "textarea",
                    "holder" => "div",
                    "class" => "",
                    "heading" => esc_attr__("Sub-heading", "themeslr"),
                    "param_name" => "subheading",
                ),
                array(
                    "group" => "Call to Action",
                    "type" => "checkbox",
                    "holder" => "div",
                    "class" => "",
                    "heading" => esc_attr__("Call to action Button 1", "themeslr"),
                    "param_name" => "button1",
                ),
                array(
                    "group" => "Call to Action",
                    "type" => "vc_link",
                    "holder" => "div",
                    "class" => "",
                    "heading" => esc_attr__("Button 1 Link & Text", "themeslr"),
                    "param_name" => "button1_link",
                    "dependency" => array(
                      'element' => 'button1',
                      'value' => "true",
                    ),
                ),

                array(
                    "group" => "Call to Action",
                    "type" => "checkbox",
                    "holder" => "div",
                    "class" => "",
                    "heading" => esc_attr__("Call to action Button 2", "themeslr"),
                    "param_name" => "button2",
                ),

                array(
                    "group" => "Call to Action",
                    "type" => "vc_link",
                    "holder" => "div",
                    "class" => "",
                    "heading" => esc_attr__("Button 2 Link & Text", "themeslr"),
                    "param_name" => "button2_link",
                    "dependency" => array(
                      'element' => 'button2',
                      'value' => "true",
                    ),
                ),

            )
        ) );
        //Your "container" content element should extend WPBakeryShortCodesContainer class to inherit all required functionality
        if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
            class WPBakeryShortCode_themeslr_slider_hero_parent extends WPBakeryShortCodesContainer {
            }
        }
        if ( class_exists( 'WPBakeryShortCode' ) ) {
            class WPBakeryShortCode_themeslr_hero_slide extends WPBakeryShortCode {
            }
        }
    }
}