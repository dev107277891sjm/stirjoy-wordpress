<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://homescriptone.com
 * @since      1.0.0
 *
 * @package    Hs_Doko
 * @subpackage Hs_Doko/public
 */
namespace HS\Doko;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Hs_Doko
 * @subpackage Hs_Doko/public
 */
class Hs_Doko_Public {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name       The name of the plugin.
     * @param      string $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        global $doko_bundle_id, $doko_package_data;
        $this->plugin_name = $plugin_name;
        if ( WP_DEBUG ) {
            $this->version = rand( 0, 500 );
        } else {
            $this->version = $version;
        }
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        /**
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
        $page_url_id = $this->get_page_id();
        $post_obj = get_post( $page_url_id );
        if ( is_object( $post_obj ) && !has_shortcode( $post_obj->post_content, 'doko-bundles' ) ) {
            return;
        }
        // desenqueue script + style.
        wp_dequeue_style( 'bootstrap' );
        wp_dequeue_style( 'bootstrap-rtl' );
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/hs-doko-public.css',
            array(),
            $this->version,
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name . '-modal',
            plugin_dir_url( __FILE__ ) . 'css/hs-doko-modal.css',
            array(),
            $this->version,
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name . '-fontwasome',
            plugin_dir_url( __FILE__ ) . 'css/all.min.css',
            array(),
            $this->version,
            'all'
        );
        wp_enqueue_style( 'jquery-ui' );
        wp_enqueue_style(
            'hs-doko-snackbar-css',
            plugin_dir_url( __FILE__ ) . 'css/hs-doko-snackbar.css',
            array(),
            $this->version,
            'all'
        );
        wp_enqueue_style(
            'hs-doko-card-css',
            plugin_dir_url( __FILE__ ) . 'css/hs-doko-card.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        /**
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
        $page_url_id = $this->get_page_id();
        $post_obj = get_post( $page_url_id );
        if ( is_object( $post_obj ) && !has_shortcode( $post_obj->post_content, 'doko-bundles' ) ) {
            return;
        }
        // desenqueue script + style.
        wp_dequeue_script( 'bootstrap' );
        wp_dequeue_script( 'bootstrap-rtl' );
        $term_file = "-min.js";
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $term_file = ".js";
        }
        wp_enqueue_script(
            $this->plugin_name . '-modal',
            plugin_dir_url( __FILE__ ) . "js/hs-doko-modal{$term_file}",
            array('jquery'),
            $this->version,
            false
        );
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . "js/hs-doko-public{$term_file}",
            array('jquery', 'wp-hooks'),
            $this->version,
            false
        );
        wp_enqueue_script(
            $this->plugin_name . '-utils',
            plugin_dir_url( __FILE__ ) . "js/hs-doko-utils{$term_file}",
            array('jquery', $this->plugin_name, 'wp-hooks'),
            $this->version,
            false
        );
        wp_enqueue_script(
            'hs-doko-snackbar',
            plugin_dir_url( __FILE__ ) . "js/hs-doko-snackbar{$term_file}",
            array('jquery'),
            $this->version,
            false
        );
        $loading_message = esc_html__( 'Loading', 'doko-bundle-builder' );
        /**
         * Filter the data passed to the JS.
         *
         * @since 1.0.0
         */
        $data = apply_filters( 'doko_js_data_boxpage', array(
            'ajaxurl'                         => admin_url( 'admin-ajax.php' ),
            'wc_price_args'                   => array(
                'html'                         => true,
                'currency_symbol'              => get_woocommerce_currency_symbol( get_woocommerce_currency() ),
                'currency_position'            => get_option( 'woocommerce_currency_pos', true ),
                'decimal_separator'            => wc_get_price_decimal_separator(),
                'currency_format_trim_zeros'   => wc_get_price_thousand_separator(),
                'currency_format_num_decimals' => wc_get_price_decimals(),
                'price_format'                 => get_woocommerce_price_format(),
            ),
            'next_message'                    => esc_html__( 'Next', 'doko-bundle-builder' ),
            'loading_message'                 => $loading_message,
            'completed_message'               => esc_html__( 'Your bundle is added to the cart, you will redirect to cart in few moments', 'doko-bundle-builder' ),
            'cart_page_url'                   => wc_get_cart_url(),
            'limit_product_number'            => esc_html__( "The product %1s can't be added more than %2s in the bundle.", 'doko-bundle-builder' ),
            'limit_product_amount'            => esc_html__( 'You reached the limit of %s for this box.', 'doko-bundle-builder' ),
            'can_use_pro'                     => doko_fs()->can_use_premium_code(),
            'product_is_added_to_box_message' => esc_html__( 'Product %s is added to bundle', 'doko-bundle=builder' ),
            'total_qty_exceeded'              => esc_html__( 'You reached the limit of %s in the bundle.', 'doko-bundle-builder' ),
        ) );
        wp_add_inline_script( $this->plugin_name, ' var doko_object=' . wp_json_encode( $data ) . ';' );
        // use by default the shop layout of the woocommerce theme.
        add_filter( 'body_class', function ( $classes ) {
            return array_merge( $classes, array(
                'woocommerce',
                'woocommerce-no-js',
                'woocommerce-page',
                'woocommerce-shop'
            ) );
        }, 99999 );
    }

    /**
     * Init shortcodes.
     */
    public function init_shortcodes() {
        global $doko_is_card_page;
        global $is_doko_page;
        global $doko_current_page;
        global $doko_bundle_id;
        global $doko_is_infinite_loading;
        $doko_is_card_page = false;
        $is_doko_page = false;
        $doko_is_infinite_loading = false;
        $doko_bundle_id = $this->get_page_id();
        if ( isset( $_POST['bundleId'] ) ) {
            $doko_bundle_id = $_POST['bundleId'];
        }
        add_shortcode( 'doko-bundles', array($this, 'display_shortcode') );
        add_shortcode( 'doko_products', 'HS\\Doko\\doko_display_products' );
    }

    /**
     * 	Template Part's
     *
     * @param  string $template Default template file path.
     * @param  string $slug     Template file slug.
     * @param  string $name     Template file name.
     * @return string           Return the template part from plugin.
     */
    function doko_override_woocommerce_template_part( $template, $slug, $name ) {
        $page_id = $this->get_page_id();
        $post_obj = get_post( $page_id );
        if ( \has_shortcode( $post_obj->post_content, 'doko-bundles' ) ) {
            $template_directory = untrailingslashit( DOKO_DIR_PATH ) . '/templates/';
            if ( $name ) {
                $path = $template_directory . "{$slug}-{$name}.php";
            } else {
                $path = $template_directory . "{$slug}.php";
            }
            return ( file_exists( $path ) ? $path : $template );
        }
        return $template;
    }

    /**
     * Template File
     *
     * @param  string $template      Default template file  path.
     * @param  string $template_name Template file name.
     * @param  string $template_path Template file directory file path.
     * @return string                Return the template file from plugin.
     */
    function doko_override_woocommerce_template( $template, $template_name, $template_path ) {
        $page_id = $this->get_page_id();
        $post_obj = get_post( $page_id );
        if ( is_object( $post_obj ) && has_shortcode( $post_obj->post_content, 'doko-bundles' ) ) {
            // if woodmart theme is used
            if ( defined( 'WOODMART_THEMEROOT' ) && $template_name == "loop/loop-start.php" ) {
                return untrailingslashit( WOODMART_THEMEROOT ) . "/woocommerce/loop/loop-start.php";
            }
            if ( defined( 'WOODMART_THEMEROOT' ) && $template_name == "loop/loop-end.php" ) {
                return untrailingslashit( WOODMART_THEMEROOT ) . "/woocommerce/loop/loop-end.php";
            }
            // use templates from plugin.
            $template_directory = untrailingslashit( DOKO_DIR_PATH ) . '/templates/';
            $path = $template_directory . $template_name;
            if ( file_exists( $path ) ) {
                return $path;
            }
            // use templates from themes.
            if ( $template && file_exists( $template ) ) {
                $path = $template;
            }
            return ( file_exists( $path ) ? $path : $template );
        }
        return $template;
    }

    function add_btn_elements() {
        global $product;
        global $doko_package_data;
        global $doko_current_page;
        global $doko_bundle_id;
        if ( function_exists( 'hongo_shop_loop_button' ) ) {
            remove_all_actions( 'hongo_shop_loop_button' );
        }
        if ( is_ajax() ) {
            $package_id = filter_input( INPUT_POST, 'bundleId' );
            $page_id = filter_input( INPUT_POST, 'bundleContentId' );
        } else {
            $page_id = $doko_current_page;
            $package_id = $doko_bundle_id;
        }
        $doko_cur_package_data = array();
        if ( $package_id && 'first-step' != $page_id ) {
            $doko_package_data = get_post_meta( $package_id, 'doko', true );
            $doko_cur_package_data = $doko_package_data[$page_id];
        }
        if ( isset( $doko_package_data['enable-add-to-box-button'] ) && $doko_package_data['enable-add-to-box-button'] == 'yes' ) {
            doko_template_loop_add_to_cart();
        }
    }

    /**
     * Retrieve all rules related to a bundle.
     *
     * @param $package_id string|integer Bundle Package Id
     * @return array
     */
    private function get_rules_by_bundle( $package_id ) {
        $rule_configs = array();
        return $rule_configs;
    }

    /**
     * Display the shortcode.
     *
     * @param mixed $atts Attributes.
     *
     * @return mixed
     */
    public function display_shortcode( $atts ) {
        global $doko_is_card_page;
        global $doko_bundle_id, $doko_package_data;
        global $is_doko_page;
        global $doko_current_page;
        global $doko_current_page_data;
        global $step_identifier;
        $package_id = ( isset( $atts['id'] ) ? $atts['id'] : false );
        $doko_bundle_id = $package_id;
        $package_data = $this->get_data( $package_id );
        $doko_package_data = $package_data;
        if ( wp_is_json_request() ) {
            return;
        }
        if ( !$package_data ) {
            return esc_html__( 'An error occurred while loading this page, please write to the site administrator.', 'doko-bundle-builder' );
        }
        /**
         * Filter the statuses that are allowed to be displayed.
         *
         * @since 1.0.0
         */
        $allowed_post_status = apply_filters( 'doko_bundle_status', array('publish') );
        if ( !in_array( get_post_status( $package_id ), $allowed_post_status, true ) ) {
            return esc_html__( 'This bundle is not available to the public yet.', 'doko-bundle-builder' );
        }
        $product_tags = array();
        if ( isset( $package_data['box-selection-mode'] ) ) {
            $box_data = hs_dk_get_selection_mode( $package_data, $package_data['box-selection-mode'], 'box-' . $package_data['box-selection-mode'] );
            $box_selection_mode = $package_data['box-selection-mode'];
            $product_tags = ( isset( $package_data['box-tags'] ) ? $package_data['box-tags'] : array() );
        }
        ob_start();
        $step_bg_image = '';
        $custom_css = '';
        ?>
			<script>
				var doko_bundle_data = <?php 
        echo wp_json_encode( $package_data );
        ?>;
			</script>
		<?php 
        $counter = 1;
        $doko_current_page = 'first-step';
        $doko_current_page_data = $package_data;
        $is_doko_page = true;
        $step_title = '';
        if ( isset( $package_data['box-description'] ) ) {
            $step_title = $package_data['box-description'];
        }
        $fields_to_unset = Hs_Doko_Admin::get_data_to_unset();
        $last_step_title = ( isset( $package_data['last-step-title'] ) ? $package_data['last-step-title'] : 'Building Your Box' );
        $last_step_description = ( isset( $package_data['last-step-description'] ) ? $package_data['last-step-description'] : ' Please wait a moment while we prepare your custom gift box!' );
        foreach ( $fields_to_unset as $field ) {
            if ( isset( $package_data[$field] ) ) {
                unset($package_data[$field]);
            }
        }
        $step_identifier = array(
            1 => 'first-step',
        );
        ?>
		<div id="doko-giftbox-progress-bar">
			<div class="wrapper">
				<div class="bar"></div>
				<div class="steps">

				<div class="step" data-step-id="1">
								<a href="#" class="woocommerce-loop-product__title doko-not-clickable" tabindex="-1">
									<div class="icon">
										<span>1</span>
									</div>
									<div class="woocommerce-loop-product__title doko-label-title">
									<?php 
        echo wp_sprintf( 
            /* translators: %d: Step number. */
            esc_html__( 'Step %d', 'doko-bundle-builder' ),
            1
         );
        ?>
										</div>
									<div class="description ng-binding" >
									<?php 
        if ( !empty( $step_title ) ) {
            echo wp_kses_post( $step_title );
        } else {
            echo esc_html__( 'Packaging', 'doko-bundle-builder' );
        }
        ?>
									</div>
								</a>
							</div>

				<?php 
        $step_nb = 2;
        foreach ( $package_data as $key => $value ) {
            $step_identifier[$step_nb] = $key;
            ?>
							<div class="step" data-step-id="<?php 
            echo esc_attr( $step_nb );
            ?>" data-step-hash="<?php 
            echo esc_attr( $key );
            ?>">
								<a href="#" class="woocommerce-loop-product__title">
									<div class="icon">
										<span><?php 
            echo esc_attr( $step_nb );
            ?></span>
									</div>
									<div class="woocommerce-loop-product__title doko-label-title">
								<?php 
            echo wp_sprintf( 
                /* translators: %d: Step number. */
                esc_html__( 'Step %d', 'doko-bundle-builder' ),
                esc_attr( $step_nb )
             );
            ?>
									</div>
									<div class="description ng-binding" ><?php 
            ( isset( $value['screen-name'] ) ? formulus_format_fields( $value['screen-name'] ) : '' );
            ?></div>
								</a>
							</div>
						<?php 
            ++$step_nb;
        }
        ?>

					<div class="step" data-step-id="<?php 
        echo esc_attr( $step_nb );
        ?>">
						<a href="#"  class="woocommerce-loop-product__title">
							<div class="icon">
								<span><?php 
        echo esc_attr( $step_nb );
        ?></span>
							</div>
							<div class="woocommerce-loop-product__title doko-label-title"><?php 
        echo esc_html__( 'Step', 'doko-bundle-builder' ) . esc_attr( $step_nb );
        ?></div>
							<div class="description ng-binding" ><?php 
        echo esc_html__( 'Done!', 'doko-bundle-builder' );
        ?></div>
						</a>
					</div>
				</div>
			</div>
		</div>

		<br/>
		<br/>
		<br/>
			<?php 
        ?>
			<div class="modal doko_modal">
				<div class="doko_modal_product_title"></div>
				<div class="doko_modal_product_side" style="display: flex; justify-content: space-around;">
					<div>
						<div class="doko_modal_product_img"></div>
					</div>
					<div>
						<div class="doko_modal_product_desc"></div>
						<div class="doko_modal_product_price"></div>
					</div>
				</div>
				
				
			</div>
			<div class="doko-box-section doko-navigation" >
				<!-- DOKO STEP 1 - BOX SELECTION -->
				<div class="hs-dk-box-selector hs-dk-bundle-page" id="doko-bundle-builder" data-page-id="<?php 
        echo esc_attr( $counter );
        ?>" data-bundle-content-id="<?php 
        echo esc_attr( 'first-step' );
        ?>">
					
						<br>
					<?php 
        if ( !is_array( $box_data ) ) {
            $box_data = array();
        }
        if ( 'products' === $box_selection_mode ) {
            hs_dk_display_products_disposition( $box_data );
        } elseif ( 'categories' === $box_selection_mode ) {
            hs_dk_display_products_from_categories( $box_data );
        } elseif ( 'tags' == $box_selection_mode ) {
        }
        $doko_is_card_page = false;
        ?>
				</div>
				<script>
					var doko_step_identifier = '<?php 
        echo json_encode( $step_identifier );
        ?>'
				</script>
		<?php 
        foreach ( $package_data as $bundle_id => $bundle_data ) {
            ++$counter;
            $doko_current_page = $bundle_id;
            $doko_current_page_data = $bundle_data;
            if ( isset( $bundle_data['screen-disposition'] ) ) {
                $bundle_data['bundle-id'] = $bundle_id;
                $bundle_data['parent-bundle-id'] = $package_id;
                $sdisposition = $bundle_data['screen-disposition'];
                $bundle_viewer = ( !isset( $bundle_data['display-bundle-viewer'] ) ? 'yes' : $bundle_data['display-bundle-viewer'] );
                $is_pro = false;
                $doko_is_card_page = false;
                if ( !$is_pro ) {
                    $sdisposition = 'bundle-products';
                }
                ?>
					<!-- DOKO STEP <?php 
                echo esc_attr( $counter );
                ?> - Dynamic  SELECTION -->
					<div class="hs-dk-bundle-page" data-page-id="<?php 
                echo esc_attr( $counter );
                ?>" data-bundle-content-id="<?php 
                echo esc_attr( $bundle_data['bundle-id'] );
                ?>" data-bundle-id="<?php 
                echo esc_attr( $bundle_data['parent-bundle-id'] );
                ?>"  data-card-id="<?php 
                echo esc_attr( $bundle_data['parent-bundle-id'] );
                ?>">
						<?php 
                if ( $bundle_viewer == "yes" ) {
                    if ( 'bundle-products' == $sdisposition ) {
                        $this->display_bundle_products_screen( $bundle_data );
                    } elseif ( 'bundle-cart-content' == $sdisposition ) {
                    } elseif ( 'bundle-card-page' == $sdisposition ) {
                    }
                }
                ?>
					</div>
				<?php 
            }
        }
        ++$counter;
        ?>
			<!-- DOKO STEP <?php 
        echo esc_attr( $counter );
        ?> - Add to cart -->
			<div class="hs-dk-box-selector hs-dk-bundle-page" id="doko-bundle-builder" data-page-id="<?php 
        echo esc_attr( $counter );
        ?>">
			<?php 
        ob_start();
        ?>
						<div class="build-page wrapper">

							<div id="build-complete">
								<h4 class="doko-step-title secondary">Done!</h4>
								<h1 class="doko-step-title primary ng-binding" >
								<?php 
        formulus_format_fields( $last_step_title );
        ?>
								</h1>
								<div class="doko-step-description ng-binding" ><p>
								<?php 
        formulus_format_fields( $last_step_description );
        ?>

							</p>
							</div>

								<div class="status-icon success" >

									<div  class="ng-loading doko-spinner-loading">
										<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
									</div>

									<div  class="ng-loading doko-spinner-error" style="display: none;">
										<i class="fa fa-circle-exclamation" aria-hidden="true"></i>
									</div>

									<div  class="ng-complete doko-spinner-complete">
										<i class="fa fa-check" aria-hidden="true"></i>
									</div>

								</div>

						<div  style="margin: 40px auto 0 auto; width: 400px; max-width: 100%;" class="ng-scope doko-is-complete">
							<button class="button wp-element-button doko-go-to-cart" type="button">
													<span><?php 
        esc_html_e( 'View Cart', 'doko-bundle-builder' );
        ?></span>
											</button>

							<button class="button alt wp-element-button doko-restart" type="button" >
													<span><?php 
        esc_html_e( ' Build Another Bundle', 'doko-bundle-builder' );
        ?></span>
											</button>

						</div>
						</div>
						<br/>
						<br/>
			<?php 
        $build_page = ob_get_clean();
        echo wp_kses_post( 
            /**
             * Filter the content of the build page.
             *
             * @since 1.0.0
             */
            apply_filters( 'doko_complete_box_page', $build_page )
         );
        $box_variants = apply_filters( 'doko_navigation_box_types', array(
            // 'doko-footer-navigation',
            'doko-box-container',
            'doko-box-contents',
            'doko-card-page',
        ) );
        ?>
				</div>

			</div>
			<div class="hs-dk-menu-fix">
				<div class="hs-dk-menu-box">
					<div class="hs-dk-menu-box-img-fix">
						<?php 
        foreach ( $box_variants as $variant ) {
            echo wp_kses_post( sprintf( '<ul class="%s"></ul>', $variant ) );
        }
        ?>
					</div>
					<div class="hs-dk-menu-btn-content-fix">
					<ul class="doko-btn-navigation">
						<li class="doko-button-rb">
						    <button href="#total" rel="modal:open" class="doko-page-btn button button-primary doko-total-btn wp-block-button__link wp-element-button wc-block-components-product-button__button has-font-size has-small-font-size has-text-align-center wc-interactive doko_total_btn_elt" data-btn-type="total" style="background-color: #f5f1f1; color:black;" ><?php 
        esc_attr_e( 'Total :', 'doko-bundle-builder' );
        ?> <?php 
        echo wp_kses_post( wc_price( 0 ) );
        ?></button>
						</li>
						<li><button href="#prev" class="doko-page-btn button button-primary wp-block-button__link wp-element-button wc-block-components-product-button__button has-font-size has-small-font-size has-text-align-center wc-interactive" data-btn-type="prev" ><?php 
        esc_attr_e( 'Back', 'doko-bundle-builder' );
        ?></button></li>
						<li><button href="#next" class="doko-page-btn button button-primary wp-block-button__link wp-element-button wc-block-components-product-button__button has-font-size has-small-font-size has-text-align-center wc-interactive"  data-btn-type="next"><?php 
        esc_attr_e( 'Next', 'doko-bundle-builder' );
        ?></button></li>
						<li><button href="#complete-box" class="doko-page-btn button button-primary wp-block-button__link wp-element-button wc-block-components-product-button__button has-font-size has-small-font-size has-text-align-center wc-interactive" style="display:none;"  data-btn-type="complete-box"><?php 
        esc_attr_e( 'Complete Box', 'doko-bundle-builder' );
        ?></button></li>
					</ul>
					</div>
				</div>
			</div>
			<input type="hidden" name="doko-current-page" value="1">
			<input type="hidden" name="doko-bundle-page-id" value="<?php 
        echo esc_attr( $package_id );
        ?>">
			<div class='hs-dk-loading' style='display:none; text-align:center;'></div>
			<script>
				doko_object.screens = 
				<?php 
        $screens = array_keys( $package_data );
        echo wp_json_encode( array_merge( array('first-step'), $screens ) );
        ?>
				;
			</script>
			<div id="doko_total_details" class="modal">
              <table class="doko_total_details doko_select wc-block-cart-items wp-block-woocommerce-cart-line-items-block">
                <thead>
                    <tr>
                        <th></th>
                        <th><?php 
        esc_html_e( 'Product Name', 'doko-bundle-builder' );
        ?></th>
                        <th class="doko_style_to_hide"><?php 
        esc_html_e( 'Unit Price', 'doko-bundle-builder' );
        ?></th>
                        <th class="doko_style_to_hide"><?php 
        esc_html_e( 'Quantity', 'doko-bundle-builder' );
        ?></th>
                        <th><?php 
        esc_html_e( 'Total', 'doko-bundle-builder' );
        ?></th>
						<th></th>
                    </tr>
                </thead>
                <tbody></tbody>
              </table>
              <center><a class="doko_total_details_price button button-primary doko-total-btn wp-block-button__link wp-element-button wc-block-components-product-button__button has-font-size has-small-font-size has-text-align-center wc-interactive"></a></center>
            </div>
		<?php 
        wc_print_notices();
        do_action( 'doko_bundle_viewer_is_loaded' );
        return ob_get_clean();
    }

    public function get_products_infinite_loading() {
        ob_start();
        formulus_format_fields( ob_get_clean() );
        wp_die();
    }

    /**
     * Get Products to display.
     */
    public function get_products_to_pick_display() {
        ob_start();
        formulus_format_fields( ob_get_clean() );
        wp_die();
    }

    /**
     * Add box contents to the cart by AJAX.
     */
    public function add_to_cart() {
        $data = filter_input_array( INPUT_POST );
        if ( isset( $data['contents'] ) ) {
            $bundle_id = $data['bundle_id'];
            $total_price = 0;
            $data = $data['contents'];
            $container = $data['container'];
            $card_details = array();
            if ( isset( $data['card_details'] ) ) {
                $card_details = $data['card_details'];
            }
            $product_id = $container['product_id'];
            $quantity = (int) $container['product_qty'];
            $product = wc_get_product( $product_id );
            if ( isset( $container['variation_id'] ) && $container['variation_id'] != 0 ) {
                $variation_id = $container['variation_id'];
                $product = wc_get_product( $variation_id );
                $variation_attr = $product->get_default_attributes();
            } else {
                $variation_id = 0;
                $variation_attr = array();
            }
            $price = (float) $product->get_price();
            $total_price = $price * $quantity;
            $pdts = array();
            if ( isset( $data['contents'] ) ) {
                $contents = $data['contents'];
                if ( isset( $data['contents']['card_details'] ) ) {
                    unset($data['contents']['card_details']);
                }
                if ( !empty( $data['contents'] ) ) {
                    foreach ( $contents as $content ) {
                        $price = $content['product_price'];
                        if ( isset( $content['options'] ) ) {
                            foreach ( $content['options'] as $optin ) {
                                $price += (int) $optin['amount'];
                            }
                        }
                        $total_price += $content['product_qty'] * $price;
                        if ( empty( $content ) ) {
                            continue;
                        }
                        $pdts[] = $content;
                    }
                }
            }
            $dcontents = array();
            $dcontents += array(
                'box_contents' => $pdts,
                'total_price'  => $total_price,
                'container'    => $container,
                'bundle_id'    => $bundle_id,
            );
            $dcontents = apply_filters(
                'doko_cart_contents_element',
                $dcontents,
                $product_id,
                $variation_id,
                $data
            );
            $is_added = WC()->cart->add_to_cart(
                $product_id,
                $quantity,
                $variation_id,
                $variation_attr,
                $dcontents
            );
            $response_notice = wc_print_notices( true );
            echo wp_json_encode( array(
                'doko_is_added_to_cart'  => false != $is_added,
                'doko_html_response_adc' => $response_notice,
            ) );
        }
        wp_die();
    }

    /**
     * Add box contents.
     *
     * @param mixed $cart_item Cart Item data.
     * @param mixed $cart_item_key Cart Item Key.
     *
     * @return mixed
     */
    public function add_box_contents( $cart_item, $cart_item_key ) {
        if ( isset( $cart_item_key['box_contents'] ) ) {
            if ( isset( $cart_item_key['bundle_id'] ) && !empty( $cart_item_key['bundle_id'] ) ) {
                $bundle_data = get_post_meta( $cart_item_key['bundle_id'], 'doko', true );
                if ( isset( $bundle_data['enable-child-product-display'] ) && 'yes' != $bundle_data['enable-child-product-display'] ) {
                    return $cart_item;
                }
                $pvariation_id = $cart_item_key['variation_id'];
                $pproduct_id = $cart_item_key['product_id'];
                $children_product = array();
                $children_product[] = array(
                    'key'   => 'Original Price',
                    'value' => wc_price( $cart_item_key['container']['product_price'] ),
                );
                foreach ( $cart_item_key['box_contents'] as $screen_id => $screen_content ) {
                    $product_id = $screen_content['product_id'];
                    $variation_id = 0;
                    if ( isset( $screen_content['variation_id'] ) ) {
                        $variation_id = $screen_content['variation_id'];
                    }
                    $price = $screen_content['product_price'];
                    $qty = $screen_content['product_qty'];
                    $product = wc_get_product( $product_id );
                    if ( !is_object( $product ) ) {
                        continue;
                    }
                    $ptype = $product->get_type();
                    $product_name = $product->get_name();
                    if ( 'variable' == $ptype && $variation_id != 0 ) {
                        $product = wc_get_product( $variation_id );
                        $product_name = $product->get_name();
                    }
                    $children_product[] = array(
                        'key'   => "<a href='" . get_permalink( $product_id ) . "'>" . $product_name . '</a>',
                        'value' => wc_price( $price ) . '  x' . $qty,
                    );
                }
                $cart_item = array_merge( $cart_item, $children_product );
            }
        }
        return apply_filters( 'doko_add_box_contents', $cart_item, $cart_item_key );
    }

    /**
     * Set box total price.
     */
    public function set_box_total_price() {
        foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
            if ( isset( $cart_item['box_contents'] ) ) {
                /**
                 * Filter the total price of the box.
                 *
                 * @since 1.0.0
                 */
                $total_price = apply_filters(
                    'doko_get_total_price',
                    $cart_item['total_price'],
                    $cart_item_key,
                    $cart_item
                );
                $cart_item['data']->set_price( $total_price );
            }
        }
    }

    /**
     * Replace default button text.
     *
     * @param mixed $btn_text Button text.
     *
     * @return mixed
     */
    public function replace_default_btn_text( $btn_text, $product, $args ) {
        $page_url_id = $this->get_page_id();
        $post_obj = get_post( $page_url_id );
        if ( has_shortcode( $post_obj->post_content, 'doko-bundles' ) ) {
            global $product;
            $product_url = wc_placeholder_img_src();
            if ( $product->get_image_id() != 0 && $product->get_image_id() != '' ) {
                $product_url = wp_get_attachment_url( $product->get_image_id() );
            } else {
                $product_url = content_url( '/plugins/woocommerce/assets/images/placeholder.png' );
            }
            global $doko_is_card_page;
            $price = $product->get_price();
            if ( $price == '' || empty( $price ) ) {
                $price = 0;
            }
            $btn_title_text = esc_html__( 'Add to box', 'doko-bundle-builder' );
            $category_ids = implode( ',', $product->get_category_ids() );
            $tag_ids = implode( ',', $product->get_tag_ids() );
            $icon_informations = '';
            $ptype = $product->get_type();
            $doko_display_variations_as_many = apply_filters( 'doko_display_variations_as_many', value: true );
            $childrens = $product->get_children();
            if ( !$doko_display_variations_as_many && count( $childrens ) > 0 && !in_array( $ptype, array('simple', 'variation') ) ) {
            } else {
                $btn_text = sprintf(
                    '<div class="doko_wrapper_btn"><a style="cursor:pointer;" data-quantity="%s" data-product-id="%s" data-product-price="%s" data-site-currency="%s" data-product-name="%s" data-image-url="%s" class="%s" data-product-variation-parent-id="%s" data-card-mode="%s" data-product-cat="%s" data-product-tag="%s" >%s</a>' . $icon_informations . '</div>',
                    esc_attr( ( isset( $args['quantity'] ) ? $args['quantity'] : 1 ) ),
                    $product->get_id(),
                    $price,
                    get_woocommerce_currency_symbol(),
                    get_the_title(),
                    $product_url,
                    'button doko-add-to-box wp-block-button__link wp-element-button wc-block-components-product-button__button has-font-size has-small-font-size has-text-align-center wc-interactive',
                    $product->get_parent_id(),
                    ( $doko_is_card_page ? 'yes' : 'no' ),
                    $category_ids,
                    $tag_ids,
                    ( $doko_is_card_page ? esc_html__( 'Add card to box', 'doko-bundle-builder' ) : $btn_title_text )
                );
            }
            $btn_text = apply_filters( 'hs_doko_btn_add_to_box_link', $btn_text, array(
                'product'           => $product,
                'page_id'           => $page_url_id,
                'doko_is_card_page' => $doko_is_card_page,
                'args'              => $args,
            ) );
        }
        return $btn_text;
    }

    /**
     * Add box contents order line.
     *
     * @param mixed $item Item.
     * @param mixed $cart_item_key Cart Item Key.
     * @param mixed $values Values.
     *
     * @return void
     */
    public function add_box_contents_order_line( $item, $cart_item_key, $values ) {
        if ( isset( $values['box_contents'] ) ) {
            $doko_children_products = array();
            $item->update_meta_data( 'Original Price', wc_price( $values['container']['product_price'] ) );
            foreach ( $values['box_contents'] as $screen_id => $screen_content ) {
                $product_id = $screen_content['product_id'];
                $variation_id = 0;
                if ( isset( $screen_content['variation_id'] ) && $screen_content['variation_id'] != 0 ) {
                    $product_id = $screen_content['variation_id'];
                    $variation_id = $screen_content['variation_id'];
                }
                $price = $screen_content['product_price'];
                $qty = $screen_content['product_qty'];
                $product = wc_get_product( $product_id );
                $product_name = $product->get_name();
                $item->update_meta_data( "<a href='" . get_permalink( $product_id ) . "'>" . $product_name . '</a>', wc_price( $price ) . '  x' . $qty );
                $data = array(
                    'qty'          => $qty,
                    'product_id'   => $product_id,
                    'variation_id' => $variation_id,
                );
                $doko_children_products[] = $data;
            }
            if ( isset( $values['card_details'] ) ) {
                $card_details = $values['card_details'];
                $tproduct_name = '';
                if ( isset( $card_details['to'] ) && $card_details['to'] != '' ) {
                    $tproduct_name .= esc_html__( '<br/> To : ', 'doko-bundle-builder' ) . $card_details['to'];
                }
                if ( isset( $card_details['from'] ) && $card_details['from'] != '' ) {
                    $tproduct_name .= esc_html__( '<br/> From : ', 'doko-bundle-builder' ) . $card_details['from'];
                }
                if ( isset( $card_details['message'] ) && $card_details['message'] != '' ) {
                    $tproduct_name .= esc_html__( '<br/> Message : ', 'doko-bundle-builder' ) . $card_details['message'];
                }
                if ( isset( $card_details['options'] ) && $card_details['options'] != '' ) {
                    $tproduct_name .= esc_html__( '<br/> Options Choosed : ', 'doko-bundle-builder' ) . $card_details['options'];
                }
                $item->update_meta_data( "<a href='" . get_permalink( $card_details['product_id'] ) . "'>" . get_the_title( $card_details['product_id'] ) . '</a>', $tproduct_name );
                $doko_children_products[] = array(
                    'qty'        => $qty,
                    'product_id' => $product_id,
                );
            }
            $item->add_meta_data( 'doko_bundle_id', $values['bundle_id'], true );
            $item->add_meta_data( 'doko_children_bundle_ids', json_encode( $doko_children_products ) );
        }
    }

    private function get_page_id() {
        $page_url_id = url_to_postid( $_SERVER['REQUEST_URI'] );
        if ( isset( $_SERVER['HTTP_REFERER'] ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            $page_url_id = url_to_postid( $_SERVER['HTTP_REFERER'] );
        }
        return $page_url_id;
    }

    /**
     * Disable product Link.
     *
     * @param mixed $product_link PRODUCT LINK.
     *
     * @return mixed
     */
    public function disable_product_link( $product_link ) {
        $page_url_id = $this->get_page_id();
        $post_obj = get_post( $page_url_id );
        if ( has_shortcode( $post_obj->post_content, 'doko-bundles' ) || defined( 'DOING_AJAX' ) && DOING_AJAX && has_shortcode( $post_obj->post_content, 'doko-bundles' ) ) {
            $product_link = '#doko-add-to-box';
        }
        return $product_link;
    }

    private function display_box_title( $package_data ) {
        if ( isset( $package_data['display-bundle-title'] ) && 'yes' == $package_data['display-bundle-title'] ) {
            echo do_blocks( $package_data['bundle-title'] );
        }
    }

    private function display_bundle_products_screen( $package_data, $is_card_screen = false ) {
        if ( isset( $package_data['enable-add-to-box-button'] ) && $package_data['enable-add-to-box-button'] == 'yes' ) {
            remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
        }
        $this->display_box_title( $package_data );
        ?>
			<br>
			<div class="hs-dk-box-selector" id="doko">
				<?php 
        global $doko_current_package_data;
        $doko_current_package_data = $package_data;
        // this part handle the box content selection.
        if ( 'products' === $package_data['options'] ) {
            hs_dk_display_products_disposition( $package_data['products'], $is_card_screen );
        } elseif ( 'categories' === $package_data['options'] ) {
            hs_dk_display_products_from_categories( $package_data['categories'], $is_card_screen );
        } elseif ( 'tags' == $package_data['options'] ) {
        }
        ?>
			</div>
		<?php 
    }

    private function get_data( $package_id ) {
        return get_post_meta( $package_id, 'doko', true );
    }

    private function display_bundle_cart_content_screen( $package_data ) {
    }

    private function display_card_content_page( $package_data ) {
    }

}

add_filter( 'woocommerce_order_item_get_formatted_meta_data', function ( $names ) {
    $new_names = $names;
    foreach ( $new_names as $name_id => $name ) {
        if ( $name->key == 'doko_bundle_id' && !is_admin() ) {
            unset($new_names[$name_id]);
        }
        if ( $name->key == 'doko_children_bundle_ids' ) {
            unset($new_names[$name_id]);
        }
    }
    return $new_names;
}, 999 );
add_action(
    'woocommerce_reduce_order_item_stock',
    function ( $item, $change, $order ) {
        if ( $item->get_meta( 'doko_bundle_id' ) ) {
            $children_products = $item->get_meta( 'doko_children_bundle_ids' );
            $children_products = json_decode( $children_products, true );
            $changes = array();
            foreach ( $children_products as $key => $value ) {
                $product_id = $value['product_id'];
                if ( isset( $value['variation_id'] ) && $value['variation_id'] != 0 ) {
                    $product_id = $value['variation_id'];
                }
                $stock_avail = get_post_meta( $product_id, '_manage_stock', true );
                if ( $stock_avail == 'yes' ) {
                    $qty = $value['qty'];
                    $product = wc_get_product( $product_id );
                    $new_stock = wc_update_product_stock( $product, $qty, 'decrease' );
                    $changes[] = array(
                        'product' => $product,
                        'from'    => $new_stock + $qty,
                        'to'      => $new_stock,
                    );
                }
            }
            wc_trigger_stock_change_notifications( $order, $changes );
        }
    },
    10,
    3
);
add_action( 'woocommerce_restore_order_stock', function ( $order ) {
    foreach ( $order->get_items() as $item ) {
        if ( !$item->is_type( 'line_item' ) ) {
            continue;
        }
        $children_products = $item->get_meta( 'doko_children_bundle_ids' );
        $children_products = json_decode( $children_products, true );
        foreach ( $children_products as $key => $value ) {
            $product_id = $value['product_id'];
            if ( isset( $value['variation_id'] ) && $value['variation_id'] != 0 ) {
                $product_id = $value['variation_id'];
            }
            $stock_avail = get_post_meta( $product_id, '_manage_stock', true );
            $qty = $value['qty'];
            $product = wc_get_product( $product_id );
            if ( $stock_avail == 'yes' ) {
                wc_update_product_stock( $product, $qty, 'increase' );
                $order->add_order_note( esc_html__( 'Stock levels of product ', 'doko-bundle-builder' ) . $product->get_formatted_name() . ' has increased of ' . $qty );
            }
        }
    }
} );
//todo : display in modal qty, wit description