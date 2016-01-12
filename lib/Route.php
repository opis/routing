<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013-2016 Marius Sarca
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
    /** @var    \Opis\Routing\Pattern */
    protected $routePattern;

    /** @var    callable */
    protected $routeAction;

    /** @var    \Opis\Routing\CompiledRoute */
    protected $compiledRoute;

    /** @var    \Opis\Routing\CompiledPattern */
    protected $compiledPattern;

    /** @var    string */
    protected $delimitedPattern;

    /** @var    array */
    protected $wildcards = array();

    /** @var    array */
    protected $bindings = array();

    /** @var    array */
    protected $defaults = array();

    /** @var    array */
    protected $properties = array();

    /** @var    \Opis\Routing\Router */
    protected $router;

    /**
     * Constructor
     * 
     * @param   \Opis\Routing\Pattern   $pattern
     * @param   callable                $action
     * 
     * @throws  \Opis\Routing\CallableExpectedException
     */
    public function __construct(Pattern $pattern, $action)
    {
        if (!is_callable($action)) {
            throw new CallableExpectedException();
        }

        $this->routePattern = $pattern;
        $this->routeAction = $action;
    }

    /**
     * Get the route's pattern
     * 
     * @return  \Opis\Routing\Pattern
     */
    public function getPattern()
    {
        return $this->routePattern;
    }

    /**
     * Get the route's callback
     * 
     * @return  callable
     */
    public function getAction()
    {
        return $this->routeAction;
    }

    /**
     * Get the route's wildcards
     * 
     * @return  array
     */
    public function getWildcards()
    {
        return $this->wildcards;
    }

    /**
     * Get the route's bindings
     * 
     * @return  array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Get the route's default values
     * 
     * @return  array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Get the route's properties
     * 
     * @return  array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Get the route's compiler
     * 
     * @staticvar   \Opis\Routing\Compiler  $compiler
     * 
     * @return      \Opis\Routing\Compiler
     */
    public function getCompiler()
    {
        static $compiler;

        if ($compiler === null) {
            $compiler = new Compiler();
        }

        return $compiler;
    }

    /**
     * Get the compiled pattern of this route
     * 
     * @return  string|null
     */
    public function getCompiledPattern()
    {
        return $this->compiledPattern;
    }

    /**
     * Get the delimited pattern of this route
     * 
     * @return  string|null
     */
    public function getDelimitedPattern()
    {
        if ($this->delimitedPattern === null) {
            $this->delimitedPattern = $this->compile()->delimit();
        }

        return $this->delimitedPattern;
    }

    /**
     * Compile this route
     * 
     * @return  \Opis\Routing\CompiledRoute
     */
    public function compile()
    {
        if ($this->compiledRoute === null) {
            $this->compiledRoute = new CompiledRoute($this);
        }

        return $this->compiledRoute;
    }

    /**
     * Bind a value to a name
     * 
     * @param   string      $name
     * @param   callable    $callback
     * 
     * @return  $this
     * 
     * @throws  \Opis\Routing\CallableExpectedException
     */
    public function bind($name, $callback)
    {
        if (!is_callable($callback)) {
            throw new CallableExpectedException();
        }

        $this->bindings[$name] = $callback;
        return $this;
    }

    /**
     * Define a new wildcard
     * 
     * @param   string  $name
     * @param   string  $value
     * 
     * @return  $this
     */
    public function wildcard($name, $value)
    {
        $this->wildcards[$name] = $value;
        return $this;
    }

    /**
     * Define a new implicit value
     * 
     * @param   string  $name
     * @param   mixed   $value
     * 
     * @return  $this
     */
    public function implicit($name, $value)
    {
        $this->defaults[$name] = $value;
        return $this;
    }

    /**
     * Set a property
     * 
     * @param   string  $name
     * @param   mixed   $value
     * 
     * @return  $this
     */
    public function set($name, $value)
    {
        $this->properties[$name] = $value;
        return $this;
    }

    /**
     * Chack if a property was set
     * 
     * @param   string  $name
     * 
     * @return  boolean
     */
    public function has($name)
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
    public function get($name, $default = null)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : $default;
    }

    /**
     * Get a property
     * 
     * @param   string  $name
     * 
     * @return  mixed
     */
    public function __get($name)
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
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Set a property
     * 
     * @param   string  $name
     * @param   array   $arguments
     * 
     * @return  $this
     */
    public function __call($name, $arguments)
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

        $map = array($this, 'wrapClosures');
        $croute = $this->compile();
        
        $object = serialize(array(
            'routePattern' => $this->routePattern,
            'routeAction' => $routeAction,
            'compiledPattern' => $croute->compile(),
            'delimitedPattern' => $croute->delimit(),
            'wildcards' => $this->wildcards,
            'bindings' => array_map($map, $this->bindings),
            'defaults' => array_map($map, $this->defaults),
            'properties' => array_map($map, $this->properties),
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
        $object = SerializableClosure::unserializeData($data);

        if ($object['routeAction'] instanceof SerializableClosure) {
            $object['routeAction'] = $object['routeAction']->getClosure();
        }

        $map = array($this, 'unwrapClosures');

        $this->routePattern = $object['routePattern'];
        $this->routeAction = $object['routeAction'];
        $this->delimitedPattern = $object['delimitedPattern'];
        $this->compiledPattern = $object['compiledPattern'];
        $this->wildcards = $object['wildcards'];
        $this->bindings = array_map($map, $object['bindings']);
        $this->defaults = array_map($map, $object['defaults']);
        $this->properties = array_map($map, $object['properties']);
    }

    /**
     * Wrap all closures
     * 
     * @param   mixed   $value
     * 
     * @return  mixed
     */
    protected function wrapClosures(&$value)
    {
        if ($value instanceof Closure) {
            return SerializableClosure::from($value);
        } elseif (is_array($value)) {
            return array_map(array($this, __FUNCTION__), $value);
        } elseif ($value instanceof \stdClass) {
            $object = (array) $value;
            $object = array_map(array($this, __FUNCTION__), $object);
            return (object) $object;
        }
        return $value;
    }

    /**
     * Unwrap closures
     * 
     * @param   mixed   $value
     * 
     * @return  mixed
     */
    protected function unwrapClosures(&$value)
    {
        if ($value instanceof SerializableClosure) {
            return $value->getClosure();
        } elseif (is_array($value)) {
            return array_map(array($this, __FUNCTION__), $value);
        } elseif ($value instanceof \stdClass) {
            $object = (array) $value;
            $object = array_map(array($this, __FUNCTION__), $object);
            return (object) $object;
        }
        return $value;
    }
}
