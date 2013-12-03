<?php

namespace Opis\Routing\Example;

use Opis\Routing\Route as BaseRoute;
use Closure;

class Route extends BaseRoute
{
    
    public function __construct($path, Closure $action)
    {
        parent::__construct($path, $action);
    }
    
    public function where($name, $value)
    {
        return $this->match($name, $value);
    }
    
    public static function create($path, Closure $action)
    {
        return new static($path, $action);
    }
    
}