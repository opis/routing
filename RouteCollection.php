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

class RouteCollection
{
    protected $routes = array();
    
    protected $bindings = array();
    
    protected $placeholders = array();
    
    protected $filters = array();
    
    public function add(Route $route)
    {
        $this->routes[] = $route;
        return $this;
    }
    
    public function bind($name, Closure $value)
    {
        $this->bindings[$name] = $value;
        return $this;
    }
    
    public function placeholder($name, $value)
    {
        $this->placeholders[$name] = $value;
        return $this;
    }
    
    public function filter($name, Closure $filter)
    {
        $this->filters[$name] = $filter;
        return $this;
    }
    
    public function getRoutes()
    {
        return $this->routes;
    }
    
    public function getFilters()
    {
        return $this->filters;
    }
    
    public function getBindings()
    {
        return $this->bindings;
    }
    
    public function getPlaceholders()
    {
        return $this->placeholders;
    }
    
}