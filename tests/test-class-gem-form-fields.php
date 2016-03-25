<?php
namespace GEM;

require_once( 'testcase.php' );

class Test_GEM_Form_Fields extends WP_GEMTestCase {

	/**
	 * PHP unit setup function
	 *
	 * @return void
	 */
	function setUp() {
		parent::setUp();
	}

	public function test_basics() {
		$this->assertTrue( class_exists( 'GEM_Form_Fields', false ) );
	}

	public function test_dispatch_field() {
		$this->assertEmpty( \GEM_Form_Fields::dispatch_field( 'not_an_object' ) );

		$field = new \stdClass();
		$field->type = 'incorrect_type';
		$this->assertEmpty( \GEM_Form_Fields::dispatch_field( $field ) );

		$field = new \stdClass();
		$field->type = 'string';
		$field->field_type = 'string';
		$field->name = 'the_name_a';
		$field->required = false;
		$field->display = 'text_a';
		ob_start();
		\GEM_Form_Fields::dispatch_field( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<input type="text" name="the_name_a" id="form_1_the_name_a" class="gem-field" />', $actual_output );

		$field = new \stdClass();
		$field->type = 'checkbox';
		$field->field_type = 'string';
		$field->name = 'the_name_a';
		$field->required = false;
		$field->display = 'text_a';
		ob_start();
		\GEM_Form_Fields::dispatch_field( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<input type="text" name="the_name_a" id="form_1_the_name_a" class="gem-field" />', $actual_output );
	}

	public function test_string() {
		add_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );
		$field = new \stdClass();
		$field->name = 'the_name_a';
		$field->required = false;
		$field->display = 'text_a';
		ob_start();
		\GEM_Form_Fields::string( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<label for="form_1_the_name_a">', $actual_output );
		$this->assertContains( '<input type="text" name="the_name_a" id="form_1_the_name_a" class="gem-field a_sample_class" />', $actual_output );
		remove_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );

		$field->required = true;
		ob_start();
		\GEM_Form_Fields::string( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<label for="form_1_the_name_a">', $actual_output );
		$this->assertContains( '<span class="required">*</span>', $actual_output );
		$this->assertContains( '<input type="text" name="the_name_a" id="form_1_the_name_a" class="gem-field gem-required" />', $actual_output );
	}

	public function test_checkbox() {
		add_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );
		$field = new \stdClass();
		$field->name = 'the_name_a';
		$field->required = false;
		$field->display = 'text_a';
		$field->value = 'the_value';
		ob_start();
		\GEM_Form_Fields::checkbox( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<label for="form_1_the_name_athe_value">', $actual_output );
		$this->assertContains( '<input type="checkbox" value="the_value" name="the_name_a" id="form_1_the_name_athe_value" class="gem-checkbox a_sample_class" />', $actual_output );
		$this->assertContains( 'text_a', $actual_output );
		remove_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );

		$field->required = true;
		ob_start();
		\GEM_Form_Fields::checkbox( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<span class="required">*</span>', $actual_output );
		$this->assertContains( '<label for="form_1_the_name_athe_value">', $actual_output );
		$this->assertContains( '<input type="checkbox" value="the_value" name="the_name_a" id="form_1_the_name_athe_value" class="gem-checkbox gem-required" />', $actual_output );
		$this->assertContains( 'text_a', $actual_output );
	}

	public function test_checkboxes() {
		add_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );
		$field = new \stdClass();
		$field->name = 'the_name_a';
		$field->required = false;
		$field->display = 'text_a';
		$field->value = 'the_value';
		$field->options = '["Option 1","Option 2"]';
		ob_start();
		\GEM_Form_Fields::checkboxes( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<label for="form_1_the_name_a">', $actual_output );
		$this->assertContains( 'text_a', $actual_output );
		$this->assertContains( '<input type="checkbox" id="form_1_the_name_a" name="the_name_a" value="Option 1"> Option 1<br>', $actual_output );
		$this->assertContains( '<input type="checkbox" id="form_1_the_name_a" name="the_name_a" value="Option 2"> Option 2<br>', $actual_output );
		remove_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );

		$field->required = true;
		ob_start();
		\GEM_Form_Fields::checkboxes( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<span class="required">*</span>', $actual_output );
		$this->assertContains( '<label for="form_1_the_name_a">', $actual_output );
		$this->assertContains( 'text_a', $actual_output );
		$this->assertContains( '<input type="checkbox" id="form_1_the_name_a" name="the_name_a" value="Option 1"> Option 1<br>', $actual_output );
		$this->assertContains( '<input type="checkbox" id="form_1_the_name_a" name="the_name_a" value="Option 2"> Option 2<br>', $actual_output );
	}

	public function test_dropdown() {
		add_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );
		$field = new \stdClass();
		$field->name = 'the_name_a';
		$field->required = false;
		$field->display = 'text_a';
		$field->value = 'the_value';
		$field->options = '["Option 1","Option 2"]';
		ob_start();
		\GEM_Form_Fields::dropdown( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<label for="form_1_the_name_a">', $actual_output );
		$this->assertContains( 'text_a', $actual_output );
		$this->assertContains( '<select id="form_1_the_name_a" name="the_name_a">', $actual_output );
		$this->assertContains( '<option value="Option 1"> Option 1<br>', $actual_output );
		$this->assertContains( '<option value="Option 2"> Option 2<br>', $actual_output );
		remove_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );

		$field->required = true;
		ob_start();
		\GEM_Form_Fields::dropdown( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<span class="required">*</span>', $actual_output );
		$this->assertContains( '<label for="form_1_the_name_a">', $actual_output );
		$this->assertContains( 'text_a', $actual_output );
		$this->assertContains( '<select id="form_1_the_name_a" name="the_name_a">', $actual_output );
		$this->assertContains( '<option value="Option 1"> Option 1<br>', $actual_output );
		$this->assertContains( '<option value="Option 2"> Option 2<br>', $actual_output );
	}

	public function test_radio_buttons() {
		add_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );
		$field = new \stdClass();
		$field->name = 'the_name_a';
		$field->required = false;
		$field->display = 'text_a';
		$field->value = 'the_value';
		$field->options = '["Option 1","Option 2"]';
		ob_start();
		\GEM_Form_Fields::radio_buttons( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<label for="form_1_the_name_a">', $actual_output );
		$this->assertContains( 'text_a', $actual_output );
		$this->assertContains( '<input type="radio" id="form_1_the_name_a" name="the_name_a" value="Option 1"> Option 1<br>', $actual_output );
		$this->assertContains( '<input type="radio" id="form_1_the_name_a" name="the_name_a" value="Option 2"> Option 2<br>', $actual_output );
		remove_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );

		$field->required = true;
		ob_start();
		\GEM_Form_Fields::radio_buttons( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<span class="required">*</span>', $actual_output );
		$this->assertContains( '<label for="form_1_the_name_a">', $actual_output );
		$this->assertContains( 'text_a', $actual_output );
		$this->assertContains( '<input type="radio" id="form_1_the_name_a" name="the_name_a" value="Option 1"> Option 1<br>', $actual_output );
		$this->assertContains( '<input type="radio" id="form_1_the_name_a" name="the_name_a" value="Option 2"> Option 2<br>', $actual_output );
	}

	public function test_date() {
		add_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );
		$field = new \stdClass();
		$field->name = 'the_name_a';
		$field->required = false;
		$field->display = 'text_a';
		$field->value = 'the_value';
		ob_start();
		\GEM_Form_Fields::date( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<label for="form_1_the_name_a">', $actual_output );
		$this->assertContains( 'text_a', $actual_output );
		$this->assertContains( '<select fingerprint="date" id="form_1_the_name_a" name="the_name_a">', $actual_output );
		$this->assertContains( '<option value="January"> January </option>', $actual_output );
		$this->assertContains( '<option value="December"> December </option>', $actual_output );
		$this->assertContains( '<select fingerprint="date" id="form_1_the_name_a" name="the_name_a">', $actual_output );
		$this->assertContains( '<option value="01"> 1 </option>', $actual_output );
		$this->assertContains( '<option value="31"> 31 </option>', $actual_output );
		$this->assertContains( '<select fingerprint="date" id="form_1_the_name_a" name="the_name_a">', $actual_output );
		$this->assertContains( '<option value="2021"> 2021 </option>', $actual_output );
		$this->assertContains( '<option value="1936"> 1936 </option>', $actual_output );
		$this->assertContains( '<input type="hidden" id="form_1_the_name_a" name="the_name_a" value="">', $actual_output );
		remove_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );

		$field->required = true;
		ob_start();
		\GEM_Form_Fields::date( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<span class="required">*</span>', $actual_output );
	}

	public function test_text_field() {
		add_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );
		$field = new \stdClass();
		$field->name = 'the_name_a';
		$field->required = false;
		$field->display = 'text_a';
		$field->value = 'the_value';
		ob_start();
		\GEM_Form_Fields::text_field( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<label for="form_1_the_name_a">', $actual_output );
		$this->assertContains( 'text_a', $actual_output );
		$this->assertContains( '<input type="text" name="the_name_a" id="form_1_the_name_a" class="gem-field a_sample_class" />', $actual_output );
		remove_action( 'gem_required_field_class', array( $this, 'gem_required_field_class_callback' ) );

		$field->required = true;
		ob_start();
		\GEM_Form_Fields::text_field( $field );
		$actual_output = ob_get_contents();
		ob_end_clean();
		$this->assertContains( '<label for="form_1_the_name_a">', $actual_output );
		$this->assertContains( 'text_a', $actual_output );
		$this->assertContains( '<span class="required">*</span>', $actual_output );
		$this->assertContains( '<input type="text" name="the_name_a" id="form_1_the_name_a" class="gem-field gem-required" />', $actual_output );
	}

	public function gem_required_field_class_callback( $field_classes ) {
		$field_classes[] = 'a_sample_class';

		return $field_classes;
	}
}
