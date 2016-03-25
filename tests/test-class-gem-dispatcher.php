<?php
namespace GEM;

require_once( 'mock-transport.php' );
require_once( 'testcase.php' );

class Test_GEM_Dispatcher extends WP_GEMTestCase {

	/**
	 * PHP unit setup function
	 *
	 * @return void
	 */
	function setUp() {
		parent::setUp();

		\WP_Http_Mock_Transport::$test_class = $this;
		add_action( 'http_api_transports', array( $this, 'get_transports' ) );
	}

	function tearDown() {
		parent::tearDown();

		remove_action( 'http_api_transports', array( $this, 'get_transports' ) );
		\WP_Http_Mock_Transport::$test_class = null;
	}

	public function get_transports() {
		return array( 'Mock_Transport' );
	}

	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Dispatcher', false ) );
	}

	public function test_fetch_forms() {
		$test = $this;
		$user_name = 'the_user';
		$api_key = 'the_key';
		$sample_response = '{ "test": "OK" }';

		\WP_Http_Mock_Transport::$expected_url = 'http://api.madmimi.com/signups.json';
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 401,
			),
			'body' => $sample_response,
		);
		$this->assertFalse( \GEM_Dispatcher::fetch_forms( $user_name ) );

		\WP_Http_Mock_Transport::$expected_url = "http://api.madmimi.com/signups.json?username=$user_name&api_key=the_key";
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 401,
			),
			'body' => $sample_response,
		);
		$this->assertFalse( \GEM_Dispatcher::fetch_forms( $user_name, $api_key ) );

		\WP_Http_Mock_Transport::$expected_url = "http://api.madmimi.com/signups.json?username=$user_name&api_key=$api_key";
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 200,
			),
			'body' => $sample_response,
		);
		$this->assertEquals( json_decode( $sample_response ), \GEM_Dispatcher::fetch_forms( $user_name, $api_key ) );
		$this->assertEquals( json_decode( $sample_response ), get_transient( 'gem-' . $user_name . '-lists' ) );
	}

	public function test_get_forms() {
		$user_name = 'the_user';
		$api_key = 'the_key';
		$sample_data = 'the_sample';

		update_option( 'gem-settings', false );
		$this->assertFalse( \GEM_Dispatcher::get_forms( $user_name ) );

		update_option( 'gem-settings', array( 'api-key' => $api_key ) );
		set_transient( 'gem-' . $user_name . '-lists', $sample_data );
		$this->assertEquals( $sample_data, \GEM_Dispatcher::get_forms( $user_name ) );

		delete_transient( 'gem-' . $user_name . '-lists' );
	}

	public function test_get_fields() {
		$test = $this;
		$form_id = 'the_id';
		$sample_data = 'the_sample';
		$sample_response = '{ "test": "OK" }';

		set_transient( 'gem-form-' . $form_id, $sample_data );
		$this->assertEquals( $sample_data, \GEM_Dispatcher::get_fields( $form_id ) );
		delete_transient( 'gem-form-' . $form_id );

		\WP_Http_Mock_Transport::$expected_url = "http://api.madmimi.com/signups/$form_id.json";
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 401,
			),
			'body' => $sample_response,
		);
		$this->assertFalse( \GEM_Dispatcher::get_fields( $form_id ) );

		\WP_Http_Mock_Transport::$expected_url = "http://api.madmimi.com/signups/$form_id.json";
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 200,
			),
			'body' => $sample_response,
		);
		$this->assertEquals( json_decode( $sample_response ), \GEM_Dispatcher::get_fields( $form_id ) );
		$this->assertEquals( json_decode( $sample_response ), get_transient( 'gem-form-' . $form_id ) );
		delete_transient( 'gem-form-' . $form_id );
	}

	public function test_get_user_level() {
		$sample_data = 'the_sample';
		$user_name = 'the_user';
		$sample_response = '{ "result": "OK" }';

		update_option( 'gem-settings', false );
		$this->assertFalse( \GEM_Dispatcher::get_user_level() );

		update_option( 'gem-settings', array( 'username' => $user_name ) );
		set_transient( 'gem-' . $user_name . '-account', $sample_data );
		$this->assertEquals( $sample_data, \GEM_Dispatcher::get_user_level( ) );
		delete_transient( 'gem-' . $user_name . '-account' );

		\WP_Http_Mock_Transport::$expected_url = "http://api.madmimi.com/user/account_status?username=$user_name";
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 401,
			),
		);
		$this->assertFalse( \GEM_Dispatcher::get_user_level( ) );

		\WP_Http_Mock_Transport::$expected_url = "http://api.madmimi.com/user/account_status?username=$user_name";
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 200,
			),
			'body' => $sample_response,
		);
		$this->assertEquals( 'OK', \GEM_Dispatcher::get_user_level( ) );
		$this->assertEquals( 'OK', get_transient( 'gem-' . $user_name . '-account' ) );
		delete_transient( 'gem-' . $user_name . '-account' );
	}

	public function test_user_sign_in() {
		$sample_response = 'the_response';

		\WP_Http_Mock_Transport::$expected_url = 'http://api.madmimi.com/sessions/single_signon_token';
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 401,
			),
		);
		$this->assertFalse( \GEM_Dispatcher::user_sign_in( ) );

		\WP_Http_Mock_Transport::$expected_url = 'http://api.madmimi.com/sessions/single_signon_token';
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 200,
			),
			'body' => $sample_response,
		);
		$this->assertEquals( 'http://api.madmimi.com/sessions/single_signon?token=' . $sample_response, \GEM_Dispatcher::user_sign_in( ) );
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
		$this->assertEquals( 'http://api.madmimi.com/signups.json?username=the_user&api_key=the_key', \GEM_Dispatcher::get_method_url( 'forms', false,  $auth ) );
		$this->assertEquals( 'http://api.madmimi.com/signups/the_id.json?username=the_user&api_key=the_key', \GEM_Dispatcher::get_method_url( 'fields', $params,  $auth ) );
		$this->assertEquals( 'http://api.madmimi.com/user/account_status?username=the_user&api_key=the_key', \GEM_Dispatcher::get_method_url( 'account', false,  $auth ) );
		$this->assertEquals( 'http://api.madmimi.com/sessions/single_signon_token?username=the_user&api_key=the_key', \GEM_Dispatcher::get_method_url( 'signin', false,  $auth ) );
		$this->assertEquals( 'http://api.madmimi.com/sessions/single_signon?token=the_token&username=the_user', \GEM_Dispatcher::get_method_url( 'signin_redirect', $params,  $auth ) );

		$auth = array(
			'username' => 'the_user',
			'api-key' => 'the_key',
		);
		update_option( 'gem-settings', $auth );
		$this->assertEquals( 'http://api.madmimi.com/signups.json?username=the_user&api_key=the_key', \GEM_Dispatcher::get_method_url( 'forms', false,  false ) );
		$this->assertEquals( 'http://api.madmimi.com/signups/the_id.json?username=the_user&api_key=the_key', \GEM_Dispatcher::get_method_url( 'fields', $params,  false ) );
		$this->assertEquals( 'http://api.madmimi.com/user/account_status?username=the_user&api_key=the_key', \GEM_Dispatcher::get_method_url( 'account', false,  false ) );
		$this->assertEquals( 'http://api.madmimi.com/sessions/single_signon_token?username=the_user&api_key=the_key', \GEM_Dispatcher::get_method_url( 'signin', false,  false ) );
		$this->assertEquals( 'http://api.madmimi.com/sessions/single_signon?token=the_token&username=the_user', \GEM_Dispatcher::get_method_url( 'signin_redirect', $params,  false ) );
	}

	public function test_is_response_ok() {
		//$request = new \WP_Error();
		$request = array(
			'response' => array(
				'code' => 200,
			),
		);
		$this->assertTrue( \GEM_Dispatcher::is_response_ok( $request ) );

		$request = array(
			'response' => array(
				'code' => 304,
			),
		);
		$this->assertTrue( \GEM_Dispatcher::is_response_ok( $request ) );

		$request = array(
			'response' => array(
				'code' => 404,
			),
		);
		$this->assertFalse( \GEM_Dispatcher::is_response_ok( $request ) );

		$request = new \WP_Error();
		$this->assertFalse( \GEM_Dispatcher::is_response_ok( $request ) );
	}
}
