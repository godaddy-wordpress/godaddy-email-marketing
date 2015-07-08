<?php

class GEM_Settings {

	public $slug;
	private $hook;
	private $gem;

	public function __construct() {

		$this->gem = gem();

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
			__( 'GoDaddy Email Marketing Settings', 'gem' ),        // <title> tag
			__( 'GoDaddy Signup Forms', 'gem' ),        // menu label
			'manage_options',                         // required cap to view this page
			$this->slug = 'gem-settings',        // page slug
			array( &$this, 'display_settings_page' )  // callback
		);

		add_action( 'load-' . $this->hook, array( $this, 'page_load' ) );

	}

	public function page_load() {

		// main switch for some various maintenance processes
		if ( isset( $_GET['action'] ) ) {

			$settings = get_option( $this->slug );

			switch ( $_GET['action'] ) {

				case 'debug-reset' :

					if ( ! $this->gem->debug ) {
						return;
					}

					if ( isset( $settings['username'] ) ) {
						delete_transient( 'gem-' . $settings['username'] . '-lists' );
					}

					delete_option( $this->slug );

					break;

				case 'debug-reset-transients' :

					if ( ! $this->gem->debug ) {
						return;
					}

					if ( isset( $settings['username'] ) ) {

						// remove all lists
						delete_transient( 'gem-' . $settings['username'] . '-lists' );

						// mass-removal of all forms
						foreach ( GEM_Dispatcher::get_forms()->signups as $form ) {
							delete_transient( 'gem-form-' . $form->id );
						}

						add_settings_error( $this->slug, 'gem-reset', __( 'All transients were removed.', 'gem' ), 'updated' );
					}

					break;

				case 'refresh' :

					// remove only the lists for the current user
					if ( isset( $settings['username'] ) ) {

						if ( delete_transient( 'gem-' . $settings['username'] . '-lists' ) ) {
							add_settings_error( $this->slug, 'gem-reset', __( 'Forms list was successfully updated.', 'gem' ), 'updated' );
						}

					}

					foreach ( (array) GEM_Dispatcher::get_forms()->signups as $form ) {
						delete_transient( 'gem-form-' . $form->id );
					}

					break;

				case 'edit_form' :

					if ( ! isset( $_GET['form_id'] ) ) {
						return;
					}

					$tokenized_url = add_query_arg( 'redirect', sprintf( '/signups/%d/edit', absint( $_GET['form_id'] ) ), GEM_Dispatcher::user_sign_in() );

					// Not wp_safe_redirect as it's an external site
					wp_redirect( $tokenized_url );
					exit;

					break;

				case 'dismiss' :

					$user_id = get_current_user_id();

					if ( ! $user_id ) {
						return;
					}

					update_user_meta( $user_id, 'gem-dismiss', 'show' );

					break;

			}

		}

		// set up the help tabs
		add_action( 'in_admin_header', array( $this, 'setup_help_tabs' ) );

		// enqueue the CSS for the admin
		wp_enqueue_style( 'gem-admin', plugins_url( 'css/admin.css', GEM_PLUGIN_BASE ) );

	}

	public function setup_help_tabs() {

		$screen = get_current_screen();

		$screen->add_help_tab( array(
			'title' => __( 'Overview', 'gem' ),
			'id'    => 'gem-overview',
			'content' => sprintf( __( '
				<h3>Instructions</h3>
				<p>Once the plugin is activated, you will be able to select and insert any of your GoDaddy Email Marketing webforms right into your site. Setup is easy. Below, simply enter your account email address and API key (found in your GoDaddy Email Marketing account [%1$s] area). Here are the 3 ways you can display a webform on your site:</p>
				<ul>
					<li><strong>Widget:</strong> Go to Appearance &rarr; widgets and find the widget called “GoDaddy Email Marketing Form” and drag it into the widget area of your choice. You can then add a title and select a form!</li>
					<li><strong>Shortcode:</strong> You can add a form to any post or page by adding the shortcode (ex. <code>[gem id=80326]</code>)  in the page/post editor</li>
					<li><strong>Template Tag:</strong> You can add the following template tag into any WordPress file: <code>%2$s</code>. Ex. <code>%3$s</code></li>
				</ul>', 'gem' ), '<a target="_blank" href="https://madmimi.com/user/edit">https://madmimi.com/user/edit</a>', '&lt;?php gem_form( $form_id ); ?&gt;', '&lt;?php gem_form( 91 ); ?&gt;' ),
		) );

		$screen->set_help_sidebar( __( '
			<p><strong>For more information:</strong></p>
			<p><a href="http://madmimi.com" target="_blank">Mad Mimi</a></p>
			<p><a href="http://help.madmimi.com" target="_blank">Mad Mimi Help Docs</a></p>
			<p><a href="http://blog.madmimi.com" target="_blank">Mad Mimi Blog</a></p>
			<p><a href="mailto:support@madmimi.com" target="_blank" class="button">Contact Mad Mimi</a></p>
		', 'gem' ) );

	}

	public function register_settings() {

		global $pagenow;

		// If no options exist, create them.
		if ( ! get_option( $this->slug ) ) {
			update_option( $this->slug, apply_filters( 'gem_default_options', array(
				'username' => '',
				'api-key'  => '',
			) ) );
		}

		register_setting( 'gem-options', $this->slug, array( $this, 'validate' ) );

		// First, we register a section. This is necessary since all future options must belong to a
		add_settings_section(
			'general_settings_section',
			__( 'Account Details', 'gem' ),
			array( 'GEM_Settings_Controls', 'description' ),
			$this->slug
		);

		add_settings_field(
			'username',
			__( 'GoDaddy Email Marketing Username', 'gem' ),
			array( 'GEM_Settings_Controls', 'text' ),
			$this->slug,
			'general_settings_section',
			array(
				'id' => 'username',
				'page' => $this->slug,
				'description' => __( 'Your GoDaddy Email Marketing username (email address)', 'gem' ),
				'label_for' => $this->slug . '-username',
			)
		);

		add_settings_field(
			'api-key',
			__( 'GoDaddy Email Marketing API Key', 'gem' ),
			array( 'GEM_Settings_Controls', 'text' ),
			$this->slug,
			'general_settings_section',
			array(
				'id' => 'api-key',
				'page' => $this->slug,
				'description' => sprintf( '<a target="_blank" href="%s">%s</a>', 'https://support.godaddy.com/help/article/15909/find-api-key', _x( 'Where can I find my API key?', 'settings page', 'gem' )  ),
				'label_for' => $this->slug . '-api-key',
			)
		);

		$user_info = GEM_Dispatcher::get_user_level();

		add_settings_field(
			'display_powered_by',
			'',
			array( 'GEM_Settings_Controls', 'checkbox' ),
			$this->slug,
			'general_settings_section',
			array(
				'id' => 'display_powered_by',
				'page' => $this->slug,
				'label' => __( 'Display "Powered by GoDaddy"?', 'gem' )
			)
		);

		do_action( 'gem_setup_settings_fields' );

	}

	public function display_settings_page() { ?>

		<div class="wrap">

			<?php screen_icon(); ?>

			<h2><?php esc_html_e( 'GoDaddy Email Marketing Settings', 'gem' ); ?></h2>

			<?php if ( ! GEM_Settings_Controls::get_option( 'username' ) ) : ?>

				<div class="gem-identity updated notice">

					<h3><?php echo esc_html_x( 'Enjoy the GoDaddy Email Marketing Experience, first hand.', 'gem header note', 'gem' ); ?></h3>

					<p><?php echo esc_html_x( 'Add your GoDaddy Email Marketing webform to your WordPress site! Easy to set up, the GoDaddy Email Marketing plugin allows your site visitors to subscribe to your email list.', 'header note', 'gem' ); ?></p>
					<p class="description"><?php echo sprintf( esc_html_x( 'Don\'t have a GoDaddy Email Marketing account? Get one in less than 2 minutes! %s', 'header note', 'gem' ), sprintf( '<a target="_blank" href="https://godaddy.com/business/email-marketing" class="button">%s</a>', esc_html_x( 'Sign Up Now', 'header note', 'gem' ) ) ); ?></p>

				</div>

			<?php endif; ?>

			<form method="post" action="options.php">

				<?php settings_fields( 'gem-options' );

				do_settings_sections( $this->slug );

				submit_button( _x( 'Save Settings', 'save settings button', 'gem' ) ); ?>

				<h3><?php esc_html_e( 'Available Forms', 'gem' ); ?></h3>

				<table class="wp-list-table widefat">

					<thead>
						<tr>
							<th><?php esc_html_e( 'Form Name', 'gem' ); ?></th>
							<th><?php esc_html_e( 'Form ID', 'gem' ); ?></th>
							<th><?php esc_html_e( 'Shortcode', 'gem' ); ?></th>
						</tr>
					</thead>

					<tfoot>
						<tr>
							<th><?php esc_html_e( 'Form Name', 'gem' ); ?></th>
							<th><?php esc_html_e( 'Form ID', 'gem' ); ?></th>
							<th><?php esc_html_e( 'Shortcode', 'gem' ); ?></th>
						</tr>
					</tfoot>

					<tbody>

					<?php

					$forms = GEM_Dispatcher::get_forms();

					if ( $forms && ! empty( $forms->signups ) ) :

						foreach( $forms->signups as $form ) :

							$edit_link = add_query_arg( array(
								'action' => 'edit_form',
								'form_id' => $form->id,
							) ); ?>

							<tr>
								<td>

									<?php echo esc_html( $form->name ); ?>

									<div class="row-actions">
										<span class="edit">
											<a target="_blank" href="<?php echo esc_url( $edit_link ); ?>" title="<?php esc_attr_e( 'Opens in a new window', 'gem' ); ?>"><?php esc_html_e( 'Edit form in GoDaddy Email Marketing', 'gem' ); ?></a> |
										</span>
										<span class="view">
											<a target="_blank" href="<?php echo esc_url( $form->url ); ?>"><?php esc_html_e( 'Preview', 'gem' ); ?></a>
										</span>
									</div>
								</td>

								<td><code><?php echo absint( $form->id ); ?></code></td>
								<td><input type="text" class="code" value="[gem id=<?php echo absint( $form->id ); ?>]" onclick="this.select()" readonly /></td>

							</tr>

						<?php endforeach;
					else : ?>

						<tr>
							<td colspan="3"><?php esc_html_e( 'No forms found', 'gem' ); ?></td>
						</tr>

					<?php endif; ?>

					</tbody>
				</table>

				<br />

				<p class="description">
					<?php esc_html_e( 'Not seeing your form?', 'gem' ); ?> <a href="<?php echo esc_url( add_query_arg( 'action', 'refresh' ) ); ?>" class="button"><?php esc_html_e( 'Refresh Forms', 'gem' ); ?></a>
				</p>

				<?php if ( $this->gem->debug ) : ?>

					<h3><?php esc_html_e( 'Debug', 'gem' ); ?></h3>
					<p>
						<a href="<?php echo esc_url( add_query_arg( 'action', 'debug-reset' ) ); ?>" class="button-secondary"><?php esc_html_e( 'Erase All Data', 'gem' ); ?></a>
						<a href="<?php echo esc_url( add_query_arg( 'action', 'debug-reset-transients' ) ); ?>" class="button-secondary"><?php esc_html_e( 'Erase Transients', 'gem' ); ?></a>
					</p>

				<?php endif; ?>

			</form>

		</div>

	<?php }

	public function validate( $input ) {

		// validate creds against the API
		if ( ! ( empty( $input['username'] ) || empty( $input['api-key'] ) ) ) {

			$data = GEM_Dispatcher::fetch_forms( $input['username'], $input['api-key'] );

			if ( ! $data ) {

				// credentials are incorrect
				add_settings_error( $this->slug, 'invalid-creds', __( 'The credentials are incorrect! Please verify that you have entered them correctly.', 'gem' ) );

				return $input; // bail

			} elseif ( ! empty( $data->total ) ) {

				// test the returned data, and let the user know she's alright!
				add_settings_error( $this->slug, 'valid-creds', __( 'Connection with GoDaddy Email Marketing has been established! You\'re all set!', 'gem' ), 'updated' );

			}

		} else {

			// empty
			add_settings_error( $this->slug, 'invalid-creds', __( 'Please fill in the username and the API key first.', 'gem' ) );

		}

		return $input;

	}
}


final class GEM_Settings_Controls {

	public static function description() { ?>

		<p><?php esc_html_e( 'Please enter your GoDaddy Email Marketing username and API Key in order to be able to create forms.', 'gem' ); ?></p>

	<?php }

	public static function select( $args ) {

		if ( empty( $args['options'] ) || empty( $args['id'] ) || empty( $args['page'] ) ) {
			return;
		} ?>

		<select id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( sprintf( '%s[%s]', $args['page'], $args['id'] ) ); ?>">

			<?php foreach ( $args['options'] as $name => $label ) : ?>

				<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $name, (string) self::get_option( $args['id'] ) ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>

			<?php endforeach; ?>

		</select>

	<?php }

	public static function text( $args ) {

		if ( empty( $args['id'] ) || empty( $args['page'] ) ) {
			return;
		} ?>

		<input type="text" name="<?php echo esc_attr( sprintf( '%s[%s]', $args['page'], $args['id'] ) ); ?>"
			id="<?php echo esc_attr( sprintf( '%s-%s', $args['page'], $args['id'] ) ) ?>"
			value="<?php echo esc_attr( self::get_option( $args['id'] ) ); ?>" class="regular-text code" />

		<?php self::show_description( $args );

	}

	public static function checkbox( $args ) {

		if ( empty( $args['id'] ) || empty( $args['page'] ) ) {
			return;
		}

		$name = sprintf( '%s[%s]', $args['page'], $args['id'] );
		$label = isset( $args['label'] ) ? $args['label'] : ''; ?>

		<label for="<?php echo esc_attr( $name ); ?>">
			<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( self::get_option( $args['id'] ) ); ?> />
			<?php echo esc_html( $label ); ?>
		</label>

		<?php self::show_description( $args );

	}

	public function show_description( $field_args ) {

		if ( isset( $field_args['description'] ) ) : ?>

			<p class="description"><?php echo wp_kses_post( $field_args['description'] ); ?></p>

		<?php endif;

	}

	public static function get_option( $key = '' ) {

		$settings = get_option( 'gem-settings' );

		return ( ! empty( $settings[ $key ] ) ) ? $settings[ $key ] : false;

	}
}
