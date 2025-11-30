<?php
namespace HS\Doko;
/**
 * Fired during plugin deactivation
 *
 * @link       https://homescriptone.com
 * @since      1.0.0
 *
 * @package    Hs_Doko
 * @subpackage Hs_Doko/core
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Hs_Doko
 * @subpackage Hs_Doko/core
 */
class Hs_Doko_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Not like register_uninstall_hook(), you do NOT have to use a static function.
		doko_fs()->add_action( 'after_uninstall', 'doko_fs_uninstall_cleanup' );
	}
}
