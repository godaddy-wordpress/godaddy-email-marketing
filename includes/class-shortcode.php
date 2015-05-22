<?php

class Mad_Mimi_Shortcode {

	public function render( $atts ) {

		extract( shortcode_atts( array(
			'id' => false,
		), $atts ) );

		if ( ! $id ) {
			return;
		}

		return Mad_Mimi_Form_Renderer::process( $id, false );

	}
}

/**
 * The main template tag. Pass on the ID and watch the magic happen.
 *
 * @since 1.0
 * @see Mad_Mimi_Form_Renderer
 * @param int $id The ID of the form you wish to output
 */
function madmimi_form( $id ) {

	if ( ! class_exists( 'Mad_Mimi_Form_Renderer' ) ) {
		return;
	}

	Mad_Mimi_Form_Renderer::process( $id, true );

}
