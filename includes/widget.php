<?php

/**
 * Mad Mimi widget
 *
 * @since 2.8.0
 */
class Mad_Mimi_Form_Widget extends WP_Widget {

	function __construct() {

		parent::__construct( 'mimi-form', __( 'Mad Mimi Form' ), array(
			'classname'   => 'mimi-form',
			'description' => _x( 'Embed any Mad Mimi webform in your sidebar', 'widget description', 'mimi' )
		) );

		foreach ( array( 'wpautop', 'wptexturize', 'convert_chars' ) as $filter ) {
			add_filter( 'mimi_widget_text', $filter );
		}

	}

	function widget( $args, $instance ) {

		extract( $args );

		$title   = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Mad Mimi Form', 'mimi' ) : $instance['title'], $instance, $this->id_base );
		$text    = empty( $instance['text'] ) ? '' : $instance['text'];
		$form_id = empty( $instance['form'] ) ? false : $instance['form'];

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		if ( $text ) {
			echo apply_filters( 'mimi_widget_text', $text );
		}

		Mad_Mimi_Form_Renderer::process( $form_id, true );

		echo $after_widget;

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

		$title         = esc_attr( $instance['title'] );
		$selected_form = absint( $instance['form'] );
		$text          = esc_textarea( $instance['text'] );

		$forms = Mad_Mimi_Dispatcher::get_forms(); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php _e( 'Additional Text:' ); ?></label>
			<textarea class="widefat" rows="3" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>"><?php echo $text; ?></textarea>
		</p>

		<p>

			<?php if ( ! empty( $forms->signups ) ) : ?>

				<label for="<?php echo $this->get_field_id( 'form' ); ?>"><?php _e( 'Form:' ); ?></label>
				<select name="<?php echo $this->get_field_name( 'form' ); ?>" id="<?php echo $this->get_field_id( 'form' ); ?>" class="widefat">
					<?php foreach ( $forms->signups as $f ) : ?>
						<option value="<?php echo esc_attr( $f->id ); ?>" <?php selected( $selected_form, $f->id ); ?>><?php echo esc_html( $f->name ); ?></option>
					<?php endforeach; ?>
				</select>

			<?php else : ?>

			<span><?php printf( __( 'Please set up your Mad Mimi account in the <a href="%s">settings page</a>.', 'mimi' ), admin_url( 'options-general.php?page=mad-mimi-settings' ) ); ?>

			<?php endif; ?>

		</p>

	<?php }
}
