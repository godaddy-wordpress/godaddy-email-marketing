<?php

class WP_GEMTestCase extends WP_UnitTestCase {
	/**
	 * Holds the plugin base class
	 *
	 * @var Plugin
	 */
	protected $plugin_file_path;

	/**
	 * PHP unit setup function
	 *
	 * @return void
	 */
	function setUp() {
		parent::setUp();
		$this->plugin_file_path = $GLOBALS['_plugin_file'];
	}

	public function test_plugin_initialized() {
		$this->assertFalse( null == $this->plugin_file_path );
	}

	protected function assertIsDefinedAction( $action_name, $callback, $priority = 10 ) {
		global $wp_filter;

		$this->assertArrayHasKey( $action_name, $wp_filter );
		$this->assertArrayHasKey( $priority, $wp_filter[ $action_name ] );
		$actions = $wp_filter[ $action_name ][ $priority ];

		foreach ( $actions as $action ) {
			if ( array_key_exists( 'function', $action ) ) {
				if ( $action['function'] == $callback ) {
					return;
				}
			}
		}

		$this->fail( $action_name . ' action is not registered. ' );
	}
}
