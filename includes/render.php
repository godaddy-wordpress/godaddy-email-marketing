<?php

class Mad_Mimi_Form_Renderer {

	function process( $form_id ) {
		$form = Mad_Mimi_Dispatcher::get_fields( (int) $form_id );

		if ( ! empty( $form->fields ) ) : ?>
			<form action="<?php echo esc_url( $form->submit ); ?>" method="post" class="mimi-form">
			
				<?php

				foreach ( $form->fields as $count => $field ) {
					echo "\n<p>\n";

					Mad_Mimi_Form_Fields::dispatch_field( $field );

					echo "\n</p>\n";
				}

				?>

				<p>
					<a href="http://madmimi.com" target="_blank">Powered by Mad Mimi</a>
				</p>

				<input type="submit" value="<?php _e( 'Submit', 'mimi' ); ?>" class="button mimi-submit" />
			</form>
			<?php

		endif;
	}
}

final class Mad_Mimi_Form_Fields {

	public function dispatch_field( $field ) {
		if ( ! is_object( $field ) || ! method_exists( __CLASS__, $field->type ) )
			return false;

		call_user_func( array( __CLASS__, $field->type ), $field );
	}

	public static function string( $args ) {
		$required = apply_filters( 'mimi_required_field_class', $args->required ? 'mimi-required' : '', $args );
		?>
		<label for="<?php echo esc_attr( $args->name ); ?>">
			<?php echo esc_html( $args->display ); ?>
			<?php if ( $args->required && apply_filters( 'mimi_required_field_indicator', true, $args ) ) : ?>
			<span class="required">*</span>
			<?php endif; ?>
		</label>
		<input type="text" name="<?php echo esc_attr( $args->name ); ?>" id="<?php echo esc_attr( $args->name ); ?>" class="<?php echo esc_attr( $required ); ?>" />
		<?php
	}
}