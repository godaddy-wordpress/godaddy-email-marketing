<?php
/**
 * Widget class
 *
 * @package GEM
 */

/**
 * GoDaddy Email Marketing widget.
 *
 * @since 1.0
 */
class GEM_Form_Widget extends WP_Widget {

	/**
	 * Sets up a new GoDaddy Email Marketing widget instance.
	 */
	public function __construct() {
		parent::__construct(
			'gem-form',
			__( 'GoDaddy Email Marketing Form', 'godaddy-email-marketing' ),
			array(
				'classname'   => 'gem-form',
				'description' => _x( 'Embed GoDaddy Email Marketing signup forms in the sidebar.', 'widget description', 'godaddy-email-marketing' ),
			)
		);

		foreach ( array( 'wpautop', 'wptexturize', 'convert_chars' ) as $filter ) {
			add_filter( 'gem_widget_text', $filter );
		}
	}

	/**
	 * Outputs the content for the current widget instance.
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Custom Menu widget instance.
	 */
	public function widget( $args, $instance ) {

		// Set the initial form ID value if one exists.
		if ( empty( $instance['form'] ) ) {
			$forms = GEM_Dispatcher::get_forms();
			$valid_creds = (bool) get_option( 'gem-valid-creds' );

			// Create a default form.
			if ( empty( $forms->signups ) && $valid_creds ) { // @codeCoverageIgnoreStart
				GEM_Dispatcher::add_default_form();
				$forms = GEM_Dispatcher::fetch_forms();
			}
			// @codeCoverageIgnoreEnd

			if ( ! empty( $forms->signups ) ) {
				$instance['form'] = $forms->signups[0]->id;

				// Update widget instance with new form ID
				$settings = $this->get_settings();
				$settings[ $this->number ] = $instance;
				$this->save_settings( $settings );
			}
		}

		$title   = apply_filters( 'widget_title', ! empty( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );
		$text    = empty( $instance['text'] ) ? '' : $instance['text'];
		$form_id = empty( $instance['form'] ) ? false : $instance['form'];

		echo $args['before_widget']; // xss ok

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // xss ok
		}

		if ( $text ) {
			echo wp_kses_post( apply_filters( 'gem_widget_text', $text ) );
		}

		$renderer = new GEM_Form_Renderer();
		$renderer->process( $form_id, true );

		echo $args['after_widget']; // xss ok
	}

	/**
	 * Handles updating settings for the current widget instance.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['text']  = $new_instance['text'];
		$instance['form']  = absint( $new_instance['form'] );

		return $instance;
	}

	/**
	 * Outputs the widget settings form.
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {

		// Set defaults.
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
			'text'  => '',
			'form'  => 0,
		) );

		$forms = GEM_Dispatcher::get_forms();
		$valid_creds = (bool) get_option( 'gem-valid-creds' );

		// Create a default form.
		if ( empty( $forms->signups ) && $valid_creds ) { // @codeCoverageIgnoreStart
			GEM_Dispatcher::add_default_form();
			$forms = GEM_Dispatcher::fetch_forms();
		}
		// @codeCoverageIgnoreEnd
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'godaddy-email-marketing' ); ?></label>
			<br/>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ) ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php esc_html_e( 'Additional Text:', 'godaddy-email-marketing' ); ?></label>
			<br/>
			<textarea class="widefat" rows="3" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>"><?php echo esc_textarea( $instance['text'] ); ?></textarea>
		</p>

		<p>

			<?php if ( ! empty( $forms->signups ) ) : ?>

				<label for="<?php echo esc_attr( $this->get_field_id( 'form' ) ); ?>"><?php esc_html_e( 'Form:', 'godaddy-email-marketing' ); ?></label>
				<br/>
				<select name="<?php echo esc_attr( $this->get_field_name( 'form' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'form' ) ); ?>" class="widefat" value="<?php echo esc_attr( $instance['form'] ) ?>">

					<?php foreach ( $forms->signups as $form ) : ?>
						<option value="<?php echo esc_attr( $form->id ); ?>" <?php selected( $instance['form'], $form->id ); ?>><?php echo esc_html( $form->name ); ?></option>
					<?php endforeach; ?>

				</select>

			<?php else : ?>

			<span><?php printf(
				esc_html_x( 'Please set up your GoDaddy Email Marketing account in the %s.', 'link to settings page', 'godaddy-email-marketing' ),
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( admin_url( 'options-general.php?page=gem-settings' ) ),
					esc_html__( 'settings page', 'godaddy-email-marketing' )
				)
			); ?></span>

			<?php endif; ?>

		</p>
		<?php
	}
}
