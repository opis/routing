<?php
/* ===========================================================================
 * Copyright 2013-2016 The Opis Project
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Routing\Test;

use Opis\Routing\Compiler;
use Opis\Routing\Context;
use Opis\Routing\Dispatcher;
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

    /** @var  Dispatcher */
    protected $dispatcher;

    public function setUp()
    {
        $this->routes = new RouteCollection();
        $this->dispatcher = new Dispatcher();
        $this->router = new Router($this->dispatcher, $this->routes);
    }

    public function tearDown()
    {
        $this->routes = new RouteCollection();
        $this->router = new Router($this->dispatcher, $this->routes);
    }

    public function testBasicRouting()
    {
        $this->routes->addRoute(new Route('/foo', function (){
            return 'ok';
        }));

        $this->assertEquals('ok', $this->router->route(new Context('/foo')));
    }

    public function testRouteArgument()
    {
        $this->routes->addRoute(new Route('/foo/{bar}', function ($bar){
            return $bar;
        }));

        $this->assertEquals('baz', $this->router->route(new Context('/foo/baz')));
    }

    public function testOptionalArgument()
    {
        $this->routes->addRoute(new Route('/foo/{bar?}', function ($bar = 'baz'){
            return $bar;
        }));

        $this->assertEquals('baz', $this->router->route(new Context('/foo')));
    }

    public function testImplicitArgument()
    {
        $route = (new Route('/foo/{bar?}', function ($bar){
            return $bar;
        }))->implicit('bar', 'baz');

        $this->routes->addRoute($route);

        $this->assertEquals('baz', $this->router->route(new Context('/foo')));
    }

    public function testMultipleArguments()
    {
        $this->routes->addRoute(new Route('/{foo}/{bar}', function ($bar, $foo){
            return $foo.$bar;
        }));

        $this->assertEquals('bazqux', $this->router->route(new Context('/baz/qux')));
    }

    public function testWildcardArgument()
    {
        $route = (new Route('/foo/{bar}', function ($bar){
            return $bar;
        }))->where('bar', '[0-9]+');

        $this->routes->addRoute($route);

        $this->assertEquals(null, $this->router->route(new Context('/foo/bar')));
        $this->assertEquals('123', $this->router->route(new Context('/foo/123')));
    }

    public function testBindArgument()
    {
        $route = (new Route('/foo/{bar}', function ($bar){
            return $bar;
        }))->bind('bar', function($bar){
            return strtoupper($bar);
        });

        $this->routes->addRoute($route);

        $this->assertEquals('BAR', $this->router->route(new Context('/foo/bar')));
    }

    public function testSerialization()
    {
        $route = (new Route('/foo/{bar}', function ($bar){
            return $bar;
        }))->bind('bar', function($bar){
            return strtoupper($bar);
        });

        $routes = new RouteCollection();
        $routes->addRoute($route);
        $router = new Router($this->dispatcher, unserialize(serialize($routes)));
        $this->assertEquals('BAR', $router->route(new Context('/foo/bar')));
    }

}