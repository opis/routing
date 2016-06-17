<?php

namespace Opis\Routing\Test;

use Opis\Routing\Compiler;
use Opis\Routing\Path;
use Opis\Routing\Route;
use Opis\Routing\RouteCollection;
use Opis\Routing\Router;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    public function testRouting()
    {
        $routes = new RouteCollection();
        $routes->addRoute(new Route('/foo/{bar}/{car?}', function ($bar){
            return $bar;
        }));
        $router = new Router($routes);
        var_dump($router->route(new Path('/foo/dar')));die;
    }
}