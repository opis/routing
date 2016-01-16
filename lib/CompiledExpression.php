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

class CompiledExpression
{
    protected $compiler;
    protected $pattern;
    protected $compiledPattern;
    protected $wildcards;
    protected $bindings;
    protected $defaults;
    protected $cache = array();

    public function __construct(
        Compiler $compiler,
        Pattern $pattern, 
        $compiledPattern = null,
        array $wildcards = array(), 
        array $defaults = array(), 
        array $bindings = array())
    {
        $this->compiler = $compiler;
        $this->pattern = $pattern;
        $this->compiledPattern = $compiledPattern;
        $this->wildcards = $wildcards;
        $this->defaults = $defaults;
        $this->bindings = $bindings;
    }

    public function pattern()
    {
        return $this->pattern;
    }

    public function wildcards()
    {
        return $this->wildcards;
    }

    public function defaults()
    {
        return $this->defaults;
    }

    public function specials()
    {
        return $this->specials;
    }

    public function bindings()
    {
        return $this->bindings;
    }

    public function names()
    {
        if (!isset($this->cache['names'])) {
            $this->cache['names'] = $this->compiler->names($this->pattern());
        }

        return $this->cache['names'];
    }

    public function compile()
    {
        if ($this->compiledPattern === null) {
            $this->compiledPattern = $this->compiler->compile($this->pattern(), $this->wildcards());
        }

        return $this->compiledPattern;
    }

    public function values(Path $path)
    {
        $id = (string) $path;

        if (!isset($this->cache['values'][$id])) {
            $this->cache['values'][$id] = $this->compiler->values($this->compile(), $path);
        }

        return $this->cache['values'][$id];
    }

    public function extract(Path $path)
    {
        $id = (string) $path;

        if (!isset($this->cache['extract'][$id])) {
            $this->cache['extract'][$id] = $this->compiler->extract($this->names(), $this->values($path), $this->defaults());
        }

        return $this->cache['extract'][$id];
    }

    public function bind(Path $path, array $specials = array())
    {
        $id = (string) $path;

        if (!isset($this->cache['bind'][$id])) {
            $this->cache['bind'][$id] = $this->compiler->bind($this->extract($path), $this->bindings(), $specials);
        }

        return $this->cache['bind'][$id];
    }

    public function delimit()
    {
        if (!isset($this->cache['delimit'])) {
            $this->cache['delimit'] = $this->compiler->delimit($this->compile());
        }
        return $this->cache['delimit'];
    }

    public function match(Path $value)
    {
        return preg_match($this->delimit(), (string) $value);
    }
}
