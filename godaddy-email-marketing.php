<?php
/**
 * Plugin Name: GoDaddy Email Marketing Signup Forms
 * Plugin URI: https://wordpress.org/plugins/goaddy-email-marketing-sign-up-forms/
 * Description: Add the GoDaddy Email Marketing signup form to your WordPress site! Easy to set up, the plugin allows your site visitors to subscribe to your email lists.
 * Author: GoDaddy, LLC
 * Version: 1.0.4
 * Author URI: https://godaddy.com/business/email-marketing
 * License: GPLv2 or later
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package GEM
 */

/**
 * GoDaddy Email Marketing.
 *
 * @since 1.0
 */
class GEM_Official {

	/**
	 * GEM_Official instance.
	 *
	 * @var GEM_Official
	 */
	private static $instance;

	/**
	 * Plugin basename.
	 *
	 * @var string
	 */
	private static $basename;

	/**
	 * GEM_Settings instance.
	 *
	 * @var GEM_Settings
	 */
	public $settings;

	/**
	 * Turns on debugging.
	 *
	 * @var bool
	 */
	public $debug;

	/**
	 * Class instance.
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
			self::$instance->setup_constants();
			self::$instance->requirements();
			self::$instance->setup_actions();
		}
		return self::$instance;
	}

	/**
	 * Adds actions and filters.
	 */
	private function setup_actions() {
		add_action( 'init',          array( $this, 'init' ) );
		add_action( 'widgets_init',  array( $this, 'register_widget' ) );
		add_action( 'init',          array( $this, 'register_shortcode' ), 20 );
		add_action( 'admin_notices', array( $this, 'action_admin_notices' ) );
		add_filter( 'plugin_action_links_' . self::$basename, array( $this, 'action_links' ), 10 );

		register_activation_hook( __FILE__,   array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Creates the constants.
	 */
	private function setup_constants() {

		// Plugin's main directory.
		defined( 'GEM_PLUGIN_DIR' )
			or define( 'GEM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

		// Absolute URL to plugin's dir.
		defined( 'GEM_PLUGIN_URL' )
			or define( 'GEM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		// Absolute URL to plugin's dir.
		defined( 'GEM_PLUGIN_BASE' )
			or define( 'GEM_PLUGIN_BASE', plugin_basename( __FILE__ ) );

		// Plugin's main directory.
		defined( 'GEM_VERSION' )
			or define( 'GEM_VERSION', '1.0.4' );

		// Set up the base name.
		isset( self::$basename ) || self::$basename = plugin_basename( __FILE__ );
	}

	/**
	 * Loads the PHP files.
	 *
	 * @todo include only some on is_admin()
	 */
	private function requirements() {

		// The Dispatcher.
		require_once GEM_PLUGIN_DIR . 'includes/class-dispatcher.php';

		// The shortcode.
		require_once GEM_PLUGIN_DIR . 'includes/class-shortcode.php';

		// The file renders the form.
		require_once GEM_PLUGIN_DIR . 'includes/render.php';

		// The main widget.
		require_once GEM_PLUGIN_DIR . 'includes/widget.php';

		// Settings page, creds validation.
		require_once GEM_PLUGIN_DIR . 'includes/settings.php';
	}

	/**
	 * Initializes the plugin.
	 */
	public function init() {

		// Enable debug mode?
		$this->debug = (bool) apply_filters( 'gem_debug', false );

		// Initialize settings.
		if ( is_admin() ) {
			$this->settings = new GEM_Settings;
		}

		// Enqueue scripts and styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

		// Load our textdomain to allow multilingual translations.
		load_plugin_textdomain( 'gem', false, dirname( self::$basename ) . '/languages/' );
	}

	/**
	 * Registers the shortcode.
	 */
	public function register_shortcode() {

		// Register shortcode.
		add_shortcode( 'gem', array( 'GEM_Shortcode', 'render' ) );
		add_shortcode( 'GEM', array( 'GEM_Shortcode', 'render' ) );
	}

	/**
	 * Registers the widget.
	 */
	public function register_widget() {
		register_widget( 'GEM_Form_Widget' );
	}

	/**
	 * Enqueues scripts and styles.
	 */
	public function enqueue() {

		// Main JavaScript file.
		wp_enqueue_script( 'gem-main', plugins_url( 'js/gem.js', __FILE__ ), array( 'jquery' ), GEM_VERSION, true );

		// Datepicker JavaScript file.
		wp_enqueue_script( 'function', plugins_url( 'js/function.js', __FILE__ ), array( 'jquery' ), GEM_VERSION, true );

		// JQuery-ui.
		wp_enqueue_script( 'jquery-ui', 'http://code.jquery.com/ui/1.11.4/jquery-ui.js', array( 'jquery' ), '1.11.4' );

		// Assistance CSS.
		wp_enqueue_style( 'gem-base', plugins_url( 'css/gem.css', __FILE__ ), false, GEM_VERSION );

		// Datepicker CSS.
		wp_enqueue_style( 'jquery-ui', plugins_url( 'css/jquery-ui.css', __FILE__ ), true, GEM_VERSION );

		// Help strings.
		wp_localize_script( 'gem-main', 'GEM', array(
			'thankyou' 				=> _x( 'Thank you for signing up!', 'ajax response', 'gem' ),
			'thankyou_suppressed' 	=> _x( 'Thank you for signing up! Please check your email to confirm your subscription.', 'ajax response', 'gem' ),
			'oops' 					=> _x( 'Oops! There was a problem. Please try again.', 'ajax response', 'gem' ),
			'fix' 					=> _x( 'There was a problem. Please fill all required fields.', 'ajax response', 'gem' ),
		) );
	}

	/**
	 * Adds the settings page to the action links.
	 *
	 * @param array $actions An array of plugin action links.
	 */
	public function action_links( $actions ) {
		return array_merge(
			array(
				'settings' => sprintf( '<a href="%s">%s</a>', menu_page_url( 'gem-settings', false ), __( 'Settings', 'gem' ) ),
			),
			$actions
		);
	}

	/**
	 * Nothing to do here (for now).
	 */
	public function activate() {}

	/**
	 * Deletes the gem version.
	 */
	public function deactivate() {
		delete_option( 'gem-version' );
	}

	/**
	 * Displays the admin notice.
	 */
	public function action_admin_notices() {
		$screen = get_current_screen();

		if ( 'plugins' != $screen->id ) {
			return;
		}

		$version = get_option( 'gem-version' );

		if ( ! $version ) {
			update_option( 'gem-version', GEM_VERSION ); ?>

			<div class="updated fade">
				<p>
					<strong><?php esc_html_e( 'GoDaddy Email Marketing is almost ready.', 'gem' ); ?></strong> <?php esc_html_e( 'You must enter your username &amp; API key for it to work.', 'gem' ); ?> &nbsp;
					<a class="button" href="<?php menu_page_url( 'gem-settings' ); ?>"><?php esc_html_e( 'Let\'s do it!', 'gem' ); ?></a>
				</p>
			</div>

			<?php
		}
	}
}

/**
 * GoDaddy Email Marketing instance.
 *
 * @since 1.0
 */
function gem() {
	return GEM_Official::instance();
}
add_action( 'plugins_loaded', 'gem' );
