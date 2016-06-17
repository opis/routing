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

class Router
{
    /** @var RouteCollection */
    protected $routes;

    /** @var FilterCollection */
    protected $filters;

    /** @var DispatcherResolver */
    protected $resolver;

    /** @var    array */
    protected $specials = array();

    /** @var  Path|null */
    protected $currentPath;

    /** @var  Route|null */
    protected $currentRoute;


    public function __construct(RouteCollection $routes, DispatcherResolver $resolver = null, FilterCollection $filters = null, array $specials = array())
    {
        $this->routes = $routes;
        $this->resolver = $resolver;
        $this->filters = $filters;
        $this->specials = $specials;
    }

    /**
     * Get the route collection
     * 
     * @return  RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->routes;
    }

    /**
     * Get the filter collection
     * 
     * @return  FilterCollection
     */
    public function getFilterCollection()
    {
        if($this->filters === null){
            $this->filters = new FilterCollection();
        }
        return $this->filters;
    }

    /**
     * Get the dispatcher resolver
     * 
     * @return DispatcherResolver
     */
    public function getDispatcherResolver()
    {
        if($this->resolver === null){
            $this->resolver = new DispatcherResolver();
        }
        return $this->resolver;
    }

    /**
     * Get special values
     * 
     * @return  array
     */
    public function getSpecialValues()
    {
        return $this->specials + array(
            'path' => $this->currentPath,
            'self' => $this->currentRoute,
        );
    }

    /**
     * 
     * @param   Path  $path
     * 
     * @return  mixed
     */
    public function route(Path $path)
    {
        $this->currentPath = $path;

        foreach ($this->match($path) as $route) {
            $this->currentRoute = $route;
            if(!$this->pass($path, $route)){
                continue;
            }
            $dispatcher = $this->getDispatcherResolver()->resolve($path, $route, $this);
            return $dispatcher->dispatch($path, $route, $this);
        }
        
        return false;
    }

    /**
     * @param Path $path
     * @return Route[]
     */
    public function match(Path $path): array
    {
        $results = [];
        $path = (string) $path;
        $routes = $this->getRouteCollection();

        foreach ($routes->getRegexPatterns() as $routeID => $pattern){
            if(preg_match($pattern, $path)){
                $results[$routeID] = $routes->getRoute($routeID);
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
        foreach ($this->getFilterCollection()->getFilters() as $filter) {
            if (!$filter->pass($path, $route, $this)) {
                return false;
            }
        }

        return true;
    }
}
