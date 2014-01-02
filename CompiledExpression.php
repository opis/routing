<?php

namespace Opis\Routing;

class CompiledExpression
{
    protected $compiler;
    
    protected $pattern;
    
    protected $placeholders;
    
    protected $cache = array();
    
    public function __construct(CompilerInterface $compiler, $pattern, array $placeholders = array())
    {
        $this->compiler = $compiler;
        $this->pattern = $pattern;
        $this->placeholders = $placeholders;
    }
    
    public function pattern()
    {
        return $this->pattern;
    }
    
    public function wildcards()
    {
        return $this->placeholders;
    }
    
    public function names()
    {
        if(!isset($this->cache['names']))
        {
            $this->cache['names'] = $this->compiler->names($this->pattern);
        }
        
        return $this->cache['names'];
    }
    
    public function compile()
    {
        if(!isset($this->cache['compiled']))
        {
            $this->cache['compiled'] = $this->compiler->compile($this->pattern, $this->placeholders);
        }
        
        return $this->cache['compiled'];
    }
    
    public function values($path)
    {
        if(!isset($this->cache['values'][$path]))
        {
            $this->cache['values'][$path] = $this->compiler->values($this->delimit(), $path);
        }
        
        return $this->cache['values'][$path];
    }
    
    public function extract($path, array $defauls = array())
    {
        if(!isset($this->cache['extract'][$path]))
        {
            $this->cache['extract'][$path] = $this->compiler->extract($this->names(), $this->values($path), $defauls);
        }
        
        return $this->cache['extract'][$path];
    }
    
    public function bind($path, array $bindings, array $defaults = array())
    {
        if(!isset($this->cache['bind'][$path]))
        {
            $this->cache['bind'][$path] = $this->compiler->bind($this->extract($path, $defaults), $bindings);
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
    
    public function match($value)
    {
        return preg_match($this->delimit(), $value);
    }
}