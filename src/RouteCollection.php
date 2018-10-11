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

use Serializable;
use Opis\Pattern\RegexBuilder;
use Opis\Closure\SerializableClosure;

class RouteCollection implements Serializable
{
    /** @var Route[] */
    protected $routes = [];

    /** @var null|string[] */
    protected $regex;

    /** @var string[] */
    protected $namedRoutes = [];

    /** @var  RegexBuilder */
    protected $builder;

    /** @var bool */
    protected $dirty = false;

    /** @var string|null */
    protected $sortKey;

    /** @var bool */
    protected $sortDescending;

    /** @var callable */
    protected $factory;

    /**
     * RouteCollection constructor.
     * @param callable|null $factory
     * @param RegexBuilder|null $builder
     * @param string|null $sortKey
     * @param bool $sortDescending
     */
    public function __construct(
        callable $factory = null,
        RegexBuilder $builder = null,
        string $sortKey = null,
        bool $sortDescending = true
    ) {
        $this->factory = $factory ?? function (
                RouteCollection $collection,
                string $id,
                string $pattern,
                callable $action,
                string $name = null
            ) {
                return new Route($collection, $id, $pattern, $action, $name);
            };
        $this->builder = $builder ?? new RegexBuilder();
        $this->sortKey = $sortKey;
        $this->sortDescending = $sortDescending;
    }

    /**
     * @param string $pattern
     * @param callable $action
     * @param string|null $name
     * @return Route
     */
    public function createRoute(string $pattern, callable $action, string $name = null): Route
    {
        $id = $this->generateRouteId();
        /** @var Route $route */
        $route = ($this->factory)($this, $id, $pattern, $action, $name);
        if (!($route instanceof Route)) {
            throw new \RuntimeException("Invalid return value from factory. Expected instance of " . Route::class);
        }

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
    public function getRoute(string $id)
    {
        return $this->routes[$id] ?? null;
    }

    /**
     * @param string $id
     * @return null|string
     */
    public function getRegex(string $id)
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
        if (!$this->dirty || $this->sortKey === null) {
            return;
        }


        $sortKey = $this->sortKey;
        $descending = $this->sortDescending;

        /** @var string[] $keys */
        $keys = array_reverse(array_keys($this->routes));
        /** @var Route[] $values */
        $values = array_reverse(array_values($this->routes));

        $done = false;

        while (!$done) {
            $done = true;
            for ($i = 0, $l = count($this->routes) - 1; $i < $l; $i++) {

                if ($descending) {
                    $invert = $values[$i]->get($sortKey) < $values[$i + 1]->get($sortKey);
                } else {
                    $invert = $values[$i]->get($sortKey) > $values[$i + 1]->get($sortKey);
                }

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
     * @inheritdoc
     */
    public function serialize()
    {
        $this->sort();
        SerializableClosure::enterContext();
        $object = serialize($this->getSerialize());
        SerializableClosure::exitContext();
        return $object;
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $this->setUnserialize(unserialize($serialized));
    }

    /**
     * @return array
     */
    protected function getSerialize()
    {
        $factory = $this->factory;

        if ($factory instanceof \Closure) {
            $factory = SerializableClosure::from($factory);
        }

        return [
            'builder' => $this->builder,
            'routes' => $this->routes,
            'namedRoutes' => $this->namedRoutes,
            'regex' => $this->getRegexPatterns(),
            'dirty' => $this->dirty,
            'sortKey' => $this->sortKey,
            'sortDescending' => $this->sortDescending,
            'factory' => $factory,
        ];
    }

    /**
     * @param $object
     */
    protected function setUnserialize($object)
    {
        $this->builder = $object['builder'];
        $this->routes = $object['routes'];
        $this->namedRoutes = $object['namedRoutes'];
        $this->regex = $object['regex'];
        $this->dirty = $object['dirty'];
        $this->sortKey = $object['sortKey'];
        $this->sortDescending = $object['sortDescending'];
        $this->factory = $object['factory'];

        if ($this->factory instanceof SerializableClosure) {
            $this->factory = $this->factory->getClosure();
        }
    }

    /**
     * @return string
     */
    protected function generateRouteId(): string
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
