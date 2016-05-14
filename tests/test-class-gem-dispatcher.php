<?php
/**
 * Test Dispatcher.
 *
 * @group dispatcher
 */
class Test_GEM_Dispatcher extends WP_UnitTestCase {

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

		Mock_Http_Response::$test_class = $this;
		add_filter( 'http_response', array( 'Mock_Http_Response', 'http_response' ), 10, 3 );
	}

	/**
	 * Teardown.
	 *
	 * @inheritdoc
	 */
	public function tearDown() {
		parent::tearDown();

		remove_filter( 'http_response', array( 'Mock_Http_Response', 'http_response' ), 10, 3 );
		Mock_Http_Response::$test_class = null;
	}

	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Dispatcher', false ) );
	}

	public function test_fetch_forms() {
		$test = $this;
		$user_name = 'the_user';
		$api_key = 'the_key';
		$sample_response = '{ "test": "OK" }';

		Mock_Http_Response::$expected_args = array(
			'timeout' => 10,
		);
		Mock_Http_Response::$expected_url = 'https://gem.godaddy.com/signups.json';
		Mock_Http_Response::$data = array(
			'response' => array(
				'code' => 401,
			),
			'body' => $sample_response,
		);
		$this->assertFalse( GEM_Dispatcher::fetch_forms( $user_name ) );

		Mock_Http_Response::$expected_url = "https://gem.godaddy.com/signups.json?username=$user_name&api_key=the_key";
		Mock_Http_Response::$data = array(
			'response' => array(
				'code' => 401,
			),
			'body' => $sample_response,
		);
		$this->assertFalse( GEM_Dispatcher::fetch_forms( $user_name, $api_key ) );
	}

	public function test_fetch_forms_is_set() {
		$test = $this;
		$user_name = 'the_user';
		$api_key = 'the_key';
		$sample_response = '{ "test": "OK" }';

		Mock_Http_Response::$data = array(
			'response' => array(
				'code' => 200,
			),
			'body' => $sample_response,
		);
		$this->assertEquals( json_decode( $sample_response ), GEM_Dispatcher::fetch_forms( $user_name, $api_key ) );
		$this->assertEquals( json_decode( $sample_response ), get_transient( 'gem-' . $user_name . '-lists' ) );
	}

	public function test_add_default_form() {
		$user_name = 'the_user';
		$api_key = 'the_key';
		$sample_data = 'the_sample';
		$sample_response = '{ "test": "OK" }';

		update_option( 'gem-settings', false );
		$this->assertFalse( GEM_Dispatcher::add_default_form() );

		Mock_Http_Response::$expected_args = array(
			'method' => 'POST',
			'timeout' => 10,
			'body' => array(
				'username' => $user_name,
				'api_key' => $api_key,
				'name' => 'Signup Form',
				'integration' => 'WordPress',
				'hidden' => false,
				'subscriberListName' => 'WordPress',
			),
		);
		Mock_Http_Response::$expected_url = 'https://gem.godaddy.com/api/v3/signupForms';
		Mock_Http_Response::$data = array(
			'response' => array(
				'code' => 401,
			),
			'body' => $sample_response,
		);
		update_option( 'gem-settings', array( 'api-key' => $api_key, 'username' => $user_name ) );
		$this->assertFalse( GEM_Dispatcher::add_default_form() );

		Mock_Http_Response::$expected_url = 'https://gem.godaddy.com/api/v3/signupForms';
		Mock_Http_Response::$data = array(
			'response' => array(
				'code' => 200,
			),
			'body' => $sample_response,
		);
		update_option( 'gem-settings', array( 'api-key' => $api_key, 'username' => $user_name ) );
		$this->assertTrue( GEM_Dispatcher::add_default_form() );
	}

	public function test_get_forms() {
		$user_name = 'the_user';
		$api_key = 'the_key';
		$sample_data = 'the_sample';

		update_option( 'gem-settings', false );
		$this->assertFalse( GEM_Dispatcher::get_forms( $user_name ) );

		update_option( 'gem-settings', array( 'api-key' => $api_key ) );
		set_transient( 'gem-' . $user_name . '-lists', $sample_data );
		$this->assertEquals( $sample_data, GEM_Dispatcher::get_forms( $user_name ) );

		delete_transient( 'gem-' . $user_name . '-lists' );
	}

	public function test_get_fields() {
		$test = $this;
		$form_id = 'the_id';
		$sample_data = 'the_sample';
		$sample_response = '{ "test": "OK" }';

		set_transient( 'gem-form-' . $form_id, $sample_data );
		$this->assertEquals( $sample_data, GEM_Dispatcher::get_fields( $form_id ) );
		delete_transient( 'gem-form-' . $form_id );

		Mock_Http_Response::$expected_url = "https://gem.godaddy.com/signups/$form_id.json";
		Mock_Http_Response::$data = array(
			'response' => array(
				'code' => 401,
			),
			'body' => $sample_response,
		);
		$this->assertFalse( GEM_Dispatcher::get_fields( $form_id ) );

		Mock_Http_Response::$expected_url = "https://gem.godaddy.com/signups/$form_id.json";
		Mock_Http_Response::$data = array(
			'response' => array(
				'code' => 200,
			),
			'body' => $sample_response,
		);
		$this->assertEquals( json_decode( $sample_response ), GEM_Dispatcher::get_fields( $form_id ) );
		$this->assertEquals( json_decode( $sample_response ), get_transient( 'gem-form-' . $form_id ) );
		delete_transient( 'gem-form-' . $form_id );
	}

	public function test_get_user_level() {
		$sample_data = 'the_sample';
		$user_name = 'the_user';
		$sample_response = '{ "result": "OK" }';

		update_option( 'gem-settings', false );
		$this->assertFalse( GEM_Dispatcher::get_user_level() );

		update_option( 'gem-settings', array( 'username' => $user_name ) );
		set_transient( 'gem-' . $user_name . '-account', $sample_data );
		$this->assertEquals( $sample_data, GEM_Dispatcher::get_user_level() );
		delete_transient( 'gem-' . $user_name . '-account' );

		Mock_Http_Response::$expected_url = "https://gem.godaddy.com/user/account_status?username=$user_name";
		Mock_Http_Response::$data = array(
			'response' => array(
				'code' => 401,
			),
		);
		$this->assertFalse( GEM_Dispatcher::get_user_level() );

		Mock_Http_Response::$expected_url = "https://gem.godaddy.com/user/account_status?username=$user_name";
		Mock_Http_Response::$data = array(
			'response' => array(
				'code' => 200,
			),
			'body' => $sample_response,
		);
		$this->assertEquals( 'OK', GEM_Dispatcher::get_user_level() );
		$this->assertEquals( 'OK', get_transient( 'gem-' . $user_name . '-account' ) );
		delete_transient( 'gem-' . $user_name . '-account' );
	}

	public function test_get_method_url() {
		$auth = array(
			'username' => 'the_user',
			'api_key' => 'the_key',
		);
		$params = array(
			'id' => 'the_id',
			'token' => 'the_token',
		);
		$this->assertEquals( 'https://gem.godaddy.com/signups.json?username=the_user&api_key=the_key', GEM_Dispatcher::get_method_url( 'forms', false,  $auth ) );
		$this->assertEquals( 'https://gem.godaddy.com/signups/the_id.json?username=the_user&api_key=the_key', GEM_Dispatcher::get_method_url( 'fields', $params,  $auth ) );
		$this->assertEquals( 'https://gem.godaddy.com/user/account_status?username=the_user&api_key=the_key', GEM_Dispatcher::get_method_url( 'account', false,  $auth ) );

		$auth = array(
			'username' => 'the_user',
			'api-key' => 'the_key',
		);
		update_option( 'gem-settings', $auth );
		$this->assertEquals( 'https://gem.godaddy.com/signups.json?username=the_user&api_key=the_key', GEM_Dispatcher::get_method_url( 'forms', false,  false ) );
		$this->assertEquals( 'https://gem.godaddy.com/signups/the_id.json?username=the_user&api_key=the_key', GEM_Dispatcher::get_method_url( 'fields', $params,  false ) );
		$this->assertEquals( 'https://gem.godaddy.com/user/account_status?username=the_user&api_key=the_key', GEM_Dispatcher::get_method_url( 'account', false,  false ) );
	}

	public function test_is_response_ok() {
		$request = array(
			'response' => array(
				'code' => 200,
			),
		);
		$this->assertTrue( GEM_Dispatcher::is_response_ok( $request ) );

		$request = array(
			'response' => array(
				'code' => 304,
			),
		);
		$this->assertTrue( GEM_Dispatcher::is_response_ok( $request ) );

		$request = array(
			'response' => array(
				'code' => 404,
			),
		);
		$this->assertFalse( GEM_Dispatcher::is_response_ok( $request ) );

		$request = new WP_Error();
		$this->assertFalse( GEM_Dispatcher::is_response_ok( $request ) );
	}
}
