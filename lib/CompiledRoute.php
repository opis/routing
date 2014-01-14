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
        if(!isset($this->cache['route']['pattern']))
        {
            $this->cache['route']['pattern'] = $this->route->getPattern();
        }
        
        return $this->cache['route']['pattern'];
    }
    
    public function wildcards()
    {
        if(!isset($this->cache['route']['wildcards']))
        {
            $this->cache['route']['wildcards'] = $this->route->getWildcards();
        }
        
        return $this->cache['route']['wildcards'];
    }
    
    public function defaults()
    {
        if(!isset($this->cache['route']['defaults']))
        {
            $this->cache['route']['defaults'] = $this->route->getDefaults();
        }
        
        return $this->cache['route']['defaults'];
    }
    
    public function bindings()
    {
        if(!isset($this->cache['route']['bindings']))
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
        $id = (string) $path;
        
        if(!isset($this->cache['values'][$id]))
        {
            $this->cache['values'][$id] = $this->compiler->values($this->compile(), $path);
        }
        
        return $this->cache['values'][$id];
    }
    
    public function extract(Path $path)
    {
        $id = (string) $path;
        
        if(!isset($this->cache['extract'][$id]))
        {
            $this->cache['extract'][$id] = $this->compiler->extract($this->names(), $this->values($path), $this->defaults());
        }
        
        return $this->cache['extract'][$id];
    }
    
    public function bind(Path $path)
    {
        $id = (string) $path;
        
        if(!isset($this->cache['bind'][$id]))
        {
            $this->cache['bind'][$id] = $this->compiler->bind($this->extract($path), $this->bindings());
        }
        
        return $this->cache['bind'][$id];
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