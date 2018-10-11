<?php
/* ===========================================================================
 * Copyright 2018 Zindex Software
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

use ArrayAccess;

class RouteInvoker
{
    /** @var Context */
    protected $context;

    /** @var Route */
    protected $route;

    /** @var ArrayAccess */
    protected $global;

    /** @var string[] */
    protected $names;

    /** @var array */
    protected $values;

    /** @var array[] */
    protected $bindings;

    /** @var RouteInvoker */
    protected $result;

    /** @var ArgumentResolver */
    protected $argumentResolver;

    /**
     * @param Context $context
     * @param Route $route
     * @param ArrayAccess $global
     */
    public function __construct(Route $route, Context $context, ArrayAccess $global)
    {
        $this->context = $context;
        $this->route = $route;
        $this->global = $global;
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
        if ($this->names === null) {
            $this->names = $this->route->getRouteCollection()->getRegexBuilder()->getNames($this->route->getPattern());
        }

        return $this->names;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        if ($this->values === null) {
            $routes = $this->route->getRouteCollection();
            $builder = $routes->getRegexBuilder();

            $regex = $routes->getRegex($this->route->getID());
            $values = $builder->getValues($regex, (string)$this->context);

            $this->values = array_intersect_key($values, array_flip($this->getNames())) + $this->route->getDefaults();
        }

        return $this->values;
    }

    /**
     * @return callable[]
     */
    public function getBindings(): array
    {
        if ($this->bindings === null) {
            $this->bindings = $this->route->getBindings();
        }

        return $this->bindings;
    }

    /**
     * @return mixed
     */
    public function invokeAction()
    {
        if ($this->result === $this) {
            $callback = $this->route->getAction();
            $arguments = $this->getArgumentResolver()->resolve($callback);
            $this->result = $callback(...$arguments);
        }

        return $this->result;
    }

    /**
     * @return ArgumentResolver
     */
    public function getArgumentResolver(): ArgumentResolver
    {
        if ($this->argumentResolver === null) {

            $resolver = new ArgumentResolver($this->global);

            foreach ($this->getValues() as $key => $value) {
                $resolver->addValue($key, $value);
            }

            foreach ($this->getBindings() as $key => $callback) {
                $resolver->addBinding($key, $callback);
            }

            $this->argumentResolver = $resolver;
        }

        return $this->argumentResolver;
    }
}