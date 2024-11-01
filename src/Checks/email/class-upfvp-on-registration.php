<?php
/**
 * Handles the email validation on registration.
 *
 * @package upfvp/Checks
 */

/**
 * Class UPFVP_On_Registration
 */
class UPFVP_On_Registration {


	/**
	 * The validator object
	 *
	 * @var object
	 */
	protected $validator = NULL;


	/**
	 * UPFVP_is_email constructor.
	 */
	public function __construct() {}

	/**
	 * Set up the handler.
	 */
	public function setup() {

		add_action( 'registration_errors', array( $this, 'validate' ), 10, 3 );
	}

	/**
	 * Validate the number.
	 *
	 * @param WP_Error $errors The errors.
	 * @param string   $sanitized_user_login The user login.
	 * @param string   $email The user email.
	 *
	 * @return WP_Error
	 */
	public function validate( $errors, $sanitized_user_login, $email ) {

		// If it is not an email by WP or because we have hooked in already.
		if ( ! is_email( $email ) || email_exists( $email )  ) {
			return $errors;
		}

		$this->validator->set_param('email');
		$this->validator->set_value( $email );
		$maybe_valid = $this->validator->validate();
		if ( ! $maybe_valid ) {
			$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.', 'uproc-form-validator' ) );
		}
		return $errors;
	}

	/**
	 * Set the validator.
	 *
	 * @param object $validator The validator.
	 *
	 * @return object
	 */
	public function set_validator( $validator ) {

		$this->validator = (object) $validator;
		return $this->get_validator();
	}

	/**
	 * Get the validator.
	 *
	 * @return object
	 */
	public function get_validator() {

		return $this->validator;
	}
}
