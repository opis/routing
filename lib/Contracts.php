<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2013-2015 Marius Sarca
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
    public function __toString();
}

interface PatternInterface extends Serializable
{
    public function __toString();
}

interface CompiledPatternInterface extends Serializable
{
    public function __toString();
}

interface CompilerInterface extends Serializable
{
    public function compile(PatternInterface $pattern, array $placeholders = array());
    
    public function delimit(CompiledPatternInterface $compiled);
    
    public function names(PatternInterface $pattern);
    
    public function values(CompiledPatternInterface $pattern, PathInterface $path);
    
    public function extract(array $names, array $values, array $defaults = array());
    
    public function bind(array $values, array $bindings);
    
    public function build(PatternInterface $pattern, array $values = array());
}

interface RouteInterface extends Serializable
{   
    public function getPattern();
    
    public function getAction();
    
    public function getWildcards();
    
    public function getBindings();
    
    public function getDefaults();
    
    public function getProperties();
    
    public static function getCompiler();
    
    public function compile();
    
    public function bind($name, Closure $callback);
    
    public function wildcard($name, $regex);
    
    public function implicit($name, $value);
    
    public function set($name, $value);
    
    public function has($name);
    
    public function get($name, $default = null);
}

interface RouterInterface
{
    public function route(PathInterface $path);
}

interface DispatcherInterface
{
    public function dispatch(PathInterface $path, RouteInterface $route);
    
    public function invokeAction(Closure $action, array $values = array());
}

interface DispatcherResolverInterface
{   
    public function resolve(PathInterface $path, RouteInterface $route);
}

interface FilterInterface
{
    public function pass(PathInterface $path, RouteInterface $route);
}
