<?php
/**
 * Helper class for building the Hosted Payment Page URL.
 *
 * @package TrueLayer/Classes/Requests/Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class for the redirect link generation.
 */
class Truelayer_Helper_Hosted_Payment_Page_URL {

	/**
	 * Generates the bank choice redirect url.
	 *
	 * @param WC_Order|int $order The WooCommerce Order or order id.
	 * @return string
	 */
	public static function build_hosted_payment_page_url( $order ) {
		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order );
		}

		if ( ! $order instanceof WC_Order ) {
			return '';
		}

		$settings = get_option( 'woocommerce_truelayer_settings' );

		$url                     = ( 'yes' === $settings['testmode'] ) ? 'https://payment.truelayer-sandbox.com/' : 'https://payment.truelayer.com/';
		$truelayer_payment_id    = $order->get_meta( '_truelayer_payment_id', true );
		$truelayer_payment_token = $order->get_meta( '_truelayer_payment_token', true );

		$redirect_uri = rawurlencode( home_url( '/wc-api/TrueLayer_Redirect/' ) );

		$hosted_payment_page_url = $url . 'payments#payment_id=' . $truelayer_payment_id . '&resource_token=' . $truelayer_payment_token . '&return_uri=' . $redirect_uri;

		return $hosted_payment_page_url;
	}
}
