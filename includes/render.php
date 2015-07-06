<?php

class GEM_Form_Renderer {

	private static $loops = 0;

	public function process( $form_id, $echo = false ) {

		$form = GEM_Dispatcher::get_fields( (int) $form_id );

		if ( ! empty( $form->fields ) ) :

			self::$loops++; ob_start(); ?>

			<div class="gem-form-wrapper" id="form-<?php echo absint( $form_id ); ?>">
				<form action="<?php echo esc_url( $form->submit ); ?>" method="post" class="gem-form">

					<?php do_action( 'gem_before_fields', $form_id, $form->fields ); ?>

					<?php foreach ( $form->fields as $count => $field ) : ?>

						<p><?php GEM_Form_Fields::dispatch_field( $field, self::$loops ); ?></p>

					<?php endforeach; ?>

					<?php do_action( 'gem_after_fields', $form_id, $form->fields ); ?>

					<?php $show_powered_by = GEM_Settings_Controls::get_option( 'display_powered_by' ) ? true : false;

					if ( $show_powered_by ) : ?>

						<p>
							<a href="http://madmimi.com" target="_blank"><?php esc_html_e( 'Powered by GoDaddy', 'gem' ); ?></a>
						</p>

					<?php endif; ?>

					<input type="hidden" name="form_id" value="<?php echo absint( $form->id ); ?>" />
					<input type="submit" value="<?php echo esc_attr( $form->button_text ); ?>" class="button gem-submit" />
					<span class="gem-spinner"></span>

				</form>
			</div>

			<?php $output = ob_get_clean();

			if ( $echo ) {
				echo $output;
			}

			return $output;

		endif;

	}
}

class GEM_Form_Fields {

	private static $cycle = 0;

	public function dispatch_field( $field, $cycle = 1 ) {

		if ( ! is_object( $field ) || ! method_exists( __CLASS__, $field->type ) ) {
			return;
		}

		self::$cycle = absint( $cycle );

		call_user_func( array( __CLASS__, $field->type ), $field );

	}

	public function get_form_id( $field_name ) {

		// since HTML ID's can't exist in the same exact spelling more than once... make it special.
		return sprintf( 'form_%s_%s', self::$cycle, $field_name );

	}

	public static function string( $args ) {

		$field_classes = array( 'gem-field' );

		// is this field required?
		if ( $args->required ) {
			$field_classes[] = 'gem-required';
		}

		$field_classes = (array) apply_filters( 'gem_required_field_class', $field_classes, $args ); ?>

		<label for="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>">

			<?php echo esc_html( $args->display ); ?>

			<?php if ( $args->required && apply_filters( 'gem_required_field_indicator', true, $args ) ) : ?>
				<span class="required">*</span>
			<?php endif; ?>

		</label>

		<input type="text" name="<?php echo esc_attr( $args->name ); ?>" id="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>" class="<?php echo esc_attr( join( ' ', $field_classes ) ); ?>" />

	<?php }

	public static function checkbox( $args ) {

		$field_classes = array( 'gem-checkbox' );

		// is this field required?
		if ( $args->required ) {
			$field_classes[] = 'gem-required';
		}

		$field_classes = (array) apply_filters( 'gem_required_field_class', $field_classes, $args ); ?>

		<label for="<?php echo esc_attr( self::get_form_id( $args->name ) . $args->value ); ?>">

			<input type="checkbox" value="<?php echo esc_attr( $args->value ); ?>" name="<?php echo esc_attr( $args->name ); ?>" id="<?php echo esc_attr( self::get_form_id( $args->name ) . $args->value ); ?>" class="<?php echo esc_attr( join( ' ', $field_classes ) ); ?>" />

			<?php echo esc_html( $args->display ); ?>

			<?php if ( $args->required && apply_filters( 'gem_required_field_indicator', true, $args ) ) : ?>
				<span class="required">*</span>
			<?php endif; ?>

		</label>

	<?php }
}
