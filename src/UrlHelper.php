<?php

namespace PHPLegends\Routes;

/**
 * Helper to generate urls based on routes
 * 
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>s
 * */
class UrlHelper
{

    /**
     * @var string
     * */
    protected $base_url;

    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * 
     * @param \PHPLegends\Routes\Collection $collection
     * @param string $baseUrl
     * */

    public function __construct(RouteCollection $routes, ?string $base_url = null)
    {
        $this->routes = $routes;

        $this->setBaseUrl($base_url);
    }

    /**
     * Generate a url based on uri
     * 
     * @param string $uri
     * @param array $query 
     * */
    public function to(string $uri, array $query = []): string
    {
        $uri = rtrim($this->buildUriWithBaseUrl($uri), '/') . '/';

        $query && $uri .= '?' . http_build_query($query);

        return $uri;
    }

    /**
     * Build url from route url name
     * 
     * @param string $name
     * @param string|array $parameters
     * @param array $query
     * @return string
     * */
    public function route(string $name, $parameters = [], array $query = []): ?string
    {
        $route = $this->getRouteCollection()->first(static function (Route $route) use ($name) {
            return $route->getName() === $name;
        });

        if ($route === null) return null;

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
        return $this->base_url;
    }

    /**
     * Sets the value of baseUrl.
     *
     * @param string $baseUrl the base url
     * @return self
     */
    public function setBaseUrl(string $base_url)
    {
        $this->base_url = $base_url;

        return $this;
    }

    /**
     * Gets the route collection
     *
     * @return PHPLegends\Collections\RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->routes;
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