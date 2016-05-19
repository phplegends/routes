<?php

namespace PHPLegends\Routes\Exceptions;

/**
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * */

class InvalidVerbException extends RouteException 
{

	/**
	 * @var int
	 * */
	protected $statusCode;

	/**
	 * Constructor
	 * 
	 * @param string $message
	 * @param int $statusCode
	 * @return void
	 * */
	public function __construct($message, $statusCode = 500)
	{
		parent::__construct($message);

		$this->statusCode = $statusCode;
	}

	/**
	 * Gets the status code
	 * 
	 * @return int
	 * */
	public function getStatusCode()
	{
		return $this->statusCode;
	}
}