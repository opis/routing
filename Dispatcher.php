<?php

namespace Opis\Routing;

class Dispatcher implements DispatcherInterface
{
    
    protected $compiler;
    
    public function __construct(CompilerInterface $compiler = null)
    {
        if($compiler === null)
        {
            $compiler = new Compiler();
        }
        
        $this->compiler = $compiler;
    }
    
    public function dispatch(Path $path, Route $route)
    {
        $expression = new CompiledExpression($this->compiler, $route->getPattern(), $route->getWildcards());
        $arguments = $expression->bind($path, $route->getBindings(), $route->getDefaults());
        $action = $route->getAction();
        
        if(!is_callable($action))
        {
            throw new \RuntimeException('Route action is not callable');
        }
        
        return call_user_func_array($action, $arguments);
    }
    
}
