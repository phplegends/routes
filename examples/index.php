<?php

include_once __DIR__ . '/../vendor/autoload.php';

use PHPLegends\Routes\Router;
use PHPLegends\Routes\Dispatcher;

$router = new Router;

$router->addFilter('age', function ()
{
	if (isset($_GET['age']) && $_GET['age'] < 18) {
		return 'Não, vocẽ é menor de idade';
	}
});

$router->post('/', function ()
{
	if ($_GET) print_r($_GET);

	return 'Welcome to home page';

})->setFilters(['age']);

$router->get('/info', function ()
{
	return 'Page of Info';
});

$router->addRoute(['get', 'put'], '/param/{str}', function ($string = null)
{
	return sprintf('You param passed is "%s"', $string);
});

header('content-type: text/html;charset=utf-8;');

$dispatch = new Dispatcher(
	strtok($_SERVER['REQUEST_URI'], '?'), 
	$_SERVER['REQUEST_METHOD']
);

echo $router->dispatch($dispatch);

exit(1);

