<?php

namespace GEM;

require_once( 'mock-transport.php' );
require_once( 'testcase.php' );

class Test_GEM_Form_Renderer extends WP_GEMTestCase {

	/**
	 * @var GEM_Form_Renderer
	 */
	private $instance;

	/**
	 * PHP unit setup function
	 *
	 * @return void
	 */
	function setUp() {
		parent::setUp();
		$this->instance = new \GEM_Form_Renderer();
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
		\WP_Http_Mock_Transport::$response = array(
			'response' => array(
				'code' => 401,
			),
			'body' => '',
		);
		$this->assertNull( $this->instance->process( 0 ) );

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
		$actual_result = $this->instance->process( 123 );

		$this->assertContains( '<div class="gem-form-wrapper" id="form-123">', $actual_result );
		$this->assertContains( '<form action="http://the_url" method="post" class="gem-form">', $actual_result );
		$this->assertContains( '<input type="hidden" name="form_id" value="0" />', $actual_result );
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
	}
}
