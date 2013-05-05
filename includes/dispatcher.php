<?php

class Mad_Mimi_Dispatcher {

	const base_api = 'http://api.madmimi.com/';

	private static $ok_codes = array( 200, 304 );

	public static function fetch_forms( $username, $api_key = false ) {
		if ( ! ( $username && $api_key ) ) {
			$username = AAL_Settings_Controls::get_option( 'username' );
			$api_key = AAL_Settings_Controls::get_option( 'api-key' );
		}

		// Prepare the URL that includes our credentials
		$response = wp_remote_get( self::get_method_url( 'forms' ) );

		// credentials are incorrect
		if ( ! in_array( wp_remote_retrieve_response_code( $response ), self::$ok_codes ) )
			return false;

		// cache results for 24hrs
		set_transient( "mimi-{$username}-lists", $data = json_decode( wp_remote_retrieve_body( $response ) ), defined( DAY_IN_SECONDS ) ? DAY_IN_SECONDS : 60 * 60 * 24 );

		return $data;
	}

	public static function get_forms( $username = false ) {
		$username = $username ? $username : AAL_Settings_Controls::get_option( 'username' );

		if ( false === ( $data = get_transient( "mimi-{$username}-lists" ) ) ) {
			$data = self::fetch_forms( $username );
		}
		return $data;
	}

	public static function get_fields( $form_id ) {
		if ( false === ( $fields = get_transient( "mimi-form-$form_id" ) ) ) {
			// fields are not cached. fetch and cache.
			$fields = wp_remote_get( self::get_method_url( 'fields', array(
				'id' => $form_id,
			) ) );

			// was there an error, connection is down? bail and try again later.
			if ( is_wp_error( $fields ) || ! in_array( wp_remote_retrieve_response_code( $fields ), self::$ok_codes ) )
				return false;

			// @TODO: should we cache results for longer than a day?
			set_transient( "mimi-form-$form_id", $fields = json_decode( wp_remote_retrieve_body( $fields ) ), DAY_IN_SECONDS );
		}

		return $fields;
	}

	public static function get_method_url( $method, $params = false ) {
		$auth = array(
			'username' => AAL_Settings_Controls::get_option( 'username' ),
			'api_key' => AAL_Settings_Controls::get_option( 'api-key' ),
		);

		extract( (array) $params, EXTR_SKIP );

		$final = '';

		switch ( $method ) {
			case 'forms':
				$final = add_query_arg( $auth, "signups.json" );
				break;

			case 'fields':
				$final = add_query_arg( $auth, "signups/{$id}.json" );
				break;
		}

		return self::base_api . $final;
	}
}