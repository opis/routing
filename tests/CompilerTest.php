<?php

namespace Opis\Routing\Test;

use Opis\Routing\Compiler;
use Opis\Routing\Context;
use Opis\Routing\Route;
use Opis\Routing\RouteCollection;
use Opis\Routing\Router;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    public function testNames()
    {
        $c = new Compiler();
        $n = $c->getNames('/a/{b}/c/{d?}');
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