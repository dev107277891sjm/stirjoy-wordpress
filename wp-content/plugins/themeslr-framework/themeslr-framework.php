<?php
/**
* Plugin Name: ThemeSLR Framework
* Plugin URI: http://themeslr.com/
* Description: ThemeSLR Framework;
* Version: 2.1
* Author: ThemeSLR
* Author http://themeslr.com/
* Text Domain: themeslr
*/
// RELEASE DATE: 22-SEP-2025

$plugin_dir = plugin_dir_path( __FILE__ );


DEFINE( 'THEMESLR_FRAMEWORK_DIR', plugin_dir_url( __FILE__ ));


add_filter('widget_text','do_shortcode');


/**
||-> Function: require_once() plugin necessary parts
*/
require_once('inc/api-v3/API.php'); // POST TYPES
require_once('inc/post-types/post-types.php'); // POST TYPES
require_once('inc/shortcodes/shortcodes.php'); // SHORTCODES
require_once('inc/elementor-widgets/include.php'); // SHORTCODES
require_once('inc/widgets/widgets-theme.php'); // THEME WIDGETS (WP 5 Requirement)
require_once('inc/metaboxes/metaboxes.php'); // METABOXES
// DEMO IMPORTER V2
require_once('inc/demo-importer-v2/wbc907-plugin-example.php');
require_once('inc/shortcodes/vc-shortcodes.inc.arrays.php');


// cmb2
global $pagenow;
$excluded_cmb = array(
    'user-edit.php', 'profile.php'
);
if (!in_array($pagenow, $excluded_cmb)) {
    require_once ('vendor/cmb2/init.php');
}





/**

||-> Function: LOAD PLUGIN TEXTDOMAIN

*/
function themeslr_init_lang(){
    load_plugin_textdomain('themeslr', false, dirname( plugin_basename( __FILE__ ) ). '/languages/');
}
add_action('init', 'themeslr_init_lang');


// |---> REDUX FRAMEWORK (WP 5 Requirement)
if (!function_exists('politicalwp_RemoveDemoModeLink')) {
    function politicalwp_RemoveDemoModeLink() { // Be sure to rename this function to something more unique
        if ( class_exists('ReduxFrameworkPlugin') ) {
            remove_filter( 'plugin_row_meta', array( ReduxFrameworkPlugin::get_instance(), 'plugin_metalinks'), null, 2 );
        }
        if ( class_exists('ReduxFrameworkPlugin') ) {
            remove_action('admin_notices', array( ReduxFrameworkPlugin::get_instance(), 'admin_notices' ) );    
        }
    }
    add_action('init', 'politicalwp_RemoveDemoModeLink');
}


/**
||-> Function: themeslr_enqueue_scripts() becomes themeslr_framework() in v1.5
*/
function themeslr_framework() {
    // CSS
    wp_register_style( 'animate',  plugin_dir_url( __FILE__ ) . 'css/animate.css' );
    wp_enqueue_style( 'animate' );
    wp_register_style( 'themeslr-shortcodes-inc',  plugin_dir_url( __FILE__ ) . 'inc/shortcodes/shortcodes.css' );
    wp_enqueue_style( 'themeslr-shortcodes-inc' );
    
    // SCRIPTS
    wp_enqueue_script( 'magnific-popup', plugin_dir_url( __FILE__ ) . 'js/jquery.magnific-popup.min.js', array(), '1.1.0', true );
    wp_enqueue_script( 'jquery-touchswipe', plugin_dir_url( __FILE__ ) . 'js/jquery.touchSwipe.min.js', array(), '1.6.18', true );
    wp_enqueue_script( 'tslr-custom', plugin_dir_url( __FILE__ ) . 'js/tslr-custom.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'themeslr_framework' );




/**

||-> Function: themeslr_enqueue_admin_scripts()

*/
function themeslr_enqueue_admin_scripts( $hook ) {
    // CSS
    wp_register_style( 'css-tslr-custom',  plugin_dir_url( __FILE__ ) . 'css/tslr-custom.css' );
    wp_enqueue_style( 'css-tslr-custom' );
    wp_register_style( 'css-fontawesome-icons',  plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css' );
    wp_enqueue_style( 'css-fontawesome-icons' );
    wp_register_style( 'css-simple-line-icons',  plugin_dir_url( __FILE__ ) . 'css/simple-line-icons.css' );
    wp_enqueue_style( 'css-simple-line-icons' );

}
add_action('admin_enqueue_scripts', 'themeslr_enqueue_admin_scripts');



function themeslr_excerpt_limit($string, $word_limit) {
    $words = explode(' ', $string, ($word_limit + 1));
    if(count($words) > $word_limit) {
        array_pop($words);
    }
    return implode(' ', $words);
}

