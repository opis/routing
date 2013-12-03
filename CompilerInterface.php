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

interface CompilerInterface
{
    /**
     * Compile a pattern
     */
    
    function compile($pattern, array $placeholders = array(), $delimit = true);
    
    function delimit($compiled);
    
    function names($pattern);
    
    function values($pattern, $path);
    
    function htmlencode($value);
    
    function extract(array $names, array $values, array $defaults = array());
    
    function bind(array $values, array $bindings);
    
    function build($pattern, array $values = array());
}