<?php
namespace GEM;

class WP_GEMTestCase extends \WP_UnitTestCase {
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
}
