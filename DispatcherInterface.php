<?php

namespace Opis\Routing;

interface DispatcherInterface
{
    function dispatch(Route $route);
}