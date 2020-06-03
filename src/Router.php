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

namespace Opis\Routing;

use ArrayAccess, ArrayObject;
use Opis\Http\Request;
use Opis\Routing\Filters\{
    RequestFilter, UserFilter
};

class Router
{

    private RouteCollection $routes;

    /** @var Filter[] */
    private array $filters;

    private Dispatcher $dispatcher;

    protected ArrayAccess $global;

    private array $compacted = [];

    /**
     * Router constructor.
     * @param RouteCollection $routes
     * @param Dispatcher|null $dispatcher
     * @param ArrayAccess|null $global
     * @param array|null $filters
     */
    public function __construct(
        RouteCollection $routes,
        ?Dispatcher $dispatcher = null,
        ?ArrayAccess $global = null,
        ?array $filters = null
    ) {
        $this->routes = $routes;
        $this->dispatcher = $dispatcher ?? new DefaultDispatcher();
        $this->global = $global ?? new ArrayObject();
        $this->filters = $filters ?? [new RequestFilter(), new UserFilter()];
    }

    /**
     * Get the route collection
     *
     * @return  RouteCollection
     */
    public function getRouteCollection(): RouteCollection
    {
        return $this->routes;
    }

    /**
     * @return Filter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Get global values
     *
     * @return  ArrayAccess
     */
    public function getGlobalValues(): ArrayAccess
    {
        return $this->global;
    }

    /**
     * Get the dispatcher resolver
     *
     * @return Dispatcher
     */
    public function getDispatcher(): Dispatcher
    {
        return $this->dispatcher;
    }

    /**
     * @param Route $route
     * @param Request $request
     * @return RouteInvoker
     */
    public function resolveInvoker(Route $route, Request $request): RouteInvoker
    {
        $cid = spl_object_hash($request);
        $rid = spl_object_hash($route);

        if (!isset($this->compacted[$cid][$rid])) {
            return $this->compacted[$cid][$rid] = new RouteInvoker($this, $route, $request);
        }

        return $this->compacted[$cid][$rid];
    }

    public function route(Request $request)
    {
        $this->global['request'] = $request;
        return $this->getDispatcher()->dispatch($this, $request);
    }
}
