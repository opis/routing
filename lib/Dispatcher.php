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

use Opis\Routing\Contracts\DispatcherInterface;
use Opis\Routing\Contracts\PathInterface;
use Opis\Routing\Contracts\RouteInterface;

class Dispatcher implements DispatcherInterface
{
    
    public function dispatch(PathInterface $path, RouteInterface $route)
    {
        return $this->invokeAction($route->getAction(), $route->compile()->bind($path));
    }
    
    public function invokeAction(callable $action, array $values = array())
    {
        $isMethod = true;
        $that = null;
        
        if(is_string($action) && strpos($action, '::') !== false)
        {
            $action = explode('::', $action);
        }
        
        if(is_object($action) && !($action instanceof \Closure))
        {
            $callback = new \ReflectionMethod(get_class($action), '__invoke');
        }
        elseif(is_array($action))
        {
            $that = $action[0];
            
            if(is_string($that))
            {
                $className =  $that;
                $that = null;
            }
            else
            {
                $className = get_class($that);
            }
            $callback = new \ReflectionMethod($className, $action[1]);
        }
        else
        {
            $callback = new \ReflectionFunction($action);
            $isMethod = false;
        }
        
        $parameters = $callback->getParameters();
        $arguments = array();
        
        foreach($parameters as $param)
        {
            $name = $param->getName();
            if(isset($values[$name]))
            {
                $arguments[] = $values[$name];
                unset($values[$name]);
            }
            elseif($param->isOptional())
            {
                $arguments[] = $param->getDefaultValue();
            }
            else
            {
                $arguments[] = null;
            }
        }
        
        $arguments += $values;
        
        return $isMethod ? $callback->invokeArgs($that, $arguments) : $callback->invokeArgs($arguments);
    }
}