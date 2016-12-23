<?php


define( 'ICY_WCSI__STORE_URL', 'https://icywordpress.com' );


define( 'ICY_WCSI__ITEM_NAME', 'WooCommerce Shoppable Instagram' );

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

function edd_sl_sample_plugin_updater() {

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'icy_instagram_license' ) );

	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( ICY_WCSI__STORE_URL, __FILE__, array(
			'version' 	=> ICYIG_VER, 				// current version number
			'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
			'item_name' => ICY_WCSI__ITEM_NAME, 	// name of this plugin
			'author' 	=> 'David Jensen'  // author of this plugin
		)
	);

}
add_action( 'admin_init', 'edd_sl_sample_plugin_updater', 0 );




/************************************
* this illustrates how to activate
* a license key
*************************************/

function ICY_WCSI__activate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['edd_license_activate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'ICY_WCSI__nonce', 'ICY_WCSI__nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'icy_instagram_license' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $license,
			'item_name' => urlencode( ICY_WCSI__ITEM_NAME ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( ICY_WCSI__STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "valid" or "invalid"

		update_option( 'ICY_WCSI__license_status', $license_data->license );

	}
}
add_action('admin_init', 'ICY_WCSI__activate_license');


/***********************************************
* Illustrates how to deactivate a license key.
* This will descrease the site count
***********************************************/

function ICY_WCSI__deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['edd_license_deactivate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'ICY_WCSI__nonce', 'ICY_WCSI__nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'icy_instagram_license' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license' 	=> $license,
			'item_name' => urlencode( ICY_WCSI__ITEM_NAME ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( ICY_WCSI__STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' )
			delete_option( 'ICY_WCSI__license_status' );

	}
}
add_action('admin_init', 'ICY_WCSI__deactivate_license');