<?php

namespace Opis\Routing;

class Pattern
{
    protected $pattern;
    
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }
    
    public function __toString()
    {
        return $this->pattern;
    }
    
}