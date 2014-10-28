<?php

class Mad_Mimi_Form_Renderer {

	private static $loops = 0;

	public function process( $form_id, $echo = false ) {
		$form = Mad_Mimi_Dispatcher::get_fields( (int) $form_id );

		if ( ! empty( $form->fields ) ) :

			self::$loops++; ob_start(); ?>

			<div class="mimi-form-wrapper" id="form-<?php echo absint( $form_id ); ?>">
				<form action="<?php echo esc_url( $form->submit ); ?>" method="post" class="mimi-form">

					<?php do_action( 'mimi_before_fields', $form_id, $form->fields ); ?>

					<?php foreach ( $form->fields as $count => $field ) : ?>

						<p><?php Mad_Mimi_Form_Fields::dispatch_field( $field, self::$loops ); ?></p>

					<?php endforeach; ?>

					<?php do_action( 'mimi_after_fields', $form_id, $form->fields ); ?>

					<?php
					$show_powered_by = Mad_Mimi_Settings_Controls::get_option( 'display_powered_by' ) ? true : false;

					if ( $show_powered_by ) : ?>
					<p>
						<a href="http://madmimi.com" target="_blank"><?php _e( 'Powered by Mad Mimi', 'mimi' ); ?></a>
					</p>
					<?php endif; ?>

					<input type="hidden" name="form_id" value="<?php echo absint( $form->id ); ?>" />
					<input type="submit" value="<?php echo esc_attr( $form->button_text ); ?>" class="button mimi-submit" />
					<span class="mimi-spinner"></span>
				</form>
			</div>
			<?php

			$output = ob_get_clean();

			if ( $echo )
				echo $output;

			return $output;

		endif;
	}
}

class Mad_Mimi_Form_Fields {

	private static $cycle = 0;

	public function dispatch_field( $field, $cycle = 1 ) {
		if ( ! is_object( $field ) || ! method_exists( __CLASS__, $field->type ) )
			return;

		self::$cycle = absint( $cycle );

		call_user_func( array( __CLASS__, $field->type ), $field );
	}

	public function get_form_id( $field_name ) {
		// since HTML ID's can't exist in the same exact spelling more than once... make it special.
		return esc_attr( sprintf( 'form_%s_%s', self::$cycle, $field_name ) );
	}

	public static function string( $args ) {
		$field_classes = array( 'mimi-field' );

		// is this field required?
		if ( $args->required )
			$field_classes[] = 'mimi-required';

		$field_classes = (array) apply_filters( 'mimi_required_field_class', $field_classes, $args );
		?>
		<label for="<?php echo self::get_form_id( $args->name ); ?>">
			<?php echo esc_html( $args->display ); ?>
			<?php if ( $args->required && apply_filters( 'mimi_required_field_indicator', true, $args ) ) : ?>
			<span class="required">*</span>
			<?php endif; ?>
		</label>
		<input type="text" name="<?php echo esc_attr( $args->name ); ?>" id="<?php echo self::get_form_id( $args->name ); ?>" class="<?php echo esc_attr( join( ' ', $field_classes ) ); ?>" />
		<?php
	}

	public static function checkbox( $args ) {
		$field_classes = array( 'mimi-checkbox' );

		// is this field required?
		if ( $args->required )
			$field_classes[] = 'mimi-required';

		$field_classes = (array) apply_filters( 'mimi_required_field_class', $field_classes, $args );
		?>
		<label for="<?php echo self::get_form_id( $args->name ) . esc_attr( $args->value ); ?>">
			<input type="checkbox" value="<?php echo esc_attr( $args->value ); ?>" name="<?php echo esc_attr( $args->name ); ?>" id="<?php echo self::get_form_id( $args->name ) . esc_attr( $args->value ); ?>" class="<?php echo esc_attr( join( ' ', $field_classes ) ); ?>" />
			<?php echo esc_html( $args->display ); ?>
			<?php if ( $args->required && apply_filters( 'mimi_required_field_indicator', true, $args ) ) : ?>
			<span class="required">*</span>
			<?php endif; ?>
		</label>
		<?php
	}
}
