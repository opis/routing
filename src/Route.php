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

use Closure;
use Serializable;
use Opis\Closure\SerializableClosure;

class Route implements Serializable
{
    use ClosureWrapperTrait;

    /** @var  RouteCollection */
    protected $collection;

    /** @var string */
    protected $routePattern;

    /** @var callable */
    protected $routeAction;

    /** @var  string|null */
    protected $routeName;

    /** @var  string|null */
    protected $routeID;

    /** @var    array */
    protected $wildcards = array();

    /** @var    array */
    protected $bindings = array();

    /** @var    array */
    protected $defaults = array();

    /** @var    array */
    protected $properties = array();

    /**
     * Route constructor
     *
     * @param string $pattern
     * @param callable $action
     * @param string|null $name
     */
    public function __construct(string $pattern, callable $action, string $name = null)
    {
        $this->routePattern = $pattern;
        $this->routeAction = $action;
        $this->routeName = $name;
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        if($this->routeID === null){
            $this->routeID = sprintf('%012x%04x%04x%012x',
                random_int(0, 0xffffffffffff),
                random_int(0, 0x0fff) | 0x4000,
                random_int(0, 0x3fff) | 0x8000,
                random_int(0, 0xffffffffffff)
            );
        }

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
    public function getWildcards(): array
    {
        return $this->wildcards;
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
     * Get the route's properties
     * 
     * @return  array
     */
    public function getProperties(): array 
    {
        return $this->properties;
    }

    /**
     * @param RouteCollection $collection
     * @return Route
     */
    public function setRouteCollection(RouteCollection $collection): self
    {
        $this->collection = $collection;
        return $this;
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
     * @return $this|Route
     */
    public function bind(string $name, callable $callback): self
    {
        $this->bindings[$name] = $callback;
        return $this;
    }

    /**
     * Define a new wildcard
     *
     * @param   string $name
     * @param   string $value
     * @return $this|Route
     */
    public function wildcard(string $name, string $value): self
    {
        $this->wildcards[$name] = $value;
        return $this;
    }

    /**
     * Define a new wildcard
     *
     * @param   string $name
     * @param   string $value
     * @return $this|Route
     */
    public function where(string $name, string $value): self
    {
        return $this->wildcard($name, $value);
    }

    /**
     * Define a new implicit value
     *
     * @param   string $name
     * @param   mixed $value
     * @return $this|Route
     */
    public function implicit(string $name, $value): self
    {
        $this->defaults[$name] = $value;
        return $this;
    }

    /**
     * Set a property
     *
     * @param   string $name
     * @param   mixed $value
     * @return $this|Route
     */
    public function set(string $name, $value): self
    {
        $this->properties[$name] = $value;
        return $this;
    }

    /**
     * Check if a property was set
     * 
     * @param   string  $name
     * 
     * @return  boolean
     */
    public function has(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    /**
     * Get a property
     * 
     * @param   string  $name
     * @param   mixed   $default    (optional)
     * 
     * @return  mixed
     */
    public function get(string $name, $default = null)
    {
        return $this->properties[$name] ?? $default;
    }

    /**
     * Get a property
     * 
     * @param   string  $name
     * 
     * @return  mixed
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Set a property
     * 
     * @param   string  $name
     * @param   mixed   $value
     * 
     * @return  $this
     */
    public function __set(string $name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Set a property
     *
     * @param   string $name
     * @param   array $arguments
     * @return $this|Route
     */
    public function __call(string $name, array $arguments): self 
    {
        return $this->set($name, array_shift($arguments));
    }

    /**
     * Serialize the route
     * 
     * @return  string
     */
    public function serialize()
    {
        SerializableClosure::enterContext();

        $routeAction = $this->routeAction;

        if ($routeAction instanceof Closure) {
            $routeAction = SerializableClosure::from($routeAction);
        }

        $map = array(static::class, 'wrapClosures');
        
        $object = serialize(array(
            'routePattern' => $this->routePattern,
            'routeAction' => $routeAction,
            'routeName' => $this->routeName,
            'routeID' => $this->routeID,
            'wildcards' => $this->wildcards,
            'bindings' => array_map($map, $this->bindings),
            'defaults' => array_map($map, $this->defaults),
            'properties' => array_map($map, $this->properties),
            'collection' => $this->collection,
        ));

        SerializableClosure::exitContext();

        return $object;
    }

    /**
     * Unserialize the route
     * 
     * @param   string  $data
     */
    public function unserialize($data)
    {
        $object = unserialize($data);

        if ($object['routeAction'] instanceof SerializableClosure) {
            $object['routeAction'] = $object['routeAction']->getClosure();
        }

        $map = array(static::class, 'unwrapClosures');

        $this->routePattern = $object['routePattern'];
        $this->routeAction = $object['routeAction'];
        $this->routeName = $object['routeName'];
        $this->routeID = $object['routeID'];
        $this->wildcards = $object['wildcards'];
        $this->bindings = array_map($map, $object['bindings']);
        $this->defaults = array_map($map, $object['defaults']);
        $this->properties = array_map($map, $object['properties']);
        $this->collection = $object['collection'];
    }

}
