<?php

namespace PHPLegends\Routes;

/**
 * Helpers to generates the urls for routes
 * 
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>s
 * */
class UrlGenerator
{

    /**
     * 
     * @var string
     * */
    protected $baseUrl;

    /**
     * 
     * @var \PHPLegends\Routes\Router
     * */

    protected $router;

    /**
     * 
     * @param \PHPLegends\Routes\Collection $collection
     * @param string $uriRoot
     * */

    public function __construct(Router $router, $baseUrl = null)
    {

        $this->setRouter($router);

        $this->setBaseUrl($baseUrl);
    }

    /**
     * 
     * @param string $uri
     * @param 
     * */
    public function to($uri, array $query = [])
    {
        $uri = rtrim($this->buildUriWithBaseUrl($uri), '/') . '/';

        $query && $uri .= '?' . http_build_query($query);

        return $uri;
    }

    /**
     * 
     * @param string $name
     * @param string|array $parameters
     * @param array $query
     * @return string
     * */
    public function route($name, $parameters = [], array $query = [])
    {

        $callback = function ($route) use ($name) {

            return $route->getName() === $name && $route->acceptedVerb('GET');
        };

        $route = $this->getRouteCollection()->firstOrFail($callback);

        return $this->to($route->toUri((array) $parameters), $query);
    }

    /**
     * Generate url via string action
     * 
     * @param string $action
     * @param string|array $parameters
     * @param array $query
     * */
    public function action($action, $parameters = [], array $query = [])
    {
        $callback = function (Route $route) use ($action) {

            return $route->getActionName() === $action && $route->acceptedVerb('GET');
        };

        $route = $this->getRouteCollection()->firstOrFail($callback);

        return $this->to($route->toUri((array) $parameters), $query);
    }


    /**
     * Implode all itens of array and build uri segments
     * 
     * @param array $parameters
     * @return string
     * */
    protected function toSegments(array $parameters)
    {
        return implode('/', array_map('rawurldecode', $parameters));
    }

    /**
     * Add a "uri root" tho $uri
     * 
     * @param string $uri
     * @return strings
     * */
    protected function buildUriWithBaseUrl($uri)
    {
        return rtrim($this->getBaseUrl(), '/') . '/' . ltrim($uri, '/');
    }

    /**
     * Gets the value of baseUrl.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Sets the value of baseUrl.
     *
     * @param string $baseUrl the base url
     * @return self
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function getRouteCollection()
    {
        return $this->getRouter()->getCollection();
    }

    /**
     * Gets the value of router.
     *
     * @return PHPLegends\Routes\Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Sets the value of router.
     *
     * @param mixed $router the router
     *
     * @return self
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Gets the clone with secure baseurl
     * 
     * @experimental
     * @return string
     * */
    public function secure()
    {
        $clone = clone $this;

        $clone->setBaseUrl(
            preg_replace('/^https?/', 'https', $clone->getBaseUrl())
        );

        return $clone;
    }

}