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
use ReflectionFunction;
use ReflectionMethod;

class Callback
{
    /** @var    mixed */
    protected $thisObject;

    /** @var    boolean */
    protected $isMethod = false;

    /** @var    \ReflectionFunction|\ReflectionMethod */
    protected $reflection;

    /**
     * Constructor
     * 
     * @param   callable    $callback
     * 
     * @throws  CallableExpectedException
     */
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new CallableExpectedException();
        }

        if (is_array($callback)) {
            list($object, $method) = $callback;
            $this->thisObject = is_string($object) ? null : $object;
            $this->reflection = new ReflectionMethod($object, $method);
            $this->isMethod = true;
        } elseif (is_string($callback)) {
            if (function_exists($callback)) {
                $this->reflection = new ReflectionFunction($callback);
            } else {
                $this->reflection = new ReflectionMethod($callback);
                $this->isMethod = true;
            }
        } elseif ($callback instanceof Closure) {
            $this->reflection = new ReflectionFunction($callback);
        } else {
            $this->reflection = new ReflectionMethod($callback, "__invoke");
            $this->thisObject = $callback;
            $this->isMethod = true;
        }
    }

    /**
     * 
     * @return  boolean
     */
    public function isMethod()
    {
        return $this->isMethod;
    }

    /**
     * 
     * @return  mixed
     */
    public function getThisObject()
    {
        return $this->thisObject;
    }

    /**
     * 
     * @return  \ReflectionFunction|\ReflectionMethod
     */
    public function getReflection()
    {
        return $this->reflection;
    }

    /**
     * 
     * @return  array
     */
    public function getParameters()
    {
        return $this->reflection->getParameters();
    }

    /**
     * 
     * @param   array   $values
     * @param   array   $specials   (optional)
     * @param   boolean $bind       (optional)
     * 
     * @return  array
     */
    public function getArguments(array $values, array $specials = array(), $bind = true)
    {
        $arguments = array();
        $parameters = $this->getParameters();

        foreach ($parameters as $param) {

            $name = $param->getName();

            if (isset($values[$name])) {
                $arguments[] = $bind ? $values[$name]->value() : $values[$name];
            } elseif (isset($specials[$name])) {
                $arguments[] = $specials[$name];
            } elseif ($param->isOptional()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                $arguments[] = null;
            }
        }
        
        return $arguments;
    }

    /**
     * 
     * @param   array   $arguments
     * 
     * @return  mixed
     */
    public function invoke($arguments = array())
    {
        if ($this->isMethod) {
            return $this->reflection->invokeArgs($this->thisObject, $arguments);
        }

        return $this->reflection->invokeArgs($arguments);
    }
}
