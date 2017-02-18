<?php


class WC_Shoppable_Instagram_API {

	private $errors     = array();

	private $has_errors = false;

	private $token      = '';

	const API = 'https://api.instagram.com/v1';

	public function __construct( $token = '' ) {
		$this->token = $token;
	}

	public function get_media( $user = 'self', $count = 10 ) {
		$response = $this->request( '/users/' . $user . '/media/recent/',  array( 'count' => absint( $count ) ) );

		if( is_wp_error( $response ) ) {
			$this->log_error( $response->get_error_message() );

			return false;
		}

		$media = json_decode( wp_remote_retrieve_body( $response ) );

		return $media;
	}

	public function pagination( $obj ) {
        if( is_object( $obj ) && ! is_null( $obj->pagination ) ) {
            if( ! isset( $obj->pagination->next_url ) ) {
                return;
            }

            $request = parse_url( $obj->pagination->next_url );

            $response = $this->request( str_replace( '/v1', '', $request['path'] ), array( 'max_id' => $obj->pagination->next_max_id, 'count' => 10 ) );

            if( is_wp_error( $response ) ) {
				$this->log_error( $response->get_error_message() );

				return false;
			}

			$media = json_decode( wp_remote_retrieve_body( $response ) );

			if( ! isset( $media->data ) || empty( $media->data ) ) {
				$this->log_error( __( 'Unable to load media using pagination method', 'wcsi' ) );
			}
			
			return $media;
        }

        return false;
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

	public function request( $endpoint = '', $args = array() ) {
		if( ! is_array( $args ) ) {
			$args = array();
		}

		$args = array_merge( $args, array( 'access_token' => $this->token ) );

		$request_uri = add_query_arg( $args, self::API . $endpoint );

		return wp_remote_get( $request_uri );
	}

}