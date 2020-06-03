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

use Opis\Http\Request;
use Opis\Routing\{
    Filter, Route, Router
};

class RequestFilter implements Filter
{
    /**
     * @param Router $router
     * @param Route $route
     * @param Request $request
     * @return bool
     */
    public function filter(Router $router, Route $route, Request $request): bool
    {
        if (!in_array($request->getMethod(), $route->getMethod())) {
            return false;
        }

        $properties = $route->getProperties();

        if (($properties['secure'] ?? false) && $request->getUri()->getScheme() !== 'https') {
            return false;
        }

        if (null !== $domain = ($properties['domain'] ?? null)) {
            $regex = $route->getRouteCollection()->getDomainBuilder()->getRegex($domain, $route->getPlaceholders());
            if(!preg_match($regex, $request->getUri()->getHost())) {
                return false;
            }
        }

        return true;
    }
}
