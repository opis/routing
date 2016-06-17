<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013-2016 Marius Sarca
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Routing;

use Opis\Routing\Collections\RouteCollection;
use Opis\Routing\Collections\FilterCollection;

class Router
{
    /** @var \Opis\Routing\RouteCollection*/
    protected $routes;

    /** @var    \Opis\Routing\Collections\FilterCollection */
    protected $filters;

    /** @var    \Opis\Routing\DispatcherResolver */
    protected $resolver;

    /** @var    array */
    protected $specials = array();

    /** @var  Path|null */
    protected $currentPath;

    /** @var  Route|null */
    protected $currentRoute;

    /**
     * Constructor
     * 
     * @param   \Opis\Routing\Collections\RouteCollection   $routes
     * @param   \Opis\Routing\DispatcherResolver|null       $resolver   (optional)
     * @param   \Opis\Routing\PathFilter|null               $filters    (optional)
     */
    public function __construct(
        RouteCollection $routes, 
        DispatcherResolver $resolver = null,
        FilterCollection $filters = null,
        array $specials = array()
    ) {
        $this->routes = $routes;

        if ($resolver === null) {
            $resolver = new DispatcherResolver();
        }

        if ($filters === null) {
            $filters = new FilterCollection();
            $filters[] = new PathFilter();
        }

        $this->resolver = $resolver;
        $this->filters = $filters;
        $this->specials = $specials;
    }

    /**
     * Get the route collection
     * 
     * @return  \Opis\Routing\RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->routes;
    }

    /**
     * Get the filter collection
     * 
     * @return  \Opis\Routing\Collections\FilterCollection
     */
    public function getFilterCollection()
    {
        return $this->filters;
    }

    /**
     * Get the dispatcher resolver
     * 
     * @return  \Opis\Routing\DispatcherResolver
     */
    public function getDispatcherResolver()
    {
        return $this->resolver;
    }

    /**
     * Get special values
     * 
     * @return  array
     */
    public function getSpecialValues()
    {
        return $this->specials;
    }

    /**
     * 
     * @param   Path  $path
     * 
     * @return  mixed
     */
    public function route(Path $path)
    {
        $routes = $this->match($path);

        if(empty($routes)) {
            return false;
        }

        $this->currentPath = $path;

        foreach ($routes as $route) {
            $this->currentRoute = $route;
            // if pass filter
        }

        foreach ($this->routes->toArray() as $route) {
            $this->specials['self'] = $route;

            if ($this->pass($path, $route)) {
                $dispatcher = $this->resolver->resolve($this, $path, $route);
                return $dispatcher->dispatch($this, $path, $route);
            }
        }
    }

    /**
     * @param Path $path
     * @return Route[]
     */
    public function match(Path $path): array
    {
        $results = [];
        $path = (string) $path;
        $routes = null;
        $collection = $this->getRouteCollection();

        foreach ($collection->getRegexPatterns() as $routeID => $pattern){
            if(preg_match($pattern, $path)){
                if($routes === null){
                    $routes = $collection->getRoutes();
                }
                $results[$routeID] = $routes[$routeID];
            }
        }

        return $results;
    }

    /**
     * @param Path $path
     * @param Route $route
     * @return array
     */
    public function extractValues(Path $path, Route $route): array
    {
        $names = $this->getRouteCollection()->getCompiler()->getNames($route->getPattern());
    }

    /**
     * @param array $values
     * @param string[] $bindings
     * @return Binding[]
     */
    public function bind(array $values, array $bindings): array
    {

    }

    /**
     * @param callable $callback
     * @param Binding[] $values
     * @param bool $bind
     * @return array
     */
    public function buildArguments(Callback $callback, array $values, bool $bind = true): array
    {

    }

    /**
     * 
     * @param   \Opis\Routing\Path  $path
     * @param   \Opis\Routing\Route $route
     * 
     * @return  boolean
     */
    protected function pass(Path $path, Route $route)
    {
        foreach ($this->filters->toArray() as $filter) {
            if (!$filter->pass($this, $path, $route)) {
                return false;
            }
        }

        return true;
    }
}
