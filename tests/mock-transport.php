<?php

class WP_Http_Mock_Transport {

	public static $response = array();
	public static $test_class;
	public static $expected_url;

	public static function test() {
		return true;
	}

	public function request( $url, $args ) {
		if ( null !== self::$test_class && ! empty( self::$expected_url ) ) {
			self::$test_class->assertEquals( self::$expected_url, $url );
		}

		return self::$response;
	}
}
