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
    /** @var    \Opis\Routing\Collections\RouteCollection */
    protected $routes;

    /** @var    \Opis\Routing\Collections\FilterCollection */
    protected $filters;

    /** @var    \Opis\Routing\DispatcherResolver */
    protected $resolver;

    /** @var    array */
    protected $specials;

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
     * @return  \Opis\Routing\Collections\RouteCollection
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
     * @param   \Opis\Routing\Path  $path
     * 
     * @return  mixed
     */
    public function route(Path $path)
    {
        $this->specials += array(
            'path' => $path,
            'self' => null,
        );
        
        foreach ($this->routes->toArray() as $route) {
            $this->specials['self'] = $route;

            if ($this->pass($path, $route)) {
                $dispatcher = $this->resolver->resolve($this, $path, $route);
                return $dispatcher->dispatch($this, $path, $route);
            }
        }
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
