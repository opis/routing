<?php
/* ===========================================================================
 * Copyright 2013-2016 The Opis Project
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

    /** @var  Context|null */
    protected $currentContext;

    /** @var  Route|null */
    protected $currentRoute;

    /** @var array */
    protected $names = array();

    /** @var  Compiler */
    protected $compiler;

    /**
     * Router constructor.
     * @param RouteCollection $routes
     * @param DispatcherResolver|null $resolver
     * @param FilterCollection|null $filters
     * @param array $specials
     */
    public function __construct(
        RouteCollection $routes,
        DispatcherResolver $resolver = null,
        FilterCollection $filters = null,
        array $specials = array()
    ){
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
            'router' => $this,
            'context' => $this->currentContext,
            'self' => $this->currentRoute,
        );
    }

    /**
     * @return Compiler
     */
    public function getCompiler(): Compiler
    {
        if($this->compiler === null){
            $this->compiler = $this->getRouteCollection()->getCompiler();
        }

        return $this->compiler;
    }

    /**
     * 
     * @param   Context  $context
     * 
     * @return  mixed
     */
    public function route(Context $context)
    {
        if(false === $route = $this->findRoute($context)){
            return false;
        }

        $dispatcher = $this->getDispatcherResolver()->resolve($this, $context, $route);
        return $dispatcher->dispatch($this, $context, $route);
    }

    /**
     * @param Context $context
     * @return bool|Route
     */
    public function findRoute(Context $context)
    {
        $this->currentContext = $context;

        /** @var Route $route */
        foreach ($this->match($context) as $route) {
            $this->currentRoute = $route;
            if(!$this->pass($context, $route)){
                continue;
            }
            return $route;
        }

        return false;
    }


    /**
     * @param Context $context
     * @return \Generator
     */
    public function match(Context $context): \Generator
    {
        $context = (string) $context;
        $routes = $this->getRouteCollection();

        foreach ($routes->getRegexPatterns() as $routeID => $pattern){
            if(preg_match($pattern, $context)){
                yield $routes->getRoute($routeID);
            }
        }
    }

    /**
     * @param Context $context
     * @param Route $route
     * @return array
     */
    public function extract(Context $context, Route $route): array
    {
        $names = $this->getCompiler()->getNames($route->getPattern());
        $regex = $this->getRouteCollection()->getRegex($route->getID());
        $values = $this->getCompiler()->getValues($regex, (string) $context);

        return array_intersect_key($values, array_flip($names)) + $route->getDefaults();
    }

    /**
     * @param array $values
     * @param callable[] $bindings
     * @return Binding[]
     */
    public function bind(array $values, array $bindings): array
    {
        $bound = array();

        foreach($bindings as $key => $callback) {
            $arguments = $this->buildArguments($callback, $values, false);
            $bound[$key] = new Binding($callback, $arguments);
        }

        foreach($values as $key => $value) {
            if(!isset($bound[$key])) {
                $bound[$key] = new Binding(null, null, $value);
            }
        }

        return $bound;
    }

    /**
     * @param callable $callback
     * @param Binding[]|array $values
     * @param bool $bind
     * @return array
     */
    public function buildArguments(callable $callback, array $values, bool $bind = true): array
    {
        $arguments = array();

        if(is_string($callback)){
            if(function_exists($callback)){
                $parameters = (new \ReflectionFunction($callback))->getParameters();
            } else {
                $parameters = (new \ReflectionMethod($callback))->getParameters();
            }
        } elseif (is_object($callback)){
            if($callback instanceof \Closure){
                $parameters = (new \ReflectionFunction($callback))->getParameters();
            } else {
                $parameters = (new \ReflectionMethod($callback, '__invoke'))->getParameters();
            }
        } else {
            $parameters = (new \ReflectionMethod(reset($callback), end($callback)))->getParameters();
        }

        $specials = $this->getSpecialValues();

        foreach ($parameters as $param) {
            $name = $param->getName();

            if (isset($values[$name])) {
                $arguments[] = $bind ? $values[$name]->value() : $values[$name];
            } elseif (isset($specials[$name])) {
                $arguments[] = $specials[$name];
            } elseif ($param->isOptional()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                $arguments[] = null;
            }
        }

        return $arguments;
    }

    /**
     * 
     * @param   \Opis\Routing\Context  $context
     * @param   \Opis\Routing\Route $route
     * 
     * @return  boolean
     */
    protected function pass(Context $context, Route $route)
    {
        foreach ($this->getFilterCollection()->getFilters() as $filter) {
            if (!$filter->pass($this, $context, $route)) {
                return false;
            }
        }

        return true;
    }
}
