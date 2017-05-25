<?php
/* ===========================================================================
 * Copyright 2013-2017 The Opis Project
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

use Serializable;

class RouteCollection implements Serializable
{
    /** @var Route[] */
    protected $routes = array();

    /** @var null|string[] */
    protected $regex;

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
        if($this->regex === null){
            $this->regex = array();
            foreach($this->routes as $route){
                $this->regex[$route->getID()] = $this->compiler->getRegex($route->getPattern(), $route->getWildcards());
            }
        }
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
        $id = $route->setRouteCollection($this)->getID();
        $this->routes[$id] = $route;
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
        if($this->regex === null){
            $this->getRegexPatterns();
        }
        return $this->regex[$id] ?? false;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize(array(
            'compiler' => $this->compiler,
            'routes' => $this->routes,
            'namedRoutes' => $this->namedRoutes,
            'regex' => $this->getRegexPatterns(),
        ));
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $object = unserialize($serialized);
        $this->compiler = $object['compiler'];
        $this->routes = $object['routes'];
        $this->namedRoutes = $object['namedRoutes'];
        $this->regex = $object['regex'];
    }

}
