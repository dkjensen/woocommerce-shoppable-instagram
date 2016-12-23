<?php


class WC_Shoppable_Instagram_API {

	private $errors     = array();

	private $has_errors = false;

	private $token      = '';

	const API = 'https://api.instagram.com/v1/';

	public function __construct( $token = '' ) {
		$this->token = $token;
	}

	public function get_media( $user = 'self' ) {
		$response = $this->request( 'users/' . $user . '/media/recent/' );

		if( is_wp_error( $response ) ) {
			$this->log_error( $response->get_error_message() );

			return false;
		}

		$media = json_decode( wp_remote_retrieve_body( $response ) );

		return $media;
	}

	public function pagination( $obj, $limit = 0 ) {
        if( is_object( $obj ) && ! is_null( $obj->pagination ) ) {
            if( ! isset( $obj->pagination->next_url ) ) {
                return;
            }

            $apiCall = explode( '?', $obj->pagination->next_url );

            if( count( $apiCall ) < 2 ) {
                return;
            }

            $function = str_replace( self::API_URL, '', $apiCall[0] );

            $auth = (strpos($apiCall[1], 'access_token') !== false);

            if (isset($obj->pagination->next_max_id)) {
                return $this->_makeCall($function, $auth, array('max_id' => $obj->pagination->next_max_id, 'count' => $limit));
            }

            return $this->_makeCall($function, $auth, array('cursor' => $obj->pagination->next_cursor, 'count' => $limit));
        }
        
        throw new InstagramException("Error: pagination() | This method doesn't support pagination.");
    }

	public function log_error( $error = '' ) {
		if( ! $this->has_errors ) {
			$this->has_errors = true;
		}

		$this->errors[] = sanitize_text_field( $error );
	}

	public function has_errors() {
		return (bool) $this->has_errors;
	}

	public function request( $endpoint = '' ) {
		$request_uri = add_query_arg( array( 'access_token' => $this->token ), self::API . $endpoint );

		return wp_remote_get( $request_uri );
	}

}