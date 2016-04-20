<?php
/**
 * Settings classes
 *
 * @package GEM
 */

/**
 * GoDaddy Email Marketing settings.
 *
 * @since 1.0
 */
class GEM_Settings {

	/**
	 * The page slug.
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * The settings page's hook_suffix.
	 *
	 * @var string
	 */
	public $hook;

	/**
	 * GEM_Official instance.
	 *
	 * @var GEM_Official
	 */
	private $gem;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->gem = gem();

		add_action( 'admin_menu', array( $this, 'action_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register the settings page.
	 *
	 * @action admin_menu
	 */
	public function action_admin_menu() {
		$this->hook = add_options_page(
			__( 'GoDaddy Email Marketing Settings', 'godaddy-email-marketing' ),
			__( 'GoDaddy Signup Forms', 'godaddy-email-marketing' ),
			'manage_options',
			$this->slug = 'gem-settings',
			array( $this, 'display_settings_page' )
		);

		// Various maintenance processes
		add_action( 'load-' . $this->hook, array( $this, 'page_load' ) );

		// Enqueue admin CSS.
		add_action( 'admin_print_styles-' . $this->hook, array( $this, 'admin_enqueue_style' ) );

		// Enqueue admin JavaScript.
		add_action( 'admin_print_scripts-' . $this->hook, array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue the CSS for the admin.
	 */
	public function admin_enqueue_style() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		if ( is_rtl() ) {
			$suffix = '-rtl' . $suffix;
		}

		wp_enqueue_style( 'gem-admin', plugins_url( "css/admin{$suffix}.css", GEM_PLUGIN_BASE ) );
	}

	/**
	 * Enqueue the JavaScript for the admin.
	 */
	public function admin_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'gem-admin', plugins_url( "js/admin{$suffix}.js", GEM_PLUGIN_BASE ), array( 'jquery' ), GEM_VERSION, true );
	}

	/**
	 * Executes during page load.
	 *
	 * Listens for several user initiated actions, adds a help tab, and enqueues resources.
	 */
	public function page_load() {

		// Main switch for various maintenance processes.
		if ( isset( $_GET['action'] ) ) {
			$settings = get_option( $this->slug );

			switch ( $_GET['action'] ) {
				case 'debug-reset' :
					if ( ! $this->gem->debug ) {
						return;
					}

					if ( isset( $settings['username'] ) ) {

						// Mass-removal of all forms.
						$forms = GEM_Dispatcher::get_forms();

						if ( isset( $forms->signups ) ) {
							foreach ( (array) $forms->signups as $form ) {
								delete_transient( 'gem-form-' . $form->id );
							}
						}

						// Remove all lists.
						delete_transient( 'gem-' . $settings['username'] . '-lists' );
					}

					delete_option( $this->slug );

					set_transient( 'debug-reset', true, 30 );
					// @codeCoverageIgnoreStart
					if ( 'cli' !== php_sapi_name() ) {
						wp_safe_redirect( remove_query_arg( array( 'action', 'settings-updated' ), add_query_arg( 'tab', 'settings' ) ) );
						exit;
					}
					// @codeCoverageIgnoreEnd

					break;
				case 'debug-reset-transients' :
					if ( ! $this->gem->debug ) {
						return;
					}

					if ( isset( $settings['username'] ) ) {

						// Mass-removal of all forms.
						$forms = GEM_Dispatcher::get_forms();

						if ( isset( $forms->signups ) ) {
							foreach ( (array) $forms->signups as $form ) {
								delete_transient( 'gem-form-' . $form->id );
							}
						}

						// Remove all lists.
						delete_transient( 'gem-' . $settings['username'] . '-lists' );

						set_transient( 'debug-reset-transients', true, 30 );
						// @codeCoverageIgnoreStart
						if ( 'cli' !== php_sapi_name() ) {
							wp_safe_redirect( remove_query_arg( array( 'action', 'settings-updated' ), add_query_arg( 'tab', 'settings' ) ) );
							exit;
						}
						// @codeCoverageIgnoreEnd
					}

					break;
				case 'refresh' :

					if ( isset( $settings['username'] ) ) {

						// Mass-removal of all forms.
						$forms = GEM_Dispatcher::get_forms();

						if ( isset( $forms->signups ) ) {
							foreach ( (array) $forms->signups as $form ) {
								delete_transient( 'gem-form-' . $form->id );
							}
						}

						// Remove all lists.
						delete_transient( 'gem-' . $settings['username'] . '-lists' );

						set_transient( 'gem-refresh', true, 30 );
						// @codeCoverageIgnoreStart
						if ( 'cli' !== php_sapi_name() ) {
							wp_safe_redirect( remove_query_arg( array( 'action', 'settings-updated' ) ) );
							exit;
						}
						// @codeCoverageIgnoreEnd
					}

					break;
				case 'edit_form' :
					if ( ! isset( $_GET['form_id'] ) ) {
						return;
					}

					// @codeCoverageIgnoreStart
					$tokenized_url = add_query_arg( 'redirect', sprintf( '/signups/%d/edit', absint( $_GET['form_id'] ) ), GEM_Dispatcher::user_sign_in() );

					// Not wp_safe_redirect as it's an external site.
					if ( 'cli' !== php_sapi_name() ) {
						wp_redirect( $tokenized_url );
						exit;
					}

					break;
					// @codeCoverageIgnoreEnd
				case 'dismiss' :
					$user_id = get_current_user_id();

					if ( ! $user_id ) {
						return;
					}

					update_user_meta( $user_id, 'gem-dismiss', 'show' );

					break;
			}
		} else if ( isset( $_GET['settings-updated'] ) && 'cli' !== php_sapi_name() ) { // @codeCoverageIgnoreStart
			wp_safe_redirect( remove_query_arg( array( 'action', 'settings-updated' ), add_query_arg( 'tab', 'settings' ) ) );
			exit;
		}
		// @codeCoverageIgnoreEnd

		// Add one time settings notices.
		if ( get_transient( 'debug-reset' ) ) {

			// All data reset.
			$this->add_settings_error( $this->slug, 'debug-reset', __( 'All data has been removed.', 'godaddy-email-marketing' ), 'updated' );
		} elseif ( get_transient( 'debug-reset-transients' ) ) {

			// Transients reset.
			$this->add_settings_error( $this->slug, 'debug-reset-transients', __( 'All transients were removed.', 'godaddy-email-marketing' ), 'updated' );
		} elseif ( get_transient( 'gem-refresh' ) ) {

			// Form refresh.
			$this->add_settings_error( $this->slug, 'gem-refresh', __( 'Forms list was successfully updated.', 'godaddy-email-marketing' ), 'updated' );
		} elseif ( get_transient( 'gem-invalid-creds' ) ) {

			// Invalid credentials.
			$this->add_settings_error( $this->slug, 'gem-invalid-creds', __( 'The credentials are incorrect! Please verify that you have entered them correctly.', 'godaddy-email-marketing' ) );
		} elseif ( get_transient( 'gem-valid-creds' ) ) {

			// Valid credentials.
			$this->add_settings_error( $this->slug, 'gem-valid-creds', __( 'Connection with GoDaddy Email Marketing has been established! You\'re all set!', 'godaddy-email-marketing' ), 'updated' );

		} elseif ( get_transient( 'gem-settings-updated' ) ) {

			// Settings updated.
			$this->add_settings_error( $this->slug, 'gem-settings-updated', __( 'Settings have been updated.', 'godaddy-email-marketing' ), 'updated' );
		} elseif ( get_transient( 'gem-empty-creds' ) ) {

			// Empty credentials.
			$this->add_settings_error( $this->slug, 'gem-empty-creds', __( 'Please fill in the username and the API key first.', 'godaddy-email-marketing' ) );
		}

		// Set up the help tabs.
		add_action( 'in_admin_header', array( $this, 'setup_help_tabs' ) );
	}

	/**
	 * Register a settings error to be displayed to the user
	 *
	 * @param string $setting Slug title of the setting to which this error applies
	 * @param string $code    Slug-name to identify the error. Used as part of 'id' attribute in HTML output.
	 * @param string $message The formatted message text to display to the user (will be shown inside styled
	 *                        `<div>` and `<p>` tags).
	 * @param string $type    Optional. Message type, controls HTML class. Accepts 'error' or 'updated'.
	 *                        Default 'error'.
	 */
	public function add_settings_error( $setting, $code, $message, $type = 'error' ) {
		add_settings_error( $setting, $code, $message, $type );
		delete_transient( $code );
	}

	/**
	 * Registers the help tab.
	 *
	 * @action in_admin_header
	 */
	public function setup_help_tabs() {
		$screen = get_current_screen();

		// @todo Remove HTML from the translation strings.
		$screen->add_help_tab( array(
			'title'   => __( 'Overview', 'godaddy-email-marketing' ),
			'id'      => 'gem-overview',
			'content' => sprintf(
				'<h3>%s</h3><p>%s</p><ul><li>%s</li><li>%s</li><li>%s</li></ul>',
				esc_html__( 'Instructions', 'godaddy-email-marketing' ),
				sprintf(
					esc_html__( 'Once the plugin is activated, you will be able to select and insert any of your GoDaddy Email Marketing webforms right into your site. Setup is easy. Below, simply enter your account email address and API key (found in your GoDaddy Email Marketing account [%s] area). Here are the 3 ways you can display a webform on your site:', 'godaddy-email-marketing' ),
					'<a href="https://gem.godaddy.com/user/edit" target="_blank">https://gem.godaddy.com/user/edit</a>'
				),
				__( '<strong>Widget:</strong> Go to Appearance &rarr; widgets and find the widget called “GoDaddy Email Marketing Form” and drag it into the widget area of your choice. You can then add a title and select a form!', 'godaddy-email-marketing' ),
				__( '<strong>Shortcode:</strong> You can add a form to any post or page by adding the shortcode (ex. <code>[gem id=80326]</code>) in the page/post editor.', 'godaddy-email-marketing' ),
				sprintf(
					__( '<strong>Template Tag:</strong> You can add the following template tag into any WordPress file: <code>%s</code>. Ex. <code>%s</code>', 'godaddy-email-marketing' ),
					'&lt;?php gem_form( $form_id ); ?&gt;',
					'&lt;?php gem_form( 91 ); ?&gt;'
				)
			),
		) );

		$screen->set_help_sidebar(
			sprintf(
				'<p><strong>%s</strong></p><p><a href="https://godaddy.com" target="_blank">%s</a></p><p><a href="https://support.godaddy.com/" target="_blank">%s</a></p><p><a href="https://support.godaddy.com/" target="_blank" class="button">%s</a></p>',
				esc_html__( 'For more information:', 'godaddy-email-marketing' ),
				esc_html__( 'GoDaddy', 'godaddy-email-marketing' ),
				esc_html__( 'GoDaddy Help', 'godaddy-email-marketing' ),
				esc_html__( 'Contact GoDaddy', 'godaddy-email-marketing' )
			)
		);
	}

	/**
	 * Registers the settings.
	 *
	 * @action admin_init
	 */
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

		// First, we register a section. This is necessary since all future options must belong to one.
		add_settings_section(
			'general_settings_section',
			__( 'Account Details', 'godaddy-email-marketing' ),
			array( 'GEM_Settings_Controls', 'description' ),
			$this->slug
		);

		add_settings_field(
			'username',
			__( 'Username', 'godaddy-email-marketing' ),
			array( 'GEM_Settings_Controls', 'text' ),
			$this->slug,
			'general_settings_section',
			array(
				'id' => 'username',
				'page' => $this->slug,
				'description' => __( 'Your GoDaddy Email Marketing username (email address)', 'godaddy-email-marketing' ),
				'label_for' => $this->slug . '-username',
			)
		);

		add_settings_field(
			'api-key',
			__( 'API Key', 'godaddy-email-marketing' ),
			array( 'GEM_Settings_Controls', 'text' ),
			$this->slug,
			'general_settings_section',
			array(
				'id' => 'api-key',
				'page' => $this->slug,
				'description' => sprintf( '<a target="_blank" href="%s">%s</a>', 'https://www.godaddy.com/help/find-api-key-15909', _x( 'Where can I find my API key?', 'settings page', 'godaddy-email-marketing' ) ),
				'label_for' => $this->slug . '-api-key',
			)
		);

		$user_info = GEM_Dispatcher::get_user_level();

		add_settings_field(
			'display_powered_by',
			__( 'Footer Link', 'godaddy-email-marketing' ),
			array( 'GEM_Settings_Controls', 'checkbox' ),
			$this->slug,
			'general_settings_section',
			array(
				'id' => 'display_powered_by',
				'page' => $this->slug,
				'label' => __( 'Display "Powered by GoDaddy"?', 'godaddy-email-marketing' ),
			)
		);

		// Add the debugging section.
		add_settings_section(
			'debugging_section',
			__( 'Debugging', 'godaddy-email-marketing' ),
			array( 'GEM_Settings_Controls', 'debugging' ),
			$this->slug
		);

		add_settings_field(
			'debug',
			__( 'Debug Mode', 'godaddy-email-marketing' ),
			array( 'GEM_Settings_Controls', 'checkbox' ),
			$this->slug,
			'debugging_section',
			array(
				'id' => 'debug',
				'page' => $this->slug,
				'label' => __( 'Activated', 'godaddy-email-marketing' ),
			)
		);

		if ( $this->gem->debug ) {
			add_settings_field(
				'erase_transients',
				__( 'Cache Reset', 'godaddy-email-marketing' ),
				array( 'GEM_Settings_Controls', 'button' ),
				$this->slug,
				'debugging_section',
				array(
					'url' => add_query_arg( 'action', 'debug-reset-transients' ),
					'label' => __( 'Erase Transients', 'godaddy-email-marketing' ),
					'description' => __( 'Purges only the cached transients associated with this plugin, and should be attempted before a hard reset.', 'godaddy-email-marketing' ),
				)
			);

			add_settings_field(
				'erase_all_data',
				__( 'Hard Reset', 'godaddy-email-marketing' ),
				array( 'GEM_Settings_Controls', 'button' ),
				$this->slug,
				'debugging_section',
				array(
					'url' => add_query_arg( 'action', 'debug-reset' ),
					'label' => __( 'Erase All Data', 'godaddy-email-marketing' ),
					'description' => __( 'Purges all saved data associated with this plugin.', 'godaddy-email-marketing' ),
				)
			);
		}

		do_action( 'gem_setup_settings_fields' );
	}

	/**
	 * Prints out all settings sections added to a particular settings page in columns.
	 *
	 * @global array $wp_settings_sections Storage array of all settings sections added to admin pages
	 * @global array $wp_settings_fields Storage array of settings fields and info about their pages/sections
	 *
	 * @param string $page The slug name of the page whos settings sections you want to output.
	 * @param int    $columns The number of columns in each row.
	 */
	public function do_settings_sections( $page, $columns = 2 ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}

		$index = 0;

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			if ( ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				continue;
			}
			$index++;

			// Set the column class.
			$class = 'col col-' . $index;
			if ( $columns === $index ) {
				$class .= ' last';
				$index = 0;
			}
			?>
			<div class="<?php echo esc_attr( $class ); ?>">
				<?php
				if ( ! empty( $section['title'] ) ) {
					echo '<h3>' . esc_html( $section['title'] ) . '</h3>' . "\n";
				}
				if ( ! empty( $section['callback'] ) ) {
					call_user_func( $section['callback'], $section );
				}
				if ( isset( $wp_settings_fields ) ) { ?>
				<table class="form-table">
					<?php do_settings_fields( $page, $section['id'] ); ?>
				</table>
				<?php } ?>
			</div>
			<?php
		}
	}

	/**
	 * Displays the settings page.
	 *
	 * @todo Move this into a view file and include.
	 */
	public function display_settings_page() {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		$forms = GEM_Dispatcher::get_forms();
		$valid_creds = (bool) get_option( 'gem-valid-creds' );
		if ( ! empty( $forms->signups ) && empty( $tab ) ) {
			$tab = 'forms';
		}
		?>
		<div class="wrap about-wrap">
			<div class="intro">
				<h1>
					<?php esc_html_e( 'GoDaddy Email Marketing Signup Forms', 'godaddy-email-marketing' ); ?>
					<sup><?php echo esc_html( GEM_VERSION ); ?></sup>
				</h1>

				<?php if ( empty( $forms->signups ) ) : ?>
				
					<div class="gem-identity updated notice">
						<p><?php echo esc_html_x( 'Enjoy the GoDaddy Email Marketing Experience, first hand.', 'gem header note', 'godaddy-email-marketing' ); ?></p>

						<p><?php echo esc_html_x( 'Add your GoDaddy Email Marketing webform to your WordPress site! Easy to set up, the GoDaddy Email Marketing plugin allows your site visitors to subscribe to your email list.', 'header note', 'godaddy-email-marketing' ); ?></p>

						<p class="description">
							<?php if ( true === $valid_creds ) : ?>
								<?php echo esc_html_x( 'You don\'t have any forms yet.', 'godaddy-email-marketing' ); ?>
								<?php $this->signups_button(); ?>
							<?php else : ?>
								<?php echo sprintf( esc_html_x( 'Don\'t have a GoDaddy Email Marketing account? Get one in less than 2 minutes! %s', 'header note', 'godaddy-email-marketing' ), sprintf( '<a target="_blank" href="https://godaddy.com/business/email-marketing" class="button">%s</a>', esc_html_x( 'Sign Up Now', 'header note', 'godaddy-email-marketing' ) ) ); ?>
							<?php endif; ?>
						</p>
					</div>
				<?php endif; ?>
			</div>

			<form method="post" action="options.php">
				<h2 class="nav-tab-wrapper">
					<?php if ( ! empty( $forms->signups ) ) : ?>
						<a href="#forms" class="nav-tab <?php echo esc_attr( 'forms' === $tab ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e( 'Forms', 'godaddy-email-marketing' ); ?></a>
					<?php endif; ?>
					<a href="#settings" class="nav-tab <?php echo esc_attr( 'settings' === $tab || empty( $tab ) ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e( 'Settings', 'godaddy-email-marketing' ); ?></a>
				</h2>
				
				<div id="setting-errors"></div>

				<?php if ( ! empty( $forms->signups ) ) : ?>
					<div id="forms" class="panel">
						<h3><?php esc_html_e( 'Available Signup Forms', 'godaddy-email-marketing' ); ?></h3>
						<table class="wp-list-table widefat">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Form Name', 'godaddy-email-marketing' ); ?></th>
									<th><?php esc_html_e( 'Form ID', 'godaddy-email-marketing' ); ?></th>
									<th><?php esc_html_e( 'Shortcode', 'godaddy-email-marketing' ); ?></th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th><?php esc_html_e( 'Form Name', 'godaddy-email-marketing' ); ?></th>
									<th><?php esc_html_e( 'Form ID', 'godaddy-email-marketing' ); ?></th>
									<th><?php esc_html_e( 'Shortcode', 'godaddy-email-marketing' ); ?></th>
								</tr>
							</tfoot>
							<tbody>
							<?php foreach ( $forms->signups as $form ) :
								$edit_link = add_query_arg( array(
									'action' => 'edit_form',
									'form_id' => $form->id,
								) ); ?>
								<tr>
									<td>
										<?php echo esc_html( $form->name ); ?>
										<div class="row-actions">
											<span class="edit">
												<a target="_blank" href="<?php echo esc_url( $edit_link ); ?>" title="<?php esc_attr_e( 'Opens in a new window', 'godaddy-email-marketing' ); ?>"><?php esc_html_e( 'Edit form in GoDaddy Email Marketing', 'godaddy-email-marketing' ); ?></a> |
											</span>
											<span class="view">
												<a target="_blank" href="<?php echo esc_url( $form->url ); ?>"><?php esc_html_e( 'Preview', 'godaddy-email-marketing' ); ?></a>
											</span>
										</div>
									</td>
									<td><code><?php echo absint( $form->id ); ?></code></td>
									<td><input type="text" class="code" value="[gem id=<?php echo absint( $form->id ); ?>]" onclick="this.select()" readonly /></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>

						<br style="clear:both" />
						<p class="description">
							<?php esc_html_e( 'Not seeing your form?', 'godaddy-email-marketing' ); ?> <a href="<?php echo esc_url( add_query_arg( 'action', 'refresh' ) ); ?>" class="button"><?php esc_html_e( 'Refresh Forms', 'godaddy-email-marketing' ); ?></a>
							<?php
							if ( true === $valid_creds ) {
								$this->signups_button();
							}
							?>
						</p>
					</div>
				<?php endif; ?>

				<div id="settings" class="two-col panel">
					<?php settings_fields( 'gem-options' ); ?>

					<?php $this->do_settings_sections( $this->slug ); ?>

					<br style="clear:both" />
					<p class="submit">
						<?php submit_button( _x( 'Save Settings', 'save settings button', 'godaddy-email-marketing' ), 'primary', 'submit', false ); ?>
						<?php if ( empty( $forms->signups ) && true === $valid_creds ) : ?>
							<a href="<?php echo esc_url( add_query_arg( 'action', 'refresh' ) ); ?>" class="button"><?php esc_html_e( 'Refresh Forms', 'godaddy-email-marketing' ); ?></a>
						<?php endif; ?>
					</p>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Signup button for a new form.
	 */
	public function signups_button() {
		?>
		<a href="https://gem.godaddy.com/signups" target="_blank" class="button"><?php esc_html_e( 'Create a New Signup Form', 'godaddy-email-marketing' ); ?></a>
		<?php
	}

	/**
	 * Validate the API credentials by fetching the form.
	 *
	 * @param array $input An array of user input values.
	 * @return array
	 */
	public function validate( $input ) {

		// Sanitize the Username text field.
		$input['username'] = isset( $input['username'] ) ? sanitize_text_field( $input['username'] ) : '';

		// Santizie the API key text field.
		$input['api-key'] = isset( $input['api-key'] ) ? sanitize_text_field( $input['api-key'] ) : '';

		// Sanitize "Powered by GoDaddy" checkbox.
		$input['display_powered_by'] = ( isset( $input['display_powered_by'] ) && 1 === intval( $input['display_powered_by'] ) ) ? 1 : 0;

		// Sanitize Debug Mode checkbox.
		$input['debug'] = ( isset( $input['debug'] ) && 1 === intval( $input['debug'] ) ) ? 1 : 0;

		// The valid credential options default value.
		$validated = false;

		// Validate creds against the API.
		if ( ! ( empty( $input['username'] ) || empty( $input['api-key'] ) ) ) {

			// Check for an API connection.
			$data = GEM_Dispatcher::fetch_forms( $input['username'], $input['api-key'] );

			if ( ! $data ) {

				// Credentials are incorrect.
				set_transient( 'gem-invalid-creds', true, 30 );
			} elseif ( isset( $data->total ) && isset( $data->signups ) ) {

				$non_api_change = (
					GEM_Settings_Controls::get_option( 'username' ) === $input['username']
					&&
					GEM_Settings_Controls::get_option( 'api-key' ) === $input['api-key']
				);

				// Let the user know settings were updated or a connection was made.
				if ( $non_api_change ) {
					set_transient( 'gem-settings-updated', true, 30 );
				} else {
					set_transient( 'gem-valid-creds', true, 30 );
				}

				// Flag the credentials as being valid.
				$validated = true;
			}
		} else {

			// Credentials are empty.
			set_transient( 'gem-empty-creds', true, 30 );
		}

		// Set the valid credential option to reduce API calls if not connected, and to manipulate the UI.
		update_option( 'gem-valid-creds', $validated );

		// Return the sanitized input array.
		return $input;
	}
}

/**
 * GoDaddy Email Marketing settings controls.
 *
 * @since 1.0
 */
final class GEM_Settings_Controls {

	/**
	 * Displays the debugging section.
	 */
	public static function debugging() {
		printf(
			'<p>%s</p>',
			esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'godaddy-email-marketing' )
		);
	}

	/**
	 * Displays the unauthenticated description.
	 */
	public static function description() {
		printf(
			'<p>%s</p>',
			esc_html__( 'Please enter your GoDaddy Email Marketing username and API Key. Your credentials will connect WordPress with your GoDaddy Email Marketing account and display your available forms.', 'godaddy-email-marketing' )
		);
	}

	/**
	 * Displays the select option.
	 *
	 * @param array $args Settings field arguments.
	 */
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
		<?php
	}

	/**
	 * Displays the text input & description.
	 *
	 * @param array $args Settings field arguments.
	 */
	public static function text( $args ) {
		if ( empty( $args['id'] ) || empty( $args['page'] ) ) {
			return;
		}

		$name  = sprintf( '%s[%s]', $args['page'], $args['id'] );
		$id    = sprintf( '%s-%s', $args['page'], $args['id'] );
		$value = self::get_option( $args['id'] );
		?>

		<input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ) ?>" value="<?php echo esc_attr( $value ); ?>" class="widefat code" />

		<?php self::show_description( $args );
	}

	/**
	 * Displays the checkbox input & description.
	 *
	 * @param array $args Settings field arguments.
	 */
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

	/**
	 * Displays the button & description.
	 *
	 * @param array $args Settings field arguments.
	 */
	public static function button( $args ) {
		if ( empty( $args['url'] ) || empty( $args['label'] ) ) {
			return;
		}
		?>

		<p>
			<a href="<?php echo esc_url( $args['url'] ); ?>" class="button-secondary"><?php echo esc_html( $args['label'] ); ?></a>
		</p>

		<?php self::show_description( $args );
	}

	/**
	 * Displays the description.
	 *
	 * @param array $args Settings field arguments.
	 */
	public static function show_description( $args ) {
		if ( isset( $args['description'] ) ) : ?>

			<p class="description"><?php echo wp_kses_post( $args['description'] ); ?></p>

		<?php endif;
	}

	/**
	 * Get the settings value.
	 *
	 * @param string $key Settings key.
	 * @return false|mixed Returns the settings value or false.
	 */
	public static function get_option( $key = '' ) {
		$settings = get_option( 'gem-settings' );

		return ( ! empty( $settings[ $key ] ) ) ? $settings[ $key ] : false;
	}
}
