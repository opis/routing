<?php

namespace Opis\Routing;

class DispatcherResolver
{
    public function resolve(Path $path, Route $route)
    {
        return new Dispatcher();
    }
}