<?php
/**
 * Test Settings Controls.
 *
 * @group controls
 */
class Test_GEM_Settings_Controls extends WP_UnitTestCase {

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Test that GEM_Settings_Controls exists.
	 */
	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Settings_Controls', false ) );
	}

	/**
	 * Test description markup.
	 *
	 * @see GEM_Settings_Controls::description()
	 */
	public function test_description() {
		ob_start();
		GEM_Settings_Controls::description();
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<p>For this plugin to work, it needs to access your GoDaddy Email Marketing account. <a target="_blank" href="https://sso.godaddy.com/?realm=idp&app=gem&path=/wordpress_plugin">Sign in here</a> to get your username and API key. Copy and paste them below; then click &quot;Save Settings.&quot; If you don&#039;t have a GoDaddy Email Marketing account, <a target="_blank" href="https://sso.godaddy.com/account/create?path=/wordpress_plugin&app=gem&realm=idp&ssoreturnpath=/%3Fpath%3D%2Fwordpress_plugin%26app%3Dgem%26realm%3Didp">sign up here</a>.</p>', $actual_output );
	}

	/**
	 * Test select markup.
	 *
	 * @see GEM_Settings_Controls::select()
	 */
	public function test_select() {
		ob_start();
		GEM_Settings_Controls::select( array(
			'options' => array( 'key' => 'the_value' ),
			'id' => 'the_id',
			'page' => 'the_page',
		) );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<select id="the_id" name="the_page[the_id]">', $actual_output );
		$this->assertContains( '<option value="key" >', $actual_output );
		$this->assertContains( 'the_value', $actual_output );
	}

	/**
	 * Test select markup.
	 *
	 * @see GEM_Settings_Controls::select()
	 */
	public function test_select_is_empty() {
		ob_start();
		GEM_Settings_Controls::select( array(
			'id' => null,
		) );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertEmpty( $actual_output );
	}

	/**
	 * Test text markup.
	 *
	 * @see GEM_Settings_Controls::text()
	 */
	public function test_text() {
		ob_start();
		GEM_Settings_Controls::text(array(
			'id' => 'the_id',
			'page' => 'the_page',
		) );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<input type="text" name="the_page[the_id]"', $actual_output );
		$this->assertContains( 'id="the_page-the_id"', $actual_output );
		$this->assertContains( 'value="" class="widefat code" />', $actual_output );
	}

	/**
	 * Test text markup.
	 *
	 * @see GEM_Settings_Controls::text()
	 */
	public function test_text_is_empty() {
		ob_start();
		GEM_Settings_Controls::text( array(
			'id' => null,
		) );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertEmpty( $actual_output );
	}

	/**
	 * Test checkbox markup.
	 *
	 * @see GEM_Settings_Controls::checkbox()
	 */
	public function test_checkbox() {
		ob_start();
		GEM_Settings_Controls::checkbox( array(
			'id' => 'the_id',
			'page' => 'the_page',
		) );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<label for="the_page[the_id]">', $actual_output );
		$this->assertContains( '<input type="checkbox" name="the_page[the_id]" id="the_page[the_id]" value="1"  />', $actual_output );
		$this->assertContains( '</label>', $actual_output );
	}

	/**
	 * Test checkbox markup.
	 *
	 * @see GEM_Settings_Controls::checkbox()
	 */
	public function test_checkbox_is_empty() {
		ob_start();
		GEM_Settings_Controls::checkbox( array(
			'id' => null,
		) );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertEmpty( $actual_output );
	}

	/**
	 * Test button markup.
	 *
	 * @see GEM_Settings_Controls::button()
	 */
	public function test_button() {
		ob_start();
		GEM_Settings_Controls::button( array(
			'url' => 'http://sample.org',
			'label' => 'Cool Button',
		) );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<p>', $actual_output );
		$this->assertContains( '<a href="http://sample.org" class="button-secondary">Cool Button</a>', $actual_output );
		$this->assertContains( '</p>', $actual_output );
	}

	/**
	 * Test button markup.
	 *
	 * @see GEM_Settings_Controls::button()
	 */
	public function test_button_is_empty() {
		ob_start();
		GEM_Settings_Controls::button( array() );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertEmpty( $actual_output );
	}

	/**
	 * Test show description markup.
	 *
	 * @see GEM_Settings_Controls::show_description()
	 */
	public function test_show_description() {
		ob_start();
		GEM_Settings_Controls::show_description( array(
			'description' => 'the_description',
		) );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<p class="description">the_description</p>', $actual_output );
	}

	/**
	 * Test option value.
	 *
	 * @see GEM_Settings_Controls::get_option()
	 */
	public function test_get_option() {
		update_option( GEM_Settings::SLUG, array( 'username' => 'user_name', 'api-key' => '1234' ) );
		$this->assertFalse( GEM_Settings_Controls::get_option( 'error' ) );
		$this->assertEquals( 'user_name', GEM_Settings_Controls::get_option( 'username' ) );
		$this->assertEquals( '1234', GEM_Settings_Controls::get_option( 'api-key' ) );
		delete_option( GEM_Settings::SLUG );
	}

	/**
	 * Test delete option value.
	 *
	 * @see GEM_Settings_Controls::delete_option()
	 */
	public function test_delete_option() {
		update_option( GEM_Settings::SLUG, array( 'username' => 'user_name', 'api-key' => '1234' ) );
		$this->assertEquals( '1234', GEM_Settings_Controls::get_option( 'api-key' ) );
		$this->assertFalse( GEM_Settings_Controls::delete_option( 'fake-key' ) );
		$this->assertTrue( GEM_Settings_Controls::delete_option( 'api-key' ) );
		$this->assertFalse( GEM_Settings_Controls::get_option( 'api-key' ) );
		delete_option( GEM_Settings::SLUG );
	}

	/**
	 * Test update option value.
	 *
	 * @see GEM_Settings_Controls::update_option()
	 */
	public function test_update_option() {
		update_option( GEM_Settings::SLUG, array( 'username' => 'user_name', 'api-key' => '1234' ) );
		$this->assertEquals( '1234', GEM_Settings_Controls::get_option( 'api-key' ) );
		$this->assertFalse( GEM_Settings_Controls::update_option( 'api-key', '1234' ) );
		$this->assertTrue( GEM_Settings_Controls::update_option( 'api-key', '4321' ) );
		$this->assertEquals( '4321', GEM_Settings_Controls::get_option( 'api-key' ) );
		delete_option( GEM_Settings::SLUG );
	}
}
