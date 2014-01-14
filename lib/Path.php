<?php

namespace Opis\Routing;

class Path
{
    protected $path;
    
    public function __construct($path)
    {
        $this->path = $path;
    }
    
    public function __toString()
    {
        return $this->path;
    }
}