<?php
/**
 * Dispatcher class
 *
 * @package GEM
 */

/**
 * GoDaddy Email Marketing Dispatcher.
 *
 * @since 1.0
 */
class GEM_Blocks {

	public function __construct() {

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_scripts' ) );

		add_action( 'wp_ajax_get_gem_form', [ $this, 'get_gem_form' ] );

	}

	public function enqueue_block_scripts() {

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'gem-blocks', plugins_url( "../js/blocks{$suffix}.js", __FILE__ ), array( 'wp-i18n', 'wp-element', 'wp-blocks', 'wp-components' ), GEM_VERSION, true );

		wp_localize_script(
			'gem-blocks',
			'gem',
			[
				'forms'        => $this->get_forms(),
				'settingsURL'  => admin_url( 'options-general.php?page=gem-settings' ),
				'getFormError' => __( 'There was an error retreiving the GEM form. Please try again.', 'godaddy-email-marketing-sign-up-forms' ),
			]
		);

	}

	/**
	 * Render the GEM form in the block
	 *
	 * @since NEXT
	 *
	 * @return mixed Markup for the GEM form
	 */
	public function get_gem_form() {

		$form_id = filter_input( INPUT_POST, 'formID', FILTER_SANITIZE_NUMBER_INT );

		if ( ! $form_id ) {

			wp_send_json_error();

		}

		wp_send_json_success( gem_form( $form_id, false ) );

	}

	/**
	 * Retreive the GEM forms.
	 *
	 * @return array GEM forms array.
	 */
	private function get_forms() {

		$forms = GEM_Dispatcher::fetch_forms();

		if ( empty( $forms->signups ) ) {

			return [];

		}

		$forms_array = [];

		foreach ( $forms->signups as $form ) {

			$forms_array[] = [
				'label' => $form->name,
				'value' => $form->id,
			];

		}

		return $forms_array;

	}
}
