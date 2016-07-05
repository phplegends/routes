<?php

namespace PHPLegends\Routes;

/**
 *  Inspector for routable class
 *
 *  @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * */
class RoutableInspector
{
    /**
     * @var \ReflectionClass
     * */
    protected $reflection;

    /**
     * @var string
     * */
    protected $methodRule = '/^action([A-Z][0-9A-Za-z]+)(Get|Post|Put|Head|Options|Trace|Delete|Any)$/';

    protected $crudableList = [
        'actionIndexGet'     => 'index',
        'actionCreateGet'    => 'create',
        'actionCreatePost'   => 'create',
        'actionDeleteDelete' => 'delete/{str}',
        'actionUpdateGet'    => 'update/{str}',
        'actionUpdatePost'   => 'update/{str}',
    ];

    /**
     *
     * @param string|object $target
     * */
    public function __construct($target)
    {
        $this->setClass($target);
    }

    /**
     * Validate if method is routable
     *
     * @param string $method
     * @return boolean
     * */
    public function isValidMethod($method)
    {
        $reflection = $this->getReflection()->getMethod($method);

        return ! $reflection->isAbstract()
                && $reflection->isPublic()
                && preg_match($this->methodRule, $method) > 0;
    }

    /**
     * Get list of ReflectionMethod if is valid routable
     *
     * @return array
     * */
    public function getRoutableReflectionMethodList()
    {
        $reflections = [];

        foreach ($this->getReflection()->getMethods() as $reflection) {

            if ($this->isValidMethod($reflection->name)) {

                $reflections[] = $reflection;
            }
        }

        return $reflections;
    }

    /**
     * Gets the routable list
     *
     * @param \PHPLegends\Routes\Router|null $router
     * @return \PHPLegends\Routers\Router
     * */
    public function generateRoutables(Router $router = null, $prefix = null)
    {
        $router ?: $router = new Router;

        $prefix ?: $prefix = $this->buildUriPrefix($this->reflection->getShortName());

        foreach ($this->getRoutableReflectionMethodList() as $reflection) {

            $this->addRouteFromMethod($router, $reflection->getName(), $prefix);
        }

        return $router;
    }


    protected function addRouteFromMethod(Router $router, $methodName, $prefix)
    {
        $verb = $this->getMethodVerb($methodName);

        $uri = $this->buildUriFromMethod($methodName, $prefix);

        $action = $this->reflection->name . '::' . $methodName;

        return $router->$verb($uri, $action);
    }

    /**
     * Gets the value of class.
     *
     * @return mixed
     */
    public function getClass()
    {
        return $this->reflection->getName();
    }

    /**
     * Sets the value of class.
     *
     * @param string|object $class the class
     * @return self
     */
    public function setClass($class)
    {
        if (is_string($class) && class_exists($class) || is_object($class)) {

            $this->setReflection(new \ReflectionClass($class));

            return $this;
        }

        throw new \InvalidArgumentException(
            "Class '$class' doesn't not exists"
        );

    }

    /**
     * Sets the value of reflection.
     *
     * @param ReflectionClass $reflection the reflection
     *
     * @return self
     */
    public function setReflection(\ReflectionClass $reflection)
    {
        $this->reflection = $reflection;

        return $this;
    }

    /**
     * Gets the value of reflection.
     *
     * @return \ReflectionClass
     */
    public function getReflection()
    {
        return $this->reflection;
    }

    protected function buildUriFromMethod($method, $prefix = null)
    {
        $action = $this->camelCaseToHifen(
            $this->getMethodWithoutActionAndVerb($method)
        );

        $action === 'index' && $action = '';

        foreach ($this->getReflection()->getMethod($method)->getParameters() as $parameter)
        {
            $action .= $parameter->isOptional() ? '/{str?}' : '/{str}';
        }

        return rtrim($prefix, '/') . '/' . $action;

    }

    public function buildUriPrefix($controller)
    {

        return $this->camelCaseToHifen(
            preg_replace('/Controller$/', '', $controller)
        );
    }

    /**
     *
     * @param string $method
     * @throws \InvalidArgumentException
     * @return string
     * */
    public function buildPossibleActionName($method)
    {
        if (! $this->isValidMethod($method)) {

            throw new \InvalidArgumentException('Invalid routable method name');
        }

        return $this->getClass() . '::' . $method;
    }

    public function generateCrudRoutes(Router $router = null, $prefix = null)
    {
        $router ?: $router = new Router;

        if ($prefix === null) {

            $prefix = $this->buildUriPrefix(
                $this->reflection->getShortName()
            );
        }

        foreach  ($this->crudableList as $method => $uri) {

            if (method_exists($this->reflection->name, $method) && $this->isValidMethod($method)) {

                $name = $this->buildCrudName($method, $prefix);

                $verb = $this->getMethodVerb($method);

                $router->$verb($prefix . '/' . $uri, $this->buildPossibleActionName($method), $name);
            }

        }

        return $router;
    }

    protected function buildCrudName($method, $prefix)
    {
        $verb = strtolower($this->getMethodVerb($method));

        $name = strtolower($this->getMethodWithoutActionAndVerb($method));

        if ($verb === 'get') {

            return $prefix . '.' . $name;
        }

        return $prefix . '.' . $verb . '.' . $name;

    }

    protected function camelCaseToHifen($camel)
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '-$0', $camel)), '-');
    }

    /**
     * Gets the data of Method
     * 
     * @param string $method
     * @return false|array
     * */
    protected function getMethodPartAndVerb($method)
    {
        preg_match($this->methodRule, $method, $matches);

        return empty($matches) ? false : array_slice($matches, 1);
    }

    /**
     * 
     * @param string $method
     * @return string
     * */
    protected function getMethodVerb($method)
    {
        return strtoupper(preg_replace($this->methodRule, '$2', $method));
    }

    /**
     * 
     * @param string $method
     * @return string
     * */
    protected function getMethodWithoutActionAndVerb($method)
    {
        return preg_replace($this->methodRule, '$1', $method);
    }
}
