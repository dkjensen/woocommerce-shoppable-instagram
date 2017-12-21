<?php


class WC_Shoppable_Instagram_Admin {

	/**
	 * Enqueue admin scripts
	 * 
	 * @return type
	 */
	public function admin_scripts() {
		wp_enqueue_style( 'icywp-instagram-admin', plugin_dir_url( __FILE__ ) . '/assets/css/icy-instagram-admin.css', array( 'woocommerce_admin_styles' ), WCSI_VER );
		wp_enqueue_script( 'icywp-instagram-admin', plugin_dir_url( __FILE__ ) . '/assets/js/icy-instagram-admin.js', array( 'jquery' ), WCSI_VER, true );
		wp_enqueue_script( 'wc-enhanced-select' );
		wp_enqueue_script( 'thickbox' );
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
		global $wcsi;

		update_option( 'icy_instagram_feed_token', 0 );

		$wcsi->deleteCache();

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

			<div class="error"><p><?php _e( 'Please Authenticate with Instagram below to get started.' ); ?></p></div>

		<?php endif; 
	}



	public function admin_settings() {
		global $wcsi;

		$settings       = get_option( 'icy_instagram_config_settings' );
		$display_num  = ( isset( $settings['display_num'] ) && $settings['display_num'] <= 20 && $settings['display_num'] >= 1 ) ? (int) $settings['display_num'] : 10;
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


	function settings() {
		global $wcsi;

		add_settings_section(
			'icy_instagram_settings_section',
			'Instagram Settings',
			array( $this, 'settings_section_callback' ),
			'instagram-feed'
		);

		add_settings_field(
				'wcsi_license_key',
				'Plugin License Key',
				array( $this, 'license_callback' ),
				'instagram-feed',
				'icy_instagram_settings_section'
			);

		add_settings_field(
			'icy_instagram_config_settings',
			'Configuration Settings',
			array( $this, 'configuration_callback' ),
			'instagram-feed',
			'icy_instagram_settings_section'
		);

		register_setting( 'instagram-feed', 'icy_instagram_config_settings' );
	}


	function settings_section_callback() {}


	function license_callback() {
		$settings_value = get_option( 'wcsi_license_key' );
		$license_key = isset( $settings_value ) ? esc_attr( $settings_value ) : '';

		echo '<p><input type="text" name="wcsi_license_key" value="' . $license_key . '" placeholder="Enter license key" size="50" /><br />';
		echo '<span class="description">' . __( 'Enter your plugin license key to receive automatic updates.' ) . '</span></p>';
	}


	function configuration_callback() {
		$settings_value = get_option( 'icy_instagram_config_settings' );

		$display_num        = isset( $settings_value['display_num'] ) ? (int) $settings_value['display_num'] : 10;
		$social             = isset( $settings_value['enabled_social'] ) ? (array) $settings_value['enabled_social'] : array();

		$hoverstate   		= isset( $settings_value['hoverstate'] ) ? (int) $settings_value['hoverstate'] : 0;

		$facebook_enabled   = isset( $settings_value['enabled_social']['facebook'] ) ? (int) $settings_value['enabled_social']['facebook'] : 0;
		$twitter_enabled    = isset( $settings_value['enabled_social']['twitter'] ) ? (int) $settings_value['enabled_social']['twitter'] : 0;
		$instagram_enabled  = isset( $settings_value['enabled_social']['instagram'] ) ? (int) $settings_value['enabled_social']['instagram'] : 0;
		$google_enabled     = isset( $settings_value['enabled_social']['google'] ) ? (int) $settings_value['enabled_social']['google'] : 0;
		$pinterest_enabled  = isset( $settings_value['enabled_social']['pinterest'] ) ? (int) $settings_value['enabled_social']['pinterest'] : 0;
		$email_enabled      = isset( $settings_value['enabled_social']['email'] ) ? (int) $settings_value['enabled_social']['email'] : 0;

		// Number of images to display
		echo '<p><input type="number" name="icy_instagram_config_settings[display_num]" value="' . $display_num . '" min="1" max="20" />';
		echo '<span class="description" style="margin: 0 0 0 10px;">' . __( 'How many Instagram images would you like to display? (Max 20)' ) . '</span></p>';

		echo '<p>&nbsp;</p>';

		echo '<h4>' . __( 'Display Settings', 'icyig' ) . '</h4>';
		echo '<p><input type="checkbox" name="icy_instagram_config_settings[hoverstate]" value="1" ' . checked( $hoverstate, 1, false ) . ' /> ' . __( 'Show <em>Shop Now</em> on hover?' ) . '</p>';

		echo '<p>&nbsp;</p>';

		echo '<h4>' . __( 'Social Sharing', 'icyig' ) . '</h4>';
		echo '<p><input type="checkbox" name="icy_instagram_config_settings[enabled_social][facebook]" value="1" ' . checked( $facebook_enabled, 1, false ) . ' /> ' . __( 'Enable Facebook', 'icyig') . '</p>';
		echo '<p><input type="checkbox" name="icy_instagram_config_settings[enabled_social][twitter]" value="1" ' . checked( $twitter_enabled, 1, false ) . ' /> ' . __( 'Enable Twitter', 'icyig') . '</p>';
		echo '<p><input type="checkbox" name="icy_instagram_config_settings[enabled_social][instagram]" value="1" ' . checked( $instagram_enabled, 1, false ) . ' /> ' . __( 'Enable Instagram', 'icyig') . '</p>';
		echo '<p><input type="checkbox" name="icy_instagram_config_settings[enabled_social][google]" value="1" ' . checked( $google_enabled, 1, false ) . ' /> ' . __( 'Enable Google+', 'icyig') . '</p>';
		echo '<p><input type="checkbox" name="icy_instagram_config_settings[enabled_social][pinterest]" value="1" ' . checked( $pinterest_enabled, 1, false ) . ' /> ' . __( 'Enable Pinterest', 'icyig') . '</p>';
		echo '<p><input type="checkbox" name="icy_instagram_config_settings[enabled_social][email]" value="1" ' . checked( $email_enabled, 1, false ) . ' /> ' . __( 'Enable Email', 'icyig') . '</p>';
	}


	function get_hotspots() {
		global $wcsi;

		$media_data = get_option( 'icyig_media_data_' . $wcsi->getToken() );

		print json_encode( maybe_unserialize( $media_data ) );

		wp_die();
	}


	function cleanup_hotspots() {
		global $wcsi;

		$data = (array) json_decode( stripslashes( $_POST['data'] ), true );

		// Only keep the latest 40 images hotspots
		if( is_array( $data ) ) {
			$data = array_slice( $data, 0, 40 );
		}

		if( null === $data ) {
			_e( 'The data could not be decoded.' );
		}else {
			_e( 'Data updated successfully.' );
			update_option( 'icyig_media_data_' . $wcsi->getToken(), $data );
		}

		wp_die();
	}
}