<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @see       https://homescriptone.com
 * @since      1.0.0
 *
 * @package doko
 */
namespace HS\Doko;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class Hs_Doko_Admin {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     *
     * @var string the ID of this plugin
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     *
     * @var string the current version of this plugin
     */
    private $version;

    /**
     * The default args of the cpt.
     *
     * @param string $menu_icon Menu icon name.
     *
     * @return mixed|void
     */
    private function get_default_cpt_args( $menu_icon = false, $args = array() ) {
        /**
         * Filter the default cpt args.
         *
         * @since 1.0.0
         */
        $args = wp_parse_args( $args, apply_filters( 'doko_cpt_args', array(
            'hierarchical'        => false,
            'supports'            => array('title'),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'has_archive'         => false,
            'query_var'           => false,
            'can_export'          => true,
            'menu_icon'           => $menu_icon,
        ) ) );
        return $args;
    }

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name the name of this plugin.
     * @param string $version     the version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        if ( WP_DEBUG ) {
            $this->version = rand( 0, 500 );
        } else {
            $this->version = $version;
        }
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        /*
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Hs_Doko_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Hs_Doko_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        global $post_type, $parent_file;
        $allowed_slugs = hs_dk_get_allowed_slug();
        if ( in_array( $post_type, $allowed_slugs['post_type'], true ) || in_array( $parent_file, $allowed_slugs['page'], true ) ) {
            wp_enqueue_style(
                'doko-select2',
                plugin_dir_url( __FILE__ ) . 'css/doko-select2.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'css/hs-doko-admin.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style( 'jquery-ui' );
            wp_enqueue_style( 'doko-select2' );
            wp_enqueue_style(
                'hs-corecss',
                plugin_dir_url( __FILE__ ) . 'css/core.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                'hs-iso-corecss',
                plugin_dir_url( __FILE__ ) . 'css/isolated-block-editor.css',
                array(),
                $this->version,
                'all'
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        /*
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Hs_Doko_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Hs_Doko_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        global $post_type, $parent_file;
        $allowed_slugs = hs_dk_get_allowed_slug();
        if ( in_array( $post_type, $allowed_slugs['post_type'], true ) || in_array( $parent_file, $allowed_slugs['page'], true ) ) {
            wp_enqueue_script(
                'hs-select2',
                plugin_dir_url( __FILE__ ) . 'js/hs-doko-select2.js',
                array('jquery'),
                $this->version,
                false
            );
            wp_enqueue_script(
                'hs-blockUI-js',
                plugin_dir_url( __FILE__ ) . 'js/hs-doko-blockUI.js',
                array('jquery'),
                $this->version,
                false
            );
            wp_enqueue_script(
                'hs-isolated-editor-js',
                plugin_dir_url( __FILE__ ) . 'js/isolated-block-editor.js',
                array(
                    'react',
                    'wc-components',
                    'wp-components',
                    'wp-element',
                    'wp-hooks',
                    'wp-i18n',
                    'wp-data'
                ),
                $this->version,
                false
            );
            wp_enqueue_script(
                'hs-serializeJSON-js',
                plugin_dir_url( __FILE__ ) . 'js/hs-doko-serializejson.js',
                array('jquery'),
                $this->version,
                false
            );
            wp_enqueue_script( 'jquery-tiptip' );
            wp_enqueue_media();
            $asset_lists = array(
                'jquery',
                'hs-select2',
                // 'select2',
                'jquery-ui-core',
                'jquery-ui-accordion',
                'jquery-ui-sortable',
                'jquery-ui-widget',
                'wp-hooks',
                'jquery-tiptip',
            );
            wp_enqueue_script(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'js/hs-doko-admin.js',
                $asset_lists,
                $this->version,
                false
            );
            if ( 'doko-bundles' == $post_type ) {
                wp_enqueue_script(
                    $this->plugin_name . '-bundle',
                    plugin_dir_url( __FILE__ ) . 'js/hs-doko-bundle.js',
                    $asset_lists,
                    $this->version,
                    false
                );
                wp_enqueue_script(
                    $this->plugin_name . '-bg-admin',
                    plugin_dir_url( __FILE__ ) . 'js/hs-doko-bg-admin.js',
                    $asset_lists,
                    $this->version,
                    false
                );
                // include codemirror
                $cm_settings = array();
                $cm_settings['codeEditor'] = wp_enqueue_code_editor( array(
                    'type' => 'text/css',
                ) );
                wp_localize_script( 'jquery', 'cm_settings', $cm_settings );
                wp_enqueue_script( 'wp-theme-plugin-editor' );
                wp_enqueue_style( 'wp-codemirror' );
                wp_register_style(
                    'woocommerce_admin_styles',
                    WC()->plugin_url() . '/assets/css/admin.css',
                    array(),
                    $this->version
                );
                wp_enqueue_style( 'woocommerce_admin_styles' );
                wp_enqueue_style( 'jquery-ui-style' );
            }
            if ( 'doko-bundles-rules' == $post_type ) {
                wp_enqueue_script(
                    $this->plugin_name . '-bundle-rules',
                    plugin_dir_url( __FILE__ ) . 'js/hs-doko-rules.js',
                    $asset_lists,
                    $this->version,
                    false
                );
            }
            $saved_data = $this->get_data();
            /**
             * Filter the data to be passed to the js file
             *
             * @since 1.0.0
             */
            $data = apply_filters( 'hs_dk_admin_js_object_data', array(
                'pick_product_message'  => esc_html__( 'Enter your product(s) name(s)', 'doko-bundle-builder' ),
                'pick_category_message' => esc_html__( 'Enter your categorie(s) name(s)', 'doko-bundle-builder' ),
            ) );
            if ( $saved_data ) {
                $data_to_unset = $this->get_data_to_unset();
                foreach ( $data_to_unset as $value ) {
                    unset($saved_data[$value]);
                }
                $data['ids'] = array_map( function ( $data ) {
                    return array(
                        'id'   => $data,
                        'args' => $this->generate_content_args( $data ),
                    );
                }, array_keys( $saved_data ) );
            }
            wp_add_inline_script( $this->plugin_name, ' var doko_object=' . wp_json_encode( $data ) . ';' );
        }
    }

    /**
     * Add menu to the plugin.
     *
     * @return void
     */
    public function add_menus() {
        add_submenu_page(
            'edit.php?post_type=doko-bundles',
            esc_html__( 'Documentation', 'doko-bundle-builder' ),
            esc_html__( 'Documentation', 'doko-bundle-builder' ),
            'manage_woocommerce',
            'doko-docs',
            array($this, 'redirect_to_docs')
        );
    }

    public function save_settings() {
    }

    public function redirect_to_docs() {
        wp_redirect( 'https://docs.homescriptone.com/wordpress-plugins/doko-builder-the-ultimate-dynamic-bundle-builder-for-woocommerce?utm_source=' . get_site_url() . '&utm_medium=WC' );
        exit;
    }

    /**
     * Provide options for the select that will be initialized.
     *
     * @param mixed $package_mode Package mode.
     * @param mixed $options List of options.
     *
     * @return array
     */
    private function get_dynamic_select_options( $package_mode, $id, $options = array() ) {
        /**
         * Filter the options for the select that will be initialized.
         *
         * @since 1.0.0
         */
        return apply_filters( 'hs_dk_package_select_options', array(
            'type'              => 'select',
            'required'          => true,
            'return'            => true,
            'options'           => $options,
            'custom_attributes' => array(
                'multiple'          => true,
                'data-package-mode' => $package_mode,
            ),
            'selected'          => $options,
            'id'                => $id,
        ) );
    }

    /**
     * Provide package selection options.
     *
     * @return array
     */
    private function get_package_selection_options( $id = '' ) {
        $id = uniqid();
        $package_selection_options = array(
            'type'   => 'radio',
            'return' => true,
            'class'  => array('doko-card-selection-mode'),
            'id'     => $id,
        );
        $modes = array(
            'options' => array(
                'products'   => 'Individual Products',
                'categories' => 'Categories',
            ),
        );
        /**
         * Filter the package selection options.
         *
         * @since 1.0.0
         */
        $package_selection_options += apply_filters( 'hs_dk_package_options', $modes );
        return $package_selection_options;
    }

    /**
     * Add new custom post type to the dashboard.
     *
     * @return void
     */
    public function define_custom_post_type() {
        $box_labels = array(
            'name'               => esc_html__( 'Bundles', 'doko-bundle-builder' ),
            'singular_name'      => esc_html__( 'Bundle', 'doko-bundle-builder' ),
            'add_new'            => esc_html__( 'New Bundle', 'doko-bundle-builder' ),
            'add_new_item'       => esc_html__( 'New Bundle', 'doko-bundle-builder' ),
            'edit_item'          => esc_html__( 'Edit Bundle', 'doko-bundle-builder' ),
            'new_item'           => esc_html__( 'New Bundle', 'doko-bundle-builder' ),
            'view_item'          => esc_html__( 'View Bundle', 'doko-bundle-builder' ),
            'search_items'       => esc_html__( 'Search Bundle', 'doko-bundle-builder' ),
            'not_found'          => esc_html__( 'No Bundle found', 'doko-bundle-builder' ),
            'not_found_in_trash' => esc_html__( 'No Bundle in the trash', 'doko-bundle-builder' ),
            'menu_name'          => esc_html__( 'Bundles', 'doko-bundle-builder' ),
        );
        // initialize the Box CPT metabox.
        $box_args = $this->get_default_cpt_args( 'dashicons-screenoptions' );
        $box_args['labels'] = $box_labels;
        $box_args['description'] = esc_html__( 'Bundle', 'doko-bundle-builder' );
        register_post_type( 'doko-bundles', $box_args );
        /**
         * Fires after the custom post type is added.
         *
         * @since 1.0.0
         */
        do_action( 'hs_doko_add_new_cpt' );
    }

    /**
     * Provide data of saved package.
     */
    private function get_data( $post_id = false ) {
        if ( !$post_id ) {
            global $post;
            if ( is_object( $post ) ) {
                $post_id = $post->ID;
            } else {
                return false;
            }
        }
        return get_post_meta( $post_id, 'doko', true );
    }

    /**
     * Handle meta boxes.
     */
    public function add_meta_boxes() {
        add_meta_box(
            'doko-box-settings',
            esc_html__( 'First Screen setup', 'doko-bundle-builder' ),
            array($this, 'get_box_settings'),
            'doko-bundles',
            'normal',
            'high'
        );
        add_meta_box(
            'doko-bundle-screens',
            'Bundle Screens',
            array($this, 'display_dynamic_bundle'),
            'doko-bundles',
            'normal'
        );
        add_meta_box(
            'doko-bundle-options',
            'Bundle Screens Options',
            array($this, 'get_navigation_customisation'),
            'doko-bundles',
            'normal'
        );
        do_action( 'doko_add_metaboxes' );
    }

    public function get_dynamic_rules() {
    }

    /**
     *  Provide the box metabox display.
     *
     * @return void
     */
    public function get_box_settings() {
        $data = $this->get_data();
        echo wp_kses_post( esc_html__( 'This refers to the first navigation screen of the bundle builder : ', 'doko-bundle-builder' ) );
        formulus_format_fields( '<br/><br/>' );
        /**
         * Filter the table arguments.
         *
         * @since 1.0.0
         */
        $table_args = apply_filters( 'doko_box_table_fields', array(
            'box-description'     => array(
                'label'       => esc_html__( 'Title', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label',
                'tr_class'    => 'doko-box-description',
                'description' => wp_sprintf( 
                    /* translators: %s: strong tag */
                    esc_html__( 'Title to show on top of the bundle selector.%1$1sEg:%2$2s CHOOSE YOUR BOX COLOR ', 'doko-bundle-builder' ),
                    '<br/> <strong>',
                    '</strong>'
                 ),
                'content'     => '<textarea id="doko-box-description" name="doko[box-description]" >' . wp_kses_post( ( isset( $data['box-description'] ) ? $data['box-description'] : esc_html__( 'CHOOSE YOUR BOX COLOR', 'doko-bundle-builder' ) ) ) . '</textarea><input type="hidden" name="doko[bundle-title]" />',
            ),
            'box-selection-mode'  => array(
                'label'       => esc_html__( 'Display according to :', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label',
                'content'     => formulus_input_fields( 'doko[box-selection-mode]', $this->get_package_selection_options() + array(
                    'custom_attributes' => array(
                        'data-tr-products-name'   => 'doko-box-products-mode',
                        'data-tr-categories-name' => 'doko-box-categories-mode',
                    ),
                ), wp_kses_post( ( isset( $data['box-selection-mode'] ) ? $data['box-selection-mode'] : 'products' ) ) ),
                'tr_class'    => 'doko-box-selection-mode',
            ),
            'box-products-mode[]' => array(
                'label'       => esc_html__( 'Products to display :', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label',
                'description' => esc_html__( 'Define the products, your customers are allowed to pick to fill this step in the bundle.', 'doko-bundle-builder' ),
                'content'     => formulus_input_fields( 'doko[box-products][]', $this->get_dynamic_select_options( 'products', 'box-products', hs_dk_get_data( ( isset( $data['box-products'] ) ? $data['box-products'] : array() ), true ) ) ),
                'tr_class'    => 'doko-box-products-mode',
            ),
            'box-categories-mode' => array(
                'label'       => esc_html__( 'Categories to display :', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label',
                'description' => esc_html__( 'Define the products categories, your customers are allowed to pick to fill this step in the bundle.', 'doko-bundle-builder' ),
                'content'     => formulus_input_fields( 'doko[box-categories][]', $this->get_dynamic_select_options( 'categories', 'box-categories', hs_dk_get_data( ( isset( $data['box-categories'] ) ? $data['box-categories'] : array() ) ) ) ),
                'tr_class'    => 'doko-box-categories-mode',
            ),
        ) );
        $table_args += array(
            'enable-screen-redirect'   => array(
                'label'       => esc_html__( 'Enable auto redirect to next page :', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label',
                'description' => esc_html__( 'Redirect automatically to next step, if customers add a product to the box.', 'doko-bundle-builder' ),
                'content'     => formulus_input_fields( 'doko[enable-screen-redirect]', array(
                    'type'    => 'select',
                    'options' => array(
                        'yes' => esc_html__( 'Yes', 'doko-bundle-builder' ),
                        'no'  => esc_html__( 'No', 'doko-bundle-builder' ),
                    ),
                ), ( isset( $data['enable-screen-redirect'] ) ? $data['enable-screen-redirect'] : 'yes' ) ),
            ),
            'no-products-message'      => array(
                'label'       => esc_html__( 'No products message :', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label',
                'description' => esc_html__( 'Message to display, if no products is added to the box.', 'doko-bundle-builder' ),
                'content'     => formulus_input_fields( 'doko[no-products-message]', array(
                    'type' => 'textarea',
                ), ( isset( $data['no-products-message'] ) ? $data['no-products-message'] : '' ) ),
                'tr_class'    => 'doko_first_screen_no_products',
            ),
            'enable-bottom-navigation' => array(
                'label'       => esc_html__( 'Show/Hide the bundle navigation bar :', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label',
                'description' => esc_html__( 'Whether the bundle navigation bar, should always be shown once the page is loaded. By default, it will be showned only if customers add something to the box.', 'doko-bundle-builder' ),
                'content'     => formulus_input_fields( 'doko[enable-bottom-navigation]', array(
                    'type'     => 'select',
                    'options'  => array(
                        'yes' => __( 'Yes', 'doko-bundle-builder' ),
                        'no'  => __( 'No', 'doko-bundle-builder' ),
                    ),
                    'tr_class' => 'doko-enable-bottom-navigation',
                    'key'      => 'enable-bottom-navigation',
                ), ( isset( $data['enable-bottom-navigation'] ) ? $data['enable-bottom-navigation'] : 'no' ) ),
                'tr_class'    => 'doko_first_screen_no_products',
            ),
        );
        formulus_input_table( 'doko-box-settings', $table_args );
        ?>
		<input type="hidden" name="doko_nonce" value="<?php 
        echo esc_html( wp_create_nonce( 'doko_nonce' ) );
        ?>" />
		<?php 
    }

    public function get_navigation_customisation() {
        $data = $this->get_data();
        $args = array(
            'enable-child-product-display' => array(
                'key'     => 'enable-child-product-display',
                'label'   => esc_html__( 'Display the bundle contents into the cart and checkout page', 'doko-bundle-builder' ),
                'name'    => 'doko[enable-child-product-display]',
                'type'    => 'select',
                'options' => array(
                    'yes' => __( 'Yes', 'doko-bundle-builder' ),
                    'no'  => __( 'No', 'doko-bundle-builder' ),
                ),
                'default' => ( isset( $data['enable-child-product-display'] ) ? $data['enable-child-product-display'] : 'no' ),
            ),
            'last-step-title'              => array(
                'key'     => 'last-step-title',
                'label'   => esc_html__( 'Title to use on the latest step page', 'doko-bundle-builder' ),
                'name'    => 'doko[last-step-title]',
                'type'    => 'textarea',
                'default' => ( isset( $data['last-step-title'] ) ? $data['last-step-title'] : 'BUILDING YOUR BUNDLE BOX' ),
            ),
            'last-step-description'        => array(
                'key'     => 'last-step-description',
                'type'    => 'textarea',
                'label'   => esc_html__( 'Description to use on the latest step page after the last step title section', 'doko-bundle-builder' ),
                'name'    => 'doko[last-step-description]',
                'default' => ( isset( $data['last-step-description'] ) ? $data['last-step-description'] : 'Please wait a moment while we prepare your custom bundle box' ),
            ),
            'enable-add-to-box-button'     => array(
                'key'         => 'enable-add-to-box-button',
                'type'        => 'select',
                'options'     => array(
                    'yes' => __( 'Yes', 'doko-bundle-builder' ),
                    'no'  => __( 'No', 'doko-bundle-builder' ),
                ),
                'label'       => esc_html__( 'Enable/Disable additional add to box button below the product card', 'doko-bundle-builder' ),
                'description' => esc_html__( "In some WooCommerce themes, the Add to Cart button is not displayed on bundle pages, to reduce the bounce rate on the bundle page, enable this option to fix it, by default it's set to no.", 'doko-bundle-builder' ),
                'name'        => 'doko[enable-add-to-box-button]',
                'default'     => ( isset( $data['enable-add-to-box-button'] ) ? $data['enable-add-to-box-button'] : 'yes' ),
            ),
        );
        formulus_input_table( 'doko-options', array_map( function ( $data ) {
            return current( array(
                $data['key'] => array(
                    'label'       => $data['label'],
                    'description' => ( isset( $data['description'] ) ? $data['description'] : '' ),
                    'label_class' => 'doko-table-label',
                    'tr_class'    => ( isset( $data['tr_class'] ) ? $data['tr_class'] : '' ),
                    'content'     => formulus_input_fields( $data['name'], array(
                        'type'    => $data['type'],
                        'options' => ( isset( $data['options'] ) ? $data['options'] : array() ),
                        'return'  => true,
                    ), $data['default'] ),
                ),
            ) );
        }, $args ) );
    }

    public static function get_data_to_unset() {
        /**
         * Filter the data to unset from the database
         *
         * @since 1.0.0
         */
        return apply_filters( 'doko_data_to_unset', array(
            'enable-child-product-display',
            'last-step-title',
            'last-step-description',
            'enable-discount-display',
            'box-description',
            'bundle-title',
            'box-selection-mode',
            'box-products',
            'box-categories',
            'box-numbers-of-products',
            'enable-screen-redirect',
            'no-products-message',
            'enable-add-to-box-button',
            'enable-pagination',
            'enable-gift-message',
            'qty-position-field',
            'enable-qty-input',
            'enable-first-screen',
            'box-tags',
            'steps-background-image',
            'custom-css',
            'enable-product-description',
            'enable-bottom-navigation',
            'use-bundle-name-as-product-name'
        ) );
    }

    public function display_dynamic_bundle() {
        ?>
				<span class="product-data-wrapper type_box ">
					<button class="button button-primary hs-add-screen" id="add-btn-off"><?php 
        esc_html_e( 'Add Screen ', 'doko-bundle-builder' );
        ?></button>
					
					<span class="button expand-close doko-btn-expand">
						<a href class="expand_all"><?php 
        esc_html_e( 'Expand', 'doko-bundle-builder' );
        ?></a> / <a href class="close_all"><?php 
        esc_html_e( 'Close', 'doko-bundle-builder' );
        ?></a>
					</span>
				</span>
				<div class="woocommerce_variations wc-metaboxes">
					<?php 
        $data = $this->get_data();
        if ( $data ) {
            $data_to_unset = $this->get_data_to_unset();
            foreach ( $data_to_unset as $value ) {
                if ( isset( $data[$value] ) ) {
                    unset($data[$value]);
                }
            }
            foreach ( $data as $bundle_key => $bundle_details ) {
                $args = $this->generate_content_args( $bundle_key );
                if ( !is_array( $bundle_details ) ) {
                    continue;
                }
                formulus_format_fields( $this->generate_screen_configuration_content( $bundle_key, $args, $bundle_details ) );
            }
        }
        ?>
				</div>

			<input type="hidden" name="doko_nonce" value="<?php 
        echo esc_html( wp_create_nonce( 'doko_nonce' ) );
        ?>" />
		<?php 
    }

    private function generate_bundle_fields( $args = array(), $saved_data = false ) {
        extract( $args );
        // Generate HTML for the screen selection part.
        $html = '<ul>';
        foreach ( hs_dk_get_disposition() as $screen ) {
            $html .= "<li data-slug='" . $screen['slug'] . "' ><a href='" . $screen['preview_img_link'] . "' target='_blank'> " . $screen['title'] . ' </a> </li>';
        }
        $html .= '</ul>';
        // Generate HTML for the title selection part.
        $title_html = '<ul>';
        foreach ( hs_dk_get_disposition( true ) as $screen ) {
            $title_html .= "<li data-slug='" . $screen['slug'] . "' ><a href='" . $screen['preview_img_link'] . "' target='_blank'> " . $screen['title'] . ' </a> </li>';
        }
        $title_html .= '</ul>';
        $bundle_title = '';
        $products = array();
        $categories = array();
        $enable_pagination = 'no';
        $nb_of_products_per_page = '1';
        $enable_gift_message = 'no';
        $display_bundle_viewer = 'yes';
        $use_qty_input = $qty_position_field = '';
        $tags = array();
        $title_gift_message = esc_html__( 'Add a gift message', 'doko-bundle-builder' );
        $desc_gift_message = esc_html__( 'To include different messages for different recipients, please contact our support.', 'doko-bundle-builder' );
        if ( $saved_data ) {
            $screen_name = $saved_data['screen-name'];
            $screen_disposition = $saved_data['screen-disposition'];
            $display_bundle_title = ( isset( $saved_data['display-bundle-title'] ) ? $saved_data['display-bundle-title'] : 'no' );
            $bundle_title = $saved_data['bundle-title'];
            $options = ( isset( $saved_data['options'] ) ? $saved_data['options'] : 'products' );
            $products = ( isset( $saved_data['products'] ) ? $saved_data['products'] : array() );
            $categories = ( isset( $saved_data['categories'] ) ? $saved_data['categories'] : array() );
            $tags = ( isset( $saved_data['tags'] ) ? array_values( $saved_data['tags'] ) : array() );
            $products_list = hs_dk_get_data( $products, true );
            $categories_list = hs_dk_get_data( $categories, false );
        }
        $screens_fields = array(
            'screen-name' => array(
                'label'       => esc_html__( 'Screen Name :', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label',
                'description' => esc_html__( 'Define a slug to easily identify this screen', 'doko-bundle-builder' ),
                'content'     => formulus_input_fields( "doko[{$select_id}][screen-name]", array(
                    'type'   => 'text',
                    'return' => true,
                ), $screen_name ) . '<span class="doko-bundle-screen-name-tip"></span>',
            ),
        );
        $option_list = array(
            'bundle-products' => esc_html__( 'Bundle Products', 'doko-bundle-builder' ),
            'upgrade-cc'      => esc_html__( 'Bundle Cart Content (PRO Version)', 'doko-bundle-builder' ),
            'upgrade-card'    => esc_html__( 'Bundle Card Page (PRO Version)', 'doko-bundle-builder' ),
        );
        $screenoptions = array('upgrade-cc', 'upgrade-card');
        $oscreens_fields = array(
            'choose-bundle-screen-disposition' => array(
                'label'       => esc_html__( 'Choose Bundle Screen disposition :', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label',
                'description' => esc_html__( 'Define the screen disposition, it supports the following disposition : ', 'doko-bundle-builder' ) . wp_kses_post( $html ),
                'content'     => formulus_input_fields( "doko[{$select_id}][screen-disposition]", array(
                    'type'             => 'select',
                    'options'          => apply_filters( 'doko_bundle_screen_disposition', $option_list ),
                    'disabled_options' => $screenoptions,
                    'return'           => true,
                ), ( isset( $saved_data['screen-disposition'] ) ? $saved_data['screen-disposition'] : 'bundle-products' ) ),
                'tr_class'    => 'choose-bundle-screen-disposition',
            ),
            'display-bundle-title'             => array(
                'label'       => esc_html__( 'Show / Hide Bundle Screen Title :', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label',
                'description' => esc_html__( 'Whether it displays a screen title or not :', 'doko-bundle-builder' ),
                'content'     => formulus_input_fields(
                    "doko[{$select_id}][display-bundle-title]",
                    array(
                        'type'    => 'radio',
                        'id'      => $enable_box_details_description_id,
                        'options' => array(
                            'yes' => esc_html__( 'Show', 'doko-bundle-builder' ),
                            'no'  => esc_html__( 'Hide', 'doko-bundle-builder' ),
                        ),
                        'return'  => true,
                    ),
                    ( isset( $display_bundle_title ) ? $display_bundle_title : esc_html__( 'no', 'doko-bundle-builder' ) ),
                    $enable_box_details_description_id
                ),
                'tr_class'    => 'display-bundle-title',
            ),
            'bundle-screen-title'              => array(
                'label'       => esc_html__( 'Bundle Screen Title :', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label',
                'tr_class'    => 'doko-tr-section-bundle-title-' . $select_id,
                'description' => esc_html__( 'Define the bundle screen title. It can be a descriptive text highlighting what your customers must do on this page :', 'doko-bundle-builder' ) . wp_kses_post( $title_html ),
                'content'     => '<textarea id="' . $box_description_editor_id . '" name="doko[' . $select_id . '][bundle-title]" >' . (( isset( $bundle_title ) ? $bundle_title : esc_html__( 'CHOOSE YOUR BOX COLOR', 'doko-bundle-builder' ) )) . '</textarea>',
            ),
            $select_name                       => array(
                'label'       => esc_html__( 'Show Products on this screen based on :', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label',
                'content'     => formulus_input_fields( "doko[{$select_id}][options]", $this->get_package_selection_options( $select_id ), ( isset( $options ) ? $options : 'products' ) ),
                'tr_class'    => 'doko-box-selection-mode',
            ),
            $pdts_select_name . '[]'           => array(
                'label'       => esc_html__( 'Products to display :', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label ',
                'tr_class'    => 'doko-tr-section-prod-' . $select_id,
                'description' => esc_html__( 'Define the products, your customers are allowed to pick to fill this step in the bundle.', 'doko-bundle-builder' ),
                'content'     => formulus_input_fields( "doko[{$select_id}][products][]", $this->get_dynamic_select_options( 'products', $pdts_select_id, ( isset( $products_list ) ? $products_list : array() ) ) ),
            ),
            $ctgs_select_name . '[]'           => array(
                'label'       => esc_html__( 'Categories to display :', 'doko-bundle-builder' ),
                'label_class' => 'doko-table-label ',
                'tr_class'    => 'doko-tr-section-cat-' . $select_id,
                'description' => esc_html__( 'Define the products categories, your customers are allowed to pick to fill this step in the bundle.', 'doko-bundle-builder' ),
                'content'     => formulus_input_fields( "doko[{$select_id}][categories][]", $this->get_dynamic_select_options( 'categories', $ctgs_select_id, ( isset( $categories_list ) ? $categories_list : array() ) ) ),
            ),
        );
        $screens_fields = array_merge( $screens_fields, $oscreens_fields );
        /**
         * Filter the screen data.
         *
         * @since 1.0.0
         */
        $table_args = apply_filters(
            'doko_dynamic_screen_table_fields',
            $screens_fields,
            $screen_name,
            $select_id,
            $saved_data
        );
        return formulus_input_table( $section_name, $table_args, true );
    }

    /**
     * Generate shortcode to use in pages.
     *
     * @param mixed $post WP Post Object.
     */
    public function display_package_shortcodes( $post ) {
        $post_id = $post->ID;
        global $post_type;
        if ( 'doko-bundles' === $post_type ) {
            if ( 'auto-draft' === $post->post_status ) {
                return;
            }
            formulus_format_fields( $this->generate_bundle_shortcode( $post_id ) );
        }
    }

    private function generate_bundle_shortcode( $bundle_id ) {
        ob_start();
        $shortcode = sprintf( '[doko-bundles id="%1$d"]', $bundle_id );
        ?>
			<div class="inside">
				<p class="description">
				<label for="doko-bundles-shortcode"><?php 
        esc_attr_e( 'Copy this shortcode and paste it into a new page you\'ve created :', 'doko-bundle-builder' );
        ?></label>
				<span class="shortcode wp-ui-highlight">
					<input type="text" id="doko-bundles-shortcode" onfocus="this.select();" readonly="readonly" class="large-text" value='<?php 
        echo wp_kses_post( $shortcode );
        ?>'>
				</span>
				</p>
			</div>
		<?php 
        return ob_get_clean();
    }

    /**
     * Query WC by ajax.
     */
    public function query_wc() {
        $query = filter_input( INPUT_GET, 'q' );
        $operation_type = filter_input( INPUT_GET, 'operation_type' );
        $data_found = array();
        if ( 'products' === $operation_type ) {
            $data = hs_dk_get_wc_products( $query );
        } elseif ( 'categories' === $operation_type ) {
            $data = hs_dk_get_wc_categories( $query );
        }
        if ( !empty( $data ) ) {
            foreach ( $data as $data_id => $data_name ) {
                $data_found[] = array($data_id, $data_name);
            }
        }
        echo wp_json_encode( $data_found );
        wp_die();
    }

    /**
     * Save package.
     *
     * @param int $post_id Post ID.
     */
    public function save_package( $post_id ) {
        $meta_key = 'doko';
        if ( isset( $_POST[$meta_key] ) ) {
            $posted_data = filter_input_array( INPUT_POST );
            $posted_data = $posted_data['doko'];
            update_post_meta( $post_id, $meta_key, $posted_data );
        }
    }

    /**
     * Change CPT title.
     *
     * @param mixed $title Title.
     *
     * @return mixed
     */
    public function change_cpt_title( $title ) {
        global $typenow;
        if ( 'doko-bundles' === $typenow ) {
            $title = esc_html__( 'Bundle name', 'doko-bundle-builder' );
        }
        return $title;
    }

    private function generate_content_args( $data_id ) {
        return array(
            'elem_id'                             => $data_id,
            'screen_id'                           => $data_id,
            'screen_name'                         => $data_id,
            'select_id'                           => $data_id,
            'select_name'                         => $data_id,
            'mb_fieldname'                        => "doko-box-selection-{$data_id}",
            'title_editor_name'                   => 'doko-box-title-name-' . $data_id,
            'title_editor_id'                     => 'doko-box-title-id-' . $data_id,
            'bypass_screen_id'                    => 'doko-box-bypass-screen-id-' . $data_id,
            'bypass_screen_name'                  => 'doko-box-bypass-screen-name-' . $data_id,
            'limit_products_id'                   => 'doko-box-limit-products-id-' . $data_id,
            'limit_products_name'                 => 'doko-box-limit-products-name-' . $data_id,
            'box_description_editor_name'         => 'doko-box-description-editor-name-' . $data_id,
            'box_description_editor_id'           => 'doko-box-description-editor-id-' . $data_id,
            'pdts_selection_id'                   => 'doko-box-pdts-selection-id-' . $data_id,
            'pdts_selection_name'                 => 'doko-box-pdts-selection-name-' . $data_id,
            'pdts_select_name'                    => 'doko-box-pdts-select-name-' . $data_id,
            'pdts_select_id'                      => 'doko-box-pdts-select-id-' . $data_id,
            'ctgs_select_name'                    => 'doko-ctgs-select-name-' . $data_id,
            'ctgs_select_id'                      => 'doko-ctgs-select-id-' . $data_id,
            'enable_form_id'                      => 'doko-enable-form-id-' . $data_id,
            'enable_form_name'                    => 'doko-enable-form-name-' . $data_id,
            'section_name'                        => "doko-box-selection-{$data_id}",
            'enable_box_details_description_name' => "doko-box-details-description-name-{$data_id}",
            'enable_box_details_description_id'   => "doko-box-details-description-id-{$data_id}",
            'enable_bundle_viewer'                => "doko-box-enable-bundle-viewer-{$data_id}",
        );
    }

    public function get_dynamic_contents() {
        $data_id = uniqid();
        $args = $this->generate_content_args( $data_id );
        $data = array(
            'html' => $this->generate_screen_configuration_content( $data_id, $args ),
            'args' => $args,
        );
        echo wp_json_encode( $data );
        wp_die();
    }

    private function generate_screen_configuration_content( $data_id, $args, $saved_data = false ) {
        $content = $this->generate_bundle_fields( $args, $saved_data );
        $screen_name = ( isset( $saved_data['screen-name'] ) ? $saved_data['screen-name'] : '#' . $data_id );
        $variation_id = $data_id;
        ob_start();
        ?>
			<div class="woocommerce_variation wc-metabox doko-metabox variation-needs-update doko-dynamic-bundle closed" style="border-radius: 3px; background: #fff; border: 1px solid black; margin: 0 !important;">
				<h3 class="doko-collapse">
					<a href="#delete" class="remove_variation delete" style="float: right;" rel="<?php 
        echo esc_attr( $variation_id );
        ?>"><?php 
        esc_html_e( 'Remove', 'doko-bundle-builder' );
        ?></a>
					<div class="tips sort" data-tip="<?php 
        esc_attr_e( 'Drag and drop, or click to set admin variation order', 'doko-bundle-builder' );
        ?>"></div>
					<div class="handlediv" style="width: 27px; float: right;" aria-label="<?php 
        esc_attr_e( 'Click to toggle', 'doko-bundle-builder' );
        ?>"></div>
					<strong><span class="doko-section-<?php 
        echo esc_attr( $data_id );
        ?>"> <?php 
        echo wp_kses_post( $screen_name );
        ?>  </span></strong>

					<input type="hidden" class="variable_post_id"  />
					<input type="hidden" class="variation_menu_order"  />
				</h3>
				<div class="woocommerce_variable_attributes wc-metabox-content doko-metabox-content" style="display: none;">
					<div class="data">
						<?php 
        formulus_format_fields( $content );
        ?>
					</div>
				</div>
			</div>
		<?php 
        return ob_get_clean();
    }

    public function get_columns_values( $column_name, $id ) {
        if ( 'bundle_shortcodes' === $column_name ) {
            formulus_format_fields( $this->generate_bundle_shortcode( $id ) );
        }
        /**
         * Fires after the column value is generated.
         *
         * @since 1.0.0
         */
        do_action( 'doko_bundles_screen_columns_content', $column_name, $id );
    }

    public function get_columns( $defaults ) {
        $defaults['bundle_shortcodes'] = esc_html__( 'Bundle Shortcodes', 'doko-bundle-builder' );
        /**
         * Filters the columns of the bundles screen.
         *
         * @since 1.0.0
         */
        return apply_filters( 'doko_bundles_screen_columns', $defaults );
    }

}
