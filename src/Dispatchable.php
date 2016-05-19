<?php

namespace PHPLegends\Routes;

interface Dispatchable
{
	/**
	 * Sets the router for current dispatch
	 * @param Router $route
	 * */
	public function setRouter(Router $route);

	/**
	 * @throws \RunTimeException
	 * */
	public function dispatch();

	/**
	 * 
	 * @return Router
	 * */
	public function getRouter();

	/**
	 * @param \Exception $e
	 * */
	public function handleException(\Exception $e);
}