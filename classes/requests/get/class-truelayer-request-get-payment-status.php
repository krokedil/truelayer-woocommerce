<?php
/**
 * Class for the request to fetch the TrueLayer payment status.
 *
 * @package TrueLayer_For_WooCommerce/Classes/Requests/Get
 */

use Krokedil_TrueLayer_Dependencies\TrueLayer\Interfaces\Payment\PaymentRetrievedInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Class for the request to add a item to the TrueLayer payment status.
 */
class TrueLayer_Request_Get_Payment_Status extends TrueLayer_Request {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->log_title = 'Get TrueLayer payment status';
		$this->arguments = $arguments;
	}

	/**
	 * Make the request.
	 *
	 * @return PaymentRetrievedInterface|WP_Error
	 */
	public function request() {
		$this->client = $this->get_client();

		try {
			return $this->get_payment_status();
		} catch ( Exception $e ) {
			return new WP_Error( 'tl_get_payment_status_error', $e->getMessage() );
		}
	}

	/**
	 * Get the payment status.
	 *
	 * @return PaymentRetrievedInterface|WP_Error
	 *
	 * @throws Exception If the request fails.
	 */
	private function get_payment_status() {
		$payment_status = $this->client->getPayment( $this->arguments['transaction_id'] ?? '' );

		return $payment_status;
	}
}
