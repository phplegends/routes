<?php

namespace PHPLegends\Routes;

use PHPLegends\Routes\Exceptions\RouteException;

/**
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * */

class Route
{

    /**
     * @var string
     * */
    protected $methods = ['*'];

    /**
     * @var string
     * */
    protected $pattern;

    /**
     * @var callable
     **/
    protected $action;

    /**
     * The name of route
     * @var string
     * */

    protected $name = null;

    /**
     *@var array
     * */
    protected $filters = [];


    const ANY_METHOD_WILDCARD = '*';

    protected $patternTranslations = [
        '*'      => '(.*)',
        '{num}'  => '(\d+)',
        '{num?}'  => '?(\d+)?',
        '{str}'  => '([a-z0-9-_]+)',
        '{str?}'  => '?([a-z0-9-_]+)?',
        '/'      => '\/',
        '\\'     => '\\\\',
        '{date}' => '(\d{4}\/\d{2}\/\d{2})',
        '{date?}' => '?(\d{4}\/\d{2}\/\d{2})?'
    ];

    public function __construct($pattern, $action = null, array $methods = ['*'])
    {
        $this->setPattern($pattern);

        $this->setMethods($methods);

        $this->setAction($action);
    }

    public function setAction ($action)
    {
        if ($action instanceof \Closure) {

            $this->action = $action;

            return $this;
        }

        $parts = explode('::', $action);

        if (count($parts) != 2) {

            throw new \LengthException('Malformed action string');
        }

        $this->validateRoutable($parts[0], $parts[1]);

        $this->action = $parts;

        return $this;

    }

    public function setPattern($pattern)
    {   
        $this->pattern = $pattern;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function setMethod($methods)
    {
        $this->methods = (array) $methods;

        return $this;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Asserts if http verb is accepted
     * @param string $method
     * */
    public function acceptedMethod($method)
    {   

        if (isset($this->methods[0]) && $this->methods[0] == static::ANY_METHOD_WILDCARD) {

            return true;
        }

        return in_array(strtoupper($method), $this->getMethods());
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getActionAsCallable()
    {
        if ($this->action instanceof \Closure) {

            return $this->action;
        }

        return [new $this->action[0], $this->action[1]];
    }

    public function getParsedPattern()
    {
        return '/^\/?' . strtr($this->pattern, $this->getPatternTranslations()) . '\/?$/';
    }

    protected function getPatternTranslations()
    {
        return $this->patternTranslations;
    }

    public function where($wildcard, $regex)
    {
        $this->patternTranslations[$wildcard] = $regex;
    }

    public function match($url)
    {
        return preg_match($this->getParsedPattern(), trim($url)) > 0;
    }

    public function getParameters($url)
    {
        preg_match($this->getParsedPattern(), $url, $matches);

        return array_slice($matches, 1);
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName() 
    {
        return $this->name;
    }

    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    public function getFilters()
    {
        return $this->filters;
    }


    protected function validateRoutable($class)
    {
        $routable = '\PHPLegends\Routes\Routable';

        if (! is_subclass_of($class, $routable, true)) {

            $message = sprintf("%s doesn't exist or doesnt implements %s", $class,$routable);

            throw new RouteException($message);
        }

        if (! method_exists($parts[0], $parts[1])) {

            throw new \InvalidArgumentException("Action {$parts[0]}::{$parts[1]}() doesn't exist");
        }

        return true;
    }

}