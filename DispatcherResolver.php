<?php

namespace Opis\Routing;

class DispatcherResolver implements DispatcherResolverInterface
{
    public function resolve(Path $path, Route $route)
    {
        return new Dispatcher();
    }
}