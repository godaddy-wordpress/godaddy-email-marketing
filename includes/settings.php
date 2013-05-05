<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AAL_Settings {

	public $slug;
	private $hook;
	private $mimi;
	
	public function __construct() {
		$this->mimi = madmimi(); // main Mad Mimi instance

		add_action( 'admin_menu', array( $this, 'action_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register the settings page
	 *
	 * @since 1.0
	 */
	public function action_admin_menu() {
		$this->hook = add_options_page(
			__( 'Mad Mimi Settings', 'aryo-aal' ), 	// <title> tag
			__( 'Mad Mimi Settings', 'aryo-aal' ), 			// menu label
			'manage_options', 								// required cap to view this page
			$this->slug = 'mad-mimi-settings', 			// page slug
			array( &$this, 'display_settings_page' )			// callback
		);

		add_action( "load-$this->hook", array( $this, 'page_load' ) );
	}

	public function page_load() {
		if ( isset( $_GET['debug'] ) && $this->mimi->debug ) {
			$settings = get_option( $this->slug );

			switch ( $_GET['debug'] ) {
				case 'reset':
					if ( isset( $settings['username'] ) ) {
						delete_transient( "mimi-{$settings['username']}-lists" );
					}
					delete_option( $this->slug );

					break;
				case 'reset-transients':
					if ( isset( $settings['username'] ) ) {
						// remove all lists
						delete_transient( "mimi-{$settings['username']}-lists" );
						
						// mass-removal of all forms
						foreach ( Mad_Mimi_Dispatcher::get_forms()->signups as $form ) {
							delete_transient( "mimi-form-{$form->id}" );
						}

						add_settings_error( $this->slug, 'mimi-reset', __( 'All transients were removed.', 'mimi' ), 'updated' );
					}
					break;
			}
		}
	}

	public function register_settings() {
		// If no options exist, create them.
		if ( ! get_option( $this->slug ) ) {
			update_option( $this->slug, apply_filters( 'mimi_default_options', array(
				'username' => '',
				'api-key' => '',
			) ) );
		}

		register_setting( 'madmimi-options', $this->slug, array( $this, 'validate' ) );

		// First, we register a section. This is necessary since all future options must belong to a 
		add_settings_section(
			'general_settings_section',
			__( 'Account Details', 'aryo-aal' ),
			array( 'AAL_Settings_Controls', 'description' ),
			$this->slug
		);

		add_settings_field(
			'username',
			__( 'Mad Mimi Username', 'aryo-aal' ),
			array( 'AAL_Settings_Controls', 'text' ),
			$this->slug,
			'general_settings_section',
			array(
				'id' => 'username',
				'page' => $this->slug,
				'description' => __( 'Your Mad Mimi username (email address)', 'mimi' ),
			)
		);

		add_settings_field(
			'api-key',
			__( 'Mad Mimi API Key', 'aryo-aal' ),
			array( 'AAL_Settings_Controls', 'text' ),
			$this->slug,
			'general_settings_section',
			array(
				'id' => 'api-key',
				'page' => $this->slug,
				'description' => __( 'You can find your API key at <a href="https://madmimi.com/user/edit">https://madmimi.com/user/edit</a>', 'mimi' ),
			)
		);
		
		add_settings_field(
			'display_powered_by',
			__( 'Display "Powered by Mad Mimi"?', 'aryo-aal' ),
			array( 'AAL_Settings_Controls', 'select' ),
			$this->slug,
			'general_settings_section',
			array(
				'id' => 'display_powered_by',
				'page' => $this->slug,
				'options' => array(
					'forever' => __( 'Yes', 'aryo-aal' ),
					'365' => __( 'No', 'aryo-aal' ),
				),
			)
		);
	}

	public function display_settings_page() {
		?>
		<!-- Create a header in the default WordPress 'wrap' container -->
		<div class="wrap">
		
			<?php screen_icon(); ?>
			<h2><?php _e( 'Mad Mimi Settings', 'aryo-aal' ); ?></h2>
			
			<form method="post" action="options.php">
				<?php
				settings_fields( 'madmimi-options' );
				do_settings_sections( $this->slug );
				submit_button();
				?>

				<h3><?php _e( 'Available Forms', 'mimi' ); ?></h3>

				<table class="wp-list-table widefat fixed posts" style="width: 60%;">
					<thead>
						<tr>
							<th><?php _e( 'Form Name', 'mimi' ); ?></th>
							<th><?php _e( 'Form ID', 'mimi' ); ?></th>
							<th><?php _e( 'Shortcode', 'mimi' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th><?php _e( 'Form Name', 'mimi' ); ?></th>
							<th><?php _e( 'Form ID', 'mimi' ); ?></th>
							<th><?php _e( 'Shortcode', 'mimi' ); ?></th>
						</tr>
					</tfoot>
					<tbody>
					<?php

					$forms = Mad_Mimi_Dispatcher::get_forms();

					if ( ! empty( $forms->signups ) ) :
				 
						foreach( $forms->signups as $form ) : ?>

							<tr>
								<td><?php echo esc_html( $form->name ); ?></td>
								<td><?php echo absint( $form->id ); ?></td>
								<td><input type="text" class="code" value="[madmimi id=<?php echo absint( $form->id ); ?>]" onclick="this.select()" readonly /></td>
							</tr>

							<?php
						endforeach;
					else : ?>
						<tr>
							<td colspan="3"><?php _e( 'No forms found', 'mimi' ); ?></td>
						</tr>
						<?php 
					endif; 
					?>	
					</tbody>
				</table>

				<?php if ( $this->mimi->debug ) : ?>

				<h3><?php _e( 'Debugging', 'mimi' ); ?></h3>
				<p>
					<a href="<?php echo add_query_arg( 'debug', 'reset' ); ?>" class="button-secondary"><?php _e( 'Erase All Data', 'mimi' ); ?></a>
					<a href="<?php echo add_query_arg( 'debug', 'reset-transients' ); ?>" class="button-secondary"><?php _e( 'Erase Transients', 'mimi' ); ?></a>
				</p>

				<?php endif; ?>

			</form>
			
		</div><!-- /.wrap -->
		<?php
	}

	public function validate( $input ) {
		// validate creds against the API

		if ( ! ( empty( $input['username'] ) || empty( $input['api-key'] ) ) ) {
			$data = Mad_Mimi_Dispatcher::fetch_forms( $input['username'], $input['api-key'] );

			if ( ! $data ) {
				// credentials are incorrect
				add_settings_error( $this->slug, 'invalid-creds', __( 'The credentials are incorrect! Please verify that you have entered them correctly.', 'mimi' ) );
				
				return $input; // bail

			} elseif ( ! empty( $data->total ) ) {
				// test the returned data, and let the user know she's alright!
				add_settings_error( $this->slug, 'valid-creds', __( 'Credentials are correct! You\'re all set!', 'mimi' ), 'updated' );
			}

		} else {
			// empty
			add_settings_error( $this->slug, 'invalid-creds', __( 'Please fill in the username and the API key first.', 'mimi' ) );
		}

		return $input;
	}	
}


final class AAL_Settings_Controls {

	public static function description() {
		?>
		<p><?php _e( 'Please enter your Mad Mimi username and API Key in order to be able to create forms.', 'aryo-aal' ); ?></p>
		<?php
	}

	public static function select( $args ) {
		extract( $args, EXTR_SKIP );

		if ( empty( $options ) || empty( $id ) || empty( $page ) )
			return;
		
		?>
		<select id="<?php echo esc_attr( $id ); ?>" name="<?php printf( '%s[%s]', esc_attr( $page ), esc_attr( $id ) ); ?>">
			<?php foreach ( $options as $name => $label ) : ?>
			<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $name, (string) self::get_option( $id ) ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public static function text( $args ) {
		extract( $args, EXTR_SKIP );

		if ( empty( $id ) || empty( $page ) )
			return;

		$name = esc_attr( sprintf( '%s[%s]', esc_attr( $page ), esc_attr( $id ) ) );
		$value = esc_attr( self::get_option( $id ) );

		?>
		<input type="text" name="<?php echo $name; ?>" value="<?php echo $value; ?>" class="regular-text code" />

		<?php if ( isset( $description ) ) : ?>
		<p class="description"><?php echo $description; ?></p>
		<?php endif; ?>
		<?php
	}

	public static function get_option( $key = '' ) {
		$settings = get_option( 'mad-mimi-settings' );
		return ( ! empty( $settings[ $key ] ) ) ? $settings[ $key ] : false;
	}
}