<?php
/**
 * This class handles the checks for is_email().
 *
 * @package upfvp/Checks
 */

/**
 * Class UPFVP_is_email
 */
class UPFVP_is_email {

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
		add_filter( 'is_email', array( $this, 'validate' ), 10, 3 );
	}

	/**
	 * Validate the number.
	 *
	 * @param boolean $is_valid Assumend validation status.
	 * @param string  $email    Email to validate.
	 * @param string  $context   The context.
	 *
	 * @return bool
	 */
	public function validate( $is_valid, $email, $context ) {

		if ( ! $is_valid ) {
			return FALSE;
		}
		$this->validator->set_param('email');
		$this->validator->set_value( $email );
		return $this->validator->validate();
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
