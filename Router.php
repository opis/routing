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

namespace Opis\Routing;

class Router
{
    
    protected $routes;
    
    protected $filters;
    
    protected $resolver;
    
    public function __construct(RouteCollection $routes,
                                DispatcherResolverInterface $resolver = null,
                                FilterCollection $filters = null)
    {
        $this->routes = $routes;
        
        if($resolver === null)
        {
            $resolver = new DispatcherResolver();
        }
        
        if($filters === null)
        {
            $filters = new FilterCollection();
            $filters[] = new PathFilter();
        }
        
        $this->resolver = $resolver;
        $this->filters = $filters;
    }
    
    public function getRouteCollection()
    {
        return $this->routes;
    }
    
    public function getFilterCollection()
    {
        return $this->filters;
    }
    
    public function getDispatcherResolver()
    {
        return $this->resolver;
    }
    
    public function route(Path $path)
    {
        foreach($this->routes as $route)
        {
            if($this->pass($path, $route))
            {
                $dispatcher = $this->resolver->resolve($this, $route);
                return $dispatcher->dispatch($this, $route);
            }
        }
    }
    
    protected function pass(Path $path, Route $route)
    {
        foreach($this->filters as $filter)
        {
            if(!$filter->pass($path, $route))
            {
                return false;
            }
        }
        return true;
    }
    
}