<?php
/* ===========================================================================
 * Copyright 2018 Zindex Software
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

use Opis\Routing\{DefaultDispatcher,
    Route,
    Router,
    RouteCollection};
use Opis\Http\{
    Response,
    Request
};
use PHPUnit\Framework\TestCase;
use function Opis\Closure\init as enableSerialization;

class RoutingTest extends TestCase
{
    /** @var  Router */
    protected $router;
    /** @var  RouteCollection */
    protected $collection;

    public function setUp(): void
    {
        $this->collection = new RouteCollection();
        $global = new \ArrayObject();
        $global['x'] = 'X';
        $this->router = new Router($this->collection, new DefaultDispatcher(), $global);
    }

    /**
     * @param $pattern
     * @param $action
     * @param string $method
     * @return Route
     */
    protected function route($pattern, $action, $method = 'GET')
    {
        if (!is_array($method)) {
            $method = [$method];
        }
        return $this->collection->createRoute($pattern, $action, $method, 0, "current");
    }

    /**
     * @param $path
     * @param string $domain
     * @param string $method
     * @param bool $secure
     * @return Response
     */
    protected function exec($path, $method = 'GET', $domain = 'localhost', $secure = false, array $headers = [])
    {

        $headers += [
            'Host' => $domain
        ];

        $request = new Request($method, $path, 'HTTP/1.1', $secure, $headers);

        return $this->router->route($request);
    }

    public function testBasicRouting()
    {
        $this->route('/', function () {
            return 'OK';
        });

        $response = $this->exec('/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getBody());
    }

    public function testNotFound1()
    {
        $this->assertEquals(404, $this->exec('/')->getStatusCode());
        $this->assertEquals('', $this->exec('/')->getBody());
    }

    public function testNotFound2()
    {
        $this->route('/', function () {
            return 'OK';
        });

        $this->assertEquals(404, $this->exec('/foo')->getStatusCode());
        $this->assertEquals('', $this->exec('/foo')->getBody());
    }

    public function testNotFound3()
    {
        $this->route('/', function () {
            return new Response(404, [], null);
        });

        $this->assertEquals(404, $this->exec('/')->getStatusCode());
        $this->assertEquals('', $this->exec('/')->getBody());
    }

    public function testParam()
    {
        $this->route('/{foo}', function ($foo) {
            return $foo;
        });

        $this->assertEquals(200, $this->exec('/bar')->getStatusCode());
        $this->assertEquals('bar', $this->exec('/bar')->getBody());
    }

    public function testParamConstraintSuccess()
    {
        $this->route('/{foo}', function ($foo) {
            return $foo;
        })
            ->where('foo', '[a-z]+');

        $this->assertEquals(200, $this->exec('/bar')->getStatusCode());
        $this->assertEquals('bar', $this->exec('/bar')->getBody());
    }

    public function testParamInlineRegex()
    {
        $this->route('/{foo=[a-z]+}', function ($foo) {
            return $foo;
        });

        $this->assertEquals(200, $this->exec('/bar')->getStatusCode());
        $this->assertEquals('bar', $this->exec('/bar')->getBody());

        $this->assertEquals(404, $this->exec('/123')->getStatusCode());
        $this->assertEquals('', $this->exec('/123')->getBody());
    }

    public function testParamConstraintFail()
    {
        $this->route('/{foo}', function ($foo) {
            return $foo;
        })
            ->where('foo', '[a-z]+');

        $this->assertEquals(404, $this->exec('/123')->getStatusCode());
        $this->assertEquals('', $this->exec('/123')->getBody());
    }

    public function testParamOptional1()
    {
        $this->route('/{foo?}', function ($foo) {
            return $foo;
        });

        $this->assertEquals(200, $this->exec('/bar')->getStatusCode());
        $this->assertEquals('bar', $this->exec('/bar')->getBody());
    }

    public function testParamOptional2()
    {
        $this->route('/{foo?}', function ($foo = 'bar') {
            return $foo;
        });
        $this->assertEquals(200, $this->exec('/')->getStatusCode());
        $this->assertEquals('bar', $this->exec('/')->getBody());
    }

    public function testParamOptional3()
    {
        $this->route('/{foo?}', function ($foo) {
            return $foo;
        })
            ->implicit('foo', 'bar');

        $this->assertEquals(200, $this->exec('/')->getStatusCode());
        $this->assertEquals('bar', $this->exec('/')->getBody());
    }

    public function testMultipleParams()
    {
        $this->route('/{foo}/{bar}', function ($bar, $foo) {
            return $bar . $foo;
        });

        $this->assertEquals(200, $this->exec('/foo/bar')->getStatusCode());
        $this->assertEquals('barfoo', $this->exec('/foo/bar')->getBody());
    }

    public function testLocalBeforeFilterSuccess()
    {
        $this->route('/', function () {
            return 'OK';
        })
            ->filter('foo', function () {
                return true;
            });

        $this->assertEquals(200, $this->exec('/')->getStatusCode());
        $this->assertEquals('OK', $this->exec('/')->getBody());
    }

    public function testLocalBeforeFilterFail()
    {
        $this->route('/', function () {
            return 'OK';
        })
            ->filter('foo', function () {
                return false;
            });

        $this->assertEquals(404, $this->exec('/')->getStatusCode());
        $this->assertEquals('', $this->exec('/')->getBody());
    }

    public function testGlobalBeforeFilterSuccess()
    {
        $this->collection->filter('foo', function() {
            return true;
        });

        $this->route('/', function () {
            return 'OK';
        })
            ->filter('foo');

        $this->assertEquals(200, $this->exec('/')->getStatusCode());
        $this->assertEquals('OK', $this->exec('/')->getBody());
    }

    public function testGlobalBeforeFilterFail()
    {
        $this->collection->filter('foo', function() {
            return false;
        });

        $this->route('/', function () {
            return 'OK';
        })
            ->filter('foo');

        $this->assertEquals(404, $this->exec('/')->getStatusCode());
        $this->assertEquals('', $this->exec('/')->getBody());
    }

    public function testLocalFilterGlobalValuesSuccess()
    {
        $this->route('/', function () {
            return 'OK';
        })
            ->filter('foo', function ($x) {
                return $x == 'X';
            });

        $this->assertEquals(200, $this->exec('/')->getStatusCode());
        $this->assertEquals('OK', $this->exec('/')->getBody());
    }

    public function testLocalFilterGlobalValuesFail()
    {
        $this->route('/', function () {
            return 'OK';
        })
            ->filter('foo', function ($x) {
                return $x != 'X';
            });

        $this->assertEquals(404, $this->exec('/')->getStatusCode());
        $this->assertEquals('', $this->exec('/')->getBody());
    }

    public function testGlobalFilterGlobalValuesSuccess()
    {
        $this->collection->filter('foo', function ($x) {
            return $x == 'X';
        });

        $this->route('/', function () {
            return 'OK';
        })
            ->filter('foo');

        $this->assertEquals(200, $this->exec('/')->getStatusCode());
        $this->assertEquals('OK', $this->exec('/')->getBody());
    }

    public function testGlobalFilterGlobalValuesFail()
    {
        $this->collection->filter('foo', function ($x) {
            return $x != 'X';
        });

        $this->route('/', function () {
            return 'OK';
        })
            ->filter('foo');

        $this->assertEquals(404, $this->exec('/')->getStatusCode());
        $this->assertEquals('', $this->exec('/')->getBody());
    }

    public function testLocalBinding1()
    {
        $this->route('/{foo}', function ($foo) {
            return $foo;
        })
            ->bind('foo', function ($foo) {
                return strtoupper($foo);
            });

        $this->assertEquals(200, $this->exec('/bar')->getStatusCode());
        $this->assertEquals('BAR', $this->exec('/bar')->getBody());
    }

    public function testLocalBinding2()
    {
        $this->route('/', function ($foo) {
            return $foo;
        })
            ->bind('foo', function () {
                return 'BAR';
            });

        $this->assertEquals(200, $this->exec('/')->getStatusCode());
        $this->assertEquals('BAR', $this->exec('/')->getBody());
    }

    public function testGlobalBinding1()
    {
        $this->collection->bind('foo', function ($foo) {
            return strtoupper($foo);
        });

        $this->route('/{foo}', function ($foo) {
            return $foo;
        });

        $this->assertEquals(200, $this->exec('/bar')->getStatusCode());
        $this->assertEquals('BAR', $this->exec('/bar')->getBody());
    }

    public function testGlobalBinding2()
    {
        $this->collection->bind('foo', function () {
            return 'BAR';
        });

        $this->route('/', function ($foo) {
            return $foo;
        });

        $this->assertEquals(200, $this->exec('/')->getStatusCode());
        $this->assertEquals('BAR', $this->exec('/')->getBody());
    }

    public function testGlobals1()
    {
        $this->route('/', function ($x) {
            return $x;
        });

        $this->assertEquals(200, $this->exec('/')->getStatusCode());
        $this->assertEquals('X', $this->exec('/')->getBody());
    }

    public function testGlobals2()
    {
        $this->route('/', function ($y) {
            return $y;
        })
            ->bind('y', function ($x) {
                return $x;
            });

        $this->assertEquals(200, $this->exec('/')->getStatusCode());
        $this->assertEquals('X', $this->exec('/')->getBody());
    }

    public function testSerialization()
    {
        enableSerialization();

        $this->route('/', function () {
            return 'OK';
        })
            ->filter('foo', function ($x) {
                return $x == 'X';
            });

        $this->router = unserialize(serialize($this->router));
        $this->assertEquals(200, $this->exec('/')->getStatusCode());
        $this->assertEquals('OK', $this->exec('/')->getBody());
    }
}
