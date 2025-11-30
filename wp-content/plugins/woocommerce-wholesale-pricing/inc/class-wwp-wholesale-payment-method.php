<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WWP_Wholesale_Payment_Method' ) ) {

	/**
	 * Wholesale Custom Payment Method Class.
	 */
	class WWP_Wholesale_Payment_Method extends WC_Payment_Gateway {
				
		/**
		 * Instructions
		 *
		 * @var string
		 */
		public $instructions = '';

		/**
		 * Constructor for the gateway.
		 *
		 * @param string $wwp_custom_gateway Gateway Name.
		 */
		public function __construct( $wholesale_custom_gateway ) { 
			
			$this->id           = strtolower( str_replace( ' ', '_', $wholesale_custom_gateway ) );
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$this->icon         = apply_filters( 'wwp_payment_gateway_icon', '' );
			$this->has_fields   = true;
			$this->method_title = $wholesale_custom_gateway;

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables.
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->instructions = $this->get_option( 'instructions', $this->description );

			// Actions.
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

			// Customer Emails.
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}

		/**
		 * Initialize Gateway Settings Form Fields.
		 */
		public function init_form_fields() {
		
			/**
			* Hooks
			*
			* @since 3.0
			*/
			$this->form_fields = apply_filters(
				'wwp_payment_form_fields',
				array(
					'enabled'     => array(
						'title'   => __( 'Enable/Disable', 'woocommerce-wholesale-pricing' ),
						'type'    => 'checkbox',
						'label'   => 'Enable ' . $this->method_title,
						'default' => 'yes',
					),
					'title'       => array(
						'title'       => __( 'Title', 'woocommerce-wholesale-pricing' ),
						'type'        => 'text',
						'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'woocommerce-wholesale-pricing' ),
						'default'     => $this->method_title,
						'desc_tip'    => true,
					),
					'description' => array(
						'title'       => __( 'Description', 'woocommerce-wholesale-pricing' ),
						'type'        => 'textarea',
						'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce-wholesale-pricing' ),
						'default'     => null,
						'desc_tip'    => true,
					),
				)
			);
		}

		/**
		 * Output for the order received page.
		 */
		public function thankyou_page() {
			if ( $this->instructions ) {
				echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
			}
		}

		/**
		 * Add content to the WC emails.
		 *
		 * @param WC_Order $order Order object.
		 * @param bool     $sent_to_admin Send email to admin.
		 * @param bool     $plain_text Plain text.
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {

			if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {
				echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) ) . PHP_EOL;
			}
		}

		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id Order Id.
		 * @return array
		 */
		public function process_payment( $order_id ) {

			$order = wc_get_order( $order_id );

			// Mark as on-hold (we're awaiting the payment).
			$order->update_status( 'processing', $this->method_title );

			// Reduce stock levels.
			$order->reduce_order_stock();

			// Remove cart.
			WC()->cart->empty_cart();
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	}
}

// add_filter( 'woocommerce_payment_gateways', 'wwp_woocommerce_payment_gateways' );

// function wwp_woocommerce_payment_gateways( $gateways ) {
//  $settings = get_option( 'wwp_wholesale_pricing_options', true );
//  if ( !isset($settings['enable_custom_payment_method']) || 'yes' != $settings['enable_custom_payment_method'] ) {
//      return $gateways;
//  }
//  if (  ! is_wholesaler_user( get_current_user_id() ) && !is_admin()  ) {
//      return $gateways;
//  }
//  $role = get_current_user_role_id();
//  if ($role) {  
//      $payment_method_name = get_term_meta($role, 'wwp_wholesale_payment_method_name', true);
		
//      //var_dump($settings['payment_method_name']);
//  } else {
//      $payment_method_name =  $settings['payment_method_name'];
//  }
	
//  foreach ( (array) $payment_method_name as  $key => $wholesale_custom_gateway ) {
//      if ( array_key_exists( $key, $settings['payment_method_name'] ) ) {
//          $class = 'WWP_Wholesale_Payment_Method';
//          if ( class_exists( $class ) ) { 
//              $wholesale_custom_gateway_id = strtolower( str_replace( ' ', '_', $wholesale_custom_gateway ) );
//              if (is_wholesaler_user( get_current_user_id() ) ) {
//                  if ( 'yes' == $wholesale_custom_gateway ) {
//                      $gateways[] = new $class( $key );
//                  }
//              } else {
//                  $gateways[] = new $class( $key );
//              } 
//          }
//      }
	
		
//  }
//  return $gateways;
// }
