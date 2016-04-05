<?php
/**
 * Dispatcher class
 *
 * @package GEM
 */

/**
 * GoDaddy Email Marketing Dispatcher.
 *
 * @since 1.0
 */
class GEM_Dispatcher {

	/**
	 * API's base URL
	 *
	 * @var string
	 */
	const BASE_API = 'http://api.madmimi.com/';

	/**
	 * HTTP response codes
	 *
	 * @var array
	 */
	private static $ok_codes = array( 200, 304 );

	/**
	 * Gets and sets the forms.
	 *
	 * @param string  $username The username.
	 * @return string $api_key  The API key.
	 * @return false|array The form fields array or false.
	 */
	public static function fetch_forms( $username, $api_key = false ) {
		if ( ! ( $username && $api_key ) ) {
			$username = GEM_Settings_Controls::get_option( 'username' );
			$api_key  = GEM_Settings_Controls::get_option( 'api-key' );
		}

		$auth = array(
			'username' => $username,
			'api_key' => $api_key,
		);

		// Prepare the URL that includes our credentials.
		$response = wp_remote_get( self::get_method_url( 'forms', false, $auth ), array(
			'timeout' => 10,
		) );

		// Delete all existing transients for this user.
		delete_transient( 'gem-' . $username . '-lists' );

		// Credentials are incorrect.
		if ( ! in_array( wp_remote_retrieve_response_code( $response ), self::$ok_codes ) ) {
			return false;
		}

		// @todo should we cache for *always* since we have a button to clear the cache?
		// Maybe having an expiration on such a thing can bloat wp_options?
		set_transient( 'gem-' . $username . '-lists', $data = json_decode( wp_remote_retrieve_body( $response ) ), defined( DAY_IN_SECONDS ) ? DAY_IN_SECONDS : 60 * 60 * 24 );

		return $data;
	}

	/**
	 * Gets the forms.
	 *
	 * @param string $username The username.
	 * @return false|array The form fields array or false.
	 */
	public static function get_forms( $username = false ) {
		$username = $username ? $username : GEM_Settings_Controls::get_option( 'username' );
		$api_key = GEM_Settings_Controls::get_option( 'api-key' );

		if ( empty( $api_key ) ) {
			return false;
		}

		if ( false === ( $data = get_transient( 'gem-' . $username . '-lists' ) ) ) {
			$data = self::fetch_forms( $username );
		}

		return $data;
	}

	/**
	 * Gets and sets the form fields.
	 *
	 * @param string $form_id Form ID.
	 * @return false|object The form fields JSON object or false.
	 */
	public static function get_fields( $form_id ) {
		if ( false === ( $fields = get_transient( 'gem-form-' . $form_id ) ) ) {

			// Fields are not cached. fetch and cache.
			$fields = wp_remote_get( self::get_method_url( 'fields', array(
				'id' => $form_id,
			) ) );

			// Was there an error, connection is down? bail and try again later.
			if ( ! self::is_response_ok( $fields ) ) {
				return false;
			}

			// @todo should we cache results for longer than a day? not expire at all?
			set_transient( 'gem-form-' . $form_id, $fields = json_decode( wp_remote_retrieve_body( $fields ) ) );
		}

		return $fields;
	}

	/**
	 * Gets and sets the user data.
	 *
	 * @return false|array The user data or false.
	 */
	public static function get_user_level() {
		$username = GEM_Settings_Controls::get_option( 'username' );

		// No username entered by user?
		if ( ! $username ) {
			return false;
		}

		if ( false === ( $data = get_transient( 'gem-' . $username . '-account' ) ) ) {
			$data = wp_remote_get( self::get_method_url( 'account' ) );

			// If the request has failed for whatever reason.
			if ( ! self::is_response_ok( $data ) ) {
				return false;
			}

			$data = json_decode( wp_remote_retrieve_body( $data ) );
			$data = $data->result;

			// No need to expire at all.
			set_transient( 'gem-' . $username . '-account', $data );
		}

		return $data;
	}

	/**
	 * Gets the sign in redirect URL.
	 *
	 * @return false|string The URL if sign in is successful or false.
	 */
	public static function user_sign_in() {

		// Prepare the URL that includes our credentials.
		$response = wp_remote_get( self::get_method_url( 'signin', false ), array(
			'timeout' => 10,
		) );

		// Credentials are incorrect.
		if ( ! in_array( wp_remote_retrieve_response_code( $response ), self::$ok_codes ) ) {
			return false;
		}

		// Retrieve the token.
		return self::get_method_url( 'signin_redirect', array(
			'token' => wp_remote_retrieve_body( $response ),
		) );
	}

	/**
	 * Utility function for getting a URL for various API methods
	 *
	 * @param string $method The short of the API method.
	 * @param array  $params Extra parameters to pass on with the request.
	 * @param bool   $auth   Autentication array including API key and username.
	 *
	 * @return string The final URL to use for the request
	 */
	public static function get_method_url( $method, $params = array(), $auth = false ) {
		$auth = $auth ? $auth : array(
			'username' => GEM_Settings_Controls::get_option( 'username' ),
			'api_key' => GEM_Settings_Controls::get_option( 'api-key' ),
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

	/**
	 * Check for an OK response.
	 *
	 * @todo Does this really need to be by reference?
	 *
	 * @param array $request HTTP response by reference.
	 * @return bool
	 */
	public static function is_response_ok( &$request ) {
		return ( ! is_wp_error( $request ) && in_array( wp_remote_retrieve_response_code( $request ), self::$ok_codes ) );
	}
}
