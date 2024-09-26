<?php
/**
 * Class for the request to fetch the TrueLayer merchant accounts.
 *
 * @package TrueLayer_For_WooCommerce/Classes/Requests/Get
 */

defined( 'ABSPATH' ) || exit;

use KrokedilTrueLayerDeps\TrueLayer\Interfaces\MerchantAccount\MerchantAccountInterface;

/**
 * Class for the request to add a item to the TrueLayer merchant accounts.
 */
class TrueLayer_Get_Merchant_Accounts extends TrueLayer_Request {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments = array() ) {
		parent::__construct( $arguments );
		$this->log_title = 'Get TrueLayer merchant account';
		$this->arguments = $arguments;
	}

	/**
	 * Make the request.
	 *
	 * @return array|WP_Error
	 */
	public function request() {
		$this->client = $this->get_client();

		try {
			return $this->get_merchant_accounts();
		} catch ( Exception $e ) {
			return new WP_Error( 'tl_get_merchant_accounts_error', $e->getMessage() );
		}
	}

	/**
	 * Get the merchant accounts.
	 *
	 * @return MerchantAccountInterface[]|WP_Error
	 *
	 * @throws Exception If the request fails.
	 */
	private function get_merchant_accounts() {
		$merchant_accounts = $this->client->getMerchantAccounts();

		return $merchant_accounts;
	}
}
