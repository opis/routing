<?php
/* ===========================================================================
 * Copyright 2013-2018 The Opis Project
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

use Opis\Routing\Context;
use Opis\Routing\Dispatcher;
use Opis\Routing\RouteCollection;
use Opis\Routing\Router;
use PHPUnit\Framework\TestCase;

class RoutingTest extends TestCase
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
        $this->router = new Router($this->routes, $this->dispatcher);
    }

    public function tearDown()
    {
        $this->routes = new RouteCollection();
        $this->router = new Router($this->routes, $this->dispatcher);
    }

    public function testBasicRouting()
    {
        $this->routes->createRoute('/foo', function () {
            return 'ok';
        });

        $this->assertEquals('ok', $this->router->route(new Context('/foo')));
    }

    public function testRouteArgument()
    {
        $this->routes->createRoute('/foo/{bar}', function ($bar) {
            return $bar;
        });

        $this->assertEquals('baz', $this->router->route(new Context('/foo/baz')));
    }

    public function testOptionalArgument()
    {
        $this->routes->createRoute('/foo/{bar?}', function ($bar = 'baz') {
            return $bar;
        });

        $this->assertEquals('baz', $this->router->route(new Context('/foo')));
    }

    public function testImplicitArgument()
    {
        $this->routes->createRoute('/foo/{bar?}', function ($bar) {
            return $bar;
        })->implicit('bar', 'baz');

        $this->assertEquals('baz', $this->router->route(new Context('/foo')));
    }

    public function testMultipleArguments()
    {
        $this->routes->createRoute('/{foo}/{bar}', function ($bar, $foo) {
            return $foo . $bar;
        });

        $this->assertEquals('bazqux', $this->router->route(new Context('/baz/qux')));
    }

    public function testWildcardArgument()
    {
        $this->routes->createRoute('/foo/{bar}', function ($bar) {
            return $bar;
        })->where('bar', '[0-9]+');

        $this->assertEquals(null, $this->router->route(new Context('/foo/bar')));
        $this->assertEquals('123', $this->router->route(new Context('/foo/123')));
    }

    public function testBindArgument()
    {
        $this->routes->createRoute('/foo/{bar}', function ($bar) {
            return $bar;
        })->bind('bar', function ($bar) {
            return strtoupper($bar);
        });

        $this->assertEquals('BAR', $this->router->route(new Context('/foo/bar')));
    }

    public function testSerialization()
    {
        $routes = new RouteCollection();

        $routes->createRoute('/foo/{bar}', function ($bar) {
            return $bar;
        })->bind('bar', function ($bar) {
            return strtoupper($bar);
        });

        $router = new Router(unserialize(serialize($routes)), $this->dispatcher);
        $this->assertEquals('BAR', $router->route(new Context('/foo/bar')));
    }

    public function testWhereIn()
    {
        $this->routes->createRoute('/foo/{bar}', function ($bar) {
            return $bar;
        })->whereIn('bar', ['a', 'b', 'car']);


        $this->assertEquals(null, $this->router->route(new Context('/foo/bar')));
        $this->assertEquals('a', $this->router->route(new Context('/foo/a')));
        $this->assertEquals('b', $this->router->route(new Context('/foo/b')));
        $this->assertEquals(null, $this->router->route(new Context('/foo/ab')));
        $this->assertEquals('car', $this->router->route(new Context('/foo/car')));
    }

    public function testBindings()
    {
        $this->routes->createRoute('/foo/{bar}', function ($baz) {
            return $baz;
        })
            ->bind('bar', function ($bar) {
                return strtoupper($bar);
            })
            ->bind('baz', function ($bar) {
                return 'baz' . $bar;
            });

        $this->assertEquals('bazBAR', $this->router->route(new Context('/foo/bar')));
    }

}