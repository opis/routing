<?php

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