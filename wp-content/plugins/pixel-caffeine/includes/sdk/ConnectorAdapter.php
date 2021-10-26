<?php
/**
 * Custom adapter to make SDK API calls through the internal proxy
 *
 * @package PixelCaffeine
 */

namespace PixelCaffeine\FB;

use FacebookAds\Exception\Exception;
use FacebookAds\Http\Adapter\AbstractAdapter;
use FacebookAds\Http\Client;
use FacebookAds\Http\RequestInterface;
use FacebookAds\Http\ResponseInterface;

/**
 * Class ConnectorAdapter
 *
 * @package PixelCaffeine\FB
 */
class ConnectorAdapter extends AbstractAdapter {

	/**
	 * List of request options
	 *
	 * @var \ArrayObject<int|string, mixed>
	 */
	protected $opts;

	/**
	 * The connector adapter instance.
	 *
	 * @var \AEPC_Facebook_Adapter|null
	 */
	protected $connector;

	/**
	 * ConnectorAdapter constructor.
	 *
	 * @param Client                      $client The SDK client instance.
	 * @param \AEPC_Facebook_Adapter|null $connector The connector adapter instance.
	 */
	public function __construct( Client $client, \AEPC_Facebook_Adapter $connector = null ) {
		parent::__construct( $client );

		$this->connector = $connector;
	}

	/**
	 * Get the list of request options
	 *
	 * @return \ArrayObject<int|string, mixed>
	 */
	public function getOpts() {
		if ( null === $this->opts ) {
			$this->opts = new \ArrayObject(
				array()
			);
		}

		return $this->opts;
	}

	/**
	 * Set the list of request options
	 *
	 * @param \ArrayObject<int|string, mixed> $opts  List of request options.
	 */
	public function setOpts( \ArrayObject $opts ) {
		$this->opts = $opts;
	}

	/**
	 * Send the request to Facebook API
	 *
	 * @param RequestInterface $request The request to send.
	 *
	 * @return ResponseInterface
	 * @throws \InvalidArgumentException When no connector defined during constructor.
	 * @throws Exception When the request fails.
	 */
	public function sendRequest( RequestInterface $request ) {
		if ( empty( $this->connector ) ) {
			throw new \InvalidArgumentException( 'The Connector Adapter needs Facebook API Connector adapter to work' );
		}

		$response = $this->getClient()->createResponse();

		$payload = array(
			'endpoint'   => $request->getPath(),
			'method'     => $request->getMethod(),
			'auth_token' => $this->connector->access_token,
			'parameters' => $request->getBodyParams()->export(),
		);

		$raw_response = $this->connector->send_request( $payload );

		if ( is_wp_error( $raw_response ) ) {
			$logger = new \AEPC_Admin_Logger();
			$logger->log(
				sprintf( 'Facebook API error: %s', $raw_response->get_error_message() ),
				array(
					'code'    => $raw_response->get_error_code(),
					'request' => $request,
					'payload' => $payload,
				)
			);
			throw new Exception( $raw_response->get_error_message(), is_int( $raw_response->get_error_code() ) ? $raw_response->get_error_code() : 500 );
		}

		$response->setStatusCode( (int) wp_remote_retrieve_response_code( $raw_response ) );
		$response->setBody( wp_remote_retrieve_body( $raw_response ) );

		if ( $response->getStatusCode() >= 400 ) {
			$logger = new \AEPC_Admin_Logger();
			$logger->log(
				sprintf( 'Facebook API request failed (error code %s)', $response->getStatusCode() ),
				array(
					'code'    => $response->getStatusCode(),
					'request' => $request,
					'payload' => $payload,
				)
			);
			throw new Exception( 'Facebook API request failed', $response->getStatusCode() );
		}

		return $response;
	}
}
