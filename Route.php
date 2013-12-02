<?php

namespace Opis\Routing;

use Closure;

class Route
{
    protected $routePath;
    
    protected $routeAction;
    
    protected $placeholders = array();
    
    protected $bindings = array();
    
    protected $def = array();
    
    protected $properties = array();
    
    public function __construct($path, $action)
    {
        $this->routePath = $path;
        $this->routeAction = $action;
    }
    
    public function getPath()
    {
        return $this->routePath;
    }
    
    public function getAction()
    {
        return $this->routeAction;
    }
    
    public function getPlaceholders()
    {
        return $this->placeholders;
    }
    
    public function getBindings()
    {
        return $this->bindings;
    }
    
    public function getDefaults()
    {
        return $this->def;
    }
    
    public function getProperties()
    {
        return $this->properties;
    }
    
    public function bind($name, Closure $value)
    {
        $this->bindings[$name] = $value;
        return $this;
    }
    
    public function placeholder($name, $value)
    {
        $this->placeholders[$name] = $value;
        return  $this;
    }
    
    public function defaults($name, $value)
    {
        $this->def[$name] = $value;
        return $this;
    }
    
    public function set($name, $value)
    {
        $this->properties[$name] = $value;
        return $this;
    }
    
    public function has($name)
    {
        return isset($this->properties[$name]);
    }
    
    public function get($name, $default = null)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : $default;
    }
}