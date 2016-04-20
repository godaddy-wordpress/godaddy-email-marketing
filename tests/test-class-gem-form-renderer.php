<?php
class Test_GEM_Form_Renderer extends WP_UnitTestCase {

	/**
	 * @var GEM_Form_Renderer
	 */
	private $instance;

	/**
	 * Load WP_Http_Mock_Transport
	 */
	public static function setUpBeforeClass() {
		require_once( 'mock-transport.php' );
	}

	/**
	 * PHP unit setup function
	 *
	 * @return void
	 */
	function setUp() {
		parent::setUp();
		$this->instance = new GEM_Form_Renderer();
		add_action( 'http_api_transports', array( $this, 'get_transports' ) );
	}

	function tearDown() {
		parent::tearDown();

		remove_action( 'http_api_transports', array( $this, 'get_transports' ) );
	}

	public function get_transports() {
		return array( 'Mock_Transport' );
	}

	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Form_Renderer', false ) );
	}

	public function test_process() {
		update_option( 'gem-settings', array( 'username' => 'user_name', 'api-key' => '1234' ) );
		set_transient( 'gem-form-123', json_decode( '{"id":123,"name":"Signup Form","fields":{"field_a":{"type":"string","field_type":"string","name":"the_name_a","required":false,"display":"text_a"},"field_b":{"type":"checkbox","field_type":"checkbox","required":true,"name":"the_name_b","value":"the_value","display":"text_b"}},"submit":"the_url","button_text":"button_text"}' ), 60 );
		set_transient( 'gem-user_name-lists', json_decode( '{"total":1,"signups":[{"id":123,"name":"Signup Form","thumbnail":"the_url","url":"the_url"}]}' ), 60 );
		$actual_result = $this->instance->process( 123 );

		$this->assertContains( '<div class="gem-form-wrapper" id="form-123">', $actual_result );
		$this->assertContains( '<form action="http://the_url" method="post" class="gem-form">', $actual_result );
		$this->assertContains( '<input type="hidden" name="form_id" value="123" />', $actual_result );
		$this->assertContains( '<input type="submit" value="button_text" class="button gem-submit" />', $actual_result );
		$this->assertContains( 'text_a', $actual_result );
		$this->assertContains( 'text_b', $actual_result );
		$this->assertContains( '<label for="form_1_the_name_a">', $actual_result );
		$this->assertContains( '<input type="text" name="the_name_a" id="form_1_the_name_a" class="gem-field" />', $actual_result );
		$this->assertContains( '<label for="form_1_the_name_bthe_value">', $actual_result );
		$this->assertContains( '<input type="checkbox" value="the_value" name="the_name_b" id="form_1_the_name_bthe_value" class="gem-checkbox gem-required" />', $actual_result );

		ob_start();
		$this->instance->process( 123, true );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertNotEmpty( $actual_output );

		delete_option( 'gem-settings' );
		delete_transient( 'gem-form-123' );
		delete_transient( 'gem-user_name-lists' );
	}
}
