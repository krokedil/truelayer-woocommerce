<?php
/**
 * Krokedil Paynopva for WooCommerce request base class.
 *
 * @package @package TrueLayer_For_WooCommerce/classes/requests/
 */

use KrokedilTrueLayerDeps\Nyholm\Psr7\Factory\Psr17Factory;
use KrokedilTrueLayerDeps\TrueLayer\Client;
use KrokedilTrueLayerDeps\TrueLayer\Interfaces\Client\ClientInterface;

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed indirectly.
	exit;
}

/**
 * Class TrueLayer_Request
 */
abstract class TrueLayer_Request {

	/**
	 * The request method.
	 *
	 * @var string
	 */
	public $method;

	/**
	 * The request method.
	 *
	 * @var string
	 */
	public $endpoint;

	/**
	 * The request idempotency_key.
	 *
	 * @var string
	 */
	public $idempotency_key;

	/**
	 * The request title.
	 *
	 * @var string
	 */
	protected $log_title;

	/**
	 * The TrueLayer session id.
	 *
	 * @var string
	 */
	protected $truelayer_session_id;

	/**
	 * The request arguments.
	 *
	 * @var array
	 */
	protected $arguments;

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * The TrueLayer client.
	 *
	 * @var ClientInterface
	 */
	protected $client;


	/**
	 * Class constructor.
	 *
	 * @param array $arguments Constructor arguments.
	 */
	public function __construct( $arguments = array() ) {
		$this->arguments = $arguments;

		// Load TrueLayer settings and sets their use here.
		$this->settings = get_option( 'woocommerce_truelayer_settings' );
	}

	/**
	 * Check for test mode.
	 *
	 * @return string
	 */
	protected function is_test_mode() {
		return 'yes' === $this->settings['testmode'];
	}

	/**
	 * Get Live Environment Credentials.
	 *
	 * @return string
	 */
	protected function get_client_id() {
		return $this->is_test_mode() ? $this->settings['truelayer_sandbox_client_id'] : $this->settings['truelayer_client_id'];
	}

	/**
	 * Get Sandbox Environment Credentials.
	 *
	 * @return string
	 */
	protected function get_client_secret() {
		$key           = $this->is_test_mode() ? 'truelayer_sandbox_client_secret' : 'truelayer_client_secret';
		$client_secret = TruelayerEncryption()->decrypt_value( $key );

		return $client_secret;
	}

	/**
	 * Get the Private Key.
	 *
	 * @return string
	 */
	public function get_certificate() {
		$key         = $this->is_test_mode() ? 'truelayer_sandbox_client_certificate' : 'truelayer_client_certificate';
		$certificate = TruelayerEncryption()->decrypt_value( $key );

		return $certificate;
	}

	/**
	 * Get the Private Key.
	 *
	 * @return string
	 */
	public function get_private_key() {
		$key         = $this->is_test_mode() ? 'truelayer_sandbox_client_private_key' : 'truelayer_client_private_key';
		$private_key = TruelayerEncryption()->decrypt_value( $key );

		return $private_key;
	}

	/**
	 * Returns banking providers.
	 *
	 * @return array
	 */
	public function get_banking_providers() {
		$banking_providers = empty( $this->settings['truelayer_banking_providers'] ) ? array( 'retail' ) : $this->settings['truelayer_banking_providers']; // Default from TrueLayer is retail only. @see https://docs.truelayer.com/reference/create-payment.
		return array_map( 'strtolower', $banking_providers );
	}

	/**
	 * Returns the release channel
	 *
	 * @return array
	 */
	public function get_release_channel() {
		$release_channel = empty( $this->settings['truelayer_release_channel'] ) ? 'general_availability' : $this->settings['truelayer_release_channel']; // Default from TrueLayer is general_availability. @see https://docs.truelayer.com/reference/create-payment.
		return $release_channel;
	}

	/**
	 * Request headers.
	 *
	 * @param array $body The Request Body.
	 * @return array
	 */
	protected function get_request_headers( $body = array() ) {
		return array(
			'Content-Type' => 'application/json',
			'TL-Agent'     => 'truelayer-woocommerce/' . TRUELAYER_WC_PLUGIN_VERSION,
		);
	}

	/**
	 * Get the Truelayer client.
	 *
	 * @return ClientInterface
	 */
	protected function get_client() {
		$client = Client::configure()
			->clientId( $this->get_client_id() )
			->clientSecret( $this->get_client_secret() )
			->keyId( $this->get_certificate() )
			->pem( $this->get_private_key() )
			->useProduction( ! $this->is_test_mode() )
			->httpClient( new TrueLayer_Http_Client( $this->log_title ) )
			->httpRequestFactory( new Psr17Factory() )
			->create();

		return $client;
	}

	/**
	 * Make the request.
	 *
	 * @return object|WP_Error
	 */
	abstract public function request();
}
