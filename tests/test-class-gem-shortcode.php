<?php
/**
 * Test Shortcode.
 *
 * @group shortcode
 */
class Test_GEM_Shortcode extends WP_UnitTestCase {

	/**
	 * @var GEM_Shortcode
	 */
	private $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->instance = new GEM_Shortcode();
	}

	/**
	 * Test that GEM_Shortcode exists.
	 */
	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Shortcode', false ) );
	}

	/**
	 * Test render.
	 *
	 * @see GEM_Shortcode::render()
	 */
	public function test_render() {
		$this->assertNull( $this->instance->render( array( 'id' => null ) ) );
		$this->assertNull( $this->instance->render( array( 'id' => 123 ) ) );
	}

	/**
	 * Test form.
	 *
	 * @see GEM_Shortcode::gem_form()
	 */
	public function test_gem_form() {
		$this->assertTrue( function_exists( 'gem_form' ) );
	}

	/**
	 * Test markup echo.
	 *
	 * @see GEM_Shortcode::gem_form()
	 */
	public function test_gem_form_markup() {
		GEM_Settings_Controls::update_option( 'username', 'user_name' );
		GEM_Settings_Controls::update_option( 'api-key', '1234' );
		set_transient( 'gem-form-123', json_decode( '{"id":123,"name":"Signup Form","fields":{"field_a":{"type":"string","field_type":"string","name":"the_name_a","required":false,"display":"text_a"},"field_b":{"type":"checkbox","field_type":"checkbox","required":true,"name":"the_name_b","value":"the_value","display":"text_b"}},"submit":"the_url","button_text":"button_text"}' ), 60 );
		set_transient( 'gem-user_name-lists', json_decode( '{"total":1,"signups":[{"id":123,"name":"Signup Form","thumbnail":"the_url","url":"the_url"}]}' ), 60 );

		ob_start();
		gem_form( 123 );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertNotEmpty( $actual_output );

		delete_option( GEM_Settings::SLUG );
		delete_transient( 'gem-form-123' );
		delete_transient( 'gem-user_name-lists' );
	}

	/**
	 * Test markup return.
	 *
	 * @see GEM_Shortcode::gem_form()
	 */
	public function test_gem_form_function_when_echo_is_false() {
		GEM_Settings_Controls::update_option( 'username', 'user_name' );
		GEM_Settings_Controls::update_option( 'api-key', '1234' );
		set_transient( 'gem-form-123', json_decode( '{"id":123,"name":"Signup Form","fields":{"field_a":{"type":"string","field_type":"string","name":"the_name_a","required":false,"display":"text_a"},"field_b":{"type":"checkbox","field_type":"checkbox","required":true,"name":"the_name_b","value":"the_value","display":"text_b"}},"submit":"the_url","button_text":"button_text"}' ), 60 );
		set_transient( 'gem-user_name-lists', json_decode( '{"total":1,"signups":[{"id":123,"name":"Signup Form","thumbnail":"the_url","url":"the_url"}]}' ), 60 );

		$form = gem_form( 123, false );
		$this->assertNotEmpty( $form );

		delete_option( GEM_Settings::SLUG );
		delete_transient( 'gem-form-123' );
		delete_transient( 'gem-user_name-lists' );
	}
}
