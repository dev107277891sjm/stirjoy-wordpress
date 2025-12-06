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
define( 'AUTH_KEY',         '7Bfk6[O-cToREg5C1<ae&4z$h_~_:gERn8?=qJ:^3!X458FEtwu(uBV.Go/0Q<MJ' );
define( 'SECURE_AUTH_KEY',  'A4M%ziW4rF.3wSz;!Ar1Os&1F>TunBQ.~}Qi4 6pN7Jn+eFBg5gWXeGEPx]zcGKa' );
define( 'LOGGED_IN_KEY',    'UGrymV[jP;kPFphW9_!<RoHs8QH^gx>c-(Z5q$|iQ}zt4FZC>+J7rh!F_rYq|Doh' );
define( 'NONCE_KEY',        'dCegoA~=#AYK=EOd 4|3axJVzmp|[Q=f`V?YpV.Riq5hAB}#Seu_ohyl-ZmY@Uwi' );
define( 'AUTH_SALT',        'mufip9(ItZ7QB_om>Lols<.=N<xEALo)3(s,vS{`g1qx?U1>D(Hq:YQG5Z8!>eSG' );
define( 'SECURE_AUTH_SALT', 'k_7]{>2Kg$#3l9L|0&`+?o9A4Xohy{3;j;V/)9f|h@-py}FwQRAx%l.#OHcAbc],' );
define( 'LOGGED_IN_SALT',   '`7mtp[Ykhdr@<!8<Vn`4!F?R|C1,$mdOL;+dkqoeWiUNg742!-3S6PLPPa?a=bRu' );
define( 'NONCE_SALT',       'K~b=}h]g11se/lIZKr$Oa8NtWc*?GEHX<1*Y/RDC{<_ItJ>3nf$tM|OFWu!Ex19P' );

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
