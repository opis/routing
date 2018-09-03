<?php
/* ============================================================================
 * Copyright 2018 Zindex Software
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

use ArrayObject;
use Opis\Routing\ArgumentResolver;
use PHPUnit\Framework\TestCase;

class ArgumentResolverTest extends TestCase
{
    public function testValue()
    {
        $r = new ArgumentResolver(new ArrayObject(['a' => 1, 'b' => 'test']));

        $this->assertEquals(1, $r->getArgumentValue('a'));

        $this->assertEquals('test', $r->getArgumentValue('b'));
        $this->assertNull($r->getArgumentValue('c'));

        $r->addValue('c', [1, 2, 3]);
        $this->assertEquals([1, 2, 3], $r->getArgumentValue('c'));

        $r->addValue('a', 2);
        $this->assertEquals(2, $r->getArgumentValue('a'));
    }

    public function testBindings()
    {
        $r = new ArgumentResolver(new ArrayObject(['a' => 1]));

        $r->addBinding('a', function ($a) {
            return $a + 1;
        });

        $this->assertEquals(2, $r->getArgumentValue('a'));
    }

    public function testResolve()
    {
        $r = new ArgumentResolver(new ArrayObject(['a' => 1, 'b' => 'test']));

        $this->assertEquals([1, 'test', null], $r->resolve(function ($a, $b, $c = null) {}));

        $r->addValue('c', [1, 2, 3]);

        $this->assertEquals([1, 'test', [1, 2, 3]], $r->resolve(function ($a, $b, $c = null) {}));

        $this->assertEquals([null, null, 5, 'test-D'], $r->resolve(function ($A, $B, $C = 5, $D = 'test-D') {}));
    }

    public function testBindingsNested()
    {
        $r = new ArgumentResolver(new ArrayObject(['a' => 1, 'b' => 'test']));

        $r->addBinding('a', function ($a) {
            return $a + 1;
        });

        $r->addBinding('b', function ($a, $b) {
            return strtoupper($b . $a);
        });

        $this->assertEquals('TEST2', $r->getArgumentValue('b'));
    }
}