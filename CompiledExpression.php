<?php

namespace Opis\Routing;

class CompiledExpression
{
    protected $compiler;
    
    protected $pattern;
    
    protected $placeholders;
    
    protected $cache = array();
    
    public function __construct(Compiler $compiler, $pattern, array $placeholders = array())
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
    
    public function values()
    {
        if(!isset($this->cache['values']))
        {
            $this->cache['values'] = $this->compiler->values($this->compile(), $this->pattern);
        }
        
        return $this->cache['values'];
    }
    
    public function extract(array $defauls = array())
    {
        if(!isset($this->cache['extract']))
        {
            $this->cache['extract'] = $this->compiler->extract($this->names(), $this->values(), $defauls);
        }
        
        return $this->cache['extract'];
    }
    
    public function bind(array $bindings = array())
    {
        if(!isset($this->cache['bind']))
        {
            $this->cache['bind'] = $this->compiler->bind($this->values(), $bindings);
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
}