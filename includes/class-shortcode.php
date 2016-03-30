<?php

class GEM_Shortcode {

	public function render( $atts ) {

		extract( shortcode_atts( array(
			'id' => false,
		), $atts ) );

		if ( ! $id ) {
			return;
		}

		return GEM_Form_Renderer::process( $id, false );

	}
}

/**
 * The main template tag. Pass on the ID and watch the magic happen.
 *
 * @since 1.0
 * @see GEM_Form_Renderer
 * @param int $id The ID of the form you wish to output
 */
function gem_form( $id ) {

	if ( ! class_exists( 'GEM_Form_Renderer' ) ) {
		return;
	}

	$renderer = new GEM_Form_Renderer();
	$renderer->process( $id, true );

}
