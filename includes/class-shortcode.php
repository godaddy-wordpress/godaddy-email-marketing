<?php

class Mad_Mimi_Shortcode {

	public function render( $atts ) {
		extract( shortcode_atts( array(
			'id' => false,
		), $atts ) );

		if ( ! $id )
			return;

		Mad_Mimi_Form_Renderer::process( $id );
	}
}