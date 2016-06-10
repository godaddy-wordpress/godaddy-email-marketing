<?php
/**
 * Test Widget.
 *
 * @group widget
 */
class Test_GEM_Form_Widget extends WP_UnitTestCase {

	/**
	 * Test that GEM_Form_Widget exists.
	 */
	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Form_Widget', false ) );
	}

	/**
	 * Test constructor.
	 *
	 * @see GEM_Form_Widget::__construct()
	 */
	public function test_construct() {
		$instance = new GEM_Form_Widget();
		$this->assertEquals( 10, has_action( 'gem_widget_text', 'wpautop' ) );
		$this->assertEquals( 10, has_action( 'gem_widget_text', 'wptexturize' ) );
		$this->assertEquals( 10, has_action( 'gem_widget_text', 'convert_chars' ) );

		$this->assertEquals( 'gem-form', $instance->id_base );
		$this->assertEquals( 'GoDaddy Email Marketing Form', $instance->name );
		$this->assertEquals( 'widget_gem-form', $instance->option_name );
	}

	/**
	 * Test widget output.
	 *
	 * @see GEM_Form_Widget::widget()
	 */
	public function test_widget() {
		GEM_Settings_Controls::update_option( 'username', 'user_name' );
		GEM_Settings_Controls::update_option( 'api-key', '1234' );
		set_transient( 'gem-form-123', json_decode( '{"id":123,"name":"Signup Form","fields":{"field_a":{"type":"string","field_type":"string","name":"the_name_a","required":false,"display":"text_a"},"field_b":{"type":"checkbox","field_type":"checkbox","required":true,"name":"the_name_b","value":"the_value","display":"text_b"}},"submit":"the_url","button_text":"button_text"}' ), 60 );
		set_transient( 'gem-user_name-lists', json_decode( '{"total":1,"signups":[{"id":123,"name":"Signup Form","thumbnail":"the_url","url":"the_url"}]}' ), 60 );

		$widget = new GEM_Form_Widget();
		$args = array(
			'before_widget' => 'before_text',
			'after_widget' => 'after_text',
			'before_title' => 'before_title',
			'after_title' => 'after_title',
		);
		$instance = array(
			'title' => 'the_title',
			'text' => 'the_text',
			'form' => '123',
		);

		ob_start();
		$widget->widget( $args, $instance );
		$actual_output = ob_get_contents();
		ob_end_clean();

		delete_option( GEM_Settings::SLUG );
		delete_transient( 'gem-form-123' );
		delete_transient( 'gem-user_name-lists' );

		$this->assertContains( 'before_textbefore_titlethe_titleafter_title<p>the_text</p>', $actual_output );
		$this->assertContains( '<form action="http://the_url" method="post" class="gem-form">', $actual_output );
		$this->assertContains( '<label for="form_3_the_name_a">', $actual_output );
		$this->assertContains( 'text_a', $actual_output );
		$this->assertContains( '<input type="text" name="the_name_a" id="form_3_the_name_a" class="gem-field" data-label="text_a" />', $actual_output );
		$this->assertContains( '<label for="form_3_the_name_bthe_value">', $actual_output );
		$this->assertContains( '<input type="checkbox" value="the_value" name="the_name_b" id="form_3_the_name_bthe_value" class="gem-checkbox gem-required" />', $actual_output );
		$this->assertContains( 'text_b', $actual_output );
		$this->assertContains( '<input type="hidden" name="form_id" value="123" />', $actual_output );
		$this->assertContains( '<input type="submit" value="button_text" class="button gem-submit" />', $actual_output );
		$this->assertContains( 'after_text', $actual_output );
	}

	/**
	 * Test widget output.
	 *
	 * @see GEM_Form_Widget::widget()
	 */
	public function test_widget_with_false_id() {
		GEM_Settings_Controls::update_option( 'username', 'user_name' );
		GEM_Settings_Controls::update_option( 'api-key', '1234' );
		set_transient( 'gem-form-123', json_decode( '{"id":123,"name":"Signup Form","fields":{"field_a":{"type":"string","field_type":"string","name":"the_name_a","required":false,"display":"text_a"},"field_b":{"type":"checkbox","field_type":"checkbox","required":true,"name":"the_name_b","value":"the_value","display":"text_b"}},"submit":"the_url","button_text":"button_text"}' ), 60 );
		set_transient( 'gem-user_name-lists', json_decode( '{"total":1,"signups":[{"id":123,"name":"Signup Form","thumbnail":"the_url","url":"the_url"}]}' ), 60 );

		$widget = new GEM_Form_Widget();
		$args = array(
			'before_widget' => 'before_text',
			'after_widget' => 'after_text',
			'before_title' => 'before_title',
			'after_title' => 'after_title',
		);
		$instance = array(
			'title' => 'the_title',
			'text' => 'the_text',
			'form' => null,
		);

		ob_start();
		$widget->widget( $args, $instance );
		$actual_output = ob_get_contents();
		ob_end_clean();

		delete_option( GEM_Settings::SLUG );
		delete_transient( 'gem-form-123' );
		delete_transient( 'gem-user_name-lists' );

		$this->assertContains( '<input type="hidden" name="form_id" value="123" />', $actual_output );
	}

	/**
	 * Test update.
	 *
	 * @see GEM_Form_Widget::update()
	 */
	public function test_update() {
		$widget = new GEM_Form_Widget();

		$new_instance = array(
			'title' => '<b>the_title</b>',
			'text' => 'new_text',
			'form' => 123,
		);
		$old_instance = array(
			'title' => 'the_old',
			'text' => 'old_text',
			'form' => 456,
			'other' => 'new_value',
		);
		$output = $widget->update( $new_instance, $old_instance );
		$this->assertEquals( 'the_title', $output['title'] );
		$this->assertEquals( 'new_text', $output['text'] );
		$this->assertEquals( 123, $output['form'] );
		$this->assertEquals( 'new_value', $output['other'] );

		$new_instance['form'] = -123;
		$output = $widget->update( $new_instance, $old_instance );
		$this->assertEquals( 123, $output['form'] );
	}

	/**
	 * Test form.
	 *
	 * @see GEM_Form_Widget::form()
	 */
	public function test_form() {
		$widget = new GEM_Form_Widget();
		$user_name = 'the_user';
		$api_key = 'the_api_key';
		$sample_data = new stdClass();
		$sample_field = new stdClass();
		$sample_field->id = 'the_field_id';
		$sample_field->name = 'the_field_name';
		$sample_data->signups = array( $sample_field );

		GEM_Settings_Controls::update_option( 'username', $user_name );
		GEM_Settings_Controls::update_option( 'api-key', $api_key );
		set_transient( 'gem-' . $user_name . '-lists', $sample_data );

		$instance = array(
			'title' => 'the_title',
			'text' => 'the_text',
			'form' => 123,
		);
		ob_start();
		$widget->form( $instance );
		$actual_output = ob_get_contents();
		ob_end_clean();
		delete_transient( 'gem-' . $user_name . '-lists' );

		$this->assertContains( '<input class="widefat" id="widget-gem-form--title" name="widget-gem-form[][title]" type="text" value="the_title" />', $actual_output );
		$this->assertContains( '<textarea class="widefat" rows="3" id="widget-gem-form--text" name="widget-gem-form[][text]">the_text</textarea>', $actual_output );
		$this->assertContains( '<label for="widget-gem-form--form">Form:</label>', $actual_output );
		$this->assertContains( '<select name="widget-gem-form[][form]" id="widget-gem-form--form" class="widefat" value="123">', $actual_output );
		$this->assertContains( '<option value="the_field_id" >the_field_name</option>', $actual_output );
	}

	/**
	 * Test form message on failure.
	 *
	 * @see GEM_Form_Widget::form()
	 */
	public function test_form_fails_message() {
		$widget = new GEM_Form_Widget();
		$user_name = 'the_user';
		$api_key = 'the_api_key';
		$sample_data = new stdClass();
		$sample_data->signups = array();

		GEM_Settings_Controls::update_option( 'username', $user_name );
		GEM_Settings_Controls::update_option( 'api-key', $api_key );
		set_transient( 'gem-' . $user_name . '-lists', $sample_data );

		$instance = array(
			'title' => 'the_title',
			'text' => 'the_text',
			'form' => 123,
		);
		ob_start();
		$widget->form( $instance );
		$actual_output = ob_get_contents();
		ob_end_clean();
		delete_transient( 'gem-' . $user_name . '-lists' );

		$this->assertContains( 'Please set up your GoDaddy Email Marketing account in the', $actual_output );
	}
}
