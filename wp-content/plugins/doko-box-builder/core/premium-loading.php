<?php

if ( !function_exists( 'doko_fs' ) ) {
    // Create a helper function for easy SDK access.
    function doko_fs() {
        global $doko_fs;
        if ( !isset( $doko_fs ) ) {
            // // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_10692_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_10692_MULTISITE', true );
            }
            // Include Freemius SDK.
            require_once __DIR__ . '/vendor/freemius/start.php';
            $doko_fs = fs_dynamic_init( array(
                'id'                  => '10692',
                'slug'                => 'doko-box-builder',
                'premium_slug'        => 'doko-premium',
                'type'                => 'plugin',
                'public_key'          => 'pk_465743b2659e2191bf94d88f7c839',
                'is_premium'          => false,
                'premium_suffix'      => 'pro',
                'has_addons'          => true,
                'has_paid_plans'      => true,
                'menu'                => array(
                    'slug' => 'edit.php?post_type=doko-bundles',
                ),
                'parallel_activation' => [
                    'enabled'                  => true,
                    'premium_version_basename' => 'doko-premium/hs-doko.php',
                ],
                'is_live'             => true,
            ) );
        }
        return $doko_fs;
    }

    // Init Freemius.
    doko_fs();
    // Signal that SDK was initiated.
    do_action( 'doko_fs_loaded' );
}