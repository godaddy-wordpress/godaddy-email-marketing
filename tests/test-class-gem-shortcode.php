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
}
