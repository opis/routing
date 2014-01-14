<?php

namespace Opis\Routing;

class Dispatcher implements DispatcherInterface
{
    
    public function dispatch(Path $path, Route $route)
    {
        $action = $route->getAction();
        
        if(!is_callable($action))
        {
            throw new \RuntimeException('Route action is not callable');
        }
        
        return $this->invokeAction($path, $action);
    }
    
    protected function invokeAction(Path $path, $action)
    {
        $callback = new \ReflectionFunction($action);
        
        $parameters = $callback->getParameters();
        $values  = $route->compile()->bind($path);
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