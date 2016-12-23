<?php


class WC_Shoppable_Instagram_Admin {

	public function __construct() {
		global $wcsi;

		$this->includes();

		
	}


	public function includes() {
		
	}


	/**
	 * Enqueue admin scripts
	 * 
	 * @return type
	 */
	public function admin_scripts() {
		wp_enqueue_style( 'icywp-instagram-admin', plugin_dir_url( __FILE__ ) . '/assets/css/icy-instagram-admin.css', array( 'woocommerce_admin_styles' ) );
		wp_enqueue_script( 'icywp-instagram-admin', plugin_dir_url( __FILE__ ) . '/assets/js/icy-instagram-admin.js', array( 'jquery' ), '1', true );
		wp_enqueue_script( 'wc-enhanced-select' );
		wp_enqueue_script( 'thickbox' );
	}


	/**
	 * Actions to fire on init
	 * 
	 * @return type
	 */
	public function admin_init() {
		// Remove Emojis from this page
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}


	/**
	 * Add the settings page under Settings
	 * 
	 * @return type
	 */
	public function admin_menu() {
		add_submenu_page( 'options-general.php', __( 'Instagram Feed' ), __( 'Instagram Feed' ), 'manage_options', 'instagram-feed', array( $this, 'admin_settings' ) );
	}


	/**
	 * Save the authorization token in the database
	 * 
	 * @return type
	 */
	public function updateAuth() {
		global $wcsi;

		if( isset( $_GET['token'] ) && empty( $wcsi->getToken() ) ) {
			if( isset( $_GET['user_id'] ) ) {
				update_option( 'wcsi_user_id', absint( $_GET['user_id'] ) );
			}

			update_option( 'icy_instagram_feed_token', $_GET['token'] );

			$wcsi->deleteCache();

			wp_redirect( admin_url( 'options-general.php?page=instagram-feed' ) );
			exit;
		}
	}


	public function disconnectAuth() {
		update_option( 'icy_instagram_feed_token', 0 );

		wp_redirect( admin_url( 'options-general.php?page=instagram-feed' ) );
		exit;
	}


	/**
	 * Display a notice to use to enter credentials if have not already
	 * 
	 * @return type
	 */
	public function auth_check_notice() {
		global $wcsi;

		$access_token = $wcsi->getToken();

		if( empty( $access_token ) ) : ?>

			<div class="error"><p><?php _e( 'Please specify your Instagram Application details below, and then authenticate your account.' ); ?></p></div>

		<?php endif; 
	}



	public function admin_settings() {
		global $wcsi;

		$config       = get_option( 'icy_instagram_config_settings' );
		$display_num  = ( isset( $config['display_num'] ) && $config['display_num'] <= 20 && $config['display_num'] >= 1 ) ? (int) $config['display_num'] : 10;
		$access_token = $wcsi->getToken();
	?>

	<div class="wrap">
		<h2><?php _e( 'Instagram Feed Settings' ); ?></h2>

		<div class="left-wrap">
			<form method="post" action="options.php">
				<?php 
					settings_fields( 'instagram-feed' );
					do_settings_sections( 'instagram-feed' );
				?>

				<p>
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Settings' ); ?>" />
					
					<?php /*<a href="<?php print $wcsi->ig()->getLoginUrl(); ?>" class="button"><?php _e( 'Authenticate Credentials' ); ?></a>*/ ?>
					
					<?php if( $wcsi->getToken() ) : ?>
					
					<a href="<?php print admin_url( 'options-general.php?page=instagram-feed&disconnect=true' ); ?>" class="button disconnect"><?php _e( 'Disconnect Authentication' ); ?></a>
					
					<?php else : ?>

					<a href="<?php print esc_url( add_query_arg( array( 'client_id' => '1baf4155ad1b42248c1650423b68dd76', 'redirect_uri' => urlencode( add_query_arg( array( 'redirect' => admin_url( 'options-general.php?page=instagram-feed' ) ), esc_url( 'https://auth.icywordpress.com/instagram/' ) ) ), 'response_type' => 'code' ), 'https://api.instagram.com/oauth/authorize/' ) ); ?>" class="button-secondary connect"><?php _e( 'Authenticate with Instagram' ); ?></a>

					<?php endif; ?>
				</p>
			</form>
		</div>

		<div class="right-wrap">
			<div id="ig-image-overlay" class="ig-img-overlay" style="display:none;">
			    <div class="ig-img">
			    	<div class="ig-img-c"></div>
			    	<div class="ig-img-hsc"></div>
			    	<div class="ig-hotspot-settings" style="display: none;">
			    		<a href="#close-hotspot-settings" class="ig-close-hotspot">&#10134;</a>
			    		<a href="#close-hotspot-settings" class="ig-save-hotspot">&#10004;</a>
				    	<input type="text" name="ig-hotspot-product" class="wc-product-search" data-placeholder="Search for a product" data-action="woocommerce_json_search_products" />
				    </div>
			    </div>
			</div>

			<?php 
				if( $access_token ) {

					$media = $wcsi->getMedia( $display_num );

					if( ! empty( $media ) ) {

						if( class_exists( 'WooCommerce' ) ) {
							add_thickbox();
						}

						foreach( $media as $entry ) {

							?>

							<div class="ig-image" data-image-id="<?php print $entry->id; ?>">
								<div class="ig-img-container">
									<a href="#TB_inline?width=600&height=600&inlineId=ig-image-overlay" class="thickbox ig-img-settings-action">
										<img src="<?php print $entry->images->thumbnail->url; ?>" />
									</a>
								</div>
								<div class="ig-img-data" data-image="<?php print $entry->images->standard_resolution->url; ?>"></div>
							</div>

							<?php

						}

					}elseif( isset( $media->meta->error_type ) ) {
						print '<p class="ig-error">' . $media->meta->error_message . ' Please verify your Client ID and Client Secret are correct, and then re-authenticate your credentials.</p>';

						// Reset the token to 0 so we can re-authenticate
						update_option( 'icy_instagram_feed_token', 0 );
					}
				}
			?>

		</div>
	</div>

	<?php
	}


	function get_hotspots() {
		global $wcsi;

		$media_data = get_option( 'icyig_media_data_' . $wcsi->getToken() );

		print json_encode( maybe_unserialize( $media_data ) );

		wp_die();
	}


	function update_database() {
		global $wcsi;

		$data = (array) json_decode( stripslashes( $_POST['data'] ), true );

		// Only keep the latest 20 images hotspots
		if( is_array( $data ) ) {
			$data = array_slice( $data, 0, 20 );
		}

		if( null === $data ) {
			print __( 'There data could not be decoded by json_decode' );
		}else {
			update_option( 'icyig_media_data_' . $wcsi->getToken(), $data );
		}

		wp_die();
	}
}