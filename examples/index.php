<?php

include_once __DIR__ . '/../vendor/autoload.php';

use PHPLegends\Routes\Router;

$router = new Router;

$router->get('/', function ()
{
	return 'Welcome to home page';
});

$router->get('/info', function ()
{
	return 'Page of Info';
});

$router->get('/param/{str?}', function ($string = null)
{
	return sprintf('You param passed is "%s"', $string);
});

$router->addRoute(['*'], '*', function ()
{
	http_response_code(404);

	return 'Página não existe';	
});


header('content-type: text/html;charset=utf-8;');

echo $router->dispatch($_SERVER['REQUEST_URI']);




