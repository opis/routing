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

use Opis\Routing\Traits\{
    Filter as FilterTrait,
    Bindings as BindingTrait
};
use Opis\Utils\RegexBuilder;

class RouteCollection
{
    use FilterTrait;
    use BindingTrait;

    /** @var Route[] */
    private $routes = [];

    /** @var null|string[] */
    private $regex;

    /** @var string[] */
    private $namedRoutes = [];

    /** @var  RegexBuilder */
    private $builder;

    /** @var RegexBuilder|null */
    private $domainBuilder;

    /** @var bool */
    private $dirty = false;

    /** @var callable[] */
    private $mixins = [];

    /**
     * RouteCollection constructor.
     * @param RegexBuilder|null $builder
     */
    public function __construct(
        RegexBuilder $builder = null
    ) {
        $this->builder = $builder ?? new RegexBuilder([
                RegexBuilder::CAPTURE_MODE => RegexBuilder::CAPTURE_LEFT,
        ]);
    }

    /**
     * @param string $pattern
     * @param callable $action
     * @param string[] $method
     * @param int $priority
     * @param string|null $name
     * @return Route
     */
    public function createRoute(string $pattern, callable $action, array $method, int $priority = 0, string $name = null): Route
    {
        $id = $this->generateRouteId();
        $route = new Route($this, $id, $pattern, $action, $method, $priority, $name);
        $this->routes[$id] = $route;
        $this->dirty = true;
        $this->regex = null;

        if (null !== $name = $route->getName()) {
            $this->namedRoutes[$name] = $id;
        }

        return $route;
    }

    /**
     * @return RegexBuilder
     */
    public function getRegexBuilder(): RegexBuilder
    {
        return $this->builder;
    }

    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @return array
     */
    public function getRegexPatterns(): array
    {
        if ($this->regex === null) {
            $this->regex = [];
            foreach ($this->routes as $route) {
                $this->regex[$route->getID()] = $this->builder->getRegex($route->getPattern(),
                    $route->getPlaceholders());
            }
        }
        return $this->regex;
    }

    /**
     * @return array
     */
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }

    /**
     * @param string $id
     * @return null|Route
     */
    public function getRoute(string $id): ?Route
    {
        return $this->routes[$id] ?? null;
    }

    /**
     * @param string $id
     * @return null|string
     */
    public function getRegex(string $id): ?string
    {
        if ($this->regex === null) {
            $this->getRegexPatterns();
        }

        return $this->regex[$id] ?? null;
    }

    /**
     * Sort collection
     */
    public function sort()
    {
        if (!$this->dirty) {
            return;
        }

        /** @var string[] $keys */
        $keys = array_reverse(array_keys($this->routes));
        /** @var Route[] $values */
        $values = array_reverse(array_values($this->routes));

        $done = false;

        while (!$done) {
            $done = true;
            for ($i = 0, $l = count($this->routes) - 1; $i < $l; $i++) {
                $invert = $values[$i]->getPriority() < $values[$i + 1]->getPriority();
                if ($invert) {
                    $done = false;
                    $temp_value = $values[$i];
                    $temp_key = $keys[$i];
                    $values[$i] = $values[$i + 1];
                    $keys[$i] = $keys[$i + 1];
                    $values[$i + 1] = $temp_value;
                    $keys[$i + 1] = $temp_key;
                }
            }
        }

        $this->regex = null;
        $this->dirty = false;
        $this->routes = array_combine($keys, $values);
    }

    /**
     * @return RegexBuilder
     */
    public function getDomainBuilder(): RegexBuilder
    {
        if ($this->domainBuilder === null) {
            $this->domainBuilder = new RegexBuilder([
                RegexBuilder::SEPARATOR_SYMBOL => '.',
                RegexBuilder::CAPTURE_MODE => RegexBuilder::CAPTURE_RIGHT,
            ]);
        }
        return $this->domainBuilder;
    }

    /**
     * @return callable[]
     */
    public function getMixins(): array
    {
        return $this->mixins;
    }

    /**
     * @param string $name
     * @param callable $callback
     * @return static
     */
    public function mixin(string $name, callable $callback): self
    {
        $this->mixins[$name] = $callback;
        return $this;
    }

    public function __serialize(): array
    {
        return [
            'builder' => $this->builder,
            'domainBuilder' => $this->domainBuilder,
            'dirty' => $this->dirty,
            'regex' => $this->regex,
            'routes' => $this->routes,
            'namedRoutes' => $this->namedRoutes,
            'defaults' => $this->defaults,
            'bindings' => $this->bindings,
            'filters' => $this->filters,
            'placeholders' => $this->placeholders,
            'mixins' => $this->mixins,
        ];
    }

    public function __unserialize(array $data): void
    {
        foreach ($data as $property => $value) {
            $this->{$property} = $value;
        }
    }

    /**
     * @return string
     */
    private function generateRouteId(): string
    {
        try {
            return sprintf('%012x%04x%04x%012x',
                random_int(0, 0xffffffffffff),
                random_int(0, 0x0fff) | 0x4000,
                random_int(0, 0x3fff) | 0x8000,
                random_int(0, 0xffffffffffff)
            );
        } catch (\Throwable $e) {
            return sprintf('%012x%04x%04x%012x',
                rand(0, 0xffffffffffff),
                rand(0, 0x0fff) | 0x4000,
                rand(0, 0x3fff) | 0x8000,
                rand(0, 0xffffffffffff)
            );
        }
    }
}
