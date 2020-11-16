<?php
/* ===========================================================================
 * Copyright 2018-2020 Zindex Software
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

use RuntimeException;
use Opis\Http\Request;

class RouteInvoker extends Invoker
{
    private Router $router;

    private Route $route;

    private Request $request;

    /** @var string[]|null */
    private ?array $names = null;

    /** @var array|null */
    private ?array $values = null;

    /** @var callable[]|null */
    private ?array $bindings = null;

    public function __construct(Router $router, Route $route, Request $request)
    {
        parent::__construct($router->getGlobalValues());
        $this->router = $router;
        $this->route = $route;
        $this->request = $request;
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        if ($this->names === null) {
            $names = [];
            $collection = $this->route->getRouteCollection();
            if (null !== $domain = ($this->route->getProperties()['domain'] ?? null)) {
                $names += $collection->getDomainBuilder()->getNames($domain);
            }
            $names += $collection->getRegexBuilder()->getNames($this->route->getPattern());
            $this->names = $names;
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
            $values = $builder->getValues($regex, $this->request->getUri()->path());

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

    protected function getCallback(): callable
    {
        $callback = $this->route->getAction();

        if (!$callback instanceof ControllerCallback) {
            return $callback;
        }

        /** @var ControllerCallback $callback */
        $methodName = $callback->getMethod();
        $className = $callback->getClass();

        $argResolver = $this->getArgumentResolver();

        if ($className[0] === '@') {
            $className = substr($className, 1);
            $class = $argResolver->getArgumentValue($className);
            if ($class === null) {
                throw new RuntimeException("Unknown controller variable '$className'");
            }
        } else {
            $class = $className;
        }

        if ($methodName[0] === '@') {
            $methodName = substr($methodName, 1);
            $method = $argResolver->getArgumentValue($methodName);
            if ($method === null) {
                throw new RuntimeException("Unknown controller variable '$methodName'");
            }
        } else {
            $method = $methodName;
        }

        if (!$callback->isStatic()) {
            if (!is_subclass_of($class, Controller::class)) {
                throw new RuntimeException("Controller class {$class} must extend " . Controller::class);
            }
            $class = new $class();
        }

        $ret = [$class, $method];

        if (!is_callable($ret)) {
            if (is_object($class)) {
                throw new RuntimeException("Cannot find public method '{$method}' on controller class " . get_class($class));
            } else {
                throw new RuntimeException("Cannot find public static method '{$method}' on controller class {$class}");
            }
        }

        return $ret;
    }
}