<?php

namespace Opis\Routing\Test;

use Opis\Routing\Compiler;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{


    public function testNames()
    {
        $c = new Compiler();
        $names = $c->names('/{a}/b/c/{d}');
        $this->assertEquals(['a', 'd'], $names);
    }

    public function testOptionalNames()
    {
        $c = new Compiler();
        $names = $c->names('/{a?}/b/c/{d}/{e?}');
        $this->assertEquals(['a', 'd', 'e'], $names);
    }

    public function testValues()
    {
        
        $c = new Compiler();
        $r = $c->getRegex('/{a}/{b}/{c}/{d}/{e}/{f}/{g}/{h}/{j}/{k}/{l}');
        die($r);

    }
}