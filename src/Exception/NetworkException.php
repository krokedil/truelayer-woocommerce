<?php
namespace Krokedil\TrueLayer\Exception;

use KrokedilTrueLayerDeps\Psr\Http\Client\NetworkExceptionInterface;
use KrokedilTrueLayerDeps\Psr\Http\Message\RequestInterface;

class NetworkException extends \Exception implements NetworkExceptionInterface
{
	/**
	 * @var RequestInterface
	 */
	protected $request;

	/**
	 * NetworkException constructor.
	 *
	 * @param \WP_Error $wp_error The WordPress error from the request that failed.
	 * @param RequestInterface $request The PSR-7 request that was sent.
	 */
	public function __construct( \WP_Error $wp_error, RequestInterface $request)	{
		parent::__construct( $wp_error->get_error_message() );
		$this->request = $request;
	}

	/**
     * Returns the request.
     *
     * The request object MAY be a different object from the one passed to ClientInterface::sendRequest()
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface {
		return $this->request;
	}
}
