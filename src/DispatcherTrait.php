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

namespace Opis\Routing;

trait DispatcherTrait
{
    /** @var  Context */
    protected $context;

    /** @var  Router */
    protected $router;

    /** @var  Route|null */
    protected $route;

    /**
     * @return null|Route
     */
    protected function findRoute()
    {
        /** @var Route $route */
        foreach ($this->match() as $route){
            $this->route = $route;
            if(!$this->pass($route)){
                continue;
            }

            return $route;
        }

        return null;
    }

    /**
     * @return \Generator
     */
    protected function match(): \Generator
    {
        $context = (string) $this->context;
        $routes = $this->router->getRouteCollection();

        foreach ($routes->getRegexPatterns() as $routeID => $pattern){
            if(preg_match($pattern, $context)){
                yield $routes->getRoute($routeID);
            }
        }
    }

    /**
     * @param Route $route
     * @return bool
     */
    protected function pass(Route $route): bool
    {
        foreach ($this->router->getFilterCollection()->getFilters() as $filter){
            if(!$filter->pass($this->router, $this->context, $route)){
                return false;
            }
        }

        return true;
    }

    /**
     * @return callable
     */
    protected function getSpecialValues(): callable
    {
        return function (){
            $extra = [
                'router' => $this->router,
                'route' => $this->route,
                'context' => $this->context,
            ];

            return $this->router->getSpecialValues() + $extra;
        };
    }
}