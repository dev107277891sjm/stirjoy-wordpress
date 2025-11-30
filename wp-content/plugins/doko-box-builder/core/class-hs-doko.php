<?php

namespace HS\Doko;

/**
 * The file that defines the core plugin class
 *
 * A class definition that core attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://homescriptone.com
 * @since      1.0.0
 *
 * @package    Hs_Doko
 * @subpackage Hs_Doko/core
 */
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Hs_Doko
 * @subpackage Hs_Doko/core
 */
class Hs_Doko {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @var      Hs_Doko_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'DOKO_VERSION' ) ) {
            $this->version = DOKO_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'doko-bundle-builder';
        $this->load_dependencies();
        $this->set_locale();
        if ( is_admin() ) {
            $this->define_admin_hooks();
        }
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Hs_Doko_Loader. Orchestrates the hooks of the plugin.
     * - Hs_Doko_i18n. Defines internationalization functionality.
     * - Hs_Doko_Admin. Defines all hooks for the admin area.
     * - Hs_Doko_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( __DIR__ ) . 'core/class-hs-doko-loader.php';
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( __DIR__ ) . 'core/class-hs-doko-i18n.php';
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( __DIR__ ) . 'admin/class-hs-doko-admin.php';
        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( __DIR__ ) . 'public/class-hs-doko-public.php';
        /**
         * The class responsible of all fields defined in the project.
         */
        require_once plugin_dir_path( __DIR__ ) . 'core/homescriptone-formulus.php';
        require_once plugin_dir_path( __DIR__ ) . 'core/class-hs-doko-utils.php';
        $this->loader = new Hs_Doko_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Hs_Doko_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     */
    private function set_locale() {
        $plugin_i18n = new Hs_Doko_I18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_admin_hooks() {
        $plugin_admin = new Hs_Doko_Admin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menus' );
        $this->loader->add_action( 'init', $plugin_admin, 'define_custom_post_type' );
        $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_meta_boxes' );
        $this->loader->add_action( 'edit_form_after_title', $plugin_admin, 'display_package_shortcodes' );
        $this->loader->add_action( 'wp_ajax_hs_dk_query_wc', $plugin_admin, 'query_wc' );
        $this->loader->add_action( 'save_post_doko-bundles', $plugin_admin, 'save_package' );
        $this->loader->add_action( 'save_post_doko-bundles-rules', $plugin_admin, 'save_package' );
        $this->loader->add_filter( 'enter_title_here', $plugin_admin, 'change_cpt_title' );
        $this->loader->add_action( 'wp_ajax_doko_get_admin_screen', $plugin_admin, 'get_dynamic_contents' );
        $this->loader->add_action( 'admin_post', $plugin_admin, 'save_settings' );
        $this->loader->add_filter( 'manage_edit-doko-bundles_columns', $plugin_admin, 'get_columns' );
        $this->loader->add_action(
            'manage_doko-bundles_posts_custom_column',
            $plugin_admin,
            'get_columns_values',
            5,
            2
        );
        $this->loader->add_action( 'wp_ajax_doko_get_bundle_rule', $plugin_admin, 'get_dynamic_rules' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_public_hooks() {
        $plugin_public = new Hs_Doko_Public($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action(
            'wp_enqueue_scripts',
            $plugin_public,
            'enqueue_styles',
            20
        );
        $this->loader->add_action(
            'wp_enqueue_scripts',
            $plugin_public,
            'enqueue_scripts',
            20
        );
        $this->loader->add_action( 'init', $plugin_public, 'init_shortcodes' );
        $this->loader->add_action(
            'woocommerce_after_shop_loop_item_title',
            $plugin_public,
            'add_btn_elements',
            9999
        );
        $this->loader->add_filter(
            'wc_get_template_part',
            $plugin_public,
            'doko_override_woocommerce_template_part',
            10,
            3
        );
        $this->loader->add_filter(
            'woocommerce_locate_template',
            $plugin_public,
            'doko_override_woocommerce_template',
            10,
            3
        );
        $this->loader->add_action( 'wp_ajax_doko-get-box-display', $plugin_public, 'get_products_to_pick_display' );
        $this->loader->add_action( 'wp_ajax_nopriv_doko-get-box-display', $plugin_public, 'get_products_to_pick_display' );
        $this->loader->add_action( 'wp_ajax_doko-store-bundle-content', $plugin_public, 'store_bundle_content_in_session' );
        $this->loader->add_action( 'wp_ajax_nopriv_doko-store-bundle-content', $plugin_public, 'store_bundle_content_in_session' );
        $this->loader->add_action( 'wp_ajax_doko_wc_add_to_cart', $plugin_public, 'add_to_cart' );
        $this->loader->add_action( 'wp_ajax_nopriv_doko_wc_add_to_cart', $plugin_public, 'add_to_cart' );
        $this->loader->add_filter(
            'woocommerce_get_item_data',
            $plugin_public,
            'add_box_contents',
            10,
            2
        );
        $this->loader->add_action(
            'woocommerce_checkout_create_order_line_item',
            $plugin_public,
            'add_box_contents_order_line',
            10,
            3
        );
        $this->loader->add_action( 'woocommerce_before_calculate_totals', $plugin_public, 'set_box_total_price' );
        $this->loader->add_filter(
            'woocommerce_loop_add_to_cart_link',
            $plugin_public,
            'replace_default_btn_text',
            9999,
            3
        );
        $this->loader->add_filter(
            'woocommerce_loop_product_link',
            $plugin_public,
            'disable_product_link',
            9999
        );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Hs_Doko_Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version() {
        return $this->version;
    }

}
