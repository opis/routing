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

class CompiledRoute
{
    /** @var Context */
    protected $context;

    /** @var Route */
    protected $route;

    /** @var callable */
    protected $extra;

    /** @var string[] */
    protected $names;

    /** @var array */
    protected $values;

    /** @var array[] */
    protected $bindings;

    /** @var CompiledRoute */
    protected $result;

    /**
     * CompiledRoute constructor.
     * @param Context $context
     * @param Route $route
     * @param callable $extra
     */
    public function __construct(Context $context, Route $route, callable $extra)
    {
        $this->context = $context;
        $this->route = $route;
        $this->extra = $extra;

        $this->result = $this;
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        if($this->names === null){
            $this->names = $this->route->getRouteCollection()->getCompiler()->getNames($this->route->getPattern());
        }

        return $this->names;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        if($this->values === null){
            $routes = $this->route->getRouteCollection();
            $compiler = $routes->getCompiler();

            $regex = $routes->getRegex($this->route->getID());
            $values = $compiler->getValues($regex, (string) $this->context);

            $this->values = array_intersect_key($values, array_flip($this->getNames())) + $this->route->getDefaults();
        }

        return $this->values;
    }

    /**
     * @return Binding[]
     */
    public function getBindings(): array
    {
        if($this->bindings === null){

            $values = $this->getValues();
            $bindings = $this->route->getBindings();

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

            $this->bindings = $bound;
        }

        return $this->bindings;
    }

    /**
     * @return mixed
     */
    public function invokeAction()
    {
        if($this->result === $this){
            $callback = $this->route->getAction();
            $arguments = $this->getArguments($callback);
            $this->result = $callback(...$arguments);
        }

        return $this->result;
    }

    /**
     * @param callable $callback
     * @param bool $bind
     * @return array
     */
    public function getArguments(callable $callback, bool $bind = true): array
    {
        $values = $bind ? $this->getBindings() : $this->getValues();
        return $this->buildArguments($callback, $values, $bind);
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

        $extra = ($this->extra)();

        foreach ($parameters as $param) {
            $name = $param->getName();

            if (isset($values[$name])) {
                $arguments[] = $bind ? $values[$name]->value() : $values[$name];
            } elseif (isset($extra[$name])) {
                $arguments[] = $extra[$name];
            } elseif ($param->isOptional()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                $arguments[] = null;
            }
        }

        return $arguments;
    }
}