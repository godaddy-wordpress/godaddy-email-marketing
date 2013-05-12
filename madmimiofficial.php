<?php
/*
Plugin Name: Mad Mimi Official
Plugin URI: http://wordpress.org/extend/plugins/mad-mimi-official/
Description: This is the official Mad Mimi plugin for WordPress.
Author: Mad Mimi, LLC
Version: 1.0
Author URI: http://madmimi.com/
License: GPLv2 or later
*/

/**
 * 
 * @todo
 * 2. uninstall hook
 */

class MadMimi_Official {
	private static $instance;
	private static $basename;

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
		add_action( 'init', 		array( $this, 'init' ) );
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
		add_action( 'init', 		array( $this, 'register_shortcode'	), 20 );
		add_filter( 'plugin_action_links_' . self::$basename, array( $this, 'action_links' ), 10, 2 );
	}

	private function setup_constants() {
		// Plugin's main directory
		defined( 'MADMIMI_PLUGIN_DIR' )
			or define( 'MADMIMI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

		// Absolute URL to plugin's dir
		defined( 'MADMIMI_PLUGIN_URL' )
			or define( 'MADMIMI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		// Set up the base name
		isset( self::$basename ) || self::$basename = plugin_basename( __FILE__ );
	}

	// @todo include only some on is_admin()
	private function requirements() {
		require_once MADMIMI_PLUGIN_DIR . 'includes/class-dispatcher.php';
		// the shortcode
		require_once MADMIMI_PLUGIN_DIR . 'includes/class-shortcode.php';
		// the file renders the form
		require_once MADMIMI_PLUGIN_DIR . 'includes/render.php';
		// the main widget
		require_once MADMIMI_PLUGIN_DIR . 'includes/widget.php';
		// settings page, creds validation
		require_once MADMIMI_PLUGIN_DIR . 'includes/settings.php';
		// AJAX
		require_once MADMIMI_PLUGIN_DIR . 'includes/class-ajax.php';
	}

	public function init() {
		// enable debug mode?
		$this->debug = (bool) apply_filters( 'madmimi_debug', false );

		// initialize settings
		if ( is_admin() )
			$this->settings = new Mad_Mimi_Settings;

		// enqueue scripts n styles
		// @todo not on admin
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

		// register AJAX actions
		Mad_Mimi_AJAX::register();

		// Load our textdomain to allow multilingual translations
		load_plugin_textdomain( 'mimi', false, dirname( self::$basename ) . '/languages/' );
	}

	public function register_shortcode() {
		// register shortcode
		add_shortcode( 'madmimi', array( 'Mad_Mimi_Shortcode', 'render' ) );		
		add_shortcode( 'MadMimi', array( 'Mad_Mimi_Shortcode', 'render' ) );		
	}

	public function register_widget() {
		register_widget( 'Mad_Mimi_Form_Widget' );
	}

	public function enqueue() {
		// main JavaScript file
		wp_enqueue_script( 'mimi-main', plugins_url( 'js/mimi.js', __FILE__ ), array( 'jquery' ), false, true );
		
		// assistance CSS
		if ( apply_filters( 'mimi_include_basic_css', true ) )
			wp_enqueue_style( 'mimi-base', plugins_url( 'css/mimi.css', __FILE__ ) );

		// help strings
		wp_localize_script( 'mimi-main', 'MadMimi', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			
			'thankyou' => _x( 'Thank you for signing up! Please check your email.', 'ajax response', 'mimi' ),
			'oops' => _x( 'Oops! There was a problem. Please try again.', 'ajax response', 'mimi' ),
			'fix' => _x( 'There was a problem. Please fix the highlighted fields.', 'ajax response', 'mimi' ),
		) );
	}

	public function action_links( $links, $file ) {
		$links[] = sprintf( '<a href="%s">%s</a>', menu_page_url( 'mad-mimi-settings', false ), __( 'Settings' ) );
		return $links;
	}
}

function madmimi() {
	return MadMimi_Official::instance();
}
add_action( 'plugins_loaded', 'madmimi' );