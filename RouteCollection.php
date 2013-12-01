<?php

namespace Opis\Routing;

use Closure;

class RouteCollection
{
    protected $routes = array();
    
    protected $bindings = array();
    
    protected $patterns = array();
    
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
    
    public function pattern($name, $value)
    {
        $this->patterns[$name] = $value;
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
    
    public function getBindings(Route $route = null)
    {
        $bindings = ($route === null) ? array() : $route->getBindings();
        return $bindings + $this->bindings;
    }
    
    public function getPatterns(Route $route = null)
    {
        $patterns = ($route === null) ? array() : $route->getWheres();
        return $patterns + $this->patterns;
    }
    
}