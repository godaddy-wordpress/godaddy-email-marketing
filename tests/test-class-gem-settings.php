<?php
/**
 * Test Settings.
 *
 * @group settings
 */
class Test_GEM_Settings extends WP_UnitTestCase {

	/**
	 * Mock_Http_Response instance.
	 *
	 * @var Mock_Http_Response
	 */
	private $http_response;

	/**
	 * Load Mock_Http_Response
	 */
	public static function setUpBeforeClass() {
		require_once( 'mock-http-response.php' );
	}

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->http_response = new Mock_Http_Response();
		Mock_Http_Response::$test_class = $this;
		add_filter( 'pre_http_request', array( $this->http_response, 'filter_response' ), 10, 3 );
	}

	/**
	 * Teardown.
	 *
	 * @inheritdoc
	 */
	public function tearDown() {
		global $wp_settings_errors;
		parent::tearDown();

		remove_filter( 'pre_http_request', array( $this->http_response, 'filter_response' ), 10, 3 );
		Mock_Http_Response::$data = null;
		Mock_Http_Response::$test_class = null;
		Mock_Http_Response::$expected_args = null;
		Mock_Http_Response::$expected_url = null;
		$wp_settings_errors = array();
	}

	/**
	 * Add sample data.
	 *
	 * @param string $slug The setting option slug.
	 */
	public function set_data( $slug = '' ) {
		update_option( $slug, array( 'username' => 'user_name', 'api-key' => '1234' ) );
		set_transient( 'gem-form-123', json_decode( '{"id":123,"name":"Signup Form","fields":{"field_a":{"type":"string","field_type":"string","name":"the_name_a","required":false,"display":"text_a"},"field_b":{"type":"checkbox","field_type":"checkbox","required":true,"name":"the_name_b","value":"the_value","display":"text_b"}},"submit":"the_url","button_text":"button_text"}' ) );
		set_transient( 'gem-user_name-lists', json_decode( '{"total":1,"signups":[{"id":123,"name":"Signup Form","thumbnail":"the_url","url":"the_url"}]}' ) );
	}

	/**
	 * Delete sample data.
	 */
	public function delete_data( $slug ) {
		delete_option( $slug );
		delete_transient( 'gem-form-123' );
		delete_transient( 'gem-user_name-lists' );
	}

	/**
	 * Test that GEM_Settings exists.
	 */
	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Settings', false ) );
	}

	/**
	 * Test constructor.
	 *
	 * @see GEM_Settings::__construct()
	 */
	public function test_construct() {
		$instance = new GEM_Settings();
		$this->assertEquals( 10, has_action( 'admin_menu', array( $instance, 'action_admin_menu' ) ) );
		$this->assertEquals( 10, has_action( 'admin_init', array( $instance, 'register_settings' ) ) );
	}

	/**
	 * Test menu.
	 *
	 * @see GEM_Settings::action_admin_menu()
	 */
	public function test_action_admin_menu() {
		$instance = new GEM_Settings();
		$instance->action_admin_menu();

		$this->assertEquals( 10, has_action( 'load-' . $instance->hook, array( $instance, 'page_load' ) ) );
		$this->assertEquals( GEM_Settings::SLUG, $instance->slug );
	}

	/**
	 * Test styles are enqueued.
	 *
	 * @see GEM_Settings::admin_enqueue_style()
	 */
	public function test_admin_enqueue_style() {
		$instance = new GEM_Settings();
		$instance->admin_enqueue_style();

		$this->assertTrue( wp_style_is( 'gem-admin', 'enqueued' ) );
	}

	/**
	 * Test scripts are enqueued.
	 *
	 * @see GEM_Settings::admin_enqueue_scripts()
	 */
	public function test_admin_enqueue_scripts() {
		$instance = new GEM_Settings();
		$instance->admin_enqueue_scripts();

		$this->assertTrue( wp_script_is( 'gem-admin', 'enqueued' ) );
	}

	/**
	 * Test actions are loaded.
	 *
	 * @see GEM_Settings::page_load()
	 */
	public function test_page_load() {
		$instance = new GEM_Settings();

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$this->assertTrue( current_user_can( 'manage_options' ) );

		$instance->page_load();
		$instance->action_admin_menu();

		$this->assertEquals( 10, has_action( 'in_admin_header', array( $instance, 'setup_help_tabs' ) ) );
		$this->assertEquals( 10, has_action( 'admin_print_styles-' . $instance->hook, array( $instance, 'admin_enqueue_style' ) ) );
		$this->assertEquals( 10, has_action( 'admin_print_scripts-' . $instance->hook, array( $instance, 'admin_enqueue_scripts' ) ) );
	}

	/**
	 * Test debug reset.
	 *
	 * @see GEM_Settings::page_load()
	 */
	public function test_page_load_debug_reset() {
		$gem = gem();
		$instance = new GEM_Settings();
		$instance->action_admin_menu();
		$this->set_data( $instance->slug );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// debug-reset action:
		$_GET['action']       = 'debug-reset';
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'gem_settings_hard_reset_nonce' );
		$gem->debug = false;
		$instance->page_load();
		$this->assertNotNull( get_option( $instance->slug, null ) );
		$this->assertObjectHasAttribute( 'id', get_transient( 'gem-form-123' ) );
		$this->assertObjectHasAttribute( 'total', get_transient( 'gem-user_name-lists' ) );

		$gem->debug = true;
		$instance->page_load();
		$this->assertFalse( get_option( $instance->slug ) );
		$this->assertFalse( get_transient( 'gem-form-123' ) );
		$this->assertFalse( get_transient( 'gem-user_name-lists' ) );
		$errors = get_settings_errors( $instance->slug );
		$this->assertNotEmpty( $errors );
		$this->assertEquals( 'debug-reset', $errors[0]['code'] );

		$this->delete_data( $instance->slug );
	}

	/**
	 * Test debug reset transients.
	 *
	 * @see GEM_Settings::page_load()
	 */
	public function test_page_load_debug_reset_transients() {
		$gem = gem();
		$instance = new GEM_Settings();
		$instance->action_admin_menu();
		$this->set_data( $instance->slug );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// debug-reset-transients action:
		$_GET['action']       = 'debug-reset-transients';
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'gem_settings_reset_transients_nonce' );
		$gem->debug = false;
		update_option( $instance->slug, array( 'username' => null ) );
		$instance->page_load();
		$this->assertObjectHasAttribute( 'id', get_transient( 'gem-form-123' ) );
		$this->assertObjectHasAttribute( 'total', get_transient( 'gem-user_name-lists' ) );

		$gem->debug = true;
		update_option( $instance->slug, array( 'username' => 'user_name', 'api-key' => '1234' ) );
		$instance->page_load();
		$errors = get_settings_errors( $instance->slug );
		$this->assertFalse( get_transient( 'gem-form-123' ) );
		$this->assertFalse( get_transient( 'gem-user_name-lists' ) );
		$this->assertNotEmpty( $errors );
		$this->assertEquals( 'debug-reset-transients', $errors[0]['code'] );

		$this->delete_data( $instance->slug );
	}

	/**
	 * Test refresh.
	 *
	 * @see GEM_Settings::page_load()
	 */
	public function test_page_load_refresh() {
		$instance = new GEM_Settings();
		$instance->action_admin_menu();
		$this->set_data( $instance->slug );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// refresh action:
		$_GET['action']       = 'refresh';
		$_REQUEST['_wpnonce'] = wp_create_nonce( 'gem_settings_refresh_nonce' );
		$instance->page_load();
		$errors = get_settings_errors( $instance->slug );
		$this->assertFalse( get_transient( 'gem-form-123' ) );
		$this->assertFalse( get_transient( 'gem-user_name-lists' ) );
		$this->assertNotEmpty( $errors );
		$this->assertEquals( 'gem-refresh', $errors[0]['code'] );

		$this->delete_data( $instance->slug );
	}

	/**
	 * Test refresh message.
	 *
	 * @see GEM_Settings::page_load()
	 */
	public function test_page_load_transient_gem_refresh() {
		$instance = new GEM_Settings();
		set_transient( 'gem-refresh', true, 30 );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$instance->action_admin_menu();
		$instance->page_load();

		$errors = get_settings_errors( $instance->slug );
		$this->assertNotEmpty( $errors );
		$this->assertEquals( 'gem-refresh', $errors[0]['code'] );
	}

	/**
	 * Test invalid credentials message.
	 *
	 * @see GEM_Settings::page_load()
	 */
	public function test_page_load_transient_gem_invalid_creds() {
		$instance = new GEM_Settings();
		set_transient( 'gem-invalid-creds', true, 30 );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$instance->action_admin_menu();
		$instance->page_load();

		$errors = get_settings_errors( $instance->slug );
		$this->assertNotEmpty( $errors );
		$this->assertEquals( 'gem-invalid-creds', $errors[0]['code'] );
	}

	/**
	 * Test valid credentials message.
	 *
	 * @see GEM_Settings::page_load()
	 */
	public function test_page_load_transient_gem_valid_creds() {
		$instance = new GEM_Settings();
		set_transient( 'gem-valid-creds', true, 30 );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$instance->action_admin_menu();
		$instance->page_load();

		$errors = get_settings_errors( $instance->slug );
		$this->assertNotEmpty( $errors );
		$this->assertEquals( 'gem-valid-creds', $errors[0]['code'] );
	}

	/**
	 * Test settings updated message.
	 *
	 * @see GEM_Settings::page_load()
	 */
	public function test_page_load_transient_gem_settings_updated() {
		$instance = new GEM_Settings();
		set_transient( 'gem-settings-updated', true, 30 );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$instance->action_admin_menu();
		$instance->page_load();

		$errors = get_settings_errors( $instance->slug );
		$this->assertNotEmpty( $errors );
		$this->assertEquals( 'gem-settings-updated', $errors[0]['code'] );
	}

	/**
	 * Test empty credentials message.
	 *
	 * @see GEM_Settings::page_load()
	 */
	public function test_page_load_transient_gem_empty_creds() {
		$instance = new GEM_Settings();
		set_transient( 'gem-empty-creds', true, 30 );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$instance->action_admin_menu();
		$instance->page_load();

		$errors = get_settings_errors( $instance->slug );
		$this->assertNotEmpty( $errors );
		$this->assertEquals( 'gem-empty-creds', $errors[0]['code'] );
	}

	/**
	 * Test help tabs.
	 *
	 * @see GEM_Settings::setup_help_tabs()
	 */
	public function test_setup_help_tabs() {
		global $current_screen;

		$current_screen = WP_Screen::get( 'test_gem' );

		$instance = new GEM_Settings();
		$instance->setup_help_tabs();

		$tabs = $current_screen->get_help_tabs();
		$this->assertArrayHasKey( 'gem-overview', $tabs );
		$this->assertContains( 'GoDaddy', $current_screen->get_help_sidebar() );
	}

	/**
	 * Test settings get registered.
	 *
	 * @see GEM_Settings::register_settings()
	 */
	public function test_register_settings() {
		global $new_whitelist_options;
		global $wp_settings_sections;
		global $wp_settings_fields;

		$instance = new GEM_Settings();
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

	/**
	 * Test settings page.
	 *
	 * @see GEM_Settings::display_settings_page()
	 */
	public function test_display_settings_page() {
		set_transient( 'gem-user_name-account', true );
		$this->set_data( GEM_Settings::SLUG );
		gem()->debug = true;
		$instance = new GEM_Settings();
		$instance->action_admin_menu();
		$instance->register_settings();
		update_option( $instance->slug, array(
			'username' => 'user_name',
			'api-key' => '1234',
			'display_powered_by' => 1,
			'debug' => 1,
		) );
		update_option( 'gem-valid-creds', true );

		ob_start();
		$instance->display_settings_page();
		$actual_output = ob_get_contents();
		ob_end_clean();

		$this->assertContains( "<input type='hidden' name='option_page' value='gem-options' />", $actual_output );
		$this->assertContains( '<input type="text" name="gem-settings[username]"', $actual_output );
		$this->assertContains( '<label for="gem-settings-username">Username</label>', $actual_output );
		$this->assertContains( '<label for="gem-settings-api-key">API Key</label>', $actual_output );
		$this->assertContains( '<input type="text" name="gem-settings[api-key]"', $actual_output );
		$this->assertContains( '<input type="checkbox" name="gem-settings[display_powered_by]" id="gem-settings[display_powered_by]" value="1"  checked=\'checked\' />', $actual_output );
		$this->assertContains( '<input type="checkbox" name="gem-settings[debug]" id="gem-settings[debug]" value="1"  checked=\'checked\' />', $actual_output );
		$this->assertContains( 'class="button-secondary">Erase All Data</a>', $actual_output );
		$this->assertContains( 'class="button-secondary">Erase Transients</a>', $actual_output );
		$this->assertContains( '<a href="https://gem.godaddy.com/signups" target="_blank" class="button">Create a New Signup Form</a>', $actual_output );

		$this->delete_data( $instance->slug );
	}

	/**
	 * Test settings page empty forms.
	 *
	 * @see GEM_Settings::display_settings_page()
	 */
	public function test_display_settings_page_empty_forms() {
		set_transient( 'gem-user_name-account', true );
		$instance = new GEM_Settings();
		$instance->action_admin_menu();
		$instance->register_settings();
		update_option( $instance->slug, array(
			'username' => 'user_name',
			'api-key' => '1234',
			'display_powered_by' => 1,
			'debug' => 1,
		) );
		update_option( 'gem-valid-creds', true );

		ob_start();
		$instance->display_settings_page();
		$actual_output = ob_get_contents();
		ob_end_clean();

		$this->assertContains( 'Refresh Forms', $actual_output );

		$this->delete_data( $instance->slug );
	}

	/**
	 * Test settings page forms.
	 *
	 * @see GEM_Settings::display_settings_page()
	 */
	public function test_display_settings_page_forms() {
		Mock_Http_Response::$data = array(
			'response' => array(
				'code' => 200,
			),
			'body' => '{"total":1,"signups":[{"id":"54321", "name":"Test Form", "url":"http://sample.org"}]}',
		);
		GEM_Settings_Controls::update_option( 'username', 'tester' );
		GEM_Settings_Controls::update_option( 'api-key', '12345' );
		$instance = new GEM_Settings();
		$instance->action_admin_menu();

		ob_start();
		$instance->display_settings_page();
		$actual_output = ob_get_contents();
		ob_end_clean();

		$this->assertContains( '54321', $actual_output );
		$this->assertContains( 'Test Form', $actual_output );
		$this->assertContains( 'http://sample.org', $actual_output );

		delete_option( GEM_Settings::SLUG );
		delete_transient( 'gem-tester-account' );
		delete_transient( 'gem-tester-lists' );
		delete_transient( 'gem-form-54321' );
	}

	/**
	 * Test validate false creds.
	 *
	 * @see GEM_Settings::validate()
	 */
	public function test_validate_sets_gem_valid_creds_to_false() {
		global $wp_settings_errors;

		$instance = new GEM_Settings();
		$instance->action_admin_menu();

		$wp_settings_errors = array();
		$expected_output = array(
			'username' => '',
			'api-key' => '',
			'display_powered_by' => 0,
			'debug' => 0,
		);
		$actual_output = $instance->validate( array() );
		$errors = get_settings_errors( $instance->slug );
		$this->assertEquals( $expected_output, $actual_output );
		$this->assertFalse( get_option( 'gem-valid-creds' ) );

		Mock_Http_Response::$data = array(
			'response' => array(
				'code' => 500,
			),
		);
		$wp_settings_errors = array();
		$creds = array( 'username' => 'user_name', 'api-key' => '1234' );
		$expected_output = array(
			'username' => 'user_name',
			'api-key' => '1234',
			'display_powered_by' => 0,
			'debug' => 0,
		);
		$actual_output = $instance->validate( $creds );
		$errors = get_settings_errors( $instance->slug );
		$this->assertEquals( $expected_output, $actual_output );
		$this->assertFalse( get_option( 'gem-valid-creds' ) );

		Mock_Http_Response::$data = array(
			'response' => array(
				'code' => 200,
			),
			'body' => '{"total":2}',
		);
		$wp_settings_errors = array();
		$creds = array( 'username' => 'user_name', 'api-key' => '1234' );
		$expected_output = array(
			'username' => 'user_name',
			'api-key' => '1234',
			'display_powered_by' => 0,
			'debug' => 0,
		);
		$actual_output = $instance->validate( $creds );
		$errors = get_settings_errors( $instance->slug );
		$this->assertEquals( $expected_output, $actual_output );
		$this->assertFalse( get_option( 'gem-valid-creds' ) );
	}

	/**
	 * Test validate true creds.
	 *
	 * @see GEM_Settings::validate()
	 */
	public function test_validate_sets_gem_valid_creds_to_true() {
		Mock_Http_Response::$data = array(
			'response' => array(
				'code' => 200,
			),
			'body' => '{"total":1,"signups":[{"id":"54321", "name":"Test Form", "url":"http://sample.org"}]}',
		);

		$instance = new GEM_Settings();
		$instance->action_admin_menu();

		$creds = array( 'username' => 'user_name', 'api-key' => '1234' );
		$expected_output = array(
			'username' => 'user_name',
			'api-key' => '1234',
			'display_powered_by' => 0,
			'debug' => 0,
		);
		$actual_output = $instance->validate( $creds );
		$this->assertEquals( $expected_output, $actual_output );
		$this->assertTrue( get_option( 'gem-valid-creds' ) );
	}

	/**
	 * Test validate no API changes.
	 *
	 * @see GEM_Settings::validate()
	 */
	public function test_validate_non_api_change() {
		$instance = new GEM_Settings();
		$instance->action_admin_menu();
		$this->set_data( $instance->slug );
		$expected_output = array(
			'username' => 'user_name',
			'api-key' => '1234',
			'display_powered_by' => 1,
			'debug' => 0,
		);
		$this->assertEquals( $expected_output, $instance->validate( $expected_output ) );
		$this->assertTrue( get_transient( 'gem-settings-updated' ) );

		$this->delete_data( $instance->slug );
	}

	/**
	 * Test getting various request urls.
	 *
	 * @see GEM_Settings::generate_help_tab_content()
	 */
	public function test_generate_help_tab_content() {

		global $locale;

		$domains = array(
			''    => 'www',
			'uk'  => 'ua',
			'el'  => 'gr',
		);

		foreach ( $domains as $lang => $domain ) {

			$locale   = $lang;
			$instance = new GEM_Settings();

			ob_start();

			$instance->generate_help_tab_content();

			$contents = ob_get_clean();

			$this->assertContains( "<iframe src=\"https://{$domain}.godaddy.com/help/godaddy-email-marketing-1000013\" frameborder=\"0\" scrolling=\"no\"></iframe>", $contents );

		}

		unset( $locale );

	}
}
