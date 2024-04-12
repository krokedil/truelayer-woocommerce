<?php
use KrokedilTrueLayerDeps\TrueLayer\Exceptions\ApiResponseUnsuccessfulException;
use KrokedilTrueLayerDeps\TrueLayer\Exceptions\ValidationException;
/**
 * Create Payment request body class.
 *
 * @package TrueLayer_For_WooCommerce/Classes/Requests/Post
 */

defined( 'ABSPATH' ) || exit;

use KrokedilTrueLayerDeps\TrueLayer\Interfaces\Beneficiary\BeneficiaryInterface;
use KrokedilTrueLayerDeps\TrueLayer\Interfaces\Payment\PaymentCreatedInterface;
use KrokedilTrueLayerDeps\TrueLayer\Interfaces\PaymentMethod\BankTransferPaymentMethodInterface;
use KrokedilTrueLayerDeps\TrueLayer\Interfaces\UserInterface;

/**
 * Class TrueLayer_Request_Create_Payment
 */
class TrueLayer_Request_Create_Payment extends TrueLayer_Request {

	/**
	 * WooCommerce Order ID
	 *
	 * @var int
	 */
	public $order_id;

	/**
	 * Class constructor.
	 *
	 * @param array $arguments the class constructor arguments array.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->log_title = 'Create payment';
		$this->order_id  = $arguments['order_id'];
	}

	/**
	 * Make the request.
	 *
	 * @return PaymentCreatedInterface|WP_Error
	 */
	public function request() {
		$this->client = $this->get_client();

		try {
			return $this->create_payment();
		} catch ( ValidationException $e ) {
			return new WP_Error( 'tl_create_payment_validation_error', 'Validation error' );
		} catch ( ApiResponseUnsuccessfulException $e ) {
			return new WP_Error( 'tl_create_payment_api_response_error', $e->getMessage() );
		} catch ( Exception $e ) {
			return new WP_Error( 'tl_create_payment_error', $e->getMessage() );
		}
	}

	/**
	 * Build the payment object.
	 *
	 * @return PaymentCreatedInterface|WP_Error
	 *
	 * @throws Exception If the order ID is invalid.
	 */
	private function create_payment() {
		$order = wc_get_order( $this->order_id );

		if ( ! $order ) {
			throw new Exception( 'Invalid order ID' );
		}

		$payment = $this->client
			->payment()
			->amountInMinor( TrueLayer_Helper_Order::get_order_amount( $order ) )
			->currency( get_woocommerce_currency() )
			->paymentMethod( $this->create_bank_transfer_payment_method( $order ) )
			->user( $this->create_user( $order ) )
			->create();

		return $payment;
	}

	/**
	 * Create the payment method object.
	 *
	 * @param WC_Order $order The WooCommerce order object.
	 * @return BankTransferPaymentMethodInterface
	 */
	private function create_bank_transfer_payment_method( $order ) {
		$payment_method = $this->client
			->paymentMethod()
			->bankTransfer()
			->beneficiary( $this->create_beneficiary( $order ) );

		$payment_method = $this->add_provider_selection( $payment_method, $order->get_billing_country() );

		return $payment_method;
	}

	/**
	 * Create the beneficiary object.
	 *
	 * @param WC_Order $order The WooCommerce order object.
	 * @return BeneficiaryInterface
	 */
	private function create_beneficiary( $order ) {
		return $this->client->beneficiary()
			->merchantAccount()
			->accountHolderName( $this->settings['truelayer_beneficiary_account_holder_name'] ?? '' )
			->reference( $order->get_order_number() )
			->merchantAccountId( truelayer_get_merchant_account_id( $order->get_currency() ) );
	}

	/**
	 * Add provider selection.
	 *
	 * @param BankTransferPaymentMethodInterface $payment_method The payment method object.
	 * @param string                             $billing_country The billing country.
	 *
	 * @return BankTransferPaymentMethodInterface
	 */
	private function add_provider_selection( $payment_method, $billing_country ) {
		$release_channel   = $this->get_release_channel();
		$banking_providers = $this->get_banking_providers();

		$provider_filter = $this->client->providerFilter()
			->countries( array( $billing_country ) )
			->releaseChannel( $release_channel )
			->customerSegments( $banking_providers );

		$provider_selection = $this->client->providerSelection()
			->userSelected()
			->filter( $provider_filter );

		$payment_method->providerSelection( $provider_selection );

		return $payment_method;
	}

	/**
	 * Create the user object.
	 *
	 * @param WC_Order $order The WooCommerce order object.
	 * @return UserInterface
	 */
	private function create_user( $order ) {
		$user = $this->client->user()
			->name( TrueLayer_Helper_Order::get_account_holder_name( $order ) )
			->email( $order->get_billing_email() );

		$date_of_birth = TrueLayer_Helper_Order::get_user_date_of_birth( $order );
		if ( ! empty( $date_of_birth ) ) {
			$user->dateOfBirth( $date_of_birth );
		}

		$user = $this->maybe_add_billing_address( $user, $order );

		return $user;
	}

	/**
	 * Maybe add billing address.
	 *
	 * @param UserInterface $user The user object.
	 * @param WC_Order      $order The WooCommerce order object.
	 *
	 * @return UserInterface
	 */
	private function maybe_add_billing_address( $user, $order ) {
		if ( empty( $order->get_billing_address_1() ) ) {
			return $user;
		}

		$address = $user->address( null );

		if ( $order->get_billing_address_1() ) {
			$address->addressLine1( $order->get_billing_address_1() );
		}

		if ( $order->get_billing_address_2() ) {
			$address->addressLine2( $order->get_billing_address_2() );
		}

		if ( $order->get_billing_city() ) {
			$address->city( $order->get_billing_city() );
		}

		if ( $order->get_billing_postcode() ) {
			$address->zip( $order->get_billing_postcode() );
		}

		if ( $order->get_billing_country() ) {
			$address->countrycode( $order->get_billing_country() );
		}

		if ( ! empty( $order->get_billing_state() ) ) {
			$address->state( $order->get_billing_state() );
		}

		$user->address( $address );

		return $user;
	}
}
