<?php
/**
 * Refund request body class.
 *
 * @package TrueLayer_For_WooCommerce/Classes/Requests/Order_Management
 */

use Krokedil_TrueLayer_Dependencies\TrueLayer\Interfaces\Payment\RefundCreatedInterface;
use Krokedil_TrueLayer_Dependencies\TrueLayer\Interfaces\Payment\RefundFailedInterface;

/**
 * Class TrueLayer_Request_Refunds
 */
class TrueLayer_Request_Refunds extends TrueLayer_Request {

	/**
	 * WooCommerce Order ID
	 *
	 * @var int
	 */
	public $order_id;

	/**
	 * The WooCommerce order.
	 *
	 * @var WC_Order
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
		$this->order_id  = $arguments['order_id'];
		$this->order     = wc_get_order( $this->order_id );
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
		$reference  = ! empty( $this->reason ) ? $this->reason : __( 'Refund for order ', 'truelayer-for-woocommerce' ) . $this->order->get_order_number();

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
