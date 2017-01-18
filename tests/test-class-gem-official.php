<?php
/**
 * Test GEM.
 *
 * @group gem
 */
class Test_GEM_Official extends WP_UnitTestCase {

	/**
	 * @var GEM_Official
	 */
	private $instance;

	/**
	 * Holds the plugin file path
	 *
	 * @var Plugin
	 */
	protected $plugin_file_path;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->plugin_file_path = $GLOBALS['_plugin_file'];
		$this->instance = GEM_Official::instance();
	}

	/**
	 * Filter to set the locale manually.
	 */
	public function locale( $locale ) {
		return 'es_ES';
	}

	/**
	 * Test that both GEM_Official & gem() exist.
	 */
	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Official', false ) );
		$this->assertTrue( function_exists( 'gem' ) );
	}

	/**
	 * Test instance.
	 *
	 * @see GEM_Official::instance()
	 */
	public function test_instance() {
		$this->assertInstanceOf( 'GEM_Official', $this->instance );
		$instance_second = GEM_Official::instance();
		$this->assertSame( $this->instance, $instance_second );
	}

	/**
	 * Test actions & filters.
	 *
	 * @see GEM_Official::setup_actions()
	 */
	public function test_setup_actions() {
		global $wp_filter;

		$this->assertArrayHasKey( 'init', $wp_filter );
		$this->assertArrayHasKey( 'widgets_init', $wp_filter );
		$this->assertArrayHasKey( 'admin_notices', $wp_filter );
		$this->assertArrayHasKey( 'plugins_loaded', $wp_filter );
		$this->assertArrayHasKey( 'plugin_action_links_' . plugin_basename( $this->plugin_file_path ), $wp_filter );
		$this->assertArrayHasKey( 'activate_' . plugin_basename( $this->plugin_file_path ), $wp_filter );
		$this->assertArrayHasKey( 'deactivate_' . plugin_basename( $this->plugin_file_path ), $wp_filter );
	}

	/**
	 * Test constants.
	 *
	 * @see GEM_Official::setup_constants()
	 */
	public function test_setup_constants() {
		$this->assertTrue( defined( 'GEM_PLUGIN_DIR' ) );
		$this->assertTrue( defined( 'GEM_PLUGIN_URL' ) );
		$this->assertTrue( defined( 'GEM_PLUGIN_BASE' ) );
		$this->assertTrue( defined( 'GEM_VERSION' ) );

		$this->assertEquals( GEM_PLUGIN_DIR, plugin_dir_path( $this->plugin_file_path ) );
		$this->assertEquals( GEM_PLUGIN_URL, plugin_dir_url( $this->plugin_file_path ) );
		$this->assertEquals( GEM_PLUGIN_BASE, plugin_basename( $this->plugin_file_path ) );
		$plugin_data = get_plugin_data( $this->plugin_file_path );
		$this->assertEquals( GEM_VERSION, $plugin_data['Version'] );
	}

	/**
	 * Test requirements.
	 *
	 * @see GEM_Official::requirements()
	 */
	public function test_requirements() {
		$this->assertTrue( class_exists( 'GEM_Dispatcher', false ) );
		$this->assertTrue( class_exists( 'GEM_Shortcode', false ) );
		$this->assertTrue( class_exists( 'GEM_Form_Renderer', false ) );
		$this->assertTrue( class_exists( 'GEM_Form_Fields', false ) );
		$this->assertTrue( class_exists( 'GEM_Settings', false ) );
		$this->assertTrue( class_exists( 'GEM_Settings_Controls', false ) );
		$this->assertTrue( class_exists( 'GEM_Form_Widget', false ) );
	}

	/**
	 * Test init.
	 *
	 * @see GEM_Official::init()
	 */
	public function test_init() {
		global $wp_filter;

		$this->assertFalse( $this->instance->debug );
		$this->assertNull( $this->instance->settings );
		$this->assertArrayHasKey( 'wp_enqueue_scripts', $wp_filter );

		// test in admin case:
		define( 'WP_ADMIN', true );
		$second_instance = new GEM_Official();
		$second_instance->init();
		$this->assertInstanceOf( 'GEM_Settings', $second_instance->settings );
	}

	/**
	 * Test i18n.
	 *
	 * @see GEM_Official::i18n()
	 */
	public function test_i18n() {
		unload_textdomain( 'godaddy-email-marketing' );
		$this->assertFalse( is_textdomain_loaded( 'godaddy-email-marketing' ) );

		add_filter( 'plugin_locale', array( $this, 'locale' ) );
		$this->instance->i18n();
		$this->assertTrue( is_textdomain_loaded( 'godaddy-email-marketing' ) );
		remove_filter( 'plugin_locale', array( $this, 'locale' ) );

		$this->assertTrue( unload_textdomain( 'godaddy-email-marketing' ) );
		$this->assertFalse( is_textdomain_loaded( 'godaddy-email-marketing' ) );
	}

	/**
	 * Test register shortcode.
	 *
	 * @see GEM_Official::register_shortcode()
	 */
	public function test_register_shortcode() {
		global $shortcode_tags;

		$this->instance->register_shortcode();
		$this->assertArrayHasKey( 'gem', $shortcode_tags );
		$this->assertArrayHasKey( 'GEM', $shortcode_tags );

		$shortcode = new GEM_Shortcode();
		$this->assertEquals( $shortcode_tags['gem'], array( $shortcode, 'render' ) );
		$this->assertEquals( $shortcode_tags['GEM'], array( $shortcode, 'render' ) );
		$this->assertTrue( has_shortcode( 'This is a blob with [gem id=123] in it', 'gem' ) );
		$this->assertTrue( has_shortcode( 'This is a blob with [GEM] in it', 'GEM' ) );
	}

	/**
	 * Test register widget.
	 *
	 * @see GEM_Official::register_widget()
	 */
	public function test_register_widget() {
		global $wp_widget_factory;

		$this->instance->register_widget();
		$this->assertArrayHasKey( 'GEM_Form_Widget', $wp_widget_factory->widgets );
		$this->assertInstanceOf( 'GEM_Form_Widget', $wp_widget_factory->widgets['GEM_Form_Widget'] );
	}

	/**
	 * Test scripts & styles are enqueued.
	 *
	 * @see GEM_Official::enqueue()
	 */
	public function test_enqueue() {
		$this->instance->enqueue();
		$this->assertTrue( wp_script_is( 'gem-main','queue' ) );
		$this->assertTrue( wp_style_is( 'gem-base', 'registered' ) );
	}

	/**
	 * Test action links.
	 *
	 * @see GEM_Official::action_links()
	 */
	public function test_action_links() {
		global $_parent_pages;

		$_parent_pages[ GEM_Settings::SLUG ] = 'settings_slug';

		$sample_array = array( 'the_key' => 'the_value' );
		$actual_result = $this->instance->action_links( $sample_array );

		$this->assertArrayHasKey( 'the_key', $actual_result );
		$this->assertEquals( 'the_value', $actual_result['the_key'] );
		$this->assertArrayHasKey( 'settings', $actual_result );
		$this->assertEquals( '<a href="http://example.org/wp-admin/settings_slug?page=gem-settings">Settings</a>', $actual_result['settings'] );
	}

	/**
	 * Test activate.
	 *
	 * @see GEM_Official::activate()
	 */
	public function test_activate() {
		// nothing to test
	}

	/**
	 * Test deactivate.
	 *
	 * @see GEM_Official::deactivate()
	 */
	public function test_deactivate() {
		update_option( 'gem-version', 'test_version' );

		$this->instance->deactivate();
		$this->assertNull( get_option( 'gem-version', null ) );
	}

	/**
	 * Test admin notices.
	 *
	 * @see GEM_Official::action_admin_notices()
	 */
	public function test_action_admin_notices() {
		global $current_screen;

		$current_screen = new stdClass();
		$current_screen->id = 'test';

		ob_start();
		$this->instance->action_admin_notices();
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertEmpty( $actual_output );

		ob_start();
		update_option( 'wpem_gem_notice', 1 );
		$current_screen->id = 'dashboard';
		$this->instance->action_admin_notices();
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( 'Your website has a superpower: Email marketing.', $actual_output );

		ob_start();
		delete_option( 'wpem_gem_notice' );
		$current_screen->id = 'dashboard';
		$this->instance->action_admin_notices();
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertEmpty( $actual_output );

		ob_start();
		delete_option( 'gem-version' );
		$current_screen->id = 'plugins';
		$this->instance->action_admin_notices();
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( 'GoDaddy Email Marketing is almost ready.', $actual_output );

		ob_start();
		update_option( 'gem-version', 'test_version' );
		$current_screen->id = 'plugins';
		$this->instance->action_admin_notices();
		$actual_output = ob_get_contents();
		ob_end_clean();

		$this->assertEmpty( $actual_output );
	}
}
