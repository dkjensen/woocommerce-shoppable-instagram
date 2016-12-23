<?php


class WC_Shoppable_Instagram {

	protected $_instagram;


	private $_api_key = null;


	private $_api_secret = null;


	private $token = null;


	private $_errors = array();


	const API_URL = 'https://api.instagram.com/v1/';


	public function __construct() {
		$this->init();
		$this->includes();
	}



	private function init() {
		$settings_value = get_option( 'icy_instagram_feed' );
	}


	public function admin_init() {
		global $wcsi_admin, $wcsi_settings;

		$page = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : '';
		

		$wcsi_admin->admin_init();
		//$wcsi_admin->admin_menu();
		//$wcsi_settings->init();

		$wcsi_settings->init();

		add_action( 'admin_enqueue_scripts', array( $wcsi_admin, 'admin_scripts' ), 99 );

		add_action( 'wp_ajax_icyig_update', array( $wcsi_admin, 'update_database' ) );
		add_action( 'wp_ajax_icyig_get_hotspots', array( $wcsi_admin, 'get_hotspots' ) );

		if( $page == 'instagram-feed' ) {
			add_action( 'admin_notices', array( $wcsi_admin, 'auth_check_notice' ) );

			if( isset( $_GET['token'] ) && $this->getToken() == 0 ) {
				$wcsi_admin->updateAuth();
			}

			if( isset( $_GET['disconnect'] ) && $_GET['disconnect'] == 'true' ) {
				$wcsi_admin->disconnectAuth();
			}
		}
	}




	public function ig() {
		return $this->_instagram;
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
		// See if we have media that is cached already
		//if( false === $this->getCached() ) {

			// Get a new batch of Instagram media
			//$media = $this->_instagram->getUserMedia( 'self', intval( $num ) );

			$api = new WC_Shoppable_Instagram_API( $this->getToken() );
			$media = $api->get_media( $this->getUserID() );

			// Check if we have any errors
			if( $api->has_errors() ) {
				return false;
			}

			$result = array();

			/**
			 * Loop through all the media and only grab the media that are images. 
			 * 
			 * This is to prevent displaying less images than desired, e.g. if the
			 * user wants to display 10 images, but a video is returned in the media 
			 * we grabbed, it would only return 9.
			 * 
			 * We apply pagination and grab the next 10 results, until we are at our
			 * desired number of images to be displayed
			 * 
			 */
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


				try {
					$media = $api->pagination( $media );
				}catch( InstagramException $e ) {
					return $result;
				}

				// Check for errors again
				if( $this->has_errors( $media ) ) {
					$this->log_error( $media );

					return $result;
				}

			}while( count( $result ) <= $num );

			// Cache the results
			//$this->setCache( $result );

			return $result;
		//}

		//return $this->getCached();
	}


	public function has_errors( $media ) {
		if( isset( $media->meta->error_type ) ) {
			return true;
		}

		return false;
	}


	public function log_error( $media ) {
		$this->_errors[] = $media->meta->error_message;
	}


	public function show_errors() {
		if( ! empty( $this->_errors ) ) {
			foreach( $this->_errors as $error ) {
				print '<p class="ig-error">' . $error . '</p>';
			}
		}
	}


	public function includes() {
		
	}


	public function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}


	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}


	public function template_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates';
	}

}