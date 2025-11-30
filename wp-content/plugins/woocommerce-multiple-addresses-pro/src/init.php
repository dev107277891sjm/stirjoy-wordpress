<?php
/**
 * The instantiate functionality of the plugin.
 *
 * @package    woocommerce-multiple-addresses-pro
 * @subpackage woocommerce-multiple-addresses-pro/src
 *
 * @link  https://themehigh.com
 * @since 1.0.0.0
 */

namespace Themehigh\WoocommerceMultipleAddressesPro;

 /**
  * Instantiate the plugin services class
 *
 * @package woocommerce-multiple-addresses-pro
 * @link    https://themehigh.com
 */
final class Init
{

    /**
     * Store all the classes inside an array
     *
     * @return array full list of classes
     */
    public static function get_services()
    {

        return [
            includes\THWMA_Base::class,
            admin\THWMA_Admin::class,
            thpublic\THWMA_Public::class,
            thpublic\THWMA_Public_shipping::class,
            thpublic\THWMA_Public_billing::class,
            thpublic\THWMA_Public_MyAccount::class,
        ];
    }

    /**
    * Loop through the classes,initialize them,
    * and call the register() method if it exists
    *
    * @return void
    */
    public static function register_services()
    {
        foreach (self::get_services() as $class) {
            $service = self::_instantiate($class);
            if (method_exists($service, 'register')) {
                $service->register();
            }
        }
    }

    /**
    * Initialize the class
    *
    * @param class $class class from the services array
    *
    * @return class instance new instance of the class
    */
    private static function _instantiate($class)
    {
        // if ($class!='Inc\Actions\CompareWidget') {
            $service = new $class();
            return $service;
        // }
    }
}
