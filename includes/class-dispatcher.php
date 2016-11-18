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
	 * Transient expiration (1 day in seconds)
	 *
	 * @var int
	 */
	const EXPIRATION = 86400;

	/**
	 * HTTP response codes
	 *
	 * @var array
	 */
	private static $ok_codes = array( 200, 304 );

	/**
	 * Gets and sets the forms.
	 *
	 * @param string $username The username.
	 * @param string $api_key
	 *
	 * @return string $api_key  The API key.
	 */
	public static function fetch_forms( $username = '', $api_key = '' ) {
		if ( ! $username && ! $api_key ) {
			$username = GEM_Settings_Controls::get_option( 'username' );
			$api_key  = GEM_Settings_Controls::get_option( 'api-key' );
		}

		if ( ! $username || ! $api_key ) {
			return false;
		}

		$auth = array(
			'username' => $username,
			'api_key'  => $api_key,
		);

		// Prepare the URL that includes our credentials.
		$response = wp_remote_get( self::get_method_url( 'forms', false, $auth ), array(
			'timeout' => 10,
		) );

		// Delete all existing transients for this user.
		delete_transient( 'gem-' . $username . '-lists' );

		// Credentials are incorrect.
		if ( ! in_array( wp_remote_retrieve_response_code( $response ), self::$ok_codes, true ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );
		set_transient( 'gem-' . $username . '-lists', $data, self::EXPIRATION );

		return $data;
	}

	/**
	 * Add a default form.
	 *
	 * @return bool True on success or false on failue.
	 */
	public static function add_default_form() {
		$username = GEM_Settings_Controls::get_option( 'username' );
		$api_key  = GEM_Settings_Controls::get_option( 'api-key' );

		if ( ! $username || ! $api_key ) {
			return false;
		}

		// Prepare the URL that includes our credentials.
		$response = wp_remote_post( self::get_api_base_url( 'api/v3/signupForms' ), array(
			'method' => 'POST',
			'timeout' => 10,
			'body' => array(
				'username' => $username,
				'api_key' => $api_key,
				'name' => 'Signup Form',
				'integration' => 'WordPress',
				'hidden' => false,
				'subscriberListName' => 'WordPress',
			),
		) );

		// Credentials are correct.
		if ( self::is_response_ok( $response ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Gets the forms.
	 *
	 * @return array|false The form fields array or false.
	 */
	public static function get_forms() {
		$username = GEM_Settings_Controls::get_option( 'username' );

		if ( ! $username ) {
			return false;
		}

		if ( false === ( $data = get_transient( 'gem-' . $username . '-lists' ) ) ) {
			$data = self::fetch_forms();
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
		if ( false === ( $data = get_transient( 'gem-form-' . $form_id ) ) ) {

			// Fields are not cached. fetch and cache.
			$response = wp_remote_get( self::get_method_url( 'fields', array(
				'id' => $form_id,
			) ) );

			// Was there an error, connection is down? bail and try again later.
			if ( ! self::is_response_ok( $response ) ) {
				return false;
			}

			$data = json_decode( wp_remote_retrieve_body( $response ) );
			set_transient( 'gem-form-' . $form_id, $data, self::EXPIRATION );
		}

		return $data;
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
			$data = false;
			$request = wp_remote_get( self::get_method_url( 'account' ) );

			// If the request has failed for whatever reason.
			if ( ! self::is_response_ok( $request ) ) {
				return false;
			}

			$body = json_decode( wp_remote_retrieve_body( $request ) );

			if ( isset( $body->result ) ) {
				$data = $body->result;
			}

			// No need to expire at all.
			if ( $data ) {
				set_transient( 'gem-' . $username . '-account', $data );
			}
		}

		return $data;
	}

	/**
	 * Return the API base URL.
	 *
	 * @param  string $path (optional)
	 *
	 * @return string
	 */
	public static function get_api_base_url( $path = '' ) {

		/**
		 * Filter the API base URL.
		 *
		 * @since 1.1.1
		 *
		 * @var string
		 */
		$url = (string) apply_filters( 'gem_api_base_url', 'https://gem.godaddy.com/' );

		return trailingslashit( $url ) . $path;

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
		}

		return self::get_api_base_url( $path );
	}

	/**
	 * Check for an OK response.
	 *
	 * @param array $request HTTP response by reference.
	 * @return bool
	 */
	public static function is_response_ok( $request ) {
		return ( ! is_wp_error( $request ) && in_array( wp_remote_retrieve_response_code( $request ), self::$ok_codes, true ) );
	}
}
