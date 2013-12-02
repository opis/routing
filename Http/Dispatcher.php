<?php

namespace Opis\Routing\Http;

use Opis\Routing\Route;
use Opis\Routing\DispatcherInterface;

class Dispatcher implements DispatcherInterface
{
    protected $path;
    
    protected $bindings;
    
    protected $placeholders;
    
    protected $compiler;
    
    public function __construct(Router $router)
    {
        $this->compiler = $router->getCompiler();
        $this->path = $router->getPath();
        $this->bindings = $router->getCollection()->getBindings();
        $this->placeholders = $router->getCollection()->getPlaceholders();
    }
    
    public function dispatch(Route $route)
    {
        $routePath = $route->getPath();
        $placeholders = $route->getPlaceholders() + $this->placeholders;
        $bindings = $route->getBindings() + $this->bindings;
        $expr = $this->compiler->compile($routePath, $placeholders);
        $names = $this->compiler->names($routePath);
        $values = $this->compiler->values($expr, $this->path);
        $values = $this->compiler->extract($names, $values, $route->getDefaults());
        $arguments = $this->compiler->bind($values, $bindings);
        $action = $route->getAction();
        return call_user_func_array($action, $arguments);
    }
}