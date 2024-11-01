<?php
/**
 * Registers and handles the admin area.
 *
 * @package upfvp
 */

/**
 * Class UPFVP_Admin
 */
class UPFVP_Admin {

	/**
	 * Plugin instance.
	 *
	 * @see   get_instance()
	 * @var  object
	 */
	protected static $instance = NULL;

	/**
	 * Load the admin interface
	 *
	 * @return void
	 */
	public function load() {
		$plugin = UPFVP_Plugin::get_instance();

		// Check, if the admin notice should be hidden.
		if (
			current_user_can( 'manage_options' ) &&
			! empty( $_GET['upfvp-auth-notice'] ) && // Input var okay.
			wp_verify_nonce( sanitize_key( wp_unslash( $_GET['upfvp-auth-notice'] ) ), 'remove-auth-notice' ) // Input var okay.
		) {
			update_option( 'upfvp-auth-invalid', 0 );
		}

		// Check, if the admin notice should be displayed.
		if ( 1 === (int) get_option( 'upfvp-auth-invalid' ) ) {
			add_action( 'admin_notices', array( $this, 'show_notice' ) );
		}

		if (strlen($plugin->get_option('upfvp_email')) === 0) {
			update_option( 'upfvp-auth-invalid', 1 );
		}


		if (strlen($plugin->get_option('upfvp_api_key')) === 0) {
			update_option( 'upfvp-auth-invalid', 1 );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Shows the admin notice.
	 *
	 * @return void
	 */
	public function show_notice() {
		$url = admin_url( 'options-general.php' );
		$url = add_query_arg(
			array(
				'page'                => 'uproc_form_validator',
				'upfvp-auth-notice' => wp_create_nonce( 'remove-auth-notice' ),
			),
			$url
		);
		?>
		<div class="notice notice-error">
			<p>
				<?php esc_html_e( 'The Email or API Key are invalid.', 'uproc-form-validator' ); ?>
				<a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Go to settings and hide message', 'uproc-form-validator' ); ?></a>
			</p>

		</div>
		<?php
	}

	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	function admin_menu() {

		add_options_page( __('UProc for Wordpress', 'uproc-form-validator' ), __('UProc for Wordpress', 'uproc-form-validator' ), 'manage_options', 'uproc_form_validator', array( $this, 'render_page' ) );
	}

	/**
	 * Render the page.
	 *
	 * @return void
	 */
	function render_page() {

		?>
		<div class="wrap">
			<h1><?php esc_html_e('UProc for Wordpress', 'uproc-form-validator' ); ?></h1>
			<form action='options.php' method='post'>
				<?php
				settings_fields( 'uproc_form_validator' );
				do_settings_sections( 'uproc_form_validator' );
				submit_button();
				?>
			</form>

			<div class="card upfvp-card" style="">
				<h2><?php esc_html_e('Validate value', 'uproc-form-validator'); ?></h2>
				<p><?php esc_html_e('Please enter the email value you want to validate. By default, current logged user email appears.', 'uproc-form-validator'); ?></p>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><?php esc_html_e('Email', 'uproc-form-validator'); ?></th>
						<td>
							<input id="upfvp-param" value="email" type="hidden">
							<input id="upfvp-value" value="<?php echo esc_attr(wp_get_current_user()->user_email)?>" type="text">
						</td>
					</tr>
					</tbody>
				</table>
				<p id="upfvp-message"></p>
				<p class="submit">
					<input name="submit" id="upfvp-button-validate" class="button button-primary" value="<?php echo esc_attr( __('Validate', 'uproc-form-validator') ); ?>" type="submit">
				</p>
				<ul id="upfvp-list"></ul>
			</div>

		</div>
		<?php
	}

	/**
	 * Enqueue the necessary scripts and styles
	 *
	 * @param string $hook The page hook.
	 *
	 * @return void
	 */
	function enqueue( $hook ) {

		if ( 'settings_page_uproc_form_validator' !== $hook ) {
			return;
		}

		$plugin = UPFVP_Plugin::get_instance();
		$plugin_url = $plugin->get_plugin_url();

		$min = "";//( ! $plugin->is_debug() ) ? '.min' : '';

		wp_register_style( 'upfvp_main_style', $plugin_url . 'assets/css/upfvp_style' . $min . '.css' );
		wp_register_script('upfvp_main_script', $plugin_url . 'assets/js/upfvp_script' . $min . '.js', array( 'jquery', 'underscore' ), '1.0', TRUE );

		$js_vars = $plugin->js_localization();
		$js_vars['ul_tpl'] = '<li><span><%- status %></span><%- value %></li>';

		wp_localize_script( 'upfvp_main_script', 'upfvp', $js_vars);
		wp_enqueue_script( 'upfvp_main_script' );
		wp_enqueue_style( 'upfvp_main_style' );
	}

	/**
	 * Register the settings
	 *
	 * @return void
	 */
	function register_settings() {

		register_setting( 'uproc_form_validator', 'upfvp_settings' );

		/*
		add_settings_section(
			'upfvp_pluginPage_section',
			__( 'What is this?', 'uproc-form-validator' ),
			array( $this, 'render_section_intro' ),
			'uproc_form_validator'
		);
*/
		add_settings_section(
			'upfvp_credentials_section',
			__( 'Credentials', 'uproc-form-validator' ),
			array( $this, 'render_section_credentials' ),
			'uproc_form_validator'
		);

		add_settings_section(
			'upfvp_options_section',
			__( 'Email options', 'uproc-form-validator' ),
			array( $this, 'render_section_options' ),
			'uproc_form_validator'
		);

		add_settings_field(
			'upfvp_email',
			__( 'Email', 'uproc-form-validator' ),
			array( $this, 'render_settings_field' ),
			'uproc_form_validator',
			'upfvp_credentials_section',
			array(
				'id'      => 'email',
				'type'    => 'text',
				'key'     => 'upfvp_email',
				'default' => '',
			)
		);

		add_settings_field(
			'upfvp_api_key',
			__( 'API Key', 'uproc-form-validator' ),
			array( $this, 'render_settings_field' ),
			'uproc_form_validator',
			'upfvp_credentials_section',
			array(
				'id'      => 'apikey',
				'type'    => 'text',
				'key'     => 'upfvp_api_key',
				'default' => '',
			)
		);

		add_settings_field(
			'upfvp_reg_check',
			__( 'Validate email on registration', 'uproc-form-validator' ),
			array( $this, 'render_settings_field' ),
			'uproc_form_validator',
			'upfvp_options_section',
			array(
				'id'      => 'reg_check',
				'type'    => 'checkbox',
				'key'     => 'upfvp_reg_check',
				'default' => 0,
			)
		);

		add_settings_field(
			'upfvp_comments_check',
			__( 'Validate email for comments', 'uproc-form-validator' ),
			array( $this, 'render_settings_field' ),
			'uproc_form_validator',
			'upfvp_options_section',
			array(
				'id'      => 'comments_check',
				'type'    => 'checkbox',
				'key'     => 'upfvp_comments_check',
				'default' => 0,
			)
		);

		add_settings_field(
			'upfvp_is_email_check',
			__( 'Hook to is_email() function', 'uproc-form-validator' ),
			array( $this, 'render_settings_field' ),
			'uproc_form_validator',
			'upfvp_options_section',
			array(
				'id'      => 'is_email_check',
				'type'    => 'checkbox',
				'key'     => 'upfvp_is_email_check',
				'default' => 0,
			)
		);


		add_settings_field(
			'upfvp_check_on_orders',
			__( 'Check emails on orders', 'uproc-form-validator' ),
			array( $this, 'render_settings_field' ),
			'uproc_form_validator',
			'upfvp_options_section',
			array(
				'id'      => 'check_on_orders',
				'type'    => 'checkbox',
				'key'     => 'upfvp_check_on_orders',
				'default' => 0,
				'data-tooltip' => 'Include email validation on orders'
			)
		);


		add_settings_field(
			'upfvp_accept_free_email',
			__( 'Accept free emails', 'uproc-form-validator' ),
			array( $this, 'render_settings_field' ),
			'uproc_form_validator',
			'upfvp_options_section',
			array(
				'id'      => 'accept_free_email',
				'type'    => 'checkbox',
				'key'     => 'upfvp_accept_email_free',
				'default' => 0,
				'data-tooltip' => 'Accept free isp emails (gmail, hotmail, yahoo, ...)'
			)
		);


	}

	/**
	 * Renders the section area.
	 *
	 * @return void
	 */
	function render_section_intro() {

		//echo wp_kses_post( __('Visit <a href="https://uproc.io" target="_blank">our service</a> to validate any field value or data file. Please, check all the capabilities in <a href="https://uproc.io/#/catalog" target="_blank">our catalog</a>.', 'upfvp_credentials_section' ) );

		//echo '<br><br>';
		//echo wp_kses_post( __('You can <a href="https://uproc.io/#/signup" target="_blank">register</a> for a free API key (you get 1€ for testing purposes). You have to confirm the email received after signup.', 'upfvp_credentials_section' ) );
		//echo '<br>';
		//echo wp_kses_post( __('Additional cash can be added to your account by Paypal, Credit Card or Bank Transfer payment methods at <a href="https://uproc.io/#/billing" target="_blank">Payment section</a>.', 'upfvp_credentials_section' ) );
		//echo '<br><br>';

		//esc_html_e('To use the form validation in 3rd party forms, add the "upfvp-value" class to those inputs of the form that you want to validate and add the following code to the functions.php of your child theme:', 'uproc-form-validator' );

		/*echo '<textarea rows="3" class="large-text code" disabled>if ( function_exists( \'upfvp_activate_third_party\' ) ) {
	upfvp_activate_third_party();
}</textarea>';
*/
	}

	/**
	 * Renders the section area.
	 *
	 * @return void
	 */
	function render_section_credentials() {

		echo wp_kses_post( __('Visit <a href="https://uproc.io" target="_blank">our service</a> to validate any field value or data file. Please, check all the capabilities in <a href="https://app.uproc.io/#/tools" target="_blank">our catalog</a>.', 'upfvp_credentials_section' ) );
		echo '<br><br>';
		echo wp_kses_post( __('You can <a href="https://app.uproc.io/#/signup" target="_blank">register</a> for a free API key (you get some balance for testing purposes). You have to confirm the email received after signup.', 'upfvp_credentials_section' ) );
		echo '<br>';
		echo wp_kses_post( __('Additional cash can be added to your account by Paypal, Credit Card or Bank Transfer payment methods at <a href="https://app.uproc.io/#/purchase" target="_blank">Payment section</a>.', 'upfvp_credentials_section' ) );

		//esc_html_e('To use the form validation in 3rd party forms, add the "upfvp-value" class to those inputs of the form that you want to validate and add the following code to the functions.php of your child theme:', 'uproc-form-validator' );

		/*echo '<textarea rows="3" class="large-text code" disabled>if ( function_exists( \'upfvp_activate_third_party\' ) ) {
	upfvp_activate_third_party();
}</textarea>';
*/
	}

	/**
	 * Renders the section area.
	 *
	 * @return void
	 */
	function render_section_options() {

		echo wp_kses_post( __('Mark the options that you want to enable email validation on your website.', 'upfvp_options_section' ) );
		echo '<br><br>';
		echo wp_kses_post( __('Enabled option "Check emails on orders" can decrease fastly your balance at UProc if you have a WooCommerce Plugin installed. Please, disable it to avoid extra charges.', 'upfvp_options_section' ) );
		echo '<br>';
		echo wp_kses_post( __('Enabled option "Accept free emails" will consider valid any free email (Gmail, Yahoo, Hotmail, ...). Uncheck it to only accept non free emails.', 'upfvp_options_section' ) );
		echo '<br>';

		//esc_html_e('To use the form validation in 3rd party forms, add the "upfvp-value" class to those inputs of the form that you want to validate and add the following code to the functions.php of your child theme:', 'uproc-form-validator' );

		/*echo '<textarea rows="3" class="large-text code" disabled>if ( function_exists( \'upfvp_activate_third_party\' ) ) {
	upfvp_activate_third_party();
}</textarea>';
*/
	}

	/**
	 * Render a settings field
	 *
	 * @param array $args Specify the field.
	 *
	 * @return void
	 */
	public function render_settings_field( $args ) {
		$default_args = array(
			'id'      => 'apikey',
			'type'    => 'text',
			//'key'     => 'upfvp_api_key',
			'default' => 0,
		);
		$args = wp_parse_args( $args, $default_args );

		$plugin = UPFVP_Plugin::get_instance();


		$option = $plugin->get_option( $args['key'] );
		$value = ( NULL !== $option ) ? $option : $args['default'];

		if ( 'text' === $args['type'] ) :
		?>
		<input
			id="upfvp_value_<?php echo esc_attr( $args['key'] ); ?>"
			type="text"
			name="upfvp_settings[<?php echo esc_attr( $args['key'] ); ?>]"
			value="<?php echo esc_attr( trim($value) ); ?>"
		>
		<?php
		elseif ( 'checkbox' === $args['type'] ) : ?>
		<input
			id="upfvp_value_<?php echo esc_attr( $args['key'] ); ?>"
			type="checkbox"
			name="upfvp_settings[<?php echo esc_attr( trim($args['key']) ); ?>]"
			value="1"
			<?php checked( $value, 1 ); ?>
		>
		<?php
		endif;

	}

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @return  object of this class
	 */
	public static function get_instance() {

		NULL === self::$instance and self::$instance = new self;

		return self::$instance;
	}


	/**
	 * Empty and protected constructor.
	 */
	protected function __construct() {}

	/**
	 * Empty and private clone.
	 */
	private function __clone(){}

}
