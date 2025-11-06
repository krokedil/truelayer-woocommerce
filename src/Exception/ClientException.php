<?php
namespace Krokedil\TrueLayer\Exception;

use KrokedilTrueLayerDeps\Psr\Http\Client\RequestExceptionInterface ;
use KrokedilTrueLayerDeps\Psr\Http\Message\RequestInterface;

class ClientException extends \Exception implements RequestExceptionInterface  {

	/**
	 * @var RequestInterface
	 */
	protected $request;

	/**
	 * ClientException constructor.
	 *
	 * @param RequestInterface $request The PSR-7 request that was sent.
	 * @param string $message The Exception message to throw.
	 * @param int $code The Exception code.
	 * @param \Exception|null $previous The previous throwable used for the exception chaining.
	 */
	public function __construct( RequestInterface $request, $message = "", $code = 0, \Exception $previous = null )	{
		parent::__construct( $message, $code, $previous );
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
