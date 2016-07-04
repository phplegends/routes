#PHPLegends Routes

The PHPLegends\Routes is a package for easy routing for you app.

For hello world, you can try this:

```php
$router = new \PHPLegends\Routes\Router;

$router->get('/', function () {
    return 'Hello World';
});

$page = isset($_GET['page']) ? $_GET['page'] : '/';

$dispatcher = new Dispatcher($page, $_SERVER['REQUEST_METHOD']);

$router->dispatcher($dispacher);
```

Now, run `php -s localhost:8000` in folder of your `index.php`
