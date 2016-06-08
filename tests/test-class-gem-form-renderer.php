<?php
/**
 * Test Renderer.
 *
 * @group renderer
 */
class Test_GEM_Form_Renderer extends WP_UnitTestCase {

	/**
	 * Test that GEM_Form_Renderer exists.
	 */
	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Form_Renderer', false ) );
	}

	/**
	 * Test process output.
	 *
	 * @see GEM_Form_Renderer::process()
	 */
	public function test_process() {
		GEM_Settings_Controls::update_option( 'username', 'user_name' );
		GEM_Settings_Controls::update_option( 'api-key', '1234' );
		set_transient( 'gem-form-123', json_decode( '{"id":123,"name":"Signup Form","fields":{"field_a":{"type":"string","field_type":"string","name":"the_name_a","required":false,"display":"text_a"},"field_b":{"type":"checkbox","field_type":"checkbox","required":true,"name":"the_name_b","value":"the_value","display":"text_b"}},"submit":"the_url","button_text":"button_text"}' ), 60 );
		set_transient( 'gem-user_name-lists', json_decode( '{"total":1,"signups":[{"id":123,"name":"Signup Form","thumbnail":"the_url","url":"the_url"}]}' ), 60 );

		$instance = new GEM_Form_Renderer();
		$actual_result = $instance->process( 123 );

		$this->assertContains( '<div class="gem-form-wrapper" id="form-123">', $actual_result );
		$this->assertContains( '<form action="http://the_url" method="post" class="gem-form">', $actual_result );
		$this->assertContains( '<input type="hidden" name="form_id" value="123" />', $actual_result );
		$this->assertContains( '<input type="submit" value="button_text" class="button gem-submit" />', $actual_result );
		$this->assertContains( 'text_a', $actual_result );
		$this->assertContains( 'text_b', $actual_result );
		$this->assertContains( '<label for="form_1_the_name_a">', $actual_result );
		$this->assertContains( '<input type="text" name="the_name_a" id="form_1_the_name_a" class="gem-field" data-label="text_a" />', $actual_result );
		$this->assertContains( '<label for="form_1_the_name_bthe_value">', $actual_result );
		$this->assertContains( '<input type="checkbox" value="the_value" name="the_name_b" id="form_1_the_name_bthe_value" class="gem-checkbox gem-required" />', $actual_result );

		ob_start();
		$instance->process( 123, true );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertNotEmpty( $actual_output );

		delete_option( GEM_Settings::SLUG );
		delete_transient( 'gem-form-123' );
		delete_transient( 'gem-user_name-lists' );
	}
}
