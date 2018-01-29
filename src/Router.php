<?php
/* ===========================================================================
 * Copyright 2013-2017 The Opis Project
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

use SplObjectStorage;
use Exception;

class Router
{
    /** @var RouteCollection */
    protected $routes;

    /** @var FilterCollection */
    protected $filters;

    /** @var Dispatcher */
    protected $dispatcher;

    /** @var array */
    protected $global;

    /** @var SplObjectStorage */
    protected $store;

    /** @var Context|null */
    protected $context;

    /**
     * Router constructor.
     * @param RouteCollection $routes
     * @param IDispatcher|null $dispatcher
     * @param FilterCollection|null $filters
     * @param GlobalValues|null $global
     */
    public function __construct(
        RouteCollection $routes,
        IDispatcher $dispatcher = null,
        FilterCollection $filters = null,
        GlobalValues $global = null
    ){
        if($dispatcher === null){
            $dispatcher = new Dispatcher();
        }
        $this->routes = $routes;
        $this->dispatcher = $dispatcher;
        $this->filters = $filters;
        $this->global = $global;
        $this->store = new SplObjectStorage();
    }

    /**
     * Get the route collection
     * 
     * @return  RouteCollection
     */
    public function getRouteCollection(): RouteCollection
    {
        return $this->routes;
    }

    /**
     * Get the filter collection
     * 
     * @return  FilterCollection
     */
    public function getFilterCollection(): FilterCollection
    {
        if($this->filters === null){
            $this->filters = new FilterCollection();
        }
        return $this->filters;
    }

    /**
     * Get global values
     *
     * @return  GlobalValues
     */
    public function getGlobalValues(): GlobalValues
    {
        if($this->global === null){
            $this->global = new GlobalValues();
        }
        return $this->global;
    }

    /**
     * Get the dispatcher resolver
     * 
     * @return IDispatcher
     */
    public function getDispatcher(): IDispatcher
    {
        return $this->dispatcher;
    }

    /**
     * @return Context
     * @throws Exception
     */
    public function getContext(): Context
    {
        if($this->context === null){
            throw new Exception("Invalid routing context");
        }
        return $this->context;
    }


    /**
     * @param Route $route
     * @return CompactRoute
     * @throws Exception
     */
    public function compact(Route $route)
    {
        if(!isset($this->store[$route])){
            return $this->store[$route] = new CompactRoute($route, $this->getContext(), $this->getGlobalValues());
        }
        return $this->store[$route];
    }

    /**
     *
     * @param   Context $context
     *
     * @return  mixed
     * @throws \Exception
     */
    public function route(Context $context)
    {
        $this->context = $context;
        return $this->getDispatcher()->dispatch($this);
    }
}
