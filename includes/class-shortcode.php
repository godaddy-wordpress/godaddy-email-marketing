<?php
/**
 * Shortcode class & template tag
 *
 * @package GEM
 */

/**
 * GoDaddy Email Marketing shortcode.
 *
 * @since 1.0
 */
class GEM_Shortcode {

	/**
	 * Renders the shortcode.
	 *
	 * @param array $atts An array of shortcode attributes.
	 *
	 * @return string|void
	 */
	public function render( $atts ) {
		extract( shortcode_atts( array(
			'id' => false,
		), $atts ) );

		if ( ! $id ) {
			return;
		}

		return gem_form( $id, false );
	}

	/**
	 * Registers the shortcode UI with Shortcake.
	 *
	 * @codeCoverageIgnore
	 */
	public function shortcode_ui() {
		$forms = GEM_Dispatcher::get_forms();

		if ( ! empty( $forms->signups ) ) {
			$options = array();
			foreach ( $forms->signups as $form ) {
				$options[ $form->id ] = esc_html( $form->name );
			}
			reset( $options );

			$args = array(
				'label' => esc_html__( 'GoDaddy Email Marketing', 'godaddy-email-marketing' ),
				'listItemImage' => 'dashicons-feedback',
				'attrs' => array(
					array(
						'label'       => esc_html__( 'Signup Forms', 'godaddy-email-marketing' ),
						'description' => esc_html__( 'Choose one of the available forms.', 'godaddy-email-marketing' ),
						'attr'        => 'id',
						'encode'      => false,
						'type'        => 'select',
						'options'     => $options,
						'value'       => key( $options ),
					),
				),
			);

			shortcode_ui_register_for_shortcode( 'gem', $args );
		}
	}
}

/**
 * The main template tag. Pass on the ID and watch the magic happen.
 *
 * @since 1.0
 * @see   GEM_Form_Renderer
 *
 * @param int  $id   The ID of the form you wish to output.
 * @param bool $echo Wether to echo the form field. Default true.
 *
 * @return string
 */
function gem_form( $id, $echo = true ) {
	if ( class_exists( 'GEM_Form_Renderer', false ) ) {
		$renderer = new GEM_Form_Renderer();
		$form = $renderer->process( $id, false );

		if ( ! $echo ) {
			return $form;
		}

		echo $form;//xss ok
	}
}
