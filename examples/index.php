<?php

include_once __DIR__ . '/../vendor/autoload.php';

use PHPLegends\Routes\Router;

$router = new Router;

$router->get('/', function ()
{
	if ($_GET) print_r($_GET);

	return 'Welcome to home page';
});

$router->get('/info', function ()
{
	return 'Page of Info';
});

$router->addRoute(['get', 'put'], '/param/{str}', function ($string = null)
{
	return sprintf('You param passed is "%s"', $string);
});

header('content-type: text/html;charset=utf-8;');

try {

	echo $router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

} catch (\PHPLegends\Routes\Exceptions\HttpException $e) {

	http_response_code($e->getStatusCode());

	throw $e;
}




