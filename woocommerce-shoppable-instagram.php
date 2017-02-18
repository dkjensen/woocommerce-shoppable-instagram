<?php
/**
 * Plugin Name: WooCommerce Shoppable Instagram
 * Description: Instagram feed with product links
 * Version: 1.0.4
 * Author: Icy WordPress
 * Author URI: https://icywordpress.com
 * License: GPL2
 */


define( 'WCSI_VER', '1.0.4' );


define( 'WCSI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

define( 'WCSI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

define( 'WCSI_EDD_STORE_URL', 'https://icywordpress.com' );

define( 'WCSI_EDD_PRODUCT_NAME', 'WooCommerce Shoppable Instagram' );


include_once 'includes/class-wc-shoppable-instagram.php';
include_once 'includes/class-wc-shoppable-instagram-api.php';
include_once 'includes/admin/class-icy-instagram-admin.php';
include_once 'includes/shortcodes/wc-shoppable-instagram-shortcode.php';

/*
if( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	include_once 'edd/EDD_SL_Plugin_Updater.php';
}

$edd_updater = new EDD_SL_Plugin_Updater( WCSI_EDD_STORE_URL, __FILE__, array(
	'version' 	=> WCSI_VER,
	'license' 	=> trim( get_option( 'wcsi_license_key' ) ),
	'item_name' => WCSI_EDD_PRODUCT_NAME,
	'author' 	=> 'Icy WordPress',
	'url'       => home_url()
) );

include_once 'edd/edd-hooks.php';
*/

$wcsi = new WC_Shoppable_Instagram();
$wcsi_admin = new WC_Shoppable_Instagram_Admin();

add_action( 'admin_init', array( $wcsi, 'admin_init' ) );
add_action( 'admin_menu', array( $wcsi_admin, 'admin_menu' ) );
add_action( 'wp_enqueue_scripts', array( $wcsi, 'frontend_scripts' ) );