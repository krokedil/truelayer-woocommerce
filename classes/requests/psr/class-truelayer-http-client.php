<?php
/**
 * A PSR-18 compliant HTTP client that wraps the WordPress HTTP API.
 *
 * @package TrueLayer_For_WooCommerce/classes/requests/psr
 */

use KrokedilTrueLayerDeps\Nyholm\Psr7\Response;
use KrokedilTrueLayerDeps\Psr\Http\Client\ClientInterface;
use KrokedilTrueLayerDeps\Psr\Http\Message\RequestInterface;
use KrokedilTrueLayerDeps\Psr\Http\Message\ResponseInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Class TrueLayer_Http_Client
 */
class TrueLayer_Http_Client implements ClientInterface {
	/**
	 * The log title to use for logging.
	 *
	 * @var string
	 */
	protected $log_title;

	/**
	 * The payment ID to use for the logging.
	 *
	 * @var string|null
	 */
	protected $payment_id;

	/**
	 * Class constructor.
	 *
	 * @param string      $log_title The log title to use for logging.
	 * @param string|null $payment_id The payment ID to use for the logging.
	 *
	 * @return void
	 */
	public function __construct( $log_title = 'TrueLayer Request', $payment_id = null ) {
		$this->log_title  = $log_title;
		$this->payment_id = $payment_id;
	}

	/**
	 * Sends a PSR-7 request and returns a PSR-7 response.
	 *
	 * @param RequestInterface $request The request to send.
	 *
	 * @return ResponseInterface
	 */
	public function sendRequest( RequestInterface $request ): ResponseInterface {
		$uri          = $request->getUri();
		$args         = $this->formatArgs( $request );
		$http_version = $request->getProtocolVersion();

		// Make the request.
		$response = wp_remote_request( $uri, $args );

		// Create the PSR-7 response.
		$response_code    = wp_remote_retrieve_response_code( $response );
		$response_headers = wp_remote_retrieve_headers( $response );
		$response_reason  = wp_remote_retrieve_response_message( $response );
		$response_body    = wp_remote_retrieve_body( $response );

		// Ensure the response code is numeric.
		$response_code = is_numeric( $response_code ) ? (int) $response_code : 400;

		// Format the headers to ensure they are an array.
		$response_headers = is_array( $response_headers ) ? $response_headers : iterator_to_array( $response_headers );

		// Create the PSR-7 response.
		$response = new Response( $response_code, $response_headers, $response_body, $http_version, $response_reason );

		$this->logRequest( $uri, $args, $response_body, $response_code );

		return $response;
	}

	/**
	 * Format the arguments for the WordPress HTTP API request.
	 *
	 * @param RequestInterface $request The request to send.
	 *
	 * @return array
	 */
	private function formatArgs( RequestInterface $request ): array {
		$args = array(
			'method'      => $request->getMethod(),
			'headers'     => $this->formatHeaders( $request ),
			'httpversion' => $request->getProtocolVersion(),
			'user-agent'  => $this->getUserAgent(),
			'timeout'     => apply_filters( 'truelayer_request_timeout', 10 ),
		);

		// Add the request body if it is set.
		if ( $request->getBody()->getSize() > 0 ) {
			$body = json_decode( (string) $request->getBody(), true );
			if ( ! empty( $body ) ) {
				$args['body'] = apply_filters( 'truelayer_request_args', (string) wp_json_encode( $body ) );
			}
		}

		return $args;
	}

	/**
	 * Format the headers for the WordPress HTTP API request.
	 *
	 * @param RequestInterface $request The request to send.
	 *
	 * @return array
	 */
	private function formatHeaders( RequestInterface $request ): array {
		$headers = array();

		foreach ( $request->getHeaders() as $header => $values ) {
			$headers[ $header ] = $request->getHeaderLine( $header );
		}

		return $headers;
	}

	/**
	 * Log the request and response.
	 *
	 * @param string $uri The request URI.
	 * @param array  $args The request arguments.
	 * @param string $response_body The response body.
	 * @param int    $response_code The response code.
	 *
	 * @return void
	 */
	private function logRequest( $uri, $args, $response_body, $response_code ) {
		$log_title = $this->log_title;

		// If the request is for the auth token, set the log title.
		if ( str_contains( $uri, 'auth' ) ) {
			$log_title = 'Get auth token';
		}

		// If the payment ID is not set, try to get it from the response body.
		if ( empty( $this->payment_id ) && ! empty( $response_body ) ) {
			$response_json    = json_decode( $response_body, true );
			$this->payment_id = $response_json['id'] ?? null;
		}

		$log = TrueLayer_Logger::format_log( $this->payment_id, $args['method'], $log_title, $args, json_decode( $response_body, true ), $response_code, (string) $uri );

		TrueLayer_Logger::log( $log );
	}

	/**
	 * Get the user agent for the request.
	 *
	 * @return string
	 */
	private function getUserAgent() {
		return 'WooCommerce: ' . WC()->version . ' - Plugin version: ' . TRUELAYER_WC_PLUGIN_VERSION . ' - PHP Version: ' . PHP_VERSION . ' - Krokedil';
	}
}
