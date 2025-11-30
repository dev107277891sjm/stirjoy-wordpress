<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'g&_e,9DRWAMI*mO6svg 4HiGiUzR::j`YCHNZ,TV3Pg@TpfJ&^G<,JYgY_Ico(Se' );
define( 'SECURE_AUTH_KEY',  'qgQIDoWFX=OZ<dN9l*4l7>vWLlia=zW$A/aGo&/cvy3R8T0TRR<f9?TZ7*b5K/ZR' );
define( 'LOGGED_IN_KEY',    '_3,>^P3V}zk^t83I.o0Qh*nm5F?_Zlm)p=5GV3~a{5UuJk vG$SN[CcPC#B4[!<9' );
define( 'NONCE_KEY',        'x7-zI>a2N#a}J-W@e^`K<kJf9^a4wD]72m_{|I9Fm#es10x=%F,7qC|nxL+O=LDn' );
define( 'AUTH_SALT',        '7Dx#pWQM,0cav;wEXvcEue]xQ/GA9`[D~$EJ9<u><i7&ZOTc!D%kDc_)qyh[RT$Y' );
define( 'SECURE_AUTH_SALT', '8W|WGbA%I=T$B=_murE}DCa%;!?Xp[A,2(O45)Z+1iEZKXN54zIN+!$[8Se;u(R!' );
define( 'LOGGED_IN_SALT',   '+B`AP4])3-Vw2lgl=;|/j6N/T8,82)eHF:zp^yf{j30]{CW[JY9e[*PDO.:3nmWD' );
define( 'NONCE_SALT',       'd{yU3=`NC2382BUQu#rEdGL6+(f7;kZ~[NT6Wvu`6.y3TcCc4YaRm_l$|_^]XBeR' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
