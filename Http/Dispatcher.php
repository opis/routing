<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013 Marius Sarca
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

namespace Opis\Routing\Http;

use Opis\Routing\Route;
use Opis\Routing\DispatcherInterface;

class Dispatcher implements DispatcherInterface
{
    protected $path;
    
    protected $bindings;
    
    protected $placeholders;
    
    protected $compiler;
    
    public function __construct(Router $router)
    {
        $this->compiler = $router->getCompiler();
        $this->path = $router->getPath();
        $this->bindings = $router->getCollection()->getBindings();
        $this->placeholders = $router->getCollection()->getPlaceholders();
    }
    
    public function dispatch(Route $route)
    {
        $routePath = $route->getPath();
        $placeholders = $route->getPlaceholders() + $this->placeholders;
        $bindings = $route->getBindings() + $this->bindings;
        $expr = $this->compiler->compile($routePath, $placeholders);
        $names = $this->compiler->names($routePath);
        $values = $this->compiler->values($expr, $this->path);
        $values = $this->compiler->extract($names, $values, $route->getDefaults());
        $arguments = $this->compiler->bind($values, $bindings);
        $action = $route->getAction();
        return call_user_func_array($action, $arguments);
    }
}