<?php
/**
 * The main plugin class
 *
 * @package plugins
 */

/**
 * Class UPFVP_Plugin
 */
class UPFVP_Plugin {

	/**
	 * Whether we run in debugging mode.
	 *
	 * @var  bool
	 */
	protected static $debug = NULL;

	/**
	 * Plugin instance.
	 *
	 * @see   get_instance()
	 * @var  object
	 */
	protected static $instance = NULL;

	/**
	 * URL to this plugin's directory.
	 *
	 * @var  string
	 */
	public $plugin_url = '';

	/**
	 * Path to this plugin's directory.
	 *
	 * @var  string
	 */
	public $plugin_path = '';

	/**
	 * The API Object.
	 *
	 * @var object
	 */
	private $api = NULL;

	/**
	 * The validator object.
	 *
	 * @var object
	 */
	private $validator = NULL;

	/**
	 * The options
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @return  object of this class
	 */
	public static function get_instance() {

		if ( NULL === self::$debug ) {
			self::$debug = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? TRUE : FALSE;
		}

		NULL === self::$instance and self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook  plugins_loaded
	 * @return   void
	 */
	public function plugin_setup() {

		$this->plugin_url	= plugins_url( '/', dirname( __FILE__ ) );
		$this->plugin_path   = 'uproc-form-validator/';
		$this->load_language( 'uproc-form-validator' );

		// Load Options.
		$this->set_options( get_option( 'upfvp_settings', array() ) );

		// Load the API.
		require_once( dirname( __FILE__ ) . '/API/class-upfvp-api.php' );
		require_once( dirname( __FILE__ ) . '/API/class-upfvp-field-validator.php' );
		$this->api = new UPFVP_API();
		$this->api->set_email( trim($this->get_option( 'upfvp_email' )) );
		$this->api->set_apikey( trim($this->get_option( 'upfvp_api_key' )) );
		$this->validator = new UPFVP_Field_Validator();
		$this->validator->set_api( $this->api );

		// Load the Ajax Interface.
		require_once( dirname( __FILE__ ) . '/AJAX/class-upfvp-ajax-handler.php' );
		require_once( dirname( __FILE__ ) . '/AJAX/class-upfvp-ajax-validate-field.php' );
		$ajax_handler = new UPFVP_Ajax_Handler();
		$ajax = new UPFVP_Ajax_Validate_Field();
		$ajax->set_validator( $this->validator );
		$ajax->set_handler( $ajax_handler );

		//don't to be check
		$check_on_orders = 0 === (int) $this->get_option( 'upfvp_check_on_orders' );
		$check = ($this->is_woocommerce_request() && !$check_on_orders) || !$this->is_woocommerce_request();

		if ($check) {
			//If check is disabled, don't accept free emails
			if ( 0 === (int) $this->get_option( 'upfvp_accept_email_free' ) ) {
				require_once( dirname( __FILE__ ) . '/Checks/email/class-upfvp-accept-email-free.php' );
				$comment_check = new UPFVP_Is_Free();
				$comment_check->set_validator( $this->validator );
				$comment_check->setup();
			}

			if ( 1 === (int) $this->get_option( 'upfvp_is_email_check' ) ) {
				require_once( dirname( __FILE__ ) . '/Checks/email/class-upfvp-is-email.php' );
				$email_check = new UPFVP_is_email();
				$email_check->set_validator( $this->validator );
				$email_check->setup();
			}

			if ( 1 === (int) $this->get_option( 'upfvp_comments_check' ) ) {
				require_once( dirname( __FILE__ ) . '/Checks/email/class-upfvp-on-comment.php' );
				$comment_check = new UPFVP_On_Comment();
				$comment_check->set_validator( $this->validator );
				$comment_check->setup();
			}

			if ( 1 === (int) $this->get_option( 'upfvp_reg_check' ) ) {
				require_once( dirname( __FILE__ ) . '/Checks/email/class-upfvp-on-registration.php' );
				$comment_check = new UPFVP_On_Registration();
				$comment_check->set_validator( $this->validator );
				$comment_check->setup();
			}
		}

		/**
		 * Filters whether the ajax call is only for logged in users. Default: FALSE.
		 *
		 * @param bool
		 */
		$ajax_is_private = (bool) apply_filters( 'upfvp_api_is_private', TRUE );
		$ajax->set_private( $ajax_is_private );
		$ajax->register();

		add_action( 'wp_enqueue_scripts', array( $this, 'register_script' ) );

		if ( is_admin() ) {
			// Load the Admin Interface.
			require_once( dirname( __FILE__ ) . '/class-upfvp-admin.php' );
			$admin = UPFVP_Admin::get_instance();
			$admin->load();
		}
	}

	public function is_woocommerce_request() {
		global $woocommerce;

		// Use new checkout object for WC 3.0+
		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			$billing_email = $woocommerce->checkout->posted['billing_email'];
		} else {
			$checkout = new WC_Checkout;
			$billing_email = $checkout->get_value('billing_email');
		}

		return strlen(strtolower( $billing_email )) > 0;
	}

	/**
	 * Whether we are in debug mode.
	 *
	 * @return bool
	 */
	public function is_debug() {

		return self::$debug;
	}

	/**
	 * Register the Javascript.
	 */
	public function register_script() {

		// Minimize the script on production site.
		$min = '';
		if ( ! self::$debug ) {
			$min = '.min';
		}
		wp_register_script('upfvp_frontend_script', $this->get_plugin_url() . 'assets/js/upfvp_form_script' . $min . '.js', array( 'jquery', 'underscore' ), '1.0', TRUE );

		$js_vars = $this->js_localization();

		// The HTML templates.
		$js_vars['tpl']    = '<span class="upfvp-error"><%- status %> <%- value %></span>';

		wp_localize_script( 'upfvp_frontend_script', 'upfvp', $js_vars);
	}

	/**
	 * Enqueue Script on frontend.
	 *
	 * @see upfvp_activate_third_party()
	 */
	public function enqueue_frontend() {
		wp_enqueue_script( 'upfvp_frontend_script' );
	}

	/**
	 * Add footer styles on frontend
	 */
	public function footer_styles() {
		?><style>.upfvp-error {color: #f00;}</style><?php
	}
	/**
	 * Get the plugin url
	 *
	 * @return string plugin url.
	 */
	public function get_plugin_url() {

		return $this->plugin_url;
	}

	/**
	 * Get the plugin url
	 *
	 * @return string plugin path.
	 */
	public function get_plugin_path() {

		return $this->plugin_path;
	}

	/**
	 * Localized Javascript Variables.
	 *
	 * @return array
	 **/
	public function js_localization() {

		return array(
			'AJAX_URL' => esc_js( admin_url( 'admin-ajax.php' ) ),
			'nonce'    => esc_js( wp_create_nonce( 'validate-value' ) ),

			/*
			'200'      => __( 'OK - Valid Address', 'uproc-form-validator' ),
			'207'      => __( 'OK - Catch-All Active', 'uproc-form-validator' ),
			'215'      => __( 'OK - Catch-All Test Delayed', 'uproc-form-validator' ),

			'302'      => __( 'Local Address', 'uproc-form-validator' ),
			'303'      => __( 'IP Address Literal', 'uproc-form-validator' ),
			'305'      => __( 'Disposable Address', 'uproc-form-validator' ),
			'308'      => __( 'Role Address', 'uproc-form-validator' ),
			'313'      => __( 'Server Unavailable', 'uproc-form-validator' ),
			'314'      => __( 'Address Unavailable', 'uproc-form-validator' ),
			'316'      => __( 'Duplicate Address', 'uproc-form-validator' ),
			'317'      => __( 'Server Reject', 'uproc-form-validator' ),

			'401'      => __( 'Bad Address', 'uproc-form-validator' ),
			'404'      => __( 'Domain Not Fully Qualified', 'uproc-form-validator' ),
			'406'      => __( 'MX Lookup Error', 'uproc-form-validator' ),
			'409'      => __( 'No-Reply Address', 'uproc-form-validator' ),
			'410'      => __( 'Address Rejected', 'uproc-form-validator' ),
			'413'      => __( 'Server Unavailable', 'uproc-form-validator' ),
			'414'      => __( 'Address Unavailable', 'uproc-form-validator' ),
			'420'      => __( 'Domain Name Misspelled', 'uproc-form-validator' ),

			'114'      => __( 'Validation Delayed', 'uproc-form-validator' ),
			'118'      => __( 'Rate Limit Exceeded', 'uproc-form-validator' ),
			'119'      => __( 'API Key Invalid or Depleted', 'uproc-form-validator' ),
			'121'      => __( 'Task Accepted', 'uproc-form-validator' ),

			'800'      => __( 'Email Address Missing', 'uproc-form-validator' ),
			'801'      => __( 'Service Unavailable', 'uproc-form-validator' ),
			*/
			'permission'      => __( 'Email or API Key Invalid or Depleted', 'uproc-form-validator' ),
			'ok' 			=> __( 'OK - Valid value', 'uproc-form-validator' ),
			'ko' 			=> __( 'KO - Not valid value', 'uproc-form-validator' )
		);
	}

	/**
	 * Get the options.
	 *
	 * @return array
	 */
	public function get_options() {

		return $this->options;
	}

	/**
	 * Set the options.
	 *
	 * @param array $options The options.
	 *
	 * @return array
	 */
	public function set_options( $options ) {

		$defaults = array(
			'upfvp_email' => '',
			'upfvp_api_key' => '',
		);
		$this->options = wp_parse_args( (array) $options, $defaults );
		return $this->get_options();
	}

	/**
	 * Return a single option.
	 *
	 * @param string $option_key The option key.
	 *
	 * @return mixed|null
	 */
	public function get_option( $option_key ) {

		return ( ! empty( $this->options[ $option_key ] ) ) ? $this->options[ $option_key ] : NULL;
	}



	/**
	 * Empty and protected constructor.
	 */
	protected function __construct() {}

	/**
	 * Empty and private clone.
	 */
	private function __clone(){}


	/**
	 * Loads translation file.
	 *
	 * Accessible to other classes to load different language files (admin and
	 * front-end for example).
	 *
	 * @wp-hook init
	 * @param   string $domain The plugins domain.
	 * @return  void
	 */
	public function load_language( $domain ) {

		load_plugin_textdomain(
			$domain,
			FALSE,
			$this->plugin_path . 'languages'
		);
	}
}
