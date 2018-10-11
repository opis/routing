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

use Closure;
use Serializable;
use Opis\Pattern\RegexBuilder;
use Opis\Closure\SerializableClosure;

class Route implements Serializable
{
    use ClosureTrait;

    /** @var  RouteCollection */
    protected $collection;

    /** @var string */
    protected $routePattern;

    /** @var callable */
    protected $routeAction;

    /** @var string|null */
    protected $routeName;

    /** @var string */
    protected $routeID;

    /** @var array */
    protected $placeholders = [];

    /** @var array */
    protected $bindings = [];

    /** @var array */
    protected $defaults = [];


    /**
     * @param RouteCollection $collection
     * @param string $id
     * @param string $pattern
     * @param callable $action
     * @param string|null $name
     */
    public function __construct(
        RouteCollection $collection,
        string $id,
        string $pattern,
        callable $action,
        string $name = null
    ) {
        $this->collection = $collection;
        $this->routeID = $id;
        $this->routePattern = $pattern;
        $this->routeAction = $action;
        $this->routeName = $name;
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->routeID;
    }

    /**
     * Get the route's pattern
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->routePattern;
    }

    /**
     * Get the route's callback
     *
     * @return  callable
     */
    public function getAction(): callable
    {
        return $this->routeAction;
    }

    /**
     * Get the name of the route
     *
     * @return null|string
     */
    public function getName()
    {
        return $this->routeName;
    }

    /**
     * Get the route's wildcards
     *
     * @return  array
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    /**
     * Get the route's bindings
     *
     * @return callable[]
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Get the route's default values
     *
     * @return  array
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * @return RouteCollection
     */
    public function getRouteCollection(): RouteCollection
    {
        return $this->collection;
    }

    /**
     * Bind a value to a name
     *
     * @param   string $name
     * @param   callable $callback
     * @return  static|Route
     */
    public function bind(string $name, callable $callback): self
    {
        $this->bindings[$name] = $callback;
        return $this;
    }

    /**
     * Define a new placeholder
     *
     * @param   string $name
     * @param   string $value
     * @return  static|Route
     */
    public function placeholder(string $name, string $value): self
    {
        $this->placeholders[$name] = $value;
        return $this;
    }

    /**
     * Define a new placeholder
     *
     * @param   string $name
     * @param   string $value
     * @return  static|Route
     */
    public function where(string $name, string $value): self
    {
        return $this->placeholder($name, $value);
    }

    /**
     * @param string $name
     * @param string[] $values
     * @return static|Route
     */
    public function whereIn(string $name, array $values): self
    {
        if (empty($values)) {
            return $this;
        }

        $delimiter = $this->collection->getRegexBuilder()->getOptions()[RegexBuilder::REGEX_DELIMITER];

        $value = implode('|', array_map(function ($value) use ($delimiter) {
            return preg_quote($value, $delimiter);
        }, $values));

        return $this->placeholder($name, $value);
    }

    /**
     * Define a new implicit value
     *
     * @param   string $name
     * @param   mixed $value
     * @return  static|Route
     */
    public function implicit(string $name, $value): self
    {
        $this->defaults[$name] = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        SerializableClosure::enterContext();
        $data = serialize($this->getSerializableData());
        SerializableClosure::exitContext();

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $this->setUnserializedData(unserialize($serialized));
    }

    /**
     * @return array
     */
    protected function getSerializableData(): array
    {
        $routeAction = $this->routeAction;

        if ($routeAction instanceof Closure) {
            $routeAction = SerializableClosure::from($routeAction);
        }

        return [
            'routePattern' => $this->routePattern,
            'routeAction' => $routeAction,
            'routeName' => $this->routeName,
            'routeID' => $this->routeID,
            'placeholders' => $this->placeholders,
            'bindings' => $this->wrapClosures($this->bindings),
            'defaults' => $this->wrapClosures($this->defaults),
            'collection' => $this->collection,
        ];
    }

    /**
     * @param array $data
     */
    protected function setUnserializedData(array $data)
    {
        if ($data['routeAction'] instanceof SerializableClosure) {
            $data['routeAction'] = $data['routeAction']->getClosure();
        }

        $this->routePattern = $data['routePattern'];
        $this->routeAction = $data['routeAction'];
        $this->routeName = $data['routeName'];
        $this->routeID = $data['routeID'];
        $this->placeholders = $data['placeholders'];
        $this->bindings = $this->unwrapClosures($data['bindings']);
        $this->defaults = $this->unwrapClosures($data['defaults']);
        $this->collection = $data['collection'];
    }
}
