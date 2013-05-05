<?php
/*
Plugin Name: Mad Mimi Official
Plugin URI: http://wordpress.org/extend/plugins/mad-mimi-official/
Description: This is the official Mad Mimi plugin for WordPress.
Author: Mad Mimi
Version: 0.1
Author URI: http://madmimi.com/
License: GPLv2 or later
*/

final class MadMimi_Official {

	private static $instance;

	public $settings;
	public $debug;

	public function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new MadMimi_Official;
			self::$instance->setup_constants();
			self::$instance->requirements();
			self::$instance->setup_actions();
		}
		return self::$instance;
	}

	private function setup_actions() {
		add_action( 'init', 		array( $this, 'init' 			) );
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
	}

	private function setup_constants() {
		// Plugin's main directory
		defined( 'MADMIMI_PLUGIN_DIR' )
			or define( 'MADMIMI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

		// Absolute URL to plugin's dir
		defined( 'MADMIMI_PLUGIN_URL' )
			or define( 'MADMIMI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	private function requirements() {
		if ( is_admin() ) {
			// the main widget
			require_once MADMIMI_PLUGIN_DIR . 'includes/widget.php';
			// settings page, creds validation
			require_once MADMIMI_PLUGIN_DIR . 'includes/settings.php';
		}
	}

	public function init() {
		// enable debug mode?
		$this->debug = (bool) apply_filters( 'madmimi_debug', false );

		// initialize settings
		$this->settings = new AAL_Settings;
	}

	public function register_widget() {
		register_widget( 'Mad_Mimi_Form_Widget' );
	}

	public function fetch_forms( $username, $api_key = false ) {
		if ( ! ( $username && $api_key ) ) {
			$username = AAL_Settings_Controls::get_option( 'username' );
			$api_key = AAL_Settings_Controls::get_option( 'api-key' );
		}

		// Prepare the URL that includes our credentials
		$url = add_query_arg( array(
			'username' => $username,
			'api_key' => $api_key,
		), 'http://api.madmimi.com/signups.json' );

		$response = wp_remote_get( $url );

		// credentials are incorrect
		if ( 200 != wp_remote_retrieve_response_code( $response ) )
			return false;

		// cache results for 24hrs
		set_transient( "mimi-{$username}-lists", $data = json_decode( wp_remote_retrieve_body( $response ) ), defined( DAY_IN_SECONDS ) ? DAY_IN_SECONDS : 60 * 60 * 24 );

		return $data;
	}

	public function get_forms( $username = false ) {
		if ( false === ( $data = get_transient( "mimi-{$username}-lists" ) ) ) {
			$data = $this->fetch_forms( $username );
		}
		return $data;
	}
}

function madmimi() {
	return MadMimi_Official::instance();
}
add_action( 'plugins_loaded', 'madmimi' );