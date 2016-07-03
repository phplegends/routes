<?php

namespace PHPLegends\Routes;

/**
 * 
 * 
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * */

class Filter
{
	/**
	 * 
	 * @var callable
	 * */
	protected $callback;

	/**
	 * 
	 * @var string
	 * */
	protected $name;

	/**
	 * 
	 * 
	 * @param string $name
	 * @param callable $callback
	 * */
	public function __construct($name, callable $callback)
	{
		$this->name = $name;

		$this->callback = $callback;
	}

	/**
	 * 
	 * @return callable
	 * */
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * Gets the name value
	 * 
	 * @return string
	 * */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Invoke the callable of filter
	 * 
	 * @param mixed[] ...$args
	 * @return mixed
	 * */
	public function __invoke()
	{
		return call_user_func_array($this->getCallback(), func_get_args());
	}

	public function raiseExceptionIfNotNull()
	{
		$result = call_user_func_array($this, func_get_args());

		if ($result === null) return;

		if ($result instanceof \Exception)
		{
			throw $result;

		} elseif ($this->shouldBeException($result)) {	

			throw new \Exception((string) $result);
		}

		return $result;

	}

	protected function shouldBeException($e)
	{
		return is_string($e) 
			|| (is_object($e) && method_exists($e, '__toString'));

	}



}