<?php

/*
Plugin Name: Mad Mimi Sign Up Forms
Plugin URI: http://wordpress.org/extend/plugins/madmimi/
Description: The Official Mad Mimi plugin allows your site visitors to subscribe to your email lists
Author: Mad Mimi, LLC
Version: 1.1
Author URI: http://madmimi.com/
License: GPLv2 or later

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class MadMimi_Official {

	private static $instance;
	private static $basename;

	public $settings;
	public $debug;

	public static function instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
			self::$instance->setup_constants();
			self::$instance->requirements();
			self::$instance->setup_actions();
		}

		return self::$instance;

	}

	private function setup_actions() {

		add_action( 'init', 		 array( $this, 'init' ) );
		add_action( 'widgets_init',  array( $this, 'register_widget' ) );
		add_action( 'init', 		 array( $this, 'register_shortcode'	), 20 );
		add_action( 'admin_notices', array( $this, 'action_admin_notices' ) );
		add_filter( 'plugin_action_links_' . self::$basename, array( $this, 'action_links' ), 10 );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

	}

	private function setup_constants() {

		// Plugin's main directory
		defined( 'MADMIMI_PLUGIN_DIR' )
			or define( 'MADMIMI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

		// Absolute URL to plugin's dir
		defined( 'MADMIMI_PLUGIN_URL' )
			or define( 'MADMIMI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		// Absolute URL to plugin's dir
		defined( 'MADMIMI_PLUGIN_BASE' )
			or define( 'MADMIMI_PLUGIN_BASE', plugin_basename( __FILE__ ) );

		// Plugin's main directory
		defined( 'MADMIMI_VERSION' )
			or define( 'MADMIMI_VERSION', '1.1' );

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

	}

	public function init() {

		// enable debug mode?
		$this->debug = (bool) apply_filters( 'madmimi_debug', false );

		// initialize settings
		if ( is_admin() ) {
			$this->settings = new Mad_Mimi_Settings;
		}

		// enqueue scripts n styles
		// @todo not on admin
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

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
		wp_enqueue_script( 'mimi-main', plugins_url( 'js/mimi.js', __FILE__ ), array( 'jquery' ), MADMIMI_VERSION, true );

		// assistance CSS
		wp_enqueue_style( 'mimi-base', plugins_url( 'css/mimi.css', __FILE__ ), false, MADMIMI_VERSION );

		// help strings
		wp_localize_script( 'mimi-main', 'MadMimi', array(
			'thankyou' 				=> _x( 'Thank you for signing up!', 'ajax response', 'mimi' ),
			'thankyou_suppressed' 	=> _x( 'Thank you for signing up! Please check your email to confirm your subscription.', 'ajax response', 'mimi' ),
			'oops' 					=> _x( 'Oops! There was a problem. Please try again.', 'ajax response', 'mimi' ),
			'fix' 					=> _x( 'There was a problem. Please fill all required fields.', 'ajax response', 'mimi' ),
		) );

	}

	public function action_links( $actions ) {

		return array_merge(
			array(
				'settings' => sprintf( '<a href="%s">%s</a>', menu_page_url( 'mad-mimi-settings', false ), __( 'Settings' ) )
			),
			$actions
		);

	}

	public function activate() {
		// nothing to do here (for now)
	}

	public function deactivate() {
		delete_option( 'madmimi-version' );
	}

	public function action_admin_notices() {

		$screen = get_current_screen();

		if ( 'plugins' != $screen->id ) {
			return;
		}

		$version = get_option( 'madmimi-version' );

		if ( ! $version ) {

			update_option( 'madmimi-version', MADMIMI_VERSION ); ?>

			<div class="updated fade">
				<p>
					<strong><?php _e( 'Mad Mimi is almost ready.', 'mimi' ); ?></strong> <?php _e( 'You must enter your Mad Mimi username &amp; API key for it to work.', 'mimi' ); ?> &nbsp;
					<a class="button" href="<?php menu_page_url( 'mad-mimi-settings' ); ?>"><?php _e( 'Let\'s do it!', 'mimi' ); ?></a>
				</p>
			</div>

			<?php
		}
	}
}

function madmimi() {
	return MadMimi_Official::instance();
}
add_action( 'plugins_loaded', 'madmimi' );
