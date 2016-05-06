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
    protected $verbs = ['*'];

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
        '*'      => '(.*?)',
        '{num}'  => '(\d+)',
        '{num?}'  => '?(\d+)?',
        '{str}'  => '([a-z0-9-_]+)',
        '{str?}'  => '?([a-z0-9-_]+)?',
        '/'      => '\/',
        '\\'     => '\\\\',
        '{date}' => '(\d{4}\/\d{2}\/\d{2})',
        '{date?}' => '?(\d{4}\/\d{2}\/\d{2})?'
    ];

    public function __construct($pattern, $action = null, array $verbs = ['*'])
    {
        $this->setPattern($pattern);

        $this->setMethod($verbs);

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
        $this->pattern = ltrim($pattern, '/');
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function setMethod($verbs)
    {
        $this->verbs = (array) $verbs;

        return $this;
    }

    public function getVerbs()
    {
        return $this->verbs;
    }

    /**
     * Asserts if http verb is accepted
     * @param string $method
     * */
    public function acceptedVerbs($verb)
    {   

        if (isset($this->verbs[0]) && $this->verbs[0] == static::ANY_METHOD_WILDCARD) {

            return true;
        }

        return in_array(strtoupper($verb), $this->getVerbs());
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

    public function getActionAsClosure()
    {
        if ($this->action instanceof \Closure)
        {
            return $this->action;
        }

        $me = $this;

        return function () use ($me) {

            $action = $me->getAction();

            return call_user_func_array($action, func_get_args());
        };
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
        if (preg_match($this->getParsedPattern(), trim($url), $matches) > 0) {

            return array_slice($matches, 1);
        }

        return false;
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

    protected function validateRoutable($controller, $action)
    {

        if (! method_exists($controller, $action)) {

            throw new \InvalidArgumentException("Action {$controller}::{$action}() doesn't exist");
        }

        return true;
    }


}