<?php

namespace Opis\Routing;

class Dispatcher implements DispatcherInterface
{
    
    public function dispatch(Path $path, Route $route)
    {   
        return $this->invokeAction($path, $route->getAction(), $route->compile()->bind($path));
    }
    
    protected function invokeAction(Path $path, $action, $values)
    {
        
        if(!is_callable($action))
        {
            throw new \RuntimeException('Route action is not callable');
        }
        
        $callback = new \ReflectionFunction($action);
        
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
        
        return $callback->invokeArgs($arguments);
    }
}