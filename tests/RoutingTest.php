<?php

namespace Opis\Routing\Test;

use Opis\Routing\Path;
use Opis\Routing\Route;
use Opis\Routing\RouteCollection;
use Opis\Routing\Router;
use PHPUnit\Framework\TestCase;

class RoutingTest extends  TestCase
{
    /** @var  RouteCollection */
    protected $routes;
    /** @var  Router */
    protected $router;

    public function setUp()
    {
        $this->routes = new RouteCollection();
        $this->router = new Router($this->routes);
    }

    public function tearDown()
    {
        $this->routes = new RouteCollection();
        $this->router = new Router($this->routes);
    }

    public function testBasicRouting()
    {
        $this->routes->addRoute(new Route('/foo', function (){
            return 'ok';
        }));

        $this->assertEquals('ok', $this->router->route(new Path('/foo')));
    }

    public function testRouteArgument()
    {
        $this->routes->addRoute(new Route('/foo/{bar}', function ($bar){
            return $bar;
        }));

        $this->assertEquals('baz', $this->router->route(new Path('/foo/baz')));
    }

    public function testOptionalArgument()
    {
        $this->routes->addRoute(new Route('/foo/{bar?}', function ($bar = 'baz'){
            return $bar;
        }));

        $this->assertEquals('baz', $this->router->route(new Path('/foo')));
    }

    public function testImplicitArgument()
    {
        $route = (new Route('/foo/{bar?}', function ($bar){
            return $bar;
        }))->implicit('bar', 'baz');

        $this->routes->addRoute($route);

        $this->assertEquals('baz', $this->router->route(new Path('/foo')));
    }

    public function testMultipleArguments()
    {
        $this->routes->addRoute(new Route('/{foo}/{bar}', function ($bar, $foo){
            return $foo.$bar;
        }));

        $this->assertEquals('bazqux', $this->router->route(new Path('/baz/qux')));
    }

    public function testWildcardArgument()
    {
        $route = (new Route('/foo/{bar}', function ($bar){
            return $bar;
        }))->where('bar', '[0-9]+');

        $this->routes->addRoute($route);

        $this->assertEquals(false, $this->router->route(new Path('/foo/bar')));
        $this->assertEquals('123', $this->router->route(new Path('/foo/123')));
    }

    public function testBindArgument()
    {
        $route = (new Route('/foo/{bar}', function ($bar){
            return $bar;
        }))->bind('bar', function($bar){
            return strtoupper($bar);
        });

        $this->routes->addRoute($route);

        $this->assertEquals('BAR', $this->router->route(new Path('/foo/bar')));
    }

}