<?php

namespace Opis\Routing\Example;

use Opis\Routing\RouteCollection as BaseCollection;
use Closure;

class RouteCollection extends BaseCollection
{
    
    protected $patterns = array();
    
    protected $bindings = array();
    
    public function pattern($name, $pattern)
    {
        $this->patterns[$name] = $pattern;
        return $this;
    }
    
    public function bind($name, Closure $value)
    {
        $this->bindings[$name] = $value;
        return $this;
    }
    
    public function offsetSet($offset, $value)
    {
        parent::offsetSet($offset, $value);
        $value->set('wildcards', $this->patterns);
        $value->set('bindings', $this->bindings);
    }
}