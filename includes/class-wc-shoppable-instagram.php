<?php


class WC_Shoppable_Instagram {

	private $token = null;


	private $_errors = array();


	public function admin_init() {
		global $wcsi_admin;

		$page = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : '';
		
		$wcsi_admin->settings();

		add_action( 'update_option_icy_instagram_config_settings', array( $this, 'deleteCache' ) );

		add_action( 'wp_ajax_icyig_update', array( $wcsi_admin, 'cleanup_hotspots' ) );
		add_action( 'wp_ajax_icyig_get_hotspots', array( $wcsi_admin, 'get_hotspots' ) );

		if( $page == 'instagram-feed' ) {
			add_action( 'admin_enqueue_scripts', array( $wcsi_admin, 'admin_scripts' ), 99 );
			add_action( 'admin_notices', array( $wcsi_admin, 'auth_check_notice' ) );

			/**
			 * Remove conflicting emojis on the settings page
			 */
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );

			if( isset( $_GET['token'] ) && $this->getToken() == 0 ) {
				$wcsi_admin->updateAuth();
			}

			if( isset( $_GET['disconnect'] ) && $_GET['disconnect'] == 'true' ) {
				$wcsi_admin->disconnectAuth();
			}
		}
	}


	public function frontend_scripts() {
		wp_register_style( 'wcsi-frontend-style', WCSI_PLUGIN_URL . '/assets/css/icy-instagram-frontend.css', array(), WCSI_VER );
		wp_register_script( 'wcsi-frontend-script', WCSI_PLUGIN_URL . '/assets/js/icy-instagram-frontend.js', array( 'jquery' ), WCSI_VER, true );
	}


	public function getToken() {
		$token = get_option( 'icy_instagram_feed_token' );
		
		return $token;
	}


	public function setToken( $token ) {
		$this->token = ! empty( $token ) ? $token : 0;
	}

	public function getUserID() {
		$user_id = get_option( 'wcsi_user_id' );

		if( $user_id ) {
			return absint( $user_id );
		}

		return 'self';
	}


	public function getCached() {
		return get_transient( 'wcsi_stored_media' );
	}


	public function setCache( $media ) {
		set_transient( 'wcsi_stored_media', $media, apply_filters( 'wcsi_stored_media_duration', 60 * 30 ) );
	}


	public function deleteCache() {
		wp_cache_flush();
		
		delete_transient( 'wcsi_stored_media' );
	}


	public function getMedia( $num = 0 ) {
		if( false === $this->getCached() ) {
			$instagram = new WC_Shoppable_Instagram_API( $this->getToken() );
			
			$media = $instagram->get_media( $this->getUserID(), $num );

			if( $instagram->has_errors() ) {
				return false;
			}

			$result = array();

			$initial_media_count = count( $media->data );

			if( ! empty( $media->data ) ) {
				do {
					if( ! empty( $media->data ) ) {
						foreach( $media->data as $entry ) {
							if( $entry->type == 'image' ) {
								$result[] = $entry;

								if( sizeof( $result ) >= $num )
									break 2;
							}
						}
					}

					$media = $instagram->pagination( $media );


					if( $instagram->has_errors() ) {
						return $result;
					}

				}while( count( $result ) <= $num && count( $initial_media_count ) > $num );

				$this->setCache( $result );
			}

			return $result;
		}

		return $this->getCached();
	}


	public function parse_data( $mdata ) {
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
}