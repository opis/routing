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

use Iterator;
use ArrayAccess;

class RouteCollection implements Iterator, ArrayAccess
{
    protected $routes = array();
    
    public function rewind()
    {
        return reset($this->routes);
    }
    
    public function current()
    {
        return current($this->routes);
    }
    
    public function key()
    {
        return key($this->routes);
    }
    
    public function next()
    {
        return next($this->routes);
    }
    
    public function valid()
    {
        return key($this->routes) !== null;
    }
    
    public function offsetSet($offset, $value)
    {
        $this->check($value);
        
        if (is_null($offset))
        {
            $this->routes[] = $value;
        }
        else
        {
            $this->routes[$offset] = $value;
        }
    }
    
    public function offsetExists($offset)
    {
        return isset($this->routes[$offset]);
    }
    
    public function offsetUnset($offset)
    {
        unset($this->routes[$offset]);
    }
    
    public function offsetGet($offset)
    {
        return isset($this->routes[$offset]) ? $this->routes[$offset] : null;
    }
    
    public function toArray()
    {
        return $this->routes;
    }
    
    protected function check(Route $value)
    {
        
    }
    
}