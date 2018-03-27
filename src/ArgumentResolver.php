<?php
/* ===========================================================================
 * Copyright 2013-2018 The Opis Project
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

class ArgumentResolver
{
    /** @var array */
    protected $values = [];

    /** @var callable[] */
    protected $bindings = [];

    /** @var ArrayAccess */
    protected $global;

    /**
     * ArgumentsContainer constructor.
     * @param ArrayAccess $global
     */
    public function __construct(ArrayAccess $global)
    {
        $this->global = $global;
    }

    /**
     * @param string $name
     * @param $value
     * @return ArgumentResolver
     */
    public function addValue(string $name, $value): self
    {
        $this->values[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @param callable $binding
     * @return ArgumentResolver
     */
    public function addBinding(string $name, callable $binding): self
    {
        $this->bindings[$name] = $binding;
        return $this;
    }

    /**
     * @param string $name
     * @param bool $bind
     * @param null $default
     * @return mixed|null
     */
    public function getArgumentValue(string $name, bool $bind = true, $default = null)
    {
        if ($bind && isset($this->bindings[$name])) {
            $callable = $this->bindings[$name];
            unset($this->bindings[$name]);
            $this->values[$name] = $callable(...$this->resolve($callable, true));
        }

        if (array_key_exists($name, $this->values)) {
            return $this->values[$name];
        }

        if ($this->global->offsetExists($name)) {
            return $this->global[$name];
        }

        return $default;
    }

    /**
     * @param callable $callback
     * @param bool $bind
     * @return array
     */
    public function resolve(callable $callback, bool $bind = true): array
    {
        $arguments = [];

        try {
            $parameters = $this->getParameters($callback);
        } catch (\ReflectionException $e) {
            return $arguments;
        }

        foreach ($parameters as $param) {
            $arguments[] = $this->getArgumentValue($param->getName(), $bind,
                $param->isOptional() ? $param->getDefaultValue() : null);
        }

        return $arguments;
    }

    /**
     * @param callable $callback
     * @return array
     * @throws \ReflectionException
     */
    public function getParameters(callable $callback): array
    {
        if (is_string($callback)) {
            if (function_exists($callback)) {
                $parameters = (new \ReflectionFunction($callback))->getParameters();
            } else {
                $parameters = (new \ReflectionMethod($callback))->getParameters();
            }
        } elseif (is_object($callback)) {
            if ($callback instanceof \Closure) {
                $parameters = (new \ReflectionFunction($callback))->getParameters();
            } else {
                $parameters = (new \ReflectionMethod($callback, '__invoke'))->getParameters();
            }
        } else {
            /** @var array $callback */
            $parameters = (new \ReflectionMethod(reset($callback), end($callback)))->getParameters();
        }

        return $parameters;
    }
}