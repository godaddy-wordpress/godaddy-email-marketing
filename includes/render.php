<?php
/**
 * Render classes
 *
 * @package GEM
 */

/**
 * GoDaddy Email Marketing form.
 *
 * @since 1.0
 */
class GEM_Form_Renderer {

	/**
	 * Form instance number.
	 *
	 * @var int
	 */
	private static $loops = 0;

	/**
	 * Generates the form.
	 *
	 * @param string $form_id Form ID.
	 * @param bool   $echo    Wether to echo the form field. Default false.
	 *
	 * @return string
	 */
	public function process( $form_id, $echo = false ) {
		$form = GEM_Dispatcher::get_fields( (int) $form_id );
		$forms = GEM_Dispatcher::get_forms();
		$form_ids = array();

		if ( ! empty( $forms->signups ) ) {
			foreach ( $forms->signups as $signup_form ) {
				$form_ids[] = $signup_form->id;
			}
		}

		if ( ! empty( $form->fields ) && in_array( (int) $form_id, $form_ids, true ) ) :

			self::$loops++;
			ob_start();
			?>

			<div class="gem-form-wrapper" id="form-<?php echo absint( $form_id ); ?>">
				<form action="<?php echo esc_url( $form->submit ); ?>" method="post" class="gem-form">

					<?php do_action( 'gem_before_fields', $form_id, $form->fields ); ?>

					<?php foreach ( $form->fields as $count => $field ) : ?>

						<p><?php GEM_Form_Fields::dispatch_field( $field, self::$loops ); ?></p>

					<?php endforeach; ?>

					<?php do_action( 'gem_after_fields', $form_id, $form->fields ); ?>

					<p>
						<input type="hidden" name="form_id" value="<?php echo absint( $form->id ); ?>" />
						<input type="submit" value="<?php echo esc_attr( $form->button_text ); ?>" class="button gem-submit" />
						<span class="gem-spinner"></span>
					</p>

					<?php $show_powered_by = GEM_Settings_Controls::get_option( 'display_powered_by' ) ? true : false;

					if ( $show_powered_by ) : ?>

						<p>
							<a href="https://www.godaddy.com/online-marketing/email-marketing" rel="nofollow" target="_blank"><?php esc_html_e( 'Powered by GoDaddy', 'godaddy-email-marketing' ); ?></a>
						</p>

					<?php endif; ?>

				</form>
			</div>

			<?php $output = ob_get_clean();

			if ( $echo ) {
				echo $output; // xss ok
			}

			return $output;

		endif;
	}
}

/**
 * GoDaddy Email Marketing form fields.
 *
 * @since 1.0
 */
class GEM_Form_Fields {

	/**
	 * Form instance number.
	 *
	 * @var int
	 */
	private static $cycle = 0;

	/**
	 * Dispatches the method used to display fields.
	 *
	 * @param object $field Form field.
	 * @param int    $cycle Form instance number.
	 */
	public static function dispatch_field( $field, $cycle = 1 ) {
		if ( ! is_object( $field ) || ! method_exists( __CLASS__, $field->type ) ) {
			return;
		}

		self::$cycle = absint( $cycle );

		if ( ! is_null( $field->field_type ) ) {
			call_user_func( array( __CLASS__, $field->field_type ), $field );
		} else {
			call_user_func( array( __CLASS__, $field->type ), $field );
		}
	}

	/**
	 * Generates the ID form the form name.
	 *
	 * @param string $field_name Form name.
	 * @return string The form ID.
	 */
	public static function get_form_id( $field_name ) {

		// Since HTML ID's can't exist in the same exact spelling more than once... make it special.
		return sprintf( 'form_%s_%s', self::$cycle, $field_name );
	}

	/**
	 * Displays the string field.
	 *
	 * @todo How is this differnt from the GEM_Form_Fields::text_field method?
	 *
	 * @param array $args Settings field arguments.
	 */
	public static function string( $args ) {
		$field_classes = array( 'gem-field' );

		// Is this field required?
		if ( $args->required ) {
			$field_classes[] = 'gem-required';
		}

		$field_classes = (array) apply_filters( 'gem_required_field_class', $field_classes, $args );
		?>

		<label for="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>">

			<?php echo esc_html( $args->display ); ?>

			<?php if ( $args->required && apply_filters( 'gem_required_field_indicator', true, $args ) ) : ?>
				<span class="required">*</span>
			<?php endif; ?>

		</label>
		<br/>

		<input type="text" name="<?php echo esc_attr( $args->name ); ?>" id="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>" class="<?php echo esc_attr( join( ' ', $field_classes ) ); ?>" data-label="<?php echo esc_attr( $args->display ); ?>" />

		<?php
	}

	/**
	 * Displays the checkbox field.
	 *
	 * @todo This appears to be deprecated.
	 *
	 * @param array $args Settings field arguments.
	 */
	public static function checkbox( $args ) {
		$field_classes = array( 'gem-checkbox' );

		// Is this field required?
		if ( $args->required ) {
			$field_classes[] = 'gem-required';
		}

		$field_classes = (array) apply_filters( 'gem_required_field_class', $field_classes, $args );
		?>

		<label for="<?php echo esc_attr( self::get_form_id( $args->name ) . $args->value ); ?>">

			<input type="checkbox" value="<?php echo esc_attr( $args->value ); ?>" name="<?php echo esc_attr( $args->name ); ?>" id="<?php echo esc_attr( self::get_form_id( $args->name ) . $args->value ); ?>" class="<?php echo esc_attr( join( ' ', $field_classes ) ); ?>" />

			<?php echo esc_html( $args->display ); ?>

			<?php if ( $args->required && apply_filters( 'gem_required_field_indicator', true, $args ) ) : ?>
				<span class="required">*</span>
			<?php endif; ?>

		</label>
		<br/>

		<?php
	}

	/**
	 * Displays the checkboxes field.
	 *
	 * @param array $args Settings field arguments.
	 */
	public static function checkboxes( $args ) {
		?>

		<label for="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>">
			<?php echo esc_html( $args->display ); ?>
			<?php if ( $args->required && apply_filters( 'gem_required_field_indicator', true, $args ) ) : ?>
				<span class="required">*</span>
			<?php endif; ?>
		</label>
		</br>

		<?php
		$trim_values = array( '[', ']' );
		$options = $args->options;
		foreach ( $trim_values as $trim ) {
			$options = trim( $options, $trim );
		}

		$trimmed_options = array();
		$options = str_replace( '"', '', $options );
		$trimmed_options = explode( ',', $options );

		foreach ( $trimmed_options as $key => $value ) : ?>
			<input type="checkbox" data-id="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>" data-name="<?php echo esc_attr( $args->name ); ?>" value="<?php echo esc_attr( $value ); ?>" /> <?php echo esc_attr( $value ); ?><br>
		<?php endforeach; ?>

		<input type="hidden" id="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>" name="<?php echo esc_attr( $args->name ); ?>" value="" class="<?php echo $args->required ? 'gem-required' : ''; ?>" data-label="<?php echo esc_attr( $args->display ); ?>" />

		<?php
	}

	/**
	 * Displays the select dropdown field.
	 *
	 * @param array $args Settings field arguments.
	 */
	public static function dropdown( $args ) {
		?>

		<label for="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>">
			<?php echo esc_html( $args->display ); ?>
			<?php if ( $args->required && apply_filters( 'gem_required_field_indicator', true, $args ) ) : ?>
				<span class="required">*</span>
			<?php endif; ?>
		</label>
		</br>
		<select id="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>" name="<?php echo esc_attr( $args->name ); ?>" class="<?php echo $args->required ? 'gem-required' : ''; ?>" data-label="<?php echo esc_attr( $args->display ); ?>">

		<?php $trim_values = array( '[', ']' );
		$options = $args->options;
		foreach ( $trim_values as $trim ) {
			$options = trim( $options, $trim );
		}
		$trimmed_options = array();
		$options = str_replace( '"', '', $options );
		$trimmed_options = explode( ',', $options );

		foreach ( $trimmed_options as $dropdown_options ) : ?>
			<option value="<?php echo esc_attr( $dropdown_options ); ?>"> <?php echo esc_html( $dropdown_options ); ?><br>
		<?php endforeach; ?>
		</select>

		<?php
	}

	/**
	 * Displays the radio buttons field.
	 *
	 * @param array $args Settings field arguments.
	 */
	public static function radio_buttons( $args ) {
		?>

		<label for="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>" class="<?php echo $args->required ? 'gem-required' : ''; ?>" data-label="<?php echo esc_attr( $args->display ); ?>" data-name="<?php echo esc_attr( $args->name ); ?>">
			<?php echo esc_html( $args->display ); ?>
			<?php if ( $args->required && apply_filters( 'gem_required_field_indicator', true, $args ) ) : ?>
				<span class="required">*</span>
			<?php endif; ?>
		</label>
		</br>

		<?php $trim_values = array( '[', ']' );
		$options = $args->options;
		foreach ( $trim_values as $trim ) {
			$options = trim( $options, $trim );
		}

		$trimmed_options = array();
		$options = str_replace( '"', '', $options );
		$trimmed_options = explode( ',', $options );

		foreach ( $trimmed_options as $key => $value ) : ?>
			<input type="radio" id="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>" name="<?php echo esc_attr( $args->name ); ?>" value="<?php echo esc_attr( $value ); ?>" /> <?php echo esc_html( $value ); ?><br>
		<?php endforeach;
	}

	/**
	 * Displays the date field.
	 *
	 * @param array $args Settings field arguments.
	 */
	public static function date( $args ) {
		?>

		<label for="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>">
			<?php echo esc_html( $args->display ); ?>
			<?php if ( $args->required && apply_filters( 'gem_required_field_indicator', true, $args ) ) : ?>
				<span class="required">*</span>
			<?php endif; ?>
		</label>
		</br>

		<?php $current_year = date( 'Y' ); ?>

		<span class="third">
			<select fingerprint="date" data-id="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>" data-name="<?php echo esc_attr( $args->name ); ?>">
				<option value=""> <?php esc_html_e( 'Month', 'godaddy-email-marketing' ) ?> </option>
				<option value="<?php esc_attr_e( 'January', 'godaddy-email-marketing' ) ?>"> <?php esc_html_e( 'January', 'godaddy-email-marketing' ) ?> </option>
				<option value="<?php esc_attr_e( 'Febuary', 'godaddy-email-marketing' ) ?>"> <?php esc_html_e( 'Febuary', 'godaddy-email-marketing' ) ?> </option>
				<option value="<?php esc_attr_e( 'March', 'godaddy-email-marketing' ) ?>"> <?php esc_html_e( 'March', 'godaddy-email-marketing' ) ?> </option>
				<option value="<?php esc_attr_e( 'April', 'godaddy-email-marketing' ) ?>"> <?php esc_html_e( 'April', 'godaddy-email-marketing' ) ?> </option>
				<option value="<?php esc_attr_e( 'May', 'godaddy-email-marketing' ) ?>"> <?php esc_html_e( 'May', 'godaddy-email-marketing' ) ?> </option>
				<option value="<?php esc_attr_e( 'June', 'godaddy-email-marketing' ) ?>"> <?php esc_html_e( 'June', 'godaddy-email-marketing' ) ?> </option>
				<option value="<?php esc_attr_e( 'July', 'godaddy-email-marketing' ) ?>"> <?php esc_html_e( 'July', 'godaddy-email-marketing' ) ?> </option>
				<option value="<?php esc_attr_e( 'August', 'godaddy-email-marketing' ) ?>"> <?php esc_html_e( 'August', 'godaddy-email-marketing' ) ?> </option>
				<option value="<?php esc_attr_e( 'September', 'godaddy-email-marketing' ) ?>"> <?php esc_html_e( 'September', 'godaddy-email-marketing' ) ?> </option>
				<option value="<?php esc_attr_e( 'October', 'godaddy-email-marketing' ) ?>"> <?php esc_html_e( 'October', 'godaddy-email-marketing' ) ?> </option>
				<option value="<?php esc_attr_e( 'November', 'godaddy-email-marketing' ) ?>"> <?php esc_html_e( 'November', 'godaddy-email-marketing' ) ?> </option>
				<option value="<?php esc_attr_e( 'December', 'godaddy-email-marketing' ) ?>"> <?php esc_html_e( 'December', 'godaddy-email-marketing' ) ?> </option>
			</select>
		</span>
		<span class="third">
			<select fingerprint="date" data-id="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>" data-name="<?php echo esc_attr( $args->name ); ?>">
				<option value=""> <?php esc_html_e( 'Day', 'godaddy-email-marketing' ) ?> </option>
				<?php for ( $i = 1; $i < 32; $i++ ) : ?>
					<option value="<?php echo strlen( $i ) < 2 ? '0' . esc_attr( $i ) : esc_attr( $i ); ?>"> <?php echo esc_attr( $i ); ?> </option>
				<?php endfor; ?>
			</select>
		</span>
		<span class="third">
			<select fingerprint="date" data-id="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>" data-name="<?php echo esc_attr( $args->name ); ?>">
				<option value=""> <?php esc_html_e( 'Year', 'godaddy-email-marketing' ) ?> </option>
				<?php for ( $x = $current_year + 5 ; $x > $current_year - 81 ; $x-- ) : ?>
					<option value="<?php echo absint( $x ); ?>"> <?php echo absint( $x ); ?> </option>
				<?php endfor; ?>
			</select>
		</span>

		<input type="hidden" id="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>" name="<?php echo esc_attr( $args->name ); ?>" value="" class="<?php echo $args->required ? 'gem-required' : ''; ?>" data-label="<?php echo esc_attr( $args->display ); ?>" />

		<?php
	}

	/**
	 * Displays the text field.
	 *
	 * @param array $args Settings field arguments.
	 */
	public static function text_field( $args ) {
		$field_classes = array( 'gem-field' );

		// Is this field required?
		if ( $args->required ) {
			$field_classes[] = 'gem-required';
		}

		$field_classes = (array) apply_filters( 'gem_required_field_class', $field_classes, $args );
		?>

		<label for="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>">

			<?php echo esc_html( $args->display ); ?>

			<?php if ( $args->required && apply_filters( 'gem_required_field_indicator', true, $args ) ) : ?>
				<span class="required">*</span>
			<?php endif; ?>

		</label>

		<input type="text" name="<?php echo esc_attr( $args->name ); ?>" id="<?php echo esc_attr( self::get_form_id( $args->name ) ); ?>" class="<?php echo esc_attr( join( ' ', $field_classes ) ); ?>" data-label="<?php echo esc_attr( $args->display ); ?>" />

		<?php
	}
}
