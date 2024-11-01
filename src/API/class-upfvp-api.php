<?php
/**
 * The main api.
 *
 * @package upfvp/API
 */

/**
 * Class UPFVP_API
 */
class UPFVP_API {

	/**
	 * The API endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'https://api.uproc.io/api/v2/process';

	/**
	 * The field to validate.
	 *
	 * @var string
	 */
	protected $param = 'email';

	/**
	 * The value to validate.
	 *
	 * @var string
	 */
	protected $value = NULL;

	/**
	 * The email for auth.
	 *
	 * @var string
	 */
	protected $email = NULL;

	/**
	 * The API Key for auth.
	 *
	 * @var string
	 */
	protected $apikey = NULL;

	/**
	 * The response object.
	 *
	 * @var object
	 */
	protected $response = NULL;

	/**
	 * Perform the request.
	 *
	 * @return null|object
	 */
	public function request() {

		$response = wp_cache_get( $this->get_value(), 'upfvp' );
		if ( $response ) {
			return $this->set_response( $response );
		}

		$body = new \stdClass();
		$body->processor = "get-email-lookup";
		$body->params = new \stdClass();
		$body->params->{$this->get_param()} = $this->get_value();

		$args = array(
			'method'   => 'POST',
			'headers' => [
				'Content-Type' => 'application/json',
				'Authorization' => 'Basic ' . base64_encode($this->get_email() . ":" . $this->get_apikey()),
				'UProc-scope'        => 'wpplugin'
			],
			'timeout'  => 45,
			'blocking' => TRUE,
		  'body'     => json_encode($body)
		);

		$result = wp_remote_post( $this->endpoint, $args );
		//var_dump($result);

		//$args['body'] = json_encode(json_decode($result['body']));
		//wp_remote_post( "http://requestb.in/178ijxs1", $args );
		//var_dump(wp_remote_retrieve_body( $result ));
		if ( ! is_wp_error( $result ) ) {
			$response = $this->set_response( json_decode( wp_remote_retrieve_body( $result ) ) );
			wp_cache_set( $this->get_value(), $response, 'upfvp' );
			return $response;
		}
		return NULL;
	}

	/**
	 * Get the endpoint.
	 *
	 * @return string
	 */
	public function get_endpoint() {
		return $this->endpoint;
	}

	/**
	 * Set the endpoint.
	 *
	 * @param string $endpoint The endpoint.
	 *
	 * @return string
	 */
	public function set_endpoint( $endpoint ) {

		$this->endpoint = (string) $endpoint;
		return $this->get_endpoint();
	}

	/**
	 * Get the value.
	 *
	 * @return string
	 */
	public function get_value() {

		return $this->value;
	}

	/**
	 * Set the value value.
	 *
	 * @param string $value The value address.
	 *
	 * @return string
	 */
	public function set_value( $value ) {

		$value = trim( (string) $value );
		$this->value = $value;
		return $this->get_value();
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
	 * Set the param.
	 *
	 * @param string $param The param name to validate.
	 *
	 * @return string
	 */
	public function set_param( $param ) {

		$param = trim( (string) $param );
		$this->param = $param;
		return $this->get_param();
	}

	/**
	 * Get the email auth.
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Set the Email Auth.
	 *
	 * @param string $email The Email Auth.
	 *
	 * @return string
	 */
	public function set_email( $email ) {

		$this->email = (string) $email;
		return $this->get_email();
	}

	/**
	 * Get the API Key.
	 *
	 * @return string
	 */
	public function get_apikey() {

		return $this->apikey;
	}

	/**
	 * Set the API Key.
	 *
	 * @param string $apikey The API Key.
	 *
	 * @return string
	 */
	public function set_apikey( $apikey ) {

		$this->apikey = (string) $apikey;
		return $this->get_apikey();
	}

	/**
	 * Get the Response Object.
	 *
	 * @return object
	 */
	public function get_response() {

		return $this->response;
	}

	/**
	 * Set the Response Object.
	 *
	 * @param  object $response The Response Object.
	 *
	 * @return object
	 */
	public function set_response( $response ) {

		$this->response = (object) $response;
		return $this->get_response();
	}
}
