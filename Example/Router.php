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

namespace Opis\Routing\Example;

use Opis\Routing\Router as AbstractRouter;
use Opis\Routing\Example\PathFilter;

class Router extends AbstractRouter
{   
    protected $path;
    
    protected $compiler;
    
    protected $filterList;
    
    protected $dispatcher;
    
    public function __construct($path, RouteCollection $collection)
    {
        parent::__construct($collection);
        
        $this->path = $path;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getCompiler()
    {
        if($this->compiler === null)
        {
            $this->compiler = new Compiler();
        }
        
        return $this->compiler;
    }
    
    public function filters()
    {
        if($this->filterList === null)
        {
            $this->filterList = array(
                new PathFilter($this)
            );
        }
        return $this->filterList;
    }
    
    public function dispatcher()
    {
        if($this->dispatcher === null)
        {
            $this->dispatcher = new Dispatcher($this);
        }
        
        return $this->dispatcher;
    }
}