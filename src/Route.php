<?php

namespace PHPLegends\Routes;

use PHPLegends\Routes\Exceptions\RouteException;
use PHPLegends\Routes\Exceptions\HttpException;

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

    /**
     * The constructor
     * 
     * @param string $pattern
     * @param string|Closure $action
     * @param array $verbs
     * @return void
     * */
    public function __construct($pattern, $action, array $verbs = ['*'])
    {
        $this->setPattern($pattern);

        $this->setVerbs($verbs);

        $this->setAction($action);
    }

    /**
     * Sets the action
     * 
     * @param string $action
     * @throws \LengthException
     * @return self
     * */
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

    /**
     * Sets the pattern for route
     * 
     * @param string $pattern
     * @return self
     * */
    public function setPattern($pattern)
    {   
        $this->pattern = ltrim($pattern, '/');

        return $this;
    }


    /**
     * Gets the value of pattern.
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Sets the verbs of route
     * 
     * @param array|string $verbs
     * @return self
     * */
    public function setVerbs($verbs)
    {
        $this->verbs = array_map('strtoupper', (array) $verbs);

        return $this;
    }

    public function getVerbs()
    {
        return $this->verbs;
    }

    /**
     * Asserts if http verb is accepted
     * 
     * @param string $method
     * */
    public function acceptedVerb($verb)
    {   

        if (isset($this->verbs[0]) && $this->verbs[0] == static::ANY_METHOD_WILDCARD) {

            return true;
        }

        return in_array(strtoupper($verb), $this->getVerbs(), true);
    }

    /**
     * @return array|Closure
     * */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return callable
     * */
    public function getActionAsCallable()
    {
        if ($this->action instanceof \Closure) {

            return $this->action;
        }

        return [new $this->action[0], $this->action[1]];
    }

    /**
     * Forces the action to be returns Closure
     * 
     * @return \Closure
     * */
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

    /**
     * Creates a new wildcard for regex
     * 
     * @param string $wildcard
     * @param string $regex
     * @return self
     * */
    public function where($wildcard, $regex)
    {
        $this->patternTranslations[$wildcard] = $regex;

        return $this;
    }

    /**
     * Determines if uri matches with regex. If match, return params of uri
     * 
     * @param string $uri
     * @return false | array
     * */
    public function match($uri)
    {
        if (preg_match($this->getParsedPattern(), trim($uri), $matches) > 0) {

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

    /**
     * 
     * @param string[] $filters
     * @return self
     * */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;

        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Check if route contains a filter
     * @return boolean
     * */
    public function hasFilter($name)
    {
        return in_array($name, $this->filters, true);
    }

    /**
     * Validates if the action is valid
     * 
     * @param string $controller
     * @param string $action
     * */
    protected function validateRoutable($controller, $action)
    {

        if (! method_exists($controller, $action)) {

            throw new \InvalidArgumentException("Action {$controller}::{$action}() doesn't exist");
        }

        return true;
    }
}