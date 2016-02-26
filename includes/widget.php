<?php

/**
 * GoDaddy Email Marketing widget
 *
 * @since 2.8.0
 */
class GEM_Form_Widget extends WP_Widget {

	function __construct() {

		parent::__construct( 'gem-form', __( 'GoDaddy Email Marketing Form', 'gem' ), array(
			'classname'   => 'gem-form',
			'description' => _x( 'Embed any GoDaddy Email Marketing webform in your sidebar.', 'widget description', 'gem' )
		) );

		foreach ( array( 'wpautop', 'wptexturize', 'convert_chars' ) as $filter ) {
			add_filter( 'gem_widget_text', $filter );
		}

	}

	function widget( $args, $instance ) {

		extract( $args );

		$title   = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'GoDaddy Email Marketing Form', 'gem' ) : $instance['title'], $instance, $this->id_base );
		$text    = empty( $instance['text'] ) ? '' : $instance['text'];
		$form_id = empty( $instance['form'] ) ? false : $instance['form'];

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		if ( $text ) {
			echo wp_kses_post( apply_filters( 'gem_widget_text', $text ) );
		}

		GEM_Form_Renderer::process( $form_id, true );

		echo $args['after_widget'];

	}

	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['text']  = $new_instance['text'];
		$instance['form']  = absint( $new_instance['form'] );

		return $instance;

	}

	function form( $instance ) {

		// set defaults
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
			'text'  => '',
			'form'  => 0,
		) );

		$forms = GEM_Dispatcher::get_forms(); ?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'gem' ); ?></label>
			<br/>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ) ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php esc_html_e( 'Additional Text:', 'gem' ); ?></label>
			<br/>
			<textarea class="widefat" rows="3" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>"><?php echo esc_textarea( $instance['text'] ); ?></textarea>
		</p>

		<p>

			<?php if ( ! empty( $forms->signups ) ) : ?>

				<label for="<?php echo esc_attr( $this->get_field_id( 'form' ) ); ?>"><?php esc_html_e( 'Form:', 'gem' ); ?></label>
				<br/>
				<select name="<?php echo esc_attr( $this->get_field_name( 'form' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'form' ) ); ?>" class="widefat">

					<?php foreach ( $forms->signups as $f ) : ?>
						<option value="<?php echo esc_attr( $f->id ); ?>" <?php selected( $instance['form'], $f->id ); ?>><?php echo esc_html( $f->name ); ?></option>
					<?php endforeach; ?>

				</select>

			<?php else : ?>

			<span><?php echo wp_kses( sprintf( __( 'Please set up your GoDaddy Email Marketing account in the %ssettings page%s.', 'gem' ), esc_url_raw( admin_url( 'options-general.php?page=gem-settings' ) ) ), array( 'a' => array( 'href' => array() ) ) ); ?>

			<?php endif; ?>

		</p>

	<?php }

}
