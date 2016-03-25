<?php
namespace GEM;

require_once( 'testcase.php' );

class Test_GEM_Settings extends WP_GEMTestCase {

	/**
	 * PHP unit setup function
	 *
	 * @return void
	 */
	function setUp() {
		parent::setUp();
	}

	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Settings', false ) );
	}

	public function test_construct() {
		$instance = new \GEM_Settings();
		$this->assertIsDefinedAction( 'admin_menu', array( $instance, 'action_admin_menu' ) );
		$this->assertIsDefinedAction( 'admin_init', array( $instance, 'register_settings' ) );
	}

	public function test_action_admin_menu() {
		$instance = new \GEM_Settings();
		$instance->action_admin_menu();

		$this->assertIsDefinedAction( 'load-', array( $instance, 'page_load' ) );
		$this->assertEquals( 'gem-settings', $instance->slug );
	}

	public function test_page_load() {
		$instance = new \GEM_Settings();
		$instance->page_load();

		$this->assertIsDefinedAction( 'in_admin_header', array( $instance, 'setup_help_tabs' ) );
		$this->assertArrayHasKey( 'gem-admin', wp_styles()->registered );

		$_GET['action'] = 'debug-reset';
		$instance = new \GEM_Settings();
		$instance->page_load();
	}

	private function assertIsDefinedAction( $action_name, $callback, $priority = 10 ) {
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
