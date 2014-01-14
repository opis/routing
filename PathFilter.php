<?php

namespace Opis\Routing;

class PathFilter implements FilterInterface
{
    public function pass(Path $path, Route $route)
    {
        return $route->compile()->match($path);
    }
}