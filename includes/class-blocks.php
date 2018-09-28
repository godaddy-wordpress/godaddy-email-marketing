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

	}

	public function enqueue_block_scripts() {

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'gem-blocks', plugins_url( "../js/blocks{$suffix}.js", __FILE__ ), array( 'wp-i18n', 'wp-element', 'wp-blocks', 'wp-components' ), GEM_VERSION, true );

		wp_localize_script( 'gem-blocks', 'gem', [
			'forms' => $this->get_forms(),
		] );

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
