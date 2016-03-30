<?php
namespace GEM;

require_once( 'testcase.php' );

class Test_GEM_Settings extends WP_GEMTestCase {

	/**
	 * PHP unit setup function
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		\WP_Http_Mock_Transport::$test_class = $this;
		add_action( 'http_api_transports', array( $this, 'get_transports' ) );
	}

	public function tearDown() {
		parent::tearDown();

		remove_action( 'http_api_transports', array( $this, 'get_transports' ) );
		\WP_Http_Mock_Transport::$test_class = null;
	}

	public function get_transports() {
		return array( 'Mock_Transport' );
	}

	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Settings', false ) );
	}

	public function test_construct() {
		$instance = new \GEM_Settings();
		$this->assertIsDefinedAction( 'admin_menu', array( $instance, 'action_admin_menu' ) );
		$this->assertIsDefinedAction( 'admin_init', array( $instance, 'register_settings' ) );
	}

	public function test_action_admin_menu() {
		$instance = new \GEM_Settings();
		$instance->action_admin_menu();

		$this->assertIsDefinedAction( 'load-', array( $instance, 'page_load' ) );
		$this->assertEquals( 'gem-settings', $instance->slug );
	}

	public function test_page_load() {
		global $wp_settings_errors;
		global $current_user;

		$gem = gem();
		$sample_data = 'test_data';
		$sample_data_2 = 'test_data_2';
		$sample_response = json_encode(
			array(
				'signups' => array( array( 'id' => 'the_id' ) ),
			)
		);
		$instance = new \GEM_Settings();
		$instance->page_load();
		$instance->action_admin_menu();

		$this->assertIsDefinedAction( 'in_admin_header', array( $instance, 'setup_help_tabs' ) );
		$this->assertArrayHasKey( 'gem-admin', wp_styles()->registered );

		// debug-reset action:
		$_GET['action'] = 'debug-reset';
		$gem->debug = false;
		update_option( $instance->slug, array( 'username' => 'user_name' ) );
		set_transient( 'gem-user_name-lists', $sample_data );
		$instance->page_load();
		$this->assertNotNull( get_option( $instance->slug, null ) );
		$this->assertEquals( $sample_data, get_transient( 'gem-user_name-lists' ) );

		$gem->debug = true;
		update_option( $instance->slug, array( 'username' => 'user_name' ) );
		set_transient( 'gem-user_name-lists', $sample_data );
		$instance->page_load();
		$this->assertNull( get_option( $instance->slug, null ) );
		$this->assertFalse( get_transient( 'gem-user_name-lists' ) );

		// debug-reset-transients action:
		$_GET['action'] = 'debug-reset-transients';
		$gem->debug = false;
		update_option( $instance->slug, array( 'username' => null ) );
		set_transient( 'gem-user_name-lists', $sample_data );
		$instance->page_load();
		$this->assertEquals( $sample_data, get_transient( 'gem-user_name-lists' ) );

		$gem->debug = true;
		update_option( $instance->slug, array( 'username' => 'user_name', 'api-key' => '1234' ) );
		set_transient( 'gem-user_name-lists', $sample_data );
		set_transient( 'gem-form-the_id', $sample_data_2 );
		\WP_Http_Mock_Transport::$expected_url = null;
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 200,
			),
			'body' => $sample_response,
		);
		$instance->page_load();
		$this->assertEquals( $sample_response, json_encode( get_transient( 'gem-user_name-lists' ) ) );
		$this->assertFalse( get_transient( 'gem-form-the_id' ) );
		$this->assertNotEmpty( get_settings_errors( $instance->slug ) );
		$this->assertEquals( 'gem-reset', get_settings_errors( $instance->slug )[0]['code'] );

		// refresh action:
		$_GET['action'] = 'refresh';
		update_option( $instance->slug, array( 'username' => 'user_name', 'api-key' => '1234' ) );
		set_transient( 'gem-user_name-lists', $sample_data );
		set_transient( 'gem-form-the_id', $sample_data_2 );
		\WP_Http_Mock_Transport::$expected_url = null;
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 200,
			),
			'body' => $sample_response,
		);
		$instance->page_load();
		$this->assertEquals( $sample_response, json_encode( get_transient( 'gem-user_name-lists' ) ) );
		$this->assertFalse( get_transient( 'gem-form-the_id' ) );

		// dismiss action:
		$_GET['action'] = 'dismiss';
		$current_user_object = new \WP_User();
		$current_user_object->ID = 12345;
		$current_user = $current_user_object;
		$instance->page_load();
		$this->assertEquals( 'show', get_user_meta( 12345, 'gem-dismiss' )[0] );
	}

	public function test_setup_help_tabs() {
		global $current_screen;

		$current_screen = \WP_Screen::get( 'test_gem' );

		$instance = new \GEM_Settings();
		$instance->setup_help_tabs();

		$tabs = $current_screen->get_help_tabs();
		$this->assertArrayHasKey( 'gem-overview', $tabs );
		$this->assertContains( 'GoDaddy', $current_screen->get_help_sidebar() );
	}

	public function test_register_settings() {
		global $new_whitelist_options;
		global $wp_settings_sections;
		global $wp_settings_fields;

		$instance = new \GEM_Settings();
		$instance->action_admin_menu();
		$instance->register_settings();

		$this->assertArrayHasKey( 'gem-options', $new_whitelist_options );
		$this->assertEquals( $instance->slug, $new_whitelist_options['gem-options'][0] );

		$this->assertArrayHasKey( $instance->slug, $wp_settings_sections );
		$this->assertArrayHasKey( 'general_settings_section', $wp_settings_sections[ $instance->slug ] );
		$this->assertEquals( 'general_settings_section', $wp_settings_sections[ $instance->slug ]['general_settings_section']['id'] );
		$this->assertEquals( 'Account Details', $wp_settings_sections[ $instance->slug ]['general_settings_section']['title'] );

		$this->assertArrayHasKey( $instance->slug, $wp_settings_fields );
		$this->assertArrayHasKey( 'general_settings_section', $wp_settings_fields[ $instance->slug ] );
		$this->assertArrayHasKey( 'username', $wp_settings_fields[ $instance->slug ]['general_settings_section'] );
		$this->assertArrayHasKey( 'api-key', $wp_settings_fields[ $instance->slug ]['general_settings_section'] );
		$this->assertArrayHasKey( 'display_powered_by', $wp_settings_fields[ $instance->slug ]['general_settings_section'] );
	}

	public function test_display_settings_page() {
		$instance = new \GEM_Settings();
		$instance->action_admin_menu();

		ob_start();
		$instance->display_settings_page();
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( "<input type='hidden' name='option_page' value='gem-options' />", $actual_output );
		$this->assertContains( '<input type="text" name="gem-settings[username]"', $actual_output );
		$this->assertContains( '<label for="gem-settings-username">GoDaddy Email Marketing Username</label>', $actual_output );
		$this->assertContains( '<label for="gem-settings-api-key">GoDaddy Email Marketing API Key</label>', $actual_output );
		$this->assertContains( '<input type="text" name="gem-settings[api-key]"', $actual_output );
		$this->assertContains( '<input type="checkbox" name="gem-settings[display_powered_by]" id="gem-settings[display_powered_by]" value="1"  />', $actual_output );
		$this->assertContains( '<a href="?action=debug-reset" class="button-secondary">Erase All Data</a>', $actual_output );
		$this->assertContains( '<a href="?action=debug-reset-transients" class="button-secondary">Erase Transients</a>', $actual_output );
	}

	public function test_validate() {
		global $wp_settings_errors;

		$sample_response = json_encode(
			array(
				'signups' => array( array( 'id' => 'the_id' ) ),
			)
		);
		$sample_response_2 = json_encode(
			array(
				'total' => 2,
			)
		);
		$instance = new \GEM_Settings();
		$instance->action_admin_menu();

		$wp_settings_errors = array();
		$actual_output = $instance->validate( array() );
		$this->assertEmpty( $actual_output );
		$this->assertNotEmpty( get_settings_errors( $instance->slug ) );
		$this->assertEquals( 'invalid-creds', get_settings_errors( $instance->slug )[0]['code'] );

		\WP_Http_Mock_Transport::$expected_url = null;
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 200,
			),
			'body' => $sample_response,
		);
		$wp_settings_errors = array();
		$creds = array( 'username' => 'user_name', 'api-key' => '1234' );
		$actual_output = $instance->validate( $creds );
		$this->assertEquals( $creds, $actual_output );
		$this->assertEmpty( get_settings_errors( $instance->slug ) );

		\WP_Http_Mock_Transport::$expected_url = null;
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 500,
			),
		);
		$wp_settings_errors = array();
		$creds = array( 'username' => 'user_name', 'api-key' => '1234' );
		$actual_output = $instance->validate( $creds );
		$this->assertEquals( $creds, $actual_output );
		$this->assertNotEmpty( get_settings_errors( $instance->slug ) );
		$this->assertEquals( 'invalid-creds', get_settings_errors( $instance->slug )[0]['code'] );

		\WP_Http_Mock_Transport::$expected_url = null;
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 200,
			),
			'body' => $sample_response_2,
		);
		$wp_settings_errors = array();
		$creds = array( 'username' => 'user_name', 'api-key' => '1234' );
		$actual_output = $instance->validate( $creds );
		$this->assertEquals( $creds, $actual_output );
		$this->assertNotEmpty( get_settings_errors( $instance->slug ) );
		$this->assertEquals( 'valid-creds', get_settings_errors( $instance->slug )[0]['code'] );
	}
}
