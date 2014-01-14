<?php

namespace Opis\Routing;

class CompiledPattern
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