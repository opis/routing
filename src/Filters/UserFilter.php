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

namespace Opis\Routing\Filters;

use Opis\Routing\{
    Filter, Route, Router
};
use Opis\Http\Request;

class UserFilter implements Filter
{
    /**
     * @param Router $router
     * @param Route $route
     * @param Request $request
     * @return bool
     */
    public function filter(Router $router, Route $route, Request $request): bool
    {
        $invoker = $router->resolveInvoker($route, $request);
        $filters = $route->getRouteCollection()->getFilters();

        /**
         * @var string $name
         * @var callable|null $callback
         */
        foreach ($route->getFilters() as $name => $callback) {
            if ($callback === null) {
                if (!isset($filters[$name])) {
                    continue;
                }
                $callback = $filters[$name];
            }

            $arguments = $invoker->getArgumentResolver()->resolve($callback, false);

            if (false === $callback(...$arguments)) {
                return false;
            }
        }

        return true;
    }
}
