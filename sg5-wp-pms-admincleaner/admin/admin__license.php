<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DP_AC_license {
	public function __construct(){
		add_action( 'admin_init', array($this, 'DP_AC_register_option') );
		add_action( 'admin_menu', array($this, 'DP_AC_activate_license') );
		add_action( 'admin_menu', array($this, 'DP_AC_deactivate_license') );
		add_action( 'admin_notices', array($this, 'DP_AC_admin_notices') );
	}

	public function DP_AC_register_option() {
		// creates our settings in the options table
		register_setting('DP_AC_license', 'DP_AC_license_key', 'DP_AC_edd_sanitize_license' );
	}

	public function DP_AC_edd_sanitize_license( $new ) {
		$old = get_option( 'DP_AC_license_key' );
		if( $old && $old != $new ) {
			delete_option( 'DP_AC_license_status' ); // new license has been entered, so must reactivate
		}
		return $new;
	}

	/************************************
	* Activate a license key
	*************************************/
	public function DP_AC_activate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST['DP_AC_license_activate'] ) ) {
			ob_start();
			// run a quick security check
		 	if( ! check_admin_referer( 'DP_AC_nonce', 'DP_AC_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( $_POST['DP_AC_license_key'] );
			update_option( 'DP_AC_license_key', $license );

			update_option( 'DP_AC_license_status', 'valid' );
			wp_redirect( admin_url( 'tools.php?page=' . DPLUGINS_AC_ADMIN_SLUG ) );
			exit();
		}
	}

	/***********************************************
	* Illustrates how to deactivate a license key.
	***********************************************/
	public function DP_AC_deactivate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST['DP_AC_license_deactivate'] ) ) {
			ob_start();
			// run a quick security check
		 	if( ! check_admin_referer( 'DP_AC_nonce', 'DP_AC_nonce' ) )
				return; // get out if we didn't click the Activate button

			delete_option( 'DP_AC_license_key' );
			delete_option( 'DP_AC_license_status' );

			wp_redirect( admin_url( 'tools.php?page=' . DPLUGINS_AC_ADMIN_SLUG ) );
			exit();

		}
	}

	/************************************
	* Check if a license key is still valid
	*************************************/

	public function DP_AC_check_license() {

		global $wp_version;

		$license = trim( get_option( 'DP_AC_license_key' ) );

		echo 'valid'; exit;
	}

	/**
	 * This is a means of catching errors from the activation method above and displaying it to the customer
	 */
	public function DP_AC_admin_notices() {
		if ( isset($_GET['DP_AC_sl_activation_nonce']) && isset( $_GET['DP_AC_sl_activation'] ) && ! empty( $_GET['message'] ) ) {
			$nonce = $_GET['DP_AC_sl_activation_nonce'];
			if ( !wp_verify_nonce($nonce, 'DP_AC-nonce') ) return;

			switch( $_GET['DP_AC_sl_activation'] ) {

				case 'false':
					$message = urldecode( sanitize_text_field( $_GET['message'] ) );
					?>
					<div class="error">
						<p><?php echo $message; ?></p>
					</div>
					<?php
					break;

				case 'true':
				default:
					// Developers can put a custom success message here for when activation is successful if they way.
					break;

			}
		}
	}
}

new DP_AC_license();
