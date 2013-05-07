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
		require_once MADMIMI_PLUGIN_DIR . 'includes/dispatcher.php';
		// the shortcode
		require_once MADMIMI_PLUGIN_DIR . 'includes/shortcode.php';
		// the file renders the form
		require_once MADMIMI_PLUGIN_DIR . 'includes/render.php';
		// the main widget
		require_once MADMIMI_PLUGIN_DIR . 'includes/widget.php';
		// settings page, creds validation
		require_once MADMIMI_PLUGIN_DIR . 'includes/settings.php';
		
	}

	public function init() {
		// enable debug mode?
		$this->debug = (bool) apply_filters( 'madmimi_debug', false );

		// initialize settings
		if ( is_admin() )
			$this->settings = new AAL_Settings;

		// enqueue scripts n styles
		$this->enqueue();

		// register shortcode
		add_shortcode( 'mimi', array( 'Mad_Mimi_Shortcode', 'render' ) );
	}

	public function register_widget() {
		register_widget( 'Mad_Mimi_Form_Widget' );
	}

	public function enqueue() {
		wp_enqueue_script( 'mimi-main', plugins_url( "js/mimi.js", __FILE__ ), array( 'jquery' ), false, true );
	}
	
}

function madmimi() {
	return MadMimi_Official::instance();
}
add_action( 'plugins_loaded', 'madmimi' );