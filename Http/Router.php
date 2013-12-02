<?php

namespace Opis\Routing\Http;

use Opis\Routing\RouteCollection;
use Opis\Routing\Router as AbstractRouter;
use Opis\Routing\Http\Filters\Path as PathFilter;


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
        
        $this->compiler = new Compiler();
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getCompiler()
    {
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