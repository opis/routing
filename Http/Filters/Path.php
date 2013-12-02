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

namespace Opis\Routing\Http\Filters;

use Opis\Routing\FilterInterface;
use Opis\Routing\Route;
use Opis\Routing\Http\Router;

class Path implements FilterInterface
{
    protected $compiler;
    
    protected $path;
    
    protected $placeholders;
    
    public function __construct(Router $router)
    {
        $this->compiler = $router->getCompiler();
        $this->path = $router->getPath();
        $this->placeholders = $router->getCollection()->getPlaceholders();
    }
    
    public function match(Route $route)
    {
        $placeholders = $route->getPlaceholders() + $this->placeholders;
        $pattern = $this->compiler->compile($route->getPath(), $placeholders);
        return preg_match($pattern, $this->path);
    }
    
}