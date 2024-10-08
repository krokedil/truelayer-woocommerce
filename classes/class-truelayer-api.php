<?php
/**
 * API Class File
 *
 * @package TrueLayer_For_WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use KrokedilTrueLayerDeps\TrueLayer\Interfaces\MerchantAccount\MerchantAccountInterface;
use KrokedilTrueLayerDeps\TrueLayer\Interfaces\Payment\PaymentCreatedInterface;
use KrokedilTrueLayerDeps\TrueLayer\Interfaces\Payment\PaymentRetrievedInterface;
use KrokedilTrueLayerDeps\TrueLayer\Interfaces\Payment\RefundCreatedInterface;

/**
 * The TrueLayer API class.
 */
class TrueLayer_API {

	/**
	 * The settings.
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * TrueLayer_API constructor.
	 */
	public function __construct() {
		$this->settings = get_option( 'woocommerce_truelayer_settings', array() );
	}

	/**
	 * Create a TrueLayer payment.
	 *
	 * @param WC_Order|int|WP_Post $order The WooCommerce order, order ID or WP_Post object.
	 * @return PaymentCreatedInterface|WP_Error
	 */
	public function create_payment( $order ) {
		$request  = new TrueLayer_Request_Create_Payment( array( 'order' => $order ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Refund Payment via TrueLayer.
	 *
	 * @param WC_Order|int|WP_Post $order The WooCommerce order, order ID or WP_Post object.
	 * @param int                  $amount The amount to be refunded.
	 * @param string               $reason the refund reason.
	 * @return RefundCreatedInterface|WP_Error
	 */
	public function refund_payment( $order, $amount, $reason ) {
		$request  = new TrueLayer_Request_Refunds(
			array(
				'order'  => $order,
				'amount' => $amount,
				'reason' => $reason,
			)
		);
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Get the TrueLayer payment status.
	 *
	 * @param string $transaction_id The TrueLayer payment ID.
	 * @return PaymentRetrievedInterface|WP_Error
	 */
	public function get_payment_status( $transaction_id ) {
		$request  = new TrueLayer_Request_Get_Payment_Status( array( 'transaction_id' => $transaction_id ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Get the TrueLayer merchant accounts.
	 *
	 * @return MerchantAccountInterface[]|WP_Error
	 */
	public function get_merchant_accounts() {
		$request  = new TrueLayer_Get_Merchant_Accounts( array() );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Checks for WP Errors and returns either the response as array or a false.
	 *
	 * @param object|WP_Error $response The response from the request.
	 * @return mixed
	 */
	private function check_for_api_error( $response ) {
		if ( is_wp_error( $response ) ) {
			if ( ! is_admin() ) {
				truelayer_print_error_message( $response );
			}
		}
		return $response;
	}
}
