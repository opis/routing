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

class Dispatcher implements IDispatcher
{
    /** @var  Context */
    protected $context;

    /** @var  Router */
    protected $router;

    /** @var  Route|null */
    protected $route;

    public function dispatch(Router $router, Context $context)
    {
        $this->router = $router;
        $this->context = $context;

        if(null === $route = $this->findRoute()){
            return null;
        }

        return $this->invokeAction($route);
    }

    /**
     * @return null|Route
     */
    protected function findRoute()
    {
        /** @var Route $route */
        foreach ($this->match() as $route){
            $this->route = $route;
            if(!$this->pass($route)){
                continue;
            }
            if($route === null){
                echo  'x';
            }
            return $route;
        }

        return null;
    }

    /**
     * @return \Generator
     */
    protected function match(): \Generator
    {
        $context = (string) $this->context;
        $routes = $this->router->getRouteCollection();

        foreach ($routes->getRegexPatterns() as $routeID => $pattern){
            if(preg_match($pattern, $context)){
                yield $routes->getRoute($routeID);
            }
        }
    }

    /**
     * @param Route $route
     * @return bool
     */
    protected function pass(Route $route): bool
    {
        foreach ($this->router->getFilterCollection()->getFilters() as $filter){
            if(!$filter->pass($this->router, $this->context, $route)){
                return false;
            }
        }
        return true;
    }

    /**
     * @param Route $route
     * @return array
     */
    protected function extract(Route $route)
    {
        $routes = $route->getRouteCollection();
        $compiler = $routes->getCompiler();

        $names = $compiler->getNames($route->getPattern());
        $regex = $routes->getRegex($route->getID());
        $values = $compiler->getValues($regex, (string) $this->context);

        return array_intersect_key($values, array_flip($names)) + $route->getDefaults();
    }

    /**
     * @param array $values
     * @param array $bindings
     * @return Binding[]
     */
    protected function bind(array $values, array $bindings): array
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
     * @param array $values
     * @param bool $bind
     * @return array
     */
    protected function buildArguments(callable $callback, array $values, bool $bind = true): array
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
     * @return array
     */
    protected function getSpecialValues(): array
    {
        return $this->router->getSpecialValues() + [
                'router' => $this->router,
                'route' => $this->route,
                'context' => $this->context,
            ];
    }

    /**
     * @param Route $route
     * @return mixed
     */
    protected function invokeAction(Route $route)
    {
        $callback = $route->getAction();
        $values = $this->extract($route);
        $bindings = $this->bind($values, $route->getBindings());
        $arguments = $this->buildArguments($callback, $bindings);
        return $callback(...$arguments);
    }
}
