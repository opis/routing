<?php
/* ===========================================================================
 * Copyright 2018-2020 Zindex Software
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

namespace Opis\Routing\Traits;

use Generator;
use Opis\Http\Request;
use Opis\Routing\{Route, Router};

trait Dispatcher
{
    /**
     * @param Router $router
     * @param Request $request
     * @return null|Route
     */
    protected function findRoute(Router $router, Request $request): ?Route
    {
        $global = $router->getGlobalValues();
        $global['router'] = $router;
        /** @var Route $route */
        foreach ($this->match($router, $request->getUri()->path() ?? '') as $route) {
            $global['route'] = $route;
            if (!$this->filter($router, $route, $request)) {
                continue;
            }
            return $route;
        }
        $global['route'] = null;
        return null;
    }

    /**
     * @param Router $router
     * @param string $path
     * @return Generator
     */
    protected function match(Router $router, string $path): Generator
    {
        $routes = $router->getRouteCollection();
        $routes->sort();

        foreach ($routes->getRegexPatterns() as $routeID => $pattern) {
            if (preg_match($pattern, $path)) {
                yield $routes->getRoute($routeID);
            }
        }
    }

    /**
     * @param Router $router
     * @param Route $route
     * @param Request $request
     * @return bool
     */
    protected function filter(Router $router, Route $route, Request $request): bool
    {
        foreach ($router->getFilters() as $filter) {
            if (!$filter->filter($router, $route, $request)) {
                return false;
            }
        }

        return true;
    }
}