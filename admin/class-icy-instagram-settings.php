<?php

class Icy_Instagram_Settings {


	function init() {
		global $wcsi;

		add_settings_section(
			'icy_instagram_settings_section',
			'Instagram Settings',
			array( $this, 'settings_section_callback' ),
			'instagram-feed'
		);

		
		if( ! $wcsi->getToken() ) {
			add_settings_field(
				'icy_instagram_feed',
				'Instagram API Credentials',
				array( $this, 'settings_callback' ),
				'instagram-feed',
				'icy_instagram_settings_section'
			);
		}
		

		/*
		add_settings_field(
				'icy_instagram_license',
				'Plugin License Key',
				array( $this, 'license_callback' ),
				'instagram-feed',
				'icy_instagram_settings_section'
			);
		*/

		add_settings_field(
			'icy_instagram_config_settings',
			'Configuration Settings',
			array( $this, 'configuration_callback' ),
			'instagram-feed',
			'icy_instagram_settings_section'
		);

		register_setting( 'instagram-feed', 'icy_instagram_feed' );
		//register_setting( 'instagram-feed', 'icy_instagram_license' );
		register_setting( 'instagram-feed', 'icy_instagram_config_settings' );
		$wcsi->deleteCache();
	}


	function settings_section_callback() {
		global $wcsi;

		if( ! $wcsi->getToken() ) { 
		?>
		<p><?php _e( 'In your Instagram Application settings, set the Redirect URL to:' ); ?></p>
		<p><strong><?php print admin_url( 'options-general.php?page=instagram-feed' ); ?></strong></p>
		<?php
		}
	}


	function settings_callback() {
		$settings_value = get_option( 'icy_instagram_feed' );

		$api_key = isset( $settings_value['api_key'] ) ? esc_attr( $settings_value['api_key'] ) : '';
		$api_secret = isset( $settings_value['api_secret'] ) ? esc_attr( $settings_value['api_secret'] ) : '';

		echo '<p><input type="text" name="icy_instagram_feed[api_key]" value="' . $api_key . '" placeholder="Client ID" /></p>';
		echo '<p><input type="text" name="icy_instagram_feed[api_secret]" value="' . $api_secret . '" placeholder="Client Secret" /></p>';
		echo '<input type="hidden" name="icy_instagram_postdata" value="" />';
	}

	function license_callback() {
		$settings_value = get_option( 'icy_instagram_license' );
		$license_key = isset( $settings_value ) ? esc_attr( $settings_value ) : '';

		echo '<p><input type="text" name="icy_instagram_license" value="' . $license_key . '" placeholder="Enter license key" size="50" /><br />';
		echo '<span class="description">' . __( 'Enter your plugin license key to receive automatic updates.' ) . '</span></p>';
	}


	function configuration_callback() {
		$settings_value = get_option( 'icy_instagram_config_settings' );

		$display_num        = isset( $settings_value['display_num'] ) ? (int) $settings_value['display_num'] : 10;
		$col_num            = isset( $settings_value['column_num'] ) ? (int) $settings_value['column_num'] : 5;
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

		// Number of columns
		echo '<p><input type="number" name="icy_instagram_config_settings[column_num]" value="' . $col_num . '" min="1" max="50" />';
		echo '<span class="description" style="margin: 0 0 0 10px;">' . __( 'Set the number of columns to display' ) . '</span></p>';

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

}