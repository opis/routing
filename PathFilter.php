<?php

namespace Opis\Routing;

class PathFilter implements FilterInterface
{
    protected $compiler;
    
    protected $path;
    
    public function __construct($path, CompilerInterface $compiler)
    {
        $this->compiler = $compiler;
        $this->path = $path;
    }
    
    public function match(Route $route)
    {
        $pattern = $this->compiler->compile($route->getPath(), $route->getPlaceholders());
        return preg_match($pattern, $this->path);
    }
    
}