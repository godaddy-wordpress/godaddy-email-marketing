<?php
namespace GEM;

require_once( 'testcase.php' );

class Test_GEM_Official extends WP_GEMTestCase {

	/**
	 * @var \GEM_Official
	 */
	private $instance;

	/**
	 * PHP unit setup function
	 *
	 * @return void
	 */
	function setUp() {
		parent::setUp();
		$this->instance = \GEM_Official::instance();
	}

	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Official', false ) );
		$this->assertTrue( function_exists( 'gem' ) );
	}

	public function test_instance() {
		$this->assertInstanceOf( 'GEM_Official', $this->instance );
		$instance_second = \GEM_Official::instance();
		$this->assertSame( $this->instance, $instance_second );
	}

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

	public function test_setup_constants() {
		$this->assertTrue( defined( 'GEM_PLUGIN_DIR' ) );
		$this->assertTrue( defined( 'GEM_PLUGIN_URL' ) );
		$this->assertTrue( defined( 'GEM_PLUGIN_BASE' ) );
		$this->assertTrue( defined( 'GEM_VERSION' ) );

		$this->assertEquals( GEM_PLUGIN_DIR, plugin_dir_path( $this->plugin_file_path ) );
		$this->assertEquals( GEM_PLUGIN_URL, plugin_dir_url( $this->plugin_file_path ) );
		$this->assertEquals( GEM_PLUGIN_BASE, plugin_basename( $this->plugin_file_path ) );
		$this->assertEquals( GEM_VERSION, '1.1' );
	}

	public function test_requirements() {
		$this->assertTrue( class_exists( 'GEM_Dispatcher', false ) );
		$this->assertTrue( class_exists( 'GEM_Shortcode', false ) );
		$this->assertTrue( class_exists( 'GEM_Form_Renderer', false ) );
		$this->assertTrue( class_exists( 'GEM_Form_Fields', false ) );
		$this->assertTrue( class_exists( 'GEM_Settings', false ) );
		$this->assertTrue( class_exists( 'GEM_Settings_Controls', false ) );
		$this->assertTrue( class_exists( 'GEM_Form_Widget', false ) );
	}

	public function test_init() {
		global $wp_filter;

		$this->assertFalse( $this->instance->debug );
		$this->assertNull( $this->instance->settings );
		$this->assertArrayHasKey( 'wp_enqueue_scripts', $wp_filter );

		// test in admin case:
		define( 'WP_ADMIN', true );
		$second_instance = new \GEM_Official();
		$second_instance->init();
		$this->assertInstanceOf( 'GEM_Settings', $second_instance->settings );
	}

	public function test_register_shortcode() {
		global $shortcode_tags;

		$this->assertArrayHasKey( 'gem', $shortcode_tags );
		$this->assertArrayHasKey( 'GEM', $shortcode_tags );

		$this->assertEquals( $shortcode_tags['gem'], array( 'GEM_Shortcode', 'render' ) );
		$this->assertEquals( $shortcode_tags['GEM'], array( 'GEM_Shortcode', 'render' ) );
	}

	public function test_register_widget() {
		global $wp_widget_factory;

		$this->assertArrayHasKey( 'GEM_Form_Widget', $wp_widget_factory->widgets );
		$this->assertInstanceOf( 'GEM_Form_Widget', $wp_widget_factory->widgets['GEM_Form_Widget'] );
	}

	public function test_enqueue() {
		$this->instance->enqueue();
		$this->assertContains( 'gem-main', wp_scripts()->queue );
		$this->assertContains( 'function', wp_scripts()->queue );
		$this->assertContains( 'jquery-ui', wp_scripts()->queue );
		$this->assertArrayHasKey( 'gem-base', wp_styles()->registered );
		$this->assertArrayHasKey( 'jquery-ui', wp_styles()->registered );
	}

	public function test_action_links() {
		global $_parent_pages;

		$_parent_pages['gem-settings'] = 'settings_slug';

		$sample_array = array( 'the_key' => 'the_value' );
		$actual_result = $this->instance->action_links( $sample_array );

		$this->assertArrayHasKey( 'the_key', $actual_result );
		$this->assertEquals( 'the_value', $actual_result['the_key'] );
		$this->assertArrayHasKey( 'settings', $actual_result );
		$this->assertEquals( '<a href="http://example.org/wp-admin/settings_slug?page=gem-settings">Settings</a>', $actual_result['settings'] );
	}

	public function test_activate() {
		// nothing to test
	}

	public function test_deactivate() {
		update_option( 'gem-version', 'test_version' );

		$this->instance->deactivate();
		$this->assertNull( get_option( 'gem-version', null ) );
	}

	public function test_action_admin_notices() {
		global $current_screen;

		$current_screen = new \stdClass();
		$current_screen->id = 'test';

		ob_start();
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
