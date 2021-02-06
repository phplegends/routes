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
     * @var array
     * */
    protected $verbs = ['*'];

    /**
     *
     * @var string
     * */
    protected $pattern;

    /**
     * @var array|Closure
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

        $this->setVerbs(...$verbs);

        $this->setAction($action);

        $name && $this->setName($name);
    }

    /**
     * Sets the action
     *
     * @param array|closure $action
     * @throws \InvalidArgumentException
     * @return self
     * */
    public function setAction($action)
    {
        if ($action instanceof \Closure || is_array($action)) {

            $this->action = $action;
            return $this;
        }

        throw new \InvalidArgumentException(
            sprintf('The $action argument should be a array or Closure. %s given',  is_object($action) ? get_class($action) : gettype($action))
        );
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
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Sets the verbs of route
     *
     * @param array|string $verbs
     * @return self
     * */
    public function setVerbs(string ...$verbs)
    {
        $this->verbs = array_map('strtoupper', $verbs);

        return $this;
    }

    public function getVerbs(): array
    {
        return $this->verbs;
    }

    /**
     * Asserts if http verb is accepted
     *
     * @param string $method
     * */
    public function acceptedVerb(string $verb)
    {
        if (isset($this->verbs[0]) && $this->verbs[0] === static::ANY_METHOD_WILDCARD) {
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


    public function getActionName(): string 
    {
        $action = $this->getAction();

        return is_array($action)  ? implode('::', $action) : 'Closure';
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
    public function getActionAsClosure(): \Closure
    {
        if ($this->action instanceof \Closure) {
            return $this->action;
        }

        return function (...$args) {

            $action = $this->getActionAsCallable();

            return $action(...$args);
        };
    }


    /**
     * 
     * 
     * @return string
     * */
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

            $this->parameters = array_slice($matches, 1);

            return true;
        }

        return false;
    }


    /**
     * Sets the name of route
     *
     * @param string $name
     * @throws \InvalidArgumentException
     *
     * */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the name of route
     *
     * @return string
     * */
    public function getName(): ?string
    {
        return $this->name;
    }


    /**
     * 
     * @return string
     * */
    protected function wildcardsToRegex(): string
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
    protected function wildcardsToRegexGroups(array $wildcards, $delimiter = '/'): array
    {   

        $callback = function ($value) use ($delimiter) {

            return '/' . preg_quote($value, $delimiter) . '/';
        };       

        return array_map($callback, $wildcards);

    }



    /**
     * Gets the wildcards of route pattern
     * 
     * @return array
     * */
    protected function getPatternWildcards(): array
    {
        preg_match_all($this->wildcardsToRegex(), $this->getPattern(), $matches);

        return $matches[0] ?? [];
    }

    /**
     * Sets the parameters
     * 
     * @param array $parameters
     * @return self
     * */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * 
     * @return array
     * */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Generates the uri from current route
     * 
     * @param array $parameters
     * @return string
     * */
    public function toUri(array $parameters = []): string
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
}
