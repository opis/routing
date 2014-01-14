<?php

namespace Opis\Routing;

class CompiledRoute
{
    protected $compiler;
    
    protected $route;
    
    protected $cache = array();
    
    public function __construct(CompilerInterface $compiler, Route $route)
    {
        $this->compiler = $compiler;
        $this->route = $route;
    }
    
    public function pattern()
    {
        if(isset($this->cache['route']['pattern']))
        {
            $this->cache['route']['pattern'] = $this->route->getPattern();
        }
        
        return $this->cache['route']['pattern'];
    }
    
    public function wildcards()
    {
        if(isset($this->cache['route']['wildcards']))
        {
            $this->cache['route']['wildcards'] = $this->route->getWildcards();
        }
        
        return $this->cache['route']['wildcards'];
    }
    
    public function defaults()
    {
        if(isset($this->cache['route']['defaults']))
        {
            $this->cache['route']['defaults'] = $this->route->getDefaults();
        }
        
        return $this->cache['route']['defaults'];
    }
    
    public function bindings()
    {
        if(isset($this->cache['route']['bindings']))
        {
            $this->cache['route']['bindings'] = $this->route->getBindings();
        }
        
        return $this->cache['route']['bindings'];
    }
    
    public function route()
    {
        return $this->route;
    }
    
    public function names()
    {
        if(!isset($this->cache['names']))
        {
            $this->cache['names'] = $this->compiler->names($this->pattern());
        }
        
        return $this->cache['names'];
    }
    
    public function compile()
    {
        if(!isset($this->cache['compiled']))
        {
            $this->cache['compiled'] = $this->compiler->compile($this->pattern(), $this->wildcards());
        }
        
        return $this->cache['compiled'];
    }
    
    public function values(Path $path)
    {
        $path = (string) $path;
        
        if(!isset($this->cache['values'][$path]))
        {
            $this->cache['values'][$path] = $this->compiler->values($this->delimit(), $path);
        }
        
        return $this->cache['values'][$path];
    }
    
    public function extract(Path $path)
    {
        $path = (string) $path;
        
        if(!isset($this->cache['extract'][$path]))
        {
            $this->cache['extract'][$path] = $this->compiler->extract($this->names(), $this->values($path), $this->defaults());
        }
        
        return $this->cache['extract'][$path];
    }
    
    public function bind(Path $path)
    {
        $path = (string) $path;
        
        if(!isset($this->cache['bind'][$path]))
        {
            $this->cache['bind'][$path] = $this->compiler->bind($this->extract($path), $this->bindings());
        }
        
        return $this->cache['bind'];
    }
    
    public function delimit()
    {   
        if(!isset($this->cache['delimit']))
        {
            $this->cache['delimit'] = $this->compiler->delimit($this->compile());
        }
        return $this->cache['delimit'];
    }
    
    public function match(Path $value)
    {
        return preg_match($this->delimit(), (string) $value);
    }
}