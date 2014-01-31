<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013 Marius Sarca
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

use Opis\Routing\Contracts\RouteInterface;
use Opis\Routing\Contracts\PatternInterface;
use Opis\Routing\Contracts\CompilerInterface;

class Route implements RouteInterface
{
    protected $routePattern;
    
    protected $routeAction;
    
    protected $compiler;
    
    protected $compiledRoute;
    
    protected $wildcards = array();
    
    protected $bindings = array();
    
    protected $defaults = array();
    
    protected $properties = array();
    
    public function __construct(PatternInterface $pattern, $action, CompilerInterface $compiler = null)
    {
        $this->routePattern = $pattern;
        $this->routeAction = $action;
        $this->compiler = $compiler;
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
    
    public function getCompiler()
    {
        if($this->compiler === null)
        {
            $this->compiler = new Compiler();
        }
        
        return $this->compiler;
    }
    
    public function compile()
    {
        if($this->compiledRoute === null)
        {
           $this->compiledRoute = new CompiledRoute($this);
        }
        
        return $this->compiledRoute;
    }
    
    public function bind($name, $callback)
    {
        if(is_callable($callback))
        {
            $this->bindings[$name] = $callback;
        }
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
}