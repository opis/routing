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

class RouteGroup
{
    /** @var  Route[] */
    private array $routes;

    /**
     * RouteGroup constructor.
     * @param Route[] $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function mixin(string $name, array $config = null): self
    {
        return $this->callMethod(__FUNCTION__, [$name, $config]);
    }

    public function bind(string $name, callable $callback): self
    {
        return $this->callMethod(__FUNCTION__, [$name, $callback]);
    }

    public function filter(string $name, callable $callback = null): self
    {
        return $this->callMethod(__FUNCTION__, [$name, $callback]);
    }

    public function guard(string $name, callable $callback = null): self
    {
        return $this->callMethod(__FUNCTION__, [$name, $callback]);
    }

    public function implicit(string $name, $value): self
    {
        return $this->callMethod(__FUNCTION__, [$name, $value]);
    }

    public function where(string $name, string $value): self
    {
        return $this->callMethod(__FUNCTION__, [$name, $value]);
    }

    /**
     * @param string $name
     * @param string[] $values
     * @return static
     */
    public function whereIn(string $name, array $values): self
    {
        return $this->callMethod(__FUNCTION__, [$name, $values]);
    }

    public function middleware(string ...$middleware): self
    {
        return $this->callMethod(__FUNCTION__, $middleware);
    }

    public function domain(string $value): self
    {
        return $this->callMethod(__FUNCTION__, [$value]);
    }

    public function secure(bool $value = true): self
    {
        return $this->callMethod(__FUNCTION__, [$value]);
    }

    private function callMethod(string $method, array $arguments): self
    {
        foreach ($this->routes as $route) {
            // Signal to route that this values are inherited
            Route::setIsInheriting($route, true);
            $route->{$method}(...$arguments);
            Route::setIsInheriting($route, false);
        }

        return $this;
    }
}