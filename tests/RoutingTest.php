<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013-2016 Marius Sarca
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

use Opis\Routing\Path;
use Opis\Routing\Route;
use Opis\Routing\Router;
use Opis\Routing\Pattern;
use Opis\Routing\Collections\RouteCollection;

class RoutingTest extends PHPUnit_Framework_TestCase
{
    protected $router;

    protected function route($pattern, $callback = null)
    {
        if ($this->router === null) {
            $this->router = new Router(new RouteCollection());
        }

        if ($callback === null) {
            $callback = array($this, 'cb');
        }

        $collection = $this->router->getRouteCollection();
        $route = new Route(new Pattern($pattern), $callback);
        $collection[] = $route;
        return $route;
    }

    public function cb()
    {
        return 'OK';
    }

    protected function exec($path)
    {
        return $this->router->route(new Path($path));
    }

    protected function tearDown()
    {
        $this->router = null;
    }

    /**
     * @dataProvider basicRoutingProvider
     */
    public function testBasicRouting($path)
    {
        $paths = array('/a', '/b', '/a/b/c', '/a/c', '/', '/c');

        foreach ($paths as $p) {
            $this->route($p);
        }

        $this->assertEquals('OK', $this->exec($path));
    }

    public function basicRoutingProvider()
    {
        return array(
            array('/b'),
            array('/a/b/c'),
            array('/a'),
            array('/c'),
            array('/a/c'),
            array('/'),
        );
    }

    public function testParametrizedRoute()
    {
        $this->route('/say/{word}', function($word) {
            return $word;
        });

        $this->assertEquals('hello', $this->exec('/say/hello'));
    }

    /**
     * @dataProvider wildcardProvider
     */
    public function testWildcard($path, $expected)
    {

        $this->route('/say/{word}', function() {
            return 10;
        })->wildcard('word', '[0-9]+');

        $this->route('/say/{word}', function() {
            return 11;
        })->wildcard('word', '[a-z]+');

        $this->route('/say/{word}', function() {
            return 12;
        });

        $this->assertEquals($expected, $this->exec($path));
    }

    public function wildcardProvider()
    {
        return array(
            array('/say/a', 11),
            array('/say/a9', 12),
            array('/say/900', 10),
            array('/say/abc', 11),
            array('/say/990a', 12),
        );
    }
}
