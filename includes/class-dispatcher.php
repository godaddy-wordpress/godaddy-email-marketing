<?php

class Mad_Mimi_Dispatcher {

	/**
	 * API's base URL
	 */
	const BASE_API = 'http://api.madmimi.com/';

	private static $ok_codes = array( 200, 304 );

	public static function fetch_forms( $username, $api_key = false ) {

		if ( ! ( $username && $api_key ) ) {
			$username = Mad_Mimi_Settings_Controls::get_option( 'username' );
			$api_key  = Mad_Mimi_Settings_Controls::get_option( 'api-key' );
		}

		$auth = array(
			'username' => $username,
			'api_key' => $api_key,
		);

		// Prepare the URL that includes our credentials
		$response = wp_remote_get( self::get_method_url( 'forms', false, $auth ), array(
			'timeout' => 10,
		) );

		// delete all existing transients for this user
		delete_transient( 'mimi-' . $username . '-lists' );

		// credentials are incorrect
		if ( ! in_array( wp_remote_retrieve_response_code( $response ), self::$ok_codes ) ) {
			return false;
		}

		// @todo should we cache for *always* since we have a button to clear the cache?
		// maybe having an expiration on such a thing can bloat wp_options?
		set_transient( 'mimi-' . $username . '-lists', $data = json_decode( wp_remote_retrieve_body( $response ) ), defined( DAY_IN_SECONDS ) ? DAY_IN_SECONDS : 60 * 60 * 24 );

		return $data;

	}

	public static function get_forms( $username = false ) {

		$username = $username ? $username : Mad_Mimi_Settings_Controls::get_option( 'username' );
		$api_key = Mad_Mimi_Settings_Controls::get_option( 'api-key' );

		if ( empty( $api_key ) ) {
			return false;
		}

		if ( false === ( $data = get_transient( 'mimi-' . $username . '-lists' ) ) ) {
			$data = self::fetch_forms( $username );
		}

		return $data;

	}

	public static function get_fields( $form_id ) {

		if ( false === ( $fields = get_transient( 'mimi-form-' . $form_id ) ) ) {

			// fields are not cached. fetch and cache.
			$fields = wp_remote_get( self::get_method_url( 'fields', array(
				'id' => $form_id,
			) ) );

			// was there an error, connection is down? bail and try again later.
			if ( ! self::is_response_ok( $fields ) ) {
				return false;
			}

			// @TODO: should we cache results for longer than a day? not expire at all?
			set_transient( 'mimi-form-' . $form_id, $fields = json_decode( wp_remote_retrieve_body( $fields ) ) );
		}

		return $fields;
	}

	public static function get_user_level() {

		$username = Mad_Mimi_Settings_Controls::get_option( 'username' );

		// no username entered by user?
		if ( ! $username )
			return false;

		if ( false === ( $data = get_transient( 'mimi-' . $username . '-account' ) ) ) {

			$data = wp_remote_get( self::get_method_url( 'account' ) );

			// if the request has failed for whatever reason
			if ( ! self::is_response_ok( $data ) ) {
				return false;
			}

			$data = json_decode( wp_remote_retrieve_body( $data ) );
			$data = $data->result;

			// no need to expire at all
			set_transient( 'mimi-' . $username . '-account', $data );

		}

		return $data;

	}

	public static function user_sign_in() {

		// Prepare the URL that includes our credentials
		$response = wp_remote_get( self::get_method_url( 'signin', false ), array(
			'timeout' => 10,
		) );

		// credentials are incorrect
		if ( ! in_array( wp_remote_retrieve_response_code( $response ), self::$ok_codes ) ) {
			return false;
		}

		// retrieve the token
		return self::get_method_url( 'signin_redirect', array(
			'token' => wp_remote_retrieve_body( $response ),
		) );

	}

	/**
	 * Utility function for getting a URL for various API methods
	 *
	 * @param string $method The short of the API method
	 * @param array $params Extra parameters to pass on with the request
	 * @param bool $auth Autentication array including API key and username
	 *
	 * @return string The final URL to use for the request
	 */
	public static function get_method_url( $method, $params = array(), $auth = false ) {

		$auth = $auth ? $auth : array(
			'username' => Mad_Mimi_Settings_Controls::get_option( 'username' ),
			'api_key' => Mad_Mimi_Settings_Controls::get_option( 'api-key' ),
		);

		$path = '';

		switch ( $method ) {

			case 'forms' :

				$path = add_query_arg( $auth, 'signups.json' );

				break;

			case 'fields' :

				$path = add_query_arg( $auth, 'signups/' . $params['id'] . '.json' );

				break;

			case 'account' :

				$path = add_query_arg( $auth, 'user/account_status' );

				break;

			case 'signin' :

				$path = add_query_arg( $auth, 'sessions/single_signon_token' );

				break;

			case 'signin_redirect' :

				$path = add_query_arg( array(
					'token'    => $params['token'],
					'username' => $auth['username'],
				), 'sessions/single_signon' );

				break;
		}

		return self::BASE_API . $path;
	}

	public static function is_response_ok( &$request ) {
		return ( ! is_wp_error( $request ) && in_array( wp_remote_retrieve_response_code( $request ), self::$ok_codes ) );
	}

}
