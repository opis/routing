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

namespace Opis\Routing;


class RouteCollection
{
    /** @var Route[] */
    protected $routes = array();

    /** @var string[] */
    protected $regex = array();

    /** @var string[] */
    protected $namedRoutes = array();

    /** @var  Compiler */
    protected $compiler;

    public function __construct(Compiler $compiler = null)
    {
        if ($compiler === null){
            $compiler = new Compiler();
        }

        $this->compiler = $compiler;
    }

    /**
     * @return Compiler
     */
    public function getCompiler(): Compiler
    {
        return $this->compiler;
    }

    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @return array
     */
    public function getRegexPatterns(): array
    {
        return $this->regex;
    }

    /**
     * @return array
     */
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }

    /**
     * @param Route $route
     * @return RouteCollection
     */
    public function addRoute(Route $route): self
    {
        $id = $route->getID();
        $this->routes[$id] = $route;
        $this->regex[$id] = $this->compiler->getRegex($route->getPattern(), $route->getWildcards());
        if(null !== $name = $route->getName()){
            $this->namedRoutes[$name] = $id;
        }
        return $this;
    }

    /**
     * @param string $id
     * @return bool|Route
     */
    public function getRoute(string $id)
    {
        return $this->routes[$id] ?? false;
    }

    /**
     * @param string $id
     * @return bool|string
     */
    public function getRegex(string $id)
    {
        return $this->regex[$id] ?? false;
    }

}
