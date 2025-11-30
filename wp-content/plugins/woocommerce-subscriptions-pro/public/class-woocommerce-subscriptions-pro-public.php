<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/public
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 * namespace woocommerce_subscriptions_pro_public.
 *
 * @package    Woocommerce_Subscriptions_Pro
 * @subpackage Woocommerce_Subscriptions_Pro/public
 * @author     WP Swings <webmaster@wpswings.com>
 */
class Woocommerce_Subscriptions_Pro_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The coupon error of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $coupon_error    The current version of this plugin.
	 */
	private $coupon_error;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wsp_public_enqueue_styles() {
		if ( is_product() || is_shop() ) {

			wp_enqueue_style( $this->plugin_name, WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'public/css/woocommerce-subscriptions-pro-public.css', array(), $this->version, 'all' );
		}
		if ( is_account_page() ) {
			wp_enqueue_style( 'wps_wsp_account_page', WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'public/css/woocommerce-subscriptions-pro-my-account-page.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wsp_public_enqueue_scripts() {

		wp_register_script( $this->plugin_name . '-min', WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'public/js/wps-public.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '-min' );

		wp_localize_script(
			$this->plugin_name . '-min',
			'wsp_public_param',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'  => wp_create_nonce( 'wps_wsp_public_nonce' ),
			)
		);

		if ( is_product() ) {

			wp_register_script( 'wps_wsp_single_pro_public', WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'public/js/woocommerce-subscriptions-pro-single-pro-public.js', array( 'jquery' ), $this->version, false );

			$wps_array = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'wps_wsp_nonce' => wp_create_nonce( 'wps-wsp-verify-nonce' ),
				'wps_is_expiry_enable' => wps_wsp_check_allow_expiry_by_customer(),
				'day' => __( 'Days', 'woocommerce-subscriptions-pro' ),
				'week' => __( 'Weeks', 'woocommerce-subscriptions-pro' ),
				'month' => __( 'Months', 'woocommerce-subscriptions-pro' ),
				'year' => __( 'Years', 'woocommerce-subscriptions-pro' ),

			);
			wp_localize_script( 'wps_wsp_single_pro_public', 'wsp_pro_public_param', $wps_array );
			wp_enqueue_script( 'wps_wsp_single_pro_public' );

		}
		if ( is_account_page() ) {
			wp_register_script( $this->plugin_name, WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'public/js/woocommerce-subscriptions-pro-public.js', array( 'jquery' ), $this->version, false );
			$wps_array = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'wps_wsp_nonce' => wp_create_nonce( 'wps-wsp-verify-nonce' ),
				'error_text' => __( 'Please enter coupon code', 'woocommerce-subscriptions-pro' ),
			);

			wp_localize_script( $this->plugin_name, 'wsp_pro_public_param', $wps_array );
			wp_enqueue_script( $this->plugin_name );
		}
	}

	/**
	 * This function is used to create susbcription expiry field in product page.
	 *
	 * @name wps_wsp_woocommerce_before_add_to_cart_button
	 * @since    1.0.0
	 */
	public function wps_wsp_woocommerce_before_add_to_cart_button() {
		global $product;
		if ( isset( $product ) && ! empty( $product ) ) {
			$product_id = $product->get_id();

			if ( wps_sfw_check_product_is_subscription( $product ) && wps_wsp_check_allow_expiry_by_customer() ) {
				$subscription_interval = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true );
				if ( empty( $subscription_interval ) ) {
					$subscription_interval = 'day';
				}
				?>
					<div class="wps_wsp_expiry_interval_field_wrap">
						<p class="wps_wsp_expiry_interval_field">
							<label for="wps_wsp_expiry_number" class="wps_wsp_label"><?php esc_html_e( 'Subscriptions Expiry Interval', 'woocommerce-subscriptions-pro' ); ?></label>
							<input type="hidden" name="wps_wsp_before_atc_nonce" value=<?php echo esc_html( wp_create_nonce( 'wps_wsp_before_atc_nonce' ) ); ?>>

							<input type="number" min="1" name="wps_wsp_expiry_number" id="wps_wsp_expiry_number" class="wps_wsp_expiry_number" placeholder="<?php esc_attr_e( 'Enter subscription expiry', 'woocommerce-subscriptions-pro' ); ?>">
							<select id="wps_wsp_expiry_number_interval" name="wps_wsp_expiry_number_interval" class="wps_wsp_expiry_number_interval">
							<?php foreach ( wps_sfw_subscription_expiry_period( $subscription_interval ) as $value => $label ) { ?>
								<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php } ?>
							</select>
						</p>
					</div>
				<?php
			}
		}
	}

	/**
	 * This function is used to add subscription expiry date by customer.
	 *
	 * @name wps_wsp_woocommerce_add_cart_item_data
	 * @param array $the_cart_data the_cart_data.
	 * @param int   $product_id product_id.
	 * @param int   $variation_id variation_id.
	 * @since    1.0.0
	 */
	public function wps_wsp_woocommerce_add_cart_item_data( $the_cart_data, $product_id, $variation_id ) {

		$product_id = empty( $variation_id ) ? $product_id : $variation_id;
		$product = wc_get_product( $product_id );
		if ( isset( $_POST['wps_type_selection'] ) ) {
			$the_cart_data['wps_type_selection'] = $_POST['wps_type_selection'];
		}
		if ( wps_sfw_check_product_is_subscription( $product ) ) {

			if ( isset( $_POST['wps_wsp_expiry_number'] ) && ! empty( $_POST['wps_wsp_expiry_number'] ) ) {
				if ( ! isset( $_POST['wps_wsp_before_atc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_wsp_before_atc_nonce'] ) ), 'wps_wsp_before_atc_nonce' ) ) {
					return;
				}
				$wps_wsp_expiry_number = isset( $_POST['wps_wsp_expiry_number'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wsp_expiry_number'] ) ) : '';

				$wps_sfw_subscription_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_number', true );

				if ( $wps_wsp_expiry_number < $wps_sfw_subscription_number ) {
					return $the_cart_data;
				}
				$wps_wsp_expiry_number_interval = isset( $_POST['wps_wsp_expiry_number_interval'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wsp_expiry_number_interval'] ) ) : '';

				$item_meta['wps_wsp_expiry_number'] = $wps_wsp_expiry_number;
				$item_meta['wps_wsp_expiry_number_interval'] = $wps_wsp_expiry_number_interval;

				$item_meta = apply_filters( 'wps_wsp_add_cart_item_data', $item_meta, $the_cart_data, $product_id, $variation_id );
				$the_cart_data ['product_meta'] = array( 'meta_data' => $item_meta );
			}
		}
		return $the_cart_data;
	}

	/**
	 * This function is used to restrict expiry.
	 *
	 * @name wps_wsp_expiry_add_to_cart_validation
	 * @param bool $validate validate.
	 * @param int  $product_id product_id.
	 * @param int  $quantity quantity.
	 * @since 1.0.0
	 */
	public function wps_wsp_expiry_add_to_cart_validation( $validate, $product_id, $quantity ) {
		$product = wc_get_product( $product_id );
		if ( wps_sfw_check_product_is_subscription( $product ) ) {
			if ( isset( $_POST['wps_wsp_expiry_number'] ) && ! empty( $_POST['wps_wsp_expiry_number'] ) ) {
				if ( ! isset( $_POST['wps_wsp_before_atc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_wsp_before_atc_nonce'] ) ), 'wps_wsp_before_atc_nonce' ) ) {
					return;
				}

				$wps_wsp_expiry_number = isset( $_POST['wps_wsp_expiry_number'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wsp_expiry_number'] ) ) : '';

				$wps_sfw_subscription_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_number', true );

				if ( $wps_wsp_expiry_number < $wps_sfw_subscription_number ) {
					$validate = false;
					wc_add_notice( __( 'Expiry Interval must be greater than subscription interval.', 'woocommerce-subscriptions-pro' ), 'error' );

				}
			}
		}
		return $validate;
	}

	/**
	 * This function is used to show subscription expiry date by customer in cart page.
	 *
	 * @name wps_wsp_show_time_interval_on_cart
	 * @param string $wps_price_html price.
	 * @param int    $product_id product_id.
	 * @param array  $cart_item cart_item.
	 * @since    1.0.0
	 */
	public function wps_wsp_show_time_interval_on_cart( $wps_price_html, $product_id, $cart_item ) {
		if ( is_cart() || is_checkout() ) {
			if ( isset( $cart_item ) && ! empty( $cart_item ) && is_array( $cart_item ) ) {
				if ( wps_sfw_check_product_is_subscription( $cart_item['data'] ) ) {
					$wps_product_id = $cart_item['data']->get_id();
					if ( $product_id == $wps_product_id ) {
						if ( isset( $cart_item['product_meta']['meta_data'] ) ) {
							if ( isset( $cart_item['product_meta']['meta_data']['wps_wsp_expiry_number'] ) ) {
								$wps_wsp_expiry_number = $cart_item['product_meta']['meta_data']['wps_wsp_expiry_number'];
								$wps_wsp_expiry_number_interval = $cart_item['product_meta']['meta_data']['wps_wsp_expiry_number_interval'];
								switch ( $wps_wsp_expiry_number_interval ) {
									case 'day':
										/* translators: %s: search term */
										$wps_price_html = sprintf( _n( '%s Day', '%s Days', $wps_wsp_expiry_number, 'woocommerce-subscriptions-pro' ), $wps_wsp_expiry_number );
										break;
									case 'week':
										/* translators: %s: search term */
										$wps_price_html = sprintf( _n( '%s Week', '%s Weeks', $wps_wsp_expiry_number, 'woocommerce-subscriptions-pro' ), $wps_wsp_expiry_number );
										break;
									case 'month':
										/* translators: %s: search term */
										$wps_price_html = sprintf( _n( '%s Month', '%s Months', $wps_wsp_expiry_number, 'woocommerce-subscriptions-pro' ), $wps_wsp_expiry_number );
										break;
									case 'year':
										/* translators: %s: search term */
										$wps_price_html = sprintf( _n( '%s Year', '%s Years', $wps_wsp_expiry_number, 'woocommerce-subscriptions-pro' ), $wps_wsp_expiry_number );
										break;
								}
							}
						}
					}
				}
			}
		}
		return $wps_price_html;
	}

	/**
	 * This function is used to show subscription expiry date by customer in cart page.
	 *
	 * @name wps_wsp_change_subscription_expiry_by_customer
	 * @param array $wps_recurring_data wps_recurring_data.
	 * @param array $cart_item cart_item.
	 * @since    1.0.0
	 */
	public function wps_wsp_change_subscription_expiry_by_customer( $wps_recurring_data, $cart_item ) {

		if ( isset( $cart_item['product_meta']['meta_data']['wps_wsp_expiry_number'] ) ) {
			 $wps_wsp_expiry_number = $cart_item['product_meta']['meta_data']['wps_wsp_expiry_number'];
			 $wps_wsp_expiry_number_interval = $cart_item['product_meta']['meta_data']['wps_wsp_expiry_number_interval'];
			 $wps_recurring_data['wps_sfw_subscription_expiry_number'] = $wps_wsp_expiry_number;
			 $wps_recurring_data['wps_sfw_subscription_expiry_interval'] = $wps_wsp_expiry_number_interval;
		}
		$wps_availabe_coupon = WC()->cart->get_applied_coupons();
		if ( ! empty( $wps_availabe_coupon ) && is_array( $wps_availabe_coupon ) ) {
			foreach ( WC()->cart->get_applied_coupons() as $code ) {
				$coupon = new WC_Coupon( $code );
				$coupon_type  = $coupon->get_discount_type();
				$coupon_id = $coupon->get_id();

				$giftcardcoupon = wps_wsp_get_meta_data( $coupon_id, 'wps_wgm_giftcard_coupon', true );

				if ( 'initial_fee_discount' == $coupon_type ) {
					$wps_recurring_data['initial_fee_discount'] = $code;
				} elseif ( 'initial_fee_percent_discount' == $coupon_type ) {
					$wps_recurring_data['initial_fee_percent_discount'] = $code;
				} elseif ( 'recurring_product_discount' == $coupon_type ) {
					$wps_recurring_data['recurring_product_discount'] = $code;
				} elseif ( 'recurring_product_percent_discount' == $coupon_type ) {
					$wps_recurring_data['recurring_product_percent_discount'] = $code;
				} elseif ( ! empty( $giftcardcoupon ) && 'fixed_cart' == $coupon_type ) {
					if ( wps_wsp_get_subscription_coupon_enable_for_gc() ) {
						$wps_recurring_data['wps_wgm_giftcard_coupon'] = $code;
					}
				}
			}
		}

		return $wps_recurring_data;
	}

	/**
	 * This function is used to get available payment method.
	 *
	 * @name wps_wsp_manual_payment_gateway_for_woocommerce
	 * @param array  $supported_payment_method supported_payment_method.
	 * @param string $payment_method payment_method.
	 * @since    1.0.0
	 */
	public function wps_wsp_manual_payment_gateway_for_woocommerce( $supported_payment_method, $payment_method ) {
			$has_subscription = false;
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( isset( $cart_item['product_meta']['meta_data']['wps_wsp_expiry_number'] ) ) {
				$wps_wsp_expiry_interval = $cart_item['product_meta']['meta_data']['wps_wsp_expiry_number_interval'];
				$wps_wsp_expiry_number = $cart_item['product_meta']['meta_data']['wps_wsp_expiry_number'];
				if ( 'day' === $wps_wsp_expiry_interval && 90 < $wps_wsp_expiry_number ) {
					if ( ( array_search( 'paypal', $supported_payment_method ) === $payment_method ) !== false ) {
						unset( $supported_payment_method[ $payment_method ] );
					}
				}
				if ( 'week' === $wps_wsp_expiry_interval && 52 < $wps_wsp_expiry_number ) {
					if ( ( array_search( 'paypal', $supported_payment_method ) === $payment_method ) !== false ) {
						unset( $supported_payment_method[ $payment_method ] );
					}
				}
				if ( 'month' === $wps_wsp_expiry_interval && 24 < $wps_wsp_expiry_number ) {
					if ( ( array_search( 'paypal', $supported_payment_method ) === $payment_method ) !== false ) {
						unset( $supported_payment_method[ $payment_method ] );
					}
				}
				if ( 'year' === $wps_wsp_expiry_interval && 5 < $wps_wsp_expiry_number ) {
					if ( ( array_search( 'paypal', $supported_payment_method ) === $payment_method ) !== false ) {
						unset( $supported_payment_method[ $payment_method ] );
					}
				}
			}
			if ( function_exists( 'wps_wsp_check_is_cart_subscription' ) && wps_wsp_check_is_cart_subscription() && ( ! isset( $cart_item['wps_type_selection'] ) || 'one_time' !== $cart_item['wps_type_selection'] ) ) {
				$has_subscription = true;
			}
		}
		if ( wps_wsp_enbale_accept_manual_payment() ) {
			if ( 'bacs' == $payment_method ) {
				$supported_payment_method[] = $payment_method;
			} elseif ( 'cheque' == $payment_method ) {
				$supported_payment_method[] = $payment_method;
			} elseif ( 'cod' == $payment_method ) {
				$supported_payment_method[] = $payment_method;
			}
		}
		if ( ! $has_subscription ) {
			$supported_payment_method[] = $payment_method;
		}
		return $supported_payment_method;
	}

	/**
	 * This function is used to add paused button html.
	 *
	 * @name wps_wsp_order_details_html_for_paused_subscription
	 * @param int $wps_subscription_id wps_subscription_id.
	 * @since    1.0.0
	 */
	public function wps_wsp_order_details_html_for_paused_subscription( $wps_subscription_id ) {
		if ( wps_wsp_enable_pause_susbcription_by_customer() ) {

			$wps_status = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_subscription_status', true );
			if ( 'active' == $wps_status ) {
				$wps_wsp_pause_url = $this->wps_wsp_pause_url( $wps_subscription_id, $wps_status );
				?>
					<!-- <td> -->
						<a href="<?php echo esc_url( $wps_wsp_pause_url ); ?>" class="button wps_wsp_pause_subscription"><?php esc_html_e( 'Pause', 'woocommerce-subscriptions-pro' ); ?></a>
					<!-- </td> -->
				<?php
			}
		}
		if ( wps_wsp_start_pause_susbcription_by_customer() ) {

			$wps_status = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_subscription_status', true );
			if ( 'paused' == $wps_status ) {
				$wps_wsp_reactivate_url = $this->wps_wsp_reactivate_url( $wps_subscription_id, $wps_status );
				?>
					<!-- <td> -->
						<a href="<?php echo esc_url( $wps_wsp_reactivate_url ); ?>" class="button wps_wsp_reactivate_subscription"><?php esc_html_e( 'Reactivate', 'woocommerce-subscriptions-pro' ); ?></a>
					<!-- </td> -->
				<?php
			}
		}
		$parent_id = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_parent_order', true );
		if ( $parent_id && wc_get_order( $parent_id ) && in_array( wc_get_order( $parent_id )->get_payment_method(), array( 'stripe', 'stripe_sepa' ) ) && defined( 'WC_STRIPE_VERSION' ) ) {

			?>
			<!-- <td> -->
				<a href="<?php echo esc_url( site_url( 'my-account/add-payment-method/?wps_subscription_id=' . $wps_subscription_id ) ); ?>" class="button wps_wsp_payment_method_change"><?php esc_html_e( 'Change Payment Method', 'woocommerce-subscriptions-pro' ); ?></a>
			<!-- </td> -->
			<?php
		}
		$wps_status = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_subscription_status', true );
		if ( 'on' == get_option( 'wsp_allow_customer_subsciption_edit' ) && 'active' == $wps_status ) {
			?>
			<!-- <td> -->
				<div class="subscription-edit-container wps-not-content"></div>
				<a href="#" class="button wps_wsp_get_edit_form" data-subscription_id="<?php echo esc_attr( $wps_subscription_id ); ?>"><?php esc_html_e( 'Update Subscription', 'woocommerce-subscriptions-pro' ); ?></a>
			<!-- </td> -->
			<?php
		}
	}

	/**
	 * This function is used to add paused url.
	 *
	 * @name wps_wsp_pause_url
	 * @param int    $wps_subscription_id wps_subscription_id.
	 * @param status $wps_status wps_status.
	 * @since    1.0.0
	 */
	public function wps_wsp_pause_url( $wps_subscription_id, $wps_status ) {

		$wps_link = add_query_arg(
			array(
				'wps_subscription_id'        => $wps_subscription_id,
				'wps_subscription_status_pause' => $wps_status,
			)
		);
		$wps_link = wp_nonce_url( $wps_link, $wps_subscription_id . $wps_status );

		return $wps_link;
	}

	/**
	 * This function is used to add reactivate url.
	 *
	 * @name wps_wsp_reactivate_url
	 * @param int    $wps_subscription_id wps_subscription_id.
	 * @param status $wps_status wps_status.
	 * @since    1.0.0
	 */
	public function wps_wsp_reactivate_url( $wps_subscription_id, $wps_status ) {

		$wps_link = add_query_arg(
			array(
				'wps_subscription_id'        => $wps_subscription_id,
				'wps_subscription_status_reactivate' => $wps_status,
			)
		);
		$wps_link = wp_nonce_url( $wps_link, $wps_subscription_id . $wps_status );

		return $wps_link;
	}

	/**
	 * This function is used to pause susbcription.
	 *
	 * @name wps_wsp_pause_susbcription
	 * @since 1.0.0
	 */
	public function wps_wsp_pause_susbcription() {

		if ( isset( $_GET['wps_subscription_status_pause'] ) && isset( $_GET['wps_subscription_id'] ) && isset( $_GET['_wpnonce'] ) && ! empty( $_GET['_wpnonce'] ) ) {
			$user_id      = get_current_user_id();

			$wps_status   = sanitize_text_field( wp_unslash( $_GET['wps_subscription_status_pause'] ) );
			$wps_subscription_id = sanitize_text_field( wp_unslash( $_GET['wps_subscription_id'] ) );
			if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
				$this->wps_wsp_pause_susbcription_by_customer( $wps_subscription_id, $wps_status, $user_id );
			}
		} elseif ( isset( $_GET['wps_subscription_status_reactivate'] ) && isset( $_GET['wps_subscription_id'] ) && isset( $_GET['_wpnonce'] ) && ! empty( $_GET['_wpnonce'] ) ) {
			$user_id      = get_current_user_id();

			$wps_status   = sanitize_text_field( wp_unslash( $_GET['wps_subscription_status_reactivate'] ) );
			$wps_subscription_id = sanitize_text_field( wp_unslash( $_GET['wps_subscription_id'] ) );
			if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
				$this->wps_wsp_reactivate_susbcription_by_customer( $wps_subscription_id, $wps_status, $user_id );
			}
		}
	}

	/**
	 * This function is used to pause susbcription.
	 *
	 * @name wps_wsp_pause_susbcription_by_customer
	 * @param int    $wps_subscription_id wps_subscription_id.
	 * @param string $wps_status wps_status.
	 * @param int    $user_id user_id.
	 * @since 1.0.0
	 */
	public function wps_wsp_pause_susbcription_by_customer( $wps_subscription_id, $wps_status, $user_id ) {

		$wps_customer_id = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_customer_id', true );
		if ( 'active' == $wps_status && $wps_customer_id == $user_id ) {

			wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'paused' );
			wps_wsp_set_pause_subscription_timestamp( $wps_subscription_id );
			wps_wsp_send_email_for_pause_susbcription( $wps_subscription_id );
			wc_add_notice( __( 'Subscription Paused Successfully', 'woocommerce-subscriptions-pro' ), 'success' );
			$redirect_url = wc_get_endpoint_url( 'show-subscription', $wps_subscription_id, wc_get_page_permalink( 'myaccount' ) );
			wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	/**
	 * This function is used to reactivate susbcription.
	 *
	 * @name wps_wsp_reactivate_susbcription_by_customer
	 * @param int    $wps_subscription_id wps_subscription_id.
	 * @param string $wps_status wps_status.
	 * @param int    $user_id user_id.
	 * @since 1.0.0
	 */
	public function wps_wsp_reactivate_susbcription_by_customer( $wps_subscription_id, $wps_status, $user_id ) {

		$wps_customer_id = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_customer_id', true );
		if ( 'paused' == $wps_status && $wps_customer_id == $user_id ) {

			wps_wsp_reactivate_time_calculation( $wps_subscription_id );
			wps_wsp_update_meta_data( $wps_subscription_id, 'wps_subscription_status', 'active' );
			wps_wsp_send_email_for_reactivate_susbcription( $wps_subscription_id );
			wc_add_notice( __( 'Subscription Reactivated Successfully', 'woocommerce-subscriptions-pro' ), 'success' );
			$redirect_url = wc_get_endpoint_url( 'show-subscription', $wps_subscription_id, wc_get_page_permalink( 'myaccount' ) );
			wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	/**
	 * This function is used to add upgrade/downgrade button.
	 *
	 * @name wps_wsp_product_details_downgrade_upgrade
	 * @param int $wps_subscription_id wps_subscription_id.
	 * @since    1.0.0
	 */
	public function wps_wsp_product_details_downgrade_upgrade( $wps_subscription_id ) {
		if ( wps_wsp_check_upgrade_downgrade() ) {
			if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
				$product_id = wps_wsp_get_meta_data( $wps_subscription_id, 'product_id', true );
				$product = wc_get_product( $product_id );

				if ( wps_sfw_check_variable_product_is_subscription( $product ) ) {
					$product_url = get_permalink( $product_id );

					$wps_upgrade_downgrade = $this->wps_wsp_get_upgrade_downgrade_url( $wps_subscription_id, $product_id, $product_url );
					$wps_wsp_text = wps_wsp_get_upgrade_downgrade_text();

					$wps_upgrade_downgrade_url = sprintf( '<a href="%s" class="wps_upgrade_downgrade btn button">%s</a>', $wps_upgrade_downgrade, $wps_wsp_text );
					echo apply_filters( 'wps_sfw_upgrade_downgrade_button', wp_kses_post( '<br/>' . $wps_upgrade_downgrade_url ), $wps_subscription_id );
				}
			}
		}
	}

	/**
	 * This function is used to add upgrade/downgrade url.
	 *
	 * @name wps_wsp_get_upgrade_downgrade_url
	 * @param int    $wps_subscription_id wps_subscription_id.
	 * @param int    $product_id product_id.
	 * @param string $product_url product_url.
	 * @since    1.0.0
	 */
	public function wps_wsp_get_upgrade_downgrade_url( $wps_subscription_id, $product_id, $product_url ) {
		$wps_query_args =
					array(
						'wps_downgrade_upgrade_subscription' => absint( $wps_subscription_id ),
						'product_id'                         => absint( $product_id ),
						'wps_switch_nonce'                   => wp_create_nonce( 'wps_downgrade_upgrade_nonce' ),
					);

		$wps_upgrade_downgrade_url  = add_query_arg( $wps_query_args, $product_url );

		return $wps_upgrade_downgrade_url;
	}

	/**
	 * This function is used to validate upgrade/downgrade product.
	 *
	 * @name wps_wsp_upgrade_downgrade_add_to_cart_validation
	 * @param bool $valid valid.
	 * @param int  $product_id product_id.
	 * @param int  $quantity quantity.
	 * @since    1.0.0
	 */
	public function wps_wsp_upgrade_downgrade_add_to_cart_validation( $valid, $product_id, $quantity ) {

		if ( ! isset( $_GET['wps_downgrade_upgrade_subscription'] ) ) {
			return $valid;
		}
		if ( ! isset( $_POST['variation_id'] ) ) {
			return $valid;
		}
		if ( ! isset( $_GET['wps_switch_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wps_switch_nonce'] ) ), 'wps_downgrade_upgrade_nonce' ) ) {
			return false;
		}

		if ( isset( $_GET['product_id'] ) ) {
			$product = wc_get_product( sanitize_text_field( wp_unslash( $_POST['variation_id'] ) ) );

			if ( ! wps_sfw_check_variable_product_is_subscription( $product ) ) {
				$wps_error = __( 'You can switch only with subscription products', 'woocommerce-subscriptions-pro' );
				wc_add_notice( $wps_error, 'error' );
				$valid = false;
			} elseif ( $_GET['product_id'] == $_POST['variation_id'] ) {
				$wps_error = __( 'You can not switch with same variation', 'woocommerce-subscriptions-pro' );
				wc_add_notice( $wps_error, 'error' );
				$valid = false;
			}
		}

		$product_id       = isset( $_POST['variation_id'] ) ? sanitize_text_field( wp_unslash( $_POST['variation_id'] ) ) : '';

		$_product = wc_get_product( $product_id );

		$wps_subscription_id = isset( $_GET['wps_downgrade_upgrade_subscription'] ) ? sanitize_text_field( wp_unslash( $_GET['wps_downgrade_upgrade_subscription'] ) ) : '';
		$wps_recurring_total = $_product->get_price();

		$wps_old_price = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_recurring_total', true );

		$wps_old_interval = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_sfw_subscription_interval', true );
		$wps_new_interval = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true );
		$wps_wsp_old_number = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_sfw_subscription_number', true );

		if ( 'day' == $wps_old_interval ) {
			$per_day_old_price = ( $wps_old_price / $wps_wsp_old_number );

		} elseif ( 'week' == $wps_old_interval ) {
			$wps_old_div_number = $wps_wsp_old_number * 7;

			$per_day_old_price = ( $wps_old_price / $wps_old_div_number );

		} elseif ( 'month' == $wps_old_interval ) {
			$wps_old_div_number = $wps_wsp_old_number * 30;

			$per_day_old_price = ( $wps_old_price / $wps_old_div_number );

		} elseif ( 'year' == $wps_old_interval ) {
			$wps_old_div_number = $wps_wsp_old_number * 365;

			$per_day_old_price = ( $wps_old_price / $wps_old_div_number );

		}

		$wps_new_subs_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_number', true );

		if ( 'day' == $wps_new_interval ) {
			$per_day_new_price = ( $wps_recurring_total / $wps_new_subs_number );

		} elseif ( 'week' == $wps_new_interval ) {
			$wps_new_div_number = $wps_new_subs_number * 7;

			$per_day_new_price = ( $wps_recurring_total / $wps_new_div_number );

		} elseif ( 'month' == $wps_new_interval ) {
			$wps_new_div_number = $wps_new_subs_number * 30;

			$per_day_new_price = ( $wps_recurring_total / $wps_new_div_number );

		} elseif ( 'year' == $wps_new_interval ) {
			$wps_new_div_number = $wps_new_subs_number * 365;

			$per_day_new_price = ( $wps_recurring_total / $wps_new_div_number );

		}
		if ( get_option( 'wsp_enable_allow_same_interval', false ) ) {

			if ( $wps_new_interval !== $wps_old_interval ) {
				/* translators: Placeholder 1: Plan, placeholder 2: plan */
				wc_add_notice( sprintf( __( 'You can not upgrade into %1$2s from %2$1s', 'woocommerce-subscriptions-pro' ), $wps_new_interval, $wps_old_interval ), 'error' );
				return false;
			}
		}

		if ( get_option( 'wps_wsp_downgrade_variable_subscription' ) ) {
			if ( $per_day_old_price > $per_day_new_price ) {
				wc_add_notice( __( 'You can not downgrade the plan', 'woocommerce-subscriptions-pro' ), 'error' );
				return false;
			}
		}

		return $valid;
	}

	/**
	 * This function is used to get upgrade/downgrade link.
	 *
	 * @name wps_wsp_downgrade_upgrade_link
	 * @param string $permalink permalink.
	 * @param object $post post.
	 * @since    1.0.0
	 */
	public function wps_wsp_downgrade_upgrade_link( $permalink, $post ) {

		if ( ! isset( $_GET['wps_downgrade_upgrade_subscription'] ) || ! is_product() || 'product' !== $post->post_type ) {
			return $permalink;
		}
		$product = wc_get_product( $post );

		if ( $product ) {
			$product_type = $product->get_type();
			if ( 'variable' == $product_type ) {
				$product_id = isset( $_GET['product_id'] ) ? sanitize_text_field( wp_unslash( $_GET['product_id'] ) ) : '';
				$permalink = $this->wps_wsp_get_upgrade_downgrade_url( sanitize_text_field( wp_unslash( $_GET['wps_downgrade_upgrade_subscription'] ) ), $product_id, $permalink );
			}
		}

		return $permalink;
	}

	/**
	 * This function is used to get upgrade/downgrade cart data.
	 *
	 * @name wps_wsp_upgrade_downgrade_cart_details
	 * @param array $cart_item_data cart_item_data.
	 * @param int   $product_id product_id.
	 * @param int   $variation_id variation_id.
	 * @since    1.0.0
	 */
	public function wps_wsp_upgrade_downgrade_cart_details( $cart_item_data, $product_id, $variation_id ) {

		if ( isset( $_GET['wps_downgrade_upgrade_subscription'] ) && ! empty( $_GET['wps_downgrade_upgrade_subscription'] ) ) {
			$user_id = get_current_user_id();

			$wps_subscription_id = sanitize_text_field( wp_unslash( $_GET['wps_downgrade_upgrade_subscription'] ) );
			if ( wps_wsp_check_upgrade_downgrade() ) {
				if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
					$exiting_product_id = wps_wsp_get_meta_data( $wps_subscription_id, 'product_id', true );
					$wps_existing_user = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_customer_id', true );
					if ( $wps_existing_user != $user_id ) {
						wc_add_notice( __( 'You can not switch others user subscriptions.', 'woocommerce-subscriptions-pro' ), 'error' );
						WC()->cart->empty_cart( true );
						wp_redirect( get_permalink( $product_id ) );
						exit();
					}
					$wps_next_payment_date = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_next_payment_date', true );
					$product_id = isset( $_GET['product_id'] ) ? sanitize_text_field( wp_unslash( $_GET['product_id'] ) ) : '';
					$cart_item_data['wps_upgrade_downgrade_data'] = array(
						'wps_subscription_id'       => $wps_subscription_id,
						'product_id'                => absint( $product_id ),
						'wps_next_payment_date'     => $wps_next_payment_date,
					);

				}
			}
		}
		return $cart_item_data;
	}

	/**
	 * This function is used to get upgrade/downgrade cart data.
	 *
	 * @name wps_wsp_is_upgrade_downgrade_text
	 * @param string $product_subtotal product_subtotal.
	 * @param array  $cart_item cart_item.
	 * @param int    $cart_item_key cart_item_key.
	 * @since    1.0.0
	 */
	public function wps_wsp_is_upgrade_downgrade_text( $product_subtotal, $cart_item, $cart_item_key ) {
		if ( isset( $cart_item['wps_upgrade_downgrade_data'] ) ) {
			$wps_subscription_id = $cart_item['wps_upgrade_downgrade_data']['wps_subscription_id'];
			if ( ! wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
				return $product_subtotal;
			}
			$wps_switch_type = $this->wps_wsp_get_upgrade_downgrade_type( $wps_subscription_id, $cart_item );
			if ( ! empty( $wps_switch_type ) ) {

				if ( 'upgrade' == $wps_switch_type ) {
					$wps_switch_type = __( 'Upgrade', 'woocommerce-subscriptions-pro' );
				} else {
					$wps_switch_type = __( 'Downgrade', 'woocommerce-subscriptions-pro' );
				}
				// translators: type.
				$product_subtotal = sprintf( '%1s %2$s(%3$s)%4$s', $product_subtotal, '<span class="subscription-switch-direction">', $wps_switch_type, '</span>' );
			}
		}

		return $product_subtotal;
	}

	/**
	 * This function is used to get upgrade/downgrade type.
	 *
	 * @name wps_wsp_get_upgrade_downgrade_type
	 * @param int   $wps_subscription_id wps_subscription_id.
	 * @param array $cart_item cart_item.
	 * @since    1.0.0
	 */
	public function wps_wsp_get_upgrade_downgrade_type( $wps_subscription_id, $cart_item ) {
		$wps_switch_type = '';

		if ( isset( $cart_item ) && ! empty( $cart_item ) && is_array( $cart_item ) ) {
			if ( $cart_item['data']->is_on_sale() ) {
				$price = $cart_item['data']->get_sale_price();
			} else {
				$price = $cart_item['data']->get_regular_price();
			}
			$wps_recurring_total = $price * $cart_item['quantity'];
			$wps_old_price = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_recurring_total', true );

			$product_id = $cart_item['data']->get_id();
			$wps_old_interval = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_sfw_subscription_interval', true );
			$wps_new_interval = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_interval', true );
			$wps_wsp_old_number = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_sfw_subscription_number', true );

			if ( 'day' == $wps_old_interval ) {
				$per_day_old_price = ( $wps_old_price / $wps_wsp_old_number );

			} elseif ( 'week' == $wps_old_interval ) {
				$wps_old_div_number = $wps_wsp_old_number * 7;

				$per_day_old_price = ( $wps_old_price / $wps_old_div_number );

			} elseif ( 'month' == $wps_old_interval ) {
				$wps_old_div_number = $wps_wsp_old_number * 30;

				$per_day_old_price = ( $wps_old_price / $wps_old_div_number );

			} elseif ( 'year' == $wps_old_interval ) {
				$wps_old_div_number = $wps_wsp_old_number * 365;

				$per_day_old_price = ( $wps_old_price / $wps_old_div_number );
			}
			$wps_new_subs_number = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_number', true );

			if ( 'day' == $wps_new_interval ) {
				$per_day_new_price = ( $wps_recurring_total / $wps_new_subs_number );

			} elseif ( 'week' == $wps_new_interval ) {
				$wps_new_div_number = $wps_new_subs_number * 7;

				$per_day_new_price = ( $wps_recurring_total / $wps_new_div_number );

			} elseif ( 'month' == $wps_new_interval ) {
				$wps_new_div_number = $wps_new_subs_number * 30;

				$per_day_new_price = ( $wps_recurring_total / $wps_new_div_number );

			} elseif ( 'year' == $wps_new_interval ) {
				$wps_new_div_number = $wps_new_subs_number * 365;

				$per_day_new_price = ( $wps_recurring_total / $wps_new_div_number );
			}
			if ( $per_day_new_price >= $per_day_old_price ) {
				$wps_switch_type = 'upgrade';
			} elseif ( $per_day_new_price < $per_day_old_price ) {
				$wps_switch_type = 'downgrade';
			}
		}
		return $wps_switch_type;
	}

	/**
	 * This function is used to get upgrade/downgrade order.
	 *
	 * @name wps_wsp_is_upgrade_downgrade_order
	 * @param bool   $valid valid.
	 * @param array  $wps_recurring_data wps_recurring_data.
	 * @param object $order order.
	 * @param array  $posted_data posted_data.
	 * @param array  $cart_item cart_item.
	 * @throws WC_Data_Exception Exception may be thrown if value is invalid.
	 * @since    1.0.0
	 */
	public function wps_wsp_is_upgrade_downgrade_order( $valid, $wps_recurring_data, $order, $posted_data, $cart_item ) {
		if ( isset( $cart_item['wps_upgrade_downgrade_data'] ) && ! empty( $cart_item['wps_upgrade_downgrade_data'] ) ) {
			$wps_subscription_id = isset( $cart_item['wps_upgrade_downgrade_data']['wps_subscription_id'] ) ? $cart_item['wps_upgrade_downgrade_data']['wps_subscription_id'] : '';
			if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {

				$order_id = $order->get_id();
				$wps_switch_type = $this->wps_wsp_get_upgrade_downgrade_type( $wps_subscription_id, $cart_item );

				$wps_data = array(
					'wps_wsp_switch_recurring_data'  => $wps_recurring_data,
					'wps_wsp_switch_recurring_order_id' => $order_id,
					'wps_wsp_switch_cart_data' => $cart_item['wps_upgrade_downgrade_data'],
					'wps_wsp_switch_type' => $wps_switch_type,

				);
				wps_wsp_update_meta_data( $wps_subscription_id, 'wps_upgrade_downgrade_data', $wps_data );

				wps_wsp_update_meta_data( $order_id, 'wps_upgrade_downgrade_order', 'yes' );

				wps_wsp_update_meta_data( $order_id, 'wps_subscription_id', $wps_subscription_id );

				$valid = true;

			}
		}
		return $valid;
	}

	/**
	 * This function is used to set upgrade/downgrade price.
	 *
	 * @name wps_wsp_add_switch_subscription_price_and_sigup_fee
	 * @param object $cart cart.
	 * @since    1.0.0
	 */
	public function wps_wsp_add_switch_subscription_price_and_sigup_fee( $cart ) {
		$set = false;

		if ( WC()->session->downgrade_upgrade_notice ) {
			WC()->session->__unset( 'downgrade_upgrade_notice' );
		}
		if ( isset( $cart ) && ! empty( $cart ) ) {

			foreach ( $cart->cart_contents as $key => $cart_data ) {
				// Remove susbcription products from cart if settings disable.
				$this->wps_wsp_remove_susbcription_product_from_cart( $cart_data );

				$product_id = $cart_data['data']->get_id();

				if ( $this->wps_wsp_check_cart_has_switch_subscription( $cart_data ) ) {

					$wps_subscription_id = $cart_data['wps_upgrade_downgrade_data']['wps_subscription_id'];

					if ( ! wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
						return;
					}
					$wps_switch_type = $this->wps_wsp_get_upgrade_downgrade_type( $wps_subscription_id, $cart_data );

					$product_id = $cart_data['data']->get_id();
					$_product = wc_get_product( $product_id );
					$product_price = $_product->get_price();

					if ( wps_wsp_check_enable_prorate_price_upgrade_downgrade() && ( 'downgrade' == $wps_switch_type || 'upgrade' == $wps_switch_type ) ) {
						$product_price = wps_wsp_proprate_price_calculate( $wps_subscription_id, $product_id, $product_price, $cart_data, $set );
						$wps_obj = $this->wps_wsp_generate_public_obj();
						$wps_sfw_free_trial_number = $wps_obj->wps_sfw_get_subscription_trial_period_number( $product_id );

						if ( isset( $wps_sfw_free_trial_number ) && ! empty( $wps_sfw_free_trial_number ) ) {
							$product_price = 0;
						}
						$cart_data['data']->set_price( $product_price );
					}
				}
			}
		}
	}

	/**
	 * This function is used to allow signup fee.
	 *
	 * @name wps_sfw_allow_signup_fee
	 * @param bool   $allow allow.
	 * @param object $cart cart.
	 * @since    1.0.0
	 */
	public function wps_sfw_allow_signup_fee( $allow, $cart ) {
		if ( WC()->session->downgrade_upgrade_notice ) {
			WC()->session->__unset( 'downgrade_upgrade_notice' );
		}
		$product_id = $cart['data']->get_id();

		if ( $this->wps_wsp_check_cart_has_switch_subscription( $cart ) ) {
			$allow = false;

			$wps_subscription_id = $cart['wps_upgrade_downgrade_data']['wps_subscription_id'];

			if ( ! wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
				return;
			}
			$wps_switch_type = $this->wps_wsp_get_upgrade_downgrade_type( $wps_subscription_id, $cart );

			$product_id = $cart['data']->get_id();
			$_product = wc_get_product( $product_id );
			$product_price = $_product->get_price();

			if ( wps_wsp_check_enable_singup_upgrade_downgrade() && ( 'downgrade' == $wps_switch_type || 'upgrade' == $wps_switch_type ) ) {
				$product_price = wps_wsp_proprate_price_calculate( $wps_subscription_id, $product_id, $product_price, $cart, false );
				$wps_obj = $this->wps_wsp_generate_public_obj();
				$wps_sfw_free_trial_number = $wps_obj->wps_sfw_get_subscription_trial_period_number( $product_id );
				$wps_sfw_signup_fee = $wps_obj->wps_sfw_get_subscription_initial_signup_price( $product_id );
				$wps_sfw_signup_fee = is_numeric( $wps_sfw_signup_fee ) ? (float) $wps_sfw_signup_fee : 0;
				if ( $wps_sfw_signup_fee ) {
					$allow = true;
				}
			}
		}
		return $allow;
	}

	/**
	 * This function is used to generate object.
	 *
	 * @name wps_wsp_generate_public_obj
	 * @since    1.0.0
	 */
	public function wps_wsp_generate_public_obj() {
		$wps_obj = '';
		if ( class_exists( 'Subscriptions_For_Woocommerce_Public' ) ) {
			$wps_obj = new Subscriptions_For_Woocommerce_Public( 'woocommerce-subscriptions-pro', '1.0.0' );
		}
		return $wps_obj;
	}

	/**
	 * This function is used to check cart have switch subscription.
	 *
	 * @name wps_wsp_check_cart_has_switch_subscription
	 * @param array $cart_item cart_item.
	 * @since    1.0.0
	 */
	public function wps_wsp_check_cart_has_switch_subscription( $cart_item ) {
		$valid = false;
		if ( isset( $cart_item['wps_upgrade_downgrade_data'] ) && ! empty( $cart_item['wps_upgrade_downgrade_data'] ) ) {
			$valid = true;
		}

		return $valid;
	}

	/**
	 * This function is used to show sync interval.
	 *
	 * @name wps_wsp_show_sync_interval_price
	 * @param string $wps_price_html wps_price_html.
	 * @param int    $product_id product_id.
	 * @since    1.0.0
	 */
	public function wps_wsp_show_sync_interval_price( $wps_price_html, $product_id ) {

		if ( wps_wsp_start_susbcription_from_certain_date_of_month() && wps_wsp_subscription_syn_enable_per_product( $product_id ) ) {

			$wps_price_html = wps_wsp_get_sync_subscription_details( $product_id, $wps_price_html );
		}
		return $wps_price_html;
	}

	/**
	 * This function is used to show first payment on single product page.
	 *
	 * @name wps_wsp_show_first_payment_date_for_sync_subscription
	 * @since    1.0.0
	 */
	public function wps_wsp_show_first_payment_date_for_sync_subscription() {
		global $product;
		if ( wps_sfw_check_product_is_subscription( $product ) ) {
			$product_id = $product->get_id();
			if ( wps_wsp_start_susbcription_from_certain_date_of_month() ) {
				$wps_first_payment = wps_wsp_get_sync_first_payment_date( $product_id );
				?>
					<p class="wps_wsp_sync_fist_payment_date">
						<?php
							echo wp_kses_post( $wps_first_payment );
						?>
					</p>
					<?php
			}
			if ( wps_wsp_allow_start_date_subscription() ) {

				$wps_start_date = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_start_date', true );
				if ( $wps_start_date ) {
					?>
					<div class="wps_wsp_start_date">
					<span><?php esc_html_e( 'Your subscription will start on: ', 'woocommerce-subscriptions-pro' ); ?>
					<?php echo esc_html( wps_sfw_get_the_wordpress_date_format( strtotime( $wps_start_date ) ) ); ?>
					</div>
					<?php
				}
			}
		}
	}

	/**
	 * This function is used to show cart price.
	 *
	 * @name wps_wsp_cart_price_for_sync_subscription
	 * @param int   $price price.
	 * @param array $cart_data cart_data.
	 * @since    1.0.0
	 */
	public function wps_wsp_cart_price_for_sync_subscription( $price, $cart_data ) {

		$product_id = $cart_data['data']->get_id();

		if ( wps_wsp_start_susbcription_from_certain_date_of_month() && wps_wsp_subscription_syn_enable_per_product( $product_id ) ) {

			$wps_wsp_prorate_type = wsp_get_prorate_price_on_sync_enable();

			if ( 'wps_wsp_prorate_no' == $wps_wsp_prorate_type ) {
				return $price;
			} elseif ( 'wps_wsp_prorate_simple' == $wps_wsp_prorate_type || 'wps_wsp_prorate_if_free_trial' == $wps_wsp_prorate_type ) {
				$price = wps_wsp_prorate_price_for_sync( $price, $product_id, $cart_data );
			}
		}
		return $price;
	}

	/**
	 * This function is used to add first payment date for variable product.
	 *
	 * @name wps_wsp_variation_descriptions
	 * @param array  $variation_data variation_data.
	 * @param object $product product.
	 * @param object $variation variation.
	 * @since    1.0.0
	 */
	public function wps_wsp_variation_descriptions( $variation_data, $product, $variation ) {
		if ( wps_sfw_check_product_is_subscription( $variation ) ) {
			$product_id = $variation->get_id();
			if ( wps_wsp_allow_start_date_subscription() ) {
				$variation_data['wps_start_payment_html'] = wps_wsp_get_sync_start_payment_date( $product_id );
			}
			if ( wps_wsp_start_susbcription_from_certain_date_of_month() ) {
				if ( isset( $variation_data ) && ! empty( $variation_data ) && is_array( $variation_data ) ) {

					$variation_data['wps_first_payment_html'] = wps_wsp_get_sync_first_payment_date( $product_id );
				}
			}
		}
		return $variation_data;
	}

	/**
	 * This function is used to add to cart validation.
	 *
	 * @name wps_wsp_add_to_cart_validation
	 * @param bool $validate validate.
	 * @param int  $product_id product_id.
	 * @param int  $quantity quantity.
	 * @since    1.0.0
	 */
	public function wps_wsp_add_to_cart_validation( $validate, $product_id, $quantity ) {

		if ( wps_wsp_check_enable_add_multiple_subscription_cart() ) {
			$validate = true;
		}
		return $validate;
	}

	/**
	 * This function is used enable/disable shipping.
	 *
	 * @name wps_wsp_enable_shipping_subscription
	 * @param bool $enable as enable.
	 * @since    1.0.0
	 */
	public function wps_wsp_enable_shipping_subscription( $enable ) {

		if ( ! function_exists( 'WC' ) || ! did_action( 'woocommerce_cart_loaded_from_session' ) || ! $enable ) {
			return $enable;
		}
		if ( ! is_cart() && ! is_checkout() ) {
			return $enable;
		}

		if ( function_exists( 'wps_sfw_is_cart_has_subscription_product' ) && wps_sfw_is_cart_has_subscription_product() ) {
			$allow_first_purchase_shipping = ( 'on' === get_option( 'wsp_allow_shipping_on_subscription_first_puchase' ) );
			$allow_on_subscription = wps_wsp_enable_shipping_on_subscription();
			if ( $allow_on_subscription || $allow_first_purchase_shipping ) {
				return true;
			}
			return false;
		}
		return $enable;
	}

	/**
	 * This function is used to check if cart needs shipping.
	 *
	 * @name woocommerce_cart_needs_shipping
	 * @param bool $needs needs.
	 * @since    2.6.1
	 */
	public function woocommerce_cart_needs_shipping( $needs ) {
		if ( ! function_exists( 'WC' ) || ! did_action( 'woocommerce_cart_loaded_from_session' ) || ! $needs ) {
			return $needs;
		}
		if ( ! is_cart() && ! is_checkout() ) {
			return $needs;
		}

		if ( function_exists( 'wps_sfw_is_cart_has_subscription_product' ) && wps_sfw_is_cart_has_subscription_product() ) {
			$allow_first_purchase_shipping = ( 'on' === get_option( 'wsp_allow_shipping_on_subscription_first_puchase' ) );
			$allow_on_subscription = wps_wsp_enable_shipping_on_subscription();
			if ( $allow_on_subscription || $allow_first_purchase_shipping ) {
				return true;
			}
			return false;
		}
		return $needs;
	}

	/**
	 * This function is used to remove susbcription from cart.
	 *
	 * @name wps_wsp_remove_susbcription_product_from_cart
	 * @param array $cart_data cart_data.
	 * @since    1.0.0
	 */
	public function wps_wsp_remove_susbcription_product_from_cart( $cart_data ) {
		if ( ! wps_wsp_check_enable_add_multiple_subscription_cart() ) {
			if ( wps_sfw_check_product_is_subscription( $cart_data['data'] ) ) {
				if ( wps_wsp_no_of_susbcription_in_cart() > 1 && isset( $cart_data['key'] ) ) {
					WC()->cart->remove_cart_item( $cart_data['key'] );
				}
			}
		}
	}

	/**
	 * This function is used to giftcard coupon apply html.
	 *
	 * @name wps_wsp_add_gift_card_coupon_apply_html
	 * @param array $wps_subscription_id wps_subscription_id.
	 * @since    1.0.0
	 */
	public function wps_wsp_add_gift_card_coupon_apply_html( $wps_subscription_id ) {
		if ( wps_wsp_get_subscription_coupon_enable_for_gc() ) {
			?>
			<table class="shop_table wps_wsp_apply_gc_coupon">
				<h3><?php esc_html_e( 'Apply Gift card Coupon', 'woocommerce-subscriptions-pro' ); ?></h3>
				<tr>
					<td>
						<p class="wps_wsp_coupon_wrap wps_wsp_coupon_error_<?php echo esc_attr( $wps_subscription_id ); ?>" style="display: none"></p>
						<input type="text" placeholder="<?php esc_attr_e( 'Enter coupon code', 'woocommerce-subscriptions-pro' ); ?>" id="wps_wsp_gift_coupon_<?php echo esc_attr( $wps_subscription_id ); ?>" class="wps_wsp_gift_coupon" name="wps_wsp_gift_coupon"/>
						<a href="#" class="button wps_wsp_apply_giftcard_coupon" data-id="<?php echo esc_attr( $wps_subscription_id ); ?>"><?php esc_html_e( 'Apply', 'woocommerce-subscriptions-pro' ); ?></a>
						<p id="wps-wsp-my-aacount-ajax-loading-gif" class="wps-wsp-my-aacount-ajax-loading-gif" style="display: none;">
							<img src="<?php echo esc_url( WOOCOMMERCE_SUBSCRIPTIONS_PRO_DIR_URL . 'admin/image/loading.gif' ); ?>">
						</p>
					</td>
				</tr>
			</table>
			<?php
		}
	}

	/**
	 * This function is used to set multiple quantity for susbcription product.
	 *
	 * @name wps_wsp_show_quantity_fields_for_susbcriptions
	 * @param bool   $return return.
	 * @param object $product product.
	 * @since 1.1.0
	 */
	public function wps_wsp_show_quantity_fields_for_susbcriptions( $return, $product ) {
		if ( $return ) {
			if ( wps_wsp_enable_multiple_quantity_field() && wps_sfw_check_product_is_subscription( $product ) ) {
				$return = false;
			}
		}
		return $return;
	}

	/**
	 * This function is used to add start date into recurring data.
	 *
	 * @param array $wps_recurring_data wps_recurring_data.
	 * @param int   $product_id product_id.
	 * @return array
	 */
	public function wps_wsp_add_start_date_recurring( $wps_recurring_data, $product_id ) {

		if ( wps_wsp_allow_start_date_subscription() ) {
			$wps_sfw_subscription_start_date = wps_wsp_get_meta_data( $product_id, 'wps_sfw_subscription_start_date', true );
			if ( isset( $wps_sfw_subscription_start_date ) && ! empty( $wps_sfw_subscription_start_date ) ) {
				$wps_recurring_data['wps_sfw_subscription_start_date'] = $wps_sfw_subscription_start_date;
			}
		}

		return $wps_recurring_data;
	}
	/**
	 * This function is used to set subscription status.
	 *
	 * @param string $status subscription status.
	 * @param int    $wps_subscription_id subscription id.
	 * @return string
	 */
	public function wps_wsp_set_subscription_status( $status, $wps_subscription_id ) {
		if ( wps_wsp_allow_start_date_subscription() ) {
			$wps_sfw_subscription_start_date = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_sfw_subscription_start_date', true );
			if ( $wps_sfw_subscription_start_date ) {
				if ( ( current_time( 'Y-m-d' ) ) >= $wps_sfw_subscription_start_date ) {
					$status = 'active';
				} else {
					$status = 'pending';
				}
			}
		}
		return $status;
	}

	/**
	 * Function name wps_wsp_show_downgrade_upgrade_msg.
	 * this function is used to show downgrade upgrade msg.
	 *
	 * @return void
	 */
	public function wps_wsp_show_downgrade_upgrade_msg() {
		if ( ( is_cart() || is_checkout() ) ) {
			if ( WC()->session->downgrade_upgrade_notice ) {
				$downgrade_upgrade_notice = WC()->session->downgrade_upgrade_notice;
				wc_add_notice( $downgrade_upgrade_notice, 'notice' );
			}
		}
	}

	/**
	 * This function is used to remove cart notice when upgrade downgrade cart is empty.
	 *
	 * @return void
	 */
	public function wps_wsp_remove_cart_notice() {
		if ( WC()->cart->get_cart_contents_count() === 0 ) {
			if ( WC()->session->downgrade_upgrade_notice ) {
				WC()->session->__unset( 'downgrade_upgrade_notice' );
			}
		}
	}

	/**
	 * Skip subscription creates for one time purchase
	 *
	 * @param bool  $wps_is_subscription .
	 * @param array $cart_item .
	 * @return $wps_is_subscription
	 */
	public function wps_skip_creating_subscription( $wps_is_subscription, $cart_item ) {
		if ( is_array( $cart_item ) && isset( $cart_item['wps_type_selection'] ) && 'one_time' === $cart_item['wps_type_selection'] ) {
			return false;
		}
		return $wps_is_subscription;
	}

	/**
	 * Display the onetime price selection on the shop and product pages
	 *
	 * @param string $price .
	 * @param int    $product_id .
	 * @param array  $cart_item .
	 *
	 * @return $price
	 */
	public function wps_wsp_price_html_onetime_subscription_product( $price, $product_id, $cart_item ) {

		if ( function_exists( 'is_product' ) && ! is_product() ) {
			$wps_wsp_onetime_price = wps_wsp_get_meta_data( $product_id, 'wps_wsp_onetime_price', true );
			if ( is_array( $cart_item ) && isset( $cart_item['wps_type_selection'] ) && 'one_time' === $cart_item['wps_type_selection'] ) {
				return wc_price( $wps_wsp_onetime_price );
			} else {
				return $price;
			}
		}
		if ( $product_id ) {

			$product = wc_get_product( $product_id );
			$wps_wsp_onetime_price = wps_wsp_get_meta_data( $product_id, 'wps_wsp_onetime_price', true );
			$wps_sfw_one_time_purchase = wps_wsp_get_meta_data( $product_id, 'wps_sfw_one_time_purchase', true );

			if ( ! empty( $product->get_price() ) && 'on' === $wps_sfw_one_time_purchase && ! empty( $wps_wsp_onetime_price ) && $wps_wsp_onetime_price > $product->get_price() && 'variable' != $product->get_type() ) {
				if ( is_cart() || is_checkout() ) {
					if ( is_array( $cart_item ) && isset( $cart_item['wps_type_selection'] ) && 'one_time' === $cart_item['wps_type_selection'] ) {
						return wc_price( $wps_wsp_onetime_price );
					} else {
						return $price;
					}
				}
				$price_discount_percentage = abs( number_format( ( ( $wps_wsp_onetime_price - $product->get_price() ) / $wps_wsp_onetime_price ) * 100, 2 ) );

				$wps_price_html  = $price;
				$wps_price_html  .= '<div class="wps_sfw_subscription_wrapper">';
				$wps_price_html .= '<div class ="wps_sfw_subscription_inner_wrapper">';
				// translators: placeholder is price_discount_percentage.
				$wps_price_html .= '<label class="wps_sfw_subscription_label" for="wps_sfw_check_simple_cart_subscription_purchase"><input name="wps_type_selection" type="radio" class="wps_sfw_check_simple_cart_subscription_purchase" value="subscribe" id ="wps_sfw_check_simple_cart_subscription_purchase" data-pro_type="subscription" data-id="' . $product_id . '" checked>' . sprintf( esc_html__( 'Subscribe and Save %s%% Off Every Order, Guaranteed Delivery, Make Changes Any Time, Prompt VIP Support', 'woocommerce-subscriptions-pro' ), $price_discount_percentage ) . '</label></div>';
				$wps_price_html .= '<div class ="wps_wsp_onetimesimple_wrapper">';
				$wps_price_html .= '<label for="wps_sfw_check_simple_cart_one_time_purchase"><input name="wps_type_selection" type="radio" class="wps_sfw_check_simple_cart_one_time_purchase" data-pro_type="one_time" value="one_time" id="wps_sfw_check_simple_cart_one_time_purchase" data-id="' . $product_id . '">';
				// translators: one time price.
				$wps_price_html .= sprintf( esc_html__( 'One Time Purchase For %s', 'woocommerce-subscriptions-pro' ), wc_price( $wps_wsp_onetime_price ) ) . ' ';
				$wps_price_html .= '</label></div></div>';
				$price = $wps_price_html;
			}
		}
		return $price;
	}

	/**
	 * Set the onetime purcahse amount on the cart product price if product is onetime subscription
	 *
	 * @param object $cart cart.
	 * @since    1.0.0
	 */
	public function wps_wsp_add_to_cart_one_time_add_price( $cart ) {
		if ( isset( $cart ) && ! empty( $cart ) ) {
			foreach ( $cart->cart_contents as $key => $cart_item ) {
				$product_id = $cart_item['data']->get_id();
				if ( is_array( $cart_item ) && isset( $cart_item['wps_type_selection'] ) && 'one_time' === $cart_item['wps_type_selection'] ) {
					$wps_wsp_onetime_price = wps_wsp_get_meta_data( $product_id, 'wps_wsp_onetime_price', true );
					$cart_item['data']->set_price( $wps_wsp_onetime_price );
					$cart_item['data']->set_regular_price( $wps_wsp_onetime_price );
				}
			}
		}
	}

	/**
	 * Show the price html for same variation price
	 *
	 * @param bool   $bool as bool.
	 * @param object $variable_product as variable product.
	 * @param object $variation as variation.
	 * @return bool
	 */
	public function wps_wsp_woocommerce_show_variation_price( $bool, $variable_product, $variation ) {
		if ( ! $bool && function_exists( 'wps_sfw_check_product_is_subscription' ) && wps_sfw_check_product_is_subscription( $variation ) ) {
			return true;
		}
		return $bool;
	}


	/**
	 * Wps_wsp_show_related_subscription_on_order function.
	 *
	 * @param object $order as variable order.
	 */
	public function wps_wsp_show_related_subscription_on_order( $order ) {

		$order_id = $order->get_id();

		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$args = array(
				'return'  => 'ids',
				'post_type'   => 'wps_subscriptions',
				'meta_query' => array(
					array(
						'key'   => 'wps_parent_order',
						'value' => $order_id,
					),
				),
			);
			$wps_subscriptions = wc_get_orders( $args );
		} else {
			$args = array(
				'numberposts' => -1,
				'post_type'   => 'wps_subscriptions',
				'post_status' => 'wc-wps_renewal',
				'meta_query' => array(
					array(
						'key'   => 'wps_parent_order',
						'value' => $order_id,
					),
				),
			);
			$wps_subscriptions = get_posts( $args );
		}
		$wps_subscription_id = wps_wsp_get_meta_data( $order_id, 'wps_subscription_id', true );

		$switch_plan = wps_wsp_get_meta_data( $order_id, 'wps_upgrade_downgrade_order', true );

		if ( wps_sfw_check_valid_subscription( $wps_subscription_id ) && 'yes' !== $switch_plan ) {
			?>

			<header>
				<h2><?php esc_html_e( 'Related subscriptions', 'woocommerce-subscriptions-pro' ); ?></h2>
			</header>

			<table class="shop_table shop_table_responsive my_account_orders woocommerce-orders-table woocommerce-MyAccount-subscriptions woocommerce-orders-table--subscriptions">
					<thead>
						<tr>
							<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr"><?php esc_html_e( 'ID', 'woocommerce-subscriptions-pro' ); ?></span></th>
							<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr"><?php esc_html_e( 'Status', 'woocommerce-subscriptions-pro' ); ?></span></th>
							<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date"><span class="nobr"><?php echo esc_html_e( 'Next payment date', 'woocommerce-subscriptions-pro' ); ?></span></th>
							<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr"><?php echo esc_html_e( 'Recurring Total', 'woocommerce-subscriptions-pro' ); ?></span></th>
							<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-actions"><?php esc_html_e( 'Action', 'woocommerce-subscriptions-pro' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php

						foreach ( $wps_subscriptions as $key => $wps_subscription ) {
							if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
								$subcription_id = $wps_subscription;
								$susbcription = new WPS_Subscription( $subcription_id );
							} else {
								$subcription_id = $wps_subscription->ID;
								$susbcription = wc_get_order( $subcription_id );
							}
							$parent_order_id   = wps_wsp_get_meta_data( $subcription_id, 'wps_parent_order', true );
							$wps_subscription_status = wps_wsp_get_meta_data( $subcription_id, 'wps_subscription_status', true );
							$wps_next_payment_date = wps_wsp_get_meta_data( $subcription_id, 'wps_next_payment_date', true );
							$wps_show_recurring_total = wps_wsp_get_meta_data( $subcription_id, 'wps_show_recurring_total', true );

							$wps_wsfw_is_order = false;
							if ( function_exists( 'wps_sfw_check_valid_order' ) && ! wps_sfw_check_valid_order( $parent_order_id ) ) {
								$wps_wsfw_is_order = apply_filters( 'wps_wsfw_check_parent_order', $wps_wsfw_is_order, $parent_order_id );
								if ( false == $wps_wsfw_is_order ) {
									continue;
								}
							}

							?>
							<tr class="wps_sfw_account_row woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
									<td class="wps_sfw_account_col woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number">
								<?php echo esc_html( $subcription_id ); ?>
									</td>
									<td class="wps_sfw_account_col woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status">
								<?php
									$wps_subscription_status = wps_wsp_get_meta_data( $subcription_id, 'wps_subscription_status', true );
									echo esc_html( $wps_subscription_status );
								?>
									</td>
									<td class="wps_sfw_account_col woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date">
								<?php
								$wps_next_payment_date = wps_wsp_get_meta_data( $subcription_id, 'wps_next_payment_date', true );
								if ( 'cancelled' === $wps_subscription_status ) {
									$wps_next_payment_date = '';
								}
									echo esc_html( wps_sfw_get_the_wordpress_date_format( $wps_next_payment_date ) );
								?>
									</td>
									<td class="wps_sfw_account_col woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total">
									<?php
									echo wp_kses_post( wc_price( $susbcription->get_total() ) );
									?>
									</td>
									<td class="wps_sfw_account_col woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions">
										<span class="wps_sfw_account_show_subscription">
											<a href="
									<?php
									echo esc_url( wc_get_endpoint_url( 'show-subscription', $subcription_id, wc_get_page_permalink( 'myaccount' ) ) );
									?>
											">
									<?php
									esc_html_e( 'Show', 'woocommerce-subscriptions-pro' );
									?>
											</a>
										</span>
									</td>
								</tr>
								<?php } ?>
								<tbody>
							</table>
								<?php

		}
	}


	/**
	 * Wps_wsp_show_renewal_order_for_customer function
	 * b
	 *
	 * @param array $wps_subscription_id as subscription id.
	 * @return void
	 */
	public function wps_wsp_show_renewal_order_for_customer( $wps_subscription_id ) {
		$wps_wsp_renewal_order_data = wps_wsp_get_meta_data( $wps_subscription_id, 'wps_wsp_renewal_order_data', true );
		if ( is_array( $wps_wsp_renewal_order_data ) && ! empty( $wps_wsp_renewal_order_data ) ) {
			?>
			<div class="wps_sfw_account_additional_wrap wps_sfw_account_renewal_wrap">
				<h3><?php esc_html_e( 'Renewals', 'woocommerce-subscriptions-pro' ); ?></h3>
				<table class="shop_table wps_sfw_order_details">
						<thead>
							<tr>
								<th>
									<?php esc_html_e( 'ID', 'woocommerce-subscriptions-pro' ); ?>
								</th>
								<th>
									<?php esc_html_e( 'Status', 'woocommerce-subscriptions-pro' ); ?>
								</th>
								<th>
									<?php esc_html_e( 'Date', 'woocommerce-subscriptions-pro' ); ?>
								</th>
								<th>
									<?php esc_html_e( 'Total', 'woocommerce-subscriptions-pro' ); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							
							<?php

							foreach ( $wps_wsp_renewal_order_data as $key => $order_id ) {
								?>
								<tr> 
								<?php
								$order = wc_get_order( $order_id );
								if ( $order ) {
									$order_timestamp = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : '';
									$order_total = $order->get_formatted_order_total();
									$order_status = $order->get_status();
									?>
									<td><a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
									<?php echo '#' . esc_html( $order_id ); ?>
										</a>
									</td>
									<td><?php echo esc_html( $formatted = ucwords( str_replace( '_', ' ', $order_status ) ) ); ?></td>
									<td><?php echo esc_html( wps_sfw_get_the_wordpress_date_format( $order_timestamp ) ); ?></td>
									<td><?php echo wp_kses_post( $order_total ); ?></td>
									<?php
								}
								?>
								<tr>
									<?php
							}
							?>
						</tbody>
				</table>
			</div>
			<?php
		}
	}


	/**
	 * This function used to save the numner of time, the particular product has been cancelled before the trial period ended.
	 *
	 * @param int $subscription_id .
	 * @param int $user_id .
	 */
	public function wps_wsp_restrict_customer_to_cancel_before_trial_ended( int $subscription_id, $user_id ) {

		$product_id = wps_wsp_get_meta_data( $subscription_id, 'product_id', true );

		$renewal_data = wps_wsp_get_meta_data( $subscription_id, 'wps_wsp_renewal_order_data', true );
		$freq_free_trial = wps_wsp_get_meta_data( $subscription_id, 'wps_sfw_subscription_free_trial_number', true );

		if ( empty( $renewal_data ) && ! empty( $freq_free_trial ) ) {
			$cancel_product_data = get_user_meta( $user_id, 'wps_wsp_cancel_before_trial_ended_data', true ) ? get_user_meta( $user_id, 'wps_wsp_cancel_before_trial_ended_data', true ) : array();
			$cancel_ended_count_total = 1;
			if ( isset( $cancel_product_data[ $product_id ] ) ) {
				$cancel_ended_count_total = $cancel_product_data[ $product_id ] + 1;
			}
			$cancel_product_data[ $product_id ] = $cancel_ended_count_total;
			update_user_meta( $user_id, 'wps_wsp_cancel_before_trial_ended_data', $cancel_product_data );
		}
	}

	/**
	 * This function allow to visible or hide the cancel button for the customer.
	 *
	 * @param int   $default as deafult.
	 * @param array $subscription_id as subscription id.
	 */
	public function wps_wsp_customer_cancel_button_callback( $default, $subscription_id ) {

		$wps_product_id = wps_wsp_get_meta_data( $subscription_id, 'product_id', true );

		$checkbox_trial_end  = wps_wsp_get_meta_data( $wps_product_id, 'wps_sfw_free_trails_limit_checkbox', true );
		$get_trial_end_limit = wps_wsp_get_meta_data( $wps_product_id, 'wps_wsp_limt_for_free_trial', true );
		if ( 'on' === $checkbox_trial_end && ! empty( $get_trial_end_limit ) ) {
			$customer_id = wps_wsp_get_meta_data( $subscription_id, 'wps_customer_id', true );
			$get_saved_cancel_data = get_user_meta( $customer_id, 'wps_wsp_cancel_before_trial_ended_data', true );
			if ( isset( $get_saved_cancel_data[ $wps_product_id ] ) ) {
				$cancel_count = $get_saved_cancel_data[ $wps_product_id ];
				if ( $cancel_count >= $get_trial_end_limit ) {
					$default = 'no';
				}
			}
		}
		$after_days = get_option( 'wsp_time_duration_subscription_cancellation' );
		if ( 'on' == get_option( 'wsp_allow_time_subscription_cancellation' ) && $after_days ) {
			$parent_id = wps_wsp_get_meta_data( $subscription_id, 'wps_parent_order', true );
			$parent_order = wc_get_order( $parent_id );
			if ( is_object( $parent_order ) && ! empty( $parent_order ) ) {

				$order_date = $parent_order->get_date_created();

				// Get the current date.
				$current_date = new DateTime();

				// Calculate the difference in days.
				$date_difference = $current_date->diff( $order_date )->days;

				if ( $date_difference < $after_days ) {
					$default = 'no';
				}
			}
		}
		return $default;
	}

	/**
	 * Function to add cart item meta data.
	 *
	 * @param array $data .
	 * @param array $cart_item .
	 * @return array
	 */
	public function wps_wsp_get_subscription_meta_on_cart( $data = array(), $cart_item = array() ) {
		if ( isset( $cart_item['wps_upgrade_downgrade_data'] ) ) {
			$wps_subscription_id = $cart_item['wps_upgrade_downgrade_data']['wps_subscription_id'];
			if ( ! wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
				return $product_subtotal;
			}
			$wps_switch_type = $this->wps_wsp_get_upgrade_downgrade_type( $wps_subscription_id, $cart_item );
			if ( ! empty( $wps_switch_type ) ) {

				if ( 'upgrade' == $wps_switch_type ) {
					$wps_switch_type = __( 'Upgrade', 'woocommerce-subscriptions-pro' );
				} else {
					$wps_switch_type = __( 'Downgrade', 'woocommerce-subscriptions-pro' );
				}
				$data[] = array(
					'name'   => 'wps-wsp-switch-direction',
					'hidden' => true,
					'value'  => html_entity_decode( '<span class="subscription-switch-direction">(' . $wps_switch_type . ')</span>' ),
				);
			}
		}
		return $data;
	}
	/**
	 * Calculating correct recurring price.
	 *
	 * @param integer $price .
	 * @param array() $cart_data .
	 * @param bool    $bool will decide to show or create subscription.
	 */
	public function wps_sfw_manage_line_total_for_plan_switch_callback( $price, $cart_data, $bool ) {
		// For the upgrade/downgrade process.
		$product_id   = $cart_data['data']->get_id();
		if ( $this->wps_wsp_check_cart_has_switch_subscription( $cart_data ) ) {
			$wps_subscription_id = $cart_data['wps_upgrade_downgrade_data']['wps_subscription_id'];

			if ( ! wps_sfw_check_valid_subscription( $wps_subscription_id ) ) {
				return;
			}
			$price = wc_get_product( $product_id )->get_price() * $cart_data['quantity'];
			return $price;
		}

		// Manage prorate price for the recurring.
		if ( wps_wsp_start_susbcription_from_certain_date_of_month() && wps_wsp_subscription_syn_enable_per_product( $product_id ) ) {
			$price = wc_get_product( $product_id )->get_price() * $cart_data['quantity'];

			return $price;
		}

		return $price;
	}

	/**
	 * Remove the subscription html price for WC Cart block.
	 *
	 * @param string $price .
	 * @param int    $product_id .
	 * @param array  $cart_item .
	 *
	 * @return $price
	 */
	public function wps_sfw_show_one_time_subscription_price_block_callback( $price, $product_id, $cart_item ) {
		if ( isset( $cart_item['wps_type_selection'] ) && 'one_time' === $cart_item['wps_type_selection'] ) {
			return null;
		}
		return $price;
	}

	/**
	 * Add the shipping fee in the final total.
	 *
	 * @param integer $data .
	 * @param array() $cart_data .
	 * @param bool    $bool will decide to show or create subscription .
	 */
	public function wps_sfw_add_shipping_fee_for_display_callack( $data, $cart_data, $bool ) {
		// Add the shipping fee if enabled.
		$allow_shipping = get_option( 'wsp_allow_shipping_subscription', false );

		if ( 'on' === $allow_shipping && $bool ) {
			$data['shipping_fee'] = WC()->cart->get_shipping_total() + WC()->cart->get_shipping_tax();
		}
		return $data;
	}

	/**
	 * Prorate price tooltip
	 *
	 * @param integer $price .
	 * @param array() $cart_item .
	 * @param string  $cart_item_key .
	 */
	public function woocommerce_cart_item_prorate_subtotal( $price, $cart_item, $cart_item_key ) {

		$product_id = $cart_item['data']->get_id();
		if ( wps_wsp_start_susbcription_from_certain_date_of_month() && wps_wsp_subscription_syn_enable_per_product( $product_id ) ) {
			$wps_wsp_prorate_type = wsp_get_prorate_price_on_sync_enable();

			if ( 'wps_wsp_prorate_simple' == $wps_wsp_prorate_type || 'wps_wsp_prorate_if_free_trial' == $wps_wsp_prorate_type ) {
				$prorate_tooltip_text = esc_html__( 'Prorate pricing adjusts the price of a subscription depending on when it started and when it will be renewed.', 'woocommerce-subscriptions-pro' );
				return $price . '<span class="wps_prorate_price_tooltip" title="' . $prorate_tooltip_text . '"></span>';
			}
		}
		return $price;
	}

	/**
	 * Update payment method for subscription
	 *
	 * @param mixed $source_object_id .
	 * @param mixed $source_object .
	 * @return void
	 */
	public function wps_sfw_update_payment_method_for_subscription( $source_object_id, $source_object ) {
		if ( isset( $_REQUEST['wps_subscription_id'] ) && $source_object_id ) {
			$subscription_id = isset( $_REQUEST['wps_subscription_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wps_subscription_id'] ) ) : 0;
			$parent_id       = wps_wsp_get_meta_data( $subscription_id, 'wps_parent_order', true );
			if ( $parent_id ) {
				wps_wsp_update_meta_data( $parent_id, '_stripe_source_id', $source_object_id );
				$parent_order = wc_get_order( $parent_id );
				// translators: payment method id.
				$parent_order->add_order_note( sprintf( esc_attr__( 'Payment method %s updated', 'woocommerce-subscriptions-pro' ), $source_object_id ) );
			}
		}
	}

	/**
	 * Display a notice
	 *
	 * @param mixed $id .
	 * @return void
	 */
	public function wps_sfw_display_a_notice( $id ) {
		if ( isset( $_REQUEST['wps_subscription_id'] ) && in_array( $id, array( 'stripe', 'stripe_sepa' ) ) ) {
			$subscription_id = isset( $_REQUEST['wps_subscription_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wps_subscription_id'] ) ) : 0;
			?>
			<p>
			<?php
				// translators: subscription id.
				echo esc_attr( sprintf( esc_attr__( 'This will update the Payment Method used for the subscription #%s.', 'woocommerce-subscriptions-pro' ), $subscription_id ) );
			?>
			</p>
			<?php
		}
	}

	/**
	 * Function to remove the enable paypal standard setting from the general setting.
	 *
	 * @param mixed $general_settings .
	 * @return mixed
	 */
	public function wps_wsp_add_general_settings_fields_callback( $general_settings ) {
		if ( isset( $general_settings[5] ) && isset( $general_settings[5]['id'] ) && 'wps_sfw_enable_paypal_standard' === $general_settings[5]['id'] ) {
			if ( function_exists( 'wps_wsp_disable_discontinued_paypal' ) && ! wps_wsp_disable_discontinued_paypal( 'wps_paypal' ) ) {
				unset( $general_settings[5] );
			}
		}
		return $general_settings;
	}

	/**
	 * Function to disable the discontinued paypal, if there is no subscription exist with these payment methods.
	 *
	 * @param mixed $methods .
	 * @return mixed
	 */
	public function wps_wsp_remove_discontinued_paypal( $methods ) {
		if ( function_exists( 'wps_wsp_disable_discontinued_paypal' ) ) {
			if ( ! wps_wsp_disable_discontinued_paypal( 'wps_paypal' ) ) {
				$key = array_search( 'WC_Gateway_Wps_Paypal_Integration', $methods );
				unset( $methods[ $key ] );
			}
			if ( ! wps_wsp_disable_discontinued_paypal( 'wps_paypal_subscription' ) ) {
				$key = array_search( 'WPS_Paypal_Subscription_Integration_Gateway', $methods );
				unset( $methods[ $key ] );
			}
		}
		return $methods;
	}

	/**
	 * This function is used to support wallet payment for subscription.
	 *
	 * @name wps_wsp_wallet_payment_gateway_for_subscription
	 * @param array  $supported_payment_method supported_payment_method.
	 * @param string $payment_method payment_method.
	 * @since    1.0.0
	 */
	public function wps_wsp_wallet_payment_gateway_for_subscription( $supported_payment_method, $payment_method ) {

		$user_id        = get_current_user_id();
		$wallet_amount  = get_user_meta( $user_id, 'wps_wallet', true );

		$wps_cart_total = WC()->cart->get_total( 'edit' );

		$cart_fee = WC()->cart->get_fee_total();

		$wps_cart_total = intval( $wps_cart_total ) + abs( $cart_fee );

		// wallet compatbility.
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( is_plugin_active( 'wallet-system-for-woocommerce/wallet-system-for-woocommerce.php' ) ) {
			$plug = get_plugins();
			if ( isset( $plug['wallet-system-for-woocommerce/wallet-system-for-woocommerce.php'] ) ) {
				if ( $plug['wallet-system-for-woocommerce/wallet-system-for-woocommerce.php']['Version'] > '2.5.16' ) {
					if ( $wallet_amount >= $wps_cart_total ) {

						if ( 'wps_wcb_wallet_payment_gateway' == $payment_method ) {
							$supported_payment_method[] = $payment_method;
						}
					}
				}
			}
		}
		// wallet compatbility.
		return $supported_payment_method;
	}

	/**
	 * Show the product course description for the variation product.
	 *
	 * @param mixed $product .
	 */
	public function wps_sfw_variable_course_description( $product ) {
		if ( $product->is_type( 'variable' ) ) {
			foreach ( $product->get_children() as $product_id ) {
				$saved_courses = get_post_meta( $product_id, 'wps_learnpress_course', true ) ? get_post_meta( $product_id, 'wps_learnpress_course', true ) : array();
				$course_name = null;
				if ( ! empty( $saved_courses ) ) {
					foreach ( $saved_courses as $course_id ) {
						$course        = learn_press_get_course( $course_id );
						$course_name[] = $course->get_title();
					}
					?>
						<div class="wps-product-notice">
						<?php
							/* translators: %s: variation product name */
							echo esc_attr( sprintf( esc_attr__( '%s will be including', 'subscription-for-woocommerce' ), wc_get_product( $product_id )->get_name() ) );
						?>
							<?php
							echo esc_attr( implode( ', ', $course_name ) );
							?>
						</div>
					<?php
				}
			}
		}
	}
}
