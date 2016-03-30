<?php

namespace GEM;

require_once( 'testcase.php' );

class Test_GEM_Shortcode extends WP_GEMTestCase {

	/**
	 * @var \GEM_Shortcode
	 */
	private $instance;

	/**
	 * PHP unit setup function
	 *
	 * @return void
	 */
	function setUp() {
		parent::setUp();
		$this->instance = new \GEM_Shortcode();
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
		$this->assertTrue( class_exists( 'GEM_Shortcode', false ) );
	}

	public function test_render() {
		$this->assertNull( $this->instance->render( array( 'id' => null ) ) );
	}

	public function test_gem_form() {
		$this->assertTrue( function_exists( 'gem_form' ) );
	}

	public function test_gem_form_function() {
		$sample_data = array(
			'fields' => array(
				'field_a' => array(
					'type' => 'string',
					'field_type' => 'string',
					'name' => 'the_name_a',
					'required' => false,
					'display' => 'text_a',
				),
				'field_b' => array(
					'type' => 'checkbox',
					'field_type' => 'checkbox',
					'required' => true,
					'name' => 'the_name_b',
					'value' => 'the_value',
					'display' => 'text_b',
				),
			),
			'submit' => 'the_url',
			'id' => 'the_id',
			'button_text' => 'button_text',
		);
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 200,
			),
			'body' => json_encode( $sample_data ),
		);

		ob_start();
		gem_form( 123 );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertNotEmpty( $actual_output );
	}
}
