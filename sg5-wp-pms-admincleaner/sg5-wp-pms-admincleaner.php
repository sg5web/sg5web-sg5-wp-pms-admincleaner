<?php
/**
 * Plugin Name: SG5 PMS WP Admin Cleaner
 * Plugin URI:  https://sg5.biz/
 * Description: Optimize the WordPress Admin panel with one click.
 * Author: SG5 Digital Solution
 * Author URI: https://sg5.biz/
 * Text Domain: dpwac
 * Version: 1.8.0
 * License: GNU General Public License v2.0 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
defined( 'ABSPATH' ) or die( 'Move Along, Nothing to See Here' );

// DP_AC Plugin const
define( 'DPLUGINS_AC_STORE_URL', 'https://dplugins.com/' );
define( 'DPLUGINS_AC_ITEM_ID', 17364 );
define( 'DPLUGINS_AC_ITEM_NAME',  'WP Admin Cleaner' );
define( 'DPLUGINS_AC_ADMIN_SLUG', 'dplugins_admin_cleaner' );

define( 'DP_AC_AUTHOR', 'devusrmk' );
define( 'DP_AC_PLUGINVERSION',  '1.8.0' );


// Plugin const
define('DP_AC_UPDATER', __FILE__);
define('DP_AC_BASE', plugin_basename(__FILE__));
define('DP_AC_URL',	plugin_dir_url(__FILE__));
define('DP_AC_DIR',	plugin_dir_path(__FILE__));

// Load Admin
if( !class_exists( 'DP_AC_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/admin/edd/DP_AC_SL_Plugin_Updater.php' );
}

require_once DP_AC_DIR . 'admin/admin.php';
$DP_AC_al_option = get_option('DP_AC_al_option');
if($DP_AC_al_option == "yes"){
	require_once DP_AC_DIR . 'admin/adminLogin.php';
}
require_once DP_AC_DIR . 'admin/admin__license.php';

function DP_AC_DP_AC_plugin_updater() {

	// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
	if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
		return;
	}

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'DP_AC_license_key' ) );

	// setup the updater
	$edd_updater = new DP_AC_SL_Plugin_Updater( DPLUGINS_AC_STORE_URL, DP_AC_UPDATER,
		array(
			'version' => DP_AC_PLUGINVERSION,                    // current version number
			'license' => $license_key,             // license key (used get_option above to retrieve from DB)
			'item_id' => DPLUGINS_AC_ITEM_ID,       // ID of the product
			'author'  => DP_AC_AUTHOR, // author of this plugin
			'beta'    => false,
		)
	);
}
//add_action( 'init', 'DP_AC_DP_AC_plugin_updater' );

/*================================================
=            Clean up After Uninstall            =
================================================*/

function DP_AC_delete_plugin_database(){
	global $wpdb;
	$DP_AC_remove_data = get_option('DP_AC_remove_data');
	if ($DP_AC_remove_data == "yes") {
		// options table
		$options_table = $wpdb->prefix . 'options';
		$wpdb->query("DELETE FROM $options_table WHERE option_name LIKE '%DP_AC%'");
		
		// usermeta
		$usermeta_table = $wpdb->prefix . 'usermeta';
		$wpdb->query("DELETE FROM $usermeta_table WHERE meta_key LIKE '%DP_AC%'");
		
		// custom menus table
		$dp_ac_custom_menus_table = $wpdb->prefix . "dp_ac_custom_menus";
		$wpdb->query("DELETE FROM $dp_ac_custom_menus_table");
	}
}
register_uninstall_hook(__FILE__, 'DP_AC_delete_plugin_database');
