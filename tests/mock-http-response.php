<?php
/**
 * Mock Http Response class.
 */
class Mock_Http_Response {

	/**
	 * Response data.
	 *
	 * @var array
	 */
	public static $data = array();

	/**
	 * Unit Test instance.
	 *
	 * @var WP_UnitTestCase
	 */
	public static $test_class;

	/**
	 * Expected HTTP request arguments.
	 *
	 * @var array
	 */
	public static $expected_args;

	/**
	 * Expected request URL.
	 *
	 * @var string
	 */
	public static $expected_url;

	/**
	 * Filters the HTTP API response.
	 *
	 * @param array  $data HTTP response.
	 * @param array  $args HTTP request arguments.
	 * @param string $url  The request URL.
	 * @return array
	 */
	public function filter_response( $data, $args, $url ) {
		if ( null !== self::$test_class ) {
			if ( ! empty( self::$expected_args ) ) {
				self::$test_class->assertContains( self::$expected_args, $args );
			}
			if ( ! empty( self::$expected_url ) ) {
				self::$test_class->assertEquals( self::$expected_url, $url );
			}
		}

		if ( ! empty( self::$data ) ) {
			return self::$data;
		}

		return $data;
	}
}
