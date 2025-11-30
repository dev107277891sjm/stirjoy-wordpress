<?php
/**
 * The subscription module.
 *
 *  @package Woocommerce_Subscriptions_Pro/includes
 */

declare( strict_types = 1 );

use WooCommerce\PayPalCommerce\Vendor\Dhii\Container\ServiceProvider;
use WooCommerce\PayPalCommerce\Vendor\Dhii\Modular\Module\ModuleInterface;
use Psr\Log\LoggerInterface;
use WooCommerce\PayPalCommerce\ApiClient\Exception\RuntimeException;
use WooCommerce\PayPalCommerce\Vaulting\PaymentTokenRepository;
use WooCommerce\PayPalCommerce\WcGateway\Gateway\PayPalGateway;
use WooCommerce\PayPalCommerce\WcGateway\Gateway\CreditCardGateway;
use WooCommerce\PayPalCommerce\Vendor\Interop\Container\ServiceProviderInterface;
use WooCommerce\PayPalCommerce\Vendor\Psr\Container\ContainerInterface;

/**
 * Class WPS_WC_PayPal_Payments_Module
 */
class WPS_WC_PayPal_Payments_Module implements ModuleInterface {

	/**
	 * {@inheritDoc}
	 */
	public function setup(): ServiceProviderInterface {
		return new ServiceProvider(
			array(
				'wc-subscriptions.helper'                => static function ( ContainerInterface $container ): WPS_WC_PayPal_Payments_Helper {
					return new WPS_WC_PayPal_Payments_Helper();
				},
				'wps-subscription.renewal-handler'     => static function ( ContainerInterface $container ): WPS_WC_PayPal_Payments_Renewal_Handler {
					return new WPS_WC_PayPal_Payments_Renewal_Handler(
						$container->get( 'woocommerce.logger.woocommerce' ),
						$container->get( 'vaulting.repository.payment-token' ),
						$container->get( 'api.endpoint.order' ),
						$container->get( 'api.factory.purchase-unit' ),
						$container->get( 'api.factory.shipping-preference' ),
						$container->get( 'api.factory.payer' ),
						$container->get( 'onboarding.environment' ),
						$container->get( 'wcgateway.settings' ),
						$container->get( 'wcgateway.processor.authorized-payments' ),
						$container->get( 'wcgateway.funding-source.renderer' ),
						$container->get( 'wc-subscriptions.helpers.real-time-account-updater' ),
						$container->get( 'wc-subscriptions.helper' )
					);
				},
			),
			array()
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function run( ContainerInterface $c ): void {

	 add_action(
			'wps_sfw_other_payment_gateway_renewal',
			/**
			 * Pay the Renewal Order.
			 *
			 * @param object  $order .
			 * @param string $subscription_id .
			 * @param string $payment_method .
			 *
			 * @return void
			 */
			function ( $order, $subscription_id, $payment_method ) use ( $c ) {
				if ( ! in_array( $payment_method, array( PayPalGateway::ID, CreditCardGateway::ID ), true ) ) {
					return;
				}
				$order->update_status( 'pending' );
				$renewal_handler = $c->get( 'wps-subscription.renewal-handler' );
				$renewal_handler->renew( $order );
			},
			10,
			3
		);

		add_action(
			'wps_sfw_order_status_changed',
			/**
			 * Manage the payment token id for the subscription.
			 */
			function ( $order_id, $subscription_id ) use ( $c ) {
                $subscription = new WPS_Subscription( $subscription_id );
				// Double check subscription payment method.
				if ( ! in_array( $subscription->get_payment_method(), array( PayPalGateway::ID, CreditCardGateway::ID ), true ) ) {
					return;
				}
				if (!$c->has('save-payment-methods.eligible') || !$c->get('save-payment-methods.eligible')) {
					return;
				}
				$payment_token_repository = $c->get( 'vaulting.repository.payment-token' );
				$logger                   = $c->get( 'woocommerce.logger.woocommerce' );
				$this->add_payment_token_id( $subscription, $payment_token_repository, $logger );
			}, 11, 2
		);

		$this->maybe_remove_action_scheduler_filter();
	}

	/**
	 * Adds Payment token ID to subscription.
	 *
	 * @param \WPS_Subscription    $subscription             The subscription.
	 * @param PaymentTokenRepository $payment_token_repository The payment repository.
	 * @param LoggerInterface        $logger                   The logger.
	 */
	protected function add_payment_token_id( \WPS_Subscription $subscription, PaymentTokenRepository $payment_token_repository, LoggerInterface $logger ) {
		try {
			$tokens = $payment_token_repository->all_for_user_id( $subscription->get_user_id() );
			if ( $tokens ) {
				$latest_token_id = end( $tokens )->id() ? end( $tokens )->id() : '';
				$subscription->update_meta_data( 'payment_token_id', $latest_token_id );
                $subscription->save();
			}
		} catch ( RuntimeException $error ) {
			$message = sprintf(
			// translators: %1$s is the payment token Id, %2$s is the error message.
				__(
					'Could not add token Id to subscription %1$s: %2$s',
					'woocommerce-subscriptions-pro'
				),
				$subscription->get_id(),
				$error->getMessage()
			);

			$logger->log( 'warning', $message );
		}
	}

	/**
	 * Remove filter action_scheduler_before_execute added from WooCommerce\PayPalCommerce\Subscription\SubscriptionModule
	 * to avoid errors with subscription renew process.
	 *
	 * @return void
	 */
	protected function maybe_remove_action_scheduler_filter() {
		global $wp_filter;

		if ( empty( $wp_filter['action_scheduler_before_execute'] ) || ! class_exists( 'ReflectionFunction' ) ) {
			return;
		}

		foreach ( $wp_filter['action_scheduler_before_execute'] as $priority => $callbacks ) {
			foreach ( $callbacks as $id => $callback ) {
				if ( empty( $callback['function'] ) || ! is_object( $callback['function'] ) ) {
					continue;
				}

				$function = new ReflectionFunction( $callback['function'] );
				if (
					$function->getClosureThis() instanceof WooCommerce\PayPalCommerce\Subscription\SubscriptionModule ||
					$function->getClosureThis() instanceof WooCommerce\PayPalCommerce\PayPalSubscriptions\PayPalSubscriptionsModule
				) {
					unset( $wp_filter['action_scheduler_before_execute']->callbacks[ $priority ][ $id ] );
				}
			}
		}
	}
}
