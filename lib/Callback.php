<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013-2015 Marius Sarca
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
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;

class Callback
{
    protected $thisObject;
    protected $isMethod = false;
    protected $reflection;
    
    public function __construct($callback)
    {
        if(!is_callable($callback))
        {
            throw new InvalidArgumentException('$callback must be a valid callable value');
        }
        
        $reflection = null;
        
        if(is_array($callback))
        {
            list($object, $method) = $callback;
            
            $this->thisObject = is_string($object) ? null : $object;
            $this->reflection = new ReflectionMethod($object, $method);
            $this->isMethod = true;
        }
        elseif(is_string($callback))
        {
            if(function_exists($callback))
            {
                $this->reflection = new ReflectionFunction($callback);
            }
            else
            {
                $this->reflection = new ReflectionMethod($callback);
                $this->isMethod = true;
            }
        }
        elseif($callback instanceof Closure)
        {
            $this->reflection = new ReflectionFunction($callback);
        }
        else
        {
            $this->reflection = new ReflectionMethod($callback, "__invoke");
            $this->thisObject = $callback;
            $this->isMethod = true;
        }
        
    }
    
    public function isMethod()
    {
        return $this->isMethod;
    }
    
    public function getThisObject()
    {
        return $this->thisObject;
    }
    
    public function getReflection()
    {
        return $this->reflection;
    }
    
    public function getParameters()
    {
        return $this->reflection->getParameters();
    }
    
    public function invoke($arguments = array())
    {
        if($this->isMethod)
        {
            return $this->reflection->invokeArgs($this->thisObject, $arguments);
        }
        
        return $this->reflection->invokeArgs($arguments);
    }
    
}
