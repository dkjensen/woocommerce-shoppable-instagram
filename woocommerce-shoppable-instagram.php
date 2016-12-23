<?php
/**
 * Plugin Name: WooCommerce Shoppable Instagram
 * Description: Instagram feed with product links
 * Version: 1.0.2
 * Author: David Jensen
 * Author URI: http://dkjensen.com
 * License: GPL2
 */


define( 'ICYIG_VER', '1.0.2' );


if( ! defined( 'WCSI_PLUGIN_DIR' ) ) {
	define( 'WCSI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if( ! defined( 'WCSI_PLUGIN_URL' ) ) {
	define( 'WCSI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}


include_once 'libs/Instagram.php';
include_once 'libs/InstagramException.php';


include_once 'includes/class-instagram-feed.php';
include_once 'includes/icy-shortcodes.php';

include_once 'includes/class-wc-shoppable-instagram.php';
include_once 'includes/class-wc-shoppable-instagram-api.php';


	include_once 'admin/class-icy-instagram-admin.php';
	require_once 'admin/class-icy-instagram-settings.php';



include_once 'edd/EDD_SL_Plugin_Updater.php';
include_once 'edd/edd-hooks.php';

$wcsi = new WC_Shoppable_Instagram();
$wcsi_admin = new WC_Shoppable_Instagram_Admin();
$wcsi_settings = new Icy_Instagram_Settings();

add_action( 'admin_init', array( $wcsi, 'admin_init' ) );
add_action( 'admin_menu', array( $wcsi_admin, 'admin_menu' ) );