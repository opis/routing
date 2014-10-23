<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013-2014 Marius Sarca
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
use Opis\Closure\SerializableClosure;
use Opis\Routing\Contracts\RouteInterface;
use Opis\Routing\Contracts\PatternInterface;
use Opis\Routing\Contracts\CompilerInterface;

class Route implements RouteInterface
{
    protected $routePattern;
    
    protected $routeAction;
    
    protected $compiledRoute;
    
    protected $compiledPattern;
    
    protected $delimitedPattern;
    
    protected $wildcards = array();
    
    protected $bindings = array();
    
    protected $defaults = array();
    
    protected $properties = array();
    
    public function __construct(PatternInterface $pattern, Closure $action)
    {
        $this->routePattern = $pattern;
        $this->routeAction = $action;
        $this->defaults['self'] = $this;
        $this->defaults['path'] = null;
    }
    
    public function getPattern()
    {
        return $this->routePattern;
    }
    
    public function getAction()
    {
        return $this->routeAction;
    }
    
    public function getWildcards()
    {
        return $this->wildcards;
    }
    
    public function getBindings()
    {
        return $this->bindings;
    }
    
    public function getDefaults()
    {
        return $this->defaults;
    }
    
    public function getProperties()
    {
        return $this->properties;
    }
    
    public function getCompiledPattern()
    {
        if(is_string($this->compiledPattern))
        {
            $this->compiledPattern = new CompiledPattern($this->compiledPattern);
        }
        
        return $this->compiledPattern;
    }
    
    public function getDelimitedPattern()
    {
        if($this->delimitedPattern === null)
        {
            $this->delimitedPattern = $this->compile()->delimit();
        }
        
        return $this->delimitedPattern;
    }
    
    public static function getCompiler()
    {
        static $compiler = null;
        
        if($compiler === null)
        {
            $compiler = new Compiler();
        }
        
        return $compiler;
    }
    
    public function compile()
    {
        if($this->compiledRoute === null)
        {
           $this->compiledRoute = new CompiledRoute($this);
        }
        
        return $this->compiledRoute;
    }
    
    public function bind($name, Closure $callback)
    {
        $this->bindings[$name] = $callback;
        return $this;
    }
    
    public function wildcard($name, $value)
    {
        $this->wildcards[$name] = $value;
        return  $this;
    }
    
    public function implicit($name, $value)
    {
        $this->defaults[$name] = $value;
        return $this;
    }
    
    public function set($name, $value)
    {
        $this->properties[$name] = $value;
        return $this;
    }
    
    public function has($name)
    {
        return isset($this->properties[$name]);
    }
    
    public function get($name, $default = null)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : $default;
    }
    
    public function __get($name)
    {
        return $this->get($name);
    }
    
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }
    
    public function __call($name, $arguments)
    {
        return $this->set($name, array_shift($arguments));
    }
    
    public function serialize()
    {
        
        $map = function(&$value) use(&$map){
            
            if($value instanceof Closure)
            {
                return SerializableClosure::from($value);
            }
            elseif(is_array($value))
            {
                return array_map($map, $value);
            }
            elseif($value instanceof \stdClass)
            {
                $object = (array) $value;
                $object = array_map($map, $object);
                return (object) $object;
            }
            return $value;
        };
        
        $path = $this->defaults['path'];
        unset($this->defaults['path'], $this->defaults['self']);
        
        $compiledPatter = $this->getCompiler()->compile($this->routePattern, $this->wildcards);
        
        SerializableClosure::enterContext();
        
        $object = serialize(array(
            'routePattern' => $this->routePattern,
            'routeAction' => SerializableClosure::from($this->routeAction),
            'compiledPattern' => $compiledPatter,
            'delimitedPattern' => $this->getCompiler()->delimit($compiledPatter),
            'wildcards' => $this->wildcards,
            'bindings' => array_map($map, $this->bindings),
            'defaults' => array_map($map, $this->defaults),
            'properties' => array_map($map, $this->properties),
        ));
        
        SerializableClosure::exitContext();
        
        $this->defaults['self'] = $this;
        $this->defaults['path'] = $path;
        
        return $object;
    }
    
    public function unserialize($data)
    {
        $object = SerializableClosure::unserializeData($data);
        
        $map = function(&$value) use(&$map){
            
            if($value instanceof SerializableClosure)
            {
                return $value->getClosure();
            }
            elseif(is_array($value))
            {
                return array_map($map, $value);
            }
            elseif($value instanceof \stdClass)
            {
                $object = (array) $value;
                $object = array_map($map, $object);
                return (object) $object;
            }
            return $value;
        };
        
        $this->routePattern = $object['routePattern'];
        $this->routeAction = $object['routeAction']->getClosure();
        $this->delimitedPattern = $object['delimitedPattern'];
        $this->compiledPattern = $object['compiledPattern'];
        $this->wildcards = $object['wildcards'];
        $this->bindings = array_map($map, $object['bindings']);
        $this->defaults = array_map($map, $object['defaults']);
        $this->properties = array_map($map, $object['properties']);
        $this->defaults['self'] = $this;
        $this->defaults['path'] = null;
    }
}
