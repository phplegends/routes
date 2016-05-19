<?php

namespace PHPLegends\Routes\Exceptions;

/**
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * */
class NotFoundException extends RouteException
{
	/**
	 * @param string $message
	 * @return void
	 * 
	 * */
	public function __construct($message)
	{
		parent::__construct($message, 404);
	}
}