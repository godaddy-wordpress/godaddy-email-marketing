<?php
namespace GEM;

require( 'testcase.php' );

class Test_GEM_Official extends WP_GEMTestCase {

	/**
	 * PHP unit setup function
	 *
	 * @return void
	 */
	function setUp() {
		parent::setUp();
	}

	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Official', false ) );
	}
}
