<?php

class Icy_Instagram_Feed {


	public static $settings = array();


	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );

		$config_settings = get_option( 'icy_instagram_config_settings' );

		self::$settings = ( false !== $config_settings ) ? $config_settings : array();

		if( ! is_admin() && isset( self::$settings['column_num'] ) ) {
			add_action( 'wp_head', array( $this, 'inline_styles' ) );
		}
	}





	public function frontend_scripts() {
		global $wcsi;
		
		wp_enqueue_style( 'icyig-style', WCSI_PLUGIN_URL . '/assets/css/icy-instagram-frontend.css', array(), ICYIG_VER );
		wp_enqueue_script( 'icyig', WCSI_PLUGIN_URL . '/assets/js/icy-instagram-frontend.js', array( 'jquery' ), ICYIG_VER, true );
	}



	public function inline_styles() {
		$column_width = abs( number_format( 100 / (int) self::$settings['column_num'], 4 ) );
		?>

		<style type="text/css">
			.icyig-container .icyig-thumb-container {
				width: <?php print $column_width; ?>%;
			}
		</style>

		<?php
	}



	public static function social_links() {
		$social_links = isset( self::$settings['enabled_social'] ) ? self::$settings['enabled_social'] : array();

		$nav_menu = '';

		if( ! empty( $social_links ) && is_array( $social_links ) ) {

			$nav_menu = '<div class="icyig-social-menu"><ul class="icyig-social-list">';

			foreach( $social_links as $social => $enabled ) {
				if( $enabled != 1 )
					continue;

				$nav_menu .= '<li class="icyig-social-link-container ' . $social . '" data-network="' . $social . '"><a href="" target="_blank" class="icyig-social-link"></a></li>';
			}

			$nav_menu .= '</ul></div>';
		}

		return $nav_menu;
	}



	public static function parse_data( $mdata ) {
		if( $mdata && is_array( $mdata ) ) {
			foreach( $mdata as $image_id => $imgdata ) {

				if( ! isset( $imgdata['hotspots'] ) ) {
					$mdata[$image_id]['hotspots'] = array();
					$hotspots = array();
				}else {
					$hotspots = (array) $imgdata['hotspots'];
				}

				foreach( $hotspots as $hsid => $hotspot ) {
					$product_id = isset( $hotspot['product_id'] ) ? (int) $hotspot['product_id'] : 0;

					if( $product_id === 0 ) {
						unset( $mdata[$image_id]['hotspots'][$hsid] );
					}

					$product_post_type = get_post_type( $product_id );

					if( false !== $product_post_type ) {
						if( in_array( $product_post_type, array( 'product', 'variation' ) ) ) {
							$mdata[$image_id]['hotspots'][$hsid]['url'] = get_permalink( $product_id );
							$mdata[$image_id]['hotspots'][$hsid]['title'] = get_the_title( $product_id );
						}
					}
				}
			}
		}

		return $mdata;
	}



	public static function display_error() {



	}

}



new Icy_Instagram_Feed();