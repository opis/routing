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

namespace Opis\Routing\Test;

use Opis\Routing\Compiler;
use Opis\Routing\Context;
use Opis\Routing\Route;
use Opis\Routing\RouteCollection;
use Opis\Routing\Router;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    public function testKeys()
    {
        $c = new Compiler();
        $n = $c->getKeys('/a/{b}/c/{d?}');
        $this->assertEquals(['b', 'd'], $n);
    }

    public function testValues()
    {
        $c = new Compiler();
        $n = $c->getValues($c->getRegex('/a/{b}/c/{d}'), '/a/2/c/d');
        $this->assertEquals(['b' => '2', 'd' => 'd'], $n);
    }

    public function testOptValues()
    {
        $c = new Compiler();
        $n = $c->getValues($c->getRegex('/a/{b}/c/{d?}'), '/a/2/c');
        $this->assertEquals(['b' => '2'], $n);
    }
}