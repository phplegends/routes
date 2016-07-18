<?php

namespace PHPLegends\Routes;

use PHPLegends\Routes\Exceptions\RouteException;

/**
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * */

class Route
{

    const ANY_METHOD_WILDCARD = '*';

    /**
     *
     * @var string
     * */
    protected $verbs = ['*'];

    /**
     *
     * @var string
     * */
    protected $pattern;

    /**
     * @var string|Closure
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

    /**
     * 
     * @var array
     * */
    protected $parameters = [];

    /**
     * 
     * @var boolean
     * */
    protected $matched = false;
    
    /**
     *
     * @var array
     * */
    protected $patternTranslations = [
        '{num}'   => '(\d+)',
        '{num?}'  => '?(\d+)?',
        '{str}'   => '([A-Za-z0-9-_]+)',
        '{str?}'  => '?([A-Za-z0-9-_]+)?',
        '{date}'  => '(\d{4}\/\d{2}\/\d{2})',
        '{date?}' => '?(\d{4}\/\d{2}\/\d{2})?'
    ];

    /**
     * The constructor
     *
     * @param string $pattern
     * @param string|Closure $action
     * @param array $verbs
     * @param string|null $name
     * @return void
     * */
    public function __construct($pattern, $action, array $verbs = ['*'], $name = null)
    {
        $this->setPattern($pattern);

        $this->setVerbs($verbs);

        $this->setAction($action);

        $name && $this->setName($name);
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
        $this->pattern = trim($pattern, '/');

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
     * @todo isso está funcionando?
     *
     * @return \Closure
     * */
    public function getActionAsClosure()
    {
        if ($this->action instanceof \Closure)
        {
            return $this->action;
        }

        return function () {

            $action = $this->getActionAsCallable();

            return call_user_func_array($action, func_get_args());
        };
    }


    /**
     * Gets the name of action
     * 
     * @return string
     * */

    public function getActionName()
    {
        if ($this->action instanceof \Closure) {

            return 'Closure';
        }

        return implode('::', $this->action);
    }

    public function getParsedPattern()
    {

        $regex = strtr(addcslashes($this->pattern, '$/\\'), $this->getPatternTranslations());

        if (strpos($regex, '?') === 0) {

            $regex = ltrim($regex, '?');
        }

        return '/^\/?' . $regex . '\/?$/';
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
     * @return boolean
     * */
    public function match($uri)
    {
        if (preg_match($this->getParsedPattern(), trim($uri), $matches) > 0) {

            $parameters = array_slice($matches, 1);

            $this->setParameters($parameters);

            return true;
        }

        return false;
    }

    /**
     * Generates the uri from current route
     * 
     * @param array $parameters
     * @return string
     * */
    public function toUri(array $parameters = [])   
    {
        
        $pattern = $this->getPattern();

        $wildcardsRegexes = $this->wildcardsToRegexGroups(
            $matches = $this->getPatternWildcards()
        );

        $parameters = array_slice($parameters, 0, count($wildcardsRegexes));
        
        $this->validateParametersByWildcards($matches, $parameters);

        $uri = preg_replace($wildcardsRegexes, $parameters, $pattern, 1);

        return '/' . rtrim($uri, '/');

    }


    /**
     * Sets the name of route
     *
     * @param string $name
     * @throws \InvalidArgumentException
     *
     * */
    public function setName($name)
    {
        if (is_string($name) || $name === null) {

            $this->name = $name;

            return $this;
        }

        throw new \InvalidArgumentException('Name of route must be string or null value');

    }

    /**
     * Gets the name of route
     *
     * @return string
     * */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets list of filters
     *
     * @param array $filters
     * @return self
     * */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Add filters
     *
     * @param string|array $filter
     * @return self
     * */
    public function addFilter($filters)
    {

        $filters = is_array($filters) ? $filters : func_get_args();

        foreach ($filters as $filter) {

            $this->filters[] = $filter;
        }

        return $this;
    }

    /**
     *
     * @return array
     * */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Check if route contains a filter
     *
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

    /**
     * 
     * @return string
     * */
    protected function wildcardsToRegex()
    {
        $wildcards = array_map('preg_quote', array_keys($this->getPatternTranslations()));

        return sprintf('/%s/', implode('|', $wildcards));
    }

    /**
     * 
     * @param array $wildcards
     * @param string $delimiter
     * @return array
     * */
    protected function wildcardsToRegexGroups(array $wildcards, $delimiter = '/')
    {   

        $callback = function ($value) use ($delimiter) {

            return '/' . preg_quote($value, $delimiter) . '/';
        };       

        return array_map($callback, $wildcards);

    }

    /**
     * 
     * 
     * @param string $wildcard
     * @param string|int $value
     * @return boolean
     * */
    protected function validateParameterByWildcard($wildcard, $value)
    {
        if (isset($this->patternTranslations[$wildcard])) {

            $regex = ltrim($this->patternTranslations[$wildcard], '?');

            return preg_match("/^$regex$/", $value) >  0;
        }

        return false;
    }       

    /**
     * 
     * 
     * @param array $wildcards
     * @param array $parameters
     * @return boolean
     * */
    protected function validateParametersByWildcards(array $wildcards, array $parameters)
    {
        foreach(array_map(null, $wildcards, $parameters) as $key => $data) {

            list($wildcard, $param) = $data;

            if (! $this->validateParameterByWildcard($wildcard, $param)) {
                
                $message = 'Unable to convert route to uri. Unexpected value "%s" in argument #%d';

                throw new \UnexpectedValueException(sprintf($message, $param, $key));
            }
        }

        return true;
    }


    /**
     * Gets the wildcards of route pattern
     * 
     * @return array
     * */
    protected function getPatternWildcards()
    {
        preg_match_all($this->wildcardsToRegex(), $this->getPattern(), $matches);

        return empty($matches[0]) ? [] : $matches[0];
    }

    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}
