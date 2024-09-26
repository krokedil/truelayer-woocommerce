<?php
/**
 * Refund request body class.
 *
 * @package TrueLayer_For_WooCommerce/Classes/Requests/Order_Management
 */

use KrokedilTrueLayerDeps\TrueLayer\Interfaces\Payment\RefundCreatedInterface;
use KrokedilTrueLayerDeps\TrueLayer\Interfaces\Payment\RefundFailedInterface;

/**
 * Class TrueLayer_Request_Refunds
 */
class TrueLayer_Request_Refunds extends TrueLayer_Request {
	/**
	 * The WooCommerce order, order ID or WP_Post object.
	 *
	 * @var WC_Order|int|WP_Post
	 */
	public $order;

	/**
	 * The amount to be refunded.
	 *
	 * @var int
	 */
	public $amount;

	/**
	 * The refund reason.
	 *
	 * @var string
	 */
	public $reason;

	/**
	 * Class constructor.
	 *
	 * @param array $arguments the class constructor arguments array.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->order     = wc_get_order( $arguments['order'] );
		$this->log_title = 'Refund payment';

		$this->amount = intval( round( $arguments['amount'] * 100 ) );
		$this->reason = $arguments['reason'] ?? '';
	}

	/**
	 * Make the request.
	 *
	 * @return RefundCreatedInterface|WP_Error
	 */
	public function request() {
		$this->client = $this->get_client();

		try {
			return $this->refund_payment();
		} catch ( Exception $e ) {
			return new WP_Error( 'tl_refund_payment_error', $e->getMessage() );
		}
	}

	/**
	 * Refund the payment.
	 *
	 * @return RefundCreatedInterface|RefundFailedInterface|WP_Error
	 *
	 * @throws Exception When the payment is not found.
	 */
	private function refund_payment() {
		$payment_id = $this->order->get_transaction_id();
		// translators: %s: order number.
		$default_reason = sprintf( __( 'Refund for order %s', 'truelayer-for-woocommerce' ), $this->order->get_order_number() );
		$reference      = ! empty( $this->reason ) ? $this->reason : $default_reason;

		// Get the payment.
		$payment = TrueLayer()->api->get_payment_status( $payment_id );

		// Refund the payment.
		$refund = $this->client
			->refund()
			->payment( $payment )
			->amountInMinor( $this->amount )
			->reference( $reference )
			->create();

		return $refund;
	}
}
