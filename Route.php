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

use Closure;

class Route
{
    protected $routePath;
    
    protected $routeAction;
    
    protected $wildcards = array();
    
    protected $bindings = array();
    
    protected $def = array();
    
    protected $properties = array();
    
    public function __construct($path, $action)
    {
        $this->routePath = $path;
        $this->routeAction = $action;
    }
    
    public function getPath()
    {
        return $this->routePath;
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
        return $this->def;
    }
    
    public function getProperties()
    {
        return $this->properties;
    }
    
    public function bind($name, Closure $value)
    {
        $this->bindings[$name] = $value;
        return $this;
    }
    
    public function match($name, $value)
    {
        $this->wildcards[$name] = $value;
        return  $this;
    }
    
    public function defaults($name, $value)
    {
        $this->def[$name] = $value;
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