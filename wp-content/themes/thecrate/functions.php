<?php

if ( ! isset( $content_width ) ) {
    $content_width = 640; /* pixels */
}

/**
||-> thecrate_redux
*/
function thecrate_redux($redux_meta_name1 = '',$redux_meta_name2 = ''){

    global  $thecrate_redux;
    if (is_null($thecrate_redux)) {
        return;
    }

    $html = '';
    if (isset($redux_meta_name1) && !empty($redux_meta_name2)) {
        $html = $thecrate_redux[$redux_meta_name1][$redux_meta_name2];
    }elseif(isset($redux_meta_name1) && empty($redux_meta_name2)){
        if (isset($thecrate_redux[$redux_meta_name1])) {
            $html = $thecrate_redux[$redux_meta_name1];
        }
    }
    
    return $html;

}


/**
||-> thecrate_setup
*/
function thecrate_setup() {

    /*
     * Make theme available for translation.
     * Translations can be filed in the /languages/ directory.
     * If you're building a theme based on thecrate, use a find and replace
     * to change 'thecrate' to the name of your theme in all the template files
     */
    load_theme_textdomain( 'thecrate', get_template_directory() . '/languages' );

    // This theme uses wp_nav_menu() in one location.
    register_nav_menus( array(
        'primary' => esc_html__( 'Primary menu', 'thecrate' ),
    ) );

    // ADD THEME SUPPORT
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'custom-header' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
   	add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'html5', array(
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
    ) );// Switch default core markup for search form, comment form, and comments to output valid HTML5.
    // Enable support for Post Formats.
    add_theme_support( 'custom-background', apply_filters( 'thecrate_custom_background_args', array(
        'default-color' => 'ffffff',
        'default-image' => '',
    ) ) );// Set up the WordPress core custom background feature.
    remove_theme_support( 'widgets-block-editor' );

}
add_action( 'after_setup_theme', 'thecrate_setup' );



/**
||-> Register widget area.
||-> @link http://codex.wordpress.org/Function_Reference/register_sidebar
*/
function thecrate_widgets_init() {

    global  $thecrate_redux;

    register_sidebar( array(
        'name'          => esc_html__( 'Sidebar', 'thecrate' ),
        'id'            => 'sidebar-1',
        'description'   => esc_html__( 'Main Site Sidebar', 'thecrate' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    if ( class_exists( 'WooCommerce' ) && class_exists( 'ReduxFrameworkPlugin' ) ) {
        register_sidebar( array(
            'name'          => esc_html__( 'WooCommerce', 'thecrate' ),
            'id'            => 'woocommerce',
            'description'   => esc_html__( 'For WooCommerce pages', 'thecrate' ),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ) );
    }

    if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
        if (!empty($thecrate_redux['thecrate_dynamic_sidebars'])){
            foreach ($thecrate_redux['thecrate_dynamic_sidebars'] as &$value) {
                $id           = str_replace(' ', '', esc_attr($value));
                $id_lowercase = strtolower(esc_attr($id));
                if ($id_lowercase) {
                    register_sidebar( array(
                        'name'          => esc_attr($value),
                        'id'            => esc_attr($id_lowercase),
                        'description'   => esc_html__( 'Sidebar ', 'thecrate' ) . esc_attr($value),
                        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                        'after_widget'  => '</aside>',
                        'before_title'  => '<h3 class="widget-title">',
                        'after_title'   => '</h3>',
                    ) );
                }
            }
        }
        
        // FOOTER ROW 1
        if (isset($thecrate_redux['thecrate_footer_row_1_layout'])) {
            $footer_row_1 = $thecrate_redux['thecrate_footer_row_1_layout'];
            $nr1 = array("1", "2", "3", "4", "5", "6");
            if (in_array($footer_row_1, $nr1)) {
                for ($i=1; $i <= $footer_row_1 ; $i++) { 
                    register_sidebar( array(
                        'name'          => esc_html__( 'Footer Col. ','thecrate').esc_attr($i),
                        'id'            => 'footer_row_1_'.esc_attr($i),
                        'description'   => esc_html__( 'Footer Widgetized Area No. ', 'thecrate' ) . esc_attr($i),
                        'before_widget' => '<aside id="%1$s" class="widget vc_column_vc_container %2$s">',
                        'after_widget'  => '</aside>',
                        'before_title'  => '<h3 class="widget-title">',
                        'after_title'   => '</h3>',
                    ) );
                }
            }elseif ($footer_row_1 == 'column_half_sub_half' || $footer_row_1 == 'column_sub_half_half') {
                $footer_row_1 = '3';
                for ($i=1; $i <= $footer_row_1 ; $i++) { 
                    register_sidebar( array(
                        'name'          => esc_html__( 'Footer Col. ', 'thecrate' ) . esc_attr($i),
                        'id'            => 'footer_row_1_'.esc_attr($i),
                        'description'   => esc_html__( 'Footer Widgetized Area No. ', 'thecrate' ) . esc_attr($i),
                        'before_widget' => '<aside id="%1$s" class="widget vc_column_vc_container %2$s">',
                        'after_widget'  => '</aside>',
                        'before_title'  => '<h3 class="widget-title">',
                        'after_title'   => '</h3>',
                    ) );
                }
            }elseif ($footer_row_1 == 'column_sub_fourth_third' || $footer_row_1 == 'column_third_sub_fourth' || $footer_row_1 == 'column_3_2_2_2_3') {
                $footer_row_1 = '5';
                for ($i=1; $i <= $footer_row_1 ; $i++) { 
                    register_sidebar( array(
                        'name'          => esc_html__( 'Footer Col. ','thecrate').esc_attr($i),
                        'id'            => 'footer_row_1_'.esc_attr($i),
                        'description'   => esc_html__( 'Footer Widgetized Area No. ', 'thecrate' ) . esc_attr($i),
                        'before_widget' => '<aside id="%1$s" class="widget vc_column_vc_container %2$s">',
                        'after_widget'  => '</aside>',
                        'before_title'  => '<h3 class="widget-title">',
                        'after_title'   => '</h3>',
                    ) );
                }
            }elseif ($footer_row_1 == 'column_sub_third_half' || $footer_row_1 == 'column_half_sub_third' || $footer_row_1 == 'column_four_two_two_four') {
                $footer_row_1 = '4';
                for ($i=1; $i <= $footer_row_1 ; $i++) { 
                    register_sidebar( array(
                        'name'          => esc_html__( 'Footer Col. ','thecrate').esc_attr($i),
                        'id'            => 'footer_row_1_'.esc_attr($i),
                        'description'   => esc_html__( 'Footer Widgetized Area No. ', 'thecrate' ) . esc_attr($i),
                        'before_widget' => '<aside id="%1$s" class="widget vc_column_vc_container %2$s">',
                        'after_widget'  => '</aside>',
                        'before_title'  => '<h3 class="widget-title">',
                        'after_title'   => '</h3>',
                    ) );
                }
            }
        }
    }
}
add_action( 'widgets_init', 'thecrate_widgets_init' );


/**
||-> Enqueue scripts and styles.
*/
function thecrate_scripts() {

    //STYLESHEETS
    wp_enqueue_style( "font-awesome5", get_template_directory_uri()."/css/font-awesome/all.min.css" );
    wp_enqueue_style( "bootstrap", get_template_directory_uri()."/css/bootstrap.min.css" );
    wp_enqueue_style( "owl-carousel", get_template_directory_uri()."/css/owl.carousel.css" );
    wp_enqueue_style( "thecrate-style", get_stylesheet_uri(), array(), null );
    wp_enqueue_style( "loaders", get_template_directory_uri()."/css/loaders.css" );
    wp_enqueue_style( "nice-select", get_template_directory_uri()."/css/nice-select.css" );
    if ( ! class_exists( "Vc_Manager" ) ) {
        wp_enqueue_style( "js_composer", get_template_directory_uri()."/css/js_composer.css" );
    }
    wp_enqueue_style( "thecrate-gutenberg-frontend", get_template_directory_uri()."/css/gutenberg-frontend.css" );
    // RTL SUPPORT
    if ( is_rtl() ) {
        wp_enqueue_style( "thecrate-rtl", get_template_directory_uri()."/css/thecrate-rtl.css" );
    }

    //SCRIPTS
    wp_enqueue_script( "modernizr", get_template_directory_uri() . "/js/modernizr.2.6.2.js", array("jquery"), "2.6.2", true );
    wp_enqueue_script( "classie", get_template_directory_uri() . "/js/classie.js", array("jquery"), "2.6.2", true );
    wp_enqueue_script( "jquery-appear", get_template_directory_uri() . "/js/jquery.appear.js", array("jquery"), "1.0.0", true );
    wp_enqueue_script( "jquery-countTo", get_template_directory_uri() . "/js/jquery.countto.js", array("jquery"), "1.0.0", true );
    wp_enqueue_script( "owl-carousel", get_template_directory_uri() . "/js/owl.carousel.js", array("jquery"), "1.3.3", true );
    wp_enqueue_script( "modernizr-viewport", get_template_directory_uri() . "/js/modernizr.viewport.js", array("jquery"), "2.6.2", true );
    wp_enqueue_script( "bootstrap", get_template_directory_uri() . "/js/bootstrap.min.js", array("jquery"), "3.3.1", true );
    wp_enqueue_script( "jquery-countdown", get_template_directory_uri() . "/js/jquery.countdown.js", array("jquery"), "1.0.0", true );
    wp_enqueue_script( "stickit", get_template_directory_uri() . "/js/jquery.stickit.min.js", array("jquery"), "1.0.0", true );
    wp_enqueue_script( "sticky", get_template_directory_uri() . "/js/jquery.sticky.js", array("jquery"), "1.0.0", true );
    wp_enqueue_script( "loaders", get_template_directory_uri() . "/js/loaders.css.js", array("jquery"), "1.0.0", true );
    wp_enqueue_script( "jquery-nice-select", get_template_directory_uri() . "/js/jquery.nice-select.min.js", array("jquery"), "1.0", true );
    wp_enqueue_script( "thecrate-custom", get_template_directory_uri() . "/js/thecrate-custom.js", array("jquery"), false, true );
    if ( is_singular() && comments_open() && get_option( "thread_comments" ) ) {
        wp_enqueue_script( "comment-reply" );
    }
}
add_action( "wp_enqueue_scripts", "thecrate_scripts" );


/**
||-> Enqueue admin css/js
*/
function thecrate_enqueue_admin_scripts( $hook ) {
    // CSS
    wp_enqueue_style( "thecrate-admin-style", get_template_directory_uri().'/css/admin-style.css' );
}
add_action('admin_enqueue_scripts', 'thecrate_enqueue_admin_scripts');


/**
||-> Enqueue css to js_composer
*/
add_action( 'vc_base_register_front_css', 'thecrate_enqueue_front_css_foreever' );
function thecrate_enqueue_front_css_foreever() {
    wp_enqueue_style( 'js_composer_front' );
}


/**
||-> Force Visual Composer to initialize as "built into the theme". This will hide certain tabs under the Settings->Visual Composer page
*/
add_action( 'vc_before_init', 'thecrate_vcSetAsTheme' );
function thecrate_vcSetAsTheme() {
    vc_set_as_theme( true );
}


/**
||-> Other required parts/files
*/
/* ========= LOAD CUSTOM FUNCTIONS ===================================== */
require_once get_template_directory() . '/inc/custom-functions.php';
require_once get_template_directory() . '/inc/custom-functions.header.php';
require_once get_template_directory() . '/inc/custom-functions.footer.php';
/* ========= Customizer additions. ===================================== */
require_once get_template_directory() . '/inc/customizer.php';
/* ========= Load Jetpack compatibility file. ===================================== */
require_once get_template_directory() . '/inc/jetpack.php';
/* ========= Include the TGM_Plugin_Activation class. ===================================== */
require_once get_template_directory() . '/inc/tgm/include_plugins.php';

function thecrate_load_on_init(){
    require_once get_template_directory() . '/redux-framework/config.php';
}
add_action('after_setup_theme', 'thecrate_load_on_init');
/* ========= CUSTOM COMMENTS ===================================== */
require_once get_template_directory() . '/inc/custom-comments.php';
/* ========= Load Gutenberg editor functions ===================================== */
require_once get_template_directory() . '/inc/custom-functions.gutenberg.php';
/* ========= Load WooCommerce functions ===================================== */
require_once get_template_directory() . '/inc/custom-functions.woocommerce.php';


/**
||-> add_image_size //Resize images
*/
/* ========= RESIZE IMAGES ===================================== */
add_image_size( 'thecrate_related_post_pic700x400',  700, 400, true );
add_image_size( 'thecrate_post_pic700x450',          700, 450, true );
add_image_size( 'thecrate_post_widget_pic70x70',     70, 70, true );
add_image_size( 'thecrate_530x600',                530, 600, true );
add_image_size( 'thecrate_1300x650',                1300, 650, true );
add_image_size( 'thecrate_1420x140',                1420, 140, true );
add_image_size( 'thecrate_testimonials_150x150',    150, 150, true );
add_image_size( 'thecrate_clients_160x90',         160, 90, true );
add_image_size( 'thecrate_portfolio01_390x275',     390, 275, true );
add_image_size( 'thecrate_blogpost01_1420x170',     1420, 170, true );
add_image_size( 'thecrate_about_600x600',     600, 600, true );
add_image_size( 'thecrate_testimonials02_250x530',     250, 300, true );


/**
||-> LIMIT POST CONTENT
*/
function thecrate_excerpt_limit($string, $word_limit) {
    $words = explode(' ', $string, ($word_limit + 1));
    if(count($words) > $word_limit) {
        array_pop($words);
    }
    return implode(' ', $words);
}


/**
||-> PAGINATION
*/
if ( ! function_exists( 'thecrate_pagination' ) ) {
    function thecrate_pagination($query = null) {

        if (!$query) {
            global  $wp_query;
            $query = $wp_query;
        }
        
        $big = 999999999; // need an unlikely integer
        $current = (get_query_var('paged')) ? get_query_var('paged') : ((get_query_var('page')) ? get_query_var('page') : '1');
        echo paginate_links( 
            array(
                'base'          => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                'format'        => '?paged=%#%',
                'current'       => max( 1, $current ),
                'total'         => $query->max_num_pages,
                'prev_text'     => '&#171;',
                'next_text'     => '&#187;',
            ) 
        );
    }
}


/**
||-> SEARCH FOR POSTS ONLY
*/
function thecrate_search_filter($query) {
    if ($query->is_search && !isset($_GET['post_type'])) {
        $query->set('post_type', 'post');
    }
    return $query;
}
if(!is_admin()){
    add_filter('pre_get_posts', 'thecrate_search_filter');
}


/**
||-> FUNCTION: ADD EDITOR STYLE
*/
function thecrate_add_editor_styles() {
    add_editor_style( 'css/custom-editor-style.css' );
}
add_action( 'admin_init', 'thecrate_add_editor_styles' );


/**
||-> FUNCTION: CUSTOM SEARCH FORM
*/
function thecrate_custom_search_form(){

    $search_in_cpt = 'post';
    if (class_exists( 'WooCommerce' )) {
        $search_in_cpt = 'product';
    }

    $content = '';
    $content .= '<div class="theme-search">
                    <form method="GET" action="'.esc_url(home_url('/')).'">
                        <input class="search-input" placeholder="'.esc_attr__('Enter search term...', 'thecrate').'" type="search" value="" name="s" id="search" />
                        <input type="hidden" name="post_type" value="'.esc_attr($search_in_cpt).'" />
                        <input class="search-submit" type="submit" value="&#xf002;" />
                        <i class="fas fa-times theme-search-closing-icon"></i>
                    </form>
                </div>';

    return $content;
}


// |---> REVOLUTION SLIDER
if(function_exists( 'set_revslider_as_theme' )){
    add_action( 'init', 'thecrate_disable_revslider_update_notices' );
    function thecrate_disable_revslider_update_notices() {
        set_revslider_as_theme();
    }
}



/**
* 
* Returns a paragraph with text only for logged in users -> When no primary menu is set
* @param string $text_align the text align (it can be center, left or right)
* 
**/
if (!function_exists('thecrate_no_menu_set_notice')) {
    function thecrate_no_menu_set_notice($text_align = ''){
        
        $html = '';
        
        if ( is_user_logged_in() ) {
            $html .= '<p class="no-menu text-'.esc_attr($text_align).'">'.esc_html__('Primary navigation menu is missing. Add one from "Appearance" -> "Menus"','thecrate').'</p>';
        }

        return $html;
    }
}


if ( ! function_exists( 'thecrate_post_views' ) ) {
    function thecrate_post_views($post_id) { 
        $count_key = 'thecrate_post_views_count';
        $count = get_post_meta($post_id, $count_key, true);
        if($count ==''){
            $count = 1;
            delete_post_meta($post_id, $count_key);
            add_post_meta($post_id, $count_key, '1');
        } else {        
            $count++;        
            update_post_meta($post_id, $count_key, $count);    
        }
    }
}

if ( ! function_exists( 'thecrate_post_views_number' ) ) {
    function thecrate_post_views_number($post_id) {
        $html = '';
        $html .= '<i class="far fa-eye"></i> ';
        if ( get_post_meta( $post_id , 'thecrate_post_views_count', true) == '') {
            $html .= esc_html__('0', 'thecrate');                            
        } else { 
            $html .= get_post_meta( $post_id , 'thecrate_post_views_count', true);
        }
        return $html;
    }
}


// Filter except length to 35 words.
// tn custom excerpt length
function thecrate_custom_excerpt_length( $length ) {
    return 35;
}
add_filter( 'excerpt_length', 'thecrate_custom_excerpt_length', 999 );


// KSES ALLOWED HTML
if (!function_exists('thecrate_kses_allowed_html')) {
    function thecrate_kses_allowed_html($tags, $context) {
      switch($context) {
        case 'link': 
            $tags = array( 
                'a' => array(
                    'href' => array(),
                    'class' => array(),
                    'title' => array(),
                    'target' => array(),
                    'rel' => array(),
                    'data-commentid' => array(),
                    'data-postid' => array(),
                    'data-belowelement' => array(),
                    'data-respondelement' => array(),
                    'data-replyto' => array(),
                    'aria-label' => array(),
                ),
                'img' => array(
                    'src' => array(),
                    'alt' => array(),
                    'style' => array(),
                    'height' => array(),
                    'width' => array(),

                ),
            );
            return $tags;
        break;

        case 'icon':
            $tags = array(
                'i' => array(
                    'class' => array(),
                ),
            );
            return $tags;
        break;
        
        default: 
            return $tags;
      }
    }
    add_filter( 'wp_kses_allowed_html', 'thecrate_kses_allowed_html', 10, 2);
}


/**
 * Minifying the CSS
  */
function thecrate_minify_css($css){
  $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
  return $css;
}


// Disable WooCommerce Setup Wizard
add_filter('woocommerce_enable_setup_wizard', '__return_false');


// Disable Elementor Setup Wizard
function thecrate_disable_elementor_onboarding() {
    // Update Elementor onboarding option to mark it as completed
    update_option( 'elementor_onboarding_completed', 'yes' );
}
add_action( 'admin_init', 'thecrate_disable_elementor_onboarding' );