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

namespace Opis\Routing\Contracts;

use Serializable;
use Closure;

interface PathInterface
{
    function __toString();
}

interface PatternInterface extends Serializable
{
    function __toString();
}

interface CompiledPatternInterface extends Serializable
{
    function __toString();
}

interface CompilerInterface extends Serializable
{
    function compile(PatternInterface $pattern, array $placeholders = array());
    
    function delimit(CompiledPatternInterface $compiled);
    
    function names(PatternInterface $pattern);
    
    function values(CompiledPatternInterface $pattern, PathInterface $path);
    
    function extract(array $names, array $values, array $defaults = array());
    
    function bind(array $values, array $bindings);
    
    function build(PatternInterface $pattern, array $values = array());
}

interface RouteInterface extends Serializable
{   
    function getPattern();
    
    function getAction();
    
    function getWildcards();
    
    function getBindings();
    
    function getDefaults();
    
    function getProperties();
    
    function getCompiler();
    
    function compile();
    
    function bind($name, Closure $callback);
    
    function wildcard($name, $regex);
    
    function implicit($name, $value);
    
    function set($name, $value);
    
    function has($name);
    
    function get($name, $default = null);
}

interface RouterInterface
{
    function route(PathInterface $path);
}

interface DispatcherInterface
{
    function dispatch(PathInterface $path, RouteInterface $route);
    
    function invokeAction(Closure $action, array $values = array());
}

interface DispatcherResolverInterface
{   
    function resolve(PathInterface $path, RouteInterface $route);
}

interface FilterInterface
{
    function pass(PathInterface $path, RouteInterface $route);
}
