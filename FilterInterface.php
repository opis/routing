<?php

namespace Opis\Routing;

interface FilterInterface
{
    function match(Route $route);
}