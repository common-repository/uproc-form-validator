<?php
/**
 * The value validator
 *
 * @package upfvp/API
 */

/**
 * Class UPFVP_Field_Validator
 */
class UPFVP_Field_Validator {

	/**
	 * Whether the value is valid.
	 *
	 * @var bool
	 */
	protected $is_valid = TRUE;

	/**
	 * The param name.
	 *
	 * @var string
	 */
	protected $param = NULL;

	/**
	 * The value to validate.
	 *
	 * @var string
	 */
	protected $value = '';

	/**
	 * The property_name_to_check to validate.
	 *
	 * @var string
	 */
	protected $property_name_to_check = '';

	/**
	 * The property_value_to_check to validate.
	 *
	 * @var string
	 */
	protected $property_value_to_check = '';

	/**
	 * The returned response.
	 *
	 * @var string
	 */
	protected $response = NULL;


	/**
	 * The value status.
	 *
	 * @var string
	 */
	protected $value_status = '';

	/**
	 * The API object.
	 *
	 * @var object
	 */
	protected $api = NULL;

	/**
	 * Validate the value.
	 *
	 * @return boolean
	 */
	public function validate() {

		$api = $this->get_api();
		$api->set_param( $this->get_param() );
		$api->set_value( $this->get_value() );
		$response = $api->request();

		$this->set_response( $response );
  	$this->set_value_status( $response->result ? 'ok' : 'ko' );
		$this->set_is_valid( $response->result );

		// If there was an connection error, the value is valid.
		if ( NULL === $response ) {
			$this->set_is_valid( TRUE );

			/**
			 * Filters whether the phone number is valid.
			 *
			 * @param boolean $is_valid Whether the email is valid or not.
			 * @param object  $response The response
			 */
			return apply_filters( 'upfvp_value_valid', $this->get_is_valid(), $response );
		} else {
			$this->set_is_valid( TRUE );
		}

		if ( $this->get_value_status() === 'ko' ) {
			$this->set_is_valid( FALSE );
		}

		// If the API Key is invalid or depleted, we update an option to show an admin notice.
		//var_dump($this->get_value_status());
		//var_dump($response->message[0]->error);
		if ( $this->get_value_status() === 'ko'  && isset($response->message) === true) {
			//var_dump($response);
			if (is_array($response->message) && $response->message[0]->error === 'permission') {
				$this->set_value_status( 'permission' );
				update_option( 'upfvp-auth-invalid', 1 );
			}
		}

		//die('field_validator-' . $this->get_property_name_to_check().'-' . $this->get_property_value_to_check());

		//Make invalid depending on message property value
		$field_name = strtolower((string) $this->get_property_name_to_check());
		if ($field_name !== ""  && isset($response->message) === true) {
			$expected_value = strtolower((string) $this->get_property_value_to_check());
			if (isset($response->message->{$field_name}) === true) {
				$real_value = $response->message->{$field_name} ? 'true' : 'false';
				if ($expected_value !== $real_value ) {
					$this->set_is_valid( FALSE );
				}
			}
		}

		//var_dump($this->get_value_status());

		/**
		 * Filters whether the value is valid.
		 *
		 * @param boolean $is_valid Whether the email is valid or not.
		 * @param object  $response The response
		 */
		return apply_filters( 'upfvp_value_valid', $this->get_is_valid(), $response );
	}

	/**
	 * Get the validty of a number.
	 *
	 * @return bool
	 */
	public function get_is_valid() {

		return $this->is_valid;
	}

	/**
	 * Set the validty of a number.
	 *
	 * @param bool $is_valid The validity.
	 *
	 * @return bool
	 */
	protected function set_is_valid( $is_valid ) {

		$this->is_valid = (bool) $is_valid;
		return $this->get_is_valid();
	}

	/**
	 * Get the response
	 *
	 * @return bool
	 */
	public function get_response() {
		return $this->response;
	}

	/**
	 * Set the response
	 *
	 * @param bool $response The validity.
	 *
	 * @return bool
	 */
	protected function set_response( $response ) {
		$this->response = $response;
		return $this->get_response();
	}

	/**
	 * Get the param.
	 *
	 * @return string
	 */
	public function get_param() {

		return $this->param;
	}

	/**
	 * Set the param name.
	 *
	 * @param string $param The param name.
	 *
	 * @return string
	 */
	public function set_param( $param ) {

		$this->param = (string) $param;
		return $this->get_param();
	}


	/**
	 * Get the phone number.
	 *
	 * @return string
	 */
	public function get_value() {

		return $this->value;
	}

	/**
	 * Set the phone number.
	 *
	 * @param string $value The phone number.
	 *
	 * @return string
	 */
	public function set_value( $value ) {

		$this->value = (string) $value;
		return $this->get_value();
	}

	/**
	 * Get the property_name_to_check.
	 *
	 * @return string
	 */
	public function get_property_name_to_check() {
		return $this->property_name_to_check;
	}

	/**
	 * Set the property_name_to_check.
	 *
	 * @param string $property_name_to_check The property_name_to_check name.
	 *
	 * @return string
	 */
	public function set_property_name_to_check( $property_name_to_check ) {
		$this->property_name_to_check = (string) $property_name_to_check;
		return $this->get_property_name_to_check();
	}

	/**
	 * Get the property_value_to_check.
	 *
	 * @return string
	 */
	public function get_property_value_to_check() {

		return $this->property_value_to_check;
	}

	/**
	 * Set the property_value_to_check name.
	 *
	 * @param string $property_value_to_check The property_value_to_check name.
	 *
	 * @return string
	 */
	public function set_property_value_to_check( $property_value_to_check ) {

		$this->property_value_to_check = (string) $property_value_to_check;
		return $this->get_property_value_to_check();
	}

	/**
	 * Set the value status.
	 *
	 * @param string $status The status.
	 *
	 * @return string
	 */
	public function set_value_status( $status ) {

		$this->value_status = (string) $status;
		return $this->get_value_status();
	}

	/**
	 * Get the phone status.
	 *
	 * @return string
	 */
	public function get_value_status() {

		return $this->value_status;
	}

	/**
	 * Get the API
	 *
	 * @return object
	 */
	public function get_api() {

		return $this->api;
	}

	/**
	 * Set the API.
	 *
	 * @param object $api The API Object.
	 *
	 * @return object
	 */
	public function set_api( $api ) {

		$this->api = (object) $api;
		return $this->get_api();
	}
}
