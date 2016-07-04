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

    /**
     *
     * @param string|object $target
     * */
    public function __construct($target)
    {
        $this->setClass($target);
    }

    /**
     * Gets the data of Method
     * @return false | array
     * */
    public function getMethodData($method)
    {
        preg_match($this->methodRule, $method, $matches);

        return empty($matches) ? false : array_slice($matches, 1);
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
    public function getRoutables(Router $router = null, $prefix = null)
    {
        $router ?: $router = new Router;

        $class = $this->getClass();

        $prefix ?: $prefix = $this->buildUriPrefix($class);

        foreach ($this->getRoutableReflectionMethodList() as $reflection) {

            list($part, $method) = $data = $this->getMethodData($name = $reflection->getName());

            $uri = $this->buildUriMethodFromMethodPart($part, $prefix);

            $action = $class . '::' . $name;

            $router->$method($uri, $action);
        }

        return $router;
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

        } else {

            throw new \InvalidArgumentException(
                'The value of setClass must be class name or object'
            );
        }

        return $this;
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

    protected function buildUriMethodFromMethodPart($part, $controller = null)
    {
        $action = $this->camelCaseToHifen($part);

        $action === 'index' && $action = '';

        return $controller . '/' . $action;
    }

    protected function buildUriPrefix($controller)
    {
        return $this->camelCaseToHifen(
            preg_replace('/Controller$/', '', $controller)
        );
    }

    protected function camelCaseToHifen($camel)
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '-$0', $camel)), '-');
    }
}
